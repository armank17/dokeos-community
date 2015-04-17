<?php
class application_reporting_controllers_ReportAjax  extends application_reporting_controllers_Report
{
    
    public function __construct() {
        parent::__construct();
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
    }
    
    public function displaySessionsTab() {
        $this->isEmpty = empty($this->queryFilters) && !$this->isFiltered;      
        $this->currentTab = 'sessions';
        $this->searchPlaceHolder = $this->get_lang('SearchBySessionName');
        $this->printPage = $this->getRequest()->getProperty('action', '');
        $this->txtSearchDefault = $this->txtSearchSession;        
        $this->setSessionsAverageValues();
        echo $this->setTemplate('sessions_tab', 'Report');
        exit;
    }
    
    public function displayCoursesTab() {        
        $this->isEmpty = empty($this->queryFilters) && !$this->isFiltered;      
        $this->currentTab = 'courses';
        $this->searchPlaceHolder = $this->get_lang('SearchByCourseTitle');
        $this->printPage = $this->getRequest()->getProperty('action', '');
        $this->txtSearchDefault = $this->txtSearchCourse;        
        $this->setCoursesAverageValues();
        echo $this->setTemplate('courses_tab', 'Report');
        exit;
    }
    
    public function displayQuizzesTab() {
        $this->isEmpty = empty($this->queryFilters) && !$this->isFiltered;
        $this->currentTab = 'quizzes';
        $this->searchPlaceHolder = $this->get_lang('SearchByQuizOrExamName');
        $this->printPage = $this->getRequest()->getProperty('action', '');
        $this->txtSearchDefault = $this->txtSearchQuiz;
        $this->setQuizzes($this->selectedQuizTypeFilter);
        $this->setQuizzesAverageValues();
        echo $this->setTemplate('quizzes_tab', 'Report');
        exit;
    }
    
    public function displayModulesTab() {
        $this->isEmpty = empty($this->queryFilters) && !$this->isFiltered;
        $this->currentTab = 'modules';
        $this->searchPlaceHolder = $this->get_lang('SearchByModuleName');
        $this->printPage = $this->getRequest()->getProperty('action', '');
        $this->txtSearchDefault = $this->txtSearchModule;
        $this->setModulesAverageValues();
        echo $this->setTemplate('modules_tab', 'Report');
        exit;
    }
    
    public function displayLearnersTab() {
        $this->isEmpty = empty($this->queryFilters) && !$this->isFiltered;
        $this->currentTab = 'learners';
        $this->searchPlaceHolder = $this->get_lang('SearchByLearnerFirtnameOrLastname');
        $this->printPage = $this->getRequest()->getProperty('action', '');
        $this->txtSearchDefault = $this->txtSearchLearner;
        $this->setLearnersAverageValues();
        echo $this->setTemplate('learners_tab', 'Report');
        exit;
    }
    
    public function displayModuleUsers() {
        $this->currentTab = 'module_users';
        $this->searchPlaceHolder = $this->get_lang('SearchByLearnerFirtnameOrLastname');
        $this->txtSearchDefault = $this->txtSearchModuleUsers;
        $lpId = $this->getRequest()->getProperty('lpId', '');
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $this->setModuleUsersAverageValues($courseCode, $lpId);
        echo $this->setTemplate('module_users', 'Report');
        exit;
    }
    
    public function displayModuleUserDetail() {        
        $this->currentTab = $this->getRequest()->getProperty('currentTab', '');;        
        $lpId = $this->getRequest()->getProperty('lpId', '');
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $learnerId = $this->getRequest()->getProperty('learnerId', '');
        $sessionId = $this->getRequest()->getProperty('sessionId', null);  
        $this->setModuleUserDetailValues($courseCode, $lpId, $learnerId, $sessionId);                
        echo $this->setTemplate('user_module_detail', 'Report');
        exit;
    }
    
    public function displayUserQuizResult() {
        $this->currentTab = $this->getRequest()->getProperty('currentTab', '');;        
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
        $this->selectedModuleItemId = $this->getRequest()->getProperty('lpItemId', '');
        $this->selectedModuleViewId = $this->getRequest()->getProperty('lpViewId', '');        
        
        $this->setUserQuizResult($attemptId, $courseCode, $quizId, $learnerId, $mode, $sessionId, $exeExoId);
        echo $this->setTemplate('user_quiz_result', 'Report');
        exit;
    }
    
    public function displayLearnerDetail() {
        $this->currentTab = 'user_detail';                
        $learnerId = $this->getRequest()->getProperty('learnerId', '');                        
        $this->setLearnerDetailValues($learnerId);        
        echo $this->setTemplate('learner_detail', 'Report');
        exit;
    }
    
