<?php

require_once "include/bittorrent.inc.php";

dbconn(0);

loggedinorreturn();

stdhead($CURUSER["username"] . "'s private page");

$referrer = $_SERVER['HTTP_REFERER'];
$UA = $_SERVER['HTTP_USER_AGENT'];
$request = $_SERVER["REQUEST_URI"];

if (isset($_GET["edited"])) {
    print("<p id=toast class=success>Your profile has been updated.</p>\n");
} elseif (strpos($referrer, 'login') !== false) {
    print("<p id=toast class=success><span class=title>Welcome back, " . htmlspecialchars($CURUSER["username"]) . "!</span><br>We've missed you!\n");
    if (strpos($UA, 'MYOB') === false)
        print("<span id=uawarn><b>Warning!</b>\nYour browser's user agent is leaking!<br>" . $UA . "</span>");
    else
        print("<span id=uawarn class=good><b>User Agent OK</b>" . $UA . "</span>");
    print("</p>\n");
} elseif (strpos($request, 'fail') !== false) {
    print("<p id=toast class=warn><span class=title>Password change failed!</span><br>The passwords you supplied did not match.</p>\n");
}

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM torrents WHERE owner=" . $CURUSER["id"]);
$row = mysqli_fetch_array($res);
$mytorrents = $row[0];
?>
<form method=post action=updateprefs.php>
<div class="tablewrap slim">
<table id=userprefs>
<tr><th colspan=2>Tracker Preferences</th></tr>

<?php
tr("Torrents per page", "<input type=text name=pagesize size=3 value=$pagesize disabled/>", 1); // disabled for now, needs a separate form?
tr("Cloak name", "<label><input type=checkbox name=hideuploader> Don't show my name on torrents</label>", 1);
tr("Prevent Comments", "<label><input type=checkbox name=hideuploader> Don't allow comments on my torrents</label>", 1);
print("<tr id=dostuff><td colspan=2><input type=submit value=\"Save Preferences\" /></td></tr>");

print("</table>\n</div>\n");
?>
</div>
</form>

<?php stdfoot(); ?>
