<?php
/* For licensing terms, see /license.txt */

/**
 * Main controller used for MVC model
 */
class Controller 
{
    
    protected $attributes = Array();
    
    /**
     * Magic method 
     */
    public function __get($key){
      return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }

    /**
     * Magic method 
     */
    public function __set($key, $value) { 
      $this->attributes[$key] = $value;
    } 
    
    /**
     * Construct
     */
    public function __construct() {}
    
    /**
     * Load an object for the controller
     * @param   string|array      the resources name to load
     * @return  void
     */
    public function load($name, $type) {
        switch ($type) {
            case 'helper':
                 if (is_array($name)) {
                    foreach ($name as $helper) {
                        $this->loadHelper($helper);
                    }
                 } else {
                     $this->loadHelper($name);
                 }
                 break;
        }
    }
    
    /**
     * Load a helper object
     * @param   string|array      a Helper object name or names list
     * @return  void
     */
    public function loadHelper($name) {
       $objName = $name.'Helper'; 
       $helper  = $name.'_helper';                 
       $helperSyspath = api_get_path(SYS_HELPER_PATH).$helper.'.php'; 
       if (file_exists($helperSyspath)) {
           require_once $helperSyspath;                   
           $this->$helper = new $objName();
       }
    }
    
}
?>
