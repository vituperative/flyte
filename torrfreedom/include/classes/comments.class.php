<?php
include_once("../methods_.php");
include_once mm::require_class("sql");

class comments extends sql{
	const commentfields=array(
		"id", "user", "torrent", "added", "text", "ori_text"
	);
//
	function countComments(){
		return $this->getCountOfTB("comments");
	}

	function delCommentIsWhere($is, $where="id"){
		$allowed=false;
		foreach(self::commentfields as $allow_fields)
		{
			if($where == $allow_fields){
				$allowed=true;
				break;
			}
		}
		if(!$allowed) return false;
		return $this->doSQL( sql::sqls['delCommentsWhereIS'], $where, $is);
	} 
	function delCommentByUserID($id){
		return $this->delCommentIsWhere($id, "user");
	}
}
?>
