<?php


// resetting the course id
$cidReset=true;

// setting the help
$help_content = 'platformadministration';

// we are not inside a course, so we reset the course id
// $cidReset = true;

// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');

require_once api_get_path( SYS_PATH ) . 'main/core/model/product/ProductLMS.php';
//require_once ('../course_home/course_home_functions.php');
//require ('product_functions.php');

api_protect_admin_script();

$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){        
    });
</script>';
$htmlHeadXtra[] = '<style type="text/css">
	</style>';

// setting the name of the tool
$nameTools = get_lang('PlatformAdmin');

// setting breadcrumbs
//$interbreadcrumb[] = array('url' => 'index.php', 'name' => $nameTools);

// Displaying the header
Display::display_header($nameTools);

echo '<div class="actions">';
echo '<a href="'.api_get_self().'?action=add'.'">' . Display::return_icon('pixel.gif',get_lang('AddProduct'), array('class' => 'toolactionplaceholdericon toolactionaddprogramme')) . get_lang('AddProduct') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/tool_list.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ListTool') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/product_user_list.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ProductListUser') . '</a>';
echo '</div>';

// open the content div
echo '<div id="content">';

$lms = new ProductLMS();
$form = new FormValidator('edit_lms_info');

$form->add_textfield('name', get_lang('NameLMS'));
$form->applyFilter('name', 'html_filter');
$form->applyFilter('name', 'trim');
$form->add_textfield('max_users', get_lang('MaxUsers'));
$form->addRule('max_users', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('max_users', get_lang('ThisFieldMustBeNumeric'), 'numeric');
$form->add_textfield('max_courses', get_lang('MaxCourses'));
$form->addRule('max_courses', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('max_courses', get_lang('ThisFieldMustBeNumeric'), 'numeric');
$form->add_textfield('max_espace', get_lang('MaxEspace'));
$form->addRule('max_espace', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('max_espace', get_lang('ThisFieldMustBeNumeric'), 'numeric');
$form->add_textfield('price', get_lang('Price'));
$form->addRule('price', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('price', get_lang('ThisFieldMustBeNumeric'), 'numeric');
$form->add_textfield('time', get_lang('Time'));
$form->addRule('time', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('time', get_lang('ThisFieldMustBeNumeric'), 'numeric');
//$form->add_textfield('time', get_lang('Time'));
//$form->addRule('time', get_lang('ThisFieldIsRequired'), 'required');
//$form->addRule('time', get_lang('ThisFieldMustBeNumeric'), 'numeric');
$form->addElement('radio', 'radio_expiration_date', get_lang('ExpirationDate'), get_lang('NeverExpires'), 0);
$group = array ();
$group[] = & $form->createElement('radio', 'radio_expiration_date', null, get_lang('NumberOfMonths'), 1);
$group[] = & $form->createElement('text', 'months', null, array('size'=>5, 'maxlength'=>5));
$form->addGroup($group, 'max_months_group', null, null, false);
$group = array ();
$group[] = & $form->createElement('radio', 'radio_expiration_date', null, get_lang('CustomDate'), 2);
$group[] = & $form->createElement('datepickerdate', 'expiration_date', null, array('form_name' => $form->getAttribute('name'), 'onchange' => 'javascript: enable_expiration_date();'));
$form->addGroup($group, 'max_member_group', null, null, false);
$form->addElement('style_submit_button', 'submit', get_lang('Validate'), 'class="add" style="float:left"');

$defaults = array(
    'name' => trim($lms->name),
    'max_users' => intval($lms->max_users),
    'max_courses' => intval($lms->max_courses),
    'max_espace' => intval($lms->max_espace),
    'price' => intval($lms->price),
    'time' => intval($lms->time),
);
$form->setDefaults($defaults);
if($form->validate())
{
    $post = $form->exportValues();
    $lms->setInfo($post);
}

$form->display();
// close the content div
echo '<div class="clear"> </div>';
echo '</div>';

// display the footer
Display :: display_footer();
?>
