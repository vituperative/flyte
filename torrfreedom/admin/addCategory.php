<?php
require 'admin_class.php';
$admin = new admin();

//delUserByUsername($username, $withTorrents=True, $withComments=True)

if(isset($_GET['add_category'])  ){
   $ret=$admin->addCategoryByName($_GET['add_category']);
   if( !$ret ){
      print ("<p class=fail>MySQL error: ".$admin->getLastSQLError()."</p>");
   }
   if( isset($_GET['sort_index']) && $_GET['sort_index'] > 0 ){
	$ret=$admin->changeCategorySortIndexByName($_GET['add_category'], $_GET['sort_index']);
   	if( !$ret )
     	 print ("<p class=fail>MySQL error: ".$admin->getLastSQLError()."</p>");
   }
   if( $ret )
   	header("Location: categories.php");
}

?>
<form action=addCategory.php method=GET>
<div id=server class=usermanage>
<table>
	<tr><th colspan=2>Create Category</th></tr>
	<tr><td>Name</td><td><input type=text name=add_category required></td></tr>
	<tr><td>sort_index(optional [-1 is default])</td><td><input type=number value=-1 name=sort_index></td></tr>
</table>
</div>
</form>

<?php stdfoot(); ?>

