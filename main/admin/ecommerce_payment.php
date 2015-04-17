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
///home/cesar/sites/dokeospro22/main/payment/atos-sips/param/certif.fr.011223344551111
$file_tmp = api_get_path(SYS_CODE_PATH) . 'payment/atos-sips/param/certif.fr.011223344551111';
$table_payment_settings = Database::get_main_table( TABLE_MAIN_PAYMENT_SETTINGS );
$sql ="SELECT * FROM ".$table_payment_settings." WHERE value='011223344551111' OR value='".$file_tmp."' ";
$res = Database::query($sql, __FILE__, __LINE__);
$num = Database::num_rows($res);
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready(function() {

    $("#e_commerce_catalog_tax_text").keydown(function(){
        $("#tax_custom").click();
    });

    $("#e_commerce_catalog_tax_select").change(function(){
        $("#tax_percent").click();
    });

    $("#tax_custom").before("<br/>");
    $("#e_commerce_catalog_tax_text, #e_commerce_catalog_tax_select").after("<br/>");

    $("#payment_paypal").parent().append($(".paypal_opcs"));
    
    $("#payment_atos").parent().append($(".atos_opcs"));
    $("input[name=\"e_commerce\"]").change(function(){
        if($(this).val() == 2){
            $(".paypal_opcs").show(200);
            $(".atos_opcs").hide(200);
        }else{
            if($(this).val() == 1){
                $(".paypal_opcs").hide(200);
                $(".atos_opcs").show(200);            
            }else{
                $(".paypal_opcs").hide(200);
                $(".atos_opcs").hide(200);
            }
        }
    });
    
    $("#check_api_default").click(function(){
        if($("#check_api_default").is(":checked")){
            $(".divAtos").hide();
        }else{
            $(".divAtos").show();
        }
    });
   console.log($("#num_default_atos").val());
    if($("#num_default_atos").val()== 2){
        $(".divAtos").hide();
        $("#check_api_default").attr("checked", true);

     }
});
</script>';
$htmlHeadXtra[] = '<style></style>';

$objCatalog = new EcommerceCatalog();

// section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();


// Setting the breadcrumbs
$interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));


$tool_name = get_lang('Payment');

// Display the header
Display::display_header($tool_name);


//Actions
echo <<<EOF
<div class="actions">
EOF;
$objCatalog = new EcommerceCatalog();
$objCatalog->getCatalogSettings();

echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_settings.php">' . Display::return_icon('pixel.gif', get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('Settings') . '</a>';
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
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_payment.php" class="active">' . Display::return_icon('pixel.gif', get_lang('Payment'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . get_lang('Payment') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('Invoices') . '</a>';
echo <<<EOF
</div>
EOF;

$form = new FormValidator('settings', 'post', '','','enctype="multipart/form-data"');

$default_values = array();
$tbl_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
$tbl_settings_options = Database :: get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);
// Currency options
$variable = 'e_commerce_catalog_currency';
$sql = "SELECT * FROM {$tbl_settings_current} sc WHERE variable = '{$variable}' AND category = 'Ecommerce'";

$res = Database::query($sql, __FILE__, __LINE__);
$catalog_type = Database::fetch_object($res);
$default_values[$catalog_type->variable] = $catalog_type->selected_value;

$sql = "SELECT * FROM {$tbl_settings_options} so WHERE variable = '{$variable}'";

$group = array();
$res = Database::query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($res)) {
    $group[] = &$form->createElement('radio', $row['variable'], '', $row['display_text'], $row['value']);
}

//$form->addElement('header', null, get_lang('PaymentSection'));
$form->addGroup($group, null, get_lang($catalog_type->comment), '<br />');

// Currency options
$variable = 'e_commerce_catalog_decimal';
$sql = "SELECT * FROM {$tbl_settings_current} sc WHERE variable = '{$variable}' AND category = 'Ecommerce'";

$res = Database::query($sql, __FILE__, __LINE__);
$catalog_type = Database::fetch_object($res);
$default_values[$catalog_type->variable] = $catalog_type->selected_value;

