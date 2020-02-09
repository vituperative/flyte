<?php

require_once "include/bittorrent.inc.php";

dbconn();

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
<form method=post action=takeprofedit.php>
<table id=myaccount>
<tr><th>Profile for: <?php echo $CURUSER["username"] ?></th><th>
<?php
if (!$mytorrents)
    print("No torrents uploaded!");
else
    print("<a href=mytorrents.php>My Torrents</a> (" . $mytorrents . ")");
print("</th></tr>");
tr("Comments posted", $row[0]);

tr("New password", "<input type=password name=chpassword size=40 required />", 1);
tr("Confirm password", "<input type=password name=passagain size=40 required />", 1);

?>
<tr id=dostuff><td colspan=2><input type=submit value="Update Password" /></td></tr>
</table>
</form>

<?php stdfoot(); ?>
