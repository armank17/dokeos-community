<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	Index of the admin tools
*
*	@package dokeos.help
==============================================================================
*/

$help['createcourse']['GeneralHelp'][] 		= '<a href="http://www.dokeos.com">http://www.dokeos.com</a>';
$help['createcourse']['ContextualHelp'][] 	= 'A little bit of text';
$help['createcourse']['ContextualHelp'][] 	= 'Even more text';

$help['profile'][]				= '<a href="http://www.dokeos.com">Profile help</a>';

// platform administration
$help['platformadministration'][]= '<a href="http://dokeos.com/en/manual/platformadministration">'.get_lang('PlatformAdministratorManual').'</a>';

$help['platformadministrationuserlist'][]= '<a href="http://dokeos.com/en/manual/platformadministration">'.get_lang('PlatformAdministratorManual').'</a>';
$help['platformadministrationuserlist'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers">'.get_lang('ManagingUsers').'</a>';
$help['platformadministrationuserlist'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/searchingusers">'.get_lang('SearchingUsers').'</a>';
$help['platformadministrationuserlist'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/searchingusersusingadvancedsearch">'.get_lang('SearchingUsersAdvanced').'</a>';
$help['platformadministrationuserlist'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/userlist">'.get_lang('UserList').'</a>';

$help['platformadministrationuseradd'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/addingusers">'.get_lang('AddUsers').'</a>';
$help['platformadministrationuseradd']['SeeAlso'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/importingusers">'.get_lang('ImportUsers').'</a>';
$help['platformadministrationuseradd']['SeeAlso'][]= '<a href="http://dokeos.com/en/manual/platformadministration/settings/">'.get_lang('SelfRegistration').'</a>';

$help['platformadministrationimportuser'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/importingusers">'.get_lang('ImportUsers').'</a>';
$help['platformadministrationimportuser']['SeeAlso'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/exportingusers">'.get_lang('ExportUsers').'</a>';

$help['platformadministrationexportuser'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/exportingusers">'.get_lang('AddUsers').'</a>';
$help['platformadministrationexportuser']['SeeAlso'][]= '<a href="http://dokeos.com/en/manual/platformadministration/managingusers/importingusers">'.get_lang('ImportUsers').'</a>';

$help['platformadministrationsettingssecurity']['GeneralHelp'][]='<a href="http://dokeos.com/en/manual/platformadministration">'.get_lang('PlatformAdministratorManual').'</a>';
$help['platformadministrationsettingssecurity']['GeneralHelp'][]='<a href="http://dokeos.com/en/manual/platformadministration/managingportal">'.get_lang('DokeosConfigSettings').'</a>';
$help['platformadministrationsettingssecurity']['ContextualHelp'][]='<a href="http://dokeos.com/en/manual/platformadministration/configuringportal/security">Configuration settings: Security</a>';


/**
 * 
 */
function get_help($helptopic){
	global $help;

	return $help[$helptopic];
}
?>
