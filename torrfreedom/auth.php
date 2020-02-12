<?php

$realm = "Torrent Skank";
//$users = array('boss' => 'doitnow';)
$_SERVER = array('boss' => 'boss');
$PHP_AUTH_USER = "boss";
$PHP_AUTH_PW = "boss";
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Login to Torrent Skank"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<body style=background:#121010;color:#911><table height=100% width=100%><tr><td align=center><h1>Login failed</h1><p>Please supply valid login credentials to access this resource.</p></td></tr></table></body>';
    exit;
} else {
    echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
    echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
}
?>
