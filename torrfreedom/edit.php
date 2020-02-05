<?php
require_once "include/bittorrent.inc.php";
if (!mkglobal("id")) {
    die();
}
$id = intval($id);
if (!$id) {
    die();
}
dbconn();
loggedinorreturn();
$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM torrents WHERE id = $id");
$row = mysqli_fetch_array($res);
if (!$row) {
    die();
}
stdhead("Edit torrent \"" . $row["name"] . "\"");

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && $CURUSER["admin"] != "yes")) {
    print("<p id=warn>Sorry, you can't edit this torrent. ");
    print("You're not the rightful owner, or you're not <a href=\"login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">logged in</a>.</p>\n");
} else {
    print("<form method=\"post\" action=\"takeedit.php\">\n");
    print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
    if (isset($_GET["returnto"])) {
        print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
    }

    print("<table id=torrentedit>\n");
    print("<tr><th colspan=2>Edit Torrent</th></tr>\n");
    tr("Name", "<input type=\"text\" class=\"input\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" size=\"80\" />", 1);
    tr("Description", "<textarea name=\"descr\" class=\"input\" rows=\"10\" cols=\"80\">" . htmlspecialchars($row["ori_descr"]) . "</textarea>", 1);

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
    tr("Visible", "<label><input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "") . " value=\"1\" /> Visible on main page</label>\n<p>Note: the torrent will automatically become visible when there's a seeder, and will be hidden automatically when there has been no seeder for a while. Use this switch to speed the process up manually.</p>", 1);

    if ($CURUSER["admin"] == "yes") {
        tr("Banned", "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "") . " value=\"1\" /> Banned", 1);
    }

    print("</table>\n");
    print("</form>\n");
    print("<hr />\n");
    print("<p>\n");
    print("<form method=\"post\" action=\"delete.php\">\n");
    print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
    if (isset($_GET["returnto"])) {
        print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
    }

    print("<tr id=dostuff><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Update Torrent\" /> ");
    print("<input type=\"submit\" value=\"Delete Torrent\" />\n");
    print("<input type=\"checkbox\" name=\"sure\" value=\"1\" /> Confirm delete</td></tr>\n");
    print("</form>\n");
    print("</p>\n");
}

stdfoot();
