<?php
echo "fcm";

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
 "to" : "f1dY9EWYQGyzX47dZaELwK:APA91bFHvEv7V6w_sNioyDS9SpKJfjNXsByknxKKbs81P49ljUvy3xEF05PkXJBEnfLUTiU3zOURrT4tT6QFF4M4DNuEzkGOPVPsgeAUVX1bv_5SVpdm2roIKtcDuuUNftTwijXIhlvR",
 "collapse_key" : "type_a",
 "notification" : {
     "body" : "Body of Your Notification",
     "title": "Title of Your Notification"
 },
 "data" : {
     "body" : "Body of Your Notification in Data",
     "title": "Title of Your Notification in Title",
     "key_1" : "Value for key_1",
     "key_2" : "Value for key_2"
 }
}',
    CURLOPT_HTTPHEADER => array(
        'Authorization: key=AAAAGOimNTo:APA91bEV6ltxh4VQ9PWKtL6-_7vWXYeF-xvq1ZqQTLvYXjTUtMfNXqUH3YhhbY8yoxE1ZWk41srH1JHUEmOsOWtwftJmsafNBrOAW7AcVTfkC-bGhG-YFt628Ld3aQH4ROWjscDN3B5T',
        'Content-Type: application/json'
    ),
));

print_r($curl);

echo $response = curl_exec($curl);

curl_close($curl);
echo $response;
