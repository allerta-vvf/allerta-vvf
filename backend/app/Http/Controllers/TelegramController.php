<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramBotLogins;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
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

        return [
            "start_link" => "https://t.me/$telegramBotUsername?start=$telegramBotStartParameter"
        ];
    }
}
