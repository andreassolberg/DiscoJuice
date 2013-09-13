<?php

$basename = dirname(dirname(__FILE__));

require_once($basename . '/lib/httplanguage.php');

$FEEDPATH = $basename . '/feeds/';

$fullURI = $_SERVER['PATH_INFO'];
$script = $_SERVER['SCRIPT_NAME'];

$path = substr($fullURI, strlen($script) + 1, strlen($fullURI) - strlen($script) - 1);
$parameters = explode('/', $fullURI);

// print_r($FEEDPATH); exit;

function getAvailLanguages($feed) {
	global $FEEDPATH;

	$lang = array();
	$search = $feedfile = $FEEDPATH . $feed . '.js';
	
	if ($handle = opendir($FEEDPATH)) {
	    while (false !== ($file = readdir($handle))) {
	        if ($file != "." && $file != "..") {
				if (preg_match('/^' . $feed . '\.([a-zA-Z_]+)\.js$/', $file, $matches)) {
					$lang[] = $matches[1];
					error_log('Match: ' . var_export($matches, TRUE));
				}

	        }
	    }
	    closedir($handle);
	}
	return $lang;
}


function getFeedList() {
	global $FEEDPATH;
	
	$feedlist = array();
	
	if ($handle = opendir($FEEDPATH)) {
	    while (false !== ($file = readdir($handle))) {
	        if ($file != "." && $file != "..") {
				if (preg_match('/^([a-z0-9\-_]+)\.([a-zA-Z_]+)\.js$/', $file, $matches)) {
					$fileh = $matches[1];
					$lang = $matches[2];
					
					$fs = round(filesize($FEEDPATH . $file) / 1024);
					$fm = date ("d. F Y H:i:s.", filemtime($FEEDPATH . $file));
					
					if (!array_key_exists($fileh, $feedlist)) {
						$feedlist[$fileh] = array();
					}
					
					$feedlist[$fileh][$lang] = array(
						'size' => $fs,
						'updatedFormatted' => $fm,
					);
				}

	        }
	    }
	    closedir($handle);
	}
	return $feedlist;
}


// if (!is_array($parameters)) {
// 	$parameters = array($parameters);
// }

function getFlag($lang) {
	$lc = substr($lang, 0, 2);
	$mapping = array(
		'en' => 'gb',
		'da' => 'dk',
		'ja' => 'jp',
		'el' => 'gr',
		'nn' => 'no',
		'cs' => 'cz',
		'sl' => 'si',
	);
	if (isset($mapping[$lc])) $lc = $mapping[$lc];
	return '/flags/' . $lc . '.png';
}


if ($fullURI === '/') {
	
	echo '<!DOCTYPE html>

	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>DiscoJuice Data Feeds</title>
		
		<style>
			
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
		
		</style>

	</head>

	<body>
	<div class="data">
	<h1>DiscoJuice Data Feeds</h1>
	
		<p>Visit <a href="http://discojuice.org">discojuice.org</a> for more information about DiscoJuice. <a href="/engine/">DiscoJuice script engine</a></p>
	
	';
	echo '<table>
		<thead>
			<tr>
				<th>Feed identifier</th>
				<th>Available languages</th>
			</tr>
		</thead>
		<tbody>
	';
	
	$feedlist = getFeedList();



	ksort($feedlist);
	foreach($feedlist AS $key => $entry) {
		
		echo '<tr class="row"><td><a href="/feeds/' . htmlspecialchars($key) . '">'  . htmlspecialchars($key) . '</a></td>';
		
		echo '<td>';
		foreach($entry AS $lang => $file) {
			
			echo '<div class="langdata">' . 
				'<img style="margin: 2px 5px; position: relative; top: 2px" src="' . getFlag($lang) . '" />' .
				'' . $lang . ' updated ' . $file['updatedFormatted']  . ' ( ' . $file['size'] . ' kB )</div>';
						
		}
		echo '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>
	</div>
	</body>
	</html>';
	exit;
	
}

if (count($parameters) !== 2) {
	throw new Exception('Invalid number of parameters');
}

if (empty($parameters[1])) {
	throw new Exception('Empty feed id');
}



$feed = $parameters[1];



if (!preg_match('/^[0-9a-z\-_]+$/', $feed)) {
	throw new Exception('Invalid characters in feed name');
}





$availableLanguages = getAvailLanguages($feed);
$selectedLang = getHTTPLanguage($availableLanguages);

$feedfile = $FEEDPATH . $feed . '.' . $selectedLang . '.js';

error_log('Languages avail: ' . var_export($availableLanguages, TRUE));
error_log('Client says: ' . var_export(getAcceptLanguage($availableLanguages), TRUE));
error_log('Selected Lang: ' . $selectedLang);
error_log('Selected File: ' . $feedfile);


if (!file_exists($feedfile)) {
	throw new Exception('Feed not found');
}

$data = file_get_contents($feedfile);


ob_start("ob_gzhandler");


$last_modified_time = filemtime($feedfile); 
$last_modified_string = gmdate("D, d M Y H:i:s", $last_modified_time)." GMT";
$etag = md5_file($feedfile);



$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
if ($if_modified_since === $last_modicfied_string) {
    header("HTTP/1.0 304 Not Modified");
	error_log('HTTP/1.0 304 Not Modified   - If not Modified Since');
    exit;
}






header("Vary: Content-Language");
header("Last-Modified: ". $last_modified_string);
header("Etag: ". $etag);


$expires = 60*60*24*3; // Cache for three day
header("Pragma: public");



if ($_REQUEST['callback']) {
	if(!preg_match('/^[a-z0-9A-Z\-_]+$/', $_REQUEST['callback'])) throw new Exception('Invalid characters in callback.');

	header('Content-Type: application/javascript; charset=utf-8');
	echo $_REQUEST['callback'] . '(' . $data . ')';
} else {
	header('Content-Type: application/json; charset=utf-8');
	echo $data;
}



