<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}
require_once "include/bittorrent.inc.php";
require_once "user/user.class.php";
$user=new user();
if (!$CURUSER)
  header("Location: ./");
stdhead();

printf("<div class=\"tablewrap slim\">\n<table id=stats>");

printf("<tr><th colspan=2>Tracker Stats</th></tr>");
printf("<tr><td>Total Torrents</td><td>%d</td></tr>", $user->countTorrents());
printf("<tr><td>Active torrents</td><td>%d</td></tr>", $user->getCountActiveTorrents());
if ($CURUSER["admin"] == "yes")
  printf("<tr><td>Completed Downloads</td><td>%d</td></tr>", $user->getTorrentsCompleted());
printf("<tr><td>Connected Peers</td><td>%d seeds / %d leechers</span></td></tr>",$user->countOfSeeders(),$user->countOfLeech(), 1);
if ($CURUSER["admin"] == "yes")
printf("<tr><td>Total Views</td><td>%d</td></tr>", $user->getTorrentsViews());
printf("<tr><td>Total Comments</td><td>%d</td></tr>", $user->countComments());

printf("</div></table>");

stdfoot();
?>
