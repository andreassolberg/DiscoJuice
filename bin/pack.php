#!/usr/bin/env php
<?php

function packJS($data) {
	$postdata = array(
		'js_code' => $data,
		'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		'output_info' => 'compiled_code',
		//'output_info' => 'errors',
		'warning_level' => 'VERBOSE',
		'output_format' => 'text',
	);

	$opts = array('http' =>
	    array(
	        'method'  => 'POST',
	        'header'  => 'Content-type: application/x-www-form-urlencoded',
	        'content' => http_build_query($postdata)
	    )
	);
	$context  = stream_context_create($opts);

	$compressed = file_get_contents('http://closure-compiler.appspot.com/compile', false, $context);
	return $compressed;
}

$basename = dirname(dirname(__FILE__));
$configraw = file_get_contents($basename . '/etc/config.js');
$config = json_decode($configraw, true);



$version = $config['version'];
// if (count($argv) >= 2) $version = $argv[1];

echo 'Version: ' . $version . "\n";


$date = date('Y-m-d H:i');

// $base = './discojuice/www/discojuice/';
$sourcebase = $basename . '/discojuice/';

$files = array(
	'discojuice.misc.js',
	'discojuice.ui.js',
	'discojuice.control.js',
	'discojuice.hosted.js',
);
$data = '';
foreach($files AS $file) {
	$data .= file_get_contents($sourcebase . $file);
}
$data .= "\n" . 'DiscoJuice.Version = "' . $version . ' (' . $date . ')";' . "\n";


$langmeta = json_decode(file_get_contents($sourcebase . 'languages.json'), TRUE);
foreach($langmeta AS $lang) {
	
	$ldata = $data . file_get_contents($sourcebase . 'discojuice.dict.' . $lang . '.js');
	$compressed = packJS($ldata);
	$filename = $basename . '/builds/discojuice-' .  $version . '.' . $lang . '.min.js';
	echo "Packing " . $filename . "\n";
	file_put_contents($filename, $compressed);
}


$ldata = file_get_contents($sourcebase . 'idpdiscovery.js');
// echo $ldata;
$compressed = packJS($ldata);
$filename = $basename . '/builds/idpdiscovery-' .  $version . '.min.js';
echo "Packing " . $filename . "\n";
file_put_contents($filename, $compressed);


