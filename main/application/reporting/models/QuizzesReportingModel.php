<?php
class application_reporting_models_QuizzesReportingModel
{
    
    private $_ado; 
    
    private $_tblQuiz;
    private $_tblTrackExercise;
    private $_tblTrackAttempt;
    private $_tblExam;
    
    private $_isPlatformAdmin;
    private $_isCourseManager;
    private $_currentUser;
    private $_trainerId;
    
    private $_coursesReportingModel;
    private $_learnersReportingModel;
    
    public function __construct() {
        $this->_ado = appcore_db_DB::conn();
        
        $this->setMainTables();
        $this->setStatsTables();
        
        $this->_isPlatformAdmin = api_is_platform_admin();
        $this->_isCourseManager = api_is_allowed_to_create_course();
        $this->_currentUser = api_get_user_id();
        
        $this->_coursesReportingModel = new application_reporting_models_CoursesReportingModel();
        $this->_learnersReportingModel = new application_reporting_models_LearnersReportingModel();
    }
    
    public function getCourseQuizzesAndExamsAvgScore($courseCode, $sessionId = null, $learners = array()) {
        $sumScore = $count = 0;
        $quizzes = $this->getQuizzes($courseCode, $sessionId, array(), '', $learners);        
        if (!empty($quizzes)) {
            foreach ($quizzes as $quiz) {
                $sumScore += $quiz['avgScore'];
                $count++;
            }
        }        
        $exams = $this->getExams($courseCode, $sessionId, array(), '', $learners); 
        if (!empty($exams)) {
            foreach ($exams as $exam) {               
                $sumScore += intval($exam['avgScore']);
                $count++;
            }
        }
        $result = $count > 0?round($sumScore / $count):false;
        return $result;
    }
    
    public function getCourseQuizzesScore($courseCode, $sessionId = null, $learners = array()) {
        $sumScore = 0;
        $countQuizzes = 0;
        $quizzes = $this->getQuizzes($courseCode, $sessionId);
        if (!empty($learners)) {
            foreach ($learners as $learner) {
                $userScore = $this->getUserQuizLastScore($courseCode, $learner['user_id'], $sessionId, $quizzes);
                if ($userScore !== false) {
                    $sumScore += $userScore;
                    $countQuizzes++;
                }
            }
        }
        $result = $countQuizzes > 0?(round(($sumScore / count($learners)) / count($quizzes))):false;
        return $result;
    }
    
    public function getCourseExamsScore($courseCode, $sessionId = null, $learners = array()) {        
        $sumScore = 0;
        $countExams = 0;
        $exams = $this->getExams($courseCode, $sessionId);
        if (!empty($learners)) {            
            foreach ($learners as $learner) {
                $userScore = $this->getUserExamLastScore($courseCode, $learner['user_id'], $sessionId, $exams);
                if ($userScore !== false) {
                    $sumScore += $userScore;
                    $countExams++;
                }
            }
        }
        $result = $countExams > 0?(round(($sumScore / count($learners)) / count($exams))):false;
        return $result;
    }
    
    public function getQuizInfo($courseCode, $quizId) {
        $this->setCourseTables($courseCode);
        $sql  = "SELECT id, title FROM {$this->_tblQuiz} WHERE id = ?";
        return $this->_ado->GetRow($sql, array($quizId));       
    }
    
    public function getExamInfo($courseCode, $examId) {
        $this->setCourseTables($courseCode);
        $sql  = "SELECT id, exam_name as title FROM {$this->_tblExam} WHERE id = ?";
        return $this->_ado->GetRow($sql, array($examId));       
    }
    
