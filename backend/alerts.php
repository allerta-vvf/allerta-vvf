<?php
require_once 'utils.php';

function alertsRouter (FastRoute\RouteCollector $r) {
    $r->addRoute(
        'GET',
        '',
        function ($vars) {
            global $db;
            $alerts = $db->select("SELECT * FROM `".DB_PREFIX."_alerts`");
            if(is_null($alerts)) $alerts = [];
            foreach($alerts as &$alert) {
                if(isset($_GET["load_less"])) {
                    $alert = [
                        "id" => $alert["id"],
                        "created_at" => $alert["created_at"]
                    ];
                } else {
                    $alert["crew"] = json_decode($alert["crew"], true);
                }
            }
            apiResponse($alerts);
        }
    );

    $r->addRoute(
        'POST',
        '',
        function ($vars) {
            global $db;
            $crew = [
                [
                    "name" => "Nome1",
                    "response" => "waiting"
                ],
                [
                    "name" => "Nome2",
                    "response" => true
                ],
                [
                    "name" => "Nome3",
                    "response" => false
                ]
            ];
            $db->insert(
                DB_PREFIX."_alerts",
                [
                    "crew" => json_encode($crew),
                    "type" => $_POST["type"],
                    "created_at" => get_timestamp()
                ]
            );
            apiResponse([
                "crew" => $crew,
                "id" => $db->getLastInsertId()
            ]);
        }
    );

    $r->addRoute(
        'GET',
        '/{id:\d+}',
        function ($vars) {
            global $db;
            $alert = $db->selectRow("SELECT * FROM `".DB_PREFIX."_alerts` WHERE `id` = :id", [":id" => $vars["id"]]);
            if(is_null($alert)) {
                apiResponse(["error" => "alert not found"]);
                return;
            }
            $alert["crew"] = json_decode($alert["crew"], true);
            apiResponse($alert);
        }
    );

    $r->addRoute(
        'POST',
        '/{id:\d+}/settings',
        function ($vars) {
            global $db;
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
            global $db;
            $db->delete(
                DB_PREFIX."_alerts",
                [
                    "id" => $vars["id"]
                ]
            );
        }
    );
}