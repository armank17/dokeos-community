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

// additional javascript
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
//$this_section = SECTION_MYGRADEBOOK;

$evaledit = Evaluation :: load($_GET['editeval']);

// creating the form
$form = new FormValidator('presence','post', api_get_self().'?cidReq='.Security::remove_XSS($_GET['cidReq']).'&amp;selectcat='.Security::remove_XSS($_GET['selectcat']));
// hidden fields
$form->addElement('hidden', 'hid_id',$id_current);
$form->addElement('hidden', 'hid_user_id');
$form->addElement('hidden', 'hid_category_id');
$form->addElement('hidden', 'hid_course_code');
$form->addElement('hidden', 'hid_visible',$evaledit[0]->is_visible());
// header
//$form->addElement('header', '', get_lang('EditPresence'));
// title
$form->addElement('html','<table width="100%"><tr><td align="center" width="150px"><img align="center" src="../img/presence_64.png"></td>');
$form->addElement('html','<td>');
$form->addElement('text', 'presence_title', get_lang('Activity'));
$form->addElement('text', 'presence_trainer', get_lang('Trainer'));
//$form->addElement('text', 'presence_creator', get_lang('PresenceSheetCreatedBy'));
$form->addElement('hidden', 'presence_creator');
$form->add_datepickerdate('presence_date',get_lang('Date'));
$form->addElement('text', 'presence_duration', get_lang('Duration'));
$form->addElement('text', 'weight', get_lang('Weight'));
//$form->addElement('checkbox', 'addresult', get_lang('AddResult'));
$form->addElement('hidden', 'addresult');
$form->addElement('html','</td></tr></table>');
$form->addElement('style_submit_button' , 'submit', get_lang('SaveEditPresence'),'class="save"');
$defaults = array (
		'hid_id' => $evaledit[0]->get_id(),
		'presence_title' => $evaledit[0]->get_name(),
		'hid_user_id' => $evaledit[0]->get_user_id(),
		'hid_course_code' => $evaledit[0]->get_course_code(),
		'hid_category_id' => $evaledit[0]->get_category_id(),
		'date' => $evaledit[0]->get_date(),
		'weight' => $evaledit[0]->get_weight(),
		'max' => $evaledit[0]->get_max(),
		'visible' => $evaledit[0]->is_visible());



$comment_array = unserialize($evaledit[0]->get_description());
$defaults['presence_trainer'] = $comment_array['presence_trainer'];
$defaults['presence_creator'] = $comment_array['presence_creator'];
$defaults['presence_date'] = $comment_array['presence_date'];
$defaults['presence_duration'] = $comment_array['presence_duration'];
$form->setDefaults($defaults);

if ($form->validate()) {
	$values = $form->exportValues();


	//var_dump($values)

	$eval = new Evaluation();
	$eval->set_id($values['hid_id']);
	$eval->set_name($values['presence_title']);
	$description = array (
			'presence_trainer'=>$values['presence_trainer'],
			'presence_creator'=>$values['presence_creator'],
			'presence_date'=>$values['presence_date'],
			'presence_duration'=>$values['presence_duration'],
	);
	// the description field
	$eval->set_description(serialize($description));
	$eval->set_user_id($values['hid_user_id']);
	$eval->set_course_code($values['hid_course_code']);
	$eval->set_category_id($values['hid_category_id']);
	$eval->set_weight($values['weight']);
	$eval->set_date(time());
	$eval->set_max('1');
	$eval->set_visible($values['hid_visible']);
	$eval->set_type('presence');
	$eval->save();

	if ($values['addresult'] == '1')
	{
		header('Location: gradebook_add_result.php?selectcat=' . $eval->get_category_id().'&selecteval=' . $eval->get_id() . '&amp;view=presence');
	}
	else
	{
		header('Location: '.$_SESSION['gradebook_dest'].'?selectcat=' . $eval->get_category_id() . '&amp;view=presence');
	}
	exit;
}
$selectcat_inter=isset($_GET['selectcat'])?Security::remove_XSS($_GET['selectcat']):'';
$interbreadcrumb[]= array (	'url' => '../gradebook/'.$_SESSION['gradebook_dest'].'?selectcat=' . $evaledit[0]->get_category_id(), 'name' => get_lang('Gradebook'));

Display::display_tool_header(get_lang('EditPresence'));
// Actions bar
echo '<div class="actions">';
echo '<a href="'.$_SESSION['gradebook_dest'].'?selectcat='.$evaledit[0]->get_category_id().'&amp;'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.get_lang('Gradebook'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.get_lang('Gradebook').'</a>';
echo '</div>';

echo '<div id="content">';
$form->display();
echo '</div>';

// Actions bar
echo '<div class="actions">';
echo '</div>';
Display :: display_footer();
