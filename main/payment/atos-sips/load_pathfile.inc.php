<?php
/**
 * Load pathfile data
 */
require_once '../../inc/global.inc.php';

$d_logo     = api_get_path(WEB_CODE_PATH).'payment/atos-sips/logo/';
$f_default  = api_get_path(SYS_CODE_PATH).'payment/atos-sips/param/parmcom.defaut';
$f_param    = api_get_path(SYS_CODE_PATH).'payment/atos-sips/param/parmcom';
$f_certificate = api_get_path(SYS_CODE_PATH).'payment/atos-sips/param/certif';

$pathfile = api_get_path(SYS_CODE_PATH).'payment/atos-sips/param/pathfile';
if (file_exists($pathfile)) {
    $content = file_get_contents($pathfile);
    // add path to logo inside pathfile file
    if (strpos($content, 'D_LOGO!!') !== false) {
        $content = str_replace('D_LOGO!!', 'D_LOGO!'.$d_logo.'!', $content);
    }
    // add path to "default" inside pathfile file
    if (strpos($content, 'F_DEFAULT!!') !== false) {
        $content = str_replace('F_DEFAULT!!', 'F_DEFAULT!'.$f_default.'!', $content);
    }
    // add path to "param" inside pathfile file
    if (strpos($content, 'F_PARAM!!') !== false) {
        $content = str_replace('F_PARAM!!', 'F_PARAM!'.$f_param.'!', $content);
    }
    // add path to "certificate" inside pathfile file
    if (strpos($content, 'F_CERTIFICATE!!') !== false) {
        $content = str_replace('F_CERTIFICATE!!', 'F_CERTIFICATE!'.$f_certificate.'!', $content);
    }
    $saved = file_put_contents($pathfile, $content);
}
?>
