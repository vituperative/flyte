<?php
require 'admin_class.php';
$admin = new admin();

if(isset($_GET['del_user'])){
	print("DEL!");
	$admin->delUserByUsername($_GET['del_user']);
}

?>
<form action=deluser.php method=GET>
<div id=server class=usermanage>
<table>
<tr><th colspan=2>Delete User</th></tr>
<tr><td>Username</td><td><input type=text name=del_user required></td></tr>
<tr id=dostuff><td colspan=2>	<input type=submit value="Delete User"</td></tr>
</table>
</div>
</form>
<?php stdfoot(); ?>