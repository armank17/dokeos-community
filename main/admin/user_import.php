<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* This tool allows platform admins to add users by uploading a CSV or XML file
* @package dokeos.admin
*/

// Language files that should be included
$language_file = array ('admin', 'registration');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationimportuser';

// including the global Dokeos file
require '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'classmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'import.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

// Section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// User permissions
api_protect_admin_script(true);

// Database table definitions

// Set this option to true to enforce strict purification for usenames.
$purification_option_for_usernames = false;

function validate_data($users) {
	global $defined_auth_sources;
	$errors = array();
	$usernames = array();
        
	// 1. Check if mandatory fields are set.
	$mandatory_fields = array('LastName', 'FirstName');
	if (api_get_setting('registration', 'email') == 'true') {
		$mandatory_fields[] = 'Email';
	}
	foreach ($users as $index => $user) {
		foreach ($mandatory_fields as $field) {
			if (empty($user[$field])) {
				$user['error'] = get_lang($field.'Mandatory');
				$errors[] = $user;
			}
		}

		// 2. Check username, first, check whether it is empty.
		if (!UserManager::is_username_empty($user['UserName'])) {
			// 2.1. Check whether username is too long.
			if (UserManager::is_username_too_long($user['UserName'])) {
				$user['error'] = get_lang('UserNameTooLong');
				$errors[] = $user;
			}
			// 2.2. Check whether the username was used twice in import file.
			if (isset($usernames[$user['UserName']])) {
				$user['error'] = get_lang('UserNameUsedTwice');
				$errors[] = $user;
			}
			// 2.3. Check whether username already exists.
			if (!UserManager::is_username_available($user['UserName'])) {
				$user['error'] = get_lang('UserNameAlreadyExists').' - '.$user['UserName'];
				$errors[] = $user;
			}
			$usernames[$user['UserName']] = 1;
                        
                        // 2.4 Check username characters
                        if (preg_match('/([^\w])+/', $user['UserName'])) {
                                $user['error'] = get_lang('OnlyLettersAndNumbersAllowed');
                                $errors[] = $user;
                        }
                        // 2.5. Check whether username is allready occupied.
			/*if (!UserManager::is_username_available($user['UserName'])) {
				$user['error'] = get_lang('UserNameNotAvailable');
				$errors[] = $user;
			}*/
		}
		// 3. Check status.
		if (isset($user['Status']) && !api_status_exists($user['Status'])) {
			
			$user['error'] = get_lang('WrongStatus');
			$errors[] = $user;
		}
		// 4. Check classname
		if (!empty($user['ClassName'])) {
			if (!ClassManager :: class_name_exists($user['ClassName'])) {
				$user['error'] = get_lang('ClassNameNotAvailable');
				$errors[] = $user;
			}
		}
		// 5. Check authentication source
		if (!empty($user['AuthSource'])) {
			if (!in_array($user['AuthSource'], $defined_auth_sources)) {
				$user['error'] = get_lang('AuthSourceNotAvailable');
				$errors[] = $user;
			}
		}
	}
	return $errors;
}

/**
 * Add missing user-information (which isn't required, like password, username etc).
 */
function complete_missing_data($user) {
	global $purification_option_for_usernames;
	// 1. Create a username if necessary.
	if (UserManager::is_username_empty($user['UserName'])) {
		$user['UserName'] = UserManager::create_unique_username($user['FirstName'], $user['LastName']);
	} else {
		$user['UserName'] = UserManager::purify_username($user['UserName'], $purification_option_for_usernames);
	}
	// 2. Generate a password if necessary.
	if (empty($user['Password'])) {
		$user['Password'] = api_generate_password();
	}
	// 3. Set status if not allready set.
	if (empty($user['Status'])) {
		$user['Status'] = 'user';
	}
	// 4. Set authsource if not allready set.
	if (empty($user['AuthSource'])) {
		$user['AuthSource'] = PLATFORM_AUTH_SOURCE;
	}
	return $user;
}

/**
 * Save the imported data
 * @param   array   List of users
 * @return  void
 * @uses global variable $inserted_in_course, which returns the list of courses the user was inserted in
 */
