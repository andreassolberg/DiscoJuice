<?php

$basename = dirname(dirname(__FILE__));

require_once($basename . '/lib/httplanguage.php');

$PATH = $basename . '/builds/';
$configraw = file_get_contents($basename . '/etc/config.js');
$config = json_decode($configraw, true);
if ($config === null) {
	echo 'Invalid configuration in config.js';
	throw new Exception('Invalid configuration in config.js');
}




$enableCache = false;

$stable = array('discojuice-' . $config['tags']['stable'] . '.', '.min.js');
$dev =  array('discojuice-' . $config['tags']['dev'] . '.', '.min.js');
$idpdisco = 'idpdiscovery-' . $config['tags']['stable'] . '.min.js';
$availableLanguages = json_decode(
	str_replace('_', '-', 
		file_get_contents($basename . '/discojuice/languages.json')
	), TRUE
);



// print_r($stable); exit;


// print_r($config); exit;





$fullURI = substr($_SERVER['PATH_INFO'], 1);


// if (!is_array($parameters)) {
// 	$parameters = array($parameters);
// }

//echo '<pre>';print_r($fullURI); exit;
if (empty($fullURI) ) {
	
	echo '<!DOCTYPE html>

	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>DiscoJuice Engine</title>
		
		<style>
			thead th {
				text-align: left;
			}
			body {
				background: #eee;
			    font-family: "Lucida Sans Unicode", "Lucida Grande", Verdana, Arial, Helvetica, sans-serif;
			}
			
			div.data {
				margin: 3em;
				padding: 2em;
				border: 1px solid #ccc;
				-webkit-border-radius: 10px;
				-moz-border-radius: 10px;
				border-radius: 10px;
				background: white;
			}
			
			table tr.row td {
				border-top: 1px solid #eee;
			}
			.langdata {
				font-size: 86%;
			}
			li {
				margin: 3px 20px
			}
		
		</style>

	</head>

	<body>
	<div class="data">
	<h1>DiscoJuice Engine</h1>
	
		<p>Visit <a href="http://discojuice.org">discojuice.org</a> for more information about DiscoJuice. <a href="http://static.discojuice.org/feeds/">DiscoJuice data feeds</a></p>
			
		<p>These are automatic links that detects language and selects the most updated version.</p>

		<ul>
			<li style=""><tt><a href="discojuice-stable.min.js">discojuice-stable.min.js</a></tt> (' . $stable[0] . 'en' . $stable[1] . ')</li>
			<li style=""><tt><a href="discojuice-dev.min.js">discojuice-dev.min.js</a></tt> (' . $dev[0] . 'en' . $dev[1] . ')</li>
			<li style=""><tt><a href="idpdiscovery.js">idpdiscovery.js</a></tt> (' . $idpdisco . ')</li>
		</ul>
	';
	
	echo '
		<p>You may also link to a specific version and language</p>
		
		<table style="width: 100%">
		<thead>
			<tr>
				<th>Script</th>
				<th>Size</th>
				<th>Last updated</th>
			</tr>
		</thead>
		<tbody>';
	
	$filelist = scandir($PATH);
	
	foreach($filelist AS $file) {
       if ($file == "." || $file == "..") continue;

		$fileh = $file;
		$fs = round(filesize($PATH . $file) / 1024);
		$fm = date ("d. F Y H:i:s.", filemtime($PATH . $file));
      	echo '<tr class="row">' . 
			'<td><a href="' . htmlspecialchars($fileh) . '"><tt>' . htmlspecialchars($fileh). '</tt></a></td>' .
			'<td>' . $fs . ' kB</td>' . 
			'<td>' . $fm . '</td>' .
			'</tr>';
	}
	
	echo '</tbody></table>
	</div>
	</body>
	</html>';
	exit;
	
}


$selectedFile = null;
$selectedLang = getHTTPLanguage($availableLanguages);


error_log('Selected lang: ' . $selectedLang);

error_log('Client says: ' . var_export(getAcceptLanguage($availableLanguages), TRUE));
error_log('Available languages: ' . var_export($availableLanguages, TRUE));

if ($fullURI == 'discojuice-stable.min.js') {
	$selectedFile = $stable[0] . $selectedLang . $stable[1];
	if (!file_exists($PATH . $selectedFile)) {
		$selectedFile = $stable[0] . 'en' . $stable[1];
	}
} else if ($fullURI == 'discojuice-dev.min.js') {
	$selectedFile = $dev[0] . $selectedLang . $dev[1];
	if (!file_exists($PATH . $selectedFile)) {
		$selectedFile = $dev[0] . 'en' . $dev[1];
	}
} else if ($fullURI == 'idpdiscovery.js') {
	$selectedFile = $idpdisco;
} else {
	if (preg_match('/^discojuice-([0-9\.]+)(\.[a-zA-Z_]+)?(\.min)?\.js$/', $fullURI, $matches)) {
		$selectedFile = $fullURI;
	} elseif(preg_match('/^idpdiscovery-([0-9\.]+)(\.[a-zA-Z_]+)?(\.min)?\.js$/', $fullURI, $matches)) {
		$selectedFile = $fullURI;
	} else {
		echo 'Invalid file';
		exit;
	}

}

if (!file_exists($PATH . $selectedFile)) {
	echo 'File not found';
	exit;
}

error_log('Providing DiscoJuice engine file: ' . $selectedFile);


$fullfile = $PATH . $selectedFile;

$data = file_get_contents($fullfile);

ob_start("ob_gzhandler");

$last_modified_time = filemtime($fullfile); 
$last_modified_string = gmdate("D, d M Y H:i:s", $last_modified_time)." GMT";
$etag = md5_file($fullfile); 


$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
if ($enableCache && $if_modified_since === $last_modified_string) {
    header("HTTP/1.0 304 Not Modified");
	error_log('HTTP/1.0 304 Not Modified   - If not Modified Since');
    exit;
}

header("X-Selected-Language: " . $selectedLang);
header("X-Selected-File: " . $selectedFile);
header("Vary: Content-Language");
header("Last-Modified: " . $last_modified_string); 
header("Etag: $etag");

$expires = 60*60*24*3; // Cache for three day
header("Pragma: public");


header('Content-Type: application/javascript; charset=utf-8');
echo $data;


