<?php
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		wiki_get_information();
		break;
	case 'get_widget_content':
		wiki_get_content();
		break;
	case 'get_widget_title':
		wiki_get_title();
		break;
	case 'selectwikipage':
		selectwikipage_form();
		break;
	case 'install':
		wiki_install();
		break;
	case 'wiki_save':
		wiki_save($_POST['reflink']);
		break;
}
switch ($_GET['action']) {
	case 'get_widget_information':
		wiki_get_information();
		break;
	case 'get_widget_content':
		wiki_get_content();
		break;
	case 'get_widget_title':
		wiki_get_title();
		break;
	case 'selectwikipage':
		selectwikipage_form();
		break;
	case 'install':
		wiki_install();
		break;
	case 'wiki_save':
		wiki_save($_GET['reflink']);
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
function wiki_get_scope(){
	return array('course');
}

function wiki_get_content(){
	global $tbl_wiki;
	global $groupfilter;

	// include wiki.inc.php
	include_once(api_get_path(SYS_CODE_PATH).'wiki/wiki.inc.php');

	// needed to make wiki links clickable
	$groupfilter='group_id=0';

	// additional css
	echo '
	<style>
		.wiki_title{ border-bottom: 1px solid #BBB; color: #444; font-size: 20px; margin: 0 0 10px; padding: 2px 10px 1px 0;}
	</style>';

	// Database table definition
	$tbl_wiki 			= Database::get_course_table(TABLE_WIKI);
	$table_wiki 		= Database::get_course_table(TABLE_WIKI);
	$table_wiki_conf 	= Database::get_course_table(TABLE_WIKI_CONF);
	$table_wikiwidget 	= Database :: get_course_table('wikiwidget');


	// condition for the sessions
	$condition_session = api_get_session_condition(api_get_session_id());

	// link to add a wiki page
	if (api_is_allowed_to_edit ()) {
		echo '<a href="' . api_get_path ( WEB_PATH ) . 'main/widgets/wiki/widgetfunctions.php?action=selectwikipage" title="' . get_lang ( 'SelectWikiPage' ) . '" class="dialoglink">' . Display::return_icon ( 'new_test.gif' ) . ' ' . get_lang ( 'SelectWikiPage' ) . '</a>';
	}

	// Select the active wiki pages
	$active = array();
	$sqlactive = "SELECT * FROM $table_wikiwidget";
	$resultactive = Database::query($sqlactive,__LINE__,__FILE__);
	while ($objectactive = Database::fetch_object($resultactive)){
		$active[] = $objectactive->reflink;
	}


	// display all the wiki pages
	$sql='SELECT * FROM '.$table_wiki.', '.$table_wiki_conf.'
				WHERE visibility=1 AND '.$table_wiki_conf.'.page_id='.$table_wiki.'.page_id
				AND '.$table_wiki.'.group_id=0'.$condition_session.' AND reflink IN (\''.implode("','",$active).'\')
				ORDER BY id ASC';
	$allpages=Database::query($sql,__LINE__,__FILE__);
	$pages_display=array();
	while ($obj = Database::fetch_object($allpages)){
		// save in array to be sure that we have the latest version
		$pages_display[$obj->reflink] = array('title' => $obj->title, 'content' => $obj->content);
	}

	foreach($pages_display as $key=>$wiki_page){
		echo '<div class="wiki_title">'.$wiki_page['title'].'</div>';
		echo make_wiki_link_clickable($wiki_page['content']);
	}
}

function wiki_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('wiki', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('Wiki');
	}
}

function wiki_get_information(){
	echo '<span style="float:right;">';
	wiki_get_screenshot();
	echo '</span>';
	echo get_lang('WikiExplanation');
}
function wiki_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/widgets/wiki/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}
function wiki_settings_form(){

}

function selectwikipage_form(){
	// Database table definition
	$table_wiki 		= Database::get_course_table(TABLE_WIKI);
	$table_wiki_conf 	= Database::get_course_table(TABLE_WIKI_CONF);
	$table_wikiwidget 	= Database :: get_course_table('wikiwidget');

	// condition for the sessions
	$condition_session = api_get_session_condition(api_get_session_id());

	// style
	echo "	<style type='text/css'>
				.selectwiki{
					height: 30px;
					padding-left: 20px;
				}
				.activewiki{
					background-image: url('../../img/accept.png');
					background-repeat: no-repeat;
					background-image: url('".api_get_path(WEB_PATH)."main/img/accept.png');
					background-repeat: no-repeat;
				}

			</style>";
	echo '	<script type="text/javascript">
				$(document).ready(function() {
					$(".selectwiki").live("click", function(){
						$(this).toggleClass("activewiki");
						var varreflink = $(this).attr("id");
						$.ajax({
							url: "'.api_get_path(WEB_PATH).'main/widgets/wiki/widgetfunctions.php",
							data: {action: "wiki_save", reflink: varreflink},
							success: function(content){

							}
						});
					});
				})
			</script>';

	// Select the active wiki pages
	$sqlactive = "SELECT * FROM $table_wikiwidget";
	$resultactive = Database::query($sqlactive,__LINE__,__FILE__);
	while ($objectactive = Database::fetch_object($resultactive)){
		$active[] = $objectactive->reflink;
	}


	// display all the wiki pages
	$sql='SELECT * FROM '.$table_wiki.', '.$table_wiki_conf.'
				WHERE visibility=1 AND '.$table_wiki_conf.'.page_id='.$table_wiki.'.page_id
				AND '.$table_wiki.'.group_id=0'.$condition_session.'
			GROUP BY '.$table_wiki.'.page_id';
	$allpages=Database::query($sql,__LINE__,__FILE__);
	while ($obj = Database::fetch_object($allpages)){
		if (in_array($obj->reflink, $active)){
			$status = 'activewiki';
		} else {
			$status = '';
		}
		echo '<a href="" onclick="return false;" id="'.$obj->reflink.'" class="selectwiki '.$status.'">'.$obj->title.'</a><br />';
	}
}

/**
 *
 * @version Dokeos 2.0
 * @since March 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function wiki_install(){
	global $_course;

	$table_wikiwidget = Database :: get_course_table('wikiwidget');

	$sql = "CREATE TABLE  $table_wikiwidget (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`reflink` VARCHAR( 250 ) NOT NULL
				) ";
	$result = Database::query($sql, __FILE__, __LINE__);
}

function wiki_save($reflink){
	$table_wikiwidget = Database :: get_course_table('wikiwidget');

	$sql = "SELECT * FROM $table_wikiwidget WHERE reflink = '".Database::escape_string($reflink)."'";
	$result = Database::query($sql, __FILE__, __LINE__);
	$number = Database::num_rows($result);

	if ($number == 0){
		$sql = "INSERT INTO $table_wikiwidget (reflink) VALUES ('".Database::escape_string($reflink)."')";
		$result = Database::query($sql, __FILE__, __LINE__);
	} else {
		$sql = "DELETE FROM $table_wikiwidget WHERE reflink = '".Database::escape_string($reflink)."'";
		$result = Database::query($sql, __FILE__, __LINE__);
	}

}
?>
