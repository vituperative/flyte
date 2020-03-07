<?php
  if (ob_get_level() == 0) {
    ob_start("ob_gzhandler");
  if ( strstr(__DIR__,"include") !== FALSE){
//	print(__DIR__);
  	require_once "bittorrent.inc.php";
  }
  else
	require_once "include/bittorrent.inc.php";
  //dbconn(0);
  //stdhead(); WHO IS ADD THIS ?! WHICH? WHERE U?
}
global $CURUSER, $pic_base_url, $tracker_title, $tracker_url_name, $tracker_path;
?>

<?php
  header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'none';");
  header("Referrer-Policy: same-origin;");
  header("X-Content-Type-Options: nosniff;");
  header("X-XSS-Protection: 1;mode=block;");
  header("Set-Cookie: HttpOnly; SameSite=Strict;");
  header("X-Frame-Options: Deny;");
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv=Content-Language content=en-us>
    <meta charset="UTF-8">
    <?php
        $request = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : $_SERVER['SCRIPT_FILENAME']; // TODO ADD THAT 2 FUNCTION
        if (strpos($request, "install/") !== false) {
            print("<link rel=stylesheet href=../include/style.css type=text/css>\n");
            print("<link rel=stylesheet href=installer.css type=text/css>\n");
        } else if (strpos($request, "admin") !== false) {
            print("<link rel=stylesheet href=../include/style.css type=text/css>\n");
            print("<link rel=stylesheet href=admin.css type=text/css>\n");
        } else {
            print("<link rel=stylesheet href=include/style.css type=text/css>\n");
        }
    ?>
    <style type=text/css>html, body{background: #151414;} body{opacity: 0 !important; text-align: center;}</style>
    <link rel=shortcut icon href=<?php echo $tracker_path ?>favicon.ico>
    <link rel=alternate type=application/rss+xml title="<?php echo $tracker_title; ?> RSS Feed" href=rss.php>
    <title><?php
        if (strpos($request, "install/") !== false) {
            $tracker_title = "FLYTE INSTALL";
            echo strtoupper($tracker_title);
        } else {
            if ($tracker_title == false)
                $tracker_title = "FLYTE";
            echo strtoupper($tracker_title);
            $username = htmlspecialchars($CURUSER["username"]);
            $page = basename($_SERVER['PHP_SELF']);
            $page = str_replace("index", "", $page);
            $page = str_replace("my.php", "$username's account settings", $page);
            $page = str_replace("mytorrents", "$username's torrents", $page);
            $page = str_replace("takeprofedit", "update profile", $page);
            $pagename = rtrim($page, "php");
            if ($pagename != ".")
                echo (" | ");
            echo strtoupper(rtrim($pagename, "."));
        }
        ?></title>
</head>
<body>
<div id=header>
<div class="shim top"></div>
<?php
print("<div id=sitename><a href=" . $tracker_path . ">" . $tracker_title . "</a></div>\n");
if(!function_exists("topnav")){
function topnav() {
    global $CURUSER;
    global $username;
    global $tracker_path;
    $isadmin = $CURUSER["admin"] == "yes";
    $request = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : $_SERVER['SCRIPT_FILENAME'];
    print("<div id=topnav>");
    if (strpos($request, "admin") !== false && $isadmin) {
            print("<a href=#>Blacklist</a> | <a href=#>Configure</a> | <a href=server.php>Server</a> | <a href=users.php>Users</a> | <a href=../logout.php>Logout</a></div>\n");
    } else if ($CURUSER) {
        print("<a href=upload.php>Upload</a> | <a href=my.php>Account</a> | <a href=logout.php>Logout</a>");
        if ($isadmin)
            print(" | <a href=" . $tracker_path . "admin/>Admin</a>");
    } else {
        print("<a href=login.php>Login</a> | <a href=signup.php>Signup</a>");
    }
    if (strpos($request, "admin") === false)
        print(" | <a href=rss.php>RSS Feed</a> | <a href=help.php>Help</a> | <a href=stats.php>Stats</a></div>\n");
}
}

if (strpos($request, "install/") == false) {
    topnav();
    if ($request !== $tracker_path)
        if  (!strpos($request, "cat"))
            print("<div id=tracker class=shim></div>");
} else {
    print("<div id=installation class=shim></div>");
}
print("</div>\n<hr id=top hidden>");
?>

<?php
/**
 $server = $_SERVER['HTTP_HOST'];
 $referrer = $_SERVER['HTTP_REFERER'];
 $request = $_SERVER["REQUEST_URI"];
 $cookie = $_COOKIE["auth"];
 $spacer = "&nbsp;&nbsp;&nbsp;&bullet;&nbsp;&nbsp;&nbsp;";
 if (!$referrer) {$referrer = "???";}
 if (!$cookie) {$referrer = "???";}
 print("<p id=toast class=fixed>" . $spacer . "<b>Server:</b> <i>" . $server . "</i><br>");
 print($spacer . "<b>Referring URL:</b> <i>" . $referrer . "</i><br>");
 print($spacer . "<b>Request URI:</b> <i>" . $request . "</i><br>");
 print($spacer . "<b>Auth cookie:</b> <i>");
 if ($cookie) {
     $cookie = truncate($cookie, 8, 0);
 } else{
     $cookie = "Not logged in";
 }
 print($cookie . "&hellip;</i></p>");
**/
 ?>
