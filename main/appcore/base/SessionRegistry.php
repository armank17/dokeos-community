<?php
require_once dirname(__FILE__).'/Registry.php';

class appcore_base_SessionRegistry extends appcore_base_Registry
{
    private $value = array();
    
    private static $instance = NULL;
    
    public static function instance()
    {
        $returnValue = NULL;
        if(!self::$instance) {
                self::$instance = new self();
        }
        $returnValue = self::$instance;        
        return $returnValue;
    }
    
    protected function get($key)
    {
        return $this->value[$key];
    }
    
    protected function set($key, $value)
    {
        $this->value[$key] = $value;
    }
    
    public static function getSession()
    {
        $returnValue = NULL;
        $returnValue = self::instance()->get('dokeos');
        return $returnValue;
    }
    
    public static function setSession(appcore_controller_Session $objSession)
    {
        self::instance()->set('dokeos', $objSession);
    }
}