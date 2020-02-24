<?php
include_once("../methods_.php");
include_once mm::require_class("users");

class admin extends users{
	const DEBUG = FALSE;
	function getServInfo(
		$indicesServer = array(
    		'SERVER_NAME',
    		'SERVER_ADDR',
    		'SERVER_PORT',
    		'SERVER_SIGNATURE',
    		'SERVER_SOFTWARE',
    		'SERVER_PROTOCOL',
		)
	){
		$returns = array();
		foreach ($indicesServer as $info) {
			//print("INFO:".$_SERVER[$info]);
			if (isset($_SERVER[$info])) $returns[$info] = $_SERVER[$info];
		}
		return $returns;
	}

	function __construct($moveIfNotAdmin=True, $page='../index.php'){
		sql::__construct();
		if($moveIfNotAdmin) {
			$is_admin = $this->checkAdmin();
			if(!$is_admin){
				
				if(!self::DEBUG){
					header("Location: ../index.php");
					exit(0);
				}else{
					//..
				}
			}
		}
		stdhead("Admin page");
		//include_once "../include/page_header.inc.php";
	}

	function checkAdmin(){
		global $CURUSER;
		$this->isAdmin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
		return $this->isAdmin;
	}
};
?>
