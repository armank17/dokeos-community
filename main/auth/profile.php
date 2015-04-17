<?php

/* For licensing terms, see /license.txt */
/**
 * This file displays the user's profile,
 * optionally it allows users to modify their profile as well.
 *
 * See inc/conf/profile.conf.php to modify settings
 *
 * @package dokeos.auth
 */
/**
 * Init section
 */
// Language files that should be included.
$language_file = array('registration', 'messages', 'userInfo');
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';

// including timezone library
require_once '../inc/lib/timezone.lib.php';

if (api_get_setting('allow_social_tool') == 'true' && api_get_setting('show_tabs', 'my_profile') == 'false') {
    $this_section = SECTION_SOCIAL;
} else {
    $this_section = SECTION_MYPROFILE;
}

$_SESSION['this_section'] = $this_section;

if (!(isset($_user['user_id']) && $_user['user_id']) || api_is_anonymous($_user['user_id'], true)) {
    api_not_allowed(true);
}

api_set_crop_token();

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
$htmlHeadXtra[] = '<script src="../inc/lib/javascript/tag/jquery.fcbkcomplete.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/tag/style.css" rel="stylesheet" type="text/css" />';

$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thickbox.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thickbox.css" type="text/css" media="projection, screen">';
$htmlHeadXtra[] = '
	<style type="text/css">
	div.row div.label{
		width: 10%;
	}
	div.row div.formw{
		width: 85%;
	}
        #edit_image {
                display:none !important;
        }
	</style>
	';

$htmlHeadXtra[] = '<script type="text/javascript">
function show_icon_edit(element_html) {
	ident="#edit_image";
	$(ident).show();
}

function hide_icon_edit(element_html)  {
	ident="#edit_image";
	$(ident).hide();
}

function confirmation(name) {
	if (confirm("' . get_lang('AreYouSureToDelete', '') . ' " + name + " ?"))
		{return true;}
	else
		{return false;}
}
function show_image(image,width,height) {
	width = parseInt(width) + 20;
	height = parseInt(height) + 20;
	window_x = window.open(image,\'windowX\',\'width=\'+ width + \', height=\'+ height + \'\');

}
function generate_open_id_form() {
	$.ajax({
		contentType: "application/x-www-form-urlencoded",
		beforeSend: function(objeto) {
		/*$("#div_api_key").html("Loading...");*/ },
		type: "POST",
		url: "' . api_get_path(WEB_AJAX_PATH) . 'user_manager.ajax.php?a=generate_api_key",
		data: "num_key_id="+"",
		success: function(datos) {
		 $("#div_api_key").html(datos);
		}
	});
}
 function isNumberKey(evt){
var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)){
        return false;
    }else{
        return true;
    }    
}

</script>';

$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_CODE_PATH) . 'application/courseInfo/assets/js/infoModel.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/document/NiceUpload.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css">';
$htmlHeadXtra[] = "<script>
    $(function() {
        $('#picture_name').hide();
        var path_img = 'main/upload/users/';
        var url = '" . api_get_path(WEB_CODE_PATH) . "index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop';
        $('#user_picture').NiceInputUpload(165, 165, 'left', 304, true, 'progress_user');
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
                $('#progress_user').css({width : progress + '%', background : 'skyblue'});
                $('#progress_user').html(progress + '%');
            }
        });
    });
</script>";


$interbreadcrumb[] = array('url' => '../auth/profile.php', 'name' => get_lang('ModifyProfile'));
if (!empty($_GET['coursePath'])) {
    $course_url = api_get_path(WEB_COURSE_PATH) . htmlentities(strip_tags($_GET['coursePath'])) . '/index.php';
    $interbreadcrumb[] = array('url' => $course_url, 'name' => Security::remove_XSS($_GET['courseCode']));
}


$warning_msg = '';
if (!empty($_GET['fe'])) {
    $warning_msg .= get_lang('UplUnableToSaveFileFilteredExtension');
    $_GET['fe'] = null;
}

$jquery_ready_content = '';
if (api_get_setting('allow_message_tool') == 'true') {
    $jquery_ready_content = <<<EOF
			$(".message-content .message-delete").click(function(){
				$(this).parents(".message-content").animate({ opacity: "hide" }, "slow");
				$(".message-view").animate({ opacity: "show" }, "slow");
			});
EOF;
}

/*
  -----------------------------------------------------------
  Configuration file
  -----------------------------------------------------------
 */
require_once api_get_path(CONFIGURATION_PATH) . 'profile.conf.php';

/*
  -----------------------------------------------------------
  Libraries
  -----------------------------------------------------------
 */
require_once api_get_path(LIBRARY_PATH) . 'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'image.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'social.lib.php';

$tool_name = is_profile_editable() ? get_lang('ModifProfile') : get_lang('ViewProfile');

$table_user = Database :: get_main_table(TABLE_MAIN_USER);

/*
  -----------------------------------------------------------
  Form
  -----------------------------------------------------------
 */

/*
 * Get initial values for all fields.
 */
$user_data = UserManager::get_user_info_by_id(api_get_user_id());
$array_list_key = UserManager::get_api_keys(api_get_user_id());
$id_temp_key = UserManager::get_api_key_id(api_get_user_id(), 'dokeos');
$value_array = $array_list_key[$id_temp_key];
$user_data['api_key_generate'] = $value_array;

