<?php
class application_demo_controllers_Index extends appcore_command_Command
{
    
    public $userModel;
    public $myvariable;
    
    public $userList;
    
    public function __construct() {
        
        $this->userModel = new application_demo_models_UserModel();
        
    }
    
    public function show() {
        
        // Here my logic for the action        
        $this->userList = $this->userModel->getAll();
        
    }

	
    
    public function toogle() {
        $this->loadHtmlHeadXtra();
    }
    
    public function loadHtmlHeadXtra() {
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/team.js');        
    }
    
    
}
