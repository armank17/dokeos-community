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
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'language.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
global $_user;

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Javascript
$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function() {
     var vat_num = $("#vatnumber_id").val();
     if (vat_num.length > 0) {
       $("#taxe_id").attr("checked","")
       $("#vatnumber_id").attr("disabled", false);
     } else {
       //$("#taxe_id").attr("checked","checked")
       //$("#vatnumber_id").attr("disabled", true);
     }

     $("#taxe_id").click(function() {
        var is_checked = $("#taxe_id").is(":checked");
        if (is_checked) {
            $("#vatnumber_id").val("");
            $("#vatnumber_id").attr("disabled", true);
        } else {
            $("#vatnumber_id").attr("disabled", false);
        }
     });
 });
</script>';
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

if (!isset($_SESSION['steps']['3b'])) {
    $_SESSION['steps']['3b'] = true;
}

if(isset($_REQUEST['iden']))
{
	$iden = $_REQUEST['iden'];
	$_SESSION['iden'] =  $iden;
}
if(isset($_REQUEST['wish']))
{
	$wish = $_REQUEST['wish'];
	if($wish == 0){
	$user_id = $_user['user_id'];
	}
	$_SESSION['wish'] =  $wish;
}
if(isset($_REQUEST['user_id']))
{
	$user_id = $_REQUEST['user_id'];
}

// start the content div
echo '<div id="content">';
// Steps breadcrumbs
SessionManager::display_steps_breadcrumbs();

$product = SessionManager::get_session_category($_REQUEST['id']);
echo '<div class="row"><div class="form_header register-payment-steps-name"><h2>'.$product['name'] . ' - '  .get_lang('PayerInformation') .  '</h2></div></div>';

$form = new FormValidator('registration_step3b', 'post', 'registration_step3b.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&next=3');
$form->addElement('hidden', 'cat_id', intval($_REQUEST['id']));

if ($iden == 1) {
    // Company
    $extra = Usermanager::get_extra_field_information_by_name('organization');
    $form->addElement('text', 'company', ($extra?$extra['field_display_text']:get_lang('Organization')),'class="focus" style="width:250px;"');
    $form->applyFilter('company', 'html_filter');
    $form->applyFilter('company', 'trim');
    $defaults['company'] = isset($_SESSION['payer_info']['company'])?$_SESSION['payer_info']['company']:'';
}

// civility
$civilities = array('none'=>'', 'Monsieur'=>'Monsieur', 'Madame'=>'Madame', 'Mademoiselle'=>'Mademoiselle');
$form->addElement('select', 'civility', get_lang('Civility'), $civilities);

// Lastname
$form->addElement('text', 'lastname', get_lang('LastName').' <span class="sym-error">*</span>','style="width:250px;"');
$form->applyFilter('lastname', 'html_filter');
$form->applyFilter('lastname', 'trim');
$form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');

// firstname
$form->addElement('text', 'firstname', get_lang('FirstName').' <span class="sym-error">*</span>','class="focus" style="width:250px;"');
$form->applyFilter('firstname', 'html_filter');
$form->applyFilter('firstname', 'trim');
$form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');

$extra = Usermanager::get_extra_field_information_by_name('street');
// street number
$form->addElement('text', 'street_number', ($extra?$extra['field_display_text']:get_lang('StreetNumber')).' <span class="sym-error">*</span>','style="width:250px;"');
$form->applyFilter('street_number', 'html_filter');
$form->applyFilter('street_number', 'trim');
$form->addRule('street_number', get_lang('ThisFieldIsRequired'), 'required');

// street
$extra = Usermanager::get_extra_field_information_by_name('addressline2');
$form->addElement('text', 'street', ($extra?$extra['field_display_text']:get_lang('AdditionalStreet')),'style="width:250px;"');
$form->applyFilter('street', 'html_filter');
$form->applyFilter('street', 'trim');

// zipcode
$extra = Usermanager::get_extra_field_information_by_name('zipcode');
$form->addElement('text', 'zipcode', ($extra?$extra['field_display_text']:get_lang('Zipcode')));
$form->applyFilter('zipcode', 'html_filter');
$form->applyFilter('zipcode', 'trim');

// city
$extra = Usermanager::get_extra_field_information_by_name('city');
$form->addElement('text', 'city', ($extra?$extra['field_display_text']:get_lang('City')).' <span class="sym-error">*</span>','style="width:250px;"');
$form->applyFilter('city', 'html_filter');
$form->applyFilter('city', 'trim');
$form->addRule('city', get_lang('ThisFieldIsRequired'), 'required');


