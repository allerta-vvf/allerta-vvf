<?php
use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\Message;

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

function getUserIdByMessage(Message $message)
{
    global $db;
    return $db->selectValue("SELECT user FROM `".DB_PREFIX."_bot_telegram` WHERE `chat_id` = ?", [$message->from->id]);
}

function requireBotLogin(Message $message)
{
    $userId = getUserIdByMessage($message);
    if ($userId === null) {
        $message->reply(
            "Non hai ancora collegato il tuo account Allerta al bot.".
            "\nPer farlo, premere su <strong>\"Collega l'account al bot Telegram\"</strong>."
        );
        exit();
    }
}

function sendTelegramNotification($message)
{
    global $Bot, $db;

    if(is_null($Bot)) initializeBot(NONE);

    //TODO: implement different types of notifications
    //TODO: add command for subscribing to notifications
    $chats = $db->select("SELECT `chat_id` FROM `".DB_PREFIX."_bot_telegram_notifications`");
    if(!is_null($chats)) {
        foreach ($chats as $chat) {
            $chat = $chat['chat_id'];
            $Bot->sendMessage([
                "chat_id" => $chat,
                "text" => $message
            ]);
        }
    }
}

function yesOrNo($value)
{
    return ($value === 1 || $value) ? '<b>SI</b>' : '<b>NO</b>';
}

function telegramBotRouter() {
    global $Bot;

    initializeBot();

    $Bot->addErrorHandler(function ($e) {
        print('Caught '.get_class($e).' exception from general handler'.PHP_EOL);
        print($e.PHP_EOL);
    });
    
    $Bot->onCommand('start', function (Message $message, array $args = []) {
        global $db;
        if(isset($args[0])) {
            $registered_chats = $db->select("SELECT * FROM `".DB_PREFIX."_bot_telegram` WHERE `chat_id` = ?", [$message->from->id]);
            if(!is_null($registered_chats) && count($registered_chats) > 1) {
                $message->chat->sendMessage(
                    "⚠️ Questo account Allerta è già associato ad un'altro utente Telegram.".
                    "\nContattare un amministratore."
                );
                return;
            }
            $response = $db->update(
                DB_PREFIX.'_bot_telegram',
                ['chat_id' => $message->from->id],
                ['tmp_login_token' => $args[0]]
            );
            if($response === 1) {
                $message->chat->sendMessage(
                    "✅ Login avvenuto con successo!".
                    "\nPer ottenere informazioni sul profilo, utilizzare il comando /info".
                    "\nPer ricevere informazioni sui comandi, utilizzare il comando /help o visualizzare il menu dei comandi da Telegram"
                );
            } else {
                $message->chat->sendMessage(
                    "⚠️ Chiave di accesso non valida, impossibile eseguire il login.".
                    "\nRiprovare o contattare un amministratore."
                );
            }
        } else {
            $message->chat->sendMessage(
                "Per iniziare, è necessario collegare l'account di Allerta con Telegram.".
                "\nPer farlo, premere su <strong>\"Collega l'account al bot Telegram\"</strong>."
            );
        }
    });

    $Bot->onCommand('help', function (Message $message, array $args = []) {
        $message->chat->sendMessage(
            "ℹ️ Elenco dei comandi disponibili:".
            "\n/info - Ottieni informazioni sul profilo connesso".
            "\n/help - Ottieni informazioni sui comandi".
            "\n/attiva - Modifica la tua disponibilità in \"reperibile\"".
            "\n/disattiva - Modifica la tua disponibilità in \"non reperibile\"".
            "\n/elenco_disponibili - Mostra un elenco dei vigili attualmente disponibili"
        );
    });
    
    $Bot->onCommand('info', function (Message $message) {
        global $users;
        $user_id = getUserIdByMessage($message);
        if(is_null($user_id)) {
            $message->chat->sendMessage('⚠️ Questo account Telegram non è associato a nessun utente di Allerta.');
        } else {
            $user = $users->get_user($user_id);
            $message->chat->sendMessage(
                "ℹ️ Informazioni sul profilo:".
                "\n<i>Nome:</i> <b>".$user["name"]."</b>".
                "\n<i>Disponibile:</i> ".yesOrNo($user["available"]).
                "\n<i>Caposquadra:</i> ".yesOrNo($user["chief"] === 1).
                "\n<i>Autista:</i> ".yesOrNo($user["driver"] === 1).
                "\n<i>Interventi svolti:</i> <b>".$user["services"]."</b>".
                "\n<i>Esercitazioni svolte:</i> <b>".$user["trainings"]."</b>".
                "\n<i>Minuti di disponibilità:</i> <b>".$user["availability_minutes"]."</b>"
            );
        }
    });

    //Too difficult and "spaghetti to explain it here in comments, please use https://regexr.com/
    //Jokes apart, checks if text contains something like "Attiva", "attiva", "Disponibile", "disponibile" but not "Non ", "non ", "Non_", "non_", "Dis" or "dis"
    $Bot->onText("/\/?(Sono |sono |Io sono |Io sono )?(?<!non( |_))(?<!dis)(?<!Non( |_))(?<!Dis)(Attiva|Attivami|Attivo|Disponibile|Operativo|attiva|attivami|attivo|disponibile|operativo)/", function (Message $message, $matches = []) {
        global $Bot, $availability;
        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 3) return;
        $user_id = getUserIdByMessage($message);
        $availability->change(1, $user_id);
        $Bot->sendMessage($message->from->id, "Disponibilità aggiorata con successo.\nOra sei <b>operativo</b>.");
    });

    $Bot->onText("/\/?(Io |Io sono )?(Disattiva|Disattivo|Disattivami|Non( |_)attivo|Non( |_)(Sono |sono )?disponibile|Non( |_)(Sono |sono )?operativo|disattiva|disattivo|sisattivami|non( |_)(Sono |sono )?attivo|non( |_)(Sono |sono )?disponibile|non( |_)(Sono |sono )?operativo)/", function (Message $message, $matches = []) {
        global $Bot, $availability;
        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 4) return;
        $user_id = getUserIdByMessage($message);
        $availability->change(0, $user_id);
        $Bot->sendMessage($message->from->id, "Disponibilità aggiorata con successo.\nOra sei <b>non operativo</b>.");
    });

    $Bot->onText("/\/?(Elenco|elenco|Elenca|elenca)(_| )(Disponibili|disponibili)/", function (Message $message, $matches = []) {
        global $db, $users;
        requireBotLogin($message);
        if(count(explode(" ", $message->text)) > 2) return;
        $result = $db->select("SELECT `chief`, `driver`, `available`, `name` FROM `".DB_PREFIX."_profiles` WHERE available = 1 and hidden = 0 ORDER BY chief DESC, services ASC, trainings DESC, availability_minutes ASC, name ASC");
        if(!is_null($result) && count($result) > 0) {
            $msg = "ℹ️ Vigili attualmente disponibili:";
            foreach($result as $user) {
                $msg .= "\n<b>".$user["name"]."</b>";
                if($user["driver"]) $msg .= " 🚒";
                if($user["chief"]) {
                    $msg .= " CS";
                }
            }
        } else {
            $msg = "⚠️ Nessun vigile disponibile.";
        }
        $message->reply($msg);
    });
    
    $Bot->start();
}