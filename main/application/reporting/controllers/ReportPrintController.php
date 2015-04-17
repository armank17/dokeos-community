<?php
class application_reporting_controllers_ReportPrint  extends application_reporting_controllers_Report
{    
    public function __construct() {
        parent::__construct();
        $this->setReducedHeader(); 
        $this->disabledFooterCore();
    }
    
    public function sessions() {        
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchSession = urldecode($searchText); 
        $this->setSessions();        
        $this->txtSearchDefault = $this->txtSearchSession;
        $this->setSessionsAverageValues(false);
    }
    
    public function courses() {        
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchCourse = urldecode($searchText); 
        $this->setCourses();        
        $this->txtSearchDefault = $this->txtSearchCourse;
        $this->setCoursesAverageValues(false);
    }
    
    public function modules() {
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchModule = urldecode($searchText);        
        $this->txtSearchDefault = $this->txtSearchModule;
        $this->setModulesAverageValues(false);
    }
    
    public function quizzes() {        
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchQuiz = urldecode($searchText);        
        $this->txtSearchDefault = $this->txtSearchQuiz;
        $this->setQuizzes($this->selectedQuizTypeFilter);
        $this->setQuizzesAverageValues(false);
    }
    
    public function facetoface() {
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchFace2face = urldecode($searchText);
        $this->txtSearchDefault = $this->txtSearchFace2face;       
        $this->setFace2FaceAverageValues(false);
    }
    
    public function learners() {
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchLearner = urldecode($searchText);
        $this->txtSearchDefault = $this->txtSearchLearner;
        $this->setLearnersAverageValues(false);
    }
    
    public function moduleUsers() {
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchModuleUsers = urldecode($searchText);
        $this->txtSearchDefault = $this->txtSearchModuleUsers;
        $lpId = $this->getRequest()->getProperty('lpId', '');
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $this->setModuleUsersAverageValues($courseCode, $lpId, false);
    }
    
    public function quizUsers() {
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchQuizUsers = urldecode($searchText);
        $this->txtSearchDefault = $this->txtSearchQuizUsers;
        
        $id = $this->getRequest()->getProperty('id', '');
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $mode = $this->getRequest()->getProperty('mode', '');
        $this->setQuizUsersAverageValues($courseCode, $id, $mode, false);
        
    }
    
    public function facetofaceUsers() {
        $searchText = $this->getRequest()->getProperty('searchText', '');
        $this->txtSearchFace2faceUsers = urldecode($searchText);
        $this->txtSearchDefault = $this->txtSearchFace2faceUsers;        
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $face2faceId = $this->getRequest()->getProperty('face2faceId', '');
        $type = $this->getRequest()->getProperty('type', '');        
        $this->setFace2FaceUsersAverageValues($courseCode, $face2faceId, $type, false);
    }
    
    public function userModuleDetail() {       
        $lpId = $this->getRequest()->getProperty('lpId', '');
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $learnerId = $this->getRequest()->getProperty('learnerId', '');
        $sessionId = $this->getRequest()->getProperty('sessionId', null);          
        $this->setModuleUserDetailValues($courseCode, $lpId, $learnerId, $sessionId);    
    }
    
    public function userQuizResult() {
        $quizId = $this->getRequest()->getProperty('quizId', '');
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $learnerId = $this->getRequest()->getProperty('learnerId', '');
        $sessionId = $this->getRequest()->getProperty('sessionId', null);
        
        $mode = $this->getRequest()->getProperty('mode', '');
        $attemptId = $this->getRequest()->getProperty('attempt_id', '');        
        $exeExoId = $this->getRequest()->getProperty('exeExoId', '');
        
        $this->selectedModuleId = $this->getRequest()->getProperty('lpId', '');        
        $this->selectedModuleCourse = $courseCode;
        $this->selectedModuleLearner = $learnerId;
        $this->selectedModuleSession = $sessionId;
        $this->setUserQuizResult($attemptId, $courseCode, $quizId, $learnerId, $mode, $sessionId, $exeExoId);
    }
    
    public function learnerDetail() {              
        $learnerId = $this->getRequest()->getProperty('learnerId', '');                        
        $this->setLearnerDetailValues($learnerId);
    }
    
    public function learnerAccessDetails() {
        $learnerId = $this->getRequest()->getProperty('learnerId', '');                
        $this->setLearnerAccessDetailValues($learnerId);
    }
    
}
