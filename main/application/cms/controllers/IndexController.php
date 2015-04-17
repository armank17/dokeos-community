<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class application_cms_controllers_Index extends appcore_command_Command 
{
    public $cms;
    public $cmsModel;
    public function __construct() {
        $this->cmsModel = new application_cms_models_CmsModel();
        $where = ' WHERE status > 0 ';
        $this->cms = $this->cmsModel->getCms($where);
        
    }
    public function Index(){
        
    }
    public function getAction() {
        $html = '';
        if (api_is_allowed_to_edit()) {
            $html .= '<div id="header_actions" class="actions">';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'document/document.php">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactionback')) . $this->get_lang("Documents") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Index&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactionlist')) . $this->get_lang("ListPage") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Newpage&func=createPage&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $this->get_lang('Cms'), array('class' =>  'toolactionplaceholdericon toolactiondocumentcreate')) . $this->get_lang("NewPage") . '</a>';
            $html .= '</div>';            
        }        
        return $html;
    }
    
    
}
?>
