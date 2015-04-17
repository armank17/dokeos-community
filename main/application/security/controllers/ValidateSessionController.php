<?php
class application_security_controllers_ValidateSession extends appcore_command_Command
{
    
    public function verifySession()
    {                
        if(!$this->getSession()->getProperty('_user')) {
            header("Location: ".api_get_path(WEB_PATH));
        }
    }
    
    public function verifySessionMobile()
    {
        if(!$this->getSession()->getProperty('_user'))
            header("Location: ".api_get_path(WEB_PATH) . 'main/index.php?module=mobile&cmd=login');
    }
    
    public function verifyAccessToCourse($admin = false) 
    {
        if ($admin) {
            if (!api_is_allowed_to_edit()) {
                api_not_allowed();
            }
        }
        else {
            api_protect_course_script();
        }
    }
   
    public function create_Session()
    {
        if($this->getRequest()->getProperty('id_user'))
        {
            $id_user = $this->getRequest()->getProperty('id_user');
            $objUser = $this->getUser($id_user);
            $this->getSession()->setProperty('_user', $objUser);
        }
    }
    
    public function getUser($id_user)
    {
        $objModelSecurity = new application_security_models_security();
        $result = $objModelSecurity->getUser($id_user);
        return $result;
    }
    
    public function getSessionCloseMobile(){
        $this->verifySessionMobile();
        $this->getSession()->deleteProperty('_user');
        header("Location: ".api_get_path(WEB_PATH) . 'main/index.php?module=mobile&cmd=login');
    }
    
}