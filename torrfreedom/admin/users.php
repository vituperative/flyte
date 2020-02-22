<?php
require 'admin_class.php';
$admin = new admin();

print("<div id=server class=users>\n");

if (isset($_GET['add_user'])){
    $admin->addUser($_GET['add_user'], $_GET['password'], $_GET['admin']);
} elseif(isset($_GET['del_user'])){
   $admin->delUserByUsername($_GET['del_user']);
}

$result = $admin->getAllUsers();

echo "<table>
<tr><th>User</th><th>Admin</th><th>Joined</th><th>Last Login</th><th>Last Access</th><th>Torrents</th><th>Comments</th><th>Delete</th></tr>\n";
while($row = mysqli_fetch_array($result))
{
	if( strstr($row['last_login'],"1970-01-01 00:00:00") !== FALSE ){
		$row['last_login']="Never";
		$row['last_access']="Never";
	}
	$isadmin= $admin->isAdmin($row['username']) == True ? "+" : "-";

echo "<tr>";
echo "<td>" . $row['username'] . "</td>";
echo "<td>" . $isadmin.  "</td>"; // TODO check if user is admin
echo "<td>" . $row['added'] . "</td>";
echo "<td>" . $row['last_login'] . "</td>"; // TODO replace 1970 date with "Never"
echo "<td>" . $row['last_access'] . "</td>"; // TODO replace 1970 date with "Never"
echo "<td>" . $row['cntt'] . "</td>";
echo "<td>" . $row['cntc'] . "</td>";
echo "<td><a href='deluser.php?wdel_user=".$row['username']."' class=button><span class=no></span></a></td>"; // TODO no immediate delete, switch to deluser.php for confirm/options
echo "</tr>\n";
}

echo "<tr id=dostuff><td colspan=8><a class=button href=adduser.php>Create New User Account</a></td></tr>";
echo "</table>\n";
echo "</div>";
stdfoot();
