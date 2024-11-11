<?
	if(!isset($_GET['currency'])){die;}
	if(!isset($_GET['name'])){die;}
	if(!isset($_GET['price'])){die;}	
	if(!isset($_GET['custom'])){die;}

	$currency = $_GET['currency'];
	$name = $_GET['name'];
	$price = $_GET['price'];	
	$custom = $_GET['custom'];
	
	include ('../../init.php');
	
	if($gsValues['BILLING_PAYPALV2_CLIENT_ID'] == ''){die;}
?>

<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><? echo $gsValues['NAME'].' - Payment Gateway' ?></title>
	<link type="text/css" href="style.css" rel="Stylesheet" />
</head>

<body>
	<div id="payment_panel">
		<div class="table">
			<div class="table-cell center-middle">
				<div class="row">
					<img class="logo" src="<? echo $gsValues['URL_ROOT'].'/img/'.$gsValues['LOGO']; ?>" />
				</div>
				<div class="details">
					<div class="row">
						<div class="container last">			
							<div class="row2 text ">
								<div class="block width70">
									<strong><? echo $name; ?></strong>
								</div>
								<div class="block width30">
									<span class="float-right"><strong><? echo $price.' '.$currency; ?></strong></span>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="container last">
							<div class="row2 text">
								<div class="block width100">
									Purchased plan will appear in your account after payment is confirmed, sometimes it may take a while. 
								</div>								
							</div>
						</div>						
					</div>
				</div>
				<div class="row">
					<!-- Set up a container element for the button -->
					<div id="paypal-button-container" style="width: 360px; display: inline-block;"></div>
				</div>
			</div>    
		</div>
	</div>
	
    <!-- Include the PayPal JavaScript SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=<? echo $gsValues['BILLING_PAYPALV2_CLIENT_ID']; ?>&currency=<? echo $currency; ?>"></script>

    <script>		
        // Render the PayPal button into #paypal-button-container
        paypal.Buttons({
			
			// Style
		    style: {
                color:  'blue',
                label:  'pay',
                height: 40//,
				//layout: 'horizontal'
            },
			
            // Set up the transaction
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
							value: '<? echo $price; ?>',
                        },
						description: '<? echo $name; ?>',
						custom_id : '<? echo $custom; ?>'
                    }],
					application_context: {
						shipping_preference: 'NO_SHIPPING'
					}
                });
            },

            // Finalize the transaction
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Show a success message to the buyer
                    document.getElementById('paypal-button-container').innerHTML = '<div class="completed">Transaction completed. Thank you!<div>';
                });
            }
			
        }).render('#paypal-button-container');
    </script>
</body>
