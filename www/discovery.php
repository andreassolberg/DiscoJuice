<?php



?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Select your login provider – DiscoJuice</title>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>

	<script type="text/javascript" language="javascript" src="http://cdn.discojuice.org/engine/discojuice-dev.min.js"></script>

	<link rel="stylesheet" type="text/css" href="http://cdn.discojuice.org/dev/css/discojuice.css?v=0.1-4" />

	<style type="text/css">
		body {
			text-align: center;
		}
		div.discojuice {
			text-align: left;
			position: relative;
			width: 600px;
			margin-right: auto;
			margin-left: auto;
		}
	</style>

	<script type="text/javascript">

		var up = window.location.pathname.split('/');
		var options = {};
		if (up.length == 4) {
			var lup = up[up.length-1];
			options = JSON.parse(decodeURIComponent(lup));

		}
				

		var title = 'DiscoJuice Demo';
		if (options.title) title = options.title;

		console.log("Path: " + window.location.pathname, up, lup);
		console.log(up);
		console.log(options);



		$("document").ready(function() {
			var djc = DiscoJuice.Hosted.getConfig(
		       "Select provider",
				options.entityID || null,
		       	null, 
		       	options.feeds || ["edugain", "kalmar", "feide"], 
		       	options.return || "http://service.org/login?idp="
		    );
			djc.always = true;
			console.log(djc);
			$("body").DiscoJuice(djc);
		});

	</script>
	
	
	
</head>
<body style="background: #e8e8e8">

	<h1>TODO: not yet impletented</h1>



</body>
</html>









