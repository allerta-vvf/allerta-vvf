<?php
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

require_once("vendor/autoload.php");
require("config.php");
if(!defined('SENTRY_LOADED')) {
    if(!defined(SENTRY_ENABLED)) define(SENTRY_ENABLED, false);
    if(SENTRY_ENABLED) {
        \Sentry\init([
            'dsn' => SENTRY_DSN,
            'environment' => SENTRY_ENV,
            'integrations' => [
                new \Sentry\Integration\ModulesIntegration(),
            ]
        ]);
        define('SENTRY_LOADED', true);
    }
}

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
    const EDITOR = \Delight\Auth\Role::EDITOR;
    const SUPER_EDITOR = \Delight\Auth\Role::SUPER_EDITOR;

    const DEVELOPER = \Delight\Auth\Role::DEVELOPER;

    const GUEST = \Delight\Auth\Role::SUBSCRIBER;
    const EXTERNAL_VIEWER = \Delight\Auth\Role::REVIEWER;

    const ADMIN = \Delight\Auth\Role::ADMIN;
    const SUPER_ADMIN = \Delight\Auth\Role::SUPER_ADMIN;

    public function __construct()
    {
    }

}

function get_timestamp() {
    return round(microtime(true) * 1000);
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
        if(defined("running_telegram_bot_webhook")) {
            $source_type = "telegram";
        }
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_strimwidth($_SERVER['HTTP_USER_AGENT'], 0, 200, "...") : null;
        $db->insert(
            DB_PREFIX."_log",
            ["action" => $action, "changed" => $changed, "editor" => $editor, "timestamp" => $timestamp, "ip" => $ip, "source_type" => $source_type, "user_agent" => $user_agent]
        );
    }
}

class Options
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
                    $this->optionsCache->set($db->select("SELECT * FROM `".DB_PREFIX."_options` WHERE `enabled` = 1"))->expiresAfter(60*60*24*7);
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
                $this->auth->admin()->addRoleForUserById($userId, Role::SUPER_EDITOR);
            }
            logger(__("log_messages.user_created"), $userId, $inserted_by);
            return $userId;
        } else {
            return false;
        }
    }

    public function get_users()
    {
        return $this->db->select("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `hidden` = 0");
    }
    
    public function getUserById($id)
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
        logger(__("log_messages.user_removed"), null, $removed_by);
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

    public function generateToken($precedent_user_id = null)
    {
        $token_params = [
            "roles" => $this->auth->getRoles(),
            "name" => $this->getName(),
            "v" => 2
        ];
        if(!is_null($precedent_user_id)) {
            $token_params["impersonating_user"] = true;
            $token_params["precedent_user_id"] = $precedent_user_id;
        }
        $token = $this->auth->generateJWTtoken($token_params);
        return $token;
    }

    public function loginAndReturnToken($username, $password)
    {
        $this->auth->loginWithUsername($username, $password);

        if($this->auth->hasRole(\Delight\Auth\Role::CONSULTANT)) {
            //Migrate to new user roles
            $this->auth->admin()->removeRoleForUserById($this->auth->getUserId(), \Delight\Auth\Role::CONSULTANT);
            $this->auth->admin()->addRoleForUserById($this->auth->getUserId(), Role::SUPER_EDITOR);
            
            $this->auth->loginWithUsername($username, $password);
        }

        return $this->generateToken();
    }

    public function loginAsUserIdAndReturnToken($userId)
    {
        $precedent_user_id = null;
        if(!is_null($this->auth->getUserId())) {
            if((int) $userId === (int) $this->auth->getUserId()) {
                return $this->generateToken();
            }
            $precedent_user_id = $this->auth->getUserId();
            $this->auth->logOut();
        }

        $this->auth->admin()->logInAsUserById($userId);

        if($this->auth->hasRole(\Delight\Auth\Role::CONSULTANT)) {
            //Migrate to new user roles
            $this->auth->admin()->removeRoleForUserById($this->auth->getUserId(), \Delight\Auth\Role::CONSULTANT);
            $this->auth->admin()->addRoleForUserById($this->auth->getUserId(), Role::SUPER_EDITOR);
            
            $this->auth->admin()->logInAsUserById($userId);
        }

        return $this->generateToken($precedent_user_id);
    }


    public function isHidden($id=null)
    {
        if(is_null($id)) $id = $this->auth->getUserId();
        if(is_null($id)) return true;
        return $this->db->selectValue("SELECT hidden FROM `".DB_PREFIX."_profiles` WHERE `id` = ?", [$id]);
    }

    public function getName($id=null)
    {
        if(is_null($id)) $id = $this->auth->getUserId();
        return $this->db->selectValue("SELECT name FROM `".DB_PREFIX."_profiles` WHERE `id` = ?", [$id]);
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

    public function change_manual_mode($manual_mode, $user_id = null) {
        global $db, $users;
        if(is_null($user_id)) $user_id = $users->auth->getUserId();
        $db->update(
            DB_PREFIX."_profiles",
            [
                "manual_mode" => $manual_mode
            ],
            [
                "id" => $user_id
            ]
        );
    }

    public function change($availability, $user_id, $is_manual_mode=true)
    {
        if($is_manual_mode) logger(sprintf(__("availability_changed_to"), __($availability ? 'available' : 'not_available')), $user_id, $this->users->auth->getUserId());
        
        $change_values = ["available" => $availability];
        if($is_manual_mode) $change_values["manual_mode"] = 1;

        $response = $this->db->update(
            DB_PREFIX."_profiles",
            $change_values,
            ["id" => $user_id]
        );

        if(!$this->users->isHidden($user_id)) {
            $available_users_count = $this->db->selectValue("SELECT COUNT(id) FROM `".DB_PREFIX."_profiles` WHERE `available` = 1 AND `hidden` = 0");
            if($available_users_count === 5) {
                sendTelegramNotification(__("telegram_bot.available_full"));
            } else if($available_users_count < 2) {
                sendTelegramNotification(__("telegram_bot.not_available"));
            } else if($available_users_count < 5) {
                sendTelegramNotification(__("telegram_bot.available_support"));
            }
        }

        return $response;
    }
}

