<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/
// name of the language file that needs to be included
$language_file = array ('registration','admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessionimport';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require api_get_path(CONFIGURATION_PATH).'add_course.conf.php';
require_once api_get_path(LIBRARY_PATH).'add_course.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';

$htmlHeadXtra[] = '<style>html > body #content{min-height: 400px;}</style>';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

// setting breadcrumbs
$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));

// Database Table Definitions
$tbl_user					= Database::get_main_table(TABLE_MAIN_USER);
$tbl_course					= Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_course_user			= Database::get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_session				= Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_session_user			= Database::get_main_table(TABLE_MAIN_SESSION_USER);
$tbl_session_course			= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_course_user	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_access_url_session		= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);

// variable initialisation
$form_sent = 0;
$error_message = ''; // Avoid conflict with the global variable $error_msg (array type) in add_course.conf.php.
$tool_name = get_lang('UnsubscribeSessionUsers');

// Set this option to true to enforce strict purification for usenames.
$purification_option_for_usernames = false;

$inserted_in_course = array();

if ($_POST['formSent']) {
	if (isset($_FILES['import_file']['tmp_name'])) {
		$form_sent = $_POST['formSent'];
		$session_id = $_POST['session_name'];

		$content = file($_FILES['import_file']['tmp_name']);

		if (!api_strstr($content[0], ';')) {
			$error_message = get_lang('NotCSV');
		} else {
			$tag_names = array();

			foreach ($content as $key => $enreg) {
				$enreg = explode(';', trim($enreg));
				if ($key) {
					foreach ($tag_names as $tag_key => $tag_name) {
						$users[$key - 1][$tag_name] = $enreg[$tag_key];
					}
				} else {
					foreach ($enreg as $tag_name) {
						$tag_names[] = api_eregi_replace('[^a-z0-9_-]', '', $tag_name);
					}
					if (!in_array('UserName', $tag_names) || !in_array('Email', $tag_names)) {
						$error_message = get_lang('NoNeededData');
						break;
					}
				}
			}
					 
		}

		foreach ($users as $enreg) {
					$user_counter = 0;
					$course_counter = 0;

					$username = api_eregi_replace('"', '', $enreg['UserName']);
					$firstname = api_eregi_replace('"', '', $enreg['FirstName']);
					$lastname = api_eregi_replace('"', '', $enreg['LastName']);					
					$email = api_eregi_replace('"', '', $enreg['Email']);

					$sql = "SELECT user_id FROM $tbl_user WHERE username = '".$username."'";
					$res = Database::query($sql, __FILE__, __LINE__);
					$user_id = Database::result($res, 0, 0);

					$sql = "DELETE FROM $tbl_session_user WHERE id_user = ".$user_id." AND id_session = ".$session_id;
					Database::query($sql, __FILE__, __LINE__);

					$sql = "DELETE FROM $tbl_session_course_user WHERE id_user = ".$user_id." AND id_session = ".$session_id;
					Database::query($sql, __FILE__, __LINE__);
		}
                $_SESSION["display_confirmation_message"] = get_lang('FileImported');
                $_SESSION["display_error_message"] = $error_message;

		header('Location: session_users_unsubscribe.php?');
		//header('Location: session_users_unsubscribe.php?action=show_message&message='.urlencode(get_lang('FileImported').' '.$error_message));
		exit;
	}
	else {
		$error_message = get_lang('NoInputFile');
	}
}

// display the header
Display::display_header($tool_name);

$message = $_REQUEST['message'];

//if (!empty($error_message) || !empty($message)) {
//	//Display::display_confirmation_message($message.$error_message);	
//        $_SESSION['display_confirmation_message']=$message.$error_message;
//}

