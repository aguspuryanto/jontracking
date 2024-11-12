<?
        // disable PHP xdebug module
        if(function_exists('xdebug_disable')) { xdebug_disable(); }
        error_reporting(E_ALL ^ E_DEPRECATED);
        
        // set 0 UTC timezone
        date_default_timezone_set('UTC');

        // date_default_timezone_set('Asia/Jakarta');
        
        $gsValues = array();
        
        include ('config.custom.php');
        include ('config.php');
        
        @include ('config.hosting.php');
        
        // strip server name slashes
        $gsValues['NAME'] = stripcslashes($gsValues['NAME']);
        
        // check for some variables
        if (!isset($gsValues['CONNECTION_TIMEOUT']))
        {
                $gsValues['CONNECTION_TIMEOUT'] = '5';
        }
        
        if (!isset($gsValues['URL_LOGIN']))
        {
            $gsValues['URL_LOGIN'] = $gsValues['URL_ROOT'];
        }
        
        // check for last slash in root path
        if (substr($gsValues['PATH_ROOT'], -1) != '/')
        {
                $gsValues['PATH_ROOT'] .= '/';
        }
        
        // prepare language array
        $la = array();
        
        // gets language from cookies
        if (isset($_COOKIE['gs_language']))
        {
                $gsValues['LANGUAGE'] = $_COOKIE['gs_language'];
        }
        else
        {
                $expire = time() + 2592000;
                setcookie('gs_language', $gsValues['LANGUAGE'], $expire, '/');
        }
        
        // puts selected language into cookies
        if (isset($_GET['lng']))
        {
                $gsValues['LANGUAGE'] = $_GET['lng'];
                $expire = time() + 2592000;
                setcookie('gs_language', $gsValues['LANGUAGE'], $expire, '/');
        }
        
        // connect to mysql  
        $ms = mysqli_connect($gsValues['DB_HOSTNAME'], $gsValues['DB_USERNAME'], $gsValues['DB_PASSWORD'], $gsValues['DB_NAME'], $gsValues['DB_PORT']);
        // Check connection
        // if (!$ms)
        if($ms === false)
        {
                // echo "Error connecting to database.";
                // die;
                die("ERROR: Could not connect. " . mysqli_connect_error());
        }
        
        mysqli_set_charset($ms, 'utf8');
        
        $q = "SET SESSION sql_mode = ''";
        $r = mysqli_query($ms, $q);        
                
        // avoid injection attacks
        if(isset($_COOKIE))
        {
                $_COOKIE = initEscapeRecursive($_COOKIE);
        }
        
        if(isset($_POST))
        {
                $_POST = initEscapeRecursive($_POST);
        }
        
        if(isset($_GET))
        {
                $_GET = initEscapeRecursive($_GET);
        }
        
        function initEscapeRecursive($arr)
        {
                if(is_array($arr)){
                        $temp_arr = array();
                        foreach ($arr as $key=>$value){
                                $temp_arr[initEscapeString($key)] = initEscapeRecursive($value);
                        }
                        return $temp_arr;
                }
                else
                {
                        return initEscapeString($arr);
                }
        }
        
        function initEscapeString($value)
        {
                global $ms;
                
                if(get_magic_quotes_gpc())
                {
                        $value = stripslashes($value);
                }
                
                $value = strip_tags($value);
                
                return mysqli_real_escape_string($ms, $value);
        }

        // Close connection
        // mysqli_close($ms);
?>