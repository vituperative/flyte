<?php

require_once "include/bittorrent.inc.php";

function bark($msg)
{
    genbark($msg, "Edit failed!");
}

if (!mkglobal("id:name:descr:type")) {
    bark("Missing form data!");
}

$id = intval($id);
if (!$id) {
    die();
}

dbconn();

loggedinorreturn();

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT owner, filename, save_as FROM torrents WHERE id = $id");
$row = mysqli_fetch_array($res);
if (!$row) {
    die();
}

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && $CURUSER["admin"] != "yes")) {
    bark("You're not the owner! How did that happen?\n");
}

$updateset = array();

$fname = $row["filename"];
preg_match('/^(.+)\.torrent$/si', $fname, $matches);
$shortfname = $matches[1];
$dname = $row["save_as"];

$updateset[] = "name = " . sqlesc($name);
$updateset[] = "search_text = " . sqlesc(searchfield("$shortfname $dname $torrent"));
$updateset[] = "descr = " . sqlesc(parsedescr($descr));
$updateset[] = "ori_descr = " . sqlesc($descr);
$updateset[] = "category = " . intval($type);
if ($CURUSER["admin"] == "yes") {
    if ($_POST["banned"]) {
        $updateset[] = "banned = 'yes'";
        $_POST["visible"] = 0;
    } else {
        $updateset[] = "banned = 'no'";
    }

}
$updateset[] = "visible = '" . ($_POST["visible"] ? "yes" : "no") . "'";

mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $id");

$returl = "details.php?id=$id&edited=1";
if (isset($_POST["returnto"])) {
    $returl .= "&returnto=" . urlencode($_POST["returnto"]);
}

header("Refresh: 0; url=$returl");
