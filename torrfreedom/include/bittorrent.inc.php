<?php
ini_set('default_charset', 'utf-8');
function getmicrotime() { 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
} 

$time_start = getmicrotime();

require_once("secrets.inc.php");
require_once("cleanup.php");

$max_torrent_size = 10000000;
$announce_interval = 3600;
$signup_timeout = 86400 * 3;
$max_dead_torrent_time = 4 * 3600;
$autoclean_interval = 600;
$pic_base_url = "pic/";

##########################
#
# Changes below here should not normally be required
#
##########################

$appname = "TorrFreedom";
$version = "1.1.0";

# the first one will be displayed on the pages
$announce_urls = array();
$announce_urls[] = $tracker_url_name . "/announce.php";
$announce_urls[] = $tracker_url_key . "/announce.php";
$announce_urls[] = $tracker_url_name . "/announce";
$announce_urls[] = $tracker_url_key . "/announce";
$announce_urls[] = $tracker_url_name . "/a";
$announce_urls[] = $tracker_url_key . "/a";

function dbconn($autoclean = 1) {
	global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

	@($GLOBALS["___mysqli_ston"] = mysqli_connect($mysql_host,  $mysql_user,  $mysql_pass, $mysql_db))
		or die(mysqli_error($GLOBALS["___mysqli_ston"]));

	userlogin();

	if ($autoclean)
		register_shutdown_function("autoclean");
}

function userlogin() {
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
	if (!$id || strlen($pass) != 32)
		return;
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT admin, id, username, password, secret, status FROM users WHERE id = $id AND status = 'confirmed'");
	$row = mysqli_fetch_array($res);
	if (!$row)
		return;
	$sec = $row["secret"];
	if ($pass !== md5($sec . $row["password"] . $sec))
		return;
	$GLOBALS["CURUSER"] = $row;
	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET last_access = NOW() WHERE id = " . $row["id"]);
}

function hex_esc($matches) {
	return sprintf("%02x", ord($matches[0]));
}

function autoclean() {
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
	if ($ts + $autoclean_interval > $now)
		return;
	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE avps SET value_u=$now WHERE arg='lastcleantime' AND value_u = $ts");
	if (!mysqli_affected_rows($GLOBALS["___mysqli_ston"]))
		return;

	docleanup();
}

function unesc($x) {
	if (get_magic_quotes_gpc())
		return stripslashes($x);
	return $x;
}

function mksize($bytes) {
   $suffix = array ('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
   $index = floor (log ($bytes + 1, 1024)); // + 1 to prevent -INF
   return sprintf ("%.0f %s", $bytes / pow (1024, $index), $suffix[$index]);
}

function deadtime() {
	global $announce_interval;
	return time() - floor($announce_interval * 1.3);
}

function mkprettytime($s) {
	if ($s < 0)
		$s = 0;
	$t = array();
	foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
		$y = explode(":", $x);
		if ($y[0] > 1) {
			$v = $s % $y[0];
			$s = floor($s / $y[0]);
		}
		else
			$v = $s;
		$t[$y[1]] = $v;
	}

	if ($t["day"])
		return $t["day"] . " day(s), " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
	if ($t["hour"])
		return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
	if ($t["min"])
		return sprintf("%d:%02d", $t["min"], $t["sec"]);
	return $t["sec"] . " secs";
}

function mkglobal($vars) {
	if (!is_array($vars))
		$vars = explode(":", $vars);
	foreach ($vars as $v) {
		if (isset($_GET[$v]))
			$GLOBALS[$v] = unesc($_GET[$v]);
		elseif (isset($_POST[$v]))
			$GLOBALS[$v] = unesc($_POST[$v]);
		else
			return 0;
	}
	return 1;
}

function tr($x,$y,$noesc=0,$count=0) {
	if ($noesc)
		$a = $y;
	else {
		$a = htmlspecialchars($y);
		$a = str_replace("\n", "<br />\n", $a);
	}
    if($count % 2 == 0) {
        $style = 'r';
    } else {
        $style = 'a';
    }
	print("<tr><td valign=\"top\" align=\"left\" class=\"${style}1\"><font size=2><b>$x</b></td><td valign=\"top\" class=\"${style}2\" ><font size=2>$a</td></tr>\n");
}

