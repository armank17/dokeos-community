<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* This tool allows platform admins to update course-user relations by uploading a CSVfile
* @package dokeos.admin
*/

// Language files that should be included
$language_file = array ('admin', 'registration');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationcourseuserimport';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// Including additional libraries.
require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'import.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

// Setting the section (for the tabs).
$this_section = SECTION_PLATFORM_ADMIN;

// Protecting the admin section.
api_protect_admin_script();


// Setting the breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

// no timelimit because this can take some time
set_time_limit(0);

// Creating the form.
$form = new FormValidator('course_user_import');
$form->addElement('header', '', get_lang('AddUsersToACourse').' CSV');
$form->addElement('file', 'import_file', get_lang('ImportCSVFileLocation'));
$form->addElement('checkbox', 'subscribe', get_lang('Action'), get_lang('SubscribeUserIfNotAllreadySubscribed'));
$form->addElement('checkbox', 'unsubscribe', '', get_lang('UnsubscribeUserIfSubscriptionIsNotInFile'));
$form->addElement('style_submit_button', 'submit',get_lang('Import'),'class="save"');

$error_message = $message = '';
$inserted_in_course = array();
if ($form->validate()) {		
        if ($_FILES['import_file']['size'] !== 0) {    
            $allowed_file_mimetype = array('csv');
            $ext_import_file = substr($_FILES['import_file']['name'],(strrpos($_FILES['import_file']['name'],'.')+1));
            if (!in_array($ext_import_file,$allowed_file_mimetype)) {
                $error_message = get_lang('YouMustImportAFileAccordingToSelectedOption');
            } else if (!isset($_POST['subscribe']) && !isset($_POST['unsubscribe'])) {
                $error_message = get_lang('YouMustSelectAnAction');
            } else {
                $users_courses = parse_csv_data($_FILES['import_file']['tmp_name']);
                $errors = validate_data($users_courses);
                $check = Security::check_token();
                if (count($errors) == 0) {                
                    if ($check) {
                        save_data($users_courses);
                        // Build the alert message in case there were visual codes subscribed to.
                        $warn = '';
                        if (count($inserted_in_course) > 1) {	        	
                            if ($_POST['subscribe']) {
                                $warn = get_lang('UsersSubscribedToBecauseVisualCode').': ';
                            } else {
                                $warn = get_lang('UsersUnsubscribedFromBecauseVisualCode').': ';
                            }	        	
                            // The users have been inserted in more than one course.
                            foreach ($inserted_in_course as $code => $info) {
                                $warn .= ' '.$info.' ('.$code.'),';
                            }
                            $warn = substr($warn,0,-1);
                        }
                        $message = get_lang('FileImported');
                    }                
                }
            }
        }        
        Security::clear_token();
}
// Displaying the header.
Display :: display_header(get_lang('AddUsersToACourse').' CSV');



// Displaying the tool title.
// api_display_tool_title(get_lang('AddUsersToACourse').' CSV');

echo '<div class="actions">';
CourseManager::show_menu_course_admin('import');
echo '</div>';

// start the content div
echo '<div id="content" class="maxcontent">';

// displaying feedback messages
if (!empty($error_message)) {
	Display :: display_error_message($error_message,false,true);
} else if (!empty($message)) {	
	if (!empty($warn)) {
		Display :: display_warning_message($warn,false,true);
	}
	Display :: display_normal_message($message,false,true);
}

if (count($errors) != 0) {
	$error_message = '<ul>';
	foreach ($errors as $index => $error_course) {
		$error_message .= '<li>'.get_lang('Line').' '.$error_course['line'].': <strong>'.$error_course['error'].'</strong>: ';
		$error_message .= $error_course['Code'].' '.$error_course['Title'];
		$error_message .= '</li>';
	}
	$error_message .= '</ul>';
	Display :: display_error_message($error_message,false,true);
}

// Displaying the form.
$token = Security::get_token();
$form->addElement('hidden','sec_token');
$form->setConstants(array('sec_token' => $token));
$form->display();
?>
<p><?php echo get_lang('CSVMustLookLike').' ('.get_lang('MandatoryFields').')'; ?> :</p>
<blockquote>
<pre>
<b>UserName</b>;<b>CourseCode</b>;<b>Status</b>
jdoe;course01;<?php echo COURSEMANAGER; ?>

adam;course01;<?php echo STUDENT; ?>
</pre>
<?php
echo COURSEMANAGER.': '.get_lang('Teacher').'<br />';
echo STUDENT.': '.get_lang('Student').'<br />';
?>
</blockquote>
<?php
// close the content div
echo '</div>';	

// Display the footer
Display :: display_footer();

/**
 * Validates the imported data.
 */
