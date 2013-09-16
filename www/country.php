<?php

class Store {
	
	protected $memcache;
	
	function __construct () {
		$this->memcache = new Memcache;
		$this->memcache->connect('localhost', 11211);
	}

	public function set($key, $value) {
		$this->memcache->set('co-' . $key, $value, false, 0);
	}

	public function get($key) {
		return $this->memcache->get('co-' . $key);
	}
	
	public function exists($key) {
		return ($this->memcache->get('co-' . $key) !== false);
	}
}


class CountryLookup {

	const CACHETIME = 86400; 

	
	/* Instance of sspmod_core_Storage_SQLPermanentStorage
	 * 
	 * key1		calendar URL
	 * key2		NULL
	 * type		'calendar'
	 *
	 */
	public $store;
	public $ip;
	
	public function __construct($ip = NULL) {
		if (is_null($ip)) $ip = $_SERVER['REMOTE_ADDR'];
		
		if (empty($ip))
			throw new Exception('Trying to use the TimeZone class without specifying an IP address');
		$this->ip = $ip;
		
		$this->store = new Store();

	}

	public function lookupRegion($region) {
		
		if ($this->store->exists('region-' . $region, NULL)) {
			error_log('IP Geo location: Found region [' . $region . '] in cache.');
			return $this->store->get('region-' . $region);
		}
		
		error_log('Lookup region');
		$rawdata = file_get_contents('http://freegeoip.net/tz/json/' . $region);
		
		if (empty($rawdata)) throw new Exception('Error looking up IP geo location for [' . $ip . ']');
		$data = json_decode($rawdata, TRUE);
		if (empty($data)) throw new Exception('Error decoding response from looking up IP geo location for [' . $ip . ']');
		
		if (empty($data['timezone'])) throw new Exception('Could not get TimeZone from IP lookup');
		
		$timezone = $data['timezone'];
		
		error_log('IP Geo location: Store region [' . $region . '] in cache: ' . $timezone);
		$this->store->set('region-' . $region, $timezone);
		
		return $timezone;	
	}
	
	public function getRegion() {
		return $this->lookupIP($this->ip);		
	}
	
	public function getGeo() {
		return $this->lookupGeo($this->ip);		
	}
	
	public function lookupGeo($ip) {

		if ($this->store->exists('geo-' . $ip)) {
			error_log('IP Geo location (geo): Found ip [' . $ip . '] in cache.');
			$stored =  $this->store->get('geo-' . $ip);
			if ($stored === NULL) throw new Exception('Got negative cache for this IP');
			return $stored;
		}
		
		error_log('Lookup IP');
		$rawdata = file_get_contents('http://freegeoip.net/json/' . $ip);
		
		if (empty($rawdata)) throw new Exception('Error looking up IP geo location for [' . $ip . ']');
		$data = json_decode($rawdata, TRUE);
		if (empty($data)) throw new Exception('Error decoding response from looking up IP geo location for [' . $ip . ']');
		
		if (empty($data['longitude'])) {
			$this->store->set('geo-' . $ip, NULL);
		}
		
		
		if (empty($data['longitude'])) throw new Exception('Could not get longitude from IP lookup');
		if (empty($data['latitude'])) throw new Exception( 'Could not get latitude from IP lookup');
		
		$geo = $data['latitude'] . ',' . $data['longitude'];
		
		error_log('IP Geo location: Store ip [' . $ip . '] in cache: ' . $geo);
		$this->store->set('geo-' . $ip, $geo);
		
		return $geo;
	}
	
