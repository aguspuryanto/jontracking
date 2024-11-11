<?
//error_reporting(E_ALL ^ E_DEPRECATED);
class gps {

    public static $host = "http://localhost:8082";
    private static $adminEmail='admin';
    private static $adminPassword='-FlodIadIm6'; //dP $adminPassword='admin';
    public static $cookie;
    private static $jsonA='Accept: application/json';
    private static $jsonC='Content-Type: application/json';
    private static $urlEncoded='Content-Type: application/x-www-form-urlencoded';


//Server
    public static function server(){

        return self::curl('/api/server?'.$data,'GET',$sessionId,'',array());
    }


//Session
    public static function loginAdmin(){

        $data='email='.self::$adminEmail.'&password='.self::$adminPassword;
        return self::curl('/api/session','POST','',$data,array(self::$urlEncoded));
    }

    public static function login($email,$password){

        $data='email='.$email.'&password='.$password;

        return self::curl('/api/session','POST','',$data,array(self::$urlEncoded));
    }

    public static function deviceAdd($sessionId,$name,$uniqueId){

        $id = '-1';

        $data='{"name":"'.$name.'","uniqueId":"'.$uniqueId.'"}';

        return self::curl('/api/devices','POST',$sessionId,$data,array(self::$jsonC));
    }

    public static function logout($sessionId){

        return self::curl('/api/session','DELETE',$sessionId,'',array(self::$urlEncoded));
    }

    public static function session($sessionId){

        return self::curl('/api/session?'.$data,'GET',$sessionId,'',array());
    }

    public static function commandSend($sessionId,$deviceId,$type,$attributes){

        $id = '0';
        $description = 'true';
        $textChannel = 'false';

        $data='{"type":"'.$type.'","deviceId":"'.$deviceId.'","attributes":'.$attributes.',"description":'.$description.'}';

        return self::curl('/api/commands/send','POST',$sessionId,$data,array(self::$jsonC));
    }


//curl
    public static function curl($task,$method,$cookie,$data,$header) {

        $res=new stdClass();
        $res->responseCode='';
        $res->error='';
        $header[]="Cookie: ".$cookie;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$host.$task);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if($method=='POST' || $method=='PUT' || $method=='DELETE') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        $data=curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', substr($data, 0, $size), $c) == 1) self::$cookie = $c[1];
        $res->response = substr($data, $size);

        if(!curl_errno($ch)) {
           //echo $res->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        else {
            $res->responseCode=400;
            $res->error= curl_error($ch);
        }

        curl_close($ch);
        return $res;
    }
}
?>
