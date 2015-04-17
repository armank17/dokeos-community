<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) Dokeos SPRL

	For a full list of contributors, see "credits.txt".
	For licensing terms, see "dokeos_license.txt"

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	http://www.dokeos.com
==============================================================================
*/

/**
==============================================================================
*	@package dokeos.calendar
==============================================================================
*/

// setting the language file
$language_file = 'agenda';

// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');

// additional libraries
require (api_get_path ( LIBRARY_PATH ) . 'groupmanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
require ('functions.php');
/*================ Translate lang calendar ================*/
if(($isocode = api_get_language_isocode())!="en")
    $htmlHeadXtra [] = '<script src="' . api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-'.$isocode.'.js" type="text/javascript" language="javascript"></script>';
/*================ ================ ================*/

$htmlHeadXtra[] = '<script language="javascript">
    $(document).ready(function() {
        var currentDate = new Date();
        $("#start_date").datetimepicker({
                showOn: "button",
                buttonImage: "' . api_get_path(WEB_IMG_PATH) . 'calendar.gif",
                buttonImageOnly: true,
                dateFormat: "dd-mm-yy",
                timeFormat: "hh:mm:00 tt",
                currentText: getLang("Today"),
                closeText: getLang("Done"),
                onClose: function(dateText, inst) {
                                var testStartDate = $(this).datetimepicker("getDate");
                                testStartDate.setHours(testStartDate.getHours()+1); 
                                $("#end_date").datetimepicker("setDate", testStartDate);

                },
                onSelect: function (selectedDateTime){
                                var testStartDate = $(this).datetimepicker("getDate");
                                testStartDate.setHours(testStartDate.getHours()+1); 
                                $("#end_date").datetimepicker("setDate", testStartDate);
                }
        });
        $("#end_date").datetimepicker({
                //defaultDate: "+1m",
                showOn: "button",
                buttonImage: "' . api_get_path(WEB_IMG_PATH) . 'calendar.gif",
                buttonImageOnly: true,
                //defaultDate: new Date(),
                dateFormat: "dd-mm-yy",
                timeFormat: "hh:mm:00 tt",
                currentText: getLang("Today"),
                closeText: getLang("Done")
        });
    });
</script>';

//session
if (isset($_GET['id_session'])) {
    $_SESSION['id_session'] = intval($_GET['id_session']);
}

$lib_path = api_get_path(LIBRARY_PATH);
require_once ($lib_path . '/display.lib.php');

$nameTools = get_lang('Calendar');
$DaysShort = api_get_week_days_short();
$DaysLong = api_get_week_days_long();
$MonthsLong = api_get_months_long();

$current_page = $_GET['action'];
$htmlHeadXtra[] = '<script language="javascript">
  $(document).ready(function() {
        $("body").append("<input type=\'hidden\' id=\'title\' value=\'\'/>");
		$("#datepicker").datepicker({
            dateFormat: "yy-mm-dd",
            beforeShow: function(input, inst) {
            },
            onChangeMonthYear: function(year, month, inst) {
              $.ajax({
                  url: "ajax.php?'.api_get_cidreq().'",
                  data: {action: "studentgetevents", year: year, month: month},
                  beforeSend: function() {
                    $("#datapicker_content").hide();
                    $("#loader").show()
                  },
                  complete: function(){
                    $("#datapicker_content").show();
                    $("#loader").hide()
                  },
                  success: function(data){
                    $("#datapicker_content").html(data);
                  }
              });
            },
            onSelect: function(dateText, inst) {
              $.ajax({
                  url: "ajax.php?'.api_get_cidreq().'",
                  data: {action: "studentgeteventsday", date: dateText},
                  beforeSend: function() {
                    $("#datapicker_content").hide();
                    $("#loader").show()
                  },
                  complete: function(){
                    $("#datapicker_content").show();
                    $("#loader").hide()
                  },
                  success: function(data){
                    $("#datapicker_content").html(data);
                  }
              });
            }
        });
	});
</script>';
$htmlHeadXtra[] = '<script language="javascript">
function date_error(number){
    if(number===1){
        alert("'.get_lang(InvalidDate).'");
    }else if(number===2){
        alert("'.get_lang(StartDateShouldBeBeforeEndDate).'");
    }
}
</script>';
$htmlHeadXtra[] = '
    <style type="text/css">
    #footer {
    background-color:transparent !important;
    }
    .ui-widget-header {
    cursor:default;
    }
//    .ui-datepicker-prev, .ui-datepicker-next {
//    top:7px !important;
//    }
//    .ui-widget-content {
//    cursor:default;
//    }
    </style>
';
// javascript code for teachers... and students
if ($_GET['action'] <> 'detail' AND $_GET['action'] <> 'add' AND $_GET['action'] <> 'edit') {
	//$htmlHeadXtra[] = calendar_javascript();
}
// google calendar import
if (api_get_setting('calendar_google_import')=='true') {
	//$htmlHeadXtra[] = google_calendar_additional_js_libraries();
}
// the toolbar set that has to be used
if (!api_is_allowed_to_edit()) {
    header('Location: agenda_student.php');
	$fck_attribute['ToolbarSet'] = 'AgendaStudent';
} else {
	$fck_attribute['ToolbarSet'] = 'Agenda';
}
$fck_attribute['Height'] = '200px;';
$fck_attribute['Width'] = '650px;';

// Setting the section of this file (for the tabs)
$this_section = SECTION_COURSES;

// access rights
api_protect_course_script ();

