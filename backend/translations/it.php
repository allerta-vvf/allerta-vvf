<?php
return [
    "log_messages" => [
        "new_login" => "Nuovo accesso",
        "availability_schedules_updated" => "Programmazione disponibilità aggiornata",
        "user_added" => "Utente aggiunto",
        "user_updated" => "Utente aggiornato",
        "user_removed" => "Utente rimosso",
        "service_added" => "Servizio aggiunto",
        "service_updated" => "Servizio aggiornato",
        "service_removed" => "Servizio rimosso",
        "training_added" => "Esercitazione aggiunta",
        "training_updated" => "Esercitazione aggiornata",
        "training_removed" => "Esercitazione rimossa",
        "available" => "Disponibilità cambiata in \"reperibile\"",
        "not_available" => "Disponibilità cambiata in \"non reperibile\"",
        "telegram_account_linked" => "Account Telegram collegato"
    ],
    "login" => [
        "wrong_email" => "Email errata",
        "wrong_password" => "Password errata",
        "wrong_username" => "Nome utente errato",
        "wrong_userid" => "ID utente errato",
        "email_not_confirmed" => "Email non confermata"
    ],
    "alerts" => [
        "alert_removed" => "Allerta rimossa.\\nDisponibilità non più richiesta.",
        "alert_completed" => "Numero minimo vigili richiesti raggiunto.\nPartecipazione non più richiesta.",
        "accepted" => "🟢 Partecipazione accettata.",
        "rejected" => "🔴 Partecipazione rifiutata.",
        "no_chief_available" => "Nessun caposquadra disponibile. Contattare i vigili manualmente.",
        "no_driver_available" => "Nessun autista disponibile. Contattare i vigili manualmente.",
        "not_enough_users_available" => "Non ci sono abbastanza utenti disponibili. Contattare i vigili manualmente.",
        "alert_not_found" => "Allerta non trovata",
        "alert_in_progress" => "Allertamento in corso",
        "alert_complete" => "Allertamento completato",
        "alert_cancelled" => "Allertamento annullato",
        "added_by" => "Lanciata da: "
    ],
    "telegram_bot" => [
        "available_support" => "🧯 Distaccamento operativo per supporto",
        "available_full" => "🚒 Distaccamento operativo con squadra completa",
        "not_available" => "⚠️ Distaccamento non operativo",
        "schedule_disabled_warning" => "⚠️ Attenzione! La tua disponibilità <b>non segue la programmazione oraria</b>.\nAttualmente sei <b>%s</b>.\nScrivi \"/programma\" se vuoi ripristinare la programmazione.",
        "available_users" => "ℹ️ Vigili attualmente disponibili:",
        "no_user_available" => "⚠️ Nessun utente disponibile.",
        "chief_abbr" => "CS",
        "account_not_linked" => 
            "⚠️ Non hai ancora collegato il tuo account Allerta al bot.".
            "\nPer farlo, premere su <strong>\"Collega l'account al bot Telegram\"</strong>.",
        "account_already_linked" => 
            "⚠️ Questo account Allerta è già associato ad un'altro utente Telegram.".
            "\nContattare un amministratore.",
        "login_successful" => 
            "✅ Login avvenuto con successo!".
            "\nPer ottenere informazioni sul profilo, utilizzare il comando /info".
            "\nPer ricevere informazioni sui comandi, utilizzare il comando /help o visualizzare il menu dei comandi da Telegram",
        "login_failed" => 
            "⚠️ Chiave di accesso non valida, impossibile eseguire il login.".
            "\nRiprovare o contattare un amministratore.",
        "full_team_requested" => "Richiesta <b>squadra completa 🚒</b>",
        "support_team_requested" => "<b>Supporto 🧯</b>",
        "alert_waiting" => "In attesa ⏳",
        "alert_available" => "Presente 🟢",
        "alert_not_available" => "Non presente 🔴",
        "alert_accept_button" => "✅ Partecipo",
        "alert_decline_button" => "Non partecipo ❌",
        "help_command" => 
            "ℹ️ Elenco dei comandi disponibili:".
            "\n/info - Ottieni informazioni sul profilo connesso".
            "\n/help - Ottieni informazioni sui comandi".
            "\n/attiva - Modifica la tua disponibilità in \"reperibile\"".
            "\n/disattiva - Modifica la tua disponibilità in \"non reperibile\"".
            "\n/programma - Abilita programmazione oraria".
            "\n/disponibili - Mostra un elenco dei vigili attualmente disponibili".
            "\n/stato - Mostra lo stato della disponibilità della squadra",
        "info_command" => 
            "ℹ️ Informazioni sul profilo:".
            "\n<i>Nome:</i> <b>{name}</b>".
            "\n<i>Disponibile:</i> {available}".
            "\n<i>Caposquadra:</i> {chief}".
            "\n<i>Autista:</i> {driver}".
            "\n<i>Interventi svolti:</i> <b>{services}</b>".
            "\n<i>Esercitazioni svolte:</i> <b>{trainings}</b>".
            "\n<i>Minuti di disponibilità:</i> <b>{availability_minutes}</b>",
        "schedule_mode_enabled" => "Programmazione oraria <b>abilitata</b>.\nPer disabilitarla (e tornare in modalità manuale), cambiare la disponbilità usando i comandi \"/attiva\" e \"/disattiva\"",
        "availability_updated" => "Disponibilità aggiornata con successo.\nOra sei <b>%s</b>",
        "debug_telegram_user_id" => "🔎 ID Utente Telegram: <b>%s</b>",
        "debug_chat_id" => "💬 ID Chat: <b>%s</b>",
        "debug_username" => "🔎 Nome utente: <b>%s</b>",
        "debug_first_name" => "🔎 Nome: <b>%s</b>",
        "debug_last_name" => "🔎 Cognome: <b>%s</b>",
        "debug_language_code" => "🌐 Codice lingua: <b>%s</b>",
        "debug_is_bot" => "🤖 Bot: <b>%s</b>",
        "debug_message_json" => "🔎 Messaggio JSON:"
    ],
    "other_user_availability_change_forbidden" => "Non hai il permesso di cambiare la disponibilità di altri utenti",
    "impersonate_user_forbidden" => "Non hai il permesso di impersonare altri utenti",
    "too_many_requests" => "Troppe richieste",
    "unknown_error" => "Errore sconosciuto",
    "access_denied" => "Accesso negato",
    "available" => "disponibile",
    "not_available" => "non disponibile",
    "notes" => "note",
    "team" => "squadra",
    "yes" => "si",
    "no" => "no"
];
