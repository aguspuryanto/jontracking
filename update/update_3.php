<?
	error_reporting(E_ALL ^ E_DEPRECATED);

	session_start();
	set_time_limit(0);
	
	include ('../config.php');
	include ('../config.custom.php');
	
	$ms = mysqli_connect($gsValues['DB_HOSTNAME'], $gsValues['DB_USERNAME'], $gsValues['DB_PASSWORD'], $gsValues['DB_NAME'], $gsValues['DB_PORT']);
	if (!$ms)
	{
	    echo "Error connecting to database.";
	    die;
	}
	
	$q = "SET @@global.sql_mode= '';";
	$r = mysqli_query($ms, $q);
	
	// --------------------------------------------------------
	// modify database tables
	// --------------------------------------------------------
	
	$q = "CREATE TABLE IF NOT EXISTS `gs_user_expenses` (
			`expense_id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`dt_expense` date NOT NULL,
			`name` varchar(50) COLLATE utf8_bin NOT NULL,
			`imei` varchar(20) COLLATE utf8_bin NOT NULL,  
			`quantity` double NOT NULL,
			`cost` double NOT NULL,
			`supplier` varchar(64) COLLATE utf8_bin NOT NULL,
			`buyer` varchar(64) COLLATE utf8_bin NOT NULL,
			`odometer` double NOT NULL,
			`engine_hours` int(11) NOT NULL,
			`desc` varchar(2048) COLLATE utf8_bin NOT NULL,
			PRIMARY KEY (`expense_id`),
			KEY `user_id` (`user_id`),
			KEY `imei` (`imei`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;";
	$r = mysqli_query($ms, $q);
	
	$gsValuesNew['EXPENSES'] = 'true';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>