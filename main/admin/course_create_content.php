<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = 'admin';

// include the global Dokeos file
include ('../inc/global.inc.php');

// the section (for the tabs)
$this_section=SECTION_PLATFORM_ADMIN;

// access restrictions
api_protect_admin_script();

// tool name
$tool_name = get_lang('DummyCourseCreator');

// breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
Display::display_header($tool_name);
//api_display_tool_title($tool_name);
if(api_get_setting('server_type') != 'test')
{
	echo get_lang('DummyCourseOnlyOnTestServer');
}
elseif( isset($_POST['action']))
{
	require_once('../coursecopy/classes/DummyCourseCreator.class.php');
	$dcc = new DummyCourseCreator();
	$dcc->create_dummy_course($_POST['course_code']);
	echo get_lang('Done');
}
else
{
	echo get_lang('DummyCourseDescription');
	echo '<form method="post"><input type="hidden" name="course_code" value="'.$_GET['course_code'].'"/><input type="submit" name="action" value="'.get_lang('Ok').'"/></form>';
}
// display the footer
Display::display_footer();
?>
