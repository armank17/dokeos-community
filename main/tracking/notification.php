<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file[] = 'admin';
$language_file[] = 'tracking';
$language_file[] = 'scorm';

// setting the help
$help_content = 'courselog';

// variable initialisation (@todo: sanitize this)
$pathopen = isset($_REQUEST['pathopen']) ? $_REQUEST['pathopen'] : null;

// resetting the course id
$cidReset = true;

// including the global Dokeos file
require '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpathItem.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpathList.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/scorm.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/scormItem.class.php';
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require api_get_path(LIBRARY_PATH).'statsUtils.lib.inc.php';
require api_get_path(SYS_CODE_PATH).'resourcelinker/resourcelinker.inc.php';

// the section (for the tabs)
$from_myspace = false;
if (isset($_GET['from']) && $_GET['from'] == 'myspace') {
	$from_myspace = true;
	$this_section = "session_my_space";
} else {
	$this_section = SECTION_COURSES;
}

// access restrictions
$is_allowedToTrack = api_is_course_admin() || api_is_platform_admin() || api_is_course_coach() || $is_sessionAdmin;
if (!$is_allowedToTrack) {
	Display :: display_tool_header(null);
	api_not_allowed();
	Display :: display_footer();
	exit;
}


// starting the output buffering when we are exporting the information
$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
if ($export_csv) {
	ob_start();
}
$csv_content = array();

// charset determination
if (!empty($_GET['scormcontopen'])) {
    $tbl_lp = Database::get_course_table(TABLE_LP_MAIN);
	$contopen = (int) $_GET['scormcontopen'];
	$sql = "SELECT default_encoding FROM $tbl_lp WHERE id = ".$contopen;
	$res = Database::query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($res);
	$lp_charset = $row['default_encoding'];
}

// Additional style definitions
$htmlHeadXtra[] = "<style type='text/css'>
/*<![CDATA[*/
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
/*]]>*/
</style>";

// Database table definitions
$TABLETRACK_ACCESS      = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
$TABLETRACK_LINKS       = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LINKS);
$TABLETRACK_DOWNLOADS   = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_DOWNLOADS);
$TABLETRACK_ACCESS_2    = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ACCESS);
$TABLETRACK_EXERCISES 	= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TABLECOURSUSER	        = Database::get_main_table(TABLE_MAIN_COURSE_USER);
$TABLECOURSE	        = Database::get_main_table(TABLE_MAIN_COURSE);
$TABLECOURSE_LINKS      = Database::get_course_table(TABLE_LINK);
$table_user = Database::get_main_table(TABLE_MAIN_USER);
$TABLEQUIZ = Database :: get_course_table(TABLE_QUIZ_TEST);

$tbl_learnpath_main = Database::get_course_table(TABLE_LP_MAIN);
$tbl_learnpath_item = Database::get_course_table(TABLE_LP_ITEM);
$tbl_learnpath_view = Database::get_course_table(TABLE_LP_VIEW);
$tbl_learnpath_item_view = Database::get_course_table(TABLE_LP_ITEM_VIEW);

// breadcrumbs
if (isset($_GET['origin']) && $_GET['origin'] == 'resume_session') {
    $interbreadcrumb[] = array('url' => '../admin/index.php','name' => get_lang('PlatformAdmin'));
    $interbreadcrumb[] = array('url' => '../admin/session_list.php','name' => get_lang('SessionList'));
    $interbreadcrumb[] = array('url' => '../admin/resume_session.php?id_session='.$_SESSION['id_session'], 'name' => get_lang('SessionOverview'));
}

$view = (isset($_REQUEST['view']) ? $_REQUEST['view'] : '');

$nameTools = get_lang('Tracking');

// display the header
Display::display_tool_header($nameTools, 'Tracking');

// getting all the students of the course
$a_students = CourseManager :: get_student_list_from_course_code($_course['id'], true, (empty($_SESSION['id_session']) ? null : $_SESSION['id_session']));
$nbStudents = count($a_students);

/*
==============================================================================
		MAIN CODE
==============================================================================
*/

