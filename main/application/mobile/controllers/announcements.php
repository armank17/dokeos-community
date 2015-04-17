<?php

class application_mobile_controllers_announcements extends appcore_command_Command
{
    public $_objSecurity;
    public $_objAnnoun;
    public $_data;
    public $_user;
    
    public function __construct() {
        $this->_objAnnoun = new application_mobile_models_announcements();
        $this->_objSecurity = new application_security_controllers_ValidateSession();
        $this->_objSecurity->verifySessionMobile();
    }
    
    public function index(){
         $this->setTheme('mobile_nav','header_announ');
    }
    
    public function details(){
        $this->setTheme('mobile_nav','header_detail');
    }
    
    public function json(){
        $this->disabledFooterCore();
        $this->disabledHeaderCore();
        $case = $this->getRequest()->getProperty('case');
        
        switch ($case):
            case 'announ':
                $course = $this->getRequest()->getProperty('course');
                $objAnnoun = new application_announcement_models_collection();
                $data = $objAnnoun->getAnnouncementByUser(api_get_user_id(), $course);
                $dtaJson = $this->getArrayAnnoun($data,$course);
                break;
            case 'detail':
                $id = $this->getRequest()->getProperty('id');
                $course = $this->getRequest()->getProperty('course');
                $objAnnoun = new application_announcement_models_collection();
                $dtaJson = $objAnnoun->getAnnouncementById($id,$course);
                $dtaJson->content = strip_tags($dtaJson->content);
                break;
        endswitch;
        
        echo json_encode($dtaJson);
    }
    
    public function getArrayAnnoun($data,$course){
        $array = array();
        foreach ($data->getIterator() as $value):
            $content = strip_tags($value->content);
            
            $array[] = array(
                'id' => $value->id,
                'title' => $value->title,
                'content' => $content,
                'announcementdate' => announcementdate,
                'end_date' => $this->convertDate($value->end_date),
                'course' => $course
                );
        endforeach;
        
        return $array;
    }
    
    public function convertDate($fecha){
        $fetch = explode('-', $fecha);
        $new = $fetch[2] . '/' . $fetch[1] . '/' . $fetch[0];
        return $new;
    }
 
}