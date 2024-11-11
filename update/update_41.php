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
	
	$q = "alter table gs_objects add column unassign_driver varchar(5) not null after `accuracy`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_share_position add column delete_expired varchar(5) not null after `expire_dt`";
	$r = mysqli_query($ms, $q);
	
	$q = "UPDATE gs_user_share_position SET `delete_expired` = 'false'";
	$r = mysqli_query($ms, $q);
	
	$gsValuesNew['KML'] = 'true';
	
	$config = '';
	foreach ($gsValuesNew as $key => $value)
	{
		$config .= '$gsValues[\''.strtoupper($key).'\'] = "'.$value.'";'."\r\n";
	}
	
	$config = "<?\r\n".$config. "?>";
	
	file_put_contents('../config.custom.php', $config, FILE_APPEND | LOCK_EX);

	echo 'Script successfully finished!';
?>