<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}

require_once 'include/bittorrent.inc.php';

dbconn();

$pagesize = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 25;
$searchstr = @unesc($_GET["search"]);
$cleansearchstr = searchfield($searchstr);
if (empty($cleansearchstr)) {
    unset($cleansearchstr);
}

$orderby = "ORDER BY torrents.id DESC";
if(isset($_GET['order'])){

$orders = array("added", "swarmsize", "size", "times_completed", "comments");
foreach( $orders as $order ){
   if( $_GET['order'] == $order ){
         $orderby = "ORDER BY torrents.$order DESC";
   }
}


}
//print ("now order by is: ".$orderby);


$addparam = "";
$wherea = array();

if (isset($_GET["incldead"]) ) {
    $addparam .= "incldead=1&amp;";
    if (!isset($CURUSER) || $CURUSER["admin"] !== "yes") {
        $wherea[] = "banned != 'yes'";
    }
    if( $_GET["incldead"] !='1' )
     $wherea[] = "visible != 'no'";
}else $wherea[] = "visible != 'no'";

//var_dump($wherea);

if (isset($_GET["cat"]) && ($_GET["cat"] != 0)) {
    $wherea[] = "category = " . sqlesc($_GET["cat"]);
    $addparam .= "cat=" . urlencode($_GET["cat"]) . "&amp;";
}
$wherebase = $wherea;
if (isset($cleansearchstr)) {
    $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
    $addparam .= "search=" . urlencode($searchstr) . "&amp;";
    $orderby = "";
}
$where = implode(" AND ", $wherea);
if ($where != "") {
    $where = "WHERE $where";
}

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM torrents $where") or die(mysqli_error($GLOBALS["___mysqli_ston"]));
$row = mysqli_fetch_array($res);
$count = $row[0];

if (!$count && isset($cleansearchstr)) {
    $wherea = $wherebase;
    $orderby = "ORDER BY id DESC";
    $searcha = explode(" ", $cleansearchstr);
    $sc = 0;
    foreach ($searcha as $searchss) {
        if (strlen($searchss) <= 1) {
            continue;
        }

        $sc++;
        if ($sc > 5) {
            break;
        }

        $ssa = array();
        foreach (array("search_text", "ori_descr", "torrents.name") as $sss) {
            $ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
        }

        $wherea[] = "(" . implode(" OR ", $ssa) . ")";
    }
    if ($sc) {
        $where = implode(" AND ", $wherea);
        if ($where != "") {
            $where = "WHERE $where";
        }
	//echo "SELECT COUNT(*) FROM torrents $where";

        $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) FROM torrents $where");
        if ($res != false) {
            $row = mysqli_fetch_array($res);
            $count = $row[0];
        }
    }
}

//print("where:".$where);

if ($count) {
    list($pagertop, $pagerbottom, $limit) = pager($pagesize, $count, "./?" . $addparam);
    $query = "SELECT torrents.*, DATE_FORMAT(CONVERT_TZ(torrents.added, @@session.time_zone, '+00:00'), '%d.%m.%y %T') as added, categories.name AS cat_name, torrents.leechers+torrents.seeders as swarmsize, users.username FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";

   // die($query);
    $res = mysqli_query($GLOBALS["___mysqli_ston"], $query)
    or die(mysqli_error($GLOBALS["___mysqli_ston"]));
} else {
    unset($res);
}

//if ($count == 1) {
//    $row = mysql_fetch_array($res);
//    header("Refresh: 0; url=details.php?id=" . $row["id"] . "&searched=" . urlencode($searchstr));
//    exit();
//}

$additionals = 1;

if (isset($cleansearchstr)) {
    stdhead("Search results for: $searchstr");
} else {
    stdhead();
    ?>
<?php
}

$cats = genrelist();

?>

<div id=searchandshow>
<input type=checkbox name=togglepanel id=togglepanel><label for=togglepanel title="Toggle Panel Visibility"><span id=toggle>&nbsp;</span></label>
<form method="get" action="./">
<div id=search>
<input name="search" type="text" value="<?=htmlspecialchars($searchstr)?>" size="40" class="input">
<select class="input" name="cat"><option value="0">All Categories</option>
<?php

$catdropdown = "";
foreach ($cats as $cat) {
    $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
    if (isset($_GET["cat"]) && $cat["id"] == $_GET["cat"]) {
        $catdropdown .= " selected=\"selected\"";
    }

    $catdropdown .= ">" . htmlspecialchars($cat["name"]) . "</option>\n";
}

$deadchkbox = "<label><input type=\"checkbox\" name=\"incldead\" value=\"1\"";
if (isset($_GET["incldead"])) {
    $deadchkbox .= " checked=\"checked\"";
}

$deadchkbox .= " /> include inactive</label>&nbsp; \n";

?>
<?=$catdropdown?>
</select>
Sort by:
<select name='order'>
<option value='added'>Upload Date</option>
<option value='swarmsize'>Swarm size</option>
<option value='size'>File size</option>
<option value='times_completed'>Downloads</option>
<option value='comments'>Comments</option>
</select>
<?=$deadchkbox?>
<input type="submit" value="Search!" class="input"/>
</div>
</form>
<div id=torrentshow>
<!--
<?php
if ($additionals) {
    $time_end = getmicrotime();
    $time = round($time_end - $time_start, 4);
}
?>
<form method="get" action="./">
Show: <select class="input" name="cat"><option value="0">All Categories</option>
<?=$catdropdown?>
</select>
<?=$deadchkbox?>
<input type="submit" value="Go!" class="input"/>
</form>
-->
</div>
</div>
<?php

if (isset($cleansearchstr)) {
    print("<h2>Search results for \"" . htmlspecialchars($searchstr) . "\"</h2>\n");
}

if ($count) {
    print($pagertop);

    torrenttable($res);

    print($pagerbottom);
} else {
    if (isset($cleansearchstr)) {
        print("<p id=toast class=warn><span class=title>Search Results</span>Nothing found!<br>");
        print("Try again with a refined search string.</p>\n");
    } else {
        print("<p id=toast class=warn><span class=title>Warning!</span>No active torrents currently available.</p>\n");
    }
}
if (isset($_SERVER['HTTP_REFERER']))
    $referrer = $_SERVER['HTTP_REFERER'];
else
    $referrer = "unknown";
if (isset($_COOKIE["auth"]))
    $cookie = $_COOKIE["auth"];
if (strpos($referrer, 'my') !== false && strpos($referrer, 'returnto') === false && $cookie === false) {
    print("<p id=toast class=success><span class=title>Logout Complete</span>You have been successfully logged out!</p>\n");
}
stdfoot();
?>

