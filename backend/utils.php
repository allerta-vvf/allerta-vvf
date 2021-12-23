<?php
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;

require_once("vendor/autoload.php");
require("config.php");

$db = \Delight\Db\PdoDatabase::fromDsn(
        new \Delight\Db\PdoDsn(
            "mysql:host=".DB_HOST.";dbname=".DB_NAME,
            DB_USER,
            DB_PASSWORD
        )
    );

$JWTconfig = Configuration::forAsymmetricSigner(
    new Signer\Rsa\Sha256(),
    InMemory::base64Encoded('LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2Z0lCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktnd2dnU2tBZ0VBQW9JQkFRRFR2d0U4N010Z1JFWUwKVEw0YUhoUW8zWnpvZ214eHZNVXNLblB6eXhSczFZclhPU09wd04wbnBzWGFyQktLVklVTU5MZkZPRHAvdm5RbgoyWnAwNk44WEc1OVdBT0t3dkM0TWZ4TERRa0ErSlhnZ3pIbGtiVm9UTitkVWtkWUlGcVNLdUFQR3dpV1RvUksyClN4RWhpajNyRTJGT044alFadkR4WmtpUDlhNHZ4Sk8zT1RQUXdLcmVkWEZpT2JzWEQvYzNSdExGaEtjdGpDeUgKT0lyUDBiUUVzZWUvbTdKTnRHNHJ5NkJQdXNONndiK3ZKbzVpZUJZUGEzYzE5YWtOcTZxL25ZV2hwbGhra0pTdQphT3JMNXhYRUZ6STVUdmN2blhSNTY4R1ZjeEs4WUxmRmtkeHBzWEd0NXJBYmVoMGgvVTVrSUxFQXF2OFA5UEdUClpwaWNLYnJuQWdNQkFBRUNnZ0VBZDN5VFFFUUhSOTEvQVNWZktQSE1RbnM3N2VDYlBWdGVrRnVzYnVnc01IWVkKRVBkSGJxVk1wdkZ2T01SYytmNVR6ZDE1emlxNnFCZGJDSm04bFRoTG00aVUwejFRcnBhaURaOHZnVXZEWU01WQpDWG9aRGxpK3VaV1VUcDYwL245NGZtYjBpcFpJQ2hTY3NJMlByek9KV1R2b2J2RC91c284TUp5ZFdjOHphZlFtCnVxWXp5Z09makZadlU0bFNmZ3pwZWZocHF1eTBKVXk1VGlLUm1HVW53TGIzVHRjc1ZhdmpzbjRRbU53TFlnT0YKMk9FK1IxMmV4M3BBS1RpUkU2RmNuRTF4RklvMUdLaEJhMk90Z3czTURPNkdnK2tuOFE0YWxLejZDNlJSbGdhSApSN3NZekVmSmhzay9HR0ZUWU96WEtRejJsU2FTdEt0OXdLQ29yMDRSY1FLQmdRRHpQT3U1akNUZmF5VW83eFkyCmpIdGlvZ0h5S0xMT2J0OWwzcWJ3Z1huYUQ2cm54WU52Q3JBME9NdlQraVpYc0ZaS0prWXpKcjhaT3hPcFBST2sKMTBXZE9hZWZpd1V5TDVkeXB1ZVN3bElEd1ZtK2hJNEJzODJNYWpIdHpPb3poKzczd0ErYXc1clBzODRVaXg5dwpWYmJ3YVZSNnFQL0JWMDl5SllTNWtRN2Ztd0tCZ1FEZTJ4anl3WDJkMk1DK3F6UnIrTGZVKzErZ3EwampoQkNYCldIcVJONklFQ0IweFRuWFVmOVdML1ZDb0kxLzU1QmhkYmJFamErNGJ0WWdjWFNQbWxYQklSS1E0VnRGZlZtWUIKa1BYZUQ4b1o3THl1TmRDc2JLTmUreDFJSFhEZTZXZnMzTDl1bENmWHhlSUU4NHd5M2ZkNjZtUWFoeVhWOWlEOQpDa3VpZk1xVXBRS0JnUUNpeWRIbFkxTEdKL285dEEyRXdtNU5hNm1ydk9zMlYyT3gxTnFiT2J3b1liWDYyZWlGCjUzeFg1dThiVmw1VTc1SkFtKzc5aXQvNGJkNVJ0S3V4OWRVRVRiTE9od2NhT0ZtK2hNK1ZHL0l4eXpSWjJuTUQKMXFjcFkyVTVCcHh6a25VdllGM1JNVG9wNmVkeFBrN3pLcHA5dWJDdFN1K29JTnZ0eEFoWS9Ta2NJd0tCZ0dQMQp1cGNJbXlPMkdaNXNoTEw1ZU51YmRTVklMd1YrTTBMdmVPcXlIWVhaYmQ2ejVyNU9LS2NHRkt1V1VuSndFVTIyCjZnR05ZOXdoN005c0o3SkJ6WDljNnB3cXRQY2lkZGEyQXRKOEdwYk9UVU9HOS9hZk5CaGlZcHY2T0txRDN3MnIKWm1KZktnL3F2cHFoODN6TmV6Z3k4bnZEcXdEeHlaSTJqLzV1SXgvUkFvR0JBTVdSbXh0djZIMmNLaGliSS9hSQpNVEpNNFFSanlQTnhRcXZBUXN2K29IVWJpZDA2VkszSkUrOWlReWl0aGpjZk5Pd25DYW9PN0k3cUFqOVFFZkpTCk1aUWMvVy80REhKZWJvMmtkMTF5b1hQVlRYWE91RXdMU0tDZWpCWEFCQlkwTVBOdVBVbWlYZVUwTzNUeWkzN0oKVFVLenJnY2Q3TnZsQTQxWTR4S2NPcUVBCi0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0='),
    InMemory::base64Encoded('LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUEwNzhCUE96TFlFUkdDMHkrR2g0VQpLTjJjNklKc2NiekZMQ3B6ODhzVWJOV0sxemtqcWNEZEo2YkYycXdTaWxTRkREUzN4VGc2Zjc1MEo5bWFkT2pmCkZ4dWZWZ0Rpc0x3dURIOFN3MEpBUGlWNElNeDVaRzFhRXpmblZKSFdDQmFraXJnRHhzSWxrNkVTdGtzUklZbzkKNnhOaFRqZkkwR2J3OFdaSWovV3VMOFNUdHprejBNQ3EzblZ4WWptN0Z3LzNOMGJTeFlTbkxZd3NoemlLejlHMApCTEhudjV1eVRiUnVLOHVnVDdyRGVzRy9yeWFPWW5nV0QydDNOZldwRGF1cXY1MkZvYVpZWkpDVXJtanF5K2NWCnhCY3lPVTczTDUxMGVldkJsWE1TdkdDM3haSGNhYkZ4cmVhd0czb2RJZjFPWkNDeEFLci9EL1R4azJhWW5DbTYKNXdJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0t')
);

