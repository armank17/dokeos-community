<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file='admin';
$cidReset=true;
include('../inc/global.inc.php');
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_admin_script();

include_once(api_get_path(LIBRARY_PATH).'fileManage.lib.php');

$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('Statistics');

// display the header
Display::display_header($tool_name);

// display the tool title
//api_display_tool_title($tool_name);

// display the footer
Display::display_footer();
?>
