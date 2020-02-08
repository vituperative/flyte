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
<form method="post" action="takecomment.php">
<input type="hidden" name="id" value="<?= $id ?>" />
<table id=comment>
<tr><th>Add comment to torrent: <?= htmlspecialchars($torrow["name"]) ?></th></tr>
<tr><td><textarea class="input" name="main" rows="20" cols="60" required></textarea></td></tr>
<tr id=dostuff><td><input class="input" type="submit" value="Post Comment" /></td></tr>
</table>
</form>
<?php

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT comments.id, text, comments.added, username FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id DESC LIMIT 5");

$allrows = array();
while ($row = mysqli_fetch_array($res))
    $allrows[] = $row;

if (count($allrows)) {
    print("<hr>\n");
    print("<h3 id=recentcomments>Recent Comments</h3>\n");
    commenttable($allrows);
}

stdfoot();

?>
