<?
        $container_width = 'width25';
        
        if (($_SESSION["privileges_maintenance"] == false) && ($_SESSION["privileges_tasks"] == false))
        {
               $container_width = 'width50'; 
        }
        
        if (($_SESSION["privileges_maintenance"] == true) && ($_SESSION["privileges_tasks"] == false))
        {
               $container_width = 'width33';
        }
        
        if (($_SESSION["privileges_maintenance"] == false) && ($_SESSION["privileges_tasks"] == true))
        {
               $container_width = 'width33'; 
        }
?>


<div id="dialog_dashboard" title="<? echo $la['DASHBOARD']; ?>">
        <div class="row">
                <div class="block <? echo $container_width; ?>">
			<div class="container">
                                <div id="dialog_dashboard_objects" class="dashboard-container">
                                        <div class="table">
                                                <div class="table-cell center-middle">
                                                        <div class="loader">
                                                                <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
                <div class="block <? echo $container_width; ?>">
			<div class="container <? if (($_SESSION["privileges_maintenance"] == false) && ($_SESSION["privileges_tasks"] == false)) { echo 'last'; } ?>">
                                <div id="dialog_dashboard_events" class="dashboard-container">
                                        <div class="table">
                                                <div class="table-cell center-middle">
                                                        <div class="loader">
                                                                <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
                <? if ($_SESSION["privileges_maintenance"] == true) { ?>
                        <div class="block <? echo $container_width; ?>">
                                <div class="container <? if ($_SESSION["privileges_tasks"] == false) { echo 'last'; } ?>">
                                        <div id="dialog_dashboard_maintenance" class="dashboard-container">
                                                <div class="table">
                                                        <div class="table-cell center-middle">
                                                                <div class="loader">
                                                                        <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                <? } ?>
                <? if ($_SESSION["privileges_tasks"] == true) { ?>
                <div class="block <? echo $container_width; ?>">
			<div class="container last">
                                <div id="dialog_dashboard_tasks" class="dashboard-container">
                                        <div class="table">
                                                <div class="table-cell center-middle">
                                                        <div class="loader">
                                                                <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
                <? } ?>
        </div>
        <div class="row">
                <div class="block width75">			
			<div class="container">
                                <div id="dialog_dashboard_odometer" class="dashboard-container">
                                        <div class="table">
                                                <div class="table-cell center-middle">
                                                        <div class="loader">
                                                                <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
                <div class="block width25">			
			<div class="container last">
                                <div id="dialog_dashboard_mileage" class="dashboard-container">
                                        <div class="table">
                                                <div class="table-cell center-middle">
                                                        <div class="loader">
                                                                <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
        </div>
</div>