<?php

require 'admin_class.php';

$admin = new admin();

function total($what,$val){
   printf("<tr><td>%s</td><td>%s</td></tr>", $what,$val);
}

print("<div id=server class=overview>\n<table>\n");
print("<tr><th colspan=2>Tracker Overview</th></tr>");
total("Torrents", $admin->getCountActiveTorrents() . " active / " . $admin->countTorrents() . " total");
total("Connected Peers", $admin->countOfSeeders() . " seeds / " . $admin->countOfLeech() . " leechers", 1);
total("Categories", $admin->countCategories());
total("Torrent Views", $admin->getTorrentsViews());
total("Completed Downloads",  $admin->getTorrentsCompleted());
total("Registered Users", "<a href=users.php>" . $admin->countUsers() . "</a>");
total("Unique logins", $admin->countAccess());
print("</table>");

stdfoot(); ?>
