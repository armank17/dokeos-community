<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		platformnavigation_get_information();
		break;
	case 'get_widget_content':
		platformnavigation_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		platformnavigation_get_information();
		break;
	case 'get_widget_content':
		platformnavigation_get_content();
		break;
	case 'get_widget_title':
		platformnavigation_get_title();
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
function platformnavigation_get_scope(){
	return array('platform');
}

function platformnavigation_get_content(){
	echo '<ul>';
	if (api_is_allowed_to_create_course() && ($_SESSION['studentview'] != 'studentenview')){	
		echo "<li><a href=\"main/create_course/add_course.php\">".get_lang('CourseCreate')."</a></li>";
	}
	echo "<li><a href=\"main/auth/courses.php\">".get_lang('CourseManagement')."</a></li>";
	echo '</ul>';
}

function platformnavigation_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('platformnavigation', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('PlatformNavigation');
	}
}

function platformnavigation_get_information(){
	echo '<span style="float:right;">';
	platformnavigation_get_screenshot();
	echo '</span>';	
	echo get_lang('PlatformNavigationExplanation');
}
function platformnavigation_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/course_home/widgets/clock/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}
function platformnavigation_settings_form(){
	
}
?>
