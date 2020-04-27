<?php
require_once 'core.php';
if($utente->autenticato()){
  $tools->redirect("lista.php");
}
$errore = false;
if(isset($_POST['nome']) & isset($_POST['password'])){
  $login = $utente->login($_POST['nome'], md5($_POST['password']));
  //var_dump($login); exit;
  if($login===true){
    $tools->redirect("lista.php");
  } else {
    $errore = $login;
  }
}
loadtemplate('index.html', ['errore' => $errore, 'titolo' => 'Login', 'distaccamento' => 'VVF Darfo', 'urlsoftware' => '', 'utente' => $utente->info(false)], false);
/*
if(isset($_SESSION['accesso'])){
  if($_SESSION['accesso'] == "loggato"){
    if($_SESSION['admin'] == 1){
      $tools->redirect("lista_admin.php");
    } else {
      $tools->redirect("lista.php");
    }
  }
}

if(isset($_POST['nome']) & isset($_POST['password'])){
$nome = $_POST['nome'];
$password = md5($_POST['password']);
$sql = "SELECT * FROM vigili WHERE nome='$nome' AND password='$password';";
if ($result=mysqli_query($connessione, $sql))
  {
  // Return the number of rows in result set
  $rowcount=mysqli_num_rows($result);
  if($rowcount > 0){
    $_SESSION['accesso'] = "loggato";
    while ($row = mysqli_fetch_array($result)){
      $_SESSION['admin'] = $row['caposquadra'];
      $_SESSION['nome'] = $row['nome'];
    }
    $connesso = isset($_POST['connesso']) ? $_POST['connesso'] : '0';
    if($connesso == 1){
        $cookie = bin2hex(implode("-", array($_SESSION['admin'], $_SESSION['nome'])));
        //$cookie = "ciao";
        setcookie("l53o453g35i34434n", $cookie, time() + 108000);
    }
    if($_SESSION['admin'] == 1){
      $tools->redirect("lista_admin.php");

    } else {
      $tools->redirect("lista.php");
    }
  } else {
    $err = <<<HTML
<div class='text-center' id="err">
<script>
var sound = new Howl({
  src: ['non_hai_detto_la_parola_magica.mp3'],
  autoplay: true,
  volume: 0.9,
  onend: function() {
    console.log('Finito');
  }
});
sound.play();
//var myVar = setInterval(function(){ sound.play(); }, 10000);
</script>
<script>
$("#err").delay(5000).fadeOut(300);
</script>
Password non valida
<img src='./images/nonono.gif'></img>
</div>
HTML;
  }
  // Free result set
  mysqli_free_result($result);
  }
} else if(isset($_COOKIE['l53o453g35i34434n'])){
    $cookie = pack("H*",$_COOKIE['l53o453g35i34434n']);
    $cookie = explode("-", $cookie);
    if(is_array($cookie)){
        $_SESSION['accesso'] = "loggato";
        $_SESSION['admin'] = $cookie[0];
        $_SESSION['nome'] = $cookie[1];
        if($_SESSION['admin'] == 1){
            redirect("lista_admin.php");
        } else {
            redirect("lista.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
 <head>
<?php head(); ?>

  <style>
   center {
    text-align: center;
   }

   #modulogin {

    margin-top: 60px;
    padding: 30px 0 30px 0;
    width: 90%;
    height: auto;
    background: #fafafa;
    border-radius: 15px;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.5);
   }

   input::placeholder {

    color: lightgray;

   }
  </style>

 </head>

 <body>
<?php $tools->body() ?>
<?php if(!is_null($err)) echo $err; ?>
  <div class="container text-center" id="modulogin">
   <form method="post">

     <img alt="VVF" src="./risorse/images/logo.jpg" class="img-resposive"><br><br><br>
    <input type="text" name="nome" placeholder="Nome" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <input type="checkbox" name="connesso" value='1' id="connesso" checked><label for='connesso'>Rimani Connesso</label><br>
    <input type="submit" name="login" class="btn btn-lg btn-success" value="Accedi">
   </form>
 </div>
<br>
<div id="panico" style='display: none' class="text-center"><i class="fa fa-exclamation-triangle"></i><br>
<p>Se hai premuto “Accedi” ma non è successo niente premi <a href=lista_admin.php>qui (admin)</a> o <a href=lista.php>qui (non-admin)</a></p></div>
<br><br>
<a hidden class="text-center" href="https://www.abuseipdb.com/user/30576" title="AbuseIPDB is an IP address blacklist for webmasters and sysadmins to report IP addresses engaging in abusive behavior on their networks" alt="AbuseIPDB Contributor Badge">
	<img class="text-center" src="https://www.abuseipdb.com/contributor/30576.svg" style="width: 376px;">
</a>
<?php
//debug
//print_r($_SESSION);
?>
 </body>
</html>
*/
