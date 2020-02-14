<?php
require_once "include/bittorrent.inc.php";
global $tracker_path;
function bark($msg) {
    genbark($msg, "Update failed!");
}
dbconn(0);
loggedinorreturn();

if (!mkglobal("chpassword:passagain")) {
    bark("Submission failed! Please ensure you have correctly filled in the form!");
}

$set = array();

$updateset = array();
$newsecret = 0;
$sec = mksecret();

if ($chpassword != "") {
    if (strlen($chpassword) > 15) {
        bark("Sorry, password is too long (max is 15 chars)");
    }

    if ($chpassword != $passagain) {
        header("Location: " . $trackerpath . "my.php?edit=failed");
        bark("The passwords didn't match. Please try again.");
    }

    $hashpass = hash("sha3", $sec . $chpassword . $sec);
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
