<?php
require_once 'titler_class.php';
class page_header_headers{
	const mHeaders = array(
		"Content-Security-Policy"=>"default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'none'",
		"Referrer-Policy"=>"same-origin",
		"X-Content-Type-Options"=>"1;mode=block",
		"Set-Cookie"=>" HttpOnly; SameSite=Strict",
		"X-Frame-Options"=>"Deny"
	);
	function sendDefaultHeaders(){
		foreach(self::mHeaders as $header_name=>$header_value){
			header($header_name.": ".$header_value.";");
			//print(":::: |".$header_name.": ".$header_value.";");
		}
	}
	function __construct($stdhead=""){
		$this->sendDefaultHeaders();
		$titler = new titler();
		$titler->import("head");
		dbconn();
		stdhead($stdhead);
	}
	function __destruct(){
		stdfoot();
	}
}
