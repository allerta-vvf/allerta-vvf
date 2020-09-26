<?php
require_once 'vendor/autoload.php';
use Tracy\Debugger;

if(!file_exists("config.php") && !file_exists("../../config.php")) header('Location: install/install.php');

require_once 'config.php';

session_start();
date_default_timezone_set('Europe/Rome');

class tools{
  public $check_cf_ip;

  public function __construct($check_cf_ip){
    define("TOOLS", "OK");
    $this->check_cf_ip = $check_cf_ip;
  }

  public function validate_form_data($data, $noempty=true, $value=null){
    if(!is_array($data) && isset($data) && !empty($data)){
      if(substr($data, 0, 6) == '$post-'){
        $data = substr($data, 6);
        if(isset($_POST[$data])){
          $data = $_POST[$data];
        }
      }
    }
    if(is_array($data)){
      if(empty($data)){
        $continue = false;
        return false;
      } else {
        $continue = true;
      }
      if($continue){
        foreach($data as $key=>$value){
          if(!is_array($value) && isset($value) && !empty($value)){
            if(substr($value, 0, 6) == '$post-'){
              $value = substr($value, 6);
              if(isset($_POST[$value])){
                $value = $_POST[$value];
              }
            }
          }
          if($continue){
          if(!is_array($value)){
            bdump($value);
            bdump("_");
          $validazione = $this->validate_form_data($value, $noempty, $value);
          if(!$validazione){
            $continue = false;
            return false;
          }
        }
        }
        }
        if($continue){
          return true;
        }
      }
    } else if(isset($data)) {
      if(!empty($data)){
        if(!is_null($value)){
          return $value == $data;
        } else {
          bdump($data);
          return true;
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function get_ip(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if($this->check_cf_ip){
      if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
      }
    }
    return $ip;
  }

  public function get_page_url(){
    if(!empty($_SERVER["HTTPS"])){
      if($_SERVER["HTTPS"] == "on"){
        $protocol = "https";
      } else {
        $protocol = "http";
      }
    } else {
      $protocol = "http";
    }
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
  }

  public function redirect($url){
    if (!headers_sent()){
        header('Location: '.$url);
        exit;
    } else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
    }
  }
  function extract_unique($data){
    $array2=[];
    foreach($data as $arr){
        if(is_array($arr)){
            $tmp = $this->extract_unique($arr);
            foreach($tmp as $temp){
                if(!is_array($temp)){
                    if(!in_array($temp, $array2)){
                        $array2[] = $temp;
                    }
                }
            
            }
        } else {
            if(!in_array($arr, $array2)){
                $array2[] = $arr;
            }
        }
    }
    return $array2;
  }

  public function createKey($hashCode=false, $lenght=128){
    $code = str_replace(".", "", bin2hex(random_bytes(10)).base64_encode(openssl_random_pseudo_bytes(30)));
    if($hashCode){
      $code = $code.".".hash("sha256", $code);
    }
    return $code;
  }

  public function sanitize($string, $htmlAllowed=false, $htmlPurifierOptions=[]){
    if($htmlAllowed){
      $config = HTMLPurifier_Config::createDefault();
      foreach ($htmlPurifierOptions as $key => $value) {
        $config->set($key, $value);
      }
      $purifier = new HTMLPurifier($config);
      $string = $purifier->purify($string);
    } else {
      $string = htmlspecialchars($string);
    }
    return $string;
  }
}

class database{
  protected $db_host = DB_HOST;
  protected $db_dbname = DB_NAME;
  protected $db_username = DB_USER;
  protected $db_password = DB_PASSWORD;
  public $connection = null;
  public $query = null;
  public $stmt = null;
  public $load_from_file = true;
  public $options = [];
  public $options_cache_file = null;

  public function connect(){
    try {
      $this->connection = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_dbname, $this->db_username, $this->db_password);
      $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
      exit($e->getMessage());
    }
  }

  public function isOptionsEmpty(){
    return empty($this->exec("SELECT * FROM `%PREFIX%_options`;", true));
  }

  public function __construct(){
    if(!defined("DATABASE")){
      define("DATABASE", "OK");
    }
    $this->connect();
    if($this->isOptionsEmpty()){
      header('Location: install/install.php');
    }
    $file_infos = pathinfo(array_reverse(debug_backtrace())[0]['file']);
    if(strpos($file_infos['dirname'], 'risorse') !== false) {
      $this->options_cache_file = "../../options.txt";
    } else {
      $this->options_cache_file = "options.txt";
    }
    if($this->load_from_file){
      if(file_exists($this->options_cache_file)/* && time()-@filemtime($this->options_cache_file) < 604800*/){
        $this->options = unserialize(file_get_contents($this->options_cache_file), ['allowed_classes' => false]);
      } else {
        $this->options = $this->exec("SELECT * FROM `%PREFIX%_options` WHERE `enabled` = 1", true);
        file_put_contents($this->options_cache_file, serialize( $this->options ));
      }
    }
  }

  public function close(){
    $this->connection = null;
  }

  public function exec($sql1, $fetch=false, $param=null, ...$others_params){
    try{
      //$this->connection->beginTransaction();
      array_unshift($others_params,$sql1);
      bdump($others_params);
      $toReturn = [];
      foreach($others_params as $sql){
        $sql = str_replace("%PREFIX%", DB_PREFIX, $sql);
        bdump($sql);
        $this->stmt = $this->connection->prepare($sql);
        if(!is_null($param)){
          $this->query = $this->stmt->execute($param);
        } else {
          $this->query = $this->stmt->execute();
        }
        bdump($this->query);
        
        if($fetch == true){
          if(count($others_params) > 1) {
            $toReturn[] = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
          } else {
            $toReturn = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
          }
        }
      }
      //$this->connection->commit();
      //$this->stmt->closeCursor();
      return $toReturn;
    } catch (PDOException $e) {
      print "Error!: " . $e->getMessage() . "<br/>";
      //$this->connection->rollBack();
      die();
    }
  }
  
  public function exists($table, $id){
      $risultato = $this->exec("SELECT :table FROM `%PREFIX%_services` WHERE id = :id;", true, [":table" => $table, ":id" => $id]);
      return !empty($risultato);
  }
  
  public function getOption($name){
    if(defined($name)){
      return constant($name);
    } else {
      //$option = $this->exec("SELECT `value` FROM `%PREFIX%_options` WHERE `name` = :name AND `enabled` = 1;", true, [":name" => $name]);
      //return empty($option) ? "" : $option[0]["value"];
      foreach($this->options as $option){
        if($name == $option["name"]){
          return empty($option["value"]) ? "" : $option["value"];
        }
      }
    }
  }

  public function incrementa($incrementa){
    bdump($incrementa);
    $sql = "UPDATE `%PREFIX%_profiles` SET `services`= services + 1 WHERE id IN ($incrementa);";
    $this->exec($sql, false);
  }

  public function getIncrementa($id){
    bdump($id);
    $sql = "SELECT `incrementa` FROM `%PREFIX%_services` WHERE `id` = :id";
    $incrementa = $this->exec($sql, true, [":id" => $id])[0]['incrementa'];
    bdump($incrementa);
    return $incrementa;
  }

  public function diminuisci($id){
    $sql = "UPDATE `%PREFIX%_profiles` SET `services`= services - 1 WHERE id IN ({$this->getIncrementa($id)});";
    $this->exec($sql, false);
  }

  public function incrementa_trainings($incrementa){
    bdump($incrementa);
    $sql = "UPDATE `%PREFIX%_profiles` SET `trainings`= trainings + 1 WHERE id IN ($incrementa);";
    $this->exec($sql, false);
  }

  public function getIncrementa_trainings($id){
    bdump($id);
    $sql = "SELECT `incrementa` FROM `%PREFIX%_trainings` WHERE `id` = :id";
    $incrementa = $this->exec($sql, true, [":id" => $id])[0]['incrementa'];
    bdump($incrementa);
    return $incrementa;
  }

  public function diminuisci_trainings($id){
    $sql = "UPDATE `%PREFIX%_profiles` SET `trainings`= trainings - 1 WHERE id IN ({$this->getIncrementa_trainings($id)});";
    $this->exec($sql, false);
  }

  public function add_service($data, $codice, $uscita, $rientro, $capo, $autisti, $personale, $luogo, $note, $tipo, $incrementa, $inseritoda){
    $autisti = implode(",", $autisti);
    bdump($autisti);
    $personale = implode(",", $personale);
    bdump($personale);
    $incrementa = implode(",", $incrementa);
    bdump($incrementa);
    $sql = "INSERT INTO `%PREFIX%_services` (`id`, `data`, `codice`, `uscita`, `rientro`, `capo`, `autisti`, `personale`, `luogo`, `note`, `tipo`, `incrementa`, `inseritoda`) VALUES (NULL, :data, :codice, :uscita, :rientro, :capo, :autisti, :personale, :luogo, :note, :tipo, :incrementa, :inseritoda);";
    $this->exec($sql, false, [":data" => $data, ":codice" => $codice, "uscita" => $uscita, ":rientro" => $rientro, ":capo" => $capo, ":autisti" => $autisti, ":personale" => $personale, ":luogo" => $luogo, ":note" => $note, ":tipo" => $tipo, ":incrementa" => $incrementa, ":inseritoda" => $inseritoda]);
    $this->incrementa($incrementa);
  }

  public function remove_service($id){
    $this->diminuisci($id);
    $this->exec("DELETE FROM `%PREFIX%_services` WHERE `id` = :id", true, [":id" => $id]);
  }


  public function change_service($id, $data, $codice, $uscita, $rientro, $capo, $autisti, $personale, $luogo, $note, $tipo, $incrementa, $inseritoda){
    $this->remove_service($id); // TODO: update, instead of removing and re-adding (with another id)
    $this->add_service($data, $codice, $uscita, $rientro, $capo, $autisti, $personale, $luogo, $note, $tipo, $incrementa, $inseritoda);
  }

  public function add_training($data, $name, $start_time, $end_time, $capo, $personale, $luogo, $note, $incrementa, $inseritoda){
    $personale = implode(",", $personale);
    bdump($personale);
    $incrementa = implode(",", $incrementa);
    bdump($incrementa);
    $sql = "INSERT INTO `%PREFIX%_trainings` (`id`, `data`, `name`, `inizio`, `fine`, `capo`, `personale`, `luogo`, `note`, `incrementa`, `inseritoda`) VALUES (NULL, :data, :name, :start_time, :end_time, :capo, :personale, :luogo, :note, :incrementa, :inseritoda);";
    $this->exec($sql, false, [":data" => $data, ":name" => $name, "start_time" => $start_time, ":end_time" => $end_time, ":capo" => $capo, ":personale" => $personale, ":luogo" => $luogo, ":note" => $note, ":incrementa" => $incrementa, ":inseritoda" => $inseritoda]);
    $this->incrementa_trainings($incrementa);
  }

  public function remove_training($id){
    $this->diminuisci_trainings($id);
    bdump($id);
    $this->exec("DELETE FROM `%PREFIX%_trainings` WHERE `id` = :id", true, [":id" => $id]);
  }


  public function change_training($id, $data, $name, $start_time, $end_time, $capo, $personale, $luogo, $note, $incrementa, $inseritoda){
    $this->remove_training($id); // TODO: update, instead of removing and re-adding (with another id)
    bdump("removed");
    $this->add_training($data, $name, $start_time, $end_time, $capo, $personale, $luogo, $note, $incrementa, $inseritoda);
  }
}

final class Role {
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

