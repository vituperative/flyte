
<?php

if (ob_get_level() == 0) ob_start();
require_once("include/bittorrent.inc.php");
require_once("include/benc.php");
dbconn();



class Announcer {
  protected $sql_templates = array(
	  "getTorrentByID" => "SELECT id, banned, seeders + leechers AS numpeers FROM torrents WHERE ",
	  "getPeersByTorrentID" => "SELECT %s FROM peers WHERE torrent = '%d' AND (1 OR connectable = 'yes') %s", // fields, torrentID, limit
//        $selfwhere = "torrent = $torrentid AND " . hash_where("peer_id", $peer_id);
	  "getPeersByTorrentID_selfwhere" => "torrent = %s AND %s",
	  //SELECT $fields FROM peers WHERE $selfwhere
	  "selectWW" => "select %s WHERE %s",
	  //"DELETE FROM peers WHERE $this->selfwhere"
	  "deleteWW" => "DELETE FROM %s WHERE %s"
  );

  protected static $sDB=null;
   
   public function __construct( $db=false ){
	   if ($db !== false) $this->sDB=$db;
	   else $this->sDB=$GLOBALS["___mysqli_ston"];
	   dbconn(0); // TODO: another DB support full;
   }

   protected function err($msg) {
        	$this->benc_resp(array("failure reason" => array("type" => "string", "value" => $msg)));
        	//exit();
   }


   public function getRSize($ask, $default=50){
     foreach(array("num want", "numwant", "num_want") as $k) {
        if (isset($ask[$k])) {
                return intval($ask[$k]);
                break;
	}
	return $default;

     }
   }
   protected function checkPort($port){
	if (!$port || $port > 0xffff) return false;
	return true;
   }
   protected function getTorrentByID($ihash){
	$res = mysqli_query($GLOBALS["___mysqli_ston"], $this->sql_templates['getTorrentByID'] . hash_where("info_hash", $ihash));

	$torrent = mysqli_fetch_assoc($res);
	if (!$torrent){
		$this->err("torrent not registered with this tracker");
		return false;
	}

	return $torrent;
   }
   protected function getPeersByTorrentID($torrent, $fields="seeder, peer_id, ip, port"){// TODO: все $res/selfwhere и ттд в константы запросов блять в $torQueryGETTORRENT; и ттд
	   $limit = "";//поправить эт нах
        if ($torrent["numpeers"] > $this->rsize)
		$limit = "ORDER BY RAND() LIMIT $this->rsize";
	$torrentid=$torrent['id'];
	$qRAW=sprintf($this->sql_templates['getTorrentByID'], $fields, $torrentid, $limit);
	$res = mysqli_query($GLOBALS["___mysqli_ston"], $qRAW);

	$resp = "d" . benc_str("interval") . "i" . $announce_interval . "e" . benc_str("peers") . "l";
	unset($this->self);
	while ($row = mysqli_fetch_assoc($res)) {
         $row["peer_id"] = hash_pad($row["peer_id"]);

         if ($row["peer_id"] === $peer_id) {
                $this->self = $row;//???!
                continue;
         } // это чосукаблятьяебалнахуйнепонимаюблятьааааа ладно перепроверить это 10 раз мб надо таки

         $resp .= "d" .
                 benc_str("ip") . benc_str($row["ip"]) .
                 benc_str("peer id") . benc_str($row["peer_id"]) .
                 benc_str("port") . "i" . $row["port"] . "e" .
                 "e";
	}//while end
	$resp .= "ee";
	//"getPeersByTorrentID_selfwhere" => "torrent = %s AND %s"
	$this->selfwhere=sprintf($this->sql_templates['getTorrentByID_selfwhere'], $torrentid, hash_where("peer_id", $peer_id) );

	if (!isset($this->self)) {

        	$res = mysqli_query($GLOBALS["___mysqli_ston"], sprintf($this->sql_templates['selectWW'], $fields, $selfwhere));
        	$row = mysqli_fetch_assoc($res);
        	if ($row)
                	$this->self = $row;
		}

	return $resp;
   }



