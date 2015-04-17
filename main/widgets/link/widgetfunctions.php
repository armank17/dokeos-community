<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		link_get_information();
		break;
	case 'get_widget_content':
		link_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		link_get_information();
		break;
	case 'get_widget_content':
		link_get_content();
		break;
	case 'get_widget_title':
		link_get_title();
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
function link_get_scope(){
	return array('course');
}


function link_get_content(){
	echo 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit';
}

function link_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('link', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('Links');
	}
}

function link_get_information(){
	echo get_lang('LinksInformation');
}
function link_settings_form(){
	
}
?>
