<?php
require_once 'include/titler_class.php';
class page_header_headers{
	const mHeaders = array(
		"Content-Security-Policy"=>"default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'none'",
		"Referrer-Policy"=>"same-origin",
		"X-Content-Type-Options"=>"1;mode=block",
		"Set-Cookie"=>" HttpOnly; SameSite=Strict",
		"X-Frame-Options"=>"Deny"
	);
	function add($name,$value){
		header($name.": ".$value.";");
	}
	function sendDefaultHeaders(){
		foreach(self::$mHeaders as $header_name=>$header_value)
			$this->add($header_name,$header_value);
	}
	function __construct(){
		$this->sendDefaultHeaders();
		$this->titler = new titler();
		$this->titler->import("head.tpl");
	}

}