   protected function checkEvent($event, $torrentid){
	$updateset = array();

      	if ($event == "stopped") {
		if (isset($self)) {
		      //deleteWW
                      mysqli_query($GLOBALS["___mysqli_ston"], sprintf($this->sql_templates['deleteWW'], "peers", $this->selfwhere) );
                      if (mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
                              if ($self["seeder"] == "yes")
                                      array_push($updateset, "seeders = seeders - 1");
                              else
                                      array_push($updateset, "leechers = leechers - 1");
                      }
              }
      }else {
              if ($event == "completed")
                      array_push($updateset, "times_completed = times_completed + 1");
	      //   $this->port = intval($port);
	     //   $this->downloaded = $this->bigintval($downloaded);
  	    // $this->uploaded = $this->bigintval($uploaded);
   	   //$this->left = $this->bigintval($left);

	      //
              if (isset($self)) {
                      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE peers SET ip = " . sqlesc($ip) . ", port = $this->port, uploaded  = $this->uploaded, downloaded  = $this->downloaded, to_go  = $this->left, last_action = NOW(), seeder = '$this->seeder' WHERE $this->selfwhere");
                      if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) && $self["seeder"] != $seeder) {
                              if ($seeder == "yes") {
                                      array_push($updateset, "seeders = seeders + 1");
                                      array_push($updateset, "leechers = leechers - 1");
                              }
                              else {
                                      array_push($updateset, "seeders = seeders - 1");
                                      array_push($updateset, "leechers = leechers + 1");
                              }
                      }
              }
              else {
      // anonymity breaker, commented out for security, I2P nodes are always connectable
      //              $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
      //              if (!$sockres)
      //                      $connectable = "no";
      //              else {
                              $connectable = "yes";
      //                      @fclose($sockres);
      //              }
                      $ret = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO peers (connectable, torrent, peer_id, ip, port, uploaded, downloaded, to_go, started, last_action, seeder) VALUES ('$connectable', $torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", $this->port, $this->uploaded, $this->downloaded, $this->left, NOW(), NOW(), '$this->seeder')");//IP всегда пустое что ли у нас?! peer_id из GLOBALS?
                      if ($ret) {
                              if ($seeder == "yes")
                                      array_push($updateset, "seeders = seeders + 1");
                              else
                                      array_push($updateset, "leechers = leechers + 1");
                      }
              }
      }//endELSE
      if ($seeder == "yes") {
       if ($torrent["banned"] != "yes")
                array_push($updateset, "visible = 'yes'");
          array_push($updateset, "last_action = NOW()");
      }

      if (count($updateset))// ЕСЛИ ВОЩЕ ЧОТ НАД ТО ЕБАШИМ ОК ДА ЫЫЫ
	      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $torrentid");

  
   }
   const defreq = "info_hash:peer_id:ip:port:uploaded:downloaded:left:!event";      
   public function announce($ask){
	//Так ещё раз пройтись по всем действиям прежде чем реквестить, пересмотреть не в терминальчике, а сука на FULL HD на acer монитор 2000 какого то года!
	$opt=0;
	$rsize=50;
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
		 $this->err("invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");
	$this->constructAnswer();
	$this->rsize = $this->getRSize();//rsize какой то хуй знает чо какие то цифры там блять хотят

	if (!$this->checkPort){
		$this->err("invalid port");
		exit();
	}

	if (!isset($event)) $this->event="";
	else $this->event=$event;
	$this->seeder = ($this->left == 0) ? "yes" : "no";
	dbconn(0);
	//$this->info_hash=$info_hash;
	$torrent = $this->getTorrentByID($info_hash);// блять потом поправить сука
	if(!torrent) return false;


	$resp=$this->getPeersByTorrentID($torrent);
	$this->checkEvent($this->event, $torrent['id']);

	$this->benc_resp_raw($resp);


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
 protected function constructAnswer(){

   $this->port = intval($port);
   $this->downloaded = $this->bigintval($downloaded);
   $this->uploaded = $this->bigintval($uploaded);
   $this->left = $this->bigintval($left);
 }



	
};
?>
