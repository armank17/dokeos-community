<?php

/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = 'gradebook';

// including the global dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
require_once ('../inc/global.inc.php');
require_once ('lib/be.inc.php');
require_once ('lib/gradebook_functions.inc.php');
require_once ('lib/fe/catform.class.php');

// Access restrictions
api_block_anonymous_users();
block_students();

$edit_cat= isset($_GET['editcat']) ? $_GET['editcat'] : '';
$catedit = Category :: load($edit_cat);
$form = new CatForm(CatForm :: TYPE_EDIT, $catedit[0], 'edit_cat_form');
if ($form->validate()) {
	$values = $form->exportValues();
	$cat = new Category();
	$cat->set_id($values['hid_id']);
	$cat->set_name($values['name']);
	if (empty ($values['course_code'])) {
		$cat->set_course_code(null);
	}else {
		$cat->set_course_code($values['course_code']);
	}
	$cat->set_description($values['description']);
	$cat->set_user_id($values['hid_user_id']);
	$cat->set_parent_id($values['hid_parent_id']);
	$cat->set_weight($values['weight']);
	$cat->set_certificate_min_score($values['certif_min_score']);
	if (empty ($values['visible'])) {
		$visible = 0;
	} else {
		$visible = 1;
	}
	$cat->set_visible($visible);
	$cat->save();
	header('Location: '.$_SESSION['gradebook_dest'].'?editcat=&amp;selectcat=' . $cat->get_parent_id());
	exit;
}
$selectcat = isset($_GET['selectcat']) ? Security::remove_XSS($_GET['selectcat']) : '';
$interbreadcrumb[] = array (
	'url' => 'index.php',
	'name' => get_lang('Gradebook'
));
Display::display_tool_header(get_lang('EditCategory'));
echo '<div class="actions">';
echo '<a href="'.$_SESSION['gradebook_dest'].'?selectcat='.Security::remove_XSS($_GET['editcat']).'&'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.get_lang('Gradebook'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Back').' '.get_lang('To').' '.get_lang('Gradebook').'</a>';
echo '</div>';

echo '<div id="content">';
echo '<h3 class="orange">'.get_lang('EditCategory').'</h3>';
$form->display();
echo '</div>';
Display :: display_footer();
