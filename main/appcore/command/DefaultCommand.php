<?php
require_once dirname(__FILE__).'/Command.php';
class appcore_command_DefaultCommand extends appcore_command_Command
{
    public function doExecute(appcore_controller_Request $objRequest)
    {
        $objRequest->addFeedback("Bienvenido: Dokeos usando MVC");
    }
}