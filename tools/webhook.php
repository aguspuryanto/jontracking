<?
        function sendWebhookQueue($url, $post_data)
        {
                global $ms;
		
		$q = "INSERT INTO `gs_webhook_queue` 	(`dt_webhook`,
							`webhook_url`,
                                                        `post_data`)
							VALUES
							('".gmdate("Y-m-d H:i:s")."',
							'".$url."',
                                                        '".$post_data."')";
		$r = mysqli_query($ms, $q);
                
                if ($r)
                {
                        return true;
                }
                else
                {
                        return false;
                }
        }
        
        function sendWebhook($url, $post_data)
        {
                if ($url != '')
                {
                        $context = stream_context_create(array('http' => array('method' => 'POST',
                                                                               'header' => 'Content-type: application/x-www-form-urlencoded',
                                                                               'content' => $post_data,
                                                                               'timeout' => 3), 'ssl' => array('verify_peer' => false)));                        
                        $result = @file_get_contents($url, false, $context);
                                
                        return true;
                }
                else
                {
                        return false;
                }
        }
        
        function sendWebhookCURL($webhooks)
        {                
                $curl_arr = array();
                $master = curl_multi_init(); 
                
                for ($i = 0; $i < count($webhooks); $i++)
                {
                        $curl_arr[$i] = curl_init($webhooks[$i]['webhook_url']);
			curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl_arr[$i], CURLOPT_POST, true);
			curl_setopt($curl_arr[$i], CURLOPT_POSTFIELDS, $webhooks[$i]['post_data']);
			curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl_arr[$i], CURLOPT_CONNECTTIMEOUT, 3);
			curl_multi_add_handle($master, $curl_arr[$i]); 
                }
                
                do
		{
			curl_multi_exec($master, $running);
		}
		while ($running > 0);
		
		for ($i = 0; $i < count($webhooks); $i++)
		{
			$result = curl_multi_getcontent($curl_arr[$i]);
                        curl_multi_remove_handle($master, $curl_arr[$i]);
		}

                curl_multi_close($master);		
		unset($curl_arr);
                
                return true;
        }
?>