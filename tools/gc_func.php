<?	
	function geocoderGetAddress($lat, $lng)
	{
		global $ms, $gsValues;
		
		$result = '';
		
		if ($gsValues['GEOCODER_CACHE'] == 'true')
		{
			$result = getGeocoderCache($lat, $lng);
		}
		
		if ($result == '')
		{
			usleep(50000);
			
			$url = $gsValues['URL_ROOT'].'/tools/gc/'.$gsValues['GEOCODER_SERVICE'].'.php';	
			$url .= '?cmd=latlng&lat='.$lat.'&lng='.$lng;
			
			$context = stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 3), 'ssl' => array('verify_peer' => false)));
			$result = @file_get_contents($url, false, $context);
			$result = json_decode($result);
			
			if ($gsValues['GEOCODER_CACHE'] == 'true')
			{
				insertGeocoderCache($lat, $lng, $result);
			}
		}
		
		return $result;
	}
?>