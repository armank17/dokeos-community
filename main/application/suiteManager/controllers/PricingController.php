<?php
class application_suiteManager_controllers_Pricing extends appcore_command_Command 
{
    private $_configuration;
    private $_sasVersion;    
    
    public $dokeosSuite;
    public $currentUserInfo;   
    public $pricingModel;
    
    public function __construct() {
        global $_configuration;
        $this->_configuration = $_configuration;
        $this->_sasVersion = (isset($this->_configuration['sas_version']) && $this->_configuration['sas_version'] === true);
        
        $this->currentUserInfo = api_get_user_info(api_get_user_id());
        
        $this->currentUserInfo['extra'] = UserManager::get_extra_user_data(api_get_user_id());
        
        $this->pricingModel = new application_suiteManager_models_PricingModel();
        
        $this->validateSession();
        $this->setTheme();
        $this->loadHtmlHeadXtra();
    }
    
    public function index() {
        $this->dokeosSuite = $this->getDokeosSuite();
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();        
        if (!$this->_sasVersion && !api_is_platform_admin()) {
            api_not_allowed();
        }      
    }
    
    public function sendEmailPricing($info) {        
        $fromName = $this->currentUserInfo['firstName'].' '.$this->currentUserInfo['lastName'];
        $fromEmail = $this->currentUserInfo['mail'];
        $emailContact = api_get_setting('set_portal_upgrade_email_contact');    
        $emailto = !empty($emailContact)?$emailContact:'christian@dokeos.net';
        $recipientName = $this->get_lang('Dokeos');
        $emailsubject = $this->get_lang('RequestUpgradePortal');
        $portal = api_get_path(WEB_PATH);
        $message = "<p>$portal wants to upgrade, bellow selected values</p>";
        
        $dokeosSuite = $this->getDokeosSuite();        
        if (!empty($info)) {
            foreach ($info['pricing'] as $variable => $suite) {
                $message .= "<p>".$this->get_lang('Product').": ".$suite['suite']."</p>";
                if (!empty($suite['attribute'])) {
                    $message .= "<p>".$this->get_lang('Attribute').": ".$suite['attribute']." ".$dokeosSuite[$variable]['attributes']['name']."</p>";
                }
                $message .= "<p>".$this->get_lang('Price').": ".$suite['price']."</p>";
            }
        }       
        api_mail_html($recipientName, $emailto, $emailsubject, $message, $fromName, $fromEmail);                
    }
    
    public function getDokeosSuite() {
        
        $suite['manager'] = array(
            'id' => 1,
            'name' => $this->get_lang('Manager'),
            'image_path' => api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/img/suite/manager.jpg',
            'short_description' => get_lang('SuiteManagerShortDescription'),
            'large_description' => get_lang('SuiteManagerLargeDescription'),
            'more_info_link' => '#',
            'default_price' => 96.80,
            'default_attribute' => '51-200',
            'attributes' => array(
                                'name' => $this->get_lang('ChooseTheNbOfUsers'),
                                'values'=> array('51-200'  => 96.80, '201-500' => 1403.60, '501-1000' => 2855.60)
                            )
        );
        
        $suite['author'] = array(
            'id' => 2,
            'name' => $this->get_lang('Author'),
            'image_path' => api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/img/suite/author.jpg',
            'short_description' => get_lang('SuiteAuthorShortDescription'),
            'large_description' => get_lang('SuiteAuthorLargeDescription'),
            'more_info_link' => '#',
            'default_price' => 130.58,
            'default_attribute' => '1',
            'attributes' => array(
                                'name' => $this->get_lang('NumberOfAuthor'),
                                'values'=> array('1' => 130.58, '2' => 929.28, '3' => 1703.68, '4' => 2429.68, '5' => 3034.68, '6-10' => 5575.68)
                            )                                        
        );
        
        $suite['live'] = array(
            'id' => 3,
            'name' => $this->get_lang('Live'),
            'image_path' => api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/img/suite/live.jpg',
            'short_description' => get_lang('SuiteLiveShortDescription'),
            'large_description' => get_lang('SuiteLiveLargeDescription'),
            'more_info_link' => '#',
            'default_price' => 72.60,
            'default_attribute' => '1-10',
            'attributes' => array(
                                'name' => $this->get_lang('SimultaneousUsers'),
                                'values'=> array('1-10' => 72.60, '11-25' => 798.60, '26-50' => 1524.60)
                            )                                        
        );
        
        $suite['shop'] = array(
            'id' => 4,
            'name' => $this->get_lang('Shop'),
            'image_path' => api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/img/suite/shop.jpg',
            'short_description' => get_lang('SuiteShopShortDescription'),
            'large_description' => get_lang('SuiteShopLargeDescription'),
            'more_info_link' => '#',
            'default_price' => 145.20
        );
        
        $suite['channel'] = array(
            'id' => 5,
            'name' => $this->get_lang('Channel'),
            'image_path' => api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/img/suite/channel.jpg',
            'short_description' => get_lang('SuiteChannelShortDescription'),
            'large_description' => get_lang('SuiteChannelLargeDescription'),
            'more_info_link' => '#',
            'default_price' => 142.78,
            'default_attribute' => '0-1',
            'attributes' => array(
                                'name' => $this->get_lang('NumberOfGb'),
                                'values'=> array('0-1' => 142.78, '1-5' => 868.78, '5-20' => 1352.78)
                            )
        );
        
        $suite['game'] = array(
            'id' => 6,
            'name' => $this->get_lang('Game'),
            'image_path' => api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/img/suite/game.jpg',
            'short_description' => get_lang('SuiteGameShortDescription'),
            'large_description' => get_lang('SuiteGameLargeDescription'),
            'more_info_link' => '#',
            'default_price' => 508.20,
            'default_attribute' => '1-10',
            'attributes' => array(
                                'name' => $this->get_lang('SimultaneousUsers'),
                                'values' => array('1-10'  => 508.20, '11-25' => 2323.20, '26-50' => 2928.20)
                            )                         
        );
        
        $suite['evaluation'] = array(
            'id' => 7,
            'name' => $this->get_lang('Evaluation'),
            'image_path' => api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/img/suite/evaluation.jpg',
            'short_description' => get_lang('SuiteEvaluationShortDescription'),
            'large_description' => get_lang('SuiteEvaluationLargeDescription'),
            'more_info_link' => '#',
            'default_price' => 203.28,
        );
        
        return $suite;        
    }
    
    public function loadHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/css/suiteManager.css', 'css');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/javascript/functions.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/dokeos.js.php');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/js/suiteManagerModel.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/suiteManager/assets/js/suiteManagerController.js');
        
    }
    
}
