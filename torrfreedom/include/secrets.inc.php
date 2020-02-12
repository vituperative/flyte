<?php

// Host of your mysql database (usually localhost)
$mysql_host = "localhost";

// Username to access the database
$mysql_user = "root";

// Password to access the database
$mysql_pass = "yourpassward";

// The name of the database you will use
$mysql_db = "flyte";

// Name of your tracker
$tracker_title = "FLYTE";

// Complete human url to tracker location. DO NOT trail with a /
// $tracker_url_name = "http://torrents.skank.i2p";
$tracker_url_name = "http://yourtrackername.i2p";

// Complete b64 or b32 url to tracker location. DO NOT trail with a /
// remember to append the .i2p suffix after your key
// $tracker_url_key = "http://nfrjvknwcw47itotkzmk6mdlxmxfxsxhbhlr5ozhlsuavcogv4hq.b32.i2p";
$tracker_url_key = "http://yourb32address.b32.i2p";

// absolute path to the tracker, excluding the domain name or ip address, with a trailing /
// if the tracker software is being served from the document root, specify / as the path
$tracker_path = "/";

// Complete server path to the torrents directory on your server.
// use forward slashes for windows paths eg. C:/path/to/torrents
$torrent_dir = "/home/path/to/torrents";

// b32 address
$b32 = "http://yourb32.b32.i2p";

// siteadmin contact e-mail
$contact_url = "<a href=mailto:yourcontactemail.i2p>yourcontactemail@mail.i2p</a>";
$contact = "yourcontactemail@mail.i2p";

?>
