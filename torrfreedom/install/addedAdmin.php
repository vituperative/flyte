<?php
require_once "install_class.php";
require_once "../include/bittorrent.inc.php";
require_once "../include/page_header.inc.php";
$installer = new Installer();
?>
<table id=wrapper>
    <tr>
        <td>
            <div id=installer class=addadmin>
                <?php
                if (isset($_GET['alreadyadded'])) {
                    echo "<div class=step>Warning: Account exists!</div>";
                    echo "<p class=warn>Administrator account under that name already exists!</p>";
                } else {
                    echo "<div class=step>Success!</div>";
                    echo "<p class=success>New Administrator account successfully created!</p>";
                }
                header('Refresh: 10; URL=addAdmin.php');
                ?>
            </div>
        </td>
    </tr>
</table>
<?php stdfoot(); ?>