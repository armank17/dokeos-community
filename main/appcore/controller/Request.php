<?php
require_once dirname(__FILE__).'/../base/RequestRegistry.php';
class appcore_controller_Request
{
    private $properties = array();
    
    private $feedback = array();
    
    public function __construct() {
        $this->init();
        appcore_base_RequestRegistry::setRequest($this);
    }
    
    public function init()
    {
        $this->properties = $_REQUEST;
        return;
    }
    
    public function getProperty($key, $value = false)
    {
        if(key_exists($key, $this->properties))
            return $this->properties[$key];
        else
            return $value;        
    }
    
    public function setProperty($key, $value)
    {
        if(key_exists($key,$this->properties))
        $this->properties[$key] = $value;
        else return false;
    }
    
    public function deleteProperty($key)
    {
        if(key_exists($key, $this->properties))
            unset($this->properties[$key]);
    }    
    
    public function addFeedback($message)
    {
        array_push($this->feedback, $message);
    }
    
    public function getFeedbackString($separator = '\n')
    {
        $returnValue = (string)'';
        $returnValue = implode($separator, $this->feedback);
        return $returnValue;
    }
}