<?php

require_once("include/bittorrent.inc.php");

dbconn();

stdhead("Login");

unset($returnto);
if (!empty($_GET["returnto"])) {
	$returnto = $_GET["returnto"];
	if (!isset($_GET["nowarn"])) {
		print("Error: Please login before attempting to view this page...</p>\n");
	}
}

?>
<table id=dologin>
<tr>

<th colspan=2>Login</th>
</tr>
<form method="post" action="takelogin.php">
<tr><td align="center" class="r2"><BR>Username:<input class="input" type="text" size="20" name="username" /><BR><BR></td></tr>
<tr><td align="center" class="r1"><BR>Password:<input class="input" type="password" size="20" name="password" /><BR><BR></span></td></tr>
<tr><td colspan="2" align="center" class="r2"><br><input type="submit" value="Log in!" class="input" /><br><br></td></tr>
</table>
<?php

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");

?>
</form>
<BR><b>Don't have an account?</b> <a href="signup.php">Sign up</a> right now!
<?php

stdfoot();

?>
