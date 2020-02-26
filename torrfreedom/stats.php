<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}

require_once "user/user.class.php";
$user=new user();

printf("Active torrents %d <br/>", $user->getCountActiveTorrents() );;
printf("Uploaded Torrents %d <br/>", $user->countTorrents() );;
printf("Active torrents %d <br/>", $user->getCountActiveTorrents() );;


printf("Active peers: %d</b>, with <b>%d</b> seeders and <b>%d</b> leechers", $user->countPeers(),$user->countOfSeeders(),$user->countOfLeech() );

printf("Completed downloads %d <br/>", $user->getTorrentsCompleted() );;


printf("getTorrentsHits is %d <br/>", $user->getTorrentsHits());
printf("countComments is %d <br/>", $user->countComments());
?>
