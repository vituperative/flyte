<?php

require_once "include/benc.php";
require_once "include/bittorrent.inc.php";

ini_set("upload_max_filesize", $max_torrent_size);

function bark($msg)
{
    genbark($msg, "Upload failed!");
}

function dict_check($d, $s)
{
    if ($d["type"] != "dictionary") {
        bark("Invalid data in torrent: not a dictionary!");
    }

    $a = explode(":", $s);
    $dd = $d["value"];
    $ret = array();
    foreach ($a as $k) {
        unset($t);
        if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
            $k = $m[1];
            $t = $m[2];
        }

        if (!isset($dd[$k])) {
            if($k == "announce") {
                $dd[$k]["value"] = false;
                $dd[$k]["type"] = "string";
            }
            else if($k == "announce-list") {
                $dd[$k]["value"] = false;
                $dd[$k]["type"] = "list";
            }
            else
                bark("Dictionary is missing key(s).. no trackers in torrent?");
        }

        if (isset($t)) {
            if ($dd[$k]["type"] != $t) {
                bark("Invalid entry in dictionary");
            }
            
            $ret[] = $dd[$k]["value"];
        } else {
            $ret[] = $dd[$k];
        }

    }
    return $ret;
}

function dict_get($d, $k, $t)
{
    if ($d["type"] != "dictionary") {
        bark("Invalid data in torrent: not a dictionary!");
    }

    $dd = $d["value"];
    if (!isset($dd[$k])) {
        return;
    }

    $v = $dd[$k];
    if ($v["type"] != $t) {
        bark("Invalid dictionary entry type");
    }

    return $v["value"];
}

dbconn(0);

loggedinorreturn();

foreach (explode(":", "descr:type:name") as $v) {
    if (!isset($_POST[$v])) {
        bark("Missing form data!");
    }
}

if (!isset($_FILES["file"])) {
    bark("Missing form data!");
}

$f = $_FILES["file"];
$fname = unesc($f["name"]);
if (empty($fname)) {
    bark("Please supply a filename!");
}

if (!validfilename($f["name"])) {
    bark("Invalid filename! $fname");
}

if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches)) {
    bark("Invalid filename (not a .torrent)");
}

$shortfname = $torrent = $matches[1];
if (!empty($_POST["name"])) {
    $torrent = unesc($_POST["name"]);
}

$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname)) {
    bark("eek!");
}

if (!filesize($tmpname)) {
    bark("Empty file!");
}

$dict = bdec_file($tmpname, $max_torrent_size);

if (!isset($dict)) {
    bark("What the hell did you upload?! This is not a bencoded file!");
}

