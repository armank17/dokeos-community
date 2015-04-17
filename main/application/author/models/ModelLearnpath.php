<?php

/**
 * Learnpath model
 * @package application.author.models.ModelLearnpath
 */
class application_author_models_ModelLearnpath 
{
    private $_ado;
    private $_tblLP;
    private $_tblLpItem;
    private $_tblLpItemView;
    private $_tblLpView;
    private $_tblEcommercUserPrivileges;
    private $_tblEcommerItems;
    private $_tblEcommerLpModules;
    private $_tblSearchEngine;
    
    private $_sessionId;
    private $_userId;
    private $_courseCode;
    private $_courseInfo;
    
    private $_lpId;
    public  $fieldValues = array();
    
    public function __construct($courseCode = null, $sessionId = null, $userId = null) {
        if (!isset($courseCode)) {
            $courseCode = api_get_course_id();
        }
        if (!isset($sessionId)) {
            $sessionId = api_get_session_id();
        }
        if (!isset($userId)) {
            $userId = api_get_user_id();
        }
        $this->_courseCode = $courseCode;
        $this->_sessionId = $sessionId;
        $this->_userId = $userId;     
        $this->_courseInfo = api_get_course_info($courseCode);
        $this->_ado = appcore_db_DB::conn();
        
        // Definition of tables
        $this->_tblLP = Database::get_course_table(TABLE_LP_MAIN, $this->_courseInfo['dbName']);
        $this->_tblLpItem = Database::get_course_table(TABLE_LP_ITEM, $this->_courseInfo['dbName']);
        $this->_tblLpItemView = Database::get_course_table(TABLE_LP_ITEM_VIEW, $this->_courseInfo['dbName']);
        $this->_tblLpView = Database::get_course_table(TABLE_LP_VIEW, $this->_courseInfo['dbName']);
        
        $this->_tblEcommercUserPrivileges = Database::get_main_table(TABLE_MAIN_ECOMMERCE_USER_PRIVILEGES);
        $this->_tblEcommerItems = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $this->_tblEcommerLpModules = Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS);
        
