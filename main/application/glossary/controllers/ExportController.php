<?php

/**
 * Controller Glossary_Export
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_glossary_controllers_Export extends appcore_command_Command {

    public $_objSecurity;
    public $_objCollect;
    public $_objIndex;
    public $_objExcel;
    //views
    //views
    public $form;

    function __construct() {
        $this->setTheme('tools');
        $this->setLanguageFile(array('glossary'));
        $this->_objIndex = new application_glossary_controllers_Index();
        $this->_objCollect = new application_glossary_models_collection();
        $this->_objExcel = new PHPExcel();
    }
    
    function getAction(){
       return $this->_objIndex->getHeaderOptions();
    }
    
    public function index(){
        if(!empty($_POST)):
            $check = Security::check_token('post');
            if ($check) :
                $data = $this->_objCollect->getGlossaryList();
                $i = 1;
                foreach ($data->getIterator() as $value):
                    $this->_objExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.($i), $value->name)
                        ->setCellValue('B'.($i), $value->description);
                    $i++;
                endforeach;
                $cbotype = $this->getRequest()->getProperty('cbo_type');
                
                $this->_objExcel->getActiveSheet()->setTitle('Glossary');
                $this->_objExcel->setActiveSheetIndex(0);
                $type_file = array('csv'=>'CSV', 'xls'=>'Excel5', 'xlsx'=>'Excel2007');
                
                $objWriter = PHPExcel_IOFactory::createWriter($this->_objExcel, $type_file[$cbotype]);
                $url = api_get_path(SYS_COURSE_PATH);
                $course = api_get_course_info();
                
                //$temp_path = $url.$course['path'].'/temp/';
                
                $name = $url.$course['path'].'/temp/'.'GlossaryTerms.' . $cbotype;
                if ($cbotype == 'csv')
                    $objWriter->setDelimiter(';');
                
                $objWriter->save($name);
                
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($name));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($name));
                ob_clean();
                flush();
                readfile($name);
                exit;
            endif;
        endif;
        $this->form = $this->getForm();
    }
    
    function getForm(){
        global $charset;
        // Editor config
        $editor_config = array('ToolbarSet' => 'Glossary', 'Width' => '100%', 'Height' => '250');

        $form = new FormValidator('glossary-export', 'post', 'index.php?module=glossary&cmd=Export&func=index&' . api_get_cidreq());
        $type_file = array('csv' => $this->get_lang('CSVFileType'), 'xls' => $this->get_lang('XLSFileType'), 'xlsx' => $this->get_lang('XLSXFileType'));
        $form->addElement('select', 'cbo_type', $this->get_lang('SelectFileType'), $type_file,array('id' => 'cbo_type'));
        $form->addElement('style_submit_button', 'ExportGlossary', $this->get_lang('Export'), 'class="save"');
        
        $token = Security::get_token();
        $form->addElement('hidden','sec_token');
        $form->setConstants(array('sec_token' => $token));
        
        return $form;
    }

}