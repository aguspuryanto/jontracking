<?
	$billing_btn_class = 'billing-btn';
	
	if (strtotime($gsValues['SERVER_PLAN_NEXT_PAYMENT'].' - 7 day') <= strtotime(gmdate("Y-m-d")))
	{
		$billing_btn_class = 'billing-btn-red';
	}
?>

<div id="billing_server_billing_bnt" class="<? echo $billing_btn_class; ?>">
	<a href="#" onclick="billingServerOpen();" title="<? echo $la['BILLING']; ?>">
		<img class="float-left" src="theme/images/cart-white.svg" border="0"/>
		<span id="billing_server_plan_name">
		<?
			if (!isset($gsValues['SERVER_PLAN_NAME']))
			{
				echo caseToLower($la['NA']);
			}
			else
			{
				echo $gsValues['SERVER_PLAN_NAME'];	
			}
		?>
		</span>
	</a>
</div>