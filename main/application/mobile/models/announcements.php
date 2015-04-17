<?php
/**
 * model collection
 * @author johnny, <johnny1402@gmail.com>
 * @package index 
 */
class application_mobile_models_announcements extends ArrayObject
{
    public $database;
    
    public function __construct() {
        $this->database = new application_mobile_models_database();
    }
    
}