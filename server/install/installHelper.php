<?php
if (file_exists('../vendor/autoload.php')) {
    try {
        require '../vendor/autoload.php';
    } catch (Exception $e) {
        die("Please install composer and run composer install (".$e);
    }
} else {
    die("Please install composer and run composer install");
}

function checkConnection($host, $user, $password, $database){
    try{
        $connection = new PDO("mysql:host=$host", $user, $password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $connectionOk = true;
    } catch (PDOException $e){
        $connectionOk = false;
        ?>
        <div class="wp-die-message"><h1>Errore nello stabilire una connessione al database</h1>
            <p>Questo potrebbe voler dire che nome utente e password nel file <code>config.php</code> sono sbagliate o che non possiamo contattare il database <code><?php echo $database; ?></code>. Potrebbe voler dire che il tuo database è irraggiungibile.</p>
            <ul>
                <li>Sei sicuro di avere nome utente e password corretti?</li>
                <li>Sei sicuro di aver scritto l'hostname corretto?</li>
                <li>Sei sicuro che il server del database sia attivo?</li>
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
    if($connectionOk){
        try{
            try{
                $connection->exec("CREATE DATABASE IF NOT EXISTS " . preg_replace('/[^a-zA-Z0-9]/', '', trim($database)));
            } catch(Exception $e) {
                //nothing
            }
            $connection->exec("use " . preg_replace('/[^a-zA-Z0-9]/', '', trim($database)));
        } catch (PDOException $e){
            ?>
            <div class="wp-die-message"><h1>Impossibile selezionare il database</h1>
                <p>Siamo riusciti a connetterci al server del database (il che significa che il tuo nome utente e password sono ok), ma non siamo riusciti a selezionare il database <code><?php echo $database; ?></code>.</p>
                <ul>
                    <li>Sei sicuro che esista?</li>
                    <li>L'utente <code><?php echo $user; ?></code> ha i permessi per usare il database <code><?php echo $database; ?></code>?</li>
                    <li>In alcuni sistemi il nome del tuo database ha il tuo nome utente come prefisso, ovvero <code><?php echo $user; ?>_<?php echo $database; ?></code>. Potrebbe essere questo il problema?</li>
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
}

function replaceInFile($edits,$file){
    $content = file_get_contents($file);
    foreach($edits as $edit){
        $content = str_replace($edit[0],$edit[1],$content);
    }
    file_put_contents($file,$content);
}

function generateConfig($host,$user,$password,$db,$prefix){
    try{
        if (file_exists('../config.php')) {
            rename("../config.php", "../config.old.php");
        }
        copy("../config-sample.php", "../config.php");
        replaceInFile([["@@db@@", $db],["@@user@@",$user],["@@password@@",$password],["@@host@@",$host],["@@prefix@@",$prefix]],"../config.php");
    } catch (Exception $e) {
        ?>
        <div class="wp-die-message"><h1>Impossibile modificare il file di configurazioni</h1>
            <p>Non siamo riusciti a scrivere il file di configurazione <code>config.php</code>, richiesto per il funzionamento del programma.<br>E' tuttavia possibile modificarlo manualmente, seguentdo le seguenti istruzioni:</p>
            <ul>
                <li>Accedere alla cartella di installazione di allerta (connettersi via FTP in caso di server sul cloud).</li>
                <li>Rinominare il file <code>config-sample.php</code> in <code>config.php</code>.</li>
                <li>Modificare le prime 16 righe del file con il seguente testo:</li>
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
        require "../config.php";
        $connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $prefix = DB_PREFIX;
        $connection->exec("
CREATE TABLE IF NOT EXISTS `".$prefix."_certificati` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`codice` text NOT NULL,
`nome` text NOT NULL,
`interventi` text NOT NULL,
`url` text NOT NULL,
`file` text NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_esercitazioni` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`data` date NOT NULL,
`nome` varchar(999) NOT NULL,
`inizio` time NOT NULL,
`fine` time NOT NULL,
`personale` text NOT NULL,
`capo` text NOT NULL,
`luogo` text NOT NULL,
`note` text NOT NULL,
`dec` varchar(999) NOT NULL DEFAULT 'test',
`inseritoda` varchar(200) NOT NULL DEFAULT 'test',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_interventi` (
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
CREATE TABLE IF NOT EXISTS `".$prefix."_intrusioni` (
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
`azione` varchar(100) NOT NULL,
`subisce` varchar(100) NOT NULL,
`agisce` varchar(100) NOT NULL,
`data` varchar(100) NOT NULL,
`ora` varchar(100) NOT NULL,
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
`nome` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `nometipologia` (`nome`(99))
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `".$prefix."_vigili` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`role` INT(50) NOT NULL DEFAULT '0',
`hidden` BOOLEAN NOT NULL DEFAULT FALSE,
`disabled` BOOLEAN NOT NULL DEFAULT FALSE,
`nome` text CHARACTER SET utf8 NOT NULL,
`disponibile` tinyint(1) NOT NULL DEFAULT 0,
`caposquadra` tinyint(1) NOT NULL DEFAULT 0,
`autista` tinyint(1) NOT NULL DEFAULT 0,
`telefono` varchar(25) DEFAULT NULL,
`password` varchar(200) NOT NULL,
`password_hash` varchar(2000) NOT NULL,
`interventi` int(11) NOT NULL DEFAULT 0,
`esercitazioni` int(11) NOT NULL,
`online` tinyint(1) NOT NULL DEFAULT 0,
`online_time` int(11) NOT NULL,
`minuti_dispo` int(11) NOT NULL,
`immagine` varchar(1000) NOT NULL,
PRIMARY KEY (`id`),
KEY `Id` (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
INSERT INTO `".$prefix."_dbversion` (`id`, `version`, `timestamp`) VALUES (NULL, '1', current_timestamp());");
    } catch (Exception $e) {
        ?>
        <div class="wp-die-message"><h1>Impossibile creare le tabelle</h1>
            <p>Siamo riusciti a connetterci al server del database (il che significa che il tuo nome utente e password sono ok), ma non siamo riusciti a creare le tabelle.</p>
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

function initOptions($name, $visible, $password, $report_email, $distaccamento){
    try{
        require_once "../config.php";
        $connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $prefix = DB_PREFIX;
        $prep = $connection->prepare("
INSERT INTO `".$prefix."_vigili` (`id`, `role`, `hidden`, `disabled`, `nome`, `disponibile`, `caposquadra`, `autista`, `telefono`, `password`, `password_hash`, `interventi`, `esercitazioni`, `online`, `online_time`, `minuti_dispo`, `immagine`) VALUES ('1', '5', :hidden, '0', :name, '0', '1', '0', NULL, MD5(:password), '', '0', '0', '0', '0', '0', '');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('1', 'report_email', :report_email, '1', current_timestamp(), current_timestamp(), '1');
INSERT INTO `".$prefix."_options` (`id`, `name`, `value`, `enabled`, `created_time`, `last_edit`, `user_id`) VALUES ('2', 'distaccamento', :distaccamento, '1', current_timestamp(), current_timestamp(), '1');");
        $prep->bindParam(':name', $name, PDO::PARAM_STR);
        $prep->bindParam(':hidden', $visible, PDO::PARAM_INT);
        $prep->bindParam(':password', $password, PDO::PARAM_STR);
        $prep->bindParam(':report_email', $report_email, PDO::PARAM_STR);
        $prep->bindParam(':distaccamento', $distaccamento, PDO::PARAM_STR);
        $prep->execute();
    } catch (Exception $e) {
        ?>
        <div class="wp-die-message"><h1>Impossibile riempire le tabelle</h1>
            <p>Siamo riusciti a connetterci al server del database (il che significa che il tuo nome utente e password sono ok), ma non siamo riusciti a riempire le tabelle.</p>
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