<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array('exercice', 'coursebackup', 'admin');
// setting the help
$help_content = 'importbackup';
// including the global file
require_once ('../inc/global.inc.php');
// include additional libraries
include_once(api_get_path(LIBRARY_PATH) . 'fileManage.lib.php');
require_once('classes/CourseBuilder.class.php');
require_once('classes/CourseArchiver.class.php');
require_once('classes/CourseRestorer.class.php');
require_once('classes/CourseSelectForm.class.php');
require_once('classes/Course.class.php');
// Check access rights (only teachers are allowed here)
if (!api_is_allowed_to_edit()) {
    api_not_allowed(true);
}
//remove memory and time limits as much as possible as this might be a long process...
if (function_exists('ini_set')) {
    ini_set('memory_limit', '256M');
    ini_set('max_execution_time', 10800);
}
// section for the tabs
$this_section = SECTION_COURSES;
// breadcrumbs
$interbreadcrumb[] = array("url" => "../course_info/maintenance.php", "name" => get_lang('Maintenance'));
// Displaying the header
$nameTools = get_lang('ImportBackup');
Display::display_tool_header($nameTools);
// Display the tool title
// api_display_tool_title($nameTools);
// start the content div
echo '<div class="actions">';
//echo '<a href="../course_info/infocours.php">' . Display::return_icon('pixel.gif', get_lang('Groups'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Back') . '</a>';
echo Course :: show_menu_course_setting();
echo '</div>';
echo '<div id="content">';
//ini_get("upload_max_filesize")
if ((isset($_POST['action']) && $_POST['action'] == 'course_select_form' ) || (isset($_POST['import_option']) && $_POST['import_option'] == 'full_backup' )) {
    $error = false;
    if (isset($_POST['action']) && $_POST['action'] == 'course_select_form') {
        // partial backup here we recover the documents posted
        $course = CourseSelectForm::get_posted_course();
    } else {
        if($_POST['backup_type'] == 'server') {
            $filename = $_POST['backup_server'];
            $delete_file = false;
        } else {
            if($_FILES['backup']['error'] == 0){
                $filename = CourseArchiver::import_uploaded_file($_FILES['backup']['tmp_name']);
                if ($filename === false) {
                    $error = true;
                } else                {
                    $delete_file = true;
                }
            } else {
                $error = true;
            }
        }
        if(!$error) {
            // full backup
            $course = CourseArchiver::read_course($filename, $delete_file);
        }
    }
    if(!$error && $course->has_resources()) {
        $cr = new CourseRestorer($course);
        $cr->set_file_option($_POST['same_file_name_option']);
        $cr->restore();
        Display::display_confirmation_message(get_lang('ImportFinished'), false, true);
        '<a class="backup-link" href="' . api_get_path(WEB_COURSE_PATH).api_get_course_path() . '/index.php">&lt;&lt; ' . get_lang('CourseHomepage') . '</a>';
    } else {
        if(!$error){
            Display::display_warning_message(get_lang('NoResourcesInBackupFile') .
            '<a class="backup-link" href="import_backup.php?' . api_get_cidreq() . '">&lt;&lt; ' . get_lang('TryAgain') . '</a>', false, true);
        } elseif ($filename === false) {
            Display::display_error_message(get_lang('ArchivesDirectoryNotWriteableContactAdmin') .
            '<a class="backup-link" style="background-image:none; text-transform:uppercase; padding-left:10px;" href="import_backup.php?' . api_get_cidreq() . '">&lt;&lt; ' . get_lang('TryAgain') . '</a>', false, true);
        } else {
            Display::display_error_message(api_ucfirst(get_lang('UploadError')) .
            '<a class="backup-link" style="background-image:none; text-transform:uppercase; padding-left:10px;" href="import_backup.php?' . api_get_cidreq() . '">&lt;&lt; ' . get_lang('TryAgain') . '</a>', false, true);
        }
    }
    CourseArchiver::clean_backup_dir();
} elseif (isset($_POST['import_option']) && $_POST['import_option'] == 'select_items') {
    if ($_POST['backup_type'] == 'server') {
        $filename = $_POST['backup_server'];
        $delete_file = false;
    } else {
        $filename = CourseArchiver::import_uploaded_file($_FILES['backup']['tmp_name']);
        $delete_file = true;
    }
    $course = CourseArchiver::read_course($filename, $delete_file);
    if ($course->has_resources() && ($filename !== false)) {
        CourseSelectForm::display_form($course, array('same_file_name_option' => $_POST['same_file_name_option']));
    } elseif ($filename === false) {
        Display::display_error_message(get_lang('ArchivesDirectoryNotWriteableContactAdmin') .
        '<a class="backup-link" style="background-image:none; text-transform:uppercase; padding-left:10px;" href="import_backup.php?' . api_get_cidreq() . '">&lt;&lt; ' . get_lang('TryAgain') . '</a>', false, true);
    } else {
        Display::display_warning_message(get_lang('NoResourcesInBackupFile').
        '<a class="backup-link" style="background-image:none; text-transform:uppercase; padding-left:10px;" href="import_backup.php?' . api_get_cidreq() . '">&lt;&lt; ' . get_lang('TryAgain') . '</a>', false, true);
    }
} else {
    if (isset($_GET['cidReq'])) {
	$user = api_get_user_info();
	$backups = CourseArchiver::get_available_backups($is_platformAdmin ? null : $user['user_id']);
	$backups_available = (count($backups) > 0);

        api_display_tool_title(get_lang('SelectBackupFile'));

	include (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
	$form = new FormValidator('import_backup_form', 'POST', 'import_backup.php', '', 'multipart/form-data');

	$renderer = $form->defaultRenderer();
	$renderer->setElementTemplate('<div>{element}</div> ');

	$form->addElement('hidden', 'action', 'restore_backup');

	$form->addElement('radio', 'backup_type', '', get_lang('LocalFile') . " &nbsp&nbsp&nbsp( " . get_lang('UploadMaxSize') . ": " . ini_get("upload_max_filesize") . " )", 'local', 'id="bt_local" class="checkbox" onclick="javascript:document.import_backup_form.backup_server.disabled=true;document.import_backup_form.backup.disabled=false;"');
	$form->addElement('file', 'backup', '', 'style="margin-left: 50px;"');
	$form->addElement('html', '<br />');

	if($backups_available){
            $form->addElement('radio', 'backup_type', '', get_lang('ServerFile'), 'server', 'id="bt_server" class="checkbox" onclick="javascript:document.import_backup_form.backup_server.disabled=false;document.import_backup_form.backup.disabled=true;"');
            $options['null'] = '-';
            foreach ($backups as $index => $backup) {
                $options[$backup['file']] = $backup['course_code'] . ' (' . $backup['date'];
            }
            $form->addElement('select', 'backup_server', '', $options, 'style="margin-left: 50px;"');
            $form->addElement('html', '<script type="text/javascript">document.import_backup_form.backup_server.disabled=true;</script>');
	} else {
            $form->addElement('radio', '', '', '<i>' . get_lang('NoBackupsAvailable') . '</i>', '', 'disabled="true"');
	}

	$form->addElement('html', '<br /><br />');

	$form->addElement('radio', 'import_option', '', get_lang('ImportFullBackup'), 'full_backup', 'id="import_option_1" class="checkbox"');
	$form->addElement('radio', 'import_option', '', get_lang('LetMeSelectItems'), 'select_items', 'id="import_option_2" class="checkbox"');

	$form->addElement('html', '<br /><br />');

	$form->addElement('html', get_lang('SameFilename'));
	$form->addElement('html', '<br /><br />');
	$form->addElement('radio', 'same_file_name_option', '', get_lang('SameFilenameSkip'), FILE_SKIP, 'id="same_file_name_option_1" class="checkbox"');
	$form->addElement('radio', 'same_file_name_option', '', get_lang('SameFilenameRename'), FILE_RENAME, 'id="same_file_name_option_2" class="checkbox"');
	$form->addElement('radio', 'same_file_name_option', '', get_lang('SameFilenameOverwrite'), FILE_OVERWRITE, 'id="same_file_name_option_3" class="checkbox"');

	$form->addElement('html', '<br />');
	$form->addElement('html', '<div class="pull-bottom">');
	$form->addElement('style_submit_button', null, get_lang('ImportBackup'), 'class="save"');
        $form->addElement('html', '</div">');
        

	$values['backup_type'] = 'local';
	$values['import_option'] = 'full_backup';
	$values['same_file_name_option'] = FILE_OVERWRITE;
	$form->setDefaults($values);

	$form->add_progress_bar();

	$form->display();
    }else{
        Display::display_error_message(api_ucfirst(get_lang('UploadError')).
        '<a class="backup-link" href="import_backup.php?'.api_get_cidreq().'">&lt;&lt; '.get_lang('TryAgain').'</a>',false,true);
    }
}
// close the content div
echo '</div>';
// display the footer
Display::display_footer();