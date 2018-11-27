
<?php
	require "includes/agent.php";
?>


<html>
<head>
	<title>Test Page</title>
	<meta charset="utf-8">
</head>
<body>
	<?php
		$hours = "";
	    for ($i=0; $i<24; $i++)
	      $hours = $hours. '"' . $i . '", ';
	    $hours = substr($hours, 0, -2);
	    $hours = "[" . $hours . "]";
	    echo $hours;
	?>
</form>
</body>
</html>