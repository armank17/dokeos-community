<?php

ob_start();
/* For licensing terms, see /dokeos_license.txt */

/**
 * Display a list of courses and search for courses
 * @package dokeos.admin
 */
// Language files that should be included
$language_file = array('admin', 'courses', 'link', 'index');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationcourselist';

// including the global Dokeos file
require dirname(__FILE__) . ('/../inc/global.inc.php');
// including additional libraries
require_once (api_get_path(LIBRARY_PATH) . 'course.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
//require_once (api_get_path(LIBRARY_PATH) . 'sortabletable.class.php');
//require_once '../gradebook/lib/be/gradebookitem.class.php';
//require_once '../gradebook/lib/be/category.class.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';

$objCatalog = new EcommerceCatalog();

// section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();

// Setting the breadcrumbs
$interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

$tool_name = get_lang('Catalog');

// Display the header
Display::display_header($tool_name);

//Actions
echo <<<EOF
<div class="actions">
EOF;
$objCatalog = new EcommerceCatalog();
$objCatalog->getCatalogSettings();

echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_settings.php">' . Display::return_icon('pixel.gif', get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('Settings') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_catalog.php" class="active">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncatalog')) . get_lang('Catalog') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Category">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncategory')) . get_lang('langCategories') . '</a>';
switch ($objCatalog->currentValue->selected_value) {
    case CATALOG_TYPE_SESSIONS:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce">' . Display::return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
    case CATALOG_TYPE_COURSES:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce">' . Display::return_icon('pixel.gif', get_lang('Courses'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
    case CATALOG_TYPE_MODULES:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce">' . Display::return_icon('pixel.gif', get_lang('ModulePacks'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
}
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_payment.php">' . Display::return_icon('pixel.gif', get_lang('Payment'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . get_lang('Payment') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('Invoices') . '</a>';
echo <<<EOF
</div>
EOF;

$form = new FormValidator('update_ecommerce_catalog', 'post');

//$form->addElement('header', null, get_lang('CatalogName'));
$form->add_textfield('catalog', get_lang('CatalogName'), true, array('size' => '30', 'class' => 'focus'));

$form->add_textfield('category', get_lang('CategoryName'), true, array('size' => '30'));

$dsp_catalog_var = 'display_catalog_on_homepage';
$opcs = api_get_settings_options($dsp_catalog_var);
$group = array();
if (is_array($opcs)) {
    foreach ($opcs as $key => $value) {
        $group[] = & $form->createElement('radio', $dsp_catalog_var, '', get_lang($value['display_text']), $value['value']);
    }
}

$form->addGroup($group, null, get_lang('DisplayCatalog'), '<br/>');

$dsp_images_var = 'display_categories_on_homepage';
$opcs = api_get_settings_options($dsp_images_var);
$group = array();
if (is_array($opcs)) {
    foreach ($opcs as $key => $value) {
        $group[] = & $form->createElement('radio', $dsp_images_var, '', get_lang($value['display_text']), $value['value']);
    }
}

$form->addGroup($group, null, get_lang('DisplayImages'), '<br/>');

$payment_var = api_get_setting('e_commerce_payment_method');

$group = array();
foreach ($payment_var as $ind => $val) {
    $element = & $form->createElement('checkbox', 'e_commerce_payment_method[' . $ind . ']', '', get_lang(ucfirst($ind)));
    if ($val == 'true')
        $element->setChecked(true);
    if ($ind != 'online')
        $element->freeze();
    $group[] = $element;
    break; //add for paypal - just one
}
$form->addGroup($group, null, get_lang('PaymentAccepted'), '<br/>');

$form->add_html_editor('msg_creditcard', get_lang('CreditCardMessage'), false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '600px', 'Height' => '90px'));
//$form->add_html_editor('msg_cheque', get_lang('ChequeMessage'), false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '600px', 'Height' => '90px'));
//$form->add_html_editor('msg_end_payment', get_lang('MessageWhenEndPayment'), false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '600px', 'Height' => '90px'));

$form->addElement('style_submit_button', 'submit', get_lang('Save'), 'class="save"');


$values = array();
$dsp_images = api_get_setting($dsp_images_var);
$dsp_catalog = api_get_setting($dsp_catalog_var);
$catalog = api_get_settings_options('catalogName');
$category = api_get_settings_options('categoryName');
$dspy_catalog = api_get_settings_options('displayCatalog');
$msg_creditcard = api_get_settings_options('messageCreditcard');
$msg_cheque = api_get_settings_options('messageCheque');
$msg_end_payment = api_get_settings_options('messageEndPayment');
$values['catalog'] = $catalog[0]['value'];
$values['category'] = $category[0]['value'];
$values['dspy_catalog'] = $dspy_catalog[0]['value'];
$values['display_categories_on_homepage'] = $dsp_images;
$values['display_catalog_on_homepage'] = $dsp_catalog;
$values['msg_creditcard'] = $msg_creditcard[0]['value'];
$values['msg_cheque'] = $msg_cheque[0]['value'];
$values['msg_end_payment'] = $msg_end_payment[0]['value'];

if ($form->validate()) {
    $post = $form->exportValues();

    $settings['catalogName'] = $post['catalog'];
    $settings['categoryName'] = $post['category'];
    $settings['displayCatalog'] = $post['dspy_catalog'];
//    $settings['displayImages'] = $post['dspy_images'];
    $settings['messageCreditcard'] = $post['msg_creditcard'];
    $settings['messageCheque'] = $post['msg_cheque'];
    $settings['messageEndPayment'] = $post['msg_end_payment'];
    foreach ($settings as $key => $value) {
        if (!empty($value)) {
            $var = api_get_settings_options($key);
            if (empty($var))
                api_create_settings_options($key, $value);
            else
                api_set_settings_options($key, $value);
        }
    }

    api_set_setting('display_categories_on_homepage', $post['display_categories_on_homepage']);
    api_set_setting('display_catalog_on_homepage', $post['display_catalog_on_homepage']);
    header('Location: ' . api_get_path(WEB_PATH) . 'main/admin/ecommerce_catalog.php?action=show_message&message=Saved');
    ob_end_flush();
}

$form->setDefaults($values);
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'show_message' :
            if (!empty($_GET['message'])) {
                //Display :: display_confirmation_message2(stripslashes(get_lang($_GET['message'])), false, true);
                $_SESSION['display_confirmation_message'] = stripslashes(get_lang($_GET['message']));
            }
            break;
    }
}
// start the content div
echo '<div id="content">';
api_display_tool_title($tool_name);
$form->display();
echo '</div>';

if(isset($_SESSION['display_confirmation_message'])){
    display::display_confirmation_message2($_SESSION['display_confirmation_message'], false,true);
    unset($_SESSION['display_confirmation_message']);
}
//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';
// display the footer
Display::display_footer();