echo '<div class="actions">';
echo '<a href="learners.php?'.api_get_cidreq().'&amp;studentlist=true">'.Display::return_icon('pixel.gif', get_lang('Students'), array('class' => 'toolactionplaceholdericon toolactionadminusers')).get_lang('Students').'</a>';
echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;export=csv&amp;'.$addional_param.'">'.Display::return_icon('pixel.gif', get_lang('ExportAsXLS'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportAsXLS').'</a>';
echo '<a href="javascript:void(0);" onclick="javascript: window.print();">'.Display::return_icon('pixel.gif', get_lang('Print'), array('class' => 'toolactionplaceholdericon toolactionprint32')).get_lang('Print').'</a>';
echo '</div>';

// start the content div
echo '<div id="content">';
// else display student list with all the informations
// BEGIN : form to remind inactives susers
	//$form = new FormValidator('reminder_form', 'get', api_get_path(REL_CODE_PATH).'announcements/announcements.php?'.api_get_cidreq());
        $form = new FormValidator('reminder_form', 'get',api_get_path(WEB_VIEW_PATH).'announcement/index.php?'.api_get_cidreq());

	$renderer = $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{label} {element}</span>&nbsp;<button class="save" type="submit">'.get_lang('SendNotification').'</button>','since');

	$options = array (
		2 => '2 '.get_lang('Days'),
		3 => '3 '.get_lang('Days'),
		4 => '4 '.get_lang('Days'),
		5 => '5 '.get_lang('Days'),
		6 => '6 '.get_lang('Days'),
		7 => '7 '.get_lang('Days'),
		15 => '15 '.get_lang('Days'),
		30 => '30 '.get_lang('Days'),
		'never' => get_lang('Never')

	);

	$el = $form -> addElement('select', 'since', '<img width="22" align="middle" src="'.api_get_path(WEB_IMG_PATH).'messagebox_warning.gif" border="0" />'.get_lang('RemindInactivesLearnersSince'), $options);
	$el -> setSelected(7);
	$form -> addElement('hidden', 'cidReq', api_get_course_id());
	$form -> addElement('hidden', 'action', 'add');
	$form -> addElement('hidden', 'remindallinactives', 'true');

	$form -> display();
  echo '<br/>';
	// END : form to remind inactives susers

	if ($export_csv) {
		$is_western_name_order = api_is_western_name_order(PERSON_NAME_DATA_EXPORT);
	} else {
		$is_western_name_order = api_is_western_name_order();
	}
	$sort_by_first_name = api_sort_by_first_name();

	$tracking_column = isset($_GET['tracking_column']) ? $_GET['tracking_column'] : 0;
	$tracking_direction = isset($_GET['tracking_direction']) ? $_GET['tracking_direction'] : 'DESC';

	if (count($a_students) > 0) {

	    if ($export_csv) {
			$csv_content[] = array ();
		}

	    $all_datas = array();
	    $course_code = $_course['id'];

		$user_ids = array_keys($a_students);
		$table = new SortableTable('users', 'get_number_of_users', 'get_user_data', (api_is_western_name_order() xor api_sort_by_first_name()) ? 3 : 2);

		$parameters['cidReq'] 		= Security::remove_XSS($_GET['cidReq']);
		$parameters['studentlist'] 	= Security::remove_XSS($_GET['studentlist']);
		$parameters['from'] 	= Security::remove_XSS($_GET['myspace']);

		$table->set_additional_parameters($parameters);

		$table -> set_header(0, get_lang('OfficialCode'), false, 'align="center"');
		if ($is_western_name_order) {
			$table -> set_header(1, get_lang('FirstName'), false, 'align="center"');
			$table -> set_header(2, get_lang('LastName'), true, 'align="center"');
		} else {
    		$table -> set_header(1, get_lang('LastName'), true, 'align="center"');
			$table -> set_header(2, get_lang('FirstName'), false, 'align="center"');
		}
		$table -> set_header(3, get_lang('TrainingTime'),false);
		$table -> set_header(4, get_lang('CourseProgress'),false);
		$table -> set_header(5, get_lang('Score'),false);
		$table -> set_header(6, get_lang('Student_publication'),false);
		$table -> set_header(7, get_lang('Messages'),false);
		$table -> set_header(8, get_lang('FirstLogin'), false, 'align="center"');
		$table -> set_header(9, get_lang('LatestLogin'), false, 'align="center"');
		$table -> set_header(10, get_lang('Details'),false);
		//$html_table = $table->get_table_html();
       $table->display();


	} else {
		echo get_lang('NoUsersInCourseTracking');
	}

	// send the csv file if asked
	if ($export_csv) {
		if ($is_western_name_order) {
			$csv_headers = array (
				get_lang('OfficialCode', ''),
				get_lang('FirstName', ''),
				get_lang('LastName', ''),
				get_lang('TrainingTime', ''),
				get_lang('CourseProgress', ''),
				get_lang('Score', ''),
				get_lang('Student_publication', ''),
				get_lang('Messages', ''),
				get_lang('FirstLogin', ''),
				get_lang('LatestLogin', '')
			);
		} else {
			$csv_headers = array (
				get_lang('OfficialCode', ''),
				get_lang('LastName', ''),
				get_lang('FirstName', ''),
				get_lang('TrainingTime', ''),
				get_lang('CourseProgress', ''),
				get_lang('Score', ''),
				get_lang('Student_publication', ''),
				get_lang('Messages', ''),
				get_lang('FirstLogin', ''),
				get_lang('LatestLogin', '')
			);
		}

		if (isset($_GET['additional_profile_field']) AND is_numeric($_GET['additional_profile_field'])) {
			$csv_headers[]=get_lang('AdditionalProfileField');
		}
		ob_end_clean();
		array_unshift($csv_content, $csv_headers); // adding headers before the content
		Export :: export_table_csv($csv_content, 'reporting_student_list');
	}

