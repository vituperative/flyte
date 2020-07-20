<?php
include_once("../methods_.php");
include_once mm::require_class("comments");
class torrents extends comments{

	const torrentfields=array(
		//is much...
	);
//		"changeValueOfTorrentByID"=>"UPDATE torrents SET %s='%s' WHERE '%s'='%s'" //Update torrents set what is where a=b 
	function changeValueOfTorrentByID($what,$value,$where_a,$where_b){
		return $this->doSQL( sql::sqls['changeValueOfTorrentByID'], $what, $value, $where_a, $where_b );
	}
	function setBanTorrentByID($is_banned, $id){
		if( $is_banned === True ) $is_banned="yes";
		elseif( $is_banned === False ) $is_banned="no";
		
		$this->changeValueOfTorrentByID("banned",  "$is_banned", "id", "$id");
	}
	function setVissbleTorrentByID($is_vissible, $id){
		if( $is_vissible === True ) $is_vissible="yes";
		elseif( $is_vissible === False ) $is_vissible="no";

		$this->changeValueOfTorrentByID("visible",  "$is_vissible", "id", "$id");
	}
	function getTorrentByID($id){
		//$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM torrents WHERE id = $id");
		$ret = $this->doSQL( sql::sqls['getTorrentByID'], $id );
		return mysqli_fetch_array($ret);
	}
	//"delTorrentByX"=>"DELETE FROM torrents WHERE %s= '%s'"
	function delTorrentByX($x,$value){
		$ret1 = $this->doSQL( sql::sqls['delTorrentByX'], $x, $v );
	}

	function delTorrentsByUserID($id, $withComments=True){
		$ret0= True;
		$ret1 = $this->doSQL( sql::sqls['delTorrentsByUserID'], $id );
		if($withComments) $ret0 = $this->delCommentIsWhere("user", $id);
		if ( !($ret1 && $ret0) ) return $this->getLastSQLError() ;
		return True;
	}

	function delTorrentByID($id,$withComments=True){ //maybeto delTorrentByX? or no...
		$ret0 = True;

		$ret1 = $this->doSQL( sql::sqls['delTorrentByID'], $id );
		if($withComments) $ret0 = $this->delCommentIsWhere("torrent", $id);

		if ( !($ret1 && $ret0) ) return $this->getLastSQLError() ;
		return True;
	}
	function countTorrents(){
		return $this->getCountOfTB("torrents");
	}
	function getAllTorrents($offset=0,$limit=60){
		return $this->doSQL( sql::sqls['getAllTorrents'], $limit, $offset );
	}
	function getTorrentsByUserID($id, $offset=0,$limit=60){
		return $this->doSQL( sql::sqls['getTorrentsByUserID'], $id, $limit, $offset );
	}
	function getTorrentsByUserNick($nick, $offset=0,$limit=60){

		$user=$this->getUserByName($nick);
		//var_dump($user);
		return $this->getTorrentsByUserID($user['id'], $offset, $limit);
	}
	function countOfTorrentsByUserID($id){
		return $this->getCountOfTBWhere("torrents","owner",$id);
	}
	function countOfTorrentsByUserNick($id){
		$user=$this->getUserByName($id);
		return $this->countOfTorrentsByUserID($user['id']);
	}

}

?>
