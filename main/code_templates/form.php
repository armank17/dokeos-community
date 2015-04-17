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
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript">$(".actions a").click(function(){ alert("You clicked an action")});</script>';

// setting the breadcrumbs
$interbreadcrumb[] = array ("url"=>"overview.php", "name"=> get_lang('OverviewOfAllCodeTemplates'));
$interbreadcrumb[] = array ("url"=>"coursetool.php", "name"=> get_lang('CourseTool'));

// Display the header
Display::display_header(get_lang('CourseTool'));

// display the actions
echo '<div class="actions">';
echo '<a href="#">'.Display::return_icon('add_32.png').'Primary action 1</a>';
echo '<a href="#">'.Display::return_icon('color_line_32.png').'Primary action 2</a>';
echo '</div>';

// start the content div
echo '<div id="content">';

// initiate the object
$form = new FormValidator('name_of_the_form','post','form.php?additionalparameter='.Security::Remove_XSS($_GET['additionalparameter']));

// form header (title)
$form->addElement('header', '', 'The name of the form');

// text field
$form->addElement('text', 'text1', 'Label (100px wide)');

// text field with additional class
$form->addElement('text', 'text2', 'Label','class="whateverclassyouwant"');
$form->addElement('static', 'explanation3', '', 'The 4th parameter can be a string like <em>class="whateverclassyouwant"</em> to give the form element a different class so that it is styled differently (for instance not that wide)');

// text field
$form->addElement('text', 'text3', 'Label',array('class'=>"whateverclassyouwant", 'style'=>'width: 400px;'));
$form->addElement('static', 'explanation3', '', 'The 4th parameter can be an array of additional attributes have to be added to the form element. In this case we added <em>array("class"=>"whateverclassyouwant", "style"=>"width: 400px;")</em> as 4th paramter');

// submit button
$form->addElement('style_submit_button', 'SubmitForm', 'Submit', 'class="add"');

// setting the default values
$defaults['text1'] = 'Default input field is 795px wide';
$defaults['text2'] = 'The default value of text2';
$defaults['text3'] = 'Default values are set using $form->setDefaults($defaults);';
$form->setDefaults($defaults);

// The validation or display
if ( $form->validate() ) {
	// export the form values and handle these
	$values = $form->exportValues();
	echo '<pre>';
	print_r($values);
	echo '</pre>';
} else {
	$form->display();
}	
// close the content div
echo '</div>';


// display the actions
echo '<div class="actions">';
echo '<a href="#">'.Display::return_icon('add_32.png').' Secondary action 1</a>';
echo '<a href="#">'.Display::return_icon('color_line_32.png').' Secondary action 2</a>';
echo '</div>';

// display the footer
Display::display_footer();

/**
 * In this function you can perform all the actions (mostly based on $_GET['action'] parameter or $_POST values
 */
function form_action_handling(){
	// $_GET['action'] action handling
	switch ($_GET['action']){
		case 'add': 
			display_form();
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
?>
