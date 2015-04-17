<?php

class application_mobile_controllers_index extends appcore_command_Command
{
    public $css;
    public $_objSecurity;
    public $_objCollect;
    
    public function __construct() {
        $this->verifySession();
        $this->_objCollect = new application_mobile_models_collection();
        define('CURL','main/application/mobile/assets/');
        $this->css = 'application/mobile/assets/css/styles.css';
    }
    
    public function index(){
        $this->setTheme('mobile');
    }
    
    public function verifySession()
    {
        $this->_objSecurity = new application_security_controllers_ValidateSession();
        $this->_objSecurity->verifySessionMobile();
    }
    
    public function json()
    {
        $this->disabledFooterCore();
        $this->disabledHeaderCore();
        $case = $this->getRequest()->getProperty('case');
        switch ($case):
            case 'login':
                if($this->getSession()->getProperty('_user')){
                    $dtaUser = $this->getSession()->getProperty('_user');
                    $user = $dtaUser['firstname'];
                }
                else
                    $user = '';
                
                echo json_encode(array('name' => $user));
                break;
            case 'course':
                $data = $this->_objCollect->getCourseUser(api_get_user_id());
                $dtaJson = $this->getArrayCourse($data);
                echo json_encode($dtaJson);
                break;
            case 'session':
                $admin = $this->getSession()->getProperty('is_platformAdmin');
                $data = $this->_objCollect->getSessionUser(api_get_user_id(),$admin);
                $dtaJson = $this->getArraySession($data);
                echo json_encode($dtaJson);
                break;
            case 'sessionCourse':
                $session = $this->getRequest()->getProperty('session');
                $data = $this->_objCollect->getCourseSession($session);
                $dtaJson = $this->getArraySessionCourse($data);
                echo json_encode($dtaJson);
                break;
        endswitch;
    }
    
    public function course(){
        $this->setTheme('mobile_nav', 'header_course');
    }
    
    
    public function getArrayCourse($data,$session = ''){
        $array = array();
        foreach ($data->getIterator() as $value):
            $dtauser = api_get_user_info(api_get_user_id());
            
            if($dtauser['status'] != STUDENT)
                 $cont = $value->visibility;
             else
                 $cont = 0;
             
            $array[] = array(
                'course_code' => $value->course_code,
                'title' => $value->title,
                'description' => $description,
                'visibility' => $cont,
                'session' => $session
                );
        endforeach;
        
        return $array;
    }
    
    public function getArraySessionCourse($data){
        $array = array();
        foreach ($data->getIterator() as $value):
            $dtauser = api_get_user_info(api_get_user_id());
            
            if($dtauser['status'] != STUDENT)
                 $cont = $value->visibility;
             else
                 $cont = 0;
             
            $array[] = array(
                'course_code' => $value->course_code,
                'title' => $value->title,
                'description' => $description,
                'visibility' => $cont,
                'id_session' => $value->id_session
                );
        endforeach;
        
        return $array;
    }
    
    public function getArraySession($data){
        $array = array();
        foreach ($data->getIterator() as $value):
            $cont = $this->_objCollect->getCountCourseSession($value->id_session);
            
            $array[] = array(
                'id_session' => $value->id_session,
                'name' => $value->name,
                'description' => $value->description,
                'course_count' => $cont
                );
        endforeach;
        
        return $array;
    }
    
   
}