// breadcrumbs
$interbreadcrumb[] = array ("url" => "agenda.php", "name" => get_lang('Agenda'));
switch ($_GET['action']) {
	case 'add':
		$interbreadcrumb[] = array ("url" => "agenda.php", "name" => get_lang('AgendaAdd'));
		break;
	case 'edit':
		$interbreadcrumb[] = array ("url" => "agenda.php", "name" => get_lang('AgendaEdit'));
		break;
	case 'detail':
		$interbreadcrumb[] = array ("url" => "agenda.php", "name" => get_lang('AgendaDetail'));
		break;
}

// action handling before anything is displayed
handle_header_calendar_actions();

// Displaying the header
Display::display_tool_header ();
//Display::display_tool_header ();

// Tool introduction
Display::display_introduction_section ( TOOL_CALENDAR_EVENT );

// Actions
echo '<div class="actions">';
        $curr_view = 'list';
	switch ($_GET ['action']) {
		case 'add' :
			// return to the calendar display
			if (!isset($_POST['submit_agenda_item'])) {
				$return .= '<a href="agenda.php?'.api_get_cidreq().'&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('ReturnToCalendar'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('ReturnToCalendar').'</a>';
			}
			$return .= '<a href="agenda.php?'.api_get_cidreq().'&action=add&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('AgendaAdd'), array('class' => 'toolactionplaceholdericon toolcalendaraddevent')).get_lang('AgendaAdd').'</a>';
			break;
		case 'edit' :
			// return to the calendar display
			if (!isset($_POST['submit_agenda_item'])) {
				$return .= '<a href="agenda.php?'.api_get_cidreq().'&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('ReturnToCalendar'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('ReturnToCalendar').'</a>';
			}
			// adding of event
			if (api_is_allowed_to_edit(false,true) OR (api_get_course_setting('allow_user_edit_agenda') && !api_is_anonymous() && api_is_allowed_to_session_edit(false,true) ) OR (api_get_setting('user_manage_group_agenda')=='true' AND !empty($group_memberships))){
				$return .= '<a href="agenda.php?'.api_get_cidreq().'&action=add&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('AgendaAdd'), array('class' => 'toolactionplaceholdericon toolcalendaraddevent')).get_lang('AgendaAdd').'</a>';
			}
			break;
		case 'detail' :
			// return to the calendar display
			$return .= '<a href="agenda.php?'.api_get_cidreq().'&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('ReturnToCalendar'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('ReturnToCalendar').'</a>';
            break;
		case 'delete' :
			// return to the calendar display
			$return .= '<a href="agenda.php?'.api_get_cidreq().'&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('ReturnToCalendar'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('ReturnToCalendar').'</a>';
		case '':
			// adding of event
			if (api_is_allowed_to_edit(false,true) OR (api_get_course_setting('allow_user_edit_agenda') && !api_is_anonymous() && api_is_allowed_to_session_edit(false,true) ) OR (api_get_setting('user_manage_group_agenda')=='true' AND !empty($group_memberships))){
				$return .= '<a href="agenda.php?'.api_get_cidreq().'&action=add&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('AgendaAdd'), array('class' => 'toolactionplaceholdericon toolcalendaraddevent')).get_lang('AgendaAdd').'</a>';
			}
			break;
	}
	// export of all events
        $return .= '<a href="agenda.php?'.api_get_cidreq().'&action=export&view='.$curr_view.'">'.Display::return_icon('pixel.gif', get_lang('OutlookGmailExport'), array('class' => 'toolactionplaceholdericon toolcalendaricalexport')).get_lang('OutlookGmailExport').'</a>';
        echo $return;
echo '</div>';
if(isset($_SESSION["display_confirmation_message"])){
    Display :: display_confirmation_message2($_SESSION["display_confirmation_message"],false,true);
    unset($_SESSION["display_confirmation_message"]);    
}
// start the content div
echo '<div id="content">'; 
// Display Message
if(isset($_SESSION["display_warning_message"])){
    Display :: display_warning_message($_SESSION["display_warning_message"],false,true);
    unset($_SESSION["display_warning_message"]);
}
if(isset($_SESSION["display_error_message"])){
    Display :: display_error_message($_SESSION["display_error_message"],false,true);
    unset($_SESSION["display_error_message"]);
}
// Action handling
handle_calendar_actions();

// dialog forms
//display_dialog_course_event_form();
//display_dialog_course_event_edit_form();
// session,course,etc form
//display_dialog_general_form();

$month = (int) $_GET['month'] ? (int) $_GET['month'] : (int) date('m');
$year = (int) $_GET['year'] ? (int) $_GET['year'] : date('Y');
$filter = $year.'-'.$month;
if ((isset($_GET['action']) && $_GET['action'] != 'add' && $_GET['action'] != 'edit') || !isset($_GET['action'])) {
?>
<table width="940px">
    <tr>
        <td width="235px" valign="top">
            <div id="datepicker"></div>
        </td>
        <td valign="top">
            <div id="loader" style="display:none;"><?php echo Display::return_icon('mozilla_blu.gif'); ?></div>
          <div id="datapicker_content">
            <?php
            $month = (int) $_GET['month'] ? (int) $_GET['month'] : (int) date('m');
            $year = (int) $_GET['year'] ? (int) $_GET['year'] : date('Y');
            $filter = $year.'-'.$month;
            user_display_events_by_day($filter);
            ?>              
          </div>
        </td>
    </tr>
</table>
<?php
}

echo '</div>';
// Displaying the footer
Display::display_footer();
?>