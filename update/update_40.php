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
	
	$q = "alter table gs_objects add column last_img_file varchar(50) not null after `dt_mileage`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column accvirt varchar(5) not null after `accuracy`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_objects add column accvirt_cn varchar(128) not null after `accvirt`";
	$r = mysqli_query($ms, $q);
	
	$q = "alter table gs_user_share_position add column phone varchar(100) not null after `email`";
	$r = mysqli_query($ms, $q);
	
	$q = "UPDATE gs_templates SET name = 'share_position_su_email' WHERE name = 'share_position_su'";
	$r = mysqli_query($ms, $q);
	
	$q = "INSERT INTO `gs_templates` (`name`, `language`, `subject`, `message`) VALUES";
	$q .= " ('share_position_su_sms', 'english', 'Share position (%NAME%)', '%USER_EMAIL% has shared position: %URL_SU_MOBILE%')";
	$r = mysqli_query($ms, $q);
	
	$q = "UPDATE gs_templates SET message = 'Hello,\n\n%USER_EMAIL% has shared with you position of object %NAME%.\n\nDesktop access:\n%URL_SU%\n\nMobile access:\n%URL_SU_MOBILE%' WHERE name = 'share_position_su_email' AND language = 'english'";
	$r = mysqli_query($ms, $q);

	echo 'Script successfully finished!';
?>