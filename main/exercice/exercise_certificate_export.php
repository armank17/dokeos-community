<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file=array('exercice','tracking','admin');

// including the global dokeos file
require_once '../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';

$obj_certificate = new CertificateManager();
$certif_available = $obj_certificate->isUserAllowedGetCertificate(api_get_user_id(), $_GET['certif_tool_type'], $_GET['certif_tool_id'], api_get_course_id());
if ($certif_available) {
    if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
        $obj_certificate->displayCertificatePdf($_GET['tpl_id']);
    }
}
exit;
