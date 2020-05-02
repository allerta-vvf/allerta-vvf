<?php
include_once 'core.php';
init_class();
if(isset($_SESSION)){
    $user = $_SESSION['nome'];
} else {
    $user = "test";
}
$string = <<<EOT
<script>
ciao = 0;
function onLine() {
    ciao = ciao + 1;
    console.log(ciao);
	console.log("onLine");
	var xhr = new XMLHttpRequest();
	xhr.open('GET', 'http://62.171.139.86/allerta/online_check.php?utente=$user');
	xhr.onload = function () {
		if (xhr.status === 200) {
			console.log('Text ' + xhr.responseText);
		} else {
			console.log('Request failed.  Returned status of ' + xhr.status);
		}
	};
	xhr.send();
}

function offLine() {
	console.log("offLine");
}

function check() {
    console.log("dfcghfhdt");
	var i = new Image();
	i.onload = onLine;
	i.onerror = offLine;
	i.src = 'https://www.google-analytics.com/__utm.gif';
}
</script>
EOT;
echo($string);