function truncate($str, $length=10, $trailing='...') {
      // take off chars for the trailing
      $length-=strlen($trailing);
      $str=htmlspecialchars($str);
      if (strlen($str) > $length) {
         // string exceeded length, truncate and add trailing dots
         return substr($str,0,$length).$trailing;
      } else { 
         // string was already short enough, return the string
         $res = $str;
      }
      return $res;
}

function validfilename($name) {
	return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email) {
	return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function sqlesc($x) {
	return "'".mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $x)."'";
}

function sqlwildcardesc($x) {
	return str_replace(array("%","_"), array("\\%","\\_"), mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $x));
}

function urlparse($m) {
	$t = $m[0];
	if (preg_match(',^\w+://,', $t))
		return "<a href=\"$t\">$t</a>";
	return "<a href=\"http://$t\">$t</a>";
}

function parsedescr($d) {
	#Security: remove any html tags.
	$pd = preg_replace('/<[^>]*>/', "", $d);
	#Interface: Add breaklines
	$pd = str_replace(array("\n", "\r"), array("<br />\n", ""), htmlspecialchars($pd));
	return $pd;
}

function stdhead($title = "") {
	global $CURUSER, $pic_base_url, $tracker_title;
	header("Content-Type: text/html; charset=utf-8");
	if ($title == "")
		$title = $tracker_title . " BitTracker";
	else
		$title = $tracker_title . " BitTracker - " . htmlspecialchars($title);
	$trackertitle = $title;
	include("page_header.inc.php");
}

function stdfoot() {
	global $pic_base_url, $version, $appname, $time_start;
        $time = round(getmicrotime() - $time_start,4);
	print('</td></tr><tr><td width="100%" height="21" colspan="2">');
	print('<div align="center"><font face=arial size=1>' . $appname . " v" . $version . ' -- Page generated in ' . $time . '</div>');
	print('</td></tr></table></body></html>');
}

function genbark($x,$y) {
	stdhead($y);
	print("<h2>" . htmlspecialchars($y) . "</h2>\n");
	print("<p>" . htmlspecialchars($x) . "</p>\n");
	stdfoot();
	exit();
}

function mksecret($len = 20) {
	$ret = "";
	for ($i = 0; $i < $len; $i++)
		$ret .= chr(mt_rand(48, 122));
	return $ret;
}

function httperr($code = 404) {
	header("HTTP/1.0 404 Not found");
	print("<h1>Not Found</h1>\n");
	print("<p>Sorry pal :(</p>\n");
	exit();
}

function logincookie($id, $password, $secret, $updatedb = 1) {
	$md5 = md5($secret . $password . $secret);
	
	$auth = implode("." , array($id, $md5));
	
    setcookie("auth", $auth, 0x7fffffff, "/");

	if ($updatedb)
		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET last_login = NOW() WHERE id = $id");
}

function logoutcookie() {
	setcookie("auth", "", 0x7fffffff, "/");
}

