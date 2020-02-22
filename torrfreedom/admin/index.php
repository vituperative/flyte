<?php
require_once "../include/bittorrent.inc.php";
require 'admin_class.php';
$admin = new admin();



function total($what,$val){
	printf("<tr><td>%s</td><td>%s</td></tr>", $what,$val);
}

print("<div id=server class=overview>\n<table>\n");
print("<tr><th colspan=2>Tracker Overview</th></tr>");
total("Total Users", $admin->countUsers());
total("Total Torrents", $admin->countTorrents());
total("Total Categories", $admin->countCategories());
total("Unique logins (today / this week)", "--");
total("Peers announcing to tracker (seeds / leeches)", $admin->countOfSeeders()."/".$admin->countOfLeech() );


print("</table>");

stdfoot(); ?>
