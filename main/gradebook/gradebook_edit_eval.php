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

// access restriction
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
$evaledit = Evaluation :: load($_GET['editeval']);
$form = new EvalForm(EvalForm :: TYPE_EDIT, $evaledit[0], null, 'edit_eval_form',null,api_get_self() . '?editeval=' . $_GET['editeval']);
if ($form->validate()) {
	$values = $form->exportValues();
	$eval = new Evaluation();
	$eval->set_id($values['hid_id']);
	$eval->set_name($values['name']);
	$eval->set_description($values['description']);
	$eval->set_user_id($values['hid_user_id']);
	$eval->set_course_code($values['hid_course_code']);
	$eval->set_category_id($values['hid_category_id']);
	$eval->set_weight($values['weight']);
	$eval->set_date(strtotime($values['date']));
	$eval->set_max($values['max']);
	//$eval->set_max($values['weight']);
	if (empty ($values['visible'])) {
		$visible = 0;
	} else {
		$visible = 1;
	}
	$eval->set_visible($visible);
	$eval->save();
	header('Location: '.$_SESSION['gradebook_dest'].'?editeval=&amp;selectcat=' . $eval->get_category_id());
	exit;
}
$selectcat_inter=isset($_GET['selectcat'])?Security::remove_XSS($_GET['selectcat']):'';
$interbreadcrumb[] = array (
	'url' => $_SESSION['gradebook_dest'].'?selectcat='.$selectcat_inter,
	'name' => get_lang('Gradebook'
));
Display::display_tool_header(get_lang('EditEvaluation'));
echo '<div class="actions">';
echo '<a href="'.$_SESSION['gradebook_dest'].'?selectcat='.$selectcat_inter.'&amp;'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.get_lang('Gradebook'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Back').' '.get_lang('To').' '.get_lang('Gradebook').'</a>';
echo '</div>';
echo '<div id="content">';
$form->display();
echo '</div>';

// Actions bar
echo '<div class="actions">';
echo '</div>';
Display :: display_footer();
