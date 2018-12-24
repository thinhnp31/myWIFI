
<html>
<head>
	<title>Test Page</title>
	<meta charset="utf-8">
</head>

<style type="text/css">
.container {
	height: 100%;
	overflow: hidden;
	padding: 0;
	position: relative;
}

iframe {
	height: 56.25vw;
	left: 50%;
	min-width: 100%;
	min-height: 100%;
	transform: translate(-50%, -50%);
	position: absolute;
	top: 50%;
	width: 177.77777777778vh;
}

</style>

<body>
	<div class="container">
		<iframe src="https://www.youtube.com/embed/WlZuN_ouqEo?autoplay=1&controls=0" allow="autoplay" width="100%"></iframe>
	</div>
	

	<button type="button" id="submit_button">5</button>

	<script type="text/javascript">
		var countdown = 5;

		var x  = setInterval(function() {
			countdown -= 1;
			document.getElementById("submit_button").innerHTML = countdown;
			if (countdown <= 0)
				clearInterval(x);
		}, 1000);
	</script>
</body>
</html>