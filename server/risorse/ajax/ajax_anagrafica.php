<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$risultato = $database->esegui('SELECT * FROM `%PREFIX%_profiles` WHERE id = :id', true, array(":id" => $_GET['user'])); // Pesco i dati della tabella

$hidden = $user->hidden();
?>
<style>/*
 * Bootstrap v2.2.1
 *
 * Copyright 2012 Twitter, Inc
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Designed and built with all the love in the world @twitter by @mdo and @fat.
 */
.clearfix {
  *zoom: 1;
}
.clearfix:before,
.clearfix:after {
  display: table;
  content: "";
  line-height: 0;
}
.clearfix:after {
  clear: both;
}
html {
  font-size: 100%;
  -webkit-text-size-adjust: 100%;
  -ms-text-size-adjust: 100%;
}
a:focus {
  outline: thin dotted #333;
  outline: 5px auto -webkit-focus-ring-color;
  outline-offset: -2px;
}
a:hover,
a:active {
  outline: 0;
}
button,
input,
select,
textarea {
  margin: 0;
  font-size: 100%;
  vertical-align: middle;
}
button,
input {
  *overflow: visible;
  line-height: normal;
}
button::-moz-focus-inner,
input::-moz-focus-inner {
  padding: 0;
  border: 0;
}
body {
  margin: 0;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 14px;
  line-height: 20px;
  color: #333;
  background-color: #fff;
}
a {
  color: #08c;
  text-decoration: none;
}
a:hover {
  color: #005580;
  text-decoration: underline;
}
.row {
  margin-left: -20px;
  *zoom: 1;
}
.row:before,
.row:after {
  display: table;
  content: "";
  line-height: 0;
}
.row:after {
  clear: both;
}
[class*="span"] {
  float: left;
  min-height: 1px;
  margin-left: 20px;
}
.container,
.navbar-static-top .container,
.navbar-fixed-top .container,
.navbar-fixed-bottom .container {
  width: 940px;
}
.span12 {
  width: 940px;
}
.span11 {
  width: 860px;
}
.span10 {
  width: 780px;
}
.span9 {
  width: 700px;
}
.span8 {
  width: 620px;
}
.span7 {
  width: 540px;
}
.span6 {
  width: 460px;
}
.span5 {
  width: 380px;
}
.span4 {
  width: 300px;
}
.span3 {
  width: 220px;
}
.span2 {
  width: 140px;
}
.span1 {
  width: 60px;
}
[class*="span"].pull-right,
.row-fluid [class*="span"].pull-right {
  float: right;
}
.container {
  margin-right: auto;
  margin-left: auto;
  *zoom: 1;
}
.container:before,
.container:after {
  display: table;
  content: "";
  line-height: 0;
}
.container:after {
  clear: both;
}
p {
  margin: 0 0 10px;
}
.lead {
  margin-bottom: 20px;
  font-size: 21px;
  font-weight: 200;
  line-height: 30px;
}
small {
  font-size: 85%;
}
h1 {
  margin: 10px 0;
  font-family: inherit;
  font-weight: bold;
  line-height: 20px;
  color: inherit;
  text-rendering: optimizelegibility;
}
h1 small {
  font-weight: normal;
  line-height: 1;
  color: #999;
}
h1 {
  line-height: 40px;
}
h1 {
  font-size: 38.5px;
}
h1 small {
  font-size: 24.5px;
}
.header {
  position: fixed;
  top: 0;
  left: 50%;
  margin-left: -480px;
  background-color: #fff;
  border-bottom: 1px solid #ddd;
  padding-top: 10px;
  z-index: 10;
}
.footer {
  color: #ddd;
  font-size: 12px;
  text-align: center;
  margin-top: 20px;
}
.footer a {
  color: #ccc;
  text-decoration: underline;
}
.the-icons {
  font-size: 14px;
  line-height: 24px;
}
.switch {
  position: absolute;
  right: 0;
  bottom: 10px;
  color: #666;
}
.switch input {
  margin-right: 0.3em;
}
.codesOn .i-name {
  display: none;
}
.codesOn .i-code {
  display: inline;
}
.i-code {
  display: none;
}
@font-face {
      font-family: 'test';
      src: url('./risorse/font/test.eot?93129191');
      src: url('./risorse/font/test.eot?93129191#iefix') format('embedded-opentype'),
           url('./risorse/font/test.woff?93129191') format('woff'),
           url('./risorse/font/test.ttf?93129191') format('truetype'),
           url('./risorse/font/test.svg?93129191#test') format('svg');
      font-weight: normal;
      font-style: normal;
    }


    .demo-icon
    {
      font-family: "test";
      font-style: normal;
      font-weight: normal;
      speak: none;

      display: inline-block;
      text-decoration: inherit;
      width: 1em;
      margin-right: .2em;
      text-align: center;
      /* opacity: .8; */

      /* For safety - reset parent styles, that can break glyph codes*/
      font-variant: normal;
      text-transform: none;

      /* fix buttons height, for twitter bootstrap */
      line-height: 1em;

      /* Animation center compensation - margins should be symmetric */
      /* remove if not needed */
      margin-left: .2em;

      /* You can be more comfortable with increased icons size */
      /* font-size: 120%; */

      /* Font smoothing. That was taken from TWBS */
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;

      /* Uncomment for 3D effect */
      /* text-shadow: 1px 1px 1px rgba(127, 127, 127, 0.3); */
    }
     </style>
