<?php

// name of the language file that needs to be included
$language_file = array ('admin');

// resetting the course id
$cidReset = true;

$help_content = 'platformadministrationsessionadd';

require_once dirname(__FILE__) . '/../inc/global.inc.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalogModules.php';
// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(SYS_PATH) . 'main/inc/lib/xajax/xajax.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);
// obtaining catalog object

$ecommerceItemId = intval( $_GET['idEcommerceItem'] , 10) ;

$objCatalog = new EcommerceCatalogModules();
$objCatalog->getCatalogSettings();

if ( $objCatalog->currentValue->selected_value != CATALOG_TYPE_MODULES || $ecommerceItemId < 1 )
{
    header("location: " . api_get_path(WEB_PATH));
}

$objEcommerceItem = $objCatalog->getCourseByCode($ecommerceItemId);
$courseCode = $objCatalog->getCourseCodeByObj( $objEcommerceItem );

$form = new FormValidator( 'frmEcommerceModulePack', 'post', '#?idEcommerceItem=' .$ecommerceItemId );

if ( $form->validate() )
{
    $submitted = $form->getSubmitValues();

    if ( is_array( $submitted['date_start'] ) && (isset( $submitted['date_start']['d'] ) && isset( $submitted['date_start']['M'] ) && isset( $submitted['date_start']['Y'] )) )
    {
        $submitted['date_start'] = $submitted['date_start']['Y'] . '-' . $submitted['date_start']['M'] . '-' . $submitted['date_start']['d'];
    }
    if ( is_array( $submitted['date_end'] ) && (isset( $submitted['date_end']['d'] ) && isset( $submitted['date_end']['M'] ) && isset( $submitted['date_end']['Y'] )) )
    {
        $submitted['date_end'] = $submitted['date_end']['Y'] . '-' . $submitted['date_end']['M'] . '-' . $submitted['date_end']['d'];
    }

    CatalogueModuleModel::create()->saveItemEcommerce( $submitted );
    header("location: " . api_get_path(WEB_PATH) . '/main/admin/ecommerce_module_packs.php');
}


$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/vertical-tabs/js/jquery-jvert-tabs-1.1.4.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/vertical-tabs/css/jquery-jvert-tabs-1.1.4.css"/>';
$htmlHeadXtra[] = ' <script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/mselect/jquery.multiselect.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/mselect/jquery.multiselect.css"/>
<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/mselect/jquery.multiselect.filter.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/mselect/jquery.multiselect.filter.css"/>';

$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
Display::display_header($nameTools);
?>

<script type="text/javascript">
$(document).ready(function(){
    var tabsDiv = $('div#courseTabs');
    
    var indexofCourse = $('li[rel="<?php echo $courseCode;?>"]').index();
    indexofCourse = (indexofCourse < 0 ) ? 0 : indexofCourse;
        
     $('form#frmEcommerceModulePack div.row:last').after(tabsDiv);
	 $("#courseTabs").jVertTabs({
		    select: function(index){
		        $('div#courseTabs.vtabs div.vtabs-content-column div.vtabs-content-panel *').remove();
		    },
		    selected: indexofCourse 
});
});
</script>
<?php
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
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_courses.php">' . Display::return_icon('pixel.gif', get_lang('Courses'),array('class' => 'toolactionplaceholdericon toolactionadmincourse')) . get_lang('Courses') . '</a>';
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
?>
<div id="content">
<?php 
$form = $objCatalog->getFormForModulePack($form, $ecommerceItemId);
if ( ! is_null( $form ))
{
    $form->display();
}

        $courseLi = '';
        $courseTab = '';
        foreach( $objCatalog->getCoursesList() as $course ){
         $courseLi .= '<li rel="' . $course->code . '"><a href="'
          . api_get_path(WEB_PATH).'main/core/controller/ecommerce/EcommerceController.php?action=getCourseModulesList&course='
          . $course->code . '&itemId='.$ecommerceItemId .'">'.$course->title .'</a></li> ';
         $courseTab .= '<div id="#vtabs-content-' . $course->code . '"></div>';
        } ?>
        
<div id="courseTabs" style="width: 70%">
    <div>
        <ul>        
        <?php echo $courseLi;?>
        </ul>        
    </div>
    <div>
        <?php echo $courseTab;?>
	</div>
</div>

</div>
<?php Display::display_footer();