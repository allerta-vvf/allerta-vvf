<?php
if (file_exists("installHelper.php")) {
    try {
        require('installHelper.php');
    } catch (Exception $e) {
        die("Please re-download Allerta. Installation corrupted (".$e.")");
    }
} else {
    die("Please re-download Allerta. Installation corrupted");
}

if (!file_exists("runInstall.php")) {
    t("Already installed");
    exit();
}

$populated = false;
$userPopulated = false;
if (file_exists("../config.php")) {
    try {
        require('../config.php');
        $dbnameValue = DB_NAME;
        $unameValue = DB_USER;
        $pwdValue = DB_PASSWORD;
        $dbhostValue = DB_HOST;
        $prefixValue = DB_PREFIX;
        if(checkConnection($dbhostValue,$unameValue,$pwdValue,$dbnameValue,true)){
            $configOk = true;
            try{
                $connection = new PDO("mysql:host=$dbhostValue;dbname=$dbnameValue", $unameValue, $pwdValue,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $stmt = $connection->prepare(str_replace("%PREFIX%", DB_PREFIX, "SELECT * FROM `%PREFIX%_dbversion`;"));
                $query = $stmt->execute();
                $populated = !empty($stmt->fetchAll(PDO::FETCH_ASSOC));
                $stmt2 = $connection->prepare(str_replace("%PREFIX%", DB_PREFIX, "SELECT * FROM `%PREFIX%_users`;"));
                $query2 = $stmt2->execute();
                $userPopulated = !empty($stmt2->fetchAll(PDO::FETCH_ASSOC));
            } catch (PDOException $e){
                $populated = false;
                $userPopulated = false;
            }
        }
    } catch (Exception $e) {
        $dbnameValue = "allerta";
        $unameValue = t("user", false);
        $pwdValue = t("password", false);
        $dbhostValue = "localhost";
        $prefixValue = "allerta01";
        $configOk = false;
    }
} else {
    $dbnameValue = "allerta";
    $unameValue = t("user", false);
    $pwdValue = t("password", false);
    $dbhostValue = "localhost";
    $prefixValue = "allerta01";
    $configOk = false;
}

if(!is_cli()){
    ?>
    <html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex,nofollow">
    <title>Allerta › <?php t("installation"); ?></title>
    <link rel="stylesheet" id="dashicons-css" href="dashicons.min.css?installation" type="text/css" media="all">
    <link rel="stylesheet" id="buttons-css" href="buttons.min.css?installation" type="text/css" media="all">
    <link rel="stylesheet" id="forms-css" href="forms.min.css?installation" type="text/css" media="all">
    <link rel="stylesheet" id="install-css" href="install.min.css?installation" type="text/css" media="all">
    <script async src="zxcvbn.js"></script>
    </head>
    <body class="wp-core-ui">
    <p id="logo"><a href="javascript:alert('TODO: add docs');">Allerta</a></p>
<?php if(!isset($_POST["step"]) && !$configOk){ ?>
    <h1 class="screen-reader-text"><?php t("Before starting"); ?></h1>
    <p><?php t("Welcome in Allerta. We need some informations about the database. You have to know the following informations:"); ?></p>
    <ol>
        <li><?php t("DB name"); ?></li>
        <li><?php t("DB username"); ?></li>
        <li><?php t("DB password"); ?></li>
        <li><?php t("DB host"); ?></li>
        <li><?php t("DB prefix"); ?></li>
    </ol>
    <p>
    <?php t("We will use this informations for creating a file"); ?> <code>config.php</code>.	<strong>
    <?php printf(t("If for any reason automatic file creation doesn't work, don't worry. You can just open %s in a text editor, enter your details, and save it as", false), "<code>config-sample.php</code>"); ?> <code>config.php</code>.	</strong>
    <p><?php t("In all likelihood, this information has already been provided to you by your hosting provider. If you don't have this information, you'll need to contact your provider before you can continue. But if everything is ready..."); ?></p>

    <p class="step">
    <form method="POST">
    <input type="hidden" name="step" value="2">
    <input type="submit" value="<?php t("Submit"); ?>" class="button button-large">
    </form>
    </p>
<?php
} else if (in_array("2",$_POST)) {
?>
    <h1 class="screen-reader-text"><?php t("Configure the database connection"); ?></h1>
    <form method="post">
    <p><?php t("Below you can enter your database connection details. If you are not sure of the data to enter, contact your hosting provider"); ?>.</p>
    <table class="form-table" role="presentation">
       <tbody>
          <tr>
             <th scope="row"><label for="dbname"><?php t("DB name"); ?></label></th>
             <td><input name="dbname" id="dbname" type="text" aria-describedby="dbname-desc" size="25" value="<?php echo $dbnameValue; ?>" autofocus=""></td>
             <td id="dbname-desc"><?php t("The name of the database you want to use with Allerta"); ?>.</td>
          </tr>
          <tr>
             <th scope="row"><label for="uname"><?php t("DB username"); ?></label></th>
             <td><input name="uname" id="uname" type="text" aria-describedby="uname-desc" size="25" value="<?php echo $unameValue; ?>"></td>
             <td id="uname-desc"><?php t("Your"); echo(" "); t("DB username"); ?>.</td>
          </tr>
          <tr>
             <th scope="row"><label for="pwd"><?php t("DB password"); ?></label></th>
             <td><input name="pwd" id="pwd" type="text" aria-describedby="pwd-desc" size="25" value="<?php echo $pwdValue; ?>" autocomplete="off"></td>
             <td id="pwd-desc"><?php t("Your"); echo(" "); t("DB password"); ?>.</td>
          </tr>
          <tr>
             <th scope="row"><label for="dbhost"><?php t("DB host"); ?></label></th>
             <td><input name="dbhost" id="dbhost" type="text" aria-describedby="dbhost-desc" size="25" value="<?php echo $dbhostValue; ?>"></td>
             <td id="dbhost-desc">
                <?php printf(t("If %s doesn't work, you can get this information from your hosting provider", false), "<code>localhost</code>"); ?>.
             </td>
          </tr>
          <tr>
             <th scope="row"><label for="prefix"><?php t("DB prefix"); ?></label></th>
             <td><input name="prefix" id="prefix" type="text" aria-describedby="prefix-desc" value="<?php echo $prefixValue; ?>" size="25"></td>
             <td id="prefix-desc"><?php t("Edit this item if you want to perform multiple Alert installations on a single database"); ?>.</td>
          </tr>
       </tbody>
    </table>
    <input type="hidden" name="step" value="3">
    <p class="step"><input name="submit" type="submit" value="<?php t("Submit"); ?>" class="button button-large"></p>
    </form>
<?php
} else if (in_array("3",$_POST)) {
    checkConnection($_POST["dbhost"],$_POST["uname"],$_POST["pwd"],$_POST["dbname"]);
    generateConfig($_POST["dbhost"],$_POST["uname"],$_POST["pwd"],$_POST["dbname"],$_POST["prefix"]);
?>
    <h1 class="screen-reader-text"><?php t("Configuration file created successfully!"); ?></h1>
    <p><?php t("Great job, man!"); echo(" "); t("You have completed this part of the installation. Allerta can now communicate with the database"); echo(".<br> "); t("If you are ready, it's time to..."); ?></p>
    <p class="step">
    <form method="POST">
    <input type="hidden" name="step" value="4">
    <input type="submit" class="button button-large" value="<?php t("Populate DB"); ?>">
    </form>
    </p>
<?php
} else if ($configOk && !$populated) {
    initDB();
    header("Location: install.php");
} else if ($populated && !$userPopulated && !in_array("5",$_POST)) {
?>
    <h1 class="screen-reader-text"><?php t("Hurray!"); ?></h1>
    <p><?php t("You are almost finished installing Allerta, you just need to enter some information"); ?>.</p>
    <h2><?php t("Necessary informations:"); ?></h2>
    <p class="step">
    <form id="setup" method="post">
    <script>
    function validatePwd(){
        var pwd = document.getElementById("pass1").value;
        result = zxcvbn(pwd);
        switch(result.score) {
            case 0:
                document.getElementById("pass1").className = "short";
                document.getElementById("pass-strength-result").className = "short";
                document.getElementById("pass-strength-result").innerHTML = "<?php t("Very weak"); ?>";
                break;
            case 1:
                document.getElementById("pass1").className = "bad";
                document.getElementById("pass-strength-result").className = "bad";
                document.getElementById("pass-strength-result").innerHTML = "<?php t("Weak"); ?>";
                break;
            case 2:
                document.getElementById("pass1").className = "good";
                document.getElementById("pass-strength-result").className = "good";
                document.getElementById("pass-strength-result").innerHTML = "<?php t("Good"); ?>";
                break;
            case 3:
                document.getElementById("pass1").className = "strong";
                document.getElementById("pass-strength-result").className = "strong";
                document.getElementById("pass-strength-result").innerHTML = "<?php t("Strong"); ?>";
                break;
            case 4:
                document.getElementById("pass1").className = "strong";
                document.getElementById("pass-strength-result").className = "strong";
                document.getElementById("pass-strength-result").innerHTML = "<?php t("Very strong"); ?>";
                break;
            default:
                document.getElementById("pass1").className = "short";
                document.getElementById("pass-strength-result").className = "short";
                document.getElementById("pass-strength-result").innerHTML = "<?php t("Very weak"); ?>";
                break;
        }
    }
    </script>
	<table class="form-table" role="presentation">
	<tbody>
		<tr>
			<th scope="row"><label for="user_login"><?php t("Admin username"); ?></label></th>
			<td>
				<input name="user_name" type="text" id="user_login" size="75" value="">
				<p><?php t("Usernames can only contains alphanumeric characters, spaces, underscores, dashes, periods, and the @ symbol"); ?>.</p>
			</td>
		</tr>
        <tr class="form-field form-required user-pass1-wrap">
			<th scope="row">
				<label for="pass1">
					<?php t("Password"); ?></label>
			</th>
			<td>
				<div class="wp-pwd">
					<input type="text" name="admin_password" id="pass1" class="regular-text short" autocomplete="off" aria-describedby="pass-strength-result" onkeyup="validatePwd()">
					<div id="pass-strength-result" aria-live="polite" class="short"><?php t("Very weak"); ?></div>
				</div>
				<p><span class="description important">
				<strong><?php t("Important:"); ?></strong>
								<?php t("You will need this password to log in. Keep it in a safe place"); ?>.</span></p>
			</td>
		</tr>
        <tr>
			<th scope="row"><?php t("Is admin visible?"); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php t("Make admin user visible"); ?></span></legend>
						<label for="admin_visible"><input name="admin_visible" type="checkbox" id="admin_visible" value="0">
						<?php t("Make admin user visible"); echo(" "); t("to other users"); ?></label>
						<p class="description"><?php t("By activating this option, the user that will be created will be visible in lists and procedures"); ?>.</p>
				</fieldset>
			</td>
        </tr>
        <tr style="display:none">
			<th scope="row"><?php t("Add developer permissions to admin user"); ?></th>
			<td>
				<fieldset>
						<label for="developer"><input name="developer" type="checkbox" id="developer" value="0">
						<?php t("Add developer permissions to admin user"); ?></label>
						<p class="description"><?php t("By activating this option, the user will be able to debug Allerta"); ?>.</p>
				</fieldset>
			</td>
	    </tr>
		<tr>
			<th scope="row"><label for="admin_email"><?php t("Your email"); ?></label></th>
			<td><input name="admin_email" type="email" id="admin_email" size="50" value="">
			<p><?php t("Please check your email address carefully before continuing"); ?>.</p></td>
		</tr>
		<tr>
			<th scope="row"><label for="owner"><?php t("Owner"); ?></label></th>
			<td><input name="owner" type="text" id="owner" size="100" value="">
			<p><?php t("It will be used in reports"); ?>.</p></td>
		</tr>
    </tbody></table>
    <p class="step"><input type="submit" name="Submit" id="submit" class="button button-large" value="<?php t("Install Allerta"); ?>"></p>
    <input type="hidden" name="step" value="5">
	</form>
    </p>
<?php
} else if (in_array("5",$_POST)) {
    initOptions($_POST["user_name"], isset($_POST["admin_visible"]), isset($_POST["developer"]), $_POST["admin_password"], $_POST["admin_email"], $_POST["owner"]);
    header("Location: install.php");
} else if ($userPopulated) {
?>
    <h1 class="screen-reader-text"><?php t("Installation completed successfully"); ?>.</h1>
    <p><?php t("Great job, man!"); echo(" "); t("You have completed the installation. Allerta can now function properly"); echo(".<br> "); t("If you are ready, it's time to..."); ?></p>
    <p class="step">
    <a href="../index.php" class="login"><?php t("Login"); ?></a>
    </p>
<?php
    unlink("runInstall.php");
}
?>
    </div>
    </body>
    </html>
<?php
} else {
    run_cli();
}
?>