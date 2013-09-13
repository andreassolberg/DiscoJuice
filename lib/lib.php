<?php


/**
 * Add one or more query parameters to the given URL.
 *
 * @param $url  The URL the query parameters should be added to.
 * @param $parameter  The query parameters which should be added to the url. This should be
 *                    an associative array. For backwards comaptibility, it can also be a
 *                    query string representing the new parameters. This will write a warning
 *                    to the log.
 * @return The URL with the new query parameters.
 */


function addURLparameter($url, $parameter) {

	/* For backwards compatibility - allow $parameter to be a string. */
	if(is_string($parameter)) {
		/* Print warning to log. */
		$backtrace = debug_backtrace();
		$where = $backtrace[0]['file'] . ':' . $backtrace[0]['line'];
		SimpleSAML_Logger::warning(
			'Deprecated use of SimpleSAML_Utilities::addURLparameter at ' .	$where .
			'. The parameter "$parameter" should now be an array, but a string was passed.');

		$parameter = parseQueryString($parameter);
	}
	assert('is_array($parameter)');

	$queryStart = strpos($url, '?');
	if($queryStart === FALSE) {
		$oldQuery = array();
		$url .= '?';
	} else {
		$oldQuery = substr($url, $queryStart + 1);
		if($oldQuery === FALSE) {
			$oldQuery = array();
		} else {
			$oldQuery = parseQueryString($oldQuery);
		}
		$url = substr($url, 0, $queryStart + 1);
	}

	$query = array_merge($oldQuery, $parameter);
	$url .= http_build_query($query, '', '&');

	return $url;
}

/**
 * Parse a query string into an array.
 *
 * This function parses a query string into an array, similar to the way the builtin
 * 'parse_str' works, except it doesn't handle arrays, and it doesn't do "magic quotes".
 *
 * Query parameters without values will be set to an empty string.
 *
 * @param $query_string  The query string which should be parsed.
 * @return The query string as an associative array.
 */
function parseQueryString($query_string) {
	assert('is_string($query_string)');

	$res = array();
	foreach(explode('&', $query_string) as $param) {
		$param = explode('=', $param);
		$name = urldecode($param[0]);
		if(count($param) === 1) {
			$value = '';
		} else {
			$value = urldecode($param[1]);
		}

		$res[$name] = $value;
	}

	return $res;
}



/* This function redirects the user to the specified address.
 * An optional set of query parameters can be appended by passing
 * them in an array.
 *
 * This function will use the HTTP 303 See Other redirect if the
 * current request is a POST request and the HTTP version is HTTP/1.1.
 * Otherwise a HTTP 302 Found redirect will be used.
 *
 * The fuction will also generate a simple web page with a clickable
 * link to the target page.
 *
 * Parameters:
 *  $url         URL we should redirect to. This URL may include
 *               query parameters. If this URL is a relative URL
 *               (starting with '/'), then it will be turned into an
 *               absolute URL by prefixing it with the absolute URL
 *               to the root of the website.
 *  $parameters  Array with extra query string parameters which should
 *               be appended to the URL. The name of the parameter is
 *               the array index. The value of the parameter is the
 *               value stored in the index. Both the name and the value
 *               will be urlencoded. If the value is NULL, then the
 *               parameter will be encoded as just the name, without a
 *               value.
 *
 * Returns:
 *  This function never returns.
 */
function redirect($url, $parameters = array()) {
	assert(is_string($url));
	assert(strlen($url) > 0);
	assert(is_array($parameters));



	/* Verify that the URL is to a http or https site. */
	if (!preg_match('@^https?://@i', $url)) {
		throw new Exception('Redirect to invalid URL: ' . $url);
	}

	/* Determine which prefix we should put before the first
	 * parameter.
	 */
	if(strpos($url, '?') === FALSE) {
		$paramPrefix = '?';
	} else {
		$paramPrefix = '&';
	}

	/* Iterate over the parameters and append them to the query
	 * string.
	 */
	foreach($parameters as $name => $value) {

		/* Encode the parameter. */
		if($value === NULL) {
			$param = urlencode($name);
		} elseif (is_array($value)) {
			$param = "";
			foreach ($value as $val) {
				$param .= urlencode($name) . "[]=" . urlencode($val) . '&';				
			}
		} else {
			$param = urlencode($name) . '=' .
				urlencode($value);
		}

		/* Append the parameter to the query string. */
		$url .= $paramPrefix . $param;

		/* Every following parameter is guaranteed to follow
		 * another parameter. Therefore we use the '&' prefix.
		 */
		$paramPrefix = '&';
	}


	/* Set the HTTP result code. This is either 303 See Other or
	 * 302 Found. HTTP 303 See Other is sent if the HTTP version
	 * is HTTP/1.1 and the request type was a POST request.
	 */
	if($_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1' &&
		$_SERVER['REQUEST_METHOD'] === 'POST') {
		$code = 303;
	} else {
		$code = 302;
	}

	if (strlen($url) > 2048) {
		SimpleSAML_Logger::warning('Redirecting to URL longer than 2048 bytes.');
	}

	/* Set the location header. */
	header('Location: ' . $url, TRUE, $code);
	
	console.log('Redirecting to : ' . $url);

	/* Disable caching of this response. */
	header('Pragma: no-cache');
	header('Cache-Control: no-cache, must-revalidate');

	/* Show a minimal web page with a clickable link to the URL. */
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
		' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml">';
	echo '<head>
				<meta http-equiv="content-type" content="text/html; charset=utf-8">
				<title>Redirect</title>
			</head>';
	echo '<body>';
	echo '<h1>Redirect</h1>';
	echo '<p>';
	echo 'You were redirected to: ';
	echo '<a id="redirlink" href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
	echo '<script type="text/javascript">document.getElementById("redirlink").focus();</script>';
	echo '</p>';
	echo '</body>';
	echo '</html>';

	/* End script execution. */
	exit;
}