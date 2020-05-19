<?php
if (file_exists("installHelper.php")) {
    try {
        require('installHelper.php');
    } catch (Exception $e) {
        die("Please re-download Allerta. Installation corrupted (".$e);
    }
} else {
    die("Please re-download Allerta. Installation corrupted");
}
if (file_exists("../config.php")) {
    $runInstallation = false;
} else {
    $runInstallation = true;
}

if($runInstallation){
    ?>
    <html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex,nofollow">
    <title>Allerta › installazione</title>
    <link rel="stylesheet" id="dashicons-css" href="dashicons.min.css?installation" type="text/css" media="all">
    <link rel="stylesheet" id="buttons-css" href="buttons.min.css?installation" type="text/css" media="all">
    <link rel="stylesheet" id="forms-css" href="forms.min.css?installation" type="text/css" media="all">
    <link rel="stylesheet" id="install-css" href="install.min.css?installation" type="text/css" media="all">
    <?php if(isset($_POST["step"])){ if($_POST["step"] == "4"){ ?><script async src="zxcvbn.js"></script><?php } } ?>
    </head>
    <body class="wp-core-ui">
    <p id="logo"><a href="javascript:alert('TODO: add docs');">Allerta</a></p>
<?php if(!isset($_POST["step"])){ ?>
    <h1 class="screen-reader-text">Prima di iniziare</h1>
    <p>Benvenuto in Allerta. Prima di iniziare abbiamo bisogno di alcune informazioni sul database. Devi conoscere i seguenti dati prima di procedere.</p>
    <ol>
        <li>Nome del database</li>
        <li>Nome utente del database</li>
        <li>Password del database</li>
        <li>Host del database</li>
        <li>Prefisso tabelle (se desideri eseguire più Allerta con un solo database)</li>
    </ol>
    <p>
            Utilizzeremo queste informazioni per creare un file <code>config.php</code>.	<strong>
            Se per qualsiasi motivo la creazione automatica dei file non funziona, non ti preoccupare. Tutto questo non fa altro che inserire le informazioni nel database e in un file di configurazione. Puoi  aprire <code>config-sample.php</code> in un editor di testo, inserire i tuoi dati, e salvarlo come <code>config.php</code>.	</strong>
    <p>Con ogni probabilità, queste informazioni ti sono state già fornite dal tuo fornitore di hosting. Se non disponi di queste informazioni, dovrai contattare il tuo fornitore prima di poter proseguire. Se invece è tutto pronto…</p>
    
    <p class="step">
    <form method="POST">
    <input type="hidden" name="step" value="2">
    <input type="submit" class="button button-large">
    </form>
    </p>
<?php
} else if ($_POST["step"] == "2") {
    if (file_exists("../config.php")) {
        try {
            require('../config.php');
            $dbnameValue = DB_NAME;
            $unameValue = DB_USER;
            $pwdValue = DB_PASSWORD;
            $dbhostValue = DB_HOST;
            $prefixValue = DB_PREFIX;
        } catch (Exception $e) {
            $dbnameValue = "allerta";
            $unameValue = "utente";
            $pwdValue = "password";
            $dbhostValue = "localhost";
            $prefixValue = "allerta01";
        }
    } else {
        $dbnameValue = "allerta";
        $unameValue = "utente";
        $pwdValue = "password";
        $dbhostValue = "localhost";
        $prefixValue = "allerta01";
    }
?>
    <h1 class="screen-reader-text">Configura la connessione al database</h1>
    <form method="post">
    <p>Di seguito puoi inserire i dettagli di connessione al database. Se non sei sicuro dei dati da inserire contatta il tuo fornitore di hosting.</p>
    <table class="form-table" role="presentation">
       <tbody>
          <tr>
             <th scope="row"><label for="dbname">Nome database</label></th>
             <td><input name="dbname" id="dbname" type="text" aria-describedby="dbname-desc" size="25" value="<?php echo $dbnameValue; ?>" autofocus=""></td>
             <td id="dbname-desc">Il nome del database che vuoi utilizzare con Allerta.</td>
          </tr>
          <tr>
             <th scope="row"><label for="uname">Nome utente</label></th>
             <td><input name="uname" id="uname" type="text" aria-describedby="uname-desc" size="25" value="<?php echo $unameValue; ?>"></td>
             <td id="uname-desc">Il tuo nome utente del database.</td>
          </tr>
          <tr>
             <th scope="row"><label for="pwd">Password</label></th>
             <td><input name="pwd" id="pwd" type="text" aria-describedby="pwd-desc" size="25" value="<?php echo $pwdValue; ?>" autocomplete="off"></td>
             <td id="pwd-desc">La tua password del database.</td>
          </tr>
          <tr>
             <th scope="row"><label for="dbhost">Host del database</label></th>
             <td><input name="dbhost" id="dbhost" type="text" aria-describedby="dbhost-desc" size="25" value="<?php echo $dbhostValue; ?>"></td>
             <td id="dbhost-desc">
                Se <code>localhost</code> non funziona, puoi ottenere queste informazioni dal tuo provider di hosting.			
             </td>
          </tr>
          <tr>
             <th scope="row"><label for="prefix">Prefisso tabella</label></th>
             <td><input name="prefix" id="prefix" type="text" aria-describedby="prefix-desc" value="<?php echo $prefixValue; ?>" size="25"></td>
             <td id="prefix-desc">Modifica questa voce se desideri eseguire più installazioni di Allerta su un singolo database.</td>
          </tr>
       </tbody>
    </table>
    <input type="hidden" name="step" value="3">
    <p class="step"><input name="submit" type="submit" value="Invia" class="button button-large"></p>
    </form>
<?php
} else if ($_POST["step"] == "3") {
    checkConnection($_POST["dbhost"],$_POST["uname"],$_POST["pwd"],$_POST["dbname"]);
    generateConfig($_POST["dbhost"],$_POST["uname"],$_POST["pwd"],$_POST["dbname"],$_POST["prefix"]);
?>
    <h1 class="screen-reader-text">File di configurazione creato con successo!</h1>
    <p>Ottimo lavoro, amico! Hai completato questa parte dell'installazione. Ora WordPress può comunicare con il database. Se sei pronto, ora è il momento di…</p>
    <p class="step">
    <form method="POST">
    <input type="hidden" name="step" value="4">
    <input type="submit" class="button button-large" value="Popolare il database">
    </form>
    </p>
<?php
} else if ($_POST["step"] == "4") {
    initDB();
?>
    <h1 class="screen-reader-text">Evviva!</h1>
    <p>Hai <b>quasi terminato</b> l'installazione di Allerta, devi solo inserire alcune informazioni.</p>
    <h2>Informazioni necessarie</h2>
    <p class="step">
    <form id="setup" method="post">
    <script>
    function validatePwd(){
        var pwd = document.getElementById("pass1").value;
        result = zxcvbn(pwd);
        switch(result.score) {
            case 1:
                document.getElementById("pass1").className = "short";
                document.getElementById("pass-strength-result").className = "short";
                document.getElementById("pass-strength-result").innerHTML = "Molto debole";
                break;
            case 2:
                document.getElementById("pass1").className = "bad";
                document.getElementById("pass-strength-result").className = "bad";
                document.getElementById("pass-strength-result").innerHTML = "Debole";
                break;
            case 3:
                document.getElementById("pass1").className = "good";
                document.getElementById("pass-strength-result").className = "good";
                document.getElementById("pass-strength-result").innerHTML = "Media";
                break;
            case 4:
                document.getElementById("pass1").className = "strong";
                document.getElementById("pass-strength-result").className = "strong";
                document.getElementById("pass-strength-result").innerHTML = "Forte";
                break;
            case 5:
                document.getElementById("pass1").className = "strong";
                document.getElementById("pass-strength-result").className = "strong";
                document.getElementById("pass-strength-result").innerHTML = "Forte";
                break;
            default:
                // code block
        }
    }
    </script>
	<table class="form-table" role="presentation">
	<tbody>
		<tr>
			<th scope="row"><label for="user_login">Nome utente admin</label></th>
			<td>
				<input name="user_name" type="text" id="user_login" size="75" value="">
				<p>I nomi utente possono essere composti soltanto da caratteri alfanumerici, spazi, trattini bassi, trattini, punti e il simbolo @.</p>
			</td>
		</tr>
        <tr class="form-field form-required user-pass1-wrap">
			<th scope="row">
				<label for="pass1">
					Password</label>
			</th>
			<td>
				<div class="wp-pwd">
					<input type="text" name="admin_password" id="pass1" class="regular-text short" autocomplete="off" aria-describedby="pass-strength-result" onkeyup="validatePwd()">
					<div id="pass-strength-result" aria-live="polite" class="short">Molto debole</div>
				</div>
				<p><span class="description important">
				<strong>Importante:</strong>
								Avrai bisogno di questa password per accedere. Conservala in un posto sicuro.</span></p>
			</td>
		</tr>
        <tr>
			<th scope="row">Rendi utente admin visibile</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span>Rendi utente admin visibile </span></legend>
						<label for="admin_visible"><input name="admin_visible" type="checkbox" id="admin_visible" value="0">
						Rendi l'utente admin visibile agli altri utente</label>
						<p class="description">Attivando questa opzione, l'utente che verrà creato sarà visibile negli elenchi e nelle procedure.</p>
						</fieldset>
			</td>
	    </tr>
		<tr>
			<th scope="row"><label for="admin_email">La tua email</label></th>
			<td><input name="admin_email" type="email" id="admin_email" size="50" value="">
			<p>Controlla attentamente il tuo indirizzo email prima di continuare.</p></td>
		</tr>
		<tr>
			<th scope="row"><label for="distaccamento">Distaccamento</label></th>
			<td><input name="distaccamento" type="text" id="distaccamento" size="100" value="">
			<p>Verrà utilizzato nei report.</p></td>
		</tr>
    </tbody></table>
    <p class="step"><input type="submit" name="Submit" id="submit" class="button button-large" value="Installa Allerta"></p>
    <input type="hidden" name="step" value="5">
	</form>
    </p>
<?php
} else if ($_POST["step"] == "5") {
    initOptions($_POST["user_name"], $_POST["admin_visible"], $_POST["admin_password"], $_POST["admin_email"], $_POST["distaccamento"]);
?>
    <h1 class="screen-reader-text">Installazione terminata con successo.</h1>
    <p>Ottimo lavoro, amico! Hai completato l'installazione. Ora Allerta può funzionare correttamente. Adesso è il momento di…</p>
    <p class="step">
    <a href="../index.php">Eseguire il login</a>
    </p>
<?php
}
?>
    </div>
    </body>
    </html>
<?php
}
?>