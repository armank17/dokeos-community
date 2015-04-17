<?php
/**
 * page controller for node feature
 * @package Node 
 */

class application_node_controllers_Page extends appcore_command_Command 
{
    public $editorConfig;
    public $pageModel;
    public $nodeId;
    public $pageInfo;
    public $pages;
    
    public $accessUrlId;
    
    public function __construct() {
            $this->validateSession();
            $this->setTheme('tools');
            $this->loadHtmlHeadXtra();

        $this->editorConfig = array(
                                'ToolbarSet' => 'Node', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/'
                              );
        
        $this->pageModel = new application_node_models_PageModel();
        
        $this->nodeId = $this->getRequest()->getProperty('nodeId', '');
        if (!empty($this->nodeId)) {
            $this->pageInfo = $this->pageModel->getPageInfo($this->nodeId);
        }
        
        $this->pages = $this->pageModel->getPages();
    
    $this->accessUrlId = api_get_current_access_url_id();            
    if ($this->accessUrlId < 0) {
        $this->accessUrlId = 0;
    }
        
    }
    
    public function Index() {
        
    }
    
    public function getForm() {
        
    }
    
    public function view() {
        
    }
    
    public function create() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);   
            $show_header=($show_header==null)?0:$show_header;
            $this->pageModel->fieldValues['title'] = $node_title;
            $this->pageModel->fieldValues['content'] = $node_editor;
            $this->pageModel->fieldValues['target'] = $target; 
            //$this->pageModel->fieldValues['course_code']= $course_code;
            $this->pageModel->fieldValues['access_url_id'] = $this->accessUrlId ;
            $this->pageModel->fieldValues['show_header'] = $show_header;
            $this->pageModel->fieldValues['created_by'] = $created_by;
            $this->pageModel->fieldValues['modified_by'] = $created_by;
            $this->pageModel->fieldValues['creation_date'] = api_get_datetime();
            $this->pageModel->fieldValues['modification_date'] = api_get_datetime();
            $lastNodeId = $this->pageModel->save();
            $this->pageModel->fieldValuesToRel['course_code']= $course_code;
            $this->pageModel->fieldValuesToRel['node_id']= $lastNodeId;
            $this->pageModel->save_Node_rel_course();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=Index&'.api_get_cidreq());
    }
    
    public function delete() {
        if (!empty($this->nodeId)) {
            $this->pageModel->setNodeId($this->nodeId);
            $this->pageModel->fieldValues['deleted_by'] = $created_by;
            $this->pageModel->delete();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=Index&'.api_get_cidreq());
    }
    
    public function update() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);       
            $this->pageModel->setNodeId($nodeId);
            $this->pageModel->fieldValues['title'] = $node_title;
            $this->pageModel->fieldValues['content'] = $node_editor;
            $this->pageModel->fieldValues['showHeader'] = $showHeader;
            $this->pageModel->fieldValues['modified_by'] = $created_by;
            $this->pageModel->fieldValues['modification_date'] = api_get_datetime();
            $this->pageModel->save();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=Index&'.api_get_cidreq());
    }
        
    public function getAction() {
        $html = '';
        if (api_is_allowed_to_edit()) {
            $html .= '<div id="header_actions" class="actions">';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'document/document.php?'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . $this->get_lang("Documents") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=Index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Pages'), array('class' => 'toolactionplaceholdericon toolactiondocumentpages')) . $this->get_lang("Pages") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=getForm&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('NewPage'), array('class' => 'toolactionplaceholdericon toolactiondocumentcreate')) . $this->get_lang("NewPage") . '</a>';
            $html .= '</div>';            
        }
          return $html;
       
        
        
    }
    
    public function loadHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/pages.css', 'css');
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/pageModel.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/functions.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/pageController.js');
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        $objSecurity->verifyAccessToCourse();        
    }
    
}
?>
