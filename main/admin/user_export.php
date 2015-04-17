<?php

/* For licensing terms, see /dokeos_license.txt */

/**
 * @package dokeos.admin
 */
// Language files that should be included
$language_file = 'admin';

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationexportuser';

// including the global Dokeos file
require '../inc/global.inc.php';

// including additional libraries
include (api_get_path(LIBRARY_PATH) . 'fileManage.lib.php');
include (api_get_path(LIBRARY_PATH) . 'export.lib.inc.php');
include (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');

// Section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// User permissions
api_protect_admin_script();

// Database table definitions
$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
$user_table = Database :: get_main_table(TABLE_MAIN_USER);
$course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);

$tool_name = get_lang('ExportUserListXMLCSV');

$interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

set_time_limit(0);

$courses = array();
$courses[''] = '--';
$sql = "SELECT code,visual_code,title FROM $course_table ORDER BY visual_code";

global $_configuration;
if ($_configuration['multiple_access_urls'] == true) {
    $tbl_course_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
    $access_url_id = api_get_current_access_url_id();
    if ($access_url_id != -1) {
        $sql = "SELECT code,visual_code,title FROM $course_table as c INNER JOIN $tbl_course_rel_access_url as course_rel_url
		ON (c.code = course_rel_url.course_code)
		WHERE access_url_id = $access_url_id
		ORDER BY visual_code";
    }
}
$result = Database::query($sql, __FILE__, __LINE__);
while ($course = Database::fetch_object($result)) {
    if (strlen($course->title) > 35) {
        $course->title = substr($course->title, 0, 35) . '...';
    }
    $courses[$course->code] = $course->visual_code . ' - ' . $course->title;
}
$form = new FormValidator('export_users');
$form->addElement('header', '', $tool_name);
$form->addElement('radio', 'file_type', get_lang('OutputFileType'), 'XML', 'xml');
$form->addElement('radio', 'file_type', null, 'CSV', 'csv');
$form->addElement('checkbox', 'addcsvheader', get_lang('AddCSVHeader'), get_lang('YesAddCSVHeader'), '1');
$form->addElement('select', 'course_code', get_lang('OnlyUsersFromCourse'), $courses, "id='export_course_code'");
$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
$form->setDefaults(array('file_type' => 'csv'));

if ($form->validate()) {
    global $userPasswordCrypted;

    $export = $form->exportValues();
    $file_type = $export['file_type'];
    $course_code = $export['course_code'];

    $sql = "SELECT  u.user_id 	AS UserId,
					u.lastname 	AS LastName,
					u.firstname 	AS FirstName,
					u.email 		AS Email,
					u.username	AS UserName,
					" . (($userPasswordCrypted != 'none') ? " " : "u.password AS Password, ") . "
					u.auth_source	AS AuthSource,
					u.status		AS Status,
					u.official_code	AS OfficialCode,
					u.phone		AS Phone";
    if (strlen($course_code) > 0) {
        $sql .= " FROM $user_table u, $course_user_table cu WHERE u.user_id = cu.user_id AND course_code = '$course_code' ORDER BY lastname,firstname";
        $filename = 'export_users_' . $course_code . '_' . date('Y-m-d_H-i-s');
    } else {
        global $_configuration;
        if ($_configuration['multiple_access_urls'] == true) {
            $tbl_user_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
            $access_url_id = api_get_current_access_url_id();
            if ($access_url_id != -1) {
                $sql.= " FROM $user_table u INNER JOIN $tbl_user_rel_access_url as user_rel_url
				ON (u.user_id= user_rel_url.user_id)
				WHERE access_url_id = $access_url_id
				ORDER BY lastname,firstname";
            }
        } else {
            $sql .= " FROM $user_table u ORDER BY lastname,firstname";
        }
        $filename = 'export_users_' . date('Y-m-d_H-i-s');
    }
    require_once (api_get_path(LIBRARY_PATH) . 'usermanager.lib.php');
    $data = array();
    $extra_fields = Usermanager::get_extra_fields(0, 0, 5, 'ASC', true, true);
    if ($export['addcsvheader'] == '1' AND $export['file_type'] == 'csv') {
        if ($userPasswordCrypted != 'none') {
            $data[] = array('UserId', 'LastName', 'FirstName', 'Email', 'UserName', 'AuthSource', 'Status', 'OfficialCode', 'Phone');
        } else {
            $data[] = array('UserId', 'LastName', 'FirstName', 'Email', 'UserName', 'Password', 'AuthSource', 'Status', 'OfficialCode', 'Phone');
        }

        foreach ($extra_fields as $extra) {
            //$data[0][]=str_replace(' ','',trim(ucwords($extra[3])));
            $data[0][] = $extra[1];
        }
    }
    $res = Database::query($sql, __FILE__, __LINE__);
    while ($user = Database::fetch_array($res, 'ASSOC')) {
        $student_data = UserManager :: get_extra_user_data($user['UserId'], true, false);
        foreach ($student_data as $key => $value) {
            $key = substr($key, 6);
            if (is_array($value))
                $user[$key] = $value[$key];
            else {
                $user[$key] = $value;
            }
        }
        $data[] = $user;
    }

    switch ($file_type) {
        case 'xml':
            Export::export_table_xml($data, $filename, 'Contact', 'Contacts');
            break;
        case 'csv':
            Export::export_table_csv($data, $filename);
            break;
    }
}

// display the header
Display :: display_header($tool_name);

// display the tool title
//api_display_tool_title($tool_name);

echo '<div class="actions">';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_list.php">' . Display::return_icon('pixel.gif', get_lang('UserList'), array('class' => 'toolactionplaceholdericon toolactionadminusers')) . get_lang('UserList') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_add.php">' . Display::return_icon('pixel.gif', get_lang('AddUsers'), array('class' => 'toolactionplaceholdericon toolactionaddusertocourse')) . get_lang('AddUsers') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_fields.php">' . Display::return_icon('pixel.gif', get_lang('ManageUserFields'), array('class' => 'toolactionplaceholdericon toolactionsprofile')) . get_lang('ManageUserFields') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_import.php">' . Display::return_icon('pixel.gif', get_lang('Import'), array('class' => 'toolactionplaceholdericon toolactionupload')) . get_lang('Import') . '</a>';
echo '</div>';

// start the content div
echo '<div id="content" class="maxcontent">';

// display the form
$form->display();

// close the content div
echo '</div>';

// display the footer
Display :: display_footer();
?>
