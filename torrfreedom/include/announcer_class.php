
<?php

if (ob_get_level() == 0) ob_start();
require_once("include/bittorrent.inc.php");
require_once("include/benc.php");
dbconn();

$r = "d" . benc_str("files") . "d";// $r волшебная константа для files в benc_str массив типа бля ee в конце

$fields = "info_hash, times_completed, seeders, leechers"; // так поля

class Announcer {
  protected static $sDB=null;
   
   public function __construct( $db=false ){
	   if ($db !== false) $this->sDB=$db;
	   else $this->sDB=$GLOBALS["___mysqli_ston"];
   }

   function err($msg) {
        	benc_resp(array("failure reason" => array("type" => "string", "value" => $msg)));
        	//exit();
   }


	
   const defreq = "info_hash:peer_id:ip:port:uploaded:downloaded:left:!event";      
   public function announce($ask){
	$opt=0;
	foreach (explode(":", self::defreq) as $element) {
        	if ($element[0] == "!") {
                	$element = substr($element, 1);
                	$opt = 1; // 
        	}
        	else
                	$opt = 0;
        	if (!isset($ask[$element])) {
                	if (!$opt)
                        	$this->err("missing key");
                	continue;
        	}
        	$GLOBALS[$element] = unesc($ask[$element]);
	}
	foreach (array("info_hash","peer_id") as $x) 
         if (strlen($GLOBALS[$x]) != 20)
                err("invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");
		


   } 
 public function bigintval($value) {
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


	
};

//if (!isset($_GET["info_hash"]))
//        $query = "SELECT $fields FROM torrents ORDER BY info_hash";
//else
//        $query = "SELECT $fields FROM torrents WHERE " . hash_where("info_hash", $_GET["info_hash"]);
//
//$res = mysqli_query($GLOBALS["___mysqli_ston"], $query);
//
//while ($row = mysqli_fetch_assoc($res)) {
//        $r .= "20:" . hash_pad($row["info_hash"]) . "d" .
//                benc_str("complete") . "i" . $row["seeders"] . "e" .
//                benc_str("downloaded") . "i" . $row["times_completed"] . "e" .
//                benc_str("incomplete") . "i" . $row["leechers"] . "e" .
//                "e";
//}
//
//$r .= "ee";
//
//print($r);
//