function loggedinorreturn() {
	global $CURUSER;
	if (!$CURUSER) {
		header("Refresh: 0; url=login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
		exit();
	}
}

function loggedoutorreturn() {
	global $CURUSER;
	if ($CURUSER) {
		header("Refresh: 0; url=index.php");
		exit();
	}
}

function deletetorrent($id) {
	global $torrent_dir;
	mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM torrents WHERE id = $id");
	foreach(explode(".","peers.files.comments") as $x)
		mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM $x WHERE torrent = $id");
	unlink("$torrent_dir/$id.torrent");
}

function pager($rpp, $count, $href, $opts = array()) {
	$pages = ceil($count / $rpp);

	if (!@$opts["lastpagedefault"])
		$pagedefault = 0;
	else {
		$pagedefault = floor(($count - 1) / $rpp);
		if ($pagedefault < 0)
			$pagedefault = 0;
	}

	if (isset($_GET["page"])) {
		$page = intval($_GET["page"]);
		if ($page < 0)
			$page = $pagedefault;
	}
	else
		$page = $pagedefault;

	$pager = "";

	$mp = $pages - 1;
	$as = "<b>&lt;&lt;&nbsp;Prev</b>";
	if ($page >= 1) {
		$pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
		$pager .= $as;
		$pager .= "</a>";
	}
	else
		$pager .= $as;
	$pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$as = "<b>Next&nbsp;&gt;&gt;</b>";
	if ($page < $mp && $mp >= 0) {
		$pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
		$pager .= $as;
		$pager .= "</a>";
	}
	else
		$pager .= $as;

	if ($count) {
		$pagerarr = array();
		$dotted = 0;
		$dotspace = 3;
		$dotend = $pages - $dotspace;
		$curdotend = $page - $dotspace;
		$curdotstart = $page + $dotspace;
		for ($i = 0; $i < $pages; $i++) {
			if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
				if (!$dotted)
					$pagerarr[] = "...";
				$dotted = 1;
				continue;
			}
			$dotted = 0;
			$start = $i * $rpp + 1;
			$end = $start + $rpp - 1;
			if ($end > $count)
				$end = $count;
			$text = "$start&nbsp;-&nbsp;$end";
			if ($i != $page)
				$pagerarr[] = "<a href=\"{$href}page=$i\">$text</a>";
			else
				$pagerarr[] = "<b>$text</b>";
		}
		$pagerstr = join(" | ", $pagerarr);
		$pagertop = "<p align=\"center\">$pager<br />$pagerstr</p>\n";
		$pagerbottom = "<p align=\"center\">$pagerstr<br />$pager</p>\n";
	}
	else {
		$pagertop = "<p align=\"center\">$pager</p>\n";
		$pagerbottom = $pagertop;
	}

	$start = $page * $rpp;

	return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}

function downloaderdata($res) {
	$rows = array();
	$ids = array();
	$peerdata = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$rows[] = $row;
		$id = $row["id"];
		$ids[] = $id;
		$peerdata[$id] = array(downloaders => 0, seeders => 0, comments => 0);
	}

	if (count($ids)) {
		$allids = implode(",", $ids);
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS c, torrent, seeder FROM peers WHERE torrent IN ($allids) GROUP BY torrent, seeder");
		while ($row = mysqli_fetch_assoc($res)) {
			if ($row["seeder"] == "yes")
				$key = "seeders";
			else
				$key = "downloaders";
			$peerdata[$row["torrent"]][$key] = $row["c"];
		}
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS c, torrent FROM comments WHERE torrent IN ($allids) GROUP BY torrent");
		while ($row = mysqli_fetch_assoc($res)) {
			$peerdata[$row["torrent"]]["comments"] = $row["c"];
		}
	}

	return array($rows, $peerdata);
}

function commenttable($rows) {
	print("<table class=\"table2\" align=\"center\" cellspacing=\"0\" width=\"75%\" cellpadding=\"1\">\n");
	$count = 0;
	foreach ($rows as $row) {
		print("<tr>\n");
		if (isset($row["username"]))
			print("<td class=\"td1\"><span class=\"text1\"><a name=\"comm" . $row["id"] . "\">" . htmlspecialchars($row["username"]) . "</a></span></td>\n");
		else
			print("<td class=\"td1\"><span class=\"text1\"><a name=\"comm" . $row["id"] . "\"><i>(orphaned)</i></a></span></td>\n");
		print("<td class=\"td1\" align=\"right\"><span class=\"text1\">" . htmlspecialchars($row["added"]) . "</span></td>\n");
		print("</tr>\n");
		print("<tr>\n");
		print("<td colspan=\"2\">" . $row["text"] . "</td>\n");
		print("</tr>\n");
		$count++;
	}
	print("</table>");
}

