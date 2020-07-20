<?php
include_once("../methods_.php");
include_once mm::require_class("users");
class user extends users{
		function __construct(){
			sql::__construct();
		}
}
?>
