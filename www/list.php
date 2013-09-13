<?php




$basename = dirname(dirname(__FILE__));
$feedpath = $basename . '/feeds/';




$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
$REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';

$host = $REQUEST_PROTOCOL . '://' . $_SERVER['HTTP_HOST'];



function getFeedList() {
	global $feedpath;
	
	$feedlist = array();
	
	if ($handle = opendir($feedpath)) {
	    while (false !== ($file = readdir($handle))) {
	        if ($file != "." && $file != "..") {
				if (preg_match('/^([a-z0-9\-_]+)\.([a-zA-Z_]+)\.js$/', $file, $matches)) {
					$fileh = $matches[1];
					$lang = $matches[2];
					
					$fs = round(filesize($feedpath . $file) / 1024);
					$fm = date ("d. F Y H:i:s.", filemtime($feedpath . $file));

					$feedlist[$fileh] = 1;
					
					// if (!array_key_exists($fileh, $feedlist)) {
					// 	$feedlist[$fileh] = array();
					// }
					
					// $feedlist[$fileh][$lang] = array(
					// 	'size' => $fs,
					// 	'updatedFormatted' => $fm,
					// );
				}

	        }
	    }
	    closedir($handle);
	}
	return array_keys($feedlist);
}



$feeds = getFeedList();

$engine = array(
	'discojuice-stable.min.js',
	'discojuice-dev.min.js',
	'idpdiscovery.js',
);

header('Content-Type: text/plain; charset=UTF-8');

foreach($engine AS $f) {
	echo 'GET /engine/' . $f . "\r\n";
}

foreach($feeds AS $f) {
	echo 'GET /feeds/' . $f . "\r\n";
}




