<?php
/**
 * include appcore_command_CommandResolver
 * @author Johnny
 */
//require_once('appcore/command/CommandResolver.php');
/**
 * include appcore_controller_Request
 * @author Johnny
 */
require_once dirname(__FILE__).'/../controller/Request.php';

class appcore_controller_Controller
{
    public function __construct() 
    {
        
    }
    
    public static function run()
    {
        $instance = new appcore_controller_Controller();
        $instance->handleRequest();        
    }
    
    public function handleRequest()
    {
        $objRequest = new appcore_controller_Request();

        $cmd_r = new appcore_command_CommandResolver();

        $cmd = $cmd_r->getCommand($objRequest);

        $cmd->execute($objRequest);
           
    }
}