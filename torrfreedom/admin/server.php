<?php
require_once '../include/bittorrent.inc.php';
dbconn(0);
stdhead();
$admin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
$mysqli = new mysqli("$mysql_host", "$mysql_user", "$mysql_pass", "$mysql_db");
if (!$admin) {
header("Location: ../index.php");
}
?>

<div id=server>
<?php
$indicesServer = array(
    'SERVER_NAME',
    'SERVER_ADDR',
    'SERVER_PORT',
    'SERVER_SIGNATURE',
    'SERVER_SOFTWARE',
    'SERVER_PROTOCOL',
);

echo '<table id=serverdetails>';
echo '<tr><th colspan=2>Server Configuration</th></tr>';
foreach ($indicesServer as $arg) {
    if (isset($_SERVER[$arg])) {
        echo '<tr><td>' . $arg . '</td><td>' . $_SERVER[$arg] . '</td></tr>';
    } else {
        echo '<tr><td>' . $arg . '</td><td>-</td></tr>';
    }
}
printf("<tr><td>MySQL Version</td><td> %s</td></tr>\n", $mysqli->server_info);
$mysqli->close();

echo '<tr><th colspan=2>Tracker Configuration</th></tr>';
echo '<tr><td>$appname</td><td>' . $appname . '</td></tr>';
echo '<tr><td>$version</td><td>' . $version . '</td></tr>';
echo '<tr><td>$tracker_title</td><td>' . $tracker_title . '</td></tr>';
echo '<tr><td>$tracker_path</td><td>' . $tracker_path . '</td></tr>';
echo '<tr><td>$tracker_url_key</td><td>' . $tracker_url_key . '</td></tr>';
echo '<tr><td>$tracker_url_name</td><td>' . $tracker_url_name . '</td></tr>';
echo '<tr><td>$pic_base_url</td><td>' . $pic_base_url . '</td></tr>';
echo '<tr><td>$autoclean_interval</td><td>' . $autoclean_interval . ' (seconds)</td></tr>';
echo '<tr><td>$max_torrent_size</td><td>' . round($max_torrent_size / 1024 / 1024, 2) . ' (GB)</td></tr>';
echo '</table></div>';

stdfoot();
?>
