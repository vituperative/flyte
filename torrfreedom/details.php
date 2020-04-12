<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}

require_once "include/bittorrent.inc.php";

function dltable($name, $arr, $torrent)
{
    global $CURUSER;

    $s = "\n";
    $s .= "<table id=peerinfo>\n";
    $s .= "<tr><th>Peer</th><th>Uploaded</th><th>Downloaded</th><th>Completed</th><th>Time connected</th><th>Idle</th></tr>\n";
    $now = time();
    $admin = (isset($CURUSER) && $CURUSER["admin"] == "yes");

    foreach ($arr as $e) {
        $s .= "<tr>\n";
        if ($CURUSER) {
            $s .= "<td><code class=dest>" . truncate($e["ip"], 4, "") . "</code></td>\n";
        } else {
            $s .= "<td class=peer title=\"Peer destinations can only been seen when logged in\"></td>\n";
        }

        $s .= "<td>" . mksize($e["uploaded"]) . "</td>\n";
        $s .= "<td>" . mksize($e["downloaded"]) . "</td>\n";
        $ps = sprintf("%.1f%%", 100 * (1 - ($e["to_go"] / $torrent["size"])));
        $ps = ($ps < 0) ? "0%" : $ps;
        $s .= "<td class=downloadbar><span class=barOuter title=\"" . $ps . " complete\"><span class=barInner style=width:" . $ps . ">" . $ps . "</span></span></td>\n";
        $s .= "<td>" . mkprettytime($now - $e["st"]) . "</td>\n";
        $s .= "<td>" . mkprettytime($now - $e["la"]) . "</td>\n";
        $s .= "</tr>\n";
    }
    $s .= "</table>\n";
    return $s;
}

dbconn(0);

if (!isset($_GET["id"]) && !isset($_GET["info_hash"]) ) {
    print("<p id=toast class=warn><span class=title>Torrent does not exist!</span><br>The torrent file you are requesting does not exist on the server.</p>");
    header("Refresh: 5; url=" . $tracker_path);
}

$row = "";
$id=0;


if(isset($_GET["info_hash"])){
	$hash = $_GET["info_hash"];
	//$hash = preg_replace('/ *$/s', "", $hash);
	$hash=urldecode($hash);
	//print("Hash:".$hash);
	//$hash = hex2bin($hash);

	//print("Hash: ".$hash);
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrents.seeders, torrents.id, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(torrents.last_action) AS lastseed, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, DATE_FORMAT(CONVERT_TZ(torrents.added, @@session.time_zone, '+00:00'), '%H:%i, %a %D %M %Y') as added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, categories.name AS cat_name, category, users.username FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.info_hash = '$hash'")
	or die();
	$row = mysqli_fetch_array($res);
	$id=intval($row['id']);

}else{
	$id = $_GET["id"];
	$id = intval($id);

	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(torrents.last_action) AS lastseed, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, DATE_FORMAT(CONVERT_TZ(torrents.added, @@session.time_zone, '+00:00'), '%H:%i, %a %D %M %Y') as added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, categories.name AS cat_name, category, users.username FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id")
	or die();
	$row = mysqli_fetch_array($res);
}

$owned = $admin = 0;
if (isset($CURUSER)) {
    if ($CURUSER["admin"] == "yes") {
        $owned = $admin = 1;
    } elseif ($CURUSER["id"] == $row["owner"]) {
        $owned = 1;
    }
}