        $this->_tblSearchEngine = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
    }
    
    public function getModuleInfo($lpId) {
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblLP} WHERE id = $lpId");
    }
    
    public function getAllModules() {     
        $filter = api_get_session_condition($this->_sessionId, false, true);
        if (api_is_user_ecommerce($this->_userId)) {
             $modules = $this->getEcommerceModules($this->_userId, $this->_courseCode);
             if (!empty($modules)) {
                $filter .= " AND id IN (".  implode(',', $modules).")";
             }
        }    
        return $this->_ado->GetAll("SELECT * FROM {$this->_tblLP} $filter ORDER BY display_order, name");
    }
    
    public function getLpItems($lpId) {
        return $this->_ado->GetAll("SELECT * FROM {$this->_tblLpItem} WHERE lp_id = ? ORDER BY display_order, id", array($lpId));
    }
    
    public function getLastItem($lpId) {  
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblLpItem} WHERE lp_id = ? ORDER BY display_order DESC LIMIT 1", array($lpId));
    }
    
    public function getFirstItem($lpId) {
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblLpItem} WHERE lp_id = ? ORDER BY display_order, id LIMIT 1", array($lpId));
    }
    
    public function getFirstItemByStatus($lpId,$userId,$status) {
		
		$sql = "SELECT LPI.* FROM {$this->_tblLP} LP INNER JOIN  {$this->_tblLpView} LPV ON (LPV.LP_ID = LP.ID)  INNER JOIN {$this->_tblLpItemView} LPIV ON (LPIV.LP_VIEW_ID = LPV.ID) INNER JOIN {$this->_tblLpItem} LPI ON (LPI.ID = LPIV.LP_ITEM_ID)  WHERE LP.ID = $lpId AND  LPV.USER_ID= $userId AND LPIV.STATUS != '$status' ORDER BY LPI.DISPLAY_ORDER LIMIT 1";

		return $this->_ado->GetRow($sql);
	}
    
    
    public function getEcommerceModules(){       
       $sql = " SELECT lp_module_lp_module_id FROM {$this->_tblEcommercUserPrivileges} ep
                 INNER JOIN {$this->_tblEcommerItems} ei ON ep.ecommerce_items_id = ei.id 
                 INNER JOIN {$this->_tblEcommerLpModules} em ON ep.ecommerce_items_id = em.ecommerce_items_id
                WHERE user_id = {$this->_userId} AND item_type = 3 AND em.lp_module_course_code = '{$this->_courseCode}'";
       return $this->_ado->GetAll($sql);
    }
    
    public function updateModulePosition($lpId, $newPosition) {
        $lpId = intval($lpId);
        $newPosition = intval($newPosition);
        $sql = "SELECT display_order FROM {$this->_tblLP} WHERE id = $lpId";
        $displayOrder = $this->_ado->GetOne($sql);
        if ($newPosition > $displayOrder) {
            $sql = "UPDATE {$this->_tblLP} SET display_order = display_order - 1 WHERE display_order > $displayOrder AND display_order <= $newPosition";
            $this->_ado->Execute($sql);
        } else {
            $sql = "UPDATE {$this->_tblLP} SET display_order = display_order + 1 WHERE display_order < $displayOrder AND display_order >= $newPosition";
            $this->_ado->Execute($sql);
        }
        $sql = "UPDATE {$this->_tblLP} SET display_order = $newPosition WHERE id = $lpId";
        $this->_ado->Execute($sql);
        return $this->_ado->Affected_Rows();
    }
    
    public function save() {        
        if (empty($this->fieldValues)) {
            return false;
        }        
        if (!empty($this->_lpId)) {
            $where = 'id='.intval($this->_lpId);
            $insertSQL = $this->_ado->AutoExecute($this->_tblLP, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_lpId;
        }
        else {
			$this->fieldValues['lp_type'] = intval(1);     
            $insertSQL = $this->_ado->AutoExecute($this->_tblLP, $this->fieldValues, 'INSERT');
            $lastId = $this->_ado->Insert_ID();
        }
        return $lastId;
    }
    
    public function delete() {        
        
        // Delete from lp_item_view and lp_item tables
        $items = $this->_ado->GetAll("SELECT id FROM {$this->_tblLpItem} WHERE lp_id = {$this->_lpId}");
        if (count($items)) {
            foreach ($items as $item) {
                $this->_ado->Execute("DELETE FROM {$this->_tblLpItemView} WHERE lp_item_id = {$item['id']}");
            }
            $this->_ado->Execute("DELETE FROM {$this->_tblLpItem} WHERE lp_id = {$this->_lpId}");
        }
        
        // Delete from lp_view table
        $this->_ado->Execute("DELETE FROM {$this->_tblLpView} WHERE lp_id = {$this->_lpId}");
        
        // Remove scorm folder
        $module = $this->getModuleInfo($this->_lpId);
        if ($module['id'] == 2 OR $module['id'] == 3) {
            //this is a scorm learning path, delete the files as well            
            if (!empty($module['path'])) {
                $checkPath = $this->_ado->GetOne("SELECT id FROM {$this->_tblLP} WHERE path = '{$module['path']}' AND id != {$this->_lpId} ");
                if (empty($checkPath)) {
                    //no other LP uses that directory, delete it
                    $scormDir = api_get_path(SYS_COURSE_PATH).api_get_course_path().'/scorm/'; //absolute system path for this course
                    if (is_dir($scormDir.$module['path']) && !empty($scormDir)) {                        
                        // Proposed by Christophe (clefevre).
                        if (strcmp(substr($module['path'], -2), "/.") == 0) {
                            $module['path'] = substr($module['path'], 0, -1); // Remove "." at the end
                        }
                        rmdirr($scormDir.$module['path']);
                    }
                }
            }           
        }
        
        // Delete from lp table
        $this->_ado->Execute("DELETE FROM {$this->_tblLP} WHERE id = {$this->_lpId}");
        $this->updateDisplayOrder(); //updates the display order of all lps
        api_item_property_update($this->_courseInfo, TOOL_LEARNPATH, $this->_lpId, 'delete', $this->_userId);
        
    }
    
    public function updateDisplayOrder() {        
        $modules = $this->getAllModules();        
        if (count($modules) > 0) {
            $i = 1;
            foreach ($modules as $module) {
                if ($module['display_order'] != $i) {
                    $this->_ado->Execute("UPDATE {$this->_tblLP} SET display_order = $i WHERE id = {$module['id']}");
                }                                
                $i++;
            }
        }        
    }
    
    public function deleteEngine() {
        $searchPath = api_get_path(SYS_PATH).'searchdb';
        if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian') && is_writable($searchPath)) {
            // actually, it consists on delete terms from db, insert new ones, create a new search engine document, and remove the old one            
            $row = $this->_ado->GetRow("SELECT * FROM {$this->_tblSearchEngine} WHERE course_code = '{$this->_courseCode}' AND tool_id = '".TOOL_LEARNPATH."' AND ref_id_high_level = '{$this->_lpId}' LIMIT 1");
            if (!empty($row)) {
                $di = new DokeosIndexer();
                $lang = $this->getRequest()->getProperty('language', 'english');
                $di->connectDb(NULL, NULL, $lang);
                $di->remove_document($row['search_did']);
                $this->_ado->Execute("DELETE FROM {$this->_tblSearchEngine} WHERE course_code='{$this->_courseCode}' AND tool_id='".TOOL_LEARNPATH."' AND ref_id_high_level = '{$this->_lpId}'");
                return true;                
            }
        }
        return false;
    }
    
    public function setVisibility() {
        $isVisible = api_get_item_visibility($this->_courseInfo, TOOL_LEARNPATH, $this->_lpId);
        $action = intval($isVisible) == 1 ? 'invisible' : 'visible';
        return api_item_property_update($this->_courseInfo, TOOL_LEARNPATH, $this->_lpId, $action, $this->_userId);
    }
    
    public function setLpId($lpId) {
        $this->_lpId = intval($lpId);
    }
    
}
