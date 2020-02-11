<?php
require_once 'include/bittorrent.inc.php';


class page_class{

	function __construct($page=""){
		dbconn();
		stdhead($page);
		sendDefaultHeaders();
	}
	function __destruct(){
		stdfoot();
	}
}

?>

