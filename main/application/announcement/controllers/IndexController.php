<?php

/**
 * controller for create new createAnnouncement
 * @author Johnny, <johnny1402@gmail.com>
 * @package announcement 
 */
class application_announcement_controllers_Index extends appcore_command_Command {

    private $userId;
    private $courseCode;
    private $session_id;
    private $objCollection;
    
    public function __construct() {
        $this->validateSession();
        $this->objCollection = new application_announcement_models_collection();
        $this->redirect();
        $this->userId = api_get_user_id();
        $this->courseCode = api_get_course_id();
        $this->session_id = api_get_session_id();
    }
    
    public function validateSession()
    {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
    }
    
    public function redirect()
    {
        $arrayUser = api_get_user_info();
        switch ($arrayUser['status'])
        {
            case 5 :header("Location: ?module=announcement&cmd=ShowAnnouncement&".api_get_cidreq()."");break;
            default:header("Location: ?module=announcement&cmd=CreateAnnouncement&".api_get_cidreq()."");break;
        }
    }
    
    public function getAction() {
        $html = '<div class="actions">';
        if (api_is_allowed_to_edit()) {
            $html.='<a href="index.php?module=announcement&' . api_get_cidreq() . '&cmd=CreateAnnouncement">' . Display::return_icon('pixel.gif', $this->get_lang('AddAnnouncement'), array('class' => 'toolactionplaceholdericon toolactionannoucement')) . $this->get_lang('AddAnnouncement') . '</a>';
        }
        $html.='</div>';
        return $html;
    }    
    
    
}
