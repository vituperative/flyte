<?php
require_once("include/bittorrent.inc.php");
global $tracker_path;
logoutcookie();
header("Location: $tracker_path");
?>
