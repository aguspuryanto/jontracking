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
	
	$q = "alter table gs_user_routes modify column route_deviation double not null";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_share_position drop index imei";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_share_position modify column imei text not null";
	$r = mysqli_query($ms, $q);
	
	$gsValuesNew['MAP_ARCGIS'] = 'false';
	$gsValuesNew['MAP_ARCGIS_KEY'] = '';
	$gsValuesNew['USER_MAP_ARCGIS'] = 'false';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>