<?php
class application_reporting_models_ModulesReportingModel
{

    private $_ado; 
    
    private $_tblLp;
    private $_tblLpView;
    private $_tblLpItemView;
    private $_tblLpItem;
        
    private $_isPlatformAdmin;
    private $_isCourseManager;
    private $_currentUser;
    
    private $_coursesReportingModel;
    
    public function __construct() {
        $this->_ado = appcore_db_DB::conn();  
        
        $this->setMainTables();
        $this->setStatsTables();
        
        $this->_isPlatformAdmin = api_is_platform_admin();
        $this->_isCourseManager = api_is_allowed_to_create_course();
        $this->_currentUser = api_get_user_id();
        
        $this->_coursesReportingModel = new application_reporting_models_CoursesReportingModel();
    }

    public function getModuleInfo($courseCode, $lpId) {
        $this->setCourseTables($courseCode);
        $sql = "SELECT id, name FROM {$this->_tblLp} WHERE id = ?";
        return $this->_ado->GetRow($sql, array($lpId));
    }
    
    public function getModuleTotalTime($courseCode, $lpId, $sessionId = null, $learners = array()) {
        if (empty($learners)) { return false; }
        $this->setCourseTables($courseCode);
        $inputArray = array();
        $sumTotalTime = $averageTime = 0;
        // First, We get the lp views
        $sql = "SELECT id FROM {$this->_tblLpView} WHERE lp_id = ?";
        array_push($inputArray, $lpId);
        if (isset($sessionId)) {            
            if (is_array($sessionId) && count($sessionId) > 0) {
                $sql .= " AND session_id IN(".implode(',', $sessionId).")";
            }
            else {
                $sql .= " AND session_id = ?";
                array_push($inputArray, $sessionId);
            }           
        }
        if (!empty($learners)) {
            $sql .= " AND user_id IN(".implode(',', array_keys($learners)).")";
        }       
        $lpViews = $this->_ado->GetAll($sql, $inputArray);
               
        // Now We get the sum of total time from views
        if (!empty($lpViews)) {
            foreach ($lpViews as $lpView) {
                $sql = "SELECT SUM(total_time) FROM {$this->_tblLpItemView} WHERE lp_view_id = ?";
                $sumTotalTime += $this->_ado->getOne($sql, array($lpView['id']));
            }
            $averageTime = round($sumTotalTime / count($lpViews));
        }       
        /*if ($learners > 0) {
            $averageTime = round($sumTotalTime / count($learners));
        }*/       
        return $averageTime;
    }
  
    public function getModulesTotalTime($courseCode, $sessionId = null, $learners = array()) {        
        $this->setCourseTables($courseCode);        
        $modules = $this->getModules($courseCode, $sessionId);
        $sumTime = 0;
        $result = false;
        if (!empty($modules)) {
            foreach ($modules as $module) {                
                $moduleTime = $this->getModuleTotalTime($courseCode, $module['id'], $sessionId, $learners);
                $sumTime += intval($moduleTime);               
            }
            $result = round($sumTime / count($modules));
        }        
        return $result;
    }
     
