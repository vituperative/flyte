<?php
require_once "include/benc.php";
require_once "include/bittorrent.inc.php";

function bark($msg)
{
    genbark($msg, "Check failed!");
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

$files = scandir($torrent_dir);

foreach($files as $file) {
    if(is_dir($file))
        continue;

    if (!filesize($torrent_dir . "/" . $file)) {
        bark("Empty file!");
    }
    
    //fix torrent files
    $fp = fopen($torrent_dir . "/" . $file, "rb");
    if (!$fp)
        return;
    $e = fread($fp, $max_torrent_size);
    fclose($fp);
    if(strpos($e, "d8:announce13:announce-list") !== false || strpos($e, "d8:announce4:infod") !== false) {
        if(strpos($e, "d8:announce13:announce-list") !== false) {
    	    $e = substr($e, 27);
    	    $e = "d13:announce-list" . $e;
    	}
        if(strpos($e, "d8:announce4:infod") !== false) {
    	    $e = substr($e, 18);
    	    $e = "d4:infod" . $e;
    	}
        $fp = fopen($torrent_dir . "/" . $file, "w");
        fputs($fp, $e);
        fclose($fp);
    }

    $dict = bdec_file($torrent_dir . "/" . $file, $max_torrent_size);

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
    for($i = 0; $i < sizeof($alltrackers);) {
        if(!preg_match("^http:\/\/(w{3}\.)?(([a-zA-Z0-9-]*\.){1,2})?[a-zA-Z0-9-]*\.(i2p|I2P)\/?$", $alltrackers[$i])) {
            unset($alltrackers[$i]);
            $alltrackers = array_values($alltrackers);
        }
        else
    	  $i++;
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
    $fp = fopen($torrent_dir . "/" . $file, "w");
    if (!$fp) {
        bark("Problem rewriting torrent");
    }
    fputs($fp, $newdict);
    fclose($fp);
    echo "File " . $torrent_dir . "/" . $file . " fixed!<br />";
}
?>
