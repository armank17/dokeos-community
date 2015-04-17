<?php

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * Main script for the documents tool
 *
 * This script allows the user to upload audio files.
 *
 * The user can : - navigate through files and directories.
 * 				 - move red5 server uploaded audio and add to documents tool 
 *
 *
 * @package dokeos.document
 * @author Cesar Edinson
  ==============================================================================
 */
// name of the language file that needs to be included
// name of the language file that needs to be included
$language_file[] = 'document';
$language_file[] = 'gradebook';


// including the global Dokeos file
require_once '../inc/global.inc.php';
require_once api_get_path(SYS_CODE_PATH).'inc/lib/audiorecorder/audiorecorder_conf.php';

// include additional libraries
require_once 'document.inc.php';
require_once '../inc/lib/usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';

// including additional libraries

require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';

define('DOKEOS_DOCUMENT', true);
api_protect_course_script(true);

$lp_id = intval($_REQUEST['lp_id']);
$action = Security::remove_XSS($_REQUEST['action']);
//$cidReq = $_REQUEST['cidReq'];
$id = intval($_REQUEST['id']);
$sound = Database::escape_string($_REQUEST['sound']);

$tbl_lp_item = Database::get_course_table(TABLE_LP_ITEM);
$sql = "update " . $tbl_lp_item . " set audio = '' where id=$id ";
$result = Database::query($sql, __FILE__, __LINE__);

/* Delete table */

$tbl_lp_doc = Database::get_course_table(TABLE_DOCUMENT);

$sql = " SELECT id FROM " . $tbl_lp_doc . " 
    WHERE  path = '/audio/$sound' and  title = '$sound' ";

$result = Database::query($sql, __FILE__, __LINE__);
while ($row = Database :: fetch_array($result)) {
   $tbl_lp_id = $row['id'];
}

$sql = "DELETE FROM " . $tbl_lp_doc. "  where id=$tbl_lp_id ";
$result = Database::query($sql, __FILE__, __LINE__);
unlink(api_get_path(SYS_PATH) . 'courses/' . api_get_course_path() . '/document/audio/' . $sound);

header("location: ".api_get_path(WEB_PATH)."main/newscorm/lp_controller.php?".api_get_cidreq()."&gradebook=&action=$action&lp_id=$lp_id");       
exit;
?>