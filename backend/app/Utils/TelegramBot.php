<?php

namespace App\Utils;

use App\Models\TelegramBotNotifications;
use App\Models\TelegramBotLogins;
use App\Models\TelegramSpecialMessage;
use DefStudio\Telegraph\Facades\Telegraph;

class TelegramBot {
    static public function sendMessageToUser($userId, callable|string $message, $specialMsgType = null, $resourceId = null, $resourceType = null) {
        $chatRows = TelegramBotLogins::join("users", "users.id", "=", "telegram_bot_logins.user")
          ->select("users.id", "chat_id", "users.available")
          ->where("users.id", $userId)
          ->whereNotNull("chat_id")
          ->get();

        foreach ($chatRows as $chatRow) {
            //Get chat by id
            $chat = Telegraph::chat($chatRow["chat_id"]);

            $msgObj = null;
            if(gettype($message) == "string") {
                $msgObj = $chat->message($message);
            } else {
                $msgObj = $message($chat);
            }

            if(is_null($msgObj)) continue;
            $msgResponse = $msgObj->send();
            $msgId = $msgResponse->telegraphMessageId();

            if(!is_null($specialMsgType) && !is_null($msgId)) {
                TelegramSpecialMessage::create([
                    "message_id" => $msgId,
                    "user_id" => $chatRow["id"],
                    "chat_id" => $chatRow["chat_id"],
                    "chat_type" => "private",
                    "type" => $specialMsgType,
                    "resource_id" => $resourceId,
                    "resource_type" => $resourceType
                ]);
            }
        }
    }

    static public function sendTeamMessage(callable|string|null $message, $specialMsgType = null, $resourceId = null, $resourceType = null) {
        $msgParamType = gettype($message);
        if($msgParamType == "string") {
            if($message == "") return;
            $hash = md5($message);
            $chat_ids = TelegramBotNotifications::where("type_team_state", true)
                ->whereNot("last_message_hash", $hash)
                ->get()->pluck('chat_id')->toArray();
        } else if($msgParamType == "NULL") {
            return;
        } else {
            $chat_ids = TelegramBotNotifications::where("type_team_state", true)
                ->get()->pluck('chat_id')->toArray();
        }        
        
        foreach ($chat_ids as $chat_id) {
            $chat = Telegraph::chat($chat_id);

            $msgObj = null;
            if(gettype($message) == "string") {
                $msgObj = $chat->message($message);
                TelegramBotNotifications::where("chat_id", $chat_id)
                    ->update(["last_message_hash" => $hash]);
            } else {
                $msgObj = $message($chat);
            }

            if(is_null($msgObj)) continue;
            $msgResponse = $msgObj->send();
            $msgId = $msgResponse->telegraphMessageId();

            if(!is_null($specialMsgType) && !is_null($msgId)) {
                TelegramSpecialMessage::create([
                    "message_id" => $msgId,
                    "chat_id" => $chat_id,
                    "chat_type" => "group",
                    "type" => $specialMsgType,
                    "resource_id" => $resourceId,
                    "resource_type" => $resourceType
                ]);
            }
        }
    }

    static public function deleteMessage($chatId, $messageId) {
        $chat = Telegraph::chat($chatId);
        $chat->deleteMessage($messageId)->send();
    }
}
