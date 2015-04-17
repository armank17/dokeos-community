<?php
class application_reporting_models_LearnersReportingModel
{

    const COURSEMANAGER = 1;
    const STUDENT = 5;
    const COACH = 2;
    
    private $_userId;
    private $_sessionId;
    
    private $_ado; 
    private $_tblUser;
    private $_tblCourse;
    private $_tblCourseUser;
    private $_tblSession;
    private $_tblSessionCourse;
    private $_tblSessionCourseUser;
    private $_tblSessionUser;
    private $_tblTrackLogin;
    
    private $_isPlatformAdmin;
    private $_isCourseManager;
    private $_currentUser;
    
    private $_sessionReportingModel;
        
    public function __construct() {
        $this->_ado = appcore_db_DB::conn();  
        
        $this->_tblUser = Database::get_main_table(TABLE_MAIN_USER);
        $this->_tblCourse = Database::get_main_table(TABLE_MAIN_COURSE);
        $this->_tblCourseUser = Database::get_main_table(TABLE_MAIN_COURSE_USER);
        $this->_tblSession = Database::get_main_table(TABLE_MAIN_SESSION);
        $this->_tblSessionCourse = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
        $this->_tblSessionUser = Database::get_main_table(TABLE_MAIN_SESSION_USER);
        $this->_tblSessionCourseUser = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
        $this->_tblTrackLogin = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
        
        $this->_isPlatformAdmin = api_is_platform_admin();
        $this->_isCourseManager = api_is_allowed_to_create_course();
        $this->_currentUser = api_get_user_id();
        
        $this->_sessionReportingModel = new application_reporting_models_SessionsReportingModel();
    }
   
