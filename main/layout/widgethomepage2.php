<style type="text/css">
#location1{
	/*border: 1px solid green;*/
}

#contentwrapper{
	float: left;
	width: 100%;
}

#location2{
	margin: 0 0 0 250px; /*Margins for content column. Should be "0 location4Width 0 location3Width*/
	/*border: 1px solid red;*/
}

#location3{
	float: left;
	width: 250px; /*Width of left column*/
	margin-left: -100%;
	/*border: 1px solid blue;*/
}

#location4{
	clear: left;
	width: 100%;
	text-align: center;
	padding: 4px 0;
	/*border: 1px solid orange;*/
}

.portlet { margin: 0 1em 1em 0; }
.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
.portlet-header .ui-icon { float: right; }
.portlet-content { padding: 0.4em; }
.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
.ui-sortable-placeholder * { visibility: hidden; }
</style>





<div id="maincontainer">

<div id="location1" class="location widget" title="<?php echo get_lang('LocationHeader');?>">&nbsp;
	<?php load_widgets('location1');?>
</div>

<div id="contentwrapper">
	<div id="location2" class="location widget" title="<?php echo get_lang('LocationMain');?>"> &nbsp;
		<?php load_widgets('location2');?>  	
	</div>
</div>

<div id="location3" class="location widget" title="<?php echo get_lang('LocationSidebarLeft');?>"> &nbsp;
		<?php load_widgets('location3');?> 
		<?php 
		if (api_is_allowed_to_edit()) {
			load_configuration_widget();
		}
		load_user_widget();
		?> 
</div>

<div id="location4" class="location widget" title="<?php echo get_lang('LocationFooter');?>"> &nbsp;
	<?php load_widgets('location5');?>
</div>
