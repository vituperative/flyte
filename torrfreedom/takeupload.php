<?php

require_once("include/benc.php");
require_once("include/bittorrent.inc.php");

ini_set("upload_max_filesize",$max_torrent_size);

function bark($msg) {
	genbark($msg, "Upload failed!");
}

dbconn();

loggedinorreturn();

foreach(explode(":","descr:type:name") as $v) {
	if (!isset($_POST[$v]))
		bark("missing form data");
}

if (!isset($_FILES["file"]))
	bark("missing form data");

$f = $_FILES["file"];
$fname = unesc($f["name"]);
if (empty($fname))
	bark("Empty filename!");
if (!validfilename($f["name"]))
	bark("Invalid filename! $fname");
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
	bark("Invalid filename (not a .torrent).");
$shortfname = $torrent = $matches[1];
if (!empty($_POST["name"]))
	$torrent = unesc($_POST["name"]);

$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname))
	bark("eek");
if (!filesize($tmpname))
	bark("Empty file!");

$dict = bdec_file($tmpname, $max_torrent_size);
if (!isset($dict))
	bark("What the hell did you upload? This is not a bencoded file!");

function dict_check($d, $s) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$a = explode(":", $s);
	$dd = $d["value"];
	$ret = array();
	foreach ($a as $k) {
		unset($t);
		if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
			$k = $m[1];
			$t = $m[2];
		}
		if (!isset($dd[$k]))
			bark("dictionary is missing key(s)");
		if (isset($t)) {
			if ($dd[$k]["type"] != $t)
				bark("invalid entry in dictionary");
			$ret[] = $dd[$k]["value"];
		}
		else
			$ret[] = $dd[$k];
	}
	return $ret;
}

function dict_get($d, $k, $t) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$dd = $d["value"];
	if (!isset($dd[$k]))
		return;
	$v = $dd[$k];
	if ($v["type"] != $t)
		bark("invalid dictionary entry type");
	return $v["value"];
}

list($ann, $info) = dict_check($dict, "announce(string):info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

//check against both announce urls: name and b64key as defined in bittorrent.inc.php:30,31, secrets.inc.php:8,9
//if (!in_array($ann, $announce_urls, 1))
//	bark("invalid announce url! must be " . $announce_urls[0] . " or " . $announce_urls[1]);

//check announce url against b64key announce url, if not b64key, reencode dict
$b64_trackerurl = "$tracker_url_key/announce.php";
if (strcmp($ann,  $b64_trackerurl) != 0) {
	$dict[value][announce][value] = $b64_trackerurl;
	$newdict = benc($dict);
	$fp = fopen($tmpname, "w");
	if (!$fp)
		bark("problem rewriting torrent with new b64 key");
 	fputs($fp, $newdict);
	fclose($fp);
	}

if (strlen($pieces) % 20 != 0)
	bark("invalid pieces");

$filelist = array();
$totallen = dict_get($info, "length", "integer");
if (isset($totallen)) {
	$filelist[] = array($dname, $totallen);
	$type = "single";
}
else {
	$flist = dict_get($info, "files", "list");
	if (!isset($flist))
		bark("missing both length and files");
	if (!count($flist))
		bark("no files");
	$totallen = 0;
	foreach ($flist as $fn) {
		list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
		$totallen += $ll;
		$ffa = array();
		foreach ($ff as $ffe) {
			if ($ffe["type"] != "string")
				bark("filename error");
			$ffa[] = $ffe["value"];
		}
		if (!count($ffa))
			bark("filename error");
		$ffe = implode("/", $ffa);
		$filelist[] = array($ffe, $ll);
	}
	$type = "multi";
}

$infohash = pack("H*", sha1($info["string"]));




$descr = unesc($_POST["descr"]);

$ret = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO torrents (search_text, filename, owner, visible, info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action) VALUES (" .
		implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"), $fname, $CURUSER["id"], "no", $infohash, $torrent, $totallen, count($filelist), $type, parsedescr($descr), $descr, intval($_POST["type"]), $dname))) .
		", NOW(), NOW())");

//error_log(implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"), $fname, $CURUSER["id"], "no", $infohash, $torrent, $totallen, count($filelist), $type, parsedescr($descr), $descr, intval($_POST["type"]), $dname))), 0);

//error_log(mysqli_error($GLOBALS["___mysqli_ston"]));

if (!$ret) {
	if (mysqli_errno($GLOBALS["___mysqli_ston"]) === 1062)
		bark("Torrent already uploaded!");
	bark("mysql puked: ".mysqli_error($GLOBALS["___mysqli_ston"]));
}
$id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);

@mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM files WHERE torrent = $id");
foreach ($filelist as $file) {
	@mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO files (torrent, filename, size) VALUES ($id, ".sqlesc($file[0]).",".$file[1].")");
}

move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");

header("Refresh: 0; url=details.php?id=$id&uploaded=1");

?>
