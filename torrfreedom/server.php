<?php
require_once 'include/bittorrent.inc.php';
dbconn();
stdhead();
?>
<table id=helpwrapper height=100%>
<tr><td>
<div id=server>
<?php
$indicesServer = array(
    'SERVER_NAME',
    'SERVER_ADDR',
    'SERVER_PORT',
    'SERVER_SIGNATURE',
    'SERVER_SOFTWARE',
    'SERVER_PROTOCOL',
    'SCRIPT_NAME',
    'GATEWAY_INTERFACE',
    'REQUEST_METHOD',
    'HTTP_ACCEPT',
    'HTTP_ACCEPT_ENCODING',
    'HTTP_ACCEPT_LANGUAGE',
    'HTTP_CONNECTION',
    'HTTP_HOST',
    'HTTP_USER_AGENT',
    'REMOTE_ADDR',
    'REMOTE_HOST',
    'REMOTE_PORT',
);

echo '<table id=serverdetails>';
echo '<tr><th colspan=2>Server Indices</th></tr>';
foreach ($indicesServer as $arg) {
    if (isset($_SERVER[$arg])) {
        echo '<tr><td>' . $arg . '</td><td>' . $_SERVER[$arg] . '</td></tr>';
    } else {
        echo '<tr><td>' . $arg . '</td><td>-</td></tr>';
    }
}
echo '</table>';
?>
</div>
</td></tr>
</table>
<?php stdfoot();?>
