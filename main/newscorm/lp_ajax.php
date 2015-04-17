<?php
/* For licensing terms, see /dokeos_license.txt */

/* 
 *  General actions with ajax inside learnpath 
 * 
 */

// including the global dokeos file
require_once '../inc/global.inc.php';

api_protect_course_script();

$actions = array('display_certificate');
$action = '';
if (isset($_GET['action']) && in_array($_GET['action'], $actions)) {
    $action = $_GET['action'];
}
$course_code = api_get_course_id();
$user_id = api_get_user_id();
switch ($action) {    
    case 'display_certificate':
        require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';
        $lp_id = intval($_GET['lp_id']);
        $obj_certificate = new CertificateManager();
        // check if user can get certificate
        $allowed = $obj_certificate->isUserAllowedGetCertificate($user_id, 'module', $lp_id, $course_code);
        
        if ($allowed) {        
            $obj_certificate->displayCertificate('html', 'module', $lp_id, $course_code);
        } else {
            echo get_lang('YouDidNotGetTheCertificate');
        }
        break;
}


?>
