<?php
$language_file = array ('registration', 'index', 'tracking', 'exercice', 'scorm', 'learnpath');
require ('../inc/global.inc.php');
require 'functions.php';
include_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
include_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
include_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';
include_once api_get_path(SYS_CODE_PATH).'newscorm/learnpathItem.class.php';

$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
$export_print = isset($_GET['export']) && $_GET['export'] == 'print' ? true : false;
if ($export_csv) {
	ob_start();
}

$csv_content = array();

$lp_id = $_GET['lp_id'];
$code = $_GET['course_code'];
$user_id = $_GET['user_id'];
$session_id = $_GET['sessionId'];

if(isset($_GET['course_code'])) {
	$cidReq = Security::remove_XSS($_GET['course_code']);
}
if(empty($cidReq)){
	$cidReq = Security::remove_XSS($_GET['course']);
}

$user_infos = UserManager :: get_user_info_by_id($user_id);
$name = api_get_person_name($user_infos['firstname'], $user_infos['lastname']);

/*if (!api_is_platform_admin(true) && !CourseManager :: is_course_teacher($user_id, $cidReq) && !Tracking :: is_allowed_to_coach_student($user_id,$user_id) && $user_infos['hr_dept_id']!==$user_id) {	
	api_not_allowed();
}*/

$course_exits = CourseManager::course_exists($cidReq);

if (!empty($course_exits)) {
	$course = CourseManager :: get_course_information($cidReq);
} 

$sql = 'SELECT name
	FROM '.Database::get_course_table(TABLE_LP_MAIN, $course['db_name']).'
	WHERE id='.Database::escape_string($lp_id);
$rs = Database::query($sql, __FILE__, __LINE__);
$lp_title = Database::result($rs, 0, 0);

echo '<div id="loaderDiv" style="display:none;"><img src="../img/ajaxloader.gif" /></div>';
echo '<div id="dataDiv">';
if (!$export_csv && !$export_print) {

if(isset($_GET['page']) && $_GET['page'] == 'module'){
	echo '<p class="pull-right"><a id="moduleindividual_back" href="module_learners.php?lp_id='.$lp_id.'&course_code='.$code.'&user_id='.$user_id.'&sessionId='.$session_id.'">'.api_convert_encoding(get_lang('BackToModuleList'),'UTF-8',api_get_system_encoding()).'</a></p>';
}
else if(isset($_GET['page']) && $_GET['page'] == 'users'){
	echo '<p class="pull-right"><a href="learners_reporting.php">'.get_lang('Back').'</a></p>';
}
else {
	echo '<p class="pull-right"><a id="quizindividual_back" href="individual_reporting.php?lp_id='.$lp_id.'&course_code='.$code.'&user_id='.$user_id.'&sessionId='.$session_id.'">'.api_convert_encoding(get_lang('BackToList'),'UTF-8',api_get_system_encoding()).'</a></p>';
}
echo '<div><strong>'.api_convert_encoding($course['title'],'UTF-8',api_get_system_encoding()).' - '.api_convert_encoding($lp_title,'UTF-8',api_get_system_encoding()).' - '.api_convert_encoding($name,'UTF-8',api_get_system_encoding()).'</strong></div>';
}

$list = learnpath :: get_report_flat_ordered_items_list($lp_id,$course['db_name']);
$origin = 'tracking';

ob_start();
include_once  api_get_path(SYS_CODE_PATH).'newscorm/lp_stats_report.php';
$tracking_content = ob_get_contents();
ob_end_clean();
//echo api_utf8_decode($tracking_content, $charset);
echo $tracking_content;

if(empty($export_print)){
?>
<!--</br><span class="pull-right"><a href="<?php echo api_get_self().'?export=csv&lp_id='.$lp_id.'&course_code='.$code.'&user_id='.$user_id.'&sessionId='.$session_id; ?>" id="export_screen_list"><?php echo get_lang('Export'); ?></a> / <a href="<?php echo api_get_self().'?export=print&lp_id='.$lp_id.'&course_code='.$code.'&user_id='.$user_id.'&sessionId='.$session_id; ?>" id="print_screen_list"><?php echo get_lang('Print'); ?> </a></span></br>-->
</br><span class="pull-right"><a href="<?php echo api_get_self().'?export=csv&lp_id='.$lp_id.'&cidReq='.$code.'&course='.$code.'&course_code='.$code.'&student_id='.$user_id.'&user_id='.$user_id.'&sessionId='.$session_id; ?>" id="export_screen_list"><?php echo get_lang('Export'); ?></a> / <a href="<?php echo api_get_self().'?export=print&lp_id='.$lp_id.'&cidReq='.$code.'&course='.$code.'&course_code='.$code.'&student_id='.$user_id.'&user_id='.$user_id.'&sessionId='.$session_id; ?>" id="print_screen_list"><?php echo get_lang('Print'); ?> </a></span></br>
<?php
}
echo '</div>';
?>