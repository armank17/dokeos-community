<?php
class application_cms_controllers_CmsAjax  extends application_cms_controllers_Cms {
    public $messages;
    public function __construct() {
        parent::__construct();
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
    }
    public function saveItem() {
     extract($_POST);
     $json = array(); 
     if (trim($item_title) == '') {
            $json = array('itemId'=>0, 'message'=>$this->get_lang('TitleRequiredField'));    
     }
     else { 
         api_convert_encoding($item_title,"ISO-8859-15","UTF-8");
     }
 }  
}

?>
