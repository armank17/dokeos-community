<?php
function google_calendar_action_link(){
	if ($_GET['action'] <> 'importgooglecalendar'){
		$return = '<a href="agenda.php?action=importgooglecalendar">'.Display::return_icon('pixel.gif', get_lang('ImportGoogleCalendar'), array('class' => 'toolactionplaceholdericon toolcalendargoogleimport')).get_lang('ImportGoogleCalendar').'</a>';
	} else {
		$return = '<a href="agenda.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('ReturnToCalendar'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('ReturnToCalendar').'</a>';
	}
	return $return;
}

function google_calendar_action_handling(){
	switch ($_GET['action']){
		case 'importgooglecalendar':
			google_calendar_import();
			break;
		case 'addimportcalendarform':
			google_calendar_import_formelement();
			break;
		case 'saveimportcalendars':
			google_calendar_import_save();
			break;
		case 'google_login':
			google_login($_POST['googleuser'], $_POST['googlepassword'], $_POST['action'],$_POST['id'], $_POST['calendar']);
			break;
	}
}


function google_calendar_import(){

	global $_setting;
	//debug($_setting);

	echo '<form class="form" id="import_calendar_form">';
	echo '<div class="row"><div class="form_header">'.get_lang('ImportExternalCalendars').'</div></div>';
	$import_calendars = api_get_setting('import_calendar');
	foreach ($import_calendars as $key=>$import_calendar_info){
		$import_calendar = unserialize($import_calendar_info);
		google_calendar_import_formelement($import_calendar['url'], $import_calendar['color']);
	}

	// the form for adding a new import calendar
	google_calendar_import_formelement();

	// hide the last remove button
	echo '<script type="text/javascript">$(".RemoveExternalCalendar:last").hide();</script>';

	// the submit button
	echo '<div class="row save_import_calendars_div">';
	echo '	<div class="label"> </div>';
	echo '	<div class="formw">';
	echo '	<input type="submit" value="save" id="save_import_calendars">';
	echo '	</div>';
	echo '</div>';
	echo '<div class="clear"> </div>';
	echo '</form>';

}

function google_calendar_import_formelement($value='',$color=' '){
	echo '<div class="row">';
	echo '	<div class="label"> </div>';
	echo '	<div class="formw">';
	echo '	<input type="text" name="importcalendarsinput[]" value="'.$value.'" class="importcalendarsinput" size="110" />';
	echo '	<span class="colorselector">';
	echo '		<img src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorpicker/images/select3.png" alt="colorselector" title="colorselector" align="top" style="background-color: '.$color.';">';
	echo '		<input type="text" name="color[]" value="'.$color.'" class="colorvalue" size="7" style="display:none;"/>';
	echo '	</span>';
	Display::display_icon('context-remove.png', get_lang('RemoveExternalCalendar'),array('class'=>'RemoveExternalCalendar'));
	if ($value == ''){
		Display::display_icon('context-add.png', get_lang('AddExternalCalendar'),array('class'=>'AddExternalCalendar'));
	}
	echo '	</div>';
	echo '</div>';
}

