<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		mycourses_get_information();
		break;
	case 'get_widget_content':
		mycourses_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		mycourses_get_information();
		break;
	case 'get_widget_content':
		mycourses_get_content();
		break;
	case 'get_widget_title':
		mycourses_get_title();
		break;				
}

/**
 * This function determines if the widget can be used inside a course, outside a course or both
 * 
 * @return array 
 * @version Dokeos 1.9
 * @since January 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function mycourses_get_scope(){
	return array('platform');
}

function mycourses_get_content(){
	global $_user; 
	
	// include additional libraries
	require_once api_get_path(LIBRARY_PATH).'groupmanager.lib.php';
	require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';	
	
	// this is the main function to get the course list
	$personal_course_list = UserManager::get_personal_session_course_list($_user['user_id']);	
	
	if(count($personal_course_list) > 0) {
		echo '<ul class="courseslist" style="list-style-type:none;">';
	}
	
	foreach ($personal_course_list as $key=>$personal_course_info){
		$s_htlm_status_icon = "";
	
		if ($personal_course_info['s'] == 1) {
			$status_icon=Display::return_icon('teachers.gif', get_lang('Teacher'));
		}
		if ($personal_course_info['s'] == 2 || ($is_coach && $course['s'] != 1)) {
			$status_icon=Display::return_icon('coachs.gif', get_lang('GeneralCoach'));
		}
		if ($personal_course_info['s'] == 5 && !$is_coach) {
			$status_icon=Display::return_icon('students.gif', get_lang('Student'));
		}
	
		//display course entry
		$result.="\n\t";
		$result .= '<li class="'.$class.'"><div class="coursestatusicons">'.$status_icon.'</div>';
		//show a hyperlink to the course, unless the course is closed and user is not course admin
		if ($personal_course_info['visibility'] != COURSE_VISIBILITY_CLOSED || $personal_course_info['status'] == COURSEMANAGER) {
			$result .= '<a href="'.api_get_path(WEB_COURSE_PATH).$personal_course_info['directory'].'/">'.$personal_course_info['title'].'</a>';
		} else {
			$result .= $personal_course_info['title']." "." ".get_lang('CourseClosed')."";
		}
		// show the course_code and teacher if chosen to display this
		if (api_get_setting('display_coursecode_in_courselist') == 'true' || api_get_setting('display_teacher_in_courselist') == 'true') {
			$result .= '<br />';
		}
		if (api_get_setting('display_coursecode_in_courselist') == 'true') {
			$result .= $personal_course_info['code'];
		}
		if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
			$result .= ' &ndash; ';
		}
		if (api_get_setting('display_teacher_in_courselist') == 'true') {
			$result .= $personal_course_info['t'];
		}

		// display the what's new icons
		$result .= show_notification($personal_course_info);

	}
	echo $result;
	if(count($personal_course_list) > 0) {
		echo '</ul>';
	}	
}


/**
 * Returns the "what's new" icon notifications
 * @param	array	Course information array, containing at least elements 'db' and 'k'
 * @return	string	The HTML link to be shown next to the course
 * @version
 */
