<?php
require 'admin_class.php';
$admin = new admin();

if(isset($_GET['del_user'])){
   $ret=$admin->delUserByUsername($_GET['del_user']);
   if( !$ret ){
      print ("<p class=warn>MySQL error: ".$admin->getLastSQLError()."</p>");
   }
   header("Location: users.php");
}

?>
<form action=deluser.php method=GET>
<div id=server class=usermanage>
<table>
<tr><th colspan=2>Delete User Account</th></tr>
<tr><td>Username</td><td><input type=text name=del_user required></td></tr>
<tr><td>Delete Torrents</td><td><label><input type=checkbox name=del_torrents>&nbsp; Delete all torrents uploaded by the user</label>&nbsp; <a href=#>[View]</a></td></tr>
<tr><td>Delete Comments</td><td><label><input type=checkbox name=del_comments>&nbsp; Delete all comments posted by the user</label>&nbsp; <a href=#>[View]</a></td></tr>
<tr><td>Blacklist</td><td><label><input type=checkbox name=blacklist>&nbsp; Prevent user from recreating account</label></td></tr>
<tr id=dostuff><td colspan=2>	<input type=submit value="Delete User"</td></tr>
</table>
</div>
</form>

<?php stdfoot(); ?>
