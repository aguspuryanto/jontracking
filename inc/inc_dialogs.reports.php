<div id="dialog_report_properties" title="<? echo $la['REPORT_PROPERTIES']; ?>">
	<div class="row">	
		<div class="title-block"><? echo $la['REPORT'];?></div>
		<div class="report-block block width50">
			<div class="container">
				<div class="row2">
					<div class="width40"><? echo $la['NAME'];?></div>
					<div class="width60"><input id="dialog_report_name" class="inputbox" type="text" value="" maxlength="30"></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['TYPE']; ?></div>
					<div class="width60">
						<select id="dialog_report_type" class="select width100" onchange="reportsSwitchType();reportsListDataItems();reportsListSensors();">
							<optgroup label="<? echo $la['TEXT_REPORTS']; ?>">
							<option value="general" selected><? echo $la['GENERAL_INFO']; ?></option>
							<option value="general_merged"><? echo $la['GENERAL_INFO_MERGED']; ?></option>
							<option value="object_info"><? echo $la['OBJECT_INFO']; ?></option>
							<option value="current_position"><? echo $la['CURRENT_POSITION']; ?></option>
							<option value="current_position_off"><? echo $la['CURRENT_POSITION_OFFLINE']; ?></option>							
							<option value="route_data_sensors"><? echo $la['ROUTE_DATA_WITH_SENSORS']; ?></option>							
							<option value="drives_stops"><? echo $la['DRIVES_AND_STOPS']; ?></option>
							<option value="drives_stops_sensors"><? echo $la['DRIVES_AND_STOPS_WITH_SENSORS']; ?></option>
							<option value="drives_stops_logic"><? echo $la['DRIVES_AND_STOPS_WITH_LOGIC_SENSORS']; ?></option>
							<option value="travel_sheet"><? echo $la['TRAVEL_SHEET']; ?></option>
							<option value="travel_sheet_dn"><? echo $la['TRAVEL_SHEET_DAY_NIGHT']; ?></option>
							<option value="mileage_daily"><? echo $la['MILEAGE_DAILY']; ?></option>
							<option value="overspeed"><? echo $la['OVERSPEEDS']; ?></option>
							<option value="overspeed_count"><? echo $la['OVERSPEED_COUNT_MERGED']; ?></option>
							<option value="underspeed"><? echo $la['UNDERSPEEDS']; ?></option>
							<option value="underspeed_count"><? echo $la['UNDERSPEED_COUNT_MERGED']; ?></option>
							<option value="marker_in_out"><? echo $la['MARKER_IN_OUT']; ?></option>
							<option value="marker_in_out_gen"><? echo $la['MARKER_IN_OUT_WITH_GEN_INFORMATION']; ?></option>
							<option value="zone_in_out"><? echo $la['ZONE_IN_OUT']; ?></option>
							<option value="zone_in_out_general"><? echo $la['ZONE_IN_OUT_WITH_GEN_INFORMATION']; ?></option>
							<option value="events"><? echo $la['EVENTS']; ?></option>
							<option value="service"><? echo $la['SERVICE']; ?></option>
							<option value="fuelfillings"><? echo $la['FUEL_FILLINGS']; ?></option>
							<option value="fuelthefts"><? echo $la['FUEL_THEFTS']; ?></option>
							<option value="logic_sensors"><? echo $la['LOGIC_SENSORS']; ?></option>
							<option value="rag"><? echo $la['DRIVER_BEHAVIOR_RAG_BY_OBJECT']; ?></option>
							<option value="rag_driver"><? echo $la['DRIVER_BEHAVIOR_RAG_BY_DRIVER']; ?></option>
							<option value="tasks"><? echo $la['TASKS']; ?></option>
							<option value="rilogbook"><? echo $la['RFID_AND_IBUTTON_LOGBOOK']; ?></option>
							<option value="dtc"><? echo $la['DIAGNOSTIC_TROUBLE_CODES']; ?></option>
							<option value="expenses"><? echo $la['EXPENSES']; ?></option>
							<optgroup label="<? echo $la['GRAPHICAL_REPORTS']; ?>">
							<option value="speed_graph"><? echo $la['SPEED']; ?></option>
							<option value="altitude_graph"><? echo $la['ALTITUDE']; ?></option>
							<option value="acc_graph"><? echo $la['IGNITION']; ?></option>
							<option value="fuellevel_graph"><? echo $la['FUEL_LEVEL']; ?></option>
							<option value="temperature_graph"><? echo $la['TEMPERATURE']; ?></option>
							<option value="sensor_graph"><? echo $la['SENSOR']; ?></option>
							<optgroup label="<? echo $la['MAP_REPORTS']; ?>">
							<option value="routes"><? echo $la['ROUTES']; ?></option>
							<option value="routes_stops"><? echo $la['ROUTES_WITH_STOPS']; ?></option>
							<optgroup label="<? echo $la['MEDIA_REPORTS']; ?>">
							<option value="image_gallery"><? echo $la['IMAGE_GALLERY']; ?></option>
						</select>
					</div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['OBJECTS']; ?></div>
					<div class="width60"><select id="dialog_report_object_list" class="select-multiple-search width100" multiple="multiple" onchange="reportsSelectObject();"></select></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['MARKERS']; ?></div>
					<div class="width60"><select id="dialog_report_marker_list" class="select-multiple-search width100" multiple="multiple" disabled></select></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['ZONES']; ?></div>
					<div class="width60"><select id="dialog_report_zone_list" class="select-multiple-search width100" multiple="multiple" disabled></select></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['SENSORS']; ?></div>
					<div class="width60"><select id="dialog_report_sensor_list" class="select-multiple-search width100" multiple="multiple" disabled></select></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['DATA_ITEMS']; ?></div>
					<div class="width60"><select id="dialog_report_data_item_list" class="select-multiple width100" multiple="multiple"></select></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['FORMAT']; ?></div>
					<div class="width60">
						<select id="dialog_report_format" class="select width100"/>
							<option value="html">HTML</option>
							<option value="pdf">PDF</option>
							<option value="xls">XLS</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="report-block block width50">
			<div class="container last">				
				<div class="row2">
					<div class="width40"><? echo $la['IGNORE_EMPTY_REPORTS']; ?></div>
					<div class="width60"><input id="dialog_report_ignore_empty_reports" type="checkbox" class="checkbox"/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['SHOW_COORDINATES']; ?></div>
					<div class="width60"><input id="dialog_report_show_coordinates" type="checkbox" class="checkbox" checked disabled/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['SHOW_ADDRESSES']; ?></div>
					<div class="width60"><input id="dialog_report_show_addresses" type="checkbox" class="checkbox" disabled/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['MARKERS_INSTEAD_OF_ADDRESSES']; ?></div>
					<div class="width60"><input id="dialog_report_markers_addresses" type="checkbox" class="checkbox" disabled/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['ZONES_INSTEAD_OF_ADDRESSES']; ?></div>
					<div class="width60"><input id="dialog_report_zones_addresses" type="checkbox" class="checkbox" disabled/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['STOPS']; ?></div>
					<div class="width60">
						<select id="dialog_report_stop_duration" class="select width100"/>
							<option value="1">> 1 <? echo $la['UNIT_MIN']; ?></option>
							<option value="2">> 2 <? echo $la['UNIT_MIN']; ?></option>
							<option value="5">> 5 <? echo $la['UNIT_MIN']; ?></option>
							<option value="10">> 10 <? echo $la['UNIT_MIN']; ?></option>
							<option value="20">> 20 <? echo $la['UNIT_MIN']; ?></option>
							<option value="30">> 30 <? echo $la['UNIT_MIN']; ?></option>
							<option value="60">> 1 <? echo $la['UNIT_H']; ?></option>
							<option value="120">> 2 <? echo $la['UNIT_H']; ?></option>
							<option value="300">> 5 <? echo $la['UNIT_H']; ?></option>
						</select>
					</div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['SPEED_LIMIT']; ?> (<? echo $la["UNIT_SPEED"]; ?>)</div>
					<div class="width60"><input id="dialog_report_speed_limit" class="inputbox width100" onkeypress="return isNumberKey(event);" type="text" maxlength="3"/></div>
				</div>
			</div>
		</div>
	</div>
	<div class="row" id="dialog_report_other_dn" style="display: none;">	
		<div class="title-block"><? echo $la['TRAVEL_SHEET_DAY_NIGHT'];?></div>
		<div class="report-block block width50">
			<div class="container">
				<div class="row2">
					<div class="width40"><? echo $la['NIGHT_STARTS']; ?></div>
					<div class="width13">
						<select id="dialog_report_other_dn_starts_hour" class="select width100">
						<? include ("inc/inc_dt.hours.php"); ?>
						</select>
					</div>
					<div class="width2"></div>
					<div class="width13">
						<select id="dialog_report_other_dn_starts_minute" class="select width100">
						<? include ("inc/inc_dt.minutes.php"); ?>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="report-block block width50">
			<div class="container last">
				<div class="row2">
					<div class="width40"><? echo $la['NIGHT_ENDS']; ?></div>
					<div class="width13">
						<select id="dialog_report_other_dn_ends_hour" class="select width100">
						<? include ("inc/inc_dt.hours.php"); ?>
						</select>
					</div>
					<div class="width2"></div>
					<div class="width13">
						<select id="dialog_report_other_dn_ends_minute" class="select width100">
						<? include ("inc/inc_dt.minutes.php"); ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>		
	<div class="row" id="dialog_report_other_rag" style="display: none;">	
		<div class="title-block"><? echo $la['DRIVER_BEHAVIOR_RAG'];?></div>
		<div class="report-block block width50">
			<div class="container">
				<div class="row2">
					<div class="width40"><? echo $la['LOWEST_SCORE']; ?></div>
					<div class="width60"><input id="dialog_report_other_rag_low_score" class="inputbox width100" onkeypress="return isNumberKey(event);" type="text" maxlength="5"/></div>
				</div>
			</div>
		</div>
		<div class="report-block block width50">
			<div class="container last">
				<div class="row2">
					<div class="width40"><? echo $la['HIGHEST_SCORE']; ?></div>
					<div class="width60"><input id="dialog_report_other_rag_high_score" class="inputbox width100" onkeypress="return isNumberKey(event);" type="text" maxlength="5"/></div>
				</div>
			</div>
		</div>
	</div>
		
	<div class="row">
		<div class="schedule-block block width50">
			<div class="container">
				<div class="title-block"><? echo $la['SCHEDULE'];?></div>
				<div class="row2">
					<div class="width40"><? echo $la['DAILY'];?></div>
					<div class="width60"><input id="dialog_report_schedule_period_daily" type="checkbox" <? if ($gsValues['REPORTS_SCHEDULE'] == 'false') { ?> disabled=disabled <? } ?>/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['WEEKLY'];?></div>
					<div class="width60"><input id="dialog_report_schedule_period_weekly" type="checkbox" <? if ($gsValues['REPORTS_SCHEDULE'] == 'false') { ?> disabled=disabled <? } ?>/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['SEND_VIA_EMAIL'];?></div>
					<div class="width60"><input id="dialog_report_schedule_email_address" class="inputbox" type="text" value="" maxlength="500" placeholder="<? echo $la['EMAIL_ADDRESS']; ?>" <? if ($gsValues['REPORTS_SCHEDULE'] == 'false') { ?> disabled=disabled <? } ?>/></div>
				</div>
			</div>
		</div>
		<div class="time-period block width50">
			<div class="container last">
				<div class="title-block"><? echo $la['TIME_PERIOD'];?></div>
				<div class="row2">
					<div class="width40"><? echo $la['FILTER'];?></div>
					<div class="width60">
						<select id="dialog_report_filter" class="select width100" onchange="switchDateFilter('report');">
							<option value="0" selected></option>
							<option value="1"><? echo $la['LAST_HOUR'];?></option>
							<option value="2"><? echo $la['TODAY'];?></option>
							<option value="3"><? echo $la['YESTERDAY'];?></option>
							<option value="4"><? echo $la['BEFORE_2_DAYS'];?></option>
							<option value="5"><? echo $la['BEFORE_3_DAYS'];?></option>
							<option value="6"><? echo $la['THIS_WEEK'];?></option>
							<option value="7"><? echo $la['LAST_WEEK'];?></option>
							<option value="8"><? echo $la['THIS_MONTH'];?></option>
							<option value="9"><? echo $la['LAST_MONTH'];?></option>
						</select>
					</div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['TIME_FROM']; ?></div>
					<div class="width30">
						<input readonly class="inputbox-calendar inputbox width100" id="dialog_report_date_from" type="text" value=""/>
					</div>
					<div class="width2"></div>
					<div class="width13">
						<select id="dialog_report_hour_from" class="select width100">
						<? include ("inc/inc_dt.hours.php"); ?>
						</select>
					</div>
					<div class="width2"></div>
					<div class="width13">
						<select id="dialog_report_minute_from" class="select width100">
						<? include ("inc/inc_dt.minutes.php"); ?>
						</select>
					</div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['TIME_TO']; ?></div>
					<div class="width30">
						<input readonly class="inputbox-calendar inputbox width100" id="dialog_report_date_to" type="text" value=""/>
					</div>
					<div class="width2"></div>
					<div class="width13">
						<select id="dialog_report_hour_to" class="select width100">
						<? include ("inc/inc_dt.hours.php"); ?>
						</select>
					</div>
					<div class="width2"></div>
					<div class="width13">
						<select id="dialog_report_minute_to" class="select width100">
						<? include ("inc/inc_dt.minutes.php"); ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<center>
		<input class="button icon-action2 icon" type="button" onclick="reportProperties('generate');" value="<? echo $la['GENERATE']; ?>" />&nbsp;
		<input class="button icon-save icon" type="button" onclick="reportProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="reportProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>

<div id="dialog_reports" title="<? echo $la['REPORTS']; ?>">
	<div id="reports_tabs">
		<ul>           
			<li><a href="#reports_reports"><? echo $la['REPORTS'];?></a></li>
			<li id="reports_generated_tab"><a href="#reports_generated"><? echo $la['GENERATED'];?></a></li>
		</ul>
		<div id="reports_reports">
			<table id="reports_report_list_grid"></table>
			<div id="reports_report_list_grid_pager"></div>
		</div>
		<div id="reports_generated">
			<table id="reports_generated_list_grid"></table>
			<div id="reports_generated_list_grid_pager"></div>
		</div>
	</div>
</div>
