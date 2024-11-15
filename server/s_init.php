<?
        // disable PHP xdebug module
        if(function_exists('xdebug_disable')) { xdebug_disable(); }
        error_reporting(E_ALL ^ E_DEPRECATED);
    
        // set 0 UTC timezone
        // date_default_timezone_set('UTC');

        date_default_timezone_set('Asia/Jakarta');
    
        $gsValues = array();
        
        include ('../config.custom.php');
        include ('../config.php');
        include ('../tools/push.php');
        include ('../tools/email.php');
        include ('../tools/sms.php');
        include ('../tools/webhook.php');
        
        if (file_exists('config.hosting.php') || @file_exists('../config.hosting.php'))
	{
		include ('../config.hosting.php');
	}
        
        // strip server name slashes
        $gsValues['NAME'] = stripcslashes($gsValues['NAME']);
        
        // check for last slash in root path
        if (substr($gsValues['PATH_ROOT'], -1) != '/')
        {
            $gsValues['PATH_ROOT'] .= '/';
        }
        
        // connect to mysql
        $ms = mysqli_connect($gsValues['DB_HOSTNAME'], $gsValues['DB_USERNAME'], $gsValues['DB_PASSWORD'], $gsValues['DB_NAME'], $gsValues['DB_PORT']);
        
        if (!$ms)
        {
            echo "Error connecting to database.";
            die;
        }
        
        mysqli_set_charset($ms, 'utf8');
        
        $q = "SET SESSION sql_mode = ''";
        $r = mysqli_query($ms, $q);
                
        // avoid injection attacks
        if(isset($_COOKIE))
        {
                foreach ($_COOKIE as $key => $value)
                {
                        if(!is_array($value))
                        {
                                if(get_magic_quotes_gpc())
                                {
                                        $value = stripslashes($value);
                                }
                                
                                $value = mysqli_real_escape_string($ms, $value);
                        }
                        
                        $_COOKIE[$key] = $value;
                }
        }
        
        if(isset($_POST))
        {
                foreach ($_POST as $key => $value)
                {
                        if(!is_array($value))
                        {
                                if(get_magic_quotes_gpc())
                                {
                                        $value = stripslashes($value);
                                }
                                
                                $value = mysqli_real_escape_string($ms, $value);
                        }
                        
                        $_POST[$key] = $value;
                }
        }
        
        if(isset($_GET))
        {
                foreach ($_GET as $key => $value)
                {
                        if(!is_array($value))
                        {
                                if(get_magic_quotes_gpc())
                                {
                                        $value = stripslashes($value);
                                }
                                
                                $value = mysqli_real_escape_string($ms, $value);
                        }
                    
                        $_GET[$key] = $value;
                }
        }
?>