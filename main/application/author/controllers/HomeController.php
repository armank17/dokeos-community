<?php
/**
 * controller default in author tool
 * @package Author 
 */

class application_author_controllers_Home extends appcore_command_Command 
{
    
    public $lpModel;
    public $modules;
    public $courseCode;
    public $courseInfo;
    public $searchEnabled;
    
    public function __construct () {
        $this->validateSession();
        $this->setTheme('tools');   
        $this->toolName = TOOL_AUTHOR;
        $this->setLanguageFile(array('learnpath', 'course_home', 'scormdocument', 'scorm', 'document', 'resourcelinker', 'registration'));
        $this->courseCode = api_get_course_id();
        $this->courseInfo = api_get_course_info();
                
        $this->lpModel = new application_author_models_ModelLearnpath();      
        
        $this->searchEnabled = (api_get_setting('search_enabled') == 'true');
    }
    
    public function adminHome() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/css/styles.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/script.js');
        $lpDelete = $this->getRequest()->getProperty('lpDelete', '');        
        if (!empty($lpDelete)) {
            $this->lpModel->setLpId($lpDelete);
            $this->lpModel->delete();
            if (api_get_setting('search_enabled') === 'true' && extension_loaded('xapian')) {
                //delete from keyword
                $searchkey = new SearchEngineManager();
                $searchkey->idobj = $lpDelete;
                $searchkey->course_code = $this->courseCode;
                $searchkey->tool_id = TOOL_LEARNPATH;
                $searchkey->deleteKeyWord();
                $this->lpModel->deleteEngine();
            }            
        }
        $lpVisible = $this->getRequest()->getProperty('lpVisible', '');
        if (!empty($lpVisible)) {
            $this->lpModel->setLpId($lpVisible);
            $changed = $this->lpModel->setVisibility();
        }        
        $this->modules = $this->lpModel->getAllModules();          
    }
    
    public function uploadPpt() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();               
    }
    
    public function uploadScorm() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();               
    }
        
    public function getAction() {
        $html = '';
        if (api_is_allowed_to_edit()) {
            $html .= '<div id="header_actions" class="actions">';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Builder'), array('class' => 'toolactionplaceholdericon toolactionnew')) . $this->get_lang("Builder") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Home&func=uploadScorm&curdirpath=/&tool=learnpath&'.api_get_cidreq().'" class="action-dialog" title="'.$this->get_lang("ScormImport").'">' . Display::return_icon('pixel.gif', $this->get_lang('Scorm'), array('class' => 'toolactionplaceholdericon toolactionscorm')) . $this->get_lang("ScormImport") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Home&func=uploadPpt&curdirpath=/&tool=learnpath&'.api_get_cidreq().'" class="action-dialog" title="'.$this->get_lang("Powerpoint").'">' . Display::return_icon('pixel.gif', $this->get_lang('Powerpoint'), array('class' => 'toolactionplaceholdericon toolactionsPowerPoint')) . $this->get_lang("Powerpoint") . '</a>';
            $html .= '</div>';            
        }        
        return $html;
    }
    
    public function loadIframeHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.tagit.min.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/css/iframe_styles.css', 'css');        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.tag-it.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js'); 
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/authorModel.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/iframeScriptController.js');
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        $objSecurity->verifyAccessToCourse(true);        
        if (api_get_setting('enable_author_tool') === 'false') {
            api_not_allowed();
        }                
    }
    
}

?>
