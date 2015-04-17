<?php

// including the global Dokeos file
require_once dirname(__FILE__) . '/../inc/global.inc.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceManager.php';
require_once api_get_path(SYS_PATH) . 'main/invoice/invoice.lib.php';
//error_log("SESSION ".$_SESSION['shopping_cart']['chr_type']);
if($_SESSION['shopping_cart']['chr_type']=='0'){
    $chr_type = 0;
   // parse the data
        $extraValues = array();
        parse_str($post['custom'], $extraValues);
        $_SESSION['student_info']['civility'] = $extraValues['civility'];
        $_SESSION['student_info']['extra_organization'] = $extraValues['organization'];
        $_SESSION['student_info']['extra_tva_id'] = $extraValues['tva_id'];
        global $charset;
        $_SESSION['student_info']['firstname'] = api_convert_encoding($_SESSION['student_info']['firstname'], $charset, 'utf-8');
        $_SESSION['student_info']['lastname'] = api_convert_encoding($_SESSION['student_info']['lastname'], $charset, 'utf-8');
        $_SESSION['student_info']['extra_organization'] = api_convert_encoding($_SESSION['student_info']['extra_organization'], $charset, 'utf-8');
        $_SESSION['student_info']['extra_tva_id'] = api_convert_encoding($_SESSION['student_info']['extra_tva_id'], $charset, 'utf-8');
        for ($i = 1; $i <= intval($post['num_cart_items']); $i++) {
            $code = $post['item_number' . $i];
            $_SESSION['items_paid'][$code] = api_get_ecommerce_item($post['item_number' . $i]);
        }
        $response = array();
        $response['completed'] = TRUE;
        $response['transactionId'] = $_POST['txn_id'];
        $response['message'] = $res;
        $obj = new EcommerceManager();
        $obj->registerNewUser($_SESSION);
        //$user_info = api_get_user_info();
        if ($_REQUEST['uid'] != '') {//Existing User
            $user_info = api_get_user_info($_REQUEST['uid']);
            if (empty($user_info)) {
                $user_info = api_get_user_info_by_email($_SESSION['student_info']['email']);
            }
        } else {//New User Register
            $lastname = Database::escape_string($_REQUEST['uln']);
            $firstname = Database::escape_string($_REQUEST['ufn']);
            $email = Database::escape_string($_REQUEST['uemail']);
            $phone = Database::escape_string($_REQUEST['uph']);
            $country_code = Database::escape_string($_REQUEST['uct']);
            $civility = Database::escape_string($_REQUEST['uciv']);
            $language = Database::escape_string($_REQUEST['ulang']);
            $username = $email;
            $status = 5;
            $password = api_generate_password();
            //error_log("CREATE USER ". print_R($email,TRUE));
            //Insert new User
            $userId = UserManager::create_user($firstname, $lastname, $status, $email, $username, $password, $firstname, $language, $phone, '', PLATFORM_AUTH_SOURCE, '0000-00-00 00:00:00', 1, 0, null, $country_code, $civility);
            //get Data new User
            $user_info = api_get_user_info($userId);
            //Send email to new User
            $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);
            $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
            $email_admin = api_get_setting('emailAdministrator');
            $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
            UserManager::send_mail_to_new_user($recipient_name, $email, $subject, $username, $password, $sender_name, $email_admin, $language);
        }
        //Register shopping
//        if (!api_is_platform_admin()) {
//            UserManager::register_shopp_user_course($user_info['user_id']);
//        } else if (api_is_platform_admin() && api_get_user_id() != $user_info['user_id']) {
//            UserManager::register_shopp_user_course($user_info['user_id']);
//        }
        //Register Invoice
        //InvoiceManager::generate_pdf_invoice();
        //$sessionIds = array_keys($_SESSION['shopping_cart']['items']);
