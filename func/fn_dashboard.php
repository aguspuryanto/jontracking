<? 
	session_start();
	include ('../init.php');
	include ('fn_common.php');
	checkUserSession();
	
	loadLanguage($_SESSION["language"], $_SESSION["units"]);
	
	// check privileges
	if ($_SESSION["privileges"] == 'subuser')
	{
		$user_id = $_SESSION["manager_id"];
	}
	else
	{
		$user_id = $_SESSION["user_id"];
	}
        
    if(@$_POST['cmd'] == 'load_events_data')
	{
		$result = array();
		
		$period = $_POST['period'];
		$dtf = $_POST['dtf'];
		$dtt = $_POST['dtt'];
		
		if ($period == 'today')
		{
			if ($_SESSION["privileges"] == 'subuser')
			{
				$q = "SELECT * FROM `gs_user_last_events_data` WHERE `user_id`='".$user_id."' AND `imei` IN (".$_SESSION["privileges_imei"].")";
			}
			else
			{
				$q = "SELECT * FROM `gs_user_last_events_data` WHERE `user_id`='".$user_id."'";				
			}	
		}
		else
		{
			if ($_SESSION["privileges"] == 'subuser')
			{
				$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='".$user_id."' AND `imei` IN (".$_SESSION["privileges_imei"].")";
			}
			else
			{
				$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='".$user_id."'";				
			}	
		}
		
		$q .= " AND dt_tracker BETWEEN '".convUserUTCTimezone($dtf)."' AND '".convUserUTCTimezone($dtt)."'";
		$r = mysqli_query($ms, $q);
		
		while($row = mysqli_fetch_array($r))
		{				
			if (checkObjectActive($row['imei']) == true)
			{
				$type = $row['type'];
				
				if (isset($result[$type]))
				{
					$result[$type]['count'] += 1;
				}
				else
				{
					$result[$type] = array();
					$result[$type]['count'] = 1;
					
					$name = '';
					
					if ($type == 'sos')
					{
						$name = $la['SOS'];
					}
					else if ($type == 'bracon')
					{
						$name = $la['BRACELET_ON'];
					}						
					else if ($type == 'bracoff')
					{
						$name = $la['BRACELET_OFF'];
					}
					else if ($type == 'dismount')
					{
						$name = $la['DISMOUNT'];
					}
					else if ($type == 'door')
					{
						$name = $la['DOOR'];
					}
					else if ($type == 'mandown')
					{
						$name = $la['MAN_DOWN'];
					}
					else if ($type == 'shock')
					{
						$name = $la['SHOCK'];
					}
					else if ($type == 'tow')
					{
						$name = $la['TOW'];
					}
					else if ($type == 'pwrcut')
					{
						$name = $la['POWER_CUT'];
					}
					else if ($type == 'gpsantcut')
					{
						$name = $la['GPS_ANTENNA_CUT'];
					}
					else if ($type == 'jamming')
					{
						$name = $la['SIGNAL_JAMMING'];
					}
					else if ($type == 'lowdc')
					{
						$name = $la['LOW_DC'];
					}
					else if ($type == 'lowbat')
					{
						$name = $la['LOW_BATTERY'];
					}
					else if ($type == 'connyes')
					{
						$name = $la['CONNECTION_YES'];
					}
					else if ($type == 'connno')
					{
						$name = $la['CONNECTION_NO'];
					}
					else if ($type == 'gpsyes')
					{
						$name = $la['GPS_YES'];
					}
					else if ($type == 'gpsno')
					{
						$name = $la['GPS_NO'];
					}
					else if ($type == 'stopped')
					{
						$name = $la['STOPPED'];
					}
					else if ($type == 'moving')
					{
						$name = $la['MOVING'];
					}
					else if ($type == 'engidle')
					{
						$name = $la['ENGINE_IDLE'];
					}
					else if ($type == 'overspeed')
					{
						$name = $la['OVERSPEED'];
					}
					else if ($type == 'underspeed')
					{
						$name = $la['UNDERSPEED'];
					}
					else if ($type == 'haccel')
					{
						$name = $la['HARSH_ACCELERATION'];
					}
					else if ($type == 'hbrake')
					{
						$name = $la['HARSH_BRAKING'];
					}
					else if ($type == 'hcorn')
					{
						$name = $la['HARSH_CORNERING'];
					}
					else if ($type == 'driverch')
					{
						$name = $la['DRIVER_CHANGE'];
					}
					else if ($type == 'trailerch')
					{
						$name = $la['TRAILER_CHANGE'];
					}
					else if ($type == 'param')
					{
						$name = $la['PARAMETER'];
					}
					else if ($type == 'sensor')
					{
						$name = $la['SENSOR'];
					}
					else if ($type == 'service')
					{
						$name = $la['SERVICE'];
					}
					else if ($type == 'dtc')
					{
						$name = $la['DIAGNOSTIC_TROUBLE_CODES'];
					}
					else if ($type == 'route_in')
					{
						$name = $la['ROUTE_IN'];
					}
					else if ($type == 'route_out')
					{
						$name = $la['ROUTE_OUT'];
					}
					else if ($type == 'zone_in')
					{
						$name = $la['ZONE_IN'];
					}
					else if ($type == 'zone_out')
					{
						$name = $la['ZONE_OUT'];
					}		
					
					$result[$type]['name'] = $name;
				}
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
		die;	
	}
	
	if(@$_POST['cmd'] == 'load_maintenance_data')
	{
		$result = array();
		
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (".$_SESSION["privileges_imei"].")";
		}
		else
		{
			$q = "SELECT gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (".getUserObjectIMEIs($user_id).")";
		}
		
		$q .=  " ORDER BY gs_objects.name ASC";
		
		$r = mysqli_query($ms, $q);
		
		if ($r)
		{
			while($row = mysqli_fetch_array($r))
			{
				$service_id = $row['service_id'];
				$imei = $row['imei'];
				$object_name = getObjectName($imei);
				$name = $row['name'];
				
				$odometer = getObjectOdometer($imei);
				$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));
				
				$odometer_left_val = 0;
				$odometer_left = '-';
				
				if ($row['odo'] == 'true')
				{			    
					$row['odo_interval'] = floor(convDistanceUnits($row['odo_interval'], 'km', $_SESSION["unit_distance"]));
					$row['odo_last'] = floor(convDistanceUnits($row['odo_last'], 'km', $_SESSION["unit_distance"]));
					
					$odo_diff = $odometer - $row['odo_last'];
					$odo_diff = $row['odo_interval'] - $odo_diff;
					
					$odometer_left_val = $odo_diff;
					
					if ($odo_diff <= 0)
					{					
						$odo_diff = abs($odo_diff);
						$odometer_left = '<font color="red">'.$la["EXPIRED"].' ('.$odo_diff.' '.$la["UNIT_DISTANCE"].')</font>';
					}
					else
					{
						$odometer_left = $odo_diff.' '.$la["UNIT_DISTANCE"];
					}
				}
				
				$odometer = $odometer.' '.$la["UNIT_DISTANCE"];
				
				$engine_hours = getObjectEngineHours($imei, false);
				
				$engine_hours_left_val = 0;
				$engine_hours_left = '-';
				
				if ($row['engh'] == 'true')
				{
					$engh_diff = $engine_hours - $row['engh_last'];
					$engh_diff = $row['engh_interval'] - $engh_diff;
					
					$engine_hours_left_val = $engh_diff;
					
					if ($engh_diff <= 0)
					{					
						$engh_diff = abs($engh_diff);
						$engine_hours_left = '<font color="red">'.$la["EXPIRED"].' ('.$engh_diff.' '.$la["UNIT_H"].')</font>';
					}
					else
					{
						$engine_hours_left = $engh_diff.' '.$la["UNIT_H"];
					}
				}
				
				$engine_hours = $engine_hours.' '.$la["UNIT_H"];
				
				$days_left_val = 0;
				$days_left = '-';
				
				if ($row['days'] == 'true')
				{
					$days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));					
					$days_diff = floor($days_diff/3600/24);
					$days_diff = $row['days_interval'] - $days_diff;
					
					$days_left_val = $days_diff;
					
					if ($days_diff <= 0)
					{					
						$days_left = abs($days_diff);
						$days_left = '<font color="red">'.$la["EXPIRED"].' ('.$days_left.' '.$la["UNIT_D"].')</font>';
					}
					else
					{
						$days_left = $days_diff;
					}
				}
				
				$result[] = array('object_name' => $object_name, 'name' => $name, 'odometer_left' => $odometer_left, 'engine_hours_left' => $engine_hours_left, 'days_left' => $days_left, 'odometer_left_val' => $odometer_left_val, 'engine_hours_left_val' => $engine_hours_left_val, 'days_left_val' => $days_left_val);
			}
		}
		
		//usort($result, function($a,$b){
		//	$c = $a['odometer_left_val'] - $b['odometer_left_val'];
		//	$c .= $a['engine_hours_left_val'] - $b['engine_hours_left_val'];
		//	$c .= $a['days_left_val'] - $b['days_left_val'];
		//	return $c;
		//});
		
		header('Content-type: application/json');
		echo json_encode($result);
		die;
	}
	
	if(@$_POST['cmd'] == 'load_tasks_data')
	{
		$result = array();
		
		$dtf = $_POST['dtf'];
		$dtt = $_POST['dtt'];
		
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT * FROM `gs_object_tasks` WHERE `imei` IN (".$_SESSION["privileges_imei"].")";
		}
		else
		{
			$q = "SELECT * FROM `gs_object_tasks` WHERE `imei` IN (".getUserObjectIMEIs($user_id).")";
		}
		
		$q .= " AND dt_task BETWEEN '".convUserUTCTimezone($dtf)."' AND '".convUserUTCTimezone($dtt)."'";	
		$r = mysqli_query($ms, $q);
		
		if ($r)
		{
			while($row = mysqli_fetch_array($r))
			{				
				if (checkObjectActive($row['imei']) == true)
				{
					$status = $row['status'];
					
					if (isset($result[$status]))
					{
						$result[$status]['count'] += 1;
					}
					else
					{
						$result[$status] = array();
						$result[$status]['count'] = 1;
						
						$name = '';
						
						if ($status == 0)
						{
							$name = $la['NEW'];
						}
						else if ($status == 1)
						{
							$name = $la['IN_PROGRESS'];
						}
						else if ($status == 2)
						{
							$name = $la['COMPLETED'];
						}
						else if ($status == 3)
						{
							$name = $la['FAILED'];
						}
						
						$result[$status]['name'] = $name;
					}
				}
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
		die;	
	}
	
	if(@$_POST['cmd'] == 'load_odometer_data')
	{
		$result = array();
		
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$_SESSION["privileges_imei"].") ORDER BY `imei` ASC";
		}
		else
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' ORDER BY `imei` ASC";
		}
		
		$r = mysqli_query($ms, $q);
				
		while($row = mysqli_fetch_array($r))
		{
			$imei = $row['imei'];
			
			$q2 = "SELECT * FROM `gs_objects` WHERE `imei`='".$imei."'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);
			
			$row2['odometer'] = floor(convDistanceUnits($row2['odometer'], 'km', $_SESSION["unit_distance"]));
			
			if ($row2['active'] == 'true')
			{
				$result[] = array('name' => $row2['name'], 'odometer' => $row2['odometer']);
			}
		}
		
		usort($result, function($a,$b){
			$c = $b['odometer'] - $a['odometer'];
			return $c;
		});
		
		$result_top[] = array();
		
		if (count($result) > 10)
		{
			for ($i = 0; $i < 10; ++$i)
			{
				$result_top[] = $result[$i];
			}
			
			$result = $result_top;
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
		die;	
	}
	
	if(@$_POST['cmd'] == 'load_mileage_data')
	{
		$result = array();
		
		$curr_dt = gmdate("Y-m-d", strtotime(convUserIDTimezone($_SESSION['user_id'], gmdate("Y-m-d H:i:s"))));
				
		$result['mileage_dt_1'] = gmdate("m-d", strtotime($curr_dt));
		$result['mileage_dt_2'] = gmdate("m-d", strtotime($curr_dt.' - 1 day'));
		$result['mileage_dt_3'] = gmdate("m-d", strtotime($curr_dt.' - 2 day'));
		$result['mileage_dt_4'] = gmdate("m-d", strtotime($curr_dt.' - 3 day'));
		$result['mileage_dt_5'] = gmdate("m-d", strtotime($curr_dt.' - 4 day'));
		
		$result['mileage_1'] = 0;
		$result['mileage_2'] = 0;
		$result['mileage_3'] = 0;
		$result['mileage_4'] = 0;
		$result['mileage_5'] = 0;
		
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$_SESSION["privileges_imei"].") ORDER BY `imei` ASC";
		}
		else
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' ORDER BY `imei` ASC";
		}
		
		$r = mysqli_query($ms, $q);
		
		while($row = mysqli_fetch_array($r))
		{
			$imei = $row['imei'];
			
			$q2 = "SELECT * FROM `gs_objects` WHERE `imei`='".$imei."'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);
			
			if ($row2['active'] == 'true')
			{
				$result['mileage_1'] += $row2['mileage_1'];
				$result['mileage_2'] += $row2['mileage_2'];
				$result['mileage_3'] += $row2['mileage_3'];
				$result['mileage_4'] += $row2['mileage_4'];
				$result['mileage_5'] += $row2['mileage_5'];
			}
		}
		
		$result['mileage_1'] = floor(convDistanceUnits($result['mileage_1'], 'km', $_SESSION["unit_distance"]));
		$result['mileage_2'] = floor(convDistanceUnits($result['mileage_2'], 'km', $_SESSION["unit_distance"]));
		$result['mileage_3'] = floor(convDistanceUnits($result['mileage_3'], 'km', $_SESSION["unit_distance"]));
		$result['mileage_4'] = floor(convDistanceUnits($result['mileage_4'], 'km', $_SESSION["unit_distance"]));
		$result['mileage_5'] = floor(convDistanceUnits($result['mileage_5'], 'km', $_SESSION["unit_distance"]));
		
		if (($result['mileage_1'] == 0) && ($result['mileage_2'] == 0) && ($result['mileage_3'] == 0) && ($result['mileage_4'] == 0) && ($result['mileage_5'] == 0))
		{
			$result = array();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
		die;
	}
?>