function save_data($users) {
	global $inserted_in_course;
	// Not all scripts declare the $inserted_in_course array (although they should).
	if (!isset($inserted_in_course)) {
		$inserted_in_course = array();
	}
	require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$send_mail = $_POST['sendMail'] ? 1 : 0;
	if (is_array($users)) {
		foreach ($users as $index => $user)	{
			$user = complete_missing_data($user);
			if(empty($user['Lang'])){
				$user['Lang'] = api_get_setting('PlatformLanguage');
			}
			$user['Status'] = api_status_key($user['Status']);
                        if (!UserManager::is_username_available($user['UserName'])) {
                          //$user_id = UserManager::get_user_id_from_username($user['UserName']);
                          //$updated = UserManager::update_user($user_id, $user['FirstName'], $user['LastName'], $user['UserName'], $user['Password'], $user['AuthSource'], $user['Email'], $user['Status'], $user['OfficialCode'], $user['PhoneNumber'], '', '', 1);
                        } else {
							
                          $user_id = UserManager :: create_user($user['FirstName'], $user['LastName'], $user['Status'], $user['Email'], $user['UserName'], $user['Password'], $user['OfficialCode'], $user['Lang'], $user['PhoneNumber'], '', $user['AuthSource']);
                        }

			if (!is_array($user['Courses']) && !empty($user['Courses'])) {
				$user['Courses'] = array($user['Courses']);
			}
                        //if course is active "enrolment automatic" enrolment users to course
                        $tbl_course =  Database::get_main_table(TABLE_MAIN_COURSE);
                        $query="select code from $tbl_course where default_enrolment = 1";
                        $res = Database::query($query);
                        while ($result = Database::fetch_array($res)) {
                            for($i=0;$i<count($result);$i++){
                                array_push($user['Courses'], $result[$i]);
                            }
                        }
			if (is_array($user['Courses'])) {
				foreach ($user['Courses'] as $index => $course) {
					if (CourseManager :: course_exists($course)) {
						CourseManager :: subscribe_user($user_id, $course,$user['Status']);
						$course_info = CourseManager::get_course_information($course);
						$inserted_in_course[$course] = $course_info['title'];
					}
					if (CourseManager :: course_exists($course, true)) {
						// Also subscribe to virtual courses through check on visual code.
						$list = CourseManager :: get_courses_info_from_visual_code($course);
						foreach ($list as $vcourse) {
							if ($vcourse['code'] == $course) {
								// Ignore, this has already been inserted.
							} else {
								CourseManager :: subscribe_user($user_id, $vcourse['code'],$user['Status']);
								$inserted_in_course[$vcourse['code']] = $vcourse['title'];
							}
						}
					}
				}
                                
			}
			if (!empty($user['ClassName'])) {
				$class_id = ClassManager :: get_class_id($user['ClassName']);
				ClassManager :: add_user($user_id, $class_id);
			}

			// Saving extra fields.
			global $extra_fields;
                        
			// We are sure that the extra field exists.
			foreach($extra_fields as $extras) {
                            if (isset($user[$extras[1]])) {
                                $key 	= $extras[1];
                                $value 	= trim($user[$extras[1]]);
                                $upd_extra = UserManager::update_extra_field_value($user_id, $key,$value);
                            }
			}

			if ($send_mail) {
                            $firstname  = $user['FirstName'];
                            $lastname   = $user['LastName'];
                            $email      = $user['Email'];
                            $username   = $user['UserName'];
                            $password   = $user['Password'];
							$user_lang  = $user['Lang'];
                            $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);		
                            $sender_name    = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
                            $email_admin    = api_get_setting('emailAdministrator'); 
                            $subject        = '['.api_get_setting('siteName').'] '.get_lang('YourReg').' '.api_get_setting('siteName');
                            UserManager::send_mail_to_new_user($recipient_name, $email,$subject,$username,$password,$sender_name, $email_admin, $user_lang);           
			}                        
		}
	}
}

/**
 * Read the CSV-file
 * @param string $file Path to the CSV-file
 * @return array All userinformation read from the file
 */
function parse_csv_data($file) {
	$users = Import :: csv_to_array($file);
	foreach ($users as $index => $user) {
		if (isset ($user['Courses'])) {
			$user['Courses'] = explode('|', trim($user['Courses']));
		}
		$users[$index] = $user;
	}
	return $users;
}
/**
 * XML-parser: handle start of element
 */
