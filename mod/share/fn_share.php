<?
	include ('../../init.php');
	include ('../../func/fn_common.php');
	
	$session_check = 'false';
	
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
				
				for ($i = 0; $i < count($imeis); ++$i)
				{
					if(checkUserToObjectPrivileges($user_id, $imeis[$i]))
					{
						$session_check = 'true';
					}
				}				
			}
		}
	}
	
	if(@$_POST['cmd'] == 'session_check')
	{
		echo $session_check;
		die;
	}
?>