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
	
	if(@$_POST['cmd'] == 'load_object_group_data')
	{
		$q = "SELECT * FROM `gs_user_object_groups` WHERE `user_id`='".$user_id."' ORDER BY `group_name` ASC";
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		// add ungrouped group
		$result[] = array('name' => $la['UNGROUPED'], 'desc' => '');
		
		while($row=mysqli_fetch_array($r))
		{
			$group_id = $row['group_id'];
			
			$group_name = str_replace(array('"', "'"), '', $row['group_name']);
			
			$result[$group_id] = array('name' => $group_name, 'desc' => $row['group_desc']);
		}
		echo json_encode($result);
		die;
	}
?>