<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author  Alberto Flores aflores609@gmail.com
* @package dokeos.admin
*/

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'language.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(SYS_PATH).'main/core/controller/shopping_cart/shopping_cart_controller.php';
// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

if (isset($_REQUEST['id'])){
    $_SESSION['cat_id'] = $_REQUEST['id'];
}

if (api_get_user_id()) {
    if (!isset($_SESSION['steps'][1])) {
        $_SESSION['steps'][1] = true;
    }
}

if (!isset($_SESSION['steps'][4])) {
    $_SESSION['steps'][4] = true;
}

if (isset($_REQUEST['iden'])) {
    $iden = $_REQUEST['iden'];
    $_SESSION['iden'] =  $iden;
}
if (isset($_REQUEST['wish'])) {
    $wish = $_REQUEST['wish'];
    if($wish == 0){
    $user_id = $_user['user_id'];
    }
    $_SESSION['wish'] =  $wish;
}

//display the header
Display::display_header(get_lang('TrainingCategory'));

if (!isset($_SESSION['user_info']) && !isset($_SESSION['selected_courses'])) {
    $lback = api_get_path(WEB_PATH);
} else {
    $lback = api_get_path(WEB_CODE_PATH).'admin/'.(isset($_SESSION['payer_info'])?'registration_step3b.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&amp;prev=3b':'registration_step3.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&amp;prev=3');
}

if (api_get_user_id()) {
    echo '<div class="actions">';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/session_category_payments"">'.Display::return_icon('pixel.gif', get_lang("Catalogue"), array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('Catalogue').'</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/installment_payment.php">'.Display::return_icon('pixel.gif', get_lang("InstallmentPaymentInfo"), array('class' => 'toolactionplaceholdericon toolactionother')).get_lang('InstallmentPaymentInfo').'</a>&nbsp;';
    echo '</div>';
}

// start the content div
echo '<div id="content">';
// Steps breadcrumbs
SessionManager::display_steps_breadcrumbs();

$product = SessionManager::get_session_category($_REQUEST['id']);
echo '<div class="row"><div class="form_header register-payment-steps-name"><h2>'.$product['name'].'</h2></div></div>';
//echo '<div class="register-payment-steps">4/6 '.get_lang('SummaryView').'</div><br />';

echo '<div class="section">';
echo '<div class="sectiontitle">'.get_lang('SelectedProgram').'</div>';
echo '<div class="sectionvalue">';

echo '<h3>'.$product['name'].'</h3>';
echo '<p>'.get_lang('CoursesSelection').'</p>';

if (!empty($_SESSION['selected_courses'])) {
    echo '<ul>';
    foreach ($_SESSION['selected_courses'] as $course_code) {
        $course_info = api_get_course_info($course_code);
        echo '<li>'.$course_info['name'].'</li>';
    }
    echo '</ul>';
}
echo '<div class="link-right"><a href="'.api_get_path(WEB_CODE_PATH).'admin/category_list.php?id='.$_SESSION['cat_id'].'&amp;prev=1">'.get_lang('ChangeSelection').' ></a></div>';
echo '</div></div>';

echo '<div class="section">';
echo '<div class="sectiontitle">'.get_lang('PriceAndConditions').'</div>';
echo '<div class="sectionvalue">';


list($Year,$Month,$Day) = split('-',$product['date_start']);
$start_date = mktime(12,0,0,$Month,$Day,$Year);
$start_date = date("F jS, Y", $start_date);
$start_dateyear = explode(',',$start_date);
$month = explode(' ',$start_dateyear[0]);
$start_date = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;

list($Year,$Month,$Day) = split('-',$product['date_end']);
$end_date = mktime(12,0,0,$Month,$Day,$Year);
$end_date = date("F jS, Y", $end_date);

$end_dateyear = explode(',',$end_date);
$month = explode(' ',$end_dateyear[0]);
$end_date = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;

$country_code = isset($_SESSION['payer_info'])?$_SESSION['payer_info']['country']:$_SESSION['user_info']['extra_country'];
if (api_get_user_id()) {
    $extra_field = UserManager::get_extra_user_data(api_get_user_id());
    $country_code = $extra_field['country'];
}

