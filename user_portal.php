<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.main
*/

// Language files that should be included
$language_file = array ('courses', 'index', 'admin');

// forcing the 'current course' reset, as we're not inside a course anymore
$cidReset = true;

// global Dokeos file
require_once './main/inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';
// the section (for the tabs)
$this_section = SECTION_COURSES;

// export certificate to pdf
$obj_certificate = new CertificateManager();
$certif_available = $obj_certificate->isUserAllowedGetCertificate(api_get_user_id(), $_GET['certif_tool_type'], $_GET['certif_tool_id']);
if ($certif_available) {
    if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
        $obj_certificate->displayCertificatePdf($_GET['tpl_id']);
    }
}

// Check if we have a CSS with tablet support
$css_info = array();
$css_info = api_get_css_info();
$css_type = !is_null($css_info['type']) ? $css_info['type'] : 'tablet';
require_once 'tablet_user_portal.php';
