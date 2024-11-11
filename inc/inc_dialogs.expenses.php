<div id="dialog_expenses" title="<? echo $la['EXPENSES']; ?>">
	<table id="expenses_list_grid"></table>
	<div id="expenses_list_grid_pager"></div>
</div>

<div id="dialog_expense_properties" title="<? echo $la['EXPENSE_PROPERTIES'];?>">
	<div class="row">
		<div class="title-block"><? echo $la['EXPENSE']; ?></div>
		<div class="block width50">
			<div class="container">
				<div class="row2">
					<div class="width40"><? echo $la['NAME']; ?></div>
					<div class="width60"><input id="dialog_expense_name" class="inputbox" type="text" value="" maxlength="30"></div>
				</div>				
				<div class="row2">
					<div class="width40"><? echo $la['DATE']; ?></div>
					<div class="width30"><input readonly class="inputbox-calendar inputbox width100" id="dialog_expense_date" type="text" value=""/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['QUANTITY']; ?></div>
					<div class="width30"><input id="dialog_expense_quantity" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="11"></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['COST']; ?></div>
					<div class="width30"><input id="dialog_expense_cost" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="11"></div>
					<div class="width2"></div>
					<div class="width28"><? echo $_SESSION["currency"]; ?></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['SUPPLIER']; ?></div>
					<div class="width60"><input id="dialog_expense_supplier" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['BUYER']; ?></div>
					<div class="width60"><input id="dialog_expense_buyer" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
			</div>
		</div>
		<div class="block width50">
			<div class="container last">
				<div class="row2">
					<div class="width40"><? echo $la['OBJECT']; ?></div>
					<div class="width60"><select id="dialog_expense_object_list" class="select-search width100" onchange="expensesObjectChange();"></select></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['ODOMETER']. ' ('.$la["UNIT_DISTANCE"].')'; ?></div>
					<div class="width60"><input id="dialog_expense_odo" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['ENGINE_HOURS']. ' ('.$la["UNIT_H"].')'; ?></div>
					<div class="width60"><input id="dialog_expense_engh" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['DESCRIPTION']; ?></div>
					<div class="width60"><textarea id="dialog_expense_desc" class="inputbox" style="height:78px;" maxlength="500"></textarea></div>
				</div>
			</div>
		</div>
	</div>	
	
	<center>
		<input class="button icon-save icon" type="button" onclick="expensesProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="expensesProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>