<?php
require 'admin_class.php';
$admin = new admin();

print("<div id=server class=torrents>\n");


$result = $admin->getAllTorrents();


while($row = mysqli_fetch_array($result))
{
	foreach($row as $key=>$val){
		if($key =="category") $val=$admin->getNameOfCategoryByID($val);
		print( $key . "=>" . $val."<br/>" );
	}
}

echo "</div>";
stdfoot();
