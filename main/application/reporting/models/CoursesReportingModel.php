<?php
class application_reporting_models_CoursesReportingModel
{

    const COURSEMANAGER = 1;
    const STUDENT = 5;
    const COACH = 2;
    
    private $_currentUser;
    private $_sessionId;
    
    private $_ado; 
    private $_tblCourse;
    private $_tblCourseUser;
    private $_tblSession;
    private $_tblSessionCourse;
    private $_tblSessionCourseUser;
    private $_tblAccessUrlRelCourse;
    private $_tblTrackEOnline;
    private $_tblTrackExercise;
    private $_tblScenarioSteps;
    private $_tblScenarioActivity;
    private $_tblScenarioActivityView;
    private $_tblLp;
    private $_tblLpItem;
    private $_tblLpItemView;
    private $_tblLpView;
    private $_tblFace2Face;
    
    
    private $_isPlatformAdmin;
    private $_isCourseManager;

    public function __construct() {
        $this->_ado = appcore_db_DB::conn();  
        
        $this->_tblCourse               = Database::get_main_table(TABLE_MAIN_COURSE);
        $this->_tblCourseUser           = Database::get_main_table(TABLE_MAIN_COURSE_USER);
        $this->_tblSession              = Database::get_main_table(TABLE_MAIN_SESSION);
        $this->_tblSessionCourse        = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
        $this->_tblSessionCourseUser    = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
        $this->_tblSessionUser          = Database::get_main_table(TABLE_MAIN_SESSION_USER);
        $this->_tblAccessUrlRelCourse   = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);       
        $this->_tblTrackEOnline         = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ONLINE);
        $this->_tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
        
        $this->_isPlatformAdmin = api_is_platform_admin();
        $this->_isCourseManager = api_is_allowed_to_create_course();
        $this->_currentUser = api_get_user_id();
    }
   
    public function getCourse($courseCode, $txtSearch = '%') {
        $sql = "SELECT code, title FROM {$this->_tblCourse} WHERE code = ? AND title LIKE '$txtSearch%'";       
        return $this->_ado->GetAll($sql, $courseCode);
    }
    
    public function getCourses($txtSearch = '%') {
        $inputArray = array();
        $access_url_id=(api_get_current_access_url_id()<0)?1:api_get_current_access_url_id();  
        $sql = "SELECT code, title FROM {$this->_tblCourse} c";
        if ($this->_isPlatformAdmin) {
            if ($this->_sessionId) {
                $sql .= " INNER JOIN {$this->_tblSessionCourse} scr ON c.code = scr.course_code";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE scr.id_session = ? AND title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";
                $sql .= " ORDER BY scr.position";
                array_push($inputArray, $this->_sessionId);
                
            }
            else {
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";
                $sql .= " ORDER BY title";
            }
        }
        else if ($this->_isCourseManager) {       
            if ($this->_sessionId) {
                $sql  = " SELECT DISTINCT(code), title, sc.position as position FROM {$this->_tblCourse} c";
                $sql .= " INNER JOIN {$this->_tblSessionCourse} sc ON sc.course_code = c.code";
                $sql .= " INNER JOIN {$this->_tblSession} sess ON sess.id = sc.id_session";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE sess.id_coach = ? AND sc.id_session = ? AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id ";
                $sql .= " UNION ";
                $sql .= " SELECT DISTINCT(c.code) as code, c.title, sc.position as position FROM {$this->_tblCourse} c";
                $sql .= " INNER JOIN {$this->_tblSessionCourseUser} scu ON scu.course_code = c.code";
                $sql .= " INNER JOIN {$this->_tblSessionCourse} sc ON sc.course_code = c.code AND sc.id_session = scu.id_session";
                $sql .= " INNER JOIN {$this->_tblSession} sess ON sess.id = sc.id_session";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE (scu.status = ? && scu.id_user = ? && sc.id_session = ?) AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";
                $sql .= " ORDER BY position";
		array_push($inputArray, $this->_currentUser, $this->_sessionId, self::COACH, $this->_currentUser, $this->_sessionId); 
            }
            else {
                $sql .= " INNER JOIN {$this->_tblCourseUser} cr ON c.code = cr.course_code";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE cr.user_id = ? AND (cr.status = 1 OR cr.tutor_id = 1) AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";
                $sql .= " UNION ";
                $sql .= " SELECT DISTINCT(c.code) as code, c.title FROM {$this->_tblCourse} c";
                $sql .= " INNER JOIN {$this->_tblSessionCourse} sc ON sc.course_code = c.code";
                $sql .= " INNER JOIN {$this->_tblSession} sess ON sess.id = sc.id_session";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE sess.id_coach = ? AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";     
		$sql .= " UNION ";
                $sql .= " SELECT DISTINCT(c.code) as code, c.title FROM {$this->_tblCourse} c";
                $sql .= " INNER JOIN {$this->_tblSessionCourseUser} scu ON scu.course_code = c.code";
                $sql .= " INNER JOIN {$this->_tblSessionCourse} sc ON sc.course_code = c.code AND sc.id_session = scu.id_session";
                $sql .= " INNER JOIN {$this->_tblSession} sess ON sess.id = sc.id_session";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE (scu.status = ? && scu.id_user = ?) AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";
                $sql .= " ORDER BY title";
                array_push($inputArray, $this->_currentUser, $this->_currentUser, self::COACH, $this->_currentUser);
            }
        }
        else {           
            // For a student
            if ($this->_sessionId) {
                $sql .= " INNER JOIN {$this->_tblSessionCourseUser} scr ON c.code = scr.course_code";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE scr.id_user = ? AND scr.id_session = ? AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";
                $sql .= " ORDER BY title";
                array_push($inputArray, $this->_currentUser, $this->_sessionId);
            }
            else {
                $sql .= " INNER JOIN {$this->_tblCourseUser} cr ON c.code = cr.course_code";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE cr.user_id = ? AND uc.access_url_id = $access_url_id";
                $sql .= " UNION ";
                $sql .= " SELECT DISTINCT(c.code) as code, c.title FROM {$this->_tblCourse} c";
                $sql .= " INNER JOIN {$this->_tblSessionCourseUser} scr ON c.code = scr.course_code";
                $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
                $sql .= " WHERE scr.id_user = ? AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id";           
                $sql .= " ORDER BY title";
                array_push($inputArray, $this->_currentUser, $this->_currentUser);
            }            
        }
        return $this->_ado->GetAll($sql, $inputArray);
    }    
   
    public function getCoursesInCategorySessions($categoryId, $sessionIds = null, $txtSearch = '%') {
        $courses = array();
        $access_url_id=(api_get_current_access_url_id()<0)?1:api_get_current_access_url_id();
        if (!isset($sessionIds)) {
            $sessionIds = $this->_ado->GetCol("SELECT id FROM {$this->_tblSession} WHERE session_category_id = ?", array($categoryId));       
        }        
        if (!empty($sessionIds)) {                        
            $sql  = " SELECT DISTINCT(code), title FROM {$this->_tblCourse} c";
            $sql .= " INNER JOIN {$this->_tblSessionCourse} sc ON sc.course_code = c.code";
            $sql .= " INNER JOIN {$this->_tblSession} sess ON sess.id = sc.id_session";
            $sql .= " INNER JOIN {$this->_tblAccessUrlRelCourse} uc ON c.code = uc.course_code";
            $sql .= " WHERE sc.id_session IN(".implode(',', $sessionIds).") AND c.title LIKE '$txtSearch%' AND uc.access_url_id = $access_url_id ";
            $sql .= " ORDER BY sc.position";                
            $courses = $this->_ado->GetAll($sql);            
        }        
        return $courses;
    }
    
    public function getUserCourses($userId, $status = 5) {
        $sql = "SELECT code, title FROM {$this->_tblCourse} c INNER JOIN {$this->_tblCourseUser} cr ON c.code = cr.course_code WHERE cr.user_id = ? AND cr.status = ?";
        $rows = $this->_ado->GetAll($sql, array($userId, $status));
        $courses = array();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $courses[$row['code']] = $row;
            }
        }
        return $courses;
    }
    
    public function getLearnerCoursesSessions($learnerId) {        
        $userCoursesSessions = array();
        $sql = "SELECT id_session FROM {$this->_tblSessionUser} WHERE id_user = ?";
        $userSessionsRows = $this->_ado->GetAll($sql, array($learnerId));        
        if (!empty($userSessionsRows)) {
            foreach ($userSessionsRows as $userSessionRow) {
                //$sql = "SELECT c.code, c.title FROM {$this->_tblSessionCourseUser} scu INNER JOIN {$this->_tblCourse} c ON c.code = scu.course_code WHERE scu.id_user = ? AND scu.id_session = ? AND scu.status <> ?";
                $sql = "SELECT c.code, c.title, src.position FROM {$this->_tblSessionCourseUser} scu INNER JOIN {$this->_tblCourse} c ON c.code = scu.course_code INNER JOIN {$this->_tblSessionCourse} src on src.course_code = scu.course_code  WHERE  scu.id_user = ? AND src.id_session = ? AND scu.id_session = ?  AND scu.status <> ?  GROUP BY CODE ORDER BY src.position ASC";                                
                $rows = $this->_ado->GetAll($sql, array($learnerId, $userSessionRow['id_session'],$userSessionRow['id_session'], 2));
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $userCoursesSessions[$userSessionRow['id_session']][$row['code']] = $row; 
                    }
                }
            }
        }
        return $userCoursesSessions;
    }
    
    public function getCourseUsersOnline($courseCode) {
        $valid = 10;
	$currentDate = date('Y-m-d H:i:s', time());
	$sql = "SELECT count(login_user_id) FROM {$this->_tblTrackEOnline} WHERE DATE_ADD(login_date,INTERVAL $valid MINUTE) >= '".$currentDate."' AND course = '?' ";
        return $this->_ado->GetOne($sql, array($courseCode));	
    }
    
    
    public function getCourseScenarioActivities($courseCode, $sessionId) {
        
        // it is a trick, fill scenario activities to sessions without it
        require_once api_get_path(SYS_CODE_PATH).'course_home/course_home_functions.php';
        fill_scenario_from_course_to_session($courseCode, $sessionId);
        
        $this->setCourseTables($courseCode);        
        $scenarioActivities = array();
        $sql = "SELECT id, step_name FROM {$this->_tblScenarioSteps} WHERE session_id = ? ORDER BY step_created_order";
        $scenarioRows = $this->_ado->GetAll($sql, array($sessionId));        
        if (!empty($scenarioRows)) {
            foreach ($scenarioRows as $scenarioRow) {
                $stepId = $scenarioRow['id'];
                $scenarioActivities[$stepId] = $scenarioRow;
                // get all activities by step
                $activities = array();
                $activitySql = "SELECT id, activity_type, activity_ref, activity_name FROM {$this->_tblScenarioActivity} WHERE step_id = ? ORDER BY activity_created_order";
                $activityRows = $this->_ado->GetAll($activitySql, array($stepId));
                if (!empty($activityRows)) {
                    foreach ($activityRows as $activityRow) {
                        $activities[] = $activityRow;
                    }
                }
                $scenarioActivities[$stepId]['activities'] = $activities;
            }
        }
        return $scenarioActivities;
    }
    
    public function getUserScenarioActivityView($activityId, $userId, $stepId) {        
        $activityViewSql = "SELECT status, score FROM {$this->_tblScenarioActivityView} WHERE activity_id = ? AND user_id = ? AND step_id = ?";
        return $this->_ado->GetRow($activityViewSql, array($activityId, $userId, $stepId));        
    }
    
    public function getUserAssigmentScoreAverage($courseCode, $userId, $workId) {
        $this->setCourseTables($courseCode);       
        $average = false;
        $totalScore = $totalWeight = $countScore = 0;
        $sql = "SELECT qualification, weight FROM {$this->_tblAssignment} work INNER JOIN {$this->_tblItemProperty} ip ON work.id = ip.ref  WHERE ip.insert_user_id = ? AND work.parent_id = ?";
        $rows = $this->_ado->GetAll($sql, array($userId, $workId));        
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $totalScore += $row['qualification'];
                $totalWeight += $row['weight'];
                $countScore++;
            }
        }
        if ($countScore > 0) {
            $average = round(($totalScore * 100) / $totalWeight);
        }
        return $average;
    }
    
    public function setCourseTables($courseCode) {        
        $courseInfo = api_get_course_info($courseCode);        
        $this->_tblScenarioSteps = Database :: get_course_table(TABLE_SCENARIO_STEPS, $courseInfo['dbName']);	
	$this->_tblScenarioActivity = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $courseInfo['dbName']);
	$this->_tblScenarioActivityView = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $courseInfo['dbName']);
        $this->_tblLp = Database :: get_course_table(TABLE_LP_MAIN, $courseInfo['dbName']);
        $this->_tblLpItem = Database :: get_course_table(TABLE_LP_ITEM, $courseInfo['dbName']);
        $this->_tblLpItemView = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $courseInfo['dbName']);
        $this->_tblLpView = Database :: get_course_table(TABLE_LP_VIEW, $courseInfo['dbName']);
        $this->_tblFace2Face = Database :: get_course_table(TABLE_FACE_2_FACE, $courseInfo['dbName']);
        
        $this->_tblAssignment = Database :: get_course_table(TABLE_STUDENT_PUBLICATION, $courseInfo['dbName']);
        $this->_tblItemProperty = Database :: get_course_table(TABLE_ITEM_PROPERTY, $courseInfo['dbName']);
        
    }
    
    public function setUserId($userId, $isLearner = false, $isTeacher = false) {
        $this->_currentUser = $userId;
        if ($isLearner) {
            $this->_isPlatformAdmin = false;
            $this->_isCourseManager = false;
        }
        else if ($isTeacher) {
            $this->_isPlatformAdmin = false;
            $this->_isCourseManager = true;
        }
        if (empty($userId)) {
            $this->_isCourseManager = false;
            $this->_isPlatformAdmin = true;
        }
    }
    
    public function setSessionId($sessionId) {
        $this->_sessionId = intval($sessionId);
    }
    
}