// close the content div
echo '</div>';
echo '<div class="actions">';
$return = '<a href="courseLog.php?'.api_get_cidreq().'&amp;studentlist=resources">'.Display::return_icon('pixel.gif', get_lang('Traffic'), array('class' => 'actionplaceholdericon actionquota')).get_lang('Traffic').'</a>';
$return .= '<a href="../exercice/exercice.php?'.api_get_cidreq().'&reporting=true&page=notification&amp;show=result">'.Display::return_icon('pixel.gif', get_lang('Quiz'), array('class' => 'actionplaceholdericon actionstudentviewquiz')).get_lang('Quiz').'</a>';
$return .= '<a href="notification.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Notification'), array('class' => 'actionplaceholdericon actionannouncement')).get_lang('Notification').'</a>';
$return .= '<a href="profiling.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Profiling'), array('class' => 'actionplaceholdericon actioncoach')).get_lang('Profiling').'</a>';
echo $return;
echo '</div>';
// display the footer
Display::display_footer();



/**
 * Display all the additionally defined user profile fields
 * This function will only display the fields, not the values of the field because it does not act as a filter
 * but it adds an additional column instead.
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @since October 2009
 * @version 1.8.7
 */
function display_additional_profile_fields() {
	return Tracking::display_additional_profile_fields();
}

/**
 * This function gets all the information of a certrain ($field_id) additional profile field.
 * It gets the information of all the users so that it can be displayed in the sortable table or in the csv or xls export
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @since October 2009
 * @version 1.8.7
 */
function get_addtional_profile_information_of_field($field_id){
	return Tracking::get_addtional_profile_information_of_field($field_id);
}

/**
 * This function gets all the information of a certrain ($field_id) additional profile field for a specific list of users is more efficent than  get_addtional_profile_information_of_field() function
 * It gets the information of all the users so that it can be displayed in the sortable table or in the csv or xls export
 *
 * @author	Julio Montoya <gugli100@gmail.com>
 * @param	int field id
 * @param	array list of user ids
 * @return	array
 * @since	Nov 2009
 * @version	1.8.6.2
 */
function get_addtional_profile_information_of_field_by_user($field_id, $users) {
 return Tracking::get_addtional_profile_information_of_field_by_user($field_id, $users);
}

/**
 * count the number of students in this course (used for SortableTable)
 */
function count_student_in_course() {
	global $nbStudents;
	return $nbStudents;
}

function sort_users($a, $b) {
	return strcmp(trim(api_strtolower($a[$_SESSION['tracking_column']])), trim(api_strtolower($b[$_SESSION['tracking_column']])));
}

function sort_users_desc($a, $b) {
	return strcmp( trim(api_strtolower($b[$_SESSION['tracking_column']])), trim(api_strtolower($a[$_SESSION['tracking_column']])));
}

/**
 * Get number of users for sortable with pagination
 * @return int
 */
function get_number_of_users() {
		global $user_ids;
		return count($user_ids);
}
/**
 * Get data for users list in sortable with pagination
 * @return array
 */
function get_user_data($from, $number_of_items, $column, $direction) {
return Tracking::get_user_data($from, $number_of_items, $column, $direction, false);
}