<?php

/**
 * Controller Glossary_Index
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_glossary_controllers_Index extends appcore_command_Command {

    public $_objSecurity;
    public $_objCollect;
    public $_paginator;
    //views
    public $pagerLinks;
    public $html;
    public $list;

    function __construct() {
        $this->setTheme('tools');
        $this->setLanguageFile(array('glossary'));
        $this->_paginator = new appcore_library_pagination_Paginator();
        $this->_objCollect = new application_glossary_models_collection();
        
        //Data Terms all
        $search = $this->getRequest()->getProperty('search');
        $search = ($search == 'az')?'':$search;//
        $data = $this->_objCollect->getGlossaryList($search);
        $this->_objCollect->generatePaginator($data,$this->_paginator);
        $this->pagerLinks = $this->_paginator->links();
        $this->list = $this->getHtmlList($data);
    }
    
    /**
     * Get html glossary list
     */
    public function getHtmlList ($data,$item = "") {
        // Database table definition
        $html = '';
        $page = $this->getRequest()->getProperty('page', 1);
        $pag = '';
        if($page > 1):
            $pag = '&page=' . $page;
        endif;
        if($data->count() > 0):
            foreach ($data->getIterator() as $value):
                if($item == $value->glossary_id)
                    $name = '<b>' . $value->name . '</br>';
                else
                    $name = $value->name;
                
                $html .= '<tr class="glossary-items"><td>' . PHP_EOL
                    . '<a href="index.php?module=glossary&cmd=Item&func=index&' 
                        . api_get_cidreq() . '&id=' . $value->glossary_id . $pag . '">' . PHP_EOL  
                    . '<img class="actionplaceholdericon actionpreview" src="' 
                        . api_get_path(WEB_IMG_PATH) . '/pixel.gif" title="' 
                        . $value->name . '" alt="' . $value->name . '" />' . PHP_EOL
                    . $name
                    . '</a>' . PHP_EOL
                    . '</td></tr>' . PHP_EOL;
            endforeach;
        else:
            $html = get_lang('ThereAreNoDefinitionsHere');
        endif;
        return $html;
    }
    
    function getAction(){
        return $this->getHeaderOptions();
//        return self::getHeaderOptions();
    }
    
    public function getHeaderOptions() {
        // setting the tool constants
        $tool = TOOL_GLOSSARY;
        // Display
        $html = '<div class="actions">';
        
        $cssnew = '';
        $cssimport = '';
        $cssexport = '';
        switch ($this->getRequest()->getProperty('cmd')):
            case 'add': $cssnew = 'class="menu-select"'; break;
            case 'import': $cssimport = 'class="menu-select"';break;
            case 'export': $cssexport = 'class="menu-select"';break;
        endswitch;
        
        if (api_is_allowed_to_edit(null, true)) {
            
            $action = $this->getRequest()->getProperty('cmd');
            if(!empty($action)):
                $html .= '<a href="index.php?&module=glossary&' . api_get_cidreq() . '" >' . 
                Display::return_icon('pixel.gif', $this->get_lang('Back'), 
                    array('class' => 'toolactionplaceholdericon toolactionback')) . 
                    ' ' . $this->get_lang('Back') . '</a>';
            endif;
            
            
            $html .= '<a href="index.php?&module=glossary&cmd=Add&func=index&' . api_get_cidreq() . '" '. $cssnew . '>' .
                    Display::return_icon('pixel.gif', $this->get_lang('NewTerm'), 
                    array('class' => 'toolactionplaceholdericon toolglossaryadd')) . 
                    '  ' . $this->get_lang('NewTerm') . '</a>';
            
            $html .= '<a href="index.php?&module=glossary&cmd=Import&func=index&' . api_get_cidreq() . '" '. $cssimport . '>' . 
                    Display::return_icon('pixel.gif', $this->get_lang('ImportGlossaryTerms'), 
                    array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . 
                    ' ' . $this->get_lang('ImportGlossaryTerms') . '</a>';
            
            $html .= '<a href="index.php?&module=glossary&cmd=Export&func=index&' . api_get_cidreq() . '" '. $cssexport . '>' . 
                    Display::return_icon('pixel.gif', $this->get_lang('ExportGlossaryTerms'), 
                    array('class' => 'toolactionplaceholdericon toolactionauthorexport')) . 
                    ' ' . $this->get_lang('ExportGlossaryTerms') . '</a>';
            
        } else {
            $html .= $this->get_lang('Glossary');
        }
        $html .= '</div>';

        return $html;
    }

}