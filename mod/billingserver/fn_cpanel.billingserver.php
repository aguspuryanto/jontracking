<?
	set_time_limit(0);
	
	session_start();
	include ('../../init.php');
	include ('../../func/fn_common.php');
	checkUserSession();
	checkUserCPanelPrivileges();
	
	loadLanguage($_SESSION["language"], $_SESSION["units"]);
	
	if(@$_POST['cmd'] == 'load_billing_server_data')
	{
		$result = array();
		
		// expires soon
		$result['expires_soon'] = false;		
			
		if (isset($gsValues['SERVER_PLAN_NEXT_PAYMENT']))
		{
			if (strtotime($gsValues['SERVER_PLAN_NEXT_PAYMENT'].' - 7 day') <= strtotime(gmdate("Y-m-d")))
			{
				$result['expires_soon'] = true;
			}
		}
		
		// paypal url
		if (isset($gsValues['SERVER_PLAN_PAYPAL_URL']))
		{
			$result['paypal_url'] = $gsValues['SERVER_PLAN_PAYPAL_URL'];
		}
		else
		{
			$result['paypal_url'] = false;
		}
		
		if (isset($gsValues['SERVER_PLAN_NAME']))
		{
			$result['server_plan_name'] = $gsValues['SERVER_PLAN_NAME'];
		}
		else
		{
			$result['server_plan_name'] = caseToLower($la['NA']);
		}
		
		if (isset($gsValues['OBJECT_LIMIT']))
		{
			$result['server_plan_objects'] = $gsValues['OBJECT_LIMIT'];
		}
		else
		{
			$result['server_plan_objects'] = caseToLower($la['NA']);
		}
		
		if (isset($gsValues['HISTORY_PERIOD']))
		{
			$result['server_plan_history'] = $gsValues['HISTORY_PERIOD'];
		}
		else
		{
			$result['server_plan_history'] = caseToLower($la['NA']);
		}
		
		if (isset($gsValues['SERVER_PLAN_PERIOD']))
		{
			$result['server_plan_period'] = $gsValues['SERVER_PLAN_PERIOD'];
		}
		else
		{
			$result['server_plan_period'] = caseToLower($la['NA']);
		}
		
		if (isset($gsValues['SERVER_PLAN_PRICE']))
		{
			$result['server_plan_price'] = $gsValues['SERVER_PLAN_PRICE'];
		}
		else
		{
			$result['server_plan_price'] = caseToLower($la['NA']);
		}
		
		if (isset($gsValues['SERVER_PLAN_LAST_PAYMENT']))
		{
			$result['server_plan_last_payment'] = $gsValues['SERVER_PLAN_LAST_PAYMENT'];
		}
		else
		{
			$result['server_plan_last_payment'] = caseToLower($la['NA']);
		}
		
		if (isset($gsValues['SERVER_PLAN_NEXT_PAYMENT']))
		{
			$result['server_plan_next_payment'] = $gsValues['SERVER_PLAN_NEXT_PAYMENT'];
		}
		else
		{
			$result['server_plan_next_payment'] = caseToLower($la['NA']);
		}
		
		echo json_encode($result);
		die;
	}
?>