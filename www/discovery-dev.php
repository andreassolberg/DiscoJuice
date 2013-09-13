<?php



?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Select your login provider – DiscoJuice</title>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>


	<!-- DiscoJuice -->
	<script type="text/javascript" language="javascript" 
	src="http://cdn.discojuice.org/dev/discojuice.misc.js?v=0.1-4"></script>
	<script type="text/javascript" language="javascript" 
	src="http://cdn.discojuice.org/dev/discojuice.ui.js?v=0.1-4"></script>
	<script type="text/javascript" language="javascript" 
	src="http://cdn.discojuice.org/dev/discojuice.control.js?v=0.1-4"></script>

	<script type="text/javascript" language="javascript" 
	src="http://cdn.discojuice.org/dev/discojuice.hosted.js?v=0.1-4"></script>

	<script type="text/javascript" language="javascript" 
	src="http://cdn.discojuice.org/dev/discojuice.dict.nb.js"></script>

	<link rel="stylesheet" type="text/css" href="http://data.discojuice.org/dev/css/discojuice.css?v=0.1-4" />


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


		$("document").ready(function() {
			var djc = DiscoJuice.Hosted.getConfig(
		       "DiscoJuice Demo",
		        "https://foodl.org/saml2/entityid",
		        "http://foodl.org/response.html", ["edugain", "kalmar", "feide"], "http://service.org/login?idp="
		    );
			djc.always = true;
			console.log(djc);
			$("body").DiscoJuice(djc);
		});

	</script>
	
	
	
</head>
<body style="background: #e8e8e8">





</body>
</html>









