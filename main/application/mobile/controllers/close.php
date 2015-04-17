<?php

class application_mobile_controllers_close extends appcore_command_Command
{
    public $_objSecurity;
    
    public function __construct() {
        $this->setTheme('mobile');
        $this->_objSecurity = new application_security_controllers_ValidateSession();
        $this->_objSecurity->getSessionCloseMobile();
    }
    
    public function close()
    {
        
        
       
    }
}

