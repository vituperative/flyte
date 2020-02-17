<?php
require_once "install_class.php";
require_once "../include/bittorrent.inc.php";
require_once "../include/page_header.inc.php";
$installer = new Installer();
?>

<table id=wrapper>
    <tr>
        <td>
            <div id=installer>
                <form action='index.php' method="POST">
                    <?php
                    print($installer->initHTML("admin"));
                    print('<input type=submit value="Add admin"/>');
                    ?>
            </div>
            </form>
        </td>
    </tr>
</table>
<style type=text/css>body{opacity: 1 !important;}</style> </body> </html>