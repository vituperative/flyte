<?php
require 'admin_class.php';
$admin = new admin();

print("<div id=server class=users>\n");

if(isset($_GET['add_user'])){
    $admin->addUser($_GET['add_user'], "123456");
}
$result = $admin->getAllUsers();

echo "<table>
<tr><th>User</th><th>Joined</th><th>Last login</th><th>Last access</th><th>Torrents</th><th>Comments</th></tr>\n";
while($row = mysqli_fetch_array($result))
{
echo "<tr>";
echo "<td>" . $row['username'] . "</td>";
echo "<td>" . $row['added'] . "</td>";
echo "<td>" . $row['last_login'] . "</td>";
echo "<td>" . $row['last_access'] . "</td>";
echo "<td>" . $row['cntt'] . "</td>";
echo "<td>" . $row['cntc'] . "</td>";
echo "</tr>\n";

}
echo "<tr id=dostuff><td colspan=6><a class=button href=adduser.php>Create New User</a></td></tr>";
echo "</table>\n";
echo "</div>";
stdfoot();
?>
