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

$file = Security::remove_XSS($_REQUEST['title']);
$file_source = $dir_audio_source.$file.'.flv';
$file_mp3_info = explode('_hash_', $file);
$mp3_file_name = $file_mp3_info[0].'_'.time();
$file_target = $dir_audio_target.$mp3_file_name.'.mp3';
$file_mp3 = $mp3_file_name.'.mp3';
$db_file = '/audio/'.$file_mp3;

exec($ffmpeg . $file_source . ' -f mp3 ' . $file_target);

$file_size = filesize($file_target);

$table_document = Database::get_course_table(TABLE_DOCUMENT);
$sql = "INSERT INTO %s (path, title, filetype,size) VALUES ('%s', '%s', 'file',%s)";
$sql = sprintf($sql, $table_document, $db_file, $file_mp3, $file_size);
api_sql_query($sql, __FILE__, __LINE__);
$document_id = Database::get_last_insert_id();
api_item_property_update($_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', $_user['user_id'], $to_group_id);

if (!empty($_REQUEST['id'])) {        
    $id = intval($_REQUEST['id']);
    $action = Security::remove_XSS($_REQUEST['action']);
    $lp_id = intval($_REQUEST['lp_id']);
    $tbl_lp_item = Database::get_course_table(TABLE_LP_ITEM);
    $sql = "update " . $tbl_lp_item . " set audio = '".$file_mp3."' where id=$id ";
    $result = Database::query($sql, __FILE__, __LINE__);

    unlink($file_source);
    unlink($file_source.'.meta');// Remove the meta file created by Red5
    header("location: ".api_get_path(WEB_PATH)."main/newscorm/lp_controller.php?".api_get_cidreq()."&gradebook=&action=$action&lp_id=$lp_id");       
}
?>