  public function __construct() {}

}

class user{
  private $database = null;
  private $tools = null;
  public $auth = null;
  public $authenticated = false;

  public function __construct($database, $tools){
    $this->database = $database;
    $this->tools = $tools;
    $this->auth = new \Delight\Auth\Auth($database->connection, $tools->get_ip(), DB_PREFIX."_", false);
    $this->authenticated = $this->auth->isLoggedIn();
    define("LOGIN", "OK");
  }

  public function authenticated(){
    return $this->authenticated;
  }

  public function requirelogin(){
   if(!$this->authenticated()){
      if($this->database->getOption("intrusion_save")){
        if($this->database->getOption("intrusion_save_info")){
          $params = [":pagina" => $this->tools->get_page_url(), ":ip" => $this->tools->get_ip(), ":data" => date("d/m/Y"), ":ora" => date("H:i.s"), ":servervar" => json_encode($_SERVER)];
        } else {
          $params = [":pagina" => $this->tools->get_page_url(), ":ip" => "redacted", ":data" => date("d/m/Y"), ":ora" => date("H:i.s"), ":servervar" => json_encode(["redacted" => "true"])];
        }
        $sql = "INSERT INTO `%PREFIX%_intrusions` (`id`, `pagina`, `data`, `ora`, `ip`, `servervar`) VALUES (NULL, :pagina, :data, :ora, :ip, :servervar)";
        $this->database->exec($sql, false, $params);
      }
      $this->tools->redirect($this->database->getOption("web_url"));
   }
  }

