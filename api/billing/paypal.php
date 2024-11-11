<?
	include ('../../init.php');
	
	$debug = false;
    $paypalmode = 'sandbox';
	$paypalmode = '';
	
	// debug
	if ($debug == true)
	{
		$file = gmdate("YmdHis").'.txt';
		$handle = fopen($file, 'w');
		fwrite($handle, file_get_contents('php://input'));
		fclose($handle);	
	}
        
    if($_POST)
    {
		if($paypalmode == 'sandbox')
		{
			$paypalmode = '.sandbox';
		}
		
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval)
		{
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
			$myPost[$keyval[0]] = urldecode($keyval[1]);
		}
		
		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc'))
		{
			$get_magic_quotes_exists = true;
		}
		
		foreach ($myPost as $key => $value)
		{
			if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1)
			{
				$value = urlencode(stripslashes($value));
			}
			else
			{
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}
		
		$ch = curl_init('https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr');
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		
		if(!($res = curl_exec($ch)))
		{
			curl_close($ch);
			exit;
		}
		curl_close($ch);
		
		// debug
		if ($debug == true)
		{
			$file = 'res_'.gmdate("YmdHis").'.txt';
			$handle = fopen($file, 'w');
			fwrite($handle, $res);
			fclose($handle);	
		}
                
		//if (strcmp ($res, "VERIFIED") != 0)
		//{
		//	die;
		//}
		
		// prepare data
		$paymentstatus = strtolower($_POST['payment_status']);
		$total = $_POST['mc_gross'];
		$custom = $_POST['custom'];
		// end prepare data
		
		// check if completed
		if ($paymentstatus != "completed")
		{
			die;
		}
		
		// check if not negative price
		if ($total <= 0)
		{
			die;
		}
		
		// check for filter params
		$custom = explode(',', $custom);
		if (count($custom) == 0)
		{
			die;
		}
		
		if ($gsValues['BILLING_PAYPAL_CUSTOM'] == '')
		{
			die;
		}
		
		if ($custom[0] == $gsValues['BILLING_PAYPAL_CUSTOM'])
		{
			$email = $custom[1];
			$plan_id = $custom[2];
			
			addUserBillingPlan($email, $plan_id);
		}
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
	
	function addUserBillingPlan($email, $plan_id)
	{
		global $ms;
		
		$user_id = getUserIdFromEmail(strtolower($email));
		
		if (!$user_id)
		{
			  die;  
		}
                
		$dt_purchase = gmdate("Y-m-d H:i:s");
                
        $q = "SELECT * FROM `gs_billing_plans` WHERE `plan_id`='".$plan_id."'";
		$r = mysqli_query($ms, $q);
                
		if (!$r)
		{
			  die;  
		}
		
		$row = mysqli_fetch_array($r);
                
		$name = $row['name'];
		$active = $row['active'];
		$objects = $row['objects'];
		$period = $row['period'];
		$period_type = $row['period_type'];
		$price = $row['price'];
		
		if ($active == 'true')
		{
				$q = "INSERT INTO `gs_user_billing_plans` (`user_id`,
														`dt_purchase`,
														`name`,
														`objects`,
														`period`,
														`period_type`,
														`price`
														) VALUES (
														'".$user_id."',
														'".$dt_purchase."',
														'".$name."',
														'".$objects."',
														'".$period."',
														'".$period_type."',
														'".$price."')";
			  $r = mysqli_query($ms, $q);        
		}
	}
?>