class Services {
    private $db = null;
    private $users = null;
    private $places = null;
    
    public function __construct($db, $users, $places)
    {
        $this->db = $db;
        $this->users = $users;
        $this->places = $places;
    }

    public function list() {
        $response = $this->db->select("SELECT ".DB_PREFIX."_services.*, place.id as place_id, place.lat as lat, place.lng as lng, place.place_name as place_name, place.country as country, place.country_code as country_code, place.postcode as postcode, place.state as state, place.municipality as municipality, place.village as village, place.hamlet as hamlet, place.road as road, place.building_service_name as building_service_name, place.house_number as house_number FROM `".DB_PREFIX."_services` JOIN ".DB_PREFIX."_places_info place ON ".DB_PREFIX."_services.place_reverse = place.id ORDER BY start DESC");
        $response = is_null($response) ? [] : $response;
        foreach($response as &$service) {
            $service["chief"] = $this->users->getName($service["chief"]);

            $drivers = explode(";", $service["drivers"]);
            foreach($drivers as &$driver) {
                $driver = $this->users->getName($driver);
            }
            $service["drivers"] = implode(", ", $drivers);

            $crew = explode(";", $service["crew"]);
            foreach($crew as &$member) {
                $member = $this->users->getName($member);
            }
            $service["crew"] = implode(", ", $crew);

            $service["type"] = $this->db->selectValue("SELECT name FROM `".DB_PREFIX."_type` WHERE `id` = ?", [$service["type"]]);
        }
        return $response;
    }

    public function get($id) {
        $response = $this->db->selectRow("SELECT ".DB_PREFIX."_services.*, place.id as place_id, place.lat as lat, place.lng as lng, place.place_name as place_name, place.country as country, place.country_code as country_code, place.postcode as postcode, place.state as state, place.municipality as municipality, place.village as village, place.hamlet as hamlet, place.road as road, place.building_service_name as building_service_name, place.house_number as house_number FROM `".DB_PREFIX."_services` JOIN ".DB_PREFIX."_places_info place ON ".DB_PREFIX."_services.place_reverse = place.id WHERE ".DB_PREFIX."_services.id = ? ORDER BY start DESC", [$id]);
        if(is_null($response)) return [];
        return $response;

        $response["chief"] = $this->users->getName($response["chief"]);
        $response = explode(";", $response["drivers"]);
        foreach($response as &$driver) {
            $driver = $this->users->getName($driver);
        }
        $response["drivers"] = implode(", ", $response);

        $crew = explode(";", $response["crew"]);
        foreach($crew as &$member) {
            $member = $this->users->getName($member);
        }
        $response["crew"] = implode(", ", $crew);

        $response["type"] = $this->db->selectValue("SELECT name FROM `".DB_PREFIX."_type` WHERE `id` = ?", [$response["type"]]);

        return $response;
    }