  public function requireRole($role, $adminGranted=true){
    return $this->auth->hasRole($role) || $adminGranted && $role !== Role::DEVELOPER && $this->auth->hasRole(Role::ADMIN) || $role !== Role::DEVELOPER && $this->auth->hasRole(Role::SUPER_ADMIN);
  }

  public function name($replace=false){
    if(isset($_SESSION['_user_name'])){
      if($replace){
        return str_replace(" ", "_", $_SESSION['_user_name']);
      } else {
        return $_SESSION['_user_name'];
      }
    } else {
        return "not authenticated";
    }
  }
  
  public function nameById($id){
    $profiles = $this->database->exec("SELECT `name` FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $id]);
    if(!empty($profiles)){
      if(!is_null($profiles[0]["name"])){
        return(s($profiles[0]["name"],false));
      } else {
        $user = $this->database->exec("SELECT `username` FROM `%PREFIX%_users` WHERE id = :id;", true, [":id" => $id]);
        if(!empty($user)){
          if(!is_null($user[0]["username"])){
            return(s($user[0]["username"],false));
          } else {
            return false;
          }
        } else {
          return false;
        }
      }
    } else {
      return false;
    }
  }
  
  public function hidden(){
    $profiles = $this->database->exec("SELECT `name` FROM `%PREFIX%_profiles` WHERE hidden = 1;", true);
    return $profiles;
  }
  
  public function available($name){
    $user = $this->database->exec("SELECT available FROM `%PREFIX%_users` WHERE name = :name;", true, [":name" => $name]);
    if(empty($user)){
        return false;
    } else {
        return $user[0]["available"];
    }
  }
  
  public function info(){
    return array("autenticated" => $this->authenticated(), "id" => $this->auth->getUserId(), "name" => $this->name(), "full_viewer" => $this->requireRole(Role::FULL_VIEWER), "tester" => $this->requireRole(Role::TESTER), "developer" => $this->requireRole(Role::DEVELOPER));
  }

  public function login($name, $password, $twofa=null){
    if(!empty($name)){
      if(!empty($password)){
        try {
          $this->auth->loginWithUsername($name, $password);
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
          return ["status" => "error", "code" => 010, "text" => "Wrong email address"];
          die('Wrong email address');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
          return ["status" => "error", "code" => 011, "text" => "Wrong password"];
          die('Wrong password');
        }
        catch (\Delight\Auth\EmailNotVerifiedException $e) {
          return ["status" => "error", "code" => 012, "text" => "Email not verified"];
          die('Email not verified');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
          return ["status" => "error", "code" => 020, "text" => "Too many requests"];
          die('Too many requests');
        }
        if($this->auth->isLoggedIn()){
          $this->log("Login", $this->auth->getUserId(), $this->auth->getUserId(), date("d/m/Y"), date("H:i.s"));
          $user = $this->database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $this->auth->getUserId()]);
          if(!empty($user)){
            if(is_null($user[0]["name"])){
              $_SESSION['_user_name'] = $this->auth->getUsername();
            } else {
              $_SESSION['_user_name'] = $user[0]["name"];
            }
            $_SESSION['_user_hidden'] = $user[0]["hidden"];
            $_SESSION['_user_disabled'] = $user[0]["disabled"];
            $_SESSION['_user_caposquadra'] = $user[0]["caposquadra"];
            return true;
          }
        }
      } else {
        return ["status" => "error", "code" => 002];
      }
    } else {
      return ["status" => "error", "code" => 001];
    }
  }
  public function log($action, $changed, $editor, $date, $time){
    $params = [":action" => $action, ":changed" => $changed, ":editor" => $editor, ":date" => $date, ":time" => $time];
    $sql = "INSERT INTO `%PREFIX%_log` (`id`, `action`, `changed`, `editor`, `date`, `time`) VALUES (NULL, :action, :changed, :editor, :date, :time)";
    $this->database->exec($sql, false, $params);
  }

