<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.main
*/

// include global Dokeos file
require_once './main/inc/global.inc.php';

// Additional libraries
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

// additional stylesheets 
$htmlHeadXtra[] = '<link type="text/css" href="'.api_get_path(WEB_CODE_PATH).'css/'.api_get_setting('stylesheets').'/widgets.css" rel="stylesheet" />';

// additional javascript files
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_PATH).'main/inc/lib/javascript/widget.js.php"></script>';

// this is the main function to get the course list
$personal_course_list = UserManager::get_personal_session_course_list($_user['user_id']);

// check if a user is enrolled only in one course for going directly to the course after the login
if (api_get_setting('go_to_course_after_login') == 'true') {
	if (!isset($_SESSION['coursesAlreadyVisited']) && is_array($personal_course_list) && count($personal_course_list) == 1) {

		$key = array_keys($personal_course_list);
		$course_info = $personal_course_list[$key[0]];

		$course_directory = $course_info['d'];
		$id_session = isset($course_info['id_session'])?$course_info['id_session']:0;
		header('location:'.api_get_path(WEB_COURSE_PATH).$course_directory.'/?id_session='.$id_session);
		exit;
	}
}

// Display header
Display::display_header();

// start content div
echo '<div id="content">';

// include additional libraries
require_once './main/inc/lib/widgets.lib.php';

// loading image
echo '<div id="dialog" title=""><div align="center"><br />'.Display::return_icon('ajax-loader.gif','',array('style'=>'text-align: left;')).'</div></div>';

// load all the widget settings from the database
api_load_widget_settings('user_portal');
		
$homepagelayout = api_get_setting('widget_homepage');
if (empty($homepagelayout)){
	$custom_layout_file = api_get_path(SYS_PATH).'main/layout/widgethomepage2.php';
} else {
	$custom_layout_file = api_get_path(SYS_PATH).'main/layout/'.api_get_setting('widget_homepage').'.php';
}

if (file_exists($custom_layout_file) and in_array(api_get_setting('widget_homepage'),array('widgethomepage1','widgethomepage2','widgethomepage3','widgethomepage4','widgethomepage5','widgethomepage6')))
{
	include($custom_layout_file);
}

// end content div
echo '</div>';

// Display footer
Display::display_footer();
?>
