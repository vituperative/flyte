<?php
require_once("include/bittorrent.inc.php");
?>
<table align="center" cellpadding="6" cellspacing="0" width="90%" border="0" class="tablenews">
<tr>
<td>
<font face="arial" size="2">
<p><b><?=$tracker_title?> is a free tracker for sharing information!<br><br>
<ul>
<li>Q: I want to <b>download</b> a torrent</li><br>
<ol>
<li>Choose the torrent you like and click on the icon next to it <b><font color="red">🖹</b> or <b>🧲</b>.</li>
<li>Open .torrent file in your client and download it!</li>
</ol>
<br>
<li>Q: I want to <b>upload</b> a torrent</li><br>
<ol>
<li>Make a torrent file and add the tracker address<br>
<input onclick="this.select();" class="gensmall" readonly size="77" style="
border: 1px solid white;" value="<?=$tracker_url_key?>/a"><br>
<input onclick="this.select();" class="gensmall" readonly size="77" style="
border: 1px solid white;" value="<?=$tracker_url_key?>/announce"><br>
<input onclick="this.select();" class="gensmall" readonly size="77" style="
border: 1px solid white;" value="<?=$tracker_url_key?>/announce.php"></li>
<li><a href="signup.php">Sign up</a> for upload torrent. No e-mails. No phones. Just username and password. All passwords are encrypted.</li>
<li><a href="upload.php">Upload</a> your torrent file!</li>
<li>Start 'seeding' your torrent and others will see it!</li>
</ol>
<br>
<li>Q: How can I track new torrents? ==> <a href="rss.php">RSS Feed</a> helps you keep track!</li>
</ul>
<hr>
<b>Useful links:</b> <a href="http://tracker2.postman.i2p/" target="_blank">Postman's Tracker</a> | <a href="http://diftracker.i2p/" target="_blank">DifTracker</a> | <a href="http://mpc73okj7wq2xl6clofl64cn6v7vrvhpmi6d524nrsvbeuvjxalq.b32.i2p/" target="_blank">Torrent Search Engine</a> | <a href="http://planet.i2p/" target="_blank">I2P Planet</a> | <a href="http://identiguy.i2p/" target="_blank">IdentiGuy</a>  | To be continued... ;-)

</td>
</tr>
</table>
