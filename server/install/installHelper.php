<?php
use GetOpt\GetOpt as Getopt;
use GetOpt\Option;

function is_cli() //https://www.binarytides.com/php-check-running-cli/
{
	if( defined('STDIN') )
	{
		return true;
	}
	
	if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) 
	{
		return true;
	} 
	
	return false;
}

if (file_exists('../vendor/autoload.php')) {
    try {
        require '../vendor/autoload.php';
    } catch (Exception $e) {
        if(is_cli()){
            echo($e);
            exit(1);
        }
        die("Please install composer and run composer install (".$e);
    }
} else {
    if(is_cli()){
        echo($e);
        exit(1);
    }
    die("Please install composer and run composer install");
}

define('NAME', 'AllertaVVF');
define('VERSION', '0.1-alpha');

function checkConnection($host, $user, $password, $database, $return=false){
    try{
        $connection = new PDO("mysql:host=$host", $user, $password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $connectionOk = true;
    } catch (PDOException $e){
        if($return){
            return false;
        } else {
            if(is_cli()){
                echo($e);
                exit(8);
            }
            $connectionOk = false;
        ?>
        <div class="wp-die-message"><h1>Errore nello stabilire una connection al database</h1>
            <p>Questo potrebbe voler dire che name user e password nel file <code>config.php</code> sono sbagliate o che non possiamo contattare il database <code><?php echo $database; ?></code>. Potrebbe voler dire che il tuo database è irraddbile.</p>
            <ul>
                <li>Sei sicuro di avere name user e password corretti?</li>
                <li>Sei sicuro di aver scritto l'hostname corretto?</li>
                <li>Sei sicuro che il server del database sia attivo?</li>
            </ul>
            <p>Se non sei sicuro di cosa vogliano dire questi termini prova a contattare il tuo fornitore di hosting. Prova a fornire le seguenti informazioni:</p>
            <details>
                <summary>Informazioni avanzate</summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-2);return false;" class="button button-large">Riprova</a></p>
        </div>
        <?php
            exit();
        }
    }
    if($connectionOk){
        try{
            try{
            $connection->exec("CREATE DATABASE IF NOT EXISTS " . /*preg_replace('/[^a-zA-Z0-9]/', '', */trim($database)/*)*/);
            } catch(Exception $e) {
                //nothing
            }
        $connection->exec("use " . /*preg_replace('/[^a-zA-Z0-9]/', '', */trim($database)/*)*/);
        } catch (PDOException $e){
            if($return){
                return false;
            } else {
                if(is_cli()){
                    echo($e);
                    exit(7);
                }
            ?>
            <div class="wp-die-message"><h1>Impossibile selezionare il database</h1>
                <p>Siamo riusciti a connetterci al server del database (il che significa che il tuo name user e password sono ok), ma non siamo riusciti a selezionare il database <code><?php echo $database; ?></code>.</p>
                <ul>
                    <li>Sei sicuro che esista?</li>
                    <li>L'user <code><?php echo $user; ?></code> ha i permessi per usare il database <code><?php echo $database; ?></code>?</li>
                    <li>In alcuni sistemi il name del tuo database ha il tuo name user come prefisso, ovvero <code><?php echo $user; ?>_<?php echo $database; ?></code>. Potrebbe essere questo il problema?</li>
                </ul>
                <p>Se non sei sicuro di cosa vogliano dire questi termini prova a contattare il tuo fornitore di hosting. Prova a fornire le seguenti informazioni:</p>
                <details>
                    <summary>Informazioni avanzate</summary>
                    <pre><?php echo($e); ?></pre>
                </details>
                <p class="step"><a href="#" onclick="javascript:history.go(-2);return false;" class="button button-large">Riprova</a></p>
            </div>
            <?php
                exit();
            }
        }
        return true;
    }
}

function replaceInFile($edits,$file){
    $content = file_get_contents($file);
    foreach($edits as $edit){
        $content = str_replace($edit[0],$edit[1],$content);
    }
    file_put_contents($file,$content);
}

