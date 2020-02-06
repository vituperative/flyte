<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Language" content="en-us">
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
    <link rel="stylesheet" href="include/style.css" type="text/css">
    <style type="text/css"> html {background: #232020;} body {opacity: 0; text-align: center;} </style>
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="alternate" type="application/rss+xml" title="<?php echo $tracker_title; ?> RSS Feed" href=rss.php />
    <title>
        <?php echo $tracker_title;
        echo (" | ");
        $page = basename($_SERVER['PHP_SELF']);
        $page = str_replace("index", "home", $page);
        $pagename = rtrim($page, "php");
        echo strtoupper(rtrim($pagename, ".")); ?>
    </title>
</head>
<body>
    <div id=header>
        <div id=sitename><a href="<?php echo $tracker_url_name; ?>"><?php echo $tracker_title; ?></a></div>
        <div id="topnav">
            <a href="./">Torrents</a> | <a href="rss.php">RSS Feed</a> <?php if ($CURUSER) {?> | <a href="upload.php">Upload</a><?php }?> | <a href="help.php">Help</a> | <a href="i2psnark-standalone.zip">I2PSnark Client</a> |
            <?php if (!$CURUSER) {?>
            <a href="login.php">Login</a> | <a href="signup.php">Signup</a>
            <?php } else {?>
             <a href="my.php">Your Account</a> | <a href="logout.php">Logout</a>
            <?php }?>
        </div>
    </div>