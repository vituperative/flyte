<?php
  header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'none';");
  header("Referrer-Policy:: same-origin;");
  header("X-Content-Type-Options: nosniff;");
  header("X-XSS-Protection: 1;mode=block;");
  header("Set-Cookie: HttpOnly; SameSite=Strict;");
  header("X-Frame-Options: Deny;");
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv=Content-Language content=en-us>
    <META HTTP-EQUIV=Content-Type content=text/html; charset=UTF-8>
    <link rel=stylesheet href=include/style.css type=text/css>
    <style type=text/css> html {background: #232020;} body {opacity: 0; text-align: center;} </style>
    <link rel=shortcut icon href="<?php echo $tracker_path ?>favicon.ico" />
    <link rel=alternate type=application/rss+xml title="<?php echo $tracker_title; ?> RSS Feed" href=rss.php />
    <title>
        <?php echo strtoupper($tracker_title);
        $username = htmlspecialchars($CURUSER["username"]);
        $page = basename($_SERVER['PHP_SELF']);
        $page = str_replace("index", "", $page);
        $page = str_replace("my.php", "$username's account settings", $page);
        $page = str_replace("mytorrents", "$username's uploads", $page);
        $page = str_replace("takeprofedit", "update profile", $page);
        $pagename = rtrim($page, "php");
        if ($pagename != ".")
            echo (" | ");
        echo strtoupper(rtrim($pagename, ".")); ?>
    </title>
</head>
<body>
<div id=header>

<?php
$request = $_SERVER["REQUEST_URI"];
if ($tracker_title == "") {
    if (strpos($request, "install") !== false)
        $tracker_title = "FLYTE INSTALL";
    else
        $tracker_title = "FLYTE TRACKER";
}
?>

<div id=sitename><a href="<?php echo $tracker_url_name; ?>"><?php echo $tracker_title; ?></a></div>

<?php
function topnav() {
//    global $CURUSER;
//    global $username;
    print("<div id=topnav>\n");
    if ($CURUSER)
        print("<a href=upload.php>Upload</a> | <a href=my.php>Account</a> | <a href=logout.php>Logout" . $username . "</a>");
    else
        print("<a href=login.php>Login</a> | <a href=signup.php>Signup</a>");
    print(" | <a href=rss.php>RSS Feed</a> | <a href=help.php>Help</a>\n</div>");
}

if (strpos($request, "install") == false)
    topnav();
else
    print("<div id=installshim></div>");
?>

</div>
<hr id=top hidden>

<?php
// debugging
// $referrer = $_SERVER['HTTP_REFERER'];
// $request = $_SERVER["REQUEST_URI"];
// $cookie = $_COOKIE["auth"];
// print("<p>Referring URL detected as: $referrer</p>");
// print("<p>Request URI detected as: $request</p>");
// print("<p>Cookie: $cookie</p>");
?>