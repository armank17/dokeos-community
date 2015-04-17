<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// including the global dokeos file
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'sublanguagemanager.lib.php';
/*
 * search a term and return description from a glossary
 */
global $charset;

$new_language		= Security::remove_XSS($_REQUEST['new_language'],COURSEMANAGERLOWSECURITY);
$language_variable	= Security::remove_XSS($_REQUEST['variable_language']);
$file_id		= Security::remove_XSS($_REQUEST['file_id']);

if (isset($new_language) && isset($language_variable) && isset($file_id)) {
	$file_language = $language_files_to_load[$file_id].'.inc.php';
	$id_language = intval($_REQUEST['id']);
	$sub_language_id = intval($_REQUEST['sub']);
	$all_data_of_language=SubLanguageManager::get_all_information_of_sub_language($id_language,$sub_language_id);
	$dokeos_path_folder=api_get_path('SYS_LANG_PATH').$all_data_of_language['dokeos_folder'].'/'.$file_language;
	$all_file_of_directory=SubLanguageManager::get_all_language_variable_in_file($dokeos_path_folder);
	SubLanguageManager::add_file_in_language_directory ($dokeos_path_folder);
        $new_language = nl2br($new_language);

        $info_new_language = explode('<br />',$new_language);
        $big_string = "";
        $count_words = count($info_new_language);
        $count = 0;

        foreach ($info_new_language as $new_line) {
          $count++;
          // Last word
          $break_line="<br />";
          if ($count_words == $count) {
              $break_line="";
          }
          // Concat big strings in a line
          $new_line=Security::remove_XSS($new_line);
          $big_string.=trim($new_line.$break_line);
         // Clean first and last tag
         $first_tag = substr($big_string, 0,3);
         $last_tag = substr($big_string, -4);
         $big_string = ($first_tag == '<p>') ? substr($big_string, 3): $big_string;
         $big_string = ($last_tag == '</p>') ? substr($big_string, 0,-4): $big_string;
        }
        $new_info = $big_string;
	//update variable language
	$all_file_of_directory[$language_variable]="\"".mb_convert_encoding($new_info,$charset,'UTF-8')."\";";

	foreach ($all_file_of_directory as $key_value=>$value_info) {
		SubLanguageManager::write_data_in_file ($dokeos_path_folder,$value_info,$key_value);
	}
}