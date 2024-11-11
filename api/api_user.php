<?
    if (@$api_access != true) { die; }
        
    // split command and params
	$cmd = urldecode($cmd);
	$cmd = stripslashes($cmd);
	$cmd = str_getcsv($cmd, ",", '"');
	$command = @$cmd[0];
	$command = strtoupper($command);
	
	if ($command == 'USER_GET_OBJECTS')
    {
		// command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id."'";
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row = mysqli_fetch_array($r))
		{
			$imei = $row['imei'];
			
			$q2 = "SELECT * FROM `gs_objects` WHERE `imei`='".$imei."'";
			$r2 = mysqli_query($ms, $q2);
			
			$row2 = mysqli_fetch_array($r2);
			
			if ($row2)
			{
				$q3 = "SELECT * FROM `gs_object_custom_fields` WHERE `imei`='".$row2['imei']."' ORDER BY name ASC";
				$r3 = mysqli_query($ms, $q3);
				
				$custom_fields = array();
				
				while ($row3 = mysqli_fetch_array($r3))
				{
					$custom_fields[] = array('name' => $row3['name'], 'value' => $row3['value']);
				}
                
                if ($gsValues['SIM_NUMBER'] == 'false')
                {
                    $row2['sim_number'] = '';
                }
		
				$result[] = array(	'imei' => $row2['imei'],
									'protocol' => $row2['protocol'],
									'net_protocol' => $row2['net_protocol'],
									'ip' => $row2['ip'],
									'port' => $row2['port'],
									'active' => $row2['active'],
									'object_expire' => $row2['object_expire'],
									'object_expire_dt' => $row2['object_expire_dt'],
									'dt_server' => $row2['dt_server'],
									'dt_tracker' => $row2['dt_tracker'],
									'lat' => $row2['lat'],
									'lng' => $row2['lng'],
									'altitude' => $row2['altitude'],
									'angle' => $row2['angle'],
									'speed' => $row2['speed'],
									'params' => json_decode($row2['params'],true),
									'loc_valid' => $row2['loc_valid'],
									'dt_last_stop' => $row2['dt_last_stop'],
									'dt_last_idle' => $row2['dt_last_idle'],
									'dt_last_move' => $row2['dt_last_move'],
									'name' => $row2['name'],
									'device' => $row2['device'],
									'sim_number' => $row2['sim_number'],
									'model' => $row2['model'],
									'vin' => $row2['vin'],
									'plate_number' => $row2['plate_number'],
									'odometer' => $row2['odometer'],
									'engine_hours' => $row2['engine_hours'],
									'custom_fields' => $custom_fields
									);
			}
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	if ($command == 'OBJECT_GET_CMDS')
    {
		// command validation
        if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
        $imei = strtoupper($cmd[1]);
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
		
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `user_id`='".$user_id."' AND `imei`='".$imei."' AND `status`='0'";
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row = mysqli_fetch_array($r))
		{
			$result[] = array($row['cmd_id'], $row['type'], $row['cmd']);
			
			$q2 = "UPDATE `gs_object_cmd_exec` SET `status`='1' WHERE `cmd_id`='".$row["cmd_id"]."'";
			$r2 = mysqli_query($ms, $q2);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	if ($command == 'OBJECT_CMD_GPRS')
	{
		// command validation
		if (count($cmd) < 5)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
        $imei = strtoupper($cmd[1]);
		$name = $cmd[2];
		$type = $cmd[3];
		$cmd = $cmd[4];
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($name == '')
        {
            echo "ERROR: name can't be empty";
            die;
        }
        
        if ($type == '')
        {
            echo "ERROR: type can't be empty";
            die;
        }
        
        if ($cmd == '')
        {
            echo "ERROR: command can't be empty";
            die;
        }
		
		$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id."' AND `imei`='".$imei."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		if (!$row)
		{
            echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		$result = sendObjectGPRSCommand($user_id, $imei, $name, $type, $cmd);
        
        if ($result)
        {
            echo 'success';   
        }
        else
        {
            echo 'fail'; 
        }        
        die;
	}
	
	if ($command == 'OBJECT_CMD_SMS')
    {
        // command validation
        if (count($cmd) < 4)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
        $imei = strtoupper($cmd[1]);
		$name = $cmd[2];
		$cmd = $cmd[3];
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($name == '')
        {
            echo "ERROR: name can't be empty";
            die;
        }

        if ($cmd == '')
        {
            echo "ERROR: command can't be empty";
            die;
        }
		
		$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id."' AND `imei`='".$imei."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		if (!$row)
		{
            echo 'ERROR: no permission to access this IMEI'; 
			die;
		}
		
		$result = sendObjectSMSCommand($user_id, $imei, $name, $cmd);
        
        if ($result)
        {
            echo 'success';   
        }
        else
        {
            echo 'fail';
        }        
        die;
	}
        
    if ($command == 'OBJECT_GET_LOCATIONS')
    {
        // command validation
        if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
                
        // command parameters
		if ($cmd[1] == "*")
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."'";
			$r = mysqli_query($ms, $q);
		}
		else
		{
			$imeis = strtoupper($cmd[1]);
			$imeis = explode(';', $imeis);
			$imeis = implode('","', $imeis);
			$imeis = '"'.$imeis.'"';
			
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$imeis.")";
			$r = mysqli_query($ms, $q);
		}
        
        if ($cmd[1] == "")
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
		
        $result = array();
                
        while($row = mysqli_fetch_array($r))
		{
            $imei = $row['imei'];
                        
            $q2 = "SELECT * FROM `gs_objects` WHERE `imei`='".$imei."'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);
                        
			$result[$imei] = array( 'name' => $row2['name'],
									'dt_server' => $row2['dt_server'],
									'dt_tracker' => $row2['dt_tracker'],
									'lat' => $row2['lat'],
									'lng' => $row2['lng'],
									'altitude' => $row2['altitude'],
									'angle' => $row2['angle'],
									'speed' => $row2['speed'],
									'params' => json_decode($row2['params'],true),
									'loc_valid' => $row2['loc_valid']);        
		}
                
		header('Content-type: application/json');
		echo json_encode($result);
        die;
    }
	
	if ($command == 'OBJECT_GET_ROUTE')
    {
		// command validation
        if (count($cmd) < 5)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
                
        // command parameters
        $imei = strtoupper($cmd[1]);
		$dtf = $cmd[2];
		$dtt = $cmd[3];
		$min_stop_duration = $cmd[4];
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($dtf == '')
        {
            echo "ERROR: date and time from can't be empty";
            die;
        }
        
        if ($dtt == '')
        {
            echo "ERROR: date and time to can't be empty";
            die;
        }
        
        if ($min_stop_duration == '')
        {
            echo "ERROR: min. stop duration can't be empty";
            die;
        } 

		loadLanguage('english', 'km,l,c');
		
		$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id."' AND `imei`='".$imei."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		if (!$row)
		{
            echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		$result = getRoute($user_id, $imei, $dtf, $dtt, $min_stop_duration, true);
		
		$sstops = $result['stops'];
		$stops = array();
		for ($i = 0; $i < count($sstops); ++$i)
		{
			$stops[] = array(	'id_start' => $sstops[$i][0],
								'id_end' => $sstops[$i][1],
								'lat' => $sstops[$i][2],
								'lng' => $sstops[$i][3],
								'altitude' => $sstops[$i][4],
								'angle' => $sstops[$i][5],
								'speed' => 0,
								'dt_start'=> $sstops[$i][6],
								'dt_end' => $sstops[$i][7],
								'duration' => $sstops[$i][8],
								'fuel_consumption' => $sstops[$i][9],
								'fuel_cost' => $sstops[$i][10],
								'engine_idle' => $sstops[$i][11],
								'params' => $sstops[$i][12]);
		}
	
		$sdrives = $result['drives'];
		$drives = array();
		for ($i = 0; $i < count($sdrives); ++$i)
		{
			$drives[] = array(	'id_start_s' => $sdrives[$i][0],
								'id_start' => $sdrives[$i][1],
								'id_end' => $sdrives[$i][2],
								'dt_start_s' => $sdrives[$i][3],
								'dt_start' => $sdrives[$i][4],
								'dt_end' => $sdrives[$i][5],
								'duration' => $sdrives[$i][6],
								'route_length' => $sdrives[$i][7],
								'top_speed' => $sdrives[$i][8],
								'avg_speed'=> $sdrives[$i][9],
								'fuel_consumption' => $sdrives[$i][10],
								'fuel_cost' => $sdrives[$i][11],
								'engine_work' => $sdrives[$i][12],
								'fuel_consumption_per_100km' => $sdrives[$i][13],
								'fuel_consumption_mpg' => $sdrives[$i][14]);
		}
		
		$sevents = $result['events'];
		$events = array();
		for ($i = 0; $i < count($sevents); ++$i)
		{
			$events[] = array(	'event_desc' => $sevents[$i][0],
								'dt_tracker' => $sevents[$i][1],
								'lat' => $sevents[$i][2],
								'lng' => $sevents[$i][3],
								'altitude' => $sevents[$i][4],
								'angle' => $sevents[$i][5],
								'speed' => $sevents[$i][6],
								'params' => $sevents[$i][7]);
		}
		
		$result['stops'] = $stops;
		$result['drives'] = $drives;
		$result['events'] = $events;
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
    if ($command == 'OBJECT_GET_MESSAGES')
    {
        // command validation
        if (count($cmd) < 4)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
                
        // command parameters
        $imei = strtoupper($cmd[1]);
		$dtf = $cmd[2];
		$dtt = $cmd[3];
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($dtf == '')
        {
            echo "ERROR: date and time from can't be empty";
            die;
        }
        
        if ($dtt == '')
        {
            echo "ERROR: date and time to can't be empty";
            die;
        }
		
		$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id."' AND `imei`='".$imei."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		if (!$row)
		{
            echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		$result = array();
		
		$q = "SELECT DISTINCT	dt_tracker,
                                lat,
                                lng,
                                altitude,
                                angle,
                                speed,
                                params
                                FROM `gs_object_data_".$imei."` WHERE dt_tracker BETWEEN '".$dtf."' AND '".$dtt."' ORDER BY dt_tracker ASC";
					
		$r = mysqli_query($ms, $q);
		
		while($route_data=mysqli_fetch_array($r))
		{
			$route_data['params'] = json_decode($route_data['params'],true);
			
			$result[] = array(	$route_data['dt_tracker'],
                                $route_data['lat'],
                                $route_data['lng'],
                                $route_data['altitude'],
                                $route_data['angle'],
                                $route_data['speed'],
                                $route_data['params']);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	if ($command == 'OBJECT_GET_EVENTS')
    {
		// command validation
		if (count($cmd) < 4)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
		$imei = strtoupper($cmd[1]);
		$dtf = $cmd[2];
		$dtt = $cmd[3];
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($dtf == '')
        {
            echo "ERROR: date and time from can't be empty";
            die;
        }
        
        if ($dtt == '')
        {
            echo "ERROR: date and time to can't be empty";
            die;
        }
		
		$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id."' AND `imei`='".$imei."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		if (!$row)
		{
            echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		$result = array();
		
		$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='".$user_id."' AND `imei`='".$imei."' AND dt_tracker BETWEEN '".$dtf."' AND '".$dtt."' ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);
		
		while($event_data=mysqli_fetch_array($r))
		{
			$event_data['params'] = json_decode($event_data['params'],true);
			
			$result[] = array(	$event_data['type'],
								$event_data['event_desc'],
								$event_data['imei'],
								$event_data['name'],
								$event_data['dt_tracker'],
								$event_data['lat'],
								$event_data['lng'],
								$event_data['altitude'],
								$event_data['angle'],
								$event_data['speed'],
								$event_data['params']);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	if ($command == 'OBJECT_GET_LAST_EVENTS')
    {
		// command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		$result = array();
		
		$q = "SELECT * FROM `gs_user_last_events_data` WHERE `user_id`='".$user_id."' AND dt_server > DATE_SUB(UTC_DATE(), INTERVAL 1 DAY) ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);
		
		while($event_data=mysqli_fetch_array($r))
		{
			$event_data['params'] = json_decode($event_data['params'],true);
			
			$result[] = array(	$event_data['type'],
								$event_data['event_desc'],
								$event_data['imei'],
								$event_data['name'],
								$event_data['dt_tracker'],
								$event_data['lat'],
								$event_data['lng'],
								$event_data['altitude'],
								$event_data['angle'],
								$event_data['speed'],
								$event_data['params']);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
    
    if ($command == 'OBJECT_GET_LAST_EVENTS_7D')
    {
		// command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        $dtf = date('Y-m-d', strtotime(gmdate("Y-m-d"). ' - 7 days')).' 00:00:00';
        $dtt = date('Y-m-d', strtotime(gmdate("Y-m-d"). ' + 1 days')).' 00:00:00';        
        
		$result = array();
		
		$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='".$user_id."' AND dt_server BETWEEN '".$dtf."' AND '".$dtt."' ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);

		while($event_data=mysqli_fetch_array($r))
		{
			$event_data['params'] = json_decode($event_data['params'],true);
			
			$result[] = array(	$event_data['type'],
								$event_data['event_desc'],
								$event_data['imei'],
								$event_data['name'],
								$event_data['dt_tracker'],
								$event_data['lat'],
								$event_data['lng'],
								$event_data['altitude'],
								$event_data['angle'],
								$event_data['speed'],
								$event_data['params']);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}    
    
    if ($command == 'USER_GET_MARKERS')
    {
		// command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }		
		
		$q = "SELECT * FROM `gs_user_markers` WHERE `user_id`='".$user_id."' ORDER BY `marker_name` ASC";
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row=mysqli_fetch_array($r))
		{
			$marker_id = $row['marker_id'];			
			$result[$marker_id] = array(	'name' => $row['marker_name'],
											'desc' => $row['marker_desc'],
											'icon' => $row['marker_icon'],
											'visible' => $row['marker_visible'],
											'lat' => $row['marker_lat'],
											'lng' => $row['marker_lng'],
                                            'radius' => $row['marker_radius']
											);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	if ($command == 'USER_GET_ROUTES')
    {
		// command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }		
		
		$q = "SELECT * FROM `gs_user_routes` WHERE `user_id`='".$user_id."' ORDER BY `route_name` ASC";
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row=mysqli_fetch_array($r))
		{
			$route_id = $row['route_id'];			
			$result[$route_id] = array(	'name' => $row['route_name'],
										'color' => $row['route_color'],
										'visible' => $row['route_visible'],
										'name_visible' => $row['route_name_visible'],
										'deviation' => $row['route_deviation'],
										'points' => $row['route_points']
										);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	if ($command == 'USER_GET_ZONES')
    {
		// command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }		
		
		$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='".$user_id."' ORDER BY `zone_name` ASC";
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row=mysqli_fetch_array($r))
		{
			$zone_id = $row['zone_id'];
			$result[$zone_id] = array(	'name' => $row['zone_name'],
										'color' => $row['zone_color'],
										'visible' => $row['zone_visible'],
										'name_visible' => $row['zone_name_visible'],
										'area' => $row['zone_area'],
										'vertices' => $row['zone_vertices']
										);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
    
    if ($command == 'USER_GET_TASKS')
    {
		// command validation
		if (count($cmd) < 4)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
		$imei = strtoupper($cmd[1]);
		$dtf = $cmd[2];
		$dtt = $cmd[3];
        
        if ($imei == '')
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($dtf == '')
        {
            echo "ERROR: date and time from can't be empty";
            die;
        }
        
        if ($dtt == '')
        {
            echo "ERROR: date and time to can't be empty";
            die;
        }
        
        if ($cmd[1] == "*")
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."'";
			$r = mysqli_query($ms, $q);
		}
		else
		{
			$imeis = strtoupper($cmd[1]);
			$imeis = explode(';', $imeis);
			$imeis = implode('","', $imeis);
			$imeis = '"'.$imeis.'"';
			
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$imeis.")";
			$r = mysqli_query($ms, $q);
		}
        
        $imeis_array = array();                        
        while($row = mysqli_fetch_array($r))
		{
            $imeis_array[] = $row['imei'];
		}        
        $imeis = implode(",", $imeis_array);
		
		$result = array();
		
		$q = "SELECT * FROM `gs_object_tasks` WHERE `imei` IN (".$imeis.") AND dt_task BETWEEN '".$dtf."' AND '".$dtt."' ORDER BY dt_task ASC";
		$r = mysqli_query($ms, $q);
        
        if (!$r)
		{
			echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		while($row=mysqli_fetch_array($r))
		{
			$task_id = $row['task_id'];
			$result[$task_id] = array(	'name' => $row['name'],
                                        'imei' => $row['imei'],
                                        'object_name' => getObjectName($row['imei']),
										'dt_task' => $row['dt_task'],
                                        'delivered' => $row['delivered'],                                        
                                        'priority' => $row['priority'],
                                        'status' => $row['status'],
                                        'desc' => $row['desc'],
                                        'start_lat' => $row['start_lat'],
                                        'start_lng' => $row['start_lng'],
                                        'start_address' => $row['start_address'],
                                        'start_from_dt' => $row['start_from_dt'],
                                        'start_to_dt' => $row['start_to_dt'],
                                        'end_lat' => $row['end_lat'],
                                        'end_lng' => $row['end_lng'],
                                        'end_address' => $row['end_address'],
                                        'end_from_dt' => $row['end_from_dt'],
                                        'end_to_dt' => $row['end_to_dt']
										);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
    
    if ($command == 'USER_GET_RILOGBOOK')
    {
		// command validation
		if (count($cmd) < 4)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
		$imei = strtoupper($cmd[1]);
		$dtf = $cmd[2];
		$dtt = $cmd[3];
        
        if ($imei == '')
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($dtf == '')
        {
            echo "ERROR: date and time from can't be empty";
            die;
        }
        
        if ($dtt == '')
        {
            echo "ERROR: date and time to can't be empty";
            die;
        }
        
        if ($cmd[1] == "*")
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."'";
			$r = mysqli_query($ms, $q);
		}
		else
		{
			$imeis = strtoupper($cmd[1]);
			$imeis = explode(';', $imeis);
			$imeis = implode('","', $imeis);
			$imeis = '"'.$imeis.'"';
			
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$imeis.")";
			$r = mysqli_query($ms, $q);
		}
        
        $imeis_array = array();                        
        while($row = mysqli_fetch_array($r))
		{
            $imeis_array[] = $row['imei'];
		}        
        $imeis = implode(",", $imeis_array);
		
		$result = array();
		
		$q = "SELECT * FROM `gs_rilogbook_data` WHERE `imei` IN (".$imeis.") AND dt_tracker BETWEEN '".$dtf."' AND '".$dtt."' ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);
        
        if (!$r)
		{
			echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		while($row=mysqli_fetch_array($r))
		{
            $assign_data = array();
            $is_assign_data = false;
            
            $group = $row['group'];
            $assign_id = $row['assign_id'];
            
            if ($group == 'da')
            {
                $q2 = "SELECT * FROM `gs_user_object_drivers` WHERE `user_id`='".$user_id."' AND `driver_assign_id`='".$assign_id."'";
                $r2 = mysqli_query($ms, $q2);
                $row2 = mysqli_fetch_array($r2);
                
                if ($row2)
                {
                    $assign_data = array(   'name' => $row2['driver_name'],
                                            'assign_id' => $row2['driver_assign_id'],
                                            'idn' => $row2['driver_idn'],
                                            'address' => $row2['driver_address'],
                                            'phone' => $row2['driver_phone'],
                                            'email' => $row2['driver_email'],
                                            'desc' => $row2['driver_desc']
                                            );
                    
                    $is_assign_data = true;
                }
            }
            else if ($group == 'pa')
            {
                $q2 = "SELECT * FROM `gs_user_object_passengers` WHERE `user_id`='".$user_id."' AND `passenger_assign_id`='".$assign_id."'";
                $r2 = mysqli_query($ms, $q2);
                $row2 = mysqli_fetch_array($r2);
                
                if ($row2)
                {
                    $assign_data = array(   'name' => $row2['passenger_name'],
                                            'assign_id' => $row2['passenger_assign_id'],
                                            'idn' => $row2['passenger_idn'],
                                            'address' => $row2['passenger_address'],
                                            'phone' => $row2['passenger_phone'],
                                            'email' => $row2['passenger_email'],
                                            'desc' => $row2['passenger_desc']
                                            );
                    
                    $is_assign_data = true;
                }
            }
            else if ($group == 'ta')
            {
                $q2 = "SELECT * FROM `gs_user_object_trailers` WHERE `user_id`='".$user_id."' AND `trailer_assign_id`='".$assign_id."'";
                $r2 = mysqli_query($ms, $q2);
                $row2 = mysqli_fetch_array($r2);
                
                if ($row2)
                {
                    $assign_data = array(   'name' => $row2['trailer_name'],
                                            'assign_id' => $row2['trailer_assign_id'],
                                            'model' => $row2['trailer_model'],
                                            'vin' => $row2['trailer_vin'],
                                            'plate_number' => $row2['trailer_plate_number'],
                                            'desc' => $row2['trailer_desc']
                                            );
                    
                    $is_assign_data = true;
                }
            }
            
            if (!$is_assign_data)
            {
                $assign_data = array('assign_id' => $assign_id);
            }
            
			$rilogbook_id = $row['rilogbook_id'];
			$result[$rilogbook_id] = array( 'imei' => $row['imei'],
                                            'object_name' => getObjectName($row['imei']),
                                            'dt_tracker' => $row['dt_tracker'],
                                            'group' => $group,                                        
                                            'assign_data' => $assign_data,
                                            'lat' => $row['lat'],
                                            'lng' => $row['lng'],
                                            'address' => $row['address']
                                            );
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
    
    if ($command == 'USER_GET_DTC')
    {
		// command validation
		if (count($cmd) < 4)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
		$imei = strtoupper($cmd[1]);
		$dtf = $cmd[2];
		$dtt = $cmd[3];
        
        if ($imei == '')
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($dtf == '')
        {
            echo "ERROR: date and time from can't be empty";
            die;
        }
        
        if ($dtt == '')
        {
            echo "ERROR: date and time to can't be empty";
            die;
        }
        
        if ($cmd[1] == "*")
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."'";
			$r = mysqli_query($ms, $q);
		}
		else
		{
			$imeis = strtoupper($cmd[1]);
			$imeis = explode(';', $imeis);
			$imeis = implode('","', $imeis);
			$imeis = '"'.$imeis.'"';
			
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$imeis.")";
			$r = mysqli_query($ms, $q);
		}
        
        $imeis_array = array();                        
        while($row = mysqli_fetch_array($r))
		{
            $imeis_array[] = $row['imei'];
		}        
        $imeis = implode(",", $imeis_array);
		
		$result = array();
		
		$q = "SELECT * FROM `gs_dtc_data` WHERE `imei` IN (".$imeis.") AND dt_tracker BETWEEN '".$dtf."' AND '".$dtt."' ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);
        
        if (!$r)
		{
			echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		while($row=mysqli_fetch_array($r))
		{
			$dtc_id = $row['dtc_id'];
			$result[$dtc_id] = array(	'imei' => $row['imei'],
                                        'object_name' => getObjectName($row['imei']),
										'dt_tracker' => $row['dt_tracker'],
                                        'code' => $row['code'],                                        
                                        'lat' => $row['lat'],
                                        'lng' => $row['lng'],
                                        'address' => $row['address']
										);
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
    
    if ($command == 'USER_GET_MAINTENANCE')
    {
		// command validation
		if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
		$imei = strtoupper($cmd[1]);
        
        if ($imei == '')
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($cmd[1] == "*")
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."'";
			$r = mysqli_query($ms, $q);
		}
		else
		{
			$imeis = strtoupper($cmd[1]);
			$imeis = explode(';', $imeis);
			$imeis = implode('","', $imeis);
			$imeis = '"'.$imeis.'"';
			
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$imeis.")";
			$r = mysqli_query($ms, $q);
		}
        
        $imeis_array = array();                        
        while($row = mysqli_fetch_array($r))
		{
            $imeis_array[] = $row['imei'];
		}        
        $imeis = implode(",", $imeis_array);

		$result = array();
 
        $q = "SELECT gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (".$imeis.")
                ORDER BY gs_object_services.imei ASC";                
		$r = mysqli_query($ms, $q);
        
        if (!$r)
		{
			echo 'ERROR: no permission to access this IMEI';
			die;
		}
                
        while($row = mysqli_fetch_array($r))
        {
            $service_id = $row['service_id'];
            $imei = $row['imei'];
            $object_name = getObjectName($imei);
            $service_name = $row['name'];
            
            $odometer = getObjectOdometer($imei);            
            $odometer_left = '';
            
            if ($row['odo'] == 'true')
            {
                $odo_diff = $odometer - $row['odo_last'];
                $odometer_left = $row['odo_interval'] - $odo_diff;
            }
            
            $engine_hours = getObjectEngineHours($imei, false);            
            $engine_hours_left = '';
            
            if ($row['engh'] == 'true')
            {
                $engh_diff = $engine_hours - $row['engh_last'];
                $engine_hours_left = $row['engh_interval'] - $engh_diff;
            }
            
            $days = '';
            $days_left = '';
            
            if ($row['days'] == 'true')
            {
                $days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));					
                $days_diff = floor($days_diff/3600/24);
                $days = $days_diff;
                $days_left = $row['days_interval'] - $days_diff;
            }				
            
            if (($row['odo_left'] == 'true') || ($row['engh_left'] == 'true') || ($row['days_left'] == 'true'))
            {
                $event = true;
            }
            else
            {
                $event = false;
            }
            
			$result[$service_id] = array(	'imei' => $imei,
                                            'object_name' => $object_name,
                                            'service_name' => $service_name,
                                            'odometer' => $odometer,
                                            'odometer_left' => $odometer_left,
                                            'engine_hours' => $engine_hours,
                                            'engine_hours_left' => $engine_hours_left,
                                            'days' => $days,
                                            'days_left' => $days_left,
                                            'event' => $event
                                            );
        }
        
        header('Content-type: application/json');
        echo json_encode($result);
        die;
    }
    
    if ($command == 'USER_GET_EXPENSES')
    {
		// command validation
		if (count($cmd) < 4)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
		$imei = strtoupper($cmd[1]);
		$dtf = $cmd[2];
		$dtt = $cmd[3];
        
        if ($imei == '')
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($dtf == '')
        {
            echo "ERROR: date and time from can't be empty";
            die;
        }
        
        if ($dtt == '')
        {
            echo "ERROR: date and time to can't be empty";
            die;
        }
    
        if ($cmd[1] == "*")
		{
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."'";
			$r = mysqli_query($ms, $q);
		}
		else
		{
			$imeis = strtoupper($cmd[1]);
			$imeis = explode(';', $imeis);
			$imeis = implode('","', $imeis);
			$imeis = '"'.$imeis.'"';
			
			$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND `imei` IN (".$imeis.")";
			$r = mysqli_query($ms, $q);
		}
        
        $imeis_array = array();                        
        while($row = mysqli_fetch_array($r))
		{
            $imeis_array[] = $row['imei'];
		}        
        $imeis = implode(",", $imeis_array);
		
		$result = array();
		
		$q = "SELECT * FROM `gs_user_expenses` WHERE `imei` IN (".$imeis.") AND dt_expense BETWEEN '".$dtf."' AND '".$dtt."' ORDER BY dt_expense ASC";
		$r = mysqli_query($ms, $q);
        
        if (!$r)
		{
			echo 'ERROR: no permission to access this IMEI';
			die;
		}
		
		while($row=mysqli_fetch_array($r))
		{
			$expense_id = $row['expense_id'];
			$result[$expense_id] = array(	'name' => $row['name'],
                                            'imei' => $row['imei'],
                                            'object_name' => getObjectName($row['imei']),
                                            'dt_expense' => $row['dt_expense'],
                                            'quantity' => $row['quantity'],
                                            'cost' => $row['cost'],
                                            'supplier' => $row['supplier'],
                                            'buyer' => $row['buyer'],
                                            'odometer' => $row['odometer'],
                                            'engine_hours' => $row['engine_hours'],
                                            'desc' => $row['desc']
                                            );
		}
		
		header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	if ($command == 'GET_ADDRESS')
    {
        // command validation
        if (count($cmd) < 3)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
                
        // command parameters
        $lat = $cmd[1];
		$lng = $cmd[2];
        
        if ($lat == '')
        {
            echo "ERROR: lat can't be empty";
            die;
        }
        
        if ($lng == '')
        {
            echo "ERROR: lng can't be empty";
            die;
        }
		
		$result = '';
		
		if (($lat <> '') && ($lng <> ''))
		{
			$result = geocoderGetAddress($lat, $lng);	
		}
		
		header('Content-Type: text/html; charset=utf-8');
        echo $result;
        die;
	}

    /* API USER_REMOVE
     */
    if ($command == 'DELETE_USER')
    {
        // command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }

        // command parameters
        $id = @$_GET['id'];
		// $pwd = @$_GET['pwd'];

		$q = "SELECT * FROM `gs_users` WHERE `id`='".$id."' AND `api_key`='".($key)."' LIMIT 1";
		// $r = mysqli_query($ms, $q);        
        // Perform query
        if ($r1 = mysqli_query($ms, $q)) {
            // echo "Returned rows are: " . mysqli_num_rows($result);
            $q2 = "UPDATE `gs_users` SET `active`='false' WHERE `id`='".$id."'";
            $r2 = mysqli_query($ms, $q2);

            // Free result set
            // mysqli_free_result($r2);
        }
            
        // Free result set
        mysqli_free_result($r1);

        $result = array();
        $result['success'] = ($r1) ? true : true;
        $result['message'] = ($r1) ? 'Sukses menghapus user ' . $user_id . '!' : 'ERROR: unknown command (USER_REMOVE)' . $q;

        header('Content-type: application/json');
        echo json_encode($result);
        die;
    }
    
    echo 'ERROR: unknown command';
    die;
?>