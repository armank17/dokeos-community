<?php 
/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*                  HOME PAGE FOR EACH TRAINING
*
*	This page, included in every course's index.php is the home
*	page. To make administration simple, the teacher edits his
*	course from the home page. Only the login detects that the
*	visitor is allowed to activate, deactivate home page links,
*	access to the teachers tools (statistics, edit forums...).
*
*	@package dokeos.course_home
==============================================================================
*/
// include additional libraries
require api_get_path(LIBRARY_PATH).'widgets.lib.php';

// add javascript code
Display::javascript(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js');
Display::javascript(api_get_path(WEB_LIBRARY_PATH).'javascript/widget.js.php');

// header
Display::display_header($course_title, "Home");

// statistics
if (!isset($coursesAlreadyVisited[$_cid])) {
	event_access_course();
	$coursesAlreadyVisited[$_cid] = 1;
	api_session_register('coursesAlreadyVisited');
}

// variables (shoudl be cleaned up)
$temps = time();
$reqdate = "&reqdate=$temps";

//display course title for course home page (similar to toolname for tool pages)
//echo '<h3>'.api_display_tool_title($nameTools) . '</h3>';
?>
<link type="text/css" href="<?php echo api_get_path(WEB_CODE_PATH);?>css/<?php echo api_get_setting('stylesheets'); ?>/widgets.css" rel="stylesheet" />
<div id="content">
<div id="dialog" title=""><div align="center"><br /><?php Display::display_icon('ajax-loader.gif','',array('style'=>'text-align: left;')); ?></div></div>
<?php 
// load all the widget settings from the database
api_load_widget_settings();

$custom_layout_file = api_get_path(SYS_PATH).'main/layout/'.api_get_setting('widget_homepage').'.php';
if (file_exists($custom_layout_file) and in_array(api_get_setting('widget_homepage'),array('widgethomepage1','widgethomepage2','widgethomepage3','widgethomepage4','widgethomepage5','widgethomepage6')))
{
	include($custom_layout_file);
} else {
	include(api_get_path(SYS_PATH).'main/layout/widgethomepage2.php');
}
?>
</div>
