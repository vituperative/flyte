<?php

require_once("include/bittorrent.inc.php");

if (!mkglobal("id"))
	die();

$id = intval($id);
if (!$id)
	die();

dbconn();

loggedinorreturn();

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM torrents WHERE id = $id");
$row = mysqli_fetch_array($res);
if (!$row)
	die();

stdhead("Edit torrent \"" . $row["name"] . "\"");

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && $CURUSER["admin"] != "yes")) {
	print("<h1>Can't edit this torrent</h1>\n");
	print("<p>You're not the rightful owner, or you're not <a href=\"login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">logged in</a> properly.</p>\n");
}
else {
	print("<form method=\"post\" action=\"takeedit.php\">\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"]))
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	print("<table class=\"table1\" border=\"1\" cellspacing=\"0\" cellpadding=\"2\">\n");
	tr("Torrent name", "<input type=\"text\" class=\"input\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" size=\"80\" />", 1);
	tr("Description<br />(no html allowed)", "<textarea name=\"descr\" class=\"input\" rows=\"10\" cols=\"80\">" . htmlspecialchars($row["ori_descr"]) . "</textarea>", 1);

	$s = "<select name=\"type\">\n";

	$cats = genrelist();
	foreach ($cats as $subrow) {
		$s .= "<option value=\"" . $subrow["id"] . "\"";
		if ($subrow["id"] == $row["category"])
			$s .= " selected=\"selected\"";
		$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
	}

	$s .= "</select>\n";
	tr("Type", $s, 1);
	tr("Visible", "<input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Visible on main page<br />Note that the torrent will automatically become visible when there's a seeder, and will become automatically invisible (dead) when there has been no seeder for a while. Use this switch to speed the process up manually. Also note that invisible (dead) torrents can still be viewed or searched for, it's just not the default.", 1);

	if ($CURUSER["admin"] == "yes")
		tr("Banned", "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Banned", 1);

	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Edit it!\" /> <input type=\"reset\" value=\"Revert changes\"></td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	print("<hr />\n");
	print("<p>\n");
	print("<form method=\"post\" action=\"delete.php\">\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"]))
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	print("Don't edit it, but <input type=\"submit\" value=\"delete it!\" />\n");
	print("(Yes I'm sure about that: <input type=\"checkbox\" name=\"sure\" value=\"1\" />)\n");
	print("</form>\n");
	print("</p>\n");
}

stdfoot();

?>
