<?
	function sendPushQueue($identifier, $type, $message)
	{
		if ($identifier == '')
		{
			return false;
		}

	//	global $ms;

//		$q = "INSERT INTO `gs_push_queue` 	(`dt_push`,
//							`identifier`,
//							`type`,
//							`message`)
//							VALUES
//							('".gmdate("Y-m-d H:i:s")."',
//							'".$identifier."',
//							'".$type."',
//							'".mysqli_real_escape_string($ms, $message)."')";
//		$r = mysqli_query($ms, $q);

        sendFCM($identifier, $message);
//		if ($r)
//		{
//				return true;
//		}
//		else
//		{
//				return false;
//		}
	}

function sendFCM($token, $message){


    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT_MS => 1000,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => FALSE,
        CURLOPT_POSTFIELDS => '{
 "to" : "'.$token.'",
 "collapse_key" : "type_a",
 "notification" : {
     "body" : "'.$message.'",
     "title": "Event",
     "sound":"default"
 },
 "data" : {
 }
}',
        CURLOPT_HTTPHEADER => array(
            'Authorization: key=AAAAJIu8k_U:APA91bHjXmVMz6SCsFjI9TPTh3o3BIHX-Z_7aMjIz46bGlYQdAIE-J0lBta9FlCvFrRRaHS1ujBmX7QKYijH-WtHN9igEonAK_8K-6YG2VGn_DdeLHuKfW2uz0VtJbkQHH77c-1jNZWV',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

}
?>
