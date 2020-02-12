<?php
if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
}

require_once "include/bittorrent.inc.php";
require_once "include/benc.php";

//require_once("include/announcer_class.php"); //
//$announcer = new Announcer();
//$announcer->announce($_GET);
function err($msg)
{
    benc_resp(array("failure reason" => array("type" => "string", "value" => $msg)));
    exit();
}

function bigintval($value)
{
    $value = trim($value);
    if (ctype_digit($value)) {
        return $value;
    }
    $value = preg_replace("/[^0-9](.*)$/", '', $value);
    if (ctype_digit($value)) {
        return $value;
    }
    return 0;
}

// I2P: ip required
$req = "info_hash:peer_id:ip:port:uploaded:downloaded:left:!event";
foreach (explode(":", $req) as $x) {
    if ($x[0] == "!") {
        $x = substr($x, 1);
        $opt = 1;
    } else {
        $opt = 0;
    }

    if (!isset($_GET[$x])) {
        if (!$opt) {
            err("missing key");
        }

        continue;
    }
    $GLOBALS[$x] = unesc($_GET[$x]);
}

foreach (array("info_hash", "peer_id") as $x) {
    if (strlen($GLOBALS[$x]) != 20) {
        err("invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");
    }

}

// I2P: dont check ip
//if (empty($ip) || !preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s', $ip))
//    $ip = $_SERVER["REMOTE_ADDR"];

$port = intval($port);
$downloaded = bigintval($downloaded);
$uploaded = bigintval($uploaded);
$left = bigintval($left);

$rsize = 50;
foreach (array("num want", "numwant", "num_want") as $k) {
    if (isset($_GET[$k])) {
        $rsize = intval($_GET[$k]);
        break;
    }
}

if (!$port || $port > 0xffff) {
    err("invalid port");
}

if (!isset($event)) {
    $event = "";
}

$seeder = ($left == 0) ? "yes" : "no";

dbconn(0);

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, banned, seeders + leechers AS numpeers FROM torrents WHERE " . hash_where("info_hash", $info_hash));

$torrent = mysqli_fetch_assoc($res);
if (!$torrent) {
    err("torrent not registered with this tracker");
}

$torrentid = $torrent["id"];

$fields = "seeder, peer_id, ip, port";

$numpeers = $torrent["numpeers"];
$limit = "";
if ($numpeers > $rsize) {
    $limit = "ORDER BY RAND() LIMIT $rsize";
}

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT $fields FROM peers WHERE torrent = $torrentid AND (1 OR connectable = 'yes') $limit");

$resp = "d" . benc_str("interval") . "i" . $announce_interval . "e" . benc_str("peers") . "l";
unset($self);
while ($row = mysqli_fetch_assoc($res)) {
    $row["peer_id"] = hash_pad($row["peer_id"]);

    if ($row["peer_id"] === $peer_id) {
        $self = $row;
        continue;
    }

    $resp .= "d" .
    benc_str("ip") . benc_str($row["ip"]) .
    benc_str("peer id") . benc_str($row["peer_id"]) .
    benc_str("port") . "i" . $row["port"] . "e" .
        "e";
}

$resp .= "ee";

$selfwhere = "torrent = $torrentid AND " . hash_where("peer_id", $peer_id);

if (!isset($self)) {
    $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT $fields FROM peers WHERE $selfwhere");
    $row = mysqli_fetch_assoc($res);
    if ($row) {
        $self = $row;
    }

}

$updateset = array();

if ($event == "stopped") {
    if (isset($self)) {
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM peers WHERE $selfwhere");
        if (mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
            if ($self["seeder"] == "yes") {
                $updateset[] = "seeders = seeders - 1";
            } else {
                $updateset[] = "leechers = leechers - 1";
            }

        }
    }
} else {
    if ($event == "completed") {
        $updateset[] = "times_completed = times_completed + 1";
    }

    if (isset($self)) {
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE peers SET ip = " . sqlesc($ip) . ", port = $port, uploaded = $uploaded, downloaded = $downloaded, to_go = $left, last_action = NOW(), seeder = '$seeder' WHERE $selfwhere");
        if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) && $self["seeder"] != $seeder) {
            if ($seeder == "yes") {
                $updateset[] = "seeders = seeders + 1";
                $updateset[] = "leechers = leechers - 1";
            } else {
                $updateset[] = "seeders = seeders - 1";
                $updateset[] = "leechers = leechers + 1";
            }
        }
    } else {
// anonymity breaker, commented out for security, I2P nodes are always connectable
        //        $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
        //        if (!$sockres)
        //            $connectable = "no";
        //        else {
        $connectable = "yes";
//            @fclose($sockres);
        //        }
        $ret = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO peers (connectable, torrent, peer_id, ip, port, uploaded, downloaded, to_go, started, last_action, seeder) VALUES ('$connectable', $torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", $port, $uploaded, $downloaded, $left, NOW(), NOW(), '$seeder')");
        if ($ret) {
            if ($seeder == "yes") {
                $updateset[] = "seeders = seeders + 1";
            } else {
                $updateset[] = "leechers = leechers + 1";
            }

        }
    }
}

if ($seeder == "yes") {
    if ($torrent["banned"] != "yes") {
        $updateset[] = "visible = 'yes'";
    }

    $updateset[] = "last_action = NOW()";
}

if (count($updateset)) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $torrentid");
}

benc_resp_raw($resp);
