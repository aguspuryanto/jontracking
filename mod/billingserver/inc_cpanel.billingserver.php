<div id="dialog_billing_server" title="<? echo $la['BILLING']; ?>">
	<div class="info">
		Billing allows to view existing Hosted Server plan details and extend it before expiration. Details are updated after payment is confirmed, sometimes it may take a while. Invoice will be sent to your PayPal e-mail.
	</div>
	
	<div id="dialog_billing_server_plan">
		<div class="row">
			<div class="block width50">
				<div class="container">
					<div class="row2 text">
						<div class="width10 center-middle">
							<img src="theme/images/tick.svg" width="16px" border="0">
						</div>
						<div class="width40">
							Plan
						</div>
						<div class="width50" id="dialog_billing_server_plan_name"></div>
					</div>
					<div class="row2 text">
						<div class="width10 center-middle">
							<img src="theme/images/object-arrow.svg" width="16px" border="0">
						</div>
						<div class="width40">
							Objects
						</div>
						<div class="width50" id="dialog_billing_server_plan_objects"></div>
					</div>
					<div class="row2 text">
						<div class="width10 center-middle">
							<img src="theme/images/history.svg" width="16px" border="0">
						</div>
						<div class="width40">
							History
						</div>
						<div class="width50" id="dialog_billing_server_plan_history"></div>
					</div>
				</div>
			</div>
			<div class="block width50">
				<div class="container last">
					<div class="row2 text">
						<div class="width10 center-middle">
							<img src="theme/images/calendar.svg" width="16px" border="0">
						</div>
						<div class="width40">
							Period
						</div>
						<div class="width50" id="dialog_billing_server_plan_period"></div>
					</div>
					<div class="row2 text">
						<div class="width10 center-middle">
							<img src="theme/images/billing.svg" width="16px" border="0">
						</div>
						<div class="width40">
							Price
						</div>
						<div class="width50" id="dialog_billing_server_plan_price"></div>
					</div>
					<div class="row2 text">
						<div class="width10 center-middle">
							<img src="theme/images/time.svg" width="16px" border="0">
						</div>
						<div class="width40">
							Last payment
						</div>
						<div class="width50" id="dialog_billing_server_plan_last_payment"></div>
					</div>
					<div class="row2 text">
						<div class="width10 center-middle">
							<img src="theme/images/time.svg" width="16px" border="0">
						</div>
						<div class="width40">
							Next payment
						</div>
						<div class="width50" id="dialog_billing_server_plan_next_payment"></div>
					</div>
				</div>
			</div>
		</div>
		
		<center>
			<input class="button-32 icon-billing icon-16" type="button" onclick="billingServerPay();" value="Pay with PayPal" />
		</center>
	</div>
</div>