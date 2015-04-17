<?php
// Language files that should be included
$language_file = array('admin','courses');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationcourselist';

// including the global Dokeos file
require  dirname(__FILE__) . DIRECTORY_SEPARATOR .'../inc/global.inc.php';
// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once api_get_path(SYS_PATH) .'main/core/model/ecommerce/EcommerceCatalog.php';

$courseCode = (isset($_REQUEST['course_code']) && trim($_REQUEST['course_code']) != '' ) ? trim($_GET['course_code']) : NULL;

header("location: ".  api_get_path(WEB_PATH)."main/index.php?module=ecommerce&cmd=Course&course_code=".$courseCode);exit();
if( is_null( $courseCode ) )
{
    header('location: ecommerce_courses.php');
}

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();

// Setting the breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

$objCatalog = new EcommerceCatalog();

$tool_name = get_lang('Edit') . ' ' . get_lang('Course') ;

// Display the header
Display::display_header($tool_name);


//Actions
echo <<<EOF
<div class="actions">
EOF;
$objCatalog = new EcommerceCatalog();
$objCatalog->getCatalogSettings();

echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_settings.php">' . Display::return_icon('pixel.gif', get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('Settings') . '</a>';
switch ($objCatalog->currentValue->selected_value) {
    case CATALOG_TYPE_SESSIONS:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_list.php">' . Display::return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
    case CATALOG_TYPE_COURSES:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_courses.php">' . Display::return_icon('pixel.gif', get_lang('Courses'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
    case CATALOG_TYPE_MODULES:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_module_packs.php">' . Display::return_icon('pixel.gif', get_lang('ModulePacks'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
}
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Category">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncategory')) . get_lang('Categories') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_catalog.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncatalog')) . get_lang('Catalog') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_payment.php">' . Display::return_icon('pixel.gif', get_lang('Payment'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . get_lang('Payment') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('Invoices') . '</a>';
echo <<<EOF
</div>
EOF;

// start the content div
echo '<div id="content">';
$form = $objCatalog->getFormForCourseEcommerceByCode($courseCode);
if ( ! is_null( $form ))
{  if($form->validate()){
      echo '<script>window.location = "ecommerce_courses.php";</script>';
      exit;
   }
    $form->display();
}

echo '</div>';
//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';

// display the footer
Display::display_footer();
 

//creating form



