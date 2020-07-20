<?php
require 'admin_class.php';
$admin = new admin();

//delUserByUsername($username, $withTorrents=True, $withComments=True)

if(isset($_GET['del_category'])  ){
   $ret=$admin->delCategoryByID($_GET['del_category']);
   if( !$ret ){
      print ("<p class=fail>MySQL error: ".$admin->getLastSQLError()."</p>");
   }
   header("Location: categories.php");
}

?>
<form action=deluser.php method=GET>
<div id=server class=usermanage>
<table>
	<tr>
		<th colspan=2>Delete User Account</th>
	</tr>
	<tr>
		<td>ID</td><td><input type=text name=del_category <?php 
		if(isset($_GET['wdel_category'])) printf("value='%s'",$_GET['wdel_category'])?> required></td>
	</tr>

	<tr id=dostuff><td colspan=2>	<input type=submit value="Delete category"></td></tr>
</table>
</div>
</form>

<?php stdfoot(); ?>

