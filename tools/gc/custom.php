<?
	$gsValues = array();
	include ('../../config.custom.php');
	
	if(@$_GET['cmd'] == 'latlng')
	{
		$result = '';
		
		$search = $_GET["lat"].','.$_GET["lng"];
		$search = htmlentities(urlencode($search));
		
		// custom code for address search using lat and lng
		
		echo json_encode($result);
		die;
	}
	
	if(@$_GET['cmd'] == 'address')
	{
		$result = array();
		
		$search = htmlentities(urlencode($_GET["search"]));
		
		// custom code for lat and lng search using address
		
		echo json_encode($result);
		die;
	}
?>