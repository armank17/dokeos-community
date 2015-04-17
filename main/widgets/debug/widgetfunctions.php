<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		debug_get_information();
		break;
	case 'get_widget_content':
		debug_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		debug_get_information();
		break;
	case 'get_widget_content':
		debug_get_content();
		break;
	case 'get_widget_title':
		debug_get_title();
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
function debug_get_scope(){
	return array('course', 'platform');
}


function debug_get_content(){
	echo '<div id="debug"></debug>';
}

function debug_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('debug', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('Debug');
	}
}

function debug_get_information(){
	echo get_lang('DebugInformation');
}
function debug_settings_form(){
	
}
?>
