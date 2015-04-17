<?php
class application_reporting_models_Face2FaceReportingModel
{
    
    private $_tblScenarioSteps;
    private $_tblScenarioActivity;
    private $_tblScenarioActivityView;
    private $_tblFace2Face;
    
    private $_isPlatformAdmin;
    private $_isCourseManager;
    private $_currentUser;
    
    private $_coursesReportingModel;
    
    public function __construct() {
        $this->_ado = appcore_db_DB::conn();  

        $this->_isPlatformAdmin = api_is_platform_admin();
        $this->_isCourseManager = api_is_allowed_to_create_course();
        $this->_currentUser = api_get_user_id();
        
        $this->_coursesReportingModel = new application_reporting_models_CoursesReportingModel();
    }
    
    public function getAllFace2Faces($courses = null, $sessionId = 0, $txtSearch = '') {
        if (!isset($courses)) {
            $this->_coursesReportingModel->setSessionId($sessionId);
            $courses = $this->_coursesReportingModel->getCourses();
        }        
        $allFace2Faces = array();
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $face2faces = $this->getFace2Faces($course['code'], $sessionId, $txtSearch);
                if (!empty($face2faces)) {
                    foreach ($face2faces as $face2face) {
                        $face2face['course_code'] = $course['code'];
                        $face2face['course_title'] = $course['title'];
                        $allFace2Faces[] = $face2face;
                    }
                }
            }
        }
        return $allFace2Faces;
    }
    
    public function getFace2Faces($courseCode, $sessionId = 0, $txtSearch = '') {
        
        // it is a trick, fill scenario activities to sessions without it
        require_once api_get_path(SYS_CODE_PATH).'course_home/course_home_functions.php';
        if (is_array($sessionId) && count($sessionId) > 0) {
            foreach ($sessionId as $sessId) {
                fill_scenario_from_course_to_session($courseCode, $sessId);
            }
        }
        else {
            fill_scenario_from_course_to_session($courseCode, $sessionId);
        }        
                        
        $this->setCourseTables($courseCode);
        $courseInfo = api_get_course_info($courseCode);        
        $sessionFilter = is_array($sessionId) && count($sessionId) > 0?" AND session_id IN(".implode(',', $sessionId).")":" AND session_id = ".intval($sessionId);        
        $sql = "SELECT id, name, ff_type, session_id FROM {$this->_tblFace2Face} WHERE 1=1 $sessionFilter AND name LIKE '$txtSearch%' ORDER BY session_id";
        $face2faces = array();
        $rows = $this->_ado->GetAll($sql);
        foreach ($rows as $face2face) {
            $face2face['course_code'] = $courseCode;
            $face2face['course_title'] = $courseInfo['name'];
            $face2faces[$face2face['id']] = $face2face;
        }
        return $face2faces;
    }
    
    public function getFace2FaceUsers($courseCode, $face2faceId, $learners = array()) {
        $this->setCourseTables($courseCode);
        $inputArray = array();        
        $activityId = $this->_ado->GetOne("SELECT id FROM {$this->_tblScenarioActivity} WHERE activity_ref = ? AND activity_type = 'face2face'", array($face2faceId));        
        $sql = "SELECT DISTINCT(user_id) FROM {$this->_tblScenarioActivityView} WHERE activity_id = ?";
        array_push($inputArray, $activityId);
        if (!empty($learners)) {
            $sql .= " AND user_id IN(".implode(',', array_keys($learners)).")";
        }
        $users = array();
        $rows = $this->_ado->GetAll($sql, $inputArray);
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $users[$row['user_id']] = api_get_user_info($row['user_id']);
            }
        }
        return $users;
    }
    
    public function getFace2FaceScore($courseCode, $face2faceId, $learners) {
        $this->setCourseTables($courseCode);        
        $score = 0;
        $iMaxScore = 20;
        $countFace2Face = 0;
        $inputArray = array();    
        $activityId = $this->_ado->GetOne("SELECT id FROM {$this->_tblScenarioActivity} WHERE activity_ref = ? AND activity_type = 'face2face'", array($face2faceId));     
        if (!empty($activityId)) {
            $sql = "SELECT score FROM {$this->_tblScenarioActivityView} WHERE activity_id = ?";
            array_push($inputArray, $activityId);            
            if (!empty($learners)) {
                $sql .= " AND user_id IN(".implode(',', array_keys($learners)).")";
            }
            $rows = $this->_ado->GetAll($sql, $inputArray);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $iScore = $row['score'];
                    $score  += $iMaxScore > 0 ? ($iScore * 100) / $iMaxScore : 0;
                    $countFace2Face++;
                }
            }
        }
        $result = $countFace2Face > 0?round($score / count($learners)):false;        
        return $result;        
    }
    
    public function getUserFace2faceTotalPassed($learnerId, $courseCode, $sessionId, $percent = true) {
        $this->setCourseTables($courseCode);        
        // get all course face2faces
        $face2faces = $this->getFace2Faces($courseCode, $sessionId);
        $passed = $result = 0;        
        if (!empty($face2faces)) {
            foreach ($face2faces as $face2face) {                
                if ($face2face['ff_type'] == 2) {
                    $passedAndScore = $this->getUserPassedAndScore($courseCode, $face2face['id'], $learnerId);                    
                }
                else {
                    $passedAndScore = $this->getUserPassedAndComments($courseCode, $face2face['id'], $learnerId);
                }                
                if ($passedAndScore['passed']) {
                    $passed++;
                }
            }
        }        
        if ($percent) {
            if (count($face2faces) > 0) {
                $result = round(($passed * 100) / count($face2faces));
            }
        }
        else {
            $result = $passed;
        }
        return $result;
    }
    
    public function getPassedUsersPercentage($courseCode, $face2faceId, $type, $learners) {
        $this->setCourseTables($courseCode);
        $countPassed = $countFace2Face = $percent = 0;
        $inputArray = array();
        $minScore = $this->_ado->GetOne("SELECT max_score FROM {$this->_tblFace2Face} WHERE id = ?", array($face2faceId));
        $activityId = $this->_ado->GetOne("SELECT id FROM {$this->_tblScenarioActivity} WHERE activity_ref = ? AND activity_type = 'face2face'", array($face2faceId));
        if (!empty($activityId)) {            
            if ($type == 2) {            
                $sql = "SELECT score FROM {$this->_tblScenarioActivityView} WHERE activity_id = ?";
                array_push($inputArray, $activityId);
                if (!empty($learners)) {
                    $sql .= " AND user_id IN(".implode(',', array_keys($learners)).")";
                }
                $rows = $this->_ado->GetAll($sql, $inputArray);
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        if ($row['score'] >= $minScore) {
                            $countPassed++;
                        }
                        $countFace2Face++;
                    }

                }
            }
            else {
                $sql = "SELECT id FROM {$this->_tblScenarioActivityView} WHERE activity_id = ? AND view_count = 2";
                array_push($inputArray, $activityId);
                if (!empty($learners)) {
                    $sql .= " AND user_id IN(".implode(',', array_keys($learners)).")";
                }
                $rows = $this->_ado->GetAll($sql, $inputArray);               
                if (!empty($rows)) {
                    $countPassed = count($rows);
                    $countFace2Face++;
                }
            }            
            if (count($learners) > 0) {
                $percent = ($countPassed * 100) / count($learners);
            }            
        }
        $result = $countFace2Face > 0?round($percent):false;
        return $result;        
    }
    
    public function getUserPassedAndScore($courseCode, $face2faceId, $userId) {
        $this->setCourseTables($courseCode);
        $result = array('score' => false);        
        $minScore = $this->_ado->GetOne("SELECT max_score FROM {$this->_tblFace2Face} WHERE id = ?", array($face2faceId));
        $activityId = $this->_ado->GetOne("SELECT id FROM {$this->_tblScenarioActivity} WHERE activity_ref = ? AND activity_type = 'face2face'", array($face2faceId));
        if (!empty($activityId)) {
            $sql = "SELECT score FROM {$this->_tblScenarioActivityView} WHERE activity_id = ? AND user_id = ?";                
            $score = $this->_ado->GetOne($sql, array($activityId, $userId));        
            if (isset($score)) {
                $result['score'] = round($score);
                if ($score >= $minScore) {
                    $result['passed'] = true;                    
                }
                else {
                    $result['passed'] = false;
                }
            }
        }        
        return $result;
    }
    
     public function getUserPassedAndComments($courseCode, $face2faceId, $userId) {
        $this->setCourseTables($courseCode);
        $activityId = $this->_ado->GetOne("SELECT id FROM {$this->_tblScenarioActivity} WHERE activity_ref = ? AND activity_type = 'face2face'", array($face2faceId));
        if (!empty($activityId)) {
            $sql = "SELECT view_count, comment FROM {$this->_tblScenarioActivityView} WHERE activity_id = ? AND user_id = ?";     
            $row = $this->_ado->GetRow($sql, array($activityId, $userId));
            if (isset($row['view_count'])) {
                $result['comment'] = $row['comment'];
                if ($row['view_count'] == 2) {
                    $result['passed'] = true;                    
                }
                else {
                    $result['passed'] = false;
                }
            }
        }        
        return $result;
    }
    
    public function getFace2FaceInfo($courseCode, $face2faceId) {
        $this->setCourseTables($courseCode);
        $sql = "SELECT id, name, ff_type FROM {$this->_tblFace2Face} WHERE id = ?";
        return $this->_ado->GetRow($sql, array($face2faceId));
    }
        
    public function setCourseTables($courseCode) {        
        $courseInfo = api_get_course_info($courseCode);        
        $this->_tblScenarioSteps = Database :: get_course_table(TABLE_SCENARIO_STEPS, $courseInfo['dbName']);	
	$this->_tblScenarioActivity = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $courseInfo['dbName']);
	$this->_tblScenarioActivityView = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $courseInfo['dbName']);        
        $this->_tblFace2Face = Database :: get_course_table(TABLE_FACE_2_FACE, $courseInfo['dbName']);
    }
}