if ($user_data !== false) {
    if (is_null($user_data['language'])) {
        $user_data['language'] = api_get_setting('platformLanguage');
    }
}

/*
 * Initialize the form.
 */
$form = new FormValidator('profile', 'post', api_get_self() . "?" . str_replace('&fe=1', '', $_SERVER['QUERY_STRING']), null, array('style' => 'width: 92%; float: ' . ($text_dir == 'rtl' ? 'right;' : 'left;')));

/* Make sure this is the first submit on the form, even though it is hidden!
 * Otherwise, if a user has productions and presses ENTER to submit, he will
 * attempt to delete the first production in the list. */
//if (is_profile_editable()) {
//	$form->addElement('style_submit_button', null, get_lang('SaveSettings'), 'class="save"', array('style' => 'visibility:hidden;'));
//}
//	SUBMIT (visible)
/* if (is_profile_editable()) {
  $form->addElement('style_submit_button', 'apply_change', get_lang('SaveSettings'), 'class="save"');
  } else {
  $form->freeze();
  } */

//THEME
if (is_profile_editable() && api_get_setting('user_selected_theme') == 'true') {
    $form->addElement('select_theme', 'theme', get_lang('Theme'));
    if (api_get_setting('profile', 'theme') !== 'true')
        $form->freeze('theme');
    $form->applyFilter('theme', 'trim');
}

if (api_is_western_name_order()) {
    //	FIRST NAME and LAST NAME
    $form->addElement('text', 'firstname', get_lang('FirstName'), array('size' => 40));
    $form->addElement('text', 'lastname', get_lang('LastName'), array('size' => 40));
} else {
    //	LAST NAME and FIRST NAME
    $form->addElement('text', 'lastname', get_lang('LastName'), array('size' => 40));
    $form->addElement('text', 'firstname', get_lang('FirstName'), array('size' => 40));
}
if (api_get_setting('profile', 'name') !== 'true') {
    $form->freeze(array('lastname', 'firstname'));
} else {
    $form->applyFilter(array('lastname', 'firstname'), 'stripslashes');
    $form->applyFilter(array('lastname', 'firstname'), 'trim');
    $form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');
    $form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');
}

//	USERNAME
$form->addElement('text', 'username', get_lang('UserName'), array('size' => 40));
if (api_get_setting('profile', 'login') !== 'true') {
    $form->freeze('username');
} else {
    $form->applyFilter('username', 'stripslashes');
    $form->applyFilter('username', 'trim');
    $form->addRule('username', get_lang('ThisFieldIsRequired'), 'required');
    $form->addRule('username', get_lang('UsernameWrong'), 'username');
    $form->addRule('username', get_lang('UserTaken'), 'username_available', $user_data['username']);
}
$form->addElement('text', 'picture_name', '', 'id="picture_name"');
//	OFFICIAL CODE
if (CONFVAL_ASK_FOR_OFFICIAL_CODE) {
    $form->addElement('text', 'official_code', get_lang('OfficialCode'), array('size' => 40));
    if (api_get_setting('profile', 'officialcode') !== 'true') {
        $form->freeze('official_code');
    }
    $form->applyFilter('official_code', 'stripslashes');
    $form->applyFilter('official_code', 'trim');
    if (api_get_setting('registration', 'officialcode') == 'true' && api_get_setting('profile', 'officialcode') == 'true') {
        $form->addRule('official_code', get_lang('ThisFieldIsRequired'), 'required');
    }
}

//	EMAIL
$form->addElement('text', 'email', get_lang('Email'), array('size' => 40));
if (api_get_setting('profile', 'email') !== 'true') {
    $form->freeze('email');
} else {
    $form->applyFilter('email', 'stripslashes');
    $form->applyFilter('email', 'trim');
    if (api_get_setting('registration', 'email') == 'true') {
        $form->addRule('email', get_lang('ThisFieldIsRequired'), 'required');
    }
    $form->addRule('email', get_lang('EmailWrong'), 'email');
}
// OPENID URL
if (is_profile_editable() && api_get_setting('openid_authentication') == 'true') {
    $form->addElement('text', 'openid', get_lang('OpenIDURL'), array('size' => 40));
    if (api_get_setting('profile', 'openid') !== 'true') {
        $form->freeze('openid');
    }
    $form->applyFilter('openid', 'trim');
    //if (api_get_setting('registration', 'openid') == 'true') {
    //	$form->addRule('openid', get_lang('ThisFieldIsRequired'), 'required');
    //}
}

//	PHONE
$form->addElement('text', 'phone', get_lang('phone'), array('size' => 20, 'onkeypress' => 'return isNumberKey(event)', 'id' => 'phone'));
if (api_get_setting('profile', 'phone') !== 'true') {
    $form->freeze('phone');
}
$form->applyFilter('phone', 'stripslashes');
$form->applyFilter('phone', 'trim');
/* if (api_get_setting('registration', 'phone') == 'true') {
  $form->addRule('phone', get_lang('ThisFieldIsRequired'), 'required');
  } */

