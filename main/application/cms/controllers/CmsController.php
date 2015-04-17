<?php

class application_cms_controllers_Cms extends appcore_command_Command 
{
    protected $_itemModel;
    private $Id;
    private $title;
    private $content;
    private $created_by;
    private $deleted_by;
    private $modified_by;
    private $status;
    private $cms;
    
    
        public function __construct() {
            $this->_itemModel = new application_cms_models_CmsModel();
            
        }
        public function saveCms(){
            $content = $this->getRequest()->getProperty('cms_editor', '');
            $title = $this->getRequest()->getProperty('title', '');
            $id=$this->getRequest()->getProperty('cmsId','');
            if ($id== null):
              $status = 1;  
            endif;
            $this->_itemModel->fieldValues['content'] = $content; 
            $this->_itemModel->fieldValues['title'] = $title;
            $this->_itemModel->fieldValues['id']= $id;
            $this->_itemModel->fieldValues['created_by'] = (api_get_user_id()==0)?null:api_get_user_id();
            $this->_itemModel->fieldValues['edited_by'] = (api_get_user_id()==0)?null:api_get_user_id();
            $this->_itemModel->fieldValues['status'] = $status;
            if ($this->_itemModel->saveCmsModel()):
                header('Location: ?module=cms&cmd=Index&'.api_get_cidreq());
            endif;
        }
        
        public function listCms(){
            $CmsModel = new CMSModel();
            $result= $CmsModel->ModellistCms();
            return $result;
        }
        public function show(){
            
            $cms = new application_cms_controllers_Cms();
            $cmsId = ($_GET['cmsId']>0)?$_GET['cmsId']:0;
            $where=' WHERE id='.$cmsId;
            $arr = $this->_itemModel->getCms($where);
            
            $this->content= $arr[0]['content'];
            
        }
        public function delete(){
            $cmsId = ($_GET['cmsId']>0)?$_GET['cmsId']:0;
            if($cmsId>0){
               if($this->_itemModel->ModeldeleteCms($cmsId)):
                   
               exit;
            endif; 
            }
        }
        public function edit(){
            $id= $_GET['cmsId'];
            header('Location: ?module=cms&cmd=Newpage&func=createPage&cmsId='.$id.'&'.api_get_cidreq());
            }
        public function getAction() {
            $func = $_GET['func'];
            
        $html = '';
        if (api_is_allowed_to_edit()) {
            $html .= '<div id="header_actions" class="actions">';
            if($func !='show'):
              $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'document/document.php">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactionback')) . $this->get_lang("Documents") . '</a>';     
            endif;
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactionlist')) . $this->get_lang("ListPage") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Newpage&func=createPage&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactiondocumentcreate')) . $this->get_lang("NewPage") . '</a>';
            $html .= '</div>';            
        }        
        return $html;
    }
        /**
 * Check if a document width the choosen filename allready exists
 */
        function document_exists($filename) {
	global $filepath;
	$filename = replace_dangerous_char($filename);
	return !file_exists($filepath.$filename.'.html');
        }
        
        public function get_Id(){
            return $this->Id;
        }
        public function get_title(){
            return $this->title;
        }
        public function get_content(){
            return $this->content;
        }
        public function get_createdby(){
            return $this->created_by;
        }
        public function get_deletedby(){
            return $this->deleted_by;
        }
        public function get_modifiedby(){
            return $this->modified_by;
        }
        public function get_status(){
            return $this->status;
        }
        public function set_Id($Id){
            $this->Id = $Id;
        }
        public function set_title($title){
            $this->title=$title;
        }
        public function set_content($content){
             $this->content;
        }
        public function set_createdby($createdBy){
             $this->created_by=$createdBy;
        }
        public function set_deletedby($deletedBy){
             $this->deleted_by=$deletedBy;
        }
        public function set_modifiedby($modifiedBy){
             $this->modified_by=$modifiedBy;
        }
        public function set_status($status){
             $this->status=$status;
        }
        public function validateForm($form){
            $cmsModel= new CMSModel();
            $cmsModel->ModelsaveCms();
    
        }

        }
?>