    public function getModuleProgress($courseCode, $lpId, $sessionId = null, $learners = array()) {
        if (empty($learners)) { return false; }
        $this->setCourseTables($courseCode);
        $inputArray = array();
        // First, We get the lp views
        $sql = "SELECT id, user_id FROM {$this->_tblLpView} WHERE lp_id = ?";            
        array_push($inputArray, $lpId);        
        if (isset($sessionId)) {
            if (is_array($sessionId) && count($sessionId) > 0) {
                $sql .= " AND session_id IN(".implode(',', $sessionId).")";
            }
            else {
                $sql .= " AND session_id = ?";
                array_push($inputArray, $sessionId);
            }                       
        }        
        if (!empty($learners)) {
            $sql .= " AND user_id IN(".implode(',', array_keys($learners)).")";
        }
        $lpViews = $this->_ado->GetAll($sql, $inputArray);
        $totalItems = count($this->getModuleItems($courseCode, $lpId));
        $sumProgress = 0;
        $result = false;
        // Now We get the sum of total time from views
        if (!empty($lpViews)) {
            foreach ($lpViews as $lpView) {
                $nbCompleted = $this->_ado->GetOne("SELECT count(id) as nb_completed FROM {$this->_tblLpItemView} WHERE lp_view_id = ? AND status = 'completed'", array($lpView['id']));                
                if ($totalItems > 0) {
                    $progress = round(($nbCompleted * 100) / $totalItems);
                    if ($progress > 100) { 
                        $progress = 100;                    
                    }
                    $sumProgress += $progress;
                }                
            }
            $result = round($sumProgress / count($lpViews));
            //$result = round($sumProgress / count($learners));
        }        
        return $result;
    }
    