    public function getQuizzes($courseCode, $sessionId = 0, $quizzesIds = array(), $txtSearch = '', $learners = null) {
        $inputArray = array();
        $this->setCourseTables($courseCode);
        $courseInfo = api_get_course_info($courseCode);       
        if (is_array($sessionId) && count($sessionId) > 0) {
            $sql  = "SELECT id, title FROM {$this->_tblQuiz} WHERE active = 1 AND session_id IN(".implode(',', $sessionId).") AND title LIKE '$txtSearch%'";
        }
        else {
            $sql  = "SELECT id, title FROM {$this->_tblQuiz} WHERE active = 1 AND session_id IN(0, ?) AND title LIKE '$txtSearch%'";
            array_push($inputArray, $sessionId);
        }        
       
        if (!empty($quizzesIds)) {
            $sql .= " AND id IN (".implode(',', $quizzesIds).")";
        }       
        $rows = $this->_ado->GetAll($sql, $inputArray);         
        $quizzes = array();        
        if (!isset($learners)) {
            $this->_learnersReportingModel->setUserId($this->_trainerId);
            $learners = $this->_learnersReportingModel->getCourseLearners($courseCode, $sessionId);
        }
        if (!empty($rows)) {
            foreach ($rows as $row) {                                
                $avgScoreAndParticipation = $this->getQuizAvgScoreAndParticipation($row['id'], $courseCode, $sessionId, $learners);
                if (empty($avgScoreAndParticipation['participation'])) { continue; }
                $row['course_code'] = $courseCode;
                $row['course_title'] = $courseInfo['name'];
                $row['mode'] = 'quiz';
                $row['avgScore'] = $avgScoreAndParticipation['avgScore'];
                $row['highest'] = $avgScoreAndParticipation['highest'];
                $row['lowest'] = $avgScoreAndParticipation['lowest'];
                $row['participation'] = $avgScoreAndParticipation['participation'];                                
                $row['nb_learners'] = count($learners);                
                $row['avgTime'] = $avgScoreAndParticipation['avg_time'];
                $row['exe_exo_id'] = $row['id'];
                $quizzes[$row['id']] = $row;
            }
        }        
        return $quizzes;
    }
    
