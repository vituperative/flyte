<?php
require 'admin_class.php';
$admin = new admin();

$result = "";
$limit = 60;
$offset = 0;

if (isset($_GET['offset'])) {
    $offset = $_GET['offset'] * $limit;
}

if (!isset($_GET['user'])) {
    $result = $admin->getAllTorrents($offset);
    $username = "All Torrents" ;
} else {
    $result = $admin->getTorrentsByUserNick($_GET['user'], $offset);
    $username = "Torrents owned by: " . $_GET['user'];
}

echo "<style type=text/css>body {min-width: 1000px !important;}</style>";
echo "<div id=server class=torrents>\n<table>\n<tr>";
echo "<th>Type</th>";
echo "<th>$username</th>";
echo "<th>Seed</th>";
echo "<th>Leech</th>";
echo "<th>DL's</th>";
echo "<th>Views</th>";
echo "<th>Cmts</th>";
echo "<th>Added</th>";
echo "<th>Visible</th>";
echo "<th>Banned</th>";
echo "<th>Nuke</th></tr>";

function delTorrent($torid, $name, $user)
{
    printf("<td><a href=\"delTorrent.php?wdel_id='%s'&amp;name='%s'&amp;user='%s'\" class=button><span class=no></span></a></td>", $torid, $name, $user);
}

while ($row = mysqli_fetch_array($result)) {
    echo "<tr>";
    $torid = $row['id'];

    echo "<td>";
    if (isset($row["category"])) {
        $cat_name = $admin->getNameOfCategoryByID($row['category']);
        print("<a href=\"./?cat=" . $row['category'] . "\" class=\"catlink\" data-tooltip=\"" . $cat_name . "\"><img src=\"" . $tracker_path . "pic/" . $row['category'] . ".png\" width=24 height=24></a>");
    } else {
        print("<span class=\"catlink\" data-tooltip=\"Uncategorized\"><img src=\"" . $tracker_path . "pic/unknown.png\" width=24 height=24></span>");
    }
    echo "</td>";
    //https://stackoverflow.com/questions/14674834/php-convert-string-to-hex-and-hex-to-string
    echo "<td><a href=\"../details.php?id=" . $row['id'] . "\">" . $row['name'] . "</a><br><code>" . implode(unpack("H*", $row['info_hash'])) .  "</code></td>";
    echo "<td>" . $row['seeders'] .  "</td>";
    echo "<td>" . $row['leechers'] .  "</td>";
    echo "<td>" . $row['times_completed'] .  "</td>";
    echo "<td>" . $row['views'] .  "</td>";
    echo "<td>" . $row['comments'] .  "</td>";
    echo "<td>" .  preg_replace("/ .*/", "", $row["added"]) .  "</td>";
    echo "<td><form action=modifytorrent.php method=GET><input type=checkbox name=visible><input type=submit value=Apply><input type=hidden name=torid value=$torid /><input type=hidden name=do value=visible /></form></td>"; // TODO apply in situ, not via vistorrent.php
    echo "<td>
<form action=modifytorrent.php method=GET><input type=checkbox name=banned><input type=submit value=Apply><input type=hidden name=torid value=$torid /><input type=hidden name=do value=banned /></form></td>"; // TODO apply in situ, not via bantorrent.php
    delTorrent($torid, $row['name'], $_GET['user']);
    echo "</tr>\n";
}

echo "</table></div>";
stdfoot();
