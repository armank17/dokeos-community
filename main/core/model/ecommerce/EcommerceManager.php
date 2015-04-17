<?php

require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceAbstract.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceInterface.php';
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommercePaypal.php';
require_once api_get_path(SYS_PATH) . 'main/invoice/invoice.lib.php';

class EcommerceManager {

    private $_gatewayObj = null;
    private $_userId = 0;

    public function __construct() {
        $selectedGateway = intval(api_get_setting('e_commerce'), 10);
        $this->_gatewayObj = EcommerceFactory::getEcommerceObject($selectedGateway);
    }

    /**
     *
     * @return EcommerceInterface
     */
    public function getCurrentPaymentMethod() {
        return $this->_gatewayObj;
    }

    public function processPayment(array $request) {
        $response = $this->_gatewayObj->proccessPayment($request);

        if ($response['completed']) {
            $newUserId = $this->registerNewUser($_SESSION);
            $this->logPayment($response, $request);
            if ($_SESSION['_user']['user_id']) {
                $this->registerUserIntoCourses($_SESSION, $response, $_SESSION['_user']['user_id']);
            } else {
                $this->registerUserIntoCourses($_SESSION, $response, $this->_userId);
            }
            unset($_SESSION['shopping_cart']['items']);
        }
        if ($_SESSION['student_info']['payment_method'] == '2') {
            $newUserId = $this->registerNewUser($_SESSION);
            $this->logPayment($response, $request);
            if (!$_SESSION['_user']['user_id']) {
                $this->registerUserIntoCourses($_SESSION, $response, $this->_userId);
            }
            unset($_SESSION['shopping_cart']['items']);
            $response['completed'] = true;
        }
        return $response;
    }

    public function processResponse(array $request) {
        $response = $this->_gatewayObj->processResponse($request);
        if ($response['completed']) {
            $newUserId = $this->registerNewUser($_SESSION, $request);
            $this->logPayment($response);
            $this->registerUserIntoCourses($_SESSION, $response, $this->_userId);
        }

        return $response;
    }

    public function logPayment($transactionResult, $request) {
        $params = array();
        $params['user_id'] = $this->_userId;
        $params['sess_id'] = '0';
        $params['pay_type'] = $_SESSION['student_info']['payment_method']; // MEANS IT IS ONLINE!
        $params['ecommerce_gateway'] = EcommerceFactory::getEcommerceObject()->getGateway();
        $params['pay_data'] = serialize($transactionResult['details']);
        $params['transaction_id'] = $transactionResult['transactionId'];
        $params['curr_quota'] = $request['creditCardNumberInstallment'];
        SessionManager::save_payment_log($params);
    }

