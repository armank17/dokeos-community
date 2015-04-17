<?php 
// including the widgets language file
$language_file = array ('widgets', 'course_home');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		configuration_get_information();
		break;
	case 'get_widget_content':
		configuration_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		configuration_get_information();
		break;
	case 'get_widget_content':
		configuration_get_content();
		break;
	case 'get_widget_title':
		configuration_get_title();
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
function configuration_get_scope(){
	return array('course', 'platform');
}



function configuration_get_content(){
	global $_course;

	echo '<ul id="configuration" style="list-style: none;margin: 0;padding: 0;">';
	echo '<li><a href="'.api_get_path(WEB_PATH).'main/inc/lib/widgets.lib.php?action=addwidgets" title="'.get_lang('ManageWidgets').'" class="dialoglink">'.Display::return_icon('settings.gif','',array('align'=>'middle')).'  '.get_lang('ManageWidgets').'</a></li>';
	echo '<li><a href="'.api_get_path(WEB_PATH).'main/inc/lib/widgets.lib.php?action=addrsswidget" title="'.get_lang('AddRSSAsWidget').'" class="dialoglink">'.Display::return_icon('links.gif','',array('align'=>'middle')).'  '.get_lang('AddRSSAsWidget').'</a></li>';
	if (!empty($_course) AND is_array($_course)){
		echo '<li><a href="'.api_get_path(WEB_CODE_PATH).'blog/blog_admin.php">'.Display::return_icon('blog_admin.gif','',array('align'=>'middle')).' '.get_lang('Blog_management').'</a></li>';
		echo '<li><a href="'.api_get_path(WEB_CODE_PATH).'tracking/courseLog.php">'.Display::return_icon('statistics.png','',array('align'=>'middle')).' '.get_lang('Tracking').'</a></li>';
		echo '<li><a href="'.api_get_path(WEB_CODE_PATH).'course_info/infocours.php">'.Display::return_icon('reference.gif','',array('align'=>'middle')).' '.get_lang('Course_setting').'</a></li>';
		echo '<li><a href="'.api_get_path(WEB_CODE_PATH).'course_info/maintenance.ph">'.Display::return_icon('backup.gif','',array('align'=>'middle')).' '.get_lang('Course_maintenance').'</a></li>';
	}
	echo '</ul>';
}

function configuration_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('configuration', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('Configuration');
	}
}



function configuration_get_information(){
	echo '<span style="float:right;">';
	configuration_get_screenshot();
	echo '</span>';	
	echo 'The configuration widget allows you to enable or disable other widgets and also decide where the widgets should appear in the layout of your choice. 
		This widget is not available to students.';
}
function configuration_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/course_home/widgets/configuration/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}
function configuration_settings_form(){
	
}
?>
