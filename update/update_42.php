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
	
	$q = "alter table gs_user_object_drivers modify column driver_assign_id varchar(50) not null";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_object_passengers modify column passenger_assign_id varchar(50) not null";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_object_trailers modify column trailer_assign_id varchar(50) not null";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_rilogbook_data modify column assign_id varchar(50) not null";
	$r = mysqli_query($ms, $q);
	
	
	$gsValuesNew['OBJECT_CONTROL_DEFAULT_TEMPLATES'] = 'true';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>