<?php
use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\Message;
use skrtdev\Telegram\CallbackQuery;

require_once 'utils.php';

define('NONE', 0);
define('WEBHOOK', 1);
$Bot = null;

function initializeBot($mode = WEBHOOK) {
    global $Bot;
    if (is_null($Bot)) {
        if(defined("BASE_PATH")){
            $base_path = "/".BASE_PATH."api/bot/telegram";
        } else {
            $base_path = "/api/bot/telegram";
        }
        $_SERVER['SCRIPT_URL'] = $base_path;
    
        $NovagramConfig = [
            "disable_ip_check" => true, //TODO: fix NovaGram ip check and enable it again
            "parse_mode" => "HTML",
            "mode" => $mode
        ];
    
        if(defined("BOT_TELEGRAM_DEBUG_USER")){
            $NovagramConfig["debug"] = BOT_TELEGRAM_DEBUG_USER;
        }
    
        $Bot = new Bot(BOT_TELEGRAM_API_KEY, $NovagramConfig);
    }
}

function getLanguageFromTelegramMessage(Message $message) {
    global $translations;
    $language = $translations->default_language;
    if ($message->from->language_code !== null) {
        $language = explode("-", $message->from->language_code)[0];
    }
    return $language;
}

function getUserIdByFrom($from_id)
{
    global $db;
    return $db->selectValue("SELECT user FROM `".DB_PREFIX."_bot_telegram` WHERE `chat_id` = ?", [$from_id]);
}

function getUserIdByMessage(Message $message)
{
    return getUserIdByFrom($message->from->id);
}

function requireBotLogin(Message $message)
{
    global $users;

    $userId = getUserIdByMessage($message);
    if ($userId === null) {
        $message->reply(__("telegram_bot.account_not_linked"));
        exit();
    } else {
        if($users->auth->hasRole(\Delight\Auth\Role::CONSULTANT)) {
            //Migrate to new user roles
            $users->auth->admin()->removeRoleForUserById($users->auth->getUserId(), \Delight\Auth\Role::CONSULTANT);
            $users->auth->admin()->addRoleForUserById($users->auth->getUserId(), Role::SUPER_EDITOR);
        }
    }
}

function sendTelegramNotification($message, $do_not_send_if_same=true)
{
    global $Bot, $db;

    if(is_null($Bot)) initializeBot(NONE);

    $sentMessages = [];

    //TODO: implement different types of notifications
    //TODO: add command for subscribing to notifications
    $chats = $db->select("SELECT * FROM `".DB_PREFIX."_bot_telegram_notifications`");
    if(!is_null($chats)) {
        foreach ($chats as $chat) {
            if($do_not_send_if_same && urldecode($chat['last_notification']) === $message) continue;
            $chat = $chat['chat_id'];
            $sendMessage = $Bot->sendMessage([
                "chat_id" => $chat,
                "text" => $message
            ]);
            $db->update(
                DB_PREFIX."_bot_telegram_notifications",
                ["last_notification" => urlencode($message)],
                ["chat_id" => $chat]
            );
            $sentMessages[$chat] = $sendMessage->message_id;
        }
    }
    return $sentMessages;
}

function sendTelegramNotificationToUser($message, $userId, $options = [])
{
    global $Bot, $db;

    if(is_null($Bot)) initializeBot(NONE);

    $chat = $db->selectValue("SELECT `chat_id` FROM `".DB_PREFIX."_bot_telegram` WHERE `user` = ?", [$userId]);
    if(!is_null($chat)) {
        $message_response = $Bot->sendMessage(array_merge([
            "chat_id" => $chat,
            "text" => $message
        ], $options));
        return [$message_response->message_id, $chat];
    }
}

