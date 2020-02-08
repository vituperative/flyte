<?php
require_once("include/bittorrent.inc.php");
dbconn();
loggedoutorreturn();
stdhead("Signup");
?>
<form method="post" action="takesignup.php">
<p id=toast class=warn>Please ensure cookies are enabled in your browser.</a></p>
<table id=signup>
<tr><th colspan=2>Register an Account on <?php echo"$tracker_title";?></th></tr>
<tr><td>Desired username</td><td><input id="username" class="input" type="text" size="40" name="wantusername" required /></td></tr>
<tr><td>Choose a password</td><td><input class="input" type="password" size="40" name="wantpassword" required /></td></tr>
<tr><td>Confirm password</td><td><input class="input" type="password" size="40" name="passagain" required /></td></tr>
<tr id=dostuff><td colspan="2" align="center" ><input type="submit" value="Sign up!" class="input"/></td></tr>
</table>
</form>
<?php stdfoot(); ?>
