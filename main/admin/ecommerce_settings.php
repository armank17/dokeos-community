<?php
ob_start();
/* For licensing terms, see /dokeos_license.txt */

/**
 * Display a list of courses and search for courses
 * @package dokeos.admin
 */
// Language files that should be included
$language_file = array('admin', 'courses', 'index');

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
require_once api_get_path(SYS_PATH) . 'main/appcore/library/thumbnail/Thumbnail.php';


$objCatalog = new EcommerceCatalog();

// section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();


// Setting the breadcrumbs
$interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));


$tool_name = get_lang('Settings');

$htmlHeadXtra[] = '
    <style>
        #content {
            position:relative;        
        }
        #dialog-content {
            float: right;
            margin-top: -140px;
            width: 320px; 
        }
        .confirmation-message {
            margin-bottom:30px !important;
        }
    </style>

    <script type="text/javascript">
        (function ($) {
           try {
                $.fx.speeds._default = 1000;
                $(function() {                    
                    $( "#opener" ).click(function() {
                        //$( "#dialog" ).dialog( "open" );
                        $( "#dialog" ).dialog({
                            autoOpen: true,
                            title:"'.get_lang('Preview').'",
                            closeText:"'.get_lang('Close').'",
                            resizable: false,
                            width: 850,
                            height: 580,
                            modal:true
                        });
                        return false;
                    });
                });
            }catch(e) {}
        } (jQuery));
    </script>';
// Display the header
Display::display_header($tool_name);

//Actions
echo <<<EOF
<div class="actions">
EOF;
$objCatalog = new EcommerceCatalog();
$objCatalog->getCatalogSettings();

echo '<a href="#" class="active">' . Display::return_icon('pixel.gif', get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('Settings') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_catalog.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncatalog')) . get_lang('Catalog') . '</a>';
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


$form = new FormValidator('settings', 'post');

// Database Table Definitions
$variable = 'e_commerce_catalog_type';
$tbl_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
$tbl_settings_options = Database :: get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);
$sql = "SELECT * FROM {$tbl_settings_current} sc WHERE variable = '{$variable}' AND category = 'Ecommerce'";


$res = Database::query($sql, __FILE__, __LINE__);
$catalog_type = Database::fetch_object($res);

//$sql = "SELECT * FROM {$tbl_settings_options} so WHERE variable = '{$variable}' and display_text like 'c%' ";
$sql = "SELECT * FROM {$tbl_settings_options} so WHERE variable = '{$variable}' ";
$sql .= "ORDER BY value desc";
//only courses and sessions
$sql.= " LIMIT 1,3";
$group = array();
$res = Database::query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($res)) {
    $group[] = &$form->createElement('radio', $row['variable'], '', get_lang($row['display_text']), $row['value']);    
}

$form->addElement('header', null, get_lang('Products'));
$form->addGroup($group, null, get_lang($catalog_type->comment), '<br />');

// Company address
$form->addElement('header', null, get_lang('Invoices'));

$form->addElement('html', '
    <div id="dialog" title="Basic dialog" style="display:none;"><img src="'.api_get_path(WEB_PATH).'main/upload/invoice/thumb_settings_large.jpg" /></div>
    <div id="dialog-content">
        <a id="opener" style="float:right;" href="#"><img src="'.api_get_path(WEB_PATH).'main/upload/invoice/thumb_settings_small.jpg" /></a>
    </div>
    ');

// Company logo
$cmp_logo = api_get_settings_options('invoiceLogo');
$img_path = api_get_path(WEB_PATH). 'main/upload/invoice/logo-dokeos.png';
//$img_path = api_get_path(WEB_PATH). 'main/application/ecommerce/assets/files/invoice/logo-dokeos.png';
if(!empty($cmp_logo)){
    $img_path = api_get_path(WEB_PATH). 'main/upload/invoice/'.$cmp_logo[0]['value'];
    //$img_path = api_get_path(WEB_PATH). 'main/application/ecommerce/assets/files/invoice/'.$cmp_logo[0]['value'];
}
if($cmp_logo[0]['value'] == 'no-image')
    $img_path = '';

$form->addElement('file', 'logo', get_lang('SelectInvoiceLogo'));
$allowed_file_types = array ('jpg', 'png');
$form->addRule('logo', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
$form->addElement('static','imagesize','',get_lang('PNGorJPG'));
if(!empty($img_path))
$form->addElement('static','thumbimage',get_lang('Preview'),'<img style="width:100px;" src="'.$img_path.'">');
//$form->addElement('checkbox','remove_img',null,get_lang('RemoveImage').' / '.get_lang('WithoutImage'),1);

// Company address
$form->add_html_editor('address', get_lang('CompanyAddress'), false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '600px', 'Height' => '90px'));
$form->add_html_editor('bank', get_lang('BankInformation'), false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '600px', 'Height' => '90px'));

$form->add_html_editor('additional', get_lang('AdditionalInformation'), false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '600px', 'Height' => '90px'));

$form->addElement('style_submit_button', ' ',get_lang('SaveSettings'), 'class="save"');

$values = array();
$values[$catalog_type->variable] = $catalog_type->selected_value;
$address = api_get_settings_options('companyAddress');
$bank = api_get_settings_options('invoiceBank');
$additional = api_get_settings_options('invoiceAdditionalInfo');
$values['address'] = $address[0]['value'];
$values['bank'] = $bank[0]['value'];
$values['additional'] = $additional[0]['value'];

$form->setDefaults($values);

if ($form->validate())
{
    $post = $form->exportValues();
    
    $logo = $_FILES['logo'];
    $settings = array();
    //$temp_path = api_get_path(SYS_PATH). 'archive/invoice/';
    $temp_path = api_get_path(SYS_PATH). 'main/upload/invoice/';
    //$temp_path = api_get_path(SYS_PATH). 'main/application/ecommerce/assets/files/invoice/';
    if(!empty($logo['tmp_name']))
    {
        umask(0);
        $img_tmp = $logo['tmp_name'];
        $img_pic = replace_dangerous_char($logo['name'], 'strict');	
        $old_logo = null;
        if(api_get_settings_options('invoiceLogo'))
        {
            $old_logo = api_get_settings_options('invoiceLogo');
            @unlink($temp_path.$old_logo['value']);
        }
        // Use the function for resize the image
        $mythumb = new appcore_library_thumbnail_Thumbnail();
        $mythumb->loadImage($img_tmp);
//        $mythumb->crop(150, 80);
        $mythumb->save($temp_path . $img_pic);
        $settings['invoiceLogo'] = $img_pic;
    }

    $settings['companyAddress'] = $post['address'];
    $settings['invoiceBank'] = $post['bank'];
    $settings['invoiceAdditionalInfo'] = $post['additional'];
    
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
    api_set_setting('e_commerce_catalog_type', $post['e_commerce_catalog_type']);
    header('Location: '.api_get_path(WEB_PATH).'main/admin/ecommerce_settings.php?action=show_message&message=SaveSuccessful');
    ob_end_flush();
}
if (isset ($_GET['action'])) {
    switch ($_GET['action']) {
        case 'show_message' :
                    if (!empty($_GET['message'])) {
                        Display :: display_confirmation_message2(stripslashes(get_lang($_GET['message'])),false,true);
                    }            
            break;
    }
    
}
// start the content div
echo '<div id="content">';
api_display_tool_title($tool_name);
$form->display();
echo '</div>';

//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';

// display the footer
Display::display_footer();