echo '<div class="actions">';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'),array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';  
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_add.php">'.Display :: return_icon('pixel.gif', get_lang('AddSession'),array('class' => 'toolactionplaceholdericon toolactionadd')).get_lang('AddSession').'</a>';     
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_category_list.php">'.Display :: return_icon('pixel.gif', get_lang('ListSessionCategory'),array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('ListSessionCategory').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_export.php">'.Display::return_icon('pixel.gif',get_lang('ExportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportSessionListXMLCSV').'</a>';	        
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_import.php">' . Display::return_icon('pixel.gif', get_lang('ImportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionimportcourse')) . get_lang('ImportSessionListXMLCSV') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'coursecopy/copy_course_session.php">'.Display::return_icon('pixel.gif',get_lang('CopyFromCourseInSessionToAnotherSession'),array('class' => 'toolactionplaceholdericon toolsettings')).get_lang('CopyFromCourseInSessionToAnotherSession').'</a>';	
echo '</div>';

if(empty($_SESSION["display_error_message"])){
    if(isset($_SESSION['display_confirmation_message'])){
        display::display_confirmation_message2($_SESSION['display_confirmation_message'], false,true);
        unset($_SESSION['display_confirmation_message']);
        unset($_SESSION["display_error_message"]);
    }
}
// start the content div
echo '<div id="content">';
/*
 * ===============================================
 * DISPLAY MESSAGE
 * ===============================================
 */
if(isset($_SESSION['display_normal_message'])){
display::display_normal_message($_SESSION['display_normal_message'], false,true);
unset($_SESSION['display_normal_message']);
}
if(isset($_SESSION['display_warning_message'])){
display::display_warning_message($_SESSION['display_warning_message'], false,true);
unset($_SESSION['display_warning_message']);
}
if(isset($_SESSION['display_error_message'])){
display::display_error_message($_SESSION['display_error_message'], false,true);
unset($_SESSION['display_error_message']);
    unset($_SESSION['display_confirmation_message']);
}
global $_configuration;
$access_url_id = api_get_current_access_url_id();

$sql = "SELECT id, name FROM $tbl_session";
if ($_configuration['multiple_access_urls'] == true && $access_url_id != "-1") {
	$sql .= " , $tbl_access_url_session WHERE id = session_id AND access_url_id = ".$access_url_id;
}

$res = Database::query($sql, __FILE__, __LINE__);
$sessions = array();
while($row = Database::fetch_array($res)){
	$sessions[$row['id']] = $row['name'];
}

?>

<form method="post" action="<?php echo api_get_self(); ?>" enctype="multipart/form-data" style="margin: 0px;">
<input type="hidden" name="formSent" value="1">
<div class="row"><div class="form_header"><?php echo $tool_name; ?></div></div>
<table border="0" cellpadding="5" cellspacing="0">


<tr>
  <td nowrap="nowrap" valign="top" align="right"><?php echo get_lang('Session'); ?> :</td>
  <td>
	<select name="session_name" size="1">
	<option><?php echo get_lang('SelectSession')?></option>
	<?php
	foreach($sessions as $key=>$session){
		echo '<option value="'.$key.'">'.$session.'</option>';
	}
	?>
	</select>
  </td>
</tr>
<tr>
  <td nowrap="nowrap" align="right"><?php echo get_lang('ImportFileLocationCSV'); ?> :</td>
  <td><input type="file" name="import_file" size="30"></td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td>
      <div class="pull-bottom"><button class="save" type="submit" name="name" value="<?php echo get_lang('UnsubscribeSessionUsers'); ?>"><?php echo get_lang('UnsubscribeSessionUsers'); ?></button></div>
  </td>
</tr>
</table>
</form>

<font color="gray">
<p><?php echo get_lang('CSVMustLookLike').' ('.get_lang('MandatoryFields').')'; ?> :</p>

<blockquote>
<pre>
<b>UserName</b>;FirstName;LastName;<b>Email</b>;
<b>xxx1</b>;xxx;xxx;<b>email</b>;
<b>xxx2</b>;xxx;xxx;<b>email</b>;
</pre>
</blockquote>

</font>
<?php
// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>
