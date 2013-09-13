<?php

require_once('../lib/lib.php');


function fatal($err) {
	echo $err; exit;
}

if (empty($_REQUEST['return'])) {
	fatal('return parameter missing');
}

function setPreference($entityid) {
	$expire = time() + (3600 * 24 * 600); // 600 days from now.
	setcookie("DiscoJuice_IdPentityID", $entityid, $expire, "/", ".discojuice.org", true, true);
}

function getPreference() {
	if (!empty($_COOKIE['DiscoJuice_IdPentityID'])) return $_COOKIE['DiscoJuice_IdPentityID'];
	return null;
}

function checkACL($return) {
	$host = parse_url($return, PHP_URL_HOST);
	$acl = json_decode(file_get_contents('../acl.js'), TRUE);
	$acllocal = json_decode(file_get_contents('../acl-local.js'), TRUE);
	$acl = array_merge($acl, $acllocal);
	
	if (!is_array($acl)) throw new Exception('ACL file is not parsed as an array.');
	
	if (!in_array($host, $acl)) {
		$message = ('No access to return content to [' . $return . ']. Return parameter invalid.');
		redirect($return, array('error' => $message));
	}
}

$return = $_REQUEST['return'];

error_log('Request: ' . var_export($_REQUEST, TRUE));


try {
	checkACL($return);	
} catch(Exception $e) {
	fatal($e->getMessage());
}


try {
	$returnidparam = 'entityID';
	if (!empty($_REQUEST['returnIDParam'])) {
		$returnidparam = $_REQUEST['returnIDParam'];		
	}
	
	$response = array();
	
	// Handle setting entityID
	if (!empty($_REQUEST['IdPentityID'])) {
		setPreference($_REQUEST['IdPentityID']);
		$response[$returnidparam] = $_REQUEST['IdPentityID'];
	}
	
	// Handle preferred entityOID
	$pref = getPreference();
	if ($pref !== null) {
		$response[$returnidparam] = $pref;
	}
	
	// Return the user.
	redirect(addURLparameter($return, $response));
	
} catch (Exception $e) {
	redirect($return, array('error' => $e->getMessage()));
}