function generateConfig($host,$user,$password,$db,$prefix,$path=".."){
    try{
        if (file_exists($path.DIRECTORY_SEPARATOR.'config.php')) {
            rename($path.DIRECTORY_SEPARATOR."config.php", $path.DIRECTORY_SEPARATOR."config.old.php");
        }
        copy($path.DIRECTORY_SEPARATOR."config-sample.php", $path.DIRECTORY_SEPARATOR."config.php");
        replaceInFile([["@@db@@", $db],["@@user@@",$user],["@@password@@",$password],["@@host@@",$host],["@@prefix@@",$prefix]],$path.DIRECTORY_SEPARATOR."config.php");
    } catch (Exception $e) {
        if(is_cli()){
            echo($e);
            exit(6);
        }
        ?>
        <div class="wp-die-message"><h1>Impossibile editre il file di configurazioni</h1>
            <p>Non siamo riusciti a scrivere il file di configurazione <code>config.php</code>, richiesto per il funzionamento del programma.<br>E' tuttavia possibile editrlo manualmente, seguentdo le seguenti istruzioni:</p>
            <ul>
                <li>Accedere alla cartella di installazione di allerta (connettersi via FTP in caso di server sul cloud).</li>
                <li>Rinominare il file <code>config-sample.php</code> in <code>config.php</code>.</li>
                <li>Edit le prime 16 righe del file con il seguente testo:</li>
<code>
&lt;?php<br>
// ** Database settings ** //<br>
/* The name of the database for Allerta-vvf */<br>
define( 'DB_NAME', '<?php echo $db; ?>' );<br>
<br>
/* Database username */<br>
define( 'DB_USER', '<?php echo $user; ?>' );<br>
<br>
/* Database password */<br>
define( 'DB_PASSWORD', '<?php echo $password; ?>' );<br>
<br>
/* Database hostname */<br>
define( 'DB_HOST', '<?php echo $host; ?>' );<br>
<br>
/* Database hostname */<br>
define( 'DB_PREFIX', '<?php echo $prefix; ?>' );<br>
</code>
            </ul>
            <p>Se non sei sicuro di cosa vogliano dire questi termini prova a contattare il tuo fornitore di hosting. Prova a fornire le seguenti informazioni:</p>
            <details>
                <summary>Informazioni avanzate</summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-1);return false;" class="button button-large">Riprova</a></p>
        </div>
        <?php
        exit();
    }
}

