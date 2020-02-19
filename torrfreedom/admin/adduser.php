<?php
require 'admin_class.php';
$admin = new admin();

print("<div id=server class=users>\n");

if(isset($_GET['add_user'])){
	$admin->addUser($_GET['add_user'], $_GET['password'], $_GET['admin']);
}

?>
<form action=adduser.php style="position:absolute" method=GET>
	Nick <input type=text placeholder=nick name=add_user>
	password <input type=text placeholder=pass name=password>
	admin(yes/no) <input type=text name=admin value='no'>
	<input type=submit>
	<hr/>
</form>
<?php

stdfoot();
?>
