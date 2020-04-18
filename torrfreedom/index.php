<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}

require_once 'include/bittorrent.inc.php';

dbconn();

$pagesize = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 50;
$searchstr = @unesc($_GET["search"]);
$cleansearchstr = searchfield($searchstr);
if (empty($cleansearchstr)) {
    unset($cleansearchstr);
}

$orderby = "ORDER BY torrents.id DESC";

if (isset($_GET['order'])) {
    $orders = array("added", "swarmsize", "size", "times_completed", "comments", "category", "numfiles", "owner", "seeders", "leechers", "name", "views");
    foreach ($orders as $order) {
        if ($_GET['order'] == $order) {
            if($order == "name")
                $orderby = "ORDER BY torrents.$order ASC";
            else if($order == "owner")
                $orderby = "ORDER BY users.username ASC";
            else if($order == "swarmsize")
                $orderby = "ORDER BY torrents.leechers + torrents.seeders DESC";
            else
                $orderby = "ORDER BY torrents.$order DESC";
            break;
        }
    }
}

//print ("now order by is: ".$orderby);


$addparam = "";
$wherea = array();

if (isset($_GET["incldead"])) {
    $addparam .= "incldead=1&amp;";
    if (!isset($CURUSER) || $CURUSER["admin"] !== "yes") {
        $wherea[] = "banned != 'yes'";
    }
    if ($_GET["incldead"] != '1')
        $wherea[] = "visible != 'no'";
    else $wherea[] = "visible != 'yes'";
} else {
    $wherea[] = "visible != 'no'";
}
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
    $query = "SELECT torrents.*, DATE_FORMAT(CONVERT_TZ(torrents.added, @@session.time_zone, '+00:00'), '%d.%m.%y %T') as added, categories.name AS cat_name, torrents.leechers + torrents.seeders as swarmsize, users.username FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";

    //print($query);
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
}

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
        // not working -> header("Refresh: 5; url=./?incldead=1&cat=0");
        print("<p id=toast class=warn><span class=title>Warning!</span>No torrents currently active.<br>Select <i>include inactive</i> in the search dropdown to view all torrents.</p>\n");
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
