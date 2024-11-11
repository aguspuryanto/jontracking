<div id="dialog_maintenance" title="<? echo $la['MAINTENANCE']; ?>">
	<table id="maintenance_list_grid"></table>
	<div id="maintenance_list_grid_pager"></div>
</div>

<div id="dialog_maintenance_service_properties" title="<? echo $la['SERVICE_PROPERTIES'];?>">
	<div class="row">
		<div class="title-block"><? echo $la['SERVICE']; ?></div>
		<div class="block width50">
			<div class="container">
				<div class="row2">
					<div class="width50"><? echo $la['NAME']; ?></div>
					<div class="width50"><input id="dialog_maintenance_service_name" class="inputbox" type="text" value="" maxlength="30"></div>
				</div>				
				<div class="row2">
					<div class="width50"><? echo $la['DATA_LIST']; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_data_list" type="checkbox" class="checkbox" /></div>
				</div>
				<div class="row2">
					<div class="width50"><? echo $la['POPUP']; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_popup" type="checkbox" class="checkbox" /></div>
				</div>
				<div class="row2">
					<div class="width50"><? echo $la['ODOMETER_INTERVAL'].' ('.$la["UNIT_DISTANCE"].')'; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_odo" onchange="maintenanceServiceCheck();" type="checkbox" class="checkbox"/></div>
					<div class="width40"><input id="dialog_maintenance_service_odo_interval" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				
				<div class="row2">
					<div class="width50"><? echo $la['ENGINE_HOURS_INTERVAL']. ' ('.$la["UNIT_H"].')'; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_engh" onchange="maintenanceServiceCheck();" type="checkbox" class="checkbox"/></div>
					<div class="width40"><input id="dialog_maintenance_service_engh_interval" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				
				<div class="row2">
					<div class="width50"><? echo $la['DAYS_INTERVAL']; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_days" onchange="maintenanceServiceCheck();" type="checkbox" class="checkbox"/></div>
					<div class="width40"><input id="dialog_maintenance_service_days_interval" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
			</div>
		</div>
		<div class="block width50">
			<div class="container last">
				<div class="row2">
					<div class="width50"><? echo $la['OBJECTS']; ?></div>
					<div class="width50"><select id="dialog_maintenance_service_object_list" class="select-multiple-search width100" multiple="multiple"></select></div>
				</div>
				<div class="row2 empty"></div>
				<div class="row2 empty"></div>
				<div class="row2">
					<div class="width50"><? echo $la['LAST_SERVICE'].' ('.$la["UNIT_DISTANCE"].')'; ?></div>
					<div class="width50"><input id="dialog_maintenance_service_odo_last" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				<div class="row2">
					<div class="width50"><? echo $la['LAST_SERVICE']. ' ('.$la["UNIT_H"].')'; ?></div>
					<div class="width50"><input id="dialog_maintenance_service_engh_last" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				<div class="row2">
					<div class="width50"><? echo $la['LAST_SERVICE']; ?></div>
					<div class="width50"><input id="dialog_maintenance_service_days_last" readonly class="inputbox inputbox-calendar" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="title-block"><? echo $la['TRIGGER_EVENT']; ?></div>
		<div class="block width50">
			<div class="container">
				<div class="row2">
					<div class="width50"><?  echo $la['ODOMETER_LEFT']. ' ('.$la["UNIT_DISTANCE"].')'; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_odo_left" onchange="maintenanceServiceCheck();" type="checkbox" class="checkbox"/></div>
					<div class="width40"><input id="dialog_maintenance_service_odo_left_num" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				<div class="row2">
					<div class="width50"><?  echo $la['ENGINE_HOURS_LEFT']. ' ('.$la["UNIT_H"].')'; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_engh_left" onchange="maintenanceServiceCheck();" type="checkbox" class="checkbox"/></div>
					<div class="width40"><input id="dialog_maintenance_service_engh_left_num" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
				<div class="row2">
					<div class="width50"><? echo $la['DAYS_LEFT']; ?></div>
					<div class="width10"><input id="dialog_maintenance_service_days_left" onchange="maintenanceServiceCheck();" type="checkbox" class="checkbox"/></div>
					<div class="width40"><input id="dialog_maintenance_service_days_left_num" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="15"></div>
				</div>
			</div>
		</div>
		<div class="block width50">
			<div class="container last">
				<div class="row2">
					<div class="width50"><?  echo $la['UPDATE_LAST_SERVICE']; ?></div>
					<div class="width50"><input id="dialog_maintenance_service_update_last" type="checkbox" class="checkbox"/></div>
				</div>
			</div>
		</div>
	</div>
	
	<center>
		<input class="button icon-save icon" type="button" onclick="maintenanceServiceProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="maintenanceServiceProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>