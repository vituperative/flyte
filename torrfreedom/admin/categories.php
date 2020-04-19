<?php
require 'admin_class.php';
$admin = new admin();
$categoires = $admin->getCategories();
echo "<div id=server class=categories>\n<table>
<tr><th>id</th><th>name</th><th>sort_index</th><th>delete</th></tr>\n";
while($row = mysqli_fetch_array($categoires)){
	echo "<tr>";
	echo "<td>" . $row['id'] . "</td>";
	echo "<td>" . $row['name'] . "</td>";
	echo "<td>" . $row['sort_index'] . "</td>";
	echo "<td><a href='delCtagory.php?wdel_category=".$row['id']."' class=button><span class=no></span></a></td>";
	echo "</tr>";
}

?>
<?php stdfoot(); ?>
