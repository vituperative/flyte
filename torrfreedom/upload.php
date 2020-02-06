<?php
require_once "include/bittorrent.inc.php";
dbconn();
loggedinorreturn();
stdhead("Upload");
?>

<div class=main>
<form enctype=multipart/form-data action=takeupload.php method=post accept-charset=utf-8>
<input type=hidden name=MAX_FILE_SIZE value=<?=$max_torrent_size?> />
<p class=note id=upload>Only upload torrents you're going to seed!<br>Uploaded torrents won't be visible on the main page until you start seeding them.</p>
<table id=uploader>
<?php

tr("Upload file", "<input class=input type=file name=file size=60 required />\n", 1);
tr("Torrent name", "<input class=input type=text name=name size=80 placeholder=\"Taken from filename if not specified.\"/>", 1);
tr("Description", "<textarea class=input name=descr rows=10 cols=80 required></textarea>", 1);

$s = "<select class=input name=type required>\n<option value=\"\">Select category&hellip;</option>\n";

$cats = genrelist();
foreach ($cats as $row) {
    $s .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";
}

$s .= "</select>\n";
tr("Category", $s, 1);

?>
<tr id=dostuff><td colspan=2><input class=input type=submit value="Upload Torrent"></td></tr>
</table>
</form>
</div>
<?php stdfoot(); ?>
