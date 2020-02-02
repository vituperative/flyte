<?php

require_once("include/announcer_class.php");

class Scraper extends Announcer{
	public function __construct( $db =false ){
		parent::__construct($db);
	}
	public function doScrape(){
		$r = "d" . benc_str("files") . "d";
		$fields = "info_hash, times_completed, seeders, leechers";
		if (!isset($_GET["info_hash"]))
        		$query = "SELECT $fields FROM torrents ORDER BY info_hash";
		else
			$query = "SELECT $fields FROM torrents WHERE " .
		       	hash_where("info_hash", $_GET["info_hash"]);

		$res = mysqli_query($GLOBALS["___mysqli_ston"], $query);

		while ($row = mysqli_fetch_assoc($res)) {
        		$r .= "20:" . hash_pad($row["info_hash"]) . "d" .
                	benc_str("complete") . "i" . $row["seeders"] . "e" .
                	benc_str("downloaded") . "i" . $row["times_completed"] . "e" .
                	benc_str("incomplete") . "i" . $row["leechers"] . "e" . "e";
		}

		$r .= "ee";
		return $r;
	}
};
