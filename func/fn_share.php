<?
	session_start();
	include ('../init.php');
	include ('fn_common.php');
	include ('../tools/sms.php');
	if (version_compare(PHP_VERSION, '5.5.0', '>=')) { include ('../tools/email.php'); } else { include ('../tools/email52.php'); }
	
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
	
	if(@$_POST['cmd'] == 'load_share_position')
	{
		$share_id = $_POST['share_id'];
		
		$q = "SELECT * FROM `gs_user_share_position` WHERE `share_id`='".$share_id."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		$result = array('active' => $row['active'],
						'expire' => $row['expire'],
						'expire_dt' => $row['expire_dt'],
						'delete_expired' => $row['delete_expired'],
						'name' => $row['name'],
						'email' => $row['email'],
						'phone' => $row['phone'],
						'imei' => $row['imei'],
						'su' => $row['su']);
		
		echo json_encode($result);
		die;
	}
	
	if(@$_POST['cmd'] == 'save_share_position')
	{
		$share_id = $_POST["share_id"];
		$active = $_POST["active"];
		$name = $_POST["name"];
		$email = $_POST["email"];
		$phone = $_POST["phone"];
		$imei = $_POST["imei"];		
		$expire = $_POST["expire"];
		$expire_dt = $_POST["expire_dt"];
		$delete_expired = $_POST["delete_expired"];
		$send_email = $_POST["send_email"];
		$send_sms = $_POST["send_sms"];
		$su = $_POST["su"];
		
		if ($share_id == 'false')
		{
			$q = "INSERT INTO `gs_user_share_position` (`user_id`,
														`active`,
														`expire`,
														`expire_dt`,
														`delete_expired`,
														`name`,
														`email`,
														`phone`,
														`imei`,
														`su`)
														VALUES
														('".$user_id."',
														'".$active."',
														'".$expire."',
														'".$expire_dt."',
														'".$delete_expired."',
														'".$name."',
														'".$email."',
														'".$phone."',
														'".$imei."',
														'".$su."')";
		}
		else
		{
			$q = "UPDATE `gs_user_share_position` SET  	`active`='".$active."',
														`expire`='".$expire."',
														`expire_dt`='".$expire_dt."',
														`delete_expired`='".$delete_expired."',
														`name`='".$name."',
														`email`='".$email."',
														`phone`='".$phone."',
														`imei`='".$imei."',
														`su`='".$su."'
														WHERE `share_id`='".$share_id."'";
		}

		$r = mysqli_query($ms, $q);
		
		if ($send_email == 'true')
		{
			$template = getDefaultTemplate('share_position_su_email', $_SESSION["language"]);
					
			$subject = $template['subject'];
			$message = $template['message'];
			
			$su_email = $gsValues['URL_ROOT'].'/index.php?su='.$su;
			$su_mobile = $gsValues['URL_ROOT'].'/index.php?su='.$su.'&m=true';
						
			$subject = str_replace("%SERVER_NAME%", $gsValues['NAME'], $subject);
			$subject = str_replace("%USER_EMAIL%", $_SESSION['email'], $subject);
			$subject = str_replace("%NAME%", getObjectName($imei), $subject);
			$subject = str_replace("%URL_SU%", $su_email, $subject);
			$subject = str_replace("%URL_SU_MOBILE%", $su_mobile, $subject);
			
			$message = str_replace("%SERVER_NAME%", $gsValues['NAME'], $message);
			$message = str_replace("%USER_EMAIL%", $_SESSION['email'], $message);
			$message = str_replace("%NAME%", getObjectName($imei), $message);
			$message = str_replace("%URL_SU%", $su_email, $message);
			$message = str_replace("%URL_SU_MOBILE%", $su_mobile, $message);
			
			sendEmail($email, $subject, $message);
		}
		
		if ($send_sms == 'true')		
		{
			if (checkUserUsage($user_id, 'sms'))
			{
				$result = false;
				
				// variables
				$template = getDefaultTemplate('share_position_su_sms', $_SESSION["language"]);
						
				$subject = $template['subject'];
				$message = $template['message'];
				
				$su_email = $gsValues['URL_ROOT'].'/index.php?su='.$su;
				$su_mobile = $gsValues['URL_ROOT'].'/index.php?su='.$su.'&m=true';
							
				$subject = str_replace("%SERVER_NAME%", $gsValues['NAME'], $subject);
				$subject = str_replace("%USER_EMAIL%", $_SESSION['email'], $subject);
				$subject = str_replace("%NAME%", getObjectName($imei), $subject);
				$subject = str_replace("%URL_SU%", $su_email, $subject);
				$subject = str_replace("%URL_SU_MOBILE%", $su_mobile, $subject);
				
				$message = str_replace("%SERVER_NAME%", $gsValues['NAME'], $message);
				$message = str_replace("%USER_EMAIL%", $_SESSION['email'], $message);
				$message = str_replace("%NAME%", getObjectName($imei), $message);
				$message = str_replace("%URL_SU%", $su_email, $message);
				$message = str_replace("%URL_SU_MOBILE%", $su_mobile, $message);
				
				$q = "SELECT * FROM `gs_users` WHERE `id`='".$user_id."'";
				$r = mysqli_query($ms, $q);
				$ud = mysqli_fetch_array($r);
				
				$number = $phone;
				
				if ($ud['sms_gateway'] == 'true')
				{
					if ($ud['sms_gateway_type'] == 'http')
					{
						$result = sendSMSHTTP($ud['sms_gateway_url'], '', $number, $message);
					}
					else if ($ud['sms_gateway_type'] == 'app')
					{
						$result = sendSMSAPP($ud['sms_gateway_identifier'], '', $number, $message);
					}
				}
				else
				{
					if (($ud['sms_gateway_server'] == 'true') && ($gsValues['SMS_GATEWAY'] == 'true'))
					{
						if ($gsValues['SMS_GATEWAY_TYPE'] == 'http')
						{
							$result = sendSMSHTTP($gsValues['SMS_GATEWAY_URL'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $message);
						}
						else if ($gsValues['SMS_GATEWAY_TYPE'] == 'app')
						{
							$result = sendSMSAPP($gsValues['SMS_GATEWAY_IDENTIFIER'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $message);
						}
					}
				}
				
				if ($result == true)
				{
					//update user usage
					updateUserUsage($user_id, false, false, 1, false, false);
				}
			}	
		}
		
		echo 'OK';
		die;
	}
        
	if(@$_GET['cmd'] == 'load_share_position_list')
	{	
		$page = $_GET['page']; // get the requested page
		$limit = $_GET['rows']; // get how many rows we want to have into the grid
		$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
		$sord = $_GET['sord']; // get the direction
		$search = caseToUpper(@$_GET['s']); // get search
		
		if(!$sidx) $sidx = 1;
		
		 // get records number	
		$q = "SELECT * FROM `gs_user_share_position` WHERE `user_id`='".$user_id."'";
		
		if ($search != '')
		{
			$q .= " AND (UPPER(name) LIKE '%$search%' OR UPPER(email) LIKE '%$search%' OR UPPER(phone) LIKE '%$search%')";
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
				
		$q = "SELECT * FROM `gs_user_share_position` WHERE `user_id`='".$user_id."'";
		
		if ($search != '')
		{
			$q .= " AND (UPPER(name) LIKE '%$search%' OR UPPER(email) LIKE '%$search%' OR UPPER(phone) LIKE '%$search%')";
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
				$share_id = $row['share_id'];
				$name = $row['name'];
				$email = $row['email'];
				$phone = $row['phone'];				
				
				$imeis = explode(",", $row['imei']);
				if ($imeis[0] != '')
				{
					$imeis = count($imeis);
				}
				else
				{
					$imeis = 0;
				}
				
				$expire_dt = '';
				
				if ($row['active'] == 'true')
				{
					$active = '<img src="theme/images/tick-green.svg" />';
					
					if ($row['expire'] == 'true')
					{
						$expire_dt = $row['expire_dt'];
					}
				}
				else
				{
					$active = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
					
					if ($row['expire'] == 'true')
					{
						$expire_dt = $row['expire_dt'];
					}
				}
				
				// set modify buttons
				$modify = '<a href="#" onclick="sharePositionProperties(\''.$share_id.'\');" title="'.$la['EDIT'].'"><img src="theme/images/edit.svg" /></a>';
				$modify .= '</a><a href="#" onclick="sharePositionDelete(\''.$share_id.'\');" title="'.$la['DELETE'].'"><img src="theme/images/remove3.svg" /></a>';
				
				// set row
				$response->rows[$i]['id']=$share_id;
				$response->rows[$i]['cell']=array($name,$email,$phone,$imeis,$active,$expire_dt,$modify);
				$i++;
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($response);
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_share_position')
	{
		$share_id= $_POST["share_id"];
		
		$q = "DELETE FROM `gs_user_share_position` WHERE `share_id`='".$share_id."'";
		$r = mysqli_query($ms, $q);
		
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_share_positions')
	{
		$items = $_POST["items"];
		
		for ($i = 0; $i < count($items); ++$i)
		{
			$item = $items[$i];
			
			$q = "DELETE FROM `gs_user_share_position` WHERE `share_id`='".$item."'";
			$r = mysqli_query($ms, $q);
		}
		
		echo 'OK';
		die;
	}
?>