    public function displayLearnerAccessDetail() {
        $learnerId = $this->getRequest()->getProperty('learnerId', '');                
        $this->setLearnerAccessDetailValues($learnerId);        
        echo $this->setTemplate('learner_access_details', 'Report');
        exit;
    }
    
    public function displayCourseModules() {
        $this->currentTab = 'modules';                
        $cbo_course = $this->getRequest()->getProperty('selectedCourse', '');
        $this->selectedCourseFilter   = !empty($cbo_course)?$cbo_course:null;
        $this->getSession()->setProperty('selectedCourse', $this->selectedCourseFilter);        
        $this->setModulesAverageValues();        
        echo $this->setTemplate('modules_tab', 'Report');
        exit;
    }
    
    public function displaySessionCourses() {
        $this->currentTab = 'courses';        
        $cbo_category = $this->getRequest()->getProperty('selectedCategory', '');
        $cbo_session = $this->getRequest()->getProperty('selectedSession', '');        
        $this->selectedCategoryFilter  = !empty($cbo_category)?intval($cbo_category):null;
        $this->selectedSessionFilter  = !empty($cbo_session)?intval($cbo_session):null;        
        $this->getSession()->setProperty('selectedCategory', $this->selectedCategoryFilter);
        $this->getSession()->setProperty('selectedSession', $this->selectedSessionFilter);        
        $this->setCoursesAverageValues();        
        echo $this->setTemplate('courses_tab', 'Report');
        exit;
    }
    
    public function displayQuizUsers() {
        $this->currentTab = 'quiz_users';
        $this->searchPlaceHolder = $this->get_lang('SearchByLearnerFirtnameOrLastname');
        $this->txtSearchDefault = $this->txtSearchQuizUsers;        
        $id = $this->getRequest()->getProperty('id', '');
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $mode = $this->getRequest()->getProperty('mode', '');
        $this->setQuizUsersAverageValues($courseCode, $id, $mode);
        echo $this->setTemplate('quiz_users', 'Report');
        exit;
    }
    
    public function displayFace2FaceTab() {
        $this->isEmpty = empty($this->queryFilters) && !$this->isFiltered;
        $this->currentTab = 'facetoface';
        $this->searchPlaceHolder = $this->get_lang('SearchByActivityName');
        $this->txtSearchDefault = $this->txtSearchFace2face;
        $this->printPage = $this->getRequest()->getProperty('action', '');        
        $this->setFace2FaceAverageValues();        
        echo $this->setTemplate('facetoface_tab', 'Report');
        exit;
    }
    
    public function displayFace2FaceUsers() {
        $this->currentTab = 'facetoface_users';       
        $this->searchPlaceHolder = $this->get_lang('SearchByLearnerFirtnameOrLastname');
        $this->txtSearchDefault = $this->txtSearchFace2faceUsers;        
        $courseCode = $this->getRequest()->getProperty('courseCode', '');
        $face2faceId = $this->getRequest()->getProperty('face2faceId', '');
        $type = $this->getRequest()->getProperty('type', '');        
        $this->setFace2FaceUsersAverageValues($courseCode, $face2faceId, $type);        
        echo $this->setTemplate('facetoface_users', 'Report');
        exit;
    }
            
