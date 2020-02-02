
<?php
/*
// Host of your mysql database (usually localhost)
$mysql_host = "localhost";

// Username to access the database
$mysql_user = "test";

// Password to access the database
$mysql_pass = "test";

// The name of the database you will use
$mysql_db = "torrfreedom";

// Name of your tracker
$tracker_title = "Torrent Freedom";

// Complete human url to tracker location. DO NOT trail with a /
$tracker_url_name = "http://torrfreedom.i2p";

// Complete b64 url to tracker location. DO NOT trail with a /
// remember to append the .i2p suffix after your key
$tracker_url_key = "http://nfrjvknwcw47itotkzmk6mdlxmxfxsxhbhlr5ozhlsuavcogv4hq.b32.i2p"; 

// Complete server path to the torrents directory on your server.
// use forward slashes for windows paths eg. C:/path/to/torrents
$torrent_dir = "/path/to/torrents";
 */
require_once("need.php");//some shitcode

function installDB($file, $link){
	$sql = file_get_contents($file);
	$result = $link->query($sql);
	return $result;
}


$defPathToConfig="../include/secrets.inc.php";

$config_raw="<?php \r\n";
foreach($need4conf as $need){
	if ( !isset( $_POST[$need]) )
		die("need ".$need." for installing");
	$config_raw.='$'.$need.'="'.$_POST[$need].'";'."\r\n";
}
$link=mysqli_connect($_POST['mysql_host'],  $_POST['mysql_user'],  $_POST['mysql_pass'], $_POST['mysql_db']);

if (!$link) {
    die("Can't connect to db: " . mysqli_connect_errno() );
}
$iDB=installDB("db.sql", $link);
if(!$iDB)
        die( " Can't install db " .$link->error);



$configFile = fopen($defPathToConfig, "w") or die("Can't open ".$defPathToConfig." for write config");
fwrite($configFile, $config_raw);
fclose($configFile);






?>