//	PICTURE
if (is_profile_editable() && api_get_setting('profile', 'picture') == 'true') {
    $form->addElement('file', 'picture', ($user_data['picture_uri'] != '' ? get_lang('UpdateImage') : get_lang('AddImage')), 'id=user_picture accept="image/jpeg, image/png, image/gif"');
    //$form->addElement('html', '<div class="row"><div class="label"></div><div class="formw" style="max-width: 44%;"><div id="progress_user" style="height:30px; margin-bottom:10px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;"></div></div></div>');
    //$form->addElement('html', '<div class="row"><div class="label"></div><img id="imgPreviewNice" src=""></div>');
    $form->add_progress_bar();
    if (!empty($user_data['picture_uri'])) {
        $form->addElement('checkbox', 'remove_picture', null, get_lang('DelImage'));
    }
    //$allowed_picture_types = array('jpg', 'jpeg', 'png', 'gif');
    //$form->addRule('picture', get_lang('OnlyImagesAllowed') . ' (' . implode(',', $allowed_picture_types) . ')', 'filetype', $allowed_picture_types);
}

//	LANGUAGE
$form->addElement('select_language', 'language', get_lang('Language'));
if (api_get_setting('profile', 'language') !== 'true') {
    $form->freeze('language');
}

// TIMEZONE
$timeZoneList = TimeZone::TimeZoneList();
$timeZone = array();
foreach ($timeZoneList as $id => $value) {
    $timeZone[$id] = $value;
}
$form->addElement('select', 'timezone', get_lang('TimeZone'), $timeZone, array('id' => 'status_select'));


