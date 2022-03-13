<?php
require_once 'utils.php';

final class NoChiefAvailableException extends Exception {}
final class NoDriverAvailableException extends Exception {}
final class NotEnoughAvailableUsersException extends Exception {}

function callsList($type) {
    global $db;
    $crew = [];
    if ($type == 'full') {
    } else if ($type == 'support') {
        if($db->selectValue("SELECT COUNT(id) FROM `".DB_PREFIX."_profiles` WHERE `available` = 1") < 2) {
            throw new NotEnoughAvailableUsersException();
            return;
        }

        $chief_result = $db->selectRow("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `hidden` = 0 AND `available` = 1 AND `chief` = 1 ORDER BY services ASC, trainings DESC, availability_minutes ASC, name ASC LIMIT 1");
        if(is_null($chief_result)) {
            throw new NoChiefAvailableException();
            return;
        }
        $crew[] = $chief_result;
        if($chief_result["driver"]) {
            $result = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `hidden` = 0 AND `available` = 1 ORDER BY chief ASC, services ASC, trainings DESC, availability_minutes ASC, name ASC");
            $crew = array_merge($crew, $result);
        } else {
            $driver_result = $db->selectRow("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `hidden` = 0 AND `available` = 1 AND `driver` = 1 ORDER BY chief ASC, services ASC, trainings DESC, availability_minutes ASC, name ASC");
            if(is_null($driver_result)) {
                throw new NoDriverAvailableException();
                return;
            }
            $crew = array_merge($crew, $driver_result);
        }
    }
    return $crew;
}

function loadCrewMemberData($input) {
    global $db;
    $result = $db->selectRow("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `id` = ?", [$input["id"]]);
    if(is_null($result)) {
        throw new Exception("Crew member not found");
        return;
    }
    return array_merge($input, $result);
}

function setAlertResponse($response, $userId, $alertId) {
    global $db, $users, $Bot;
    $alert = $db->selectRow(
        "SELECT * FROM `".DB_PREFIX."_alerts` WHERE `id` = ?", [$alertId]
    );
    $crew = json_decode($alert["crew"], true);
    $messageText = $response ? "🟢 Partecipazione accettata." : "🔴 Partecipazione rifiutata.";

    foreach($crew as &$member) {
        if($member["id"] == $userId) {
            $message_id = $member["telegram_message_id"];
            $chat_id = $member["telegram_chat_id"];

            $Bot->sendMessage([
                "chat_id" => $chat_id,
                "text" => $messageText,
                "reply_to_message_id" => $message_id
            ]);

            $Bot->editMessageReplyMarkup([
                "chat_id" => $chat_id,
                "message_id" => $message_id,
                "reply_markup" => [
                    'inline_keyboard' => [
                    ]
                ]
            ]);

            $member["response"] = $response;
            $member["response_time"] = get_timestamp();
            break;
        }
    }
    $db->update(
        DB_PREFIX."_alerts",
        [
            "crew" => json_encode($crew)
        ],
        [
            "id" => $alertId
        ]
    );

    $notification_messages = json_decode($alert["notification_messages"], true);
    $notification_text = generateAlertReportMessage($alert["type"], $crew);
    foreach($notification_messages as $chat_id => $message_id) {
        $Bot->editMessageText([
            "chat_id" => $chat_id,
            "message_id" => $message_id,
            "text" => $notification_text
        ]);
    }

    $available_users_count = 0;
    $drivers_count = 0;
    $chiefs_count = 0;
    foreach($crew as $member) {
        $user = $users->getUserById($member["id"]);
        if($member["response"] === true) $available_users_count++;
        if($user["driver"]) $drivers_count++;
        if($user["chief"]) $chiefs_count++;
    }

    if($alert["type"] === "support" && $available_users_count >= 2 && $chiefs_count >= 1 && $drivers_count >= 1) {
        $db->update(
            DB_PREFIX."_alerts",
            [
                "enabled" => 0
            ],
            [
                "id" => $alert["id"]
            ]
        );
    }
}

