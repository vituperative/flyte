<?php
require 'admin_class.php';
$admin = new admin();

if(isset($_GET['add_user'])){
	$ret=$admin->addUser($_GET['add_user'], $_GET['password'], $_GET['admin']);
	if( $ret !== TRUE){
		print ("<div style='text-transform:uppercase'>Some is wrong: ".$ret."</div>");
	}
	else header("Location: users.php");
}

?>
<!-- Some new DROZD code -->
<form action=adduser.php method=GET>
<div id=server class=usermanage>
<table>
<tr><th colspan=2>Create User Account</th></tr>
<tr><td>Username</td><td><input type=text name=add_user required></td></tr>
<tr><td>Password</td><td><input type=text name=password required></td></tr>
<tr><td>	Admin Privileges</td><td>
<select name='admin'>
  <option value=no selected>No&hellip; just a regular user account</option>
  <option value=yes>Yes&hellip; full administrative privileges</option></td></tr>
<tr id=dostuff><td colspan=2>	<input type=submit value="Add User"</td></tr>
</table>
</div>
</form>
<?php stdfoot(); ?>