if (!$row || ($row["banned"] == "yes" && !$admin)) {
    stdhead();
    if (!$row)
        print("<p id=toast class=warn><span class=title>Torrent does not exist!</span>The torrent file you are requesting does not exist on the server.</p>");
    else
        print("<p id=toast class=warn><span class=title>Torrent Unavailable.</span>The torrent you are trying to access has been blacklisted from this tracker.</p>");
    header("Refresh: 5; url=" . $tracker_path);
} else {
    if (isset($_GET["hit"])) {
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET views = views + 1 WHERE id = $id");
        if (isset($_GET["tocomm"])) {
            header("Refresh: 0; url=details.php?id=$id&page=0#startcomments");
        } elseif (isset($_GET["filelist"])) {
            header("Refresh: 0; url=details.php?id=$id&filelist=1#filelist");
        } elseif (isset($_GET["toseeders"])) {
            header("Refresh: 0; url=details.php?id=$id&dllist=1#seeds");
        } elseif (isset($_GET["todlers"])) {
            header("Refresh: 0; url=details.php?id=$id&dllist=1#leeches");
        } else {
            header("Refresh: 0; url=details.php?id=$id");
        }

        exit();
    }

    if (!isset($_GET["page"])) {
        stdhead("Details for torrent \"" . $row["name"] . "\"");

        if (isset($CURUSER) && ($CURUSER["id"] == $row["owner"] || $CURUSER["admin"] == "yes")) {
            $owned = 1;
        } else {
            $owned = 0;
        }

        $spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

        if (isset($_GET["uploaded"])) {
            print("<p id=toast class=success><span class=title>Torrent uploaded!</span><br>");
            print("Note: Until you start seeding, it will not be visible on the tracker.</p>\n");
        } elseif (isset($_GET["edited"])) {
            print("<p id=toast class=success><span class=title>Success!</span><br>Torrent details have been updated!</p>\n");
        } elseif (isset($_GET["searched"])) {
            print("<p id=toast class=success><span class=title>Search results</span><br>1 match found for: " . htmlspecialchars($_GET["searched"]) . "</p>\n");
        }

        echo "<div class=tablewrap>\n<table id=details>\n";

        $url = "edit.php?id=" . $row["id"];
        if (isset($_GET["returnto"])) {
            $addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
            $url .= $addthis;
            @$keepget .= $addthis;
        }
        $editlink = "a href=\"$url\" title=\"Edit torrent details\" class=edit";

        if (isset($row["cat_name"])) {
            $s = "<a style=float:none href=\"./?cat=" . $row["category"] . "\" class=\"catlink\" data-tooltip=\"" . $row["cat_name"] . "\"><img src=\"" . $tracker_path . "pic/" . $row["category"] . ".png\" width=24 height=24></a><span class=titletorrent title=\"" . htmlspecialchars($row["name"]) . "\">Torrent: " . htmlspecialchars($row["name"]) . "</span>";
        } else {
            $s = "<span class=\"catlink\" data-tooltip=\"Uncategorized\"><img src=\"" . $tracker_path . "pic/unknown.png\" width=24 height=24></span><span class=titletorrent title=\"" . htmlspecialchars($row["name"]) . "\">Torrent: " . htmlspecialchars($row["name"]) . "</span>";
        }
        if ($owned) {
            $s .= " $spacer<$editlink><span>Edit torrent</span></a>";
        }

        if ( $admin ) {
            echo '<tr><th colspan=2>' . $s . '&nbsp;&nbsp;<a title="Download ' . $row["filename"] . '" class=download href="download.php?id=' . $id . '&amp;file=' . rawurlencode($row["filename"]) . '"><span>' . htmlspecialchars($row["filename"]) . '</span></a><a title="Delete torrent" class=nuke href="admin/delTorrent.php?wdel_id=\'' . $CURUSER["username"] . '\'&amp;id=\'' . $id . '\'&amp;&amp;name=\'' . htmlspecialchars($row["name"]) . '\'"></a></th></tr>';
        } elseif($owned){
            echo '<tr><th colspan=2>' . $s . '&nbsp;&nbsp;<a title="Download ' . $row["filename"] . '" class=download href="download.php?id=' . $id . '&amp;file=' . rawurlencode($row["filename"]) . '"><span>' . htmlspecialchars($row["filename"]) . '</span></a><a title="Delete torrent" class=nuke href="user/delTorrent.php?wdel_id=\'' . $CURUSER["username"] . '\'&amp;id=\'' . $id . '\'&amp;&amp;name=\'' . htmlspecialchars($row["name"]) . '\'"></a></th></tr>';
	}else {
            echo '<tr><th colspan=2>' . $s . '&nbsp;&nbsp;<a title="Download ' . $row["filename"] . '" class=download href="download.php?id=' . $id . '&amp;file=' . rawurlencode($row["filename"]) . '"><span>' . htmlspecialchars($row["filename"]) . '</span></a></th></tr>';
        }
        $rowcount = 0;

        if (!empty($row["descr"])) {
            print('<tr id=description><td colspan=2><div>' . htmlspecialchars_decode($row["descr"]) . '</div></td></tr>');
        }

        print("<tr><td>Info hash</td><td><code>" . preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])) . "</code></td></tr>");
        tr("Size", mksize($row["size"]) . " (" . $row["size"] . " Bytes)", 0, $rowcount++);

        if ($row["visible"] == "no" && !$CURUSER) {
            tr("Visible", "<span class=\"no small\" title=\"No seeders currently connected to this torrent\">No</span>", 1, $rowcount++);
        }

        if ($admin) {
            tr("Banned", $row["banned"], 0, $rowcount++);
        }

        tr("Added", $row["added"] . " UTC", 0, $rowcount++);
        if ($admin) {
            print("<tr><td>Stats</td><td><b>Downloads:</b> " . $row["times_completed"] . "&nbsp;&nbsp;&nbsp;<b>Views:</b> " . $row["views"] . "&nbsp;&nbsp;&nbsp;<b>Hits:</b> " . $row["hits"]);
            if ($row["visible"] == "no") {
                print("&nbsp;&nbsp;&nbsp;<b>Visible:</b> <span class=\"no small\" title=\"No seeders currently connected to this torrent\">No</span>");
            }
            print("</tr>");
        } else if ($CURUSER) {
            tr("Downloads", $row["times_completed"], 0, $rowcount++);
        }

        $keepget = "";
        $uprow = isset($row["username"]) ? htmlspecialchars($row["username"]) : "<i>Unknown</i>";
        if (!$owned && $CURUSER) {
            tr("Uploader", $uprow, 1, $rowcount++);
        }

        if ($row["type"] == "multi") {
            if (!@$_GET["filelist"]) {
                tr("Files", $row["numfiles"] . '&nbsp;&nbsp;&nbsp;<a href="details.php?id=' . $id . '&amp;filelist=1$keepget#filelist">Show list</a>', 1, $rowcount++);
            } else {
                if (intval($row["numfiles"]) > 1)
                    tr("Files", $row["numfiles"] . "&nbsp;&nbsp;<a id=files href=\"details.php?id=$id$keepget\">Hide list</a>", 1, $rowcount++);

                $s = "<table id=filelist>\n";

                $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM files WHERE torrent = $id ORDER BY id");
                while ($subrow = mysqli_fetch_array($subres)) {
                    $s .= "<tr><td>" . preg_replace(',[^/]+$,', '<b>$0</b>', htmlspecialchars($subrow["filename"])) . "</td><td>" . mksize($subrow["size"]) . "</td></tr>\n";
                }

                $s .= "</table>\n";
                tr2($s, 1, $rowcount++);
            }
        }

        if (!@$_GET["dllist"]) {
            if ($row["seeders"] || $row["leechers"]) {
                $showpeers = "&nbsp;&nbsp;&nbsp;<a href=\"details.php?id=" . $id . "&amp;dllist=1" . $keepget . "#seeds\">Show peers</a>";
            } else {
                $showpeers = "";
            }

            tr("Peers", "<b>Seeders:</b> " . $row["seeders"] . "&nbsp;&nbsp;&nbsp;<b>Downloaders:</b> " . $row["leechers"] . "&nbsp;&nbsp;&nbsp;<b>Last seeder seen: </b>" . mkprettytime($row["lastseed"]) . " ago" . $showpeers, 1 . $rowcount++);
        } else {
            $downloaders = array();
            $seeders = array();
            $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT seeder, ip, port, uploaded, downloaded, to_go, UNIX_TIMESTAMP(started) AS st, connectable, UNIX_TIMESTAMP(last_action) AS la FROM peers WHERE torrent = $id");
            while ($subrow = mysqli_fetch_array($subres)) {
                if ($subrow["seeder"] == "yes") {
                    $seeders[] = $subrow;
                } else {
                    $downloaders[] = $subrow;
                }
            }

            function leech_sort($a, $b)
            {
                $x = $a["to_go"];
                $y = $b["to_go"];
                if ($x == $y) {
                    return 0;
                }

                if ($x < $y) {
                    return -1;
                }

                return 1;
            }
            function seed_sort($a, $b)
            {
                $x = $a["uploaded"];
                $y = $b["uploaded"];
                if ($x == $y) {
                    return 0;
                }

                if ($x < $y) {
                    return 1;
                }

                return -1;
            }

            usort($seeders, "seed_sort");
            usort($downloaders, "leech_sort");

            if ($seeders) {
                tr("<b>Seeds</b><br><a id=seeds href=\"details.php?id=$id$keepget\" class=\"sublink\">Hide list</a>", dltable("Seeds", $seeders, $row), 1, $rowcount++);
            }

            if ($downloaders) {
                tr("<b>Leechers</b><br><a id=leeches href=\"details.php?id=$id$keepget\" class=\"sublink\">Hide list</a>", dltable("Leechers", $downloaders, $row), 1, $rowcount++);
            }

        }

        print("</table>\n");

    } else {
        stdhead("Comments for torrent \"" . $row["name"] . "\"");
        print("<div class=tablewrap><p class=note id=return><a href=\"details.php?id=$id\">Return to details page for torrent: " . $row["name"] . "</a></p>\n");
    }

    if ($CURUSER) {
        $commentbar = "<p id=addcomment><a href=\"addcomment.php?id=$id\">Add a comment</a></p>\n";
    } else {
        $commentbar = "<p id=needlogin class=note>Please <a href=\"login.php\">login</a> to add a comment to this torrent.</p>\n";
    }

    $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM comments WHERE torrent = $id");
    $subrow = mysqli_fetch_array($subres);
    $count = $subrow[0];

    if ($count) {
        list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=$id&", array("lastpagedefault" => 1));

        $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT comments.id, text, DATE_FORMAT(CONVERT_TZ(comments.added, @@session.time_zone, '+00:00'), '%H:%i, %a %D %M %Y') as added, username FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id $limit");
        $allrows = array();
        while ($subrow = mysqli_fetch_array($subres)) {
            $allrows[] = $subrow;
        }

        commenttable($allrows);

        print($commentbar);
        print($pagerbottom);
    }

    print("</div>\n");
}

stdfoot();