    public function increment_counter($increment)
    {
        $increment = implode(",", array_unique(explode(",", str_replace(";", ",", $increment))));
        $this->db->exec(
            "UPDATE `".DB_PREFIX."_profiles` SET `services`= services + 1 WHERE id IN ($increment)"
        );
    }

    public function decrement_counter($decrement)
    {
        $decrement = implode(",", array_unique(explode(",", str_replace(";", ",", $decrement))));
        $this->db->exec(
            "UPDATE `".DB_PREFIX."_profiles` SET `services`= services - 1 WHERE id IN ($decrement)"
        );
    }

    public function get_selected_users($id)
    {
        $response = $this->db->selectRow(
            "SELECT `chief`, `drivers`, `crew` FROM `".DB_PREFIX."_services` WHERE `id` = :id",
            ["id" => $id]
        );
        return $response["chief"].";".$response["drivers"].";".$response["crew"];
    }

    public function add($start, $end, $code, $chief, $drivers, $crew, $place, $notes, $type, $inserted_by)
    {
        $this->db->insert(
            DB_PREFIX."_services",
            ["start" => $start, "end" => $end, "code" => $code, "chief" => $chief, "drivers" => $drivers, "crew" => $crew, "place" => $place, "place_reverse" => $this->places->save_place_reverse(explode(";", $place)[0], explode(";", $place)[1]), "notes" => $notes, "type" => $type, "inserted_by" => $inserted_by]
        );
        $serviceId = $this->db->getLastInsertId();

        $this->increment_counter($chief.",".$drivers.",".$crew);
        logger(__("log_messages.service_added"));

        return $serviceId;
    }

    public function delete($id)
    {
        $service = $this->db->selectRow(
            "SELECT `chief`, `drivers`, `crew` FROM `".DB_PREFIX."_services` WHERE `id` = :id",
            ["id" => $id]
        );
        $this->decrement_counter($service["chief"].";".$service["drivers"].";".$service["crew"]);

        $this->db->delete(
            DB_PREFIX."_services",
            ["id" => $id]
        );
        logger(__("log_messages.service_deleted"));

        return true;
    }
}

