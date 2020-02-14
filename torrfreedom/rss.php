<?php
if (count($_GET)) {
    return;
}

if (ob_get_level() == 0) {
    ob_start();
}

require_once "include/bittorrent.inc.php";
require_once "include/benc.php";
dbconn();

// Convert a mysql datetime value unto a unix timestamp (epoch)
function dttm2unixtime($dttm2timestamp_in)
{
    //    returns unixtime stamp for a given date time string that comes from DB
    $date_time = explode(" ", $dttm2timestamp_in);
    $date = explode("-", $date_time[0]);
    $time = explode(":", $date_time[1]);
    unset($date_time);
    list($year, $month, $day) = $date;
    list($hour, $minute, $second) = $time;
    return mktime(intval($hour), intval($minute), intval($second), intval($month), intval($day), intval($year));
}

header("Content-Type: application/xml");
header('Content-Disposition: inline; filename="trackerfeed.xml"');
$query = "SELECT added FROM torrents ORDER BY added DESC LIMIT 1";
$res = mysqli_query($GLOBALS["___mysqli_ston"], $query);

if ($row = mysqli_fetch_assoc($res)) {
    $mod = gmdate("D, d M Y H:i:s \G\M\T", dttm2unixtime($row['added']));
    header("Last-Modified: $mod");
}
echo "<?xml version=\"1.0\"?>\n";
?>
<rss version="2.0">
    <channel>
        <title><?php echo $tracker_title; ?></title>
        <link><?=$tracker_url_name?>/</link>
        <description><?php echo $tracker_title; ?> - Recent Torrent Uploads</description>
        <language>en-us</language>
<?php

$query = "SELECT torrents.name AS name, torrents.id AS id, filename, info_hash, save_as, ori_descr, torrents.added AS added, size, categories.name AS cat_name, users.username AS username FROM torrents, categories, users WHERE category = categories.id AND owner = users.id ORDER BY added DESC LIMIT 10";

$res = mysqli_query($GLOBALS["___mysqli_ston"], $query);

if (isset($id))
    $subres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM comments WHERE torrent = " . $id);
$subrow = mysqli_fetch_array($subres);
$count = $subrow[0];

while ($row = mysqli_fetch_assoc($res)) {
    ?>
            <item>
                <title><?php echo htmlspecialchars($row['name']); ?></title>
                <category><?php echo htmlspecialchars($row['cat_name']); ?></category>
                <description>
                <![CDATA[
                    <style type="text/css">
                        body {background: #343030; font-family: Open Sans, Segoe UI, sans-serif;}
                        table {border-collapse: collapse; border-spacing: 1px;}
                        a:visited {color: #d50;}
                        a:link {font-weight: 600; color: #f60; text-decoration: none; outline: none;}
                        a:hover, a:focus {color: #f90;}
                        tr {border: 1px solid #bbb;}
                        tr:nth-child(odd) {background: #ddd;}
                        tr:nth-child(even) {background: #f2f2f2;}
                        tr:nth-last-child(n+2) td:first-child {text-align: right; border-right: 1px solid #bbb; background: rgba(0,0,0,.1);}
                        tr:first-child, tr:first-child td {padding-left: 0 !important; text-align: left !important; background: none !important; border: none !important;}
                        tr:last-child td {text-align: center;}
                        td {padding: 4px 8px;}
                        footer {display: none;}
                        hr + b {display: block;}
                        #sitename a {font-size: 28pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #898080; white-space: nowrap;}
                        @supports (-webkit-background-clip: text) {
                            #sitename {
                                text-shadow: none;
                                background: linear-gradient(to bottom, #210, #310 15%, #fff 50%, #310 80%) !important;
                                background: repeating-linear-gradient(to bottom, rgba(0, 0, 0, .2), rgba(0, 0, 0, .5) 2px),
                                            linear-gradient(to bottom, rgba(255, 96, 0, .5), rgba(0, 0, 0, .6) 100%),
                                            linear-gradient(to bottom, #740, #520 10%, #fff 35%, #310 65%) !important;
                                           -webkit-background-clip: text !important;
                                background-clip: text !important;
                                -webkit-text-stroke-color: rgba(255,255,255,.7);
                                -webkit-text-stroke-width: 1px;
                                -webkit-text-fill-color: transparent !important;
                                filter: drop-shadow(0 0 1px #300) drop-shadow(0 0 2px #200) drop-shadow(0 0 3px rgba(0, 0, 0, .1));
                            }
                        }
                    </style>

                    <table>
                        <tr><td colspan=2><div id=sitename><a href='<?php echo "$tracker_url_name"; ?>'><?php echo "$tracker_title"; ?></a></div></td></tr>
                        <tr><td>Name:</td><td><?php echo htmlspecialchars($row['name']); ?></td></tr>
                        <tr><td>Hash:</td><td><?php echo preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])); ?></td></tr>
                        <tr><td>Desc:</td><td><?php echo strip_tags($row['ori_descr'], '<br><b><strong><hr><p><ul><ol><li><i>'); ?></td></tr>
                        <tr><td>Type:</td><td>
                                <?php
if (isset($row["cat_name"])) {
        echo $row["cat_name"];
    } else {
        echo "none";
    }

    ?>
                            </td></tr>
                        <tr><td>Size:</td><td><?php echo mksize($row["size"]) . " (" . $row["size"] . " Bytes)"; ?></td></tr>
                        <tr><td>When:</td><td><?php echo gmdate("D, d M Y H:i:s \G\M\T", dttm2unixtime($row['added'])); ?></td></tr>
                        <tr><td>From:</td><td><?php echo $row["username"]; ?></td></tr>
                        <tr><td colspan=2><a href='<?=$tracker_url_name?>/download.php?id=<?php echo $row['id']; ?>&amp;file=<?php echo rawurlencode($row["filename"]); ?>'>Download Torrent</a> | <a href='<?=$tracker_url_name?>/details.php?id=<?php echo $row['id']; ?>&amp;hit=1'>View Details</a><?php if ($count) { ?> | <a href='<?=$tracker_url_name?>/details.php?id=<?php echo $row['id']; ?>&amp;hit=1&amp;tocomm=1'>View Comments</a><?php } ?></td></tr>
                    </table>
                ]]>
                </description>
                <link><?=$tracker_url_name?>/details.php?id=<?php echo $row['id']; ?>&amp;hit=1</link>
                <pubDate><?php echo gmdate('r', dttm2unixtime($row['added'])); ?></pubDate>
            </item>
<?php
}
?>
    </channel>
</rss>
