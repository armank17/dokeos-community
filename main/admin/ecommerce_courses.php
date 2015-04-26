<?php

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
require_once (api_get_path(LIBRARY_PATH) . 'sortabletable.class.php');
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';

//$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
//$htmlHeadXtra[] = '<script  type="text/javascript" src="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';
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

$htmlHeadXtra[] ='<script type="text/javascript">
$(document).ready(function() {

var id_orden = 1;

$(".data_table tbody tr:not(:first)").each(function(){
    
    $(this).attr("id", "recordsArray_"+id_orden);
    id_orden++;

});

var id_orden = 1;

$(".data_table tbody tr:not(:first)").each(function(){
    
    course_code = $("#recordsArray_"+id_orden).find("input").val();
    $(this).attr("id", "recordsArray_"+course_code);
    id_orden++;
});




// Make sortable the table by tr element
                $(".data_table tbody").sortable({
                    opacity: 0.6,
                    placeholder:"effectDragDrop",
                    items:"tr:not(:first)", // Not make sortable the first tr which is the header
                    //elements:$$(".noddrag"), handles:$$(".drag")}
                    cursor: "crosshair", 
                    //cancel: ".noddrag", 
                    handle: ".ddrag",
                    update: function() {
                        
                        var order = $(this).sortable("serialize");
                        var record = order.split("&");
                        var recordlen = record.length;
                        var disparr = new Array();
                        for (var i=0;i<(recordlen);i++) {
                            var recordval = record[i].split("=");
                            disparr[i] = recordval[1];			 
                        }

                        $.ajax({
                        type: "GET",
                        url: "'.api_get_path(WEB_AJAX_PATH).'ecommerce_courses.ajax.php?action=updatePosition&disporder="+disparr,
                        success: function(msg){}
                        })			
                    }   
		}).sortable("serialize");
                

});
</script> ';
//$htmlHeadXtra[] ='<script type="text/javascript">
//function Alert_Confim_Delete(link,title,text){
//        title || (title = getLang("ConfirmationDialog"));
//        text || (text = getLang("ConfirmYourChoice"));
//        window.parent.$.confirm(text,title, function() {
//            window.location.href = "ecommerce_courses.php?action=delete&course_code="+link;
//        }, false);
//}
//</script> ';
$objCatalog = new EcommerceCatalog();

// section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();


// Setting the breadcrumbs
$interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));


$tool_name = get_lang('Products');

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
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_list.php" class="active">' . Display::return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
    case CATALOG_TYPE_COURSES:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_courses.php" class="active">' . Display::return_icon('pixel.gif', get_lang('Courses'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
    case CATALOG_TYPE_MODULES:
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_module_packs.php" class="active">' . Display::return_icon('pixel.gif', get_lang('ModulePacks'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
        break;
}



echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_payment.php">' . Display::return_icon('pixel.gif', get_lang('Payment'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . get_lang('Payment') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('Invoices') . '</a>';
echo <<<EOF
</div>
EOF;

if(!isset($_GET['courses_ecommerce_column']) && empty($_GET['courses_ecommerce_column'])){
    unset($_SESSION['courses_ecommerce_column']);
}

if (isset($_POST['course'])){
    // Delete selected Products
			$course_codes = $_POST['course'];
			if (count($course_codes) > 0){
				foreach ($course_codes as $index => $course_code){
					CourseManager :: delete_from_payment($course_code);
				}
			}
}
if (isset($_GET['action'])){
    $code = $_GET['course_code'];
    $case = $_GET['action'];
   switch ($case){
       case 'delete':
          CourseManager :: delete_from_payment($code);
       break;
   }
}

// Create a sortable table with the course data
$get_course_data = array($objCatalog, 'getCourseEcommerceData');
$get_total_number_course_data = array($objCatalog, 'getTotalNumberCourseEcommerce');


$get_course_action_buttons = array($objCatalog, 'getCourseCatalogButtonList');
$table = new SortableTable('courses_ecommerce', $get_total_number_course_data, $get_course_data,9,20,'DESC');
$parameters = array();
//$style = '';
$table->set_additional_parameters($parameters);
$table->set_header(0, '', false, 'width="10px"');
$table->set_header(1, '', false, 'width="10px"','class=ddrag');

$table->set_header(2, get_lang('Category'), true, 'width="80px"','class=noddrag');

$table->set_header(3, get_lang('VisualCode'), true, 'width="20px"','class=noddrag');
$table->set_header(4, get_lang('TitleEcommerce'), true,'','class=noddrag');
$table->set_header(5, get_lang('Price'), true, 'width="50px"','class=noddrag');
$table->set_header(6, get_lang('Duration'), true, 'width="60px"','class=noddrag');
$table->set_header(7, get_lang('Catalog'), false, 'width="80px"','class=noddrag');

$table->set_header(8, get_lang('EcommerceAction'), false, 'width="60px"');
$table->set_column_filter(8, $get_course_action_buttons);
$table->set_form_actions(array('delete_courses' => get_lang('DeleteCourse')), 'course');

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