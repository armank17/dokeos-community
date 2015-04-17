<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = 'gradebook';

// including the global dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
require_once ('lib/be.inc.php');
require_once ('lib/gradebook_functions.inc.php');
require_once ('lib/fe/evalform.class.php');

// Access control
api_block_anonymous_users();
block_students();
$htmlHeadXtra[] = '<script type="text/javascript">
  $(document).ready(function (){
    $("div.label").attr("style","width: 100%;text-align:left");
    $("div.row").attr("style","width: 100%;");
    $("div.formw").attr("style","width: 100%;");
  });
</script>';
// the section (tabs)
if (!empty($_GET['course'])) {
	$this_section = SECTION_COURSES;
} else {
	$this_section = SECTION_MYGRADEBOOK;
}

$select_cat=isset($_GET['selectcat']) ? Security::remove_XSS($_GET['selectcat']) : '';
$evaladd = new Evaluation();
$evaladd->set_user_id($_user['user_id']);
if (isset ($_GET['selectcat']) && (!empty ($_GET['selectcat']))) {
	$evaladd->set_category_id(Database::escape_string($_GET['selectcat']));
	$cat = Category :: load($_GET['selectcat']);
	$evaladd->set_course_code($cat[0]->get_course_code());
} else {
	$evaladd->set_category_id(0);
}


// creating the form
$form = new FormValidator('presence','post', api_get_self().'?cidReq='.Security::remove_XSS($_GET['cidReq']).'&amp;selectcat='.Security::remove_XSS($_GET['selectcat']));
//$form->addElement('header', '', get_lang('NewPresenceStep1'));
$form->addElement('html','<table width="100%"><tr><td align="center" width="150px"><img align="center" src="../img/presence_64.png"></td>');
$form->addElement('html','<td>');
$form->addElement('text', 'presence_title', get_lang('Activity'));
$form->addElement('text', 'presence_trainer', get_lang('Trainer'));
//$form->addElement('text', 'presence_creator', get_lang('PresenceSheetCreatedBy'));
$form->addElement('hidden','presence_creator');
$form->add_datepickerdate('presence_date',get_lang('Date'));
$form->addElement('text', 'presence_duration', get_lang('Duration'));
$form->addElement('text', 'weight', get_lang('Weight'));
// allow the user to decide if (s)he wants to add the results immediately
// $form->addElement('checkbox', 'addresult', get_lang('AddResult'));
// force the user to add the results immediately
$form->addElement('hidden', 'addresult');
$defaults['addresult']		= '1';
$form->addElement('html','</td></tr></table>');
$form->addElement('style_submit_button' , 'submit', get_lang('SavePresence'),'class="save"');

// the default values of the form
$defaults['presence_trainer'] 	= $_user['firstName'].' '.$_user['lastName'];
$defaults['presence_creator'] 	= $_user['firstName'].' '.$_user['lastName'];
$defaults['presence_date']		= date('Y-m-d');
$form->setDefaults($defaults);

if ($form->validate()) {

	// exporting the values of the form
	$values = $form->exportValues();

	// creating an evaluation object and setting the attributes
	$eval = new Evaluation();
	$eval->set_name($values['presence_title']);
	$description = array (
			'presence_trainer'=>$values['presence_trainer'],
			'presence_creator'=>$values['presence_creator'],
			'presence_date'=>$values['presence_date'],
			'presence_duration'=>$values['presence_duration'],
	);
	// the description field
	$eval->set_description(serialize($description));


	$eval->set_user_id($_user['user_id']);
	$eval->set_course_code(Security::remove_XSS($_GET['cidReq']));
	$eval->set_category_id(Security::remove_XSS($_GET['selectcat']));
    // Set the weight into the gradebook for this resource
	$eval->set_weight($values['weight']);
	//converts the date back to unix timestamp format
	$eval->set_date(time());
	$eval->set_max('1');
	$eval->set_visible('0');
	$eval->set_type('presence');

	// storing the evaluation object in the database
	$eval->add();

	if ($values['addresult'] == '1') {
		header('Location: gradebook_add_result.php?selectcat=' . $eval->get_category_id().'&selecteval=' . $eval->get_id());
	} else {
		header('Location: '.$_SESSION['gradebook_dest'].'?selectcat=' . $eval->get_category_id());
	}
	exit;
} else {
	// the breadcrumbs
	$interbreadcrumb[] = array (
		'url' => $_SESSION['gradebook_dest'].'?selectcat='.$select_cat,
		'name' => get_lang('Gradebook'
	));

	// header
	Display::display_tool_header(get_lang('NewPresence'));

	echo '<div class="actions">';
	echo '<a href="'.$_SESSION['gradebook_dest'].'?selectcat='.$select_cat.'&amp;'.api_get_cidreq().'">'.Display::return_icon('pixel.gif',get_lang('Gradebook'), array('class' => 'toolactionplaceholdericon toolactionback')).' '.get_lang('Gradebook').'</a>';
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'exercice/exercice.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Quiz'), array('class' => 'toolactionplaceholdericon toolactionquiz')).get_lang("Quiz").'</a> ';
	echo '</div>';

	// displaying a warning
	if ($evaladd->get_course_code() == null) {
		Display :: display_normal_message(get_lang('CourseIndependentEvaluation'),false);
	}

	echo '<div id="content">';

	// displaying the form
	$form->display();

	echo '</div>';
	// the footer
	Display :: display_footer();
	exit;
}