<?php

//require_once 'inc/global.inc.php';
class appcore_base_ApiDokeos
{
    public $api;
    
    public function api_get_message()
    {
        return 'Bienvenidos a Dokeos MVC';
    }
    
    public function __call($name, $arguments) {
        include 'inc/lib/main_api.lib.php';
        $this->api = call_user_func_array($name, $arguments);
        return $this->api;
    }
    
    public function getApi()
    {
        return $this->api;
    }
}