function validate_data($users_courses) {
	$errors = array ();
	$coursecodes = array ();
	foreach ($users_courses as $index => $user_course) {
		$user_course['line'] = $index +1;
		// 1. Check whether mandatory fields are set.
		$mandatory_fields = array ('UserName', 'CourseCode', 'Status');
		foreach ($mandatory_fields as $key => $field) {
			if (!isset($user_course[$field]) || strlen($user_course[$field]) == 0) {
				$user_course['error'] = get_lang($field.'Mandatory');
				$errors[] = $user_course;
			}
		}
		// 2. Check whether coursecode exists.
		if (isset ($user_course['CourseCode']) && strlen($user_course['CourseCode']) != 0) {                    
                        $courses = explode('|',$user_course['CourseCode']);                        
                        foreach ($courses as $course_code) {
                            $user_course['CourseCode'] = $course_code;
                            // 2.1 Check whethher code has been allready used by this CVS-file.
                            if (!isset($coursecodes[$user_course['CourseCode']])) {
                                    // 2.1.1 Check whether course with this code exists in the system.
                                    $course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
                                    $sql = "SELECT * FROM $course_table WHERE code = '".Database::escape_string($user_course['CourseCode'])."'";
                                    $res = Database::query($sql, __FILE__, __LINE__);
                                    if (Database::num_rows($res) == 0) {
                                            // check if it does not exist with visual code
                                            $sql = "SELECT code FROM $course_table WHERE visual_code = '".Database::escape_string($user_course['CourseCode'])."'";
                                            $rs = Database::query($sql, __FILE__, __LINE__);
                                            if($course = Database::fetch_array($rs))
                                            {
                                                    $coursecodes[$course['code']] = 1;
                                                    $user_course['CourseCode'] = $course['code'];
                                            }
                                            else
                                            {
                                                    $user_course['error'] = get_lang('CodeDoesNotExists');
                                                    $errors[] = $user_course;
                                            }
                                    } else {
                                            $coursecodes[$user_course['CourseCode']] = 1;
                                    }
                            }
                        }
		}
		// 3. Check whether username exists.
		if (isset ($user_course['UserName']) && strlen($user_course['UserName']) != 0)
		{
			if (UserManager::is_username_available($user_course['UserName'])) {
				$user_course['error'] = get_lang('UnknownUser');
				$errors[] = $user_course;
			}
		}
		// 4. Check whether status is valid.
		if (isset ($user_course['Status']) && strlen($user_course['Status']) != 0) {
			if ($user_course['Status'] != COURSEMANAGER && $user_course['Status'] != STUDENT) {
				$user_course['error'] = get_lang('UnknownStatus');
				$errors[] = $user_course;
			}
		}
	}
	return $errors;
}

/**
 * Saves imported data.
 */
function save_data($users_courses) {
	$user_table= Database::get_main_table(TABLE_MAIN_USER);
	$course_user_table= Database::get_main_table(TABLE_MAIN_COURSE_USER);
	$csv_data = array();	
        
	foreach ($users_courses as $index => $user_course) {                
            foreach (explode('|', $user_course['CourseCode']) as $course_code) {
                $csv_data[$user_course['UserName']][$course_code] = $user_course['Status'];
            }
	}
	
	foreach($csv_data as $username => $csv_subscriptions) {
		$user_id = 0;
		$sql = "SELECT * FROM $user_table u WHERE u.username = '".Database::escape_string($username)."'";
		$res = Database::query($sql, __FILE__, __LINE__);
		$obj = Database::fetch_object($res);
		$user_id = $obj->user_id;
		$to_subscribe = $to_unsubscribe = array();
		
		if ($_POST['subscribe']) {			
			$sql = "SELECT * FROM $course_user_table WHERE user_id = $user_id ";
			$res_suscribe = Database::query($sql, __FILE__, __LINE__);
			$db_subscriptions = array();
			while($obj_suscribe = Database::fetch_object($res_suscribe)) {			
				$db_subscriptions[$obj_suscribe->course_code] = $obj_suscribe->user_id;
			}
			$to_subscribe = array_diff(array_keys($csv_subscriptions),array_keys($db_subscriptions));
		}
	
		if ($_POST['unsubscribe']) {
			/*
			 * The unsubscription system was not working at all
			 * I have to suppose what it did : unsubscribe from listed courses users not listed for this course
			 */
			foreach($csv_subscriptions as $course_code => $course_status)
			{
				$courses_to_handle_unsubscribe[$course_code][] = $user_id;
			}
		} 

        global $inserted_in_course;
        if (!isset($inserted_in_course)) {
        	$inserted_in_course = array();
        }
		if($_POST['subscribe'])	{
			foreach($to_subscribe as $index => $course_code) {
                if(CourseManager :: course_exists($course_code)) {
         
                    CourseManager::add_user_to_course($user_id,$course_code,$csv_subscriptions[$course_code]);
                    $course_info = CourseManager::get_course_information($course_code);                    
                    $inserted_in_course[$course_code] = $course_info['title'];
                }
                if (CourseManager :: course_exists($course_code,true)) {
                    // Also subscribe to virtual courses through check on visual code.
                    $list = CourseManager :: get_courses_info_from_visual_code($course_code);
                    foreach ($list as $vcourse) {
                        if ($vcourse['code'] == $course_code) {
                            // Ignore, this has already been inserted.
                        } else {
                            CourseManager::add_user_to_course($user_id,$vcourse['code'],$csv_subscriptions[$course_code]);
                            $inserted_in_course[$vcourse['code']] = $vcourse['title'];
                        }
                    }
                }
			}			
		}
	}
		
	if($_POST['unsubscribe']) {
		
		foreach($courses_to_handle_unsubscribe as $course_code => $users_not_to_unsubscribe)
		{
			$sql = 'DELETE FROM '.$course_user_table.' 
						WHERE course_code LIKE "'.Database::escape_string($course_code).'"
						AND user_id NOT IN ('.implode(',',$users_not_to_unsubscribe).')';
			Database::query($sql, __FILE__, __LINE__);
		}
		
	}
}

/**
 * Reads CSV-file.
 * @param string $file Path to the CSV-file
 * @return array All course-information read from the file
 */
function parse_csv_data($file) {
	$courses = Import :: csv_to_array($file);
	return $courses;
}
?>