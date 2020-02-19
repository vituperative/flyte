<?php
require_once "../include/bittorrent.inc.php";
dbconn();
stdhead("Admin page");
$admin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
if (!$admin)
    header("Location: ../index.php");

print("<div id=server class=overview>\n<table>\n");
print("<tr><th colspan=2>Tracker Overview</th></tr>");
print("<tr><td>Total Torrents</td><td></td></tr>");
print("<tr><td>Active Torrents</td><td></td></tr>");
print("<tr><td>Total Categories</td><td></td></tr>");
print("<tr><td>Total Users</td><td></td></tr>");
print("</table>");

stdfoot(); ?>
