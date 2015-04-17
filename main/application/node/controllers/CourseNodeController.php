<?php
/**
 * page controller for node feature
 * @package Node 
 */

class application_node_controllers_CourseNode extends appcore_command_Command 
{
    public $editorConfig;
    public $nodeModel;
    public $courseNodeModel;
    public $nodeId;
    public $nodeInfo;
    public $nodes;
    public $isAllowedToEdit;
    
    public $accessUrlId;
    
    public function __construct() {
            $this->validateSession();
            $this->setTheme('tools');
            $this->loadHtmlHeadXtra();

        $this->editorConfig = array(
                                'ToolbarSet' => 'Node', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'Placeholder' => get_lang('TypeHereNode'),
                              );
        
        $this->nodeModel = new application_node_models_NodeModel();
        $this->courseNodeModel = new application_node_models_CourseNodeModel();
        
        $this->nodeId = $this->getRequest()->getProperty('nodeId', '');
        if (!empty($this->nodeId)) {
            $this->nodeInfo = $this->courseNodeModel->getCourseNodeInfo($this->nodeId);
        }
        
        $this->pages = $this->courseNodeModel->getCourseNodes();
    
    $this->accessUrlId = api_get_current_access_url_id();            
    if ($this->accessUrlId < 0) {
        $this->accessUrlId = 1;
    }
}
    
    public function Index() {
        $this->isAllowedToEdit =  api_is_allowed_to_edit();
    }
    
    public function getForm() {
        
    }
    
    public function view() {
        
    }
    
    public function updateEnabled(){
         extract($_GET);
             $this->nodeModel->setNodeId($nodeId);
             $this->nodeModel->fieldValues['enabled']=$enabled;
             $this->nodeModel->save();
             $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=Index&'.api_get_cidreq());
    }
    
    public function create() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST); 
            $session_id =api_get_session_id();
            $enabled= ($enabled==null)?0:$enabled;
            $this->nodeModel->fieldValues['title'] = $node_title;
            $this->nodeModel->fieldValues['content'] = $node_editor;
            $this->nodeModel->fieldValues['access_url_id'] = $this->accessUrlId ;            
            $this->nodeModel->fieldValues['active'] = $active;
            $this->nodeModel->fieldValues['enabled'] = $enabled;
            //$this->nodeModel->fieldValues['node_language'] = $node_language;
         
            $lastNodeId = $this->nodeModel->save();
            
            $this->courseNodeModel->fieldValues['node_id']=$lastNodeId;
            $this->courseNodeModel->fieldValues['course_code']=$course_code;
            $this->courseNodeModel->fieldValues['session_id']= $session_id;
            
            $this->courseNodeModel->save();
            $_SESSION["display_confirmation_message"] = get_lang("PageAdd");
            
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=Index&'.api_get_cidreq());
    }
    
    public function delete() {
        if (!empty($this->nodeId)) {
            $this->nodeModel->setNodeId($this->nodeId);
            $this->nodeModel->fieldValues['deleted_by'] = $created_by;
            $this->nodeModel->delete();
            $_SESSION["display_confirmation_message"] = get_lang("PageDelete");
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=Index&'.api_get_cidreq());
    }
    
    public function update() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);      
            $enabled= ($enabled==null)?0:$enabled;
            $this->nodeModel->setNodeId($nodeId);
            $this->nodeModel->fieldValues['title'] = $node_title;
            $this->nodeModel->fieldValues['content'] = $node_editor;
            $this->nodeModel->fieldValues['modified_by'] = api_get_user_id();
            $this->nodeModel->fieldValues['enabled'] = $enabled;
            //$this->nodeModel->fieldValues['modification_date'] = api_get_datetime();
            $this->nodeModel->save();
            $_SESSION["display_confirmation_message"] = get_lang("PageUpdate");
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=Index&'.api_get_cidreq());
    }
        
    public function getAction() {
        if(isset($_REQUEST['tool']) && $_REQUEST['tool'] = 'scenario') {
	
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
	$step_id = $_REQUEST['step'];
	$activity_id = $_REQUEST['activity_id'];

	$sql_check = "SELECT * FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id." AND activity_id = ".$activity_id." AND user_id = ".api_get_user_id();
	$res_check = Database::query($sql_check, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res_check);
	if($num_rows == 0) {
		$sql = "INSERT INTO $TBL_SCENARIO_ACTIVITY_VIEW (activity_id, step_id, user_id, view_count, score, status) VALUES($activity_id, $step_id, ".api_get_user_id().", 1, '0', 'completed')";
	}
	else {
		$sql = "UPDATE $TBL_SCENARIO_ACTIVITY_VIEW SET view_count = view_count + 1 WHERE activity_id = ".$activity_id." AND step_id = ".$step_id." AND user_id = ".api_get_user_id();
	}

	Database::query($sql,__FILE__,__LINE__);
}
        if(isset($_SESSION["display_confirmation_message"])){
            Display :: display_confirmation_message2($_SESSION["display_confirmation_message"],false,true);
            unset($_SESSION["display_confirmation_message"]);
        }
        $html = '';        
        $html .= '<div id="header_actions" class="actions">';        
        $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'document/document.php?'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . $this->get_lang("Documents") . '</a>';
        if (api_is_allowed_to_edit()) {
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=getForm&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('NewPage'), array('class' => 'toolactionplaceholdericon toolactiondocumentcreate')) . $this->get_lang("NewPage") . '</a>';
        }
        $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=Index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Pages'), array('class' => 'toolactionplaceholdericon toolactiondocumentpages')) . $this->get_lang("Pages") . '</a>';

        if(!empty($_SESSION['gidReq'])){
            $gidReq = $_SESSION['gidReq'];
            $html.= '<a href="document/document.php?'. api_get_cidreq() .'&gidReq='.$gidReq.'">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactionback')) . ' ' . get_lang('Back') . '</a>';
        }

        $html .= '</div>';   
        if(isset($_REQUEST['tool']) && $_REQUEST['tool'] = 'scenario') {
                $courseInfo = api_get_course_info(api_get_course_id());
                
                $html .= '<div id="continueContainer" name="continueContainer"><a onclick="goto(\''.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/index.php'.'\')"><button id="continue" name="continue" class="continue" style="display:none;position: absolute; font-size: 18px; z-index: 100;">Continue</button></a></div>';
                $html .= "<script>function goto (href) { window.parent.location.href = href }</script>";
                
                echo '<script>$("#continue").hide();</script>';                

                $html .= "<script> function goto (href) { window.parent.location.href = href }</script>";
                $html .= '
                <script>
                function positioning_btnContinue()	{
                    offset = $("#content").offset();
                    width = $("#content").width();
                    height = $("#content").height();
                    console.log((offset.left + width) + " " + (offset.top + height) );
                    $("#continue").css("width","140px");
                    $("#continue").css("left",(offset.left + width)-130);
                    $("#continue").css("top",(offset.top + height)-35);
                    $("#continue").show();

                }
                $( window ).resize(function() {
                    positioning_btnContinue();
                });
                setTimeout(function(){
                    positioning_btnContinue();
                },400);
                </script>';
        }
          return $html;
    }
    
    public function loadHtmlHeadXtra() {
        /*$this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/pages.css', 'css');*/
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/courseNode.css', 'css');
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/glossary_page.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/courseNodeModel.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/functions.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/courseNodeController.js');
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        $objSecurity->verifyAccessToCourse();        
    }
    
}
?>
