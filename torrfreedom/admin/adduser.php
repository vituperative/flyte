<?php
require 'admin_class.php';
$admin = new admin();

if(isset($_GET['add_user'])){
	$admin->addUser($_GET['add_user'], $_GET['password'], $_GET['admin']);
}

?>
<form action=adduser.php method=GET>
<div id=server class=usermanage>
<table>
<tr><th colspan=2>Add New User</th></tr>
<tr><td>Username</td><td><input type=text name=add_user required></td></tr>
<tr><td>Password</td><td><input type=text name=password required></td></tr>
<tr><td>	Admin Privileges</td><td>
<select>
  <option value=no selected>No&hellip; just a regular user account</option>
  <option value=yes>Yes&hellip; full administrative privileges</option></td></tr>
<tr id=dostuff><td colspan=2>	<input type=submit value="Add User"</td></tr>
</table>
</div>
</form>
<?php stdfoot(); ?>