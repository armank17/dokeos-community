<?php
require_once dirname(__FILE__).'/Command.php';
class appcore_command_CommandResolver
{
    private static $base_cmd = NULL;
    
    private static $default_cmd = NULL;
    
    public function __construct() {
        if(!self::$base_cmd)
        {
            self::$base_cmd = new ReflectionClass('appcore_command_Command');
        }
        
        if(!self::$default_cmd)
        {
            self::$default_cmd = new appcore_command_DefaultCommand();
        }
        
        $filePath =  'application/index/controllers/IndexController.php';
        $className = 'application_index_controllers_index';
        
        if(file_exists($filePath))
        {
            require_once $filePath;
            if(class_exists($className))
            {
                $cmd_class = new ReflectionClass($className);
                if($cmd_class->isSubclassOf(self::$base_cmd))
                {
                    self::$default_cmd = $cmd_class->newInstance();
                }
            }
        }
        
        
    }
    
    public function getCommand(appcore_controller_Request $objRequest)
    {
        $returnValue = NULL;
        $cmd = $objRequest->getProperty('cmd');
        $func = $objRequest->getProperty('func');
        $module = $objRequest->getProperty('module');
        $submodule = $objRequest->getProperty('submodule');
        
        if(!$cmd)$cmd = 'IndexController';
        if(!$module)$module='index';
        $cmd = str_replace(array('.','/','\\'),'',$cmd);
        $module = str_replace(array('.','/','\\'),'',$module);
        $submodule = str_replace(array('.','/','\\'),'',$submodule);
        
        if(! $submodule) {
                $submodule_path = '';
                $submodule_class = '';
        }else{
                $submodule_path = $submodule.'/';
                $submodule_class = $submodule.'_';
        }        
        
        
        
        //$filepath_tmp = "application/{$module}/controllers/";
        $filepath_tmp = "application/{$module}/".$submodule_path.'controllers/';
        $prefijoClassName = str_replace('/','_',$filepath_tmp);
        
        //$filePath = "application/{$module}/controllers/{$prefijoClassName}{$cmd}.php";
        //$filePath = "application/{$module}/".$submodule_path."controllers/{$prefijoClassName}{$cmd}.php";
        if($cmd != 'IndexController')
            $cmd = $cmd.'Controller';
        $filePath = "application/{$module}/".$submodule_path."controllers/{$cmd}.php";

        //$className = "application_{$module}_controllers_{$cmd}";
        $name_controller = explode("Controller", $cmd);
        $cmd = $name_controller[0];
        $className = "application_{$module}_".$submodule_class."controllers_{$cmd}";
        if(file_exists($filePath))
        {
            require_once($filePath);
            
            if(class_exists($className))
            {
                $cmd_class = new ReflectionClass($className);
                if($cmd_class->isSubclassOf(self::$base_cmd))
                {
                    $returnValue = $cmd_class->newInstance();
                }
                else
                {
                    $objRequest->addFeedback("El command $cmd no es un Command");
                    echo "El command $cmd no es un Command";
                }
            }
            else
            {
		$objRequest->addFeedback("La clase '$className' del comando $cmd no se encontro");
                echo ("La clase '$className' del comando $cmd no se encontro. Incluido desde FilePath: $filepath");
                $returnValue = clone self::$default_cmd;                
            }
        }
        else
        {
            if (!api_is_xml_http_request()) {
                $objRequest->addFeedback("El comando $cmd no se encontro");
                echo ("El comando $cmd no se encontro");                
            }
            $returnValue = clone self::$default_cmd;
        }
        
        
        return $returnValue;
    }
}