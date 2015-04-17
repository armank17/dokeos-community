<?php

/* For licensing terms, see /dokeos_license.txt */

/**
 * @package dokeos.admin
 */
// Language files that should be included
$language_file = array('admin', 'registration');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationuseradd';

// including the global Dokeos file
require '../inc/global.inc.php';

$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jquery.timepicker/jquery-ui-timepicker-addon.js" language="javascript"></script>';
if (api_get_language_isocode()!== 'en')
    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-' . api_get_language_isocode() . '.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_CODE_PATH) . 'application/courseInfo/assets/js/infoModel.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.form.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/document/NiceUpload.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css">';

$htmlHeadXtra[] = '<script type="text/javascript">
    $(function(){
            $("#status_select option").each(function(index) {
         
            })
    });
</script>';

/* ================ Translate lang calendar ================ */
if (($isocode = api_get_language_isocode()) != "en")
    $htmlHeadXtra [] = '<script src="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-' . $isocode . '.js" type="text/javascript" language="javascript"></script>';
/* ================ ================ ================ */

// including additional libraries
$libpath = api_get_path(LIBRARY_PATH);
require_once $libpath . 'fileManage.lib.php';
require_once $libpath . 'fileUpload.lib.php';
require_once $libpath . 'usermanager.lib.php';
require_once $libpath . 'formvalidator/FormValidator.class.php';
require_once $libpath . 'image.lib.php';
require_once $libpath . 'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
require_once $libpath . 'timezone.lib.php';

$timeZoneList = TimeZone::TimeZoneList();
// Section for the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// User permissions
api_protect_admin_script(true);

define('DOKEOS_USER', true);

api_set_crop_token();

// Database table definitions
$table_admin = Database :: get_main_table(TABLE_MAIN_ADMIN);
$table_user = Database :: get_main_table(TABLE_MAIN_USER);
$database = Database::get_main_database();
$htmlHeadXtra[] = '
<script type="text/javascript">
    $(".ui-timepicker-div :not(*)");
    $(document).ready(function(){
        $("#expiration_date").datetimepicker({
            defaultDate: "+1y",
            numberOfMonths: 1,
            showOn: "button",
            buttonImage: "' . api_get_path(WEB_IMG_PATH) . 'calendar.gif",
            buttonImageOnly: true,
            //defaultDate: new Date(),
            dateFormat: "dd-mm-yy",
            timeFormat: "hh:mm:00 tt",
            currentText: getLang("Today"),
            closeText: getLang("Done")
        });
    });
</script>';

$htmlHeadXtra[] = "<script>
    $(function() {
        $('#picture_name').hide();
        var path_img = 'main/upload/users/';
        var url = '" . api_get_path(WEB_CODE_PATH) . "index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop';
        $('#user_picture').NiceInputUpload(165, 165, 'left', 303, true, 'progress_user');
        $('#user_picture').fileupload({
            url: url,
            dropZone: $(this),
            formData: {
                path: path_img,
                name: 'tempo_picture_user',
                min_width: 165,
                min_height: 155,
                max_width: 530,
                max_height: 500
            },
            done: function (e, data) {
                var ext = data.files[0].name;
                ext = (ext.substring(ext.lastIndexOf('.'))).toLowerCase();
                InfoModel.showActionDialogCrop(path_img, 165, 155, false, true, 'tempo_picture_user', 'tempo_picture_user', ext);
                $('#progress_user').hide();
            },
            progress: function (e, data) {
                $('#progress_user').show();
                $('#picture_name').val('');
                $('#imgPreviewNice').hide();
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress_user').css({width: progress + '%', background: 'skyblue'});
                $('#progress_user').html(progress + '%');
            }
        });
    });
</script>";

$htmlHeadXtra[] = '
<script type="text/javascript">
 function isNumberKey(evt){ 
var charCode = (evt.which) ? evt.which : evt.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)){
        return false;
    }else{
        return true;
    }    
}

