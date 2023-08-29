<?php

namespace App\Telegram;

use App\Models\TelegramBotLogins;
use App\Models\User;

use App\Utils\Availability;

class WebhookController extends
    \DefStudio\Telegraph\Handlers\WebhookHandler
{
    private $publicCommandsDict = [
        "info" => "Ottieni informazioni sul profilo connesso",
        "help" => "Ottieni informazioni sui comandi",
        "attiva" => "Modifica la tua disponibilità in \"reperibile\"",
        "disattiva" => "Modifica la tua disponibilità in \"non reperibile\"",
        "programma" => "Abilita programmazione oraria",
        "disponibili" => "Mostra un elenco dei vigili attualmente disponibili",
        "stato" => "Mostra lo stato della disponibilità della squadra"
    ];

    private $user = null;

    private function user(): User|null {
        if($this->user) return $this->user;
        $this->user = $this->message->from()->storage()->get('user');
        return $this->user;
    }

    /**
     * Helper and core commands
     */

    public function help(): void
    {
        $text = "ℹ️ Elenco dei comandi disponibili:";
        foreach ($this->publicCommandsDict as $command => $description) {
            $text .= "\n/$command - $description";
        }
        $this->reply($text);
    }

    public function registerCommands()
    {
        $response = $this->bot->registerCommands($this->publicCommandsDict)->send();
        $this->reply(json_encode(($response)));
    }

    public function start(string $loginCode)
    {
        if($this->user()) {
            $username = $this->user()->username;
            $this->chat->html(
                "⚠️ Il tuo account è già collegato con Telegram (username: <i>$username</i>).\n".
                "Per scollegarlo, eseguire il comando <strong><i>/logout</i></strong>"
            )->send();
            return;
        }

        if(!$loginCode || $loginCode == "/start") {
            $this->chat->html(
                "Questo Bot Telegram permette di interfacciarsi con il sistema di gestione delle disponibilità <b>AllertaVVF</b>\n".
                "Per iniziare, è necessario collegare l'account di Allerta con Telegram.\n".
                "Per farlo, accedere alla WebApp e premere su <strong>\"Collega l'account al bot Telegram\"</strong>."
            )->send();
            return;
        }

        $row = TelegramBotLogins::where('tmp_login_code', $loginCode)->first();
        if(!$row) {
            $this->chat->html(
                "⚠️ Il codice di login non è valido.\n".
                "Per favore, riprovare."
            )->send();
            return;
        }

        $row->chat_id = $this->message->chat()->id();
        $row->tmp_login_code = null;
        $row->save();

        $this->reply("✅ Il tuo account è stato collegato con successo.");
        $user = User::find($row->user);
        $this->message->from()->storage()->set("user", $user);
    }

    public function logout()
    {
        $this->message->from()->storage()->forget('user');
        TelegramBotLogins::where('chat_id', $this->message->chat()->id())->delete();
        $this->reply("✅ Il tuo account è stato scollegato con successo.");
    }

    /**
     * Generic commands
     */
    public function info()
    {
        $user = $this->user();
        if(is_null($user)) {
            $this->reply("⚠️ Il tuo account Allerta non è collegato con Telegram.\nPer favore, eseguire il comando <strong><i>/start</i></strong>.");
            return;
        }
        $this->reply(
            "ℹ️ Informazioni sul profilo:".
            "\n<i>Nome:</i> <b>".$user["name"]."</b>".
            "\n<i>Disponibile:</i> ".($user["available"] ? "<b>SI</b>" : "<b>NO</b>").
            "\n<i>Caposquadra:</i> ".($user["chief"] === 1 ? "<b>SI</b>" : "<b>NO</b>").
            "\n<i>Autista:</i> ".($user["driver"] === 1 ? "<b>SI</b>" : "<b>NO</b>").
            "\n<i>Interventi svolti:</i> <b>".$user["services"]."</b>".
            "\n<i>Esercitazioni svolte:</i> <b>".$user["trainings"]."</b>".
            "\n<i>Minuti di disponibilità:</i> <b>".$user["availability_minutes"]."</b>"
        );
    }

    public function attiva() {
        $user = $this->user();
        if(is_null($user)) {
            $this->reply("⚠️ Il tuo account Allerta non è collegato con Telegram.\nPer favore, eseguire il comando <strong><i>/start</i></strong>.");
            return;
        }
        
        Availability::updateAvailability($user, true);
        $this->reply("Disponibilità aggiornata con successo.\nOra sei <b>operativo</b>.");
    }

    public function disattiva() {
        $user = $this->user();
        if(is_null($user)) {
            $this->reply("⚠️ Il tuo account Allerta non è collegato con Telegram.\nPer favore, eseguire il comando <strong><i>/start</i></strong>.");
            return;
        }
        
        Availability::updateAvailability($user, false);
        $this->reply("Disponibilità aggiornata con successo.\nOra sei <b>non operativo</b>.");
    }

    public function programma() {
        $user = $this->user();
        if(is_null($user)) {
            $this->reply("⚠️ Il tuo account Allerta non è collegato con Telegram.\nPer favore, eseguire il comando <strong><i>/start</i></strong>.");
            return;
        }
        
        Availability::updateAvailabilityManualMode($user, false);
        $this->reply("Programmazione oraria <b>abilitata</b>.\nPer disabilitarla (e tornare in modalità manuale), cambiare la disponbilità usando i comandi \"/attiva\" e \"/disattiva\"");
    }

    public function disponibili()
    {
        $user = $this->user();
        if(is_null($user)) {
            $this->reply("⚠️ Il tuo account Allerta non è collegato con Telegram.\nPer favore, eseguire il comando <strong><i>/start</i></strong>.");
            return;
        }
        //Get all users with availability true
        $users = User::where('available', true)->where('hidden', false)->get();
        if(count($users) == 0) {
            $text = "⚠️ Nessun vigile attualmente disponibile.";
        } else {
            $text = "👨‍🚒 Elenco dei vigili attualmente disponibili:";
            foreach ($users as $user) {
                $text .= "\n- <i>".$user->name."</i>";
                if($user->chief) $text .= " CS";
                if($user->driver) $text .= " 🚒";
            }
        }
        $this->reply($text);
    }

    public function stato()
    {
        $user = $this->user();
        if(is_null($user)) {
            $this->reply("⚠️ Il tuo account Allerta non è collegato con Telegram.\nPer favore, eseguire il comando <strong><i>/start</i></strong>.");
            return;
        }
        //Get all users with availability true
        $available_users_count = User::where('available', true)->where('hidden', false)->count();
        if($available_users_count >= 5) {
            $text = "🚒 Distaccamento operativo con squadra completa";
        } else if($available_users_count >= 2) {
            $text = "🧯 Distaccamento operativo per supporto";
        } else {
            $text = "⚠️ Distaccamento non operativo";
        }
        $this->reply($text);
    }

    /**
     * TODOs:
     * - Notification when availability changes (send "stato" response again ONLY IF state changes)
     * - Notification when availability is changed by the system (send "stato" response again)
     * - At 7:00 AM, send a notification to all users with availability in manual mode, asking them to confirm their availability or dismiss this notification
     * - Everything related to alerts, ask the client what to do with that since currently unused in prod
     */
}
