<?php

class ShoppingCartModel {

    private $_steps = array(
        1 => true, 2 => false, 3 => false, 4 => false);

    public static function create() {
        return new ShoppingCartModel();
    }

    public function checkStep($currentValidStep, $session) {

        $response = true;
        $homePage = api_get_path(WEB_PATH);

        // if shopping cart is not active, redirects to homepage        
        if ($session['isShoppingCartActive'] != TRUE || !isset($session['shopping_cart']['steps'])) {
            
            header('location : ' . $homePage);
        }

        // checking step by step
        foreach ($this->_steps as $key => $value) {
            // if reaching the current step, check if prior step took place
            if ($key == $currentValidStep) {
                if (!isset($session['shopping_cart']['steps'][$currentValidStep])) {
                    if ($_SESSION['shopping_cart']['steps'][$currentValidStep - 1] == true || $currentValidStep == 1) {
                        $_SESSION['shopping_cart']['steps'][$currentValidStep] = true;
                    } else {
                        $_SESSION['shopping_cart']['steps'][$currentValidStep] = false;
                    }
                }
                break;
            }
            if($_SESSION['shopping_cart']['chr_type'] == '0'){
                $_SESSION['shopping_cart']['steps'][$currentValidStep] = true;
            }else{
                if (($key - 1) > 0) {
                    if ((!isset($session['shopping_cart']['steps'][$key - 1])) || $session['shopping_cart']['steps'][$key - 1] !== TRUE) {
                        $response = false;
                        header('location: ' . $homePage);
                    }
                }
            }
        }

        return $response;
    }

    public function getBreadCrumbs($session, $get) {

        $shoppingCart = $session['shopping_cart'];

        $stepsFromSession = (isset($shoppingCart['steps'])) ? ($shoppingCart['steps']) : $this->_steps;
        if($_SESSION['shopping_cart']['chr_type'] == '0'){
                $stepsExtraData = array(
                    1 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout.php?id=' . $session['cat_id'] . '&prev=1', 'anchor' => get_lang('ViewShoppingCart')),
                    2 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout_2_registration.php?id=' . $session['cat_id'] . '&prevStep=2', 'anchor' => get_lang('ShopPersonalData')),
                    3 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout_3_registration.php?iden=' . $session['iden'] . '&wish=' . $session['wish'] . '&id=' . $session['cat_id'] . '&prevStep=3', 'anchor' => get_lang('FinalizingYourRegistration')),
                    4 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout_4_payment_data.php?iden=' . $session['iden'] . '&wish=' . $session['wish'] . '&id=' . $session['cat_id'] . '&prevStep=4', 'anchor' => get_lang('Confirmation'))
                );
        }  else {
                $stepsExtraData = array(
                    1 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout.php?id=' . $session['cat_id'] . '&prev=1', 'anchor' => get_lang('ViewShoppingCart')),
                    2 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout_2_registration.php?id=' . $session['cat_id'] . '&prevStep=2', 'anchor' => get_lang('ShopPersonalData')),
                    3 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout_3_registration.php?iden=' . $session['iden'] . '&wish=' . $session['wish'] . '&id=' . $session['cat_id'] . '&prevStep=3', 'anchor' => get_lang('PaymentMethods')),
                    4 => array(
                        'url' => api_get_path(WEB_CODE_PATH) . 'payment/checkout_4_payment_data.php?iden=' . $session['iden'] . '&wish=' . $session['wish'] . '&id=' . $session['cat_id'] . '&prevStep=4', 'anchor' => get_lang('Confirmation'))
                );
        }

        $nextStep = $get['next'];
        $prevStep = $get['prevStep'];

        $class = '';
        $href = 'javascript:void(0)';

        $htmlSteps = array();
        $steps_s = array(1 => true, 2 => false, 3 => false, 4 => false);
        if($_SESSION['shopping_cart']['chr_type'] == '0'){
            foreach ($steps_s as $stepK => $stepV) {
                $stepK_f = 0;
                if($stepK == 2 || $stepK == 3){
                    $stepK_f = $stepK-1;
                    if (isset($stepsFromSession[$stepK]) || ($nextStep == $stepK || $nextStep == ($stepK + 1))) {
                        $class = 'done';
                        $href = $stepsExtraData[$stepK]['url'];

                        if ($stepV && ((isset($prevStep) && $prevStep == $stepK) || (isset($nextStep) && $nextStep == $stepK))) {
                            $class = 'active';
                        }
                        $htmlSteps[$stepK] = '<li id="stepsbreadcrumbs1" class="' . $class . '"><div class="arrow-left"></div><div class="arrow-center" style="color:gray;"><a href="' . $href . '">' . $stepK_f . '. ' . $stepsExtraData[$stepK]['anchor'] . '</a></div><div class="arrow-right"></div></li>';
                    } else {
                        $htmlSteps[$stepK] = '<li id="stepsbreadcrumbs1"><div class="arrow-left2"></div><div class="arrow-center2" style="color:gray;">' . $stepK_f . '. ' . $stepsExtraData[$stepK]['anchor'] . '</div><div class="arrow-right2"></div></li>';
                    }
                }
                
            }
        }  else {
            foreach ($this->_steps as $stepK => $stepV) {
                if (isset($stepsFromSession[$stepK]) || ($nextStep == $stepK || $nextStep == ($stepK + 1))) {
                    $class = 'done';
                    $href = $stepsExtraData[$stepK]['url'];

                    if ($stepV && ((isset($prevStep) && $prevStep == $stepK) || (isset($nextStep) && $nextStep == $stepK))) {
                        $class = 'active';
                    }
                    $htmlSteps[$stepK] = '<li id="stepsbreadcrumbs1" class="' . $class . '"><div class="arrow-left"></div><div class="arrow-center" style="color:gray;"><a href="' . $href . '">' . $stepK . '. ' . $stepsExtraData[$stepK]['anchor'] . '</a></div><div class="arrow-right"></div></li>';
                } else {
                    $htmlSteps[$stepK] = '<li id="stepsbreadcrumbs1"><div class="arrow-left2"></div><div class="arrow-center2" style="color:gray;">' . $stepK . '. ' . $stepsExtraData[$stepK]['anchor'] . '</div><div class="arrow-right2"></div></li>';
                }
            }
        }

        // $css_width = "";
        // // step6
        // $title6 = ! api_get_user_id() ? '6. ' . get_lang( 'ChequePayment' ) :
        // '4. ' . get_lang( 'ChequePayment' );
        // if ( isset( $nextStep ) && $nextStep == 6 )
        // {
        // $step6 .= '<li id="stepsbreadcrumbs6" class="active"><a href="' .
        // $href . '">' . $title6 . '</a></li>';
        // $css_width = "height:40px;";
        // }

        $css_width = '';
        $html = '';
        $html .= '<div style="' . $css_width . '" id="stepsbreadcrumbs"><ul>';
            foreach ($htmlSteps as $htmlStepK => $htmlStepV) {
                // if( $htmlStepK == 1 || $htmlStepK > 3 )
                // {
                // $html .= $htmlStepV;
                // }
                // else if ( ! api_get_user_id() && ( $htmlStepK > 1 && $htmlStepK <
                // 4 ))
                // {
                // $html .= $htmlStepV;
                // }
                    $html .= $htmlStepV;
            }
            
        $html .= '</ul></div>';
        return $html;
    }