$(document).ready(function() {
       var password = $("input[name=password[]]:checked", "#user_add").val()
      if(password != 0){
        $.ajax({
                type: "POST",
                url: "' . api_get_path(WEB_AJAX_PATH) . 'generate_password.ajax.php?action=generatepassword",
                beforeSend: function(){
                    
                },
                success: function(data){
                    $(".password").val(data);
                    
                }
            });
      }        
        $(".noautopassword").click(function(){        
            $(".password").val("");
        });
        $(".autopassword").click(function(){
             $.ajax({
                type: "POST",
                url: "' . api_get_path(WEB_AJAX_PATH) . 'generate_password.ajax.php?action=generatepassword",
                beforeSend: function(){
                    
                },
                success: function(data){
                    $(".password").val(data);
                    
                }
            });
        })    
});

function enable_expiration_date() { //v2.0
	document.user_add.radio_expiration_date[0].checked=false;
	document.user_add.radio_expiration_date[1].checked=true;
}

function password_switch_radio_button() {
	var input_elements = document.getElementsByTagName("input");
	for (var i = 0; i < input_elements.length; i++) {
		if (input_elements.item(i).name == "password[password_auto]" && input_elements.item(i).value == "0") {
			input_elements.item(i).checked = true;
		}
	}
}

function display_drh_list() {
	if(document.getElementById("status_user").value == ' . STUDENT . ') {
            document.getElementById("drh_list").style.display="block";
            document.getElementById("id_platform_admin").style.display="none";
	} else {
            document.getElementById("drh_list").style.display="none";
            document.getElementById("id_platform_admin").style.display="block";
            document.getElementById("drh_select").options[0].selected="selected";
	}
}
</script>';

// Additional javascript
if (api_get_setting('password_length') <> 0) {
    $password_length_rule = 'minLength: ' . api_get_setting('password_length') . ',';
}
if (api_get_setting('show_force_password_change') == 'true') {
    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_CODE_PATH) . 'inc/lib/javascript/jquery.strengthy-0.0.1.js" language="javascript"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript">
							var messages = [
									  "' . get_lang('PasswordTooShort') . '",
									  "' . get_lang('PasswordMustContainNumber') . '",
									  "' . get_lang('PasswordMustContainLowerUpper') . '",
									  "' . get_lang('PasswordMustContainSymbol') . '",
									  "' . get_lang('PasswordIsValid') . '",
									  "' . get_lang('PasswordShowPassword') . '"
									]
						
							$(document).ready(function() {
								$(".password").strengthy({
									' . $password_length_rule . '
									require: {
										numbers: ' . api_get_setting('password_rule', 'numbers') . ',
										upperAndLower: ' . api_get_setting('password_rule', 'camelcase') . ',
										symbols: ' . api_get_setting('password_rule', 'symbols') . '
									},
									errorClass: "form_error",
									validClass: "good-message",
									showToggle: true,
									msgs: messages
								});
							});
					</script>';
}

$htmlHeadXtra[] = '<style type="text/css">
	div.row div.formw{
		width: 80%;
	}
	</style>';
