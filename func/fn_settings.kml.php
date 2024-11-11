<? 
	session_start();
	include ('../init.php');
	include ('fn_common.php');
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
	
	$kml_max_total_size = 10; // in MB
	
	if ($_SESSION["privileges_kml"] == false)
	{
		die;
	}
	
	if(@$_POST['cmd'] == 'load_kml_data')
	{	
		$q = "SELECT * FROM `gs_user_kml` WHERE `user_id`='".$user_id."' ORDER BY `name` ASC";
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row=mysqli_fetch_array($r))
		{
			$kml_id = $row['kml_id'];
			$result[$kml_id] = array(	'active' => $row['active'],
										'name' => $row['name'],										
										'desc' => $row['desc'],
										'kml_file' => $row['kml_file'],
										'file_name' => $row['file_name']
										);
		}
		echo json_encode($result);
		die;
	}
	
	if(@$_GET['cmd'] == 'load_kml_list')
	{ 
		$page = $_GET['page']; // get the requested page
		$limit = $_GET['rows']; // get how many rows we want to have into the grid
		$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
		$sord = $_GET['sord']; // get the direction
		$search = caseToUpper(@$_GET['s']); // get search
				
		if(!$sidx) $sidx =1;
		
		// get records number
		$q = "SELECT * FROM `gs_user_kml` WHERE `user_id`='".$user_id."'";
		
		if ($search != '')
		{
			$q .= " AND (UPPER(`name`) LIKE '%$search%')";	
		}
		
		$r = mysqli_query($ms, $q);
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
		
		// prevent injection attacks
		if (!isMySQLOrderByValid($sidx, $sord))
		{
			die;
		}
		
		if (!isMySQLLimitValid($start, $limit))
		{
			die;
		}
		
		$q .= " ORDER BY $sidx $sord LIMIT $start, $limit";	
		$r = mysqli_query($ms, $q);
		
		$response = new stdClass();
		$response->page = $page;
		$response->total = $total_pages;
		$response->records = $count;
		
		if ($r)
		{
			$i=0;
			while($row = mysqli_fetch_array($r)) {
				$kml_id = $row['kml_id'];
				$name = $row['name'];
				$desc = $row['desc'];
				
				if ($row['active'] == 'true')
				{
					$active = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$active = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				// set modify buttons
				$modify = '<a href="#" onclick="settingsKMLProperties(\''.$kml_id.'\');" title="'.$la['EDIT'].'"><img src="theme/images/edit.svg" />';
				$modify .= '</a><a href="#" onclick="settingsKMLDelete(\''.$kml_id.'\');"  title="'.$la['DELETE'].'"><img src="theme/images/remove3.svg" /></a>';
				// set row
				$response->rows[$i]['id']=$kml_id;
				$response->rows[$i]['cell']=array($name,$active,$desc,$modify);
				$i++;
			}	
		}

		header('Content-type: application/json');
		echo json_encode($response);
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_kml')
	{
		$kml_id = $_POST["kml_id"];
		
		$q = "SELECT * FROM `gs_user_kml` WHERE `kml_id`='".$kml_id."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		$kml_file = $gsValues['PATH_ROOT'].'data/user/kml/'.$row['kml_file'];
		if(is_file($kml_file))
		{
			@unlink($kml_file);
		}
		
		$q = "DELETE FROM `gs_user_kml` WHERE `kml_id`='".$kml_id."' AND `user_id`='".$user_id."'";
		$r = mysqli_query($ms, $q);
		
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_selected_kml')
	{
		$items = $_POST["items"];
				
		for ($i = 0; $i < count($items); ++$i)
		{
			$item = $items[$i];
			
			$q = "SELECT * FROM `gs_user_kml` WHERE `kml_id`='".$item."'";
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_array($r);
			
			$kml_file = $gsValues['PATH_ROOT'].'data/user/kml/'.$row['kml_file'];
			if(is_file($kml_file))
			{
				@unlink($kml_file);
			}
			
			$q = "DELETE FROM `gs_user_kml` WHERE `kml_id`='".$item."' AND `user_id`='".$user_id."'";
			$r = mysqli_query($ms, $q);
		}
		
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'save_kml')
	{
		$kml_id = $_POST["kml_id"];
		$active = $_POST["active"];
		$name = $_POST["name"];		
		$desc = $_POST["desc"];
		$file_name = $_POST["file_name"];
		$file_data = $_POST["file_data"];
		
		$file_size = 0;
		
		if ($kml_id == 'false')
		{
			if ($file_data != '')
			{
				// get total size
				$total_size = 0;
				$q = "SELECT * FROM `gs_user_kml` WHERE `user_id`='".$user_id."'";
				$r = mysqli_query($ms, $q);
								
				while($row=mysqli_fetch_array($r))
				{
					$total_size += $row['file_size'];
				}
				
				// decode
				$filteredData = substr($file_data, strpos($file_data, ",")+1);				
				$unencodedData = base64_decode($filteredData);
				
				$file_size = mb_strlen($unencodedData,'8bit');
				$file_size = ($file_size/1048576);
				
				// check if does not exceed total size
				$total_size += $file_size;
				
				if ($kml_max_total_size < $total_size)
				{
					echo 'ERROR_TOTAL_SIZE_LIMIT';
					die;
				}				
				
				$kml_file = $_SESSION["user_id"].'_'.md5(gmdate("Y-m-d H:i:s")).'.kml';
				$file_path = $gsValues['PATH_ROOT'].'data/user/kml/'.$kml_file;
				
				if (!isFilePathValid($file_path))
				{
					die;
				}
				
				$fp = fopen( $file_path, 'wb' );
				fwrite( $fp, $unencodedData);
				fclose( $fp );
			}
			
			$q = "INSERT INTO `gs_user_kml` (	`user_id`,
												`active`,
												`name`,
												`desc`,
												`kml_file`,
												`file_name`,
												`file_size`
												) VALUES (
												'".$user_id."',
												'".$active."',
												'".$name."',
												'".$desc."',
												'".$kml_file."',
												'".$file_name."',
												'".$file_size."')";
		}
		else
		{
			$q = "UPDATE `gs_user_kml` SET	`active`='".$active."',
											`name`='".$name."', 
											`desc`='".$desc."'
											WHERE `kml_id`='".$kml_id."'";
		}
		
		$r = mysqli_query($ms, $q);
		
		echo 'OK';
	}
?>