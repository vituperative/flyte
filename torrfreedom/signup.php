<?php

require_once("include/bittorrent.inc.php");

dbconn();

loggedoutorreturn();

stdhead("Signup");

?>
<form method="post" action="takesignup.php">
<table id=signup>
<tr><th colspan=2>Register an Account (Please ensure cookies are enabled in your browser)</th></tr>
<tr><td>Desired username:</td><td><input class="input" type="text" size="40" name="wantusername" /></td></tr>
<tr><td>Pick a password:</td><td><input class="input" type="password" size="40" name="wantpassword" /></td></tr>
<tr><td>Enter password again:</td><td><input class="input" type="password" size="40" name="passagain" /></td></tr>
<tr><td colspan="2" align="center" ><input type="submit" value="Sign up!" class="input"/></td></tr>
</table>

</td></table>
</form>
<?php

stdfoot();

?>
