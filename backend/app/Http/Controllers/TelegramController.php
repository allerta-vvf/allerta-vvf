<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramBotLogins;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Str;
use App\Utils\Logger;

class TelegramController extends Controller
{
    /**
     * Returns a link that the user can use to start the login process
     */
    public function loginToken(Request $request)
    {
        //Get telegramBotUsername from the name of the first bot (first row)
        $telegramBotUsername = TelegraphBot::first()->name;
        $telegramBotStartParameter = (string) Str::uuid();

        $row = new TelegramBotLogins();
        $row->chat_id = null;
        $row->tmp_login_code = $telegramBotStartParameter;
        $row->user = $request->user()->id;
        $row->save();

        Logger::log("Inizio procedura collegamento bot Telegram");

        return [
            "start_link" => "https://t.me/$telegramBotUsername?start=$telegramBotStartParameter"
        ];
    }
}
