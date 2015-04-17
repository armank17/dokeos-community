<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('registration','admin','exercice','work');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationplatformnews';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

if (api_get_setting('enable_certificate') !== 'true') {
    api_not_allowed();
}

// actions
$allowed_actions = array('list', 'add', 'edit', 'delete', 'generateCertification');
$action = 'list';
if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions)) {
    $action = $_GET['action'];
}

// setting tool name
$tool_name = get_lang('CertificateTemplates');

// certificate object
$obj_certificate = new CertificateManager();

// template id
$tpl_id = isset($_GET['tpl_id'])?intval($_GET['tpl_id']):null;

// javascript extras
$htmlHeadXtra[] = '
    <style type="text/css">
        #certificatelang {
            margin-bottom:20px;
        }
    </style>
    <script type="text/javascript">
        function change_language(value){
            document.location.href = "'.api_get_self().'?action=list&lang="+value;
        }
        function generateCertificate(value)
        {
           document.location.href = "'.api_get_self().'?action=generateCertification&lang="+value;
        }
    </script>

';
// Displaying the header
Display::display_header($tool_name);

echo '<div class="actions">';

if ($action == 'list') {
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/certificate_templates.php?action=add">'.Display::return_icon('pixel.gif', get_lang('AddTemplate'),array('class'=>'actionplaceholdericon actionaddtemplate')).get_lang('AddTemplate').'</a>';
} else {
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/certificate_templates.php">'.Display::return_icon('pixel.gif', get_lang('CertificateTemplates'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('CertificateTemplates').'</a>';
}
echo '</div>';

echo '<div id="content">';

if (in_array($action, array('add', 'edit'))) {
    // edition
    $page_title = $action == 'add'?get_lang('AddTemplate'):get_lang('EditTemplate');
    api_display_tool_title($page_title);
    $obj_certificate->getForm($action, $tpl_id);

}
else
    {
    if($action == 'generateCertification')
    {
            $obj_certificate->generateCertificationFromEnglish($_REQUEST['lang']);
            // list
            api_display_tool_title($tool_name);
            $selected_lang = isset($_REQUEST['lang'])?$_REQUEST['lang']:api_get_interface_language();
            $obj_certificate->getLanguageForm($selected_lang);
            $certificates = $obj_certificate->getCertificatesList($selected_lang);
            $obj_certificate->displayCertificatesList($certificates);
    }
    else
    {
        if ($action == 'delete')
            {
                $deleted = $obj_certificate->deleteCertificate($tpl_id);
            }
            // list
            api_display_tool_title($tool_name);
            $selected_lang = isset($_REQUEST['lang'])?$_REQUEST['lang']:api_get_interface_language();
            $obj_certificate->getLanguageForm($selected_lang);
            $certificates = $obj_certificate->getCertificatesList($selected_lang);
            $obj_certificate->displayCertificatesList($certificates);
        }
    }


//End of else
echo '</div>';

// display the footer
Display::display_footer();
?>
