<?
	include ('../init.php');
	include ('../func/fn_common.php');
	
	if(@$_POST['cmd'] == 'latlng')
	{
		$result = '';
		
		$lat = $_POST["lat"];
		$lng = $_POST["lng"];
		
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
		
		echo json_encode($result);
	}
	
	if(@$_POST['cmd'] == 'address')
	{
		$result = '';
		$search = htmlentities(urlencode($_POST["search"]));
		
		$url = $gsValues['URL_ROOT'].'/tools/gc/'.$gsValues['GEOCODER_SERVICE'].'.php';	
		$url .= '?cmd=address&search='.$search;
		
		$opts = array('http' =>	array('method'  => 'GET'), 'ssl' => array('verify_peer' => false));
		$context  = stream_context_create($opts);
		$result = @file_get_contents($url, false, $context);
		
		echo $result;
	}
?>