function save_import_calendars(){
	// Database table definition
	$table_course_setting = Database::get_course_table ( 'course_setting' );

	// First we delete all the historic imported calendars
	$sql = "DELETE FROM $table_course_setting WHERE variable = 'import_calendar'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );

	// Add all the imported calendars (we use $key+1 because CourseManager::load_course_config_settings checks with empty() function)
	foreach ($_POST['importcalendars'] as $key=>$calendar){
		if (!empty($calendar))
		{
			$value = array('url'=>$calendar,'color'=>$_POST['color'][$key]);

			$sql = "INSERT INTO $table_course_setting (variable, subkey, value) VALUES ('import_calendar', ".Database::escape_string($key+1).", '".Database::escape_string(serialize($value))."')";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}
}

function google_calendar_get_id_js(){
	return "gcal_id = google_calendar_get_id_js($(this), $(this).parent().parent().parent().attr('class'));";
}

function google_calendar_additional_js_libraries(){
	$return[] = '<script type="text/javascript" src="' . api_get_path (WEB_LIBRARY_PATH) . 'javascript/fullcalendar-1.4.5/gcal.js" language="javascript"></script>';
	$return[] = "<script type='text/javascript'>
		function google_calendar_get_id_js(element,classlist){
			gcalfeed_url = '';
			// if it is a gcal event we also need to get the calendar feed
			if (element.hasClass('gcaledit') || element.hasClass('gcaldelete')){
				var classList = classlist.split(/\s+/);
				var gcalfeed_url = '';
				$.each( classList, function(index, item){
					if (item.substring(0, 8) == 'gcal-id-') {
						gcalfeed_url = item.replace('gcal-id-','');
					}
				});
			}
			return gcalfeed_url;
		}
		</script>";
	if ($_GET['action'] == 'importgooglecalendar'){
		$return[] = '<link rel="stylesheet" media="screen" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorpicker/css/colorpicker.css" />';
		$return[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorpicker/js/colorpicker.js"></script>';
		$return[] = "<script type='text/javascript'>
			$(document).ready(function() {
				$(document).ready(function(){
					$('.colorselector img').ColorPicker({
						color: '#0000ff',
						onShow: function (colpkr) {
							$(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							$(colpkr).fadeOut(500);
							return false;
						},
						onChange: function (hsb, hex, rgb, el) {
							$(el).css('background-color', '#' + hex);
							$(el).next().val('#'+hex);
						},
						onSubmit: function(hsb, hex, rgb, el) {
								//$(el).val(hex);
								$(el).css('background-color', '#' + hex);
								$(el).next().val('#'+hex);
								$(el).ColorPickerHide();
							}
					});
				});

				// add a new input element for a imported calendar
				$('.AddExternalCalendar').live('click',function(){
					$.ajax({
					  url: 'ajax.php',
					  data: {action: 'addimportcalendarform'},
					  success: function(data){
							$('.save_import_calendars_div').before(data);
							// remove the add buttons except for the last one
							$('.AddExternalCalendar:not(:last)').remove();
							// show all the remove buttons but hide the last remove button
							$('.RemoveExternalCalendar').show();
							$('.RemoveExternalCalendar:last').hide();
							$('.colorselector img').ColorPicker({
								color: '#0000ff',
								onShow: function (colpkr) {
									$(colpkr).fadeIn(500);
									return false;
								},
								onHide: function (colpkr) {
									$(colpkr).fadeOut(500);
									return false;
								},
								onChange: function (hsb, hex, rgb, el) {
									$(el).css('background-color', '#' + hex);
									$(el).next().val('#'+hex);
								},
								onSubmit: function(hsb, hex, rgb, el) {
										//$(el).val(hex);
										$(el).css('background-color', '#' + hex);
										$(el).next().val('#'+hex);
										$(el).ColorPickerHide();
									}
							});
						}
					});
				});

				// remove an input element for a imported calendar
				$('.RemoveExternalCalendar').live('click',function(){
					$(this).parent().parent().remove();
				});

				// save the import calendars
				$('#save_import_calendars').live('click',function(){
					var postdata = {
						action: 'saveimportcalendars',
						importcalendars: [],
						color: []
					};

					// loop over the imported calendar input elements and place the value in the postData object
					$.each($('.importcalendarsinput'), function(index, el) {
						postdata.importcalendars.push($(el).val());
					});

					// loop over the color input elements and place the value in the postData object
					$.each($('.colorvalue'), function(index, el) {
						postdata.color.push($(el).val());
					});


					$.ajax({
					  url: 'ajax.php?action=saveimportcalendars',
					  data: postdata,
					  type: 'POST',
					  success: function(data){
							$('#import_calendar_form').remove();
							DokeosCalendar.fullCalendar( 'refetchEvents' );
						}
					});

				return false;
			});
		});</script>";
	}
	return implode("\n",$return);
}

function google_calendar_import_save(){
	// Database table definition
	$table_course_setting = Database::get_course_table ( 'course_setting' );


	// First we delete all the historic imported calendars
	$sql = "DELETE FROM $table_course_setting WHERE variable = 'import_calendar'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );

	// Add all the imported calendars (we use $key+1 because CourseManager::load_course_config_settings checks with empty() function)
	foreach ($_POST['importcalendars'] as $key=>$calendar){
		if (!empty($calendar))
		{
			$value = array('url'=>$calendar,'color'=>$_POST['color'][$key]);

			$sql = "INSERT INTO $table_course_setting (variable, subkey, type, category, value) VALUES ('import_calendar', ".Database::escape_string($key+1).", 'checkbox', 'PRO', '".Database::escape_string(serialize($value))."')";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}
}

function google_calendar_sources(){
	global $sources, $htmlHeadXtra;

	$import_calendars = api_get_setting('import_calendar');
	$counter=1;
	foreach($import_calendars as $key=>$import_calendar_info){
		$import_calendar = unserialize($import_calendar_info);
		if (!empty($import_calendar['url'])){
			$sources[] = '$.fullCalendar.gcalFeed("'.$import_calendar['url'].'",{className: "gcal-event gcal-id-'.$key.'"})';
			$import_styles .= '.fc-event.gcal-id-'.$key.', .fc-event.gcal-id-'.$key.' .fc-event-time, .fc-event.gcal-id-'.$key.' a { background-color: '.$import_calendar['color'].'; color: '.getContrastYIQ($import_calendar['color']).';}';
			$counter++;
		}
	}

	$htmlHeadXtra[] = '<style type="text/css">'.$import_styles.'</style>';
}

//http://24ways.org/2010/calculating-color-contrast
//http://snook.ca/technical/colour_contrast/colour.html
function getContrast50($hexcolor){
    return (hexdec($hexcolor) > 0xffffff/2) ? 'black':'white';
}

//http://24ways.org/2010/calculating-color-contrast
//http://snook.ca/technical/colour_contrast/colour.html
function getContrastYIQ($hexcolor){
	$r = hexdec(substr($hexcolor,0,2));
	$g = hexdec(substr($hexcolor,2,2));
	$b = hexdec(substr($hexcolor,4,2));
	$yiq = (($r*299)+($g*587)+($b*114))/1000;
	return ($yiq >= 128) ? 'black' : 'white';
}

function google_calendar_form($form){
	// google calendar
	$googlejavascript = '<script type="text/javascript">
					// toggle the form to login into google
					function googletoggle(){
						if ($("#googlecheckbox").is(":checked")) {
							$("#submit_agenda_item").attr("disabled", true);
							$("#googlecredentials").slideDown();
						} else {
							$("#submit_agenda_item").removeAttr("disabled");
							$("#googlecredentials").slideUp();
							$("#selectgooglecalendar").slideUp();
							$("#googlelogin").attr("style","background-image:url(\"'.api_get_path(WEB_CODE_PATH).'img/google.png'.'\"); background-repeat:no-repeat; padding-left:20px;");
							// Change the title of the button to save the event
							$("#submit_agenda_item").html("'.get_lang('Ok').'")
						}
					}

					// remove preceding zeros
					function parseVal(val)
					{
					   while (val.charAt(0) == "0")
						  val = val.substring(1, val.length);

					   return val;
					}

					// click on the google login button
					$("#googlelogin").live("click", function(){
						// change the button
						$(this).attr("style","background-image:url(\"../img/ajax16x16.gif\"); background-repeat:no-repeat; padding-left:20px; background-position: 2px 3px;");

						// variables
						var strgoogleuser 		= $("#googleuser").val();
						var strgooglepassword 	= $("#googlepassword").val();
						// var strid			= $("#agenda_id").val();
						var strid				= "'.$_GET['id'].'";

						// doing the login and getting all the available calendars and adding these to the #selectgooglecalendar div
						$.ajax({
							url: "ajax.php?action=google_login&v=1",
							type: "POST",
							data: ({googleuser : strgoogleuser, googlepassword : strgooglepassword, action: "getcalendarlist", id : strid, calendar : \''.$_GET['calendar'].'\'}),
							success: function(data){
								if (data != "Error"){
									// hide the google login form
									$("#googlecredentials").toggle();
									// make the button to save the event clickable again
									$("#submit_agenda_item").removeAttr("disabled");
									// Change the title of the button to save the event
									$("#submit_agenda_item").html("'.get_lang('SaveGoogleCalendarEvent').'");
									// show the select element with all the google calendars
									$("#selectgooglecalendar").toggle();
									// fill the select element with all the available google calendars of your gmail account
									$("#selectgooglecalendar").html(data);
								} else {
									alert("Error happened");
									// change the button
									$("#googlelogin").attr("style","background-image:url(\"'.api_get_path(WEB_CODE_PATH).'img/google.png'.'\"); background-repeat:no-repeat; padding-left:20px;");
								}
						  	}
						});

						// if we are editing we also need to retreive all the information about the item we are editing
						if (strid == ""){

						} else {
							$.ajax({
								url: "ajax.php?action=google_login&v=2",
								type: "POST",
								dataType: "json",
								data: ({googleuser : strgoogleuser, googlepassword : strgooglepassword, action: "getiteminfo", id : strid, calendar : \''.$_GET['calendar'].'\'}),
								success: function(data){
									if (data != "Error"){
										// show the main form
										$("#new_agenda_item .row").toggle();
										$("#selectgooglecalendar .row").show();
										$("#new_agenda_item input").removeAttr("disabled");
										// fill the form with the content we received from google calendar
										$("input[name=\"title\"]").val(data.title);
										$("#agenda_id").val(strid);
										$("select[name=\"start_date[d]\"]").val(parseInt(parseVal(data.sdate_dd)));
										$("select[name=\"start_date[F]\"]").val(parseInt(parseVal(data.sdate_mm)));
										$("select[name=\"start_date[Y]\"]").val(parseInt(parseVal(data.sdate_yy)));
										$("select[name=\"start_date[H]\"]").val(parseInt(parseVal(data.sdate_hh)));
										$("select[name=\"start_date[i]\"]").val(parseInt(parseVal(data.sdate_ii)));
										$("select[name=\"end_date[d]\"]").val(parseInt(parseVal(data.edate_dd)));
										$("select[name=\"end_date[F]\"]").val(parseInt(parseVal(data.edate_mm)));
										$("select[name=\"end_date[Y]\"]").val(parseInt(parseVal(data.edate_yy)));
										$("select[name=\"end_date[H]\"]").val(parseInt(parseVal(data.edate_hh)));
										$("select[name=\"end_date[i]\"]").val(parseInt(parseVal(data.edate_ii)));
										//CKEDITOR.instances.content.setData(data.content);

										// set the selected google calendar as active
										$("#available_googlecalendars").val("http://www.google.com/calendar/feeds/"+data.calendar+"/private/full");
										$("#selectedgooglecalendar").val("http://www.google.com/calendar/feeds/"+data.calendar+"/private/full");

										// hide all the google related forms because we cannot move it to a different calendar
										$("#googlecheckbox").parent().parent().hide();
										//$("#selectedgooglecalendar").parent().parent().hide();
										$("#selectgooglecalendar").hide();

										// change submit button
										$("#submit_agenda_item").html("'.get_lang('UpdateGoogleCalendarEvent').'");
									} else {
										alert("We could not get the information of this Google Event");
									}
							  }
							});
						}

						return false;
					})

					// selecting a certain calendar
					$("#available_googlecalendars").live("change", function() {
						var selectedvalue = $("#available_googlecalendars option:selected").val()
					  	$("#selectedgooglecalendar").val(selectedvalue);
					});
			   </script>';

	// we are editing
	if (strstr($_GET['id'],'google_')){
		$googlejavascript .= '<script type="text/javascript">$(document).ready(function() {
					//$("#new_agenda_item .row").attr("style","opacity:0.4;filter:alpha(opacity=40)");
					// hide the main form
					$("#new_agenda_item .row").toggle();
					$("#new_agenda_item input").attr("disabled", true);

					// show the google login form
					$("#googlecredentials").prepend("This is a Google Calendar Event. You should login first before you can change this event");
					$("#googlecredentials").toggle();
					$("#googlecredentials input").removeAttr("disabled");

					// set the google checkbox to checked
					$("#googlecheckbox").attr("checked","checked");
				});</script>';
		$input_values['agenda_id'] = $_GET['id'];
	}

	// adding the form elements for the google login and calendar selection
	$form->addElement ('html', $googlejavascript);
	$form->addElement ('checkbox', 'google', '',get_lang('SaveGoogle'), array('onclick'=>'googletoggle();', 'style'=>'margin-bottom: 10px;', 'id' => 'googlecheckbox'));
	$form = google_login_form($form);
	return $form;
}

function google_login_form($form){
	$form->addElement ( 'html', '<div style="clear: both;"></div>' );
	$form->addElement ( 'html', '<div id="googlecredentials" style="display:none;margin-top: 10px;">' );
	$form->addElement ( 'text', 'googleuser', get_lang ( 'GoogleUser' ), array ('value' => '', 'id' => 'googleuser','maxlength' => '250', 'size' => '100' ,'autocomplete' => 'off', 'style' => 'background-image:url("../img/google_user.png"); background-repeat:no-repeat; padding-left:20px;background-position: 2px;') );
	$form->addElement ( 'password', 'googlepassword', get_lang ( 'GooglePassword' ), array ('id' => 'googlepassword', 'maxlength' => '250', 'size' => '100' ,  'autocomplete' => 'off', 'style' => 'background-image:url("../img/google_password.png"); background-repeat:no-repeat; padding-left:20px;background-position: 2px;') );
	$form->addElement ( 'submit', 'googlelogin', get_lang ( 'LoginIntoGoogle' ), array('id' => 'googlelogin', 'style' => 'background-image:url("../img/google.png"); background-repeat:no-repeat; padding-left:20px;background-position: 2px;') );
	$form->addElement ( 'html', '</div>' );
	$form->addElement ( 'hidden', 'selectedgooglecalendar', '', array('id' => 'selectedgooglecalendar', 'size' => '100') );
	$form->addElement ( 'html', '<div id="selectgooglecalendar" style="display:none;margin-top: 10px;">' );
	$form->addElement ( 'html', '</div>' );
	return $form;
}

// login into google with the given credentials
function google_login($user, $pass, $action, $id, $other){
	// set the include path to the Zend Framework
	set_include_path(api_get_path(LIBRARY_PATH).'ZendFramework-1.11.4/library');

	// Zend Framwork libraries
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	Zend_Loader::loadClass('Zend_Gdata_Calendar');
	Zend_Loader::loadClass('Zend_Gdata_HttpClient');

	try{
		// connect to service
		$gcal = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
		$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $gcal);
		$service = new Zend_Gdata_Calendar($client);
	} catch (Zend_Gdata_App_AuthException $e){
		echo 'Error';
		return false;
	}

	google_action($service, $action, $id, $other);
}

function google_action($service, $action, $id, $calendar){
	switch ($action){
		case 'deleteevent':
			try {
				// getting the user of the calendar
				$import_calendars = api_get_setting('import_calendar');
				$calendar_info = unserialize($import_calendars[$calendar]);
				$calendar_feed = $calendar_info['url'];
				$good_calendar_feed = str_replace('https://www.google.com/calendar/feeds/','',$calendar_feed);
				//debug($good_calendar_feed,'good_calendar_feed');
				$cutoff = strstr($good_calendar_feed,'/');
				$good_calendar_feed = str_replace($cutoff,'',$good_calendar_feed);
				//debug($good_calendar_feed,'good_calendar_feed');
				//debug($goodid,'good_id');
				//$eventinfo = $service->getCalendarEventEntry($feed.'/'.$goodid);

				// query the service to find the information of the event
				$query = $service->newEventQuery();
				$query->setUser($good_calendar_feed);
				$query->setVisibility('private');
				$query->setProjection('full');
				$query->setEvent($goodid);
				$event = $service->getCalendarEventEntry($query);
				$event->delete();
			} catch (Zend_Gdata_App_Exception $e) {
				//echo "Error: " . $e->getResponse();
				echo 'Error'.$id;
			}
			break;
		case 'getcalendarlist':
				// get a list of all calendars
				try {
					$listFeed = $service->getCalendarListFeed();
					echo '<div class="row">';
					echo '	<div class="label"> </div>';
					echo '	<div class="formw">';
					echo '		<select name="available_googlecalendars" id="available_googlecalendars">';
					foreach ($listFeed as $calendar) {
						echo '<option value="' . $calendar->link[0]->href . '">';
						echo $calendar->title;
						echo '</option>';
					}
					echo '		</select>';
					echo '	</div>';
					echo '</div>';
				} catch (Zend_Gdata_App_Exception $e) {
					echo 'Error';
					return false;
				}
			break;
		case 'getiteminfo':
				//echo 'http://www.google.com/calendar/feeds/default/private/full/nrf9vp7oc60ufc9fqvi2qp48ms';
				//$eventinfo = $service->getCalendarEventEntry('http://www.google.com/calendar/feeds/default/private/full/nrf9vp7oc60ufc9fqvi2qp48ms');

				// getting the id of the event
				$goodid = str_replace('google_','',$id);
				$goodid = str_replace('@google.com','',$goodid);

				// getting the user of the calendar
				$import_calendars = api_get_setting('import_calendar');
				$calendar_info = unserialize($import_calendars[$calendar]);
				$calendar_feed = $calendar_info['url'];
				$good_calendar_feed = str_replace('https://www.google.com/calendar/feeds/','',$calendar_feed);
				//debug($good_calendar_feed,'good_calendar_feed');
				$cutoff = strstr($good_calendar_feed,'/');
				$good_calendar_feed = str_replace($cutoff,'',$good_calendar_feed);
				//debug($good_calendar_feed,'good_calendar_feed');
				//debug($goodid,'good_id');
				//$eventinfo = $service->getCalendarEventEntry($feed.'/'.$goodid);

				// query the service to find the information of the event
				$query = $service->newEventQuery();
				$query->setUser($good_calendar_feed);
				$query->setVisibility('private');
				$query->setProjection('full');
				$query->setEvent($goodid);

				// getting the information
				try {
					$eventinfo = $service->getCalendarEventEntry($query);
				} catch (Zend_Gdata_App_Exception $e) {
					echo "Error: " . $e->getMessage();
				}

				//debug($eventinfo,'eventinfo');

				$when = $eventinfo->getWhen();
				//debug($when);
				$title = $eventinfo->title;
				$content = $eventinfo->content;
				$startTime = strtotime($when[0]->getStartTime());
				//debug($startTime,'startTime');
				$sdate_dd = date('d', $startTime);
				$sdate_mm = date('m', $startTime);
				$sdate_yy = date('Y', $startTime);
				$sdate_hh = date('H', $startTime);
				$sdate_ii = date('i', $startTime);
				$endTime = strtotime($when[0]->getEndTime());
				$edate_dd = date('d', $endTime);
				$edate_mm = date('m', $endTime);
				$edate_yy = date('Y', $endTime);
				$edate_hh = date('H', $endTime);
				$edate_ii = date('i', $endTime);
				$return_array = array('calendar' => addslashes($good_calendar_feed), 'title' => addslashes($title), 'content' => addslashes($content), 'sdate_dd' => $sdate_dd, 'sdate_mm' => $sdate_mm, 'sdate_yy' => $sdate_yy, 'sdate_hh' => $sdate_hh, 'sdate_ii' => $sdate_ii, 'edate_dd' => $edate_dd, 'edate_mm' => $edate_mm, 'edate_yy' => $edate_yy, 'edate_hh' => $edate_hh, 'edate_ii' => $edate_ii);
				echo json_encode($return_array);
			break;
	}
}

/**
 *
 */
function store_google($values, $id){
	if ($values['google'] <> 1){
		return false;
	}

	// Database table definition
	$table_agenda 			= Database::get_course_table ( TABLE_AGENDA );
	$table_item_property 	= Database::get_course_table ( TABLE_ITEM_PROPERTY );

	// first we have to remove it from the local calendar table (when we are editing it normally will be added to the local calendar also because the id is not numeric)
	$sql = "DELETE FROM $table_agenda WHERE id = '".Database::escape_string($id)."'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );

	// secondly we have to remove it from the local item_property table (when we are editing it normally will be added to the local calendar also because the id is not numeric)
	$sql = "DELETE FROM $table_item_property WHERE ref = '".Database::escape_string($id)."' AND tool = '".TOOL_CALENDAR_EVENT."'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );

	// set the include path to the Zend Framework
	set_include_path(api_get_path(LIBRARY_PATH).'ZendFramework-1.11.4/library');

	// Zend Framwork libraries
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	Zend_Loader::loadClass('Zend_Gdata_Calendar');
	Zend_Loader::loadClass('Zend_Gdata_HttpClient');

	try{
		// connect to service
		$gcal = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
		//$user = $_POST['googleuser'];
		//$pass = $_POST['googlepassword'];
		$client = Zend_Gdata_ClientLogin::getHttpClient($values['googleuser'], $values['googlepassword'], $gcal);
		$service = new Zend_Gdata_Calendar($client);
	} catch (Zend_Gdata_App_AuthException $e){
		echo 'Error';
		return false;
	}

	// change the date to the format Google expects
	$start_day 		= substr($values['start_date'],8,2);
	$start_month 	= substr($values['start_date'],5,2);
	$start_year 	= substr($values['start_date'],0,4);
	$start_hour 	= substr($values['start_date'],11,2);
	$start_minute	= substr($values['start_date'],14,2);

	$end_day 		= substr($values['end_date'],8,2);
	$end_month 		= substr($values['end_date'],5,2);
	$end_year 		= substr($values['end_date'],0,4);
	$end_hour 		= substr($values['end_date'],11,2);
	$end_minute		= substr($values['end_date'],14,2);

	$start 			= date(DATE_ATOM, mktime($start_hour, $start_minute, 0, $start_month, $start_day, $start_year));
	$end 			= date(DATE_ATOM, mktime($end_hour, $end_minute, 0, $end_month, $end_day, $end_year));

	// finally we save it with Google Calendar
	$title = htmlentities($values['title']);

	// saving an existing event (edit)
	if (strstr($values['agenda_id'],'google_')){
		// getting the id of the event
		$goodid = str_replace('google_','',$values['agenda_id']);
		$goodid = str_replace('@google.com','',$goodid);

		// getting the user of the calendar
		$import_calendars = api_get_setting('import_calendar');
		$calendar_info = unserialize($import_calendars[$_GET['calendar']]);
		$calendar_feed = $calendar_info['url'];
		$good_calendar_feed = str_replace('https://www.google.com/calendar/feeds/','',$calendar_feed);
		$cutoff = strstr($good_calendar_feed,'/');
		$good_calendar_feed = str_replace($cutoff,'',$good_calendar_feed);
		//debug($good_calendar_feed);
		//debug($goodid);


		$query = $service->newEventQuery();
		$query->setUser($good_calendar_feed);
		$query->setVisibility('private');
		$query->setProjection('full');
		$query->setEvent($goodid);

		try {
		    $event = $service->getCalendarEventEntry($query);
		    $event->title = $service->newTitle($title);
			$event->content = $service->newContent(strip_tags($values['content']));
		    $when = $service->newWhen();
		    $when->startTime = $start;
		    $when->endTime = $end;
		    $event->when = array($when);
		    $event->save();
		} catch (Zend_Gdata_App_Exception $e) {
			die("Error: " . $e->getResponse());
		}
		echo 'Event successfully modified!';
	} else {
		// saving a NEW event
		try {
			$event = $service->newEventEntry();
			$event->title = $service->newTitle($title);
			$event->content = $service->newContent(strip_tags($values['content']));
			$when = $service->newWhen();
			$when->startTime = $start;
			$when->endTime = $end;
			$event->when = array($when);
			$service->insertEvent($event,$values['selectedgooglecalendar']);
		} catch (Zend_Gdata_App_Exception $e) {
			echo "Error: " . $e->getResponse();
		}
		echo 'Event successfully added!';
	}
}

function google_calendar_delete_event($id){
	$googlejavascript = '<script type="text/javascript">
				$(document).ready(function() {
					// show the google login form elements
					$("#googlecredentials").toggle();

					// hide the calendar navigation (because the calendar #content is replaced with the form to login into Google Calendar
					$(".actions table").remove();

					// add a link to return to the calendar
					$(".actions").prepend("<a href=\"agenda.php\"><img src=\"../img/go_previous_32.png\" alt=\"'.get_lang('ReturnToCalendar').'\" title=\"'.get_lang('ReturnToCalendar').'\"> '.get_lang('ReturnToCalendar').'</a>");

					// click on the google login button
					$("#googlelogin").live("click", function(){
						// change the button
						$(this).attr("style","background-image:url(\"../img/ajax16x16.gif\"); background-repeat:no-repeat; padding-left:20px; background-position: 2px 3px;");

						// variables
						var strgoogleuser 		= $("#googleuser").val();
						var strgooglepassword 	= $("#googlepassword").val();
						var strid				= "'.$id.'"

						// doing the login and getting all the available calendars and adding these to the #selectgooglecalendar div
						$.ajax({
							url: "ajax.php?action=google_login",
							type: "POST",
							data: ({googleuser : strgoogleuser, googlepassword : strgooglepassword, action: "deleteevent", id : strid, calendar : \''.$_GET['calendar'].'\'}),
							success: function(data){
								if (data != "Error"){
									$("#google_delete_event").remove();
									$(location).attr("href","agenda.php");
								} else {
									alert("Error happened");
									// change the button
									$("#googlelogin").attr("style","background-image:url(\"'.api_get_path(WEB_CODE_PATH).'img/google.png'.'\"); background-repeat:no-repeat; padding-left:20px;");
								}
						  }
						});
						return false;
					});
				});
				</script>
				';
	$form = new FormValidator ( 'google_delete_event', 'post', $_SERVER ['REQUEST_URI'] );
	$form->addElement ('html', $googlejavascript);
	$form->addElement ('html', 'This is a Google Calendar Event. You should login first before you can delete this event');
	$form = google_login_form($form);
	//debug($form);
	$form->display();
}
?>
