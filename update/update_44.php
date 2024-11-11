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
	
	// check for last slash in root path
	if (substr($gsValues['PATH_ROOT'], -1) != '/')
	{
			$gsValues['PATH_ROOT'] .= '/';
	}
	
	// --------------------------------------------------------
	// modify database tables
	// --------------------------------------------------------

	$q = "alter table gs_user_reports add column marker_ids text not null after `imei`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_reports_generated add column markers int(11) not null after `objects`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_markers add column marker_radius double not null after `marker_lng`";
	$r = mysqli_query($ms, $q);
	
	$q = "UPDATE gs_user_markers SET marker_radius = 0.1";
	$r = mysqli_query($ms, $q);

	echo 'Script successfully finished!';
?>