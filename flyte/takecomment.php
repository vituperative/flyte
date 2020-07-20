<?php
require_once "include/bittorrent.inc.php";
dbconn(0);

if (!isset($CURUSER)) {
    die();
}

if (!mkglobal("main:id")) {
    die();
}

$id = intval($id);
if (!$id) {
    die();
}

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT 1 FROM torrents WHERE id = $id");
$row = mysqli_fetch_array($res);
if (!$row) {
    die();
}

mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO comments (user, torrent, added, text, ori_text) VALUES (" .
    $CURUSER["id"] . ",$id, NOW(), " . sqlesc(parsedescr($main)) . "," . sqlesc($main) . ")");

$newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);

mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET comments = comments + 1 WHERE id = $id");

header("Refresh: 0; url=details.php?id=$id&viewcomm=$newid#comm$newid");
