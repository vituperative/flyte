<?php
    require_once "need.php";
    require_once "../include/bittorrent.inc.php";
    require_once "../include/page_header.inc.php";

function installDB($file, $link)
{
    $sql = file($file);
    $templine = '';
    $errs = 0;
    foreach ($sql as $query) {
        if (substr($query, 0, 2) == '--' || $query == '') {
            continue;
        }

        $templine .= $query;
        //echo "do"; //https://stackoverflow.com/questions/19751354/how-to-import-sql-file-in-mysql-database-using-php thx author; because source db.sql sdont works
        if (substr(trim($query), -1, 1) == ';') {
            // Perform the query
            $link->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . $link->error . '<br><br>');
            // Reset temp variable to empty
            //echo ("I did ".$templine);
            $templine = '';
            $errs += 1;
        }
    }

}

$defPathToConfig = "../include/secrets.inc.php";

$config_raw = "<?php \r\n";
foreach ($need4conf as $need) {
    if (!isset($_POST[$need])) {
        var_dump($_POST);
        die("need " . $need . " for installing, <hr><a href='index.php'>START INSTALLATION</a> ");
    }
    $config_raw .= '$' . $need . '="' . $_POST[$need] . '";' . "\r\n";
}

$mysql_host = $_POST['mysql_host'];
$mysql_user = $_POST['mysql_user'];
$mysql_pass = $_POST['mysql_pass'];
$mysql_db = $_POST['mysql_db'];
if (!function_exists('mysql_connect')) print("<p class=installfail>Cannot connect to configured MySQL or MariaDB database.<br>Please ensure your database server is installed and running!</p>");stdfoot();
$link = mysqli_connect("$mysql_host", "$mysql_user", "$mysql_pass", "$mysql_db");

if (!$link) {
    var_dump($_POST);
    die("Can't connect to db: " . mysqli_connect_errno());

}
$iDB = installDB("db.sql", $link);
if ($iDB > 0) {
    echo (" errs in install db " . $iDB);
}

$configFile = fopen($defPathToConfig, "w") or die("Can't open " . $defPathToConfig . " to write config file");
fwrite($configFile, $config_raw) or die("Cannot write to the config file; check access permissions on: " . $defPathToConfig);
fclose($configFile);

echo "If you don't see errors, delete install.php and use that";

?>


</td></tr>
</table>
<style type=text/css>body{opacity: 1 !important;}</style>
</body>
</html>