    public function registerUserIntoCourses(array $session, array $transactionResult, $userId) {
        $objEcommerce = CatalogueFactory::getObject();
        $objEcommerce->registerItemsIntoUser($session, $userId);
        //$objEcommerce->registerSessionIntoUser($session, $userId);
        return $objEcommerce->registerItemsForUser($session, $transactionResult, $userId);
    }
    public function registerUserIntoCoursesFree(array $session, $userId) {
        $objEcommerce = CatalogueFactory::getObject();
        return $objEcommerce->registerItemsCoursesFree($session, $userId);
    }
    public function registerUserIntoSessionCourses(array $session, $userId) {
        $objEcommerce = CatalogueFactory::getObject();
        //$objEcommerce->registerItemsIntoUser($session, $userId);
        //$objEcommerce->registerItemsForUser($session, $transactionResult, $userId);
        return $objEcommerce->registerSessionIntoUser($session, $userId);
    }
    public function registerNewUser($session) {
        $lastname = $session['student_info']['lastname'];
        $firstname = $session['student_info']['firstname'];
        $country_code = $session['student_info']['country'];
        $payment_method = $session['student_info']['payment_method'];
        $civility = $session['student_info']['civility'];
        $phone = $session['student_info']['phone'];
        $status = 5;
        ($payment_method == '2') ? $active = 0 : $active = 1;
        $hash = api_generate_password(3);
        $part1 = $firstname[0];
        $exp_lname = explode(' ', $lastname);
        $part2 = (is_array($exp_lname) && count($exp_lname) > 1) ? $exp_lname[0] : $lastname;
        $genera_uname = strtolower($part1 . $part2 . $hash);
        $genera_uname = replace_accents($genera_uname);
        $username = $genera_uname;
        $email = $session['student_info']['email'];
        $subject = '';
        $password = api_generate_password();
        
        if (api_get_user_id() < 1) {
            if ($username != '') {
                $this->_userId = UserManager::create_user($firstname, $lastname, $status, $email, $username, $password, '', '', 
                        $phone, '', PLATFORM_AUTH_SOURCE, '0000-00-00 00:00:00', 1, 0, null, $country_code, $civility);
            }
            $extras = array();

            foreach ($session['student_info'] as $key => $value) {
                if (substr($key, 0, 6) == 'extra_') { // an extra field
                    $myres = UserManager::update_extra_field_value($this->_userId, substr($key, 6), $value);
                }
            }

            $user_info = api_get_user_info($this->_userId);
            $extra_field = UserManager::get_extra_user_data($this->_userId);
            $_user ['firstName'] = $user_info ['firstname'];
            $_user ['lastName'] = $user_info ['lastname'];
            $_user ['mail'] = $user_info ['mail'];
            $_user ['language'] = $user_info ['language'];
            $_user ['user_id'] = $this->_userId;

            $is_allowedCreateCourse = $user_info ['status'] == 1;
            api_session_register('_user');
            api_session_register('is_allowedCreateCourse');

            $recipient_name = $_user ['firstName'] . ' ' . $_user ['lastName'];
            // stats
            event_login();
            // last user login date is now
            $user_last_login_datetime = 0; // used as a unix timestamp it will
            // correspond to : 1 1 1970
            api_session_register('user_last_login_datetime');
            $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);
            $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
            $email_admin = api_get_setting('emailAdministrator');
            $c = 1;
            $programme = '';
//            $courseCodes = array_keys($session['shopping_cart']['items']);
            $courseCodes = array_keys($session['items_paid']);
            foreach ($courseCodes as $courseCode) {
//                $programme.= $c.'.- '.$session['shopping_cart']['items'][$courseCode]['name'].'<br/>';
                $programme.= $c . '.- ' . $session['items_paid'][$courseCode]['name'] . '<br/>';
                $c++;
            }
            if ($payment_method != '2') {
                $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
                if (api_get_setting('enable_invoice') == 'true')
                //InvoiceManager::generate_pdf_invoice();
                //UserManager::send_mail_to_new_user_for_credit_card_or_installment($recipient_name, $email, $subject, $firstname, $lastname, $username, $password, $programme, $sender_name, $email_admin);
                UserManager::send_mail_to_new_user_for_credit_card_or_installment($recipient_name, $email, $subject, $firstname, $lastname, $username, $password, $programme, $sender_name, $email_admin);
            }else {
                $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
                UserManager::send_mail_to_new_user_for_cheque($recipient_name, $email, $subject, $firstname, $lastname, $username, $password, $programme, $sender_name, $email_admin);
                $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('NewUserRegForCheque') . ' ' . api_get_setting('siteName');
                UserManager::send_mail_to_new_user_for_cheque_to_admin($sender_name, $email_admin, $subject, $firstname, $lastname, $username, $password, $programme, $sender_name, $email_admin);
            }
        } else {
            $userid = $session['student_info']['user_id'];
            $status = $session['student_info']['status'];
            $username = $session['student_info']['username'];

            if ($username != '') {
                $this->_userId = UserManager::update_user($userid, $firstname, $lastname, $username = null, $password = null, $auth_source = null, $email, $status = null, $official_code = null, $phone, $picture_uri = null, $expiration_date = null, $active = null, $creator_id = null, $hr_dept_id = null, $extra = null, $language = null, $country_code, $civility);
            }
            $extras = array();

            foreach ($session['student_info'] as $key => $value) {
                if (substr($key, 0, 6) == 'extra_') { // an extra field
                    $myres = UserManager::update_extra_field_value($userid, substr($key, 6), $value);
                }
            }

            $this->_userId = api_get_user_id();
            $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);
            $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
            $email_admin = api_get_setting('emailAdministrator');
            $c = 1;
            $programme = '<br/>';
//            $courseCodes = array_keys($session['shopping_cart']['items']);
            $courseCodes = array_keys($session['items_paid']);
            foreach ($courseCodes as $courseCode) {
//                $programme.= $c.'.- '.$session['shopping_cart']['items'][$courseCode]['name'].'<br/>';
                $programme.= $c . '.- ' . $session['items_paid'][$courseCode]['name'] . '<br/>';
                $c++;
            }
            if ($payment_method != '2') {
                $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
                if (api_get_setting('enable_invoice') == 'true')
                //InvoiceManager::generate_pdf_invoice();
                //UserManager::send_mail_add_programmes_for_credit_card_or_installment($recipient_name, $email, $subject, $firstname, $lastname, $programme, $sender_name, $email_admin);
                UserManager::send_mail_add_programmes_for_credit_card_or_installment($recipient_name, $email, $subject, $firstname, $lastname, $programme, $sender_name, $email_admin);
            }else {
                $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
                UserManager::send_mail_add_programmes_for_cheque($recipient_name, $email, $subject, $firstname, $lastname, $username, $password, $programme, $sender_name, $email_admin);
                $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('NewUserRegForCheque') . ' ' . api_get_setting('siteName');
                UserManager::send_mail_add_programmes_for_cheque_to_admin($sender_name, $email_admin, $subject, $firstname, $lastname, $username, $password, $programme, $sender_name, $email_admin);
            }
        }

        return $this->_userId;
    }

    /**
     * 
     */
    public function getContentMail($firstname, $lastname, $username, $password) {
        global $language_interface;
        $table_emailtemplate = Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);
        $sql = "SELECT * FROM $table_emailtemplate WHERE description = 'Userregistration' AND language= '" . $language_interface . "'";
        $result = api_sql_query($sql, __FILE__, __LINE__);
        $content = "";
        while ($row = Database::fetch_array($result)) {
            $content = $row['content'];
        }
        
        $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
        
        if (empty($content)) {
            $content = get_lang('Dear') . " {Name} ,\n\n";
            $content .= get_lang('YouAreReg') . " {siteName} " . get_lang('WithTheFollowingSettings') . "\n\n";
            $content .= get_lang('Username') . ": {username} \n";
            $content .= get_lang('Pass') . " :{password} \n\n";
            $content .= get_lang('Address') . " {siteName} " . get_lang('Is') . " - {url} \n\n";
            $content .= get_lang('Problem') . "\n\n" . get_lang('Formula') . ",\n";
            $content .= "{administratorSurname} \n";
            $content .= get_lang('Manager') . "\n";
            $content .= "{administratorTelephone} \n";
            $content .= get_lang('Email') . " : {emailAdministrator}";
        }
        $content = str_replace("/main/default_course_document", "tmp_file", $content);
        $content = str_replace('{Name}', stripslashes(api_get_person_name($firstname, $lastname)), $content);
        $content = str_replace('{siteName}', api_get_setting('siteName'), $content);
        $content = str_replace('{username}', $username, $content);
        $content = str_replace('{password}', stripslashes($password), $content);
        $content = str_replace('{administratorSurname}', api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname')), $content);
        $content = str_replace('{administratorTelephone}', api_get_setting('administratorTelephone'), $content);
        $content = str_replace('{emailAdministrator}', api_get_setting('emailAdministrator'), $content);
        $content = str_replace("tmp_file", $domain_server, $content);

        if ($_configuration['multiple_access_urls'] == true) {
            $access_url_id = api_get_current_access_url_id();
            if ($access_url_id != -1) {
                $url = api_get_access_url($access_url_id);
                $content = str_replace('{url}', $url['url'], $content);
            }
        } else {
            $content = str_replace('{url}', $_configuration['root_web'], $content);
        }
        return $content;
    }

    public function getContentMailNewCourse($firstname, $lastname, $username, $password) {
        global $language_interface;
        $table_emailtemplate = Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);
        $sql = "SELECT * FROM $table_emailtemplate WHERE description = 'Userregistration' AND language= '" . $language_interface . "'";
        $result = api_sql_query($sql, __FILE__, __LINE__);
        $content = "";
        while ($row = Database::fetch_array($result)) {
            $content = $row['content'];
        }
        
        $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
        
        if (empty($content)) {
            $content = get_lang('Dear') . " {Name} ,\n\n";
            $content .= get_lang('Address') . " {siteName} " . get_lang('Is') . " - {url} \n\n";
            $content .= get_lang('Problem') . "\n\n" . get_lang('Formula') . ",\n";
            $content .= "{administratorSurname} \n";
            $content .= get_lang('Manager') . "\n";
            $content .= "{administratorTelephone} \n";
            $content .= get_lang('Email') . " : {emailAdministrator}";
        }
        $content = str_replace("/main/default_course_document", "tmp_file", $content);
        $content = str_replace('{Name}', stripslashes(api_get_person_name($firstname, $lastname)), $content);
        $content = str_replace('{siteName}', api_get_setting('siteName'), $content);
        $content = str_replace('{username}', $username, $content);
        $content = str_replace('{password}', stripslashes($password), $content);
        $content = str_replace('{administratorSurname}', api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname')), $content);
        $content = str_replace('{administratorTelephone}', api_get_setting('administratorTelephone'), $content);
        $content = str_replace('{emailAdministrator}', api_get_setting('emailAdministrator'), $content);
        $content = str_replace("tmp_file", $domain_server, $content);

        if ($_configuration['multiple_access_urls'] == true) {
            $access_url_id = api_get_current_access_url_id();
            if ($access_url_id != -1) {
                $url = api_get_access_url($access_url_id);
                $content = str_replace('{url}', $url['url'], $content);
            }
        } else {
            $content = str_replace('{url}', $_configuration['root_web'], $content);
        }
        return $content;
    }

}

;