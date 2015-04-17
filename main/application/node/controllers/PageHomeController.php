<?php

class application_node_controllers_PageHome extends appcore_command_Command {
    
    public $editorConfig;
    public $homePageModel;
    public $nodeModel;
    public $nodeId;
    public $pageInfo;
    public $linkInfo;
    public $pages;
    public $url;
    public $accessUrlId;
    public $languageList;
    public $menuLinkModel;
    public $listLinkCategories;
    public $pageHomeTemplates = array();
    
   public function __construct() {
        $this->setTheme('');
        $this->loadHtmlHeadXtra();
        $this->editorConfig = array(
                                'ToolbarSet' => 'Node', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/'
                              );
        $this->homePageModel = new application_node_models_PageHomeModel();
        $this->nodeModel     = new application_node_models_NodeModel();
        $this->menuLinkModel = new application_node_models_MenuLinkModel();
        $this->setLanguageFile(array('admin','trad4all')); // se carga los archivos que tienen las traducciones
        $this->listLinkCategories = array(MENULINK_CATEGORY_HEADER, MENULINK_CATEGORY_FOOTER, MENULINK_CATEGORY_LEFTSIDE);
        $this->url                = api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=showPage&nodeId='.$nodeId;
        $this->nodeId             = $this->getRequest()->getProperty('nodeId', '');
        $this->setLanguageInterface();
        
        // access url id
        $this->accessUrlId = api_get_current_access_url_id();            
        if ($this->accessUrlId < 0) { $this->accessUrlId = 1; }
        
        
        // PageHome Template
        $this->pageHomeTemplates = $this->homePageModel->getPageHomeTemplates();
   }
   
   
   /**
    * List PageHome
    */
   public function Index() {
        // Set language if user choice one
        if (isset($_SESSION['user_language_choice'])){
             $language_interface = $_SESSION['user_language_choice'];
        }
        $this->setLanguageInterface($language_interface);


        $pages = $this->homePageModel->getPages();
        $current_language_id = api_get_language_id($this->languageInterface);

        $languages = api_get_languages();
        $languages = $languages['folder'];
        $languages_id[] = "0";               // all languages
        foreach($languages as $key => $value)
            $languages_id[] = api_get_language_id($value);     

        foreach($pages as $page){
            if((array_search($page['language_id'], $languages_id) !== false) && ($page['language_id'] == $current_language_id || $page['language_id'] == 0))
                $this->pages[] = $page;
        }
   }
   
   /**
    * Add/Edit PageHome
    */
   public function getForm(){
       // Set language if user choice one
       if (isset($_SESSION['user_language_choice'])){
            $language_interface = $_SESSION['user_language_choice'];
       }
       $this->setLanguageInterface($language_interface);
       
       if (!empty($this->nodeId)) {
            $this->pageInfo = $this->homePageModel->getPageInfo($this->nodeId);
            $this->linkInfo = $this->menuLinkModel->getLinkInfo($this->pageInfo['menu_link_id']);
        }
   }
   
   /**
    * Show Page
    */
   public function showPage(){
       $this->setTheme('tools');
       if (!empty($this->nodeId)) {
           $this->pageInfo = $this->homePageModel->getPageInfo($this->nodeId);                
           $language_id = intval($this->pageInfo['language_id']);
           if($language_id !== 0)
                $this->setLanguageInterface($language_id);
       }
   }
   