  public function logout(){
    try {
      $this->log("Logout", $this->auth->getUserId(), $this->auth->getUserId(), date("d/m/Y"), date("H:i.s"));
      $this->auth->destroySession();
    }
    catch (\Delight\Auth\NotLoggedInException $e) {
      die('Not logged in');
    }
  }

  public function add_user($email, $name, $username, $password, $birthday, $capo, $autista, $hidden, $disabled, $inseritoda){
    $userId = $this->auth->admin()->createUserWithUniqueUsername($email, $password, $username);
    if($userId){
      $hidden = $hidden ? 1 : 0;
      $disabled = $disabled ? 1 : 0;
      $capo = $capo ? 1 : 0;
      $autista = $autista ? 1 : 0;
      $sql = "INSERT INTO `%PREFIX%_profiles` (`hidden`, `disabled`, `name`, `caposquadra`, `autista`) VALUES (:hidden, :disabled, :name, :caposquadra, :autista)";
      $this->database->exec($sql, false, [":hidden" => $hidden, ":disabled" => $disabled, ":name" => $name, ":caposquadra" => $capo, ":autista" => $autista]);
      if($capo == 1){
        $this->auth->admin()->addRoleForUserById($userId, Role::FULL_VIEWER);
      }
      $this->log("User created", $userId, $inseritoda, date("d/m/Y"), date("H:i.s"));
      return $userId;
    } else {
      return $false;
    }
  }

