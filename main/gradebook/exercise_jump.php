<?php

/* For licensing terms, see /dokeos_license.txt */

require_once ('../inc/global.inc.php');
api_block_anonymous_users();
$this_section=SECTION_COURSES;

require_once (api_get_path(LIBRARY_PATH).'course.lib.php');

$course_code = api_get_course_id();
$course_info = Database::get_course_info($course_code);
$course_title = $course_info['title'];
$course_code = $return_result['code'];
$gradebook=Security::remove_XSS($_GET['gradebook']);

$dbname = $course_info['db_name'];

$_course['name'] = $course_title;
$_course['official_code'] = $course_code;

if (isset($_GET['doexercise'])) {
	header('Location: ../exercice/exercice_submit.php?cidReq='.$cidReq.'&amp;gradebook='.$gradebook.'&amp;origin=&amp;learnpath_id=&amp;learnpath_item_id=&amp;exerciseid='.Security::remove_XSS($_GET['doexercise']));
	exit;
} else {
	if (isset($_GET['gradebook'])) {
		$add_url = '&amp;gradebook=view&amp;exerciseid='.Security::remove_XSS((int)$_GET['exerciseId']);
	}
	header('Location: ../exercice/exercice.php?cidReq='.Security::remove_XSS($cidReq).'&amp;show=result'.$add_url);
	exit;
}
