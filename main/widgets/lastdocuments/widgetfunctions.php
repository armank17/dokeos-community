<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');
include_once('../../inc/lib/groupmanager.lib.php');
include_once('../../inc/lib/fileDisplay.lib.php');

// load the specific widget settings
api_load_widget_settings();

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		lastdocuments_get_information();
		break;
	case 'get_widget_content':
		lastdocuments_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		lastdocuments_get_information();
		break;
	case 'get_widget_content':
		lastdocuments_get_content();
		break;
	case 'get_widget_title':
		lastdocuments_get_title();
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
function lastdocuments_get_scope(){
	return array('course');
}


function lastdocuments_get_content(){
	global $_course;

	// Database table definitions
	$table_documents 		= Database :: get_course_table(TABLE_DOCUMENT);
	$table_item_property 		= Database :: get_course_table(TABLE_ITEM_PROPERTY);

	
	// how many posts should we select
	$settinglimit = api_get_setting('lastdocuments','numberofitems');
	if (!is_numeric($settinglimit) OR empty($settinglimit)){
		$numberofitems = 5;
	} else {
		$numberofitems = api_get_setting('lastdocuments','numberofitems');
	}
	
	// The sql statment: for course admins it does not matter because they can see everything
	if (api_is_allowed_to_edit()) {
		$sql = "SELECT * FROM $table_documents, $table_item_property WHERE tool='document' AND id=ref AND visibility <> '2' ORDER BY lastedit_date DESC LIMIT 0,$numberofitems";
	} else {
		$sql = "SELECT * FROM $table_documents, $table_item_property WHERE tool='document' AND id=ref AND visibility='1'ORDER BY lastedit_date DESC LIMIT 0,$numberofitems";
	}
	$result = Database::query($sql, __FILE__, __LINE__);
	
	echo '<ul id="widget_lastdocuments_posts">';
	while ($row = Database::fetch_array($result,'ASSOC')){
		if ($counter < $numberofitems) {
			echo '<li><a href="'.document_path($row['filetype'],$row['path']).'">'.document_icon($row['filetype'],$row['path']).' '.$row['title'].'</a> </li>';
		} else {
			exit;
		}
		$counter++;
		
	}	
	echo '</ul>';
}

function document_path($filetype,$path){
	if($filetype=='file') {
		$url_path = urlencode($path);
		//check the extension
		$ext=explode('.',$path);
		$ext=strtolower($ext[sizeof($ext)-1]);
		//"htmlfiles" are shown in a frameset
		if($ext == 'htm' || $ext == 'html' || $ext == 'gif' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
		{
			$url = api_get_path(WEB_CODE_PATH)."document/showinframes.php?".api_get_cidreq()."&amp;file=".$url_path.$req_gid;
		}
		else
		{
			//url-encode for problematic characters (we may not call them dangerous characters...)
			$path = str_replace('%2F', '/',$url_path).'?'.api_get_cidreq();
			$url=$www.$path;
		}
		//files that we want opened in a new window
		if($ext=='txt' || $ext=='log' || $ext=='css' || $ext=='js') //add here
		{
			$target='_blank';
		}
	}
	else
	{
		$url=api_get_path(WEB_CODE_PATH).'?'.api_get_cidreq().'&amp;curdirpath='.$url_path.$req_gid;
	}
	return $url;
}

function document_icon($filetype,$path){
	$path_elements = explode('/',$path);
	$number_of_elements = count($path_elements);
	$image = choose_image($path_elements[($number_of_elements - 1 )]);
	return Display::return_icon($image);
}

function lastdocuments_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('lastdocuments', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('LastDocuments');
	}
}

function lastdocuments_get_information(){
	echo '<span style="float:right;">';
	lastdocuments_get_screenshot();
	echo '</span>';	
	echo get_lang('LastDocumentsInformation');
}
function lastdocuments_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/course_home/widgets/lastdocuments/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}


function lastdocuments_settings_form(){
	$settinglimit = api_get_setting('lastdocuments','numberofitems');
	if (!is_numeric($settinglimit) OR empty($settinglimit)){
		$numberofitems = 5;
	} else {
		$numberofitems = api_get_setting('lastdocuments','numberofitems');
	}

	// the form to change the number of posts that have to be displayed	
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">'.get_lang('NumberOfItemsToDisplay').'</div>';
	echo '<input type="text" name="widget_setting_numberofitems" id="widget_setting_numberofitems" value="'.$numberofitems.'" />';
	echo '</div>';	
}

?>