$auth = new \Delight\Auth\Auth($db, $JWTconfig, null, DB_PREFIX."_");

final class Role
{
    //https://github.com/delight-im/PHP-Auth/blob/master/src/Role.php
    const GUEST = \Delight\Auth\Role::AUTHOR;
    const BASIC_VIEWER = \Delight\Auth\Role::COLLABORATOR;
    const FULL_VIEWER = \Delight\Auth\Role::CONSULTANT;
    const EDITOR = \Delight\Auth\Role::CONSUMER;
    const SUPER_EDITOR = \Delight\Auth\Role::CONTRIBUTOR;
    const DEVELOPER = \Delight\Auth\Role::DEVELOPER;
    const TESTER = \Delight\Auth\Role::CREATOR;
    const EXTERNAL_VIEWER = \Delight\Auth\Role::REVIEWER;
    const ADMIN = \Delight\Auth\Role::ADMIN;
    const SUPER_ADMIN = \Delight\Auth\Role::SUPER_ADMIN;

    public function __construct()
    {
    }

}

class Users
{
    private $db = null;
    public $auth = null;
    private $profile_names = [];
    private $user_names = [];
    public $holidays = null;
    
    public function __construct($db, $auth)
    {
        $this->db = $db;
        $this->auth = $auth;
        $this->profile_names = $this->db->select("SELECT `id`, `name` FROM `".DB_PREFIX."_profiles`");
        $this->user_names = $this->db->select("SELECT `id`, `username` FROM `".DB_PREFIX."_users`");
        //$this->holidays = Yasumi\Yasumi::create(get_option("holidays_provider") ?: "USA", date("Y"), get_option("holidays_language") ?: "en_US");
    }

