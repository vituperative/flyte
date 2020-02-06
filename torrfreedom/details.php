<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}

require_once "include/bittorrent.inc.php";

function dltable($name, $arr, $torrent)
{
    global $CURUSER;

//    $s = "<b>" . count($arr) . " $name</b>";
    //    if (!count($arr)) {
    //        return $s;
    //    }

    $s .= "\n";
    $s .= "<table id=peerinfo>\n";
    $s .= "<tr><th>Destination</th><th>Uploaded</th><th>Downloaded</th><th>Complete</th><th>Time connected</th><th>Idle</th></tr>\n";
    $now = time();
    $admin = (isset($CURUSER) && $CURUSER["admin"] == "yes");

    foreach ($arr as $e) {
        $s .= "<tr>\n";
        $s .= "<td><code class=dest>" . truncate($e["ip"], 4, "") . "</code></td>\n";
        $s .= "<td>" . mksize($e["uploaded"]) . "</td>\n";
        $s .= "<td>" . mksize($e["downloaded"]) . "</td>\n";
        $ps = sprintf("%.0f%%", 100 * (1 - ($e["to_go"] / $torrent["size"])));
        $ps = ($ps < 0) ? "0.000%" : $ps;
        $s .= "<td class=downloadbar><span class=barOuter title=\"" . $ps . " complete\"><span class=barInner style=width:" . $ps . ">" . $ps . "</span></span></td>\n";
        $s .= "<td>" . mkprettytime($now - $e["st"]) . "</td>\n";
        $s .= "<td>" . mkprettytime($now - $e["la"]) . "</td>\n";
        $s .= "</tr>\n";
    }
    $s .= "</table>\n";
    return $s;
}

dbconn();

$id = $_GET["id"];
$id = intval($id);
if (!isset($id) || !$id) {
    die();
}

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(torrents.last_action) AS lastseed, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, DATE_FORMAT(CONVERT_TZ(torrents.added, @@session.time_zone, '+00:00'), '%H:%i, %a %D %M %Y') as added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, categories.name AS cat_name, users.username FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id")
or die();
$row = mysqli_fetch_array($res);

$owned = $admin = 0;
if (isset($CURUSER)) {
    if ($CURUSER["admin"] == "yes") {
        $owned = $admin = 1;
    } elseif ($CURUSER["id"] == $row["owner"]) {
        $owned = 1;
    }

}

if (!$row || ($row["banned"] == "yes" && !$admin)) {
    print("no such torrent");
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
            print("<p id=success> Torrent successfully uploaded!<br>");
            print("You can start seeding now. <b>Note</b> that the torrent won't be visible until you do that!</p>\n");
        } elseif (isset($_GET["edited"])) {
            print("<p id=success>Torrent successfully edited!<br>");
            if (isset($_GET["returnto"])) {
                print("<p><b>Return to <a href=\"" . htmlspecialchars($_GET["returnto"]) . "\">previous page</a>.</b>");
            }
            print("</p>\n");
        } elseif (isset($_GET["searched"])) {
            print("<h2>Your search for \"" . htmlspecialchars($_GET["searched"]) . "\" returned a single result:</h2>\n");
        }

        echo '';
        echo '<table id=details>';

        $url = "edit.php?id=" . $row["id"];
        if (isset($_GET["returnto"])) {
            $addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
            $url .= $addthis;
            @$keepget .= $addthis;
        }
        $editlink = "a href=$url class=edit";

        $s = "<span class=titletorrent title=\"" . htmlspecialchars($row["name"]) . "\">Torrent: " . htmlspecialchars($row["name"]) . "</span>";
        if ($owned) {
            $s .= " $spacer<$editlink><span title=\"Edit torrent\">Edit torrent</span></a>";
        }

        echo '<tr><th colspan=2>' . $s . '&nbsp;&nbsp;<a  class=download href=download.php?id=$id&amp;file=' . rawurlencode($row["filename"]) . "><span>" . htmlspecialchars($row["filename"]) . '</span></a></th></tr>';

        $rowcount = 0;

