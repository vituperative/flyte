<?php
if (file_exists("methods_.php")) {
    include_once("methods_.php");
    require_once "user/user.class.php";
} else {
    include_once("../methods_.php");
    require_once "../user/user.class.php";
}

ini_set('default_charset', 'utf-8');
function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}
$time_start = getmicrotime();
if (mm::is_root_dir_()) {
    include_once "secrets.inc.php";
    include_once "cleanup.php";
} else {
    include_once "../include/secrets.inc.php";
    include_once "../include/cleanup.php";
}

global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

//require_once "secrets.inc.php";
//require_once "cleanup.php";

$max_torrent_size = 10000000;
$announce_interval = 900;
$signup_timeout = 86400 * 3;
$max_dead_torrent_time = 4 * 3600;
$autoclean_interval = 600;
$pic_base_url = "pic/";
$pagesize = 50;

##########################
#
# Changes below here should not normally be required
#
##########################

$appname = "Flyte";
$version = "1.2.1";

# the first one will be displayed on the pages
$announce_urls = array(); //
array_push(
    $announce_urls,
    $tracker_url_name . "/announce.php",
    $tracker_url_key . "/announce.php",
    $tracker_url_name . "/announce",
    $tracker_url_key . "/announce",
    $tracker_url_name . "/a",
    $tracker_url_key . "/a"
);

function dbconn($autoclean = 1)
{
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

    @($GLOBALS["___mysqli_ston"] = mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db))
        or die(mysqli_error($GLOBALS["___mysqli_ston"]));

    userlogin();

    if ($autoclean) {
        register_shutdown_function("autoclean");
    }
}

function userlogin()
{
    unset($GLOBALS["CURUSER"]);
    $uid = 0;
    $pass = '';
    if (isset($_COOKIE["auth"])) {
        list($uid, $pass) = explode(".", $_COOKIE["auth"]);
    } else {
        if (empty($_COOKIE["uid"]) || empty($_COOKIE["pass"])) {
            return;
        } else {
            $uid = $_COOKIE["uid"];
            $pass = $_COOKIE["pass"];
        }
    }
    $id = intval($uid);
    if (!$id || strlen($pass) != 32) {
        return;
    }

    $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT admin, id, username, password, secret, status FROM users WHERE id = $id AND status = 'confirmed'");
    $row = mysqli_fetch_array($res);
    if (!$row) {
        return;
    }

    $sec = $row["secret"];
    if ($pass !== md5($sec . $row["password"] . $sec)) {
        return;
    }

    $GLOBALS["CURUSER"] = $row;
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET last_access = NOW() WHERE id = " . $row["id"]);
}

function hex_esc($matches)
{
    return sprintf("%02x", ord($matches[0]));
}

function autoclean()
{
    global $autoclean_interval;

    $now = time();
    $docleanup = 0;

    $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT value_u FROM avps WHERE arg = 'lastcleantime'");
    $row = mysqli_fetch_array($res);
    if (!$row) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO avps (arg, value_u) VALUES ('lastcleantime',$now)");
        return;
    }
    $ts = $row[0];
    if ($ts + $autoclean_interval > $now) {
        return;
    }

    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE avps SET value_u=$now WHERE arg='lastcleantime' AND value_u = $ts");
    if (!mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        return;
    }

    docleanup();
}

function unesc($x)
{
    if (get_magic_quotes_gpc()) {
        return stripslashes($x);
    }

    return $x;
}

function mksize($bytes)
{
    $suffix = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
    $index = floor(log($bytes + 1, 1024)); // + 1 to prevent -INF
    if ($bytes > 102400000000)
        return sprintf("%.5s %s", $bytes / pow(1024, $index), $suffix[$index]);
    else if ($bytes > 10240000000)
        return sprintf("%.4s %s", $bytes / pow(1024, $index), $suffix[$index]);
    else if ($bytes > 1024000000)
        return sprintf("%.3s %s", $bytes / pow(1024, $index), $suffix[$index]);
    else
        return sprintf("%.0f %s", $bytes / pow(1024, $index), $suffix[$index]);
}

function deadtime()
{
    global $announce_interval;
    return time() - floor($announce_interval * 1.3);
}

