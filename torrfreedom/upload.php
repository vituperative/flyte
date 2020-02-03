<?php

require_once "include/bittorrent.inc.php";

dbconn();

loggedinorreturn();

stdhead("Upload");

?>

<div class=main>
<form enctype="multipart/form-data" action="takeupload.php" method="post" accept-charset="utf-8">
<input type="hidden" name="MAX_FILE_SIZE" value="<?=$max_torrent_size?>" />
<!--
<p><h3>The tracker's announce URLs are:</h3>
<input onclick="this.select();" class="gensmall" readonly size="77" style="
border: 1px solid white;" value="<?=$tracker_url_key?>/a"><br>
<input onclick="this.select();" class="gensmall" readonly size="77" style="
border: 1px solid white;" value="<?=$tracker_url_key?>/announce"><br>
<input onclick="this.select();" class="gensmall" readonly size="77" style="
border: 1px solid white;" value="<?=$tracker_url_key?>/announce.php"></p>
-->
<p class="note" id="upload">Only upload torrents you're going to seed!<br>Uploaded torrents won't be visible on the main page until you start seeding them.</p>

<table id="uploader" class="table1" border="2" cellspacing="0" cellpadding="5">
<?php

tr("Upload file", "<input class=\"input\" type=\"file\" name=\"file\" size=\"60\" />\n", 1);
tr("Torrent name", "<input class=\"input\" type=\"text\" name=\"name\" size=\"80\" placeholder=\"Taken from filename if not specified.\"/>", 1);
tr("Description<br />(no html allowed)", "<textarea class=\"input\" name=\"descr\" rows=\"10\" cols=\"80\"></textarea>", 1);

$s = "<select class=\"input\" name=\"type\">\n<option value=\"0\">(choose one)</option>\n";

$cats = genrelist();
foreach ($cats as $row) {
    $s .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";
}

$s .= "</select>\n";
tr("Category", $s, 1);

?>
<tr><td align="center" colspan="2"><input class="input" type="submit" value="Upload Torrent" /></td></tr>
</table>
</form>
</div>
<?php
stdfoot();

?>
