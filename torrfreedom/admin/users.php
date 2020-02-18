<?php
require_once '../include/bittorrent.inc.php';
dbconn(0);
stdhead();
$admin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
$mysqli = new mysqli("$mysql_host", "$mysql_user", "$mysql_pass", "$mysql_db");
if (!$admin) {
header("Location: ../index.php");
}

print("<div id=server>");

//$result = mysqli_query($con,"SELECT * FROM $mysql_db");

echo "<table border='1'>
<tr><th>User</th><th>Joined</th><th>Last login</th><th>Torrents</th><th>Comments</th></tr>";
/*while($row = mysqli_fetch_array($result))
{
echo "<tr>";
echo "<td>" . $row['users'] . "</td>";
//echo "<td>" . $row['LastName'] . "</td>";
echo "</tr>";
}*/
echo "</table>";

mysqli_close($mysqli);
echo "</div>";
stdfoot();
?>
