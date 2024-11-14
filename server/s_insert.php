<?
	//$_POST['net_protocol'] - tcp or udp
	//$_POST['protocol'] - device protocol, like coban, teltonika, xexun
	//$_POST['ip'] - IP address of GPS device
	//$_POST['port'] - PORT of GPS device
	//$_POST['imei'] - device 15 char ID
	//$_POST['dt_server'] - 0 UTC date and time in "YYYY-MM-DD HH-MM-SS" format
	//$_POST['dt_tracker'] - 0 UTC date and time in "YYYY-MM-DD HH-MM-SS" format
	//$_POST['lat'] - latitude with +/-
	//$_POST['lng'] - longitude with +/-
	//$_POST['altitude'] - in meters
	//$_POST['angle'] - in degree
	//$_POST['speed'] - in km/h
	//$_POST['loc_valid'] - 1 means valid location, 0 means not valid location
	//$_POST['params'] - stores array of params like acc, di, do, ai...
	//$_POST['event'] - possible events: sos, bracon, bracoff, dismount, disassem, door, mandown, shock, tow, pwrcut, gpscut, jamming, lowdc, lowbat, haccel, hbrake, hcorn

	include ('s_init.php');
	include ('s_events.php');
	include ('../func/fn_common.php');
	include ('../tools/gc_func.php');

	function insert_db_loc($loc)
	{
		global $ms;

		// format data
		$loc['imei'] = strtoupper(trim($loc['imei']));
		$loc['lat'] = (double)sprintf('%0.6f', $loc['lat']);
		$loc['lng'] = (double)sprintf('%0.6f', $loc['lng']);
		$loc['altitude'] = floor($loc['altitude']);
		$loc['angle'] = floor($loc['angle']);
		$loc['speed'] = floor($loc['speed']);
		$loc['protocol'] = strtolower($loc['protocol']);
		$loc['net_protocol'] = strtolower($loc['net_protocol']);

		//// India country lock
		//if (loc_filter_india($loc))
		//{
		//	die;
		//}

		// check for wrong IMEI
		if (!ctype_alnum($loc['imei']))
		{
			return false;
		}

		// check for wrong speed
		if ($loc['speed'] > 1000)
		{
			return false;
		}

		// check if object exists in system
		if (!checkObjectExistsSystem($loc['imei']))
		{
			insert_db_unused($loc);
			return false;
		}

		// apply GPS Roll Over fix
//		if ((substr($loc['dt_tracker'], 0, 4) > (gmdate('Y') + 10)) || (substr($loc['dt_tracker'], 0, 4) < (gmdate('Y') - 10)))
//		{
//			if (substr($loc['dt_tracker'], 5, 5) == gmdate('m-d'))
//			{
//				$loc['dt_tracker'] = gmdate('Y').substr($loc['dt_tracker'], 4, 15);
//			}
//			else
//			{
//				$loc['dt_tracker'] = gmdate("Y-m-d H:i:s");
//			}
//		}

		// check if dt_tracker is one day too far - skip coordinate
		if (strtotime($loc['dt_tracker']) >= strtotime(gmdate("Y-m-d H:i:s").' +1 days'))
		{
			return false;
		}

		// check if dt_tracker is at least one hour too far - set 0 UTC time
//		if (strtotime($loc['dt_tracker']) >= strtotime(gmdate("Y-m-d H:i:s").' +1 hours'))
//		{
//			$loc['dt_tracker'] = gmdate("Y-m-d H:i:s");
//		}

		// adjust GPS time
		$loc['dt_tracker'] = adjustObjectTime($loc['imei'], $loc['dt_tracker']);

		// get previous known location
		$loc_prev = get_gs_objects_data($loc['imei']);

		// forward loc data
//		if (($loc_prev['forward_loc_data'] == 'true') && ($loc_prev['forward_loc_data_imei'] != ''))
//		{
//			if (!checkObjectExistsSystem($loc_prev['forward_loc_data_imei']))
//			{
//				return false;
//			}
//
//			$loc['imei'] = $loc_prev['forward_loc_data_imei'];
//			$loc_prev = get_gs_objects_data($loc['imei']);
//		}

		// avoid incorrect lat and lng signs
//		if ($loc['lat'] != $loc_prev['lat'])
//		{
//			if (abs($loc['lat']) == abs($loc_prev['lat']))
//			{
//				return false;
//			}
//		}
//
//		if ($loc['lng'] != $loc_prev['lng'])
//		{
//			if (abs($loc['lng']) == abs($loc_prev['lng']))
//			{
//				return false;
//			}
//		}

		// accvirt
		//$loc = loc_accvirt($loc, $loc_prev);

		// set params BLE dt flag
		$is_ble = false;

		// merge params only if dt_tracker is newer
		if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
		{
			// check for params BLE and udpate flag
			foreach ($loc['params'] as $key => $value)
			{
				if (substr($key, 0, 3) == 'ble')
				{
					$is_ble = true;
					break;
				}
			}
			// remove params BLE if more than 5 minutes passed from last BLE packet
			if ((strtotime($loc_prev['dt_last_params_ble']) <= strtotime(gmdate("Y-m-d H:i:s")." - 2 minutes")) && ($is_ble == false))
			{
				$loc['params'] = mergeParamsBLE($loc_prev['params'], $loc['params'], false, true);
			}
			else
			{
				$loc['params'] = mergeParamsBLE($loc_prev['params'], $loc['params'], $is_ble, false);
			}

			// unassign driver if acc is off
			$loc = loc_unassign_driver($loc, $loc_prev);

			// check if there is any sensor values to ignore due to ignition off
			$loc = loc_ignore_acc($loc, $loc_prev);
		}

		// sort params array
		ksort($loc['params']);

		// check if location without valid lat and lng
		if ($loc['loc_valid'] == 0)
		{
			if (($loc['lat'] == 0) || ($loc['lng'] == 0))
			{
				if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
				{
					// add previous known location
					$loc['lat'] = $loc_prev['lat'];
					$loc['lng'] = $loc_prev['lng'];
					$loc['altitude'] = $loc_prev['altitude'];
					$loc['angle'] = $loc_prev['angle'];
					$loc['speed'] = 0;
				}
			}
		}

		// insert data into various services
		insert_db_objects($loc, $loc_prev, $is_ble);

		insert_db_status($loc, $loc_prev);

		insert_db_odo_engh($loc, $loc_prev);

		insert_db_ri($loc, $loc_prev);

		insert_db_dtc($loc);

		// check for duplicate locations
		if (loc_filter($loc, $loc_prev) == false)
		{
			insert_db_object_data($loc, $loc_prev);

			// check for local events if dt_tracker is newer, in other case only tracker events will be checked
			if (($loc['lat'] != 0) && ($loc['lng'] != 0))
			{
				// check for local events if dt_tracker is newer, in other case only tracker events will be checked
				if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
				{
					check_events($loc, $loc_prev, true, true, false);
				}
				else
				{
					check_events($loc, false, false, false, false);
				}
			}
		}
	}

	function insert_db_noloc($loc)
	{
		global $ms;

		// format data
		$loc['imei'] = strtoupper(trim($loc['imei']));
		$loc['protocol'] = strtolower($loc['protocol']);
		$loc['net_protocol'] = strtolower($loc['net_protocol']);

		// check for wrong IMEI
		if (!ctype_alnum($loc['imei']))
		{
			return false;
		}

		// check if object exists in system
		if (!checkObjectExistsSystem($loc['imei']))
		{
			return false;
		}

		// get previous known location
		$loc_prev = get_gs_objects_data($loc['imei']);

		// forward loc data
		if (($loc_prev['forward_loc_data'] == 'true') && ($loc_prev['forward_loc_data_imei'] != ''))
		{
			if (!checkObjectExistsSystem($loc_prev['forward_loc_data_imei']))
			{
				return false;
			}

			$loc['imei'] = $loc_prev['forward_loc_data_imei'];
			$loc_prev = get_gs_objects_data($loc['imei']);
		}

		// add previous known location
		$loc['dt_tracker'] = $loc_prev['dt_tracker'];
		$loc['lat'] = $loc_prev['lat'];
		$loc['lng'] = $loc_prev['lng'];
		$loc['altitude'] = $loc_prev['altitude'];
		$loc['angle'] = $loc_prev['angle'];

		// check speed for reset
		$loc['speed'] = $loc_prev['speed'];
		if ($loc['speed'] > 0)
		{
			$dt_difference = strtotime(gmdate("Y-m-d H:i:s")) - strtotime($loc['dt_tracker']);
			if($dt_difference >= 150)
			{
				$loc['speed'] = 0;
			}
		}

		$loc['loc_valid'] = $loc_prev['loc_valid'];

		// accvirt
		$loc = loc_accvirt($loc, $loc_prev);

		// set params BLE dt flag
		$is_ble = false;

		// check for params BLE and udpate flag
		foreach ($loc['params'] as $key => $value)
		{
			if (substr($key, 0, 3) == 'ble')
			{
				$is_ble = true;
				break;
			}
		}
		// remove params BLE if more than 5 minutes passed from last BLE packet
		if ((strtotime($loc_prev['dt_last_params_ble']) <= strtotime(gmdate("Y-m-d H:i:s")." - 2 minutes")) && ($is_ble == false))
		{
			$loc['params'] = mergeParamsBLE($loc_prev['params'], $loc['params'], false, true);
		}
		else
		{
			$loc['params'] = mergeParamsBLE($loc_prev['params'], $loc['params'], $is_ble, false);
		}

		// check if there is any sensor values to ignore due to ignition off
		$loc = loc_ignore_acc($loc, $loc_prev);

		// unassign driver if acc is off
		$loc = loc_unassign_driver($loc, $loc_prev);

		// sort params array
		ksort($loc['params']);

		$q = "UPDATE gs_objects SET 	`protocol`='".$loc['protocol']."',
										`net_protocol`='".$loc['net_protocol']."',
										`ip`='".$loc['ip']."',
										`port`='".$loc['port']."',
										`dt_server`='".$loc['dt_server']."',
										`speed`='".$loc['speed']."',
										`params`='".json_encode($loc['params'])."'";


		// add last params BLE dt
		if ($is_ble)
		{
			$q .= ",`dt_last_params_ble`='".gmdate("Y-m-d H:i:s")."'";
		}

		$q .= " WHERE imei='".$loc['imei']."'";

		$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

		// check if location exists
		if (($loc['lat'] != 0) && ($loc['lng'] != 0))
		{
			insert_db_status($loc, $loc_prev);

			insert_db_odo_engh($loc, $loc_prev);

			insert_db_ri($loc, $loc_prev);

			insert_db_dtc($loc);

			check_events($loc, $loc_prev, false, true, false);
		}
	}

	function insert_db_imgloc($loc)
	{
		global $ms, $gsValues;

		// format data
		$loc['imei'] = strtoupper(trim($loc['imei']));
		$loc['lat'] = (double)sprintf('%0.6f', $loc['lat']);
		$loc['lng'] = (double)sprintf('%0.6f', $loc['lng']);
		$loc['altitude'] = floor($loc['altitude']);
		$loc['angle'] = floor($loc['angle']);
		$loc['speed'] = floor($loc['speed']);
		$loc['protocol'] = strtolower($loc['protocol']);
		$loc['net_protocol'] = strtolower($loc['net_protocol']);

		// check for wrong IMEI
		if (!ctype_alnum($loc['imei']))
		{
			return false;
		}

		// check if object exists in system
		if (!checkObjectExistsSystem($loc['imei']))
		{
			return false;
		}

		if (($loc['lat'] == 0) || ($loc['lng'] == 0))
		{
			// get previous known location
			$loc_prev = get_gs_objects_data($loc['imei']);

			$loc['lat'] = $loc_prev['lat'];
			$loc['lng'] = $loc_prev['lng'];
			$loc['altitude'] = $loc_prev['altitude'];
			$loc['angle'] = $loc_prev['angle'];
			$loc['speed'] = $loc_prev['speed'];
		}

		$img_file = $loc['imei'].'_'.$loc['dt_server'].'.jpg';
		$img_file = str_replace('-', '', $img_file);
		$img_file = str_replace(':', '', $img_file);
		$img_file = str_replace(' ', '_', $img_file);

		// save to database
		$q = "INSERT INTO gs_object_img (img_file,
										imei,
										dt_server,
										dt_tracker,
										lat,
										lng,
										altitude,
										angle,
										speed,
										deviceId,
										params
										) VALUES (
										'".$img_file."',
										'".$loc['imei']."',
										'".$loc['dt_server']."',
										'".$loc['dt_tracker']."',
										'".$loc['lat']."',
										'".$loc['lng']."',
										'".$loc['altitude']."',
										'".$loc['angle']."',
										'".$loc['speed']."',
										'".$loc['deviceId']."',
										'".json_encode($loc['params'])."')";

		$r = mysqli_query($ms, $q);

		// save last img file for every IMEI
		$q = "UPDATE `gs_objects` SET `last_img_file`='".$img_file."' WHERE `imei`='".$loc['imei']."'";
		$r = mysqli_query($ms, $q);

		 // save file
		$img_path = $gsValues['PATH_ROOT'].'/data/img/';
		$img_path = $img_path.basename($img_file);

		if (!isFilePathValid($img_path))
		{
			die;
		}

		$postdata = hex2bin($loc["img"]);

		if(substr($postdata,0,3) == "\xFF\xD8\xFF")
		{
			$fp = fopen($img_path,"w");
			fwrite($fp,$postdata);
			fclose($fp);
		}
	}

	function insert_db_unused($loc)
	{
		global $ms;

		$q = "INSERT INTO `gs_objects_unused` (imei, protocol, net_protocol, ip, port, dt_server, count)
						VALUES ('".$loc['imei']."', '".$loc['protocol']."', '".$loc['net_protocol']."', '".$loc['ip']."', '".$loc['port']."', '".$loc['dt_server']."', '1')
						ON DUPLICATE KEY UPDATE protocol = '".$loc['protocol']."', net_protocol = '".$loc['net_protocol']."', ip = '".$loc['ip']."', port = '".$loc['port']."', dt_server = '".$loc['dt_server']."', count = count + 1";
		$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
	}

	function insert_db_objects($loc, $loc_prev, $is_ble)
	{
		global $ms;

		if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
		{
			if ($loc['loc_valid'] == 1)
			{
				// calculate angle
				if ($loc['angle'] == 0)
				{
					$loc['angle'] = getAngle($loc_prev['lat'], $loc_prev['lng'], $loc['lat'], $loc['lng']);
				}

				$q = "UPDATE gs_objects SET	`protocol`='".$loc['protocol']."',
											`net_protocol`='".$loc['net_protocol']."',
											`ip`='".$loc['ip']."',
											`port`='".$loc['port']."',
											`dt_server`='".$loc['dt_server']."',
											`dt_tracker`='".$loc['dt_tracker']."',
											`lat`='".$loc['lat']."',
											`lng`='".$loc['lng']."',
											`altitude`='".$loc['altitude']."',
											`angle`='".$loc['angle']."',
											`speed`='".$loc['speed']."',
											`deviceId`='".$loc['deviceId']."',
											`loc_valid`='1',
											`params`='".json_encode($loc['params'])."'";

				// add last params BLE dt
				if ($is_ble)
				{
					$q .= ",`dt_last_params_ble`='".gmdate("Y-m-d H:i:s")."'";
				}

				$q .= " WHERE imei='".$loc['imei']."'";

				$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
			}
			else
			{
				$loc['speed'] = 0;

				if (($loc['lat'] != 0) && ($loc['lng'] != 0))
				{
					$q = "UPDATE gs_objects SET	`protocol`='".$loc['protocol']."',
												`net_protocol`='".$loc['net_protocol']."',
												`ip`='".$loc['ip']."',
												`port`='".$loc['port']."',
												`dt_server`='".$loc['dt_server']."',
												`dt_tracker`='".$loc['dt_tracker']."',
												`lat`='".$loc['lat']."',
												`lng`='".$loc['lng']."',
												`altitude`='".$loc['altitude']."',
												`angle`='".$loc['angle']."',
												`speed`='".$loc['speed']."',
											    `deviceId`='".$loc['deviceId']."',
												`loc_valid`='0',
												`params`='".json_encode($loc['params'])."'";

					// add last params BLE dt
					if ($is_ble)
					{
						$q .= ",`dt_last_params_ble`='".gmdate("Y-m-d H:i:s")."'";
					}

					$q .= " WHERE imei='".$loc['imei']."'";

				}
				else
				{
					$q = "UPDATE gs_objects SET `protocol`='".$loc['protocol']."',
												`net_protocol`='".$loc['net_protocol']."',
												`ip`='".$loc['ip']."',
												`port`='".$loc['port']."',
												`dt_server`='".$loc['dt_server']."',
												`dt_tracker`='".$loc['dt_tracker']."',
												`speed`='".$loc['speed']."',
											    `deviceId`='".$loc['deviceId']."',
												`loc_valid`='0',
												`params`='".json_encode($loc['params'])."'";

					// add last params BLE dt
					if ($is_ble)
					{
						$q .= ",`dt_last_params_ble`='".gmdate("Y-m-d H:i:s")."'";
					}

					$q .= " WHERE imei='".$loc['imei']."'";
				}

				$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
			}
		}
		else
		{
			$q = "UPDATE gs_objects SET 	`protocol`='".$loc['protocol']."',
											`net_protocol`='".$loc['net_protocol']."',
											`ip`='".$loc['ip']."',
											`port`='".$loc['port']."',
											`dt_server`='".$loc['dt_server']."'
											WHERE imei='".$loc['imei']."'";

			$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
		}
	}

	function insert_db_object_data($loc, $loc_prev)
	{
		global $ms;

		$dt_difference = strtotime($loc['dt_server']) - strtotime($loc_prev['dt_server']);

		if (($loc['lat'] != 0) && ($loc['lng'] != 0))
		{
			$q = "INSERT INTO gs_object_data_".$loc['imei']."(	dt_server,
																dt_tracker,
																lat,
																lng,
																altitude,
																angle,
																speed,
																params
																) VALUES (
																'".$loc['dt_server']."',
																'".$loc['dt_tracker']."',
																'".$loc['lat']."',
																'".$loc['lng']."',
																'".$loc['altitude']."',
																'".$loc['angle']."',
																'".$loc['speed']."',
																'".json_encode($loc['params'])."'
																)";

			$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
		}
	}

