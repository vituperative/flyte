<?php
require 'admin_class.php';
$admin = new admin();

if(isset($_GET['del_torent'])){
   $ret=$admin->delTorrentByID($_GET['del_torent']);
   if( strlen($ret) ){
      print ("<p class=warn>Error: ". $ret ."</p>");
   }
   else print("deleted!");
}
if( !isset( $_GET['name'] ) ) $_GET['name']="undefined";
if( !isset( $_GET['user'] ) ) $_GET['user']="undefined";

$wdel_id=-1;
if( isset($_GET['wdel_id']))
	$wdel_id=$_GET['wdel_id'];
if($wdel_id < 0) return header("Location: index.php");

function sureBox($wdel_id){
	printf("<tr><td>Do want you delete torrent `%s` of `%s` with id `%d`</td><td></td></tr>",$_GET['name'], $_GET['user'],$wdel_id);
	printf("<tr><td>Are you sure?</td><td><input type=hidden name=del_torent value='%s' required><input value='%s' type=submit></td></tr>", $wdel_id, "are you sure? to delete torrent of id $wdel_id");
       
}

?>

<form action=delTorrent.php method=GET>
<div id=server class=usermanage>
<table>
<tr><th colspan=2>Delete torrent</th></tr>
<?php
	sureBox($wdel_id);
?>

</table>
</div>
</form>
<?php stdfoot(); ?>
