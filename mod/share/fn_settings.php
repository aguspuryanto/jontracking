<? 
	include ('../../init.php');
	include ('../../func/fn_common.php');
	
	if (isset($_GET['su']))
	{		
		$su = $_GET['su'];
		
		$q = "SELECT * FROM `gs_user_share_position` WHERE `su`='".$su."'";
		$r = mysqli_query($ms, $q);
		
		if ($row = mysqli_fetch_array($r))
		{
			if ($row['active'] == "true")
			{
				$user_id = $row['user_id'];
				$imeis = explode(",", $row['imei']);
				
				$user_data = getUserData($user_id);
				
				loadLanguage($user_data["language"], $user_data["units"]);

				for ($i = 0; $i < count($imeis); ++$i)
				{
					if(!checkUserToObjectPrivileges($user_id, $imeis[$i]))
					{
						die;
					}
				}
			}
			else
			{
				die;
			}
		}
		else
		{
			die;
		}
	}
	else
	{
		die;
	}
	
	if(@$_POST['cmd'] == 'load_server_data')
	{	
		$custom_maps = array();
		
		$q = "SELECT * FROM `gs_maps` ORDER BY `name` ASC";
		$r = mysqli_query($ms, $q);
		
		while($row=mysqli_fetch_array($r))
		{
			$map_id = $row['map_id'];
			$name = $row['name'];
			$active = $row['active'];
			$type = $row['type'];
			$url = $row['url'];
			$layers = $row['layers'];
			
			$layer_id = 'map_'.strtolower($name).'_'.$map_id;
			
			if ($active == 'true')
			{
				$custom_maps[] = array('layer_id' => $layer_id,'name' => $name, 'active' => $active, 'type' => $type, 'url' => $url, 'layers' => $layers);	
			}			
		}
		
		if (($gsValues['MAP_OSM'] == 'true') && ($user_data['privileges_map_osm'] == true)){ $map_osm = 'true'; } else { $map_osm = 'false'; }
		if (($gsValues['MAP_BING'] == 'true') && ($user_data['privileges_map_bing'] == true)){ $map_bing = 'true'; } else { $map_bing = 'false'; }
		if (($gsValues['MAP_GOOGLE'] == 'true') && ($user_data['privileges_map_google'] == true)){ $map_google = 'true'; } else { $map_google = 'false'; }
		if (($gsValues['MAP_GOOGLE_STREET_VIEW'] == 'true') && ($user_data['privileges_map_google_street_view'] == true)){ $map_google_street_view = 'true'; } else { $map_google_street_view = 'false'; }
		if (($gsValues['MAP_GOOGLE_TRAFFIC'] == 'true') && ($user_data['privileges_map_google_traffic'] == true)){ $map_google_traffic = 'true'; } else { $map_google_traffic = 'false'; }
		if (($gsValues['MAP_MAPBOX'] == 'true') && ($user_data['privileges_map_mapbox'] == true)){ $map_mapbox = 'true'; } else { $map_mapbox = 'false'; }
		if (($gsValues['MAP_ARCGIS'] == 'true') && ($user_data['privileges_map_arcgis'] == true)){ $map_arcgis = 'true'; } else { $map_arcgis = 'false'; }
		if (($gsValues['MAP_YANDEX'] == 'true') && ($user_data['privileges_map_yandex'] == true)){ $map_yandex = 'true'; } else { $map_yandex = 'false'; }
		
		$result = array('url_root' => $gsValues['URL_ROOT'],
						'map_custom' => $custom_maps,
						'map_osm' => $map_osm,
						'map_bing' => $map_bing,
						'map_google' => $map_google,
						'map_google_street_view' => $map_google_street_view,
						'map_google_traffic' => $map_google_traffic,
						'map_mapbox' => $map_mapbox,
						'map_arcgis' => $map_arcgis,
						'map_yandex' => $map_yandex,
						'map_bing_key' => $gsValues['MAP_BING_KEY'],
						'map_mapbox_key' => $gsValues['MAP_MAPBOX_KEY'],
						'map_arcgis_key' => $gsValues['MAP_ARCGIS_KEY'],
						'routing_osmr_service_url' => $gsValues['ROUTING_OSMR_SERVICE_URL'],
						'map_layer' => $gsValues['MAP_LAYER'],
						'map_zoom' => $gsValues['MAP_ZOOM'],
						'map_lat' => $gsValues['MAP_LAT'],
						'map_lng' => $gsValues['MAP_LNG'],
						'address_display_object_data_list' => $gsValues['ADDRESS_DISPLAY_OBJECT_DATA_LIST'],
						'address_display_event_data_list' => $gsValues['ADDRESS_DISPLAY_EVENT_DATA_LIST'],
						'address_display_history_route_data_list' => $gsValues['ADDRESS_DISPLAY_HISTORY_ROUTE_DATA_LIST']);
		
		echo json_encode($result);
		die;
	}
	
	if(@$_POST['cmd'] == 'load_user_data')
	{
		$result = array('map_is' => $user_data['map_is'],
						'datalist_items' => $user_data['datalist_items']);
		
		echo json_encode($result);
		die;
	}
?>