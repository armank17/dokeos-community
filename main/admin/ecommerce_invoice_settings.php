<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Display a list of courses and search for courses
* @package dokeos.admin
*/


// Language files that should be included
$language_file = array('admin','invoice');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationinvoicelist';

// including the global Dokeos file
require dirname(__FILE__) .('/../inc/global.inc.php');
// including additional libraries
//require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
//require_once '../gradebook/lib/be/gradebookitem.class.php';
//require_once '../gradebook/lib/be/category.class.php';
require_once api_get_path(SYS_PATH) .'main/core/model/ecommerce/EcommerceInvoice.php';


$objInvoice = new EcommerceInvoice();

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();


// Setting the breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));


$tool_name = get_lang('CourseList');

// Display the header
Display::display_header($tool_name);


//Actions
echo <<<EOF
<div class="actions">
EOF;
    $objCatalog = new EcommerceCatalog();
    $objCatalog->getCatalogSettings();

    switch ( $objCatalog->currentValue->selected_value )
    {
        case CATALOG_TYPE_SESSIONS:
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_add.php">'.Display::return_icon('pixel.gif', get_lang('AddSession'),array('class' => 'toolactionplaceholdericon toolactionadd')).get_lang('AddSession').'</a>';
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_list.php">' . Display::return_icon('pixel.gif', get_lang('SessionList'),array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';        
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_export.php">'.Display::return_icon('pixel.gif',get_lang('ExportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportSessionListXMLCSV').'</a>';
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_import.php">'.Display::return_icon('pixel.gif',get_lang('ImportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionimportcourse')).get_lang('ImportSessionListXMLCSV').'</a>';	        
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'coursecopy/copy_course_session.php">'.Display::return_icon('pixel.gif',get_lang('CopyFromCourseInSessionToAnotherSession'),array('class' => 'toolactionplaceholdericon toolsettings')).get_lang('CopyFromCourseInSessionToAnotherSession').'</a>';
            break;
        case CATALOG_TYPE_COURSES:
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_courses.php">'.Display::return_icon('pixel.gif', get_lang('Courses'),array('class' => 'toolactionplaceholdericon toolactionadmincourse')) . get_lang('Courses') . '</a>';
            break;
        case CATALOG_TYPE_MODULES:
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_module_packs.php">'.Display::return_icon('pixel.gif', get_lang('ModulePacks'),array('class' => 'toolactionplaceholdericon toolactionassignment')). get_lang('ModulePacks').'</a>';
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_module_packs_add.php">'.Display::return_icon('pixel.gif', get_lang('CreateModulePacks'),array('class' => 'toolactionplaceholdericon toolactionnewassignment')).get_lang('CreateModulePacks').'</a>';     
            break;
    }
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/configure_e_commerce">'.Display::return_icon('pixel.gif',get_lang('EcommerceSettings'),array('class' => 'toolactionplaceholdericon toolsettings')).get_lang('EcommerceSettings').'</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'),array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('Invoices') . '</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_invoice_settings.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'),array('class' => 'toolactionplaceholdericon toolactionprogramme')) . get_lang('InvoiceSettings') . '</a>';
//    echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/course_export.php">'.Display::return_icon('pixel.gif',get_lang('Export'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('Export').'</a>';

echo <<<EOF
</div>
EOF;

// Build the form
$form = new FormValidator('update_invoice_settings');
$form->addElement('header', '', get_lang('UpdateInvoiceSettings'));

// Company name
$form->add_textfield('company', get_lang('CompanyName'),true, array('size'=>'60', 'class'=>'focus'));
$form->applyFilter('company', 'html_filter');
$form->applyFilter('company', 'trim');

// Company address
$form->add_textfield('address1', get_lang('CompanyAddress1'),true, array('size'=>'60', 'class'=>'focus'));
$form->applyFilter('address1', 'html_filter');
$form->applyFilter('address1', 'trim');

