<?php
require_once "include/bittorrent.inc.php";
dbconn();
stdhead("Login");
unset($returnto);
if (!empty($_GET["returnto"])) {
    $returnto = $_GET["returnto"];
    if (!isset($_GET["nowarn"])) {
        print("<p id=toast class=warn>Error: Login required to view that page&hellip;</p>\n");
    }
}
?>
<form method=post action=takelogin.php>
<p id=toast class=warn>Please ensure cookies are enabled in your browser.</p>
<div class="tablewrap slim">
<table id=dologin>
<tr><th colspan=2>Login to <?php $sitename = ucwords(strtolower($tracker_title)); echo $sitename; ?></th></tr>
<tr><td>Username</td><td><input id=username class=input type=text size=20 name=username /></td></tr>
<tr><td>Password</td><td><input class=input type=password size=20 name=password /></td></tr>
<tr id=dostuff><td colspan=2><input type=submit value="Log in!" class=input /></td></tr>
</table>
</div>
<?php
if (isset($returnto)) {
    print("<input type=hidden name=returnto value=\"" . htmlspecialchars($returnto) . "\" />\n");
}
?>
</form>
<p id=invite><b>Don't have an account?</b> <a href=signup.php>Sign up</a> right now!</p>
<?php stdfoot(); ?>
