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
	
	$q = "alter table gs_user_reports add column markers_addresses varchar(5) not null after `show_addresses`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column dt_last_params_ble datetime not null after `params`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_webhook_queue add column post_data varchar(4096) not null after `webhook_url`";
	$r = mysqli_query($ms, $q);
	
	
	$gsValuesNew['SIM_NUMBER'] = 'true';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>