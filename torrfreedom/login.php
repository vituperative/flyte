<?php
require_once "include/bittorrent.inc.php";
dbconn();
stdhead("Login");
unset($returnto);
if (!empty($_GET["returnto"])) {
    $returnto = $_GET["returnto"];
    if (!isset($_GET["nowarn"])) {
        print("<p id=warn>Error: Login required to view the page...</p>\n");
    }
}
?>
<form method="post" action="takelogin.php">
<p id=warn class=cookies>Please ensure cookies are enabled in your browser.</a>
<table id=dologin>
<tr><th colspan=2>Login to <?php echo "$tracker_title"; ?></th></tr>
<tr><td>Username</td><td><input id="username" class="input" type="text" size="20" name="username" /></td></tr>
<tr><td>Password</td><td><input class="input" type="password" size="20" name="password" /></td></tr>
<tr id=dostuff><td colspan="2"><input type="submit" value="Log in!" class="input" /></td></tr>
</table>
<?php
if (isset($returnto)) {
    print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");
}
?>
</form>
<b>Don't have an account?</b> <a href="signup.php">Sign up</a> right now!
<?php stdfoot(); ?>
