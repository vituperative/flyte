<?php

require_once("include/bittorrent.inc.php");

dbconn();

loggedinorreturn();

stdhead($CURUSER["username"] . "'s private page");

if (isset($_GET["edited"])) {
	print("<h1>Profile updated!</h1>\n");
}
else
	print("<h1>Welcome, " . htmlspecialchars($CURUSER["username"]) . "!</h1>\n");

?>
<table border="1" cellpadding="5" cellspacing="0" bordercolor="#667766" align="center">
<tr>
<td align="center" width="50%"><a href="logout.php" class="biglink">Logout</a></td>
<td align="center" width="50%"><a href="mytorrents.php" class="biglink">View or edit your torrents</a></td>
</tr>
<tr>
<td colspan="2">
<form method="post" action="takeprofedit.php">
<table class="table1" border="1" cellpadding="5" cellspacing="0" bordercolor="#667766" width="100%">
<tr><td colspan="2" class="heading" align="center">Your profile</td></tr>
<?php

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM torrents WHERE owner=" . $CURUSER["id"]);
$row = mysqli_fetch_array($res);
tr("Uploaded torrents", $row[0]);

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM comments WHERE user=" . $CURUSER["id"]);
$row = mysqli_fetch_array($res);
tr("Written comments", $row[0]);

print("<tr><td colspan=\"2\" align=\"center\">Edit your settings</td></tr>\n");

tr("Change password", "<input type=\"password\" name=\"chpassword\" size=\"40\" />", 1);
tr("Type password again", "<input type=\"password\" name=\"passagain\" size=\"40\" />", 1);


?>
<tr><td colspan="2" align="center"><input type="submit" value="Submit changes!" /> <input type="reset" value="Revert changes!" /></td></tr>
</table>
</form>
</td>
</tr>
</table>
<?php

stdfoot();

?>
