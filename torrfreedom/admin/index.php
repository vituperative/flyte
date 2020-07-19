<?php

require 'admin_class.php';

$admin = new admin();

function total($what,$val){
   printf("<tr><td>%s</td><td>%s</td></tr>", $what,$val);
}

print("<div id=server class=overview>\n<table>\n");
print("<tr><th colspan=2>Tracker Overview&nbsp;&nbsp;<a href=server.php>Server Details</a></th></tr>");
total("Tracker URL", $tracker_url_name);
total("Tracker Contact", $contact);
total("Tracker announce period", $announce_interval . " seconds");
total("Tracker login expiry", $signup_timeout / 60 / 60 . " hours");
total("Torrents", $admin->getCountActiveTorrents() . " active / <a href=torrents.php>" . $admin->countTorrents() . "</a> total");
total("Connected Peers", $admin->countOfSeeders() . " seeds / " . $admin->countOfLeech() . " leechers", 1);
total("Categories", $admin->countCategories());
total("Torrent Views", $admin->getTorrentsViews());
total("Completed Downloads",  $admin->getTorrentsCompleted());
total("Registered Users", "<a href=users.php>" . $admin->countUsers() . "</a>");
total("Unique logins", $admin->countAccess());
print("</table>");

stdfoot(); ?>