function show_notification($my_course) {
    $user_id = api_get_user_id();
    $course_database = $my_course['db'];
    $course_tool_table = Database::get_course_table(TABLE_TOOL_LIST, $course_database);
    $tool_edit_table = Database::get_course_table(TABLE_ITEM_PROPERTY, $course_database);
    $t_track_e_access = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
    // get the user's last access dates to all tools of this course
    $sqlLastTrackInCourse = "SELECT access_tool,access_date FROM $t_track_e_access USE INDEX (access_cours_code, access_user_id)
									 WHERE access_cours_code = '" . $my_course['k'] . "'
									 AND access_user_id = '$user_id'";
    $resLastTrackInCourse = Database::query($sqlLastTrackInCourse, __FILE__, __LINE__);
    $oldestTrackDate = "3000-01-01 00:00:00";
    while ($lastTrackInCourse = Database::fetch_array($resLastTrackInCourse)) {
        $lastTrackInCourseDate[$lastTrackInCourse['access_tool']] = $lastTrackInCourse['access_date'];
        if ($oldestTrackDate > $lastTrackInCourse['access_date']) {
            $oldestTrackDate = $lastTrackInCourse['access_date'];
        }
    }
    // get the last edits of all tools of this course
    $sql = "SELECT tet.lastedit_date,tet.to_user_id,tet.visibility, tet.lastedit_date last_date, tet.tool tool, tet.ref ref,
						tet.lastedit_type type, tet.to_group_id group_id,
						ctt.image image, ctt.link link
					FROM $tool_edit_table tet, $course_tool_table ctt
					WHERE tet.lastedit_date > '$oldestTrackDate'
					AND ctt.name = tet.tool
					AND ctt.visibility = '1'
					AND tet.lastedit_user_id != $user_id
					ORDER BY tet.lastedit_date";

    $res = Database::query($sql);
    //get the group_id's with user membership
    $group_ids = GroupManager :: get_group_ids($course_database, $user_id);
    $group_ids[] = 0; //add group 'everyone'
    //filter all selected items
    while ($res && ($item_property = Database::fetch_array($res, 'ASSOC'))) {
        if ((!isset($lastTrackInCourseDate[$item_property['tool']])
                || $lastTrackInCourseDate[$item_property['tool']] < $item_property['lastedit_date'])
                && (in_array($item_property['to_group_id'], $group_ids)
                || $item_property['to_user_id'] == $user_id)
                && ($item_property['visibility'] == '1'
                || ($my_course['s'] == '1' && $item_property['visibility'] == '0')
                || !isset($item_property['visibility']))) {

            if ($item_property['tool'] == 'calendar_event') {
                continue;
            }
            $notifications[$item_property['tool']] = $item_property;
        }
    }
    //show all tool icons where there is something new
    $retvalue = '&nbsp;';
    if (isset($notifications)) {
        while (list ($key, $notification) = each($notifications)) {
            $lastDate = date('d/m/Y H:i', convert_mysql_date($notification['lastedit_date']));
            $type = $notification['lastedit_type'];

            if (empty($my_course['id_session'])) {
                $my_course['id_session'] = 0;
            }
            //check if there is a question mark
            $link = strrpos($notification['link'], "?") !== FALSE ? $notification['link'] . '&amp;cidReq=' . $my_course['k'] : $notification['link'] . '?cidReq=' . $my_course['k'];
            $actionicon = 'actionplaceholdericon actionmini_' . $notification['tool'];
            $retvalue .= '<a href="' . api_get_path(WEB_CODE_PATH) . $link . '&amp;ref=' . $notification['ref'] . '&amp;gidReq=' . $notification['to_group_id'] . '&amp;id_session=' . $my_course['id_session'] . '">' . Display::return_icon('pixel.gif', get_lang(ucfirst($notification['tool'])) . ' -- ' . get_lang('_title_notification') . ': ' . get_lang($type) . ' (' . $lastDate . ')', array('class' => $actionicon)) . '</a>&nbsp;';
        }
    }
    return $retvalue;
}


function mycourses_get_title($script='') {
	// if the script parameter is empty then we are inside the course and every widget can have only one title
	if (empty($script)){
		$config_title = api_get_setting('mycourses', 'title');
	} else {
		// if the $script parameter is not empty then we can have a different title for the same widget depending on the script
		// the script is store in subcategory so we need to retrieve this one from the database instead
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$sql = "SELECT * FROM $table_setting WHERE variable='mycourses' AND subkey='title' AND subcategory='".Database::escape_string($script)."'";
		$res = Database::query ( $sql, __FILE__, __LINE__ );
		$row = Database::fetch_array ( $res );
		$config_title = $row['selected_value'];
	}

	if (!empty($config_title)){
		return $config_title;
	} else {
		return get_lang('MyCourses');
	}
}

function mycourses_get_information(){
	echo '<span style="float:right;">';
	mycourses_get_screenshot();
	echo '</span>';	
	echo get_lang('MyCourseInformation');
}
function mycourses_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/course_home/widgets/clock/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}
function mycourses_settings_form(){
	
}?>
