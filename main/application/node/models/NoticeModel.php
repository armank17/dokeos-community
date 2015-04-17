
<?php

class application_node_models_NoticeModel 
{
    private $_ado;
    private $_sessionId;
    private $_nodeId;
    private $_tblNode;
    private $_tblLang;
    public  $fieldValues = array();
   
    
    public function __construct($sessionId = null) {
        $this->_sessionId = $sessionId;    
        $this->_ado = appcore_db_DB::conn();
        
        $this->_tblNode = Database::get_main_table(TABLE_MAIN_NODE);
        $this->_tblLang = Database::get_main_table(TABLE_MAIN_LANGUAGE);
        $by = api_get_user_id();
            $this->fieldValues['created_by']= $by;
            $this->fieldValues['modified_by']= $by;
            $this->fieldValues['creation_date']= api_get_datetime();
            $this->fieldValues['modification_date']=api_get_datetime();
    }
    public function getPages() {
        $access_url_id=(api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();
               return $this->_ado->GetAll("SELECT
                                            id ,
                                            title ,
                                            content ,
                                            access_url_id ,
                                            active ,
                                            language_id,
                                            enabled ,
                                            node_type 
                                        FROM {$this->_tblNode}
                                        WHERE node_type =".NODE_NOTICE);
    }
    
    public function getPageInfo($nodeId) {
        $access_url_id=(api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();
        
        return $this->_ado->GetRow("SELECT
                                            id ,
                                            title ,
                                            content ,
                                            access_url_id ,
                                            active ,
                                            language_id ,
                                            enabled ,
                                            node_type 
                                        FROM {$this->_tblNode} 
                                        WHERE active =".NODE_ACTIVE." AND node_type =" .NODE_NOTICE. " 
                                                AND access_url_id=".$access_url_id." AND id = ?", $nodeId);
    }
    public function save() {        
         
        if (!empty($this->_nodeId)) {
            // update the item
            $where = 'node_id ='.intval($this->_nodeId);
            $updateSQL = $this->_ado->AutoExecute($this->_tblNode, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_nodeId;
        }
        else {
            // create the item    
            $insertSQL = $this->_ado->AutoExecute($this->_tblNode, $this->fieldValues, 'INSERT');
            $lastId = $this->_ado->Insert_ID();
        }
        return $lastId;
    }
    public function delete($force = false) {
        if ($force) {
            //delete command
        }
        else {
            //update active field
            $this->_ado->Execute("UPDATE {$this->_tblNode} SET active = 0 WHERE id = ?", array($this->_nodeId));
        }
    }
    
    public function setNodeId($nodeId) {
        $this->_nodeId = $nodeId;
    }
   
    
    
}
