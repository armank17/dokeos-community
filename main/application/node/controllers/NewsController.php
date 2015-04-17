<?php
include api_get_path(SYS_CODE_PATH).'inc/lib/system_announcements.lib.php';
require_once (api_get_path(LIBRARY_PATH).'timezone.lib.php');

class application_node_controllers_News extends appcore_command_Command {
    
    public $editorConfig;
    public $newsModel;
    public $nodeModel;
    public $nodeId;
    public $pageInfo;
    public $pages;
    public $accessUrlId;
    public $languageList;
    
   public function __construct() {
       $this->validateSession();
       $this->setTheme('');
       $this->loadHtmlHeadXtra();

        $this->editorConfig = array(
                                'ToolbarSet' => 'Node', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/'
                                    );
        
        $this->newsModel = new application_node_models_NewsModel();
        $this->nodeModel = new application_node_models_NodeModel();
        
        $this->nodeId = $this->getRequest()->getProperty('nodeId', '');
        
        if (!empty($this->nodeId)) {
            $this->pageInfo = $this->newsModel->getPageInfo($this->nodeId);
        }
        
        $this->pages = $this->newsModel->getPages();
        $this->accessUrlId = api_get_current_access_url_id();            
    if ($this->accessUrlId < 0) {
        $this->accessUrlId = 1;
    }
    $this->setLanguageInterface();
       
   }
  
