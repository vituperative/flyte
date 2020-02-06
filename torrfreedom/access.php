<?php
require_once 'include/protect.inc.php';
require_once 'include/bittorrent.inc.php';
dbconn();
stdhead();
?>
<table id=helpwrapper height=100%>
<tr><td>
<div id=restricted>

<?php
    /* Your password */
    $password = 'MYPASS';

    /* Redirects here after login */
    $redirect_after_login = 'server.php';

    /* Will not ask password again for */
    $remember_password = strtotime('+1 hour'); // 30 days

    if (isset($_POST['password']) && $_POST['password'] == $password) {
        setcookie("password", $password, $remember_password);
        header('Location: ' . $redirect_after_login);
        exit;
    }
?>
        <h3>Password required to access this resource</h3>
        <form method="POST">
            <input type="password" name="password">
        </form>
</div>
</td></tr>
</table>
<?php stdfoot();?>