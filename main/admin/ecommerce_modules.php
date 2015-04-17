<?php

/* For licensing terms, see /dokeos_license.txt */

/**
 * Display a list of courses and search for courses
 * @package dokeos.admin
 */
// Language files that should be included
$language_file = array('admin', 'courses');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationcourselist';

// including the global Dokeos file
require dirname(__FILE__) . ('/../inc/global.inc.php');
// including additional libraries
require_once (api_get_path(LIBRARY_PATH) . 'course.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH) . 'sortabletable.class.php');
require_once '../gradebook/lib/be/gradebookitem.class.php';
require_once '../gradebook/lib/be/category.class.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';

$htmlHeadXtra[] ='<script type="text/javascript">
$(document).ready(function() {

    $(".make_visible_and_invisible_product").attr("href","javascript:void(0)").click(function() {
        var id_link = $(this).attr("id");
        var id = id_link.replace("link_", "");
        var type = "course";
        var action = "visible";

        if($("#img_"+id_link).hasClass("actionvisible")){
            $("#img_"+id_link).removeClass("actionvisible").addClass("actioninvisible");
            action = "invisible";
        }else{
            $("#img_"+id_link).removeClass("actioninvisible").addClass("actionvisible");
            action = "visible";
        }
        $.post("ecommerce_product_ajax.php", { id: id, type: type, action:action });
    });

});
</script>';

$objCatalog = new EcommerceCatalog();

// section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();


// Setting the breadcrumbs
$interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));


$tool_name = get_lang('CourseList');

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

// Create a sortable table with the course data
$get_course_data = array($objCatalog, 'getCourseEcommerceData');
$get_total_number_course_data = array($objCatalog, 'getTotalNumberCourseEcommerce');

$get_course_action_buttons = array($objCatalog, 'getCourseCatalogButtonList');

$table = new SortableTable('courses_ecommerce', $get_total_number_course_data, $get_course_data);
$parameters = array();

$table->set_additional_parameters($parameters);
$table->set_header(0, '', false, 'width="10px"');
$table->set_header(1, get_lang('Code'), false, 'width="20px"');
$table->set_header(2, get_lang('Title'), true);
$table->set_header(3, get_lang('Category'), false, 'width="80px"');
$table->set_header(4, get_lang('Price'), true, 'width="50px"');
$table->set_header(5, get_lang('Duration'), false, 'width="60px"');
$table->set_header(6, get_lang('Catalog'), false, 'width="80px"');

$table->set_header(7, get_lang('Actions'), false, 'width="60px"');
$table->set_column_filter(7, $get_course_action_buttons);
$table->set_form_actions(array('delete_courses' => get_lang('DeleteCourse')), 'course');

// start the content div
echo '<div id="content">';
$table->display();
echo '</div>';

//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';

// display the footer
Display::display_footer();