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
                <div class=step>Create Administrator Account</div>
                    <form action='index.php' method="POST">
                    <?php
                    print($installer->initHTML("admin"));
                    print("<div id=dostuff>");
                    print('<input type=submit value="Create"/>');
                    print("</div>");
                    ?>
                    </form>
            </div>
        </td>
    </tr>
</table>
<?php stdfoot(); ?>