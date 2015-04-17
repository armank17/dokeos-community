<?php

class application_mobile_controllers_reporting extends appcore_command_Command
{
    public $_text;
    
    public function __construct() {
        $this->setTheme('mobile');
        define('CURL','application/mobile/assets/');
    }
    
    public function index(){
     
        $this->_text = 'reportes';
        return $this->_text;
    }
}