function mkprettytime($s)
{
    if ($s < 0) {
        $s = 0;
    }

    $t = array();
    foreach (array("60:sec", "60:min", "24:hour", "0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        } else {
            $v = $s;
        }

        $t[$y[1]] = $v;
    }

    if ($t["day"] == 0 || $t["day"] > 1) {
        $day = " days ";
    } else {
        $day = " day ";
    }

    if ($t["hour"] == 0 || $t["hour"] > 1) {
        $hour = " hours ";
    } else {
        $hour = " hour ";
    }

    if ($t["min"] == 0 || $t["min"] > 1) {
        $minute = " minutes ";
    } else {
        $minute = " minute ";
    }

    if ($t["day"]) {
        return $t["day"] . $day . sprintf("%02d" . $hour, $t["hour"]);
    }

    if ($t["hour"]) {
        return sprintf("%d" . $hour . "%02d" . $minute, $t["hour"], $t["min"]);
    }

    if ($t["min"]) {
        return sprintf("%d" . $minute, $t["min"]);
    }

    if ($t["sec"] == 0 || $t["sec"] > 1) {
        $second = " seconds ";
    } else {
        $second = " second ";
    }

    return $t["sec"] . $second;
}

function mkglobal($vars)
{
    if (!is_array($vars)) {
        $vars = explode(":", $vars);
    }

    foreach ($vars as $v) {
        if (isset($_GET[$v])) {
            $GLOBALS[$v] = unesc($_GET[$v]);
        } elseif (isset($_POST[$v])) {
            $GLOBALS[$v] = unesc($_POST[$v]);
        } else {
            return 0;
        }
    }
    return 1;
}

function tr($x, $y, $noesc = 0, $count = 0)
{
    if ($noesc) {
        $a = $y;
    } else {
        $a = htmlspecialchars($y);
        $a = str_replace("\n", "<br>\n", $a);
    }
    if ($count % 2 == 0) {
        $style = 'r';
    } else {
        $style = 'a';
    }
    print("<tr><td>$x</td><td>$a</td></tr>\n");
}

function tr2($y, $noesc = 0, $count = 0)
{
    if ($noesc) {
        $a = $y;
    } else {
        $a = htmlspecialchars($y);
        $a = str_replace("\n", "<br>\n", $a);
    }
    if ($count % 2 == 0) {
        $style = 'r';
    } else {
        $style = 'a';
    }
    print("<tr><td colspan=2>$a</td></tr>\n");
}

function truncate($str, $length = 15, $trailing = '&hellip;')
{
    // take off chars for the trailing
    $length -= strlen($trailing);
    $str = htmlspecialchars($str);
    if (strlen($str) > $length) {
        // string exceeded length, truncate and add trailing dots
        return substr($str, 0, $length) . $trailing;
    } else {
        // string was already short enough, return the string
        $res = $str;
    }
    return $res;
}

function validfilename($name)
{
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email)
{
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function sqlesc($x)
{
    return "'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $x) . "'";
}

function sqlwildcardesc($x)
{
    return str_replace(array("%", "_"), array("\\%", "\\_"), mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $x));
}

function urlparse($m)
{
    $t = $m[0];
    if (preg_match(',^\w+://,', $t)) {
        return "<a href=\"$t\">$t</a>";
    }

    return "<a href=\"http://$t\">$t</a>";
}

function parsedescr($d)
{
    #Security: remove any html tags.
    //    $pd = preg_replace('/<[^>]*>/', "", $d);
    $pd = strip_tags($d, '<b><i><ul><ol><li><strong><hr><br><p>');
    #Interface: Add breaklines
    $pd = str_replace(array("\n", "\r"), array("<br>\n", ""), htmlspecialchars($pd));
    return $pd;
}

function stdhead($title = "")
{
    global $CURUSER, $pic_base_url, $tracker_title, $tracker_url_name, $tracker_path;
    /**
    header("Content-Type: text/html; charset=utf-8");
    if ($title == "") {
        $title = $tracker_title . " BitTorrent Tracker";
    } else {
        $title = $tracker_title . " BitTorrent Tracker - " . htmlspecialchars($title);
    }

    $trackertitle = $title;
     **/
    include "page_header.inc.php";
}

