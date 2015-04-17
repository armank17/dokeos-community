<style type="text/css">
.portlet { margin: 0 1em 1em 0; }
.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
.portlet-header .ui-icon { float: right; }
.portlet-content { padding: 0.4em; }
.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
.ui-sortable-placeholder * { visibility: hidden; }
</style>
	
<div id="location1" class="location widget" title="<?php echo get_lang('LocationHeader');?>">
	<?php load_widgets('location1');?>  
</div>

<div id="location2" class="location widget" title="<?php echo get_lang('LocationMain');?>">
	<?php load_widgets('location2');?>   			
</div>

<div id="location3" class="location widget" title="<?php echo get_lang('LocationFooter');?>">
	<?php load_widgets('location3');?>
	<?php 
	if (api_is_allowed_to_edit()) {
		load_configuration_widget();
	}
	load_user_widget();
	?> 	
</div>