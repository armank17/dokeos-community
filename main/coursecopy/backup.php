<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array('exercice', 'coursebackup', 'admin');

// setting the help
$help_content = 'backup';

// including the global Dokeos file
include ('../inc/global.inc.php');

// section for the tabs
$this_section=SECTION_COURSES;

// breadcrumbs
$interbreadcrumb[] = array ("url" => "../course_info/maintenance.php", "name" => get_lang('Maintenance'));

// Displaying the header
$nameTools = get_lang('Backup');
Display::display_tool_header($nameTools);

// Display the tool title
// api_display_tool_title($nameTools);

// Check access rights (only teachers allowed)
if (!api_is_allowed_to_edit())
{
	api_not_allowed(true);
}
// ACTIONS
echo '<div class="actions">';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'course_info/infocours.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . ' ' . get_lang('Settings') . '</a>';
echo '</div>';
// start the content div
echo '<div id="content">';
?>

<div class="section">
    <div class="sectiontitle"><a href="create_backup.php?<?php echo api_get_cidreq(); ?>"><?php echo get_lang('CreateBackup')  ?></a></div>
    <div class="sectioncontent">
    <?php echo get_lang('CreateBackupInfo') ?>
    </div>
</div>

<div class="section">
    <div class="sectiontitle"><a href="import_backup.php?<?php echo api_get_cidreq(); ?>"><?php echo get_lang('ImportBackup')  ?></a></div>
    <div class="sectioncontent">
    <?php echo get_lang('ImportBackupInfo') ?>
    </div>
</div>

<?php
// close the content div
echo '</div>';

// Display the footer
Display::display_footer();
?>
