<?php
//$Id: announcements.php 16702 2008-11-10 13:02:30Z elixir_inter $
/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2004-2008 Dokeos SPRL
  Copyright (c) 2003 Ghent University (UGent)
  Copyright (c) 2001 Universite catholique de Louvain (UCL)
  Copyright (c) various contributors

  For a full list of contributors, see "credits.txt".
  The full license can be read in "license.txt".

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  See the GNU General Public License for more details.

  Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
  info@dokeos.com

  ==============================================================================

  AGENDA HOMEPAGE

  This file takes care of all agenda navigation and displaying.

  @package dokeos.blogs
  ==============================================================================
 */

/*
  ==============================================================================
  INIT
  ==============================================================================
 */
// name of the language file that needs to be included
$language_file = 'agenda';

include ('../inc/global.inc.php');
require ('functions.php');
/*================ Translate lang calendar ================*/
if(($isocode = api_get_language_isocode())!="en")
    $htmlHeadXtra [] = '<script src="' . api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-'.$isocode.'.js" type="text/javascript" language="javascript"></script>';
/*================ ================ ================*/

$this_section = SECTION_COURSES;


/* ------------	ACCESS RIGHTS ------------ */
// notice for unauthorized people.
api_protect_course_script(true);


//session
if (isset($_GET['id_session'])) {
    $_SESSION['id_session'] = intval($_GET['id_session']);
}

$lib_path = api_get_path(LIBRARY_PATH);
require_once ($lib_path . '/display.lib.php');;

$nameTools = get_lang('Calendar');
$DaysShort = api_get_week_days_short();
$DaysLong = api_get_week_days_long();
$MonthsLong = api_get_months_long();

$current_page = $_GET['action'];
/*
  ==============================================================================
  DISPLAY
  ==============================================================================
 */
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

Display :: display_tool_header();

echo '<div id="content">';
?>
<table width="940px" style="margin-top:15px">
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
// ending div#content
    echo '</div>';
// Display the footer
    Display::display_footer();
?>