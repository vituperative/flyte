<?php
require_once 'include/bittorrent.inc.php';
dbconn(0);
stdhead("ERROR 404: Not found");
?>
<div id=notfound>
<p class=warn><span class="title">404 Not Found</span>The resource you requested is not available on this server.</p>
</div>
<?php header('Refresh: 3; URL=' . $tracker_path); ?>
<?php stdfoot(); ?>