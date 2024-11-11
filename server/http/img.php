<?
	ob_start();
	echo "OK";
	header("Connection: close");
	header("Content-length: " . (string)ob_get_length());
	ob_end_flush();
	
	if (!isset($_GET["imei"]))
	{
		die;
	}
	
        chdir('../');
        include ('s_insert.php');
        
        if (@$_GET["op"] == "img")
        {
		// check for wrong IMEI
		if (!ctype_alnum($_GET['imei']))
		{
			return false;
		}
		
		// check if object exists in system
		if (!checkObjectExistsSystem($_GET['imei']))
		{
			return false;
		}
		
		$loc = array();
		
                // get previous known location
		$loc = get_gs_objects_data($_GET['imei']);
                
		if (!$loc) {die;}
		
                $loc['dt_server'] = gmdate("Y-m-d H:i:s");
                
                $img_file = $_GET['imei'].'_'.$loc['dt_server'].'.jpg';
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
                                                params
                                                ) VALUES (
                                                '".$img_file."',
                                                '".$_GET['imei']."',
                                                '".$loc['dt_server']."',
                                                '".$loc['dt_tracker']."',
                                                '".$loc['lat']."',
                                                '".$loc['lng']."',
                                                '".$loc['altitude']."',
                                                '".$loc['angle']."',
                                                '".$loc['speed']."',
                                                '".json_encode($loc['params'])."')";
						
                $r = mysqli_query($ms, $q);
                
                // save file
                $img_path = $gsValues['PATH_ROOT'].'/data/img/';
                $img_path = $img_path.basename($img_file);		
		
		if (!isFilePathValid($img_path))
		{
			die;
		}
                
                $postdata = file_get_contents('php://input', 'r');
                
                if(substr($postdata,0,3) == "\xFF\xD8\xFF")
                {
                        $fp = fopen($img_path,"w");
                        fwrite($fp,$postdata);
                        fclose($fp);
                } 
	}
	
	mysqli_close($ms);
	die;
?>