function element_start($parser, $data) {
	$data = api_utf8_decode($data);
	global $user;
	global $current_tag;
	switch ($data) {
		case 'Contact' :
			$user = array ();
			break;
		default :
			$current_tag = $data;
	}
}

/**
 * XML-parser: handle end of element
 */
function element_end($parser, $data) {
	$data = api_utf8_decode($data);
	global $user;
	global $users;
	global $current_value;
	switch ($data) {
		case 'Contact' :
			if ($user['Status'] == '5') {
				$user['Status'] = STUDENT;
			}
			if ($user['Status'] == '1') {
				$user['Status'] = COURSEMANAGER;
			}
			$users[] = $user;
			break;
		default :
			$user[$data] = $current_value;
			break;
	}
}

/**
 * XML-parser: handle character data
 */
function character_data($parser, $data) {
	$data = trim(api_utf8_decode($data));
	global $current_value;
	$current_value = $data;
}

/**
 * Read the XML-file
 * @param string $file Path to the XML-file
 * @return array All userinformation read from the file
 */
function parse_xml_data($file) {
	global $current_tag;
	global $current_value;
	global $user;
	global $users;
	$users = array();
	$parser = xml_parser_create('UTF-8');
	xml_set_element_handler($parser, 'element_start', 'element_end');
	xml_set_character_data_handler($parser, 'character_data');
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
	xml_parse($parser, api_utf8_encode_xml(file_get_contents($file)));
	xml_parser_free($parser);
	return $users;
}

$formSent = 0;
$errorMsg = '';
$defined_auth_sources[] = PLATFORM_AUTH_SOURCE;
if (is_array($extAuthSource)) {
	$defined_auth_sources = array_merge($defined_auth_sources, array_keys($extAuthSource));
}

$tool_name = get_lang('ImportUserListXMLCSV');
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

set_time_limit(0);
$extra_fields = Usermanager::get_extra_fields(0, 0, 5, 'ASC', true);

$user_id_error = array();
$error_message = '';