function curl_call($url, $is_response_json=true)
{
    $useragent = "Allerta-VVF (https://github.com/allerta-vvf/allerta-vvf) place search proxy (see utils.php class Places)";
    try {
        $hostname = gethostname();
        if(!is_null($hostname) && $hostname != "") $useragent .= " - server hostname: ".$hostname;
    } catch (Exception $e) {
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    $response = curl_exec($ch);
    if($is_response_json) $response = json_decode($response, true);
    curl_close($ch);

    return $response;
}

class Places {
    private $cache;
    private $users;
    private $db;
    private $placesCache;

    public function __construct($cache, $users, $db)
    {
        $this->cache = $cache;
        $this->users = $users;
        $this->db = $db;
    }

    public function search($query)
    {
        $this->placesCache = $this->cache->getItem("place_".md5($query));
        $cache_element = $this->placesCache->get();
        if (is_null($cache_element)) {
            $place_response = curl_call("https://nominatim.openstreetmap.org/search?format=json&limit=6&q=".urlencode($query));

            if(is_null($place_response)) {
                $place_response = [];
            }

            $this->placesCache->set($place_response)->expiresAfter(60*60*24*365);
            $this->cache->save($this->placesCache);

            return $place_response;
        } else {
            return $cache_element;
        }
    }

    public function save_place_reverse($lat, $lng)
    {
        $this->save_static_map_image($lat, $lng);

        $response = curl_call("https://nominatim.openstreetmap.org/reverse?format=json&lat=".$lat."&lon=".$lng);

        if(is_null($response) || empty($response)) {
            $response = "{}";
            $place_name = "";
            $address = [];
        } else {
            $place_name = $response["display_name"];
            $address = $response["address"];
        }

        $row = ["lat" => $lat, "lng" => $lng, "place_name" => $place_name, "place" => json_encode($response)];
        if(isset($address["country"])) $row["country"] = $address["country"];
        if(isset($address["country_code"])) $row["country_code"] = $address["country_code"];
        if(isset($address["postcode"])) $row["postcode"] = $address["postcode"];
        if(isset($address["region"])) $row["state"] = $address["region"];
        if(isset($address["state"])) $row["state"] = $address["state"];
        if(isset($address["municipality"])) $row["municipality"] = $address["municipality"];
        if(isset($address["village"])) $row["village"] = $address["village"];
        if(isset($address["hamlet"])) $row["hamlet"] = $address["hamlet"];
        if(isset($address["road"])) $row["road"] = $address["road"];
        if(isset($address["tourism"])) $row["building_service_name"] = $address["tourism"];
        if(isset($address["croft"])) $row["building_service_name"] = $address["croft"];
        if(isset($address["isolated_dwelling"])) $row["building_service_name"] = $address["isolated_dwelling"];
        if(isset($address["amenity"])) $row["building_service_name"] = $address["amenity"];
        if(isset($address["building"])) $row["building_service_name"] = $address["building"];
        if(isset($address["house_number"])) $row["house_number"] = $address["house_number"];

        $this->db->insert(
            DB_PREFIX."_places_info",
            $row
        );

        return $this->db->getLastInsertId();
    }

    function save_static_map_image($lat, $lng)
    {
        if(get_option("use_static_map_image_generator", false)) {
            $url = get_option("static_map_image_generator_url", "");
            $url = str_replace("{{lat}}", $lat, $url);
            $url = str_replace("{{lng}}", $lng, $url);
        } else {
            $tile_x = floor($lng / 360 * pow(2, get_option("static_map_image_zoom", 18)));
            $tile_y = floor(log(tan((90 + $lat) * pi() / 360)) / pi() * pow(2, get_option("static_map_image_zoom", 18)));

            $url = "https://a.tile.openstreetmap.org/".get_option("static_map_image_zoom", 18)."/".$tile_x."/".$tile_y.".png";
        }
        $image = curl_call($url, false);
        $image_path = "tmp/".md5($lat.";".$lng).".jpg";
        file_put_contents($image_path, $image);
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
        logger(__("log_messages.availability_schedules_updated"));
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

class Translations
{
    public $loaded_languages = ["en", "it"];
    public $default_language = "en";
    public $language = null;
    public $client_languages = ["en"];
    public $loaded_translations = [];
    public $filename = "";

    public function client_languages()
    {
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $client_languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $client_languages = "en-US;q=0.5,en;q=0.3";
        }
        if(strpos($client_languages, ';') == false) {
            if(strpos($client_languages, '-') !== false) {
                return [substr($client_languages, 0, 5)];
            } else {
                return [substr($client_languages, 0, 2)];
            }
        } else {
            $client_languages = explode(",", $client_languages);
            $tmp_languages = [];
            foreach($client_languages as $language){
                if(strpos($language, ';') == false) {
                    $tmp_languages[$language] = 1;
                } else {
                    $tmp_languages[explode(";q=", $language)[0]] = (float) explode(";q=", $language)[1];
                }
            }
            arsort($tmp_languages);
            return array_keys($tmp_languages);
        }
    }

    public function __construct($force_language = false)
    {
        $this->client_languages = $this->client_languages();
        if(isset($_COOKIE["forceLanguage"]) && in_array($_COOKIE["forceLanguage"], $this->loaded_languages)){
            $this->language = $_COOKIE["forceLanguage"];
        } else if($force_language && in_array($force_language, $this->loaded_languages)){
            $this->language = $force_language;
        } else {
            foreach($this->client_languages as $language){
                if(in_array($language, $this->loaded_languages) && $this->language == null) {
                    $this->language = $language;
                }
            }
            if($this->language == null) {
                $this->language = "en";
            }
        }
        $this->filename = "translations/".$this->language.".php";
        if (file_exists($this->filename)) {
            $this->loaded_translations = require($this->filename);
        } else {
            throw new Exception("Language file not found");
        }
    }

    public function translate($string)
    {
        if(strpos($string, ".") !== false) {
            $string = explode(".", $string);
            if (!array_key_exists($string[1], $this->loaded_translations[$string[0]])) {
                throw new Exception('string does not exist');
            }
            return $this->loaded_translations[$string[0]][$string[1]];
        } else {
            if (!array_key_exists($string, $this->loaded_translations)) {
                throw new Exception('string does not exist');
            }
            return $this->loaded_translations[$string];
        }
    }
}

$users = new Users($db, $auth);
$availability = new Availability($db, $users);
$places = new Places($cache, $users, $db);
$services = new Services($db, $users, $places);
$schedules = new Schedules($db, $users);
$translations = new Translations();

function __(string $string)
{
    global $translations;
    return $translations->translate($string);
}
