<?php
require 'admin_class.php';
$admin = new admin();

if (isset($_GET['add_user'])){
    $admin->addUser($_GET['add_user'], $_GET['password'], $_GET['admin']);
} elseif(isset($_GET['del_user'])){
   $admin->delUserByUsername($_GET['del_user']);
}

$result = $admin->getAllUsers();

echo "<div id=server class=users>\n<table>
<tr><th>User</th><th>Admin</th><th>Joined</th><th>Last Login</th><th>Last Access</th><th>Torrents</th><th>Comments</th><th>Delete</th></tr>\n";
while($row = mysqli_fetch_array($result))
{
	if( strstr($row['last_login'],"1970-01-01 00:00:00") !== FALSE ){
		$row['last_login']="Never";
		$row['last_access']="Never";
	}
	$isadmin= $admin->isAdmin($row['username']) == True ? "<span class=yes></span>" : "";
//var_dump($row);
$username=$row['username'];
//$countTorrents=$admin->countOfTorrentsByUserNick($username);
echo "<tr>";

echo "<td>" . $username . "</td>";
echo "<td>" . $isadmin.  "</td>";
echo "<td>" . $row['added'] . "</td>";
echo "<td>" . $row['last_login'] . "</td>"; // TODO replace 1970 date with "Never"
echo "<td>" . $row['last_access'] . "</td>"; // TODO replace 1970 date with "Never"
if($row['cntt'])
	echo "<td><a href=torrents.php?user=$username>" . $row['cntt'] . "</a></td>";
else
	echo "<td>" . $row['cntt'] . "</td>";
echo "<td>" . $row['cntc'] . "</td>";
echo "<td><a href='deluser.php?wdel_user=".$row['username']."' class=button><span class=no></span></a></td>";

echo "</tr>\n";
}

echo "<tr id=dostuff><td colspan=8><a class=button href=adduser.php>Create New User Account</a></td></tr>";
echo "</table>\n";
echo "</div>";
stdfoot();