if ($_POST['formSent'] AND $_FILES['import_file']['size'] !== 0) {
	$file_type = $_POST['file_type'];
	Security::clear_token();
	$tok = Security::get_token();
	$allowed_file_mimetype = array('csv','xml');	
	$error_kind_file = false;

	$ext_import_file = substr($_FILES['import_file']['name'],(strrpos($_FILES['import_file']['name'],'.')+1));

	if (in_array($ext_import_file,$allowed_file_mimetype)) {
		if (strcmp($file_type, 'csv') === 0 && $ext_import_file==$allowed_file_mimetype[0]) {                     
			$users	= parse_csv_data($_FILES['import_file']['tmp_name']);	                        
                        if(api_get_setting('max_users_in_platform')){
                            global $_configuration;
                            if(is_numeric(api_get_setting('max_users_in_platform')) AND api_get_setting('max_users_in_platform') > 0 AND empty($_configuration['multiple_access_urls']) ){                
                            $check1 = api_check_user_import();            
                                if($check1['check']){
                                    $rest = ($check1['rest']) ? get_lang('OnlyCanImportTo').' '. $check1['rest']. ' '.get_lang('Users').' <br>' : ' ';
                                    $warning_message = $rest.get_lang('MaxUserInPlatform'). ': '.$check1['max_users'].'<br>'. get_lang('ContactToAdministratorForMoreDetails');
                                    $_SESSION["display_warning_message"] = $warning_message;
                                    header('Location: '.api_get_path(WEB_CODE_PATH).'admin/user_list.php');
                                    exit;
                                }  
                            }                        
                        }                       
			$errors = validate_data($users);		
			$error_kind_file = false;
		} elseif (strcmp($file_type, 'xml') === 0 && $ext_import_file==$allowed_file_mimetype[1]) { 
			$users = parse_xml_data($_FILES['import_file']['tmp_name']);                       
                        if(api_get_setting('max_users_in_platform')){
                            global $_configuration;
                            if(is_numeric(api_get_setting('max_users_in_platform')) AND api_get_setting('max_users_in_platform') > 0 AND empty($_configuration['multiple_access_urls']) ){                
                            $check1 = api_check_user_import();            
                                if($check1['check']){
                                    $rest = ($check1['rest']) ? get_lang('OnlyCanImportTo').' '. $check1['rest']. ' '.get_lang('Users').' <br>' : ' ';
                                    $warning_message = $rest.get_lang('MaxUserInPlatform'). ': '.$check1['max_users'].'<br>'. get_lang('ContactToAdministratorForMoreDetails');
                                    $_SESSION["display_warning_message"] = $warning_message;
                                    header('Location: '.api_get_path(WEB_CODE_PATH).'admin/user_list.php');
                                    exit;
                                }  
                            }                        
                        }
			$errors = validate_data($users);
			$error_kind_file = false;
		} else {
			$error_kind_file = true;
			$error_message = get_lang('YouMustImportAFileAccordingToSelectedOption');
			header('Location: '.api_get_self().'?warn='.urlencode($error_message).'&amp;file_type='.$file_type.'&amp;sec_token='.$tok);
			exit ();
		}
	} 
	else {
		$error_kind_file = true;
	}

	// List user id whith error.
	$user_id_error = array();
	if (is_array($errors)) {
		foreach ($errors as $my_errors) {
			$user_id_error[] = $my_errors['UserName'];
		}
	}
	if (is_array($users)) {
		foreach ($users as $my_user) {
			if (!in_array($my_user['UserName'], $user_id_error)) {
				$users_to_insert[] = $my_user;
			}
		}
	}
        
	$inserted_in_course = array();
	// this replace if (strcmp($_FILES['import_file']['type'], 'text/'.$file_type.'') === 0)
	if (strcmp($file_type, 'csv') === 0) {
		save_data($users_to_insert);		
	} elseif (strcmp($file_type, 'xml') === 0) {		
		save_data($users_to_insert);		
	} else {		
		$error_message = get_lang('YouMustImportAFileAccordingToSelectedOption');
	}	

	if (count($errors) > 0) {
		$see_message_import = get_lang('FileImportedJustUsersThatAreNotRegistered');
	} else {
		$see_message_import = get_lang('FileImported');
	}

	if (count($errors) != 0) {
		$warning_message = '<ul>';
		foreach ($errors as $index => $error_user) {
			$warning_message .= '<li><b>'.$error_user['error'].'</b>: ';
			$warning_message .= '</li>';
			}
		$warning_message .= '</ul>';
	} 

	// if the warning message is too long then we display the warning message trough a session
	if (api_strlen($warning_message) > 150) {
		$_SESSION['session_message_import_users'] = $warning_message;
		$warning_message = 'session_message';
	}

	if ($error_kind_file) {
		$error_message = get_lang('YouMustImportAFileAccordingToSelectedOption');
	} else {
		header('Location: '.api_get_path(WEB_CODE_PATH).'admin/user_list.php?action=show_message&warn='.urlencode($warning_message).'&message='.urlencode($see_message_import).'&sec_token='.$tok);
		exit;	
	}
}

// display the header
Display :: display_header($tool_name);

// display the tool title
//api_display_tool_title($tool_name);

