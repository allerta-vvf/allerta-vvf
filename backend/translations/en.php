<?php
return [
    "log_messages" => [ 
        //TODO: save logs as translation string in DB, then translate it when serving them
        //(and return empty string if translation is not found)
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
        "availability_changed_to" => "Availability changed to \"%s\"",
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
    ],
    "telegram_bot" => [
    //TODO: select Telegram bot language from user's language
        "available_support" => "🧯 Available for support",
        "available_full" => "🚒 Available with full team",
        "not_available" => "⚠️ Not available"
    ],
    "other_user_availability_change_forbidden" => "You don't have permission to change other users availability",
    "impersonate_user_forbidden" => "You don't have permission to impersonate other users",
    "too_many_requests" => "Too many requests",
    "unknown_error" => "Unknown error",
    "access_denied" => "Access denied",
    "available" => "available",
    "not_available" => "not available",
];
