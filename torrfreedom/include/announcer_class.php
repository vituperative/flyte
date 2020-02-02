
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
	  "getTorrentByID_selfwhere" => "torrent = %s AND %s",
	  //SELECT $fields FROM peers WHERE $selfwhere
	  "selectWW" => "select %s WHERE %s",
	  //"DELETE FROM peers WHERE $this->selfwhere"
	  "deleteWW" => "DELETE FROM %s WHERE %s",
	  "updateWW"=> "UPDATE %s SET %s = %s"
  );//TODO: fix shitcode to readable, another style and did full support of that map in code

  protected static $sDB=null;
   
   public function __construct( $db=false ){
	   if ($db !== false) self::$sDB=$db;
	   else self::$sDB=$GLOBALS["___mysqli_ston"];
	   dbconn(0); // TODO: another DB support full;
   }

   protected function err($msg) {
        	benc_resp(array("failure reason" => array("type" => "string", "value" => $msg)));
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
	  $res = mysqli_query(self::$sDB, $this->sql_templates['getTorrentByID'] . hash_where("info_hash", $ihash));

	$torrent = mysqli_fetch_assoc($res);
	if (!$torrent){
		$this->err("torrent not registered with this tracker");
		return false;
	}

	return $torrent;
   }
   protected function getPeersByTorrentID($torrent, $fields="seeder, peer_id, ip, port"){
// TODO: all $res/$selfwhere and like that to const/map of sql queries
	   $limit = "";//shitcode... TODO: del;
        if ($torrent["numpeers"] > $this->rsize)
		$limit = "ORDER BY RAND() LIMIT $this->rsize";
	$torrentid=$torrent['id'];
	$qRAW=sprintf($this->sql_templates['getTorrentByID'], $fields, $torrentid, $limit);

	$res = mysqli_query(self::$sDB, $qRAW);

	$resp = "d" . benc_str("interval") . "i" . $announce_interval . "e" . benc_str("peers") . "l";
	unset($this->self);
	while ($row = mysqli_fetch_assoc($res)) {
         $row["peer_id"] = hash_pad($row["peer_id"]);

         if ($row["peer_id"] === $peer_id) {
                $this->self = $row;//???!
                continue;
         } // WTF?!

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

        	$res = mysqli_query(self::$sDB, sprintf($this->sql_templates['selectWW'], $fields, $selfwhere));
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
                      mysqli_query(self::$sDB, sprintf($this->sql_templates['deleteWW'], "peers", $this->selfwhere) );
                      if (mysqli_affected_rows(self::$sDB)) {
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
	      if (isset($self)) {//$ip from $GLOBALS TODO: maybe fuck it GLOBALS?!
	   	      $q=sprintf($this->sql_templates['updateWW'], "peers", "ip", sqlesc($this->ip) . ", port = $this->port, uploaded  = $this->uploaded, downloaded  = $this->downloaded, to_go  = $this->left, last_action = NOW(), seeder = '$this->seeder' WHERE $this->selfwhere");
                      mysqli_query(self::$sDB, $q);
                      if (mysqli_affected_rows(self::$sDB) && $self["seeder"] != $seeder) {
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
      //"deleteWW" => "DELETE FROM %s WHERE %s",
			      $ret = mysqli_query(self::$sDB, 
				      $this->sql_templates['deleteWW'], "peers", "peer_id='".sqlesc($this->peer_id)."' AND torrent='".$torrentid."'");
			      if(!$ret){
					$this->err("sql trouble in update peer");
					exit();
			      }
			      $ret = 
			      mysqli_query(
				      self::$sDB, 
				      "INSERT INTO peers (connectable, torrent, peer_id, ip, port, uploaded, downloaded, to_go, started, last_action, seeder) VALUES 
('$connectable', $torrentid, " . sqlesc($this->peer_id) . ", " . sqlesc($this->ip) . ", $this->port, $this->uploaded, $this->downloaded, $this->left, NOW(), NOW(), '$this->seeder')"
			      );
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

      if (count($updateset))
	      mysqli_query(self::$sDB, "UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $torrentid");


  
   }
   const defreq = "info_hash:peer_id:ip:port:uploaded:downloaded:left:!event";      
   public function announce($ask){
	//to need to recheck that 10 times as minimum
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
	$this->rsize = $this->getRSize($ask);

	if ( !$this->checkPort($port) ){
		$this->err("invalid port");
		exit();
	}

	if (!isset($event)) $this->event="";
	else $this->event=$event;
	$this->seeder = ($this->left == 0) ? "yes" : "no";
	dbconn(0);
	//$this->info_hash=$info_hash;

	$this->constructAnswer();

	$torrent = $this->getTorrentByID($this->info_hash);
	if($torrent === false) return false;


	$resp=$this->getPeersByTorrentID($torrent);
	$this->checkEvent($this->event, $torrent['id']);

	benc_resp_raw($resp);


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

   $this->port = mysqli_real_escape_string(self::$sDB, intval($port) );
   $this->downloaded = mysqli_real_escape_string(self::$sDB, $this->bigintval($downloaded) );
   $this->uploaded = mysqli_real_escape_string(self::$sDB, $this->bigintval($uploaded) );
   $this->left = mysqli_real_escape_string(self::$sDB, $this->bigintval($left) );
   $this->ip = mysqli_real_escape_string(self::$sDB, $ip);
   $this->peer_id= mysqli_real_escape_string(self::$sDB, $peer_id);
   $this->info_hash=mysqli_real_escape_string(self::$sDB, $info_hash);
 }



	
};
?>
