<?php
class application_suiteManager_controllers_PricingAjax  extends application_suiteManager_controllers_Pricing 
{ 
    public function __construct() {
        parent::__construct();
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
    }
    
    public function updateAccount() {
        extract($_POST);
        $this->pricingModel->updateUserAccount($userId, $fullname, $company, $email, $phone, $address, $country, $subject, $message);
        exit;
    }
    
    public function sendPricing() {
        $this->sendEmailPricing($_POST);
        exit;
    }
}
