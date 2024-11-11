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
	
	$q = "alter table gs_objects add column mileage_1 double not null after `dt_chat`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column mileage_2 double not null after `mileage_1`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column mileage_3 double not null after `mileage_2`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column mileage_4 double not null after `mileage_3`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column mileage_5 double not null after `mileage_4`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column dt_mileage datetime not null after `mileage_5`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_reports add column ignore_empty_reports varchar(5) not null after `type`";
	$r = mysqli_query($ms, $q);
	
	$q = "UPDATE gs_user_reports SET ignore_empty_reports='false'";
	$r = mysqli_query($ms, $q);

	$gsValuesNew['DASHBOARD'] = 'true';
	$gsValuesNew['SHOW_HIDE_PASSWORD'] = 'true';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>