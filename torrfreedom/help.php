<?php
require_once("include/bittorrent.inc.php");
require_once "include/page_header.inc.php";
require_once "include/siteinfo.inc.php";
?>
<div id="help">

<h3>How do I download a torrent?</h3>
<p>Choose the torrent you like and right click on the magnet or torrent icon and select copy link location</p>
<p> Paste the link into your I2P-capable BitTorrent client and then start the torrent.</p>

<h3>How do I upload a torrent?</h3>
<p>Create a torrent file in your favorite I2P-capable BitTorrent client, optionally adding the tracker announce address indicated above.<br>
Note: if <?=$tracker_title?>'s tracker isn't already present in the torrent, it will automatically added, so you can upload ANY torrent file.</p>

<p><a href="signup.php">Sign up</a> for upload torrent. No e-mails. No phones. Just username and password. All passwords are encrypted.<p>
&bullet; <a href="upload.php">Upload</a> your torrent file!<br>
&bullet; Start seeding your torrent and others will see it!
<h3>Can I be notified of new torrents?</h3>
<p>Yes! Use the <a href="rss.php">RSS Feed</a> feature to track new uploads.</p>
</div>
<?php stdfoot(); ?>
</body>
</html>