$sym_dec = $product['currency'] == '978'?',':'.';
echo '<table width="40%">';
    echo '<tr><td><strong>'.get_lang('Start').'</strong></td><td>'.$start_date.'</td></tr>';
    echo '<tr><td><strong>'.get_lang('End').'</strong></td><td>'.$end_date.'</td></tr>';
    echo '<tr><td><strong>'.get_lang('Modality').'</strong></td><td>'.$product['modality'].'</td></tr>';
    echo '<tr><td><strong>'.get_lang('Price').'</strong></td><td>'.number_format($product['cost'], 2, $sym_dec, ' ').'&nbsp;&nbsp;'.($product['currency']=='978'?'EUR':'USD').'</td></tr>';
    echo '<tr><td><strong>'.get_lang('Tax').'</strong></td><td>'.SessionManager::get_percent_tva_by_country($country_code).'%'.'</td></tr>';
    echo '<tr><td><strong>'.get_lang('Total').'</strong></td><td>'.number_format(SessionManager::get_user_amount_pay_atos($product['cost'], $country_code), 2, $sym_dec, ' ').'&nbsp;&nbsp;'.($product['currency']=='978'?'EUR':'USD').'</td></tr>';
echo '</table>';

echo '</div></div>';

if (!api_get_user_id()) {
    echo '<div class="section">';
    echo '<div class="sectiontitle">'.get_lang('StudentIdentity').'</div>';
    echo '<div class="sectionvalue">';
    list($Year,$Month,$Day) = split('-',$product['date_start']);
    $start_date = mktime(12,0,0,$Month,$Day,$Year);
    $start_date = date("F jS, Y", $start_date);
    list($Year,$Month,$Day) = split('-',$product['date_end']);
    $end_date = mktime(12,0,0,$Month,$Day,$Year);
    $end_date = date("F jS, Y", $end_date);

    if (isset($_SESSION['user_info'])) {
       $extra_street = Usermanager::get_extra_field_information_by_name('street');
        echo '<table width="40%">';
	    echo '<tr><td><strong>'.get_lang('Civility').'</strong></td><td>'.$_SESSION['user_info']['civility'].'</td></tr>';
            echo '<tr><td><strong>'.get_lang('FirstName').'</strong></td><td>'.$_SESSION['user_info']['firstname'].'</td></tr>';
            echo '<tr><td><strong>'.get_lang('LastName').'</strong></td><td>'.$_SESSION['user_info']['lastname'].'</td></tr>';
            if (!empty($_SESSION['user_info']['extra_organization'])) {
                $extra = Usermanager::get_extra_field_information_by_name('organization');
                echo '<tr><td><strong>'.($extra?$extra['field_display_text']:get_lang('Organization')).'</strong></td><td>'.$_SESSION['user_info']['extra_organization'].'</td></tr>';
            }
            echo '<tr><td><strong>'.get_lang('Email').'</strong></td><td>'.$_SESSION['user_info']['email'].'</td></tr>';

            echo '<tr><td><strong>'.($extra_street?$extra_street['field_display_text']:get_lang('StreetNumber')).'</strong></td><td>'.$_SESSION['user_info']['extra_street'].'</td></tr>';
            $extra_address2 = Usermanager::get_extra_field_information_by_name('addressline2');
            echo '<tr><td><strong>'.($extra_address2?$extra_address2['field_display_text']:get_lang('AdditionalStreet')).'</strong></td><td>'.$_SESSION['user_info']['extra_addressline2'].'</td></tr>';
            echo '<tr><td><strong>'.get_lang('Zipcode').'</strong></td><td>'.$_SESSION['user_info']['extra_zipcode'].'</td></tr>';
            echo '<tr><td><strong>'.get_lang('City').'</strong></td><td>'.$_SESSION['user_info']['extra_city'].'</td></tr>';
            $selected_country = LanguageManager::get_countries($_SESSION['user_info']['country']);
            echo '<tr><td><strong>'.get_lang('Country').'</strong></td><td>'.$selected_country[$_SESSION['user_info']['country']].'</td></tr>';

            if (isset($_SESSION['user_info']['extra_phone'])) {
                echo '<tr><td><strong>'.get_lang('Phone').'</strong></td><td>'.$_SESSION['user_info']['extra_phone'].'</td></tr>';
            }

        echo '</table>';
    }
    echo '<div class="link-right"><a href="'.api_get_path(WEB_CODE_PATH).'admin/registration_step3.php?id='.$_SESSION['cat_id'].'&amp;prev=3">'.get_lang('ChangeSelection').' ></a></div>';
    echo '</div></div>';
    if (isset($_SESSION['payer_info'])) {
        echo '<div class="section">';
        echo '<div class="sectiontitle">'.get_lang('PayerInformation').'</div>';
        echo '<div class="sectionvalue">';
        echo '<table width="40%">';
		if (isset($_SESSION['payer_info']['company'])) {
			echo '<tr><td><strong>'.get_lang('Organization').'</strong></td><td>'.$_SESSION['payer_info']['company'].'</td></tr>';
		}
		echo '<tr><td><strong>'.get_lang('Civility').'</strong></td><td>'.$_SESSION['payer_info']['civility'].'</td></tr>';

		echo '<tr><td><strong>'.get_lang('FirstName').'</strong></td><td>'.$_SESSION['payer_info']['firstname'].'</td></tr>';
		echo '<tr><td><strong>'.get_lang('LastName').'</strong></td><td>'.$_SESSION['payer_info']['lastname'].'</td></tr>';
		echo '<tr><td><strong>'.get_lang('Email').'</strong></td><td>'.$_SESSION['payer_info']['email'].'</td></tr>';
		echo '<tr><td><strong>'.($extra_street?$extra_street['field_display_text']:get_lang('StreetNumber')).'</strong></td><td>'.$_SESSION['payer_info']['street_number'].'</td></tr>';
		echo '<tr><td><strong>'.($extra_address2?$extra_address2['field_display_text']:get_lang('AdditionalStreet')).'</strong></td><td>'.$_SESSION['payer_info']['street'].'</td></tr>';
		echo '<tr><td><strong>'.get_lang('Zipcode').'</strong></td><td>'.$_SESSION['payer_info']['zipcode'].'</td></tr>';
		echo '<tr><td><strong>'.get_lang('City').'</strong></td><td>'.$_SESSION['payer_info']['city'].'</td></tr>';

                $selected_country = LanguageManager::get_countries($_SESSION['payer_info']['country']);
                echo '<tr><td><strong>'.get_lang('Country').'</strong></td><td>'.$selected_country[$_SESSION['payer_info']['country']].'</td></tr>';

		echo '<tr><td><strong>'.($taxes?$taxes['field_display_text']:get_lang('Taxes')).'</strong></td><td>'.$_SESSION['payer_info']['vatnumber'].'</td></tr>';
		echo '<tr><td><strong>'.get_lang('Phone').'</strong></td><td>'.$_SESSION['payer_info']['phone'].'</td></tr>';
        echo '</table>';
        echo '<div class="link-right"><a href="'.api_get_path(WEB_CODE_PATH).'admin/registration_step3b.php?id='.$_SESSION['cat_id'].'&amp;prev=3b">'.get_lang('ChangeSelection').' ></a></div>';
        echo '</div></div>';
    }
}
$objCommerceManager = new EcommerceManager();
$urlaction = $objCommerceManager->getCurrentPaymentMethod()->getUrlPayment();
    echo '<div class="actions">';
            echo '<script type="text/javascript">
                function call(type) {
                    if (type == "prev1") {
                        window.location.href = "'.api_get_path(WEB_CODE_PATH).'admin/category_list.php?id='.$_SESSION['cat_id'].'&prev=1";
                    } else if (type == "next") {

                        //window.location.href = "'.api_get_path(WEB_CODE_PATH).'admin/payment_options.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&next=5";
                        window.location.href = "'.$urlaction.'";
                    } else {
                        window.location.href = "'.$lback.'";
                    }
                }

                function callPayment(pay_type) {
                    window.location.href = "'.  api_get_path(WEB_CODE_PATH).'payment/atos-sips/call_request.php?pay_type="+pay_type;
                }
            </script>';
            echo '<div align="center">
            <button name="online" value="Previous" onclick="'.(api_get_user_id()?'call(\'prev1\')':'call(\'prev\');').'">'.get_lang('Previous').'</button></a>
            <button name="online" value="OK" onclick="call(\'next\');">'.get_lang('Ok').'</button></a>
            </div>';
    echo '</div>';

// close the content div
echo '</div>';

// display the footer
Display::display_footer();