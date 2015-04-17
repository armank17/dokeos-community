<?php
require_once dirname(__FILE__).'/Registry.php';
class appcore_base_RequestRegistry extends appcore_base_Registry
{
    private $value;
    
    private static $instance;
    
    public function __construct() {
        
    }
    
    public static function instance()
    {
        $returnValue = NULL;
        if(!self::$instance)
            self::$instance = new self;
        $returnValue = self::$instance;
        return $returnValue;
    }
    
    protected function get($key) {
        return $this->value[$key];
    }
    
    protected function set($key, $value)
    {
        $this->value[$key] = $value;
    }
    
    public static function getRequest()
    {
        $returnValue = NULL;
        $returnValue = self::instance()->get('request');
        return $returnValue;
    }
    
    public static function setRequest(appcore_controller_Request $objRequest)
    {
        self::instance()->set('request', $objRequest);
    }
    
}