function stdfoot()
{
    global $pic_base_url, $version, $appname, $time_start, $contact, $tracker_title, $CURUSER;
    $time = getmicrotime() - $time_start;
    $sitename = ucwords(strtolower($tracker_title));
    $request = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : $_SERVER['SCRIPT_FILENAME']; // TODO ADD THAT 2 FUNCTION
    $bullet = '&nbsp;&nbsp;&nbsp;&bullet;&nbsp;&nbsp;&nbsp;';
    $user = new user();
    $running = $user->getCountRunningTorrents();
    $active = $user->getCountActiveTorrents();
    $total = $user->countTorrents();
    //    print('<p id=footer><span id=blurb>Running: ' . $appname . ' v. ' . $version . '</code></p>');
    //    print('<p id=footer><span id=blurb>Running: ' . $appname . ' v. ' . $version . '</code>' . $bullet . 'Page spawned in ' . $time . ' seconds</span></p>');
    if (strpos($request, "install") !== false)
        print("\n<p id=footer><span id=blurb>. . . : |&nbsp;&nbsp; " . $appname . " v. " . $version . " &nbsp;&nbsp;| : . . .</span></p>");
    else if ($CURUSER["admin"] == "yes")
        print("\n<p id=footer><span id=blurb>" . $appname . " v. " . $version . $bullet . "Torrents:&nbsp; " . $running . ' running,&nbsp;  ' . $active . " active,&nbsp; " . $total . " total" . $bullet . "Page spawned in " . round($time, 3) . " seconds</span></p>");
    else if ($CURUSER) {
        print('<p id=footer><span id=blurb>' . $sitename);
        print($bullet . 'Torrents:&nbsp; ' . $running . ' running,&nbsp;  ' . $active . ' active,&nbsp;  ' . $total . ' total' . $bullet . '<a href=rss.php target=_blank>RSS Feed</a></span></p>');
    } else if ($contact == "") {
        print('<p id=footer><span id=blurb>' . $sitename . ' (Est. 2017)' . $bullet . 'Design by <a href=http://skank.i2p/>dr|z3d</a></span></p>');
    } else {
        print('<p id=footer><span id=blurb>' . $sitename . ' (Est. 2017)' . $bullet . 'Admin: <code>' . $contact . '</code>' . $bullet . 'Design by <a href=http://skank.i2p/>dr|z3d</a></span></p>');
    }
    print("\n<style type=text/css>body {opacity: 1 !important; color: #bbb !important; overflow-x: auto !important;} a {opacity: 1 !important;} body::after {display: none !important;}</style>");
    print("\n</body>\n</html>");
}

function genbark($x, $y)
{
    stdhead($y);
    print("<p id=toast class=warn><span class=title>" . htmlspecialchars($y) . "</span><br>");
    print(htmlspecialchars($x) . "</p>\n");
    stdfoot();
    exit();
}

function mksecret($len = 20)
{
    $ret = "";
    for ($i = 0; $i < $len; $i++) {
        $ret .= chr(mt_rand(48, 122));
    }

    return $ret;
}

function httperr($code = 404)
{
    header("HTTP/1.0 404 Not found");
    print("<body style=\"background: #111; color: #f00;\">");
    print("<table width=100% height=100%><tr><td align=center><h1>Not Found</h1>\n");
    print("<p>The requested file or resource was not found on the server.</p></td></tr></table></body>\n");
    exit();
}

function logincookie($id, $password, $secret, $updatedb = 1)
{
    $md5 = md5($secret . $password . $secret);

    $auth = implode(".", array($id, $md5));

    setcookie("auth", $auth, 0x7fffffff, "/");

    if ($updatedb) {
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET last_login = NOW() WHERE id = $id");
    }
}

function logoutcookie()
{
    setcookie("auth", "", 0x7fffffff, "/");
}

