<?php
use GetOpt\GetOpt as Getopt;
use GetOpt\Option;

function finalInstallationHelperStep(){
    $logopngPath = "../resources/images/";
    unlink("runInstall.php");
    if(file_exists("../options.txt")){
        unlink("../options.txt");
    }
    if(file_exists($logopngPath."logo_sample.png") && !file_exists($logopngPath."logo.png")){
        copy($logopngPath."logo_sample.png", $logopngPath."logo.png");
    }
    if(file_exists($logopngPath."owner_sample.png") && !file_exists($logopngPath."owner.png")){
        copy($logopngPath."owner_sample.png", $logopngPath."owner.png");
    }
}

function client_languages()
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

$client_languages = client_languages();
$loaded_languages = ["en", "it"];
$default_language = "en";
$language = null;
foreach($client_languages as $tmp_language){
    if(in_array($tmp_language, $loaded_languages) && $language == null) {
        $language = $tmp_language;
    }
}
if(isset($_COOKIE["forceLanguage"]) && in_array($_COOKIE["forceLanguage"], $loaded_languages)){
    $language = $_COOKIE["forceLanguage"];
}
if (file_exists("translations/".$language.".php")) {
    $loaded_translations = include "translations/".$language.".php";
} else {
    $loaded_translations = include "translations/en.php";
}

function t($string, $echo=true)
{
    global $loaded_translations;
    try {
        if (!array_key_exists($string, $loaded_translations)) {
            throw new Exception('string does not exist');
        }
        $string = $loaded_translations[$string];
    } catch (\Exception $e) {
        //nothing
    }
    if ($echo) {
        echo $string;
    } else {
        return $string;
    }
}

function is_cli() //https://www.binarytides.com/php-check-running-cli/
{
    if(defined('STDIN') ) {
        return true;
    }

    if(empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
        return true;
    }

    return false;
}

if (file_exists('../vendor/autoload.php')) {
    try {
        include '../vendor/autoload.php';
    } catch (Exception $e) {
        if(is_cli()) {
            echo($e);
            exit(1);
        }
        die("Please install composer and run composer install (".$e);
    }
} else {
    if(is_cli()) {
        echo($e);
        exit(1);
    }
    die("Please install composer and run composer install");
}

function checkConnection($host, $user, $password, $database, $return=false)
{
    try{
        $connection = new PDO("mysql:host=$host", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $connectionOk = true;
    } catch (PDOException $e){
        if($return) {
            return false;
        } else {
            if(is_cli()) {
                echo($e);
                exit(8);
            }
            $connectionOk = false;
            ?>
        <div class="wp-die-message"><h1><?php t("Error establishing a database connection"); ?></h1>
            <p><?php printf(t("This could mean that %s and %s in file %s are wrong or that we cannot contact database %s. It could mean that your database is unreachable", false), t("DB username", false), t("DB password", false), "<code>config.php</code>", "<code>$database</code>"); ?>.</p>
            <ul>
                <li><?php printf(t("Are you sure that %s and %s correct?", false), t("DB username", false), t("DB password", false)); ?></li>
                <li><?php t("Are you sure you have entered the correct hostname?"); ?></li>
                <li><?php t("Are you sure the database server is up and running?"); ?></li>
            </ul>
            <p><?php t("If you're not sure what these terms mean, try contacting your hosting provider. Try providing the following information:"); ?></p>
            <details open>
                <summary><?php t("Advanced informations"); ?></summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-2);return false;" class="button button-large"><?php t("Try again"); ?></a></p>
        </div>
            <?php
            exit();
        }
    }
    if($connectionOk) {
        try{
            try{
                $connection->exec("CREATE DATABASE IF NOT EXISTS " . trim($database));
            } catch(Exception $e) {
                //nothing
            }
            $connection->exec("use " . trim($database));
        } catch (PDOException $e){
            if($return) {
                return false;
            } else {
                if(is_cli()) {
                    echo($e);
                    exit(7);
                }
                ?>
            <div class="wp-die-message"><h1><?php t("Cannot select database"); ?></h1>
                <p><?php t("We were able to connect to the database server (which means your username and password are ok)"); echo(", "); t("but we could not select the database"); ?> <code><?php echo $database; ?></code>.</p>
                <ul>
                    <li><?php t("Are you sure that it exists?"); ?></li>
                    <li><?php printf(t("Does user %s have permissions to use database %s?", false), "<code>$user</code>", "<code>$database</code>"); ?></li>
                    <li><?php printf(t("In some systems your database name has your username as a prefix, which is %s. Could this be the problem?", false), "<code>".$user."_".$database."</code>"); ?> </li>
                </ul>
                <p><?php t("If you're not sure what these terms mean, try contacting your hosting provider. Try providing the following information:"); ?></p>
                <details open>
                    <summary><?php t("Advanced informations"); ?></summary>
                    <pre><?php echo($e); ?></pre>
                </details>
                <p class="step"><a href="#" onclick="javascript:history.go(-2);return false;" class="button button-large"><?php t("Try again"); ?></a></p>
            </div>
                <?php
                exit();
            }
        }
        return true;
    }
}

