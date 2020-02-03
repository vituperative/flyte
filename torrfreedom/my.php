<?php

require_once("include/bittorrent.inc.php");

dbconn();

loggedinorreturn();

stdhead($CURUSER["username"] . "'s private page");

if (isset($_GET["edited"])) {
	print("<p id=success>Profile updated!</p>\n");
}
else
	print("<p id=success>Welcome, " . htmlspecialchars($CURUSER["username"]) . "!</p>\n");

?>
<form method="post" action="takeprofedit.php">
<table id=myaccount>
<tr><th colspan="2">Your profile&nbsp;&nbsp;<a href="mytorrents.php">View or edit your torrents</a></th></tr>
<?php

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM torrents WHERE owner=" . $CURUSER["id"]);
$row = mysqli_fetch_array($res);
tr("Uploaded torrents", $row[0]);

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM comments WHERE user=" . $CURUSER["id"]);
$row = mysqli_fetch_array($res);
tr("Comments posted", $row[0]);

tr("Change password", "<input type=\"password\" name=\"chpassword\" size=\"40\" />", 1);
tr("Type password again", "<input type=\"password\" name=\"passagain\" size=\"40\" />", 1);

?>
<tr id=dostuff><td colspan="2"><input type="submit" value="Submit changes!" /> <input type="reset" value="Revert changes!" /></td></tr>
</table>
</form>
<?php
stdfoot();
?>
