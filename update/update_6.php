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
	
	$q = "CREATE TABLE IF NOT EXISTS `gs_user_share_position` (
			`share_id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`active` varchar(5) COLLATE utf8_bin NOT NULL,
			`expire` varchar(5) COLLATE utf8_bin NOT NULL,
			`expire_dt` date NOT NULL,
			`name` varchar(50) COLLATE utf8_bin NOT NULL,
			`email` varchar(100) COLLATE utf8_bin NOT NULL,
			`imei` varchar(20) COLLATE utf8_bin NOT NULL,
			`su` varchar(50) COLLATE utf8_bin NOT NULL,
			PRIMARY KEY (`share_id`),
			KEY `user_id` (`user_id`),
			KEY `imei` (`imei`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;";
	$r = mysqli_query($ms, $q);
	
	$q = "INSERT INTO `gs_templates` (`name`, `language`, `subject`, `message`) VALUES";
	$q .= " ('share_position_su', 'english', 'Share position (%NAME%)', 'Hello,\n\n%USER_EMAIL% has shared with you position of object %NAME%.\n\nAccess URL:\n%URL_SU%')";
	$r = mysqli_query($ms, $q);

	echo 'Script successfully finished!';
?>