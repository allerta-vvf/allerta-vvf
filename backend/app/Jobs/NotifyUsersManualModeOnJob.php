<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\TelegramBotLogins;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Facades\Telegraph;

class NotifyUsersManualModeOnJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = TelegramBotLogins::join("users", "users.id", "=", "telegram_bot_logins.user")
          ->select("users.id", "chat_id", "users.available")
          ->where("users.availability_manual_mode", true)
          ->whereNotNull("chat_id")
          ->get();

        foreach ($users as $user) {
            //Get chat by id
            $chat = Telegraph::chat($user["chat_id"]);
            $state = $user["available"] ? "disponibile 🟢" : "non disponibile 🔴";
            $chat
              ->message(
                "⚠️ Attenzione! La tua disponibilità <b>non segue la programmazione oraria</b>.".
                "\nAttualmente sei <b>$state</b>".
                "\nSe vuoi mantenere questa impostazione,\nignora questo messaggio."
              )
              ->keyboard(Keyboard::make()->buttons([
                Button::make("🔧 Ripristina programmazione 🔧")->action('manual_mode_off')->param("user_id", $user["id"]),
                Button::make("🗑 Elimina notifica 🗑")->action('delete_notification')
              ]))
              ->send();
        }
    }

    public function failed(\Error|\TypeError $exception = null)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
    }
}
