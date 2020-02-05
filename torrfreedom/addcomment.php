<?php

require_once("include/bittorrent.inc.php");

if (!mkglobal("id"))
	die();

$id = intval($id);
if (!$id)
	die();

dbconn();

loggedinorreturn();

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM torrents WHERE id = $id");
$torrow = mysqli_fetch_array($res);
if (!$torrow)
	die();

stdhead("Add a comment to \"" . $torrow["name"] . "\"");

?>
<p class=note>Add a comment to torrent: "<?= htmlspecialchars($torrow["name"]) ?>"</p>
<p>
<form method="post" action="takecomment.php">
<input type="hidden" name="id" value="<?= $id ?>" />
<textarea class="input" name="main" rows="20" cols="60"></textarea>
</p>
<p><input class="input" type="submit" value="Post Comment" /></p>
</form>
<?php

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT comments.id, text, comments.added, username FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id DESC LIMIT 5");

$allrows = array();
while ($row = mysqli_fetch_array($res))
	$allrows[] = $row;

if (count($allrows)) {
	print("<hr />\n");
	print("<h2>Most recent comments, in reverse order</h2>\n");
	commenttable($allrows);
}

stdfoot();

?>
