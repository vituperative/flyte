<?php
include_once("../methods_.php");
include_once mm::require_file("/include/bittorrent.inc.php");
class sql{

	const sqls = array(
		"getAllUsers"=>"SELECT users.username, users.added, users.last_login, users.last_access, 
		(SELECT COUNT(*) FROM torrents WHERE torrents.owner = users.id) AS cntt,
		(SELECT COUNT(*) FROM comments WHERE comments.user = users.id) AS cntc, status
		FROM users",
		"addUser"=>"INSERT INTO users (username, password, secret, status, added,admin) VALUES( '%s', '%s', '%s', '%s'" . ", NOW(), '%s')",
		"delUser"=>"DELETE FROM users where username='%s'",

		"getTorrentByID"=>"SELECT * FROM torrents WHERE id = '%d'",
		"delTorrentByID"=>"DELETE FROM torrents WHERE id= '%d'",
		"delTorrentByX"=>"DELETE FROM torrents WHERE %s= '%s'",
		"delTorrentsByUserID"=>"DELETE FROM torrents WHERE owner= '%s'",

		"getTorrentsByUserID"=>"SELECT * FROM torrents WHERE owner= '%s' LIMIT %d OFFSET %d",

		"getUserByID"=>"SELECT * FROM users where id='%d'",
		"getUserByName"=>"SELECT * FROM users where username='%s'",

		"delCommentsWhereIS"=>"DELETE FROM comments where %s='%s'",
		"changeValueOfTorrentByID"=>"UPDATE torrents SET `%s`='%s' WHERE `%s`='%s'", //Update torrents set what is where a=b 
		"getCountOfTB"=>"select COUNT(*) AS count FROM %s",
		"getCountOfTBWhere"=>"select COUNT(*) AS count FROM %s WHERE %s='%s'",
		"isAdmin"=>"select * from users where username='%s' and admin='yes';",
		"getAllTorrents"=>"SELECT * FROM torrents LIMIT %d OFFSET %d",
		"getNameOfCategoryByID"=>"select * from categories where id='%d'"
	);
	function doSQL($sprintf, ...$arguments){
		$string = vsprintf($sprintf, $arguments );
		//printf("Debug info: %s\n\r<br>", $string);
		return $result = mysqli_query($this->con, $string ); 
	}
	function getSQLCon(){
			return $this->con;
	}
	function __construct(){
		dbconn(0);
		$this->con=$GLOBALS["___mysqli_ston"];
	}
	function getLastSQLError(){
		return mysqli_error($this->con);
	}
	function getCountOfTB($table){
		$r= $this->doSQL( sql::sqls['getCountOfTB'], $table );
		return mysqli_fetch_assoc($r)['count'];
	}
	function getCountOfTBWhere($table,$a,$b){
		$r= $this->doSQL( sql::sqls['getCountOfTBWhere'], $table,$a,$b );
		return mysqli_fetch_assoc($r)['count'];
	}
}
?>