echo '<div class="actions">';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_list.php">'.Display::return_icon('pixel.gif',get_lang('UserList'), array('class' => 'toolactionplaceholdericon toolactionadminusers')).get_lang('UserList').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_add.php">'.Display::return_icon('pixel.gif',get_lang('AddUsers'), array('class' => 'toolactionplaceholdericon toolactionaddusertocourse')).get_lang('AddUsers').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_export.php">'.Display::return_icon('pixel.gif',get_lang('Export'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('Export').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_fields.php">'.Display::return_icon('pixel.gif',get_lang('ManageUserFields'), array('class' => 'toolactionplaceholdericon toolactionsprofile')).get_lang('ManageUserFields').'</a>';
echo '</div>';

// start the content div
echo '<div id="content" class="maxcontent">';

if($_FILES['import_file']['size'] == 0 AND $_POST) {
	Display::display_error_message(get_lang('ThisFieldIsRequired'),false,true);
}


if (!empty($error_message)) {
	Display::display_error_message($error_message,false,true);
} else if (isset($_GET['warn'])) {
	$error_message = Security::remove_XSS($_GET['warn']);
	Display :: display_error_message($error_message,false,true);
}

$form = new FormValidator('user_import','post','user_import.php');
$form->addElement('header', '', $tool_name);
$form->addElement('hidden', 'formSent');
$form->addElement('file', 'import_file', get_lang('ImportFileLocation'));
$form->addRule('import_file', get_lang('ThisFieldIsRequired'), 'required');
$allowed_file_types = array ('xml', 'csv');
$form->addRule('import_file', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
$form->addElement('radio', 'file_type', get_lang('FileType'), 'XML (<a href="exemple.xml" target="_blank">'.get_lang('ExampleXMLFile').'</a>)', 'xml');
$form->addElement('radio', 'file_type', null, 'CSV (<a href="exemple.csv" target="_blank">'.get_lang('ExampleCSVFile').'</a>)', 'csv');
$form->addElement('radio', 'sendMail', get_lang('SendMailToUsers'), get_lang('Yes'), 1);
$form->addElement('radio', 'sendMail', null, get_lang('No'), 0);
$form->addElement('style_submit_button', 'submit', get_lang('Import'), 'class="save"');

// default values
$defaults['formSent'] = 1;
$defaults['sendMail'] = 0;
$defaults['file_type'] = 'xml';
$form->setDefaults($defaults);

// display the form
$form->display();

$list = array();
$list_reponse = array();
$result_xml = '';
$i = 0;
$count_fields = count($extra_fields);
if ($count_fields > 0) {
	foreach ($extra_fields as $extra) {
		$list[] = $extra[1];
		$list_reponse[] = 'xxx';
		$spaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$result_xml .= $spaces.'&lt;'.$extra[1].'&gt;xxx&lt;/'.$extra[1].'&gt;';
		if ($i != $count_fields - 1) {
			$result_xml .= '<br/>';
		}
		$i++;
	}
}

?>
<p><?php echo get_lang('CSVMustLookLike').' ('.get_lang('MandatoryFields').')'; ?> :</p>

		<blockquote>
		<pre>
		<b>LastName</b>;<b>FirstName</b>;<b>Email</b>;UserName;Password;AuthSource;OfficialCode;Lang;PhoneNumber;Status;
                <font style="color:red;"><?php if (count($list) > 0) echo implode(';', $list).';'; ?></font>Courses;<b>xxx</b>;<b>xxx</b>;<b>xxx</b>;xxx;xxx;
                <?php echo implode('/', $defined_auth_sources); ?>;xxx;english/french/spanish;xxx;user/teacher/drh;<font style="color:red;"><?php if (count($list_reponse) > 0) echo implode(';', $list_reponse).';'; ?></font>xxx1|xxx2|xxx3;<br />
		</pre>
		</blockquote>

		<p><?php echo get_lang('XMLMustLookLike').' ('.get_lang('MandatoryFields').')'; ?> :</p>

			<blockquote>
			<pre>
			&lt;?xml version=&quot;1.0&quot; encoding=&quot;<?php echo api_refine_encoding_id(api_get_system_encoding()); ?>&quot;?&gt;
			&lt;Contacts&gt;
			&lt;Contact&gt;
			<b>&lt;LastName&gt;xxx&lt;/LastName&gt;</b>
			<b>&lt;FirstName&gt;xxx&lt;/FirstName&gt;</b>
			&lt;UserName&gt;xxx&lt;/UserName&gt;
			&lt;Password&gt;xxx&lt;/Password&gt;
			&lt;AuthSource&gt;<?php echo implode('/', $defined_auth_sources); ?>&lt;/AuthSource&gt;
			<b>&lt;Email&gt;xxx&lt;/Email&gt;</b>
			&lt;OfficialCode&gt;xxx&lt;/OfficialCode&gt;
			&lt;Lang&gt;english/french/spanish&lt;/Lang&gt;
			&lt;PhoneNumber&gt;xxx&lt;/PhoneNumber&gt;
			&lt;Status&gt;user/teacher/drh<?php if ($result_xml != '') { echo '<br /><font style="color:red;">', $result_xml; echo '</font>'; } ?>&lt;/Status&gt;       
			&lt;Courses&gt;xxx1|xxx2|xxx3&lt;/Courses&gt;
			&lt;/Contact&gt;
			&lt;ClassName&gt;class 1&lt;/ClassName&gt;        
			&lt;/Contact&gt;
			&lt;/Contacts&gt;
		</pre>
		</blockquote>

<?php
// close the content div
echo '</div>';

// display the footer
Display :: display_footer();
?>