function generateAlertMessage($alertType, $alertEnabled, $alertNotes, $alertCreatedBy, $alertDeleted=false) {
    global $users;

    $message = 
      "<b><i><u>".($alertEnabled ? __("alerts.alert_in_progress") : ($alertDeleted ? __("alerts.alert_cancelled") : __("alerts.alert_complete"))).":</u></i></b> ".
      ($alertType === "full" ? __("telegram.full_team_requested") : __("telegram.support_team_requested")) . "\n";
    
    if(!is_null($alertNotes) && $alertNotes !== "") {
        $message .= ucfirst(__("notes")).":\n<b>".$alertNotes."</b>\n";
    }
    if(!is_null($alertCreatedBy)) {
        $message .= __("alerts.added_by") . "<b>".$users->getName($alertCreatedBy)."</b>\n";
    }
    
    return $message;
}

function generateAlertReportMessage($alertType, $crew, $alertEnabled, $alertNotes, $alertCreatedBy, $alertDeleted=false) {
    global $users;

    $message = generateAlertMessage($alertType, $alertEnabled, $alertNotes, $alertCreatedBy);
    $message .= "\n".ucfirst(__("team")).":\n";

    foreach($crew as $member) {
        if((!$alertEnabled || $alertDeleted) && $member["response"] === "waiting") continue;
        $user = $users->getUserById($member['id']);
        $message .= "<i>".$user["name"]."</i> ";
        if($user["chief"]) $message .= __("telegram_bot.chief_abbr");
        if($user["driver"]) $message .= "🚒";
        $message .= "- ";
        if($member["response"] === "waiting") {
            $message .= __("telegram_bot.alert_waiting");
        } else if($member["response"] === true) {
            $message .= __("telegram_bot.alert_available");
        } else if($member["response"] === false) {
            $message .= __("telegram_bot.alert_not_available");
        }
        $message .= "\n";
    }

    return $message;
}

function sendAlertReportMessage($alertType, $crew, $alertEnabled, $alertNotes, $alertCreatedBy, $alertDeleted = false) {
    $message = generateAlertReportMessage($alertType, $crew, $alertEnabled, $alertNotes, $alertCreatedBy, $alertDeleted);

    return sendTelegramNotification($message, false);
}

function sendAlertRequestMessage($alertType, $userId, $alertId, $alertNotes, $alertCreatedBy, $alertDeleted = false) {
    return sendTelegramNotificationToUser(generateAlertMessage($alertType, true, $alertNotes, $alertCreatedBy, $alertDeleted), $userId, [
        'reply_markup' => [
            'inline_keyboard' => [
                [
                    [
                        'text' => __("telegram_bot.alert_accept_button"),
                        'callback_data' => "alert_yes_".$alertId
                    ],
                    [
                        'text' => __("telegram_bot.alert_decline_button"),
                        'callback_data' => "alert_no_".$alertId
                    ]
                ]
            ]
        ]
    ]);
}

function yesOrNo($value)
{
    return '<b>'.strtoupper(($value === 1 || $value) ? __("yes") : __('no')).'</b>';
}

function sendLongMessage($text, $userId) {
    global $Bot;
    if(strlen($text) > 4096) {
        $message_json = wordwrap($text, 4096, "<@MESSAGE_SEPARATOR@>", true);
        $message_json = explode("<@MESSAGE_SEPARATOR@>", $message_json);
        foreach($message_json as $segment) {
            sendLongMessage($segment, $userId);
        }
    } else {
        $Bot->sendMessage($userId, $text);
    }
}

