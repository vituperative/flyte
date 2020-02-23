<?php
require 'admin_class.php';
$admin = new admin();
//die( $_GET['torid'] );
if(isset($_GET['continue']) && isset($_GET['torid']) && isset($_GET['val']) ){

   $ret=$admin->setBanTorrentByID($_GET['val'],$_GET['torid']);
   if( strlen($ret) ){
      print ("<p class=warn>Error: ". $ret ."</p>");
   }
   else print("changed!");
}

?>

<form action=bantorrent.php method=GET>
	banit?: <select name=val>
		<option>yes</option>
		<option>no</option>
	</select>
	<input type=hidden name=torid value='<?php echo $_GET['torid'];?>'/>
	<input type=submit value=sure? name=continue>
</form>
<?php stdfoot(); ?>
