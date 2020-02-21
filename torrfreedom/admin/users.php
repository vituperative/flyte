<?php
require 'admin_class.php';
$admin = new admin();

print("<div id=server class=users>\n");

if(isset($_GET['add_user'])){
    $admin->addUser($_GET['add_user'], $_GET['password'], $_GET['admin']);
}elseif(isset($_GET['del_user'])){
	$admin->delUserByUsername($_GET['del_user']);
} 
/*
	in now... version we can to do delete in there (C) Raven
	So need to check.
*/


$result = $admin->getAllUsers();

echo "<table>
<tr><th>User</th><th>Joined</th><th>Last login</th><th>Last access</th><th>Torrents</th><th>Comments</th><th>Delete</th></tr>\n";
while($row = mysqli_fetch_array($result))
{
echo "<tr>";
echo "<td>" . $row['username'] . "</td>";
echo "<td>" . $row['added'] . "</td>";
echo "<td>" . $row['last_login'] . "</td>";
echo "<td>" . $row['last_access'] . "</td>";
echo "<td>" . $row['cntt'] . "</td>";
echo "<td>" . $row['cntc'] . "</td>";
//...
echo "<td><a href='users.php?del_user=".$row['username']."' class=button><span class=no></span></a></td>";
echo "</tr>\n";

}
/*
//from I2Pd :) 
<style> 
  .slide p, .slide [type='checkbox']{ display:none; } 
  .slide [type='checkbox']:checked ~ p { display:block; margin-top: 0; padding: 0; }
  .disabled:after { color: #D33F3F; content: "Disabled" }
  .enabled:after  { color: #56B734; content: "Enabled"  }
</style>
<div class='slide'><label for='slide-info'>Add user. cleck for see.</label> <!-- too from I2Pd -->
<input type='checkbox' id='slide-info'/>
	<p> 	add user form	</p>
	<form action=adduser.php method=GET>
	<div id=server class=usermanage>
	<table>
	<tr><th colspan=2>Create User Account</th></tr>
	<tr><td>Username</td><td><input type=text name=add_user required></td></tr>
	<tr><td>Password</td><td><input type=text name=password required></td></tr>
	<tr><td>	Admin Privileges</td><td>
	<select>
	  <option value=no selected>No&hellip; just a regular user account</option>
	  <option value=yes>Yes&hellip; full administrative privileges</option></td></tr>
	<tr id=dostuff><td colspan=2>	<input type=submit value="Add User"</td></tr>
	</div>
</div>
*/

echo "<tr id=dostuff><td colspan=7><a class=button href=adduser.php>Create New User Account</a></td></tr>";
echo "</table>\n";
echo "</div>";
stdfoot();
?>
