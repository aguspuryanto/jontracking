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
				$map_layer = 'osm';

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
		$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='".$user_id ."' AND imei IN (".implode(",", $imeis).")";
		$r = mysqli_query($ms, $q);

		$result = array();

		while($row = mysqli_fetch_array($r))
		{
			$imei = $row['imei'];

			$q2 = "SELECT * FROM `gs_objects` WHERE `imei`='".$imei."'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);

			if ($row2['active'] == 'true')
			{
				$result[$imei] = array();
				$result[$imei]['v'] = true;
				$result[$imei]['f'] = false;
				$result[$imei]['s'] = false;
				$result[$imei]['evt'] = false;
				$result[$imei]['evtac'] = false;
				$result[$imei]['evtohc'] = false;
				$result[$imei]['a'] = '';
				$result[$imei]['l'] = array();
				$result[$imei]['d'] = array();

				$dt_server = $row2['dt_server'];
				$dt_tracker = $row2['dt_tracker'];
				$lat = $row2['lat'];
				$lng = $row2['lng'];
				$altitude = $row2['altitude'];
				$angle = $row2['angle'];
				$speed = $row2['speed'];
				$params = json_decode($row2['params'],true);

				$speed = convSpeedUnits($speed, 'km', $user_data["unit_distance"]);
				$altitude = convAltitudeUnits($altitude, 'km', $user_data["unit_distance"]);

				// status
				$result[$imei]['st'] = false;

				$result[$imei]['ststr'] = '';

				$dt_last_stop = strtotime($row2['dt_last_stop']);
				$dt_last_idle = strtotime($row2['dt_last_idle']);
				$dt_last_move = strtotime($row2['dt_last_move']);

				if (($dt_last_stop > 0) || ($dt_last_move > 0))
				{
					// stopped and moving
					//dP if ($dt_last_stop >= $dt_last_move)
					if (($dt_last_stop >= $dt_last_move) || ($speed <= 0))	//dP
					{
						$result[$imei]['st'] = 's';
						$result[$imei]['ststr'] = $la['STOPPED'].' '.getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_stop, true);
					}
					else
					{
						$result[$imei]['st'] = 'm';
						$result[$imei]['ststr'] = $la['MOVING'].' '.getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_move, true);
					}

					// idle
					if (($dt_last_stop <= $dt_last_idle) && ($dt_last_move <= $dt_last_idle))
					{
						//dP $result[$imei]['st'] = 'i';
						//dP $result[$imei]['ststr'] = $la['ENGINE_IDLE'].' '.getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_idle, true);

						if ($speed <= 0)
						{
							$result[$imei]['st'] = 'i';
							$result[$imei]['ststr'] = $la['ENGINE_IDLE'].' '.getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_idle, true);
						}
						else
						{
							$result[$imei]['st'] = 'm';
							$result[$imei]['ststr'] = $la['MOVING'].' '.getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_move, true);
						}
					}
				}

				// protocol
				$result[$imei]['p'] = $row2['protocol'];

				// connection/loc valid check
				$dt_now = gmdate("Y-m-d H:i:s");
				$dt_difference = strtotime($dt_now) - strtotime($dt_server);
				if($dt_difference < $gsValues['CONNECTION_TIMEOUT'] * 60)
				{
					$loc_valid = $row2['loc_valid'];

					if ($loc_valid == 1)
					{
						$conn = 2;
					}
					else
					{
						$conn = 1;
					}
				}
				else
				{
					// offline status
					if (strtotime($dt_server) > 0)
					{
						$result[$imei]['st'] = 'off';
						$result[$imei]['ststr'] = $la['OFFLINE'].' '.getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - strtotime($dt_server), true);
					}

					$conn = 0;
					$speed = 0;
				}

				$result[$imei]['cn'] = $conn;

				// location data
				if (($lat != 0) && ($lng != 0))
				{
					$result[$imei]['d'][] = array(	convTimezone($user_data, $dt_server),
													convTimezone($user_data, $dt_tracker),
													$lat,
													$lng,
													$altitude,
													$angle,
													$speed,
													$params);
				}

				// odometer and engine_hours
				$odometer = floor(convDistanceUnits($row2['odometer'], 'km', $user_data["unit_distance"]));
				$engine_hours = floor($row2['engine_hours'] / 60 / 60);

				$result[$imei]['o'] = $odometer;
				$result[$imei]['eh'] = $row2['engine_hours']; // we do not use conversion, because we need engine hours in seconds

				// service
				$result[$imei]['sr'] = array();

				$q3 = "SELECT * FROM `gs_object_services` WHERE `imei`='".$imei."' ORDER BY name asc";
				$r3 = mysqli_query($ms, $q3);

				while($row3 = mysqli_fetch_array($r3)) {
					$left_arr = array();
					$expired_arr = array();

					if ($row3['odo'] == 'true')
					{
						$row3['odo_interval'] = floor(convDistanceUnits($row3['odo_interval'], 'km', $user_data["unit_distance"]));
						$row3['odo_last'] = floor(convDistanceUnits($row3['odo_last'], 'km', $user_data["unit_distance"]));

						$odo_diff = $odometer - $row3['odo_last'];
						$odo_diff = $row3['odo_interval'] - $odo_diff;

						if ($odo_diff <= 0)
						{
							$expired_arr[] = abs($odo_diff).' '.$la["UNIT_DISTANCE"];
						}
						else
						{
							$left_arr[] = $odo_diff.' '.$la["UNIT_DISTANCE"];
						}
					}

					if ($row3['engh'] == 'true')
					{
						$engh_diff = $engine_hours - $row3['engh_last'];
						$engh_diff = $row3['engh_interval'] - $engh_diff;

						if ($engh_diff <= 0)
						{
							$expired_arr[] = abs($engh_diff).' '.$la["UNIT_H"];
						}
						else
						{
							$left_arr[] = $engh_diff.' '.$la["UNIT_H"];
						}
					}

					if ($row3['days'] == 'true')
					{
						$days_diff = strtotime(gmdate("M d Y ")) - (strtotime($row3['days_last']));
						$days_diff = floor($days_diff/3600/24);
						$days_diff = $row3['days_interval'] - $days_diff;

						if ($days_diff <= 0)
						{
							$expired_arr[] = abs($days_diff).' '.$la["UNIT_D"];
						}
						else
						{
							$left_arr[] = $days_diff.' '.$la["UNIT_D"];
						}
					}

					$status = '';

					if (count($left_arr) > 0)
					{
						$status = $la["LEFT"].' ('.implode(", ", $left_arr).')';
					}

					if (count($expired_arr) > 0)
					{
						$status = '<font color="red">'.$la["EXPIRED"].' ('.implode(", ", $expired_arr).')</font>';
					}

					if ($status != '')
					{
						$result[$imei]['sr'][] = array(	'name' => $row3['name'], 'data_list' => $row3['data_list'], 'popup' => $row3['popup'], 'status' => $status);
					}
				}
			}
		}

		mysqli_close($ms);

		ob_start();
		header('Content-type: application/json');
		echo json_encode($result);
		header("Connection: close");
		header("Content-length: " . (string)ob_get_length());
		ob_end_flush();
		die;
	}

	function convTimezone($user_data, $dt)
	{
		if (!isset($user_data["timezone"]))
		{
			$user_data["timezone"] = "+ 0 hour";
		}

		if (!isset($user_data["dst"]))
		{
			$user_data["dst"] = "false";
		}

		if (strtotime($dt) > 0)
		{
			$dt = gmdate("Y-m-d H:i:s", strtotime($dt.$user_data["timezone"]));

			// DST
			if ($user_data["dst"] == 'true')
			{
				$dt_ = gmdate('m-d H:i:s', strtotime($dt));
				$dst_start = $user_data["dst_start"].':00';
				$dst_end =  $user_data["dst_end"].':00';

				if (isDateInRange(convDateToNum($dt_), convDateToNum($dst_start), convDateToNum($dst_end)))
				{
					$dt = gmdate("Y-m-d H:i:s", strtotime($dt.'+ 1 hour'));
				}
			}
		}

		return $dt;
	}
?>
