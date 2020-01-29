<?php

require_once("include/bittorrent.inc.php");

function bark($msg) {
	genbark($msg, "Update failed!");
}

dbconn();

loggedinorreturn();

if (!mkglobal("chpassword:passagain"))
	bark("missing form data");

$set = array();

$updateset = array();
$newsecret = 0;
$sec = mksecret();

if ($chpassword != "") {
	if (strlen($chpassword) > 15)
		bark("Sorry, password is too long (max is 15 chars)");
	if ($chpassword != $passagain)
		bark("The passwords didn't match. Try again.");
    $hashpass = hash("sha256", $sec . $chpassword . $sec);
	$updateset[] = "password = " . sqlesc($hashpass);
	$newsecret = 1;
}

/* ****** */

$urladd = "";

if ($newsecret) {
	$updateset[] = "secret = " . sqlesc($sec);
	logincookie($CURUSER["id"], $hashpass, $sec);
}

mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET " . implode(",", $updateset) . " WHERE id = " . $CURUSER["id"]);

header("Refresh: 0; url=my.php?edited=1" . $urladd);

?>
