<?php
// name of the language file that needs to be included
//$language_file = array ('tracking');

// including the global Dokeos file
require '../../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/jquery.jqplot.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.barRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.pointLabels.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.cursor.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/jquery.jqplot.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/overcast/jquery-ui-1.8.1.custom.css" />';



api_block_anonymous_users();

if (!api_is_allowed_to_edit() && !api_is_coach() && $_user['status'] != DRH && $_user['status'] != SESSIONADMIN && !api_is_platform_admin(true)) {
	api_not_allowed(true);
}

// infos about courses
$course_code_info = Security :: remove_XSS($_GET['course']);
$info_course = CourseManager :: get_course_information($course_code_info);

// tables
$tbl_lp = Database::get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
$tbl_lpiv = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $info_course['db_name']);
$tbl_lp_v = Database::get_course_table(TABLE_LP_VIEW, $info_course['db_name']);
// infos about user
$info_user = UserManager :: get_user_info_by_id($student_id);
if($_user['status']==DRH && $a_infosUser['hr_dept_id']!=$_user['user_id'] && !api_is_platform_admin()) {
	api_not_allowed();
}


// clean vars
$learnpath_id = intval($_GET['lp_id']);
$user_id = intval($_GET['user_id']);

// get the infos about the learnpath
$sql = 'SELECT name FROM '.$tbl_lp.' WHERE lp_id='.$learnpath_id;

// get the user list of the course
$users = CourseManager::get_student_list_from_course_code($info_course['code'], true, intval(@$_SESSION['id_session']));

// get the infos on the learnpath for every user of the course
$chart_data = array();
foreach($users as $user){
	
	$user = UserManager::get_user_info_by_id($user['user_id']);

	// progress
	$progress = intval(learnpath :: get_db_progress($learnpath_id, $user['user_id'], '%', $_course['db_name']));
	
	// score
	$score = intval(Tracking :: get_avg_student_score($user['user_id'],$_course['id'], array ($learnpath_id)));
	
	// time
	$sql = 'SELECT SUM(total_time)
			FROM ' . $tbl_lpiv . ' AS item_view
			INNER JOIN ' . $tbl_lp_v . ' AS view
				ON item_view.lp_view_id = view.id
				AND view.lp_id = ' . $learnpath_id . '
				AND view.user_id = ' . intval($user['user_id']);        
	$rs = Database::query($sql, __FILE__, __LINE__);
	$time = 0;
	if (Database :: num_rows($rs) > 0) {
		$total_time = Database :: result($rs, 0, 0);
	}
	$chart_data[] = array('user_id'=>$user['user_id'], 'at_first'=>$user['user_id'] == $user_id,'firstname'=>$user['firstname'], 'lastname'=>$user['lastname'], 'progress'=>$progress, 'score'=>$score,'time'=>$total_time);
	
}

usort($chart_data, 'alphabeticalOrder');

/*
 * Sort the users by alphabetical order but if at_first is defined
 * Results are reversed because charts take it with the reversed order
 */
function alphabeticalOrder($user1, $user2){
	
	if($user1['at_first']){
		return 1;
	}
	else if($user2['at_first']){
		return -1;
	}
	else 
		return !strcmp($user1['lastname'].$user1['firstname'], $user2['lastname'].$user2['firstname']);
	
}

include('learnpath.js.php');
?>