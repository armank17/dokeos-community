<?php

class application_node_models_NodeModel{
    private $_ado;
    private $_sessionId;    
    private $_nodeId;
    private $_tblNode;
    public  $fieldValues = array();
    
     public function __construct($sessionId = null) {
        
        if (!isset($sessionId))  { $sessionId = api_get_session_id(); }
        
            $this->_sessionId = $sessionId;          
            $this->_ado = appcore_db_DB::conn();
            $this->_tblNode = Database::get_main_table(TABLE_MAIN_NODE);
           
    }
    public function getPages() {
        $api_get_current_access_url_id=( api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();
        return $this->_ado->GetAll("SELECT id,
                                           title,
                                           content,
                                           access_url_id,
                                           active,
                                           language_id,
                                           enabled,
                                           node_type,                                          
                                           display_title
                                           FROM {$this->_tblNode} 
                                           WHERE active = 1 AND access_url_id =".$api_get_current_access_url_id);
    }
    
    public function getPageInfo($nodeId) {
        return $this->_ado->GetRow("SELECT id,
                                           title,
                                           content,
                                           access_url_id,
                                           active,
                                           language_id,
                                           enabled,
                                           node_type,
                                           display_title
                                           FROM {$this->_tblNode} WHERE id = ?", array($nodeId));
    }
    public function save() { 
            $by = api_get_user_id();
            $this->fieldValues['modified_by']= $by;
            $this->fieldValues['modification_date']=api_get_datetime();
        if (!empty($this->_nodeId)) {            
            // update the item           
            $where = 'id ='.intval($this->_nodeId);
            $updateSQL = $this->_ado->AutoExecute($this->_tblNode, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_nodeId;
        }
        else {  
            //create the item'
            $this->fieldValues['created_by']= $by;
            $this->fieldValues['creation_date']= api_get_datetime();
            $insertSQL = $this->_ado->AutoExecute($this->_tblNode, $this->fieldValues, 'INSERT');
            $lastId = $this->_ado->Insert_ID();
        }
        return $lastId;
    }
    
    public function delete($force = false) {
        if ($force) {
            // delete command
        }
        else {
            // update active field
            $this->_ado->Execute("UPDATE {$this->_tblNode} SET active = 0 WHERE id = ?", array($this->_nodeId));
        }
    }
    
    public function setNodeId($nodeId) {
        $this->_nodeId = $nodeId;
    }
    
    public function getMenuLinkId($nodeId){
        return $this->_ado->GetOne("SELECT menu_link_id                                           
                                           FROM {$this->_tblNode} WHERE id = ?", array($nodeId));
    }
   
}
?>
