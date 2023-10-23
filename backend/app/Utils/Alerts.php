<?php

namespace App\Utils;

use App\Models\Alert;
use App\Models\User;
use App\Utils\TelegramBot;
use App\Utils\Logger;
use App\Exceptions\AlertClosed;
use App\Exceptions\AlertResponseAlreadySet;

class Alerts {
    public static function updateAlertResponse($alertId, $response, $userId = null, $fromTelegram = false)
    {
        $alert = Alert::find($alertId);
        if($alert->closed) {
            throw new AlertClosed();
        }

        if(is_null($userId)) {
            $userId = auth()->user()->id;
        }

        foreach($alert->crew as $crew) {
            if($crew->user->id == $userId) {
                if($crew->accepted != null) {
                    throw new AlertResponseAlreadySet();
                } else {
                    $crew->accepted = $response;
                    $crew->save();
                }
            }
        }

        $user = User::find($userId);

        //Add to logs
        Logger::log(
            "Risposta ad allertamento: ".($response ? "presente" : "non presente"),
            $user,
            $fromTelegram ? $user : null,
            $fromTelegram ? "telegram" : "web"
        );

        TelegramBot::sendMessageToUser(
            $userId,
            "La tua risposta all'allertamento è stata registrata.\n".
            "Sei <b>".($response ? "presente" : "assente")."</b>.\n".
            "Rimani in attesa di nuove istruzioni."
        );
    }
}
