<?php
require 'admin_class.php';
$admin = new admin();

print("<div id=server class=users>\n");

if(isset($_GET['del_user'])){
	print("DEL!");
	$admin->delUserByUsername($_GET['del_user']);
}

?>
<form action=deluser.php style="position:absolute" method=GET>
	Nick <input type=text placeholder=nick name=del_user>
	<input type=submit>
	<hr/>
</form>
<?php

stdfoot();
?>
