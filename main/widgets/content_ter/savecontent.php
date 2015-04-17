<?php
// include the global Dokeos file
//include_once('../../../inc/global.inc.php');

// include the widget functions
include_once('widgetfunctions.php');

// access restriction
if (!empty($_course) AND is_array($_course)){
	api_protect_course_script(true);

	// saving the content is done automatically based on $_POST['action'] = savecontent
	header('location: '.api_get_path(WEB_COURSE_PATH).$_course['path']); 
} else {
	api_protect_admin_script(true);
	header('location: '.api_get_path(WEB_PATH).'user_portal.php'); 
}
exit;
?>