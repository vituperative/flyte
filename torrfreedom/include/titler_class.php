<?php
	//todo add cache, compiling and to .tpl.cache_saltforuser / put in template function raw some <?php getTITLE() instead in method parse maybe
	//or if it is not problem use that
	require_once 'bittorrent.inc.php';
	function getTITLE(){
			$request = $_SERVER["REQUEST_URI"];
			$username=htmlspecialchars($CURUSER["username"]);
       			$page = basename(htmlspecialchars($_SERVER['PHP_SELF']));
        		$page = str_replace("index", "", $page);
        		$page = str_replace("my.php", "$username's account settings", $page);
        		$page = str_replace("mytorrents", "$username's torrents", $page);
        		$page = str_replace("takeprofedit", "update profile", $page);
			if (strpos($request, "install") !== false) $page= "INSTALLER";
        		$pagename = rtrim($page, "php");
        		if ($pagename != ".")
         		   echo (" | ");
      			echo strtoupper(rtrim($pagename, "."));	
			
	}

	class titler{
		function __construct(){
			global $CURUSER, $pic_base_url, $tracker_title, $tracker_url_name, $tracker_path;
			//$this->tracker_title=$tracker_title;
			//$this->pic_base_url=$pic_base_url;
			//$this->tracker_url_name=$tracker_url_name;
			//$this->tracker_path=$tracker_path;
			//$this->CURUSER=$CURUSER;
			$this->mObjects = array(
				"{tracker_title_upper}"=>strtoupper($tracker_title),
				"{username}"=>htmlspecialchars($CURUSER["username"]),
				"{tracker_path}"=>$tracker_path
				//"getTITLE_FUN"=>getTITLE
			);
		}




		function compile($endcode,$cachedir, $pagename){
			$compf=$cachedir."/".$pagename.'.cache';
			$endfile=fopen($compf,"w") or die("cant compile cache file; check permission to dir; ".$file);
			fwrite($endfile,$endcode);
			fclose($endfile);
		}
		function parse(){
			$returns="";
			$code="";
			do{
				$code=fread($this->file,4096);
				foreach($this->mObjects as $object=>$val){
					print( $object ." in:in ". $code );
					if( strstr($object, "_FUN") !== FALSE) str_replace($object, $val(), $code);
					else $code=str_replace($object, $val, $code);
				}
				$returns.=$code;
			}while(strlen($code) > 0);
			return $returns;
		}
		function import($page){
			$defCacheDir=__DIR__."/template/cache/"; // change
			$defTemplateDir=__DIR__."/template/";

			$predefine=$defCacheDir."/".$page.'.cache';
			if( !file_exists ($predefine) || (include $predefine) === FALSE){
				$tpl=$defTemplateDir.$page.".tpl";
				if( !file_exists($tpl) ) die("cant found template: ". $tpl);	
				$this->file=fopen($tpl, "r") 
					or die("cant open template: ". $tpl);	
				$parsed=$this->parse();
				
				$this->compile($parsed, $defCacheDir, $page);
				fclose($this->file);
			}
		}
		function __destruct(){
			
		}
	}
?>
