<?php


class Utils {
	
	public static function getSubID($hostname = null) {

		if(php_sapi_name() == 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
			return '_CLI';
		}

		if ($hostname === null) {
			$hostname = $_SERVER['HTTP_HOST'];
		}

		$subhost = null;
		$mainhost = GlobalConfig::hostname();

		if (preg_match('/^([a-zA-Z0-9]+).' . $mainhost . '$/', $hostname, $matches)) {
			$subhost = $matches[1];
		} else {
			return false;
		}
		self::validateID($subhost);
		return $subhost;
	}

	public static function getHost() {
		return $_SERVER['HTTP_HOST'];
	}


	public static function route($method = false, $match, $parameters, $object = null) {
		if (empty($_SERVER['PATH_INFO']) || strlen($_SERVER['PATH_INFO']) < 2) return false;

		$inputraw = file_get_contents("php://input");
		if ($inputraw) {
			$object = json_decode($inputraw, true);
		}
		

		$path = $_SERVER['PATH_INFO'];
		$realmethod = strtolower($_SERVER['REQUEST_METHOD']);

		if ($method !== false) {
			if (strtolower($method) !== $realmethod) return false;
		}
		if (!preg_match('|^' . $match . '|', $path, &$parameters)) return false;
		return true;
	}

	public static function genID() {
		// http://www.php.net/manual/en/function.uniqid.php#94959
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	public static function validateGroupID($id) {
		if (preg_match('/^([a-zA-Z0-9\-]+)$/', $id, $matches)) {
			return true;
		}
		throw new Exception('Invalid characters in provided identifier');
	}

	public static function validateID($id) {
		if (preg_match('/^([a-zA-Z0-9\-]+)$/', $id, $matches)) {
			return true;
		}
		throw new Exception('Invalid characters in provided app ID');
	}

	public static function human_filesize($bytes, $decimals = 2) {
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
	

	/*
	 * TODO: Moved to Utils from Config, Start using it.
	 */
	public static function generateCleanUsername($userid) {
		$username = preg_replace('/[^a-zA-Z0-9]+/', '_', $userid);
		return $username;
	}


	/**
	 *
	 * TODO: Moved to Utils. From Config. STart using it.
	 * 
	 * Generate random password.
	 * Borrowed from here: http://www.codemiles.com/php-tutorials/generate-password-using-php-t3120.html
	 * @var integer
	 */
	public static function  generateRandpassword($size=12, $power=7) {
		$vowels = 'aeuy';
		$randconstant = 'bdghjmnpqrstvz';
		if ($power & 1) {
			$randconstant .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($power & 2) {
			$vowels .= "AEUY";
		}
		if ($power & 4) {
			$randconstant .= '23456789';
		}
		if ($power & 8) {
			$randconstant .= '@#$%';
		}

		$Randpassword = '';
		$alt = time() % 2;
		for ($i = 0; $i < $size; $i++) {
			if ($alt == 1) {
				$Randpassword .= $randconstant[(rand() % strlen($randconstant))];
				$alt = 0;
			} else {
				$Randpassword .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $Randpassword;
	}



	// TODO: Moved from Config. Start using it...
	public static function getPath($path) {
		$base = dirname(dirname(__FILE__));
		return $base . '/' . $path;
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
	public static function redirect($url, $parameters = array()) {
		assert(is_string($url));
		assert(strlen($url) > 0);
		assert(is_array($parameters));

		/* Check for relative URL. */
		if(substr($url, 0, 1) === '/') {
			/* Prefix the URL with the url to the root of the
			 * website.
			 */
			$url = self::selfURLhost() . $url;
		}

		/* Verify that the URL is to a http or https site. */
		if (!preg_match('@^https?://@i', $url)) {
			throw new SimpleSAML_Error_Exception('Redirect to invalid URL: ' . $url);
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

		error_log("Redirecting user to : " . $url);

		/* End script execution. */
		exit;
	}


	public static function crypt_apr1_md5($plainpasswd) {
		$salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
		$len = strlen($plainpasswd);
		$text = $plainpasswd.'$apr1$'.$salt;
		$bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
		for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
		for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
		$bin = pack("H32", md5($text));
		for($i = 0; $i < 1000; $i++) {
			$new = ($i & 1) ? $plainpasswd : $bin;
			if ($i % 3) $new .= $salt;
			if ($i % 7) $new .= $plainpasswd;
			$new .= ($i & 1) ? $bin : $plainpasswd;
			$bin = pack("H32", md5($new));
		}
		for ($i = 0; $i < 5; $i++) {
			$k = $i + 6;
			$j = $i + 12;
			if ($j == 16) $j = 5;
			$tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
		}
		$tmp = chr(0).chr(0).$bin[11].$tmp;
		$tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
		return "$"."apr1"."$".$salt."$".$tmp;
	}


	/**
	 * Command line log function
	 * @param  string $header A colored header to show first
	 * @param  string $str    The rest of the string
	 * @return string         The encoded for terminal / CLI string
	 */
	public static function cliLog($header, $str) {

		$tag = "\033[0;35m" . sprintf("%18s ", $header) . "\033[0m";
		echo($tag . $str . "\n");
	}

	/**
	 * Encode username and password for use in .htpassword file.
	 * The latest version is prepared to be supported with the HTTP Digest
	 * Authentication method.
	 * @param  string $u Username
	 * @param  string $p Password
	 * @return string    Encoded string, representing one line in the .htpasswd file.
	 */
	public static function encodeUserPass($u, $p) {
		$hash = base64_encode(sha1($p, true));
		// return crypt(crypt($p, base64_encode($p)));
		// return crypt($p,rand(10000, 99999));
		$realm = 'UWAP';
		return $realm . ':' . md5($u . ':' . $realm . ':' .$p);
		// return Utils::crypt_apr1_md5($p);
		// return '{SHA}'.$hash;
	}

	
}