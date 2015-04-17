<?php
class application_suiteManager_models_PricingModel
{
    private $_ado;
    private $_tblUser;
    private $_tblUserField;
    private $_tblUserFieldValues;
    
    
    public function __construct() {
        $this->_ado = appcore_db_DB::conn();
        
        $this->_tblUser = Database::get_main_table(TABLE_MAIN_USER);
        $this->_tblUserField = Database::get_main_table(TABLE_MAIN_USER_FIELD);        
        $this->_tblUserFieldValues = Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);                
    }
    
    public function updateUserAccount($userId, $fullName, $companyName, $email, $phone, $address, $country, $subject, $message) {        
        // We update user table
        $this->_ado->Execute("UPDATE $this->_tblUser SET email = ?, phone = ? WHERE user_id = ?", array($email, $phone, $userId));
        
        // We update user extra fields
        $extra = array('fullname' => $fullName, 'company' => $companyName, 'address' => $address, 'country' => $country); 
        foreach ($extra as $fname => $fvalue) {
            // Save new fieldlabel into user_field table
            $field_id = UserManager::create_extra_field($fname, 1, $fname, '');
            // save the external system's id into user_field_value table
            $res = UserManager::update_extra_field_value($userId, $fname, $fvalue);
        }
        
        // We send the email
        $recipientName = 'Dokeos';
        $recipientEmail = 'bertrand@dokeos.net'; 
        $header = array ('Cc'=>'thomas@dokeos.net');
        $fullMessage  = '<p>'.$message.'</p>';
        $fullMessage .= '<p><strong>'.get_lang('FullName').'</strong>: '.$fullName.'</p>';
        $fullMessage .= '<p><strong>'.get_lang('Company').'</strong>: '.$companyName.'</p>';
        $fullMessage .= '<p><strong>'.get_lang('Email').'</strong>: '.$email.'</p>';
        $fullMessage .= '<p><strong>'.get_lang('Phone').'</strong>: '.$phone.'</p>';
        $fullMessage .= '<p><strong>'.get_lang('Address').'</strong>: '.$address.'</p>';
        $fullMessage .= '<p><strong>'.get_lang('Country').'</strong>: '.$country.'</p>';     
        $fullMessage .= '<p><strong>'.get_lang('Portal').'</strong>: '.api_get_path(WEB_PATH).'</p>';     
        api_mail_html($recipientName, $recipientEmail, $subject, $fullMessage, $fullName, $email, $header);        
    }
    
}
