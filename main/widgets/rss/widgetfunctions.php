<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

// load the specific widget settings
api_load_widget_settings();

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		rss_get_information();
		break;
	case 'get_widget_content':
		rss_get_content($_POST['content']);
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		rss_get_information();
		break;
	case 'get_widget_content':
		rss_get_content($_GET['content']);
		break;
	case 'get_widget_title':
		rss_get_title();
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
function rss_get_scope(){
	return array('course', 'platform');
}

function rss_get_content($content){
	// include the magpie RSS reader
	require_once 'includes/rss_fetch.inc';

	// fetching the RSS feed
	$rss = '';
	$rss = fetch_rss(trim($content));

	// the maximum number of items that need to be displayed
	$settinglimit = api_get_setting(trim($content),'number_of_items');

	// initialisation of the counter
	$counter = 1;
	
	// displaying the RSS feed elements
	echo '<ul>';
	foreach ($rss->items as $item ) 
	{
		if($counter <= $settinglimit OR $settinglimit<=0) {	
			echo "<li><a href=".$item['link'].">".$item['title']."</a></li>\n";
		}
		$counter++;
	}
	echo "</ul>";
}

function rss_get_title($content, $original_title=false) {
	$config_title = api_get_setting($content, 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		// include the magpie RSS reader	
		require_once 'includes/rss_fetch.inc';
	
		// fetching the RSS feed	
		$rss = fetch_rss(trim($content));
	
		return $rss->channel['title'];
	}
}

function rss_get_information(){
	echo '<span style="float:right;">';
	rss_get_screenshot();
	echo '</span>';	
	echo get_lang('RssExplanation');
}
function rss_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/widgets/rss/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}
function rss_settings_form($widget){
	$return .= '<div class="widget_setting">';
	$return .= '<div  class="ui-corner-all widget_setting_subtitle">'.get_lang('RSSNumberOfItems').'</div>';
	$return .= '<label><input type="text" name="widget_setting_number_of_items" id="widget_setting_number_of_items" value="'.api_get_setting($widget,'number_of_items').'"/></label>';
	$return .= '</div>';	
	return $return;
}
?>
