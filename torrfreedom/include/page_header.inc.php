<?php
 require_once 'page_header_class.inc.php';
 $header = new page_header_headers(); // function __construct($stdhead=""); so if you need stdhead("...") stdfoot automatically;
 
?>

<body>
<div id=header>
<div class="shim top"></div>
<?php

print("<div id=sitename><a href=" . $tracker_path . ">" . $tracker_title . "</a></div>\n");

function topnav() {
    global $CURUSER;
    global $username;
    print("<div id=topnav>");
    if ($CURUSER)
        print("<a href=upload.php>Upload</a> | <a href=my.php>Account</a> | <a href=logout.php>Logout" . $username . "</a>");
    else
        print("<a href=login.php>Login</a> | <a href=signup.php>Signup</a>");
    print(" | <a href=rss.php>RSS Feed</a> | <a href=help.php>Help</a></div>\n");
}

if (strpos($request, "install") == false)
    topnav();
else
    print("<div id=installation class=shim></div>");

print("</div>\n<hr id=top hidden>");

// debugging
/**
$server = $server = $_SERVER['HTTP_HOST'];
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
