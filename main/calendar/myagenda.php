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

// we are not inside a course, so we reset the course id
$cidReset = true;

// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');

// include additional libraries
require (api_get_path ( LIBRARY_PATH ) . 'groupmanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
require ('functions.php');

/*================ Translate lang calendar ================*/

    $htmlHeadXtra [] = '<script src="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jquery.timepicker/jquery-ui-timepicker-addon.js" type="text/javascript" language="javascript"></script>';    
if(($isocode = api_get_language_isocode())!="en")
    $htmlHeadXtra [] = '<script src="' . api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-'.$isocode.'.js" type="text/javascript" language="javascript"></script>';
/*================ ================ ================*/
$htmlHeadXtra[] = '<script language="javascript">
    $(document).ready(function() {
    $("div#agenda-fix").css({"width":"100%"});
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
// the toolbar set that has to be used
$fck_attribute['ToolbarSet'] = 'Agenda';
$fck_attribute['Height'] = '200px;';

// Setting the section of this file (for the tabs)
$this_section = SECTION_MYAGENDA;

// access control
api_block_anonymous_users();

// add additional javascript and css
if($isocode !="en")
    Display::javascript(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/jquery-ui/1.8.2/ui/i18n/jquery.ui.datepicker-'. api_get_language_isocode() .'.js');

Display::javascript(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/jquery.qtip/jquery.qtip.min.js');
Display::javascript(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/extraFunctions.js');
//Display::javascript(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/fullcalendar-1.4.5/fullcalendar.js');
Display::javascript(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/fullcalendar-1.4.5/fullcalendar.min.js');
Display::css(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/fullcalendar-1.4.5/fullcalendar-dokeos.css');
Display::css(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/fullcalendar-1.4.5/fullcalendar.css');
Display::css(api_get_path ( WEB_CODE_PATH ) . 'inc/lib/javascript/jquery.qtip/jquery.qtip.min.css');

if ($_GET['action']<>'myadd' AND $_GET['action']<>'myedit'){
	$htmlHeadXtra [] = mycalendar_javascript();
}
$htmlHeadXtra[] = "<style>
   div.label { margin-top:0px !important; }
   #start_date, #end_date{
               position:relative !important;
               z-index: 1000 !important;
                }
</style>";

// breadcrumbs
$interbreadcrumb[] = array ("url" => "myagenda.php", "name" => get_lang('Agenda'));
switch ($_GET['action']){
	case 'myadd':
		$interbreadcrumb[] = array ("url" => "myagenda.php?action=myadd", "name" => get_lang('AgendaAdd'));
		break;
	case 'myedit':
		$interbreadcrumb[] = array ("url" => "agenda.php?action=myedit&", "name" => get_lang('AgendaEdit'));
		break;
}

// showing the header
Display::display_header();

// Actions
echo '<div class="actions fc-header" >';
echo mycalendar_actions();
echo '</div>';

echo '<div id="content" style="width:940px;">';

// Action handling
handle_mycalendar_actions();

// dialog forms
display_dialog_myevent_form();
display_dialog_myevent_edit_form();

// session,course,etc form
display_dialog_general_form();

echo '<div id="calendar"></div>';
echo '</div>';

// Displaying the footer
Display::display_footer();
?>