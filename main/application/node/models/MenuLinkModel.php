<?php
class application_node_models_MenuLinkModel
{
    private $_ado;
    private $_tblMenuLink;
    private $_sessionId;
    private $_menuLinkId;
    
    
    public  $fieldValues = array();
    
    public function __construct($sessionId = null) {
        if (!isset($sessionId)){ $sessionId = api_get_session_id(); }
        $this->_sessionId = $sessionId;
        $this->_ado = appcore_db_DB::conn();
        $this->_tblMenuLink = Database::get_main_table(TABLE_MAIN_MENU_LINK);
    }
    
    public function setMenuLinkId($menuLinkId) {
        $this->_menuLinkId = $menuLinkId;
    }
    
    
    public function getMaxWeight($category, $current_access_url_id){
        return $this->_ado->GetOne("SELECT MAX(weight) FROM {$this->_tblMenuLink} WHERE active = 1 AND category = ? AND access_url_id = ? AND parent_id = 0", array($category, $current_access_url_id));
    }
    
    public function getMenuLinkType($menulink_id = null){
        if(!isset($menulink_id)) $menulink_id = $this->_menuLinkId;
        return $this->_ado->GetOne("SELECT link_type FROM {$this->_tblMenuLink} WHERE id = ?", array($menulink_id));
    }
    
    public function getMenuLinkTitle($menulink_id = null){
        if(!isset($menulink_id)) $menulink_id = $this->_menuLinkId;
        return $this->_ado->GetOne("SELECT title FROM {$this->_tblMenuLink} WHERE id = ?", array($menulink_id));
    }
    
    public function getMenuLinks($category, $showDisabled = true) {
        $current_access_url_id = (api_get_current_access_url_id()<0)? 1 : api_get_current_access_url_id();
        return $this->_ado->GetAll("SELECT * FROM {$this->_tblMenuLink} WHERE active = 1  AND category = ? AND access_url_id = ? AND enabled = ". ($showDisabled? 'enabled' : '1') ." ORDER BY weight", array($category, $current_access_url_id));
    }
    
    public function getLinkInfo($menuLinkId) {
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblMenuLink} WHERE id = ?", array($menuLinkId));
    }
   
    
    public function save() {
        $this->fieldValues['modified_by']       = api_get_user_id();
        $this->fieldValues['modification_date'] = api_get_datetime();
        
        if (!empty($this->_menuLinkId)) {
            // update the item
            $where     = 'id ='.intval($this->_menuLinkId);
            $updateSQL = $this->_ado->AutoExecute($this->_tblMenuLink, $this->fieldValues, 'UPDATE', $where);
            $lastId    = $this->_menuLinkId;
        }
        else {
            // create the item
            $this->fieldValues['created_by']    = api_get_user_id();
            $this->fieldValues['creation_date'] = api_get_datetime();
            $insertSQL = $this->_ado->AutoExecute($this->_tblMenuLink, $this->fieldValues, 'INSERT');
            $lastId    = $this->_ado->Insert_ID();
        }
        return $lastId;
    }
   
    public function delete($force = false) {
        if ($force) {
            // delete command
        }
        else {
            // update active field
            $where = 'id ='.intval($this->_menuLinkId);
            
            $this->fieldValues['modified_by']       = api_get_user_id();
            $this->fieldValues['modification_date'] = api_get_datetime();
            $this->fieldValues['active']            = 0;
            
            $this->_ado->AutoExecute($this->_tblMenuLink, $this->fieldValues, 'UPDATE', $where);  //$this->_ado->Execute("UPDATE {$this->_tblMenuLink} SET active = 0 WHERE id = ?", array($this->_menuLinkId));
        }
    }
}