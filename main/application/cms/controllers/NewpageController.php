<?php

class application_cms_controllers_Newpage extends appcore_command_Command {
    public $showEditor;
    public $editorValue;
    public $editorConfig;
    public $itemCms;
    public $itemModel;
    public $title='';
    
    public function __contruct(){
        $this->itemCms = new application_cms_controllers_Cms();   
        $this->itemCms->Id; 
    }
    public function createPage(){
         $cmsId=($_GET['cmsId']==0)?null:$_GET['cmsId'];
         
        // echo 'id: '.$cmsId;
         if(isset($cmsId)):
             $this->itemModel = new application_cms_models_CmsModel();
             $arr=$this->itemModel->getCms('WHERE id='.$cmsId);
             $this->editorValue= $arr[0]['content'];
             $this->title = $arr[0]['title'];
         else :             
             $this->editorValue='';
         endif;
         $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/cms/assets/css/styles.css', 'css');
         $this->showEditor = true;
         
         $this->editorConfig = array(
                                'ToolbarSet' => 'cms', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/'
                            );
    }
    public function getAction() {
        $html = '';
        if (api_is_allowed_to_edit()) {
            $html .= '<div id="header_actions" class="actions">';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactionback')) . $this->get_lang("Back") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactionlist')) . $this->get_lang("ListPage") . '</a>';
            $html .= '</div>';            
        }        
        return $html;
    }
    
    
    
    
}
?>
