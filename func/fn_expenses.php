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
	
	if(@$_POST['cmd'] == 'load_expense')
	{
		$expense_id = $_POST['expense_id'];
		
		$q = "SELECT * FROM `gs_user_expenses` WHERE `expense_id`='".$expense_id."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		// odometer and engine hours
		$row['odometer'] = floor(convDistanceUnits($row['odometer'], 'km', $_SESSION["unit_distance"]));
			
		$row['engine_hours'] = floor($row['engine_hours'] / 60 / 60);
		
		$result = array('dt_expense' => $row['dt_expense'],
						'name' => $row['name'],	
						'imei' => $row['imei'],											
						'quantity' => $row['quantity'],
						'cost' => $row['cost'],
						'supplier' => $row['supplier'],
						'buyer' => $row['buyer'],
						'odometer' => $row['odometer'],
						'engine_hours' => $row['engine_hours'],
						'desc' => $row['desc']);
		
		echo json_encode($result);
		die;
	}
	
	if(@$_POST['cmd'] == 'save_expense')
	{
		$expense_id = $_POST["expense_id"];
		$name = $_POST["name"];
		$imei = $_POST["imei"];
		$date = $_POST["date"];
		$quantity = $_POST["quantity"];
		$cost = $_POST["cost"];
		$supplier = $_POST["supplier"];
		$buyer = $_POST["buyer"];
		$odometer = $_POST["odometer"];
		$engine_hours = $_POST["engine_hours"];
		$desc = $_POST["desc"];
		
		$odometer = floor(convDistanceUnits($odometer, $_SESSION["unit_distance"], 'km'));
		
		$engine_hours = $engine_hours * 60 * 60;
		
		if ($expense_id == 'false')
		{
			$q = "INSERT INTO `gs_user_expenses`(	`dt_expense`,
                                                    `name`,
                                                    `imei`,
                                                    `quantity`,
													`cost`,
                                                    `supplier`,
													`buyer`,
													`odometer`,
													`engine_hours`,
													`desc`)
													VALUES
													('".$date."',
													'".$name."',
													'".$imei."',
													'".$quantity."',
													'".$cost."',
													'".$supplier."',
													'".$buyer."',
													'".$odometer."',
													'".$engine_hours."',
													'".$desc."')";
		}
		else
		{
			$q = "UPDATE `gs_user_expenses` SET  	`dt_expense`='".$date."',
													`name`='".$name."',
                                                    `imei`='".$imei."',
                                                    `quantity`='".$quantity."',
													`cost`='".$cost."',
                                                    `supplier`='".$supplier."',
													`buyer`='".$buyer."',
													`odometer`='".$odometer."',
													`engine_hours`='".$engine_hours."',
													`desc`='".$desc."'
													WHERE `expense_id`='".$expense_id."'";
		}

		$r = mysqli_query($ms, $q);
		
		echo 'OK';
		die;
	}
        
	if(@$_GET['cmd'] == 'load_expenses_list')
	{
		$page = $_GET['page']; // get the requested page
		$limit = $_GET['rows']; // get how many rows we want to have into the grid
		$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
		$sord = $_GET['sord']; // get the direction
		$search = caseToUpper(@$_GET['s']); // get search
		
		if(!$sidx) $sidx =1;
		
		 // get records number	
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_user_expenses.name AS expenses_name,
				gs_objects.*, gs_user_expenses.*
				FROM gs_objects
				INNER JOIN gs_user_expenses ON gs_objects.imei = gs_user_expenses.imei
				WHERE gs_user_expenses.imei IN (".$_SESSION["privileges_imei"].")";
		}
		else
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_user_expenses.name AS expenses_name,
				gs_objects.*, gs_user_expenses.*
				FROM gs_objects
				INNER JOIN gs_user_expenses ON gs_objects.imei = gs_user_expenses.imei
				WHERE gs_user_expenses.imei IN (".getUserObjectIMEIs($user_id).")";
		}
		
		if ($search != '')
		{
			$q .= " AND (UPPER(gs_objects.name) LIKE '%$search%' OR UPPER(gs_user_expenses.name) LIKE '%$search%' OR UPPER(gs_user_expenses.supplier) LIKE '%$search%' OR UPPER(gs_user_expenses.buyer) LIKE '%$search%')";	
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
				
		if ($_SESSION["privileges"] == 'subuser')
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_user_expenses.name AS expenses_name,
				gs_objects.*, gs_user_expenses.*
				FROM gs_objects
				INNER JOIN gs_user_expenses ON gs_objects.imei = gs_user_expenses.imei
				WHERE gs_user_expenses.imei IN (".$_SESSION["privileges_imei"].")";
		}
		else
		{
			$q = "SELECT gs_objects.name AS objects_name, gs_user_expenses.name AS expenses_name,
				gs_objects.*, gs_user_expenses.*
				FROM gs_objects
				INNER JOIN gs_user_expenses ON gs_objects.imei = gs_user_expenses.imei
				WHERE gs_user_expenses.imei IN (".getUserObjectIMEIs($user_id).")";
		}
		
		if ($search != '')
		{
			$q .= " AND (UPPER(gs_objects.name) LIKE '%$search%' OR UPPER(gs_user_expenses.name) LIKE '%$search%' OR UPPER(gs_user_expenses.supplier) LIKE '%$search%' OR UPPER(gs_user_expenses.buyer) LIKE '%$search%')";	
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
				$expense_id = $row['expense_id'];
				$date = $row['dt_expense'];
				$name = $row['expenses_name'];
				$imei = $row['imei'];
				$object_name = $row['objects_name'];			
				$qty = $row['quantity'];
				$cost = $row['quantity'] * $row['cost'].' '.$_SESSION["currency"];
				$supplier = $row['supplier'];
				$buyer = $row['buyer'];
				
				// set modify buttons
				$modify = '<a href="#" onclick="expensesProperties(\''.$expense_id.'\');" title="'.$la['EDIT'].'"><img src="theme/images/edit.svg" />';
				$modify .= '</a><a href="#" onclick="expensesDelete(\''.$expense_id.'\');" title="'.$la['DELETE'].'"><img src="theme/images/remove3.svg" /></a>';
				
				// set row
				$response->rows[$i]['id']=$expense_id;
				$response->rows[$i]['cell']=array($date,$name,$object_name,$qty,$cost,$supplier,$buyer,$modify);
				$i++;
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($response);
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_expense')
	{
		$expense_id = $_POST["expense_id"];
		
		$q = "DELETE FROM `gs_user_expenses` WHERE `expense_id`='".$expense_id."'";
		$r = mysqli_query($ms, $q);
		
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_selected_expenses')
	{
		$items = $_POST["items"];
		
		for ($i = 0; $i < count($items); ++$i)
		{
			$item = $items[$i];
			
			$q = "DELETE FROM `gs_user_expenses` WHERE `expense_id`='".$item."'";
			$r = mysqli_query($ms, $q);
		}
		
		echo 'OK';
		die;
	}
?>