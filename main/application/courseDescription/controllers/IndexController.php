<?php

/**
 * controller default in module course description
 * @author Johnny, <johnny1402@gmail.com>
 * @package courseDescription 
 */
class application_courseDescription_controllers_Index extends appcore_command_Command
{
    private $objCollection;
    
    private $default_description_titles;
    
    private $default_description_class;
    
    private $description_type;
    
    private $description_id;
    
    public $form_html;
    
    public $css;

    public function __construct() {
        $this->verifySession();
        $this->css ='application/courseDescription/assets/css/style.css';
        $this->setTheme('tools');
        $this->setLanguageFile(array ('course_description', 'pedaSuggest', 'accessibility'));
        $this->initValueDefault();
        $this->objCollection = new application_courseDescription_models_courseDescription();
        
    }
    
    public function verifySession()
    {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
    }
    
    /**
     *  Get announcement list
     *  @param      int     Optional, User id 
     *  @return`    array   Announcements
     */
    public function getCourseDescriptionList() {
        
        $result = $this->objCollection->getCourseDescriptionList();
        return $result;
    }
    
    public function showDescription()
    {
        $this->description_type = $this->getRequest()->getProperty('description_type');
        $this->form_html = $this->getForm();
    }
    
    public function getCourseDescriptionInfo($description_type)
    {
        $result = $this->objCollection->getCourseDescriptionInfo($description_type);
        return $result;        
    }
    
    public function deleteDescription()
    {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        
        $objDescription = $this->objCollection->deleteDescription($this->getRequest()->getProperty('id_description'));
        if($objDescription->_result)
            echo json_encode(array('action' => 1, 'message' => $objDescription->_message));
        else
            echo json_encode(array('action' => 0, 'message' => $objDescription->_message));
        die();
        
    }
    
    public function belongsSession($id_description)
    {
        return $this->objCollection->belongsSession($id_description);
    }
        
    public function getForm() {
        $returnValue =  $this->getHtmlForm();
        return $returnValue;
    }
    
    public function getHtmlForm()
    {
        $objDescription = $this->getCourseDescriptionInfo($this->description_type);
        if($objDescription == NULL)
        {
            $this->description_id = 0;
            // Set some default values
            $default['title'] = $this->default_description_titles[$this->description_type];
            $default['description_id'] = $this->description_id;
            $default['description_type'] = $this->description_type;            
        }
        else
        {
            if(api_get_session_id() != $objDescription->session_id)
                $this->description_id = 0;
            else
                $this->description_id = $objDescription->id;
            // Set some default values
            $default['title'] = $objDescription->title;
            $default['contentDescription'] = $objDescription->content;
            $default['description_id'] = $this->description_id;
            $default['description_type'] = $this->description_type;            
        }
        
        $form = new FormValidator('course_description','POST','index.php?module=courseDescription&cmd=Index&'.api_get_cidreq().'&func=showDescription');
        $renderer = & $form->defaultRenderer();
        $form->addElement('hidden', 'description_type');
         $form->addElement('hidden', 'description_id', '', array('id'=>'description_id'));
        
        $renderer->setElementTemplate('<div class="row"><div>'.$this->get_lang('Title').' {element}</div></div>', 'title');
        $form->add_textfield('title', $this->get_lang('Title'), true, array('size'=>'width: 350px;','class'=>'focus', 'id'=>'titleDescription'));
        $form->applyFilter('title','html_filter');
        
        $renderer->setElementTemplate('<div class="row"><div>{element}</div></div>', 'contentDescription');			
        $form->add_html_editor('contentDescription', $this->get_lang('Content'), false, false, array('ToolbarSet' => 'TrainingDescription', 'Width' => '100%', 'Height' => '275', 'FullPage' => true, 'ID'=>'contentDescription'));
        
        $form->addElement('style_submit_button', null, $this->get_lang('Save'), 'class="save" id="btnDescription"');
        /*$token = Security::get_token();
        $form->addElement('hidden', 'sec_token');
        $form->setConstants(array('sec_token' => $token));*/
        $form->setDefaults($default);
        return $form;
    }
    
    public function addDescription()
    {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        
        //$check = Security::check_token();
        //if ($check) 
           // {
            $objDescription = new stdClass();
            $objDescription->_id = $this->getRequest()->getProperty('description_id');
            $objDescription->title = $this->getRequest()->getProperty('title');
            $objDescription->content = $this->getRequest()->getProperty('contentDescription');
            $objDescription->session_id = api_get_session_id();
            $objDescription->description_type = $this->getRequest()->getProperty('description_type');
            $objDescription->_result = false;
            $objDescription->_message = '';
            $objDescription = $this->objCollection->addDescription($objDescription);
            $this->getRequest()->deleteProperty('title');
            $this->getRequest()->deleteProperty('description');
            //Security::clear_token();
            if($objDescription->_result)
                echo json_encode(array('action' => 1, 'message' => $objDescription->_message, 'id'=>$objDescription->_id));
            else
                echo json_encode(array('action' => 0, 'message' => $objDescription->_message));
       /* }
        else
        {
            echo json_encode(array('action' => -1, 'message' => COURSEDESCRIPTION_MESSAGE_MULTIPOST));
        }*/
    }
    
    
    public function getAction()
    {
        $html= '<div class="actions">';
        $html.=$this->display_action();
        $html.='</div>';
        return $html;        
    }
    
    public function initValueDefault()
    {
        $default_description_titles = array();
        $default_description_titles[1]= $this->get_lang('Objectives');
        $default_description_titles[2]= $this->get_lang('HumanAndTechnicalResources');
        $default_description_titles[3]= $this->get_lang('Assessment');
        $default_description_titles[4]= $this->get_lang('GeneralDescription');
        $default_description_titles[5]= $this->get_lang('Agenda');
        $this->default_description_titles = $default_description_titles;

        $default_description_class = array();
        $default_description_class[1]= 'skills';
        $default_description_class[2]= 'resources';
        $default_description_class[3]= 'assessment';
        $default_description_class[4]= 'prerequisites';
        $default_description_class[5]= 'other';        
        $this->default_description_class = $default_description_class;
    }
    
    public function display_action()
    {
        $show_form = 0;

        if (!$this->getRequest()->getProperty('description_id')) {		
                $show_form = 1;
        }
        if (!$this->getRequest()->getProperty('description_type')) {		
                $show_form = 1;
        }
        if ($this->getRequest()->getProperty('showlist') == 1) {
                $show_form = 0;
        }

        if (api_is_allowed_to_edit(null,true)) {
            $categories = array ();

            foreach ($this->default_description_titles as $id => $title) {						
                    $categories[$id] = $title;
            }
            $i=1;                   
            ksort($categories);
            $action_icons ='';
            foreach ($categories as $id => $title) {
                // We are displaying only 5 first items							
                $action_icons .= '<a href="?'.api_get_cidreq().'&module=courseDescription&func=showDescription&description_type='.$id.'">'.Display::return_icon('pixel.gif', $title, array('class' => 'toolactionplaceholdericon toolaction'.$this->default_description_class[$id])).' '.$title.'</a>';
                $i++;                            
            }
        }
        return $action_icons;           
    }
}