<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Bart Mollet
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require_once ('../inc/global.inc.php');
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';

// including additional libraries
require_once(api_get_path(LIBRARY_PATH).'sessionmanager.lib.php');
require_once ('../inc/lib/xajax/xajax.inc.php');
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

$objCatalog = new EcommerceCatalog();




$catalogRow = $objCatalog->getCatalogue();

// setting breadcrumbs
$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => "session_list.php","name" => get_lang('SessionList'));

// obtaining catalog object


// Displaying the header
Display::display_header($nameTools);

echo '<div class="actions">';

$objCatalog->getCatalogSettings();




switch ( $objCatalog->currentValue->selected_value )
{
    case CATALOG_TYPE_SESSIONS:
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'),array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_export.php">'.Display::return_icon('pixel.gif',get_lang('ExportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportSessionListXMLCSV').'</a>';
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_import.php">'.Display::return_icon('pixel.gif',get_lang('ImportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionimportcourse')).get_lang('ImportSessionListXMLCSV').'</a>';
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'coursecopy/copy_course_session.php">'.Display::return_icon('pixel.gif',get_lang('CopyFromCourseInSessionToAnotherSession'),array('class' => 'toolactionplaceholdericon toolsettings')).get_lang('CopyFromCourseInSessionToAnotherSession').'</a>';
        break;
    case CATALOG_TYPE_COURSES:
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_courses.php">' . Display :: return_icon('pixel.gif', get_lang('Courses'),array('class' => 'toolactionplaceholdericon toolactionadmincourse')) . get_lang('Courses') . '</a>';
        break;
    case CATALOG_TYPE_MODULES:
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/ecommerce_module_packs.php">' . Display :: return_icon('pixel.gif', get_lang('ModulePacks'),array('class' => 'toolactionplaceholdericon toolactionassignment')) . get_lang('ModulePacks') . '</a>';
        break;
}

echo '</div>';

echo '<style type="text/css">
		div.row {
			width: 900px;
		}
		div.row div.label{
			width: 275px;
		}
		div.row div.formw{
			width: 600px;
		}
		</style>';

echo '<div id="content">';

$form->display();

//} //End of else statement
echo '</div>';

// display the footer
Display::display_footer();
?>