function initDB(){
    try{
        $connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $prefix = DB_PREFIX;
        $connection->exec("
CREATE TABLE IF NOT EXISTS `".$prefix."_certificati` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`codice` text NOT NULL,
`name` text NOT NULL,
`services` text NOT NULL,
`url` text NOT NULL,
`file` text NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_trainings` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`data` date NOT NULL,
`name` varchar(999) NOT NULL,
`inizio` time NOT NULL,
`fine` time NOT NULL,
`personale` text NOT NULL,
`capo` text NOT NULL,
`luogo` text NOT NULL,
`note` text NOT NULL,
`incrementa` varchar(999) NOT NULL DEFAULT 'test',
`inseritoda` varchar(200) NOT NULL DEFAULT 'test',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_services` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`data` date NOT NULL,
`codice` text NOT NULL,
`uscita` time NOT NULL,
`rientro` time NOT NULL,
`capo` varchar(999) NOT NULL DEFAULT 'test',
`autisti` varchar(999) NOT NULL DEFAULT 'test',
`personale` varchar(999) NOT NULL DEFAULT 'test',
`luogo` varchar(999) NOT NULL DEFAULT 'test',
`note` varchar(999) NOT NULL DEFAULT 'test',
`tipo` text NOT NULL,
`incrementa` varchar(999) NOT NULL,
`inseritoda` varchar(200) NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_intrusions` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`pagina` varchar(999) COLLATE utf8mb4_unicode_ci NOT NULL,
`data` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`ora` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`servervar` varchar(9999) COLLATE utf8mb4_unicode_ci NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_log` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`action` varchar(100) NOT NULL,
`changed` varchar(100) NOT NULL,
`editor` varchar(100) NOT NULL,
`date` varchar(100) NOT NULL,
`time` varchar(100) NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_minuti` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`mese` enum('gennaio','febbraio','marzo','aprile','maggio','giugno','luglio','agosto','settembre','ottobre','novembre','dicembre') NOT NULL,
`anno` varchar(4) NOT NULL,
`list` mediumtext NOT NULL,
`a1` mediumtext NOT NULL,
`a2` mediumtext NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_tipo` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `nametipologia` (`name`(99))
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_users` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
`password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`status` tinyint(2) unsigned NOT NULL DEFAULT '0',
`verified` tinyint(1) unsigned NOT NULL DEFAULT '0',
`resettable` tinyint(1) unsigned NOT NULL DEFAULT '1',
`roles_mask` int(10) unsigned NOT NULL DEFAULT '0',
`registered` int(10) unsigned NOT NULL,
`last_login` int(10) unsigned DEFAULT NULL,
`force_logout` mediumint(7) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `Id` (`id`),
UNIQUE KEY `email` (`email`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_profiles` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`hidden` BOOLEAN NOT NULL DEFAULT FALSE,
`disabled` BOOLEAN NOT NULL DEFAULT FALSE,
`name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`available` tinyint(1) NOT NULL DEFAULT 0,
`caposquadra` tinyint(1) NOT NULL DEFAULT 0,
`autista` tinyint(1) NOT NULL DEFAULT 0,
`telefono` varchar(25) DEFAULT NULL,
`services` int(11) NOT NULL DEFAULT 0,
`trainings` int(11) NOT NULL DEFAULT 0,
`online` tinyint(1) NOT NULL DEFAULT 0,
`online_time` int(11) NOT NULL DEFAULT 0,
`minuti_dispo` int(11) NOT NULL DEFAULT 0,
`immagine` varchar(1000) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `Id` (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_users_confirmations` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`user_id` int(10) unsigned NOT NULL,
`email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
`selector` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`expires` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `selector` (`selector`),
KEY `email_expires` (`email`,`expires`),
KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `".$prefix."_users_remembered` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`user` int(10) unsigned NOT NULL,
`selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`expires` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `selector` (`selector`),
KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `".$prefix."_users_resets` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`user` int(10) unsigned NOT NULL,
`selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`expires` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `selector` (`selector`),
KEY `user_expires` (`user`,`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `".$prefix."_users_throttling` (
`bucket` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`tokens` float unsigned NOT NULL,
`replenished_at` int(10) unsigned NOT NULL,
`expires_at` int(10) unsigned NOT NULL,
PRIMARY KEY (`bucket`),
KEY `expires_at` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `".$prefix."_options` (
`id` INT NOT NULL AUTO_INCREMENT,
`name` TEXT NOT NULL, `value` MEDIUMTEXT NOT NULL,
`enabled` BOOLEAN NOT NULL DEFAULT TRUE,
`created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`last_edit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`user_id` INT NOT NULL,
PRIMARY KEY (`id`),
KEY `Id` (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `".$prefix."_dbversion` (
`id` INT NOT NULL AUTO_INCREMENT,
`version` INT NOT NULL,
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
PRIMARY KEY (`id`),
KEY `Id` (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `".$prefix."_dbversion` (`version`, `timestamp`) VALUES('1', current_timestamp());
INSERT INTO `".$prefix."_tipo` (`id`, `name`) VALUES (NULL, 'type1'), (NULL, 'type2');");
    } catch (Exception $e) {
        if(is_cli()){
            echo($e);
            exit(10);
        }
        ?>
        <div class="wp-die-message"><h1>Impossibile creare le tabelle</h1>
            <p>Siamo riusciti a connetterci al server del database (il che significa che il tuo name user e password sono ok), ma non siamo riusciti a creare le tabelle.</p>
            <p>Se non sei sicuro di cosa vogliano dire questi termini prova a contattare il tuo fornitore di hosting. Prova a fornire le seguenti informazioni:</p>
            <details>
                <summary>Informazioni avanzate</summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-1);return false;" class="button button-large">Riprova</a></p>
        </div>
        <?php
        exit();
    }
}

final class Role {
    //https://github.com/delight-im/PHP-Auth/blob/master/src/Role.php
    const SUPER_ADMIN = \Delight\Auth\Role::SUPER_ADMIN;
  
    public function __construct() {}
  
}

function full_path()
{
    $s = &$_SERVER;
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    $uri = $protocol . '://' . $host . $s['REQUEST_URI'];
    $segments = explode('?', $uri, 2);
    $url = $segments[0];
    return $url;
}

function initOptions($name, $visible, $password, $report_email, $owner){
    try{
        require_once "../config.php";
        $connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $prefix = DB_PREFIX;
        $auth = new \Delight\Auth\Auth($connection, $_SERVER['REMOTE_ADDR'], $prefix."_");
        $userId = $auth->register($report_email, $password, $name);
        $auth->admin()->addRoleForUserById($userId, Role::SUPER_ADMIN);
        $option_check_cf_ip = empty($_SERVER['HTTP_CF_CONNECTING_IP']) ? "INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('10', 'check_cf_ip', 1, '1', current_timestamp(), current_timestamp(), '1');" : "INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('10', 'check_cf_ip', 0, '1', current_timestamp(), current_timestamp(), '1');";
        $prep = $connection->prepare("
INSERT INTO `".$prefix."_profiles` (`id`, `hidden`) VALUES (NULL, :hidden);
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('1', 'report_email', :report_email, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('2', 'owner', :owner, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('3', 'web_url', :web_url, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('4', 'use_custom_error_sound', 0, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('5', 'use_custom_error_image', 0, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('6', 'intrusion_save', 1, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('7', 'intrusion_save_info', 1, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('8', 'enable_technical_support', 0, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('9', 'technical_support_key', '', '1', current_timestamp(), current_timestamp(), '1');
$option_check_cf_ip");
        $prep->bindValue(':hidden', ($visible ? 0 : 1), PDO::PARAM_INT);        
        $prep->bindValue(':report_email', $report_email, PDO::PARAM_STR);
        $prep->bindValue(':owner', $owner, PDO::PARAM_STR);
        $prep->bindValue(':web_url', str_replace("install/install.php", "", full_path()), PDO::PARAM_STR);
        $prep->execute();
    } catch (Exception $e) {
        if(is_cli()){
            echo($e);
            exit(11);
        }
        ?>
        <div class="wp-die-message"><h1>Impossibile riempire le tabelle</h1>
            <p>Siamo riusciti a connetterci al server del database (il che significa che il tuo name user e password sono ok), ma non siamo riusciti a riempire le tabelle.</p>
            <p>Se non sei sicuro di cosa vogliano dire questi termini prova a contattare il tuo fornitore di hosting. Prova a fornire le seguenti informazioni:</p>
            <details>
                <summary>Informazioni avanzate</summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-1);return false;" class="button button-large">Riprova</a></p>
        </div>
        <?php
        exit();
    }
}

function validate_arg($options, $name, $default){
    return array_key_exists($name, $options) ? $options[$name] : (getenv($name)!==false ? getenv($name) : (getenv(strtoupper($name))!==false ? getenv(strtoupper($name)) : $default));
}

function change_dir($directory){
    try{
        chdir($directory);
    } catch(Exception $e){
        if(is_cli()){
            exit(4);
        }
    }
}
function cli_helper($action, $options){
    switch ($action) {
        case "config":
            $db_name = validate_arg($options, "db_name", "allerta");
            $db_username = validate_arg($options, "db_username", "root");
            $db_password = validate_arg($options, "db_password", "");
            $db_host = validate_arg($options, "db_host", "127.0.0.1");
            $db_prefix = validate_arg($options, "db_prefix", "allerta");
            $path = isset($options->getOptions["path"]) ? "." : "..";
            checkConnection($db_host, $db_username, $db_password, $db_name);
            generateConfig($db_host,$db_username,$db_password,$db_name,$db_prefix,$path);
            echo("Config created successful");
            exit(0);
        case "populate":
            $name = validate_arg($options, "name", "admin");
            $visible = array_key_exists("visible", $options);
            $password = validate_arg($options, "password", "password");
            $report_email = validate_arg($options, "report_email", "postmaster@localhost.local");
            $owner = validate_arg($options, "owner", "Owner");
            initDB();
            initOptions($name, $visible, $password, $report_email, $owner);
            echo("DB Populated successful");
            unlink("runInstall.php");
            exit(0);
    }
}
function run_cli(){
    $_SERVER['REMOTE_ADDR'] = "127.0.0.1";
    $getopt = new \GetOpt\GetOpt();
    $getopt->addCommands([ 
        \GetOpt\Command::create('config', 'conf', [
            \GetOpt\Option::create('n', 'db_name', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('DB name')
                ->setArgumentName('DB name'),
            \GetOpt\Option::create('u', 'db_username', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('DB username')
                ->setArgumentName('DB username'),
            \GetOpt\Option::create('a', 'db_password', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('DB password')
                ->setArgumentName('DB password'),
            \GetOpt\Option::create('o', 'db_host', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('DB host')
                ->setArgumentName('DB host'),
            \GetOpt\Option::create('r', 'db_prefix', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('DB prefix')
                ->setArgumentName('DB prefix')
        ])->setDescription(
            'Creates the config file "config.php".' . PHP_EOL .
            PHP_EOL .
            'This file is required for running "populate".'
        )->setShortDescription('Create a new config file'),

        \GetOpt\Command::create('populate', 'Populate', [
            \GetOpt\Option::create('m', 'name', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('Admin name')
                ->setArgumentName('Admin name'),
            \GetOpt\Option::create('b', 'visible', \GetOpt\GetOpt::NO_ARGUMENT)
                ->setDescription('Is admin visible?')
                ->setArgumentName('Is admin visible?'),
            \GetOpt\Option::create('s', 'password', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('Admin password')
                ->setArgumentName('Admin password'),
            \GetOpt\Option::create('w', 'owner', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('Owner')
                ->setArgumentName('Owner'),
            \GetOpt\Option::create('e', 'report_email', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('Report email')
                ->setArgumentName('Report email')
        ])->setDescription(
            'Populate Allerta database.' . PHP_EOL .
            PHP_EOL .
            'This require a working config.php file.'
        )->setShortDescription('Populate DB')
    ]);

    $getopt->addOptions([
        Option::create('v', 'version', \GetOpt\GetOpt::NO_ARGUMENT)
            ->setDescription('Show version information and quit'),
        
        Option::create('h', 'help', \GetOpt\GetOpt::NO_ARGUMENT)
            ->setDescription('Show this help and quit'),

        Option::create("p", 'path', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
            ->setDescription('Destination path')
            ->setArgumentName('path')
            ->setValidation('is_writable', function($operand, $value) {
                if(file_exists($value)){
                    echo($value . ' is not writable. Directory permissions: ' . @fileperms($value));
                    exit(4);
                } else {
                    echo($value . ' not exists.');
                    exit(3);
                }
            })
    ]);

    // process arguments and catch user errors
    try {
        try {
            $getopt->process();
        } catch (Missing $exception) {
            // catch missing exceptions if help is requested
        if (!$getopt->getOption('help')) {
            throw $exception;
        }
    }
    } catch (ArgumentException $exception) {
        file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
        echo PHP_EOL . $getopt->getHelpText();
        exit;
    }

    // show version and quit
    if ($getopt->getOption('version')) {
        echo sprintf('%s: %s' . PHP_EOL, NAME, VERSION);
        exit;
    }

    // show help and quit
    $command = $getopt->getCommand();
    if (!$command || $getopt->getOption('help')) {
        echo $getopt->getHelpText();
        exit;
    }

    if (isset($getopt->getOptions()["path"])) {
        chdir($getopt->getOption('path'));
    }

    $options = $getopt->getOptions();
    switch ($command->name()) {
        case "config":
            cli_helper("config", $options);
        case "populate":
            cli_helper("populate", $options);
    }
}