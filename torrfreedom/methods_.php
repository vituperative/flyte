<?php
class mm{//magic methods

	public static function getPathToTF(){
		$dirs=array(
			"a","admin","announce","include","install","pic","s","scrape"
		);
		$pathToTF="";
		foreach(explode("/",dirname($_SERVER['PHP_SELF'])) as $dir){
				if( in_array($dir, $dirs) ){
					 $pathToTF.="/";
					 break;
				}
				$pathToTF.="/".$dir;
			}
			//print("return: ".$_SERVER['DOCUMENT_ROOT'].$pathToTF);
			return $_SERVER['DOCUMENT_ROOT'].$pathToTF;
	}
	public static function require_file($fullpathtofile){
		//print("include: ".self::getPathToTF()."$fullpathtofile"."<br/>");
		return  self::getPathToTF()."$fullpathtofile";		
	}
	public static function require_class($name){
		return self::require_file("/include/classes/$name.class.php");
	}

}

?>
