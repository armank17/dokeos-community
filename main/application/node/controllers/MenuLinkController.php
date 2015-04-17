<?php
/**
 * page controller for menu link feature
 * @package Node 
 */

class application_node_controllers_MenuLink extends appcore_command_Command 
{
    public $path;
    
    public $editorConfig;
    public $menuLinkModel;
    public $menuLinkId;
    public $menuLinkInfo;
    public $menuLinks;
    
    private $_current_access_url_id;
    
    
    public function __construct() {
        $this->validateSession();
        $this->setTheme('');
        $this->loadHtmlHeadXtra();

        /*$this->editorConfig = array(
                                'ToolbarSet' => 'Node', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/'
                              );*/
                
        $this->menuLinkModel = new application_node_models_MenuLinkModel();
        $this->path          = api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=MenuLink';
        $this->menuLinkId    = $this->getRequest()->getProperty('menuLinkId', '');
        $this->_current_access_url_id = (api_get_current_access_url_id() < 0)? 1 : api_get_current_access_url_id();
        $this->setLanguageInterface();
    }
    
    
    private function _redirectToListView(){
        $category = strtolower( $this->getRequest()->getProperty('category', '') );
        $this->redirect($this->path .'&func=listMenuLinks&category='. $category);
    }
    
    
    public function index() {
        
    }
    
    public function getForm() {
        if (!empty($this->menuLinkId))
            $this->menuLinkInfo = $this->menuLinkModel->getLinkInfo($this->menuLinkId);
    }
    
    public function listMenuLinks() {
        $category = strtolower( $this->getRequest()->getProperty('category', '') );
        switch ($category) {
            case MENULINK_CATEGORY_HEADER:
            case MENULINK_CATEGORY_FOOTER:
            case MENULINK_CATEGORY_LEFTSIDE:
                $this->menuLinks = $this->menuLinkModel->getMenuLinks($category);
       }
    }
    
    
    
    public function loadHtmlHeadXtra() {
        // jquery
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js');
        
        // css
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/menuLinks.css', 'css');        
        // js
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/functions.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/menuLinkModel.js');        
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/menuLinkController.js');
    }
    
    public function getAction() {
        $html = '';
        $func = $this->getRequest()->getProperty('func', '');
        $category = strtolower( $this->getRequest()->getProperty('category', '') );        
        
        if(trim($func) !== 'index'){
            if (api_is_allowed_to_edit()) {
                $listMenuLinks = '<a href="'. $this->path .'&func=listMenuLinks&category='. $category .'">'. Display::return_icon('pixel.gif', $this->get_lang('ListMenuLink'), array('class' => 'toolactionplaceholdericon toolactiondocumentpages')) . $this->get_lang("ListMenuLink") .'</a>';
                $addMenuLink   = '<a href="'. $this->path .'&func=getForm&category='. $category .'">'. Display::return_icon('pixel.gif', $this->get_lang('NewMenuLink'), array('class' => 'toolactionplaceholdericon toolactiondocumentcreate')) . $this->get_lang("NewMenuLink") .'</a>';
                
                $html = '<div id="header_actions" class="actions">'.
                           '<a href="'. $this->path .'&func=index">'. Display::return_icon('pixel.gif', $this->get_lang('Categories'), array('class' => 'toolactionplaceholdericon toolactiondocumentpages')) . $this->get_lang("Categories") .'</a>'.
                           ((trim($func) !== 'listMenuLinks')? $listMenuLinks : '').
                           ((trim($func) !== 'getForm')? $addMenuLink : '').
                        '</div>';
            }
        }else{
            Display::display_header_admin_of_portal(4);
        }
        return $html;
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        //$objSecurity->verifyAccessToCourse();
    }
            
        


    
    
