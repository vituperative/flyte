<?php
include_once("../methods_.php");
include_once mm::require_class("torrents");

class categories extends torrents{
	function countCategories(){
		return $this->getCountOfTB("categories");
	}
	function getNameOfCategoryByID($id){
		//getNameOfCategoryByID
		$category=$this->doSQL( sql::sqls['getNameOfCategoryByID'], $id );
		//$category=mysqli_fetch_array($category)['name'];
		return mysqli_fetch_array($category)['name'];
	}
}
?>
