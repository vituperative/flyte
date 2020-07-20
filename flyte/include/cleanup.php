<?php

require_once("bittorrent.inc.php");

function docleanup() {
	global $torrent_dir, $signup_timeout, $max_dead_torrent_time;

	set_time_limit(0);
	ignore_user_abort(1);

	do {
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id FROM torrents");
		$ar = array();
		while ($row = mysqli_fetch_array($res)) {
			$id = $row[0];
			$ar[$id] = 1;
		}

		if (!count($ar))
			break;

		$dp = @opendir($torrent_dir);
		if (!$dp)
			break;

		$ar2 = array();
		while (($file = readdir($dp)) !== false) {
			if (!preg_match('/^(\d+)\.torrent$/', $file, $m))
				continue;
			$id = $m[1];
			$ar2[$id] = 1;
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$ff = $torrent_dir . "/$file";
			unlink($ff);
		}
		closedir($dp);

		if (!count($ar2))
			break;

		$delids = array();
		foreach (array_keys($ar) as $k) {
			if (isset($ar2[$k]) && $ar2[$k])
				continue;
			$delids[] = $k;
			unset($ar[$k]);
		}
		if (count($delids))
			mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM torrents WHERE id IN (" . join(",", $delids) . ")");

		$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrent FROM peers GROUP BY torrent");
		$delids = array();
		while ($row = mysqli_fetch_array($res)) {
			$id = $row[0];
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$delids[] = $id;
		}
		if (count($delids))
			mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM peers WHERE torrent IN (" . join(",", $delids) . ")");
		
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrent FROM comments GROUP BY torrent");
		$delids = array();
		while ($row = mysqli_fetch_array($res)) {
			$id = $row[0];
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$delids[] = $id;
		}
		if (count($delids))
			mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM comments WHERE torrent IN (" . join(",", $delids) . ")");

		$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrent FROM files GROUP BY torrent");
		$delids = array();
		while ($row = mysqli_fetch_array($res)) {
			$id = $row[0];
			if (@$ar[$id])
				continue;
			$delids[] = $id;
		}
		if (count($delids))
			mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM files WHERE torrent IN (" . join(",", $delids) . ")");
	} while (0);

	$deadtime = deadtime();
	mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM peers WHERE last_action < FROM_UNIXTIME($deadtime)");

	$deadtime -= $max_dead_torrent_time;
	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET visible='no' WHERE visible='yes' AND last_action < FROM_UNIXTIME($deadtime)");

	$deadtime = time() - $signup_timeout;
	mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM users WHERE status = 'pending' AND added < FROM_UNIXTIME($deadtime) AND last_login < FROM_UNIXTIME($deadtime) AND last_access < FROM_UNIXTIME($deadtime)");

	$torrents = array();
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
	while ($row = mysqli_fetch_assoc($res)) {
		if ($row["seeder"] == "yes")
			$key = "seeders";
		else
			$key = "leechers";
		$torrents[$row["torrent"]][$key] = $row["c"];
	}

	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent");
	while ($row = mysqli_fetch_assoc($res)) {
		$torrents[$row["torrent"]]["comments"] = $row["c"];
	}

	$fields = explode(":", "comments:leechers:seeders");
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, seeders, leechers, comments FROM torrents");
	if (mysqli_num_rows($res)) while ($row = mysqli_fetch_assoc($res)) {
		$id = $row["id"];
		$torr = @$torrents[$id];
		foreach ($fields as $field) {
			if (!isset($torr[$field]))
				$torr[$field] = 0;
		}
		$update = array();
		foreach ($fields as $field) {
			if ($torr[$field] != $row[$field])
				$update[] = "$field = " . $torr[$field];
		}
		if (count($update))
			mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET " . implode(",", $update) . " WHERE id = $id");
	}

}

?>
