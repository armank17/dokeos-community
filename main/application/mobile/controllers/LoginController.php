<?php

class application_mobile_controllers_login extends appcore_command_Command
{
    public $_objUser;
    
    public function __construct() {
        $this->setTheme('mobile','header_login');
        define('CURL','main/application/mobile/assets/');
        $this->_objUser = new application_mobile_models_collection();
    }
    
    public function login(){

    }
    
    public function logearse(){
        $this->disabledFooterCore();
        $this->disabledHeaderCore();
        $data = $this->_objUser->getUser($_POST);
        if($data->count() > 0):
            //Data All
            $this->getSession()->setProperty('tablename_direction', 'ASC');
            $this->getSession()->setProperty('tablename_page_nr', 1);
            $this->getSession()->setProperty('tablename_per_page', 25);
            
            //get Database
            foreach ($data->getIterator() as $value):
                //$rol = api_get_user_info($value->user_id);
        
                if($value->status == COURSEMANAGER ):
                    $this->getSession()->setProperty('is_allowedCreateCourse', 1);
                else:
                    $this->getSession()->setProperty('is_allowedCreateCourse', 0);
                endif;
                
                foreach ($value as $item => $result):
                    $user[$item] = $result;
                endforeach;
                break;
            endforeach;
            
            $this->getSession()->setProperty('chat_username', $user['username']);
             //api_is_platform_admin();
            
            if(!empty($user['platformAdmin'])):
                $this->getSession()->setProperty('is_platformAdmin', 1);//studentview
                unset($user['platformAdmin']);
            else:
                $this->getSession()->setProperty('is_platformAdmin', '');//studentview
            endif;
                
            $this->getSession()->setProperty('teacherview', 'teacherview');//studentview //falta
            $this->getSession()->setProperty('viewasstudent', 'NO');//falta
            $this->getSession()->setProperty('is_courseMember', '');
            $this->getSession()->setProperty('is_courseAdmin', '');
            $this->getSession()->setProperty('is_courseTutor', '');
            $this->getSession()->setProperty('is_allowed_in_course', '');
            $this->getSession()->setProperty('is_courseCoach', '');
            $this->getSession()->setProperty('is_sessionAdmin', '');
            
            $this->getSession()->setProperty('_user', $user);
            
            echo 1;
//            header( api_get_path(WEB_PATH) . 'main/index.php?module=mobile&cmd=index' ) ;
        else:
            //error
            echo 'error';
        endif;
        
    }
}
