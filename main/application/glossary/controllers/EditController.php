<?php

/**
 * Controller Glossary_Add
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_glossary_controllers_Edit extends appcore_command_Command {

    public $_objSecurity;
    public $_objIndex;
    public $_objAdd;
    public $_objGlossary;
    public $_objCollect;
    public $_course;
    //views
    public $form;
    
    function __construct() {
        $this->_objIndex = new application_glossary_controllers_Index();
        $this->_objAdd = new application_glossary_controllers_Add();
        $this->_objCollect = new application_glossary_models_collection();
        $this->setTheme('tools');
        $this->setLanguageFile(array('glossary'));
        //
       
    }
    
    public function index(){
        $this->_course = $this->getRequest()->getProperty('cidReq');
        $id = $this->getRequest()->getProperty('id');
        $this->item = $this->_objCollect->getItemGlossary($id);
        $this->form = $this->_objAdd->getForm($this->item,$this->_course);
    }
    
    public function getAction(){
       return $this->_objIndex->getHeaderOptions();
    }
    
    public function edititem(){
        $this->disabledFooterCore();
        $this->disabledHeaderCore();
        if(!empty($_POST)):
            $course = $this->getRequest()->getProperty('id');
            $action = $this->_objCollect->editGlossary($_POST, $course);
            switch ($action):
                case 1:
                     echo json_encode(array('action' => $action,'message' => GLOSSARY_MESSAGE_UPDATE));
                    break;
                
                case 2:
                    echo json_encode(array('action' => 2, 'message' => 'error'));
                    break;
                case 3:
                    $sesion = api_get_session_info(api_get_session_id());
                    echo json_encode(array('action' => 3, 'message' => GLOSSARY_MESSSAGE_ADD_SESSION . $sesion['name']));
                    break;
                    
            endswitch;
            
        endif;
        
    }
}