<?php
require_once "../include/bittorrent.inc.php";
class admin{
	const DEBUG = FALSE;
	const sqls = array(
		"getAllUsers"=>"SELECT users.username, users.added, users.last_login, users.last_access, 
		(SELECT COUNT(*) FROM torrents WHERE torrents.owner = users.id) AS cntt,
		(SELECT COUNT(*) FROM comments WHERE comments.user = users.id) AS cntc
		FROM users"
		"addUser"=>"INSERT INTO users (username, password, secret, status, added,admin) VALUES( '%s', '%s', '%s', 'confirmed'" . ", NOW(), '%s')",
		"delUser"=>"DELETE FROM USERS where username='%s'"
	);
	
	function getSQLCon(){
			return $this->con;
	}

	function addUser($username, $password, $admin='no')
	    {
		if (strlen($password) > 64)
		    die("Sorry, password is too long (max is 63 chars)");
		if (!preg_match('/^[a-z][\w.-]*$/is', $username) || strlen($username) > 40)
		    die("Invalid username. Must not be more than 40 characters long and no weird characters");
		if (!isset($this->link)) $this->ConnToDBByConfig();
		print("Connected");
		$secret = mksecret();
		$hashpass = hash("sha3-224", $secret . $password . $secret); //JES NEED TO CHANGE sha3 to sha3-224 maybe 224.....
		print("INSERT INTO users (username, password, secret, status, added,admin) VALUES( '$username', '$hashpass', '$secret', 'confirmed'" . ", NOW(), 'yes')");
		$ret = $this->doSQL( self::sqls['addUser'], $username, $hashpass, $secret, $admin );
		if ($ret !== True) return False;
		//print("return true");
		return True;
	}
	function delUserByUsername($username){
		return $this->doSQL( self::sqls['delUser'], $username);
	}

	function __construct($moveIfNotAdmin=True, $page='../index.php'){
		
		dbconn(0);
		$this->con=$GLOBALS["___mysqli_ston"];
		if($moveIfNotAdmin) {
			$is_admin = $this->checkAdmin();
			if(!$is_admin){
				header("Location: ".$page);
				if(!self::DEBUG)
					die("You are not admin. stop abuse this server pleze, kitty will died, if you be continue:(");
			}
		}
		include_once "../include/page_header.inc.php";
	}
	function doSQL($sprintf, ...$arguments){
		//print($sprintf);
		//$con=$GLOBALS["___mysqli_ston"];
		$string = $sprintf;
		foreach( $arguments as $argument )
			$string = sprintf($string, mysqli_real_escape_string($this->con, $argument) );
		//printf("Debug info: %s\n\r", $string);
		return $result = mysqli_query($this->con, $string ); 
	}
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
	function delTorrentByID($id){
		
	}
	function getAllUsers(){
		return $this->doSQL( self::sqls['getAllUsers'] );
	}
	function checkAdmin(){
		$this->isAdmin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
		return $this->isAdmin;
	}
};
