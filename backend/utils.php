<?php
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

require_once("vendor/autoload.php");
require("config.php");

$db = \Delight\Db\PdoDatabase::fromDsn(
        new \Delight\Db\PdoDsn(
            "mysql:host=".DB_HOST.";dbname=".DB_NAME,
            DB_USER,
            DB_PASSWORD
        )
    );

try {
    CacheManager::setDefaultConfig(new ConfigurationOption([
        'path' => realpath(dirname(__FILE__).'/tmp')
    ]));
    $cache = CacheManager::getInstance('files');
} catch(Exception $e) {
    $cache = null;
}
$options = new Options($db, $cache);
function get_option($name, $default=null) {
    global $options;
    try {
        return $options->get($name);
    } catch(Exception $e) {
        return $default;
    }
}

function get_ip()
{
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if(get_option("check_cf_ip", false)) {
        if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
    }
    return $ip;
}

$JWTconfig = Configuration::forAsymmetricSigner(
    new Signer\Rsa\Sha256(),
    InMemory::base64Encoded(JWT_PRIVATE_KEY),
    InMemory::base64Encoded(JWT_PUBLIC_KEY)
);

$auth = new \Delight\Auth\Auth($db, $JWTconfig, get_ip(), DB_PREFIX."_");

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

function logger($action, $changed=null, $editor=null, $timestamp=null, $source_type="api")
{
    global $db, $users;
    //timestamp added by default in DB
    if(is_null($changed)){
        $changed = $users->auth->getUserId();
    }
    if(is_null($editor)){
        $editor = $changed;
    }
    if(!$users->isHidden($editor)){
        if(get_option("log_save_ip", true)){
            $ip = get_ip();
        } else {
            $ip = null;
        }
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_strimwidth($_SERVER['HTTP_USER_AGENT'], 0, 200, "...") : null;
        $db->insert(
            DB_PREFIX."_log",
            ["action" => $action, "changed" => $changed, "editor" => $editor, "timestamp" => $timestamp, "ip" => $ip, "source_type" => $source_type, "user_agent" => $user_agent]
        );
    }
}

class options
{
    protected $db;
    protected $cache;
    public $options = [];
    public $optionsCache;

    public function __construct($db, $cache, $bypassCache=false){
        $this->db = $db;
        $this->cache = $cache;
        if(!$bypassCache){
            try {
                $this->optionsCache = $this->cache->getItem("options");
                if (is_null($this->optionsCache->get())) {
                    $this->optionsCache->set($db->select("SELECT * FROM `".DB_PREFIX."_options` WHERE `enabled` = 1"))->expiresAfter(60);
                    $this->cache->save($this->optionsCache);
                }
                $this->options = $this->optionsCache->get();
            } catch(Exception $e) {
                $this->options = $db->select("SELECT * FROM `".DB_PREFIX."_options` WHERE `enabled` = 1");
            }
        } else {
            $this->options = $db->select("SELECT * FROM `".DB_PREFIX."_options` WHERE `enabled` = 1");
        }

        if(is_null($this->options)){
            $this->options = [];
        }
    }

    public function get($name)
    {
        if(defined($name)) {
            return constant($name);
        } else {
            foreach($this->options as $option){
                if($name == $option["name"]) {
                    return empty($option["value"]) ? false : $option["value"];
                }
            }
            throw new \Exception("Option not found: ".$name);
        }
    }
}

class Users
{
    private $db = null;
    public $auth = null;
    public $holidays = null;
    
    public function __construct($db, $auth)
    {
        $this->db = $db;
        $this->auth = $auth;
        //$this->holidays = Yasumi\Yasumi::create(get_option("holidays_provider") ?: "USA", date("Y"), get_option("holidays_language") ?: "en_US");
    }

