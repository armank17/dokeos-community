<?php

class application_mobile_controllers_panel extends appcore_command_Command
{
    public $css;
    public $_objSecurity;
    
    public function __construct() {
        $this->verifySession();
        
        $this->setTheme('mobile_nav');
        define('CURL','main/application/mobile/assets/');
        $this->css = 'application/mobile/assets/css/styles.css';
    }
    
    public function verifySession()
    {
        $this->_objSecurity = new application_security_controllers_ValidateSession();
        $this->_objSecurity->verifySessionMobile();
    }
    
}

