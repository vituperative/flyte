<?php
require_once "include/bittorrent.inc.php";
dbconn();
stdhead("Admin page");
if(!isset($CURUSER) || $CURUSER["admin"] != "yes")
	die("<div class=alert>you are not admin.</div>");
//there is some HTML CODE table with functionnality which is need
?>

<?php

stdfoot();
?>
