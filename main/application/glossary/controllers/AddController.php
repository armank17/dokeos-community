<?php

/**
 * Controller Glossary_Add
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_glossary_controllers_add extends appcore_command_Command {

    public $_objSecurity;
    public $_objIndex;
    public $_objGlossary;
    public $_objCollect;
    public $_course;
    //views
    public $form;
    
    function __construct() {
        $this->_objIndex = new application_glossary_controllers_Index();
        $this->_objCollect = new application_glossary_models_collection();
        $this->setTheme('tools');
        $this->setLanguageFile(array('glossary'));
    }
    
    public function index(){
        //$idGlossary = $this->getRequest()->getProperty('cidReq');
        $this->_course = $this->getRequest()->getProperty('cidReq');
        $this->form = $this->getForm();
    }
    
    public function getAction(){
       return $this->_objIndex->getHeaderOptions();
    }
    
    /*
     * Send POST to add
     */
    public function createnew(){
        $this->disabledFooterCore();
        $this->disabledHeaderCore();
        $course = $this->getRequest()->getProperty('id');
        if(!empty($_POST)):
            $check = Security::check_token();
            if ($check){
                $this->_objGlossary = new stdClass();
                $this->_objGlossary->name = $this->getRequest()->getProperty('name');
                $this->_objGlossary->description = $this->getRequest()->getProperty('description');
                
                $this->_objGlossary->_course = $course;
                $action = $this->_objCollect->insertGlossary($this->_objGlossary);
                
                if($action == 1):
                    $this->getRequest()->deleteProperty('name');
                    $this->getRequest()->deleteProperty('description');
                    //$this->getRequest()->deleteProperty('send_to');
                    Security::clear_token();
                endif;
                
                echo json_encode(array('action' => $action,'course' => $course));
            }else
                echo json_encode(array('action' => 2,'course' => $course, 'message' => GLOSSARY_MESSSAGE_ADD));
        endif;
        
    }
    
    
    /**
     * Get glossary formulary
     * @return  object  Form object
     */
    public function getForm($item = '', $course = '') {
        if(empty($this->_course))
            $this->_course = $course;
        
        global $charset;
        $editor_config = array('ToolbarSet' => 'Glossary', 'Width' => '100%', 'Height' => '250');        // initiate the object
        //      $this->_objForm = new FormValidator('glossary', 'post', api_get_self().'?'.api_get_cidreq().($idGlossary?'&action=edit&id='.intval($idGlossary):'&action=add'));
        $form = new FormValidator('glossary-form', 'post', 'index.php?module=glossary&cmd=Add&'.((is_object($item))?'&func=edit&id='.intval($item->glossary_id):'&func=createnew&') . api_get_cidreq());
        if (is_object($item)) 
            $form->addElement('hidden', 'glossary_id', $item->glossary_id);
        
        $form->addElement('hidden', 'session_id', $item->session_id);
        
        $form->addElement('text', 'name', $this->get_lang('TermName'), array('size'=>'30','class'=>'focus'));

        $form->addElement('html_editor', 'description', $this->get_lang('Definition'), array('id' => 'description','style' => 'vertical-align:middle'), $editor_config);
        //$form->add_html_editor('description', '', false, false, api_is_allowed_to_edit() ? array('ToolbarSet' => 'Announcements', 'Width' => '650px', 'Height' => '300', 'ID'=>'id_description') : array('ToolbarSet' => 'AnnouncementsStudent', 'Width' => '650px', 'Height' => '300', 'UserStatus' => 'student'));
        if (is_object($item)) 
            $form->addElement('html','<div align="left" style="padding-left:10px;"><a href="javascript:void(0)" onclick="javascript:deleteItemGlossary(' . @$item->glossary_id . ',' . "'" . $this->_course . "'" . ')">'.Display::return_icon('pixel.gif', $this->get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'&nbsp;&nbsp;'.$this->get_lang('Delete').'</a></div>');
        
        $form->addElement('style_submit_button', 'SubmitNote', $this->get_lang('Validate'), 'class="save"');

	// setting the defaults
        if (is_object($item)){
            //$glossaryInfo = $this->getGlossaryInfo($idGlossary);
            $defaults['name'] = $item->name;
            $defaults['description'] = $item->description;
            $form->setDefaults($defaults);
        }else{
            $token = Security::get_token();
            $form->addElement('hidden','sec_token');
            $form->setConstants(array('sec_token' => $token));
        }
	
	return $form;
    }
}