<?php

class application_node_controllers_RegistrationPage extends appcore_command_Command {
    
    public $editorConfig;
    public $registrationPageModel;
    public $nodeModel;
    public $nodeId;
    public $pageInfo;
    public $Pages;
    public $accessUrlId;
    public $languageList;
    
   public function __construct() {
       
       $this->setTheme('');
       $this->loadHtmlHeadXtra();

        $this->editorConfig = array(
                                'ToolbarSet' => 'Node', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/'
                              );
         
        $this->registrationPageModel = new application_node_models_RegistrationPageModel();
        $this->nodeModel = new application_node_models_NodeModel();
       
        $this->nodeId = $this->getRequest()->getProperty('nodeId', '');
        if (!empty($this->nodeId)) {
            $this->pageInfo = $this->registrationPageModel->getPageInfo($this->nodeId);
        }        
        $this->pages = $this->registrationPageModel->getPages();
        $this->pageInfo['language']= $this->getRequest()->getProperty('language', '');/* viene del configure_homepage.php */       
        $this->accessUrlId = api_get_current_access_url_id();            
    if ($this->accessUrlId < 0) {
        $this->accessUrlId = 1;
    }
    $this->setLanguageInterface();
   }    

   public function getForm(){
       
   }
   
    public function update() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);       
            $this->nodeModel->setNodeId($nodeId);
            $this->nodeModel->fieldValues['title']       = '';
            $this->nodeModel->fieldValues['content']     = $node_editor; 
            $this->nodeModel->save();
        }
       $this->redirect(api_get_path(WEB_CODE_PATH).'admin/configure_inscription.php');
    }
    
    public function create() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);  
            $language_id = (api_get_language_id($language) == NULL)? 0 : api_get_language_id($language);
            
            /* to save in node table*/
            $this->nodeModel->fieldValues['title'] = '';
            $this->nodeModel->fieldValues['content'] = $node_editor;
            $this->nodeModel->fieldValues['node_type'] = NODE_TYPE_REGISTRATION_PAGE;
            $this->nodeModel->fieldValues['language_id'] = $language_id;
            $this->nodeModel->fieldValues['access_url_id'] = $this->accessUrlId; 
            $this->nodeModel->save();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'admin/configure_inscription.php');
    }
    public function edit(){
            
    }
   public function loadHtmlHeadXtra() {
       $this->setHtmlHeadXtra(api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css');
       $this->setHtmlHeadXtra(api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css');
    }
    
      public function getAction() {
        $html = '';
            if (api_is_allowed_to_edit()) { 
               
                 $html .= '<div id="header_actions" class="actions">';
                  
                        $html .= '<a href="'.api_get_path(WEB_CODE_PATH).'admin/configure_inscription.php">' . Display::return_icon('pixel.gif', $this->get_lang('HomePage'), array('class' => 'toolactionplaceholdericon toolactionback')) . $this->get_lang("Back") . '</a>';
                       

                $html .= '</div>';
                    }
        return $html;
      
        }

}