    public function add_user($email, $name, $username, $password, $phone_number, $birthday, $chief, $driver, $hidden, $disabled, $inserted_by)
    {
        //TODO: save birthday in db
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
            logger("User added", $userId, $inserted_by);
            return $userId;
        } else {
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
        $this->db->delete(
            DB_PREFIX."_users",
            ["id" => $id]
        );
        $this->db->delete(
            DB_PREFIX."_profiles",
            ["id" => $id]
        );
        logger("User removed", null, $removed_by);
    }
    
    public function online_time_update($id=null){
        if(is_null($id)) $id = $this->auth->getUserId();
        $time = time();
        $this->db->update(
            DB_PREFIX."_profiles",
            ["online_time" => $time],
            ["id" => $id]
        );
    }

    public function loginAndReturnToken($username, $password)
    {
        $this->auth->loginWithUsername($username, $password);
        $token = $this->auth->generateJWTtoken([
            "full_viewer" => $this->hasRole(Role::FULL_VIEWER),
            "name" => $this->getName(),
        ]);
        return $token;
    }

    public function isHidden($id=null)
    {
        if(is_null($id)) $id = $this->auth->getUserId();
        if(is_null($id)) return true;
        $user = $this->db->selectRow("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `id` = ?", [$id]);
        return $user["hidden"];
    }

    public function getName($id=null)
    {
        if(is_null($id)) $id = $this->auth->getUserId();
        $user = $this->db->selectRow("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `id` = ?", [$id]);
        return $user["name"];
    }

    public function hasRole($role, $adminGranted=true)
    {
        return $this->auth->hasRole($role) || ($adminGranted && ($this->auth->hasRole(Role::ADMIN) || $this->auth->hasRole(Role::SUPER_ADMIN)));
    }
}

class Availability {
    private $db = null;
    private $users = null;
    
    public function __construct($db, $users)
    {
        $this->db = $db;
        $this->users = $users;
    }

    public function change($availability, $user_id)
    {
        logger("Disponibilità cambiata in ".($availability ? '"disponibile"' : '"non disponibile"'), $user_id, $this->users->auth->getUserId());
        
        $available_users_count = $this->db->selectValue("SELECT COUNT(id) FROM `".DB_PREFIX."_profiles` WHERE `available` = 1 AND `hidden` = 0");
        if($available_users_count >= 5) {
            sendTelegramNotification("✅ Distaccamento operativo con squadra completa");
        } else if($available_users_count >= 2) {
            sendTelegramNotification("🚒 Distaccamento operativo per supporto");
        } else {
            sendTelegramNotification("⚠️ Distaccamento non operativo");
        }
        
        return $this->db->update(
            DB_PREFIX."_profiles",
            ["available" => $availability, 'availability_last_change' => 'manual'],
            ["id" => $user_id]
        );
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
        logger("Service added");
    }
}

class Schedules {
    private $db = null;
    private $users = null;

    public function __construct($db, $users)
    {
        $this->db = $db;
        $this->users = $users;
    }

    public function get($profile="default") {
        $response = $this->db->selectRow("SELECT * FROM `".DB_PREFIX."_schedules` WHERE `user` = ? AND `profile_name` = ?", [$this->users->auth->getUserId(), $profile]);
        if(!is_null($response)) {
            $response["schedules"] = json_decode($response["schedules"], true);
            return $response;
        }
        return [];
    }

    public function update($schedules, $profile="default") {
        //TODO implement multiple profiles
        //TODO implement holidays
        logger("Aggiornata programmazione orari disponibilità");
        if(empty($this->get($profile))) {
            return $this->db->insert(
                DB_PREFIX."_schedules",
                ["user" => $this->users->auth->getUserId(), "schedules" => $schedules, "profile_name" => $profile]
            );
        } else {
            return $this->db->update(
                DB_PREFIX."_schedules",
                ["schedules" => $schedules, "last_update" => null],
                ["user" => $this->users->auth->getUserId(), "profile_name" => $profile]
            );
        }
    }
}

$users = new Users($db, $auth);
$availability = new Availability($db, $users);
$services = new Services($db);
$schedules = new Schedules($db, $users);
