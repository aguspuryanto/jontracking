<?
    include ('../init.php');
	include ('../func/fn_common.php');
	include ('../func/fn_route.php');
	include ('../tools/gc_func.php');
	include ('../tools/sms.php');    
    if (version_compare(PHP_VERSION, '5.5.0', '>=')) { include ('../tools/email.php'); } else { include ('../tools/email52.php'); }
	
	function getUserIdFromAPIKey($key)
	{
		global $ms;
		
		$q = "SELECT * FROM `gs_users` WHERE `api_key`='".$key."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);

		if($row["api"] == "true")
		{
			return $row["id"];
		}
		else
		{
			return false;	
		}
	}
	
	function getUserIdFromUsername($username)
	{
		global $ms;
		
		$q = "SELECT * FROM `gs_users` WHERE `username`='".$username."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		return $row["id"];
	}
	
	function getUserIdFromEmail($email)
	{
		global $ms;
		
		$q = "SELECT * FROM `gs_users` WHERE `email`='".$email."'";
		$r = mysqli_query($ms, $q);
		
		if (!$r)
		{
			return false;
		}
		
		$row = mysqli_fetch_array($r);
		
		return $row["id"];
	}
	
	function getUserAPIKeyFromEmail($email)
	{
		global $ms;
		
		$q = "SELECT * FROM `gs_users` WHERE `email`='".$email."'";
		$r = mysqli_query($ms, $q);
		
		if (!$r)
		{
			return false;
		}
		
		$row = mysqli_fetch_array($r);
		
		return $row["api_key"];
	}
    
    function checkServerAPIPrivileges()
    {
        global $gsValues;
        
        if (isset($gsValues['SERVER_API_IP']))
        {
            if ($gsValues['SERVER_API_IP'] != '')
			{
                $api_ips = explode(",", $gsValues['SERVER_API_IP']);	
                if (!in_array($_SERVER['REMOTE_ADDR'], $api_ips))
                {
                    die;
                }
            }            
        }
    }
	
     // validate access to api
	$api_access = false;
	
	$api = @$_GET['api'];
    $key = @$_GET['key'];
    $cmd = @$_GET['cmd'];
	
	if ($api == '') { die; }
    if ($key == '') { die; }
    if ($cmd == '') { die; }
	
	if ($api == 'server')
	{
        if (isset($gsValues['SERVER_ENABLED']))
		{
			if ($gsValues['SERVER_ENABLED'] == 'false')
			{
				die;
			}       
		}
        
        checkServerAPIPrivileges();
	
		if ($key != $gsValues['SERVER_API_KEY'])
        {
            echo 'ERROR: wrong API key';
            die;
        }
        
		$api_access = true;
		include ('api_server.php');
	}
	
	if ($api == 'user')
	{
		if (isset($gsValues['SERVER_ENABLED']))
		{
			if ($gsValues['SERVER_ENABLED'] == 'false')
			{
				echo 'ERROR: server is not enabled';
				die;
			}       
		}
	
		$user_id = getUserIdFromAPIKey($key);
		if ($user_id == false)
        {
            echo 'ERROR: wrong API key';
            die;
        }
		//check user usage
		if (!checkUserUsage($user_id, 'api'))
        {
            echo 'ERROR: API call limit exceeded';
            die;
        }
		
		//update user usage
		updateUserUsage($user_id, false, false, false, false, 1);
		
		$api_access = true;		
		include ('api_user.php');
	}
    
    if ($api == 'hosting')
	{
		if ($key != $gsValues['HW_KEY']) { die; }
		$api_access = true;
		include ('api_hosting.php');
	}
	
	die;
?>