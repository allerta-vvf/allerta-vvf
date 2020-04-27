<?php
require_once 'core.php';
$time = time();
$nome = $utente->nome(true);
$admin = $utente->admin();
$phpsessid = $_COOKIE['PHPSESSID'];

if (isset($_COOKIE['chat']) && $_COOKIE['chat'] == true) {
echo "<!-- Smartsupp Live Chat script -->
<script type='text/javascript'>
var _smartsupp = _smartsupp || {};
_smartsupp.key = '7e1d39b8d1a7e234c56a2da730e9ce5b95508dbc';
_smartsupp.ratingEnabled = true;  // default value : false
_smartsupp.ratingComment = true;  // default value : false
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>
<script>
// basic info
	smartsupp('email', '');
	smartsupp('name', '{$nome}');
smartsupp('recording:disable', false);
	// extra info
	smartsupp('variables', {
		Nome: { label: 'Nome utente ', value: '{$nome}' },
		Admin: { label: 'Admin', value: '{$admin}' },
        SessionID: { label: 'Codice sessione', value: '{$phpsessid}' },
        Timestamp: { label: 'Timestamp', value: '{$time}'},
	});
</script>";
}
