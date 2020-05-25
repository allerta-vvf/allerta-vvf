<?php
require_once 'vendor/autoload.php';
use Tracy\Debugger;

require_once 'config.php';

session_start();
date_default_timezone_set('Europe/Rome');

class tools{
  public function __construct(){
    define("TOOLS", "OK");
  }

  public function validazione_form($data, $noempty=true, $valore=null){
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
        $continuo = false;
        return false;
      } else {
        $continuo = true;
      }
      if($continuo){
        foreach($data as $chiave=>$valore){
          if(!is_array($valore) && isset($valore) && !empty($valore)){
            if(substr($valore, 0, 6) == '$post-'){
              $valore = substr($valore, 6);
              if(isset($_POST[$valore])){
                $valore = $_POST[$valore];
              }
            }
          }
          if($continuo){
          if(!is_array($valore)){
            bdump($valore);
            bdump("_");
          $validazione = $this->validazione_form($valore, $noempty, $valore);
          if(!$validazione){
            $continuo = false;
            return false;
          }
        }
        }
        }
        if($continuo){
          bdump("passato con");
          bdump($data);
          return true;
        }
      }
    } else if(isset($data)) {
      if(!empty($data)){
        if(!is_null($valore)){
          return $valore == $data;
        } else {
          bdump("non dovrebbe succedere");
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
    if(SERVER_UNDER_CF){
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
}

class database{
  protected $db_host = DB_HOST;
  protected $db_dbname = DB_NAME;
  protected $db_username = DB_USER;
  protected $db_password = DB_PASSWORD;
  public $connection = null;
  public $query = null;
  public $stmt = null;

  public function connetti(){
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

  public function __construct(){
    if(!defined("DATABASE")){
      define("DATABASE", "OK");
    }
    $this->connetti();
  }

  public function close(){
    $this->connection = null;
  }

  public function esegui($sql, $fetch=false, $param=null){
    try{
      $this->connection->beginTransaction();
      $this->stmt = $this->connection->prepare(str_replace("%PREFIX%", DB_PREFIX, $sql));
      if(!is_null($param)){
        $this->query = $this->stmt->execute($param);
      } else {
        $this->query = $this->stmt->execute();
      }
      bdump($this->query);
      $this->connection->commit();
      if($fetch == true){
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
      }
      $this->stmt->closeCursor();
    } catch (PDOException $e) {
      print "Errore!: " . $e->getMessage() . "<br/>";
      $this->connection->rollBack();
      die();
    }
  }
  
  public function esiste($tabella, $id){
      $risultato = $this->esegui("SELECT :tabella FROM `%PREFIX%_interventi` WHERE id = :id;", true, [":tabella" => $tabella, ":id" => $id]);
      return !empty($risultato);
  }
  
  public function aggiungi_intervento($data, $codice, $uscita, $rientro, $capo, $autisti, $personale, $luogo, $note, $tipo, $incrementa, $inseritoda){
    $autisti = implode(",", $autisti);
    bdump($autisti);
    $personale = implode(",", $personale);
    bdump($personale);
    $incrementa = implode(",", $incrementa);
    bdump($incrementa);
    $sql = "INSERT INTO `%PREFIX%_interventi` (`id`, `data`, `codice`, `uscita`, `rientro`, `capo`, `autisti`, `personale`, `luogo`, `note`, `tipo`, `incrementa`, `inseritoda`) VALUES (NULL, :data, :codice, :uscita, :rientro, :capo, :autisti, :personale, :luogo, :note, :tipo, :incrementa, :inseritoda);
    UPDATE `%PREFIX%_profiles` SET `interventi`= interventi + 1 WHERE id IN (:incrementa);";
    $this->esegui($sql, false, [":data" => $data, ":codice" => $codice, "uscita" => $uscita, ":rientro" => $rientro, ":capo" => $capo, ":autisti" => $autisti, ":personale" => $personale, ":luogo" => $luogo, ":note" => $note, ":tipo" => $tipo, ":incrementa" => $incrementa, ":inseritoda" => $inseritoda]); // Non posso eseguire 2 query pdo con salvate le query nella classe dalla classe. Devo eseguirne 1 sola
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

  public function __construct($database, $tools){
    $this->database = $database;
    $this->tools = $tools;
    $this->auth = new \Delight\Auth\Auth($database->connection, $tools->get_ip(), DB_PREFIX."_");
    define("LOGIN", "OK");
  }

  public function autenticato(){
    return $this->auth->isLoggedIn();
  }

  public function requirelogin(){
   if(!$this->autenticato()){
      if(INTRUSION_SAVE){
        if(INTRUSION_SAVE_INFO){
          $parametri = [":pagina" => $this->tools->get_page_url(), ":ip" => $this->tools->get_ip(), ":data" => date("d/m/Y"), ":ora" => date("H:i.s"), ":servervar" => json_encode($_SERVER)];
        } else {
          $parametri = [":pagina" => $this->tools->get_page_url(), ":ip" => "redacted", ":data" => date("d/m/Y"), ":ora" => date("H:i.s"), ":servervar" => json_encode(["redacted" => "true"])];
        }
        $sql = "INSERT INTO `%PREFIX%_intrusioni` (`id`, `pagina`, `data`, `ora`, `ip`, `servervar`) VALUES (NULL, :pagina, :data, :ora, :ip, :servervar)";
        $this->database->esegui($sql, false, $parametri);
      }
      $this->tools->redirect(WEB_URL);
   }
  }

  public function requireRole($role){
    return $this->auth->hasRole($role);
  }

  public function name($replace=false){
    if(isset($_SESSION['_user_name'])){
      if($replace){
        return str_replace(" ", "_", $_SESSION['_user_name']);
      } else {
        return $_SESSION['_user_name'];
      }
    } else {
        return "non autenticato";
    }
  }
  
  public function nameById($id){
    $profiles = $this->database->esegui("SELECT `name` FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $id]);
    if(!empty($profiles)){
      if(!is_null($profiles[0]["name"])){
        return($profiles[0]["name"]);
      } else {
        $user = $this->database->esegui("SELECT `username` FROM `%PREFIX%_users` WHERE id = :id;", true, [":id" => $id]);
        if(!empty($user)){
          if(!is_null($user[0]["username"])){
            return($user[0]["username"]);
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
    $profiles = $this->database->esegui("SELECT `name` FROM `%PREFIX%_profiles` WHERE hidden = 1;", true);
    return $profiles;
  }
  
  public function avaible($name){
    $user = $this->database->esegui("SELECT avaible FROM `%PREFIX%_users` WHERE name = :name;", true, [":name" => $name]);
    if(empty($user)){
        return false;
    } else {
        return $user[0]["avaible"];
    }
  }
  
  public function info(){
    return array("id" => $this->auth->getUserId(), "name" => $this->name(), "full_viewer" => $this->requireRole(Role::FULL_VIEWER), "tester" => $this->requireRole(Role::TESTER), "developer" => $this->requireRole(Role::DEVELOPER));
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
          $user = $this->database->esegui("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $this->auth->getUserId()]);
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
  public function log($azione, $subisce, $agisce, $data, $ora){
    $parametri = [":azione" => $azione, ":subisce" => $subisce, ":agisce" => $agisce, ":data" => $data, ":ora" => $ora];
    $sql = "INSERT INTO `%PREFIX%_log` (`id`, `azione`, `subisce`, `agisce`, `data`, `ora`) VALUES (NULL, :azione, :subisce, :agisce, :data, :ora)";
    $this->database->esegui($sql, false, $parametri);
  }

  public function logout(){
    try {
      $this->auth->destroySession();
    }
    catch (\Delight\Auth\NotLoggedInException $e) {
      die('Not logged in');
    }
  }
}

function init_class(){
  global $user, $tools, $database;
  if(!isset($user) && !isset($tools) && !isset($database)){
    $tools = new tools();
    $database = new database();
    $user = new user($database, $tools);
  }
  //if($user->requireRole(Role::DEVELOPER)){
    Debugger::enable(Debugger::DEVELOPMENT, __DIR__ . '/error-log');
  //} else {
    //Debugger::enable(Debugger::PRODUCTION, __DIR__ . '/error-log');
  //}
}