<?php
    require_once "need.php";
    require_once "../include/bittorrent.inc.php";
    require_once "../include/page_header.inc.php";
?>
<table id=wrapper>
<tr><td>

    <div id=installer>
    <form action='install.php' method="POST">
        <?php
foreach ($need4conf as $element) {
    $type = "text";
    if (strstr($element, "pass")) {
        $type = "password";
    }

    printf("\n<label><span>%s</span> <input autocomplete=\"new-password\" type='%s' name='%s' placeholder='%s' required></label><hr>\n", str_replace("_", " ", $element), $type, $element, $element);
}
?>
        <input type=submit value="Start installation"/>
    </div>
    </form>
</td></tr>
</table>
<style type=text/css>body{opacity: 1 !important;}</style>
</body>
</html>
