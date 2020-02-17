<?php
require_once "../include/bittorrent.inc.php";
dbconn();
stdhead("Admin page");
$admin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
if (!$admin)
    header("Location: ../index.php");
?>

Server configuration: <a href=server.php>View</a>

<?php stdfoot(); ?>