function loggedinorreturn()
{
    global $CURUSER;
    if (!$CURUSER) {
        header("Refresh: 0; url=login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    }
}

function loggedoutorreturn()
{
    global $CURUSER;
    if ($CURUSER) {
        header("Refresh: 0; url=index.php");
        exit();
    }
}

function deletetorrent($id)
{
    global $torrent_dir;
    mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM torrents WHERE id = $id");
    foreach (explode(".", "peers.files.comments") as $x) {
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM $x WHERE torrent = $id");
    }

    unlink("$torrent_dir/$id.torrent");
}

function pager($rpp, $count, $href, $opts = array())
{
    global $tracker_path;
    $pages = ceil($count / $rpp);

    if (!@$opts["lastpagedefault"]) {
        $pagedefault = 0;
    } else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0) {
            $pagedefault = 0;
        }
    }

    if (isset($_GET["page"])) {
        $page = intval($_GET["page"]);
        if ($page < 0) {
            $page = $pagedefault;
        }
    } else {
        $page = $pagedefault;
    }

    $pager = "";

    $mp = $pages - 1;
    $as = "<b>&lt;&lt;&nbsp;Previous</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    } else {
        $pager .= $as;
    }

    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as = "<b>Next&nbsp;&gt;&gt;</b>";
    if ($page < $mp && $mp >= 0) {
        $pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    } else {
        $pager .= $as;
    }

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted) {
                    $pagerarr[] = "...";
                }

                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count) {
                $end = $count;
            }

            $text = "$start - $end";
            if ($i != $page) {
                $pagerarr[] = "<a class=pagelinks href=\"{$href}page=$i\">$text</a>";
            } else {
                $pagerarr[] = "<span id=pagenow>$text</span>";
            }
        }
        $pagerstr = join(" ", $pagerarr);
        $request = $_SERVER["REQUEST_URI"];
        $pagertop = "";
        if ($i != $page) {
            if ($request = $tracker_path || (strpos($request, "incldead") !== false) || (strpos($request, "mytorrents") !== false))
                $pagerbottom = "<p id=pager>$pagerstr</p>\n</div>\n";
            else
                $pagerbottom = "<p id=pager>$pagerstr</p>\n";
        } else {
            if ($request = $tracker_path || (strpos($request, "incldead") !== false) || (strpos($request, "mytorrents") !== false))
                $pagerbottom = "<p id=pager>$pagerstr<br>$pager</p>\n<div>\n";
            else
                $pagerbottom = "<p id=pager>$pagerstr<br>$pager</p>\n";
        }
    } else {
        $pagerbottom = "<p id=pager>$pager</p>\n";
    }

    $start = $page * $rpp;

    return array("", $pagerbottom, "LIMIT $start,$rpp");
}

function downloaderdata($res)
{
    $rows = array();
    $ids = array();
    $peerdata = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
        $id = $row["id"];
        $ids[] = $id;
        $peerdata[$id] = array("downloaders => 0, seeders => 0, comments => 0");
    }

    if (count($ids)) {
        $allids = implode(",", $ids);
        $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS c, torrent, seeder FROM peers WHERE torrent IN ($allids) GROUP BY torrent, seeder");
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row["seeder"] == "yes") {
                $key = "seeders";
            } else {
                $key = "downloaders";
            }

            $peerdata[$row["torrent"]][$key] = $row["c"];
        }
        $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS c, torrent FROM comments WHERE torrent IN ($allids) GROUP BY torrent");
        while ($row = mysqli_fetch_assoc($res)) {
            $peerdata[$row["torrent"]]["comments"] = $row["c"];
        }
    }

    return array($rows, $peerdata);
}

function commenttable($rows)
{
    print("<table id=comments>\n");
    $count = 0;
    foreach ($rows as $row) {
        if ($row["text"] != null) {
            print("<tr>\n");
            if (isset($row["username"])) {
                print("<th class=user>" . htmlspecialchars($row["username"]));
            } else {
                print("<th><i>User vanished!</i>\n");
            }

            print("<th class=posted>Posted: " . htmlspecialchars($row["added"]) . "</th>\n");
            print("</tr>\n");
            print("<tr>\n");
            print("<td colspan=\"2\"><span class=commentwrap>" . strip_tags($row["text"], "<b><i><ul><ol><li><strong><hr><br><p>") . "</span></td>\n");
            print("</tr>\n");
        }
        $count++;
    }
    print("</table>\n");
}

