<?php
if (ob_get_level() == 0) ob_start("ob_gzhandler");
require_once("include/bittorrent.inc.php");
require_once("include/benc.php");

require_once("include/announcer_class.php"); //



$announcer = new Announcer();

//СТАРЫЙ КОД В КОММИТАХ ЕСЬ ЧО ЫЫ
$announcer->announce($_GET);
?>