<style>
th, td {
    border: 1px solid grey;
    border-collapse: collapse;
 padding: 5px;
}

#href {
 outline: none;
 cursor: pointer;
 text-align: center;
 text-decoration: none;
 font: bold 12px Arial, Helvetica, sans-serif;
 color: #fff;
 padding: 10px 20px;
 border: solid 1px #0076a3;
 background: #0095cd;
}

 table {
   box-shadow: 2px 2px 25px rgba(0,0,0,0.5);
    border-radius: 15px;
  margin: auto;
 }


</style>
<?php
function vero1($text, $img1 = "", $img2 = ""){
    if($text == 1){
        return "sì " . $img1;
    } else {
        return "no " . $img2;
    }
}
//var_dump($risultato);
foreach($risultato as $row){
$name = ($row['online']==1) ? '<u>' . $row['name'] . "</u>" : $row['name'];
echo("<h1>Dati anagrafici <b>$name</b></h1><br><br>");
echo('<img alt="VVF" src="./risorse/images/distaccamento.png" width="150" class="img-resposive"><br><br><br>');
$disp = vero1($row['avaible'], "<i class='fa fa-check' style='color:green' width='22px'></i>", "<i class='fa fa-times'  style='color:red' width='22px'></i>");
$caposquadra = vero1($row['caposquadra'], "<img src='./risorse/images/cascoRosso.png' width='22px'>", "<img src='./risorse/images/cascoNero.png' width='22px'>");
$autista = vero1($row['autista'], "<img src='./risorse/images/volante.png' width='22px'>");
echo("<p>name:  <b>$name</b></p><br>");
echo("<p>Disponibilità:  <b>{$disp}</b></p><br>");
echo("<p>Caposquadra:  <b>{$caposquadra}</b></p><br>");
echo("<p>Autista:  <b>{$autista}</b></p><br>");
echo("<p>Numero di telefono <i class='fa fa-phone' style='color:green' width='22px'></i>:  <b><a href='tel:{$row['telefono']}'>{$row['telefono']}</a></b></p><br>");
echo("<p>Minuti di disponibilità <br>(Questo mese) <i class='demo-icon icon-hourglass'></i>:  <b>{$row['minuti_dispo']} minuti</b></p><br>");
echo("<p>Interventi svolti:  <b>{$row['interventi']}</b></p><br>");
}
?>
