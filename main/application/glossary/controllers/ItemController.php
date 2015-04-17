<?php

/**
 * Controller Glossary_Item
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_glossary_controllers_Item extends appcore_command_Command {

    public $_objSecurity;
    public $_objCollect;
    public $_paginator;
    public $_objIndex;
     //views
    public $pagerLinks;
    public $html;
    public $item;
    public $list;
    public $course;
    
    function __construct() {
        $this->setTheme('tools');
        $this->setLanguageFile(array('glossary'));
        $this->_objIndex = new application_glossary_controllers_Index();
        $this->_paginator = new appcore_library_pagination_Paginator();
        $this->_objCollect = new application_glossary_models_collection();
        
    }
    
    function getAction(){
       return $this->_objIndex->getHeaderOptions();
    }
    
    public function index(){
        $id = $this->getRequest()->getProperty('id');
        $this->course = $this->getRequest()->getProperty('cidReq');
        //Data Terms all
        $data = $this->_objCollect->getGlossaryList('');
        $this->_objCollect->generatePaginator($data,$this->_paginator);
        $this->pagerLinks = $this->_paginator->links();
        $this->list = $this->getHtmlList($data,$id);
        
        //Item Select
         $this->item = $this->_objCollect->getItemGlossary($id);
    }
    
    public function json(){
        $this->disabledFooterCore();
        $this->disabledHeaderCore();
        
        $case = $this->getRequest()->getProperty('case');
        
        switch ($case):
            case 'delete':
                $this->_objCollect->deleteGlossary($_POST);
                
                if($action == 1):
                    $result = array('action' => $action, 'message' => GLOSSARY_MESSAGE_DELETE);
                else:
                    $result = array('action' => $action, 'message' => GLOSSARY_MESSAGE_ERROR);
                endif;
                
                echo json_encode($result);
                break;
        endswitch;
    }
    
     /**
     * Get html glossary list
     */
    public function getHtmlList ($data,$id) {
        //generator html in index
        $obj = new application_glossary_controllers_index();
        $data = $obj->getHtmlList($data,$id);
        return $data;
    }
}
    