   /**
    * get PageHome Template
    */
   public function getTemplate(){
       $template_id = $this->getRequest()->getProperty('id');
       if(isset($template_id)){
           foreach($this->pageHomeTemplates as $template){
               if($template_id == $template['id']){
                   echo str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $template['content']);
               }
           }
       }
       exit;
   }
   
   /**
    * 
    */
   public function create() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);    
            $language_id   = (api_get_language_id($language_id) == NULL)? 0 : api_get_language_id($language_id);
            $enabled       = ($enabled==null)? 0 : $enabled;
            $linkEnabled   = ($linkEnabled==null)? 0 : $linkEnabled;
            $display_title = ($display_title==null)? 0 : $display_title;
            
            
            /* Save on menu link table first to get the Id and save it in node table */
            if($createlink==1){
                $this->menuLinkModel->fieldValues['title']         = $menu_link_title;
                $this->menuLinkModel->fieldValues['weight']        = $this->menuLinkModel->getMaxWeight($category, $this->accessUrlId)+1;
                $this->menuLinkModel->fieldValues['description']   = $menu_link_description;
                $this->menuLinkModel->fieldValues['category']      = $category;                
                $this->menuLinkModel->fieldValues['target']        = $target;
                $this->menuLinkModel->fieldValues['language_id']   = $language_id;
                $this->menuLinkModel->fieldValues['access_url_id'] = $this->accessUrlId;
                //$this->menuLinkModel->fieldValues['link_path']     = '/index.php?action=show&nodeId='.$nodeId; 
                $this->menuLinkModel->fieldValues['link_type']     = MENULINK_TYPE_NODE;                
                $this->menuLinkModel->fieldValues['visibility']    = intval(MENULINK_VISIBLE_ANONYMOUS | MENULINK_VISIBLE_COURSE_IN | MENULINK_VISIBLE_TOOL_IN | MENULINK_VISIBLE_LOGGED);
              
                $lastLinkId = $this->menuLinkModel->save();
                $this->nodeModel->fieldValues['menu_link_id'] = $lastLinkId;
            }
            
             /* Save on node table */           
            $this->nodeModel->fieldValues['title']          = $node_title;
            $this->nodeModel->fieldValues['content']        = $node_editor;
            $this->nodeModel->fieldValues['display_title']  = $display_title;
            $this->nodeModel->fieldValues['active']         = $active;
            $this->nodeModel->fieldValues['enabled']        = $enabled;
            $this->nodeModel->fieldValues['language_id']    = $language_id;
            $this->nodeModel->fieldValues['node_type']      = NODE_HOMEPAGE;
            $this->nodeModel->fieldValues['access_url_id']  = $this->accessUrlId;            
            $lastId = $this->nodeModel->save();
            
            // set menu link path
            if($lastLinkId > 0){
                $this->menuLinkModel->setMenuLinkId($lastLinkId);
                $this->menuLinkModel->fieldValues['link_path'] = '/index.php?action=show&nodeId='.$lastId;
                $this->menuLinkModel->save();            
            }
            
            /* Save on homepage table */
            $prom = ($promoted==null)? 0 : $promoted;
            $this->homePageModel->fieldValues['node_id']    = $lastId;
            $this->homePageModel->fieldValues['promoted']   = $prom;
            $this->homePageModel->save();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=Index');
    }
   
    public function update() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST); 
            
            $enabled        = ($enabled==null)? 0 : $enabled;
            $linkEnabled    = ($linkEnabled==null)? 0 : $linkEnabled;
            $display_title  = ($display_title==null)? 0 : $display_title;
            $language_id    = (api_get_language_id($language_id) == NULL)? 0 : api_get_language_id($language_id);            
            
            // Menu Link
            $menu_link_id = $this->nodeModel->getMenuLinkId($nodeId);
            if($createlink == 1){
                if($menu_link_id > 0){
                    $this->menuLinkModel->setMenuLinkId($menu_link_id);
                } else {
                    $this->menuLinkModel->fieldValues['weight']    = $this->menuLinkModel->getMaxWeight($category, $this->accessUrlId)+1;
                }
                $this->menuLinkModel->fieldValues['title']         = $menu_link_title;
                $this->menuLinkModel->fieldValues['description']   = $menu_link_description;
                $this->menuLinkModel->fieldValues['category']      = $category;
                $this->menuLinkModel->fieldValues['target']        = $target;
                $this->menuLinkModel->fieldValues['language_id']   = $language_id;
                $this->menuLinkModel->fieldValues['access_url_id'] = $this->accessUrlId;
                $this->menuLinkModel->fieldValues['link_path']     = '/index.php?action=show&nodeId='.$nodeId; 
                $this->menuLinkModel->fieldValues['link_type']     = MENULINK_TYPE_NODE;
                $this->menuLinkModel->fieldValues['visibility']    = intval(MENULINK_VISIBLE_ANONYMOUS | MENULINK_VISIBLE_COURSE_IN | MENULINK_VISIBLE_TOOL_IN | MENULINK_VISIBLE_LOGGED);
                $this->menuLinkModel->fieldValues['enabled']       = 1;                
                $menu_link_id = $this->menuLinkModel->save();                
                
                $this->nodeModel->fieldValues['menu_link_id'] = $menu_link_id;
            }
            else {
                if($menu_link_id > 0){
                    $this->menuLinkModel->setMenuLinkId($menu_link_id);
                    $this->menuLinkModel->fieldValues['enabled'] = 0;
                    $this->menuLinkModel->save();
                }
            }
            
            // Node
            $this->nodeModel->setNodeId($nodeId);            
            $this->nodeModel->fieldValues['title']         = $node_title;
            $this->nodeModel->fieldValues['content']       = $node_editor;            
            $this->nodeModel->fieldValues['language_id']   = $language_id;
            $this->nodeModel->fieldValues['enabled']       = $enabled;
            $this->nodeModel->fieldValues['display_title'] = $display_title;
            $this->nodeModel->save();
            
            // PageHome
            $this->homePageModel->setNodeId($nodeId);
            $this->homePageModel->fieldValues['promoted'] = $promoted;
            $this->homePageModel->save();            
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=Index');
    }
    
   
    public function delete() {
        if (!empty($this->nodeId)) {
            $menuLinkModelId = $this->nodeModel->getMenuLinkId($this->nodeId);
            if($menuLinkModelId > 0){
                $this->menuLinkModel->setMenuLinkId($menuLinkModelId);               
                $this->menuLinkModel->delete();
            }
            $this->homePageModel->setNodeId($this->nodeId);
            $this->homePageModel->fieldValues['deleted_by'] = $created_by;
            $this->homePageModel->delete();
        }
         $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=Index');
    }
    
    
    public function updateEnabled(){
         extract($_GET);
        
         if (isset($enabled)){
             $this->nodeModel->setNodeId($nodeId);
             $this->nodeModel->fieldValues['enabled']=$enabled;
             $this->nodeModel->save();
         }
         else {
             $this->homePageModel->setNodeId($nodeId);
             $this->homePageModel->fieldValues['promoted']=$promoted;
             $this->homePageModel->save();
         }
    }
    
    
    
    
   
   public function loadHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'inc/lib/javascript/chosen/chosen.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/pages.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/pageHome.css', 'css');
        
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'inc/lib/javascript/chosen/chosen.jquery.min.js', 'js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/pageHomeModel.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/functions.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/pageHomeController.js');
    }
    
    public function getAction() {
        $html = '';
        $function_name = $this->getRequest()->getProperty('func', '');
        
        if($function_name != 'showPage'){
            if (api_is_allowed_to_edit()) { 
                ob_start();
                api_display_language_form(true, '', true);
                $language_select = ob_get_contents();
                ob_end_clean();
                
                $add_param = '?language='.api_get_interface_language();
                $html .= '<div id="header_actions" class="actions">'
                            .'<div class="float_l">'
                                .'<a href="'.api_get_path(WEB_CODE_PATH).'admin/configure_homepage.php">' . Display::return_icon('pixel.gif', $this->get_lang('HomePage'), array('class' => 'toolactionplaceholdericon toolactionhomepage')) . $this->get_lang("CampusHomepage") . '</a>'
                                //   .'<a href="'.api_get_path(WEB_CODE_PATH).'admin/configure_homepage.php?action=edit_top&amp;edit_template=false">' . Display::return_icon('pixel.gif', $this->get_lang('EditHomePage'), array('class' => 'toolactionplaceholdericon tooledithome')) . $this->get_lang("EditHomePage") . '</a>';
                                .'<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=getForm">' . Display::return_icon('pixel.gif', $this->get_lang('CreatePageFromATemplate'), array('class' => 'toolactionplaceholdericon toolactiontemplates')) . $this->get_lang("CreatePageFromATemplate") . '</a>'
                                .'<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=Index">' . Display::return_icon('pixel.gif', $this->get_lang('ListPage'), array('class' => 'toolactionplaceholdericon toolactiondocumentpages')) . $this->get_lang("ListPage") . '</a>'
                                .'<a href="'.api_get_path(WEB_CODE_PATH).'admin/slides_management.php'.$add_param.'">' . Display::return_icon('pixel.gif', $this->get_lang('SlidesManagement'), array('class' => 'toolactionplaceholdericon toolallpages')) . $this->get_lang("SlidesManagement") . '</a>'
                                //.'<a href="'.api_get_path(WEB_CODE_PATH).'admin/configure_homepage.php?action=logo">' . Display::return_icon('pixel.gif', $this->get_lang('EditHomePage'), array('class' => 'toolactionplaceholdericon dokeos_toolaction')) . $this->get_lang("Logo") . '</a>'
                                //   .'<div  style="margin-right:10px;float:right">
                                //   .api_display_language_form(true, '', true).'</div>';
                            .'</div>'
                            .(($function_name == 'Index')? '<div class="float_r" style="margin-right:10px;">'. $language_select .'</div>' : '')
                            .'<div style="clear: both;"></div>'
                        .'</div>';
            }
        }
        return $html;      
   }
        
   public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        $objSecurity->verifyAccessToCourse();  
    }
    
}
?>
