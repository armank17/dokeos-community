<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Bart Mollet
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require ('../inc/global.inc.php');

// including additional libraries
require_once(api_get_path(LIBRARY_PATH).'sessionmanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'language.lib.php');
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
global $_user;

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Database Table Definitions
$tbl_user		= Database::get_main_table(TABLE_MAIN_USER);
$tbl_session_category 	= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);

$htmlHeadXtra[] = '<style type="text/css">
div.row div.label {
    width: 17%;
}
div.row div.formw {
    width: 760px;
}
</style>';

//display the header
Display::display_header(get_lang('TrainingCategory'));

if (!isset($_SESSION['steps'][3])) {
    $_SESSION['steps'][3] = true;
}
if (isset($_REQUEST['iden'])) {
    $iden = $_REQUEST['iden'];
    $_SESSION['iden'] =  $iden;
}
if (isset($_REQUEST['wish'])) {
    $wish = $_REQUEST['wish'];
    if ($wish == 0) {
    $user_id = $_user['user_id'];
    }
    $_SESSION['wish'] =  $wish;
}
if (isset($_REQUEST['user_id'])) {
    $user_id = $_REQUEST['user_id'];
}

if (!empty($user_id)) {
    $sql = "SELECT firstname,lastname,diplomas FROM $tbl_user WHERE user_id = ".$user_id;
    $result = Database::query($sql,__FILE__,__LINE__);
    while ($row = Database::fetch_array($result)) {
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $category = $row['diplomas'];
    }
}

// start the content div
echo '<div id="content">';
// Steps breadcrumbs
SessionManager::display_steps_breadcrumbs();

//if(!isset($_REQUEST['id'])) {

$product = SessionManager::get_session_category($_REQUEST['id']);
echo '<div class="row"><div class="form_header register-payment-steps-name"><h2>'.$product['name']. ' - '.get_lang('Student'). ' '.get_lang('Information'). '</h2></div></div>';

$form = new FormValidator('registration_step3', 'post', 'registration_step3.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&next=3');
$form->addElement('hidden', 'cat_id', intval($_REQUEST['id']));

$civilities = array('' => '--', get_lang('Mr') => get_lang('Mr'), get_lang('Mrs') => get_lang('Mrs'), get_lang('Miss') => get_lang('Miss'));
$form->addElement('select', 'civility', get_lang('Civility').' <span class="sym-error">*</span>', $civilities, 'style="width:250px;"');

// Lastname
$form->addElement('text', 'lastname', get_lang('LastName').' <span class="sym-error">*</span>','style="width:250px;"');
$form->applyFilter('lastname', 'html_filter');
$form->applyFilter('lastname', 'trim');
$form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');

$form->addElement('text', 'firstname', get_lang('FirstName').' <span class="sym-error">*</span>','class="focus" style="width:250px;"');
$form->applyFilter('firstname', 'html_filter');
$form->applyFilter('firstname', 'trim');
$form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');

$countries = LanguageManager::get_countries();
$countries = array(0 => '--') + $countries;
$form->addElement('select', 'country', get_lang('Country').' <span class="sym-error">*</span>', $countries, 'style="width:250px;"');