function searchfield($s)
{
    return preg_replace(array('/[^a-zA-Z0-9[\p{L}]]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist()
{
    $ret = array();
    $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM categories ORDER BY sort_index, id");
    while ($row = mysqli_fetch_array($res)) {
        $ret[] = $row;
    }

    return $ret;
}

function linkcolor($num)
{
    if (!$num) {
        return "red";
    }

    if ($num == 1) {
        return "yellow";
    }

    return "green";
}

function torrenttable($res, $variant = "index")
{
    global $pic_base_url;
    global $announce_urls;
    global $CURUSER;
    global $tracker_url_name;
    global $tracker_path;
    $url = (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") ? "?" . $_SERVER['QUERY_STRING'] . "&" : "?";
    $filteredURL = preg_replace('~(\?|&)order=[^&]*.~', '$1', $url);
    $filteredURL = htmlspecialchars($filteredURL);
?>

    <div class=tablewrap id=torrentlist>
        <table id=torrents>
            <tr>
                <th>
                    <a href="./<?= $filteredURL; ?>order=category">Type</a>
                </th>
                <th>
                    <a href="./<?= $filteredURL; ?>order=name">Name</a>
                </th>
                <th>Torrent</th>
                <?php
                if ($variant == "mytorrents") {
                ?>
                    <th>Visible</th>
                <?php
                }
                ?>
                <th>
                    <a href="./<?= $filteredURL; ?>order=size">Size</a>
                </th>
                <th>
                    <a href="./<?= $filteredURL; ?>order=numfiles">Files</a>
                </th>
                <th>
                    <a href="./<?= $filteredURL; ?>order=seeders">Seeds</a>
                </th>
                <th>
                    <a href="./<?= $filteredURL; ?>order=leechers">Leech</a>
                </th>
                <?php
                if ($CURUSER["admin"] == "yes") {
                ?>
                    <th>
                        <a href="./<?= $filteredURL; ?>order=views">Views</a>
                    </th>
                <?php
                }
                if ($CURUSER) {
                ?>
                    <th>
                        <a href="./<?= $filteredURL; ?>order=times_completed">DL's</a>
                    </th>
                <?php
                }
                ?>
                <th>
                    <a href="./<?= $filteredURL; ?>order=comments">Comments</a>
                </th>
                <th>
                    <a href="./<?= $filteredURL; ?>order=added">Added</a>
                </th>
                <?php
                if ($variant != "mytorrents" && $CURUSER) {
                ?>
                    <th>
                        <a href="./<?= $filteredURL; ?>order=owner">Uploader</a>
                    </th>
                <?php
                }
                ?>
            </tr>
        <?php
        $styles = array('a', 'r');

        while ($row = mysqli_fetch_assoc($res)) {

            array_push($styles, array_shift($styles));

            $id = $row["id"];
            print("<tr>\n");

            print("<td>");
            if (isset($row["cat_name"])) {
                print("<a href=\"./?cat=" . $row["category"] . "\" class=\"catlink\" data-tooltip=\"" . $row["cat_name"] . "\"><img src=\"" . $tracker_path . "pic/" . $row["category"] . ".png\" width=24 height=24></a>");
            } else {
                print("<span class=\"catlink\" data-tooltip=\"Uncategorized\"><img src=\"" . $tracker_path . "pic/unknown.png\" width=24 height=24></span>");
            }

            print("</td>\n");

            $dispname = htmlspecialchars($row["name"]);
            print("<td class=torrentname><a title=\"View details for: " . $dispname . "\" href=\"details.php?");
            if ($variant == "mytorrents") {
                print("returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;");
            }

            print("id=$id");
            if ($variant == "index") {
                print("&amp;hit=1");
            }

            print("\">$dispname</a>\n");
            if (isset($row["descr"]) && $row["descr"]) {
                //            print("<br>" . truncate(htmlspecialchars($row["ori_descr"], ENT_NOQUOTES), 150));
                $description = strip_tags($row["ori_descr"]);
                print("<br><span class=briefdesc");
                if (strlen($description) > 120) {
                    print(" title=\"" . htmlspecialchars(substr($description, 0, 1000)));
                    if (strlen($description) > 1000) {
                        print(" &hellip; [more information available on the details page]");
                    }
                    print("\"");
                }
                print(">");
                if (strlen($description) > 200)
                    print(substr($description, 0, 200) . "&hellip;");
                else
                    print($description);
                print("</span>");
            }
            print("</td>\n");

            if ($variant == "index") {
                print("<td class=dlicons><a href=\"download.php?id=$id&amp;file=" . htmlentities(urlencode($row["filename"])) . "\"><img src=\"" . $tracker_path . "pic/download.png\" border=0 width=24 height=24></a> <a href=\"magnet:?xt=urn:btih:" . preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])) . "&amp;dn=" . htmlentities(urlencode($row["filename"])) . "&amp;tr=" . $announce_urls[5] . "\"><img src=\"" . $tracker_path . "pic/magnet.png\" border=0 width=24 height=24></a></td>");
            } elseif ($variant == "mytorrents") {
                print("<td><a href=\"edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\"><span class=edit title=\"Edit torrent\">edit</span></a></td>\n");
            }

            if ($variant == "mytorrents") {
                print("<td>");
                if ($row["visible"] == "no") {
                    print("<span class=no>no</span>");
                } else {
                    print("<span class=yes>yes</span>");
                }

                print("</td>\n");
            }

            print("<td title=\"" . $row["size"] . " bytes\">" . mksize($row["size"]) . "</td>\n");

            if ($row["type"] == "shashgle") {
                print("<td>" . $row["numfiles"] . "</td>\n");
            } else {
                if ($variant == "index") {
                    print("<td><a href=\"details.php?id=$id&amp;hit=1&amp;filelist=1\">" . $row["numfiles"] . "</a></td>\n");
                } else {
                    print("<td><a href=\"details.php?id=$id&amp;filelist=1#filelist\">" . $row["numfiles"] . "</a></td>\n");
                }
            }

            if ($row["seeders"]) {
                if ($variant == "index") {
                    print("<td><a href=\"details.php?id=$id&amp;hit=1&amp;toseeders=1\">" . $row["seeders"] . "</a></td>\n");
                } else {
                    print("<td><a href=\"details.php?id=$id&amp;dllist=1#seeds\">" . $row["seeders"] . "</a></td>\n");
                }
            } else {
                print("<td>" . $row["seeders"] . "</td>\n");
            }

            if ($row["leechers"]) {
                if ($variant == "index") {
                    print("<td><a href=\"details.php?id=$id&amp;hit=1&amp;todlers=1\">" . $row["leechers"] . "</a></td>\n");
                } else {
                    print("<td><a href=\"details.php?id=$id&amp;dllist=1#leeches\">" . $row["leechers"] . "</a></td>\n");
                }
            } else {
                print("<td>" . $row["leechers"] . "</td>\n");
            }

            if ($CURUSER["admin"] == "yes")
                print("<td>" . $row["views"] . "</td>\n");
            if ($CURUSER)
                print("<td>" . $row["times_completed"] . "</td>\n");

            if (!$row["comments"]) {
                print("<td>" . $row["comments"] . "</td>\n");
            } else {
                if ($variant == "index") {
                    print("<td><a href=\"details.php?id=$id&amp;hit=1&amp;tocomm=1\">" . $row["comments"] . "</a></td>\n");
                } else {
                    print("<td><a href=\"details.php?id=$id&amp;page=0#startcomments\">" . $row["comments"] . "</a></td>\n");
                }
            }

            print("<td>" . preg_replace("/ .*/", "", $row["added"]) . "</td>\n");

            if ($variant == "index" && $CURUSER) {
                print("<td class=uploadername>" . (isset($row["username"]) ? htmlspecialchars($row["username"]) : "<i>Unknown</i>") . "</td>\n");
            }

            print("</tr>\n");
        }

        print("</table>\n");

        if (isset($rows)) {
            return $rows;
        }
    }

    function hash_pad($hash)
    {
        return str_pad($hash, 20);
    }

    function hash_where($name, $hash)
    {
        $shhash = preg_replace('/ *$/s', "", $hash);
        return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
    }
