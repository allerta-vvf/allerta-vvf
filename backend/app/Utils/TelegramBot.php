<?php

namespace App\Utils;

use App\Models\TelegramBotNotifications;
use App\Models\TelegramBotLogins;
use DefStudio\Telegraph\Facades\Telegraph;

class TelegramBot {
    static public function sendMessageToUser($userId, callable|string $message) {
        $chatRows = TelegramBotLogins::join("users", "users.id", "=", "telegram_bot_logins.user")
          ->select("users.id", "chat_id", "users.available")
          ->where("users.id", $userId)
          ->whereNotNull("chat_id")
          ->get();

        foreach ($chatRows as $chatRow) {
            //Get chat by id
            $chat = Telegraph::chat($chatRow["chat_id"]);

            if(gettype($message) == "string") {
                $chat->message($message)->send();
            } else {
                $message($chat);
            }
        }
    }

    static public function sendTeamMessage(callable|string|null $message) {
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

            if(gettype($message) == "string") {
                $chat->message($message)->send();
                TelegramBotNotifications::where("chat_id", $chat_id)
                    ->update(["last_message_hash" => $hash]);
            } else {
                $message($chat);
            }
        }
    }
}
