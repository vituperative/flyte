<?php
require 'admin_class.php';
$admin = new admin();

//$mysqli = new mysqli("$mysql_host", "$mysql_user", "$mysql_pass", "$mysql_db");

?>

<div id=server>
<?php
$params_of_serv = $admin->getServInfo();
/*
$indicesServer = array(
    'SERVER_NAME',
    'SERVER_ADDR',
    'SERVER_PORT',
    'SERVER_SIGNATURE',
    'SERVER_SOFTWARE',
    'SERVER_PROTOCOL',
);
*/

echo '<table id=serverdetails>';
echo '<tr><th colspan=2>Server Configuration</th></tr>';
foreach ($params_of_serv as $key=>$val) {
    if (isset($_SERVER[$key])) {
        echo '<tr><td>' . $key . '</td><td>' . $val . '</td></tr>';
    } else {
        echo '<tr><td>' . $key . '</td><td>-</td></tr>';
    }
}
printf("<tr><td>MySQL Version</td><td> %s</td></tr>\n", mysqli_get_server_info( $admin->getSQLCon() ) );
echo '<tr><th colspan=2>Tracker Configuration</th></tr>';
echo '<tr><td>$appname</td><td>' . $appname . '</td></tr>';
echo '<tr><td>$version</td><td>' . $version . '</td></tr>';
echo '<tr><td>$tracker_title</td><td>' . $tracker_title . '</td></tr>';
echo '<tr><td>$tracker_path</td><td>' . $tracker_path . '</td></tr>';
echo '<tr><td>$tracker_url_key</td><td>' . $tracker_url_key . '</td></tr>';
echo '<tr><td>$tracker_url_name</td><td>' . $tracker_url_name . '</td></tr>';
echo '<tr><td>$pic_base_url</td><td>' . $pic_base_url . '</td></tr>';
echo '<tr><td>$autoclean_interval</td><td>' . $autoclean_interval . 's &nbsp; (' . $autoclean_interval / 60 . ' minutes)</td></tr>';
echo '<tr><td>$max_dead_torrent_time</td><td>' . $max_dead_torrent_time . 's &nbsp; (' . $max_dead_torrent_time / 60 / 60 . ' hours)</td></tr>';
echo '<tr><td>$max_torrent_size</td><td>' . $max_torrent_size . 'K &nbsp; (' . round($max_torrent_size / 1024 / 1024, 2) . 'GB)</td></tr>';
echo '</table></div>';

stdfoot();
?>
