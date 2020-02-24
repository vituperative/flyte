<?php
require 'admin_class.php';
$admin = new admin();
?>

<form action=delTorrent.php method=GET>
<div id=server class=dialog>

<?php
if(isset($_GET['del_torent'])) {
   $ret=$admin->delTorrentByID($_GET['del_torent']);
   if( strlen($ret) ){
      print ("<p class=fail>Error: ". $ret ."</p>");
   }
   else print("<p class=success>Torrent deleted!</p>");
}
?>

<?php
if(!isset($_GET['del_torent'])) {
?>
<table>
<tr><th colspan=2>Delete torrent</th></tr>
<tr><td><b>Torrent ID or Hash</b></td><td><input type=text name=del_torent required></td></tr>
<tr id=dostuff><td colspan=2><input type=submit value="Delete Torrent"></td></tr>
</table>
<?php } ?>
</div>
</form>
<?php stdfoot(); ?>