function telegramBotRouter() {
    global $Bot;

    define("running_telegram_bot_webhook", true);

    initializeBot();

    $Bot->addErrorHandler(function ($e) {
        print('Caught '.get_class($e).' exception from general handler'.PHP_EOL);
        print($e.PHP_EOL);
    });
    
    $Bot->onCommand('start', function (Message $message, array $args = []) {
        global $db, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        if(isset($args[0])) {
            $registered_chats = $db->select("SELECT * FROM `".DB_PREFIX."_bot_telegram` WHERE `chat_id` = ?", [$message->from->id]);
            if(!is_null($registered_chats) && count($registered_chats) > 1) {
                $message->chat->sendMessage(__("telegram_bot.account_already_linked"));
                return;
            }
            $response = $db->update(
                DB_PREFIX.'_bot_telegram',
                ['chat_id' => $message->from->id],
                ['tmp_login_token' => $args[0]]
            );
            if($response === 1) {
                logger("log_messages.telegram_account_linked");
                $message->chat->sendMessage(__("telegram_bot.login_successful"));
            } else {
                $message->chat->sendMessage(__("telegram_bot.login_failed"));
            }
        } else {
            $message->chat->sendMessage(__("telegram_bot.account_not_linked"));
        }
    });

    $Bot->onCommand('help', function (Message $message, array $args = []) {
        global $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        $message->chat->sendMessage(__("telegram_bot.help_command"));
    });
    
    $Bot->onCommand('debug_userid', function (Message $message) {
        global $Bot, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        $messageText = sprintf(__("telegram_bot.debug_telegram_user_id"), $message->from->id);
        $messageText .= "\n".sprintf(__("telegram_bot.debug_chat_id"), $message->chat->id);
        if(isset($message->from->username)) {
            $messageText .= "\n".sprintf(__("telegram_bot.debug_username"), $message->from->username);
        }
        if(isset($message->from->first_name)) {
            $messageText .= "\n".sprintf(__("telegram_bot.debug_first_name"), $message->from->first_name);
        }
        if(isset($message->from->last_name)) {
            $messageText .= "\n".sprintf(__("telegram_bot.debug_last_name"), $message->from->last_name);
        }
        if(isset($message->from->language_code)) {
            $messageText .= "\n".sprintf(__("telegram_bot.debug_language_code"), $message->from->language_code);
        }
        if(isset($message->from->is_bot)) {
            $messageText .= "\n".sprintf(__("telegram_bot.debug_is_bot"), yesOrNo($message->from->is_bot));
        }
        $message->reply($messageText);

        if(defined("BOT_TELEGRAM_DEBUG_USER") && BOT_TELEGRAM_DEBUG_USER !== $message->from->id){
            $messageText .= "\n\n".__("telegram_bot.debug_message_json");
            $Bot->sendMessage(BOT_TELEGRAM_DEBUG_USER, $messageText);
            $message_json = json_encode($message, JSON_PRETTY_PRINT);
            sendLongMessage($message_json, BOT_TELEGRAM_DEBUG_USER);
        }
    });
    
    $Bot->onCommand('info', function (Message $message) {
        global $users, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        $user_id = getUserIdByMessage($message);
        if(is_null($user_id)) {
            $message->chat->sendMessage(__("telegram_bot.account_not_linked"));
        } else {
            $user = $users->getUserById($user_id);
            $template_replacements = [
                "{name}" => $user["name"],
                "{available}" => yesOrNo($user["available"]),
                "{chief}" => yesOrNo($user["chief"] === 1),
                "{driver}" => yesOrNo($user["driver"] === 1),
                "{services}" => $user["services"],
                "{trainings}" => $user["trainings"],
                "{availability_minutes}" => $user["availability_minutes"]
            ];
            $message->chat->sendMessage(strtr(__("telegram_bot.info_command"), $template_replacements));
        }
    });

    //Too difficult and "spaghetti to explain it here in comments, please use https://regexr.com/
    //Jokes apart, checks if text contains something like "Attiva", "attiva", "Disponibile", "disponibile" but not "Non ", "non ", "Non_", "non_", "Dis" or "dis"
    $Bot->onText("/\/?(Sono |sono |Io sono |Io sono )?(?<!non( |_))(?<!dis)(?<!Non( |_))(?<!Dis)(Attiva|Attivami|Attivo|Disponibile|Operativo|attiva|attivami|attivo|disponibile|operativo)/", function (Message $message, $matches = []) {
        global $Bot, $availability, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 3) return;
        $user_id = getUserIdByMessage($message);
        $availability->change(1, $user_id, true);
        $Bot->sendMessage([
            "chat_id" => $message->from->id,
            "text" => sprintf(__("telegram_bot.availability_updated"), __("available")),
            "disable_notification" => true
        ]);
    });

    $Bot->onText("/\/?(Io |Io sono )?(Disattiva|Disattivo|Disattivami|Non( |_)attivo|Non( |_)(Sono |sono )?disponibile|Non( |_)(Sono |sono )?operativo|disattiva|disattivo|sisattivami|non( |_)(Sono |sono )?attivo|non( |_)(Sono |sono )?disponibile|non( |_)(Sono |sono )?operativo)/", function (Message $message, $matches = []) {
        global $Bot, $availability, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 4) return;
        $user_id = getUserIdByMessage($message);
        $availability->change(0, $user_id, true);
        $Bot->sendMessage([
            "chat_id" => $message->from->id,
            "text" => sprintf(__("telegram_bot.availability_updated"), __("not_available")),
            "disable_notification" => true
        ]);
    });

    $Bot->onText("/\/?(Abilita( |_)|abilita( |_)|Attiva( |_)|attiva( |_))?(Programma|Programmazione|programmazione|Programmazione( |_)oraria|programma|programmazione( |_)oraria)/", function (Message $message, $matches = []) {
        global $Bot, $availability, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 3) return;
        $userId = getUserIdByMessage($message);
        $availability->change_manual_mode(0, $userId);
        $Bot->sendMessage($message->from->id, __("telegram_bot.schedule_mode_enabled"));
    });

    $Bot->onText("/\/?(Stato|stato)( |_)?(Distaccamento|distaccamento)?/", function (Message $message, $matches = []) {
        global $db, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 2) return;
        $available_users_count = $db->selectValue("SELECT COUNT(id) FROM `".DB_PREFIX."_profiles` WHERE `available` = 1 AND `hidden` = 0");
        if($available_users_count >= 5) {
            $message->reply(__("telegram_bot.available_full"));
        } else if($available_users_count >= 2) {
            $message->reply(__("telegram_bot.available_support"));
        } else if($available_users_count >= 0) {
            $message->reply(__("telegram_bot.not_available"));
        }
    });

    $Bot->onText("/\/?(Elenco|elenco|Elenca|elenca)?(_| )?(Disponibili|disponibili)/", function (Message $message, $matches = []) {
        global $db, $translations;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));

        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 2) return;
        $result = $db->select("SELECT `chief`, `driver`, `available`, `name` FROM `".DB_PREFIX."_profiles` WHERE available = 1 and hidden = 0 ORDER BY chief DESC, services ASC, trainings DESC, availability_minutes DESC, name ASC");
        if(!is_null($result) && count($result) > 0) {
            $msg = __("telegram_bot.available_users");
            foreach($result as $user) {
                $msg .= "\n<b>".$user["name"]."</b>";
                if($user["driver"]) $msg .= " 🚒";
                if($user["chief"]) {
                    $msg .= " ".__("telegram_bot.chief_abbr");
                }
            }
        } else {
            $msg = __("telegram_bot.no_user_available");
        }
        $message->reply($msg);
    });

    $Bot->onCallbackQuery(function (CallbackQuery $callback_query) use ($Bot) {
        global $translations;
        $user = $callback_query->from;
        $message = $callback_query->message;
        $chat = $message->chat;
        $translations->setLanguage(getLanguageFromTelegramMessage($message));      

        if(strpos($callback_query->data, 'alert_') === 0) {
            $data = explode("_", str_replace("alert_", "", $callback_query->data));
            $alert_id = $data[1];

            setAlertResponse($data[0] === "yes", getUserIdByFrom($user->id), $alert_id);
            return;
        }
    });
    
    $Bot->start();
}
