<div id="dialog_share_position" title="<? echo $la['SHARE_POSITION']; ?>">
	<table id="share_position_list_grid"></table>
	<div id="share_position_list_grid_pager"></div>
</div>

<div id="dialog_share_position_properties" title="<? echo $la['SHARE_POSITION_PROPERTIES'];?>">
	<div class="row">
		<div class="title-block"><? echo $la['SHARE_POSITION']; ?></div>
		<div class="block width50">
			<div class="container">
				<div class="row2">
					<div class="width40"><? echo $la['ACTIVE']; ?></div>
					<div class="width60"><input id="dialog_share_position_active" type="checkbox" checked="checked"/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['NAME']; ?></div>
					<div class="width60"><input id="dialog_share_position_name" class="inputbox" type="text" value="" maxlength="30"/></div>
				</div>				
				<div class="row2">
					<div class="width40"><? echo $la['OBJECT']; ?></div>
					<div class="width60"><select id="dialog_share_position_object_list" class="select-multiple-search width100" multiple="multiple"></select></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['EMAIL']; ?></div>
					<div class="width60"><input id="dialog_share_position_email" class="inputbox" type="text" value="" maxlength="50"/></div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['PHONE']; ?></div>
					<div class="width60"><input id="dialog_share_position_phone" class="inputbox" type="text" value="" maxlength="50"/></div>
				</div>
			</div>
		</div>
		<div class="block width50">
			<div class="container last">
				<div class="row2">
					<div class="width40"><? echo $la['EXPIRE_ON']; ?></div>
					<div class="width10">
						<input id="dialog_share_position_expire" type="checkbox" class="checkbox" onchange="sharePositionCheck();"/>
					</div>
					<div class="width50">
						<input readonly class="inputbox-calendar inputbox width100" id="dialog_share_position_expire_dt"/>
					</div>
				</div>
				<div class="row2">
					<div class="width40"><? echo $la['DELETE_AFTER_EXPIRATION']; ?></div>
					<div class="width60">
						<input id="dialog_share_position_delete_expired" type="checkbox" class="checkbox"/>
					</div>
				</div>				
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="title-block"><? echo $la['ACCESS_VIA_URL']; ?></div>
		<div class="row2">
			<div class="width195"><? echo $la['SEND_VIA_EMAIL']; ?></div>
			<div class="width805"><input id="dialog_share_position_send_email" type="checkbox" checked="checked"/></div>
		</div>
		<div class="row2">
			<div class="width195"><? echo $la['SEND_VIA_SMS']; ?></div>
			<div class="width805"><input id="dialog_share_position_send_sms" type="checkbox" checked="checked"/></div>
		</div>	
		<div class="row2">
			<div class="width195"><? echo $la['URL_DESKTOP']; ?></div>
			<div class="width805">
				<input class="inputbox" id="dialog_share_position_su" readonly />
			</div>
		</div>
		<div class="row2">
			<div class="width195"><? echo $la['URL_MOBILE']; ?></div>
			<div class="width805">
				<input class="inputbox" id="dialog_share_position_su_mobile" readonly />
			</div>
		</div>
	</div>
	
	<center>
		<input class="button icon-save icon" type="button" onclick="sharePositionProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="sharePositionProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>