<?php
require_once '../include/bittorrent.inc.php';
dbconn(0);
stdhead();
$admin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
if (!$admin) {
header("Location: ../index.php");
}

print("<div id=server>");

$result = mysqli_query($GLOBALS["___mysqli_ston"],
"SELECT users.username, users.added, users.last_login, users.last_access, 
(SELECT COUNT(*) FROM torrents WHERE torrents.owner = users.id) AS cntt,
(SELECT COUNT(*) FROM comments WHERE comments.user = users.id) AS cntc
FROM users");

echo "<table border='1'>
<tr><th>User</th><th>Joined</th><th>Last login</th><th>Last access</th><th>Torrents</th><th>Comments</th></tr>";
while($row = mysqli_fetch_array($result))
{
echo "<tr>";
echo "<td>" . $row['username'] . "</td>";
echo "<td>" . $row['added'] . "</td>";
echo "<td>" . $row['last_login'] . "</td>";
echo "<td>" . $row['last_access'] . "</td>";
echo "<td>" . $row['cntt'] . "</td>";
echo "<td>" . $row['cntc'] . "</td>";
echo "</tr>";
}
echo "</table>";
echo "</div>";
stdfoot();
?>