if (!empty($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

$interbreadcrumb[] = array('url' => 'index.php', 'name' => get_lang('PlatformAdmin'));
$tool_name = get_lang('AddUsers');

// Create the form
$form = new FormValidator('user_add');
// Begin table form
$form->addElement('html', '<div style="float:left;text-align: left; width: 65%;" >');
$form->addElement('header', '', $tool_name);
$form->addElement('text', 'picture_name', '', 'id="picture_name"');
if (api_is_western_name_order()) {
    // Firstname
    $form->addElement('text', 'firstname', get_lang('FirstName'), 'class="focus" id="firstname"');
    $form->applyFilter('firstname', 'html_filter');
    $form->applyFilter('firstname', 'trim');
    $form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');
    // Lastname
    $form->addElement('text', 'lastname', get_lang('LastName'));
    $form->applyFilter('lastname', 'html_filter');
    $form->applyFilter('lastname', 'trim');
    $form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');
} else {
    // Lastname
    $form->addElement('text', 'lastname', get_lang('LastName'));
    $form->applyFilter('lastname', 'html_filter');
    $form->applyFilter('lastname', 'trim');
    $form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');
    // Firstname
    $form->addElement('text', 'firstname', get_lang('FirstName'));
    $form->applyFilter('firstname', 'html_filter');
    $form->applyFilter('firstname', 'trim');
    $form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');
}
// Official code
$form->addElement('text', 'official_code', get_lang('OfficialCode'), array('size' => '40'));
$form->applyFilter('official_code', 'html_filter');
$form->applyFilter('official_code', 'trim');
// Email
$form->addElement('text', 'email', get_lang('Email'), array('size' => '40'));
$form->addRule('email', get_lang('EmailWrong'), 'email');
$form->addRule('email', get_lang('EmailWrong'), 'required');
$form->addRule('email', get_lang('EmailTaken'), 'email_available', $user_data['email']);
// Phone
$form->addElement('text', 'phone', get_lang('PhoneNumber'), array('size' => 20, 'onkeypress' => 'return isNumberKey(event)', 'id' => 'phone'));
// Picture
$form->addElement('file', 'picture', get_lang('AddPicture'), 'id=user_picture accept="image/jpeg, image/png, image/gif"');
//$allowed_picture_types = array('jpg', 'jpeg', 'png', 'gif');
//$form->addRule('picture', get_lang('OnlyImagesAllowed') . ' (' . implode(',', $allowed_picture_types) . ')', 'filetype', $allowed_picture_types);

//$form->addElement('html', '<div class="row"><div class="label"></div><div class="formw" style="max-width: 50%;"><div id="progress_user" style="height:30px; margin-bottom:10px; border-radius:5px; text-align:center; line-height:30px; font-weight:bold; font-size:14px;"></div></div></div>');
//$form->addElement('html', '<div class="row"><div class="label"></div><div class="formw"><img id="imgPreviewNice" src=""></div></div>');

// Time Zone
$timeZone = array();
foreach ($timeZoneList as $id => $value) {
    $timeZone[$id] = $value;
}
$form->addElement('select', 'timezone', get_lang('TimeZone'), $timeZone, array('id' => 'status_select'));
// Username
$form->addElement('text', 'username', get_lang('LoginName'), array('maxlength' => USERNAME_MAX_LENGTH));
$form->addRule('username', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('username', sprintf(get_lang('UsernameMaxXCharacters'), (string) USERNAME_MAX_LENGTH), 'maxlength', USERNAME_MAX_LENGTH);
$form->addRule('username', get_lang('OnlyLettersAndNumbersAllowed'), 'username');
$form->addRule('username', get_lang('UserTaken'), 'username_available', $user_data['username']);

// Password
$group = array();
$auth_sources = 0; //make available wider as we need it in case of form reset (see below)
if (count($extAuthSource) > 0) {
    $group[] = & HTML_QuickForm::createElement('radio', 'password_auto', null, get_lang('ExternalAuthentication') . ' ', 2);
    $auth_sources = array();
    foreach ($extAuthSource as $key => $info) {
        $auth_sources[$key] = $key;
    }
    $group[] = & HTML_QuickForm::createElement('select', 'auth_source', null, $auth_sources);
    $group[] = & HTML_QuickForm::createElement('static', '', '', '<br />');
}
$group[] = & HTML_QuickForm::createElement('radio', 'password_auto', get_lang('Password'), get_lang('AutoGeneratePassword') . '<br />', 1, array('class' => 'autopassword'));
$group[] = & HTML_QuickForm::createElement('radio', 'password_auto', 'id="radio_user_password"', null, 0, array('class' => 'noautopassword'));
$group[] = & HTML_QuickForm::createElement('password', 'password', null, array('onkeydown' => 'javascript: password_switch_radio_button();', 'class' => 'password'));
$form->addGroup($group, 'password', get_lang('Password'), '');
if (api_get_setting('show_force_password_change') == 'true') {
    $form->registerRule('passwordlength', 'function', 'passwordlength');
    $form->registerRule('passwordnumbers', 'function', 'passwordnumbers');
    $form->registerRule('passwordcamelcase', 'function', 'passwordcamelcase');
    $form->registerRule('passwordsymbols', 'function', 'passwordsymbols');
    $form->addRule('password', get_lang('PasswordTooShort'), 'passwordlength');
    $form->addRule('password', get_lang('PasswordMustContainSymbol'), 'passwordsymbols');
    $form->addRule('password', get_lang('PasswordMustContainLowerUpper'), 'passwordcamelcase');
    $form->addRule('password', get_lang('PasswordMustContainNumber'), 'passwordnumbers');
}


if (!api_is_session_admin()) {
    // Status
    $status = array();
    $status[COURSEMANAGER] = get_lang('Teacher');
    $status[STUDENT] = get_lang('Learner');
    $status[DRH] = get_lang('Drh');
    $status[SESSIONADMIN] = get_lang('SessionsAdmin');

    $form->addElement('select', 'status', get_lang('Status'), $status, array('id' => 'status_user', 'onchange' => 'javascript:display_drh_list();'));
    $form->addElement('select_language', 'language', get_lang('Language'));
    //drh list (display only if student)
    $display = ($_POST['status'] == STUDENT || !isset($_POST['status'])) ? 'block' : 'none';
    $form->addElement('html', '<div id="drh_list" style="display:' . $display . ';">');
    $drh_select = $form->addElement('select', 'hr_dept_id', get_lang('Drh'), array(), 'id="drh_select"');
    $drh_list = UserManager :: get_user_list(array('status' => DRH), api_sort_by_first_name() ? array('firstname', 'lastname') : array('lastname', 'firstname'));
    if (count($drh_list) == 0) {
        $drh_select->addOption('- ' . get_lang('ThereIsNotStillAResponsible', '') . ' -', 0);
    } else {
        $drh_select->addOption('- ' . get_lang('SelectAResponsible') . ' -', 0);
    }

    if (is_array($drh_list)) {
        foreach ($drh_list as $drh) {
            $drh_select->addOption(api_get_person_name($drh['firstname'], $drh['lastname']), $drh['user_id']);
        }
    }
    $form->addElement('html', '</div>');

    // Platform admin
    $group = array();
    $group[] = & HTML_QuickForm::createElement('radio', 'platform_admin', 'id="id_platform_admin"', get_lang('Yes'), 1);
    $group[] = & HTML_QuickForm::createElement('radio', 'platform_admin', 'id="id_platform_admin"', get_lang('No'), 0);
    $display = ($_POST['status'] == STUDENT || !isset($_POST['status'])) ? 'none' : 'block';
    $form->addElement('html', '<div id="id_platform_admin" style="display:' . $display . ';">');
    $form->addGroup($group, 'admin', get_lang('PlatformAdmin'), '&nbsp;');
    $form->addElement('html', '</div>');
}

// Send email
$group = array();
$group[] = & HTML_QuickForm::createElement('radio', 'send_mail', null, get_lang('Yes'), 1);
$group[] = & HTML_QuickForm::createElement('radio', 'send_mail', null, get_lang('No'), 0);
$form->addGroup($group, 'mail', get_lang('SendMailToNewUser'), '&nbsp;');
// Expiration Date
$form->addElement('radio', 'radio_expiration_date', get_lang('ExpirationDate'), get_lang('NeverExpires'), 0);
$group = array();
$group[] = & $form->createElement('radio', 'radio_expiration_date', null, get_lang('On'), 1);
//$group[] = & $form->createElement('datepicker', 'expiration_date', null, array('form_name' => $form->getAttribute('name'), 'onchange' => 'javascript: enable_expiration_date();'));
$group[] = $form->createElement('text', 'expiration_date', null, array('style' => 'margin-left: 6px;', 'id' => 'expiration_date', 'form_name' => $form->getAttribute('name'), 'onchange' => 'javascript: enable_expiration_date();'));
$form->addGroup($group, 'max_member_group', null, '', false);


if (is_portal_attribute_valid('actived_users')) {
    // Active account or inactive account
    $form->addElement('radio', 'active', get_lang('ActiveAccount'), get_lang('Active'), 1);
    $form->addElement('radio', 'active', '', get_lang('Inactive'), 0);
}

//$form->addElement('html', '<iframe width="0" height="0" name="upload_ajax" id="ajax_iframe" style="display:none;"></iframe>');
//session list
if (api_is_session_admin()) {
    $where = 'WHERE session_admin_id=' . intval(api_get_user_id());
    $where .= ' AND ( (session.date_start <= CURDATE() AND session.date_end >= CURDATE()) OR session.date_start="0000-00-00" ) ';
    $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
    $sql = "SELECT id,name,nbr_courses,date_start,date_end FROM $tbl_session $where ORDER BY name";
    $result = Database::query($sql, __FILE__, __LINE__);
    $a_sessions = Database::store_result($result);
    $session_list = array();
    $session_list[0] = get_lang('SelectSession');
    if (is_array($a_sessions)) {
        foreach ($a_sessions as $session) {
            $session_list[$session['id']] = $session['name'];
        }
    }

    //asort($session_list);
    //api_asort($session_list, SORT_STRING);
    api_natsort($session_list);

    $form->addElement('select', 'session_id', get_lang('Session'), $session_list);
}




// EXTRA FIELDS
$extra = UserManager::get_extra_fields(0, 50, 5, 'ASC');
$extra_data = UserManager::get_extra_user_data(0, true);

foreach ($extra as $id => $field_details) {
    if ($field_details[6] == 1) { // only show extra fields that are visible
        switch ($field_details[2]) {
            case USER_FIELD_TYPE_TEXT:
                $form->addElement('text', 'extra_' . $field_details[1], $field_details[3], array('size' => 40));
                $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
                $form->applyFilter('extra_' . $field_details[1], 'trim');
                break;
            case USER_FIELD_TYPE_TEXTAREA:
                $form->add_html_editor('extra_' . $field_details[1], $field_details[3], false, false, array('ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));
                //$form->addElement('textarea', 'extra_'.$field_details[1], $field_details[3], array('size' => 80));
                $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
                $form->applyFilter('extra_' . $field_details[1], 'trim');
                break;
            case USER_FIELD_TYPE_RADIO:
                $group = array();
                foreach ($field_details[9] as $option_id => $option_details) {
                    $options[$option_details[1]] = $option_details[2];
                    $group[] = & HTML_QuickForm::createElement('radio', 'extra_' . $field_details[1], $option_details[1], $option_details[2] . '<br />', $option_details[1]);
                }
                $form->addGroup($group, 'extra_' . $field_details[1], $field_details[3], '');
                break;
            case USER_FIELD_TYPE_SELECT:
                $options = array();
                foreach ($field_details[9] as $option_id => $option_details) {
                    $options[$option_details[1]] = $option_details[2];
                }
                $form->addElement('select', 'extra_' . $field_details[1], $field_details[3], $options, '');
                break;
            case USER_FIELD_TYPE_SELECT_MULTIPLE:
                $options = array();
                foreach ($field_details[9] as $option_id => $option_details) {
                    $options[$option_details[1]] = $option_details[2];
                }
                $form->addElement('select', 'extra_' . $field_details[1], $field_details[3], $options, array('multiple' => 'multiple'));
                break;
            case USER_FIELD_TYPE_DATE:
                $form->addElement('datepickerdate', 'extra_' . $field_details[1], $field_details[3], array('form_name' => 'user_add'));
                $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
                $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
                $form->setDefaults($defaults);
                $form->applyFilter('theme', 'trim');
                break;
            case USER_FIELD_TYPE_DATETIME:
                $form->addElement('datepicker', 'extra_' . $field_details[1], $field_details[3], array('form_name' => 'user_add'));
                $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
                $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
                $form->setDefaults($defaults);
                $form->applyFilter('theme', 'trim');
                break;
            case USER_FIELD_TYPE_DOUBLE_SELECT:
                $values = array();
                foreach ($field_details[9] as $key => $element) {
                    if ($element[2][0] == '*') {
                        $values['*'][$element[0]] = str_replace('*', '', $element[2]);
                    } else {
                        $values[0][$element[0]] = $element[2];
                    }
                }

                $group = '';
                $group[] = & HTML_QuickForm::createElement('select', 'extra_' . $field_details[1], '', $values[0], '');
                $group[] = & HTML_QuickForm::createElement('select', 'extra_' . $field_details[1] . '*', '', $values['*'], '');
                $form->addGroup($group, 'extra_' . $field_details[1], $field_details[3], '&nbsp;');
                if ($field_details[7] == 0)
                    $form->freeze('extra_' . $field_details[1]);

                // recoding the selected values for double : if the user has selected certain values, we have to assign them to the correct select form
                if (key_exists('extra_' . $field_details[1], $extra_data)) {
                    // exploding all the selected values (of both select forms)
                    $selected_values = explode(';', $extra_data['extra_' . $field_details[1]]);
                    $extra_data['extra_' . $field_details[1]] = array();

                    // looping through the selected values and assigning the selected values to either the first or second select form
                    foreach ($selected_values as $key => $selected_value) {
                        if (key_exists($selected_value, $values[0])) {
                            $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1]] = $selected_value;
                        } else {
                            $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1] . '*'] = $selected_value;
                        }
                    }
                }
                break;
            case USER_FIELD_TYPE_DIVIDER:
                $form->addElement('static', $field_details[1], '<br /><strong>' . $field_details[3] . '</strong>');
                break;
        }
    }
}

// Set default values
$defaults['admin']['platform_admin'] = 0;
$defaults['mail']['send_mail'] = 1;
$defaults['password']['password_auto'] = 1;
if (is_portal_attribute_valid('actived_users')) {
    $defaults['active'] = 1;
}
$defaults['expiration_date'] = date("d-m-Y 12:00:00 a", strtotime(date("d-m-Y 12:00:00 a", strtotime(date('d-m-Y 12:00:00 a'))) . " + 365 day"));
//date("Y-m-d", strtotime(date("Y-m-d", strtotime($StaringDate)) . " + 365 day"));
//$defaults['expiration_date'] = array();
//$days = api_get_setting('account_valid_duration');
//$time = strtotime('+' . $days . ' day');
//$defaults['expiration_date']['d'] = date('d', $time);
//$defaults['expiration_date']['F'] = date('m', $time);
//$defaults['expiration_date']['Y'] = date('Y', $time);
$defaults['radio_expiration_date'] = 0;
$defaults['status'] = STUDENT;

if (api_is_session_admin()) {
    $defaults['session_id'] = api_get_session_id();
}

$defaults = array_merge($defaults, $extra_data);
$form->setDefaults($defaults);

// Submit button
/*
  $form->addElement('style_submit_button', 'submit', get_lang('Add'), 'class="add"');
  $form->addElement('style_submit_button', 'submit_plus', get_lang('Add').'+', 'class="add"');
 */
$select_level = array();
//$html_results_enabled[] = FormValidator :: createElement ('style_submit_button', 'submit_plus', get_lang('Add').'+', 'class="add"');
// End table form and begin image
$form->addElement('html', '</div><br/><br/>');
//DEV
$form->addElement('html', '<div style="float:right;text-align: left; width: 35%;">' . Display::return_icon('pixel.gif', get_lang('AddUsers'), array('class' => 'imagehelp imagehelpuser')));

$form->addElement('html', '<br/><br/><br/><br/><br/><br/><br/><br/>');
/* $html_results_enabled[] = FormValidator :: createElement ('style_submit_button', 'submit', get_lang('Add'), 'class="add"');
  $form->addGroup($html_results_enabled); */

// Validate form
if ($form->validate()) {
    $check = Security::check_token('post');
    if ($check) {
        if (api_get_setting('max_users_in_platform')) {
            global $_configuration;
            if (is_numeric(api_get_setting('max_users_in_platform')) AND api_get_setting('max_users_in_platform') > 0 AND empty($_configuration['multiple_access_urls'])) {
               $check1 = api_check_user_import();            
                if ($check1['check']) {
                    $rest = ($check1['rest']) ? get_lang('OnlyCanImportTo') . ' ' . $check1['rest'] . ' ' . get_lang('Users') . ' <br>' : ' ';
                    $warning_message = $rest . get_lang('MaxUserInPlatform') . ': ' . $check1['max_users'] . '<br>' . get_lang('ContactToAdministratorForMoreDetails');
                    $_SESSION["display_warning_message"] = $warning_message;
                    header('Location: ' . api_get_path(WEB_CODE_PATH) . 'admin/user_list.php');
                    exit;
                }  
            }                        
        }
        $user = $form->exportValues();

        $picture_uri = '';

        $lastname = $user['lastname'];
        $firstname = $user['firstname'];
        $official_code = $user['official_code'];
        $email = $user['email'];
        $phone = $user['phone'];
        $username = $user['username'];
        $timezone = $user['timezone'];
        $status = intval($user['status']);
        $language = $user['language'];
        $platform_admin = intval($user['admin']['platform_admin']);
        $send_mail = intval($user['mail']['send_mail']);
        $hr_dept_id = intval($user['hr_dept_id']);
        if (count($extAuthSource) > 0 && $user['password']['password_auto'] == '2') {
            $auth_source = $user['password']['auth_source'];
            $password = 'PLACEHOLDER';
        } else {
            $auth_source = PLATFORM_AUTH_SOURCE;
            $password = $user['password']['password_auto'] == '1' ? api_generate_password(8) : $user['password']['password'];
        }
        if ($user['radio_expiration_date'] == '1') {
            //$expiration_date = $user['expiration_date'];
            $expiration_date = date("Y-m-d H:i:00", strtotime($user['expiration_date']));
        } else {
            $expiration_date = '0000-00-00 00:00:00';
        }
        $active = intval($user['active']);
        
        $user_id = UserManager::create_user($firstname, $lastname, $status, $email, $username, $password, $official_code, $language, $phone, $picture_uri, $auth_source, $expiration_date, $active, $hr_dept_id, null, null, null, null, $timezone);

        //adding to the session
        if (api_is_session_admin()) {
            $tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
            $tbl_session_rel_course_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
            $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
            $tbl_session_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_USER);

            $id_session = $user['session_id'];
            if ($id_session != 0) {
                $result = Database::query("SELECT course_code FROM $tbl_session_rel_course WHERE id_session='$id_session'", __FILE__, __LINE__);

                $CourseList = array();
                while ($row = Database::fetch_array($result)) {
                    $CourseList[] = $row['course_code'];
                }

                foreach ($CourseList as $enreg_course) {
                    Database::query("INSERT INTO $tbl_session_rel_course_rel_user(id_session,course_code,id_user) VALUES('$id_session','$enreg_course','$user_id')", __FILE__, __LINE__);
                    // updating the total
                    $sql = "SELECT COUNT(id_user) as nbUsers FROM $tbl_session_rel_course_rel_user WHERE id_session='$id_session' AND course_code='$enreg_course'";
                    $rs = Database::query($sql, __FILE__, __LINE__);
                    list($nbr_users) = Database::fetch_array($rs);
                    Database::query("UPDATE $tbl_session_rel_course SET nbr_users=$nbr_users WHERE id_session='$id_session' AND course_code='$enreg_course'", __FILE__, __LINE__);
                }

                Database::query("INSERT INTO $tbl_session_rel_user(id_session, id_user) VALUES('$id_session','$user_id')", __FILE__, __LINE__);

                $sql = "SELECT COUNT(nbr_users) as nbUsers FROM $tbl_session WHERE id='$id_session' ";
                $rs = Database::query($sql, __FILE__, __LINE__);
                list($nbr_users) = Database::fetch_array($rs);

                Database::query("UPDATE $tbl_session SET nbr_users= $nbr_users WHERE id='$id_session' ", __FILE__, __LINE__);
            }
        }

        // Attach the new user to trainings with default enrolment
        $enrolment_list = CourseManager::get_enrolment_and_no_enrolment_course_list(1);
        if (is_array($enrolment_list) && $status == STUDENT) {
            foreach ($enrolment_list as $course_id => $course_details) {
                if (CourseManager::add_user_to_course($user_id, $course_id, $status)) {
                    $send = api_get_course_setting('email_alert_to_teacher_on_new_user_in_course', $course_code);
                    if ($send == 1) {
                        CourseManager::email_to_tutor($_user['user_id'], $course_code, $send_to_tutor_also = false);
                    }
                    if ($send == 2) {
                        CourseManager::email_to_tutor($_user['user_id'], $course_code, $send_to_tutor_also = true);
                    }
                }
            }
        }
        
        $imageFileName = $user['picture_name'];
        if ($imageFileName != '') {
        
            $image_sys_path_temp = api_get_path(SYS_PATH) . 'main/upload/users/';
            $filename_temp = $image_sys_path_temp . $imageFileName;
        
            $image_sys_path = api_get_path(SYS_PATH) . 'main/upload/users/' . $user_id . '/';
            $name_image = str_replace('tempo_picture_user', 'picture_user_' . api_get_crop_token(), $imageFileName);
        
			mkdir($image_sys_path);
			
            $filename = $image_sys_path . $name_image;
			
            rename($filename_temp, $filename);
			
            $picture_uri = $name_image;
			
			UserManager::update_user($user_id, $firstname, $lastname, $username, $password, $auth_source, $email, $status, $official_code, $phone, $picture_uri, $expiration_date, $active, null, $hr_dept_id, null, $language);
		
            UserManager::resize_picture($filename, 22, $image_sys_path . "small_" . $name_image);
            UserManager::resize_picture($filename, 85, $image_sys_path . "medium_" . $name_image);
		}
        
        /*
        if (!empty($picture['name'])) {
            $picture_uri = UserManager::update_user_picture($user_id, $_FILES['picture']['name'], $_FILES['picture']['tmp_name']);
            UserManager::update_user($user_id, $firstname, $lastname, $username, $password, $auth_source, $email, $status, $official_code, $phone, $picture_uri, $expiration_date, $active, null, $hr_dept_id, null, $language);
        }
		*/
        $extras = array();
        foreach ($user as $key => $value) {
            if (substr($key, 0, 6) == 'extra_') { //an extra field
                $myres = UserManager::update_extra_field_value($user_id, substr($key, 6), $value);
            }
        }

        if ($platform_admin) {
            $sql = "INSERT INTO $table_admin SET user_id = '" . $user_id . "'";
            Database::query($sql, __FILE__, __LINE__);
        }
        if (!empty($email) && $send_mail) {
            // Process for send the mail to the new user
            $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);
            $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
            $email_admin = api_get_setting('emailAdministrator');
            $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
            UserManager::send_mail_to_new_user($recipient_name, $email, $subject, $username, $password, $sender_name, $email_admin, $language);
        }
        Security::clear_token();
        if (isset($user['submit_plus'])) {
            //we want to add more. Prepare report message and redirect to the same page (to clean the form)
            $tok = Security::get_token();
            header('Location: user_add.php?message=' . urlencode(get_lang('UserAdded')) . '&sec_token=' . $tok);
            exit();
        } else {
            $tok = Security::get_token();
            header('Location: user_list.php?action=show_message&message=' . urlencode(get_lang('UserAdded')) . '&sec_token=' . $tok);
            exit();
        }
    }
} else {
    if (isset($_POST['submit'])) {
        Security::clear_token();
    }
    $token = Security::get_token();
    $form->addElement('hidden', 'sec_token');
    $form->setConstants(array('sec_token' => $token));
}
// End image
$form->addElement('html', '</div>');
$form->addElement('style_submit_button', 'submit', get_lang('Add'), 'class="add"');

// Display the header
Display::display_header($tool_name);

echo '<div class="actions">';
//Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue'))
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_list.php">' . Display::return_icon('pixel.gif', get_lang('UserList'), array('class' => 'toolactionplaceholdericon toolactionadminusers')) . get_lang('UserList') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_fields.php">' . Display::return_icon('pixel.gif', get_lang('ManageUserFields'), array('class' => 'toolactionplaceholdericon toolactionsprofile')) . get_lang('ManageUserFields') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_export.php">' . Display::return_icon('pixel.gif', get_lang('Export'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('Export') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_import.php">' . Display::return_icon('pixel.gif', get_lang('Import'), array('class' => 'toolactionplaceholdericon toolactionupload')) . get_lang('Import') . '</a>';
echo '</div>';

// start the content div
echo '<div id="content">';

// display the tool title
//api_display_tool_title($tool_name);
if (!empty($message)) {
    Display::display_normal_message(stripslashes($message), false, true);
}
if (isset($_SESSION["display_warning_message"])) {
    Display :: display_warning_message($_SESSION["display_warning_message"], false, true);
    unset($_SESSION["display_warning_message"]);
}
// Display form
$form->display();

// close the content div
echo '</div>';

// Footer
Display::display_footer();