    public function changeCboFilters() {        
        $type = $this->getRequest()->getProperty('type', '');
        $cbo_category = $this->getRequest()->getProperty('selectedCategory', '');
        $cbo_session = $this->getRequest()->getProperty('selectedSession', '');
        $cbo_course = $this->getRequest()->getProperty('selectedCourse', '');
        
        $cbo_trainer = $this->getRequest()->getProperty('selectedTrainer', '');
        
        $this->currentTab = $this->getRequest()->getProperty('currentTab', '');
        switch ($this->currentTab) {
            case 'sessions':
                $this->searchPlaceHolder = $this->get_lang('SearchBySessionName');                
                break;
            case 'courses':
                $this->searchPlaceHolder = $this->get_lang('SearchByCourseTitle');                
                break;
            case 'modules':
                $this->searchPlaceHolder = $this->get_lang('SearchByModuleName');       
                break;
            case 'quizzes':
                $this->searchPlaceHolder = $this->get_lang('SearchByQuizOrExamName');
                $cbo_quiz = $this->getRequest()->getProperty('selectedQuiz', '');
                $cbo_quiz_type = $this->getRequest()->getProperty('selectedQuizType', '');
                break;
            case 'learners':
                $this->searchPlaceHolder = $this->get_lang('SearchByLearnerFirtnameOrLastname');
                $cbo_active_learner = $this->getRequest()->getProperty('selectedActiveLearner', '');            
                $cbo_quiz_ranking = $this->getRequest()->getProperty('selectedQuizRanking', ''); 
                break;
        }
        $this->txtSearchDefault = $this->txtSearchCourse;       
        if ($this->isPlatformAdmin) {
            $this->selectedTrainerFilter  = !empty($cbo_trainer)?intval($cbo_trainer):0;
        }
        else {
            $this->selectedTrainerFilter  = $this->currentUser;
        }
        $this->selectedCategoryFilter  = !empty($cbo_category)?intval($cbo_category):null;
        $this->selectedSessionFilter  = !empty($cbo_session)?intval($cbo_session):null;
        $this->selectedCourseFilter   = !empty($cbo_course)?strip_tags($cbo_course):null;
        $this->selectedQuizFilter     = !empty($cbo_quiz)?strip_tags($cbo_quiz):null;
        $this->selectedQuizTypeFilter = !empty($cbo_quiz_type)?intval($cbo_quiz_type):null;
        $this->selectedActiveLearnerFilter = !empty($cbo_active_learner)?intval($cbo_active_learner):null;
        $this->selectedQuizRankingFilter = !empty($cbo_quiz_ranking)?strip_tags($cbo_quiz_ranking):null;
        
        $this->setCboFilters($type);
        echo $this->setTemplate('search_form', 'Report');
        exit;
    }
    
    public function displayRowsByPage() {
        $currentTab = $this->getRequest()->getProperty('currentTab', '');
        if ($currentTab == 'module_users') {
            $this->displayModuleUsers();
        }
        else if ($currentTab == 'quiz_users') {
            $this->displayQuizUsers();
        }
        else {
            $this->submitSearch();
        }
        
    }
    
    public function submitSearch() {
        extract($_POST);     
        $this->isFiltered = true;
        if ($this->isPlatformAdmin) {
            $this->selectedTrainerFilter  = !empty($cbo_trainer)?intval($cbo_trainer):0;
        }
        else {
            $this->selectedTrainerFilter  = $this->currentUser;
        }               
        $this->selectedCategoryFilter  = !empty($cbo_category)?intval($cbo_category):null;
        $this->selectedSessionFilter  = !empty($cbo_session)?intval($cbo_session):null;
        $this->selectedCourseFilter   = !empty($cbo_course)?strip_tags($cbo_course):null;
        $this->selectedQuizFilter     = !empty($cbo_quiz)?strip_tags($cbo_quiz):null;
        $this->selectedQuizTypeFilter = !empty($cbo_quiz_type)?intval($cbo_quiz_type):null;
        $this->selectedActiveLearnerFilter = !empty($cbo_active_learner)?intval($cbo_active_learner):null;
        $this->selectedQuizRankingFilter = !empty($cbo_quiz_ranking)?strip_tags($cbo_quiz_ranking):null;
        
        $this->getSession()->setProperty('selectedCategory', $this->selectedCategoryFilter);
        $this->getSession()->setProperty('selectedSession', $this->selectedSessionFilter);
        $this->getSession()->setProperty('selectedCourse', $this->selectedCourseFilter);
        $this->getSession()->setProperty('selectedTrainer', $this->selectedTrainerFilter);
        if ($currentTab == 'quizzes') {
            $this->getSession()->setProperty('selectedQuiz', $this->selectedQuizFilter);
            $this->getSession()->setProperty('selectedQuizType', $this->selectedQuizTypeFilter);
        }
        if ($currentTab == 'learners') {
            $this->getSession()->setProperty('selectedActiveLearner', $this->selectedActiveLearnerFilter);
            $this->getSession()->setProperty('selectedQuizRanking', $this->selectedQuizRankingFilter);
        }
        $this->setCategories();
        $this->setSessions();
        $this->setCourses();        
        $this->setQuizzes($this->selectedQuizTypeFilter);
        $this->setQueryFilters();          
        
        if ($currentTab) {            
            if (mb_detect_encoding($filter_search) == 'UTF-8') {
                $filter_search = api_utf8_decode($filter_search);
            }  
            switch ($currentTab) {
                case 'sessions':
                    $this->txtSearchSession = strip_tags($filter_search); 
                    $this->setSessions();
                    $this->displaySessionsTab();
                    break;
                case 'courses':
                    $this->txtSearchCourse = strip_tags($filter_search); 
                    $this->setCourses();
                    $this->displayCoursesTab();
                    break;
                case 'modules':
                    $this->txtSearchModule = strip_tags($filter_search);
                    $this->displayModulesTab();
                    break;
                case 'quizzes':
                    $this->txtSearchQuiz = strip_tags($filter_search);
                    $this->displayQuizzesTab();
                    break;
                case 'quiz_users':
                    $this->txtSearchQuizUsers = strip_tags($filter_search);
                    $this->displayQuizUsers();
                    break;
                case 'module_users':
                    $this->txtSearchModuleUsers = strip_tags($filter_search);
                    $this->displayModuleUsers();
                    break;
                case 'learners':                    
                    $this->txtSearchLearner = strip_tags($filter_search);
                    $this->displayLearnersTab();
                    break;
                case 'facetoface':                    
                    $this->txtSearchFace2face = strip_tags($filter_search);
                    $this->displayFace2FaceTab();
                    break;
                case 'facetoface_users':                    
                    $this->txtSearchFace2faceUsers = strip_tags($filter_search);
                    $this->displayFace2FaceUsers();
                    break;
            }
        }
        exit;
    }
    
