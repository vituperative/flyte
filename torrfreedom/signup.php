<?php

require_once("include/bittorrent.inc.php");

dbconn();

loggedoutorreturn();

stdhead("Signup");

?>
<form method="post" action="takesignup.php">
<table align=center cellpadding="0" cellspacing="0" width="50%" class="table1">
<td>
<table align=center cellpadding="0" cellspacing="0" width="100%" class="table1">
<tr><td align=left colspan="2" class="td1"><span class="text1">Sign Up Form (You need cookies enabled to sign up or log in)</span></td></tr>



<tr><td align="right" class="r2">Desired username:</td><td class="r1"><input class="input" type="text" size="40" name="wantusername" /></td></tr>
<tr><td align="right" class="r2">Pick a password:</td><td class="r1"><input class="input" type="password" size="40" name="wantpassword" /></td></tr>
<tr><td align="right" class="r2">Enter password again:</td><td class="r1"><input class="input" type="password" size="40" name="passagain" /></td></tr>
<tr><td colspan="2" align="center" class="r1"><input type="submit" value="Sign up!" class="input"/></td></tr>
</table>

</td></table>
</form>
<?php

stdfoot();

?>
