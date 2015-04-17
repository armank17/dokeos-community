<?php

class application_node_models_NewsModel 
{
    private $_ado;
    private $_sessionId;
    private $_nodeId;
    private $_tblNodeNews;
    private $_tblNode;
    private $_tblLang;
    
    public  $fieldValues = array();
   
    
    public function __construct($sessionId = null) {
        $this->_sessionId = $sessionId;    
        $this->_ado = appcore_db_DB::conn();
        $this->_tblNodeNews = Database::get_main_table(TABLE_MAIN_NODE_NEWS);
        $this->_tblNode = Database::get_main_table(TABLE_MAIN_NODE);
        $this->_tblLang = Database::get_main_table(TABLE_MAIN_LANGUAGE);
    }
    public function getPages() {
        $access_url_id=(api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();
        $now = date('Y:m:d H:i:s');
        return $this->_ado->GetAll("SELECT  news.node_id,
                                            news.start_date,
                                            news.end_date,
                                            news.visible_by_trainer,
                                            news.visible_by_learner,
                                            news.visible_by_guest,
                                            node.title,
                                            node.content,
                                            node.language_id,
                                            COALESCE(language.dokeos_folder, 'All') AS lang_name,
                                            node.enabled,
                                            IF(news.start_date <= '$now' AND '$now' <= news.end_date, 1, 0) AS visible
                                    FROM {$this->_tblNodeNews} news 
                                        INNER JOIN {$this->_tblNode} node
                                        ON (news.node_id = node.id)
                                        LEFT JOIN language ON (language.id = node.language_id)
                                        WHERE node.access_url_id = $access_url_id
                                        AND node.active = 1");
    }
    
    public function getPageInfo($nodeId) {
        $access_url_id=(api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();
        
        return $this->_ado->GetRow("SELECT  news.node_id,
                                            node.enabled, 
                                            news.start_date,
                                            news.end_date,
                                            news.visible_by_trainer,
                                            news.visible_by_learner,
                                            news.visible_by_guest,
                                            node.title,
                                            node.content,
                                            node.language_id,
                                            COALESCE(language.dokeos_folder, 'All') AS lang_name 
                                    FROM {$this->_tblNodeNews} news  
                                    INNER JOIN {$this->_tblNode} node 
                                    ON (news.node_id = node.id) 
                                    LEFT JOIN language ON (language.id = node.language_id) 
                                    WHERE node.access_url_id=".$access_url_id."  
                                        AND node.active=1 
                                        AND news.node_id = ?", $nodeId);
    }
   
    public function save() {        
         
        if (!empty($this->_nodeId)) {
            // update the item
            $where = 'node_id ='.intval($this->_nodeId);
            $updateSQL = $this->_ado->AutoExecute($this->_tblNodeNews, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_nodeId;
        }
        else {
            // create the item                       
            
            $insertSQL = $this->_ado->AutoExecute($this->_tblNodeNews, $this->fieldValues, 'INSERT');
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
            //$this->_ado->Execute("UPDATE {$this->_tblNode} SET active = 0 WHERE id = ?", array($this->_nodeId));
            $this->_ado->Execute("DELETE FROM {$this->_tblNode} WHERE id = ?", array($this->_nodeId));
            $this->_ado->Execute("DELETE FROM {$this->_tblNodeNews} WHERE node_id = ?", array($this->_nodeId));
        }
    }
    
    public function setNodeId($nodeId) {
        $this->_nodeId = $nodeId;
    }
   
    public function updateVisibility($nodeId, $visible_by, $value){         
        switch ($visible_by){
            case 1:
                $visible_by = 'visible_by_guest';
                break;
            case 2:
                $visible_by = 'visible_by_learner';
                break;
            case 3:
                $visible_by = 'visible_by_trainer';
                break;
        }
        $this->setNodeId($nodeId);
        $this->_ado->Execute("UPDATE {$this->_tblNodeNews} SET $visible_by = ? WHERE node_id = ?", array($value, $this->_nodeId));
    }
    
    public function updateEnabled($nodeId, $value) {
        $this->setNodeId($nodeId);
        $this->_ado->Execute("UPDATE {$this->_tblNode} SET enabled = ? WHERE id = ?", array($value, $this->_nodeId));
    }
    
}