function replaceInFile($edits,$file)
{
    $content = file_get_contents($file);
    foreach($edits as $edit){
        $content = str_replace($edit[0], $edit[1], $content);
    }
    file_put_contents($file, $content);
}

function generateConfig($host,$user,$password,$db,$prefix,$path="..")
{
    try{
        if (file_exists($path.DIRECTORY_SEPARATOR.'config.php')) {
            rename($path.DIRECTORY_SEPARATOR."config.php", $path.DIRECTORY_SEPARATOR."config.old.php");
        }
        copy($path.DIRECTORY_SEPARATOR."config-sample.php", $path.DIRECTORY_SEPARATOR."config.php");
        replaceInFile([["@@db@@", $db],["@@user@@",$user],["@@password@@",$password],["@@host@@",$host],["@@prefix@@",$prefix]], $path.DIRECTORY_SEPARATOR."config.php");
    } catch (Exception $e) {
        if(is_cli()) {
            echo($e);
            exit(6);
        }
        ?>
        <div class="wp-die-message"><h1></h1>
            <p><?php printf(t("We were unable to write the configuration file %s, which is required for the program to work", false), "<code>config.php</code>"); echo ".<br>"; t("It is however possible to edit it manually by following the instructions below:"); ?></p>
            <ul>
                <li><?php t("Access the Allerta installation folder (connect via FTP in case of cloud server)"); ?>.</li>
                <li><?php printf(t("Rename the file %s to %s", false), "<code>config-sample.php</code>", "<code>config.php</code>"); ?>.</li>
                <li><?php t("Replace the first 16 lines of the file with the following text:"); ?></li>
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
<br>
/* Sentry options */<br>
define('SENTRY_CSP_REPORT_URI', '');<br>
define('SENTRY_ENABLED', false);<br>
define('SENTRY_DSN', '');<br>
define('SENTRY_ENV', 'prod');<br>
</code>
            </ul>
            <p><?php t("If you're not sure what these terms mean, try contacting your hosting provider. Try providing the following information:"); ?></p>
            <details open>
                <summary><?php t("Advanced informations"); ?></summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-1);return false;" class="button button-large"><?php t("Try again"); ?></a></p>
        </div>
        <?php
        exit();
    }
}

