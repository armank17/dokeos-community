<?php
/**
 * controller used for upgrade db page
 * @package Upgrade 
 */

class application_upgrade_controllers_UpdateDb extends appcore_command_Command 
{
    
    public $updateDbModel;
    public $databases;
    
    public function __construct() {
        $this->loadHtmlHeadXtra();
        $this->updateDbModel = new application_upgrade_models_UpdateDbModel();
    }
    
    /**
     * @link /main/index.php?module=upgrade&cmd=UpdateDb&func=migrateToUtf8
     */
    public function migrateToUtf8() {       
        $this->databases = $this->updateDbModel->getDatabases();
    }
    
    public function convert() {
        set_time_limit(0);
        $database = $this->getRequest()->getProperty('database', '');        
        $this->updateDbModel->updateDbTablesCharset($database);        
        $json['success'] = TRUE;
        $json['redirect'] = urlencode(api_get_path(WEB_CODE_PATH).'index.php?module=upgrade&cmd=UpdateDb&func=migrateToUtf8');
        echo json_encode($json);
        exit;
    }
    
    public function loadHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/upgrade/assets/css/upgrade.css', 'css');

    }
    
}
