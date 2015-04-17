<?php

class application_index_controllers_search extends appcore_command_Command
{
    public $html;
    
    public $css;
    
    private $objCollection;
    
    public function __construct() {
        $this->css ='application/index/assets/css/style.css';
        $this->objCollection = new application_index_models_collection();
        $this->loadAjax();
    }
    
    private function loadAjax()
    {
        $this->ajax = new xajax();
        $this->ajax->setFlag("debug", false);
        $this->ajax->register(XAJAX_FUNCTION, array('showForm', $this, 'showForm'));
        $this->ajax->processRequest();
    }  
    
    public function showForm()
    {
        $objResponse = new xajaxResponse();
        $html = '<h1>form</h1>';
        $js ='';
        $objResponse->assign("divForm", "innerHTML", $html);
        $objResponse->script($js);
        return $objResponse;
    }
    
    public function student()
    {
        $html = 'vista del student';
        return $html;
    }
    
    public function getTitle()
    {
        return 'titulo';
    }
    
    public function getUser()
    {
        $result = $this->objCollection->getUser();
        return $result;
    }
    
    public function getHtmlUser()
    {
        $html='';
        $collection = $this->getUser();
        foreach ($collection->getIterator() as $objUser)
        {
            $html.=' usuario: '.$objUser->firstname;
        }
        
        return $html;
    }
}
?>