//        tr("Filename", "<a class=\"index\" href=\"download.php?id=$id&amp;file=" . rawurlencode($row["filename"]) . "\">" . htmlspecialchars($row["filename"]) . "</a>", 1, $rowcount++);
        if (!empty($row["descr"])) {
            tr("Description", $row["descr"], 1, $rowcount++);
        }

        if (isset($row["cat_name"])) {
            tr("Category", $row["cat_name"], 0, $rowcount++);
        } else {
            tr("Category", "(none selected)", 0, $rowcount++);
        }

        tr("Size", mksize($row["size"]) . " (" . $row["size"] . " Bytes)", 0, $rowcount++);
        tr("Info hash", "<code>" . preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])) . "</code>", $rowcount++);

        if ($row["visible"] == "no") {
            tr("Visible", "<span class=\"no small\" title=\"No seeds currently connected to this torrent\">No</span>", 1, $rowcount++);
        }

        if ($admin) {
            tr("Banned", $row["banned"], 0, $rowcount++);
        }

        tr("Added", $row["added"] . " UTC", 0, $rowcount++);
        if ($CURUSER) {
            tr("Views", $row["views"], 0, $rowcount++);
            tr("Hits", $row["hits"], 0, $rowcount++);
        }
        tr("Downloads", $row["times_completed"], 0, $rowcount++);

        $keepget = "";
        $uprow = isset($row["username"]) ? htmlspecialchars($row["username"]) : "<i>Unknown</i>";
        if (!$owned && $CURUSER) {
            tr("Uploader", $uprow, 1, $rowcount++);
        }

        if ($row["type"] == "multi") {
            if (!@$_GET["filelist"]) {
                tr("Files", $row["numfiles"] . '&nbsp;&nbsp;&nbsp;<a href=details.php?id=' . $id . '&amp;filelist=1$keepget#filelist>Show list</a>', 1, $rowcount++);
            } else {
//                tr("Files", $row["numfiles"], 1, $rowcount++);

                $s = "<table id=filelist>\n";

                $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM files WHERE torrent = $id ORDER BY id");
                while ($subrow = mysqli_fetch_array($subres)) {
                    $s .= "<tr><td>" . preg_replace(',[^/]+$,', '<b>$0</b>', htmlspecialchars($subrow["filename"])) . "</td><td>" . mksize($subrow["size"]) . "</td></tr>\n";
                }

                $s .= "</table>\n";
                tr("<a name=\"filelist\">File List</a><br /><a href=\"details.php?id=$id$keepget\">Hide list</a>", $s, 1, $rowcount++);
            }
        }

        tr("Last seeder seen", mkprettytime($row["lastseed"]) . " ago", 0, $rowcount++);

        if (!@$_GET["dllist"]) {
            if ($row["seeders"] || $row["leechers"]) {
                $showpeers = "&nbsp;&nbsp;&nbsp;<a href=details.php?id=" . $id . "&amp;dllist=1" . $keepget . "#seeds>View full list</a>";
            } else {
                $showpeers = "";
            }

            tr("Peers", "Seeds: " . $row["seeders"] . "&nbsp;&nbsp;&nbsp;Downloaders: " . $row["leechers"] . $showpeers, 1, $rowcount++);
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
                tr("<a name=\"seeds\">Seeds</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">Hide list</a>", dltable("Seeds", $seeders, $row), 1, $rowcount++);
            }

            if ($downloaders) {
                tr("<a name=\"leeches\">Leechers</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">Hide list</a>", dltable("Leechers", $downloaders, $row), 1, $rowcount++);
            }

        }

        print("</table>\n");

//        print("<hr />\n");
    } else {
        stdhead("Comments for torrent \"" . $row["name"] . "\"");
        print("<p class=note id=return><a href=\"details.php?id=$id\">Return to details page</a></p>\n");
    }

//    print("<p><a name=\"startcomments\"></a></p>\n");

    if ($CURUSER) {
        $commentbar = "<p id=addcomment><a class=index href=addcomment.php?id=$id>Add a comment</a></p>\n";
    } else {
        $commentbar = "<p id=needlogin class=note>Please <a href=login.php>login</a> to add a comment to this torrent.</p>";
    }

    $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM comments WHERE torrent = $id");
    $subrow = mysqli_fetch_array($subres);
    $count = $subrow[0];

    if (!$count) {
//        print("<p id=\"nocomments\" class=\"important\" align=\"center\">No comments yet</p>\n");
    } else {
        list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=$id&", array("lastpagedefault" => 1));

        $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT comments.id, text, DATE_FORMAT(CONVERT_TZ(comments.added, @@session.time_zone, '+00:00'), '%H:%i, %a %D %M %Y') as added, username FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id $limit");
        $allrows = array();
        while ($subrow = mysqli_fetch_array($subres)) {
            $allrows[] = $subrow;
        }

        print($commentbar);
        print($pagertop);

        commenttable($allrows);

        print($pagerbottom);
    }

    print($commentbar);
}

stdfoot();
