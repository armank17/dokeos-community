<?php

class application_node_models_PageHomeModel
{
    private $_ado;
    private $_sessionId;
    private $_nodeId;
    private $_tblNode;
    private $_tblLang;
    private $_tblNodeHomePage;
    private $_tblPageHomeTemplate;
    private $_accessUrlId;
    public  $fieldValues = array();
    
    
    public function __construct($sessionId = null) {
        $this->_sessionId = $sessionId;    
        $this->_ado = appcore_db_DB::conn();
        $this->_tblNode = Database::get_main_table(TABLE_MAIN_NODE);
        $this->_tblNodeHomePage = Database::get_main_table(TABLE_MAIN_NODE_HOMEPAGE);
        $this->_tblLang = Database::get_main_table(TABLE_MAIN_LANGUAGE);
        $this->_tblPageHomeTemplate = Database::get_main_table(TABLE_MAIN_SYSTEM_TEMPLATE);
        
        $this->_accessUrlId = (api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();        
        $by= api_get_user_id();
        $this->fieldValues['created_by']= $by;
        $this->fieldValues['modified_by']= $by;
        $this->fieldValues['creation_date']= api_get_datetime();
        $this->fieldValues['modification_date']=api_get_datetime();
    }
    
    public function getPages() {
         $api_get_current_access_url_id=( api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();
        
        return $this->_ado->GetAll("SELECT * FROM {$this->_tblNode} n 
                                    INNER JOIN {$this->_tblNodeHomePage} h 
                                    ON (n.id = h.node_id) 
                                    WHERE n.active = 1 
                                    AND n.access_url_id =".$api_get_current_access_url_id);
    }
    
    public function getPageInfo($nodeId) {
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblNode} n 
                                    INNER JOIN {$this->_tblNodeHomePage} h 
                                    ON (n.id = h.node_id) 
                                    WHERE n.active = 1 
                                    AND n.access_url_id = {$this->_accessUrlId} AND n.id = ?", array($nodeId));
    }
   
    public function save() {            
        if (!empty($this->_nodeId)) {
            // update the item
            $where = 'node_id ='.intval($this->_nodeId);
          
            $updateSQL = $this->_ado->AutoExecute($this->_tblNodeHomePage, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_nodeId;
        }
        else {
            // create the item      
           
            $insertSQL = $this->_ado->AutoExecute($this->_tblNodeHomePage, $this->fieldValues, 'INSERT');
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
    
    
    public function getPageHomeTemplates(){
        $sql = "SELECT * FROM {$this->_tblPageHomeTemplate} WHERE template_type = '". SYSTEM_TEMPLATE_TYPE_HOME ."';";
        $res = Database::query($sql);
        while($templates[] = Database::fetch_array($res, 'ASSOC')){
        }
        return $templates;
    }
}