// EXTRA FIELDS
$extra = UserManager::get_extra_fields(0, 50, 5, 'ASC', false, 2);
$extra_data = UserManager::get_extra_user_data(0, true);
$display_vat = true;
foreach($extra as $id => $field_details) {

        // Don't display phone when user is not payer
        if ($iden == 1 || ($iden == 0 && $wish == 1)) {
            if ($field_details[1] == 'phone') {
                continue;
            }
        }

	if ($field_details[6] == 1) { // only show extra fields that are visible
		switch ($field_details[2]) {
			case USER_FIELD_TYPE_TEXT:
                                if (isset($_GET['iden']) && isset($_GET['wish']) && intval($_GET['iden']) === 0 && (intval($_GET['wish']) === 0 || intval($_GET['wish']) === 1) && $field_details[1] == 'tva_id') {
                                    $display_vat = false;
                                    break;
                                }

                                $required = '';
                                if ($field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'phone') {
                                    $required = ' <span class="sym-error">*</span>';
                                }
								$form->addElement('text', 'extra_'.$field_details[1], $field_details[3].$required, array('size' => 40));
								$form->applyFilter('extra_'.$field_details[1], 'stripslashes');
								$form->applyFilter('extra_'.$field_details[1], 'trim');
                                if ($field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'phone') {
                                    $form->addRule('extra_'.$field_details[1], get_lang('ThisFieldIsRequired'), 'required');
                                }

				break;
			case USER_FIELD_TYPE_TEXTAREA:
				$form->add_html_editor('extra_'.$field_details[1], $field_details[3], false, false, array('ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));
				//$form->addElement('textarea', 'extra_'.$field_details[1], $field_details[3], array('size' => 80));
				$form->applyFilter('extra_'.$field_details[1], 'stripslashes');
				$form->applyFilter('extra_'.$field_details[1], 'trim');
				break;
			case USER_FIELD_TYPE_RADIO:
				$group = array();
				foreach ($field_details[9] as $option_id => $option_details) {
					$options[$option_details[1]] = $option_details[2];
					$group[] =& HTML_QuickForm::createElement('radio', 'extra_'.$field_details[1], $option_details[1], $option_details[2].'<br />', $option_details[1]);
				}
				$form->addGroup($group, 'extra_'.$field_details[1], $field_details[3], '');
				break;
			case USER_FIELD_TYPE_SELECT:
				$options = array();
				foreach($field_details[9] as $option_id => $option_details) {
					$options[$option_details[1]] = $option_details[2];
				}
				$form->addElement('select','extra_'.$field_details[1],$field_details[3].$required,$options,'');
				break;
			case USER_FIELD_TYPE_SELECT_MULTIPLE:
				$options = array();
				foreach($field_details[9] as $option_id => $option_details) {
					$options[$option_details[1]] = $option_details[2];
				}
				$form->addElement('select', 'extra_'.$field_details[1], $field_details[3], $options, array('multiple' => 'multiple'));
				break;
			case USER_FIELD_TYPE_DATE:
				$form->addElement('datepickerdate', 'extra_'.$field_details[1], $field_details[3], array('form_name' => 'user_add'));
				$form->_elements[$form->_elementIndex['extra_'.$field_details[1]]]->setLocalOption('minYear', 1900);
				$defaults['extra_'.$field_details[1]] = date('Y-m-d 12:00:00');
				$form -> setDefaults($defaults);
				$form->applyFilter('theme', 'trim');
				break;
			case USER_FIELD_TYPE_DATETIME:
				$form->addElement('datepicker', 'extra_'.$field_details[1], $field_details[3], array('form_name' => 'user_add'));
				$form->_elements[$form->_elementIndex['extra_'.$field_details[1]]]->setLocalOption('minYear', 1900);
				$defaults['extra_'.$field_details[1]] = date('Y-m-d 12:00:00');
				$form -> setDefaults($defaults);
				$form->applyFilter('theme', 'trim');
				break;
			case USER_FIELD_TYPE_DOUBLE_SELECT:
				$values = array();
				foreach ($field_details[9] as $key => $element) {
					if ($element[2][0] == '*') {
						$values['*'][$element[0]] = str_replace('*','',$element[2]);
					} else {
						$values[0][$element[0]] = $element[2];
					}
				}
				$group = '';
				$group[] =& HTML_QuickForm::createElement('select', 'extra_'.$field_details[1], '', $values[0], '');
				$group[] =& HTML_QuickForm::createElement('select', 'extra_'.$field_details[1].'*', '', $values['*'], '');
				$form->addGroup($group, 'extra_'.$field_details[1], $field_details[3], '&nbsp;');
				if ($field_details[7] == 0)	$form->freeze('extra_'.$field_details[1]);
				// recoding the selected values for double : if the user has selected certain values, we have to assign them to the correct select form
				if (key_exists('extra_'.$field_details[1], $extra_data)) {
					// exploding all the selected values (of both select forms)
					$selected_values = explode(';', $extra_data['extra_'.$field_details[1]]);
					$extra_data['extra_'.$field_details[1]] = array();

					// looping through the selected values and assigning the selected values to either the first or second select form
					foreach ($selected_values as $key => $selected_value) {
						if (key_exists($selected_value, $values[0])) {
							$extra_data['extra_'.$field_details[1]]['extra_'.$field_details[1]] = $selected_value;
						} else {
							$extra_data['extra_'.$field_details[1]]['extra_'.$field_details[1].'*'] = $selected_value;
						}
					}
				}
				break;
			case USER_FIELD_TYPE_DIVIDER:
				$form->addElement('static', $field_details[1], '<br /><strong>'.$field_details[3].'</strong>');
				break;
		}
	}
}

// Email
$form->addElement('text', 'email', get_lang('Email').' <span class="sym-error">*</span>', array('size' => '40'));
$form->addRule('email', get_lang('EmailWrong'), 'email');
$form->addRule('email', get_lang('EmailWrong'), 'required');

// Confirmation email
$form->addElement('text', 'email2', get_lang('ConfirmationEmail').' <span class="sym-error">*</span>', array('size' => '40'));
$form->addRule('email2', get_lang('EmailWrong'), 'email');
$form->addRule('email2', get_lang('EmailWrong'), 'required');
$form->addRule(array('email', 'email2'), get_lang('EmailsNotMatch'), 'compare');

//$form->addElement('html','</br></br></br>');
$select_level = array ();
$navigator_info = api_get_navigator();
if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] >= '6')) {
	$html_results_enabled[] = FormValidator :: createElement ('submit', 'submit_plus', get_lang('Previous'), 'style="background-color: #4171B5;;height:32px;border:1px solid #b8b8b6;text-transform:uppercase;font-weight:bold;color:#fff;"');
	$html_results_enabled[] = FormValidator :: createElement ('submit', 'submit', get_lang('Ok'), 'style="background-color: #4171B5;;height:32px;border:1px solid #b8b8b6;text-transform:uppercase;font-weight:bold;color:#fff;"');
}
else {
	$html_results_enabled[] = FormValidator :: createElement ('style_submit_button', 'submit_plus', get_lang('Previous'), '');
	$html_results_enabled[] = FormValidator :: createElement ('style_submit_button', 'submit', get_lang('Ok'), '');
}
$form->addGroup($html_results_enabled);

