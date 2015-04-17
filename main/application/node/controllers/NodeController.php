<?php
/**
 * page controller for node feature
 * @package Node 
 */

class application_node_controllers_Node extends appcore_command_Command 
{
    public $editorConfig;
    public $nodeModel;
    public $nodeId;
    public $nodeInfo;
    public $nodes;
    
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
        
        $this->nodeModel = new application_node_models_NodeModel();
        
        $this->nodeId = $this->getRequest()->getProperty('nodeId', '');
        if (!empty($this->nodeId)) {
            $this->nodeInfo = $this->nodeModel->getNodeInfo($this->nodeId);
        }
        
        $this->pages = $this->nodeModel->getPages();
    
    $this->accessUrlId = api_get_current_access_url_id();            
    if ($this->accessUrlId < 0) {
        $this->accessUrlId = 1;
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
           
            $this->nodeModel->fieldValues['title'] = $node_title;
            $this->nodeModel->fieldValues['content'] = $node_editor;
            $this->nodeModel->fieldValues['access_url_id'] = $this->accessUrlId ;
            $this->nodeModel->fieldValues['created_by'] = $created_by;
            $this->nodeModel->fieldValues['modified_by'] = $created_by;
            $this->nodeModel->fieldValues['creation_date'] = api_get_datetime();
            $this->nodeModel->fieldValues['modification_date'] = api_get_datetime();
            $this->nodeModel->fieldValues['active'] = $active;
            $this->nodeModel->fieldValues['enabled'] = $enabled;
            $this->nodeModel->fieldValues['node_language'] = $node_language;
            $lastNodeId = $this->nodeModel->save();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Node&func=Index&'.api_get_cidreq());
    }
    
    public function delete() {
        if (!empty($this->nodeId)) {
            $this->nodeModel->setNodeId($this->nodeId);
            $this->nodeModel->fieldValues['deleted_by'] = $created_by;
            $this->nodeModel->delete();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Node&func=Index&'.api_get_cidreq());
    }
    
    public function update() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);       
            $this->nodeModel->setNodeId($nodeId);
            $this->nodeModel->fieldValues['title'] = $node_title;
            $this->nodeModel->fieldValues['content'] = $node_editor;
            $this->nodeModel->fieldValues['modified_by'] = $created_by;
            $this->nodeModel->fieldValues['modification_date'] = api_get_datetime();
            $this->nodeModel->save();
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Node&func=Index&'.api_get_cidreq());
    }
        
    public function getAction() {
        $html = '';
        if (api_is_allowed_to_edit()) {
            $html .= '<div id="header_actions" class="actions">';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'document/document.php?'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . $this->get_lang("Documents") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Node&func=Index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Pages'), array('class' => 'toolactionplaceholdericon toolactiondocumentpages')) . $this->get_lang("Pages") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Node&func=getForm&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('NewPage'), array('class' => 'toolactionplaceholdericon toolactiondocumentcreate')) . $this->get_lang("NewPage") . '</a>';
            $html .= '</div>';            
        }
          return $html;
    }
    
    public function loadHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/pages.css', 'css');
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/nodeModel.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/functions.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/nodeController.js');
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        $objSecurity->verifyAccessToCourse();        
    }
    
}
?>
