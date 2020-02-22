<?php
require 'admin_class.php';
$admin = new admin();

print("<style>body{opacity:1 !important}</style><div id=server class=torrents>\n");

$result = "";
$limit = 60;
$offset=0;



if(isset($_GET['offset'])){
	$offset=$_GET['offset']*$limit;
}

if(!isset($_GET['user'])){
	$result = $admin->getAllTorrents($offset);
}
else{
	$result = $admin->getTorrentsByUserNick($_GET['user'], $offset);
}


echo "<table>
<tr><th>id</th><th>info_hash(in hex)</th><th>name</th><th>filename</th><th>descr</th><th>ori_descr</th><th>category</th><th>views</th><th>visible</th><th>leechers</th><th>seeders</th><th>banned</th><th>hits</th></tr>\n";

while($row = mysqli_fetch_array($result))
{
echo "<tr>";
$torid=$row['id'];
echo "<td>" . $torid.  "</td>"; 
echo "<td>" . implode(unpack("H*", $row['info_hash'])) .  "</td>";  
//https://stackoverflow.com/questions/14674834/php-convert-string-to-hex-and-hex-to-string
echo "<td>" . $row['name'].  "</td>"; 
echo "<td>" . $row['filename'].  "</td>"; 
echo "<td>" . $row['descr'].  "</td>"; 
echo "<td>" . $row['ori_descr'].  "</td>"; 
echo "<td>" . $admin->getNameOfCategoryByID($row['category']).  "</td>"; 
echo "<td>" . $row['views'].  "</td>"; 
echo "<td><a href=vistorrent.php?torid=$torid>" . $row['visible'].  "</a></td>"; 
echo "<td>" . $row['leechers'].  "</td>"; 
echo "<td>" . $row['seeders'].  "</td>"; 
echo "<td><a href=bantorrent.php?torid=$torid>" . $row['banned'].  "</a></td>"; 
echo "<td>" . $row['hits'].  "</td>"; 
echo "</tr>\n";
/*
	foreach($row as $key=>$val){
		if($key =="category") $val=$admin->getNameOfCategoryByID($val);
		print( $key . "=>" . $val."<br/>" );
	}
*/
}


echo "</table></div>";
stdfoot();
