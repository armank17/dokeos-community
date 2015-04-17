<?php 
require_once ('../../global.inc.php');
include_once '../widgets.lib.php';
?>
		$(function() {
			// make the widgets in the different locations sortable
			$("#location1, #location2, #location3, #location4, #location5").sortable({
				connectWith: '.location',
				handle: '.portlet-header',
				opacity: 0.6, 
				cursor: 'move',
				update: function() {
					// creating the string to save the widgets in the current location
					var order = $(this).sortable('serialize') + '&location='+$(this).attr('id')+'&amp;action=savewidgetsinlocation'; 
					// sending the string to the widgets.lib.php page to save the widget in the current location
					$.post('<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php', order, function(theResponse){
						$("#debug").html(theResponse);
					}); 
					hidefirstup();
					hidelastdown();
				}	
			});
			
			// making the dialog
			// http://visualflowdesigns.com/packt-jquery-articles/jquery-ui-the-dialog-part-2/
			$('#dialog').dialog({ 
								autoOpen: false, 
								buttons: { "Ok": function() { $(this).dialog("close"); location.reload(); }} ,
								modal: true,
								/*show: 'slide',*/
								width: 560,
								height: 500,
								close: function() { $('#dialog').html('<div align="center"><br /><?php Display::display_icon('ajax-loader.gif','',array('style'=>'text-align: left;')); ?></div>');}											
								});	
			
			<?php if(api_is_allowed_to_edit()){ ?>
			// adding the icons to every widget
			$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
				.find(".portlet-header")
					.addClass("ui-widget-header ui-corner-all")
					.prepend('<span class="ui-icon ui-icon-help"></span>')
					.prepend('<span class="ui-icon ui-icon-triangle-1-n"></span>')
					.prepend('<span class="ui-icon ui-icon-triangle-1-s"></span>')
					.prepend('<span class="ui-icon ui-icon-arrow-4"></span>')
					.prepend('<span class="ui-icon ui-icon-gear"></span>')
					.prepend('<span class="ui-icon ui-icon-minusthick"></span>')
					.end()
				.find(".portlet-content");
				
			// removing the up icon for every first widget of a location
			function hidefirstup() {
				$('.location .portlet:first-child').find(".ui-icon-triangle-1-n").hide();
				$('.location .portlet:not(:first-child)').find(".ui-icon-triangle-1-n").show();
			}
			hidefirstup();
			
			// removing the down icon for every last widget of a location (and hiding the down icon for every other widget in a location)
			function hidelastdown() {
				$('.location .portlet:last-child').find(".ui-icon-triangle-1-s").hide();
				$('.location .portlet:not(:last-child)').find(".ui-icon-triangle-1-s").show();
			}
			hidelastdown();			
			<?php } else { ?>
			$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
				.find(".portlet-header")
					.addClass("ui-widget-header ui-corner-all")
					.prepend('<span class="ui-icon ui-icon-help"></span>')
					.prepend('<span class="ui-icon ui-icon-minusthick"></span>')
					.end()
				.find(".portlet-content");			
			<?php } ?>

			
	
			// collapsing the widget
			$(".portlet-header .ui-icon.ui-icon-minusthick").live("click",function() {
				$(this).toggleClass("ui-icon-plusthick");
				$(this).toggleClass("ui-icon-minusthick");
				$(this).parents(".portlet:first").find(".portlet-content").toggle();
				
				// saving the state in the database
				if ($(this).hasClass('ui-icon-plusthick')) {
					var status = 'collapsed';
				} else {
					var status = 'expanded';
				}
				var parameters = 'widget='+$(this).parent().parent().attr('id')+'&status='+status+'&amp;action=savewidgetstatus'
				$.post('<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php', parameters, function(theResponse){
						$("#debug").html(theResponse);
				}); 
			});
			
			// expanding the widget
			$(".portlet-header .ui-icon.ui-icon-plusthick").live("click",function() {
				$(this).toggleClass("ui-icon-plusthick");
				$(this).toggleClass("ui-icon-minusthick");
				$(this).parents(".portlet:first").find(".portlet-content").toggle();
				
				// saving the state in the database
				if ($(this).hasClass('ui-icon-plusthick')) {
					var status = 'collapsed';
				} else {
					var status = 'expanded';
				}
				var parameters = 'widget='+$(this).parent().parent().attr('id')+'&status='+status+'&amp;action=savewidgetstatus'
				$.post('<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php', parameters, function(theResponse){
						$("#debug").html(theResponse);
				}); 
			});			
			
			// function to collapse the portlets that the user collapsed and are saved as such in the database
			function collapse_portlet(widgetid){
				$('#'+widgetid+' .portlet-header .ui-icon.ui-icon-minusthick').addClass("ui-icon-plusthick");				
				$('#'+widgetid+' .portlet-header .ui-icon.ui-icon-minusthick').toggleClass("ui-icon-minusthick");
				$('#'+widgetid).find(".portlet-content").toggle();
			}

			<?php 
			// collapse the portals that need to be collapsed
			collapsed_portals();
			?>			
			
			
			// help (for testing)
			$(".portlet-header .ui-icon-help").live("click", function() {
				alert('<?php echo get_lang('DokeosHelp'); ?>');
			});
			
			// have all the links with class dialoglink open in the dialog
			$('.dialoglink').live('click', function() { 
				// changing the title of the dialog to the title of the link
				$('#dialog').dialog('option', 'title', $(this).attr('title'));
				// loading the page that would be opened by clicking the link into the dialog window			
				$('#dialog').load($(this).attr('href')).dialog("open");
				// prevent from opening the link in the browser
				return false;
			});


			
			// configuration of the widgets
			$(".portlet-header .ui-icon-gear").live("click", function() {
				// changing the title of the dialog
				$('#dialog').dialog('option', 'title', '<?php echo get_lang('WidgetSettings');?>:'+$(this).parent().parent().attr('id'));
				// opening the dialog and loading the configuration options for the widget
				$('#dialog').dialog("open").load('<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php',{'widget':$(this).parent().parent().attr('id'),'action':'widget_settings_form'});

			});

			// whenever a widgetconfigurationiteminfo image is clicked we need to update the widgetconfigurationinfo panel with the information of the widget we just clicked
			// and we also need to make the widget list a little bit less high
			$(".widgetconfigurationiteminfo").live("click", function(){
				// get the name of the widget we clicked
				var widget_id = $(this).parents('.widgetconfigurationitem').filter(':first').attr('id')
					
				// make the widgetlist smaller
				$("#widgetlist").animate({ 
					height: 280
				  }, 500);

				// hide the info and change the content 
				 $("#widgetconfigurationinfo").animate({ 
					opacity: 0,
					height: 0
				  }, 500 , function(){
					$("#widgetconfigurationinfo").load('<?php echo api_get_path(WEB_PATH);?>main/widgets/'+widget_id+'/widgetfunctions.php',{'action':'get_widget_information'},function(){
						$(this).prepend('<span style="float:right;" id="widgetconfigurationinfoclose" class="ui-icon ui-icon-closethick"></span>');
					});
				  }
				  );
				  // make it appear again
				  $("#widgetconfigurationinfo").animate({ 
					opacity: 1,
					height: 100
				  }, 500 );			  
			});

			// when a widgetconfigurationitembutton is clicked then we need to display the form to activate or desactive the widget in a location
			$(".widgetconfigurationitembutton").live("click", function(){
				// get the name of the widget we clicked
				var widget_id = $(this).parents('.widgetconfigurationitem').filter(':first').attr('id')
				
				// get all the locations of the current layout
				var arrayLocations = $(".location");

				// make the widgetlist smaller so that we can display the widgetconfigurationinfo
				$("#widgetlist").animate({ 
					height: 280
				  }, 500);				
				
				// hide the widgetconfigurationinfo (1) and change the content into some explanation and the form  (2) that allows you to select in which location to activate the widget (3)
				// this information is showed in the widgetconfigurationinfo area
				$("#widgetconfigurationinfo").animate({ 
					opacity: 0,
					height: 0
					}, 500 , function(){
						/*
						// (2) change the content into the form to activate the widget)
						$("#widgetconfigurationinfo").load('<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php',{'action':'displayactivationform'}, function(){
							// (3) populate the form with all the locations in the layout
							$.each(arrayLocations, function() {
								$('#widgetactivateinlocation2').append($('<option></option>').val($(this).attr("id")).html($(this).attr("title")));
							});
						});
						*/
						$("#widgetconfigurationinfo").load('<?php echo api_get_path(WEB_PATH);?>main/widgets/'+widget_id+'/widgetfunctions.php',{'action':'get_widget_information'}, function(){
							$(this).prepend('<span style="float:right;" id="widgetconfigurationinfoclose" class="ui-icon ui-icon-closethick"></span>');
							});
					}
				);
				
				// make it appear again
				$("#widgetconfigurationinfo").animate({ 
					opacity: 1,
					height: 100
				}, 500 );					
				
				// first remove the location dropdowns that might already exist
				$("span.widgetactivateinlocation").remove();
				
				//we display a dropdown list with the locations also next to the widget title (only if there is not yet such one = to prevent doubles)
				if($(this).prev().hasClass('widgetactivateinlocation')){
				}
				else {
					$(this).before('<span style="float:right;" class="widgetactivateinlocation"><?php echo get_lang('ActivateInLocation'); ?><select id="widgetactivateinlocationselect" name="widgetactivateinlocation1"><option value="disable"><?php echo get_lang('Disabled'); ?></option></select><button id="widgetactivateinlocationbutton">Save</button>"</span>');
					}
				
				// populate the activate in location dropdown list that appears next to the widget title
				$.each(arrayLocations, function() {
					$('#widgetactivateinlocationselect').append($('<option class="selectlocation"></option>').val($(this).attr("id")).html($(this).attr("title")));
				});

				// if the widget is already activated then we have to make sure that the dropdown displays the correct location for that widget	
				$.get("<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php", {action:"get_widget_location",widget:widget_id}, 
					function(activeinlocation) {
						$("#widgetactivateinlocationselect").val(activeinlocation);
				});
			  
			});

			// closing the widgetconfigurationinfo
			$("#widgetconfigurationinfoclose").live("click", function(){
				$("#widgetconfigurationinfo").animate({ 
					opacity: 1,
					height: 0
				}, 500 );
				$("#widgetlist").animate({ 
					opacity: 1,
					height: 380
				}, 500 );						
			});


			// changing the widget location
			$("#widgetactivateinlocationbutton").live("click", function() {
				// get the location that the user selected
				var locationtoactivatewidget = $('#widgetactivateinlocationselect').val();

				// get the id of the widget
				var widget_id = $(this).parents('.widgetconfigurationitem').filter(':first').attr('id')
				
				// save the widget in the location
				$.get("<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php", {action:"save_widget_location",location:locationtoactivatewidget,widget:widget_id});

				// setting the widgetitem to active or inactive
				if (locationtoactivatewidget == 'disable') {
					$(this).parents(".widgetconfigurationitem").removeClass('active');
					
				} else {
					if ($(this).hasClass('active')){
						// do nothing because the widgetconfigurationitem already has the class active
					} else {
						// add the class active
						$(this).parents(".widgetconfigurationitem").addClass('active');
					}					
				}

				// remove the dropdown and replace with the text "Saved. Click OK to finish"
				$("span.widgetactivateinlocation").html('saved Click OK to finish');
				$("span.widgetactivateinlocation").animate({ 
					opacity: 1
				  }, 2500);
				$("span.widgetactivateinlocation").animate({ 
					opacity: 0
				  }, 500);

			});
			
			
			// moving the widget one place up
			$(".portlet-header .ui-icon-triangle-1-n").live("click", function() {
				// location = the id of the first parent with as class = location
				var location_id = $(this).parents('.location').filter(':first').attr('id');
				var parameters = 'widget='+$(this).parent().parent().attr('id')+'&location='+location_id+'&amp;action=movewidgetup';
				$.post('<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php', parameters, function(theResponse){
						$("#debug").html(theResponse);
				});

				// the current widget we are moving up
				var currentwidget = $(this).parents('.portlet');
				
				// the previous widget (immediately above the current position)
				var previouswidget = $(this).parents('.portlet').prev('.portlet');
		
				// first we clone the currentwidget and place it after the previouswidget (and hide it);
				var newwidget = currentwidget.clone().insertBefore(previouswidget).hide();
				
				
				currentwidget.slideUp("slow",function(){
					newwidget.slideDown("slow");
					currentwidget.remove();	
					hidefirstup();
					hidelastdown();					
				});

			});
			
			// moving the widget one place down
			$(".portlet-header .ui-icon-triangle-1-s").live("click", function() {
				// location = the id of the first parent with as class = location
				var location_id = $(this).parents('.location').filter(':first').attr('id');
				var parameters = 'widget='+$(this).parent().parent().attr('id')+'&location='+location_id+'&amp;action=movewidgetdown'

				$.post('<?php echo api_get_path(WEB_LIBRARY_PATH);?>widgets.lib.php', parameters, function(theResponse){
						$("#debug").html(theResponse);
				}); 
				
				// the current widget we are moving down
				var currentwidget = $(this).parents('.portlet');
				
				// the next widget (immediately below the current position)
				var nextwidget = $(this).parents('.portlet').next('.portlet');
				
				// first we clone the currentwidget and place it after the nextwidget (and hide it);
				var newwidget = currentwidget.clone().insertAfter(nextwidget).hide();
			
				currentwidget.slideUp("slow",function(){
					newwidget.slideDown("slow");
					currentwidget.remove();		
					hidefirstup();
					hidelastdown();
				});				
			});	

			// hide the titles that need to be hidden according to the setting in the database
			<?php
			hide_titles();
			?>

			// hide or show the title temporarily 
			$('.toggleheader').live("click", function(){
				$(this).parent().prev().toggle();
			});

			
			function hide_titles(widgetid){
				$('#'+widgetid+' .portlet-header').hide();
				$('#'+widgetid).addClass('widgetwithouttitle');
			}	
			
			function toggleheader(widgetid){
				$("#"+widgetid+" .toggleheader").attr("style","display:none;");
			}
			<?php if (api_get_setting('widget_hidden_title_behaviour') == 'showonhover' AND api_is_allowed_to_edit) { ?>
			// show the widget header if the header is hidden (only for the course admin and if the settings widget_hidden_title_behaviour is set to  showonhover
			$(".portlet").hover(
				function (){
					$(".portlet-header:hidden",this).show();
					$(".portlet-header",this).addClass('washidden');
				},
				function (){
					if ($(".portlet-header",this).hasClass('washidden') && $(this).hasClass('widgetwithouttitle')){
						$(".portlet-header",this).hide();
					}
				}				
			);
			<?php  }?>
		});
