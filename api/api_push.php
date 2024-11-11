<?
    include ('../init.php');
	
	$cmd = @$_GET['cmd'];
	if ($cmd == '') { die; }
	
    // split command and params
	$cmd = urldecode($cmd);
	$cmd = stripslashes($cmd);
	$cmd = str_getcsv($cmd, ",", '"');
	$command = @$cmd[0];
	$command = strtoupper($command);
	
	if ($command == 'GET_PUSH_WEBVIEW')
    {
        // command validation
        if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
		
		// command parameters
        $identifier = strtolower($cmd[1]);        
        
        if ($identifier == '')
        {
            echo "ERROR: indentifier can't be empty";
            die;
        }
		
		// get unread messages number
		$q = "SELECT * FROM `gs_push_queue` WHERE `identifier`='".$identifier."' AND `type`='event' ORDER by id DESC LIMIT 1";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		if ($row)
		{
			if ($row['message'] == '')
			{
				$row['message'] = 'New event was received.';
			}
			
			$result = array(	'id' => $row['id'],
								'type' => $row['type'],
								'message' => $row['message']
								);
		}
		else
		{
			$result = array(	'id' => 0,
								'type' => '',
								'message' => ''
								);
		}

					
        header('Content-type: application/json');
        echo json_encode($result);
        die;
	}
	
	echo 'ERROR: unknown command';
    die;
?>
