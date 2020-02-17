<?php
require_once "install_class.php";
require_once "../include/bittorrent.inc.php";
require_once "../include/page_header.inc.php";
$installer = new Installer();

?>

<table id=wrapper>
    <tr>
        <td>

            <?php
            if (isset($_GET['alreadyadded']))
                echo "<div style='color:red'>already added</div>";
            else
                echo "<div style='color:green'>added</div>";
            header('Refresh: 3; URL=addAdmin.php');
            ?>
        </td>
    </tr>
</table>
<style type=text/css>body{opacity: 1 !important;}</style> </body> </html>