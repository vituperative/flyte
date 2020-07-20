<?php

require_once("include/bittorrent.inc.php");

if (!mkglobal("wantusername:wantpassword:passagain"))
	die();

function bark($msg) {
	genbark($msg, "Signup failed!");
}

if (empty($wantusername) || empty($wantpassword))
	bark("Don't leave any fields blank.");

if ($wantpassword != $passagain)
	bark("The passwords didn't match! Try again.");

if (strlen($wantpassword) > 15)
	bark("Sorry, password is too long (max is 15 chars)");

if (!preg_match('/^[a-z][\w.-]*$/is', $wantusername) || strlen($wantusername) > 15)
	bark("Invalid username. Must not be more than 15 characters long and no weird characters");

dbconn(0);

$secret = mksecret();

$hashpass = hash("sha256", $secret . $wantpassword . $secret);

$ret = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO users (username, password, secret, status, added) VALUES (" .
		implode(",", array_map("sqlesc", array($wantusername, $hashpass, $secret, 'confirmed'))) .
		", NOW())");

if (!$ret) {
	if (mysqli_errno($GLOBALS["___mysqli_ston"]) == 1062)
		bark("Username already exists!");
	bark("borked");
}

$id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);

logincookie($id, $hashpass, $secret);

header("Refresh: 0; url=my.php");

?>
