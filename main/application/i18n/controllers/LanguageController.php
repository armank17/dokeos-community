<?php
class application_i18n_controllers_Language extends appcore_command_Command 
{
    public function __construct() {}
    
    public function lang() {        
        $langFile = $this->getRequest()->getProperty('file', '');        
        if (!empty($langFile)) {
            $this->setLanguageFile(array($langFile));
        }        
        $variable = $this->getRequest()->getProperty('variable', '');
        $value = $variable;
        if (!empty($variable)) {
            $value = $this->encodingCharset($this->get_lang($variable));
        }
        echo trim($value);
        exit;        
    }
    
}
?>
