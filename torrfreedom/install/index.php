<html>
	<head>
		<title>Install prepare htmlmarkup</title>
	</head>
<body>
	<div id=installer>
		<?php
	     		require_once("need.php");
	     		foreach( $need4conf as $element){
	     			printf("\n%s: <input type='textarea' name='%s' placeholder='%s' \><hr/>\n", $element, $element, $element);
			}
		?>
	</div>
</body>
</html>