function searchfield($s) {
	return preg_replace(array('/[^a-zA-Z0-9[\p{L}]]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist() {
	$ret = array();
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM categories ORDER BY sort_index, id");
	while ($row = mysqli_fetch_array($res))
		$ret[] = $row;
	return $ret;
}

function linkcolor($num) {
	if (!$num)
		return "red";
	if ($num == 1)
		return "yellow";
	return "green";
}

function torrenttable($res, $variant = "index") {
	global $pic_base_url;
	global $announce_urls;
?>

<table align="center" cellpadding="0" cellspacing="0" width="95%" border="0" class="table1">
<tr>

<td align=center class="td1"><span class="text1">Type</span></td>
<td align=center class="td1"><span class="text1">Name</span></td>
<td align=center class="td1"><span class="text1">Torrent</span></td>
<?php

	if ($variant == "mytorrents")
		print("<td align=center class=\"td1\"><span class=\"text1\">Visible</span></td>\n");

?>
<td align=center class="td1"><span class="text1">Files</span></td>
<td align=center class="td1"><span class="text1">Comments</span></td>
<td align=center class="td1"><span class="text1">Added</span></td>
<td align=center class="td1"><span class="text1">Size</span></td>
<td align=center class="td1"><span class="text1">Views</span></td>
<td align=center class="td1"><span class="text1">Hits</span></td>
<td align=center class="td1"><span class="text1">DL's</span></td>
<td align=center class="td1"><span class="text1">Seeds</span></td>
<td align=center class="td1"><span class="text1">Leech</span></td>
<?php

	if ($variant != "mytorrents")
		print("<td align=center class=\"td1\"><span class=\"text1\">Uploader</span></td>\n");

?>

<?php

	print("</tr>\n");

    $styles = array('a','r');

	while ($row = mysqli_fetch_assoc($res)) {

		array_push($styles, array_shift($styles));

		$id = $row["id"];
		print("<tr>\n");

		print("<td class=\"".$styles[0]."2\" align=center>");
		if (isset($row["cat_name"])) {
			print("<a href=\"./?cat=" . $row["category"] . "\" class=\"catlink\" data-tooltip=\"" . $row["cat_name"] . "\"><img src=\"../pic/" . $row["category"] . ".png\" width=24 height=24></a>");
		}
		else
			print("-");
		print("</td>\n");

		$dispname = "<b>" . htmlspecialchars($row["name"]) . "</b>";
		print("<td class=\"".$styles[0]."1\" align=left><font size=2><a href=\"details.php?");
		if ($variant == "mytorrents")
			print("returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;");
		print("id=$id");
		if ($variant == "index")
			print("&amp;hit=1");
		print("\">$dispname</a>\n");
        if (isset($row["descr"])) {
            print("<br>".truncate(htmlspecialchars($row["ori_descr"]),30));
        }
        print("</td>\n");

		if ($variant == "index") {
			print("<td class=\"".$styles[0]."2\" align=center><div class=\"dlicons\"><a href=\"download.php?id=$id&file=" . htmlentities(urlencode($row["filename"])) . "\"><img src=\"../pic/download.png\" border=\"0\" width=24 height=24></a> <a href=\"magnet:?xt=urn:btih:" . preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])) . "&dn=" .  htmlentities(urlencode($row["filename"])) . "&tr=" . $announce_urls[5] . "\"><img src=\"../pic/magnet.png\" border=\"0\" width=24 height=24></a></div></td>");
		} elseif ($variant == "mytorrents")
			print("<td class=\"".$styles[0]."2\" align=center><a href=\"edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\">edit</a></td>\n");

		if ($variant == "mytorrents") {
			print("<td class=\"".$styles[0]."1\" align=center>");
			if ($row["visible"] == "no")
				print("<b>no</b>");
			else
				print("yes");
			print("</td>\n");
		}

		if ($row["type"] == "shashgle")
			print("<td class=\"".$styles[0]."2\" align=center><font size=2 face=arial>" . $row["numfiles"] . "</td>\n");
		else {
			if ($variant == "index")
				print("<td class=\"".$styles[0]."2\" align=center><b><font size=2 face=arial><a href=\"details.php?id=$id&amp;hit=1&amp;filelist=1\">" . $row["numfiles"] . "</a></b></td>\n");
			else
				print("<td class=\"".$styles[0]."2\" align=center><b><font size=2 face=arial><a href=\"details.php?id=$id&amp;filelist=1#filelist\">" . $row["numfiles"] . "</a></b></td>\n");
		}

		if (!$row["comments"])
			print("<td class=\"".$styles[0]."1\" align=center><font size=2 face=arial>" . $row["comments"] . "</td>\n");
		else {
			if ($variant == "index")
				print("<td class=\"".$styles[0]."1\" align=center><b><font size=2 face=arial><a href=\"details.php?id=$id&amp;hit=1&amp;tocomm=1\">" . $row["comments"] . "</a></b></td>\n");
			else
				print("<td class=\"".$styles[0]."1\" align=center><b><font size=2 face=arial><a href=\"details.php?id=$id&amp;page=0#startcomments\">" . $row["comments"] . "</a></b></td>\n");
		}

		print("<td class=\"".$styles[0]."1\" align=center><font size=2 face=arial>" . str_replace(" ", "<br />", $row["added"]) . "</td>\n");
		print("<td class=\"".$styles[0]."2\" align=center><font size=2 face=arial>" . mksize($row["size"]) . "</td>\n");
		print("<td class=\"".$styles[0]."1\" align=center><font size=2 face=arial>" . $row["views"] . "</td>\n");
		print("<td class=\"".$styles[0]."2\" align=center><font size=2 face=arial>" . $row["hits"] . "</td>\n");
		print("<td class=\"".$styles[0]."1\" align=center><font size=2 face=arial>" . $row["times_completed"] . "</td>\n");

		if ($row["seeders"]) {
			if ($variant == "index")
				print("<td class=\"".$styles[0]."2\" align=center><b><font size=2 face=arial><a class=\"" . linkcolor($row["seeders"]) . "\" href=\"details.php?id=$id&amp;hit=1&amp;toseeders=1\">" . $row["seeders"] . "</a></b></td>\n");
			else
				print("<td class=\"".$styles[0]."2\" align=center><b><font size=2 face=arial><a class=\"" . linkcolor($row["seeders"]) . "\" href=\"details.php?id=$id&amp;dllist=1#seeds\">" . $row["seeders"] . "</a></b></td>\n");
		}
		else
			print("<td class=\"".$styles[0]."2\" align=center><font size=2 face=arial><span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span></td>\n");

		if ($row["leechers"]) {
			if ($variant == "index")
				print("<td class=\"".$styles[0]."1\" align=center><font size=2 face=arial><b><a class=\"" . linkcolor($row["leechers"]) . "\" href=\"details.php?id=$id&amp;hit=1&amp;todlers=1\">" . $row["leechers"] . "</a></b></td>\n");
			else
				print("<td class=\"".$styles[0]."1\" align=center><font size=2 face=arial><b><a class=\"" . linkcolor($row["leechers"]) . "\" href=\"details.php?id=$id&amp;dllist=1#leeches\">" . $row["leechers"] . "</a></b></td>\n");
		}
		else
			print("<td class=\"".$styles[0]."1\" align=center><font size=2 face=arial><span class=\"" . linkcolor($row["leechers"]) . "\">" . $row["leechers"] . "</span></td>\n");

		if ($variant == "index")
			print("<td class=\"".$styles[0]."2\" align=center><font size=2 face=arial>" . (isset($row["username"]) ? htmlspecialchars($row["username"]) : "<i>(unknown)</i>") . "</td>\n");

		print("</tr>\n");
	}

	print("</table>\n");

	if (isset($rows))
		return $rows;
}

function hash_pad($hash) {
	return str_pad($hash, 20);
}

function hash_where($name, $hash) {
	$shhash = preg_replace('/ *$/s', "", $hash);
	return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}
?>