function alertsRouter (FastRoute\RouteCollector $r) {
    $r->addRoute(
        'GET',
        '',
        function ($vars) {
            global $db, $users;
            requireLogin();
            $alerts = $db->select("SELECT * FROM `".DB_PREFIX."_alerts` WHERE `enabled` = 1");
            if(is_null($alerts)) $alerts = [];
            foreach($alerts as &$alert) {
                if(isset($_GET["load_less"])) {
                    $alert = [
                        "id" => $alert["id"],
                        "created_at" => $alert["created_at"]
                    ];
                } else {
                    $alert["crew"] = json_decode($alert["crew"], true);
                    $alert["crew"] = array_map(function($crew_member) {
                        return loadCrewMemberData($crew_member);
                    }, $alert["crew"]);
                }
            }
            apiResponse($alerts);
        }
    );

    $r->addRoute(
        'POST',
        '',
        function ($vars) {
            global $db, $users;
            requireLogin();
            $users->online_time_update();
            if(!$users->hasRole(Role::SUPER_EDITOR)) {
                apiResponse(["status" => "error", "message" => "Access denied"]);
                return;
            }

            try {
                $crew_members = callsList($_POST["type"]);
            } catch (NoChiefAvailableException) {
                apiResponse(["status" => "error", "message" => "Nessun caposquadra disponibile. Contattare i vigili manualmente."]);
                return;
            } catch (NoDriverAvailableException) {
                apiResponse(["status" => "error", "message" => "Nessun autista disponibile. Contattare i vigili manualmente."]);
                return;
            } catch (NotEnoughAvailableUsersException) {
                apiResponse(["status" => "error", "message" => "Nessun utente disponibile. Distaccamento non operativo."]);
                return;
            }
            
            $crew = [];
            foreach($crew_members as $member) {
                $crew[] = [
                    "id" => $member["id"],
                    "response" => "waiting"
                ];
            }

            $notifications = sendAlertReportMessage($_POST["type"], $crew);

            $db->insert(
                DB_PREFIX."_alerts",
                [
                    "crew" => json_encode($crew),
                    "type" => $_POST["type"],
                    "created_at" => get_timestamp(),
                    "created_by" => $users->auth->getUserId(),
                    "notification_messages" => json_encode($notifications)
                ]
            );
            $alertId = $db->getLastInsertId();

            foreach($crew as &$member) {
                list($member["telegram_message_id"], $member["telegram_chat_id"]) = sendAlertRequestMessage($_POST["type"], $member["id"], $alertId);
            }

            $db->update(
                DB_PREFIX."_alerts",
                [
                    "crew" => json_encode($crew)
                ],
                [
                    "id" => $alertId
                ]
            );

            apiResponse([
                "crew" => $crew,
                "id" => $alertId
            ]);
        }
    );

    $r->addRoute(
        'GET',
        '/{id:\d+}',
        function ($vars) {
            global $db;
            requireLogin();
            $alert = $db->selectRow("SELECT * FROM `".DB_PREFIX."_alerts` WHERE `id` = :id", [":id" => $vars["id"]]);
            if(is_null($alert)) {
                apiResponse(["error" => "alert not found"]);
                return;
            }
            $alert["crew"] = json_decode($alert["crew"], true);
            $alert["crew"] = array_map(function($crew_member) {
                return loadCrewMemberData($crew_member);
            }, $alert["crew"]);
            apiResponse($alert);
        }
    );

    $r->addRoute(
        'POST',
        '/{id:\d+}/settings',
        function ($vars) {
            global $db, $users;
            requireLogin();
            $users->online_time_update();
            if(!$users->hasRole(Role::SUPER_EDITOR)) {
                apiResponse(["status" => "error", "message" => "Access denied"]);
                return;
            }
            $db->update(
                DB_PREFIX."_alerts",
                [
                    "notes" => $_POST["notes"]
                ],
                [
                    "id" => $vars["id"]
                ]
            );
        }
    );

    $r->addRoute(
        'DELETE',
        '/{id:\d+}',
        function ($vars) {
            global $db, $users;
            requireLogin();
            $users->online_time_update();
            if(!$users->hasRole(Role::SUPER_EDITOR)) {
                apiResponse(["status" => "error", "message" => "Access denied"]);
                return;
            }
            $db->update(
                DB_PREFIX."_alerts",
                [
                    "enabled" => 0
                ],
                [
                    "id" => $vars["id"]
                ]
            );
        }
    );
}