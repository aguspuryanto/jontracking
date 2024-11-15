<?
	set_time_limit(0);
	
	session_start();
	header("Content-Type: application/json");

	$method = $_SERVER['REQUEST_METHOD'];
	$params = $_SERVER['QUERY_STRING'];
	$input = json_decode(file_get_contents('php://input'), true);

	include ('../init.php');
	include ('fn_common.php');
	// echo var_dump($input);
	// echo var_dump($method);
	// echo var_dump($params);

	if(@$_POST['cmd'] == 'delete_user')
	{
		$id = $_POST['id'];
		$pwd = $_POST['pwd'];
		
		$q = "SELECT * FROM `gs_users` WHERE `password`='".md5($pwd)."' LIMIT 1";
		if($id) { 
			$q = "SELECT * FROM `gs_users` WHERE `id`='".$id."' AND `password`='".md5($pwd)."' LIMIT 1";	
		}

		$r = mysqli_query($ms, $q);
		if ($row=mysqli_fetch_array($r))
		{
			//write log
			writeLog('user_access', 'Delete user via API: successful. E-mail: '.$row['email']);

			// $q = "UPDATE `gs_users` SET `active`='false' WHERE `id`='".$row['id']."'";
			// $r = mysqli_query($ms, $q);
			$q = "DELETE FROM `gs_users` WHERE `id`='".$row['id']."'";
			$r = mysqli_query($ms, $q);

			// OR
			// delUser($row['id']);
		}
		
		$result = [
			'status' => 'OK',
			'message' => 'Delete user: successful'
		];
		echo json_encode($result);
		die;
	}

	if($method == 'GET')
	{
		if($_GET['cmd'] == 'get_manager_info') {

			$manager_id = @$_GET['manager_id'];
			if(!$manager_id || !is_numeric($manager_id))
			{
				// $manager_id = $_SESSION['user_id'];
				$result = [
					'status' => 'ERROR',
					'message' => 'Manager ID not found'
				];
			} else {
					
				$q = "SELECT * FROM `gs_users` WHERE `id`='".$manager_id."'";
				$r = mysqli_query($ms, $q);
				$row = mysqli_fetch_array($r);
				
				$info = json_decode($row['info'], true);
				if ($info == null)
				{
					$info = array('name' => '',
							'company' => '',
							'address' => '',
							'post_code' => '',
							'city' => '',
							'country' => '',
							'phone1' => '',
							'phone2' => '',
							'email' => ''
							);
				}
				
				$result = [
					'status' => ($row) ? 'OK' : 'ERROR',
					// 'sql' => $q,
					'info' => $info
				];
			}
	
			echo json_encode($result);
			die;
		}

		// token
		if($_GET['cmd'] == 'get_token') {
			$result = [
				'status' => 'OK',
				'token' => genLoginToken()
			];
	
			echo json_encode($result);
			die;
		}

		// icon
		if($_GET['cmd'] == 'get_icons') {
			// $result = [];
			// $url_root = $gsValues['URL_ROOT'];
			// foreach(glob($gsValues['PATH_ROOT'] . 'img/icon/*.*') as $filename){
			// 	$result[] = str_replace($gsValues['PATH_ROOT'], "", $gsValues['URL_ROOT'] . $filename);
			// }

			$urlPath = $gsValues['URL_ROOT'] . 'img/markers/objects/';
			$result = getFileList('img/markers/places');
			for($i=0 ; $i < count($result) ; $i++)	{
				$result[$i] = $urlPath . $result[$i];
			}
			echo json_encode($result);
			die;
		}
	}
?>