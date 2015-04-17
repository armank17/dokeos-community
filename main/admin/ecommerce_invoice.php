<?php

/* For licensing terms, see /dokeos_license.txt */

/**
 * Display a list of courses and search for courses
 * @package dokeos.admin
 */
// Language files that should be included
$language_file = array('admin', 'invoice', 'index');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationinvoicelist';

// including the global Dokeos file
require dirname(__FILE__) . ('/../inc/global.inc.php');
// including additional libraries
//require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH) . 'sortabletable.class.php');
//require_once '../gradebook/lib/be/gradebookitem.class.php';
//require_once '../gradebook/lib/be/category.class.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceInvoice.php';


$objInvoice = new EcommerceInvoice();

// section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();


// Setting the breadcrumbs
$interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));


$tool_name = get_lang('Invoices');

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
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_payment.php">' . Display::return_icon('pixel.gif', get_lang('Payment'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . get_lang('Payment') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php" class="active">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('Invoices') . '</a>';
echo <<<EOF
</div>
EOF;

// Create a sortable table with the course data
$get_invoice_data = array($objInvoice, 'getInvoiceEcommerceData');
$get_total_number_invoice_data = array($objInvoice, 'getTotalNumberInvoicerEcommerce');

$table = new SortableTable('invoices_ecommerce', $get_total_number_invoice_data, $get_invoice_data, 4, 20, 'DESC');
$parameters = array();

$table->set_additional_parameters($parameters);
$table->set_header(0, 'N°', true, 'width="30px"');
$table->set_header(1, get_lang('Organization'), true, 'width="100px"');
$table->set_header(2, get_lang('FirstName'), true, 'width="100px"');
$table->set_header(3, get_lang('LastName'), true, 'width="200px"');
$table->set_header(4, get_lang('Products'), false);
$table->set_header(5, get_lang('Date'), true, 'width=70px');
$table->set_header(6, get_lang('Download'), false, 'width="80px" align="center"');

// start the content div
echo '<div id="content">';
api_display_tool_title($tool_name);
$table->display();
echo '</div>';

//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';

// display the footer
Display::display_footer();