// Company address
$form->add_textfield('address2', get_lang('CompanyAddress2'),true, array('size'=>'60', 'class'=>'focus'));
$form->applyFilter('address2', 'html_filter');
$form->applyFilter('address2', 'trim');

// Company logo
$cmp_logo = api_get_settings_options('invoiceLogo');
$img_path = api_get_path(WEB_PATH). 'archive/invoice/logo-dokeos.png';
if(!empty($cmp_logo)){
    $img_path = api_get_path(WEB_PATH). 'archive/invoice/'.$cmp_logo[0]['value'];
}
if($cmp_logo[0]['value'] == 'no-image')
    $img_path = '';

$form->addElement('file', 'logo', get_lang('SelectInvoiceLogo'));
$allowed_file_types = array ('jpg', 'png');
$form->addRule('logo', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
$form->addElement('static','imagesize','',get_lang('PNG or JPG images'));
if(!empty($img_path))
$form->addElement('static','thumbimage',get_lang('Preview'),'<img src="'.$img_path.'">');
$form->addElement('checkbox','remove_img',null,get_lang('RemoveImage').' / '.get_lang('WithoutImage'),1);

// Bank name
$form->add_textfield('bank', get_lang('BankName'),false, array('size'=>'30'));
$form->applyFilter('bank', 'html_filter');
$form->applyFilter('bank', 'trim');

// Description
$form->addElement('textarea', 'description', get_lang('Description'), array('rows'=>5, 'cols'=>77));

$form->addElement('style_submit_button', 'submit', get_lang('Save'), 'class="save"');

$values = array();
if ('no-image' == $cmp_logo)
    $values['remove_img'] = 1;
$company = api_get_settings_options('companyName');
$address1 = api_get_settings_options('companyAddress1');
$address2 = api_get_settings_options('companyAddress2');
$bank = api_get_settings_options('invoiceBank');
$description = api_get_settings_options('invoiceDescription');
$values['company'] = $company[0]['value'];
$values['address1'] = $address1[0]['value'];
$values['address2'] = $address2[0]['value'];
$values['bank'] = $bank[0]['value'];
$values['description'] = $description[0]['value'];
$form->setDefaults($values);

// Validate form
if($form->validate())
{
    $post = $form->exportValues();
    $logo = $_FILES['logo'];
    $settings = array();
    $temp_path = api_get_path(SYS_PATH). 'archive/invoice/';
    if(!empty($logo['tmp_name']))
    {
        $img_tmp = $logo['tmp_name'];
        $img_pic = replace_dangerous_char($logo['name'], 'strict');	
        $old_logo = null;
        if(api_get_settings_options('invoiceLogo'))
        {
            $old_logo = api_get_settings_options('invoiceLogo');
            @unlink($temp_path.$old_logo['value']);
        }
        
//        $width_img = 200;
//        $height_img = 50;

        // Use the function for resize the image
        @move_uploaded_file($img_tmp, "$temp_path/$img_pic");
//        api_resize_images($temp_path,$img_tmp,$img_pic,$width_img,$height_img);
        $settings['invoiceLogo'] = $img_pic;
    }
    if($post['remove_img'] == 1)
        $settings['invoiceLogo'] = 'no-image';
    
    $settings['companyName'] = $post['company'];
    $settings['companyAddress1'] = $post['address1'];
    $settings['companyAddress2'] = $post['address2'];
    $settings['invoiceBank'] = $post['bank'];
    $settings['invoiceDescription'] = $post['description'];
    
    foreach($settings as $key=>$value)
    {
        if(!empty($value))
        {
            $var = api_get_settings_options($key);
            if(empty($var))
                api_create_settings_options($key, $value);
            else
                api_set_settings_options($key, $value);
        }
    }
//    header('Location: '.api_get_self());
    header('Location :'.  api_get_path(WEB_PATH) .'main/admin/ecommerce_invoice_settings.php');
//    exit;
}

// start the content div
echo '<div id="content">';
$form->display();
echo '</div>';

//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';

// display the footer
Display::display_footer();