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
    UPDATE `%PREFIX%_users` SET `interventi`= interventi + 1 WHERE id IN (:incrementa);";
    $this->esegui($sql, false, [":data" => $data, ":codice" => $codice, "uscita" => $uscita, ":rientro" => $rientro, ":capo" => $capo, ":autisti" => $autisti, ":personale" => $personale, ":luogo" => $luogo, ":note" => $note, ":tipo" => $tipo, ":incrementa" => $incrementa, ":inseritoda" => $inseritoda]); // Non posso eseguire 2 query pdo con salvate le query nella classe dalla classe. Devo eseguirne 1 sola
  }
}

class user{
  private $database = null;
  private $tools = null;

  public function __construct($database, $tools){
    $this->database = $database;
    $this->tools = $tools;
    define("LOGIN", "OK");
  }

  public function autenticato(){
    if(isset($_SESSION['accesso'])){
        return true;
    } else {
        return false;
    }
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

  public function admin(){
    if(isset($_SESSION['admin'])){
    if($_SESSION['admin'] == 1){
        return true;
    } else {
        return false;
    }
  } else {
    return false;
  }
  }
  public function nome($replace=false){
    if(isset($_SESSION['nome'])){
      if($replace){
        return str_replace(" ", "_", $_SESSION['nome']);
      } else {
        return $_SESSION['nome'];
      }
    } else {
        return "non autenticato";
    }
  }
  
  public function nome_by_id($id){
    $user = $this->database->esegui("SELECT nome FROM `%PREFIX%_users` WHERE id = :id;", true, [":id" => $id]);
    if(empty($user)){
        return false;
    } else {
        return $user[0]["nome"];
    }
  }
  
  public function avaible($nome){
    $user = $this->database->esegui("SELECT avaible FROM `%PREFIX%_users` WHERE nome = :nome;", true, [":nome" => $nome]);
    if(empty($user)){
        return false;
    } else {
        return $user[0]["avaible"];
    }
  }
  
  public function whitelist($array = true, $str = ", "){
    $array_data = array("test", "test2", "test3");
    if($array){
      return $array_data;
    } else if(!$array){
      return implode((string) $str, $array_data);
    }
  }
  public function info(){
    return array("nome" => $this->nome(), "admin" => $this->admin(), "codice" => "TODO", "tester" => $this->tester());
  }

  public function tester($nome="questo"){
    if($nome=="questo"){
      $nome = $this->nome();
    }
    if(in_array($nome, $this->whitelist())){
      return true;
    } else {
      return false;
    }
  }

  public function dev($nome="questo"){
    if($nome=="questo"){
      $nome = $this->nome();
    }
    if(in_array($nome, $this->whitelist())){
      return true;
    } else {
      return false;
    }
  }

  public function login($nome, $password, $twofa=null){
    if(!empty($nome)){
      if(!empty($password)){
        $users = $this->database->esegui("SELECT * FROM `%PREFIX%_users` WHERE nome = :nome AND password = :password;", true, [":nome" => $nome, ":password" => $password]);
        if(!empty($users)){
          $_SESSION["accesso"] = "autenticato";
          $_SESSION["nome"] = $users[0]["nome"];
          $_SESSION["admin"] = $users[0]["caposquadra"];
          return true;
          //return $users;
        } else {
          return ["status" => "errore", "codice" => 003, "spiegazione" => "Dati di login non corretti"];
        }
      } else {
        return ["status" => "errore", "codice" => 002];
      }
    } else {
      return ["status" => "errore", "codice" => 001];
    }
  }
  public function log($azione, $subisce, $agisce, $data, $ora){
    $parametri = [":azione" => $azione, ":subisce" => $subisce, ":agisce" => $agisce, ":data" => $data, ":ora" => $ora];
    $sql = "INSERT INTO `%PREFIX%_log` (`id`, `azione`, `subisce`, `agisce`, `data`, `ora`) VALUES (NULL, :azione, :subisce, :agisce, :data, :ora)";
    $this->database->esegui($sql, false, $parametri);
  }

  public function lista($tutti=false){
    $users = $this->database->esegui("SELECT * FROM `%PREFIX%_users`;", true);
  }

  public function logout(){
    unset($_SESSION["accesso"]);
    unset($_SESSION["nome"]);
    unset($_SESSION["admin"]);
  }
}

function init_class(){
  global $utente, $tools, $database;
  if(!isset($utente) && !isset($tools) && !isset($database)){
    $tools = new tools();
    $database = new database();
    $utente = new user($database, $tools);
  }
  if($utente->dev()){
    Debugger::enable(Debugger::DEVELOPMENT, __DIR__ . '/error-log');
  } else {
    Debugger::enable(Debugger::PRODUCTION, __DIR__ . '/error-log');
  }
}