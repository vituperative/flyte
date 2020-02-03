<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="Content-Language" content="en-us">
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
  <link rel="stylesheet" href="include/style.css" type="text/css">
  <link rel="shortcut icon" href="favicon.ico" />
  <link rel="alternate" type="application/rss+xml" title="<?php echo $tracker_title; ?> RSS Feed" href=rss.php />
  <title><?php echo $tracker_title; ?></title>
</head>
<body>
<div class="main">
  <table class="borderTable" border="0" cellpadding="2" cellspacing="0" width="100%">
    <tr>
      <td class="title" valign="middle">
        <!-- Tracker birthday: 11/06/2017 -->
        <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td rowspan="2" style="text-align: center; vertical-align: middle;"><img src="../pic/icon.png"/></td>
            <td style="font-size: 22px; text-align: left; vertical-align: middle; font-weight: bold;"><?php echo $tracker_title; ?></td>
          </tr>
          <tr>
            <td style="font-size: 22px; text-align: left; vertical-align: middle;">Tracker runs <?php $birthday = new DateTime('2017-06-11');
$now = new DateTime();
echo $birthday->diff($now)->format('%a days');?></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="background-color: #E9F8FF;">
    <div id="topnav">
        <?php if (!$CURUSER) {?>
        <a href="login.php">Login</a> / <a href="signup.php">Signup</a>&nbsp;&nbsp;
        <?php } else {?>
        <span class="username">Hello, <?=htmlspecialchars($CURUSER["username"])?></span> | <a href="my.php">Your Account</a> | <a href="logout.php">Logout</a>
        <?php }?>
        | <a href="./">Torrents</a> | <a href="upload.php">Upload</a> | <a href="rss.php">RSS Feed</a>
        | <a href="i2psnark-standalone.zip">I2PSnark Client</a>
    </div>
        </td>
    </tr>
  </table>
