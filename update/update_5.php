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
	
	$q = "CREATE TABLE IF NOT EXISTS `gs_user_kml` (
			`kml_id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`name` varchar(50) COLLATE utf8_bin NOT NULL,
			`active` varchar(5) COLLATE utf8_bin NOT NULL,
			`desc` varchar(1024) COLLATE utf8_bin NOT NULL,
			`kml_file` varchar(256) COLLATE utf8_bin NOT NULL,
			`file_name` varchar(256) COLLATE utf8_bin NOT NULL,
			`file_size` double NOT NULL,
			PRIMARY KEY (`kml_id`),
			KEY `user_id` (`user_id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;";
			  $r = mysqli_query($ms, $q);

	$q = "alter table gs_users add column usage_webhook_daily varchar(8) not null after `usage_sms_daily`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_users add column usage_webhook_daily_cnt int(11) not null after `usage_sms_daily_cnt`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_usage add column webhook int(11) not null after `sms`";
	$r = mysqli_query($ms, $q);
	
	$gsValuesNew['TACHOGRAPH'] = 'true';
	$gsValuesNew['USAGE_WEBHOOK_DAILY'] = '100';
	$gsValuesNew['SERVER_API_IP'] = '';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>