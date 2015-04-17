<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
* 	Exercise list: This script shows the list of exercises for administrators and students.
*	@package dokeos.codetemplates
==============================================================================
*/

// Language files that should be included
$language_file[] = 'languagefile1';
$language_file[] = 'languagefile2';

// setting the help
$help_content = 'codetemplate';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// setting the tabs
$this_section=SECTION_COURSES;

// Add additional javascript, css
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript">$("#actions").click(function(){ alert("You clicked an action")});</script>';

// setting the breadcrumbs
$interbreadcrumb[] = array ("url"=>"overview.php", "name"=> get_lang('OverviewOfAllCodeTemplates'));
$interbreadcrumb[] = array ("url"=>"coursetool.php", "name"=> get_lang('CourseTool'));

// Display the header
Display::display_header(get_lang('CourseTool'));

// display the actions
echo '<div class="actions">';
echo coursetool_actions();
echo '</div>';

// start the content div
echo '<div id="content_with_secondary_actions">';

// Action handling
coursetool_action_handling();

// the main content
coursetool_main();

// close the content div
echo '</div>';


// display the actions
echo '<div class="actions">';
echo coursetool_secondary_actions();
echo '</div>';

// display the footer
Display::display_footer();

/**
 * In this function you can perform all the actions (mostly based on $_GET['action'] parameter or $_POST values
 */
function coursetool_action_handling(){
	// $_GET['action'] action handling
	switch ($_GET['action']){
		case 'add': 
			display_coursetool_form();
			break;
		case 'delete': 
			course_tool_delete();
			break;
	}

	// $_POST['action'] action handling
	switch ($_POST['action']){
		case 'save': 
			display_coursetool_form();
			break;
		case 'delete': 
			course_tool_delete();
			break;
	}
}

function display_coursetool_form(){
	echo 'FORM';	
}

function course_tool_delete(){
	$var = 'Here you would do a delete action (on filesystem or in the database) and display a feedback message';
	Display::display_confirmation_message($var);
}

function coursetool_actions(){
	$return = '<a href="coursetool.php?action=add">'.Display::return_icon('add.gif').' '.get_lang('Add').'</a>';
	$return .= '<a href="coursetool.php?action=add">'.Display::return_icon('add.gif').' '.get_lang('Add').'</a>';
	return $return; 
}

function coursetool_secondary_actions(){
	$return = '<a href="coursetool.php?action=add">'.Display::return_icon('add.gif').' '.get_lang('SecondaryAction1').'</a>';
	$return .= '<a href="coursetool.php?action=add">'.Display::return_icon('add.gif').' '.get_lang('SecondaryAction2').'</a>';
	return $return; 
}

function coursetool_main(){
	echo '<table class="data_table">';
	echo '	<tr>';
	echo '		<th>column 1</th>';
	echo '		<th>column 2</th>';
	echo '		<th>column 3</th>';
	echo '		<th>column 4</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="4">Normally this table would be created by using $table = new SortableTable(...) in the code, but for illustration purposes we just added html code</td>';
	echo '	</tr>';

	echo '</table>';
}
?>
