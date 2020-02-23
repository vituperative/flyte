<?php
include_once("../methods_.php");
include_once mm::require_class("categories");
class peers extends categories{
	function countPeers(){
		return $this->getCountOfTB("peers");
	}	
	function countOfSeeders(){
		return $this->getCountOfTBWhere("peers","seeder","yes");
	}	
	function countOfLeech(){
		return $this->getCountOfTBWhere("peers","seeder","no");
	}	
}

?>