$countries = LanguageManager::get_countries();
$countries = array(0 => '--') + $countries;
$form->addElement('select', 'country', get_lang('Country').' <span class="sym-error">*</span>', $countries);
$form->addRule('country', get_lang('ThisFieldIsRequired'), 'required');

// phone
$extra = Usermanager::get_extra_field_information_by_name('phone');
$form->addElement('text', 'phone', ($extra?$extra['field_display_text']:get_lang('Phone')).' <span class="sym-error">*</span>','style="width:250px;"');
$form->applyFilter('phone', 'html_filter');
$form->applyFilter('phone', 'trim');
$form->addRule('phone', get_lang('ThisFieldIsRequired'), 'required');

// Vat number
$form->addElement('html','<div id="extra-tav">');
    $extra = Usermanager::get_extra_field_information_by_name('tva_id');
    $form->addElement('text', 'vatnumber', ($extra?$extra['field_display_text']:get_lang('Country')),'style="width:250px;" id="vatnumber_id"');
    $form->applyFilter('vatnumber', 'html_filter');
    $form->applyFilter('vatnumber', 'trim');
$form->addElement('html','</div>');

$form->addElement('checkbox', 'taxes', get_lang('WithOutVat'), null, 'id="taxe_id"');

// Email
$form->addElement('text', 'email', get_lang('Email').' <span class="sym-error">*</span>', 'style="width:250px;"');
$form->addRule('email', get_lang('EmailWrong'), 'email');
$form->addRule('email', get_lang('EmailWrong'), 'required');

// Confirmation Email
$form->addElement('text', 'email2', get_lang('ConfirmationEmail').' <span class="sym-error">*</span>', 'style="width:250px;"');
$form->addRule('email2', get_lang('EmailWrong'), 'email');
$form->addRule('email2', get_lang('EmailWrong'), 'required');
$form->addRule(array('email', 'email2'), get_lang('EmailsNotMatch'), 'compare');

//$form->addElement('html','</br></br></br>');

//$form->add_fr_zipcode_required_rule(array('zipcode', 'country'), get_lang('ZipcodeForThisCountryIsRequired'), 'fr_zipcode_required');
//$form->addRule(array('zipcode', 'country'), get_lang('ZipcodeMustBe5digits'), 'fr_zipcode');

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

$defaults['firstname'] = isset($_SESSION['payer_info']['firstname'])?$_SESSION['payer_info']['firstname']:'';
$defaults['lastname'] = isset($_SESSION['payer_info']['lastname'])?$_SESSION['payer_info']['lastname']:'';
$defaults['email'] = isset($_SESSION['payer_info']['email'])?$_SESSION['payer_info']['email']:'';

$defaults['email2'] = isset($_SESSION['payer_info']['email2'])?$_SESSION['payer_info']['email2']:'';

$defaults['street_number'] = isset($_SESSION['payer_info']['street_number'])?$_SESSION['payer_info']['street_number']:'';
$defaults['street'] = isset($_SESSION['payer_info']['street'])?$_SESSION['payer_info']['street']:'';
$defaults['zipcode'] = isset($_SESSION['payer_info']['zipcode'])?$_SESSION['payer_info']['zipcode']:'';
$defaults['city'] = isset($_SESSION['payer_info']['city'])?$_SESSION['payer_info']['city']:'';
$defaults['phone'] = isset($_SESSION['payer_info']['phone'])?$_SESSION['payer_info']['phone']:'';
$defaults['country'] = isset($_SESSION['payer_info']['country'])?$_SESSION['payer_info']['country']:'001';
$defaults['vatnumber'] = isset($_SESSION['payer_info']['vatnumber'])?$_SESSION['payer_info']['vatnumber']:'';
$defaults['taxes'] = isset($_SESSION['payer_info']['taxes'])?$_SESSION['payer_info']['taxes']:'';
$defaults['civility'] = isset($_SESSION['payer_info']['civility'])?$_SESSION['payer_info']['civility']:'Mr';

$form->setDefaults($defaults);
if(isset($_POST['submit_plus'])) {
	echo '<script type="text/javascript">window.location.href = "registration_step3.php?iden='.$iden.'&wish='.$wish.'&id='.intval($_SESSION['cat_id']).'&prev=3";</script>';
}

if( $form->validate()) {
	$payer  = $form->exportValues();
    $id     = $payer['cat_id'];
    $_SESSION['payer_info'] = $payer;

    if (empty($payer['zipcode'])) {
		$_SESSION['payer_info']['zipcode'] = 0;
	}

	if (isset($payer['submit'])) {
            // go to step 4
            echo '<script type="text/javascript">window.location.href = "feedback.php?iden='.$iden.'&wish='.$wish.'&id='.$id.'&next=4";</script>';
	}
}
$form->display();

//}
// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>