    public function submitFilters() {
        extract($_POST);        
        if ($this->isPlatformAdmin) {
            $this->selectedTrainerFilter  = !empty($cbo_trainer)?intval($cbo_trainer):0;
        }
        else {
            $this->selectedTrainerFilter  = $this->currentUser;
        }                        
        $this->selectedCategoryFilter  = !empty($cbo_category)?intval($cbo_category):null;
        $this->selectedSessionFilter  = !empty($cbo_session)?intval($cbo_session):null;
        $this->selectedCourseFilter   = !empty($cbo_course)?strip_tags($cbo_course):null;
        $this->selectedQuizFilter     = !empty($cbo_quiz)?strip_tags($cbo_quiz):null;
        $this->selectedQuizTypeFilter = !empty($cbo_quiz_type)?intval($cbo_quiz_type):null;
        $this->selectedActiveLearnerFilter = !empty($cbo_active_learner)?intval($cbo_active_learner):null;
        $this->selectedQuizRankingFilter = !empty($cbo_quiz_ranking)?strip_tags($cbo_quiz_ranking):null;
        $this->setCategories();
        $this->setSessions();
        $this->setCourses();        
        $this->setQuizzes($this->selectedQuizTypeFilter);
        $this->getSession()->setProperty('selectedCategory', $this->selectedCategoryFilter);
        $this->getSession()->setProperty('selectedSession', $this->selectedSessionFilter);
        $this->getSession()->setProperty('selectedCourse', $this->selectedCourseFilter);
        $this->getSession()->setProperty('selectedTrainer', $this->selectedTrainerFilter);
        if ($currentTab == 'quizzes') {
            $this->getSession()->setProperty('selectedQuiz', $this->selectedQuizFilter);
            $this->getSession()->setProperty('selectedQuizType', $this->selectedQuizTypeFilter);
        }
        if ($currentTab == 'learners') {
            $this->getSession()->setProperty('selectedActiveLearner', $this->selectedActiveLearnerFilter);
            $this->getSession()->setProperty('selectedQuizRanking', $this->selectedQuizRankingFilter);
        }
        $this->setQueryFilters();      
        if ($currentTab) {
            switch ($currentTab) {
                case 'sessions':
                    $this->displaySessionsTab();
                    break;
                case 'courses':
                    $this->displayCoursesTab();
                    break;
                case 'modules':
                    $this->displayModulesTab();
                    break;
                case 'quizzes':
                    $this->displayQuizzesTab();
                    break;
                case 'learners':
                    $this->displayLearnersTab();
                    break;
            }
        }
        exit;
    }
    
    public function resetFilters() {
        $this->isFiltered = false;
        $this->unsetFilters();
        if ($this->isPlatformAdmin) {
            $this->setTrainers();
            $this->selectedTrainerFilter = 0;
        }
        else {
            $this->selectedTrainerFilter = $this->currentUser;
        }
        $this->setSessions();
        $this->setCourses();
        $this->currentTab = $this->getRequest()->getProperty('currentTab', '');
        if ($this->currentTab) {
            switch ($this->currentTab) {
                case 'sessions':
                    $this->displaySessionsTab();
                    break;
                case 'courses':
                    $this->displayCoursesTab();
                    break;
                case 'modules':
                    $this->displayModulesTab();
                    break;
                case 'quizzes':
                    $this->displayQuizzesTab();
                    break;
                case 'learners':
                    $this->displayLearnersTab();
                case 'facetoface':
                    $this->displayFace2FaceTab();                    
                    break;
            }
        }
        exit;
    }
    
}