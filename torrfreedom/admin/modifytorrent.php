<?php
require 'admin_class.php';
$admin = new admin();
//die( $_GET['torid'] );
//var_dump($_GET);
//exit(0);
if(isset($_GET['continue']) && isset($_GET['torid']) && isset($_GET['val']) && $_GET['do'] == 'visible' ){
   //die("SEt visible");
   $ret=$admin->setVissbleTorrentByID($_GET['val'],$_GET['torid']);
   if( strlen($ret) ){
      print ("<p class=warn>Error: ". $ret ."</p>");
   }
   else print("changed!");
}else if(isset($_GET['del_torent'])){
   $ret=$admin->delTorrentByID($_GET['del_torent']);
   if( strlen($ret) ){
      print ("<p class=warn>Error: ". $ret ."</p>");
   }
   else print("deleted!");

}else if(isset($_GET['continue']) && isset($_GET['torid']) && isset($_GET['val']) && $_GET['do'] == 'banned' ){

   $ret=$admin->setBanTorrentByID($_GET['val'],$_GET['torid']);
   if( strlen($ret) ){
      print ("<p class=warn>Error: ". $ret ."</p>");
   }
   else print("changed!");
}

$visible = isset($_GET['visible']) && $_GET['visible'] == 'on';
$banned= isset($_GET['banned']) && $_GET['banned'] == 'on';
if(!isset($_GET['do'])){
	 header("Location: torrents.php");
	 die("are you ok?");
}
function getMessage($do){
	if($do == "banned") return "set ban of it? ";
	else return "set visible of it? ";
}

?>

<form action=modifytorrent.php method=GET>
	<?php echo getMessage($_GET['do']) ?>: <select name=val>
		<?php
			function yesno($yes,$no){
				return sprintf("<option %s>yes</option><option %s>no</option>", $yes, $no);
			}
			if($visible || $banned )
				echo yesno("selected", "");
			else
				echo yesno("", "selected");
				
		?>

	</select>
	<input type=hidden name=torid value='<?php echo $_GET['torid'];?>'/>
	<input type=hidden name=do value='<?php echo $_GET['do'];?>'/>
	<input type=submit value=sure? name=continue>
</form>
<?php stdfoot(); ?>
