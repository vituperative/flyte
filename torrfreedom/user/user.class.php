<?php
if(file_exists("../admin/admin_class.php")) require_once("../admin/admin_class.php");
else require_once("admin/admin_class.php");

class user extends users{
	function isOwnerOfTorrent($id){
		global $CURUSER;
		$torrent=$this->getTorrentByID($id);
		if ( isset($CURUSER) && ($CURUSER["id"] == $torrent["owner"]) ) return true;
		return False;
	}
	function delTorrentIfIsOwnerByID($id){
		if( $this->isOwnerOfTorrent($id) ) $this->delTorrentByID($id);
	}
};
