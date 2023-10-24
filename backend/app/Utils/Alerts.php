<?php

namespace App\Utils;

use App\Models\Alert;
use App\Models\AlertCrew;
use App\Models\User;
use App\Utils\TelegramBot;
use App\Utils\Logger;
use App\Exceptions\AlertClosed;
use App\Exceptions\AlertResponseAlreadySet;
use App\Models\TelegramSpecialMessage;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class Alerts {
    public static function generateAlertTeamMessage($alert) {
        $msgText = $alert->closed ? "Allerta <b>chiusa</b>" : "Nuovo <b>allertamento</b>";
        $msgText .= " (<i>";
        $msgText .= $alert->type == "full" ? "squadra completa" : "supporto";
        $msgText .= "</i>).\nElenco risposte:";
        foreach($alert->crew as $crew) {
            $msgText .= "\n- <b>".$crew->user->name."</b>: ".($crew->accepted ? "✅" : ($crew->accepted == NULL ? "⏳" : "❌"));
        }
        $msgText .= "\n\n<b>Note:</b>\n".$alert->notes;
        $msgText .= "\n\n<b>Aggiunta da:</b> ".$alert->addedBy->name;
        $msgText .= "\n<b>Ultima modifica alle:</b> ".$alert->updated_at->format("H:i:s");
        return $msgText;
    }

    public static function addAlert($type, $ignoreWarning, $fromTelegram = false) {
        //Count users when not hidden and available
        $count = User::where('hidden', false)->where('available', true)->count();

        if($count == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nessun utente disponibile.',
                'ignorable' => false,
            ], 400);
        }

        //Check if there is at least one chief available (not hidden)
        $chiefCount = User::where([
            ['hidden', '=', false],
            ['available', '=', true],
            ['chief', '=', true]
        ])->count();
        if($chiefCount == 0 && !$ignoreWarning) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nessun caposquadra disponibile. Sei sicuro di voler proseguire?',
                'ignorable' => true,
            ], 400);
        }

        //Check if there is at least one driver available (not hidden)
        $driverCount = User::where([
            ['hidden', '=', false],
            ['available', '=', true],
            ['driver', '=', true]
        ])->count();
        if($driverCount == 0 && !$ignoreWarning) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nessun autista disponibile. Sei sicuro di voler proseguire?',
                'ignorable' => true,
            ], 400);
        }

        //Select call list (everyone not hidden and available)
        $users = User::where('hidden', false)->where('available', true)->get();
        if(count($users) < 5 && $type = "full") $type = "support";
        
        //Create alert
        $alert = new Alert;
        $alert->type = $type;
        $alert->addedBy()->associate(auth()->user());
        $alert->updatedBy()->associate(auth()->user());
        $alert->save();

        //Create alert crew
        $alertCrewIds = [];
        foreach($users as $user) {
            $alertCrew = new AlertCrew();
            $alertCrew->user_id = $user->id;
            $alertCrew->save();

            $alertCrewIds[] = $alertCrew->id;
        }
        $alert->crew()->attach($alertCrewIds);
        $alert->save();

        //Send message to Telegram teams
        TelegramBot::sendTeamMessage(
            self::generateAlertTeamMessage($alert),
            "alert",
            $alert->id,
            "alert"
        );

        //Send message to called users
        foreach($alert->crew as $crew) {
            TelegramBot::sendMessageToUser(
                $crew->user->id,
                function ($chat) use ($alert, $crew) {
                    return $chat
                        ->message(
                            "Sei stato chiamato per un <b>allertamento</b> (<i>".
                            ($alert->type == "full" ? "squadra completa" : "supporto").
                            "</i>)\nIndica la tua presenza utilizzando i seguenti tasti:"
                        )
                        ->keyboard(Keyboard::make()->buttons([
                            Button::make("✅ Presente ✅")->action('alert_set_response')->param("user_id", $crew->user->id)->param("alert_id", $alert->id)->param("response", true),
                            Button::make("❌ Assente ❌")->action('alert_set_response')->param("user_id", $crew->user->id)->param("alert_id", $alert->id)->param("response", false)
                        ]));
                },
                "alert_user_call",
                $alert->id,
                "alert"
            );
        }

        Logger::log(
            "Nuova allerta aggiunta",
            auth()->user(),
            null,
            $fromTelegram ? "telegram" : "web"
        );

        return $alert;
    }
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

        //Remove Telegram chat user request
        $msgs = TelegramSpecialMessage::where("type", "alert_user_call")
            ->where("resource_id", $alert->id)
            ->where("resource_type", "alert")
            ->where("user_id", $userId)
            ->get();
        foreach($msgs as $msg) {
            TelegramBot::deleteMessage($msg->chat_id, $msg->message_id);
            $msg->delete();
        }

        TelegramBot::editSpecialMessage(
            $alert->id,
            "alert",
            "alert",
            self::generateAlertTeamMessage($alert)
        );

        TelegramBot::sendMessageToUser(
            $userId,
            "La tua risposta all'allertamento è stata registrata.\n".
            "Sei <b>".($response ? "presente" : "assente")."</b>.\n".
            "Rimani in attesa di nuove istruzioni.",
            "alert_user_response",
            $alert->id,
            "alert"
        );
    }
}
