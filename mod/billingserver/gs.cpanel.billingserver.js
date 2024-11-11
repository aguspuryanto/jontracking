//#################################################
// VARS
//#################################################

cpValues['billing_server_expires_soon'] = false;
cpValues['billing_server_paypal_url'] = false;

function billingServerOpen()
{
	var data = {
		cmd: 'load_billing_server_data'
	};
	
	$.ajax({
		type: "POST",
		url: "mod/billingserver/fn_cpanel.billingserver.php",
		data: data,
		dataType: 'json',
		cache: false,
		success: function(result)
		{			
			if (result['expires_soon'])
			{
				document.getElementById("billing_server_billing_bnt").className = "billing-btn-red";
				cpValues['billing_server_expires_soon'] = true;
				cpValues['billing_server_paypal_url'] = result['paypal_url'];
			}
			else
			{
				document.getElementById("billing_server_billing_bnt").className = "billing-btn";
				cpValues['billing_server_expires_soon'] = false;
				cpValues['billing_server_paypal_url'] = false;
			}
			
			document.getElementById("billing_server_plan_name").innerHTML = result['server_plan_name'];
			
			document.getElementById("dialog_billing_server_plan_name").innerHTML = result['server_plan_name'];
			document.getElementById("dialog_billing_server_plan_objects").innerHTML = result['server_plan_objects'];
			document.getElementById("dialog_billing_server_plan_history").innerHTML = result['server_plan_history'] + ' days';
			document.getElementById("dialog_billing_server_plan_period").innerHTML = result['server_plan_period'] + ' month(s)';
			document.getElementById("dialog_billing_server_plan_price").innerHTML = result['server_plan_price'] + ' EUR';
			document.getElementById("dialog_billing_server_plan_last_payment").innerHTML = result['server_plan_last_payment'];
			document.getElementById("dialog_billing_server_plan_next_payment").innerHTML = result['server_plan_next_payment'];
			
			$('#dialog_billing_server').dialog('open');
		}
	});
}

function billingServerClose()
{
	// do nothing
}

function billingServerPay()
{
	if ((cpValues['billing_server_expires_soon']) && (cpValues['billing_server_paypal_url']))
	{
		window.open(cpValues['billing_server_paypal_url'], "_blank");
	}
	else
	{
		notifyDialog('Payment shall be made not earlier than 7 days before plan expiration.');
	}
}