    public function getExams($courseCode, $sessionId = 0, $examsIds = array(), $txtSearch = '', $learners = null) {
        $inputArray = array();
        $this->setCourseTables($courseCode);
        $courseInfo = api_get_course_info($courseCode);        
        if (is_array($sessionId) && count($sessionId) > 0) {
            $sql  = "SELECT id, exam_name as title, quiz_id FROM {$this->_tblExam} WHERE session_id IN(".implode(',', $sessionId).") AND exam_name LIKE '$txtSearch%'";
        }
        else {
            $sql  = "SELECT id, exam_name as title, quiz_id FROM {$this->_tblExam} WHERE session_id IN(0, ?) AND exam_name LIKE '$txtSearch%'";
            array_push($inputArray, $sessionId);
        }                
        if (!empty($examsIds)) {
            $sql .= " AND id IN (".implode(',', $examsIds).")";
        }        
        $rows = $this->_ado->GetAll($sql, $inputArray); 
        $exams = array();
        if (!isset($learners)) {
            $this->_learnersReportingModel->setUserId($this->_trainerId);
            $learners = $this->_learnersReportingModel->getCourseLearners($courseCode, $sessionId);
        }
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $avgScoreAndParticipation = $this->getExamAvgScoreAndParticipation($row['id'], $courseCode, $sessionId, $learners);
                if (empty($avgScoreAndParticipation['participation'])) { continue; }
                $row['course_code'] = $courseCode;
                $row['course_title'] = $courseInfo['name'];
                $row['mode'] = 'exam';
                $row['avgScore'] = $avgScoreAndParticipation['avgScore'];
                $row['highest'] = $avgScoreAndParticipation['highest'];
                $row['lowest'] = $avgScoreAndParticipation['lowest'];
                $row['participation'] = $avgScoreAndParticipation['participation'];                
                $row['nb_learners'] = count($this->getExamUsers($row['id'], $learners));                
                $row['avgTime'] = $avgScoreAndParticipation['avg_time'];
                $row['exe_exo_id'] = $row['quiz_id'];
                $exams[$row['id']] = $row;
            }
        }
        return $exams;
    }
    
    public function getExamUsers($examId, $learners = array()) {
        $inputArray = array();
        $sql = "SELECT u.user_id, u.firstname, u.lastname, u.username, u.email FROM {$this->_tblUser} u INNER JOIN {$this->_tblExamUser} eu ON eu.user_id = u.user_id WHERE eu.exam_id = ?";
        array_push($inputArray, $examId);
        if (!empty($learners)) {
            $sql .= " AND eu.user_id IN (".implode(',', array_keys($learners)).")";
        }
        $users = $this->_ado->GetAll($sql, $inputArray);
        return $users;
    }
    
    public function getAllQuizzes($courses = null, $sessionId = null, $txtSearch = '') {
        if (!isset($courses)) {
            if (is_array($sessionId)) {
                $courses = $this->_coursesReportingModel->getCoursesInCategorySessions($sessionId);
            }
            else {
                $this->_coursesReportingModel->setSessionId($sessionId);
                $courses = $this->_coursesReportingModel->getCourses();
            }            
        }
        $allQuizzes = array();
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $quizzes = $this->getQuizzes($course['code'], $sessionId, array(), $txtSearch);                
                if (!empty($quizzes)) {
                    foreach ($quizzes as $quiz) {                        
                        $allQuizzes[] = $quiz;
                    }
                }
                
            }
        }
        return $allQuizzes;
    }
    
    public function getAllExams($courses = null, $sessionId = null, $txtSearch = '') {
        if (!isset($courses)) {
            if (is_array($sessionId)) {
                $courses = $this->_coursesReportingModel->getCoursesInCategorySessions($sessionId);
            }
            else {
                $this->_coursesReportingModel->setSessionId($sessionId);
                $courses = $this->_coursesReportingModel->getCourses();
            }  
        }
        $allExams = array();
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $exams = $this->getExams($course['code'], $sessionId, array(), $txtSearch);
                if (!empty($exams)) {
                    foreach ($exams as $exam) {
                        $allExams[] = $exam;
                    }
                }
                
            }
        }
        return $allExams;
    }
    
    public function getExamScoreHighestAndLowestAndExamAvgTime($examId, $courseCode, $sessionId = null, $learners = array()) {
        $inputArray = array();
        $sql  = "SELECT MAX(manual_exe_result) as highest, MIN(manual_exe_result) as lowest, SUM(UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exam_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id > 0 AND orig_lp_id = 0 AND orig_lp_item_id = 0";
        array_push($inputArray, $courseCode, $examId);
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
            $sql .= " AND exe_user_id IN (".implode(',', array_keys($learners)).")";
        }        
        $row = $this->_ado->GetRow($sql, $inputArray);
        $highest = isset($row['highest'])?round($row['highest']):false;
        $lowest  = isset($row['lowest'])?round($row['lowest']):false;        
        $avgTime = isset($row['nbseconds']) && $row['nbseconds'] > 0?round($row['nbseconds'] / count($learners)):0;        
        return array('highest' => $highest, 'lowest' => $lowest, 'avgTime' => $avgTime);
    }
    
    public function getQuizScoreHighestAndLowestAndQuizAvgTime($quizId, $courseCode, $sessionId = null, $learners = array()) {
        $inputArray = array();
        $sql  = "SELECT MAX(exe_result / exe_weighting) as highest, MIN(exe_result / exe_weighting) as lowest, SUM(UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_exo_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id = 0 AND orig_lp_id = 0 AND orig_lp_item_id = 0";
        array_push($inputArray, $courseCode, $quizId);
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
            $sql .= " AND exe_user_id IN (".implode(',', array_keys($learners)).")";
        }    
        $row = $this->_ado->GetRow($sql, $inputArray);
        $highest = isset($row['highest'])?round(($row['highest'] * 100)):false;
        $lowest  = isset($row['lowest'])?round(($row['lowest'] * 100)):false;
        $avgTime = isset($row['nbseconds']) && $row['nbseconds'] > 0?$row['nbseconds']:0;
        return array('highest' => $highest, 'lowest' => $lowest, 'avgTime' => $avgTime);
    }
    
    public function getQuizAvgScoreAndParticipation($quizId, $courseCode, $sessionId = null, $learners = array()) {       
        $inputArray = array();
        $sql  = "SELECT MAX(exe_result / exe_weighting) as score, exe_user_id, exe_weighting, (UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) as nbseconds FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_exo_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id = 0 AND orig_lp_id = 0 AND orig_lp_item_id = 0";        
        array_push($inputArray, $courseCode, $quizId);
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
            $sql .= " AND exe_user_id IN (".implode(',', array_keys($learners)).")";
        }
        $sql .= " GROUP BY exe_user_id";
       
        $rows = $this->_ado->GetAll($sql, $inputArray);
        $totPerScore = $participation = $totalWeight = 0;
        $scores = array();
        $totalTime = $avgTime = 0;
        if (!empty($rows)) {           
            foreach ($rows as $row) {
                if (!empty($row['exe_weighting'])) {
                    $totalWeight += $row['exe_weighting'];
                    $perScore  = ($row['score'] * 100);
                    $totPerScore += $perScore;
                    $scores[] = $perScore;
                    $totalTime += isset($row['nbseconds']) && $row['nbseconds'] > 0?$row['nbseconds']:0;
                    $participation++;
                }                
            }            
        }             
        $avgScore = $highest = $lowest = false;
        $noParticipation = 0;
        if (count($learners) > 0) {
            $noParticipation = count($learners) - $participation;
            $avgScore = round(round($totPerScore) / count($learners));
            $highest = round(max($scores));            
            $lowest = $noParticipation == 0?round(min($scores)):0;
            $avgTime = round($totalTime / count($learners)); 
            if ($avgScore > 100) { $avgScore = 100; }
        }      
        return array('avgScore' => $avgScore, 'participation' => $participation, 'total_weight' => $totalWeight, 'highest' => $highest, 'lowest' => $lowest, 'total_time' => $totalTime, 'participants' => count($learners), 'avg_time' => $avgTime);
    }
    
    public function getExamAvgScoreAndParticipation($examId, $courseCode, $sessionId = null, $learners = array()) {        
        $inputArray = array();         
        $sql  = "SELECT MAX(exe_id) as last_exe_id, exe_user_id, (UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) as nbseconds FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exam_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id > 0 AND orig_lp_id = 0 AND orig_lp_item_id = 0";
        array_push($inputArray, $courseCode, $examId);
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
            $sql .= " AND exe_user_id IN (".implode(',', array_keys($learners)).")";
        }        
        $sql .= " GROUP BY exe_user_id";        
        $rows = $this->_ado->GetAll($sql, $inputArray);
        $totPerScore = $participation = 0;  
        $scores = array();
        $totalTime = $avgTime = 0;
        if (!empty($rows)) {            
            foreach ($rows as $row) {
                $sql = "SELECT manual_exe_result FROM {$this->_tblTrackExercise} WHERE exe_id = ?";
                $trackExe = $this->_ado->GetRow($sql, array($row['last_exe_id']));
                $perScore = 0;
                if (!empty($trackExe)) {
                    $perScore  = $trackExe['manual_exe_result'];
                    $totPerScore += $perScore;
                    $scores[] = $perScore;
                    $totalTime += isset($row['nbseconds']) && $row['nbseconds'] > 0?$row['nbseconds']:0;
                    $participation++;
                }                
            }            
        }        
        $avgScore = $highest = $lowest = false;        
        $examUsers = $this->getExamUsers($examId, $learners);        
        $noParticipation = 0;
        if (count($examUsers) > 0) {
            $noParticipation = count($examUsers) - $participation;
            $avgScore = round(round($totPerScore) / count($examUsers));
            $highest = round(max($scores));
            $lowest = $noParticipation == 0?round(min($scores)):0;
            $avgTime = round($totalTime / count($examUsers));
            if ($avgScore > 100) { $avgScore = 100; }
        }
        return array('avgScore' => $avgScore, 'participation' => $participation, 'highest' => $highest, 'lowest' => $lowest, 'total_time' => $totalTime, 'participants' => count($examUsers), 'avg_time' => $avgTime);
    }
    
    public function getUserQuizNbAttempts($courseCode, $userId, $sessionId = null, $quizzes = array()) {
        $inputArray = array(); 
        $sql = "SELECT count(exe_id) FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_user_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id = 0 AND orig_lp_id = 0 AND orig_lp_item_id = 0";
        array_push($inputArray, $courseCode, $userId);
        if (isset($sessionId)) {
            if (is_array($sessionId) && count($sessionId) > 0) {
                $sql .= " AND session_id IN(".implode(',', $sessionId).")";
            }
            else {
                $sql .= " AND session_id = ?";
                array_push($inputArray, $sessionId);
            }
        }
        if (!empty($quizzes)) {
            $sql .= " AND exe_exo_id IN (".implode(',', array_keys($quizzes)).")";
        }
        return $this->_ado->GetOne($sql, $inputArray);        
    }
    
    public function getUserExamNbAttempts($courseCode, $userId, $sessionId = null, $exams = array()) {
        $inputArray = array(); 
        $sql = "SELECT count(exe_id) FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_user_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id > 0";
        array_push($inputArray, $courseCode, $userId);
        if (isset($sessionId)) {
            if (is_array($sessionId) && count($sessionId) > 0) {
                $sql .= " AND session_id IN(".implode(',', $sessionId).")";
            }
            else {
                $sql .= " AND session_id = ?";
                array_push($inputArray, $sessionId);
            }
        }
        if (!empty($exams)) {
            $sql .= " AND exam_id IN (".implode(',', array_keys($exams)).")";
        }
        return $this->_ado->GetOne($sql, $inputArray);        
    }
    
    public function getUserQuizMaxScoreAndTime($courseCode, $userId, $sessionId = null, $quizzes = array()) {
        $inputArray = array();  
        //$sql = "SELECT exe_result, exe_weighting, SUM(UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_user_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id = 0 AND orig_lp_id = 0 AND orig_lp_item_id = 0";
        $sql = "SELECT (exe_result / exe_weighting) as score, (UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds, exe_id, exe_exo_id FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_user_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id = 0 AND orig_lp_id = 0 AND orig_lp_item_id = 0";
        array_push($inputArray, $courseCode, $userId);
        if (isset($sessionId)) {
            if (is_array($sessionId) && count($sessionId) > 0) {
                $sql .= " AND session_id IN(".implode(',', $sessionId).")";
            }
            else {
                $sql .= " AND session_id = ?";
                array_push($inputArray, $sessionId);
            }
        }
        if (!empty($quizzes)) {
            $sql .= " AND exe_exo_id IN (".implode(',', array_keys($quizzes)).")";
        }
        $sql .= " ORDER BY score DESC, exe_id DESC LIMIT 1";       
        $row = $this->_ado->GetRow($sql, $inputArray);
        $perScore = $exeResult = $exeWeight = $countQuizzes = 0;        
        if (!empty($row)) {
            if (isset($row['score'])) {
                $perScore  = ($row['score'] * 100);
                $countQuizzes++;
            }
        }        
        $score = $countQuizzes > 0?round($perScore):false;       
        $time  = isset($row['nbseconds']) && $row['nbseconds'] > 0?$row['nbseconds']:0;
        return array('score' => $score, 'time' => $time, 'attempt_id' => $row['exe_id'], 'quiz_id' => $row['exe_exo_id']);
    }
    
    public function getUserExamLastScoreAndTime($courseCode, $userId, $sessionId = null, $exams = array()) {
        $inputArray = array(); 
        $sql = "SELECT exe_id, (UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds, exam_id, manual_exe_result, session_id, exe_exo_id FROM {$this->_tblTrackExercise} WHERE exe_cours_id = ? AND exe_user_id = ? AND status NOT IN('left_incomplete', 'incomplete') AND exam_id > 0";
        array_push($inputArray, $courseCode, $userId);
        if (isset($sessionId)) {
            if (is_array($sessionId) && count($sessionId) > 0) {
                $sql .= " AND session_id IN(".implode(',', $sessionId).")";
            }
            else {
                $sql .= " AND session_id = ?";
                array_push($inputArray, $sessionId);
            }
        }
        if (!empty($exams)) {
            $sql .= " AND exam_id IN (".implode(',', array_keys($exams)).")";
        }
        $sql .= " ORDER BY exe_id DESC LIMIT 1";        
        $row = $this->_ado->GetRow($sql, $inputArray);
        $perScore = $countExams = 0;     
        if (!empty($row)) {
            if (isset($row['manual_exe_result'])) {
                $perScore += $row['manual_exe_result'];           
                $countExams++;
            }
        }             
        $score = $countExams > 0?round($perScore):false;
        $time  = isset($row['nbseconds']) && $row['nbseconds'] > 0?$row['nbseconds']:0;
        return array('score' => $score, 'time' => $time, 'attempt_id' => $row['exe_id'], 'quiz_id' => $row['exe_exo_id']);
    }
    
    public function getUserQuizzesScore($userId, $courseCode = null, $sessionId = null, $type = null) {        
        $this->_coursesReportingModel->setUserId($userId, true);
       
        $userInfo = api_get_user_info($userId);
        $result = false;
        $score = 0;
        $countCourses = $countQuizzes = $countScore = 0;
        if (!isset($courseCode)) {
            if (isset($sessionId)) {
                $this->_coursesReportingModel->setSessionId($sessionId);
            }
            $courses = $this->_coursesReportingModel->getCourses();
            if (!empty($courses)) {
                foreach ($courses as $course) {                    
                    if (isset($type)) {
                        if ($type == 'quiz') {
                            $rows = $this->getQuizzes($course['code'], $sessionId);
                        }
                        else {
                            $rows = $this->getExams($course['code'], $sessionId);
                        }
                    }
                    else {
                        $quizzes = $this->getQuizzes($course['code'], $sessionId);
                        $exams = $this->getExams($course['code'], $sessionId);
                        $rows = array_merge($quizzes, $exams);
                    }
                    if (!empty($rows)) {
                        foreach ($rows as $row) {                               
                            $quizzesAndExamsAvgScore = $this->getCourseQuizzesAndExamsAvgScore($course['code'], $sessionId, array($userId => $userInfo));
                            if ($quizzesAndExamsAvgScore !== false) {
                                $score += $quizzesAndExamsAvgScore;
                                $countScore++;
                            }                          
                            $countQuizzes++;
                        }
                    }                    
                    $countCourses++;
                }                
            }
        }
        else {
            $countCourses = 1;
            $countQuizzes = 0;            
            if (isset($type)) {
                if ($type == 'quiz') {
                    $rows = $this->getQuizzes($courseCode, $sessionId);
                }
                else {
                    $rows = $this->getExams($courseCode, $sessionId);
                }
            }
            else {
                $quizzes = $this->getQuizzes($courseCode, $sessionId);
                $exams = $this->getExams($courseCode, $sessionId);
                $rows = array_merge($quizzes, $exams);
            }   
            if (!empty($rows)) {                        
                foreach ($rows as $row) {
                    $quizzesAndExamsAvgScore = $this->getCourseQuizzesAndExamsAvgScore($courseCode, $sessionId, array($userId => $userInfo));
                    if ($quizzesAndExamsAvgScore !== false) {
                        $score += $quizzesAndExamsAvgScore;
                        $countScore++;
                    }                         
                    $countQuizzes++;
                }
            }            
        }
        if ($countQuizzes > 0) {              
            if ($countScore > 0) {
                $result = round(($score / $countQuizzes) / $countCourses);
            }
        }
        return $result;
    }
    
    public function getQuizOrExamUsers($courseCode, $id, $mode, $sessionId = null, $txtSearch = '') {
        $result = array();
        $this->_learnersReportingModel->setUserId($this->_trainerId);
        $learners = $this->_learnersReportingModel->getCourseLearners($courseCode, $sessionId, null, $txtSearch);
        if (!empty($learners)) {
            foreach ($learners as $uid => $learner) {
                $nbAttempts = 0;
                if ($mode == 'exam') {
                    $scoreAndTime = $this->getUserExamLastScoreAndTime($courseCode, $uid, $sessionId, array($id => $mode));
                    $nbAttempts = $this->getUserExamNbAttempts($courseCode, $uid, $sessionId, array($id => $mode));
                }
                else {
                    $scoreAndTime = $this->getUserQuizMaxScoreAndTime($courseCode, $uid, $sessionId, array($id => $mode));
                    $nbAttempts = $this->getUserQuizNbAttempts($courseCode, $uid, $sessionId, array($id => $mode));
                }
                //if ($nbAttempts <= 0) { continue; }
                //if (!$this->isQuizInRanking($scoreAndTime['score'])) { continue; }
                $result[$learner['user_id']]['lastname'] = $learner['lastname'];
                $result[$learner['user_id']]['firstname'] = $learner['firstname'];
                $result[$learner['user_id']]['score'] = $scoreAndTime['score'];
                $result[$learner['user_id']]['time'] = $scoreAndTime['time'];
                $result[$learner['user_id']]['attempts'] = $nbAttempts;
                $result[$learner['user_id']]['attempt_id'] = $scoreAndTime['attempt_id'];
                $result[$learner['user_id']]['quiz_id'] = $scoreAndTime['quiz_id'];
            }
        }
        return $result;
    }
    
    public function updateQuizTrackScores($exeId, $exeResult, $exeWeighting) {
        $this->_ado->Execute("UPDATE {$this->_tblTrackExercise} SET exe_result = ?, exe_weighting = ? WHERE exe_id = ?", array($exeResult, $exeWeighting, $exeId));
    }
    
    public function setTrainerId($trainerId) {
        $this->_trainerId = $trainerId;
    }
    
    public function setCourseTables($courseCode) {
        $courseInfo = api_get_course_info($courseCode);       
        $this->_tblQuiz = Database :: get_course_table(TABLE_QUIZ_TEST, $courseInfo['dbName']);
        $this->_tblExam = Database :: get_course_table(TABLE_EXAM, $courseInfo['dbName']);
        $this->_tblExamUser = Database :: get_course_table(TABLE_EXAM_USER, $courseInfo['dbName']);
        
    }
    
    public function setMainTables() {
        $this->_tblUser = Database::get_main_table(TABLE_MAIN_USER);
    }
    
    public function setStatsTables() {
        $this->_tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
        $this->_tblTrackAttempt  = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
    }
    
}