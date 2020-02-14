<?php
require_once "include/bittorrent.inc.php";
if (!mkglobal("id")) {
    die();
}
$id = intval($id);
if (!$id) {
    die();
}
dbconn(0);
loggedinorreturn();
$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM torrents WHERE id = $id");
$row = mysqli_fetch_array($res);
if (!$row) {
    die();
}
stdhead("Edit torrent \"" . $row["name"] . "\"");

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && $CURUSER["admin"] != "yes")) {
    print("<p id=toast class=warn>Sorry, you do not have permission to edit this torrent!");
    print("You're not the rightful owner, or you're not <a href=\"login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">logged in</a>.</p>\n");
} else {
    print("<div class=tablewrap>\n");
    print("<form method=\"post\" action=\"takeedit.php\">\n");
    print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
    if (isset($_GET["returnto"])) {
        print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
    }

    print("<table id=torrentedit>\n");
    print("<tr><th colspan=2>Edit Torrent</th></tr>\n");
    tr("Name", "<input type=text class=input name=name value=\"" . htmlspecialchars($row["name"]) . "\" size=80 />", 1);
    tr("Description", "<textarea name=descr class=input rows=10 cols=80>" . htmlspecialchars($row["ori_descr"]) . "</textarea>", 1);

    $s = "<select name=\"type\">\n";

    $cats = genrelist();
    foreach ($cats as $subrow) {
        $s .= "<option value=\"" . $subrow["id"] . "\"";
        if ($subrow["id"] == $row["category"]) {
            $s .= " selected=\"selected\"";
        }

        $s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
    }

    $s .= "</select>\n";
    tr("Category", $s, 1);
    tr("Visible", "<label title=\"Note: the torrent will automatically become visible when there's a seeder, and will be hidden automatically when there has been no seeder for a while. Use this switch to speed the process up manually.\"><input type=checkbox name=visible" . (($row["visible"] == "yes") ? " checked=checked" : "") . " value=1 /> Visible on main page</label>\n", 1);

    if ($CURUSER["admin"] == "yes") {
        tr("Banned", "<label><input type=checkbox name=banned" . (($row["banned"] == "yes") ? " checked=checked" : "") . " value=1 /> Blacklist torrent</label>", 1);
    }

    $query = $_SERVER["QUERY_STRING"];
    print("<tr id=dostuff><td colspan=2 align=center><input type=submit value=\"Update Torrent\" /> <a class=button href=details.php?" . $query . ">Cancel Edit</a>");
    print("</table>\n");
    print("</form>\n");
    print("<form method=post action=delete.php>\n");
    print("<p id=nuke>\n");
    print("<input type=hidden name=id value=\"$id\">\n");
    if (isset($_GET["returnto"])) {
        print("<input type=hidden name=returnto value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
    }
    print("<input type=submit value=\"Delete Torrent\" />\n");
    print("<label><input type=checkbox name=sure value=1 /> Confirm</label></td></tr>\n");
    print("</p>\n");
    print("</form>\n");
    print("</div>\n");
}

stdfoot();