list($ann, $annlist, $info) = dict_check($dict, "announce(string):announce-list(list):info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

if($ann == false) {
    $old = $dict["value"];
    $new = array(
        "announce" => array(
            "type" => "string",
            "value" => $announce_urls[5]
        )
    );
    $dict["value"] = $new + $old;
}
if($annlist == false) {
    $ann = $dict["value"]["announce"];
    unset($dict["value"]["announce"]);
    $oldann = array(
        "announce" => $ann
    );
    $old = $dict["value"];
    $new = array(
        "announce-list" => array(
            "type" => "list",
            "value" => array(
                0 => array(
                    "type" => "list",
                    "value" => array(
                        0 => array(
                            "type" => "string",
                            "value" => $announce_urls[5]
                        )
                    )
                )
            )
        )
    );
    $dict["value"] = $oldann + $new + $old;
}

//check all announce urls, set primary announce url to tf ann url
//default is $announce_urls[5]

//collect all trackers
$alltrackers = array();

array_push($alltrackers, $dict["value"]["announce"]["value"]);
for($i = 0; $i < sizeof($dict["value"]["announce-list"]["value"]); ++$i) {
    for($j = 0; $j < sizeof($dict["value"]["announce-list"]["value"][$i]["value"]); ++$j) {
        array_push($alltrackers, $dict["value"]["announce-list"]["value"][$i]["value"][$j]["value"]);
    } 
}

//remove non-i2p trackers
for($i = 0; $i < sizeof($alltrackers); ++$i) {
    if(!preg_match("^http:\/\/(w{3}\.)?(([a-zA-Z0-9-]*\.){1,2})?[a-zA-Z0-9-]*\.(i2p|I2P)\/?$", $alltrackers[$i])) {
        unset($alltrackers[$i]);
        $alltrackers = array_values($alltrackers);
    }
}

//remove duplicates
$alltrackers = array_unique($alltrackers);
for($i = 0; $i < sizeof($alltrackers); ++$i) {
    if(in_array($alltrackers[$i], $announce_urls)) {
        unset($alltrackers[$i]);
        $alltrackers = array_values($alltrackers);
    }
}

//install primary tracker
$dict["value"]["announce"]["value"] = $announce_urls[5];

//add tracker to announce-list
array_unshift($alltrackers, $announce_urls[5]);

//save all trackers to announce-list
$dict["value"]["announce-list"]["value"] = array();
for($i = 0; $i < sizeof($alltrackers); ++$i) {
    $newan = array(
        "type" => "string",
        "value" => $alltrackers[$i]
    );
    $dict["value"]["announce-list"]["value"][] = array(); 
    $dict["value"]["announce-list"]["value"][$i][] = array();
    $dict["value"]["announce-list"]["value"][$i]["value"] = array();
    $dict["value"]["announce-list"]["value"][$i]["type"] = "list";
    $dict["value"]["announce-list"]["value"][$i]["value"][] = $newan;
}
$dict["value"]["announce-list"]["type"] = "list";

//save changes to file
$newdict = benc($dict);
$fp = fopen($tmpname, "w");
if (!$fp) {
    bark("Problem rewriting torrent");
}
fputs($fp, $newdict);
fclose($fp);

//re-read overrided file
$dict = bdec_file($tmpname, $max_torrent_size);

list($ann, $annlist, $info) = dict_check($dict, "announce(string):announce-list(list):info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

if (strlen($pieces) % 20 != 0) {
    bark("Invalid pieces detected in torrent!");
}

$filelist = array();
$totallen = dict_get($info, "length", "integer");
if (isset($totallen)) {
    $filelist[] = array($dname, $totallen);
    $type = "single";
} else {
    $flist = dict_get($info, "files", "list");
    if (!isset($flist)) {
        bark("Dictionary is missing both length and files!");
    }

    if (!count($flist)) {
        bark("Torrent contains no files!");
    }

    $totallen = 0;
    foreach ($flist as $fn) {
        list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
        $totallen += $ll;
        $ffa = array();
        foreach ($ff as $ffe) {
            if ($ffe["type"] != "string") {
                bark("Filename error!");
            }

            $ffa[] = $ffe["value"];
        }
        if (!count($ffa)) {
            bark("Filename error!");
        }

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
    if (mysqli_errno($GLOBALS["___mysqli_ston"]) === 1062) {
        bark("Torrent already uploaded!");
    }

    bark("mysql puked: " . mysqli_error($GLOBALS["___mysqli_ston"]));
}
$id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);

@mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM files WHERE torrent = $id");

$insql = "INSERT INTO files (torrent, filename, size) VALUES ";

for($i = 0; $i < sizeof($filelist); ++$i)
{
    if($i + 1 == sizeof($filelist))
    	$insql .= "($id, " . sqlesc($filelist[$i][0]) . "," . $filelist[$i][1] . ");";
    else
    	$insql .= "($id, " . sqlesc($filelist[$i][0]) . "," . $filelist[$i][1] . "), ";
}

@mysqli_query($GLOBALS["___mysqli_ston"], $insql);

move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");

header("Refresh: 2; url=details.php?id=$id&uploaded=1");

exit();
