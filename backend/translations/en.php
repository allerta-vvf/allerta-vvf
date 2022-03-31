<?php
return [
    "log_messages" => [
        "new_login" => "New login",
        "availability_schedules_updated" => "Availability schedules updated",
        "user_added" => "User added",
        "user_updated" => "User updated",
        "user_removed" => "User removed",
        "service_added" => "Service added",
        "service_updated" => "Service updated",
        "service_removed" => "Service removed",
        "training_added" => "Training added",
        "training_updated" => "Training updated",
        "training_removed" => "Training removed",
        "available" => "Availability changed to \"available\"",
        "not_available" => "Availability changed to \"not available\"",
        "telegram_account_linked" => "Telegram account linked"
    ],
    "login" => [
        "wrong_email" => "Wrong email",
        "wrong_password" => "Wrong password",
        "wrong_username" => "Wrong username",
        "wrong_userid" => "Wrong userid",
        "email_not_confirmed" => "Email not confirmed"
    ],
    "alerts" => [
        "alert_removed" => "Alerta removed.\\nAvailability not requested anymore.",
        "alert_completed" => "Minimum number of members required reached.\\nParticipation not requested anymore.",
        "accepted" => "🟢 Accepted.",
        "rejected" => "🔴 Reject.",
        "no_chief_available" => "No chief available. Contact the members manually.",
        "no_driver_available" => "No driver available. Contact the members manually.",
        "not_enough_users_available" => "Not enough users available. Contact the members manually.",
        "alert_not_found" => "Alert not found",
        "alert_in_progress" => "Alert in progress",
        "alert_complete" => "Alert complete",
        "alert_cancelled" => "Alert cancelled",
        "added_by" => "Added by: "
    ],
    "telegram_bot" => [
        "available_support" => "🧯 Available for support",
        "available_full" => "🚒 Available with full team",
        "not_available" => "⚠️ Not available",
        "schedule_disabled_warning" => "⚠️ Warning! Your availability <b>doesn't follow the schedule</b>.\nCurrently you are <b>%s</b>.\nType \"/programma\" to restore the schedule.",
        "available_users" => "ℹ️ Available users:",
        "no_user_available" => "⚠️ No user available.",
        "chief_abbr" => "C",
        "account_not_linked" => 
            "⚠️ You have not yet linked your Alert account to the bot.".
            "\nTo do this, click on <strong> \"Link account to Telegram bot\"</strong>.",
        "account_already_linked" => 
            "⚠️ This Alert account is already associated with another Telegram user.".
            "\nContact an administrator.",
        "login_successful" => 
            "✅ Login successful!".
            "\nTo get profile information, use the /info command".
            "\nTo see commands list, use the command /help or view the command menu from Telegram",
        "login_failed" => 
            "⚠️ Invalid access key, unable to login.".
            "\nPlease try again or contact an administrator.",
        "full_team_requested" => "<b>Complete team 🚒</b> requested",
        "support_team_requested" => "<b>Support 🧯</b>",
        "alert_waiting" => "Waiting ⏳",
        "alert_available" => "Available 🟢",
        "alert_not_available" => "Not available 🔴",
        "alert_accept_button" => "✅ Partecipate",
        "alert_decline_button" => "Don't Partecipate ❌",
        "help_command" => 
            "ℹ️ Commands list:".
            "\n/info - Returns profile info".
            "\n/help - Returns commands list".
            "\n/attiva - Update your availability into \"available\"".
            "\n/disattiva - Update your availability into \"not available\"".
            "\n/programma - Enable schedule mode".
            "\n/disponibili - Returns available users list".
            "\n/stato - Returns team availability status",
        "info_command" => 
            "ℹ️ Profile info:".
            "\n<i>Name:</i> <b>{name}</b>".
            "\n<i>Available:</i> {available}".
            "\n<i>Chief:</i> {chief}".
            "\n<i>Driver:</i> {driver}".
            "\n<i>Services:</i> <b>{services}</b>".
            "\n<i>Trainings:</i> <b>{trainings}</b>".
            "\n<i>Availability minutes:</i> <b>{availability_minutes}</b>",
        "schedule_mode_enabled" => "Schedule mode <b>enabled</b>.\nTo disable it (and re-enable manual mode), change your availability using the commands \"/attiva\" e \"/disattiva\"",
        "availability_updated" => "Availability updated successfully.\nNow you are <b>%s</b>",
        "debug_telegram_user_id" => "🔎 Telegram User ID: <b>%s</b>",
        "debug_chat_id" => "💬 Chat ID: <b>%s</b>",
        "debug_username" => "🔎 Username: <b>%s</b>",
        "debug_first_name" => "🔎 First Name: <b>%s</b>",
        "debug_last_name" => "🔎 Last Name: <b>%s</b>",
        "debug_language_code" => "🌐 Linguage Code: <b>%s</b>",
        "debug_is_bot" => "🤖 Bot: <b>%s</b>",
        "debug_message_json" => "🔎 JSON Message: <b>%s</b>",
    ],
    "other_user_availability_change_forbidden" => "You don't have permission to change other users availability",
    "impersonate_user_forbidden" => "You don't have permission to impersonate other users",
    "too_many_requests" => "Too many requests",
    "unknown_error" => "Unknown error",
    "access_denied" => "Access denied",
    "available" => "available",
    "not_available" => "not available",
    "notes" => "notes",
    "team" => "team",
    "yes" => "yes",
    "no" => "no"
];
