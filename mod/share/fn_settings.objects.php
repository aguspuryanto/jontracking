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
	
	if(@$_POST['cmd'] == 'load_object_data')
	{
		$q = "SELECT gs_objects.*, gs_user_objects.*
			FROM gs_objects
			INNER JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei
			WHERE gs_user_objects.imei IN (".implode(",", $imeis).")";
		
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row = mysqli_fetch_array($r))
		{			
			$imei = $row['imei'];
			
			// get object sensor list
			$sensors = getObjectSensors($imei);
			
			// get object custom fields list
			$custom_fields = getObjectCustomFields($imei);
			
			// set default odometer and engine hours type if not set in DB
			if ($row['odometer_type'] == '')
			{
				$row['odometer_type'] = 'gps';
			}
			
			if ($row['engine_hours_type'] == '')
			{
				$row['engine_hours_type'] = 'acc';
			}
			
			// odometer and engine hours
			$row['odometer'] = floor(convDistanceUnits($row['odometer'], 'km', $user_data["unit_distance"]));
			
			$row['engine_hours'] = floor($row['engine_hours'] / 60 / 60);
			
			// map arrows
			$default = array(	'arrow_no_connection' => 'arrow_red',
								'arrow_stopped' => 'arrow_red',
								'arrow_moving' => 'arrow_green',
								'arrow_engine_idle' => 'off'
								);
			
			if (($row['map_arrows'] == '') || (json_decode($row['map_arrows'],true) == null))
			{
				$map_arrows = $default;
			}
			else
			{
				$map_arrows = json_decode($row['map_arrows'],true);
				
				if (!isset($map_arrows["arrow_no_connection"])) { $map_arrows["arrow_no_connection"] = $default["arrow_no_connection"]; }
				if (!isset($map_arrows["arrow_stopped"])) { $map_arrows["arrow_stopped"] = $default["arrow_stopped"]; }
				if (!isset($map_arrows["arrow_moving"])) { $map_arrows["arrow_moving"] = $default["arrow_moving"]; }
				if (!isset($map_arrows["arrow_engine_idle"])) { $map_arrows["arrow_engine_idle"] = $default["arrow_engine_idle"]; }
			}
			
			$result[$imei] = array(	$row['protocol'],
									$row['group_id'],
									$row['driver_id'],
									$row['trailer_id'],
									$row['name'],
									$row['icon'],
									$map_arrows,
									$row['map_icon'],
									$row['tail_color'],
									$row['tail_points'],
									$row['device'], 
									$row['sim_number'],
									$row['model'],
									$row['vin'],
									$row['plate_number'],
									$row['odometer_type'],
									$row['engine_hours_type'],
									$row['odometer'],
									$row['engine_hours'],
									false,
									$row['time_adj'],
									false,
									false,
									false,
									false,
									false,
									false,
									$sensors,
									false,
									$custom_fields,
									getParamsArray($row['params']),
									$row['active'],
									$row['object_expire'],
									$row['object_expire_dt']
									);
		}
		
		echo json_encode($result);
		die;
	}
?>