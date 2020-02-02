<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="Content-Language" content="en-us">
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
  <link rel="stylesheet" href="include/style.css" type="text/css">
  <link rel="shortcut icon" href="favicon.ico" />
  <title> Installer of that tracker </title>
</head>
<body>
	<div id=installer>
	<form action='install.php' method="POST">
		<?php
	     		require_once("need.php");
	     		foreach( $need4conf as $element){
	     			printf("\n%s: <input type='textarea' name='%s' placeholder='%s' \><hr/>\n", $element, $element, $element);
			}
		?>
	</div>
	</form>
</body>
</html>