    function display_login_form() {
        $form2 = new FormValidator('formLogin');
        $form2->addElement('html', '<p>si usted es cliente ya registrado inicie sesion haciendo clic <a id="startsesion" href="#">aqui</a></p>');
        $form2->addElement('text', 'login', get_lang('UserName'));
        $form2->addElement('password', 'password', get_lang('Pass'));
        $form2->addElement('style_submit_button', 'submitAuth', get_lang('langEnter'), array('class' => 'login'));
        //$renderer =& $form->defaultRenderer();
        //$renderer->setElementTemplate('<div ><label>{label}</label></div><div>{element}</div>');
        $form2->display();
    }

    public function getFormCheckoutRegistration($session) {

        $urlForm = '';

        switch (CatalogueFactory::getObject()->getCurrentCatalogueType()) {
            case 1 :
                $urlForm = api_get_path(WEB_PATH) . 'main/payment/checkout_2_registration.php?prevStep=2&nextStep=3';
                break;
            default:
                $urlForm = api_get_path(WEB_PATH) . 'main/payment/checkout_2_registration.php?prevStep=2&nextStep=3';
                break;
        }

        $form = new FormValidator('registration', 'post', $urlForm);
        $form->addElement('html', '<h1>' . get_lang('RegisterCheckout') . '</h1>');
        //$form -> addElement('html','<p id="pstartlogin">'. get_lang('LoginShoppingcart').' <a id="startlogin"  style="cursor:pointer"><b>'. get_lang('Here').'<b></a></p>');
        if (api_get_user_id() == 0) {
            //$form -> addElement('html','<p id="pstartlogin"><a id="startlogin"  style="cursor:pointer">'.Display::return_icon('Button.png').'</a></p>');
            $form->addElement('html', '<p id="pstartlogin"><button id="startlogin" class="freshbutton-blue" onclick="return false;">' . get_lang('LoginRequireCheckout') . '</button></p>');
        }
        $form->addElement('hidden', 'cat_id', intval($_REQUEST['id']));

        $civilities = array(
            '' => '--', get_lang('Mr') => get_lang('Mr'), get_lang('Mrs') => get_lang('Mrs'), get_lang('Miss') => get_lang('Miss'));
        $form->addElement('select', 'civility', get_lang('Civility') . ' <span class="sym-error">*</span>', $civilities, 'style="width:250px;"');
        $form->addRule('civility', get_lang('ThisFieldIsRequired'), 'required');

        // Lastname

        $form->addElement('text', 'firstname', get_lang('FirstName') . ' <span class="sym-error">*</span>', 'class="focus" style="width:250px;"');
        $form->applyFilter('firstname', 'html_filter');
        $form->applyFilter('firstname', 'trim');
        $form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');

        $form->addElement('text', 'lastname', get_lang('LastName') . ' <span class="sym-error">*</span>', 'style="width:250px;"');
        $form->applyFilter('lastname', 'html_filter');
        $form->applyFilter('lastname', 'trim');
        $form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');
		if($_SESSION['shopping_cart']['chr_type']!='0' && api_get_user_id()>0){
        $form->addElement('radio', 'identification', '', get_lang('Individual'), 0);
        $form->addElement('radio', 'identification', '', get_lang('Collectivity'), 1);
        }


        $countries = LanguageManager::get_countries(null, 'iso');
        $countries = array(
            '' => '--') + $countries;
        $form->addElement('select', 'country', get_lang('Country') . ' <span class="sym-error">*</span>', $countries, 'style="width:250px;"');
        $form->addRule('country', get_lang('ThisFieldIsRequired'), 'required');

        // EXTRA FIELDS
        $extra = UserManager::get_extra_fields(0, 50, 5, 'ASC', false, 2);
        $extra_data = UserManager::get_extra_user_data(0, true);
        $display_vat = true;
        foreach ($extra as $id => $field_details) {
            if ($field_details[6] == 1 && $field_details[10] == 1) { // only show extra fields that are visible and field registration active
                // visible
                switch ($field_details[2]) {
                    case USER_FIELD_TYPE_TEXT :
                        if (isset($_GET['iden']) && isset($_GET['wish']) && intval($_GET['iden']) === 0 && (intval($_GET['wish']) === 0 || intval($_GET['wish']) === 1) && $field_details[1] == 'tva_id') {
                            $display_vat = false;
                            break;
                        }

                        $required = '';
                        if ($field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'phone' || $field_details[1] == 'zipcode') {
                            $required = ' <span class="sym-error">*</span>';
                        }
                        if ($field_details[1] != 'organization' && $field_details[1] != 'tva_id') {
                            $form->addElement('text', 'extra_' . $field_details[1], (($field_details[3] == 'Organization') ? get_lang('Organization') : $field_details[3]) . $required, array(
                                'size' => 40));
                        }
                        $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
                        $form->applyFilter('extra_' . $field_details[1], 'trim');
                        if ($field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'zipcode' || $field_details[1] == 'zipcode') {
                            $form->addRule('extra_' . $field_details[1], get_lang('ThisFieldIsRequired'), 'required');
                        }
                        if ($field_details[1] == 'zipcode') {
                            $form->addRule('extra_zipcode', get_lang('ThisFieldShouldBeNumeric'), 'numeric');
                        }

                        if ($field_details[1] == 'city') {
                            // Phone
                            $form->addElement('text', 'phone', get_lang('PhoneNumber'));
                        }
                        break;
                    case USER_FIELD_TYPE_TEXTAREA :
                        $form->add_html_editor('extra_' . $field_details[1], $field_details[3], false, false, array(
                            'ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));
                        // $form->addElement('textarea',
                        // 'extra_'.$field_details[1], $field_details[3],
                        // array('size' => 80));
                        $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
                        $form->applyFilter('extra_' . $field_details[1], 'trim');
                        break;
                    case USER_FIELD_TYPE_RADIO :
                        $group = array();
                        foreach ($field_details[9] as $option_id => $option_details) {
                            $options[$option_details[1]] = $option_details[2];
                            $group[] = & HTML_QuickForm::createElement('radio', 'extra_' . $field_details[1], $option_details[1], $option_details[2] . '<br />', $option_details[1]);
                        }
                        $form->addGroup($group, 'extra_' . $field_details[1], $field_details[3], '');
                        break;
                    case USER_FIELD_TYPE_SELECT :
                        $options = array();
                        foreach ($field_details[9] as $option_id => $option_details) {
                            $options[$option_details[1]] = $option_details[2];
                        }
                        $form->addElement('select', 'extra_' . $field_details[1], $field_details[3] . $required, $options, '');
                        break;
                    case USER_FIELD_TYPE_SELECT_MULTIPLE :
                        $options = array();
                        foreach ($field_details[9] as $option_id => $option_details) {
                            $options[$option_details[1]] = $option_details[2];
                        }
                        $form->addElement('select', 'extra_' . $field_details[1], $field_details[3], $options, array(
                            'multiple' => 'multiple'));
                        break;
                    case USER_FIELD_TYPE_DATE :
                        $form->addElement('datepickerdate', 'extra_' . $field_details[1], $field_details[3], array(
                            'form_name' => 'user_add'));
                        $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
                        $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
                        $form->setDefaults($defaults);
                        $form->applyFilter('theme', 'trim');
                        break;
                    case USER_FIELD_TYPE_DATETIME :
                        $form->addElement('datepicker', 'extra_' . $field_details[1], $field_details[3], array(
                            'form_name' => 'user_add'));
                        $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
                        $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
                        $form->setDefaults($defaults);
                        $form->applyFilter('theme', 'trim');
                        break;
                    case USER_FIELD_TYPE_DOUBLE_SELECT :
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
                        // recoding the selected values for double : if the
                        // user has selected certain values, we have to assign
                        // them to the correct select form
                        if (key_exists('extra_' . $field_details[1], $extra_data)) {
                            // exploding all the selected values (of both select
                            // forms)
                            $selected_values = explode(';', $extra_data['extra_' . $field_details[1]]);
                            $extra_data['extra_' . $field_details[1]] = array();

                            // looping through the selected values and assigning
                            // the selected values to either the first or second
                            // select form
                            foreach ($selected_values as $key => $selected_value) {
                                if (key_exists($selected_value, $values[0])) {
                                    $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1]] = $selected_value;
                                } else {
                                    $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1] . '*'] = $selected_value;
                                }
                            }
                        }
                        break;
                    case USER_FIELD_TYPE_DIVIDER :
                        $form->addElement('static', $field_details[1], '<br /><strong>' . $field_details[3] . '</strong>');
                        break;
                }
            }
        }
        // Company
        //$form->addElement( 'text', 'company', get_lang('Company') , array ('size' => '40' ) );
        // Email

        $form->addElement('text', 'email', get_lang('Email') . ' <span class="sym-error">*</span>', array(
            'size' => '40'));
        $form->addRule('email', get_lang('EmailWrong'), 'email');
        $form->addRule('email', get_lang('EmailWrong'), 'required');
        $form->addRule('email', get_lang('ThisFieldIsRequired'), 'required');

        // If isn't user logged then add this rule    
        if (api_get_user_id() == 0) {
            // Add rule to make one the email field 
            $form->addRule('email', get_lang('EmailTaken'), 'email_available');
        }

        // Confirmation email
        $form->addElement('text', 'email2', get_lang('ConfirmationEmail') . ' <span class="sym-error">*</span>', array(
            'size' => '40','autocomplete' => 'off'));
        $form->addRule('email2', get_lang('EmailWrong'), 'email');
        $form->addRule('email2', get_lang('EmailWrong'), 'required');
        $form->addRule(array(
            'email', 'email2'), get_lang('EmailsNotMatch'), 'compare');

        $form->addElement('html', '</br></br></br>');
        $select_level = array();
        $navigator_info = api_get_navigator();
        echo '<style>.pull-bottom .formw{margin:0px !important; padding:0px !important;}.pull-bottom .cusformw-content {padding:0px !important;} .form_required {margin:0px !important; padding:0px !important; height:0px !important;} input[type="text"], input[type="password"] {height: auto !important;} #registration {margin-bottom:100px;}</style>';
        $form->addElement('html', '<div class="row">
                <div class="label"></div>
                <div class="formw" style="text-align:left; margin-right:150px;"><span class="form_required"> *</span><small>' . str_replace('*', '<span class="form_required"> *</span>', get_lang('FieldRequired')) . '</small></div>
                </div>');
           
        $form->addElement ('html','<div class="pull-bottom" style="border:1px solid transparent">');
       
        if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] >= '6')) {
            $html_results_enabled[] = FormValidator::createElement('submit', 'submit', get_lang('Ok'), 'class="btnUpload" style="float: right;    height: 50px !important;     margin-left: 20px;    margin-top: 2px;"');
            if($_SESSION['shopping_cart']['steps'][1]){
				$html_results_enabled[] = FormValidator::createElement('submit', 'submit_plus', get_lang('Previous'), 'class="freshbutton-blue" style="padding-left:20px;; padding-right:20px;"');
				}
        } else {
            
            $html_results_enabled[] = FormValidator::createElement('style_submit_button', 'submit', get_lang('Ok'), 'class="btnUpload" style="float: right;    height: 50px !important;     margin-left: 20px;    margin-top: 0;"');
            if($_SESSION['shopping_cart']['steps'][1]){
				$html_results_enabled[] = FormValidator::createElement('style_submit_button', '', get_lang('Previous'), 'class="freshbutton-blue" onclick="prev_step(\'' . api_get_path(WEB_CODE_PATH) . 'payment/checkout.php?id=' . $session['cat_id'] . '&prev=1' . '\');return false;"');
				}
        }
        $form->addElement('html','<div class="clear"></div>');
        //var_dump($html_results_enabled);
        $form->addGroup($html_results_enabled);

        $user_data_info = Usermanager :: get_user_info_by_id($_SESSION['_user']['user_id'], true);
        //global $charset;
        $defaults['firstname'] = isset($session['student_info']['firstname']) ? $session['student_info']['firstname'] : ($user_data_info['firstname']);
        //$defaults['firstname'] = api_convert_encoding($defaults['firstname'], $charset,  mb_detect_encoding($defaults['firstname']));
        $defaults['lastname'] = isset($session['student_info']['lastname']) ? $session['student_info']['lastname'] : ($user_data_info['lastname']);
        //$defaults['lastname'] = api_convert_encoding($defaults['lastname'], $charset,  mb_detect_encoding($defaults['lastname']));
        $defaults['email'] = isset($session['student_info']['email']) ? $session['student_info']['email'] : ($user_data_info['email']);
        $defaults['email2'] = isset($session['student_info']['email2']) ? $session['student_info']['email2'] : ($user_data_info['email']);
        $defaults['country'] = isset($session['student_info']['country']) ? $session['student_info']['country'] : ($user_data_info['country_code']);
        $defaults['civility'] = isset($session['student_info']['civility']) ? $session['student_info']['civility'] : ($user_data_info['civility']);
        $defaults['phone'] = isset($session['student_info']['phone']) ? $session['student_info']['phone'] : ($user_data_info['phone']);

        (!empty($user_data_info['extra']['organization']) || !empty($user_data_info['extra']['tva_id'])) ? $session['student_info']['identification'] = 1 : '';

        // extra default values
        $defaults['extra_street'] = isset($session['student_info']['extra_street']) ? $session['student_info']['extra_street'] : $user_data_info['extra']['street'];
        $defaults['extra_addressline2'] = isset($session['student_info']['extra_addressline2']) ? $session['student_info']['extra_addressline2'] : $user_data_info['extra']['addressline2'];
        $defaults['extra_zipcode'] = isset($session['student_info']['extra_zipcode']) ? $session['student_info']['extra_zipcode'] : $user_data_info['extra']['zipcode'];
        $defaults['extra_city'] = isset($session['student_info']['extra_city']) ? $session['student_info']['extra_city'] : $user_data_info['extra']['city'];
        $defaults['extra_organization'] = isset($session['student_info']['extra_organization']) ? $session['student_info']['extra_organization'] : $user_data_info['extra']['organization'];
        $defaults['extra_phone'] = isset($session['student_info']['extra_phone']) ? $session['student_info']['extra_phone'] : '';
        $defaults['extra_tva_id'] = isset($session['student_info']['extra_tva_id']) ? $session['student_info']['extra_tva_id'] : $user_data_info['extra']['tva_id'];

        $defaults['identification'] = isset($session['student_info']['identification']) ? $session['student_info']['identification'] : '';

        $form->setDefaults($defaults);

        return $form;
    }

    public function getFormCheckoutRegistrationForSession($session) {

        $form = new FormValidator('registration', 'post', $urlForm);

        $form->addElement('hidden', 'cat_id', intval($_REQUEST['id']));

        $civilities = array(
            '' => '--', get_lang('Mr') => get_lang('Mr'), get_lang('Mrs') => get_lang('Mrs'), get_lang('Miss') => get_lang('Miss'));
        $form->addElement('select', 'civility', get_lang('Civility') . ' <span class="sym-error">*</span>', $civilities, 'style="width:250px;"');


        // Lastname
        $form->addElement('text', 'lastname', get_lang('LastName') . ' <span class="sym-error">*</span>', 'style="width:250px;"');
        $form->applyFilter('lastname', 'html_filter');
        $form->applyFilter('lastname', 'trim');
        $form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');

        $form->addElement('text', 'firstname', get_lang('FirstName') . ' <span class="sym-error">*</span>', 'class="focus" style="width:250px;"');
        $form->applyFilter('firstname', 'html_filter');
        $form->applyFilter('firstname', 'trim');
        $form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');

        $countries = LanguageManager::get_countries();
        $countries = array(
            '' => '--') + $countries;
        $form->addElement('select', 'country', get_lang('Country') . ' <span class="sym-error">*</span>', $countries, 'style="width:250px;"');

        // EXTRA FIELDS
        $extra = UserManager::get_extra_fields(0, 50, 5, 'ASC', false, 2);
        $extra_data = UserManager::get_extra_user_data(0, true);
        $display_vat = true;
        foreach ($extra as $id => $field_details) {

            if ($field_details[6] == 1) { // only show extra fields that are
                // visible
                switch ($field_details[2]) {
                    case USER_FIELD_TYPE_TEXT :
                        if (isset($_GET['iden']) && isset($_GET['wish']) && intval($_GET['iden']) === 0 && (intval($_GET['wish']) === 0 || intval($_GET['wish']) === 1) && $field_details[1] == 'tva_id') {
                            $display_vat = false;
                            break;
                        }

                        $required = '';
                        if ($field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'phone') {
                            $required = ' <span class="sym-error">*</span>';
                        }
                        $form->addElement('text', 'extra_' . $field_details[1], $field_details[3] . $required, array(
                            'size' => 40));
                        $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
                        $form->applyFilter('extra_' . $field_details[1], 'trim');
                        if ($field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'phone') {
                            $form->addRule('extra_' . $field_details[1], get_lang('ThisFieldIsRequired'), 'required');
                        }

                        break;
                    case USER_FIELD_TYPE_TEXTAREA :
                        $form->add_html_editor('extra_' . $field_details[1], $field_details[3], false, false, array(
                            'ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130'));
                        // $form->addElement('textarea',
                        // 'extra_'.$field_details[1], $field_details[3],
                        // array('size' => 80));
                        $form->applyFilter('extra_' . $field_details[1], 'stripslashes');
                        $form->applyFilter('extra_' . $field_details[1], 'trim');
                        break;
                    case USER_FIELD_TYPE_RADIO :
                        $group = array();
                        foreach ($field_details[9] as $option_id => $option_details) {
                            $options[$option_details[1]] = $option_details[2];
                            $group[] = & HTML_QuickForm::createElement('radio', 'extra_' . $field_details[1], $option_details[1], $option_details[2] . '<br />', $option_details[1]);
                        }
                        $form->addGroup($group, 'extra_' . $field_details[1], $field_details[3], '');
                        break;
                    case USER_FIELD_TYPE_SELECT :
                        $options = array();
                        foreach ($field_details[9] as $option_id => $option_details) {
                            $options[$option_details[1]] = $option_details[2];
                        }
                        $form->addElement('select', 'extra_' . $field_details[1], $field_details[3] . $required, $options, '');
                        break;
                    case USER_FIELD_TYPE_SELECT_MULTIPLE :
                        $options = array();
                        foreach ($field_details[9] as $option_id => $option_details) {
                            $options[$option_details[1]] = $option_details[2];
                        }
                        $form->addElement('select', 'extra_' . $field_details[1], $field_details[3], $options, array(
                            'multiple' => 'multiple'));
                        break;
                    case USER_FIELD_TYPE_DATE :
                        $form->addElement('datepickerdate', 'extra_' . $field_details[1], $field_details[3], array(
                            'form_name' => 'user_add'));
                        $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
                        $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
                        $form->setDefaults($defaults);
                        $form->applyFilter('theme', 'trim');
                        break;
                    case USER_FIELD_TYPE_DATETIME :
                        $form->addElement('datepicker', 'extra_' . $field_details[1], $field_details[3], array(
                            'form_name' => 'user_add'));
                        $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption('minYear', 1900);
                        $defaults['extra_' . $field_details[1]] = date('Y-m-d 12:00:00');
                        $form->setDefaults($defaults);
                        $form->applyFilter('theme', 'trim');
                        break;
                    case USER_FIELD_TYPE_DOUBLE_SELECT :
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
                        // recoding the selected values for double : if the
                        // user has selected certain values, we have to assign
                        // them to the correct select form
                        if (key_exists('extra_' . $field_details[1], $extra_data)) {
                            // exploding all the selected values (of both select
                            // forms)
                            $selected_values = explode(';', $extra_data['extra_' . $field_details[1]]);
                            $extra_data['extra_' . $field_details[1]] = array();

                            // looping through the selected values and assigning
                            // the selected values to either the first or second
                            // select form
                            foreach ($selected_values as $key => $selected_value) {
                                if (key_exists($selected_value, $values[0])) {
                                    $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1]] = $selected_value;
                                } else {
                                    $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1] . '*'] = $selected_value;
                                }
                            }
                        }
                        break;
                    case USER_FIELD_TYPE_DIVIDER :
                        $form->addElement('static', $field_details[1], '<br /><strong>' . $field_details[3] . '</strong>');
                        break;
                }
            }
        }
        // Company
        //$form->addElement( 'text', 'company', get_lang( 'Company' ) , array ('size' => '40' ) );
        // Email
        $form->addElement('text', 'email', get_lang('Email') . ' <span class="sym-error">*</span>', array(
            'size' => '40'));
        $form->addRule('email', get_lang('EmailWrong'), 'email');
        $form->addRule('email', get_lang('EmailWrong'), 'required');


        $email = $_REQUEST['email'];

        if ($email != "") {
            $main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
            $sql_query = 'SELECT email FROM ' . $main_user_table . ' WHERE email = "' . Database::escape_string($email) . '"';
            $sql_result = Database::query($sql_query, __FILE__, __LINE__);
            $result = Database :: fetch_array($sql_result);
            if ($result > 0) {
                $form->addElement('html', '<br/><span class="form_error">' . get_lang('xEmailAlreadyExists') . '</span>');
            }
        }

        // Confirmation email
        $form->addElement('text', 'email2', get_lang('ConfirmationEmail') . ' <span class="sym-error">*</span>', array(
            'size' => '40'));
        $form->addRule('email2', get_lang('EmailWrong'), 'email');
        $form->addRule('email2', get_lang('EmailWrong'), 'required');
        $form->addRule(array(
            'email', 'email2'), get_lang('EmailsNotMatch'), 'compare');

        $form->addElement('html', '</br></br></br>');
        $select_level = array();
        $navigator_info = api_get_navigator();
        if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] >= '6')) {
            $html_results_enabled[] = FormValidator::createElement('submit', 'submit_plus', get_lang('Previous'), 'style="background-color: #4171B5;;height:32px;border:1px solid #b8b8b6;text-transform:uppercase;font-weight:bold;color:#fff;"');
            $html_results_enabled[] = FormValidator::createElement('submit', 'submit', get_lang('Ok'), 'style="background-color: #4171B5;;height:32px;border:1px solid #b8b8b6;text-transform:uppercase;font-weight:bold;color:#fff;"');
        } else {
            $html_results_enabled[] = FormValidator::createElement('style_submit_button', 'submit_plus', get_lang('Previous'), '');
            $html_results_enabled[] = FormValidator::createElement('style_submit_button', 'submit', get_lang('Ok'), '');
        }
        $form->addGroup($html_results_enabled);

        $form->addElement('html', '<div class="row">
                <div class="label"></div>
                <div class="formw"><small>' . str_replace('*', '<span class="form_required"> *</span>', get_lang('FieldRequired')) . '</small></div>
                </div>');

        $defaults['firstname'] = isset($session['student_info']['firstname']) ? $session['student_info']['firstname'] : '';
        $defaults['lastname'] = isset($session['student_info']['lastname']) ? $session['student_info']['lastname'] : '';
        $defaults['email'] = isset($session['student_info']['email']) ? $session['student_info']['email'] : '';
        $defaults['email2'] = isset($session['student_info']['email2']) ? $session['student_info']['email2'] : '';
        $defaults['country'] = isset($session['student_info']['country']) ? $session['student_info']['country'] : '';
        $defaults['civility'] = isset($session['student_info']['civility']) ? $session['student_info']['civility'] : '';

        // extra default values
        $defaults['extra_street'] = isset($session['student_info']['extra_street']) ? $session['student_info']['extra_street'] : '';
        $defaults['extra_addressline2'] = isset($session['student_info']['extra_addressline2']) ? $session['student_info']['extra_addressline2'] : '';
        $defaults['extra_zipcode'] = isset($session['student_info']['extra_zipcode']) ? $session['student_info']['extra_zipcode'] : '';
        $defaults['extra_city'] = isset($session['student_info']['extra_city']) ? $session['student_info']['extra_city'] : '';
        $defaults['extra_organization'] = isset($session['student_info']['extra_organization']) ? $session['student_info']['extra_organization'] : '';
        $defaults['extra_phone'] = isset($session['student_info']['extra_phone']) ? $session['student_info']['extra_phone'] : '';

        $form->setDefaults($defaults);

        return $form;
    }

    public function getFormLearner(array $session, $request) {
        $iden = $request['iden'];
        $wish = $request['wish'];

        $urlForm = api_get_path(WEB_PATH) . 'main/payment/checkout_3_registration.php';
        $form = new FormValidator('registration_step3', 'post', $urlForm . '?iden=' . $iden . '&wish=' . $wish . '&id=' . intval($_SESSION['cat_id']) . '&next=3');
        /*  $form->addElement( 'hidden', 'cat_id', intval( $_REQUEST['id'] ) );

          $civilities = array (
          '' => '--', get_lang( 'Mr' ) => get_lang( 'Mr' ), get_lang( 'Mrs' ) => get_lang( 'Mrs' ), get_lang( 'Miss' ) => get_lang( 'Miss' ) );
          $form->addElement( 'select', 'civility', get_lang( 'Civility' ) . ' <span class="sym-error">*</span>', $civilities, 'style="width:250px;"' );

          // Lastname
          $form->addElement( 'text', 'lastname', get_lang( 'LastName' ) . ' <span class="sym-error">*</span>', 'style="width:250px;"' );
          $form->applyFilter( 'lastname', 'html_filter' );
          $form->applyFilter( 'lastname', 'trim' );
          $form->addRule( 'lastname', get_lang( 'ThisFieldIsRequired' ), 'required' );

          $form->addElement( 'text', 'firstname', get_lang( 'FirstName' ) . ' <span class="sym-error">*</span>', 'class="focus" style="width:250px;"' );
          $form->applyFilter( 'firstname', 'html_filter' );
          $form->applyFilter( 'firstname', 'trim' );
          $form->addRule( 'firstname', get_lang( 'ThisFieldIsRequired' ), 'required' );

          $countries = LanguageManager::get_countries();
          $countries = array (
          0 => '--' ) + $countries;
          $form->addElement( 'select', 'country', get_lang( 'Country' ) . ' <span class="sym-error">*</span>', $countries, 'style="width:250px;"' );

          // EXTRA FIELDS
          $extra = UserManager::get_extra_fields( 0, 50, 5, 'ASC', false, 2 );
          $extra_data = UserManager::get_extra_user_data( 0, true );
          $display_vat = true;
          foreach ( $extra as $id => $field_details )
          {

          // Don't display phone when user is not payer
          if ( $iden == 1 || ($iden == 0 && $wish == 1) )
          {
          if ( $field_details[1] == 'phone' )
          {
          continue;
          }
          }

          if ( $field_details[6] == 1 )
          { // only show extra fields that are
          // visible
          switch ( $field_details[2] )
          {
          case USER_FIELD_TYPE_TEXT :
          if ( isset( $_GET['iden'] ) && isset( $_GET['wish'] ) && intval( $_GET['iden'] ) === 0 && (intval( $_GET['wish'] ) === 0 || intval( $_GET['wish'] ) === 1) && $field_details[1] == 'tva_id' )
          {
          $display_vat = false;
          break;
          }

          $required = '';
          if ( $field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'phone' )
          {
          $required = ' <span class="sym-error">*</span>';
          }
          $form->addElement( 'text', 'extra_' . $field_details[1], $field_details[3] . $required, array (
          'size' => 40 ) );
          $form->applyFilter( 'extra_' . $field_details[1], 'stripslashes' );
          $form->applyFilter( 'extra_' . $field_details[1], 'trim' );
          if ( $field_details[1] == 'street' || $field_details[1] == 'city' || $field_details[1] == 'phone' )
          {
          $form->addRule( 'extra_' . $field_details[1], get_lang( 'ThisFieldIsRequired' ), 'required' );
          }

          break;
          case USER_FIELD_TYPE_TEXTAREA :
          $form->add_html_editor( 'extra_' . $field_details[1], $field_details[3], false, false, array (
          'ToolbarSet' => 'Profile', 'Width' => '100%', 'Height' => '130' ) );
          // $form->addElement('textarea',
          // 'extra_'.$field_details[1], $field_details[3],
          // array('size' => 80));
          $form->applyFilter( 'extra_' . $field_details[1], 'stripslashes' );
          $form->applyFilter( 'extra_' . $field_details[1], 'trim' );
          break;
          case USER_FIELD_TYPE_RADIO :
          $group = array ();
          foreach ( $field_details[9] as $option_id => $option_details )
          {
          $options[$option_details[1]] = $option_details[2];
          $group[] = & HTML_QuickForm::createElement( 'radio', 'extra_' . $field_details[1], $option_details[1], $option_details[2] . '<br />', $option_details[1] );
          }
          $form->addGroup( $group, 'extra_' . $field_details[1], $field_details[3], '' );
          break;
          case USER_FIELD_TYPE_SELECT :
          $options = array ();
          foreach ( $field_details[9] as $option_id => $option_details )
          {
          $options[$option_details[1]] = $option_details[2];
          }
          $form->addElement( 'select', 'extra_' . $field_details[1], $field_details[3] . $required, $options, '' );
          break;
          case USER_FIELD_TYPE_SELECT_MULTIPLE :
          $options = array ();
          foreach ( $field_details[9] as $option_id => $option_details )
          {
          $options[$option_details[1]] = $option_details[2];
          }
          $form->addElement( 'select', 'extra_' . $field_details[1], $field_details[3], $options, array (
          'multiple' => 'multiple' ) );
          break;
          case USER_FIELD_TYPE_DATE :
          $form->addElement( 'datepickerdate', 'extra_' . $field_details[1], $field_details[3], array (
          'form_name' => 'user_add' ) );
          $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption( 'minYear', 1900 );
          $defaults['extra_' . $field_details[1]] = date( 'Y-m-d 12:00:00' );
          $form->setDefaults( $defaults );
          $form->applyFilter( 'theme', 'trim' );
          break;
          case USER_FIELD_TYPE_DATETIME :
          $form->addElement( 'datepicker', 'extra_' . $field_details[1], $field_details[3], array (
          'form_name' => 'user_add' ) );
          $form->_elements[$form->_elementIndex['extra_' . $field_details[1]]]->setLocalOption( 'minYear', 1900 );
          $defaults['extra_' . $field_details[1]] = date( 'Y-m-d 12:00:00' );
          $form->setDefaults( $defaults );
          $form->applyFilter( 'theme', 'trim' );
          break;
          case USER_FIELD_TYPE_DOUBLE_SELECT :
          $values = array ();
          foreach ( $field_details[9] as $key => $element )
          {
          if ( $element[2][0] == '*' )
          {
          $values['*'][$element[0]] = str_replace( '*', '', $element[2] );
          } else
          {
          $values[0][$element[0]] = $element[2];
          }
          }
          $group = '';
          $group[] = & HTML_QuickForm::createElement( 'select', 'extra_' . $field_details[1], '', $values[0], '' );
          $group[] = & HTML_QuickForm::createElement( 'select', 'extra_' . $field_details[1] . '*', '', $values['*'], '' );
          $form->addGroup( $group, 'extra_' . $field_details[1], $field_details[3], '&nbsp;' );
          if ( $field_details[7] == 0 )
          $form->freeze( 'extra_' . $field_details[1] );
          // recoding the selected values for double : if the
          // user has selected certain values, we have to assign
          // them to the correct select form
          if ( key_exists( 'extra_' . $field_details[1], $extra_data ) )
          {
          // exploding all the selected values (of both select
          // forms)
          $selected_values = explode( ';', $extra_data['extra_' . $field_details[1]] );
          $extra_data['extra_' . $field_details[1]] = array ();

          // looping through the selected values and assigning
          // the selected values to either the first or second
          // select form
          foreach ( $selected_values as $key => $selected_value )
          {
          if ( key_exists( $selected_value, $values[0] ) )
          {
          $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1]] = $selected_value;
          } else
          {
          $extra_data['extra_' . $field_details[1]]['extra_' . $field_details[1] . '*'] = $selected_value;
          }
          }
          }
          break;
          case USER_FIELD_TYPE_DIVIDER :
          $form->addElement( 'static', $field_details[1], '<br /><strong>' . $field_details[3] . '</strong>' );
          break;
          }
          }
          }

          // Email
          $form->addElement( 'text', 'email', get_lang( 'Email' ) . ' <span class="sym-error">*</span>', array (
          'size' => '40' ) );
          $form->addRule( 'email', get_lang( 'EmailWrong' ), 'email' );
          $form->addRule( 'email', get_lang( 'EmailWrong' ), 'required' );

          // Confirmation email
          $form->addElement( 'text', 'email2', get_lang( 'ConfirmationEmail' ) . ' <span class="sym-error">*</span>', array (
          'size' => '40' ) );
          $form->addRule( 'email2', get_lang( 'EmailWrong' ), 'email' );
          $form->addRule( 'email2', get_lang( 'EmailWrong' ), 'required' );
          $form->addRule( array (
          'email', 'email2' ), get_lang( 'EmailsNotMatch' ), 'compare' );

          $form->addElement( 'html', '</br></br></br>' );
          $select_level = array ();
          $navigator_info = api_get_navigator();
          if ( $navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] >= '6') )
          {
          $html_results_enabled[] = FormValidator::createElement( 'submit', 'submit_plus', get_lang( 'Previous' ), 'style="background-color: #4171B5;;height:32px;border:1px solid #b8b8b6;text-transform:uppercase;font-weight:bold;color:#fff;"' );
          $html_results_enabled[] = FormValidator::createElement( 'submit', 'submit', get_lang( 'Ok' ), 'style="background-color: #4171B5;;height:32px;border:1px solid #b8b8b6;text-transform:uppercase;font-weight:bold;color:#fff;"' );
          } else
          {
          $html_results_enabled[] = FormValidator::createElement( 'style_submit_button', 'submit_plus', get_lang( 'Previous' ), '' );
          $html_results_enabled[] = FormValidator::createElement( 'style_submit_button', 'submit', get_lang( 'Ok' ), '' );
          }
          $form->addGroup( $html_results_enabled );

          $form->addElement( 'html', '<div class="row">
          <div class="label"></div>
          <div class="formw"><small>' . str_replace( '*', '<span class="form_required"> *</span>', get_lang( 'FieldRequired' ) ) . '</small></div>
          </div>' );

          // $form->add_fr_zipcode_required_rule(array('extra_zipcode',
          // 'extra_country'), get_lang('ZipcodeForThisCountryIsRequired'),
          // 'fr_zipcode_required');
          // $form->addRule(array('extra_zipcode', 'extra_country'),
          // get_lang('ZipcodeMustBe5digits'), 'fr_zipcode');
          $defaults['firstname'] = isset( $_SESSION['user_info']['firstname'] ) ? $_SESSION['user_info']['firstname'] : '';
          $defaults['lastname'] = isset( $_SESSION['user_info']['lastname'] ) ? $_SESSION['user_info']['lastname'] : '';
          $defaults['email'] = isset( $_SESSION['user_info']['email'] ) ? $_SESSION['user_info']['email'] : '';
          $defaults['email2'] = isset( $_SESSION['user_info']['email2'] ) ? $_SESSION['user_info']['email2'] : '';
          $defaults['country'] = isset( $_SESSION['user_info']['country'] ) ? $_SESSION['user_info']['country'] : '';
          $defaults['civility'] = isset( $_SESSION['user_info']['civility'] ) ? $_SESSION['user_info']['civility'] : '';

          // extra default values
          $defaults['extra_street'] = isset( $_SESSION['user_info']['extra_street'] ) ? $_SESSION['user_info']['extra_street'] : '';
          $defaults['extra_addressline2'] = isset( $_SESSION['user_info']['extra_addressline2'] ) ? $_SESSION['user_info']['extra_addressline2'] : '';
          $defaults['extra_zipcode'] = isset( $_SESSION['user_info']['extra_zipcode'] ) ? $_SESSION['user_info']['extra_zipcode'] : '';
          $defaults['extra_city'] = isset( $_SESSION['user_info']['extra_city'] ) ? $_SESSION['user_info']['extra_city'] : '';
          $defaults['extra_organization'] = isset( $_SESSION['user_info']['extra_organization'] ) ? $_SESSION['user_info']['extra_organization'] : '';
          if ( $iden == 0 && $wish == 0 )
          {
          $defaults['extra_phone'] = isset( $_SESSION['user_info']['extra_phone'] ) ? $_SESSION['user_info']['extra_phone'] : '';
          }

          $form->setDefaults( $defaults );
         */
        return $form;
    }

    public function getTaxInfo() {
        $taxValue = api_number_format(api_get_setting('e_commerce_catalog_tax'));
        $taxValue = floatval($taxValue / 100);
        $response = array('taxName' => get_lang('Taxes'), 'taxRate' => $taxValue);
        return $response;
    }

    public function processFormDataFromCheckout2(FormValidator $form, &$session) {
        $_SESSION['student_info'] = $form->exportValues();
        return true;
    }
    public function registerUserShop(){
        $picture_uri = '';
        $lastname = $_REQUEST['lastname'];
        $firstname = $_REQUEST['firstname'];
        //$official_code = $_REQUEST['official_code'];
        $official_code = '';
        $email = $_REQUEST['email'];
        //$phone = $_REQUEST['phone'];
        $phone = '';
        //$username = $_REQUEST['username'];
        $username = $_REQUEST['email'];
        $timezone = '';
        //$status = intval($_REQUEST['status']);
        $status = intval('5');
        //$language = $_REQUEST['language'];
        $language = '';
        $platform_admin = intval('5');
        $send_mail = $_REQUEST['email'];
        $hr_dept_id = intval('1');
            $auth_source = PLATFORM_AUTH_SOURCE;
            $password = api_generate_password(8);
            $expiration_date = '0000-00-00 00:00:00';
        $active = intval('1');
            $user_id = UserManager::create_user($firstname, $lastname, $status, $email, $username, $password, $official_code, $language, $phone, $picture_uri, $auth_source, $expiration_date, $active, $hr_dept_id, null, null, null, null, $timezone);
            if (!empty($email) && $send_mail) {
                // Process for send the mail to the new user
                $recipient_name = api_get_person_name($firstname, $lastname, null, PERSON_NAME_EMAIL_ADDRESS);
                $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
                $email_admin = api_get_setting('emailAdministrator');
                $subject = '[' . api_get_setting('siteName') . '] ' . get_lang('YourReg') . ' ' . api_get_setting('siteName');
                UserManager::send_mail_to_new_user($recipient_name, $email, $subject, $username, $password, $sender_name, $email_admin, "");
            }
            return $user_id;
        }

}
