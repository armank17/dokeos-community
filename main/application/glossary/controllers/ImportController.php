<?php

/**
 * Controller Glossary_Import
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_glossary_controllers_Import extends appcore_command_Command {

    public $_objSecurity;
    public $_objCollect;
    public $_objIndex;
    public $_objExcel;
    public $_objReader;
    //views
    //views
    public $css = '';
    public $form;
    public $text;
    public $action = false;

    function __construct() {
        $this->setTheme('tools');
        $this->setLanguageFile(array('glossary'));
        $this->_objIndex = new application_glossary_controllers_Index();
        $this->_objCollect = new application_glossary_models_collection();
    }
    
    function getAction(){
       return $this->_objIndex->getHeaderOptions();
    }
    
    public function index(){
        if(!empty($_POST)):
            //File
            global $charset;
        
            $course = $this->getRequest()->getProperty('cidReq');
            $file = pathinfo($_FILES['file_import']['name']);
            switch ($file['extension']):
                case 'csv':
                    $this->_objReader = new PHPExcel_Reader_CSV();
                    $this->_objExcel = $this->_objReader->load( $_FILES['file_import']['tmp_name'] );

                    $rowIterator = $this->_objExcel->getActiveSheet()->getRowIterator();
                    $dtaExport = array();
                    foreach ($rowIterator as $row):
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                        foreach ($cellIterator as $cell) :
                            $text = str_replace('"', '', $cell->getValue());
                            $result = explode(';', $text);
                            $dtaExport[] = array(
                                'A' => api_convert_encoding($result[0], $charset, 'UTF-8'),
                                'B' => api_convert_encoding($result[1], $charset, 'UTF-8')
                                );
                        endforeach;
                    endforeach;
                    break;
                default:
                    $this->_objReader = new PHPExcel_Reader_Excel5();
                    $this->_objReader->setReadDataOnly(true);
                    $this->_objExcel = $this->_objReader->load( $_FILES['file_import']['tmp_name'] );

                    $rowIterator = $this->_objExcel->getActiveSheet()->getRowIterator();
                    $dtaExport = array();
                    foreach ($rowIterator as $row):
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                        if(1 == $row->getRowIndex ()) continue;//skip first row
                            $i = $row->getRowIndex ();
                        foreach ($cellIterator as $cell) :
                            switch ($cell->getColumn()):
                                case 'A':
                                    $dtaExport[$i][$cell->getColumn()] = api_convert_encoding($cell->getValue(), $charset, 'UTF-8');
                                    break;
                                case 'B':
                                    $dtaExport[$i][$cell->getColumn()] = api_convert_encoding($cell->getValue(), $charset, 'UTF-8');
                                    break;
                            endswitch;
                        endforeach;
                    endforeach;
                    break;
            endswitch;
            //$this->_objExcel =  $this->_objReader->load();
            
            $action = $this->_objCollect->saveImport($dtaExport,$course);
            
            if($action == 1):
                $this->css = 'ui-state-highlight ui-corner-all';
                $this->action = true;
                $this->text = '<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> File imported successfully</p>';
            else:
                $this->css = 'ui-state-error ui-corner-all';
                $this->action = true;
                $this->text = '<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>Alert:</strong> The content field is required</p>';
                
            endif;
        endif;
        
        $this->form = $this->getForm();
    }
    
    public function getForm(){
        global $charset;
        // Editor config
        $editor_config = array('ToolbarSet' => 'Glossary', 'Width' => '100%', 'Height' => '250');

        $form = new FormValidator('glossary-import','post', 'index.php?module=glossary&cmd=Import&func=index&'.api_get_cidreq());
        $form->addElement('file', 'file_import', $this->get_lang('UploadFileToImport').' (xls, csv)',array('id' => 'file_import'));
        $allowed_ext_types = array ('xls', 'csv');
        $form->addRule('file_import', $this->get_lang('ExtensionNotAllowed'), 'filetype', $allowed_ext_types);
        $form->addRule('file_import', '<div class="required">'.$this->get_lang('ThisFieldIsRequired'), 'required');
        $form->addElement('style_submit_button', 'ImportGlossary', $this->get_lang('Import'), 'class="save" style="margin-right:400px;"');
        $form->addElement('html','<p style="margin-left:15px;"><br /><br /><br />'.$this->get_lang('CSVMustLookLike').' ('.$this->get_lang('MandatoryFields').'):</p>');
        $form->addElement('html','<div style="margin-left:15px"><blockquote><pre><b>'.$this->get_lang('Term').'</b>;<b>'.$this->get_lang('Description').'</b><br/>Dokeos;Is a company dedicated to open source Learning Management Systems.</pre></blockquote></div>');
        $token = Security::get_token();
        $form->addElement('hidden','sec_token');
        $form->setConstants(array('sec_token' => $token));
               
        return $form;       
    }
    
}