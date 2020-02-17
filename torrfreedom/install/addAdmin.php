<?php
require_once "install_class.php";
require_once "../include/bittorrent.inc.php";
require_once "../include/page_header.inc.php";
$installer = new Installer();
print("<link rel=stylesheet href=installer.css type=text/css>");
?>

<table id=wrapper>
    <tr>
        <td>
            <div id=installer>
                <form action='index.php' method="POST">
                    <?php
                    print($installer->initHTML("admin"));
                    print('<input type=submit value="Create Admin Account"/>');
                    ?>
            </form>
            </div>
        </td>
    </tr>
</table>
<?php stdfoot(); ?>