	public function lookupIP($ip) {

		if ($this->store->exists('ip-' . $ip)) {
			error_log('IP Geo location: Found ip [' . $ip . '] in cache.');
			return $this->store->get('ip-'. $ip);
		}
		
		error_log('Lookup IP [' . $ip. ']');
		$rawdata = file_get_contents('http://freegeoip.net/json/' . $ip);
		
		if (empty($rawdata)) throw new Exception('Error looking up IP geo location for [' . $ip . ']');
		$data = json_decode($rawdata, TRUE);
		if (empty($data)) throw new Exception('Error decoding response from looking up IP geo location for [' . $ip . ']');
		
		error_log('Country code: ' . $data['country_code']);
		
		if (empty($data['country_code'])) throw new Exception('Could not get Coutry Code from IP lookup : ' . var_export($data, TRUE));
		if (empty($data['region_code'])) $region = 'NA';
		
		$region = $data['country_code'] . '/' . $data['region_code'];
		
		error_log('IP Geo location: Store ip [' . $ip . '] in cache: ' . $region);
		$this->store->set('ip-' . $ip, $region);
		
		return $region;
	}
	
	public function getTimeZone() {
		$tz = 'Europe/Amsterdam';
		
		try {
			$tz = $this->lookupRegion($this->lookupIP($this->ip));
		} catch(Exception $e) {
			$tz = 'Europe/Amsterdam';
		}
		
		return $tz;
	}
	

	

}



// ----- // ----- // -----   DEMO SECTION  // ----- // ----- // ----- // ----- 
$demo = array(
	array('country' => 'GB', 'geo' => array('lat' => 53.479874, 'lon' => -2.232971)), // Manchester, United Kingdom
	array('country' => 'IE', 'geo' => array('lat' => 54.946076, 'lon' => -7.734375)), // Letterkenny, Ireland
	array('country' => 'GB', 'geo' => array('lat' => 53.595765, 'lon' => -2.005005)), // Edinburgh,+United+Kingdom
	array('country' => 'GB', 'geo' => array('lat' => 55.950176, 'lon' => -3.187536)), // London, United Kingdom
	array('country' => 'NL', 'geo' => array('lat' => 51.500152, 'lon' => -0.126236)), // Amsterdam,+The+Netherlands
	array('country' => 'SE', 'geo' => array('lat' => 52.370216, 'lon' => 4.895168)), // stockholm,+sweden
	array('country' => 'FI', 'geo' => array('lat' => 59.332788, 'lon' => 18.064488)), // Helsinki, Finland
	array('country' => 'IT', 'geo' => array('lat' => 60.169812, 'lon' => 24.93824)), // Florence, Italy
	array('country' => 'ES', 'geo' => array('lat' => 43.768732, 'lon' => 11.256901)), // Barcelona, Spain
);


// $result = $demo[8];
// $result['status'] = 'ok';
// if(preg_match('/^[0-9A-Za-z_\-]+$/', $_REQUEST['callback'], $matches)) {
// 	header('Content-type: application/javascript; utf-8');
// 	echo $_REQUEST['callback'] . '(' . json_encode($result) . ');';
// } else {
// 	header('Content-type: application/json; utf-8');
// 	echo json_encode($result);
// }
// exit;
// ----- // ----- // ----- // ----- // ----- // ----- // ----- 





try {
	
	header('Pragma: no-cache');
	header('Cache-Control: no-cache, must-revalidate');

	$result = array('status' => 'ok');

	$c = new CountryLookup();
	$region = $c->getRegion();
	
	if (preg_match('|^(.*?)/(.*?)$|', $region, $matches)) {
		if (!empty($matches[1])) $result['country'] = $matches[1];
		if (!empty($matches[2])) $result['region'] = $matches[2];
	}
	
	$geo = $c->getGeo();

	if (preg_match('|^(.*?),(.*?)$|', $geo, $matches)) {
		$result['geo'] = array('lat' => (float) $matches[1], 'lon' => (float)$matches[2]);
	}



	if(preg_match('/^[0-9A-Za-z_\-]+$/', $_REQUEST['callback'], $matches)) {
		header('Content-type: application/javascript; utf-8');
		echo $_REQUEST['callback'] . '(' . json_encode($result) . ');';
	} else {
		header('Content-type: application/json; utf-8');
		echo json_encode($result);
	}

	
} catch(Exception $e) {
	
	echo json_encode(array('status' => 'error', 'error' => $e->getMessage()));
	
}