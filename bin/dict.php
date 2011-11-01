#!/usr/bin/env php
<?php

function getData($path) {
	$opts = array(
	    'http' => array(
		    'method' =>'GET',
		    'header' => sprintf('Authorization: Basic %s', base64_encode('erlang:42rVM8VjiyHE6s') )
		)
	);
	$ctx = stream_context_create($opts);
	return json_decode(file_get_contents('http://www.transifex.net' . $path, false, $ctx), TRUE);
}

function getResource($project, $resource) {
	$data = getData('/api/2/project/' . $project . '/resource/' . $resource . '/?details');
	return $data;
}

function getTranslationInfo($project, $resource, $lang) {
	$data = getData('/api/2/project/' . $project . '/resource/' . $resource . '/stats/' . $lang . '/');
	return $data;
}

function getTranslation($project, $resource, $lang) {
	$data = getData('/api/2/project/' . $project . '/resource/' . $resource . '/translation/' . $lang . '/');
	if (!$data['content']) throw new Exception('Invalid response');
	eval('?>' . $data['content'] . '<?');
	return $LANG;
}

$info = getResource('DiscoJuice', 'discojuice');

$langcodes = array();

foreach($info['available_languages'] AS $lang) {
	echo 'Processing Language ' . $lang['name'] . "\n";
	$trans = getTranslation('DiscoJuice', 'discojuice', $lang['code']);
	$transinfo = getTranslationInfo('DiscoJuice', 'discojuice', $lang['code']);
	
	if ($transinfo['untranslated_entities'] > $transinfo['translated_entities']) {
		echo "Skipping language export, because too few translated terms.\n";
		continue;
	}
	// print_r($transinfo);
	
	$filecontent = 'if (typeof DiscoJuice == "undefined") var DiscoJuice = {}; DiscoJuice.Dict = ' . json_encode($trans, TRUE) . ';';
	file_put_contents('discojuice/discojuice.dict.' . $lang['code'] . '.js', $filecontent);
	
	$langcodes[] = $lang['code'];
	
}

file_put_contents('discojuice/languages.json', json_encode($langcodes) );