  public function remove_user($id, $rimossoda){
    $this->database->exec("DELETE FROM `%PREFIX%_users` WHERE `id` = :id", true, [":id" => $id], "DELETE FROM `%PREFIX%_profiles` WHERE `id` = :id");
    $this->log("User removed", null, $rimossoda, date("d/m/Y"), date("H:i.s"));
  }
}

class translations{
  public $loaded_languages = ["en", "it"];
  public $default_language = "en";
  public $language = null;
  public $client_languages = ["en"];
  public $loaded_translations = [];
  public $filename = "";

  public function client_languages() {
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
        $client_languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    } else {
        $client_languages = "en-US;q=0.5,en;q=0.3";
    }
    if(strpos($client_languages, ';') == false){
        if(strpos($client_languages, '-') !== false){
            return [substr($client_languages, 0, 5)];
        } else {
            return [substr($client_languages, 0, 2)];
        }
    } else {
        $client_languages = explode(",", $client_languages);
        $tmp_languages = [];
        foreach($client_languages as $key=>$language){
            if(strpos($language, ';') == false){
                $tmp_languages[$language] = 1;
            } else {
                $tmp_languages[explode(";q=",$language)[0]] = (float) explode(";q=",$language)[1];
            }
        }
        arsort($tmp_languages);
        return array_keys($tmp_languages);
    }
  }

  public function __construct(){
    $this->client_languages = $this->client_languages();
    foreach($this->client_languages as $language){
      if(in_array($language, $this->loaded_languages) && $this->language == null){
        $this->language = $language;
      }
    }
    if($this->language == null){
      $this->language = "en";
    }
    $file_infos = pathinfo(array_reverse(debug_backtrace())[0]['file']);
    if(strpos($file_infos['dirname'], 'risorse') !== false) {
      $this->filename = "../../translations/".$this->language."/".$file_infos['basename'];
    } else {
      $this->filename = "translations/".$this->language."/".$file_infos['basename'];
    }
    if (file_exists($this->filename)){
      $this->loaded_translations = array_merge(require("translations/".$this->language."/base.php"),require($this->filename));
    } else {
      try{
        $this->loaded_translations = require("translations/".$this->language."/base.php");
      } catch(Exception $e) {
        $this->loaded_translations = require("../../translations/".$this->language."/base.php");
      }
    }
  }

  public function translate($string){
    bdump($string);
    try {
      if (!array_key_exists($string, $this->loaded_translations))
        throw new Exception ('string does not exist');
      return $this->loaded_translations[$string];
    } catch (\Exception $e) {
      bdump($this->filename);
      bdump($e, $string);
      return $string;
    }
  }
}

function init_class($enableDebugger=true, $headers=true){
  global $tools, $database, $user, $translations;
  if(!isset($tools) && !isset($database) && !isset($translations)){
    $database = new database();
    $tools = new tools($database->getOption("check_cf_ip"));
    $user = new user($database, $tools);
    $translations = new translations();
  }
  if($headers){
    header("Content-Security-Policy: default-src 'unsafe-eval' 'unsafe-inline' 'self'");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Feature-Policy: autoplay 'none'; camera 'none'; microphone 'none'; payment 'none'");
  }
  //var_dump($user);
  //exit();
  //if($enableDebugger){
    //if($user->requireRole(Role::DEVELOPER)){
      Debugger::enable(Debugger::DEVELOPMENT, __DIR__ . '/error-log');
    //} else {
      //Debugger::enable(Debugger::PRODUCTION, __DIR__ . '/error-log');
    //}
  //}
  bdump(get_included_files());
  bdump($translations->loaded_translations);
}

function t($string, $echo=true){
  global $translations;
  if($echo){
    echo $translations->translate($string);
  } else {
    return $translations->translate($string);
  }
}

function s($string, $echo=true, $htmlAllowed=false, $htmlPurifierOptions=[]){
  global $tools;
  if($echo){
    echo $tools->sanitize($string, $htmlAllowed, $htmlPurifierOptions);
  } else {
    return $tools->sanitize($string, $htmlAllowed, $htmlPurifierOptions);
  }
}