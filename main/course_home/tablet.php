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
 *         HOME PAGE FOR EACH COURSE
 *
 * 	This page, included in every course's index.php is the home
 * 	page. To make administration simple, the teacher edits his
 * 	course from the home page. Only the login detects that the
 * 	visitor is allowed to activate, deactivate home page links,
 * 	access to the teachers tools (statistics, edit forums...).
 *
 * 	@package dokeos.course_home
  ==============================================================================
 */
// header
$GLOBALS['display_learner_view'] = api_get_setting('student_view_enabled') == 'true';
$GLOBALS['course_home_visibility_type'] = true;
Display::display_header($course_title, "Home");
//statistics
echo '
<style type="text/css">
#courseintro {
//    margin-top:20px !important;
    padding-bottom:20px;
//    top: -9px !important;
    position:relative;
}

.blender {
    top:-9px !important;
}

.confirmation-message {
top:35px!important;
    margin-bottom:30px !important;
}
</style>
';


if (!isset($coursesAlreadyVisited[$_cid])) {
    event_access_course();
    $coursesAlreadyVisited[$_cid] = 1;
    api_session_register('coursesAlreadyVisited');
}
// database table definition
$tool_table = Database::get_course_table(TABLE_TOOL_LIST);

$temps = time();
$reqdate = "&reqdate=$temps";
                       
// introduction section
Display::display_introduction_section(TOOL_COURSE_HOMEPAGE, array(
    'CreateDocumentWebDir' => api_get_path('WEB_COURSE_PATH') . api_get_course_path() . '/document/',
    'CreateDocumentDir' => 'document/',
    'BaseHref' => api_get_path('WEB_COURSE_PATH') . api_get_course_path() . '/'
        )
);
// action handling
if (api_is_allowed_to_edit(null, true)) {
    // make the tool visible
    if (!empty($_GET['hide'])) { // visibility 1 -> 0
        change_tool_visibility($_GET['id'], 0);
        Display::display_confirmation_message(get_lang('ToolIsNowHidden'));
    } elseif (!empty($_GET['restore'])) { // visibility 0,2 -> 1
        // make the tool invisible
        change_tool_visibility($_GET['id'], 1);
        Display::display_confirmation_message(get_lang('ToolIsNowVisible'));
    }
}
if (api_is_platform_admin()) {
    // Show message to confirm that a tools must be hidden from available tools
    // visibility 0,1->2
    if (!empty($_GET['askDelete'])) {
        echo '<div id="toolhide">';
        echo get_lang("DelLk");
        echo '<br />&nbsp;&nbsp;&nbsp;';
        echo '<a href="' . api_get_self() . '">' . get_lang('No') . '</a>&nbsp;|&nbsp;';
        echo '<a href="' . api_get_self() . '?delete=yes&amp;id=' . Security::remove_XSS($_GET['id']) . '">' . get_lang('Yes') . '</a>';
        echo '</div>';
    } elseif (isset($_GET[delete]) && $_GET[delete]) {
        // Delete a link. Note: this is different than in the other views! In the other views the visibility is set to 2
        Database::query("DELETE FROM $tool_table WHERE id='" . Database::escape_string(intval($id)) . "' AND added_tool=1", __FILE__, __LINE__);
    }
}
/*
  -----------------------------------------------------------
  Tools for course admin only
  -----------------------------------------------------------
 */
//if (api_is_allowed_to_edit(null, true) && !api_is_course_coach()) { // if admin is coach he has students rights in all courses
$current_protocol = $_SERVER['SERVER_PROTOCOL'];
$current_host = $_SERVER['HTTP_HOST'];
$server_protocol = substr($current_protocol, 0, strrpos($current_protocol, '/'));
$server_protocol = $server_protocol . '://';
if ($current_host == 'localhost') {
    //Get information of path
    $info = explode('courses', api_get_self());
    $path_work = substr($info[0], 0, strlen($info[0]));
} else {
    $path_work = "";
}

if (api_get_setting('show_session_data') == 'true' && $id_session > 0) {
    echo '<div class="sectiontablet main_activity" style="margin-top:30px; clear:both">
            <span class="sectiontitle">' . get_lang("SessionData") . '</span>
            <div style="padding:10px; margin-top:-35px;">
            <table>
                 ' . show_session_data($id_session) . '
            </table>
            </div>
	</div>';
}
$is_allowed_to_edit = api_is_allowed_to_edit();
$is_platform_admin = api_is_platform_admin();
if ($is_allowed_to_edit) {
    $my_list = get_tools_category(TOOL_BASIC);
    if (count($my_list) > 0) {
        //echo '<span class="sectiontablettitle">' . get_lang("Basic") . '</span>';
        echo '<div class="sectiontablet main_activity">';
        echo '<table>';
        show_tools_category($my_list);
        echo '</table>';
        echo '</div>';
    }
    //echo '<span class="sectiontablettitle">' . get_lang("Advanced") . '</span>';

        $my_list = get_tools_category(TOOL_ADVANCED);
        if (count($my_list) > 0) {
            echo '<div class="sectiontablet main_activity">';
            echo '<table>';
            show_tools_category($my_list);
            echo '</table>';
            echo '</div>';
        }

} else {
    if (api_get_setting('enable_pro_settings') != "true") {
        $my_list = get_tools_category(TOOL_BASIC);
        if (count($my_list) > 0) {
            echo '<div class="sectiontablet main_activity">';
            echo '<table>';
            show_tools_category($my_list);
            echo '</table>';
            echo '<div class=" main_activity">';
            show_pro_tools();
            echo '</div>';
        }
    } else {
        $my_list = get_tools_category(TOOL_BASIC_TOOL_ADVANCED);
        if (count($my_list) > 0) {
            echo '<div class="sectiontablet main_activity">';
            echo '<table>';
            show_tools_category($my_list);
            echo '</table>';
        }
    }
    
}
echo '<div id="chat_box_content" style="display:none;"></div>';

/**
 * validate not repeat any tool
 * @param array $my_list
 * @return array
 */
function validate_list_tool($my_list) {
    if (count($my_list) > 0) {
        $array_id_tool = array();
        $new_array_tool = array();
        foreach ($my_list as $puntero => $arrayTool) {
            if (!in_array($arrayTool['name'], $array_id_tool)) {
                array_push($new_array_tool, $arrayTool);
            }
            array_push($array_id_tool, $arrayTool['name']);
        }
    }
    return $new_array_tool;
}