function initDB()
{
    try{
        $db = \Delight\Db\PdoDatabase::fromDsn(
            new \Delight\Db\PdoDsn(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME,
                DB_USER,
                DB_PASSWORD
            )
        );
        $prefix = DB_PREFIX;
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_trainings` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`date` date NOT NULL,
`name` varchar(999) NOT NULL,
`beginning` time NOT NULL,
`end` time NOT NULL,
`crew` text NOT NULL,
`chief` text NOT NULL,
`place` text NOT NULL,
`notes` text NOT NULL,
`increment` varchar(999) NOT NULL,
`inserted_by` varchar(200) NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_services` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`date` date NOT NULL,
`code` text NOT NULL,
`beginning` time NOT NULL,
`end` time NOT NULL,
`chief` varchar(999) NOT NULL,
`drivers` varchar(999) NOT NULL,
`crew` varchar(999) NOT NULL,
`place` varchar(999) NOT NULL,
`notes` varchar(999) NOT NULL,
`type` text NOT NULL,
`increment` varchar(999) NOT NULL,
`inserted_by` varchar(200) NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_intrusions` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`page` varchar(999) COLLATE utf8mb4_unicode_ci NOT NULL,
`date` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`hour` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`server_var` varchar(9999) COLLATE utf8mb4_unicode_ci NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_log` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`action` varchar(100) NOT NULL,
`changed` varchar(100),
`editor` varchar(100),
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`ip` varchar(100),
`source_type` varchar(4),
`user_agent` varchar(500),
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_minutes` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`month` int(2) NOT NULL,
`year` int(2) NOT NULL,
`list` mediumtext NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_type` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `type_name` (`name`(99))
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_users` (
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
UNIQUE KEY `email` (`email`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_profiles` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`hidden` BOOLEAN NOT NULL DEFAULT FALSE,
`disabled` BOOLEAN NOT NULL DEFAULT FALSE,
`name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`available` tinyint(1) NOT NULL DEFAULT 0,
`availability_last_change` varchar(1000) DEFAULT NULL,
`chief` tinyint(1) NOT NULL DEFAULT 0,
`driver` tinyint(1) NOT NULL DEFAULT 0,
`phone_number` varchar(25) DEFAULT NULL,
`services` int(11) NOT NULL DEFAULT 0,
`trainings` int(11) NOT NULL DEFAULT 0,
`online_time` int(11) NOT NULL DEFAULT 0,
`availability_minutes` int(11) NOT NULL DEFAULT 0,
`image` varchar(1000) DEFAULT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_users_confirmations` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_users_remembered` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`user` int(10) unsigned NOT NULL,
`selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`expires` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `selector` (`selector`),
KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_users_resets` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`user` int(10) unsigned NOT NULL,
`selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`expires` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `selector` (`selector`),
KEY `user_expires` (`user`,`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_users_throttling` (
`bucket` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
`tokens` float unsigned NOT NULL,
`replenished_at` int(10) unsigned NOT NULL,
`expires_at` int(10) unsigned NOT NULL,
PRIMARY KEY (`bucket`),
KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE IF NOT EXISTS `{$prefix}_options` (
`id` INT NOT NULL AUTO_INCREMENT,
`name` TEXT NOT NULL, `value` MEDIUMTEXT NOT NULL,
`enabled` BOOLEAN NOT NULL DEFAULT TRUE,
`created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`last_edit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`user_id` INT NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE `{$prefix}_dbversion` (
`id` INT NOT NULL AUTO_INCREMENT,
`version` INT NOT NULL,
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE `{$prefix}_api_keys` (
`id` INT NOT NULL AUTO_INCREMENT,
`apikey` VARCHAR(128) NOT NULL,
`user` INT NOT NULL,
`permissions` VARCHAR(128) NOT NULL DEFAULT 'ALL',
PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE `{$prefix}_bot_telegram` (
`id` INT NOT NULL AUTO_INCREMENT,
`chat_id` VARCHAR(128) NOT NULL,
`user` INT NOT NULL,
PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec(<<<"EOL"
CREATE TABLE `{$prefix}_schedules` (
`id` INT NOT NULL AUTO_INCREMENT,
`user` INT NOT NULL,
`profile_name` VARCHAR(500) NOT NULL DEFAULT 'default',
`schedules` VARCHAR(10000) NULL DEFAULT NULL,
`holidays` VARCHAR(10000) NULL DEFAULT NULL,
`last_exec` VARCHAR(7) NULL DEFAULT NULL,
`last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=latin1;
EOL);
$db->exec("INSERT INTO `{$prefix}_dbversion` (`version`, `timestamp`) VALUES('1', current_timestamp());");
    } catch (Exception $e) {
        if(is_cli()) {
            echo($e);
            exit(10);
        }
        ?>
        <div class="wp-die-message"><h1><?php t("Unable to create tables"); ?></h1>
            <p><?php t("We were able to connect to the database server (which means your username and password are ok)"); echo(", "); t("but we were unable to create the tables"); ?>.</p>
            <p><?php t("If you're not sure what these terms mean, try contacting your hosting provider. Try providing the following information:"); ?></p>
            <details open>
                <summary><?php t("Advanced informations"); ?></summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-1);return false;" class="button button-large"><?php t("Try again"); ?></a></p>
        </div>
        <?php
        exit();
    }
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

function initOptions($name, $visible, $developer, $password, $report_email, $owner, $url=null)
{
    try{
        include_once "../config.php";
        $db = \Delight\Db\PdoDatabase::fromDsn(
            new \Delight\Db\PdoDsn(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME,
                DB_USER,
                DB_PASSWORD
            )
        );
        $prefix = DB_PREFIX;
        $auth = new \Delight\Auth\Auth($db, $_SERVER['REMOTE_ADDR'], $prefix."_");
        $userId = $auth->register($report_email, $password, $name);
        $auth->admin()->addRoleForUserById($userId, \Delight\Auth\Role::SUPER_ADMIN);
        if($developer) {
            $auth->admin()->addRoleForUserById($userId, \Delight\Auth\Role::DEVELOPER);
        }
        if(is_null($url)){
            $url = str_replace("install/install.php", "", full_path());
        }
        $options = [
            'check_cf_ip' => empty($_SERVER['HTTP_CF_CONNECTING_IP']) ? 0 : 1,
            'report_email' => $report_email,
            'owner' => $owner,
            'web_url' => $url,
            'use_custom_error_sound' => 0,
            'use_custom_error_image' => 0,
            'intrusion_save' => 1,
            'intrusion_save_info' => 0,
            'log_save_ip' => 1,
            'cron_job_code' => str_replace(".", "", bin2hex(random_bytes(10)).base64_encode(openssl_random_pseudo_bytes(30))),
            'cron_job_enabled' => 1,
            'cron_job_time' => '01;00:00',
            'service_edit' => 1,
            'service_remove' => 1,
            'training_edit' => 1,
            'training_remove' => 1,
            'use_location_picker' => 1,
            'generate_map_preview' => 1,
            'map_preview_generator_add_marker' => 1,
            'map_preview_generator' => 'osm', //[osm, custom]
            'map_preview_generator_url' => '', //not required for osm
            'map_preview_generator_url_marker' => '', //not required for osm
            'force_language' => 0,
            'force_remember_cookie' => 0,
            'holidays_provider' => 'USA',
            'holidays_language' => 'en_US',
            'messages' => '{}'
        ];
        foreach ($options as $key => $value) {
            $db->insert(
                $prefix."_options",
                ["name" => $key, "value" => $value, "enabled" => 1, "user_id" => 1]
            );
        }
        $db->insert(
            $prefix."_profiles",
            ["hidden" => $visible ? 0 : 1]
        );
    } catch (Exception $e) {
        if(is_cli()) {
            echo($e);
            exit(11);
        }
        ?>
        <div class="wp-die-message"><h1><?php t("Unable to fill in the tables"); ?></h1>
            <p><?php t("We were able to connect to the database server (which means your username and password are ok)"); echo(", "); t("but we were unable to fill in the tables"); ?>.</p>
            <p><?php t("If you're not sure what these terms mean, try contacting your hosting provider. Try providing the following information:"); ?></p>
            <details open>
                <summary><?php t("Advanced informations"); ?></summary>
                <pre><?php echo($e); ?></pre>
            </details>
            <p class="step"><a href="#" onclick="javascript:history.go(-1);return false;" class="button button-large"><?php t("Try again"); ?></a></p>
        </div>
        <?php
        exit();
    }
}

function validate_arg($options, $name, $default)
{
    return array_key_exists($name, $options) ? $options[$name] : (getenv($name)!==false ? getenv($name) : (getenv(strtoupper($name))!==false ? getenv(strtoupper($name)) : $default));
}

function change_dir($directory)
{
    try{
        chdir($directory);
    } catch(Exception $e){
        if(is_cli()) {
            exit(4);
        }
    }
}
function cli_helper($action, $options)
{
    switch ($action) {
    case "config":
        $db_name = validate_arg($options, "db_name", "allerta");
        $db_username = validate_arg($options, "db_username", "root");
        $db_password = validate_arg($options, "db_password", "");
        $db_host = validate_arg($options, "db_host", "127.0.0.1");
        $db_prefix = validate_arg($options, "db_prefix", "allerta");
        $path = isset($options->getOptions["path"]) ? "." : "..";
        checkConnection($db_host, $db_username, $db_password, $db_name);
        generateConfig($db_host, $db_username, $db_password, $db_name, $db_prefix, $path);
        t("Config created successful");
        exit(0);
    case "populate":
        $name = validate_arg($options, "name", "admin");
        $visible = array_key_exists("visible", $options);
        $developer = array_key_exists("developer", $options);
        $password = validate_arg($options, "password", "password");
        $report_email = validate_arg($options, "report_email", "postmaster@localhost.local");
        $owner = validate_arg($options, "owner", "Owner");
        $url = validate_arg($options, "url", "htp://localhost/");
        initDB();
        initOptions($name, $visible, $developer, $password, $report_email, $owner, $url);
        t("DB Populated successful");
        finalInstallationHelperStep();
        exit(0);
    }
}
function run_cli()
{
    $_SERVER['REMOTE_ADDR'] = "127.0.0.1";
    $getopt = new \GetOpt\GetOpt();
    $getopt->addCommands(
        [
        \GetOpt\Command::create(
            'config', 'conf', [
            \GetOpt\Option::create('n', 'db_name', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("DB name", false))
                ->setArgumentName(t("DB name", false)),
            \GetOpt\Option::create('u', 'db_username', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("DB username", false))
                ->setArgumentName(t("DB username", false)),
            \GetOpt\Option::create('a', 'db_password', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("DB password", false))
                ->setArgumentName(t("DB password", false)),
            \GetOpt\Option::create('o', 'db_host', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("DB host", false))
                ->setArgumentName(t("DB host", false)),
            \GetOpt\Option::create('r', 'db_prefix', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("DB prefix", false))
                ->setArgumentName(t("DB prefix", false))
            ]
        )->setDescription(
            t("Create the config file", false).' "config.php".' . PHP_EOL .
                PHP_EOL .
                sprintf(t("This file is required for running %s", false), '"populate"') . "."
        )->setShortDescription(t("Create a new config file", false)),

        \GetOpt\Command::create(
            'populate', 'Populate', [
            \GetOpt\Option::create('m', 'name', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("Admin name", false))
                ->setArgumentName(t("Admin name", false)),
            \GetOpt\Option::create('b', 'visible', \GetOpt\GetOpt::NO_ARGUMENT)
                ->setDescription(t("Is admin visible?", false))
                ->setArgumentName(t("Is admin visible?", false)),
            \GetOpt\Option::create('d', 'developer', \GetOpt\GetOpt::NO_ARGUMENT)
                ->setDescription(t("Enable devmode per the user", false))
                ->setArgumentName(t("Enable devmode per the user", false)),
            \GetOpt\Option::create('s', 'password', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("Admin password", false))
                ->setArgumentName(t("Admin password", false)),
            \GetOpt\Option::create('w', 'owner', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("Owner", false))
                ->setArgumentName(t("Owner", false)),
            \GetOpt\Option::create('e', 'report_email', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("Report email", false))
                ->setArgumentName(t("Report email", false)),
            \GetOpt\Option::create('u', 'url', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription(t("App url", false))
                ->setArgumentName(t("App url", false)),
            ]
        )->setDescription(
            t("Populate Allerta database", false) . "." . PHP_EOL .
                PHP_EOL .
                sprintf(t("This require a working %s file", false), "config.php") . "."
        )->setShortDescription(t("Populate DB", false))
        ]
    );

    $getopt->addOptions(
        [
        Option::create('v', 'version', \GetOpt\GetOpt::NO_ARGUMENT)
            ->setDescription(t("Show version information and quit", false)),

        Option::create('h', 'help', \GetOpt\GetOpt::NO_ARGUMENT)
            ->setDescription(t("Show this help and quit", false)),

        Option::create("p", 'path', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
            ->setDescription(t("Destination path", false))
            ->setArgumentName('path')
            ->setValidation(
                'is_writable', function ($value) {
                    if(file_exists($value)) {
                        printf(t("%s is not writable. Directory permissions: %s"), $value, @fileperms($value));
                        exit(4);
                    } else {
                        printf(t("%s not exists"), $value);
                        echo(".");
                        exit(3);
                    }
                }
            )
        ]
    );

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
        echo sprintf('%s: %s' . PHP_EOL, "AllertaVVF", "0.0");
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
