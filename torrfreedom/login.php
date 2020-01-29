<?php

require_once("include/bittorrent.inc.php");

dbconn();

stdhead("Login");

unset($returnto);
if (!empty($_GET["returnto"])) {
	$returnto = $_GET["returnto"];
	if (!isset($_GET["nowarn"])) {
		print("<h1>Not logged in!</h1>\n");
		print("<p><b>Error:</b> The page you tried to view can only be used when you're logged in.</p>\n");
	}
}

?>

<table class="table1" align=center cellpadding="0" cellspacing="0" width="35%">
<td>
<table class="table1" align=center cellpadding="0" cellspacing="0" width="100%">
<tr>

<td align=left class="td1"><span class="text1">Please Login (You need cookies enabled to log in.)</span></td>
</tr>
<form method="post" action="takelogin.php">
<tr><td align="center" class="r2"><BR>Username:<input class="input" type="text" size="20" name="username" /><BR><BR></td></tr>
<tr><td align="center" class="r1"><BR>Password:<input class="input" type="password" size="20" name="password" /><BR><BR></span></td></tr>
<tr><td colspan="2" align="center" class="r2"><br><input type="submit" value="Log in!" class="input" /><br><br></td></tr>
</table></table>
<?php

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");

?>
</form>
<BR><center><b>Don't have an account?</b> <a href="signup.php">Sign up</a> right now!</center>
<?php

stdfoot();

?>
