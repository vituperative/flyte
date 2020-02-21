<?php
use Rych\Bencode\Bencode;
class Tracker{
	const defreq = "info_hash:peer_id:ip:port:uploaded:downloaded:left";
	const defSizeHash=20;
	const defSizePeerID=20;
	const defRSIZE=50;

	function get_error($msg){
		$data = array(
			"string"=>"failure reason",
			"string"=>$msg
		);
		return Bencode::encode($data); // return static info from static method...
	}

	function prepare_peer($ip, $peer_id, $port, $urldecode=False){
		if($urldecode){
			$ip=urldecode($ip);
			$peer_id=urldecode($peer_id);
		}
		return array("ip"=>$ip, "peer_id"=>$peer_id, "port"=>$port);
	}

	function set_peers($peers,$announcer_interval=25){
		$data = array(
			"integer"=> $announcer_interval,
			"peers"=>array(
				$peers
			)
		);
		return Bencode::encode($data);
	}

	function announcer($info){ // there is array
		$opt = false;
		$data=array();
		foreach (explode(":", self::defreq) as $requement) {
			if (!isset($info[$requement])) {
				return get_error("Missing key");
			}
			//$data[$requement] = $info[$requement];
		}
		if( $data['info_hash'] != self::defSizeHash || $data['info_hash'] != self::defSizePeerID  )
			return get_error("invalid size of hash/peer id;");

		$port = intval($data['port']);
		$downloaded = bigintval($data['downloaded']);
		$uploaded = bigintval($data['uploaded']);
		$left = bigintval($data['left']);
		$rsize = self::defRSIZE;
		$event ="";
		$seeder = ($left == 0) ? "yes" : "no";

		foreach(array("num want", "numwant", "num_want") as $numwant) {
			if(isset( $data[$numwant] ) ){
				if(!is_numeric($rsize) ) return get_error("num want is not number");
				$rsize=$data[$numwant];
				break;
			}
		}
		if (!$port || !is_numeric($port) || $port > 0xffff) return get_error("invalid port");
		if (isset($data['event'])) $event = $data['event'];
		if($event == "completed"){
			//completed code;
		}else if ( $event == "stopped" ) {
			//stope code.
		}
		//get peer/add peer set peers/ add peers;
	}
};
