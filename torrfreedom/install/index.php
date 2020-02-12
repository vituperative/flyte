<?php
    require_once "need.php";
    require_once "../include/bittorrent.inc.php";
    require_once "../include/page_header.inc.php";
?>

<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="Content-Language" content="en-us">
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
  <link rel="stylesheet" href="../include/style.css" type="text/css">
  <link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
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
</body>
</html>