$form->addElement('html', '<div class="row">
		<div class="label"></div>
		<div class="formw"><small>'.str_replace('*', '<span class="form_required"> *</span>', get_lang('FieldRequired')).'</small></div>
	</div>');

//$form->add_fr_zipcode_required_rule(array('extra_zipcode', 'extra_country'), get_lang('ZipcodeForThisCountryIsRequired'), 'fr_zipcode_required');
//$form->addRule(array('extra_zipcode', 'extra_country'), get_lang('ZipcodeMustBe5digits'), 'fr_zipcode');
$defaults['firstname'] 		= isset($_SESSION['user_info']['firstname'])?$_SESSION['user_info']['firstname']:'';
$defaults['lastname'] 		= isset($_SESSION['user_info']['lastname'])?$_SESSION['user_info']['lastname']:'';
$defaults['email'] 		= isset($_SESSION['user_info']['email'])?$_SESSION['user_info']['email']:'';
$defaults['email2'] 		= isset($_SESSION['user_info']['email2'])?$_SESSION['user_info']['email2']:'';
$defaults['country'] 		= isset($_SESSION['user_info']['country'])?$_SESSION['user_info']['country']:'';
$defaults['civility'] 		= isset($_SESSION['user_info']['civility'])?$_SESSION['user_info']['civility']:'';

// extra default values
$defaults['extra_street'] 	= isset($_SESSION['user_info']['extra_street'])?$_SESSION['user_info']['extra_street']:'';
$defaults['extra_addressline2'] = isset($_SESSION['user_info']['extra_addressline2'])?$_SESSION['user_info']['extra_addressline2']:'';
$defaults['extra_zipcode'] 	= isset($_SESSION['user_info']['extra_zipcode'])?$_SESSION['user_info']['extra_zipcode']:'';
$defaults['extra_city'] 	= isset($_SESSION['user_info']['extra_city'])?$_SESSION['user_info']['extra_city']:'';
$defaults['extra_organization'] = isset($_SESSION['user_info']['extra_organization'])?$_SESSION['user_info']['extra_organization']:'';
if ($iden == 0 && $wish == 0) {
    $defaults['extra_phone'] = isset($_SESSION['user_info']['extra_phone'])?$_SESSION['user_info']['extra_phone']:'';
}


$form->setDefaults($defaults);
if (isset($_POST['submit_plus'])) {
	echo '<script type="text/javascript">window.location.href = "registration.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.intval($_SESSION['cat_id']).'&prev=2";</script>';
}
if( $form->validate()) {
    $user = $form->exportValues();
    $id = $user['cat_id'];
    $_SESSION['user_info'] = $user;

    if (empty($user['extra_zipcode'])) {
        $_SESSION['user_info']['extra_zipcode'] = 0;
    }

    if (isset($user['submit'])) {
        // go to step 3b
        if ($iden == 1 || ($iden == 0 && $wish == 1)) {
            echo '<script type="text/javascript">window.location.href = "registration_step3b.php?iden='.$iden.'&wish='.$wish.'&id='.$id.'&next=3b";</script>';
        } else {
            if (isset($_SESSION['payer_info'])) {
                unset($_SESSION['payer_info']);
            }
            // go to step 4
            echo '<script type="text/javascript">window.location.href = "feedback.php?iden='.$iden.'&wish='.$wish.'&id='.$id.'&next=4";</script>';
        }
    }
}
$form->display();

//}
// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>