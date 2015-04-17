<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* This file contains the functions to handle the settings
*
* @author Patrick Cool
* @since Dokeos 1.8.6.2
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('admin' );

// resetting the course id
$cidReset=true;

// setting the global file that gets the general configuration, the databases, the languages, ...
include_once ('../inc/global.inc.php');

switch ($_POST['action']){
	case 'savecoursesettingdelegation':
		save_course_setting_delegation();
		break;
}
/**
 * This function saves the delegation of a platform setting into a course setting
 * This means that depending on the status a platform setting can (or can no longer) be overwritten 
 * by the setting of a course  
 *
 */
function save_course_setting_delegation()
{
	// checking the required values
	if (empty($_POST['variable'])){
		echo get_lang('PlatformSettingVariableNotDefined');
	}
	if (!in_array($_POST['status'],array('true','false'))){
		echo get_lang('PlatformSettingIllegalValue');
	} else {
		if ($_POST['status'] == 'true') {
			$status = 1;
		}
		if ($_POST['status'] == 'false') {
			$status = 0;
		}		
	}
	
	
	// Database table definition
	$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
	
	// creating and executing the sql statement for the change in the database
	$sql = "UPDATE $table_settings_current SET scope='".Database::escape_string($status)."' WHERE variable = '".Database::escape_string($_POST['variable'])."' AND scope<>'-1'";
	Database::query($sql, __FILE__, __LINE__);
	
	// return a correct language variable
	if ($status == 1){
		echo api_utf8_encode(get_lang('CourseSettingDelegationTrueClickToFalse'));
	} else {
		echo api_utf8_encode(get_lang('CourseSettingDelegationFalseClickToTrue'));
	}
	 
	
	
}

?>
