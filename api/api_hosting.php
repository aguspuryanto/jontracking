<?
        if (@$api_access != true) { die; }
        
        // split command and params
        $cmd = explode(',', $cmd);
        $command = @$cmd[0];
        $command = strtoupper($command);
        
        if ($command == 'SERVER_CONFIG')
        {
                // command validation
                if (count($cmd) < 10) { die; }
                
                // command parameters
                $ip = $cmd[1];
                $active = $cmd[2];
                $plan_name = $cmd[3];
                $plan_period = $cmd[4];
                $plan_price = $cmd[5];
                $plan_last_payment = $cmd[6];
                $plan_next_payment = $cmd[7];
                $objects = $cmd[8];
                $history = $cmd[9];
                
                if (isset($_POST['paypal_url']))
                {
                        $paypal_url = $_POST['paypal_url'];
                }
                else
                {
                        $paypal_url = false;
                }
               
                setServerConfig($ip, $active, $plan_name, $plan_period, $plan_price, $plan_last_payment, $plan_next_payment, $paypal_url, $objects, $history);
        }
        
        function setServerConfig($ip, $active, $plan_name, $plan_period, $plan_price, $plan_last_payment, $plan_next_payment, $paypal_url, $objects, $history)
        {
                $str = '$gsValues[\'SERVER_IP\'] = \''.$ip.'\';'."\r\n";
                $str .= '$gsValues[\'SERVER_ENABLED\'] = \''.$active.'\';'."\r\n";
                $str .= '$gsValues[\'SERVER_PLAN_NAME\'] = \''.$plan_name.'\';'."\r\n";
                $str .= '$gsValues[\'SERVER_PLAN_PERIOD\'] = \''.$plan_period.'\';'."\r\n";
                $str .= '$gsValues[\'SERVER_PLAN_PRICE\'] = \''.$plan_price.'\';'."\r\n";
                $str .= '$gsValues[\'SERVER_PLAN_LAST_PAYMENT\'] = \''.$plan_last_payment.'\';'."\r\n";
                $str .= '$gsValues[\'SERVER_PLAN_NEXT_PAYMENT\'] = \''.$plan_next_payment.'\';'."\r\n";
                $str .= '$gsValues[\'SERVER_PLAN_PAYPAL_URL\'] = \''.$paypal_url.'\';'."\r\n";
                $str .= '$gsValues[\'OBJECT_LIMIT\'] = '.$objects.';'."\r\n";
                $str .= '$gsValues[\'HISTORY_PERIOD\'] = '.$history.';'."\r\n";
                
                $str = "<?\r\n".$str. "?>";
                
                $handle = fopen('../config.hosting.php', 'w');
                fwrite($handle, $str);
                fclose($handle);
        }
?>