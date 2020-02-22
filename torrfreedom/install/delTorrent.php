<?php
require 'admin_class.php';
$admin = new admin();

if(isset($_GET['del_torent'])){
   $ret=$admin->delTorrentByID($_GET['del_torent']);
   if( $ret !== TRUE){
      print ("<p class=warn>Error: ". $ret ."</p>");
   }
   else print("deleted!");
}

?>

<form action=delTorrent.php method=GET>
<div id=server class=usermanage>
<table>
<tr><th colspan=2>Delete torrent</th></tr>
<tr><td>ID OF TORRENT</td><td><input type=text name=del_torent required></td></tr>
</table>
</div>
</form>
<?php stdfoot(); ?>