//	EXTENDED PROFILE  this make the page very slow!
if (api_get_setting('extended_profile') == 'true') {
    if (!isset($_GET['type']) || (isset($_GET['type']) && $_GET['type'] == 'extended')) {
        //$form->addElement('html', '<a href="javascript: void(0);" onclick="javascript: show_extend();"> show_extend_profile</a>');
        $form->addElement('static', null, '<em>' . get_lang('OptionalTextFields') . '</em>');
        //	MY COMPETENCES
        $form->add_html_editor('competences', get_lang('MyCompetences'), false, false, array('ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));
        //	MY DIPLOMAS
        $form->add_html_editor('diplomas', get_lang('MyDiplomas'), false, false, array('ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));
        //	WHAT I AM ABLE TO TEACH
        $form->add_html_editor('teach', get_lang('MyTeach'), false, false, array('ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));

        //	MY PRODUCTIONS
        $form->addElement('file', 'production', get_lang('MyProductions'));
        if ($production_list = UserManager::build_production_list($_user['user_id'], '', true)) {
            $form->addElement('static', 'productions_list', null, $production_list);
        }
        //	MY PERSONAL OPEN AREA
        $form->add_html_editor('openarea', get_lang('MyPersonalOpenArea'), false, false, array('ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));
        $form->applyFilter(array('competences', 'diplomas', 'teach', 'openarea'), 'stripslashes');
        $form->applyFilter(array('competences', 'diplomas', 'teach'), 'trim'); // openarea is untrimmed for maximum openness
    }
}

//	PASSWORD
if (is_profile_editable() && api_get_setting('profile', 'password') == 'true') {

    if (api_get_setting('show_force_password_change') == 'true') {
        $form->addElement('password', 'password0', get_lang('Pass'), array('size' => 40));
        $form->addElement('static', null, null, '<em>' . get_lang('Enter2passToChange') . '</em>');
        $form->addElement('password', 'password1', get_lang('NewPass'), array('size' => 40, 'class' => 'password'));
        $form->addElement('password', 'password2', get_lang('langConfirmation'), array('size' => 40, 'class' => 'password'));
        //	user must enter identical password twice so we can prevent some user errors
        $form->addRule(array('password1', 'password2'), get_lang('PassTwo'), 'compare');
        $form->registerRule('newpassworddifferentthanoldpassword', 'function', 'newpassworddifferentthanoldpassword');
        $form->addRule('password1', get_lang('NewPasswordShouldBeDifferentThanOldPassword'), 'newpassworddifferentthanoldpassword');
        $form->registerRule('passwordlength', 'function', 'passwordlength');
        $form->registerRule('passwordnumbers', 'function', 'passwordnumbers');
        $form->registerRule('passwordcamelcase', 'function', 'passwordcamelcase');
        $form->registerRule('passwordsymbols', 'function', 'passwordsymbols');
        $form->addRule('password1', get_lang('PasswordTooShort'), 'passwordlength');
        $form->addRule('password1', get_lang('PasswordMustContainSymbol'), 'passwordsymbols');
        $form->addRule('password1', get_lang('PasswordMustContainLowerUpper'), 'passwordcamelcase');
        $form->addRule('password1', get_lang('PasswordMustContainNumber'), 'passwordnumbers');
        if (CHECK_PASS_EASY_TO_FIND) {
            $form->addRule('password1', get_lang('PassTooEasy') . ': ' . api_generate_password(), 'callback', 'api_check_password');
        }
    } else {
        $form->addElement('password', 'password0', get_lang('Pass'), array('size' => 40, 'value' => ''));
        $form->addElement('static', null, null, '<em>' . get_lang('Enter2passToChange') . '</em>');
        $form->addElement('password', 'password1', get_lang('NewPass'), array('size' => 40, 'class' => 'password'));
        $form->addElement('password', 'password2', get_lang('langConfirmation'), array('size' => 40, 'class' => 'password'));
        //	user must enter identical password twice so we can prevent some user errors
        $form->addRule(array('password1', 'password2'), get_lang('PassTwo'), 'compare');
    }
}


// EXTRA FIELDS
$extra = UserManager::get_extra_fields(0, 50, 5, 'ASC');
$extra_data = UserManager::get_extra_user_data(api_get_user_id(), true);
foreach ($extra as $id => $field_details) {
    if ($field_details[6] == 0) {
        continue;
    }
    switch ($field_details[2]) {
        case USER_FIELD_TYPE_TEXT:
            $form->addElement('text', 'extra_' . $field_details[1], $field_details[3], array('size' => 40));
            $form->addRule('extra_' . $field_details[1], get_lang('ShouldBeAlphanumeric'), 'alphanumeric', null);
            $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
            $form->applyFilter('extra_' . $field_details[1], 'trim');
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            break;
        case USER_FIELD_TYPE_TEXTAREA:
            $form->add_html_editor('extra_' . $field_details[1], $field_details[3], false);
            //$form->addElement('textarea', 'extra_'.$field_details[1], $field_details[3], array('size' => 80));
            $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
            $form->applyFilter('extra_' . $field_details[1], 'trim');
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            break;
        case USER_FIELD_TYPE_RADIO:
            $group = array();
            foreach ($field_details[9] as $option_id => $option_details) {
                $options[$option_details[1]] = $option_details[2];
                $group[] = & HTML_QuickForm::createElement('radio', 'extra_' . $field_details[1], $option_details[1], $option_details[2] . '<br />', $option_details[1]);
            }
            $form->addGroup($group, 'extra_' . $field_details[1], $field_details[3], '');
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            break;
        case USER_FIELD_TYPE_SELECT:
            $options = array();
            foreach ($field_details[9] as $option_id => $option_details) {
                $options[$option_details[1]] = $option_details[2];
            }
            $form->addElement('select', 'extra_' . $field_details[1], $field_details[3], $options, '');
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            break;
        case USER_FIELD_TYPE_SELECT_MULTIPLE:
            $options = array();
            foreach ($field_details[9] as $option_id => $option_details) {
                $options[$option_details[1]] = $option_details[2];
            }
            $form->addElement('select', 'extra_' . $field_details[1], $field_details[3], $options, array('multiple' => 'multiple'));
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            break;
        case USER_FIELD_TYPE_DATE:
            $form->addElement('datepickerdate', 'extra_' . $field_details[1], $field_details[3], array('form_name' => 'profile'));
            $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            $form->applyFilter('theme', 'trim');
            $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
            $form->setDefaults($defaults);
            break;
        case USER_FIELD_TYPE_DATETIME:
            $form->addElement('datepicker', 'extra_' . $field_details[1], $field_details[3], array('form_name' => 'profile'));
            $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            $form->applyFilter('theme', 'trim');
            $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
            $form->setDefaults($defaults);
            break;
        case USER_FIELD_TYPE_DOUBLE_SELECT:
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
        case USER_FIELD_TYPE_TAG:
            //the magic should be here
            $user_tags = UserManager::get_user_tags(api_get_user_id(), $field_details[0]);

            $pre_html = '<div class="row">
						<div class="label">' . $field_details[3] . '</div>
						<div class="formw">';
            $post = '</div></div>';

            $tag_list = '';
            if (is_array($user_tags) && count($user_tags) > 0) {
                foreach ($user_tags as $tag) {
                    $tag_list .= '<option value="' . $tag['tag'] . '" class="selected">' . $tag['tag'] . '</option>';
                }
            }

            $multi_select = '<select id="extra_' . $field_details[1] . '" name="extra_' . $field_details[1] . '">
           					' . $tag_list . '
      						 </select>';

            $form->addElement('html', $pre_html . $multi_select . $post);
            $url = api_get_path(WEB_AJAX_PATH) . 'user_manager.ajax.php';
            $complete_text = get_lang('StartToType');
            //if cache is set to true the jquery will be called 1 time
            $jquery_ready_content.= <<<EOF
      		$("#extra_$field_details[1]").fcbkcomplete({
	            json_url: "$url?a=search_tags&field_id=$field_details[0]",
	            cache: false,
	            filter_case: true,
	            filter_hide: true,
	            complete_text:"$complete_text",
				firstselected: true,
	            //onremove: "testme",
				//onselect: "testme",
	            filter_selected: true,
	            newel: true
          	});
EOF;
            break;
        case USER_FIELD_TYPE_TIMEZONE:
            $form->addElement('select', 'extra_' . $field_details[1], $field_details[3], api_get_timezones(), '');
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            break;
        case USER_FIELD_TYPE_SOCIAL_PROFILE:
            // get the social network's favicon
            $icon_path = UserManager::get_favicon_from_url($extra_data['extra_' . $field_details[1]], $field_details[4]);
            // special hack for hi5
            $leftpad = '1.7';
            $top = '0.4';
            $domain = parse_url($icon_path, PHP_URL_HOST);
            if ($domain == 'www.hi5.com' or $domain == 'hi5.com') {
                $leftpad = '3';
                $top = '0';
            }
            // print the input field
            $form->addElement('text', 'extra_' . $field_details[1], $field_details[3], array('size' => 60, 'style' => 'background-image: url(\'' . $icon_path . '\'); background-repeat: no-repeat; background-position: 0.4em ' . $top . 'em; padding-left: ' . $leftpad . 'em; '));
            $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
            $form->applyFilter('extra_' . $field_details[1], 'trim');
            if ($field_details[7] == 0)
                $form->freeze('extra_' . $field_details[1]);
            break;
    }
}

// the $jquery_ready_content variable collects all functions that will be load in the $(document).ready javascript function
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready(function(){
	' . $jquery_ready_content . '
});
</script>';


if (api_get_setting('profile', 'apikeys') == 'true') {
    $form->addElement('html', '<div id="div_api_key">');
    $form->addElement('text', 'api_key_generate', get_lang('MyApiKey'), array('size' => 40, 'id' => 'id_api_key_generate'));
    $form->addElement('html', '</div>');
    $form->addElement('button', 'generate_api_key', get_lang('GenerateApiKey'), array('id' => 'id_generate_api_key', 'onclick' => 'generate_open_id_form()')); //generate_open_id_form()
}
//	SUBMIT
if (is_profile_editable()) {
    $form->addElement('style_submit_button', 'apply_change', get_lang('SaveSettings'), 'class="save"');
} else {
    $form->freeze();
}

$user_data = array_merge($user_data, $extra_data);
$form->setDefaults($user_data);

/*
  ==============================================================================
  FUNCTIONS
  ==============================================================================
 */

/*
  -----------------------------------------------------------
  LOGIC FUNCTIONS
  -----------------------------------------------------------
 */

/**
 * Can a user edit his/her profile?
 *
 * @return	boolean	Editability of the profile
 */
function is_profile_editable() {
    return $GLOBALS['profileIsEditable'];
}

/*
  -----------------------------------------------------------
  PRODUCTIONS FUNCTIONS
  -----------------------------------------------------------
 */

/**
 * Upload a submitted user production.
 *
 * @param	$user_id	User id
 * @return	The filename of the new production or FALSE if the upload has failed
 */
function upload_user_production($user_id) {
    $image_path = UserManager::get_user_picture_path_by_id($user_id, 'system', true);

    $production_repository = $image_path['dir'] . $user_id . '/';

    if (!file_exists($production_repository)) {
        @mkdir($production_repository, 0777, true);
    }

    $filename = replace_dangerous_char($_FILES['production']['name']);
    $filename = disable_dangerous_file($filename);

    if (filter_extension($filename)) {
        if (@move_uploaded_file($_FILES['production']['tmp_name'], $production_repository . $filename)) {
            return $filename;
        }
    }
    return false; // this should be returned if anything went wrong with the upload
}

/**
 * Check current user's current password
 * @param	char	password
 * @return	bool true o false
 * @uses Gets user ID from global variable
 */
function check_user_password($password) {
    global $_user;
    $user_id = $_user['user_id'];
    if ($user_id != strval(intval($user_id)) || empty($password)) {
        return false;
    }
    $table_user = Database :: get_main_table(TABLE_MAIN_USER);
    $password = api_get_encrypted_password($password);
    $sql_password = "SELECT * FROM $table_user WHERE user_id='" . $user_id . "' AND password='" . $password . "'";
    $result = Database::query($sql_password);
    return Database::num_rows($result) != 0;
}

/**
 * Check if the email provided is the email of the actual user
 * @param	char	email
 * @return	bool true o false
 * @uses Gets user ID from global variable
 */
function check_user_email($email) {
    global $_user;
    $table_user = Database :: get_main_table(TABLE_MAIN_USER);
    $sql_password = "SELECT * FROM $table_user WHERE user_id='" . Database::escape_string($_user['user_id']) . "' AND email='" . Database::escape_string($email) . "'";
    $result = Database::query($sql_password);
    return Database::num_rows($result) != 0;
}

/*
  ==============================================================================
  MAIN CODE
  ==============================================================================
 */
$filtered_extension = false;
$update_success = false;
$upload_picture_success = false;
$upload_production_success = false;
$msg_fail_changue_email = false;
$msg_is_not_password = false;

if (!empty($_SESSION['is_not_password'])) {
    $msg_is_not_password = ($_SESSION['is_not_password'] == 'success');
    unset($_SESSION['is_not_password']);
} elseif (!empty($_SESSION['profile_update'])) {
    $update_success = ($_SESSION['profile_update'] == 'success');
    unset($_SESSION['profile_update']);
} elseif (!empty($_SESSION['image_uploaded'])) {
    $upload_picture_success = ($_SESSION['image_uploaded'] == 'success');
    unset($_SESSION['image_uploaded']);
} elseif (!empty($_SESSION['production_uploaded'])) {
    $upload_production_success = ($_SESSION['production_uploaded'] == 'success');
    unset($_SESSION['production_uploaded']);
} elseif (isset($_POST['remove_production'])) {
    foreach (array_keys($_POST['remove_production']) as $production) {
        UserManager::remove_user_production($_user['user_id'], urldecode($production));
    }
    if ($production_list == UserManager::build_production_list($_user['user_id'], true, true)) {
        $form->insertElementBefore($form->createElement('static', null, null, $production_list), 'productions_list');
    }
    $form->removeElement('productions_list');
    $file_deleted = true;
}

if ($form->validate()) {

    //new codigo
    $user = $form->exportValues();
    $user_id = api_get_user_id();

    $picture_uri = $user_data['picture_uri'];
    if ($user['remove_picture']) {
        $picture_uri = UserManager::delete_user_picture($user_id);
    }

    $lastname       = $user['lastname'];
    $firstname      = $user['firstname'];
    $official_code  = $user_data['official_code'];
    $phone          = $user['phone'];
    $username       = $user['username'];
    $timezone       = $user['timezone'];
    $status         = intval($user_data['status']);
    $language       = $user['language'];
    $theme          = $user['theme'];
    // set password if a new one was provided
    if ($user['password0'] != '') {
        if (check_user_password($user['password0'])) {
            if ($user['password1'] != '') {
                if ($user['password1'] == $user['password2'] && api_get_setting('profile', 'password') == 'true') {
                    $password = $user['password1'];
                } else {
                    $password = null;
            }
            }
        } else {
            $wrong_current_password = true;
            $_SESSION['is_not_password'] = 'success';
        }
    }
    if ($user['password0'] == '' && $user['password1'] != '') {
        $wrong_current_password = true;
        $_SESSION['is_not_password'] = 'success';
    }

    if (!check_user_email($user['email']) && api_get_setting('profile', 'email') == 'true') {
        $changeemail = $user['email'];
    } else {
        $changeemail = $user_data['email'];
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
        
        UserManager::resize_picture($filename, 22, $image_sys_path . "small_" . $name_image);
        UserManager::resize_picture($filename, 85, $image_sys_path . "medium_" . $name_image);
    }
    UserManager::update_user($user_id, $firstname, $lastname, $username, $password, null, $changeemail, $status, $official_code, $phone, $picture_uri, '', 1, null, 0, null, $language, null, null, $timezone, $theme);
    if (api_get_setting('openid_authentication') == 'true' && !empty($user['openid'])) {
        $up = UserManager::update_openid($user_id, $user['openid']);
    }
    $extras = array();
    foreach ($user as $key => $value) {
        if (substr($key, 0, 6) == 'extra_') { //an extra field
            $myres = UserManager::update_extra_field_value($user_id, substr($key, 6), $value);
    }
    }
    //
//    $wrong_current_password = false;
    //$user_data = $form->exportValues();
//    $user_data = $form->getSubmitValues();
    // set password if a new one was provided
//    if (!empty($user_data['password0'])) {
//        if (check_user_password($user_data['password0'])) {
//            if (!empty($user_data['password1'])) {
//                $password = $user_data['password1'];
//            }
//        } else {
//            $wrong_current_password = true;
//            $_SESSION['is_not_password'] = 'success';
//        }
//    }
//    if (empty($user_data['password0']) && !empty($user_data['password1'])) {
//        $wrong_current_password = true;
//        $_SESSION['is_not_password'] = 'success';
//    }
//    if (!check_user_email($user_data['email'])) {
//        $changeemail = $user_data['email'];
//    }
    // upload picture if a new one is provided
//    if ($_FILES['picture']['size'] AND api_get_setting('profile', 'picture') == 'true') {
//        if ($new_picture = UserManager::update_user_picture($_user['user_id'], $_FILES['picture']['name'], $_FILES['picture']['tmp_name'])) {
//            $user_data['picture_uri'] = $new_picture;
//            $_SESSION['image_uploaded'] = 'success';
//        }
//    }
    // remove existing picture if asked
//    elseif (!empty($user_data['remove_picture']) AND api_get_setting('profile', 'picture') == 'true') {
//        UserManager::delete_user_picture($_user['user_id']);
//        $user_data['picture_uri'] = '';
//    }
    // upload production if a new one is provided
//    if ($_FILES['production']['size']) {
//        $res = upload_user_production($_user['user_id']);
//        if (!$res) {
//            //it's a bit excessive to assume the extension is the reason why upload_user_production() returned false, but it's true in most cases
//            $filtered_extension = true;
//        } else {
//            $_SESSION['production_uploaded'] = 'success';
//        }
//    }
    // remove values that shouldn't go in the database
//    unset($user_data['password0'], $user_data['password1'], $user_data['password2'], $user_data['MAX_FILE_SIZE'], $user_data['remove_picture'], $user_data['apply_change']);
    // Following RFC2396 (http://www.faqs.org/rfcs/rfc2396.html), a URI uses ':' as a reserved character
    // we can thus ensure the URL doesn't contain any scheme name by searching for ':' in the string
    $my_user_openid = isset($user_data['openid']) ? $user_data['openid'] : '';
    if (!preg_match('/^[^:]*:\/\/.*$/', $my_user_openid)) {
        //ensure there is at least a http:// scheme in the URI provided
        $user_data['openid'] = 'http://' . $my_user_openid;
    }
//    $extras = array();
    // to prevent the unallowed modification of certain fields (depending op api_get_setting('profile',X)) we create an array that matches the name of the form with the appropriate setting
//    $form_setting_match = array('firstname' => 'name', 'lastname' => 'name', 'username' => 'login', 'official_code' => 'officialcode', 'email' => 'email', 'phone' => 'phone', 'language' => 'language', 'openid' => 'openid');
    // build SQL query
//    $sql = "UPDATE $table_user SET";
//    unset($user_data['api_key_generate']);
//    foreach ($user_data as $key => $value) {
//        if (substr($key, 0, 6) == 'extra_') { //an extra field
//            $new_key = substr($key, 6);
//            // format array date to 'Y-m-d' or date time  to 'Y-m-d H:i:s'
//            if (is_array($value) && isset($value['Y']) && isset($value['F']) && isset($value['d'])) {
//                if (isset($value['H']) && isset($value['i'])) {
//                    // extra field date time
//                    $time = mktime($value['H'], $value['i'], 0, $value['F'], $value['d'], $value['Y']);
//                    $extras[$new_key] = date('Y-m-d H:i:s', $time);
//                } else {
//                    // extra field date
//                    $time = mktime(0, 0, 0, $value['F'], $value['d'], $value['Y']);
//                    $extras[$new_key] = date('Y-m-d', $time);
//                }
//            } else {
//                $extras[$new_key] = $value;
//            }
//        } else {
//            // these are default profile fields whose value should go into the dokeos_main.user table
//            if (array_key_exists($key, $form_setting_match) AND api_get_setting('profile', $form_setting_match[$key]) !== 'true') {
//                // cannot change this field because the setting does not allow it
//            } else {
//                $sql .= " $key = '" . Database::escape_string($value) . "',";
//            }
//        }
//    }
    // Change the email
//    if (isset($changeemail) AND api_get_setting('profile', 'email') == 'true') {
//        $sql .= " email = '" . Database::escape_string($changeemail) . "' ";
//    }
    // change the password
//    if (isset($password) AND api_get_setting('profile', 'password') == 'true') {
//        $password = api_get_encrypted_password($password);
//        $sql .= " password = '" . Database::escape_string($password) . "'";
//    }
    // remove trailing , from the query we have so far
//    $sql = rtrim($sql, ',');
//    $sql .= " WHERE user_id  = '" . Database::escape_string($_user['user_id']) . "'";
//    Database::query($sql);
    // User tag process
    //1. Deleting all user tags
//    $list_extra_field_type_tag = UserManager::get_all_extra_field_by_type(USER_FIELD_TYPE_TAG);
//    if (is_array($list_extra_field_type_tag) && count($list_extra_field_type_tag) > 0) {
//        foreach ($list_extra_field_type_tag as $id) {
//            UserManager::delete_user_tags(api_get_user_id(), $id);
//        }
//    }
    //2. Update the extra fields and user tags if available
//    if (is_array($extras) && count($extras) > 0) {
//        foreach ($extras as $key => $value) {
//            //3. Tags are process in the UserManager::update_extra_field_value by the UserManager::process_tags function
//            $myres = UserManager::update_extra_field_value($_user['user_id'], $key, $value);
//        }
//    }
    // re-init the system to take new settings into account
    $uidReset = true;
    include_once api_get_path(INCLUDE_PATH) . 'local.inc.php';
    $_SESSION['profile_update'] = 'success';
    header("Location: " . api_get_path(WEB_PATH) . "main/social/home.php?action=success");
    //header("Location: ".api_get_self()."?{$_SERVER['QUERY_STRING']}".($filtered_extension && strpos($_SERVER['QUERY_STRING'], '&fe=1') === false ? '&fe=1' : ''));
    exit;
}


//if (isset($_GET['show'])) {
//if ((api_get_setting('allow_social_tool') == 'true' && api_get_setting('allow_message_tool') == 'true') || (api_get_setting('allow_social_tool') == 'true')) {
//$interbreadcrumb[] = array ('url' => 'javascript: void(0);', 'name' => get_lang('SocialNetwork'));
//} elseif ((api_get_setting('allow_social_tool') == 'false' && api_get_setting('allow_message_tool') == 'true')) {
//$interbreadcrumb[] = array('url' => 'javascript: void(0);', 'name' => get_lang('MessageTool'));
//}
//}

/*
  ==============================================================================
  MAIN DISPLAY SECTION
  ==============================================================================
 */
// the header
Display::display_header(get_lang('ModifyProfile'));

// Display actions
echo '<div class="actions">';
if (api_get_setting('allow_social_tool') == 'true') {
    echo '<a href="' . api_get_path(WEB_PATH) . 'main/social/home.php">' . Display::return_icon('pixel.gif', get_lang('Home'), array('class' => 'toolactionplaceholdericon toolactionshome')) . get_lang('Home') . '</a>';
    echo '<a href="' . api_get_path(WEB_PATH) . 'main/messages/inbox.php?f=social">' . Display::return_icon('pixel.gif', get_lang('Messages'), array('class' => 'toolactionplaceholdericon toolactionsmessage')) . get_lang('Messages') . $count_unread_message . '</a>';
    echo '<a href="' . api_get_path(WEB_PATH) . 'main/social/invitations.php">' . Display::return_icon('pixel.gif', get_lang('Invitations'), array('class' => 'toolactionplaceholdericon toolactionsinvite')) . get_lang('Invitations') . $total_invitations . '</a>';
    echo '<a href="' . api_get_path(WEB_PATH) . 'main/social/profile.php">' . Display::return_icon('pixel.gif', get_lang('ViewMySharedProfile'), array('class' => 'toolactionplaceholdericon toolactionsprofile')) . get_lang('ViewMySharedProfile') . '</a>';
    echo '<a href="' . api_get_path(WEB_PATH) . 'main/social/friends.php">' . Display::return_icon('pixel.gif', get_lang('Friends'), array('class' => 'toolactionplaceholdericon toolactionsfriend')) . get_lang('Friends') . '</a>';
    echo '<a href="' . api_get_path(WEB_PATH) . 'main/social/groups.php">' . Display::return_icon('pixel.gif', get_lang('Groups'), array('class' => 'toolactionplaceholdericon toolactionsgroup')) . get_lang('Groups') . '</a>';
    echo '<a href="' . api_get_path(WEB_PATH) . 'main/social/search.php">' . Display::return_icon('pixel.gif', get_lang('Search'), array('class' => 'toolactionplaceholdericon toolactionsearch')) . get_lang('Search') . '</a>';
} else {
    if (api_get_setting('extended_profile') == 'true') {
        if (api_get_setting('allow_social_tool') == 'true' && api_get_setting('allow_message_tool') == 'true') {
            echo '<a href="' . api_get_path(WEB_PATH) . 'main/social/profile.php">' . Display::return_icon('shared_profile.png', get_lang('ViewSharedProfile')) . '&nbsp;' . get_lang('ViewSharedProfile') . '</a>';
        }
        if (api_get_setting('allow_message_tool') == 'true') {
            echo '<a href="' . api_get_path(WEB_PATH) . 'main/messages/inbox.php">' . Display::return_icon('pixel.gif', get_lang('Messages'), array('class' => 'toolactionplaceholdericon toolactionsmessage')) . ' ' . get_lang('Messages') . '</a>';
        }
        $show = isset($_GET['show']) ? '&amp;show=' . Security::remove_XSS($_GET['show']) : '';

        /* if (isset($_GET['type']) && $_GET['type'] == 'extended') {
          echo '<a href="profile.php?type=reduced'.$show.'">'.Display::return_icon('edit.gif', get_lang('EditNormalProfile')).'&nbsp;'.get_lang('EditNormalProfile').'</a>';
          } else {
          echo '<a href="profile.php?type=extended'.$show.'">'.Display::return_icon('edit.gif', get_lang('EditExtendProfile')).'&nbsp;'.get_lang('EditExtendProfile').'</a>';
          } */
    }
}
echo '</div>';

echo '<div id="content">';

if (!empty($file_deleted)) {
    Display :: display_confirmation_message(get_lang('FileDeleted'), false);
} elseif (!empty($update_success)) {
    $message = get_lang('ProfileReg');

    if ($upload_picture_success) {
        $message .= '<br /> ' . get_lang('PictureUploaded');
    }

    if ($upload_production_success) {
        $message.='<br />' . get_lang('ProductionUploaded');
    }

    Display :: display_confirmation_message($message, false);
}


if (!empty($msg_fail_changue_email)) {
    $errormail = get_lang('ToChangeYourEmailMustTypeYourPassword');
    Display :: display_error_message($errormail, false);
}

if (!empty($msg_is_not_password)) {
    $warning_msg = get_lang('CurrentPasswordEmptyOrIncorrect');
    Display :: display_warning_message($warning_msg, false);
}

//User picture size is calculated from SYSTEM path
$image_syspath = UserManager::get_user_picture_path_by_id(api_get_user_id(), 'system', false, true);
$image_syspath['dir'] . $image_syspath['file'];

$image_size = @getimagesize($image_syspath['dir'] . $image_syspath['file']);

//Web path
$image_path = UserManager::get_user_picture_path_by_id(api_get_user_id(), 'web', false, true);
$image_dir = $image_path['dir'];
$image = $image_path['file'];
$image_file = $image_dir . $image;
$img_attributes = 'src="' . $image_file . '?rand=' . time() . '" '
        . 'alt="' . api_get_person_name($user_data['firstname'], $user_data['lastname']) . '" '
        . 'style="float:' . ($text_dir == 'rtl' ? 'left' : 'right') . '; margin-top:0px;padding:5px;" ';
if ($image_size[0] > 300) {
    //limit display width to 300px
    $img_attributes .= 'width="300" ';
}

// get the path,width and height from original picture
$big_image = $image_dir . 'big_' . $image;

$big_image_size = api_getimagesize($big_image);
$big_image_width = $big_image_size[0];
$big_image_height = $big_image_size[1];
$url_big_image = $big_image . '?rnd=' . time();


if (api_get_setting('allow_social_tool') == 'true') {
    echo '<div id="social-content">';

    echo '<div id="social-content-left">';
    SocialManager::show_social_menu('home', null, $user_id, $show_full_profile);
    echo '</div>';

    echo '<div id="social-content-right">';
    echo '<div id="social-content-online">';
    /* if (api_get_setting('extended_profile') == 'true') {
      $show = isset($_GET['show']) ? '&amp;show='.Security::remove_XSS($_GET['show']) : '';
      if (isset($_GET['type']) && $_GET['type'] == 'reduced') {
      echo '<a href="profile.php?type=extended '.$show.'"><span class="social-menu-text1">'.Display::return_icon('edit.gif', get_lang('EditExtendProfile')).'&nbsp;'.get_lang('EditExtendProfile').'</span></a>';
      } else {
      echo '<a href="profile.php?type=reduced'.$show.'"><span class="social-menu-text1">'.Display::return_icon('edit.gif', get_lang('EditNormalProfile')).'&nbsp;'.get_lang('EditNormalProfile').'</span></a>';
      }
      } */
    echo '</div>';
    $form->display();
    echo '</div>';
    echo '</div>';
} else {
    // Style position:absolute has been removed for Opera-compatibility.
    //echo '<div id="image-message-container" style="float:right;display:inline;position:absolute;padding:3px;width:250px;" >';
    echo '<div id="image-message-container" style="float:right;display:inline;padding:3px;width:230px;" >';

    if ($image == 'unknown.jpg') {
        echo '<img ' . $img_attributes . ' />';
    } else {
        echo '<input type="image" ' . $img_attributes . ' onclick="javascript: return show_image(\'' . $url_big_image . '\',\'' . $big_image_width . '\',\'' . $big_image_height . '\');"/>';
    }
    echo '</div>';
    $form->display();
}

// End content
echo '</div>';

// display the footer
Display :: display_footer();
?>