$sql = "SELECT * FROM {$tbl_settings_options} so WHERE variable = '{$variable}'";
$group = array();
$sign = array(1 => ',', 2 => '.');
$res = Database::query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($res)) {
    $group[] = & $form->createElement('radio', $row['variable'], '', get_lang($row['display_text']) . ' ( 2500' . $sign[$row['value']] . '00 )', $row['value']);
}

$form->addGroup($group, null, get_lang($catalog_type->comment), '<br />');

// Payment systems
$variable = 'e_commerce';
$sql = "SELECT * FROM {$tbl_settings_current} sc WHERE variable = '{$variable}' AND category = 'Ecommerce'";

$res = Database::query($sql, __FILE__, __LINE__);
$catalog_type = Database::fetch_object($res);
$default_values[$catalog_type->variable] = $catalog_type->selected_value;

$sql = "SELECT * FROM {$tbl_settings_options} so WHERE variable = '{$variable}'";
$group = array();
$res = Database::query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($res)) {
    $group[] = &$form->createElement('radio', $row['variable'], '', get_lang($row['display_text']), $row['value'], array('id' => 'payment_' . strtolower($row['display_text'])));
}
$display = 'none';
if ($catalog_type->selected_value == 2)
    $display = 'block';
$display_paypal = 'none';
$display_atos = 'none';
switch($catalog_type->selected_value){
    case 1:
        $display_atos = 'block';
        $display_paypal = 'none';
        break;
    case 2:
        $display_atos = 'none';
        $display_paypal = 'block';
        break;
    default:
        $display_atos = 'none';
        $display_paypal = 'none';        
        break;
}
$sellerpaypalemail = api_get_payment_setting('email');
$apiusername = api_get_payment_setting('username');
$apipassword = api_get_payment_setting('password');
$apisignature = api_get_payment_setting('signature');
$workspace = api_get_payment_setting('workspace');
$pdt = api_get_payment_setting('pdt');

