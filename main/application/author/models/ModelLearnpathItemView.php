<?php

class application_author_models_ModelLearnpathItemView
{
   
    private $_ado;
    
    private $_sessionId;
    private $_userId;
    private $_courseCode;
    private $_itemViewId;
    private $_lpViewId;
    private $_lpId;
    private $_tblLpItemView;
    private $_tblLpView; 
    private $_tblScAct;  
    private $_tblScActView;  
    private $_tblLpItem; 
	private $_tblScSteps;
    
    
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
        $this->courseInfo = api_get_course_info($courseCode);
        $this->_ado = appcore_db_DB::conn();

        // Definition of tables
        $this->_tblLpItem = Database::get_course_table(TABLE_LP_ITEM, $this->courseInfo['dbName']);
        $this->_tblLpItemView = Database::get_course_table(TABLE_LP_ITEM_VIEW, $this->courseInfo['dbName']);
        $this->_tblLpView = Database::get_course_table(TABLE_LP_VIEW, $this->courseInfo['dbName']);
		$this->_tblScSteps = Database::get_course_table(TABLE_SCENARIO_STEPS, $this->courseInfo['dbName']);
        $this->_tblScAct = Database::get_course_table(TABLE_SCENARIO_ACTIVITY, $this->courseInfo['dbName']);
        $this->_tblScActView = Database::get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $this->courseInfo['dbName']);
        
    }
    
    public function save($where = '') {
        if (!empty($this->_itemViewId) || !empty($where)) {
            // update the item
            if (empty($where)) {
                $where = 'id='.intval($this->_itemViewId);
            }
            $updateSQL = $this->_ado->AutoExecute($this->_tblLpItemView, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_itemViewId;
        }
        else {
            // create the item'
            $insertSQL = $this->_ado->AutoExecute($this->_tblLpItemView, $this->fieldValues, 'INSERT');
            $lastId = $this->_ado->Insert_ID();
        }
        return $lastId;
    }
    
    public function saveLpView() {
        if (!empty($this->_lpViewId)) {
            // update the item
            $where = 'id='.intval($this->_lpViewId);
            $updateSQL = $this->_ado->AutoExecute($this->_tblLpView, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_lpViewId;
        }
        else {
            // create the item'
            $insertSQL = $this->_ado->AutoExecute($this->_tblLpView, $this->fieldValues, 'INSERT');
            $lastId = $this->_ado->Insert_ID();
        }
        return $lastId;
    }
    
    public function saveLpItemView($itemId, $status = 'not attempted') {
        if (!empty($this->_lpViewId)) {     
            $sql = "INSERT INTO {$this->_tblLpItemView} SET lp_item_id = ?, lp_view_id = ?, view_count = 1, status = ?, start_time = 0";            
            $this->_ado->Execute($sql, array($itemId, $this->_lpViewId, $status));
        }
    }
    
    public function deleteLpItemsView() {
        if (!empty($this->_lpViewId)) {
            $this->_ado->Execute("DELETE FROM {$this->_tblLpItemView} WHERE lp_view_id = ?", array($this->_lpViewId));
        }
    }
    
    public function getLpViewInfo() {
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblLpView} WHERE id = ?", array($this->_lpViewId));
    }
    
    public function getLpItemView($itemId) {
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblLpItemView} WHERE lp_view_id = ? AND lp_item_id = ?", array($this->_lpViewId, $itemId));
    }
    
    public function getLpView() {        
        if (!$this->_userId) { return false; }        
        $viewId = $this->_ado->GetOne("SELECT id FROM {$this->_tblLpView} WHERE user_id = ? AND lp_id = ? AND session_id = ?", array($this->_userId, $this->_lpId, $this->_sessionId));        
        if (!$viewId) {
            $this->fieldValues['lp_id'] = $this->_lpId;
            $this->fieldValues['user_id'] = $this->_userId;
            $this->fieldValues['view_count'] = 1;
            $this->fieldValues['session_id'] = $this->_sessionId;
            $viewId = $this->saveLpView();
        }
        $this->_lpViewId = $viewId;
        return $this->getLpViewInfo();        
    }
    
    public function setLpId($lpId) {
        $this->_lpId = intval($lpId);
    }
    
    public function updateItemViewTotalTime($totalTime, $itemId, $viewId) {        
        $sql = "UPDATE {$this->_tblLpItemView} SET total_time = ? WHERE lp_item_id = ? AND lp_view_id = ?";        
        $this->_ado->Execute($sql, array($totalTime, $itemId, $viewId));        
    }
    
    public function updateScenarioActView() {
        $completed = 'Y';
		$totalItems = 0;
		$incompleteItems = 0;

		$sql = "SELECT step_completion_option, step_completion_percent FROM {$this->_tblScSteps} WHERE id =  ?";
        $row = $this->_ado->GetRow($sql, array($_SESSION['stepId'])); 
		$stepCompletionOption = trim($row['step_completion_option']);
        $stepCompletionPercent = $row['step_completion_percent'];

		if(strpos($stepCompletionOption,'@') !== false){
			list($stepCriteriaOption,$subCriteriaOption) = split("@",$stepCompletionOption);
		}
		else {
			$stepCriteriaOption = $stepCompletionOption;
			$subCriteriaOption = 'Score';
		}

        $lp_viewId = $this->_ado->GetOne("SELECT id FROM {$this->_tblLpView} WHERE lp_id = ? AND user_id = ? AND session_id = ?", array($this->_lpId, $this->_userId, $this->_sessionId));
        $sql = "SELECT DISTINCT(lp_item_id), status FROM {$this->_tblLpItemView} WHERE lp_view_id =  ?";
        $row = $this->_ado->GetAll($sql, array($lp_viewId)); 
        foreach($row as $rowItem){
			$totalItems++;
            $status = $rowItem['status'];
            if ($status == 'not attempted'){
                $completed = 'N';
				$incompleteItems++;
            }
        }
		$itemsCompleted = $totalItems - $incompleteItems;
	    $statusPercent = round(($itemsCompleted / $totalItems)*100);

		$sql_score = "SELECT item.max_score as max_score, itv.score as score FROM {$this->_tblLpItem} item, {$this->_tblLpItemView} itv WHERE itv.lp_item_id = item.id AND itv.lp_view_id = ? AND item.item_type = ? AND item.lp_id =  ?";
		$row_score = $this->_ado->GetAll($sql_score, array($this->_lpViewId, 'quiz', $this->_lpId)); 
		$scoreTotal = 0;
		$noOfQuiz = 0;
		foreach($row_score as $rowScore){
			  $noOfQuiz++;
			  $maxScore = $rowScore['max_score'];
			  $score = $rowScore['score'];
			  if($max_score <> 0) {
				   $score = $rowScore['score'];
			  }
			  else {
				   $score = 0;
			  }
			  $scorePercent = round(($score / $maxScore)*100);
			  $scoreTotal += $scorePercent;
		}
		if($noOfQuiz > 0){
		$finalScorePercent = round(($scoreTotal / $noOfQuiz)*100);
		}
		else {
		$finalScorePercent = $statusPercent;
		}
		
		if($stepCriteriaOption == 'Module' && $subCriteriaOption == 'Progress' && $statusPercent >= $stepCompletionPercent){
			$status = 'completed';
		}
		else if($stepCriteriaOption == 'Module' && $subCriteriaOption == 'Score' && $finalScorePercent >= $stepCompletionPercent){
			$status = 'completed';
		}
		else if(empty($stepCriteriaOption) || $stepCriteriaOption == 'Quiz' || $stepCriteriaOption == 'None'){
			$status = 'completed';
		}
		else {
			$status = 'notcompleted';
		}

        if (!$this->_userId) { return false; }        
        $activityId = $this->_ado->GetOne("SELECT id FROM {$this->_tblScAct} WHERE activity_ref = ? AND activity_type = ? AND step_id = ?", array($this->_lpId, 'module', $_SESSION['stepId'])); 
        $numRows = $this->_ado->GetOne("SELECT count(*) FROM {$this->_tblScActView} WHERE step_id = ? AND activity_id = ? AND user_id = ?", array($_SESSION['stepId'], $activityId, $this->_userId)); 
        if($numRows == 0) {
            $sql = "INSERT INTO {$this->_tblScActView} SET activity_id = ?, step_id = ?, user_id = ?, view_count = ?, score = ?, status = ?, comment = ''";           
            $this->_ado->Execute($sql, array($activityId, $_SESSION['stepId'], $this->_userId, 1, 0, 'notcompleted'));
            if ($completed == 'Y') {               			  
			  
              $sql = "UPDATE {$this->_tblScActView} SET view_count = view_count + 1, score = ?, status = ? WHERE activity_id = ? AND step_id = ? AND user_id = ?";   
              $this->_ado->Execute($sql, array($score,$status,$activityId, $_SESSION['stepId'], $this->_userId));
              unset($_SESSION['tool']);
            }
        }
        else {
            if ($completed == 'N'){
               $sql = "UPDATE {$this->_tblScActView} SET view_count = view_count + 1, status = ? WHERE activity_id = ? AND step_id = ? AND user_id = ?";     
               $this->_ado->Execute($sql, array($status, $activityId, $_SESSION['stepId'], $this->_userId));					
            }
            else {
               $sql = "UPDATE {$this->_tblScActView} SET view_count = view_count + 1, score = ?, status = ? WHERE activity_id = ? AND step_id = ? AND user_id = ?";    
               $this->_ado->Execute($sql, array($score,$status,$activityId, $_SESSION['stepId'], $this->_userId));
               unset($_SESSION['tool']);
           }
        }               
    }
}
?>