function insert_db_status($loc, $loc_prev)
{
	global $ms;

	if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
	{
		$imei = $loc['imei'];
		$params = json_encode($loc['params']);
		$status = $loc['status'];

		$dt_last_stop = strtotime($loc_prev['dt_last_stop']);
		$dt_last_idle = strtotime($loc_prev['dt_last_idle']);
		$dt_last_move = strtotime($loc_prev['dt_last_move']);


		$sensor = json_decode($params, true);

	    $dt_now = gmdate("Y-m-d H:i:s");
//
//		$objStatus = "SELECT * FROM gs_object_status WHERE imei='" . $imei . "'";
//		$objStatusQuery = mysqli_query($ms, $objStatus) or die(mysqli_error($ms));
//		$objStatusRes = mysqli_fetch_array($objStatusQuery);

		if ($status == "online") {
			if (isset($sensor['acc'])) {
//				$q1 = "SELECT count(*) as total from gs_object_status WHERE imei='" . $imei . "'";
//				$result = mysqli_query($ms, $q1);
//				$checkImei = mysqli_fetch_assoc($result);
//				if ($checkImei['total'] == 0) {
//					$q = "INSERT INTO `gs_object_status` (`imei`, `move`, `idle`, `stop`) VALUES ('" . $imei . "', '0', '0', '0')";
//					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
//				} else {
//
//				}


				//status moving
				if (($dt_last_stop >= $dt_last_move) && ($loc['speed'] > 1)) {
					if ($status == "online" && $sensor['acc'] == 1 && $sensor['motion'] == 1) {
						$q = "UPDATE gs_objects SET `dt_last_move`='" . $loc['dt_server'] . "' WHERE imei='" . $imei . "'";
						$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

						$dt_last_move = strtotime($loc['dt_tracker']);
					}
				}


				//status moving
				if (($dt_last_stop >= $dt_last_move) && ($loc['speed'] > 1)) {
					if ($status == "online" && $sensor['acc'] == 0 && $sensor['motion'] == 1 && $loc['speed'] > 1) {

						$dt_difference = strtotime($dt_now) - strtotime($loc['dt_tracker']);
						if ($dt_difference > 5 * 60) {
							$q = "UPDATE gs_objects SET `dt_last_stop`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
							$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

							$dt_last_stop = strtotime($loc['dt_tracker']);
						} else if ($dt_difference > 60) {
							$q = "UPDATE gs_objects SET `dt_last_idle`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
							$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
						} else {
							$q = "UPDATE gs_objects SET `dt_last_move`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
							$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

							$dt_last_move = strtotime($loc['dt_tracker']);
						}
					}
				}

				//status stop
				if ($status == "online" && $sensor['acc'] == 0 && $sensor['motion'] == 0) {

					$q = "UPDATE gs_objects SET `dt_last_stop`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}

				//status Idle
				if ($status == "online" && $sensor['acc'] == 0 && $sensor['motion'] == 1 && $loc['speed'] < 2) {
					$q = "UPDATE gs_objects SET `dt_last_idle`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}

				//status Idle
				if ($status == "online" && $sensor['acc'] == 1 && $sensor['motion'] == 0) {
					$q = "UPDATE gs_objects SET `dt_last_idle`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}

				// Add new condition to update dt_last_stop
				if ($status == "online" && $sensor['acc'] == 1 && $sensor['motion'] == 0) {
					$q = "UPDATE gs_objects SET `dt_last_stop`=NOW() WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}
				
				// Update dt_last_stop when acc is active and dt_last_idle is set
				if ($status == "online" && $sensor['acc'] == 1 && $dt_last_idle != '') {
					$q = "UPDATE gs_objects SET `dt_last_stop`=NOW() WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}

			} else {
				$q1 = "SELECT count(*) as total from gs_object_status WHERE imei='" . $imei . "'";
				$result = mysqli_query($ms, $q1);
				$checkImei = mysqli_fetch_assoc($result);
				if ($checkImei['total'] == 0) {
					$q = "INSERT INTO `gs_object_status` (`imei`, `move`, `idle`, `stop`) VALUES ('" . $imei . "', '0', '0', '0')";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				} else {

				}
				//status Move
				if (($dt_last_stop >= $dt_last_move) && ($loc['speed'] > 1)) {
					if ($status == "online" && $sensor['motion'] == 1) {

						$dt_difference = strtotime($dt_now) - strtotime($loc['dt_tracker']);

						if ($dt_difference > 5 * 60) {
							$q = "UPDATE gs_objects SET `dt_last_stop`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
							$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

							$dt_last_stop = strtotime($loc['dt_tracker']);
						} else if ($dt_difference > 60) {
							$q = "UPDATE gs_objects SET `dt_last_idle`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
							$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
						} else {
							$q = "UPDATE gs_objects SET `dt_last_move`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
							$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

							$dt_last_move = strtotime($loc['dt_tracker']);
						}
					}
				}

				//status stop
				if ($status == "online" && $sensor['motion'] == 0) {
					$q = "UPDATE gs_objects SET `dt_last_stop`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

					$dt_last_stop = strtotime($loc['dt_tracker']);
				}

				// Add new condition to update dt_last_stop
				if ($status == "online" && $sensor['motion'] == 0) {
					$q = "UPDATE gs_objects SET `dt_last_stop`=NOW() WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}
				
				// Update dt_last_stop when acc is active and dt_last_idle is set
				if ($status == "online" && $dt_last_idle != '') {
					$q = "UPDATE gs_objects SET `dt_last_stop`=NOW() WHERE imei='" . $imei . "'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}
			}
		}else{
			if ($status == "offline") {
				$q = "UPDATE gs_objects SET `dt_last_offline`='" . $loc['dt_tracker'] . "' WHERE imei='" . $imei . "'";
				$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
			}
		}
	}
}

	function insert_db_status2($loc, $loc_prev)
	{
		global $ms;

		if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
		{
			$imei = $loc['imei'];
			$params = $loc['params'];

			$dt_last_stop = strtotime($loc_prev['dt_last_stop']);
			$dt_last_idle = strtotime($loc_prev['dt_last_idle']);
			$dt_last_move = strtotime($loc_prev['dt_last_move']);

			if ($loc['loc_valid'] == 1)
			{
				// status stop
				if ((($dt_last_stop <= 0) || ($dt_last_stop < $dt_last_move)) && ($loc['speed'] == 0))
				{
					$q = "UPDATE gs_objects SET `dt_last_stop`='".$loc['dt_server']."' WHERE imei='".$imei."'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

					$dt_last_stop = strtotime($loc['dt_server']);
				}

				// status moving
				if (($dt_last_stop >= $dt_last_move) && ($loc['speed'] > 0))
				{
					$q = "UPDATE gs_objects SET `dt_last_move`='".$loc['dt_server']."' WHERE imei='".$imei."'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

					$dt_last_move = strtotime($loc['dt_server']);
				}
			}
			else
			{
				// status stop
				if ((($dt_last_stop <= 0) || ($dt_last_stop < $dt_last_move)) && ($loc['speed'] == 0))
				{
					$q = "UPDATE gs_objects SET `dt_last_stop`='".$loc['dt_server']."' WHERE imei='".$imei."'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));

					$dt_last_stop = strtotime($loc['dt_server']);
				}
			}

			// status idle
			if ($dt_last_stop >= $dt_last_move)
			{
				$sensor = getSensorFromType($imei, 'acc');
				$acc = $sensor[0]['param'];

				if (isset($params[$acc]))
				{
					if (($params[$acc] == 1) && ($dt_last_idle <= 0))
					{
						$q = "UPDATE gs_objects SET `dt_last_idle`='".$loc['dt_server']."' WHERE imei='".$imei."'";
						$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
					}
					else if (($params[$acc] == 0) && ($dt_last_idle > 0))
					{
						$q = "UPDATE gs_objects SET `dt_last_idle`='0000-00-00 00:00:00' WHERE imei='".$imei."'";
						$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
					}
				}
			}
			else
			{
				if ($dt_last_idle > 0)
				{
					$q = "UPDATE gs_objects SET `dt_last_idle`='0000-00-00 00:00:00' WHERE imei='".$imei."'";
					$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
				}
			}
		}
	}

	function insert_db_odo_engh($loc, $loc_prev)
	{
		global $ms;

		$imei = $loc['imei'];
		$params = $loc['params'];
		$params_prev = $loc_prev['params'];

		// odo gps
		if ($loc_prev['odometer_type'] == 'gps')
		{
			if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
			{
				if ($loc['loc_valid'] == 1)
				{
					if (($loc_prev['lat'] != 0) && ($loc_prev['lng'] != 0) && ($loc['speed'] > 3))
					{
						$odometer = getLengthBetweenCoordinates($loc_prev['lat'], $loc_prev['lng'], $loc['lat'], $loc['lng']);

						if ($odometer > 0)
						{
							$q = 'UPDATE gs_objects SET `odometer` = odometer + '.$odometer.' WHERE imei="'.$imei.'"';
							$r = mysqli_query($ms, $q);

							// dashboard/mileage
							$q = 'UPDATE gs_objects SET `mileage_1` = mileage_1 + '.$odometer.' WHERE imei="'.$imei.'"';
							$r = mysqli_query($ms, $q);
						}
					}
				}
			}
		}

		// odo sen
		if ($loc_prev['odometer_type'] == 'sen')
		{
			$sensor = getSensorFromType($imei, 'odo');

			if ($sensor != false)
			{
				$sensor_ = $sensor[0];

				$odo = getSensorValue($params, $sensor_);

				$result_type = $sensor_['result_type'];

				if ($result_type == 'abs')
				{
					if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
					{
						$q = 'UPDATE gs_objects SET `odometer` = '.$odo['value'].' WHERE imei="'.$imei.'"';
						$r = mysqli_query($ms, $q);

						// dashboard/mileage
						$mileage_1 = $odo['value'] - $loc_prev['odometer'];
						if ($mileage_1 > 0)
						{
							$q = 'UPDATE gs_objects SET `mileage_1` = mileage_1 + '.$mileage_1.' WHERE imei="'.$imei.'"';
							$r = mysqli_query($ms, $q);
						}
					}
				}

				if ($result_type == 'rel')
				{
					if ($odo['value'] > 0)
					{
						$q = 'UPDATE gs_objects SET `odometer` = odometer + '.$odo['value'].' WHERE imei="'.$imei.'"';
						$r = mysqli_query($ms, $q);

						// dashboard/mileage
						$q = 'UPDATE gs_objects SET `mileage_1` = mileage_1 + '.$odo['value'].' WHERE imei="'.$imei.'"';
						$r = mysqli_query($ms, $q);
					}
				}
			}
		}

		// engh acc
		if ($loc_prev['engine_hours_type'] == 'acc')
		{
			if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
			{
				if ($loc['loc_valid'] == 1)
				{
					if ((strtotime($loc['dt_tracker']) > 0) && (strtotime($loc_prev['dt_tracker']) > 0))
					{
						$engine_hours = 0;

						// get ACC sensor
						$sensor = getSensorFromType($imei, 'acc');
						$acc = $sensor[0]['param'];

						// calculate engine hours from ACC
						$dt_tracker = $loc['dt_tracker'];
						$dt_tracker_prev = $loc_prev['dt_tracker'];

						if (isset($params_prev[$acc]) && isset($params[$acc]))
						{
							if (($params_prev[$acc] == '1') && ($params[$acc] == '1'))
							{
								$engine_hours = strtotime($dt_tracker)-strtotime($dt_tracker_prev);

								// calculate engine hours only if message time difference is not longer than 300 sec in order to avoid issues with some devices which change parameters without location data
								if ($engine_hours <= 300)
								{
									$q = 'UPDATE gs_objects SET `engine_hours` = engine_hours + '.$engine_hours.' WHERE imei="'.$imei.'"';
									$r = mysqli_query($ms, $q);
								}
							}
						}
					}
				}
				else
				{
					if ((strtotime($loc['dt_server']) > 0) && (strtotime($loc_prev['dt_server']) > 0))
					{
						$engine_hours = 0;

						// get ACC sensor
						$sensor = getSensorFromType($imei, 'acc');
						$acc = $sensor[0]['param'];

						// calculate engine hours from ACC
						$dt_server = $loc['dt_server'];
						$dt_server_prev = $loc_prev['dt_server'];

						if (isset($params_prev[$acc]) && isset($params[$acc]))
						{
							if (($params_prev[$acc] == '1') && ($params[$acc] == '1'))
							{
								$engine_hours = strtotime($dt_server)-strtotime($dt_server_prev);

								// calculate engine hours only if message time difference is not longer than 300 sec in order to avoid issues with some devices which change parameters without location data
								if ($engine_hours <= 300)
								{
									$q = 'UPDATE gs_objects SET `engine_hours` = engine_hours + '.$engine_hours.' WHERE imei="'.$imei.'"';
									$r = mysqli_query($ms, $q);
								}
							}
						}
					}
				}
			}
		}

		// eng sen
		if ($loc_prev['engine_hours_type'] == 'sen')
		{
			$sensor = getSensorFromType($imei, 'engh');

			if ($sensor != false)
			{
				$sensor_ = $sensor[0];

				$engh = getSensorValue($params, $sensor_);

				$result_type = $sensor_['result_type'];

				if ($result_type == 'abs')
				{
					if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
					{
						$q = 'UPDATE gs_objects SET `engine_hours` = '.$engh['value'].' WHERE imei="'.$imei.'"';
						$r = mysqli_query($ms, $q);
					}
				}

				if ($result_type == 'rel')
				{
					$q = 'UPDATE gs_objects SET `engine_hours` = engine_hours + '.$engh['value'].' WHERE imei="'.$imei.'"';
					$r = mysqli_query($ms, $q);
				}
			}
		}
	}

	function insert_db_ri($loc, $loc_prev)
	{
		global $ms;

		if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker']))
		{
			// logbook
			$group_array = array('da', 'pa', 'ta');

			for ($i=0; $i<count($group_array); ++$i)
			{
				$group = $group_array[$i];

				$sensor = getSensorFromType($loc['imei'], $group);

				if ($sensor != false)
				{
					$sensor_ = $sensor[0];

					$sensor_data = getSensorValue($loc['params'], $sensor_);
					$assign_id = $sensor_data['value'];

					if ((string)$assign_id != '0')
					{
						$sensor_data_prev = getSensorValue($loc_prev['params'], $sensor_);
						$assign_id_prev = $sensor_data_prev['value'];

						if ((string)$assign_id != (string)$assign_id_prev)
						{
							insert_db_ri_data($loc['dt_server'], $loc['dt_tracker'], $loc['imei'], $group, $assign_id, $loc['lat'], $loc['lng']);
						}
					}
				}
			}
		}
	}

	function insert_db_ri_data($dt_server, $dt_tracker, $imei, $group, $assign_id, $lat, $lng)
	{
		global $ms;

		$address = geocoderGetAddress($lat, $lng);

		$q = 'INSERT INTO gs_rilogbook_data  (	`dt_server`,
							`dt_tracker`,
							`imei`,
							`group`,
							`assign_id`,
							`lat`,
							`lng`,
							`address`
							) VALUES (
							"'.$dt_server.'",
							"'.$dt_tracker.'",
							"'.$imei.'",
							"'.$group.'",
							"'.$assign_id.'",
							"'.$lat.'",
							"'.$lng.'",
							"'.mysqli_real_escape_string($ms, $address).'")';

		$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
	}

	function insert_db_dtc($loc)
	{
		global $ms;

		if (isset($loc['event']))
		{
			if (substr($loc['event'], 0, 3) == 'dtc')
			{
				$dtcs = str_replace("dtc:", "", $loc['event']);

				$dtcs = explode(',', $dtcs);

				for ($i = 0; $i < count($dtcs); ++$i)
				{
					if ($dtcs[$i] != '')
					{
						insert_db_dtc_data($loc['dt_server'], $loc['dt_tracker'], $loc['imei'], strtoupper($dtcs[$i]), $loc['lat'], $loc['lng']);
					}
				}
			}
		}
	}

	function insert_db_dtc_data($dt_server, $dt_tracker, $imei, $code, $lat, $lng)
	{
		global $ms;

		// check for duplicates during past 24 hours
		$q = "SELECT * FROM `gs_dtc_data` WHERE `imei`='".$imei."' AND `code`='".$code."' AND dt_server > DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)";
		$r = mysqli_query($ms, $q);

		$num = mysqli_num_rows($r);

		if ($num == 0)
		{
			$address = geocoderGetAddress($lat, $lng);

			$q = 'INSERT INTO gs_dtc_data  (`dt_server`,
							`dt_tracker`,
							`imei`,
							`code`,
							`lat`,
							`lng`,
							`address`
							) VALUES (
							"'.$dt_server.'",
							"'.$dt_tracker.'",
							"'.$imei.'",
							"'.$code.'",
							"'.$lat.'",
							"'.$lng.'",
							"'.mysqli_real_escape_string($ms, $address).'")';

			$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
		}
	}

	function get_gs_objects_data($imei)
	{
		global $ms;

		$q = "SELECT * FROM gs_objects WHERE `imei`='".$imei."'";
		$r = mysqli_query($ms, $q) or die(mysqli_error($ms));
		$row = mysqli_fetch_array($r);

		if ($row)
		{
			$row['params'] = json_decode($row['params'],true);

			return $row;
		}
		else
		{
			return false;
		}
	}

	function loc_ignore_acc($loc, $loc_prev)
	{
		$ignore_sensors = array();
		$acc_sensor = getSensorFromType($loc['imei'], 'acc');

		if ($acc_sensor)
		{
			$sensors = getObjectSensors($loc['imei']);

			foreach ($sensors as $key=>$value)
			{
				if ($value['acc_ignore'] == 'true')
				{
					$ignore_sensors[] = $value;
				}
			}

			foreach ($ignore_sensors as $key=>$value)
			{
				$sensor_data = getSensorValue($loc['params'], $acc_sensor[0]);

				if ($sensor_data['value'] == 0)
				{
					if (isset($loc_prev['params'][$value['param']]))
					{
						$loc['params'][$value['param']] = $loc_prev['params'][$value['param']];
					}
					else
					{
						unset($loc['params'][$value['param']]);
					}
				}
			}
		}

		return $loc;
	}

	function loc_accvirt($loc, $loc_prev)
	{
		if ($loc_prev['accvirt'] == 'true')
		{
			if (json_decode($loc_prev['accvirt_cn'],true) != null)
			{
				$accvirt_cn = json_decode($loc_prev['accvirt_cn'],true);

				$cn_1 = false;
				$cn_0 = false;

				// check if param exits
				if (isset($loc['params'][$accvirt_cn['param']]))
				{
					if ($accvirt_cn['accvirt_1_cn'] == 'eq')
					{
						if ($loc['params'][$accvirt_cn['param']] == $accvirt_cn['accvirt_1_val']) $cn_1 = true;
					}
					else if ($accvirt_cn['accvirt_1_cn'] == 'gr')
					{
						if ($loc['params'][$accvirt_cn['param']] > $accvirt_cn['accvirt_1_val']) $cn_1 = true;
					}
					else if ($accvirt_cn['accvirt_1_cn'] == 'lw')
					{
						if ($loc['params'][$accvirt_cn['param']] < $accvirt_cn['accvirt_1_val']) $cn_1 = true;
					}

					if ($accvirt_cn['accvirt_0_cn'] == 'eq')
					{
						if ($loc['params'][$accvirt_cn['param']] == $accvirt_cn['accvirt_0_val']) $cn_0 = true;
					}
					else if ($accvirt_cn['accvirt_0_cn'] == 'gr')
					{
						if ($loc['params'][$accvirt_cn['param']] > $accvirt_cn['accvirt_0_val']) $cn_0 = true;
					}
					else if ($accvirt_cn['accvirt_0_cn'] == 'lw')
					{
						if ($loc['params'][$accvirt_cn['param']] < $accvirt_cn['accvirt_0_val']) $cn_0 = true;
					}

					if (($cn_1) && (!$cn_0))
					{
						$loc['params']['accvirt'] = '1';
					}
					else if ((!$cn_1) && ($cn_0))
					{
						$loc['params']['accvirt'] = '0';
					}
				}
			}
		}
		else
		{
			unset($loc['params']['accvirt']);
		}

		return $loc;
	}

	function loc_unassign_driver($loc, $loc_prev)
	{
		global $ms;

		if ($loc_prev['unassign_driver'] == 'true')
		{
			$acc_sensor = getSensorFromType($loc['imei'], 'acc');
			$da_sensor = getSensorFromType($loc['imei'], 'da');

			if (($acc_sensor) && ($da_sensor))
			{
				$acc_loc = getSensorValue($loc['params'], $acc_sensor[0]);

				if ($acc_loc['value'] == 0)
				{
					$loc['params'][$da_sensor[0]['param']] = '0';
				}
			}
		}

		return $loc;
	}

	function loc_filter($loc, $loc_prev)
	{
		global $ms, $gsValues;

		if ($gsValues['LOCATION_FILTER'] == false)
		{
			return false;
		}

		if (isset($loc['lat']) && isset($loc['lng']) && isset($loc['params']))
		{
			if (($loc['event'] == '') && ($loc_prev['params'] == $loc['params']))
			{
				$dt_difference = abs(strtotime($loc['dt_server']) - strtotime($loc_prev['dt_server']));

				if($dt_difference < 120)
				{
					// skip same location
					if (($loc_prev['lat'] == $loc['lat']) && ($loc_prev['lng'] == $loc['lng']) && ($loc_prev['speed'] == $loc['speed']))
					{
						return true;
					}

					// skip drift
					$distance = getLengthBetweenCoordinates($loc_prev['lat'], $loc_prev['lng'], $loc['lat'], $loc['lng']);
					if (($dt_difference < 30) && ($distance < 0.01) && ($loc['speed'] < 3) && ($loc_prev['speed'] == 0))
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	function loc_filter_india($loc)
	{
		$lock_india_vertices = '6.795535,77.409668,23.483401,67.609863,27.877928,69.719238,29.382175,72.465820,37.195331,72.290039,37.579413,75.761719,29.573457,98.129883,25.125393,96.789551,20.550509,91.604004,6.096860,94.965820,10.444598,80.617676,6.795535,77.409668';
		$imeis = explode(",", '111111111111111,222222222222222,333333333333333');
		if (!in_array($loc['imei'], $imeis))
		{
			if (isPointInPolygon($lock_india_vertices, $loc['lat'], $loc['lng']))
			{
				return true;
			}
		}
		return false;
	}

function fcm_token_add($username, $email, $token){
    global $ms;

    $q = "INSERT INTO gs_fcm(username,email,token) VALUES ('".$username."','".$email."','".$token."')";
    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    echo "OK";
    mysqli_close($ms);
}

function fcm_token_delete($token){
    global $ms;

    $q = "DELETE FROM gs_fcm WHERE token='".$token."'";
    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    echo "OK";
    mysqli_close($ms);
}
?>
