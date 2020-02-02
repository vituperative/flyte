
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
	$sql = file($file);
	$templine = '';
	$errs=0;
	foreach ($sql as $query){
		if (substr($query, 0, 2) == '--' || $query == '') continue;
		$templine .= $query;
		//echo "do"; //https://stackoverflow.com/questions/19751354/how-to-import-sql-file-in-mysql-database-using-php thx author; because source db.sql sdont works
		if (substr(trim($query), -1, 1) == ';')
		 {
    			// Perform the query
    			$link->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . $link->error . '<br /><br />');
			// Reset temp variable to empty
			//echo ("I did ".$templine);
			$templine = '';
			$errs+=1;
		 }
		}

}


$defPathToConfig="../include/secrets.inc.php";

$config_raw="<?php \r\n";
foreach($need4conf as $need){
	if ( !isset( $_POST[$need]) ){
		var_dump($_POST);
		die("need ".$need." for installing, <hr><a href='index.php'>START INSTALLATION</a> ");
	}
	$config_raw.='$'.$need.'="'.$_POST[$need].'";'."\r\n";
}

$mysql_host=$_POST['mysql_host'];
$mysql_user=$_POST['mysql_user'];
$mysql_pass=$_POST['mysql_pass'];
$mysql_db=$_POST['mysql_db'];
$link=mysqli_connect("$mysql_host",  "$mysql_user",  "$mysql_pass", "$mysql_db");

if (!$link) {
    var_dump($_POST);
    die("Can't connect to db: " . mysqli_connect_errno() );

}
$iDB=installDB("db.sql", $link);
if($iDB>0)
        echo( " errs in install db " .$iDB);



$configFile = fopen($defPathToConfig, "w") or die("Can't open ".$defPathToConfig." for write config");
fwrite($configFile, $config_raw);
fclose($configFile);

echo "If you dont see errors, delete install.php and use that";





?>

