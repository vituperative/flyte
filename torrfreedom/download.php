<?php
require_once("include/bittorrent.inc.php");

// I2P: fucking idiots...
$id = intval($_GET['id']);
$file = $_GET['file'];

dbconn();

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT 1 FROM torrents WHERE id = $id");
$row = mysqli_fetch_array($res);

$fn = "$torrent_dir/$id.torrent";

if (!$row || !is_file($fn) || !is_readable($fn))
	httperr();

mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET hits = hits + 1 WHERE id = $id");

header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header("Content-Disposition: filename=\"" . htmlentities(urlencode($file)) . "\"");
header("Content-Description: ". htmlentities(urlencode($file)));
header("Content-Type: application/x-bittorrent");
readfile($fn);

?>
