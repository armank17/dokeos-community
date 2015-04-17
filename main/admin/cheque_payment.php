<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author
* @package dokeos.admin
*/

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin','group');

// resetting the course id
//$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// setting breadcrumbs
$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => "session_list.php","name" => get_lang('SessionList'));

// Database Table Definitions
$tbl_user		= Database::get_main_table(TABLE_MAIN_USER);
$tbl_session_category 	= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
$tbl_session            = Database::get_main_table(TABLE_MAIN_SESSION);

if (!isset($_SESSION['steps'][6])) {
    $_SESSION['steps'][6] = true;
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

if (isset($_GET['action']) && $_GET['action'] == 'cheque_payment') {

	// save registered user
    $user_id = api_get_user_id();
    if (isset($_SESSION['user_info'])) {
        $lastname = $_SESSION['user_info']['lastname'];
        $firstname = $_SESSION['user_info']['firstname'];
        $status = 5;
        $hash = api_generate_password(3);
        $part1 = $firstname[0];
        $exp_lname = explode(' ', $lastname);
        $part2 = (is_array($exp_lname) && count($exp_lname) > 1)?$exp_lname[0]:$lastname;
        $genera_uname = strtolower($part1.$part2.$hash);
        $genera_uname=replace_dangerous_char(stripslashes($genera_uname));
		//$genera_uname=disable_dangerous_file($genera_uname);
		//$genera_uname=replace_accents($genera_uname);
        $username = $genera_uname;
        $email = $_SESSION['user_info']['email'];
        if (empty($_SESSION['user_info']['user_id'])) {
            $user_id = UserManager::create_user($firstname, $lastname, $status, $email, $username);
            // deactive user
            Database::query('UPDATE user SET active = 0 WHERE user_id='.$user_id);
        } else {
            $user_id = $_SESSION['user_info']['user_id'];
            UserManager::update_user($user_id, $firstname, $lastname, $username);
            Database::query('UPDATE user SET active = 0 WHERE user_id='.$user_id);
        }

        $extras = array();
        foreach($_SESSION['user_info'] as $key => $value) {
            if (substr($key, 0, 6) == 'extra_') { //an extra fieldç
                $myres = UserManager::update_extra_field_value($user_id, substr($key, 6), $value);
            }
        }

        // save payment
        $from = isset($_SESSION['from'])?$_SESSION['from']:'';
        $params = array(
                    'user_id' => $user_id,
                    'sess_id' => intval($_SESSION['cat_id']),
                    'pay_type' => 2,
                    'pay_data' => '',
                    'from' => $from
                  );
        if ($user_id) {
            $saved = SessionManager::save_payment_atos($params);
        }

        // register to selected courses
        if (isset($_SESSION['selected_sessions']) && isset($_SESSION['cat_id']) && isset($_SESSION['cours_rel_session'])) {
            SessionManager::register_user_to_selected_courses_session($_SESSION['cours_rel_session'], $user_id, $_SESSION['selected_sessions'], $_SESSION['cat_id'], $_GET['action']);
        }

        //display the header
        Display::display_header(get_lang('TrainingCategory'));
        echo '<div id="content">';
        $catalogue_info = SessionManager::get_catalogue_info();
        echo "<p>".$catalogue_info[1]['cheque_message'].".</p>";
        echo "<form action=\"".api_get_path(WEB_PATH)."\"  method=\"post\">\n", "<button type=\"submit\" class=\"next\" name=\"next\" value=\"", get_lang('Next'), "\" validationmsg=\" ", get_lang('Next'), " \">".get_lang('Finish')."</button>\n", "</form><br />\n";
        echo '</div>';

        // Send automatically email at registration process
    /*  $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);
        $emailsubject = '['.api_get_setting('siteName').'] '.get_lang('YourReg').' '.api_get_setting('siteName');

        $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
        $email_admin = api_get_setting('emailAdministrator');

        global $language_interface,$_configuration;
        $table_emailtemplate 	= Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);
        $sql = "SELECT * FROM $table_emailtemplate WHERE description = 'EmailsInCaseOfChequePayment' AND language= '".$language_interface."'";
        $result = api_sql_query($sql, __FILE__, __LINE__);
        $row = Database::fetch_array($result);
        $content = $row['content'];
        $category_info = SessionManager::get_session_category($_SESSION['cat_id']);
        $content =  str_replace('{firstName}',stripslashes($firstname), $content);
        $content =  str_replace('{lastName}',stripslashes($lastname), $content);
        $content =  str_replace('{siteName}',api_get_setting('siteName'), $content);
        $content =  str_replace('{username}',$username, $content);
        $content =  str_replace('{Programme}',stripslashes($category_info['name']), $content);
        $content =  str_replace('{password}','**********',$content);
        $content =  str_replace('{administratorSurname}',api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname')), $content);
        $content =  str_replace('{Institution}',api_get_setting('Institution'), $content);

        if ($_configuration['multiple_access_urls'] == true) {
            $access_url_id = api_get_current_access_url_id();
            if ($access_url_id != -1) {
                $url = api_get_access_url($access_url_id);
                $content =  str_replace('{url}',$url['url'], $content);
            }
        } else {
                $content =  str_replace('{url}',$_configuration['root_web'], $content);
        }
        $content = strip_tags(str_replace('<br />',"\n", $content));
        @api_send_mail($email, $emailsubject, $content);*/

        // clean session
        SessionManager::clear_catalogue_order_process();

        // display the footer
        Display::display_footer();
        exit;
    }

    // save payer
    if (isset($_SESSION['payer_info'])) {
        $_SESSION['payer_info']['student_id'] = $user_id;
        $payer_id = SessionManager::save_payer_user($_SESSION['payer_info']);
    }

}

//display the header
Display::display_header(get_lang('TrainingCategory'));

if (!isset($_SESSION['user_info']) && !isset($_SESSION['selected_courses'])) {
    $lback = api_get_path(WEB_PATH);
} else {
    $lback = api_get_path(WEB_CODE_PATH).'admin/'.(isset($_SESSION['payer_info'])?'registration_step3b.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&amp;prev=3b':'registration_step3.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&amp;prev=3');
}

/*
echo '<div class="actions">';
$lback = isset($_SESSION['payer_info'])?'registration_step3b.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&amp;prev=3b':'registration_step3.php?iden='.$iden.'&amp;wish='.$wish.'&amp;id='.intval($_SESSION['cat_id']).'&amp;prev=3';
echo '<a href="'.$lback.'">'.Display::return_icon('back.png').get_lang('Previous').'</a>';
echo '</div>';
*/
// start the content div
echo '<div id="content">';

// Steps breadcrumbs
SessionManager::display_steps_breadcrumbs();

$product = SessionManager::get_session_category($_REQUEST['id']);
$topic = SessionManager::get_topic_info($product['topic']);
$catalogue_info = SessionManager::get_catalogue_info($topic['catalogue_id']);
$cheque_message = !empty($catalogue_info['cheque_message']) ? $catalogue_info['cheque_message'] : get_lang('ChequePaymentInfo');
echo '<div class="row"><div class="form_header register-payment-steps-name"><h2>'.$product['name'].'</h2></div></div>';

echo '<div class="quiz_content_actions">';
echo '<div class="cheque-payment-info">'.$cheque_message.'</div>';
echo '<div class="row"><div class="label"><b>'.get_lang('Programme').'</b></div><div class="formw">'.$product['name'].'</div></div>';
echo '<div class="row"><div class="label"><b>'.get_lang('Student').'</b></div><div class="formw">'.($_SESSION['user_info']['firstname'].' '.$_SESSION['user_info']['lastname']).'</div></div>';
echo '<div class="row"><div class="label"><b>'.get_lang('Price').'</b></div><div class="formw">'.$product['cost'].'&nbsp;&nbsp;'.($product['currency']=='978'?'EUR':'USD').'</div></div>';

$country = isset($_SESSION['payer_info'])?$_SESSION['payer_info']['country']:$_SESSION['user_info']['extra_country'];

echo '<div class="row"><div class="label"><b>'.get_lang('Tax').'</b></div><div class="formw">'.SessionManager::get_percent_tva_by_country($country).'%'.'</div></div>';
echo '<div class="row"><div class="label"><b>'.get_lang('Total').'</b></div><div class="formw">'.SessionManager::get_user_amount_pay_atos($product['cost'], $country).'&nbsp;&nbsp;'.($product['currency']=='978'?'EUR':'USD').'</div></div>';

echo '<br/><br/></div>';





echo '<div class="quiz_content_actions">';
echo '<div class="company-title">'.get_lang('ChequeMustBeAddressedTo').'</div><br />';
if (isset($catalogue_info['company_address'])) {
	echo '<div class="company-address">'.$catalogue_info['company_address'].'</div>';
}
if (isset($catalogue_info['bank_details'])) {
	echo '<div class="company-bank-details">'.$catalogue_info['bank_details'].'</div>';
}
echo '<br/><br/></div>';

    echo '<div class="actions">';
        //if (isset($_SESSION['user_info']) && $_SESSION['selected_courses']) {
            echo '<script type="text/javascript">
                function call(type) {
                    if (type == "prev") {
                        window.location.href = "'.api_get_path(WEB_CODE_PATH).'admin/payment_options.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&prev=5";
                    } else {
                        window.location.href = "'.api_get_path(WEB_PATH).api_get_self().'?action=cheque_payment&ct='.$_SESSION['cat_id'].'";
                    }
                }
            </script>';
            echo '<div align="center">
            <button name="online" value="Previous" onclick="call(\'prev\');">'.get_lang('Previous').'</button></a>
            <button name="online" value="OK" onclick="call(\'next\');">'.get_lang('Ok').'</button></a>
            </div>';
        /*} else {
           echo get_lang('YourSessionOrderIsOver').'&nbsp;<a href="'.api_get_path(WEB_PATH).'">'.get_lang('GoToCatalogue').'</a>';
        }*/
    echo '</div>';


// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>
