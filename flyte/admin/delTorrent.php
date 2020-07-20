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
   $wdel_id=intval($_GET['wdel_id']);
if($wdel_id < 0) return header("Location: index.php");

function sureBox($wdel_id, $msg="Nuke it!"){
   $user = str_replace("'", "", $_GET['user']);
   $user = str_replace('"', '', $user);
   printf("<tr id=message><td>Torrent: %s</td></tr>",$_GET['name'], $_GET['user'],$wdel_id);
   printf("<tr id=dostuff><td>
<input type=hidden name=del_torent value='%d' required>
<a class=button href=torrents.php?user='$user'>Cancel</a>
<input value='%s' type=submit></td></tr>", $wdel_id, $msg);
}

?>

<form action=delTorrent.php method=GET>
<div id=server class="dialog warn">
<table>
<tr><th colspan=2>Please Confirm Delete</th></tr>
<?php
   sureBox($wdel_id);
?>

</table>
</div>
</form>
<?php stdfoot(); ?>
