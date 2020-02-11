<?php
require_once 'include/bittorrent.inc.php';


class page_class{

	function __construct(){
		dbconn();
		stdhead();
		sendDefaultHeaders();
	}
	function __destruct(){
		stdfoot();
	}
}

?>