//        247  e_commerce_catalog_type  1       Sessions    
//        221  e_commerce_catalog_type  2       Courses     
//        248  e_commerce_catalog_type  3       Modules          
        $sql = "SELECT selected_value FROM " . Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT) . " WHERE variable like 'e_commerce_catalog_type'";
        $res = Database::query($sql);
        $settings = Database::fetch_array($res);
        if($settings[0]==1){
            $obj->registerUserIntoCourses($_SESSION, $response, $user_info['user_id']);
            $obj->registerUserIntoSessionCourses($_SESSION, $user_info['user_id']);
        }else if($settings[0]==2){
            //$obj->registerUserIntoCourses($_SESSION, $response, $user_info['user_id']);
            $obj->registerUserIntoCoursesFree($_SESSION, $user_info['user_id']);
        }else if ($settings[0]==3){
            
        }
        $obj->logPayment($response, $_POST);

        $_SESSION['shopping_cart']['transaction_result'] = $response;
        unset($_SESSION['shopping_cart']['items']);
        unset($_SESSION['items_paid']);
        unset($_SESSION['student_info']);
        $course_info = CourseManager::get_course_information($_SESSION['IdShopCourse']);
        unset($_SESSION['IdShopCourse']);
        $_SESSION['link_now'] = api_get_path(WEB_COURSE_PATH).$course_info['directory'].'/';
        $urlFeedBackStep = api_get_path(WEB_PATH) . 'main/payment/checkout_5_payment_feedback.php';
        //UserManager::send_message_in_outbox($email_administrator, $user_id, $title, $content);
        header('location: ' . $urlFeedBackStep);
        exit;
        
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-synch';
$tx_token = $_GET['tx'];

$workSpace = api_get_payment_setting('workspace');
if ($workSpace == 0) {
    $pp_hostname = 'www.sandbox.paypal.com';
} else {
    $pp_hostname = 'www.paypal.com';
}

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-synch';

