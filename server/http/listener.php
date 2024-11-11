<?
ob_start();
echo "OK";
//header("Content-length: " . (string)ob_get_length());
ob_end_flush();

chdir('../');
include ('s_insert.php');
//$json = '';


$data = json_decode(file_get_contents("php://input"), true);

$position = $data['position'];
$device = $data['device'];

$speedFact = 1.852;
$speed = $position['speed'] * $speedFact;

$posId =$position['id'];
$deviceId =$position['deviceId'];
$protocol=$position['protocol'];
$serverTime =date_format(new DateTime($position['serverTime']),"Y-m-d H:i:s");
$deviceTime=date_format(new DateTime($position['deviceTime']),"Y-m-d H:i:s");
$fixTime =date_format(new DateTime($position['fixTime']), "Y-m-d H:i:s");
$outdated =$position['outdated'];
$valid=$position['valid'];
$latitude =(string)$position['latitude'];
$longitude =(string)$position['longitude'];
$altitude=(string)$position['altitude'];
$speed=(string)number_format((float)$speed, 1, '.', '');
$course=(string)$position['course'];
$address=$position['address'];
$accuracy=$position['accuracy'];
$network=$position['network'];
$posAttributes=$position['attributes'];

$deviceAttributes = $device["attributes"];
$groupId = $device["groupId"];
$name = $device["name"];
$uniqueId = $device["uniqueId"];
$status = $device["status"];

$paramValues = array();

$eventVal= '';

foreach ($posAttributes as $key => $value) {
    if(is_bool($value)){
        if($value){
            $value = 1;
        }else{
            $value = 0;
        }
    }

    if($key == "ignition"){
        //$eventVal = $value;
        $paramValues["acc"] = (string)$value;
        $paramValues[$key] = (string)$value;
    }else if($key == "alarm"){
        $eventVal = $value;
    }else{
        $paramValues[$key] = (string)$value;
    }

//    if($key == "alarm"){
//        $eventVal = $value;
//    }


    //        if($key == "power"){
    //            $eventVal = $value;
    //        }
    //        if($key == "lowPower"){
    //            $eventVal = $value;
    //        }
    //        if($key == "lowBattery"){
    //            $eventVal = $value;
    //        }
    //        if($key == "powerOff"){
    //            $eventVal = $value;
    //        }
    //        if($key == "powerOn"){
    //            $eventVal = $value;
    //        }
    if($key == "overspeed"){
        $eventVal = $value;
    }
    if($key == "geofence"){
        $eventVal = $value;
    }
    if($key == "geofenceEnter"){
        $eventVal = $value;
    }
    if($key == "geofenceExit"){
        $eventVal = $value;
    }
}

$loc = array();


if($valid) {
    $loc['op'] = 'loc';
    $loc['imei'] = $uniqueId;
    $loc['dt_tracker'] = $fixTime;
    $loc['dt_server'] = $serverTime;
    $loc['lat'] = $latitude;
    $loc['lng'] = $longitude;
    $loc['altitude'] = $altitude;
    $loc['speed'] = $speed;
    $loc['angle'] = $course;
    $loc['protocol'] = $protocol;
    $loc['net_protocol'] = 'tcp';
    $loc['params'] = paramsToArray(json_encode($paramValues));
    $loc['loc_valid'] = 1;
    $loc['event'] = $eventVal;
    $loc['ip'] = '';
    $loc['port'] = '';
    $loc['deviceId'] = $deviceId;
    $loc['status'] = $status;
    insert_db_loc($loc);
}else{
    $loc['op'] = 'noloc';
    $loc['imei'] = $uniqueId;
    $loc['dt_tracker'] = $fixTime;
    $loc['dt_server'] = $serverTime;
    $loc['protocol'] = $protocol;
    $loc['net_protocol'] = 'tcp';
    $loc['params'] = paramsToArray(json_encode($paramValues));
    $loc['event'] = $eventVal;
    $loc['ip'] = '';
    $loc['port'] = '';
    $loc['deviceId'] = $deviceId;
    $loc['status'] = $status;
    $loc['loc_valid'] = 0;
    insert_db_noloc($loc);
}


//
//	for ($i = 0; $i < count($data); ++$i)
//	{
//		$loc = $data[$i];
//
//		if (!isset($loc["imei"]))
//		{
//			continue;
//		}
//
//		$loc['dt_server'] = gmdate("Y-m-d H:i:s");
//		$loc['params'] = paramsToArray($loc['params']);
//
//		if (@$loc["op"] == "loc")
//		{
//			insert_db_loc($loc);
//		}
//		else if (@$loc["op"] == "noloc")
//		{
//			insert_db_noloc($loc);
//		}
//		else if (@$loc["op"] == "imgloc")
//		{
//			insert_db_imgloc($loc);
//		}
//	}

mysqli_close($ms);
die;
?>
