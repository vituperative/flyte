<?php

require_once("include/bittorrent.inc.php");

if (!mkglobal("username:password"))
	die();

dbconn();

function bark() {
	genbark("Login failed!", "Username or password incorrect");
}

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, password, secret FROM users WHERE username = " . sqlesc($username) . " AND status = 'confirmed'");
$row = mysqli_fetch_array($res);

if (!$row)
	bark();

$hashpass = hash("sha256", $row["secret"] . $password . $row["secret"]);

if ($row["password"] != $hashpass)
	bark();

logincookie($row["id"], $hashpass, $row["secret"]);

if (!empty($_POST["returnto"]))
	header("Refresh: 0; url=" . $_POST["returnto"]);
else
	header("Location: my.php");

?>
