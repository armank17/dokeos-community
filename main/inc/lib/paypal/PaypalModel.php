<?php

require_once dirname( __FILE__ ) . '/../../global.inc.php';

require_once api_get_path( SYS_PATH ) . 'main/inc/lib/paypal/php_nvp_samples/CallerService.php';

// including additional libraries
require_once api_get_path( LIBRARY_PATH ) . 'sessionmanager.lib.php';
require_once api_get_path( LIBRARY_PATH ) . 'language.lib.php';
require_once api_get_path( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php';
require_once api_get_path( LIBRARY_PATH ) . 'usermanager.lib.php';
require_once api_get_path( LIBRARY_PATH ) . 'fileUpload.lib.php';
require_once api_get_path( LIBRARY_PATH ) . 'mail.lib.inc.php';
require_once api_get_path( LIBRARY_PATH ) . 'course.lib.php';


class PaypalModel
{
    
    protected $_response;
    
    protected $_status = FALSE;
    
    protected $_resArray = array ();
    
    protected $_mySession = array ();
    
    protected $_product = NULL;
    
    protected $_user = NULL;
    protected $_userId = NULL;
    
    const PAYMENT_TYPE_ONLINE = 1;
    
    const PAYMENT_TYPE_CHEQUE = 2;
    
    public static function create()
    {
        return new PaypalModel();
    }
    
    public function setProduct( $product )
    {
        $this->_product = $product;
    }
    
    public function setResponse( $response )
    {
        $this->_response = $response;
    }
    
    public function getStatus()
    {
        return $this->_status;
    }
    
    public function getUserId()
    {
        return $this->_userId;
    }
    
    public function createNvpstr( $session, $request )
    {
        
        $this->_user = $session['_user'];
        
        $firstName = urlencode( $request['firstName'] );
        $lastName = urlencode( $request['lastName'] );
        $creditCardType = urlencode( $request['creditCardType'] );
        $creditCardNumber = urlencode( $request['creditCardNumber'] );
        $expDateMonth = urlencode( $request['expDateMonth'] );
        
        // Month must be padded with leading zero
        $padDateMonth = str_pad( $expDateMonth, 2, '0', STR_PAD_LEFT );
        
        $expDateYear = urlencode( $request['expDateYear'] );
        $cvv2Number = urlencode( $request['cvv2Number'] );
        $address1 = urlencode( $request['address1'] );
        $address2 = urlencode( $request['address2'] );
        $city = urlencode( $request['city'] );
        $state = urlencode( '' );
        $zip = urlencode( $request['zip'] );
        $amount = urlencode( $request['amount'] );
        // $currencyCode=urlencode($_POST['currency']);
        $currencyCode = urlencode("USD");
        $paymentType = urlencode( $request['paymentType'] );
        $countryCode = urlencode( $request['cboCountry'] );
        /*
         * Construct the request string that will be sent to PayPal. The
         * variable $nvpstr contains all the variables and is a name value pair
         * string with & as a delimiter
         */               
        if($session['student_info']['payment_method']!='2'){
           $nvpstr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=" . $padDateMonth . $expDateYear . "&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&CITY=$city&STATE=$state" . "&ZIP=$zip&COUNTRYCODE=$countryCode&CURRENCYCODE=$currencyCode";             
        }else{
           $nvpstr = "&PAYMENTACTION=$paymentType&AMT=$amount&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&CITY=$city&STATE=$state"."&ZIP=$zip&COUNTRYCODE=$countryCode&CURRENCYCODE=$currencyCode"; 
        }
       
        return $nvpstr;
    }
    
    public function processInvoice( $nvpstr )
    {
        
        /*
         * Make the API call to PayPal, using API signature. The API response is
         * stored in an associative array called $resArray
         */
            return hash_call( "doDirectPayment", $nvpstr );
    }
    
    public function processForm( $session, $request, $post = null )
    {
        $response = array ();
        
        $nvpstr = $this->createNvpstr( $session, $request );
        
        $this->_resArray = $this->processInvoice( $nvpstr );
        
        $ack = strtoupper( $this->_resArray["ACK"] );
        if ( $ack == "SUCCESS" )
        {
            $this->_status = TRUE;
            $response['completed'] = TRUE;
            $response['transactionId'] = $this->_resArray['TRANSACTIONID'];
            $response['message'] = $ack;
        } else
        {
            $response['completed'] = FALSE;
            $response['transactionId'] = 0;
            $response['message'] = $this->_resArray['L_LONGMESSAGE0'];;
        }
        
        $response['details'] = $this->_resArray;
        
        return $response;
    }
    
    public function processFormSession( $session, $request, $post = null )
    {
        
        $affectedRows = 0;
        $this->_mySession = $session;
        
        // making the transaction in paypal
        $nvpstr = $this->createNvpstr( $session, $request );
        $this->_resArray = $this->processInvoice( $nvpstr );
        
        // parameters for saving transaction
        $payType = 1;
        $params = array ();
        
        // if user is new, user_id equals 0
        $params['user_id'] = intval( api_get_user_id(), 10 );
        $params['sess_id'] = count( $this->_product );
        $params['pay_type'] = $payType;
        $params['pay_data'] = $this->_resArray;
        $params['transaction_id'] = $this->_resArray['TRANSACTIONID'];
        $this->_userId = $params['user_id'];        
        
        $ack = strtoupper( $this->_resArray["ACK"] );
        
        if ( $session['nvpReqArray']['METHOD'] == 'doDirectPayment' )
        {
            $payType = PaypalModel::PAYMENT_TYPE_ONLINE;
        }
        
        if ( $ack == "SUCCESS" )
        {
            $this->_status = TRUE;
            
            $lastname = $session['user_info']['lastname'];
            $firstname = $session['user_info']['firstname'];
            $status = 5;
            $hash = api_generate_password( 3 );
            $part1 = $firstname[0];
            $exp_lname = explode( ' ', $lastname );
            $part2 = (is_array( $exp_lname ) && count( $exp_lname ) > 1) ? $exp_lname[0] : $lastname;
            $genera_uname = strtolower( $part1 . $part2 . $hash );
            $genera_uname = replace_accents( $genera_uname );
            $username = $genera_uname;
            $email = $session['user_info']['email'];
            $password = api_generate_password( 8 );
            // create new user if it doesn't exist
            if ( $this->_userId < 1 )
            {
               $this->_userId = UserManager::create_user( $firstname, $lastname, $status, $email, $username, $password );
               $recipient_name = $firstname . ' ' . $lastname; 
               $emailbody = $this->getContentMail($firstname, $lastname, $username, $password); 
               $emailsubject = '['.api_get_setting('siteName').'] '.get_lang('YourReg').' '.api_get_setting('siteName');
               $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
               $email_admin = api_get_setting('emailAdministrator');
               @api_mail($recipient_name, $email, $emailsubject, $emailbody, $sender_name, $email_admin);
            }
            
            // get UserId to be used when saving the sale
            $params['user_id'] = $this->_userId;
            
            $extras = array ();
            foreach ( $session['user_info'] as $key => $value )
            {
                if ( substr( $key, 0, 6 ) == 'extra_' )
                { // an extra field
                    $myres = UserManager::update_extra_field_value( $this->_userId, substr( $key, 6 ), $value );
                }
            }
            
            // check type of registration 1 = Separate / 0 == personally
            if ( $session['wish'] == 1 )
            {
                // create payer info
                if ( isset( $session['payer_info'] ) )
                {
                    $session['payer_info']['student_id'] = $this->_userId;
                    $payerId = SessionManager::save_payer_user( $session['payer_info'] );
                }
            }
            
            //api_send_mail( $request['email'], 'You have successfully registered to: ' . $this->_product['name'], 'Thanks!' );
            //$affectedRows = SessionManager::save_payment_atos( $params );
            return $this->_userId;
        }
        
        return $affectedRows;
    }
    
    /**
     * 
     */
    public function getContentMail($firstname, $lastname,$username,$password){
         global $language_interface;
         $table_emailtemplate 	= Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);	
         $sql = "SELECT * FROM $table_emailtemplate WHERE description = 'Userregistration' AND language= '".$language_interface."'";
         $result = api_sql_query($sql, __FILE__, __LINE__);
         $content = "";
         while($row = Database::fetch_array($result))
         {				
                 $content = $row['content'];
         }
         if(empty($content))
         {
              $content = get_lang('Dear')." {Name} ,\n\n";
              $content .= get_lang('YouAreReg')." {siteName} ".get_lang('WithTheFollowingSettings')."\n\n";
              $content .= get_lang('Username').": {username} \n";	
              $content .= get_lang('Pass')." :{password} \n\n";
              $content .= get_lang('Address')." {siteName} ".get_lang('Is')." - {url} \n\n";
              $content .= get_lang('Problem')."\n\n".get_lang('Formula').",\n";
              $content .= "{administratorSurname} \n";
              $content .= get_lang('Manager')."\n";
              $content .= "{administratorTelephone} \n";
              $content .= get_lang('Email')." : {emailAdministrator}";
         }
         
         $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
         $content = str_replace("/main/default_course_document", "tmp_file", $content);
         $content =  str_replace('{Name}',stripslashes(api_get_person_name($firstname, $lastname)), $content); 
         $content =  str_replace('{siteName}',api_get_setting('siteName'), $content); 
         $content =  str_replace('{username}',$username, $content); 
         $content =  str_replace('{password}',stripslashes($password), $content); 			
         $content =  str_replace('{administratorSurname}',api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname')), $content); 
         $content =  str_replace('{administratorTelephone}',api_get_setting('administratorTelephone'), $content); 
         $content =  str_replace('{emailAdministrator}',api_get_setting('emailAdministrator'), $content);
         $content = str_replace("tmp_file", $domain_server, $content);
         
         if ($_configuration['multiple_access_urls'] == true) {
              $access_url_id = api_get_current_access_url_id();
              if ($access_url_id != -1) {
                   $url = api_get_access_url($access_url_id);
                   $content =  str_replace('{url}',$url['url'], $content); 
              }
         }
         else {
              $content =  str_replace('{url}',$_configuration['root_web'], $content); 
         }
         return $content;
    }
    
    public function getResponse()
    {
        $response = '';
        if ( $this->_status == TRUE )
        {
            $response .= '<ul id="ErrorMessageList">';
            $response .= '<li> Transaction ID: ' . $this->_resArray['TRANSACTIONID'] . ' ' . $this->_resArray['AMT'] . '</li>';
            $response .= '<li> Total transaction cost: ' . $this->_resArray['CURRENCYCODE'] . ' ' . $this->_resArray['AMT'] . '</li>';
            $response .= '</ul>';
        
        } else
        {
            
            $response .= 'error!';
            
            if ( isset( $this->_mySession['curl_error_no'] ) )
            {
                $errorCode = $this->_mySession['curl_error_no'];
                $errorMessage = $this->_mySession['curl_error_msg'];
                session_unset();
                
                $response .= $errorCode . ' => ' . $errorMessage;
            } else
            {
                
                // filter messages from transaction process response
                $objIterator = new ArrayIterator( $this->_resArray );
                $errorMessages = array ();
                
                while ( $objIterator->valid() )
                {
                    if ( strpos( $objIterator->key(), 'L_LONGMESSAGE' ) === 0 )
                    {
                        $errorMessages[$objIterator->key()] = $objIterator->current();
                    }
                    $objIterator->next();
                }
                
                // generate HTML response using filtered error messages
                $response .= '<ul id="ErrorMessageList">';
                foreach ( $errorMessages as $message )
                {
                    $response .= '<li>' . $message . '</li>';
                }
                $response .= '</ul>';
                $response .= '<a href="javascript:history.go(-1)" >' . get_lang( 'ReturnTo' ) . '</a>';
            
            }
        }
        
        return $response;
    
    }

}