   public function getForm(){
       $this->pageInfo['start_date'] = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $this->pageInfo['start_date'], 'd-m-Y H:i'); //date("Y-m-d H:i", strtotime($this->pageInfo['start_date']));
       $this->pageInfo['end_date']   = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $this->pageInfo['end_date'], 'd-m-Y H:i'); //date("Y-m-d H:i", strtotime($this->pageInfo['end_date']));
   }
   public function Index(){
       
   }
   public function delete() {
        if (!empty($this->nodeId)) { 
            $this->newsModel->setNodeId($this->nodeId);
            $this->newsModel->fieldValues['deleted_by'] = $created_by;
            $this->newsModel->delete();
        }
         $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=Index');
    }
  
    public function update() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            
             $visible_trainer = ($visible_trainer==null)?0:$visible_trainer;
             $visible_learner = ($visible_learner==null)?0:$visible_learner;
             $visible_guest = ($visible_guest==null)?0:$visible_guest;
             $enabled = ($enabled==null)?0:$enabled;
            extract($_POST);  
            $language_id = (api_get_language_id($language_id) == NULL)? 0 : api_get_language_id($language_id);
            $start_ts = strtotime($startDate);
            $end_ts = strtotime($endDate);
            $user_ts = strtotime(api_get_datetime());
            
            if(($user_ts >= $start_ts) && ($user_ts <= $end_ts)){
                $enabled = 1;
            }else  $enabled = 0;
            $value = str_replace("&nbsp;"," ",$node_editor);
            $this->newsModel->setNodeId($nodeId);
            $this->nodeModel->setNodeId($nodeId);
            
            $this->nodeModel->fieldValues['title'] = $node_title;
            $this->nodeModel->fieldValues['content'] = $value;
            $this->nodeModel->fieldValues['active'] = $active;
            //$this->nodeModel->fieldValues['enabled'] = $enabled;
            $this->nodeModel->fieldValues['language_id'] = $language_id;  
          
            $this->nodeModel->save();
            
            $this->newsModel->fieldValues['visible_by_trainer'] = $visible_trainer;
            $this->newsModel->fieldValues['visible_by_learner'] = $visible_learner;
            $this->newsModel->fieldValues['visible_by_guest']   = $visible_guest;
            $this->newsModel->fieldValues['start_date'] = TimeZone::ConvertTimeFromUserToServer(api_get_user_id(), $startDate, 'Y-m-d H:i:s'); //date('Y-m-d H:i:s', strtotime($startDate));
            $this->newsModel->fieldValues['end_date']   = TimeZone::ConvertTimeFromUserToServer(api_get_user_id(), $endDate, 'Y-m-d H:i:s'); //date('Y-m-d H:i:s', strtotime($endDate));
            
            $this->newsModel->save();           
            
            
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=Index');
    }
    
    public function create() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            extract($_POST);
             $language_id = (api_get_language_id($language_id) == NULL)? 0 : api_get_language_id($language_id);
             $visible_trainer = ($visible_trainer==null)?0:$visible_trainer;
             $visible_learner = ($visible_learner==null)?0:$visible_learner;
             $visible_guest = ($visible_guest==null)?0:$visible_guest;
            
             $startDate = ($startDate==null)?'0000-00-00 00:00:00': TimeZone::ConvertTimeFromUserToServer(api_get_user_id(), $startDate, 'Y-m-d H:i:s'); //date('Y-m-d H:i:s', strtotime($startDate));
             $endDate = ($endDate==null)?'0000-00-00 00:00:00': TimeZone::ConvertTimeFromUserToServer(api_get_user_id(), $endDate, 'Y-m-d H:i:s'); //date('Y-m-d H:i:s', strtotime($endDate));
            /* to save in node table*/
             
            $start_ts = strtotime($startDate);
            $end_ts = strtotime($endDate);
            $user_ts = strtotime(api_get_datetime());
            
            if(($user_ts >= $start_ts) && ($user_ts <= $end_ts)){
                $enabled = 1;
            }else  $enabled = 0;
            $value = str_replace("&nbsp;"," ",$node_editor);
            $this->nodeModel->fieldValues['title'] = $node_title;
            $this->nodeModel->fieldValues['content'] = $value;
            $this->nodeModel->fieldValues['active'] = $active;
            $this->nodeModel->fieldValues['node_type'] = NODE_NEWS;
            //$this->nodeModel->fieldValues['enabled'] = $enabled;
            $this->nodeModel->fieldValues['language_id'] = $language_id; 
            $this->nodeModel->fieldValues['access_url_id'] = $this->accessUrlId;
                                   
            $lastId = $this->nodeModel->save();
            /* to save in node_news table extra fields*/
            $this->newsModel->fieldValues['node_id'] = $lastId;
            $this->newsModel->fieldValues['visible_by_trainer']= $visible_trainer;
            $this->newsModel->fieldValues['visible_by_learner']= $visible_learner;
            $this->newsModel->fieldValues['visible_by_guest']= $visible_guest;
            $this->newsModel->fieldValues['start_date']= $startDate;
            $this->newsModel->fieldValues['end_date']= $endDate;
            $this->newsModel->save();
           
            if($send_mail==1){
                SystemAnnouncementManager::send_system_announcement_by_email($node_title, $node_editor,$visible_trainer, $visible_learner);
            }
            
        }
        $this->redirect(api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=Index');
    }    
    
    public function loadHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'inc/lib/javascript/chosen/chosen.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/css/pages.css', 'css');
        
        // timepicker
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.timepicker/jquery-ui-timepicker-addon.css', 'css');
        
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.timepicker/jquery-ui-timepicker-addon.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jQuery-Timepicker/localization/jquery-ui-timepicker-'.api_get_language_isocode().'.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-'.api_get_language_isocode().'.js');
        
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'inc/lib/system_announcements.lib.php');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/functions.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/NewsModel.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/node/assets/js/NewsController.js');
    }
    
    public function getAction() { 
      //$html = '';
          if (api_is_allowed_to_edit()) {
//               $html .= '<div id="header_actions" class="actions">';
//               $html .= '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=Index">' . Display::return_icon('pixel.gif', $this->get_lang('Back'), array('class' => 'toolactionplaceholdericon toolactionlist')) . $this->get_lang("list") . '</a>';  
//               $html .= '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=getForm">' . Display::return_icon('pixel.gif', $this->get_lang('HomePage'), array('class' => 'toolactionplaceholdericon toolactionannounce_add')) . $this->get_lang("AddNews") . '</a>';
//               $html .= '</div>';
               Display::display_header_admin_of_portal(3);
          }
      //return $html;

   }
   
   public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        //$objSecurity->verifyAccessToCourse();        
   }
    
    public function setVisible(){
        $node_id = $this->getRequest()->getProperty('nodeId', '');
        $visible_by = $this->getRequest()->getProperty('person', '');
        $value = $this->getRequest()->getProperty('value', ''); 
        $value = ($value==0) ? 1:0;
        $this->newsModel->updateVisibility($node_id, $visible_by, $value);
    }
    
    public function setEnable(){
        $node_id = $this->getRequest()->getProperty('nodeId', '');
        $value   = $this->getRequest()->getProperty('value', ''); 
        $value   = 1 - $value;
        $this->newsModel->updateEnabled($node_id, $value);
    }
}
?>
