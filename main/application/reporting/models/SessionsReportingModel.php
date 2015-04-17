<?php
class application_reporting_models_SessionsReportingModel
{

    const COURSEMANAGER = 1;
    const STUDENT = 5;
    const COACH = 2;
    
    private $_coachId;
    private $_sessionId;
    
    private $_ado; 
    private $_tblSession;
    private $_tblSessionCourse;
    private $_tblSessionCourseUser;
    private $_tblSessionCategory;
    private $_tblSessionCategoryTutor;
    
    public function __construct() {
        $this->_ado = appcore_db_DB::conn();  
        $this->_tblSession = Database::get_main_table(TABLE_MAIN_SESSION);
        $this->_tblSessionCourse = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
        $this->_tblSessionCourseUser = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
        $this->_tblSessionCategory = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
        $this->_tblSessionCategoryTutor = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_TUTOR);
    }
    
    public function getAllSessions($txtSearch = '%') {
        return $this->_ado->GetAll("SELECT id, name FROM {$this->_tblSession} WHERE name LIKE '$txtSearch%'");
    }
    
    public function getCoachSessions($coachId, $txtSearch = '%') {
        return $this->_ado->GetAll("SELECT id, name FROM {$this->_tblSession} WHERE visibility = 1 AND id_coach = ? AND name LIKE '$txtSearch%'", array($coachId));
    }
    
    public function getTutorCourseSessions($tutorId) {
        $sql = "SELECT id_session, course_code FROM {$this->_tblSessionCourseUser} WHERE id_user = ? AND status = ?";
        return $this->_ado->GetAll($sql, array($tutorId, self::COACH));
    }
    
    public function getTutorSessions($tutorId, $txtSearch = '%') {
        $sql = "SELECT DISTINCT(scu.id_session) as id, s.name FROM {$this->_tblSessionCourseUser} scu INNER JOIN {$this->_tblSession} s ON s.id = scu.id_session WHERE scu.id_user = ? AND scu.status = ? AND s.visibility = 1 AND s.name LIKE '$txtSearch%'";
        return $this->_ado->GetAll($sql, array($tutorId, self::COACH));
    }
    
    public function getLearnerSessions($learnerId, $txtSearch = '%') {
        $sql = "SELECT DISTINCT(scu.id_session) as id, s.name FROM {$this->_tblSessionCourseUser} scu INNER JOIN {$this->_tblSession} s ON s.id = scu.id_session WHERE scu.id_user = ? AND scu.status <> ? AND s.visibility = 1 AND s.name LIKE '$txtSearch%'";
        return $this->_ado->GetAll($sql, array($learnerId, self::COACH));
    }
    
    public function getTutorAllSessions($tutorId, $txtSearch = '%') {
        $sessions = array();
        $coachSessions = $this->getCoachSessions($tutorId, $txtSearch);
        if (!empty($coachSessions)) {
            foreach ($coachSessions as $session) {
                $sessions[$session['id']] = $session;
            }
        }
        $tutorSessions = $this->getTutorSessions($tutorId, $txtSearch);
        if (!empty($tutorSessions)) {
            foreach ($tutorSessions as $session) {
                $sessions[$session['id']] = $session;
            }
        }
        $tutorCategoriesSessions = $this->getTutorCategoriesSessions($tutorId, $txtSearch);
        if (!empty($tutorCategoriesSessions)) {
            foreach ($tutorCategoriesSessions as $session) {
                $sessions[$session['id']] = $session;
            }
        }
        return $sessions;
    }
   
    public function getSessionCourses() {
        if ($this->_sessionId) {
            return $this->_ado->GetAll("SELECT course_code FROM {$this->_tblSessionCourse} WHERE id_session = ?", array($this->_sessionId));
        }
    }
    
    public function getCourseSessions($courseCode, $sessions = null) {
        $inputArray = array();
        $sql = "SELECT DISTINCT(sc.id_session) as id, s.name FROM {$this->_tblSessionCourse} sc INNER JOIN {$this->_tblSession} s ON s.id = sc.id_session WHERE sc.course_code = ?";
        array_push($inputArray, $courseCode);        
        if (isset($sessions)) {
            $sql .= " AND sc.id_session IN (".implode(',', $sessions).")";
        }
        return $this->_ado->GetAll($sql, $inputArray);
    }
    
    public function setCoachId($coachId) {
        $this->_coachId = intval($coachId);
    }
    
    public function setSessionId($sessionId) {
        $this->_sessionId = intval($sessionId);
    }
    
    
    public function getTutorCategories($tutorId) {
        $sql = "SELECT sc.id, sc.name FROM {$this->_tblSessionCategory} sc INNER JOIN {$this->_tblSessionCategoryTutor} sct ON sc.id = sct.session_category_id WHERE sct.tutor_id = ?";
        return $this->_ado->GetAll($sql, array($tutorId));
    }
    
    public function getTutorCategoriesSessions($tutorId, $txtSearch = '%') {
        $sessions = array();
        $categories = $this->getTutorCategories($tutorId);
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $categorySessions = $this->getCategorySessions($category['id'], $txtSearch);
                if (!empty($categorySessions)) {
                    foreach ($categorySessions as $categorySession) {
                        $sessions[$categorySession['id']] = $categorySession;
                    }
                }
            }
        }
        return $sessions;
    }
    
    public function getCategorySessions($categoryId, $txtSearch = '%') {
        $sql = "SELECT id, id_coach, name, session_category_id FROM {$this->_tblSession} WHERE session_category_id = ? AND name LIKE '$txtSearch%'";
        return $this->_ado->GetAll($sql, array($categoryId));
    }
    
    public function getCategorySessionsId($categoryId) {
        $sql = "SELECT id FROM {$this->_tblSession} WHERE session_category_id = ?";
        return $this->_ado->GetCol($sql, array($categoryId));
    }
    
    public function getAllCategories() {
        return $this->_ado->GetAll("SELECT id, name FROM {$this->_tblSessionCategory}");
    }
    
    public function getCategoryInfo($categoryId) {
        return $this->_ado->GetRow("SELECT id, name, date_start, date_end FROM {$this->_tblSessionCategory} WHERE id = ?", array($categoryId));
    }        

}