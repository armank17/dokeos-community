<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Patrick Cool
* @since Dokeos 2.0
* @package dokeos.admin
*/

// Database Table Definitions
$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

$sqlsystemsettings 		= "SELECT * FROM $table_settings_current WHERE category='".Database::escape_string(Security::Remove_XSS($_GET['category']))."'";
$resultsystemsettings 	= Database::query($sqlsystemsettings, __FILE__, __LINE__);
while ($row = Database::fetch_array($resultsystemsettings)){
	echo '<div class="sectiontitle">'.get_lang($row['title']).'</div>';
	echo '<div class="sectioncomment">'.get_lang($row['comment']).'</div>';
	echo '<div class="sectionvalue">'.display_system_settings_values($row['variable'],$row['selected_value']).'</div>';
}

// display the footer
Display :: display_footer();

/**
 * This function displays the system settings. System settings are settings that cannot be changed by the user but can give information to the user
 * depending on the system setting the display will be differently. 
 * example: the system setting that stores the installation date is in the database stored as a timestamp but we want to display a nice looking date
 * example: the system setting that stores the number of users that is allowed by the contract is stored as an integer but we also want to display a graphical and percentage display
 *
 * @since Dokeos 2.0
 * @since June 2010
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 */
function display_system_settings_values($variable,$value){
	switch ($variable){
		case 'installation_date':
			return date('d-m-Y H:i:s',$value);
			break;
	}
}
?>
