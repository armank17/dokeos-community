<?php

class application_node_models_CourseNodeModel
{
    
    private $_ado;
    private $_sessionId;
    private $_courseCode;
    private $_courseInfo;
    private $_nodeId;
    private $_tblNode;
    private $_tblNode_rel_course;
    public  $fieldValues = array();
    public  $fieldValuesToRel = array();
    
    public function __construct($courseCode = null, $sessionId = null) {
        if (!isset($courseCode)) { $courseCode = api_get_course_id(); }
        if (!isset($sessionId))  { $sessionId = api_get_session_id(); }
        $this->_courseCode = $courseCode;
        $this->_sessionId = $sessionId;    
        $this->_courseInfo = api_get_course_info($courseCode);
        $this->_ado = appcore_db_DB::conn();
        
        $this->_tblNode = Database::get_main_table(TABLE_MAIN_NODE);
        $this->_tblNode_rel_course = Database::get_main_table(TABLE_MAIN_NODE_REL_COURSE);
    }
    
    public function getCourseNodes() {
        $api_get_current_access_url_id = (api_get_current_access_url_id()==-1)? 1 : api_get_current_access_url_id();
        $course_code = Security::remove_XSS($_GET['cidReq']);
        $session_id = api_get_session_id();
        
        return $this->_ado->GetAll("SELECT n.id,n.title,n.content,n.access_url_id,n.active,n.language_id,
                                           n.enabled,n.node_type,c.course_code 
                                    FROM {$this->_tblNode} n 
                                    INNER JOIN {$this->_tblNode_rel_course} c ON (n.id = c.node_id) 
                                    WHERE active = 1
                                          AND c.course_code = '{$course_code}'
                                          AND access_url_id = {$api_get_current_access_url_id}
                                          AND c.session_id IN ({$session_id},0)");
    }
    
    public function getCourseNodeInfo($nodeId) {
        
        return $this->_ado->GetRow("SELECT n.id,n.title,n.content,n.access_url_id,n.active,n.language_id,
                                           n.enabled,n.node_type,c.course_code  
                                    FROM {$this->_tblNode} n 
                                    INNER JOIN {$this->_tblNode_rel_course} c 
                                        ON (n.id = c.node_id) WHERE id = ?", array($nodeId));
    }
   
    public function save() {            
        if (!empty($this->_nodeId)) {
            // update the item
            $where = 'id ='.intval($this->_nodeId);
            $updateSQL = $this->_ado->AutoExecute($this->_tblNode_rel_course, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_nodeId;
        }
        else {
            // create the item'
            $insertSQL = $this->_ado->AutoExecute($this->_tblNode_rel_course, $this->fieldValues, 'INSERT');
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
    
}