<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}

require_once "user/user.class.php";
$user=new user();

print("Active torrents:".$user->getCountActiveTorrents()."<br/>" );
print("Uploaded Torrents:".$user->countTorrents()."<br/>" );
print("Uploaded Torrents:".$user->countTorrents()."<br/>" );
printf("Active peers: %d</b>, with <b>%d</b> seeders and <b>%d</b> leechers", $user->countPeers(),$user->countOfSeeders(),$user->countOfLeech() );
print("Completed downloads:".$user->getTorrentsCompleted()."<br/>");

printf("getTorrentsHits is %d <br/>", $user->getTorrentsHits());
printf("countComments is %d <br/>", $user->countComments());
?>