    public function getAllLearners($active = null, $courses = array(), $sessionId = null, $txtSearch = '') {
        $inputArray = array();
        $allUsers = array();   
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $courseUsers = $this->getCourseLearners($course['code'], $sessionId, $active, $txtSearch);
                if (!empty($courseUsers)) {
                    foreach ($courseUsers as $uid => $courseUser) {
                        $allUsers[$uid] = $courseUser;
                    }
                }
            }
        }
        else {
            if ($this->_isCourseManager) {
                // get users in course
                $courses = $this->_ado->GetAll("SELECT course_code FROM $this->_tblCourseUser WHERE user_id = ? AND status = ?", array($this->_currentUser, self::COURSEMANAGER));
                if ($courses) {
                    foreach ($courses as $course) {
                        $courseUsers = $this->getCourseLearners($course['course_code'], null, $active, $txtSearch);
                        if (!empty($courseUsers)) {
                            foreach ($courseUsers as $uid => $user) {
                                $allUsers[$uid] = $user;
                            }
                        }
                    }
                }            
                // get users in session
                $sessions = $this->_sessionReportingModel->getCoachSessions($this->_currentUser);
                if (!empty($sessions)) {
                    foreach ($sessions as $session) {
                        $sessionUsers = $this->getSessionLearners($session['id'], $active, $txtSearch);
                        if (!empty($sessionUsers)) {
                            foreach ($sessionUsers as $uid => $user) {
                                $allUsers[$uid] = $user;
                            }
                        }
                    }
                }
                // get users in course session
                $sessionCourses = $this->_sessionReportingModel->getTutorCourseSessions($this->_currentUser);
                if (!empty($sessionCourses)) {
                    foreach ($sessionCourses as $sessionCourse) {
                        $sessionUsers = $this->getCourseLearners($sessionCourse['course_code'], $sessionCourse['id_session'], $active, $txtSearch);
                        if (!empty($sessionUsers)) {
                            foreach ($sessionUsers as $uid => $user) {
                                $allUsers[$uid] = $user;
                            }
                        }
                    }
                }
            }
            else {
                $sql = "SELECT user_id FROM {$this->_tblUser} WHERE status = ? AND (firstname LIKE '$txtSearch%' OR lastname LIKE '$txtSearch%')";
                array_push($inputArray, self::STUDENT);
                if (isset($active)) {
                    $sql .= " AND active = ?";
                    array_push($inputArray, $active);
                }            
                $users = $this->_ado->GetAll($sql, $inputArray);
                if (!empty($users)) {
                    foreach ($users as $user) {
                        $allUsers[$user['user_id']] = api_get_user_info($user['user_id']);
                    }
                }
            }
        }                                
        return $allUsers;
    }
    
    public function getSessionLearners($sessionId, $active = null, $txtSearch = '') {
        $inputArray = array();
        $sql = "";
        if (!empty($sessionId)) {                       
            $sql = "SELECT DISTINCT(scu.id_user) as user_id, u.firstname, u.lastname, u.status, u.active 
                     FROM {$this->_tblSessionCourseUser} scu 
                     INNER JOIN {$this->_tblSessionUser} su ON su.id_user = scu.id_user 
                     INNER JOIN {$this->_tblUser} u ON u.user_id = su.id_user   
                     WHERE scu.id_session = ? AND scu.status <> ? AND (u.firstname LIKE '$txtSearch%' OR u.lastname LIKE '$txtSearch%')";
            array_push($inputArray, $sessionId, self::COACH);
        }
        $users = array();
        $rows = $this->_ado->GetAll($sql, $inputArray);
        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (isset($active) && $row['active'] != $active) { continue; }
                $users[$row['user_id']] = $row;
            }
        } 
        return $users;
    }
    
    public function getCourseLearners($courseCode, $sessionId = null, $active = null, $txtSearch = '') {
        $inputArray = array();
        $sql = "";
        $users = array();
        if (isset($sessionId)) {
            if (is_numeric($sessionId) && $sessionId > 0) {
                if ($this->isSessionTutor($this->_userId, $sessionId)) {
                    $sql = "SELECT DISTINCT(scu.id_user) as user_id, u.firstname, u.lastname, u.status, u.active 
                             FROM {$this->_tblSessionCourseUser} scu 
                             INNER JOIN {$this->_tblSessionUser} su ON su.id_user = scu.id_user 
                             INNER JOIN {$this->_tblUser} u ON u.user_id = su.id_user 
                             WHERE scu.course_code = ? AND scu.id_session = ? AND scu.status <> ? AND (u.firstname LIKE '$txtSearch%' OR u.lastname LIKE '$txtSearch%')";
                    array_push($inputArray, $courseCode, $sessionId, self::COACH);
                }
            }
            else if (is_array($sessionId) && count($sessionId) > 0) {
                $sql = "SELECT DISTINCT(scu.id_user) as user_id, u.firstname, u.lastname, u.status, u.active 
                             FROM {$this->_tblSessionCourseUser} scu 
                             INNER JOIN {$this->_tblSessionUser} su ON su.id_user = scu.id_user 
                             INNER JOIN {$this->_tblUser} u ON u.user_id = su.id_user 
                             WHERE scu.course_code = ? AND scu.id_session IN(".implode(',', $sessionId).") AND scu.status <> ? AND (u.firstname LIKE '$txtSearch%' OR u.lastname LIKE '$txtSearch%')";
                    array_push($inputArray, $courseCode, self::COACH);
            }
            else {
                if ($this->isCourseTrainer($this->_userId, $courseCode)) {
                    $sql = "SELECT DISTINCT(cu.user_id) as user_id, u.firstname, u.lastname, u.status, u.active 
                             FROM {$this->_tblCourseUser} cu 
                             INNER JOIN {$this->_tblUser} u ON u.user_id = cu.user_id 
                             WHERE cu.course_code = ? AND cu.status = ? AND (u.firstname LIKE '$txtSearch%' OR u.lastname LIKE '$txtSearch%')";
                    array_push($inputArray, $courseCode, self::STUDENT);
                }
            }
            if (!empty($sql)) {
                $rows = $this->_ado->GetAll($sql, $inputArray);
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        if (isset($active) && $row['active'] != $active) { continue; }
                        $users[$row['user_id']] = $row;
                    }
                }
            }
        }
        else {
            // users in course include sessions            
            if ($this->isCourseTrainer($this->_userId, $courseCode)) {
                $sql = "SELECT DISTINCT(cu.user_id) as user_id, u.firstname, u.lastname, u.status, u.active 
                         FROM {$this->_tblCourseUser} cu 
                         INNER JOIN {$this->_tblUser} u ON u.user_id = cu.user_id 
                         WHERE cu.course_code = ? AND cu.status = ? AND (u.firstname LIKE '$txtSearch%' OR u.lastname LIKE '$txtSearch%')";          
                $rows = $this->_ado->GetAll($sql, array($courseCode, self::STUDENT));
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        if (isset($active) && $row['active'] != $active) { continue; }
                        $users[$row['user_id']] = $row;
                    }
                }
            }

            if (!empty($this->_userId)) {
                $tutorSessions = $this->_sessionReportingModel->getTutorAllSessions($this->_userId);
                if (!empty($tutorSessions)) {
                    $sql = "SELECT DISTINCT(scu.id_user) as user_id, u.firstname, u.lastname, u.status, u.active 
                             FROM {$this->_tblSessionCourseUser} scu 
                             INNER JOIN {$this->_tblSessionUser} su ON su.id_user = scu.id_user 
                             INNER JOIN {$this->_tblUser} u ON u.user_id = su.id_user 
                             WHERE scu.course_code = ? AND scu.status <> ? AND scu.id_session IN(".implode(',', array_keys($tutorSessions)).") AND (u.firstname LIKE '$txtSearch%' OR u.lastname LIKE '$txtSearch%')";
                    $rows = $this->_ado->GetAll($sql, array($courseCode, self::COACH));
                    if (!empty($rows)) {
                        foreach ($rows as $row) {
                            if (isset($active) && $row['active'] != $active) { continue; }
                            $users[$row['user_id']] = $row;
                        }
                    }
                }
            }
            else {
                $sql = "SELECT DISTINCT(scu.id_user) as user_id, u.firstname, u.lastname, u.status, u.active 
                             FROM {$this->_tblSessionCourseUser} scu 
                             INNER JOIN {$this->_tblSessionUser} su ON su.id_user = scu.id_user 
                             INNER JOIN {$this->_tblUser} u ON u.user_id = su.id_user 
                             WHERE scu.course_code = ? AND scu.status <> ? AND (u.firstname LIKE '$txtSearch%' OR u.lastname LIKE '$txtSearch%')";
                $rows = $this->_ado->GetAll($sql, array($courseCode, self::COACH));
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        if (isset($active) && $row['active'] != $active) { continue; }
                        $users[$row['user_id']] = $row;
                    }
                }                
            }
        }  
        return $users;
    }
    
    public function getUserPlatformConnection($userId) {
        $connections = array();
        $sql = "SELECT login_date, logout_date FROM {$this->_tblTrackLogin} WHERE login_user_id = ?";
        $rows = $this->_ado->GetAll($sql, array($userId));
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $connections[] = array('login' => strtotime($row['login_date']), 'logout' => strtotime($row['logout_date']));
            }
        }
        return $connections;    
    }
    
    public function isCourseTrainer($userId, $courseCode) {
        $result = true; // if user id is not setted, the current user is the platform admin
        if (!empty($userId)) {
            $sql = "SELECT count(course_code) FROM {$this->_tblCourseUser} WHERE user_id = ? AND course_code = ? AND status = ?";
            $result = $this->_ado->GetOne($sql, array($userId, $courseCode, self::COURSEMANAGER));
        }
        return (bool)$result;
    }
    
    public function isSessionCourseTutor($userId, $sessionId, $courseCode) {
        $result = true; // if user id is empty, the current user is the platform admin
        if (!empty($userId)) {
            $sql = "SELECT count(id_session) FROM {$this->_tblSessionCourseUser} WHERE id_user = ? AND id_session = ? AND course_code = ? AND status = ?";
            $result = $this->_ado->GetOne($sql, array($userId, $sessionId, $courseCode, self::COACH));        
        }
        return (bool)$result;
    }
    
    public function isSessionTutor($userId, $sessionId) {
        $result = true; // if user id is empty, the current user is the platform admin         
        if (!empty($userId)) {
            $result = false;
            $tutorSessions = $this->_sessionReportingModel->getTutorAllSessions($userId);
            if (!empty($tutorSessions) && in_array($sessionId, array_keys($tutorSessions))) {
                $result = true;
            }
        }
        return $result;
    }
    
    public function setUserId($userId) {
        if (empty($userId)) {
            $this->_isCourseManager = false;
        }
        $this->_userId = intval($userId);
    }
    
    public function setSessionId($sessionId) {
        $this->_sessionId = intval($sessionId);
    }
    
}