$prod = $test = '';
if ($workspace== '1'){
    $prod = 'checked="checked"';
}else{
    $test = 'checked="checked"';
}
$group[] = &$form->createElement('html', '<div class="paypal_opcs" style="display:' . $display_paypal . '; margin: 5px 20px; border: 1px dashed rgb(222, 222, 222); padding: 10px 5px; width: 545px;">
        <div class="label" style="margin: 2px 5px 0pt 0pt;">' . get_lang('WorkSpace') . '</div>
        <div class="formw">
            <input id="paypal_prod" type="radio" name="radType" value="1" ' . $prod . '>
            <label for="paypal_prod">' . get_lang('PaypalProduction') . '</label>
            <input id="paypal_test" type="radio" name="radType" value="0" ' . $test . '>
            <input id="num_default_atos" type="hidden" name="num_default_atos" value="'.$num.'" ' . $test . '>
            <label for="paypal_test">' . get_lang('PaypalTest') . '</label>
        </div>
        <div class="clear"></div>
        <div class="label" style="margin: 2px 5px 0pt 0pt;">' . get_lang('SellerPaypalEmail') . '</div>
        <div class="formw"><input type="text" name="txtEmail" size="30" value="' . $sellerpaypalemail . '"/></div>
        <div class="clear"></div>
        <div class="label" style="margin: 2px 5px 0pt 0pt;">' . get_lang('ApiUsername') . '</div>
        <div class="formw"><input type="text" name="txtApiUserName" size="30" value="' . $apiusername . '"/></div>
        <div class="clear"></div>
        <div class="label" style="margin: 2px 5px 0pt 0pt;">' . get_lang('ApiPassword') . '</div>
        <div class="formw"><input type="text" name="txtApiPassword" size="30" value="' . $apipassword . '"/></div>
        <div class="clear"></div>
        <div class="label" style="margin: 2px 5px 0pt 0pt;">' . get_lang('ApiSignature') . '</div>
        <div class="formw"><input type="text" name="txtApiSignature" size="30" value="' . $apisignature . '"/></div>
        <div class="clear"></div>
        <div class="label" style="margin: 2px 5px 0pt 0pt;">' . get_lang('PaymentPDT') . '</div>
        <div class="formw"><input type="text" name="txtPdt" size="30" value="' . $pdt . '"/></div>        
        <div class="clear"></div>
    </div>');
$group[] = &$form->createElement('html', '<div class="atos_opcs" style="display:' . $display_atos . '; margin: 5px 20px; border: 1px dashed rgb(222, 222, 222); padding: 10px 5px; width: 545px;">'.
    EcommerceFactory::getEcommerceObject(1)->getFormHtml()
    .'</div>');

$form->addGroup($group, null, get_lang($catalog_type->comment), '<br />');
//echo EcommerceFactory::getEcommerceObject(1)->getForm()->display();
// Taxes options
$variable = 'e_commerce_catalog_tax';
$sql = "SELECT * FROM {$tbl_settings_current} sc WHERE variable = '{$variable}' AND category = 'Ecommerce'";

$res = Database::query($sql, __FILE__, __LINE__);
$catalog_type = Database::fetch_object($res);
//$default_values[$catalog_type->variable] = $catalog_type->selected_value;

if (intval($catalog_type->selected_value) == 0)
    $default_values['radio_tax'] = 0;
if (in_array($catalog_type->selected_value, array('6', '19.6', '21'))) {
    $default_values['radio_tax'] = 2;
    $default_values['e_commerce_catalog_tax_select'] = $catalog_type->selected_value;
} else {
    $default_values['radio_tax'] = 1;
    $default_values['e_commerce_catalog_tax_text'] = $catalog_type->selected_value;
}

$group = array();
$status = array('6' => '6%', '19.6' => '19.6%', '21' => '21%');

$group[] = &$form->createElement('radio', 'radio_tax', null, get_lang('NoTax'), 0, array('id' => 'tax_no'));
$group[] = &$form->createElement('radio', 'radio_tax', null, get_lang('Tax') . ' %', 1, array('id' => 'tax_custom'));
$group[] = &$form->createElement('text', $variable . '_text', null, array('id' => 'e_commerce_catalog_tax_text', 'size' => '5'));
$group[] = &$form->createElement('radio', 'radio_tax', null, get_lang('Vat'), 2, array('id' => 'tax_percent'));
$group[] = &$form->createElement('select', $variable . '_select', get_lang('Percent'), $status, array('id' => 'e_commerce_catalog_tax_select'));

$form->addGroup($group, null, get_lang($catalog_type->comment), '&nbsp;');
$form->add_html_editor('terms_and_conditions', get_lang('TermsAndConditionsComment'), false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '600px', 'Height' => '90px'));

$form->addElement('style_submit_button', ' ', get_lang('SaveSettings'), 'class="save"');

if ($form->validate()) {
    $values = $form->exportValues();
    echo "<pre>";
    print_r($values);
    echo "</pre>";//die();
    $values['e_commerce_catalog_tax'] = 0;
    if ($values['radio_tax'] == 1)
        $values['e_commerce_catalog_tax'] = floatval($values['e_commerce_catalog_tax_text']);
    if ($values['radio_tax'] == 2)
        $values['e_commerce_catalog_tax'] = floatval($values['e_commerce_catalog_tax_select']);
    unset($values['radio_tax']);
    unset($values['e_commerce_catalog_tax_text']);
    unset($values['e_commerce_catalog_tax_select']);

    foreach ($values as $key => $value) {
        api_set_setting($key, $value);
    }

    if(isset($_POST['e_commerce'])){
        switch($_POST['e_commerce']){
            case 2:
                api_set_payment_setting('workspace', $_POST['radType'], 1);
                api_set_payment_setting('email', $_POST['txtEmail'], 2);
                api_set_payment_setting('username', $_POST['txtApiUserName'], 2);
                api_set_payment_setting('password', $_POST['txtApiPassword'], 2);
                api_set_payment_setting('signature', $_POST['txtApiSignature'], 2);
                api_set_payment_setting('pdt', $_POST['txtPdt'], 3);                
                break;
            case 1:
                EcommerceFactory::getEcommerceObject(1)->save($_POST, $_FILES);
                break;
        }

    }
    
    header('Location: ' . api_get_path(WEB_PATH) . 'main/admin/ecommerce_payment.php');
    ob_end_flush();
}

$default_values['terms_and_conditions'] = api_get_setting('terms_and_conditions');
$form->setDefaults($default_values);
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