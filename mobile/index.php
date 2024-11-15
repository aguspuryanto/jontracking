<?php
	session_start();
	include ('../init.php');
	include ('../func/fn_common.php');
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
        
        // set mobile app cookie
        if (isset($_GET['app']))
        {
                $expire = time() + 2592000;
                setcookie('app', $_GET['app'], $expire, '/');
                $_SESSION['app'] = $_GET['app'];
        }
        else
        {
                // get mobile app cookie
                if (isset($_COOKIE['app']))
                {
                        $_SESSION['app'] = $_COOKIE['app'];
                }
                else
                {
                        $_SESSION['app'] = 'false';
                }
        }
        
        // push notifications cookie
	$expire = time() + 2592000;
	setcookie('push_notify_identifier', '', $expire, '/');
    setcookie('push_notify_mobile', 'false', $expire, '/');
	setcookie('push_notify_mobile_interval', '', $expire, '/');
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex">
		<meta name="googlebot" content="noindex">
        <title><? echo $gsValues['NAME'].' '.$gsValues['VERSION']; ?></title>
        
		<?
			if (file_exists('../favicon.png'))
			{
				echo '<link rel="shortcut icon" href="'.$gsValues['URL_ROOT'].'/favicon.png" type="image/x-icon">';
			}
			else
			{
				echo '<link rel="shortcut icon" href="'.$gsValues['URL_ROOT'].'/favicon.ico" type="image/x-icon">';
			}	
		?>
        
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    
        <link href="theme/bootstrap.css?v=<? echo $gsValues['VERSION_ID']; ?>" rel="stylesheet">
        <link href="theme/style.css?v=<? echo $gsValues['VERSION_ID']; ?>" rel="stylesheet">
        <link href="theme/style.custom.php?v=<? echo $gsValues['VERSION_ID']; ?>" rel="Stylesheet" />
        
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
                <script type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
                <script type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
        <![endif]-->
        
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script type="text/javascript" src="../js/jquery-2.1.4.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
        
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script type="text/javascript" src="js/bootstrap.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
		<script type="text/javascript" src="js/bootbox.min.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
        <script type="text/javascript" src="../js/gs.common.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
        <script type="text/javascript" src="js/gs.connect.js?v=<? echo $gsValues['VERSION_ID']; ?>"></script>
</head>

<body class="login-page" onload="connectLoad()">
        <nav class="navbar navbar-default">
                  <div class="container-fluid">
                        <div class="navbar-header">
                                <div class="navbar-brand">
									<? echo sprintf($la['CONNECT_TO'], $gsValues['NAME']); ?>
                                </div>
                        </div>
                </div>
        </nav>
		
		<center>
			<img class="logo" src="<? echo $gsValues['URL_ROOT'].'/img/'.$gsValues['LOGO']; ?>" />
		</center>	
		
        <?
			if($_SESSION['app'] == 'false')
			{
				if ($gsValues['MULTI_SERVER_LOGIN'] == true)
				{
					echo '<div class="form-group">';
					echo '<label>'.$la['SERVER'].'</label>';
					echo '<select id="server" class="form-control" onChange="connectServer();">';
					
					foreach ($gsValues['MULTI_SERVER_LIST'] as $key => $value)
					{
						if ($gsValues['URL_ROOT'] == $key)
						{
							echo '<option selected value="'.$key.'">'.$value.'</option>';
						}
						else
						{
							echo '<option value="'.$key.'">'.$value.'</option>';
						}
					}
					echo '</select>';
					echo '</div>';
				}      
			}
        ?>
	
        <form action="#" target="" autocomplete="on">
		<label><? echo $la['LOGIN_DETAILS']; ?></label>
		
		<div class="input-group">
			<span class="input-group-addon" id="sizing-addon2"><i class="glyphicon glyphicon-user"></i></span>
			<input id="username" type="text" class="form-control" placeholder="<? echo $la['USERNAME']; ?>" aria-describedby="sizing-addon2">
		</div>
		
		<br>
		
		<div class="input-group">
			<span class="input-group-addon" id="sizing-addon2"><i class="glyphicon glyphicon-openlock"></i></span>
			<input id="password" type="password" class="form-control" placeholder="<? echo $la['PASSWORD']; ?>" aria-describedby="sizing-addon2">
		</div>
		
		<br>
                
		<div class="block1 width50 pull-left">
			<label class="remember-me"><? echo $la['REMEMBER_ME']; ?></label>
			<div class="form-group pull-left">
				<input id="remember_me" type="checkbox" name="fancy-checkbox-default" autocomplete="off" />
				<div class="btn-group btn-remember-me">
					<label for="remember_me" class="btn btn-default">
						<span class="glyphicon glyphicon-ok"></span>
						<span> </span>
					</label>
				</div>
			</div>
		</div>
                
		<div class="block2 width50 pull-right">
			<div class="form-group">
				<a type="submit" class="btn btn-blue btn-default dropdown-toggle login-btn" aria-haspopup="true" aria-expanded="false" href="#" onClick="connectLogin(); return false;">
					<i class="glyphicon glyphicon-log-in"></i>
					<? echo $la['LOGIN']; ?>
				</a>
			</div>
		</div>
                
		<div class="buttons-block clearfix">
			<? if($_SESSION['app'] == 'false') { ?>
			<div class="block1 width50 pull-left">
				<div class="form-group">
					<a class="btn btn-default dropdown-toggle desktop-btn" aria-haspopup="true" aria-expanded="false" href="../index.php">
						<i class="glyphicon glyphicon-desktop"></i>
						<? echo $la['DESKTOP_VERSION']; ?>
					</a>
				</div>
			</div>
			<? } ?>
			
			<div class="block2 width50 pull-right">
				<div class="form-group">
					<select style="width: 100%;" id="system_language" class="form-control" onChange="switchLanguageLogin();">
							<? echo getLanguageList(); ?>
					</select>
				</div>
			</div>
		</div>
	</form>
</body>
</html>