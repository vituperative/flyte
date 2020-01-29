<?php

require_once("include/bittorrent.inc.php");

dbconn();

loggedinorreturn();

stdhead($CURUSER["username"] . "'s torrents");

$where = "WHERE owner = " . $CURUSER["id"] . " AND banned != 'yes'";
$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM torrents $where");
$row = mysqli_fetch_array($res);
$count = $row[0];

if (!$count) {
?>
<h1>No torrents</h1>
<p>You haven't uploaded any torrents yet, so there's nothing in this page.</p>
<?php
}
else {
	list($pagertop, $pagerbottom, $limit) = pager(25, $count, "mytorrents.php?");

	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrents.type, torrents.comments, torrents.leechers, torrents.seeders, torrents.id, categories.name AS cat_name, torrents.name, save_as, numfiles, added, size, views, visible, hits, times_completed, category FROM torrents LEFT JOIN categories ON torrents.category = categories.id $where ORDER BY id DESC $limit");

	//print($pagertop);

	torrenttable($res, "mytorrents");

	print($pagerbottom);
}

stdfoot();

?>
