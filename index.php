<?
	if(isset($_GET['au']))
	{
		session_start();
		
		if (isset($_SESSION["user_id"]))
		{
			session_unset();
			session_destroy();
			session_start();
		}
		
		include ('init.php');
		include ('func/fn_common.php');
		
		$au = $_GET['au'];
		$mobile = @$_GET["m"];
		$user_id = getUserIdFromAU($au);
		
		if ($user_id == false)
		{
			if ($mobile == 'true')
			{
				header('Location: mobile/index.php');
				die;
			}
			else
			{
				header('Location: index.php');
				die;
			}
		}
		
		setUserSession($user_id);
		setUserSessionSettings($user_id);
		setUserSessionCPanel($user_id);
		
		//write log
		writeLog('user_access', 'User login via URL: successful');
		
		if ($mobile == 'true')
		{
			header('Location: mobile/tracking.php');
			die;
		}
		else
		{
			header('Location: tracking.php');
			die;
		}
		die;
	}
	
	if(isset($_GET['su']))
	{
		if(isset($_GET['m']))
		{
			header('Location: mod/share/index.php?su='.$_GET['su'].'&m=true');
		}
		else
		{
			header('Location: mod/share/index.php?su='.$_GET['su']);	
		}
		die;
	}
	
	session_start();
	include ('init.php');
	include ('func/fn_common.php');
	checkUserSession();
	
	loadLanguage($gsValues['LANGUAGE']);
	
	if (isset($gsValues['SERVER_ENABLED']))
	{
		if ($gsValues['SERVER_ENABLED'] == 'false')
		{
			echo 'Access to this server currently disabled.';
			die;
		}       
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex">
	<meta name="googlebot" content="noindex">
	<title><? echo $gsValues['NAME'].' '.$gsValues['VERSION']; ?></title>
	
	<?
		if (file_exists('favicon.png'))
		{
			echo '<link rel="shortcut icon" href="'.$gsValues['URL_ROOT'].'/favicon.png" type="image/x-icon">';
		}
		else
		{
			echo '<link rel="shortcut icon" href="'.$gsValues['URL_ROOT'].'/favicon.ico" type="image/x-icon">';
		}	
	?>
	
	<link type="text/css" href="theme/jquery-ui.css?v=<? echo $gsValues['VERSION_ID']; ?>" rel="Stylesheet" />
	<link type="text/css" href="theme/jquery.multiple.css?v=<? echo $gsValues['VERSION_ID']; ?>" rel="Stylesheet" />
	<link type="text/css" href="theme/style.css?v=<? echo $gsValues['VERSION_ID']; ?>" rel="Stylesheet" />
	<link type="text/css" href="theme/style.custom.php?v=<? echo $gsValues['VERSION_ID']; ?>" rel="Stylesheet" />
	
	<script type="text/javascript" src="js/jquery-2.1.4.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
	<script type="text/javascript" src="js/jquery-migrate-1.2.1.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
	<script type="text/javascript" src="js/jquery.multiple.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
	<script type="text/javascript" src="js/jquery.show-pass.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
	
	<script type="text/javascript" src="js/gs.common.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
	<script type="text/javascript" src="js/gs.connect.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
	<style>
		.clear_buttons {
            display: inline-flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
        }
		.clear_buttons a {
            /* display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px; */
}
	</style>
</head>

<body id="login" onload="connectLoad()">
	<div id="loading_panel"></div>
	
	<div id="dialog_notify" title="" style="display: none;">
		<div class="row">
			<div class="row2">
				<div class="width100 center-middle">
					<span id="dialog_notify_text"></span>
				</div>
			</div>
		</div>
		<center>
			<input class="button" type="button" onclick="$('#dialog_notify').dialog('close');" value="<? echo $la['OK']; ?>" />
		</center>
	</div>
	
	<div class="wrapper">
		<div class="inner-wrapper">
			<div class="logo-block">
				<img class="logo" src="<? echo $gsValues['URL_ROOT'].'/img/'.$gsValues['LOGO']; ?>" />	
			</div>
			
			<div class="server-select">
				<div class="row3">
				<? if ($gsValues['MULTI_SERVER_LOGIN'] == true) {
					if ($gsValues['ALLOW_REGISTRATION'] == "false") {
						echo '<div class="registration-closed">'.$la['NEW_USER_REGISTRATION_ON_THIS_SERVER_IS_CLOSED'].'</div>';
					}
					
					echo '<select id="server" class="select width100" onChange="connectServer();">';
					
					foreach ($gsValues['MULTI_SERVER_LIST'] as $key => $value) {
						if ($gsValues['URL_ROOT'] == $key) {
							echo '<option selected value="'.$key.'">'.$value.'</option>';
						} else {
							echo '<option value="'.$key.'">'.$value.'</option>';
						}
					}
					echo '</select>';
					}
				?>
				</div>
				
			</div>
							
			<div class="content">
				<div id="connect" class="content-block">
					<form action="#" target="" autocomplete="on">
						<div class="row3">
							<input placeholder="<? echo $la['USERNAME']; ?>" class="inputbox icon icon-user" id="username" maxlength="50">
						</div>
						<div class="row3" style="position: relative;">
							<? if ($gsValues['SHOW_HIDE_PASSWORD'] == "true"){?>
								<div class="reveal" title="<? echo $la['SHOW_HIDE_PASSWORD']; ?>"></div>
							<? }?>
							<input placeholder="<? echo $la['PASSWORD']; ?>" class="inputbox icon icon-password" type="password" id="password" maxlength="20">
						</div>
						
						<div class="submit-btn">
							<input type="submit" class="button" value="<? echo $la['LOGIN']; ?>" onClick="connectLogin(); return false;"/>
							<p style="text-align: center;font-weight:bolder"><a href="demo.php">Demo</a></p>
							<div class="remember-block">
									<label for="remember_me" class="custom-checkbox" title="<? echo $la['REMEMBER_ME']; ?>"></label>				
									<input class="checkbox float-right" type="checkbox" id="remember_me" name="checkbox-img" value="0">
							</div>
						</div>
					</form>
					
					<ul class="recover-register-block">
						<li><a href="#recover"><? echo $la['RECOVER_PASSWORD']; ?></a></li>
						<? if ($gsValues['ALLOW_REGISTRATION'] == "true"){?>
						<span><? echo $la['OR']; ?></span>
						<li><a href="#register"><? echo caseToLower($la['CREATE_ACCOUNT']); ?></a></li>
						<? }?>
					</ul>
				</div>
				
				<div id="recover" class="content-block">
					<div class="row3">
						<input placeholder="<? echo $la['EMAIL']; ?>" class="inputbox icon icon-email" id="rec_email" maxlength="50" />
					</div>
					<input type="hidden" id="rec_token" value="<? echo genLoginToken(); ?>" />
					<input type="button" class="button" value="<? echo $la['RECOVER']; ?>" onClick="connectRecoverURL();"/>

					<ul class="recover-register-block">
						<li><a href="#connect"><? echo $la['LOGIN']; ?></a></li>
						<? if ($gsValues['ALLOW_REGISTRATION'] == "true"){?>
						<span><? echo $la['OR']; ?></span>
						<li><a href="#register"><? echo caseToLower($la['CREATE_ACCOUNT']); ?></a></li>
						<? }?>
					</ul>
				</div>
				
				<? if ($gsValues['ALLOW_REGISTRATION'] == "true"){?>
				<div id="register" class="content-block">
					<div class="row3">
						<input placeholder="<? echo $la['EMAIL']; ?>" class="inputbox icon icon-email" id="reg_email" maxlength="50" />
					</div>
					<input type="hidden" id="reg_token" value="<? echo genLoginToken(); ?>" />
					<input type="button" class="button" value="<? echo $la['REGISTER']; ?>" onClick="connectRegister();"/>
					<ul class="recover-register-block">
						<li><a href="#connect"><? echo $la['LOGIN']; ?></a></li>
						<span><? echo $la['OR']; ?></span>
						<li><a href="#recover"><? echo caseToLower($la['RECOVER_PASSWORD']); ?></a></li>
					</ul>
				</div>
				<? }?>
			</div>
	
			<div class="footer">
				<div class="float-left">
					<a class="mobile-v" href="mobile/index.php"><? echo $la['MOBILE_VERSION']; ?></a>
				</div>
				<div class="float-right">
					<select id="system_language" class="select float-right" onChange="switchLanguageLogin();"><? echo getLanguageList(); ?></select>
				</div>
				<div class="clear" style="text-align: center;">
					<div class="clear_buttons">
						<!-- <a href="demo.php"><img src="img/live-demo-button.png" alt="Live Demo" style="width:140px;height:40px;"></a> -->
						<a href="https://play.google.com/store/apps/details?id=com.jonusa.server"><img src="img/google_play_store.png" style="height: 40px;"/></a>
						<img src="img/apple_app_store.png" style="height: 80px;"/>
					</div>
					<br>
					<h3 style="margin-bottom: 0;"><a href="privacy.php">Kebijakan dan Privasi</a></h3>
					<!-- <span style="display: block;">Server IP: <? echo $_SERVER['SERVER_ADDR']; ?> atau gsi-tracking.com</span>
					<span style="display: block;">Info Port: <a rel="nofollow" href="https://supporteddevices.net/gsi-tracking.com">supporteddevices.net/gsi-tracking.com</a></span>
					<span style="display: block;">&copy; 2018 - <? echo date('Y'); ?>. All rights reserved</span> -->
				</div>
			</div>
			
			<?
				$theme = getTheme();
				
				if (isset($theme["login_dialog_bottom_text"]))
				{	if ($theme["login_dialog_bottom_text"] != '')
					{
						echo '<div class="footer text">'.$theme["login_dialog_bottom_text"].'</div>';
					}
				}
			?>
		</div>
	</div>
	
</body>
</html>