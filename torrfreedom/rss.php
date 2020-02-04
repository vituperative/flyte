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
header('Content-Disposition: inline; filename="rss2.xml"');
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
        <description>Torrent Freedom</description>
        <language>en-us</language>
<?php

$query = "SELECT torrents.name AS name, torrents.id AS id, filename, info_hash, save_as, ori_descr, torrents.added AS added, size, categories.name AS cat_name, users.username AS username FROM torrents, categories, users WHERE category = categories.id AND owner = users.id ORDER BY added DESC LIMIT 10";

$res = mysqli_query($GLOBALS["___mysqli_ston"], $query);

while ($row = mysqli_fetch_assoc($res)) {
    ?>
            <item>
                <title><?php echo htmlspecialchars($row['name']); ?></title>
                <category><?php echo htmlspecialchars($row['cat_name']); ?></category>
                <description>
                <![CDATA[
                    <table>
                        <tr><td>Name:</td><td><?php echo htmlspecialchars($row['name']); ?></td></tr>
                        <tr><td>Hash:</td><td><?php echo preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])); ?></td></tr>
                        <tr><td>Desc:</td><td><?php echo htmlspecialchars($row['ori_descr']); ?></td></tr>
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
                        <tr><td>Link:</td><td><a href='<?=$tracker_url_name?>/download.php?id=<?php echo $row['id']; ?>&amp;file=<?php echo rawurlencode($row["filename"]); ?>'>Download Torrent</a> | <a href='<?=$tracker_url_name?>/details.php?id=<?php echo $row['id']; ?>&amp;hit=1'>View Details</a></td></tr>
                    </table>
                ]]>
                </description>
                <pubDate><?php echo gmdate('r', dttm2unixtime($row['added'])); ?></pubDate>
                <link><?=$tracker_url_name?>/details.php?id=<?php echo $row['id']; ?>&amp;hit=1</link>
                <comments><?=$tracker_url_name?>/details.php?id=<?php echo $row['id']; ?>&amp;hit=1&amp;tocomm=1</comments>
                <enclosure url="<?=$tracker_url_name?>/download.php?id=<?php echo $row['id']; ?>&amp;file=<?php echo rawurlencode($row["filename"]); ?>" length="<?php echo $row['size']; ?>" type="application/x-bittorrent" />
            </item>
<?php
}
?>
    </channel>
</rss>
