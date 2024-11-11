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
        
	if(@$_GET['cmd'] == 'load_maintenance_list')
	{
		$page = $_GET['page']; // get the requested page
		$limit = $_GET['rows']; // get how many rows we want to have into the grid
		$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
		$sord = $_GET['sord']; // get the direction
		$search = caseToUpper(@$_GET['s']); // get search
		
		if(!$sidx) $sidx =1;
		
		 // get records number	
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_object_services.name AS object_services_name,
				gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (".$_SESSION["privileges_imei"].")";
		}
		else
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_object_services.name AS object_services_name,
				gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (".getUserObjectIMEIs($user_id).")";
		}
		
		if ($search != '')
		{
			$q .= " AND (UPPER(gs_objects.name) LIKE '%$search%' OR UPPER(gs_object_services.name) LIKE '%$search%')";	
		}
		
		$r = mysqli_query($ms, $q);
		
		if (!$r){die;}
		
		$count = mysqli_num_rows($r);
		
		if ($count > 0)
		{
			$total_pages = ceil($count/$limit);
		}
		else
		{
			$total_pages = 1;
		}
		
		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
				
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_object_services.name AS object_services_name,
				gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (".$_SESSION["privileges_imei"].")";
		}
		else
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_object_services.name AS object_services_name,
				gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (".getUserObjectIMEIs($user_id).")";
		}
		
		if ($search != '')
		{
			$q .= " AND (UPPER(gs_objects.name) LIKE '%$search%' OR UPPER(gs_object_services.name) LIKE '%$search%')";	
		}
		
		// prevent injection attacks
		if (!isMySQLOrderByValid($sidx, $sord))
		{
			die;
		}
		
		if (!isMySQLLimitValid($start, $limit))
		{
			die;
		}
		
		$q .=  " ORDER BY $sidx $sord LIMIT $start, $limit";
		$r = mysqli_query($ms, $q);
		
		$response = new stdClass();
		$response->page = $page;
		$response->total = $total_pages;
		$response->records = $count;
		
		if ($r)
		{		
			$i=0;
			while($row = mysqli_fetch_array($r))
			{
				$service_id = $row['service_id'];
				$imei = $row['imei'];
				$object_name = $row['objects_name'];
				$name = $row['object_services_name'];
				
				$odometer = getObjectOdometer($imei);
				$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));
				
				$odometer_left = '-';
				
				if ($row['odo'] == 'true')
				{			    
					$row['odo_interval'] = floor(convDistanceUnits($row['odo_interval'], 'km', $_SESSION["unit_distance"]));
					$row['odo_last'] = floor(convDistanceUnits($row['odo_last'], 'km', $_SESSION["unit_distance"]));
					
					$odo_diff = $odometer - $row['odo_last'];
					$odo_diff = $row['odo_interval'] - $odo_diff;
					
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
				
				$engine_hours_left = '-';
				
				if ($row['engh'] == 'true')
				{
					$engh_diff = $engine_hours - $row['engh_last'];
					$engh_diff = $row['engh_interval'] - $engh_diff;
					
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
				
				$days = '-';
				$days_left = '-';
				
				if ($row['days'] == 'true')
				{
					$days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));					
					$days_diff = floor($days_diff/3600/24);
					$days = $days_diff;
					$days_diff = $row['days_interval'] - $days_diff;
					
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
				
				if (($row['odo_left'] == 'true') || ($row['engh_left'] == 'true') || ($row['days_left'] == 'true'))
				{
					$event = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$event = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				// set modify buttons
				$modify = '<a href="#" onclick="maintenanceObjectServiceProperties(\''.$imei.'\',\''.$service_id.'\');" title="'.$la['EDIT'].'"><img src="theme/images/edit.svg" />';
				$modify .= '</a><a href="#" onclick="maintenanceServiceDelete(\''.$service_id.'\');" title="'.$la['DELETE'].'"><img src="theme/images/remove3.svg" /></a>';
				// set row
				
				// set row
				$response->rows[$i]['id']=$service_id;
				$response->rows[$i]['cell']=array($object_name,$name,$odometer,$odometer_left,$engine_hours,$engine_hours_left,$days,$days_left,$event,$modify);
				$i++;
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($response);
		die;
	}
	
	if(@$_POST['cmd'] == 'save_service')
	{                
		$name = $_POST["name"];
		$imei = $_POST["imei"];
		$data_list = $_POST["data_list"];
		$popup = $_POST["popup"];
		$odo = $_POST["odo"];
		$odo_interval = $_POST["odo_interval"];
		$odo_last = $_POST["odo_last"];
		$engh = $_POST["engh"];
		$engh_interval = $_POST["engh_interval"];
		$engh_last = $_POST["engh_last"];
		$days = $_POST["days"];
		$days_interval = $_POST["days_interval"];
		$days_last = $_POST["days_last"];
		
		$odo_left = $_POST["odo_left"];
		$odo_left_num = $_POST["odo_left_num"];
		$engh_left = $_POST["engh_left"];
		$engh_left_num = $_POST["engh_left_num"];
		$days_left = $_POST["days_left"];
		$days_left_num = $_POST["days_left_num"];
		
		$update_last = $_POST["update_last"];
		
		// save in km
		$odo_interval = convDistanceUnits($odo_interval, $_SESSION["unit_distance"], 'km');
		$odo_last = convDistanceUnits($odo_last, $_SESSION["unit_distance"], 'km');
		$odo_left_num = convDistanceUnits($odo_left_num, $_SESSION["unit_distance"], 'km');
		
		$imeis = explode(',', $imei);
		
		for ($i = 0; $i < count($imeis); ++$i)
		{
			$q = "INSERT INTO `gs_object_services` 	(`imei`,
													`name`,
													`data_list`,
													`popup`,
													`odo`,
													`odo_interval`, 
													`odo_last`,
													`engh`,
													`engh_interval`,
													`engh_last`,
													`days`,
													`days_interval`,
													`days_last`,
													`odo_left`,
													`odo_left_num`,
													`engh_left`,
													`engh_left_num`,
													`days_left`,
													`days_left_num`,
													`update_last`)
													VALUES
													('".$imeis[$i]."',
													'".$name."',
													'".$data_list."',
													'".$popup."',
													'".$odo."',
													'".$odo_interval."',
													'".$odo_last."',
													'".$engh."',
													'".$engh_interval."',
													'".$engh_last."',
													'".$days."',
													'".$days_interval."',
													'".$days_last."',
													'".$odo_left."',
													'".$odo_left_num."',
													'".$engh_left."',
													'".$engh_left_num."',
													'".$days_left."',
													'".$days_left_num."',
													'".$update_last."')";
								
			$r = mysqli_query($ms, $q);
		}
		
		echo 'OK';
		die;
	}
	
        if(@$_POST['cmd'] == 'delete_service')
	{
		$service_id = $_POST["service_id"];
		
		$q = "DELETE FROM `gs_object_services` WHERE `service_id`='".$service_id."'";
		$r = mysqli_query($ms, $q);
		
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_selected_services')
	{
		$items = $_POST["items"];
		
		for ($i = 0; $i < count($items); ++$i)
		{
			$item = $items[$i];
			
			$q = "DELETE FROM `gs_object_services` WHERE `service_id`='".$item."'";
			$r = mysqli_query($ms, $q);
		}
		
		echo 'OK';
		die;
	}
?>