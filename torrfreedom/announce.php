<?php
if (ob_get_level() == 0) ob_start("ob_gzhandler");
require_once("include/bittorrent.inc.php");
require_once("include/benc.php");

require_once("include/announcer_class.php"); //



$announcer = new Announcer();

//OLD CODE IN COMMENTS FUCK
$announcer->announce($_GET);
?>
