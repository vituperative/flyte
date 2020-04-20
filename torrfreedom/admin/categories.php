<?php
global $tracker_path;
require 'admin_class.php';
$admin = new admin();
$categories = $admin->getCategories();
//$torrentcount = $admin->getCountOfTorrentsInCategory();
echo "<div id=server class=categories><table>
<tr><th></th><th>Name (torrents)</th><!--<th>ID</th><th>Sort Index</th>--><th>Delete</th></tr>";
while ($row = mysqli_fetch_array($categories)) {
    echo "<tr>";
    echo "<td><a href=" . $tracker_path . "?cat=" . $row['id'] . "\" class=\"catlink\" title=\"ID: " . $row['id'] . " / Sort Index: " . $row['sort_index'] . "\"><img src=\"" . $tracker_path . "pic/" . $row['id'] . ".png\" width=24 height=24></a></td>";
    echo "<td>" . $row['name'] . " (" . $torrentcount . ")</td>";
    echo "<td><a href='delCategory.php?wdel_category=" . $row['id'] . "' class=button><span class=no></span></a></td>";
    echo "</tr>";
}
echo "</table></div>";
?>

<?php stdfoot(); ?>
