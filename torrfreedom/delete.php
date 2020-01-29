<?php

require_once("include/bittorrent.inc.php");

function bark($msg) {
	genbark($msg, "Delete failed!");
}

if (!mkglobal("id"))
	bark("missing form data");

$id = intval($id);
if (!$id)
	die();

dbconn();

loggedinorreturn();

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT owner FROM torrents WHERE id = $id");
$row = mysqli_fetch_array($res);
if (!$row)
	die();

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && $CURUSER["admin"] != "yes"))
	bark("You're not the owner! How did that happen?\n");

if (!@$_POST["sure"])
	bark("Can't delete anything unless you're really sure.\n");

deletetorrent($id);

stdhead("Torrent deleted!");

if (isset($_POST["returnto"]))
	$ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">Go back to whence you came</a>";
else
	$ret = "<a href=\"./\">Back to index</a>";

?>
<h2>Torrent deleted!</h2>
<p><?= $ret ?></p>
<?php

stdfoot();

?>
