<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// we are not inside a course, so we reset the course id
$cidReset = true;

// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');
// Database Table Definitions
$tbl_session_rel_course_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_session                     = Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_session_rel_user            = Database::get_main_table(TABLE_MAIN_SESSION_USER);
$tbl_session_rel_course          = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_course                      = Database::get_main_table(TABLE_MAIN_COURSE);

switch ($_POST['action']){
	case 'savepluginorder':
		savepluginorder();
		break;
}

function savepluginorder(){
	// database table definition
	$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

	// first we delete the existing plugin order
	$sql = "DELETE FROM $table_setting WHERE variable='pluginorder'";
	$result = api_sql_query ( $sql );

	// now we save the pluginorder
	$sql = "INSERT INTO $table_setting (variable, selected_value, category) VALUES ('pluginorder','" . Database::escape_string ( implode(',',$_POST['plugin']) ) . "','system')";
	$result = api_sql_query ( $sql );

	Display::display_confirmation_message('PluginOrderChanged');
}

if(isset($_GET['ptype']) && $_GET['ptype'] != '' && isset($_GET['val']) && $_GET['val']!=''){
    $val = Security::remove_XSS($_REQUEST['val']);
    global $tbl_course, $tbl_session_rel_course, $id_session;
            $sql = 'SELECT course.code, course.visual_code, course.title
               FROM ' . $tbl_course . ' course
               WHERE course.visual_code LIKE "' . $val . '%"   ORDER BY course.code;';
                $rs = Database::query($sql, __FILE__, __LINE__);
                while($course = Database :: fetch_array($rs)) {
                    $course_list[] = $course['code'];
                    $course_title = str_replace("'", "\'", $course['title']);
                    $return .= api_utf8_encode('<a href="javascript: void(0);" onclick="javascript: add_course_to_session(\'' . $course['code'] . '\',\'' . $course_title . ' (' . $course['visual_code'] . ')' . '\')">' . $course['title'] . ' (' . $course['visual_code'] . ')</a><br />');
                }
                
                //$xajax_response->addAssign('ajax_list_courses_single', 'innerHTML', api_utf8_encode($return));
            //    $xajax_response->addAssign('ajax_list_courses_single', 'innerHTML', 'Ed');
            
    echo $return;
                //"<a href="javascript: void(0);" onclick="javascript: add_course_to_session(\'' . $course['code'] . '\',\'' . $course_title . ' (' . $course['visual_code'] . ')' . '\')">' . $course['title'] . ' (' . $course['visual_code'] . ')</a><br />";
}
?>
