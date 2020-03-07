<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}
require_once "include/bittorrent.inc.php";
require_once "user/user.class.php";
$user=new user();
stdhead();

printf("<div class=\"tablewrap slim\">\n<table id=stats>");

printf("<tr><th colspan=2>Tracker Stats</th></tr>");
printf("<tr><td>Active torrents</td><td>%d</td></tr>", $user->getCountActiveTorrents());
printf("<tr><td>Total Torrents</td><td>%d</td></tr>", $user->countTorrents());
printf("<tr><td>Completed Downloads</td><td>%d</td></tr>", $user->getTorrentsCompleted());
printf("<tr><td>Active Peers</td><td>%d &nbsp; <span title=\"Seeds / Leechers\">[ %d / %d ]</span></td></tr>", $user->countPeers(),$user->countOfSeeders(),$user->countOfLeech(), 1);
printf("<tr><td>Total Hits</td><td>%d</td></tr>", $user->getTorrentsHits());
printf("<tr><td>Total Comments</td><td>%d</td></tr>", $user->countComments());

printf("</div></table>");

stdfoot();
?>
