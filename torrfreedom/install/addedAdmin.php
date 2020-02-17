<?php
require_once "install_class.php";
require_once "../include/bittorrent.inc.php";
require_once "../include/page_header.inc.php";
$installer = new Installer();
print("<link rel=stylesheet href=installer.css type=text/css>");
?>

<div id=installer class=addadmin>
            <?php
            if (isset($_GET['alreadyadded']))
                echo "<p class=warn>Administrator account under that name already exists!</p";
            else
                echo "<p class=success>New Administrator account successfully created!</p>";
            header('Refresh: 5; URL=addAdmin.php');
            ?>
</div>
<?php stdfoot(); ?>