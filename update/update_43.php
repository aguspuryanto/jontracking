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
	
	$q = "alter table gs_objects add column forward_loc_data varchar(5) not null after `accvirt_cn`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column forward_loc_data_imei varchar(20) not null after `forward_loc_data`";
	$r = mysqli_query($ms, $q);
	
	
	$gsValuesNew['BILLING_PAYPALV2_ACCOUNT'] = '';
	$gsValuesNew['BILLING_PAYPALV2_CLIENT_ID'] = '';
	$gsValuesNew['BILLING_PAYPALV2_CUSTOM'] = '';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>