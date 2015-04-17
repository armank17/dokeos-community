<?php

/**
 * model collection
 * @author johnny, <johnny1402@gmail.com>
 * @package index 
 */
class application_index_models_collection extends ArrayObject
{
    public $database;
    
    public function __construct() {
        $this->database = new application_index_models_database();
    }
    
    public function getUser()
    {
        $sql ='SELECT * FROM'.$this->database->get_main_table(TABLE_MAIN_USER);
        $arrayUser = $this->database->getData($sql);
        $this->exchangeArray($arrayUser);
        return $this;
    }
    
}