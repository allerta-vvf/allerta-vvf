<?php
require_once 'core.php';
loadtemplate('profilo.html', ['titolo' => 'Pagina profilo', 'distaccamento' => 'VVF Darfo', 'urlsoftware' => '', 'utente' => $utente->info()]);