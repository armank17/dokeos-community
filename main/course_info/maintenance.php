<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array ('admin','create_course', 'course_info', 'coursebackup');

// setting the help
$help_content = 'maintenance';

// including the global Dokeos file
require ('../inc/global.inc.php');


// including additional libraries


// setting the section (for the tabs)
$this_section = SECTION_COURSES;

$nameTools= get_lang('Maintenance');

// Access restrictions
api_block_anonymous_users();

// Display the header
Display :: display_tool_header($nameTools);

// display the tool title
// api_display_tool_title($nameTools);
// ACTIONS
echo '<div class="actions">';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'course_info/infocours.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . ' ' . get_lang('Settings') . '</a>';
echo '</div>';
// start the content div
echo '<div id="content">';
?>

<div class="section_white">
	<div class="sectiontitle"><?php Display::display_icon('pixel.gif', get_lang("backup"),array('class'=>'toolactionplaceholdericon toolactionbackup')); ?>&nbsp;&nbsp;<a href="../coursecopy/backup.php?<?php echo api_get_cidreq();?>"><?php echo get_lang("backup");?></a></div>
    <div class="sectioncontent">
        <table width="100%" cellspacing="2" cellpadding="10" border="0" align="center">
            <tbody>
                <tr>
                    <td valign="top">
                      <strong><a href="../coursecopy/create_backup.php?<?php echo api_get_cidreq();?>"><?php echo get_lang('CreateBackup')  ?></a></strong><br/><?php echo get_lang('CreateBackupInfo') ?><br/><br/>
                      <strong><a href="../coursecopy/import_backup.php?<?php echo api_get_cidreq();?>"><?php echo get_lang('ImportBackup')  ?></a></strong><br/><?php echo get_lang('ImportBackupInfo') ?>
                    </td>
                    <td width="180px" valign="top"><?php echo Display::return_icon('instructor-table.jpg', get_lang("backup"), array('align' => 'middle')); ?></td>
                </tr>
            </tbody>
        </table>
	</div>
</div>

<!--<div class="section">
	<div class="sectiontitle"><?php Display::display_icon('copy.gif', get_lang("CopyCourse")); ?>&nbsp;&nbsp;<a href="../coursecopy/copy_course.php?<?php echo api_get_cidreq();?>"><?php echo get_lang("CopyCourse");?></a></div>
	<?php echo get_lang("DescriptionCopyCourse"); ?>
</div>-->

<div class="section_white">
	<div class="sectiontitle"><?php Display::display_icon('pixel.gif', get_lang("recycle_course"),array('class'=>'toolactionplaceholdericon toolactionemptycourse')); ?>&nbsp;&nbsp;<a href="../coursecopy/recycle_course.php?<?php echo api_get_cidreq();?>"><?php echo get_lang("recycle_course");?></a></div>
    <div class="sectioncontent">
        <table width="100%" cellspacing="2" cellpadding="10" border="0" align="center">
            <tbody>
                <tr>
                    <td valign="top">
	                <?php echo get_lang("DescriptionRecycleCourse");?>
                    </td>
                    <td width="180px" valign="top"><?php echo Display::return_icon('Sitting05.jpg', get_lang("recycle_course"), array('align' => 'middle')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="section_white">
	<div class="sectiontitle"><?php Display::display_icon('pixel.gif',get_lang("DelCourse"),array('class'=>'toolactionplaceholdericon toolactiondelete_the_course')); ?>&nbsp;&nbsp;<a href="../course_info/delete_course.php?<?php echo api_get_cidreq();?>"><?php echo get_lang("DelCourse");?></a></div>
    <div class="sectioncontent">
        <table width="100%" cellspacing="2" cellpadding="10" border="0" align="center">
            <tbody>
                <tr>
                    <td valign="top">
                    <?php echo get_lang("DescriptionDeleteCourse");	?>
                    </td>
                    <td width="180px" valign="top"><?php echo Display::return_icon('SpeechBoring.jpg', get_lang("DelCourse"), array('align' => 'middle')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
// close the content div
echo '</div>';

// footer
Display::display_footer();
?>