    public function add_user($email, $name, $username, $password, $phone_number, $birthday, $chief, $driver, $hidden, $disabled, $inserted_by)
    {
        //TODO: save birthday in db
        //$this->tools->profiler_start("Add user");
        $userId = $this->auth->admin()->createUserWithUniqueUsername($email, $password, $username);
        if($userId) {
            $hidden = $hidden ? 1 : 0;
            $disabled = $disabled ? 1 : 0;
            $chief = $chief ? 1 : 0;
            $driver = $driver ? 1 : 0;
            $this->db->insert(
                DB_PREFIX."_profiles",
                ["hidden" => $hidden, "disabled" => $disabled, "name" => $name, "phone_number" => $phone_number, "chief" => $chief, "driver" => $driver]
            );
            if($chief == 1) {
                $this->auth->admin()->addRoleForUserById($userId, Role::FULL_VIEWER);
            }
            //$this->log("User added", $userId, $inserted_by);
            //$this->tools->profiler_stop();
            return $userId;
        } else {
            //$this->tools->profiler_stop();
            return false;
        }
    }

    public function get_users()
    {
        return $this->db->select("SELECT * FROM `".DB_PREFIX."_profiles`");
    }
    
    public function get_user($id)
    {
        return $this->db->selectRow("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `id` = ?", [$id]);
    }
    
    public function remove_user($id, $removed_by)
    {
        //$this->tools->profiler_start("Remove user");
        $this->db->delete(
            DB_PREFIX."_users",
            ["id" => $id]
        );
        $this->db->delete(
            DB_PREFIX."_profiles",
            ["id" => $id]
        );
        //$this->log("User removed", null, $removed_by);
        //$this->tools->profiler_stop();
    }
    
    public function online_time_update($id=null){
        //$this->tools->profiler_start("Update online timestamp");
        if(is_null($id)) $id = $this->auth->getUserId();
        $time = time();
        $this->db->update(
            DB_PREFIX."_profiles",
            ["online_time" => $time],
            ["id" => $id]
        );
        //bdump(["id" => $id, "time" => $time]);
        //$this->tools->profiler_stop();
    }

    public function loginAndReturnToken($username, $password)
    {
        //$this->tools->profiler_start("Login");
        $this->auth->loginWithUsername($username, $password);
        $token = $this->auth->generateJWTtoken();
        //$this->tools->profiler_stop();
        return $token;
    }
}

class Services {
    private $db = null;
    
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function list() {
        $response = $this->db->select("SELECT * FROM `".DB_PREFIX."_services` ORDER BY date DESC, beginning DESC");
        return !is_null($response) ? $response : [];
    }

    public function increment_counter($increment)
    {
        $this->db->exec(
            "UPDATE `".DB_PREFIX."_profiles` SET `services`= services + 1 WHERE id IN ($increment)"
        );
    }

    public function decrement_counter($decrement)
    {
        $this->db->exec(
            "UPDATE `".DB_PREFIX."_profiles` SET `services`= services - 1 WHERE id IN ($decrement)"
        );
    }

    public function get_selected_users($id)
    {
        return $this->db->selectValue(
            "SELECT `increment` FROM `".DB_PREFIX."_services` WHERE `id` = :id LIMIT 0, 1",
            ["id" => $id]
        );
    }

    public function add($date, $code, $beginning, $end, $chief, $drivers, $crew, $place, $notes, $type, $increment, $inserted_by)
    {
        $drivers = implode(",", $drivers);
        $crew = implode(",", $crew);
        $increment = implode(",", $increment);
        $date = date('Y-m-d H:i:s', strtotime($date));
        $this->db->insert(
            DB_PREFIX."_services",
            ["date" => $date, "code" => $code, "beginning" => $beginning, "end" => $end, "chief" => $chief, "drivers" => $drivers, "crew" => $crew, "place" => $place, "place_reverse" => $this->tools->savePlaceReverse($place), "notes" => $notes, "type" => $type, "increment" => $increment, "inserted_by" => $inserted_by]
        );
        $this->increment_counter($increment);
        //$this->user->log("Service added");
    }
}

$users = new Users($db, $auth);
$services = new Services($db);