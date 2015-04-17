<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		clock_get_information();
		break;
	case 'get_widget_content':
		clock_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		clock_get_information();
		break;
	case 'get_widget_content':
		clock_get_content();
		break;
	case 'get_widget_title':
		clock_get_title();
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
function clock_get_scope(){
	return array('course', 'platform');
}

function clock_get_content(){
	echo '<script type="text/javascript" src="'.api_get_path(WEB_PATH).'main/widgets/clock/jquery.jclock.js"></script>
		  <script type="text/javascript">
			$(function($) {
			  $("#widget_clock .portlet-content").jclock();
			});
			</script>';	
}

function clock_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('clock', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('Clock');
	}
}

function clock_get_information(){
	echo '<span style="float:right;">';
	clock_get_screenshot();
	echo '</span>';	
	echo get_lang('ClockExplanation');
}
function clock_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/course_home/widgets/clock/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}
function clock_settings_form(){
	
}
?>