    public function getModulesProgress($courseCode, $sessionId = null, $learners = array()) {
        $this->setCourseTables($courseCode);        
        $modules = $this->getModules($courseCode, $sessionId);
        $sumProgress = 0;
        $result = false;
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $moduleProgress = $this->getModuleProgress($courseCode, $module['id'], $sessionId, $learners);
                $sumProgress += intval($moduleProgress);
            }
            $result = round($sumProgress / count($modules));
        }        
        return $result;
    }
    
    public function getModuleScore($courseCode, $lpId, $sessionId = null, $learners = array()) {
        if (empty($learners)) { return false; }
        $this->setCourseTables($courseCode);
        $inputArray = array();
        // First, We get the lp views
        $sql = "SELECT id, user_id FROM {$this->_tblLpView} WHERE lp_id = ?";            
        array_push($inputArray, $lpId);        
        if (isset($sessionId)) {            
            if (is_array($sessionId) && count($sessionId) > 0) {
                $sql .= " AND session_id IN(".implode(',', $sessionId).")";
            }
            else {
                $sql .= " AND session_id = ?";            
                array_push($inputArray, $sessionId);
            }           
        }        
        if (!empty($learners)) {
            $sql .= " AND user_id IN(".implode(',', array_keys($learners)).")";
        }
        $lpViews = $this->_ado->GetAll($sql, $inputArray);
        $score = 0;
        $countQuizzes = $iScore = $iMaxScore = 0;
        $result = false;
        // Now We get the sum of total time from views
        if (!empty($lpViews)) {
            foreach ($lpViews as $lpView) {                
                $sql = "SELECT iv.score, iv.max_score, i.item_type, iv.lp_item_id, i.path FROM {$this->_tblLpItemView} iv INNER JOIN {$this->_tblLpItem} i ON iv.lp_item_id = i.id WHERE iv.lp_view_id = ? AND i.item_type IN('quiz', 'sco')";                
                $rows = $this->_ado->GetAll($sql, array($lpView['id']));
                if (!empty($rows)) {
                    foreach ($rows as $row) {                              
                        if ($row['item_type'] == 'sco') {
                            $ivScore = $row['score'];
                            $ivMaxScore = $row['max_score'];
                        }                
                        if ($row['item_type'] == 'quiz') {
                            $quizTrack = $this->getUserModuleQuizItemBestAttempt($courseCode, $lpView['user_id'], $lpId, $row['lp_item_id'], $row['path'], $sessionId);                                                                                    
                            $ivScore = $quizTrack['exe_result'];
                            $ivMaxScore = $quizTrack['exe_weighting'];
                        } 
                        if ($ivMaxScore > 0) {                            
                            $iScore += $ivScore;
                            $iMaxScore += $ivMaxScore;
                            $countQuizzes++;
                        }
                    }
                }
            }            
            $score = $iMaxScore > 0 ? ($iScore * 100) / $iMaxScore : 0;
            $result = $countQuizzes > 0?round($score / count($lpViews)):false;
        }
        //$result = $countQuizzes > 0?round($score / count($learners)):false;
        return $result;
    }
    
    public function getModulesScore($courseCode, $sessionId = null, $learners = array()) {
        $this->setCourseTables($courseCode);        
        $modules = $this->getModules($courseCode, $sessionId);
        $sumScore = 0;
        $result = false;
        if (!empty($modules)) {
            foreach ($modules as $module) {                
                $moduleScore = $this->getModuleScore($courseCode, $module['id'], $sessionId, $learners);
                $sumScore += intval($moduleScore);
            }            
        }        
        $countLp  = $this->getNbModulesWithQuiz($courseCode, $sessionId);
        if ($countLp > 0) {
            $result = round($sumScore / $countLp);
        }        
        return $result;
    }
    
    public function getNbModules($courseCode, $sessionId = 0) {
        $this->setCourseTables($courseCode);
        $sessionFilter = (is_array($sessionId) && count($sessionId) > 0)?" WHERE session_id IN (".implode(',', $sessionId).")":" WHERE session_id IN (0, ".intval($sessionId).")";
        $sql = "SELECT count(distinct(id)) FROM {$this->_tblLp} $sessionFilter";
        return $this->_ado->GetOne($sql);
    }
    
    public function getNbModulesWithQuiz($courseCode, $sessionId = 0) {
        $this->setCourseTables($courseCode);
        $sessionFilter = (is_array($sessionId) && count($sessionId) > 0)?" AND lp.session_id IN (".implode(',', $sessionId).")":" AND lp.session_id IN (0, ".intval($sessionId).")";
        $sql = "SELECT count(distinct(lpi.lp_id)) FROM {$this->_tblLpItem} lpi INNER JOIN {$this->_tblLp} lp ON lpi.lp_id = lp.id WHERE lpi.item_type IN('quiz', 'sco') $sessionFilter";
        return $this->_ado->GetOne($sql);
    }
    
    public function getModules($courseCode, $sessionId = 0, $txtSearch = '') {
        $this->setCourseTables($courseCode);
        $courseInfo = api_get_course_info($courseCode);        
        if (is_array($sessionId) && count($sessionId) > 0) {
            $sql = "SELECT id, name FROM {$this->_tblLp} WHERE session_id IN (".implode(',', $sessionId).") AND name LIKE '$txtSearch%' ORDER BY name";
        }
        else {
            $sql = "SELECT id, name FROM {$this->_tblLp} WHERE session_id IN (0, ?) AND name LIKE '$txtSearch%' ORDER BY name";
        }                        
        $allModules = array();
        $rows = $this->_ado->GetAll($sql, array($sessionId));
        foreach ($rows as $module) {            
            $module['course_code'] = $courseCode;
            $module['course_title'] = $courseInfo['name'];
            $allModules[$module['id']] = $module;
        }
        return $allModules;
    }
    
    public function getAllModules($courses = null, $sessionId = null, $txtSearch = '') {
        if (!isset($courses)) {
            $this->_coursesReportingModel->setSessionId($sessionId);
            $courses = $this->_coursesReportingModel->getCourses();
        }        
        $allModules = array();
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $modules = $this->getModules($course['code'], 0, $txtSearch);
                if (!empty($modules)) {
                    foreach ($modules as $module) {
                        $module['course_code'] = $course['code'];
                        $module['course_title'] = $course['title'];
                        $allModules[] = $module;
                    }
                }
            }
        }
        return $allModules;
    }
    
    public function getUserModulesTotalTimeScoreAndProgress($userId, $courseCode = null, $sessionId = null) {
        
        $this->_coursesReportingModel->setUserId($userId, true);
        if (isset($sessionId)) {
            $this->_coursesReportingModel->setSessionId($sessionId); 
        }             
        
        $userInfo = api_get_user_info($userId);
        $result = array('total_time' => 0, 'avg_progress' => 0, 'avg_score' => false);
        $totalTime = $score = $progress = 0;
        $countCourses = $countModules = $countScore = 0;
        if (!isset($courseCode)) {
            $courses = $this->_coursesReportingModel->getCourses();
            if (!empty($courses)) {
                foreach ($courses as $course) {
                    $modules = $this->getModules($course['code'], $sessionId);
                    if (!empty($modules)) {
                        foreach ($modules as $module) {
                            $lpId = $module['id'];                           
                            $totalTime += $this->getModuleTotalTime($course['code'], $lpId, $sessionId, array($userId => $userInfo));                            
                            $progress  += $this->getModuleProgress($course['code'], $lpId, $sessionId, array($userId => $userInfo));                            
                            $moduleScore = $this->getModuleScore($course['code'], $lpId, $sessionId, array($userId => $userInfo));
                            if ($moduleScore !== false) {
                                $score += $moduleScore;
                                $countScore++;
                            }                           
                            $countModules++;
                        }
                    }
                    $countCourses++;
                }                
            }
        }
        else {
            $countCourses = 1;
            $countModules = 0;
            $modules = $this->getModules($courseCode, $sessionId);
            if (!empty($modules)) {                        
                foreach ($modules as $module) {
                    $lpId = $module['id'];
                    $totalTime += $this->getModuleTotalTime($courseCode, $lpId, $sessionId, array($userId => $userInfo));
                    $progress += $this->getModuleProgress($courseCode, $lpId, $sessionId, array($userId => $userInfo));                    
                    $moduleScore = $this->getModuleScore($courseCode, $lpId, $sessionId, array($userId => $userInfo));
                    if ($moduleScore !== false) {
                        $score += $moduleScore;
                        $countScore++;
                    } 
                    $countModules++;
                }
            }            
        }
        if ($countModules > 0) {
            $result['total_time'] = $totalTime;          
            $result['avg_progress'] = round(($progress / $countModules) / $countCourses);            
            if ($countScore > 0) {
                $result['avg_score'] = round(($score / $countModules) / $countCourses);
            }
        }
        return $result;
    }
    
    
    public function getModuleItems($courseCode, $lpId) {
        $this->setCourseTables($courseCode);
        $sql = "SELECT id, lp_id, item_type, title, content, path FROM {$this->_tblLpItem} WHERE lp_id = ? ORDER BY display_order";
        return $this->_ado->GetAll($sql, array($lpId));
    }
    
    public function getModuleItemViews($courseCode, $lpItemId, $lpViews, $sessionId = null) {
        $this->setCourseTables($courseCode);
        $result = array();
        if (!empty($lpViews)) {
            foreach ($lpViews as $view) {
                $sql = "SELECT iv.score, iv.max_score, iv.status, iv.total_time, iv.lp_item_id, iv.lp_view_id, i.item_type, i.path, i.lp_id, v.user_id, v.session_id FROM {$this->_tblLpItemView} iv INNER JOIN {$this->_tblLpItem} i ON iv.lp_item_id = i.id INNER JOIN {$this->_tblLpView} v ON v.id = iv.lp_view_id WHERE iv.lp_view_id = ? AND iv.lp_item_id = ?";                
                $row = $this->_ado->GetRow($sql, array($view['id'], $lpItemId));        
                $result[$view['id']]['status'] = $row['status'];
                $result[$view['id']]['time'] = $row['total_time'];
                $result[$view['id']]['lp_id'] = $row['lp_id'];
                $result[$view['id']]['path'] = $row['path'];
                $result[$view['id']]['item_type'] = $row['item_type'];
                $result[$view['id']]['session_id'] = $row['session_id'];
                $result[$view['id']]['lp_item_id'] = $row['lp_item_id'];
                $result[$view['id']]['lp_view_id'] = $row['lp_view_id'];                                
                if (!empty($row['session_id'])) {
                    $sessionInfo = api_get_session_info($row['session_id']);
                    $result[$view['id']]['session_name'] = $sessionInfo['name'];    
                }
                if ($row['item_type'] == 'sco') {
                    $result[$view['id']]['score'] = $row['score'];
                    $result[$view['id']]['max_score'] = $row['max_score'];
                }                
                if ($row['item_type'] == 'quiz') {
                    $quizTrack = $this->getUserModuleQuizItemBestAttempt($courseCode, $row['user_id'], $row['lp_id'], $lpItemId, $row['path'], $sessionId);
                    $result[$view['id']]['score'] = $quizTrack['exe_result'];
                    $result[$view['id']]['max_score'] = $quizTrack['exe_weighting'];
                    $result[$view['id']]['exe_id'] = $quizTrack['exe_id'];
                }                
            }
        }
        return $result;
    }
    
    public function getUserModuleQuizItemBestAttempt($courseCode, $userId, $lpId, $lpItemId, $quizId, $sessionId = null) {
        $inputArray = array();  
        $sql = "SELECT (exe_result / exe_weighting) as avg_score, exe_id, exe_result, exe_weighting FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_user_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id = 0 AND orig_lp_id = ? AND orig_lp_item_id = ? AND exe_exo_id = ?";
        array_push($inputArray, $courseCode, $userId, $lpId, $lpItemId, $quizId);        
        if (isset($sessionId)) {
            $sql .= " AND session_id = ?";            
            array_push($inputArray, $sessionId);
        }
        $sql .= " ORDER BY avg_score DESC, exe_id DESC LIMIT 1";       
        return $this->_ado->GetRow($sql, $inputArray);
    }
    
    public function getModuleUserViews($courseCode, $lpId, $userId, $sessionId = null) {
        $this->setCourseTables($courseCode);
        $inputArray = array();
        $sql = "SELECT id FROM {$this->_tblLpView} WHERE lp_id = ? AND user_id = ?";            
        array_push($inputArray, $lpId, $userId);        
        if (isset($sessionId)) {
            $sql .= " AND session_id = ?";            
            array_push($inputArray, $sessionId);
        }
        $sql .= " ORDER BY id ASC";
        $views = $this->_ado->GetAll($sql, $inputArray);        
        return $views; 
    }
    
    public function updateModuleQuizItemView($courseCode, $lpViewId, $attemptId) {     
        $this->setCourseTables($courseCode);
        // We get data from quiz attempt id and update the module
        $row = $this->_ado->GetRow("SELECT exe_result, exe_weighting, start_date, exe_date, orig_lp_item_id FROM {$this->_tblTrackExercise} WHERE exe_id = ?", array($attemptId));
        if (!empty($row['orig_lp_item_id'])) {            
            $startTime = convert_mysql_date($row['start_date']);
            $endTime 	 = convert_mysql_date($row['exe_date']);
            $totalTime = ((int)$endTime - (int)$startTime);   
            $sql = "UPDATE {$this->_tblLpItemView} SET score = ?, max_score = ?, start_time = ?, total_time = ?, status = 'completed' WHERE lp_view_id = ? AND lp_item_id = ?";
            $this->_ado->Execute($sql, array($row['exe_result'], $row['exe_weighting'], $startTime, $totalTime, $lpViewId, $row['orig_lp_item_id']));
        }        
    }
    
    
    public function setCourseTables($courseCode) {
        $courseInfo = api_get_course_info($courseCode);       
        $this->_tblLp = Database :: get_course_table(TABLE_LP_MAIN, $courseInfo['dbName']);
	$this->_tblLpView = Database :: get_course_table(TABLE_LP_VIEW, $courseInfo['dbName']);
	$this->_tblLpItemView = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $courseInfo['dbName']);
        $this->_tblLpItem = Database :: get_course_table(TABLE_LP_ITEM, $courseInfo['dbName']);
    }
    
    public function setMainTables() {}
    
    public function setStatsTables() {
        $this->_tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
        $this->_tblTrackAttempt  = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
    }    
}