    public function create() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);
            $language_id = (api_get_language_id($language_id) == NULL)? 0 : api_get_language_id($language_id);
            $visibility  = intval($menulink_visibility_anonymous, 2) * MENULINK_VISIBLE_ANONYMOUS + 
                           intval($menulink_visibility_logged, 2)    * MENULINK_VISIBLE_LOGGED + 
                           intval($menulink_visibility_course_in, 2) * MENULINK_VISIBLE_COURSE_IN + 
                           intval($menulink_visibility_tool_in, 2)   * MENULINK_VISIBLE_TOOL_IN;
            $menulink_enabled = ($menulink_enabled==null)?0:$menulink_enabled;            
            $this->menuLinkModel->fieldValues['parent_id']     = 0;
            $this->menuLinkModel->fieldValues['weight']        = $this->menuLinkModel->getMaxWeight($category, $this->_current_access_url_id)+1;
            $this->menuLinkModel->fieldValues['title']         = $menulink_title;
            $this->menuLinkModel->fieldValues['link_path']     = $menulink_path;
            $this->menuLinkModel->fieldValues['description']   = $menulink_description;
            $this->menuLinkModel->fieldValues['access_url_id'] = $this->_current_access_url_id;
            $this->menuLinkModel->fieldValues['category']      = $category;
            $this->menuLinkModel->fieldValues['language_id']   = $language_id;
            $this->menuLinkModel->fieldValues['target']        = $target;
            $this->menuLinkModel->fieldValues['enabled']       = intval($menulink_enabled);
            $this->menuLinkModel->fieldValues['visibility']    = $visibility;
            $this->menuLinkModel->save();
        }
        $this->_redirectToListView();
    }
    
    public function update() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);
            $this->menuLinkModel->setMenuLinkId($menuLinkId);
            $language_id    = (api_get_language_id($language_id) == NULL)? 0 : api_get_language_id($language_id);
            $menu_link_type = $this->menuLinkModel->getMenuLinkType();
            
            // exception: MENULINK_TYPE_PLATFORM
            if($menu_link_type == MENULINK_TYPE_PLATFORM){
                $menulink_title = $this->menuLinkModel->getMenuLinkTitle();
                if($menulink_title == 'CampusHomepage')  // <-- exception for Home menu link
                    $menulink_visibility_anonymous = 1;
            }
            
            $visibility  = (intval($menulink_visibility_anonymous) * MENULINK_VISIBLE_ANONYMOUS) +
                           (intval($menulink_visibility_logged)    * MENULINK_VISIBLE_LOGGED)    +
                           (intval($menulink_visibility_course_in) * MENULINK_VISIBLE_COURSE_IN) +
                           (intval($menulink_visibility_tool_in)   * MENULINK_VISIBLE_TOOL_IN);
            
            switch ($menu_link_type){
                case MENULINK_TYPE_PLATFORM:
                    //$this->menuLinkModel->fieldValues['parent_id']         = $parent_id;
                    //$this->menuLinkModel->fieldValues['weight']            = $weight;
                    $this->menuLinkModel->fieldValues['description'] = $menulink_description;
                    //$this->menuLinkModel->fieldValues['target']    = $target;
                    $this->menuLinkModel->fieldValues['enabled']     = $menulink_title == 'PlatformAdmin'? 1 : $menulink_enabled;
                    $this->menuLinkModel->fieldValues['visibility']  = $visibility;
                    break;
                case MENULINK_TYPE_NODE:
                    $this->menuLinkModel->fieldValues['description'] = $menulink_description;
                    $this->menuLinkModel->fieldValues['target']      = $target;
                    $this->menuLinkModel->fieldValues['enabled']     = $menulink_enabled;
                    $this->menuLinkModel->fieldValues['visibility']  = $visibility;
                    break;
                default:
                    //$this->menuLinkModel->fieldValues['parent_id']         = $parent_id;
                    //$this->menuLinkModel->fieldValues['weight']            = $weight;
                    $this->menuLinkModel->fieldValues['title']         = $menulink_title;
                    $this->menuLinkModel->fieldValues['link_path']     = $menulink_path;
                    $this->menuLinkModel->fieldValues['description']   = $menulink_description;
                    $this->menuLinkModel->fieldValues['access_url_id'] = $this->_current_access_url_id;
                    $this->menuLinkModel->fieldValues['category']      = $category;
                    $this->menuLinkModel->fieldValues['language_id']   = $language_id;
                    $this->menuLinkModel->fieldValues['target']        = $target;
                    $this->menuLinkModel->fieldValues['enabled']       = $menulink_enabled;
                    $this->menuLinkModel->fieldValues['visibility']    = $visibility;
            }
            $this->menuLinkModel->save();
        }
        $this->_redirectToListView();
    }
    
    public function delete() {
        if (!empty($this->menuLinkId)) {
            if($this->menuLinkModel->getMenuLinkType() !== MENULINK_TYPE_PLATFORM){
                $this->menuLinkModel->setMenuLinkId($this->menuLinkId);
                $this->menuLinkModel->delete();
            }
        }
        $this->_redirectToListView();
    }
    
    public function saveList(){
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST"){
            $menuLinks = array();
            
            foreach($_POST as $key => $value){
                $parse = preg_split('/[\s:]+/', $key);
                if($parse[0]=='enabled' or $parse[0]=='weight' or $parse[0]=='parentid'){
                    // $menuLinks[{id}][{column}] = {value}
                    $menuLinks[$parse[1]][$parse[0]] = $value;
                }
            }
            
            foreach($menuLinks as $key => $value){
                $this->menuLinkModel->setMenuLinkId($key);
                $this->menuLinkModel->fieldValues['parent_id']         = $value['parentid'];
                $this->menuLinkModel->fieldValues['weight']            = $value['weight'];
                $this->menuLinkModel->fieldValues['enabled']           = $value['enabled'];
                $this->menuLinkModel->save();
            }
        }
        $this->_redirectToListView();
    }
}
?>