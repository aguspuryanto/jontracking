<?
	ob_start();
	echo "OK";
	//header("Content-length: " . (string)ob_get_length());
	ob_end_flush();
	
	chdir('../');
	include ('s_insert.php');
   //$json = '';
file_put_contents("data.txt",file_get_contents('php://input'),FILE_APPEND);
file_put_contents("data.txt","\n\n\n",FILE_APPEND);


?>