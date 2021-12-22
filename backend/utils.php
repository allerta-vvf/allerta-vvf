<?php
require_once("vendor/autoload.php");
require("config.php");

$db = \Delight\Db\PdoDatabase::fromDsn(
        new \Delight\Db\PdoDsn(
            "mysql:host=".DB_HOST.";dbname=".DB_NAME,
            DB_USER,
            DB_PASSWORD
        )
    );

$auth = new \Delight\Auth\Auth($db, null, DB_PREFIX."_");

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
    private $auth = null;
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
}

$users = new Users($db, $auth);