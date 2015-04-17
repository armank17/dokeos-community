<?php
require_once dirname(__FILE__).'/../base/SessionRegistry.php';
class appcore_controller_Session
{
    private $properties = array();
    
    private $feedback = array();
    
    public function __construct() {
        $this->init();
        appcore_base_SessionRegistry::setSession($this);
    }
    
    public function init()
    {
        if(strlen(trim(session_id()))>0)
            $this->properties = &$_SESSION;
        else
            session_start();
        return;
    }
    
    public function getProperty($key)
    {
        if(key_exists($key,$this->properties))
        return $this->properties[$key];
        else return false;        
    }
    
    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
    }
    
    public function deleteProperty($key)
    {
        if(key_exists($key, $this->properties))
            unset($this->properties[$key]);
    }
    
    
    public function getFeedback()
    {
        $returnValue = array();
        $returnValue = $this->feedback;
        return (array) $returnValue;        
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