$tx_token = $_GET['tx'];
$auth_token = api_get_payment_setting('pdt'); //"qjA9wvbPBG6p-NS7MtFgC9-YCfF8Mug9B0P1rgWWgBIJpFfED71sadCppDq";
$req .= "&tx=$tx_token&at=$auth_token&charset=utf-8";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$pp_hostname/cgi-bin/webscr");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $pp_hostname"));
$res = curl_exec($ch);
curl_close($ch);
if (!$res) {
    //HTTP ERROR
    $response['completed'] = FALSE;
    $response['transactionId'] = 0;
    $response['message'] = "HTTP ERROR.";
    $_SESSION['shopping_cart']['transaction_result'] = $response;
    $urlFeedBackStep = api_get_path(WEB_PATH) . 'main/payment/checkout_5_payment_feedback.php';
} else {
    // parse the data
    $lines = explode("\n", $res);
    $post = array();
    if (strcmp($lines[0], "SUCCESS") == 0) {
        for ($i = 1; $i < count($lines); $i++) {
            list($key, $val) = explode("=", $lines[$i]);
            $post[urldecode($key)] = urldecode($val);
        }

        $_SESSION['paypal_invoice'] = array(
            'address_name' => $post['address_name'],
            'payment_status' => $post['payment_status'],
            'payment_date' => $post['payment_date'],
            'address_street' => $post['address_street'],
            'address_zip' => $post['address_zip'],
            'address_city' => $post['address_city'],
            'address_country' => $post['address_country'],
            'address_country_code' => $post['address_country_code'],
            'tax' => $post['tax'],
            'mc_gross' => $post['mc_gross'],
            'mc_currency' => $post['mc_currency'],
            'num_cart_items' => $post['num_cart_items'],
            'payer_id' => $post['payer_id'],
            'payer_email' => $post['payer_email'],
            'verify_sign' => $post['verify_sign'],
            'first_name' => $post['first_name'],
            'last_name' => $post['last_name'],
            'address1' => $post['address1'],
            'address2' => $post['address2'],
            'phone' => $post['night_phone_a']
        );

        $extraValues = array();
        parse_str($post['custom'], $extraValues);
        $_SESSION['student_info']['civility'] = $extraValues['civility'];
        $_SESSION['student_info']['extra_organization'] = $extraValues['organization'];
        $_SESSION['student_info']['extra_tva_id'] = $extraValues['tva_id'];
        global $charset;
        $_SESSION['student_info']['firstname'] = api_convert_encoding($_SESSION['student_info']['firstname'], $charset, 'utf-8');
        $_SESSION['student_info']['lastname'] = api_convert_encoding($_SESSION['student_info']['lastname'], $charset, 'utf-8');
        $_SESSION['student_info']['extra_organization'] = api_convert_encoding($_SESSION['student_info']['extra_organization'], $charset, 'utf-8');
        $_SESSION['student_info']['extra_tva_id'] = api_convert_encoding($_SESSION['student_info']['extra_tva_id'], $charset, 'utf-8');

        for ($i = 1; $i <= intval($post['num_cart_items']); $i++) {
            $code = $post['item_number' . $i];
            $_SESSION['items_paid'][$code] = api_get_ecommerce_item($post['item_number' . $i]);
        }
        
        $response = array();
        $response['completed'] = TRUE;
        $response['transactionId'] = $_POST['txn_id'];
        $response['message'] = $res;

        $obj = new EcommerceManager();
        $obj->registerNewUser($_SESSION);

        //$user_info = api_get_user_info();
        if ($_REQUEST['uid'] != '') {//Existing User
            $user_info = api_get_user_info($_REQUEST['uid']);
            if (empty($user_info)) {
                $user_info = api_get_user_info_by_email($_SESSION['student_info']['email']);
            }
        } else {//New User Register
            $lastname = Database::escape_string($_REQUEST['uln']);
            $firstname = Database::escape_string($_REQUEST['ufn']);
            $email = Database::escape_string($_REQUEST['uemail']);
            $phone = Database::escape_string($_REQUEST['uph']);
            $country_code = Database::escape_string($_REQUEST['uct']);
            $civility = Database::escape_string($_REQUEST['uciv']);
            $language = Database::escape_string($_REQUEST['ulang']);
            $username = $email;
            $status = 5;
            $password = api_generate_password();
            //Insert new User
            $userId = UserManager::create_user($firstname, $lastname, $status, $email, $username, $password, $firstname, $language, $phone, '', PLATFORM_AUTH_SOURCE, '0000-00-00 00:00:00', 1, 0, null, $country_code, $civility);
            //get Data new User
            $user_info = api_get_user_info($userId);
            //Send email to new User
            $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);
            $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
            $email_admin = api_get_setting('emailAdministrator');
            $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
            UserManager::send_mail_to_new_user($recipient_name, $email, $subject, $username, $password, $sender_name, $email_admin, $language);
        }

        //Register shopping
        if (!api_is_platform_admin()) {
            UserManager::register_shopp_user_course($user_info['user_id']);
        } else if (api_is_platform_admin() && api_get_user_id() != $user_info['user_id']) {
            UserManager::register_shopp_user_course($user_info['user_id']);
        }
        
        //Register Invoice
        InvoiceManager::generate_pdf_invoice();
        //$sessionIds = array_keys($_SESSION['shopping_cart']['items']);
//        247  e_commerce_catalog_type  1       Sessions    
//        221  e_commerce_catalog_type  2       Courses     
//        248  e_commerce_catalog_type  3       Modules          
        $sql = "SELECT selected_value FROM " . Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT) . " WHERE variable like 'e_commerce_catalog_type'";
        $res = Database::query($sql);
        $settings = Database::fetch_array($res);
        if($settings[0]==1){
            $obj->registerUserIntoCourses($_SESSION, $response, $user_info['user_id']);
            $obj->registerUserIntoSessionCourses($_SESSION, $user_info['user_id']);
        }else if($settings[0]==2){
            $obj->registerUserIntoCourses($_SESSION, $response, $user_info['user_id']);
        }else if ($settings[0]==3){
            
        }
        $obj->logPayment($response, $_POST);

        $_SESSION['shopping_cart']['transaction_result'] = $response;
        unset($_SESSION['shopping_cart']['items']);
        unset($_SESSION['items_paid']);
        unset($_SESSION['student_info']);
        $urlFeedBackStep = api_get_path(WEB_PATH) . 'main/payment/checkout_5_payment_feedback.php';
    } else if (strcmp($lines[0], "FAIL") == 0) {
        $response['completed'] = FALSE;
        $response['transactionId'] = 0;
        $response['message'] = "FAIL";
        $_SESSION['shopping_cart']['transaction_result'] = $response;
        $urlFeedBackStep = api_get_path(WEB_PATH) . 'main/payment/checkout_5_payment_feedback.php';
    }
        
        //UserManager::send_message_in_outbox($email_administrator, $user_id, $title, $content);
}
header('location: ' . $urlFeedBackStep);
exit;
?>