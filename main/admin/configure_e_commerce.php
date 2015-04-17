<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * @author Cesar Pillihuaman
 * @package main/admin
 * 
 */

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// resetting the course id
$cidReset = true;


// including the global Dokeos file
require ('../inc/global.inc.php');

api_protect_admin_script();
// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once (api_get_path(CONFIGURATION_PATH).'profile.conf.php');
require_once(api_get_path(INCLUDE_PATH).'lib/mail.lib.inc.php');
require_once(api_get_path(INCLUDE_PATH).'lib/legal.lib.php');
require_once (api_get_path(LIBRARY_PATH).'language.lib.php');


// include ecommerce lib
require_once api_get_path(SYS_PATH) . 'main/core/controller/ecommerce/EcommerceController.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';


$titleEcommerce = get_lang('EcommerceTitle');
$ecommerceController = new EcommerceController();
$gatewayValues = $ecommerceController->gatewayData;

$gateway = $gatewayValues['gateway'];
$gatewayName = $gatewayValues['display_text'];

if ( ! $ecommerceController->objEcommerce->isEnabled() )
{

    header('location: index.php');
}

$form = $ecommerceController->getForm();

$gateway = ucfirst( $gateway );

if ( isset( $form ) && ! empty( $form ))
{
   if($form->validate()){
    $ecommerceController->save( $_POST , $_FILES );
   }
}

// Display the header
Display :: display_header($titleEcommerce);

echo <<<EOF
<div class="actions">
EOF;
    $objCatalog = new EcommerceCatalog();
    $objCatalog->getCatalogSettings();

    switch ( $objCatalog->currentValue->selected_value )
    {
        case CATALOG_TYPE_SESSIONS:
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_add.php">'.Display :: return_icon('pixel.gif', get_lang('AddSession'),array('class' => 'toolactionplaceholdericon toolactionadd')).get_lang('AddSession').'</a>';
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'),array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';        
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_export.php">'.Display::return_icon('pixel.gif',get_lang('ExportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportSessionListXMLCSV').'</a>';
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_import.php">'.Display::return_icon('pixel.gif',get_lang('ImportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionimportcourse')).get_lang('ImportSessionListXMLCSV').'</a>';	        
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'coursecopy/copy_course_session.php">'.Display::return_icon('pixel.gif',get_lang('CopyFromCourseInSessionToAnotherSession'),array('class' => 'toolactionplaceholdericon toolsettings')).get_lang('CopyFromCourseInSessionToAnotherSession').'</a>';
            break;
        case CATALOG_TYPE_COURSES:
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_courses.php">' . Display :: return_icon('pixel.gif', get_lang('Courses'),array('class' => 'toolactionplaceholdericon toolactionadmincourse')) . get_lang('Courses') . '</a>';
            break;
        case CATALOG_TYPE_MODULES:
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_module_packs.php">'.Display :: return_icon('pixel.gif', get_lang('ModulePacks'),array('class' => 'toolactionplaceholdericon toolactionassignment')). get_lang('ModulePacks').'</a>';
            echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_module_packs_add.php">'.Display :: return_icon('pixel.gif', get_lang('CreateModulePacks'),array('class' => 'toolactionplaceholdericon toolactionnewassignment')).get_lang('CreateModulePacks').'</a>';     
            break;
    }
echo <<<EOF
</div>
<div id="content">
<h2 class="fc-header-title-api" id="my-title">{$titleEcommerce} - {$gatewayName}</h2>
<div style="clear: both;"></div>
EOF;

if( is_a($form, 'FormValidator')){
    $form->display();
}

// close the content div
echo '</div>';

// Footer
Display::display_footer();
