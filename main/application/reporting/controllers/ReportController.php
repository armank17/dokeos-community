<?php
/**
 * controller default in reporting
 * @package Reporting
 */

class application_reporting_controllers_Report extends appcore_command_Command 
{    
    const SELFLEARNING_QUIZ_MODE = 1;
    const EXAM_QUIZ_MODE = 2;
    const ACTIVE_LEARNER = 1;
    const INACTIVE_LEARNER = 2;
    
    const UNIQUE_ANSWER = 1;
    const MULTIPLE_ANSWER = 2;
    const FILL_IN_BLANKS = 3;
    const MATCHING = 4;
    const FREE_ANSWER = 5;
    const HOT_SPOT = 6;
    const HOT_SPOT_ORDER = 7;
    const REASONING = 8;
    const HOT_SPOT_DELINEATION = 9;
    
    public $trainers;    
    public $sessions;
    public $categories;
    public $courses;
    public $quizzes;
    
    public $learnersReportingModel;
    public $coursesReportingModel;
    public $modulesReportingModel;
    public $quizzesReportingModel;
    public $usersReportingModel;    
    public $sessionsReportingModel;
    public $face2faceReportingModel;
    
    public $sessionsData;
    public $coursesData;
    public $modulesData;
    public $quizData;
    public $learnersData;    
    public $moduleUsersData;
    public $moduleUserDetailData;
    public $quizUsersData;
    public $learnerGlobalData;    
    public $face2faceData;
    public $face2faceUsersData;
    public $learnerAccessData;
    
    public $courseInfo;
    public $leanerInfo;
    public $moduleInfo;
    public $sessionInfo;
    public $quizInfo;
    public $quizMode;
    public $face2faceInfo;

    public $currentTab;
    public $searchPlaceHolder;
    public $stylesheet;
    
    public $isPlatformAdmin;
    public $isCourseManager;
    public $currentUser;
    public $currentUserInfo;
    
    public $selectedCategoryFilter = null;
    public $selectedSessionFilter = null;
    public $selectedCourseFilter = null;
    public $selectedTrainerFilter = null;
    public $selectedQuizFilter = null;
    public $selectedQuizTypeFilter = null;    
    public $selectedActiveLearnerFilter = null;
    public $selectedQuizRankingFilter = null;
    
    public $categoriesCbo = array();
    public $sessionsCbo = array();
    public $coursesCbo = array();
    public $trainersCbo = array();
    public $quizzesCbo = array();
    public $queryFilters = array();
        
    public $txtSearchSession;
    public $txtSearchCourse;
    public $txtSearchModule;
    public $txtSearchQuiz;
    public $txtSearchLearner;
    public $txtSearchModuleUsers;
    public $txtSearchQuizUsers;
    public $txtSearchFace2face;
    public $txtSearchFace2faceUsers;
    public $txtSearchDefault;
    
    public $paginator;
    public $limit;
    public $page;
    
    public $selectedModuleCourse;
    public $selectedModuleLearner;
    public $selectedModuleId;
    public $selectedModuleItemId;
    public $selectedModuleViewId;
    public $selectedModuleSession;
    public $selectedQuizCourse;
    public $selectedQuizLearner;        
    public $selectedQuizMode;        
    public $selectedQuizId;
    public $selectedQuizAttemptId;
    public $selectedQuizExeExoId;
    public $selectedSessionId;
    public $selectedFace2FaceCourse;
    public $selectedFace2FaceId;
    public $selectedFace2FaceType;
    
    public $objExercise;
    public $quizResultContent;
    public $totalScore;
    public $totalWeighting;
    
    public $themeColor;
    public $stylesheets;
    public $chartValues;
    public $printPage;
    public $graphType;
    public $graphImg;
    
    public $isFiltered = false;
    public $isEmpty = true;
    public $rankingValues = array('100-91', '90-81', '80-71', '70-61', '60-51', '50-41', '40-31', '30-21', '20-11', '10-0');
    public $quizModes = array('quiz' => 1, 'exam' => 2);
    public $moduleStatusLangVariables = array('completed' => 'ScormCompstatus', 'incomplete' => 'ScormIncomplete', 'failed' => 'ScormFailed', 'passed' => 'ScormPassed', 'browsed' => 'ScormBrowsed', 'not attempted' => 'ScormNotAttempted');    
    public $userStatusLangVariables = array(0 => 'Inactive', 1 => 'Active');
    public $face2faceTypes = array(1 => 'PassFail2', 2 => 'Scored');
    
    public function __construct() {
        $this->validateSession();
        //$this->setTheme('responsive');
        $this->loadHtmlHeadXtra();
        $this->setLanguageFile(array('course_home', 'admin', 'notebook', 'scorm', 'tracking'));
        $this->stylesheets = api_get_setting('stylesheets');
        $GLOBALS['platform_theme'] = $this->stylesheets;
	$this->setThemeColor();
        $this->setPageSection('session_my_space');
        
        $this->isPlatformAdmin = api_is_platform_admin();
        $this->isCourseManager = api_is_allowed_to_create_course();
        $this->currentUser = api_get_user_id();
        $this->currentUserInfo = api_get_user_info($this->currentUser);
        
        $this->learnersReportingModel = new application_reporting_models_LearnersReportingModel();
        $this->coursesReportingModel  = new application_reporting_models_CoursesReportingModel();
        $this->modulesReportingModel  = new application_reporting_models_ModulesReportingModel();        
        $this->quizzesReportingModel  = new application_reporting_models_QuizzesReportingModel();               
        $this->sessionsReportingModel = new application_reporting_models_SessionsReportingModel();
        $this->face2faceReportingModel = new application_reporting_models_Face2FaceReportingModel();
                             
        $this->stylesheet = api_get_setting('stylesheets');                
       
        if (empty($this->selectedTrainerFilter) && !api_is_allowed_to_edit()) {
            $this->selectedTrainerFilter = $this->currentUser;
        }

        if ($this->isPlatformAdmin) {
            $this->setTrainers();
        }

        $this->setQueryFilters();
        $this->setCategories();
        $this->setSessions();
        $this->setCourses();           
        $this->paginator = new appcore_library_pagination_Pagination();
        $this->paginator->url_info['base_url'] = api_get_path(WEB_CODE_PATH).'index.php';
        $this->paginator->url_info['url_query'] = array('module' => 'reporting', 'cmd' => 'ReportAjax', 'func' => 'displayRowsByPage');
    }
   
    public function index() {
        $this->isEmpty = empty($this->queryFilters) && !$this->isFiltered;
        $this->currentTab = 'sessions';
        $this->searchPlaceHolder = $this->get_lang('SearchBySessionName');
        $this->txtSearchDefault = $this->txtSearchSession;
        $this->printPage = $this->getRequest()->getProperty('action', '');     
        $this->setSessionsAverageValues();
    }

    public function learner() {
        $this->currentTab = 'learner_reporting';                
        $learnerId = $this->getRequest()->getProperty('learnerId', '');                        
        $this->setLearnerDetailValues($learnerId);
    }
    
    public function export() {        
        $type = $this->getRequest()->getProperty('type', '');
        $data = array();
        switch ($type) {
            case 'sessions':
                $head = array($this->get_lang('SessionName'), $this->get_lang('Learners'), $this->get_lang('Courses'), $this->get_lang('ModulesTime'), $this->get_lang('ModulesProgress'), $this->get_lang('ModulesScore'), $this->get_lang('QuizzesScore'));
                $this->setSessionsAverageValues(false);
                $data = $this->sessionsData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_sessions');
                break;
            case 'courses':
                $head = array($this->get_lang('Course'), $this->get_lang('Learners'), $this->get_lang('ModulesTime'), $this->get_lang('ModulesProgress'), $this->get_lang('ModulesScore'), $this->get_lang('QuizzesScore'));
                $this->setCoursesAverageValues(false);
                $data = $this->coursesData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_courses');
                break;
            case 'modules':
                $head = array($this->get_lang('Modules'), $this->get_lang('ReportInCourse'), $this->get_lang('ScormTime'), $this->get_lang('Progress'), $this->get_lang('ReportScore'));
                $this->setModulesAverageValues(false);
                $data = $this->modulesData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_modules');
                break;
            case 'quizzes':
                $head = array($this->get_lang('Quiz'), $this->get_lang('Type'), $this->get_lang('ReportInCourse'), $this->get_lang('AverageScore'), $this->get_lang('Highest'), $this->get_lang('Lowest'), $this->get_lang('Participation'), $this->get_lang('AverageTime'));
                $this->setQuizzes($this->selectedQuizTypeFilter, $this->selectedSessionFilter);
                $this->setQuizzesAverageValues(false);
                $data = $this->quizData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_quizzes');
                break;
            case 'learners':
                $head = array($this->get_lang('LastName'), $this->get_lang('FirstName'), $this->get_lang('LatestConnection'), $this->get_lang('ModulesTime'), $this->get_lang('ModulesProgress'), $this->get_lang('ModulesScore'), $this->get_lang('QuizzesScore'));
                $this->setLearnersAverageValues(false);
                $data = $this->learnersData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_learners');
                break;
            case 'module_users':
                $head = array($this->get_lang('LastName'), $this->get_lang('FirstName'), $this->get_lang('ScormTime'), $this->get_lang('Progress'), $this->get_lang('ReportScore'));
                $lpId = $this->getRequest()->getProperty('lpId', '');
                $courseCode = $this->getRequest()->getProperty('courseCode', '');
                $this->setModuleUsersAverageValues($courseCode, $lpId, false);
                $data = $this->moduleUsersData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_module_users');
                break;
            case 'quiz_users':
                $head = array($this->get_lang('LastName'), $this->get_lang('FirstName'), $this->get_lang('Score'), $this->get_lang('ScormTime'), $this->get_lang('Attempts'));
                $id = $this->getRequest()->getProperty('quizId', '');
                $courseCode = $this->getRequest()->getProperty('courseCode', '');
                $mode = $this->getRequest()->getProperty('quizMode', '');
                $this->setQuizUsersAverageValues($courseCode, $id, $mode, false);
                $data = $this->quizUsersData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_quiz_users');
                break;
            case 'facetoface':
                $head = array($this->get_lang('ReportInCourse'), $this->get_lang('SessionName'), $this->get_lang('ActivityName'), $this->get_lang('Type'), $this->get_lang('Participants'), $this->get_lang('Pass'), $this->get_lang('ReportScore'));
                $this->setFace2FaceAverageValues(false);
                $data = $this->face2faceData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_face2face');
                break;
            case 'facetoface_users':
                $head = array($this->get_lang('LastName'), $this->get_lang('FirstName'), $this->get_lang('Pass'));
                $courseCode = $this->getRequest()->getProperty('courseCode', '');
                $f2fType = $this->getRequest()->getProperty('f2fType', '');
                $face2faceId = $this->getRequest()->getProperty('face2faceId', '');
                if ($f2fType == 2) {
                  $head[] =  $this->get_lang('ReportScore');
                }
                else {
                  $head[] =  $this->get_lang('Comments');  
                }
                $this->setFace2FaceUsersAverageValues($courseCode, $face2faceId, $f2fType, false);
                $data = $this->face2faceUsersData;
                array_unshift($data, $head);
                Export::export_table_csv($data, 'export_face2face_users');
                break;
        }                        
        exit;
    }
         
    public function setLearnersAverageValues($withPagination = true) {
        $courseCode = !empty($this->selectedCourseFilter)?$this->selectedCourseFilter:null;
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;
        $activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null;

        $this->learnersReportingModel->setUserId($this->selectedTrainerFilter);  
        $this->quizzesReportingModel->setTrainerId($this->selectedTrainerFilter);       
       
        if (empty($sessionId) && !empty($this->selectedCategoryFilter)) {
            $sessionId = $this->sessionsReportingModel->getCategorySessionsId($this->selectedCategoryFilter);
        }
        
        $AllLearners = $this->learnersReportingModel->getAllLearners($activeLearner, $this->courses, $sessionId, $this->txtSearchLearner);                
        
        // sort learners alphabetically
        uasort($AllLearners, array($this, 'sortLearnersItems'));
        
        $learners = $AllLearners;
        if ($withPagination) {
            if (count($AllLearners) > 0) {
                $this->paginator->url_info['url_query']['currentTab'] = 'learners';
                $this->paginator->items_total = count($AllLearners);
                $this->paginator->paginate();
            }
            if (!empty($this->paginator->limit)) {
                $learners = array_slice($AllLearners, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }
        if (!empty($learners)) {
            foreach ($learners as $learner) {
                $quizzesScore = $this->quizzesReportingModel->getUserQuizzesScore($learner['user_id'], $courseCode, $sessionId);                
                if (!$this->isQuizInRanking($quizzesScore)) { continue; }
                if (isset($courseCode)) {
                    $lastestConnection = Tracking::get_last_connection_date_on_the_course($learner['user_id'], $courseCode);                    
                }
                else {
                    $lastestConnection = Tracking::get_last_connection_date($learner['user_id'], true);
                }
                $modulesData  = $this->modulesReportingModel->getUserModulesTotalTimeScoreAndProgress($learner['user_id'], $courseCode, $sessionId);               
                $this->learnersData[$learner['user_id']]['lastname'] = $learner['lastname'];
                $this->learnersData[$learner['user_id']]['firstname'] = $learner['firstname'];
                $this->learnersData[$learner['user_id']]['lastest_connection'] = !empty($lastestConnection)?strip_tags($lastestConnection):'n.a.';             
                $this->learnersData[$learner['user_id']]['modules_time'] = api_format_time($modulesData['total_time']);
                $this->learnersData[$learner['user_id']]['modules_progress'] = $modulesData['avg_progress'].' %';
                $this->learnersData[$learner['user_id']]['modules_score'] = $modulesData['avg_score'] !== false?$modulesData['avg_score'].' %':'n.a.';
                
                if (empty($modulesData['total_time'])) {
                    $this->learnersData[$learner['user_id']]['modules_time'] = 'n.a.';
                    $this->learnersData[$learner['user_id']]['modules_progress'] = 'n.a.';
                    $this->learnersData[$learner['user_id']]['modules_score'] = 'n.a.';
                }
                
                $this->learnersData[$learner['user_id']]['quizzes_score'] = $quizzesScore !== false?$quizzesScore.' %':'n.a.';  
                
                if ($withPagination) {
                    $this->learnersData[$learner['user_id']]['lastest_connection'] = !empty($lastestConnection)?$lastestConnection:'n.a.';
                }
                
            }
        }
    }
    
    public function setLearnerGlobalInfoValues($learnerId, $courseCode, $sessionId) {
        $this->learnerInfo = api_get_user_info($learnerId);                 
        $userPictureInfo = UserManager::get_user_picture_path_by_id($learnerId, 'none', false, true);        
        $userPictureSysPath = api_get_path(SYS_CODE_PATH).$userPictureInfo['dir'].$userPictureInfo['file'];
        $userPictureWebPath = api_get_path(WEB_CODE_PATH).$userPictureInfo['dir'].$userPictureInfo['file'];
        $userPictureAttributes = array();
        if (file_exists($userPictureSysPath)) {
            list($userPictureAttributes['width'], $userPictureAttributes['height'], $userPictureAttributes['type'], $userPictureAttributes['attr']) = getimagesize($userPictureSysPath);
        }
        $this->learnerInfo['picture_info'] = array('syspath' => $userPictureSysPath, 'webpath' => $userPictureWebPath, 'attributes' => $userPictureAttributes);

        $firstConnection = Tracking::get_first_connection_date($learnerId);
        $lastConnection = Tracking::get_last_connection_date($learnerId, true);
        
        $this->learnerGlobalData['first_connection'] = !empty($firstConnection)?$firstConnection:'n.a.';
        $this->learnerGlobalData['last_connection'] = !empty($lastConnection)?$lastConnection:'n.a.';
        
        $modulesData  = $this->modulesReportingModel->getUserModulesTotalTimeScoreAndProgress($learnerId, $courseCode, $sessionId);
        $this->learnerGlobalData['modules_time'] = api_format_time($modulesData['total_time']);
        $this->learnerGlobalData['modules_progress'] = $modulesData['avg_progress'].' %';
        $this->learnerGlobalData['modules_score'] = $modulesData['avg_score'] !== false?$modulesData['avg_score'].' %':'n.a.';
        
        $this->quizzesReportingModel->setTrainerId($this->selectedTrainerFilter);
        $quizzesScore = $this->quizzesReportingModel->getUserQuizzesScore($learnerId, $courseCode, $sessionId);
        $this->learnerGlobalData['quizzes_score'] = $quizzesScore !== false?$quizzesScore.' %':'n.a.';
    }
   
    public function setLearnerAccessDetailValues($learnerId) {
                
        $this->learnerInfo = api_get_user_info($learnerId);
        
        $this->learnerAccessData = $this->learnersReportingModel->getUserPlatformConnection($learnerId);
        
    }
    
    public function setLearnerDetailValues($learnerId) {
        $courseCode = !empty($this->selectedCourseFilter)?$this->selectedCourseFilter:null;
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;
       
        $this->setLearnerGlobalInfoValues($learnerId, $courseCode, $sessionId);
          
        // get courses without session for student and selected trainer                
        $learnerCourses = $this->coursesReportingModel->getUserCourses($learnerId);       
        if (!empty($learnerCourses)) {
            foreach ($learnerCourses as $learnerCourse) {
                $this->learnerSessionCoursesDetailData[0][$learnerCourse['code']]['course_name'] = $learnerCourse['title'];               
                $trainersList = CourseManager::get_teacher_list_from_course_code($learnerCourse['code']);
                $trainers = array();
                if (!empty($trainersList)) {
                    foreach ($trainersList as $trainer) {
                        $trainers[] = $trainer['firstname'].' '.$trainer['lastname'];
                    }
                }
                $this->learnerSessionCoursesDetailData[0][$learnerCourse['code']]['trainer_name'] = !empty($trainers)?implode(' | ', $trainers):'';
                $this->learnerSessionCoursesDetailData[0][$learnerCourse['code']]['course_nb_online'] = $this->coursesReportingModel->getCourseUsersOnline($learnerCourse['code']);
                
                // get user scenario activities data
                $scenarioActivities = $this->getLearnerCourseScenarioDetailValues($learnerId, $learnerCourse['code'], 0); //$this->coursesReportingModel->getCourseScenarioActivities($learnerCourse['code'], 0, $learnerId);
                $this->learnerSessionCoursesDetailData[0][$learnerCourse['code']]['scenario'] = $scenarioActivities;
                
                // get user modules data
                $modulesData = $this->getLearnerCourseModulesDetailValues($learnerId, $learnerCourse['code'], 0);                
                $this->learnerSessionCoursesDetailData[0][$learnerCourse['code']]['modules'] = $modulesData;
                
                // get user quizzes data
                $quizzesData = $this->getLearnerCourseQuizzesDetailValues($learnerId, $learnerCourse['code'], 0);
                $this->learnerSessionCoursesDetailData[0][$learnerCourse['code']]['quizzes'] = $quizzesData;
                
                // get user face2face data
                $face2faceData = $this->getLearnerCourseFace2faceDetailValues($learnerId, $learnerCourse['code'], 0);
                $this->learnerSessionCoursesDetailData[0][$learnerCourse['code']]['face2face'] = $face2faceData;
            }
        }

        // get courses with sessions for student and selected trainer
        $learnerSessionCourses = $this->coursesReportingModel->getLearnerCoursesSessions($learnerId);
        if (!empty($learnerSessionCourses)) {
            foreach ($learnerSessionCourses as $sid => $learnerSessionCourse) {
                if (!empty($learnerSessionCourse)) {
                    foreach ($learnerSessionCourse as $course) {                                               
                        $tutorsList = CourseManager::get_coach_list_from_course_code($course['code'], $sid);
                        $tutors = array();
                        if (!empty($tutorsList)) {
                            foreach ($tutorsList as $tutor) {
                                $tutors[] = $tutor['firstname'].' '.$tutor['lastname'];
                            }
                        }
                        
                        $sessionInfo = api_get_session_info($sid);
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['course_name'] = $course['title'];
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['session_name'] = $sessionInfo['name'];
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['tutor_name'] = !empty($tutors)?implode(' | ', $tutors):'';
                        
                        // get user scenario activities data
                        $scenarioActivities = $this->getLearnerCourseScenarioDetailValues($learnerId, $course['code'], $sid); //$this->coursesReportingModel->getCourseScenarioActivities($course['code'], $sid, $learnerId);
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['scenario'] = $scenarioActivities;
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['position'] = $course['position'];
                
                        // get user modules data
                        $modulesData = $this->getLearnerCourseModulesDetailValues($learnerId, $course['code'], $sid);
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['modules'] = $modulesData;

                        // get user quizzes data
                        $quizzesData = $this->getLearnerCourseQuizzesDetailValues($learnerId, $course['code'], $sid);
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['quizzes'] = $quizzesData;
                        
                        // get user face2face data
                        $face2faceData = $this->getLearnerCourseFace2faceDetailValues($learnerId, $course['code'], $sid);
                        $this->learnerSessionCoursesDetailData[$sid][$course['code']]['face2face'] = $face2faceData;
                    }
                }
            }
        }
    }
    
     public function setQuizUsersAverageValues($courseCode, $id, $mode, $withPagination = true) {
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;
        //$activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null;
        
        $this->selectedQuizCourse = $courseCode;
        $this->selectedQuizMode = $mode;
        $this->selectedQuizId = $id;
        
        $this->quizzesReportingModel->setTrainerId($this->selectedTrainerFilter);
        
        $learners = $this->quizzesReportingModel->getQuizOrExamUsers($courseCode, $id, $mode, $sessionId, $this->txtSearchQuizUsers);
        
        // sort learners alphabetically
        uasort($learners, array($this, 'sortLearnersItems'));
        
        $this->quizMode = $mode;
        if ($this->quizMode == 'exam') {
            $this->quizInfo = $this->quizzesReportingModel->getExamInfo($courseCode, $id);
        }
        else {
            $this->quizInfo = $this->quizzesReportingModel->getQuizInfo($courseCode, $id);
        }        
        $learnersSlice = $learners;
        if ($withPagination) {
            if (count($learners) > 0) {            
                $this->paginator->url_info['url_query']['currentTab'] = 'quiz_users';
                $this->paginator->url_info['url_query']['id'] = $id;
                $this->paginator->url_info['url_query']['courseCode'] = $courseCode;
                $this->paginator->url_info['url_query']['mode'] = $mode;
                $this->paginator->items_total = count($learners);
                $this->paginator->paginate();
            }
            if (!empty($this->paginator->limit)) {
                $learnersSlice = array_slice($learners, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }

        if (!empty($learnersSlice)) {
            foreach ($learnersSlice as $uid => $learner) {                
                $this->quizUsersData[$uid]['lastname'] = $learner['lastname'];
                $this->quizUsersData[$uid]['firstname'] = $learner['firstname'];
                $this->quizUsersData[$uid]['score'] = $learner['score'] !== false?$learner['score'].' %':'n.a.';
                $this->quizUsersData[$uid]['time'] = api_format_time($learner['time']);
                $this->quizUsersData[$uid]['attempts'] = $learner['attempts'];
                
                if (empty($learner['attempts'])) {
                    $this->quizUsersData[$uid]['score'] = 'n.a.';
                    $this->quizUsersData[$uid]['time'] = 'n.a.';
                    $this->quizUsersData[$uid]['attempts'] = 'n.a.';
                }
                
                if ($withPagination) {
                    $this->quizUsersData[$uid]['session_id'] = $sessionId;
                    $this->quizUsersData[$uid]['course_code'] = $courseCode;
                    $this->quizUsersData[$uid]['exe_exo_id'] = $learner['quiz_id'];
                    $this->quizUsersData[$uid]['quiz_mode'] = $this->quizMode;
                    $this->quizUsersData[$uid]['attempt_id'] = $learner['attempt_id'];
                    $this->quizUsersData[$uid]['show_detail'] = ($learner['attempts'] > 0);
                }
            }
        }        
    }
    
    public function setQuizzesAverageValues($withPagination = true) {                
        $quizzes = $this->quizzes;
        if ($withPagination) {
            if (count($this->quizzes) > 0) {
                $this->paginator->url_info['url_query']['currentTab'] = 'quizzes';
                $this->paginator->items_total = count($this->quizzes);            
                $this->paginator->paginate();
            }       
            if (!empty($this->paginator->limit)) {
                $quizzes = array_slice($this->quizzes, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }       
        if (!empty($quizzes)) {
            $i = 1;
            foreach ($quizzes as $row) {
                if (is_numeric($row['avgScore'])) {
                    $this->chartValues[] = '{y: '.$row['avgScore'].', label: "'.cut($row['title'], 24).'" }';
                }                
                $this->quizData[$i]['title'] = $row['title'];
                $this->quizData[$i]['mode'] = $row['mode'];
                $this->quizData[$i]['incourse'] = $row['course_title'];                                
                $this->quizData[$i]['score'] = $row['avgScore'] !== false?$row['avgScore'].' %':'n.a.';
                $this->quizData[$i]['highest'] = $row['highest'] !== false?$row['highest'].' %':'n.a.';
                $this->quizData[$i]['lowest'] = $row['lowest'] !== false?$row['lowest'].' %':'n.a.';                
                $this->quizData[$i]['participation'] = $row['participation'].'/'.$row['nb_learners'];                
                $this->quizData[$i]['time'] = api_format_time($row['avgTime']);   
                if ($withPagination) {
                    $this->quizData[$i]['id'] = $row['id'];
                    $this->quizData[$i]['course_code'] = $row['course_code'];
                }
                $i++;
            }
        }
    }
    
    public function setSessionsAverageValues($withPagination = true) {
        
        $categoryId = !empty($this->selectedCategoryFilter)?$this->selectedCategoryFilter:null;
        $activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null;        
        $sessions = $this->sessions;               
        if ($withPagination) {
            if (count($this->sessions) > 0) {
                $this->paginator->url_info['url_query']['currentTab'] = 'courses';
                $this->paginator->items_total = count($this->sessions);
                $this->paginator->paginate();
            }       
            if (!empty($this->paginator->limit)) {
                $sessions = array_slice($this->sessions, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }

        if (!empty($sessions)) {
            foreach ($sessions as $session) {
                $quizzesAndExamsAvgScoreArray = $modulesScoreArray = $modulesProgressArray = $modulesTotalTimeArray = array();                
                $sessionId = $session['id'];
                $sessionLearners = $this->learnersReportingModel->getSessionLearners($sessionId, $activeLearner);
                $this->coursesReportingModel->setSessionId($sessionId);
                $courses = $this->coursesReportingModel->getCourses();
                $totModulesTime = $totModulesProgress = $totModulesScore = $totQuizzesScore = 'n.a';
                if (!empty($courses)) {
                    foreach ($courses as $course) {
                        $learners = $this->learnersReportingModel->getCourseLearners($course['code'], $sessionId, $activeLearner);
                        $quizzesAndExamsAvgScore = $this->quizzesReportingModel->getCourseQuizzesAndExamsAvgScore($course['code'], $sessionId, $learners);
                        if ($quizzesAndExamsAvgScore !== FALSE) {
                            $quizzesAndExamsAvgScoreArray[] = $quizzesAndExamsAvgScore;
                        }                                                
                        $modulesScore = $this->modulesReportingModel->getModulesScore($course['code'], $sessionId, $learners);
                        if ($modulesScore !== FALSE) {
                            $modulesScoreArray[] = $modulesScore;
                        }
                        $modulesProgress = $this->modulesReportingModel->getModulesProgress($course['code'], $sessionId, $learners);                
                        if ($modulesProgress !== FALSE) {
                            $modulesProgressArray[] = $modulesProgress;
                        }
                        $modulesTotalTime = $this->modulesReportingModel->getModulesTotalTime($course['code'], $sessionId, $learners);                        
                        if ($modulesTotalTime !== FALSE) {
                            $modulesTotalTimeArray[] = $modulesTotalTime;
                        }
                    }

                    if (!empty($modulesProgressArray)) {
                        $totModulesProgress = round(array_sum($modulesProgressArray) / count($courses)).' %';
                    }
                    if (!empty($modulesScoreArray)) {
                        $totModulesScore = round(array_sum($modulesScoreArray) / count($courses)).' %';
                    }
                    if (!empty($quizzesAndExamsAvgScoreArray)) {
                        $totQuizzesScore = round(array_sum($quizzesAndExamsAvgScoreArray) / count($courses)).' %';
                    }
                    if (!empty($modulesTotalTimeArray)) {
                        $sumTotaltime = array_sum($modulesTotalTimeArray);
                        if (!empty($sumTotaltime)) {
                            $totModulesTime = api_format_time((round($sumTotaltime / count($courses))));
                        }
                        else {
                            $totModulesTime = $totModulesProgress = $totModulesScore  = 'n.a';
                        }
                    }
                }
                $this->sessionsData[$sessionId]['name'] = $session['name'];
                $this->sessionsData[$sessionId]['total_learners'] = count($sessionLearners);
                $this->sessionsData[$sessionId]['total_courses'] = count($courses);
                $this->sessionsData[$sessionId]['modules_time']   = $totModulesTime;
                $this->sessionsData[$sessionId]['modules_progress'] = $totModulesProgress;
                $this->sessionsData[$sessionId]['modules_score'] = $totModulesScore;
                $this->sessionsData[$sessionId]['quizzes_score'] = $totQuizzesScore;                
                if ($withPagination) {
                    $this->sessionsData[$sessionId]['category_id'] = $categoryId;
                }                
            }
        }
    }
    
    public function setCoursesAverageValues($withPagination = true) {
        $activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null;
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;

        $this->learnersReportingModel->setUserId($this->selectedTrainerFilter);
        $this->quizzesReportingModel->setTrainerId($this->selectedTrainerFilter);
        $courses = $this->courses;
        if ($withPagination) {
            if (count($this->courses) > 0) {
                $this->paginator->url_info['url_query']['currentTab'] = 'courses';
                $this->paginator->items_total = count($this->courses);
                $this->paginator->paginate();
            }       
            if (!empty($this->paginator->limit)) {
                $courses = array_slice($this->courses, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }
        
        if (empty($sessionId) && !empty($this->selectedCategoryFilter)) {
            $sessionId = $this->sessionsReportingModel->getCategorySessionsId($this->selectedCategoryFilter);
        }
        
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $learners = $this->learnersReportingModel->getCourseLearners($course['code'], $sessionId, $activeLearner);
                $quizzesAndExamsAvgScore = $this->quizzesReportingModel->getCourseQuizzesAndExamsAvgScore($course['code'], $sessionId, $learners);               
                $modulesScore = $this->modulesReportingModel->getModulesScore($course['code'], $sessionId, $learners);
                $modulesProgress = $this->modulesReportingModel->getModulesProgress($course['code'], $sessionId, $learners);                
                $modulesTotalTime = $this->modulesReportingModel->getModulesTotalTime($course['code'], $sessionId, $learners);
                $nbModules = $this->modulesReportingModel->getNbModules($course['code'], $sessionId);                
                if ($nbModules > 0) {
                    $time = $modulesTotalTime !== FALSE?api_format_time($modulesTotalTime):'n.a.';
                }
                else {
                    $time = 'n.a.';
                }                
                $this->coursesData[$course['code']]['title'] = $course['title'];
                $this->coursesData[$course['code']]['total_learners'] = count($learners);                
                $this->coursesData[$course['code']]['modules_time'] = $time;                
                $this->coursesData[$course['code']]['modules_progress'] = $modulesProgress.' %';
                $this->coursesData[$course['code']]['modules_score'] = $modulesScore !== false?$modulesScore.' %':'n.a.';                
                if (empty($modulesTotalTime)) {
                    $this->coursesData[$course['code']]['modules_time'] = 'n.a.';
                    $this->coursesData[$course['code']]['modules_progress'] = 'n.a.';
                    $this->coursesData[$course['code']]['modules_score'] = 'n.a.';
                }                
                $this->coursesData[$course['code']]['quizzes_score'] = $quizzesAndExamsAvgScore !== false?$quizzesAndExamsAvgScore.' %':'n.a.';
                if ($withPagination) {
                    $this->coursesData[$course['code']]['nb_modules'] = $nbModules;
                }
            }
        }                
    }
    
    public function setUserQuizResult($attemptId, $courseCode, $id, $learnerId, $mode, $sessionId, $exeExoId) {
        $this->selectedQuizCourse = $courseCode;
        $this->selectedQuizLearner = $learnerId;
        $this->selectedQuizMode = $mode;
        $this->selectedQuizId = $id;
        
        $this->selectedQuizAttemptId = $attemptId;
        $this->selectedQuizExeExoId = $exeExoId;
        
        $this->selectedSessionId = $sessionId;        
        if (!empty($this->selectedSessionId)) {
            $this->sessionInfo = api_get_session_info($this->selectedSessionId);
        }
        if ($mode == 'exam') {
            $this->quizInfo = $this->quizzesReportingModel->getExamInfo($courseCode, $exeExoId);
        }
        else {
            $this->quizInfo = $this->quizzesReportingModel->getQuizInfo($courseCode, $exeExoId);
        } 
        $this->learnerInfo = api_get_user_info($learnerId);
        $this->courseInfo = api_get_course_info($courseCode);
        $totalScore = $totalWeighting = 0;
        $hasOpenQuestion = false;

        if (!empty($exeExoId)) {
            $objExercise = new Exercise(null, $this->courseInfo['dbName']);
            $objExercise->read($exeExoId);
            $this->objExercise = $objExercise;
            $questionList = $objExercise->selectQuestionList(); 
            if (!empty($questionList)) {
               $questionNum = 1;                
               foreach ($questionList as $questionId) {
                   $objQuestion = Question::read($questionId, $this->courseInfo['dbName']);
                   $this->quizResultContent .= '<table><tr><td>';
                   $this->quizResultContent .= '<div class="span12 qtnCount"><b>'.$this->get_lang('Question') . ' ' . $questionNum. ':</b> ' .$objQuestion->question.'</div>';
                   switch ($objQuestion->type) {
                       case self::UNIQUE_ANSWER:
                           $uniqueAnswer = Question::getInstance(self::UNIQUE_ANSWER);
                           $this->quizResultContent .= $uniqueAnswer->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName']);
                           break;
                       case self::MULTIPLE_ANSWER:
                           $multipleAnswer = Question::getInstance(self::MULTIPLE_ANSWER);
                           $this->quizResultContent .= $multipleAnswer->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName']);
                           break;
                       case self::REASONING:
                           $reazoning = Question::getInstance(self::REASONING);
                           $this->quizResultContent .= $reazoning->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName']);
                           break;
                       case self::FILL_IN_BLANKS:
                           $fillinblanks = Question::getInstance(self::FILL_IN_BLANKS);
                           $this->quizResultContent .= $fillinblanks->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName']);
                           break;
                       case self::FREE_ANSWER:
                           $hasOpenQuestion = true;
                           $freeanswer = Question::getInstance(self::FREE_ANSWER);
                           $this->quizResultContent .= $freeanswer->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName']);                           
                           break;
                       case self::MATCHING:
                           $matching = Question::getInstance(self::MATCHING);
                           $this->quizResultContent .= $matching->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName']);
                           break;
                       case self::HOT_SPOT:
                            $hotspot = Question::getInstance(self::HOT_SPOT);
                            $this->quizResultContent .= $hotspot->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName']);
                            break;
                        case self::HOT_SPOT_DELINEATION:
                            $hotspotDelineation = Question::getInstance(self::HOT_SPOT_DELINEATION);
                            $this->quizResultContent .= $hotspotDelineation->getHtmlQuestionResult($objQuestion, $attemptId, $totalScore, $totalWeighting, $this->courseInfo['dbName'], $learnerId);
                            break;
                   }  
                   $this->quizResultContent .= '</td></tr></table>';                   
                   // fix images in quiz detail
                   $from = array('../img/', '../default_course_document/images');
                   $to = array(api_get_path(WEB_IMG_PATH), api_get_path(WEB_CODE_PATH).'default_course_document/images');
                   $this->quizResultContent = str_replace($from, $to, $this->quizResultContent);                   
                   $questionNum++;
               }
               
               
               
            }
        }
        $this->totalScore = $totalScore;        
        $this->totalWeighting = $totalWeighting;
        // update scores in track exercises, it fixes wrong scores in old versions results
        if (!$hasOpenQuestion) {
            $this->quizzesReportingModel->updateQuizTrackScores($attemptId, $totalScore, $totalWeighting);
        }
        
        if (!empty($this->selectedModuleViewId)) {
            $this->modulesReportingModel->updateModuleQuizItemView($courseCode, $this->selectedModuleViewId, $attemptId);
        }
        
        
    }
    
    public function setModuleUserDetailValues($courseCode, $lpId, $learnerId, $sessionId = null) {
        if (!isset($sessionId)) {
            $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;
        }
        $this->selectedModuleCourse = $courseCode;
        $this->selectedModuleLearner = $learnerId;
        $this->selectedModuleSession = $sessionId;
        $this->selectedModuleId = $lpId;
        $this->moduleInfo = $this->modulesReportingModel->getModuleInfo($courseCode, $lpId);
        $this->learnerInfo = api_get_user_info($learnerId);
        $this->courseInfo = api_get_course_info($courseCode);
        $lpItems = $this->modulesReportingModel->getModuleItems($courseCode, $lpId);        
        if (!empty($lpItems)) {
            foreach ($lpItems as $lpItem) {
                $lpViews = $this->modulesReportingModel->getModuleUserViews($courseCode, $lpId, $learnerId, $sessionId);
                $itemViews = $this->modulesReportingModel->getModuleItemViews($courseCode, $lpItem['id'], $lpViews, $sessionId);
                $this->moduleUserDetailData[$lpItem['id']]['learner_id'] = $learnerId;                 
                $this->moduleUserDetailData[$lpItem['id']]['course_code'] = $courseCode;
                $this->moduleUserDetailData[$lpItem['id']]['session_id'] = $sessionId;                
                $this->moduleUserDetailData[$lpItem['id']]['name'] = $lpItem['title'];   
                $this->moduleUserDetailData[$lpItem['id']]['views'] = $itemViews;                
                $this->moduleUserDetailData[$lpItem['id']]['last_view'] = array_pop($itemViews);                
            }
        }       
    }

    public function setModuleUsersAverageValues($courseCode, $lpId, $withPagination = true) {
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;
        $activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null;
        $this->learnersReportingModel->setUserId($this->selectedTrainerFilter);
        
        $learners = $this->learnersReportingModel->getCourseLearners($courseCode, $sessionId, $activeLearner, $this->txtSearchModuleUsers);
        // sort learners alphabetically
        uasort($learners, array($this, 'sortLearnersItems'));
         
        $this->moduleInfo = $this->modulesReportingModel->getModuleInfo($courseCode, $lpId);
        $this->selectedModuleCourse = $courseCode;
        $this->selectedModuleId = $lpId;
        $learnersSlice = $learners;
        // Pagination
        if ($withPagination) {
            if (count($learners) > 0) {            
                $this->paginator->url_info['url_query']['currentTab'] = 'module_users';
                $this->paginator->url_info['url_query']['lpId'] = $lpId;
                $this->paginator->url_info['url_query']['courseCode'] = $courseCode;
                $this->paginator->items_total = count($learners);
                $this->paginator->paginate();
            }       
            if (!empty($this->paginator->limit)) {
                $learnersSlice = array_slice($learners, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }
        
        if (!empty($learnersSlice)) {
            foreach ($learnersSlice as $uid => $learner) {
                $time = $this->modulesReportingModel->getModuleTotalTime($courseCode, $lpId, $sessionId, array($uid => $learner));
                $progress = $this->modulesReportingModel->getModuleProgress($courseCode, $lpId, $sessionId, array($uid => $learner));
                $score = $this->modulesReportingModel->getModuleScore($courseCode, $lpId, $sessionId, array($uid => $learner));               
                $lpViews = $this->modulesReportingModel->getModuleUserViews($courseCode, $lpId, $uid, $sessionId);               
                $this->moduleUsersData[$learner['user_id']]['lastname'] = $learner['lastname'];
                $this->moduleUsersData[$learner['user_id']]['firstname'] = $learner['firstname'];
                $this->moduleUsersData[$learner['user_id']]['time'] = api_format_time($time);
                $this->moduleUsersData[$learner['user_id']]['progress'] = $progress.' %';
                $this->moduleUsersData[$learner['user_id']]['score'] = $score !== false?$score.' %':'n.a.';
                
                if (empty($lpViews)) {
                    $this->moduleUsersData[$learner['user_id']]['time'] = 'n.a.';
                    $this->moduleUsersData[$learner['user_id']]['progress'] = 'n.a.';
                    $this->moduleUsersData[$learner['user_id']]['score'] = 'n.a.';   
                }
                
                if ($withPagination) {
                    $this->moduleUsersData[$learner['user_id']]['module_id'] = $lpId;
                    $this->moduleUsersData[$learner['user_id']]['course_code'] = $courseCode;
                    $this->moduleUsersData[$learner['user_id']]['lp_views'] = $lpViews;                
                }
            }
        }        
    }
    
    public function setModulesAverageValues($withPagination = true) {
        $activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null;        
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;
        if ($this->selectedCourseFilter) {
            $modules = $this->modulesReportingModel->getModules($this->selectedCourseFilter, $sessionId, $this->txtSearchModule);
        }
        else {
            $modules = $this->modulesReportingModel->getAllModules($this->courses, $this->selectedSessionFilter, $this->txtSearchModule);
        }
        $this->learnersReportingModel->setUserId($this->selectedTrainerFilter);
        $modulesSlice = $modules;
        if ($withPagination) {
            if (count($modules) > 0) {
                $this->paginator->url_info['url_query']['currentTab'] = 'courses';
                $this->paginator->items_total = count($modules);
                $this->paginator->paginate();
            }       
            if (!empty($this->paginator->limit)) {
                $modulesSlice = array_slice($modules, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }
        
        if (empty($sessionId) && !empty($this->selectedCategoryFilter)) {
            $sessionId = $this->sessionsReportingModel->getCategorySessionsId($this->selectedCategoryFilter);
        }
        
        if (!empty($modulesSlice)) {
            $x = 1;
            foreach ($modulesSlice as $module) {               
                $learners = $this->learnersReportingModel->getCourseLearners($module['course_code'], $sessionId, $activeLearner);
                $time = $this->modulesReportingModel->getModuleTotalTime($module['course_code'], $module['id'], $sessionId, $learners);
                $progress = $this->modulesReportingModel->getModuleProgress($module['course_code'], $module['id'], $sessionId, $learners);
                $score = $this->modulesReportingModel->getModuleScore($module['course_code'], $module['id'], $sessionId, $learners);                
                $this->modulesData[$x]['name'] = $module['name'];
                $this->modulesData[$x]['incourse'] = $module['course_title'];
               
                $this->modulesData[$x]['time'] = api_format_time($time);
                $this->modulesData[$x]['progress'] = $progress.' %';
                $this->modulesData[$x]['score'] = $score !== false?$score.' %':'n.a.';                        
                if (empty($time)) {
                    $this->modulesData[$x]['time'] = 'n.a.';
                    $this->modulesData[$x]['progress'] = 'n.a.';
                    $this->modulesData[$x]['score'] = 'n.a.';
                }
                
                if ($withPagination) {
                    $this->modulesData[$x]['nb_learners'] = count($learners);
                    $this->modulesData[$x]['module_id'] = $module['id'];
                    $this->modulesData[$x]['course_code'] = $module['course_code'];
                    $this->modulesData[$x]['show_detail'] = ($time > 0);
                }                
                $x++;
            }
        }
    }
    
    public function setFace2FaceAverageValues($withPagination = true) {
        $activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null; 
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:0;
        
        if (empty($sessionId) && !empty($this->selectedCategoryFilter)) {
            $sessionId = $this->sessionsReportingModel->getCategorySessionsId($this->selectedCategoryFilter);
        }
        
        if ($this->selectedCourseFilter) {
            $face2faces = $this->face2faceReportingModel->getFace2Faces($this->selectedCourseFilter, $sessionId, $this->txtSearchFace2face);
        }
        else {
            $face2faces = $this->face2faceReportingModel->getAllFace2Faces($this->courses, $sessionId, $this->txtSearchFace2face);
        }
        $this->learnersReportingModel->setUserId($this->selectedTrainerFilter);
        
        
        $face2facesSlice = $face2faces;
        if ($withPagination) {
            if (count($face2faces) > 0) {
                $this->paginator->url_info['url_query']['currentTab'] = 'facetoface';
                $this->paginator->items_total = count($face2faces);
                $this->paginator->paginate();
            }
            if (!empty($this->paginator->limit)) {
                $face2facesSlice = array_slice($face2faces, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }
       
        if (!empty($face2facesSlice)) {
            $x = 1;
            foreach ($face2facesSlice as $face2face) {                   
                $learners = $this->learnersReportingModel->getCourseLearners($face2face['course_code'], $sessionId, $activeLearner);                
                $score = false;
                if ($face2face['ff_type'] == 2) {                
                    $score = $this->face2faceReportingModel->getFace2FaceScore($face2face['course_code'], $face2face['id'], $learners);
                }
                $passPercent = $this->face2faceReportingModel->getPassedUsersPercentage($face2face['course_code'], $face2face['id'], $face2face['ff_type'], $learners);
                $ffUsers = $this->face2faceReportingModel->getFace2FaceUsers($face2face['course_code'], $face2face['id'], $learners);
                $this->face2faceData[$x]['incourse'] = $face2face['course_title'];
                $sessionInfo = api_get_session_info($face2face['session_id']);
                $this->face2faceData[$x]['session'] = !empty($face2face['session_id'])?$sessionInfo['name']:'-';
                $this->face2faceData[$x]['name'] = $face2face['name'];                
                $this->face2faceData[$x]['type'] = $this->face2faceTypes[$face2face['ff_type']];               
                $this->face2faceData[$x]['nb_learners'] = count($learners) > 0?(count($ffUsers).' / '.count($learners)):0;
                $this->face2faceData[$x]['pass'] = $passPercent !== false?$passPercent.' %':'n.a.';
                $this->face2faceData[$x]['score'] = $score !== false?$score.' %':'n.a.';
                if ($withPagination) {
                    $this->face2faceData[$x]['face2face_id'] = $face2face['id'];
                    $this->face2faceData[$x]['course_code'] = $face2face['course_code'];
                    $this->face2faceData[$x]['ff_type'] = $face2face['ff_type'];                    
                }                
                $x++;
            }
        }                
    }
    
    public function setFace2FaceUsersAverageValues($courseCode, $face2faceId, $type, $withPagination = true) {
        
        $sessionId = !empty($this->selectedSessionFilter)?$this->selectedSessionFilter:null;
        $activeLearner = !empty($this->selectedActiveLearnerFilter)?($this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?1:0):null;
        $this->learnersReportingModel->setUserId($this->selectedTrainerFilter);

        $learners = $this->learnersReportingModel->getCourseLearners($courseCode, $sessionId, $activeLearner, $this->txtSearchFace2faceUsers);

        // sort learners alphabetically
        uasort($learners, array($this, 'sortLearnersItems'));
        
        $this->face2faceInfo = $this->face2faceReportingModel->getFace2FaceInfo($courseCode, $face2faceId);
        $this->selectedFace2FaceCourse = $courseCode;
        $this->selectedFace2FaceId = $face2faceId;
        $this->selectedFace2FaceType = $type;
        $learnersSlice = $learners;
        
        // Pagination
        if ($withPagination) {
            if (count($learners) > 0) {
                $this->paginator->url_info['url_query']['currentTab'] = 'facetoface_users';
                $this->paginator->url_info['url_query']['face2faceId'] = $face2faceId;
                $this->paginator->url_info['url_query']['courseCode'] = $courseCode;
                $this->paginator->url_info['url_query']['type'] = $type;
                $this->paginator->items_total = count($learners);
                $this->paginator->paginate();
            }
            if (!empty($this->paginator->limit)) {
                $learnersSlice = array_slice($learners, $this->paginator->low, $this->paginator->items_per_page, true);
            }
        }
        
        if (!empty($learnersSlice)) {
            foreach ($learnersSlice as $learner) {               
                $this->face2faceUsersData[$learner['user_id']]['lastname'] = $learner['lastname'];
                $this->face2faceUsersData[$learner['user_id']]['firstname'] = $learner['firstname'];
                $this->face2faceUsersData[$learner['user_id']]['firstname'] = $learner['firstname'];
                if ($type == 2) {
                    $passedAndScore = $this->face2faceReportingModel->getUserPassedAndScore($courseCode, $face2faceId, $learner['user_id']);
                    if (isset($passedAndScore['passed'])) {
                        if ($passedAndScore['passed'] === true) {
                            $this->face2faceUsersData[$learner['user_id']]['passed'] = 'V';
                        }  
                        else {
                            $this->face2faceUsersData[$learner['user_id']]['passed'] = 'X';
                        }
                    }
                    else {
                        $this->face2faceUsersData[$learner['user_id']]['passed'] = 'O';
                    }
                    $this->face2faceUsersData[$learner['user_id']]['score'] = $passedAndScore['score'];
                    if (!isset($passedAndScore['passed'])) {
                        $this->face2faceUsersData[$learner['user_id']]['score'] = 'n.a.';
                    }
                }
                else {
                    $passedAndScore = $this->face2faceReportingModel->getUserPassedAndComments($courseCode, $face2faceId, $learner['user_id']);
                    if (isset($passedAndScore['passed'])) {
                        if ($passedAndScore['passed'] === true) {
                            $this->face2faceUsersData[$learner['user_id']]['passed'] = 'V';
                        }  
                        else {
                            $this->face2faceUsersData[$learner['user_id']]['passed'] = 'X';
                        }
                    }
                    else {
                        $this->face2faceUsersData[$learner['user_id']]['passed'] = 'O';
                    }
                    $this->face2faceUsersData[$learner['user_id']]['comment'] = $passedAndScore['comment'];
                    if (!isset($passedAndScore['passed'])) {
                        $this->face2faceUsersData[$learner['user_id']]['comment'] = 'n.a.';
                    }
                }
                                                
                if ($withPagination) {
                    $this->face2faceUsersData[$learner['user_id']]['face2face_id'] = $face2faceId;
                    $this->face2faceUsersData[$learner['user_id']]['course_code'] = $courseCode;
                    $this->face2faceUsersData[$learner['user_id']]['type'] = $type;
                    $this->face2faceUsersData[$learner['user_id']]['passed'] = $passedAndScore['passed'];
                }                
            }
        }        
    }
    
    public function setQueryFilters() {  
                
        $this->selectedCategoryFilter = $this->getSession()->getProperty('selectedCategory');
        $this->selectedSessionFilter = $this->getSession()->getProperty('selectedSession');
        $this->selectedCourseFilter  = $this->getSession()->getProperty('selectedCourse');                
        $this->selectedTrainerFilter = $this->getSession()->getProperty('selectedTrainer');                
        $this->selectedQuizFilter = $this->getSession()->getProperty('selectedQuiz');
        $this->selectedQuizTypeFilter = $this->getSession()->getProperty('selectedQuizType');
        $this->selectedActiveLearnerFilter = $this->getSession()->getProperty('selectedActiveLearner');        
        $this->selectedQuizRankingFilter = $this->getSession()->getProperty('selectedQuizRanking');

        if (!$this->isPlatformAdmin) {
            $this->selectedTrainerFilter = $this->currentUser;
        }
        
        $this->currentTab = $this->getRequest()->getProperty('currentTab', '');        
        unset($this->queryFilters);

        if ($this->isPlatformAdmin) {
            if (!empty($this->selectedTrainerFilter)) {
                $trainerInfo = api_get_user_info($this->selectedTrainerFilter);
                $this->queryFilters['trainer_filter'] = $trainerInfo['lastname'].' '.$trainerInfo['firstname'];
            }
        }
        
        if (!empty($this->selectedCategoryFilter)) {
            $categoryInfo = $this->sessionsReportingModel->getCategoryInfo($this->selectedCategoryFilter);
            $this->queryFilters['category_filter'] = $categoryInfo['name'];
        }        
        if (!empty($this->selectedSessionFilter)) {
            $sessionInfo = api_get_session_info($this->selectedSessionFilter);
            $this->queryFilters['session_filter'] = $sessionInfo['name'];
        }
        if (!empty($this->selectedCourseFilter)) {
            $courseInfo = api_get_course_info($this->selectedCourseFilter);
            $this->queryFilters['course_filter'] = $courseInfo['name'];
        }
        if (!empty($this->selectedQuizFilter)) {
            list($courseCode, $mode, $id) = explode('-', $this->selectedQuizFilter);
            if ($mode == 'exam') {
                $quizInfo = $this->quizzesReportingModel->getExamInfo($courseCode, $id);
            }
            else {
                $quizInfo = $this->quizzesReportingModel->getQuizInfo($courseCode, $id);
            }            
            $this->queryFilters['quiz_filter'] = $quizInfo['title'];
        }
        if (!empty($this->selectedQuizTypeFilter)) {                        
            $this->queryFilters['quiz_type_filter'] = $this->selectedQuizTypeFilter == self::SELFLEARNING_QUIZ_MODE?'self-learning':'exam';
        }
        if (!empty($this->selectedActiveLearnerFilter)) {
            $this->queryFilters['active_learner_filter'] = $this->selectedActiveLearnerFilter == self::ACTIVE_LEARNER?$this->get_lang('ActiveLearners'):$this->get_lang('InActiveLearners');
        }
        if (!empty($this->selectedQuizRankingFilter)) {
            $this->queryFilters['quiz_ranking_filter'] = $this->selectedQuizRankingFilter.'%';
        }        
    }
   
    public function setCboFilters($type) {        
        switch ($type) {            
            case 'trainer':                
                if (!empty($this->selectedTrainerFilter)) {
                    
                    $this->categoriesCbo = $this->sessionsReportingModel->getTutorCategories($this->selectedTrainerFilter);
                    
                    $this->coursesReportingModel->setUserId($this->selectedTrainerFilter, false, true);
                    
                    $this->sessionsCbo = $this->sessionsReportingModel->getTutorAllSessions($this->selectedTrainerFilter);                    
                }
                else {
                    $this->selectedTrainerFilter = 0;
                    $this->coursesReportingModel->setUserId($this->selectedTrainerFilter);
                }
                //$this->getSession()->setProperty('selectedTrainer', $this->selectedTrainerFilter);
                $this->coursesReportingModel->setSessionId(0);
                $this->coursesCbo = $this->coursesReportingModel->getCourses();
                $this->setQuizzes($this->selectedQuizTypeFilter);
                break;                
            case 'category':                
                
                if (!empty($this->selectedTrainerFilter)) {
                    $this->coursesReportingModel->setUserId($this->selectedTrainerFilter, false, true);
                    $this->sessionsCbo = $this->sessionsReportingModel->getTutorAllSessions($this->selectedTrainerFilter);
                    $this->categoriesCbo = $this->sessionsReportingModel->getTutorCategories($this->selectedTrainerFilter);
                }                
                $this->coursesCbo = $this->coursesReportingModel->getCourses();
                                
                if (!empty($this->selectedCategoryFilter)) {
                    $this->sessionsCbo = $this->sessionsReportingModel->getCategorySessions($this->selectedCategoryFilter);                    
                    $this->coursesCbo = $this->coursesReportingModel->getCoursesInCategorySessions($this->selectedCategoryFilter);
                }
                else {
                    $this->sessionsCbo = $this->sessionsReportingModel->getTutorAllSessions($this->selectedTrainerFilter);
                }

                break;
            case 'active-learner':
            case 'quiz':
            case 'quiz-type':
            case 'course':
            case 'session':
                if (empty($this->selectedSessionFilter)) {
                    $this->selectedSessionFilter = null;
                }
                
                if (!empty($this->selectedTrainerFilter)) {
                    $this->coursesReportingModel->setUserId($this->selectedTrainerFilter, false, true);
                    $this->categoriesCbo = $this->sessionsReportingModel->getTutorCategories($this->selectedTrainerFilter);
                    $this->sessionsCbo = $this->sessionsReportingModel->getTutorAllSessions($this->selectedTrainerFilter);
                    $this->coursesReportingModel->setSessionId($this->selectedSessionFilter);                    
                }
                else {
                    $this->coursesReportingModel->setSessionId($this->selectedSessionFilter);
                }
                
                if (!empty($this->selectedCategoryFilter)) {
                    $this->sessionsCbo = $this->sessionsReportingModel->getCategorySessions($this->selectedCategoryFilter);
                }
                
                $this->coursesCbo = $this->coursesReportingModel->getCourses();
                $this->setQuizzes($this->selectedQuizTypeFilter, $this->selectedSessionFilter);
                break;            
        }        
    }
    
    public function setQuizzes($type = null, $sessionId = null) {
        if (isset($sessionId)) {
            $this->selectedSessionFilter = $sessionId;
        }                
        $this->quizzesReportingModel->setTrainerId($this->selectedTrainerFilter);
        
        $sessionIdFilter = $this->selectedSessionFilter;
        if (empty($this->selectedSessionFilter) && !empty($this->selectedCategoryFilter)) {
            $sessionIdFilter = $this->sessionsReportingModel->getCategorySessionsId($this->selectedCategoryFilter);
        }        
        
        if (!empty($type)) {
            if (!empty($this->selectedCourseFilter)) {
                if ($type == self::SELFLEARNING_QUIZ_MODE) {
                    $this->quizzes = $this->quizzesReportingModel->getQuizzes($this->selectedCourseFilter, $sessionIdFilter, array(), $this->txtSearchQuiz);
                }
                else {
                    $this->quizzes = $this->quizzesReportingModel->getExams($this->selectedCourseFilter, $sessionIdFilter, array(), $this->txtSearchQuiz);
                }
            }
            else {
                if ($type == self::SELFLEARNING_QUIZ_MODE) {
                    $this->quizzes = $this->quizzesReportingModel->getAllQuizzes($this->courses, $sessionIdFilter, $this->txtSearchQuiz);
                }
                else {
                    $this->quizzes = $this->quizzesReportingModel->getAllExams($this->courses, $sessionIdFilter, $this->txtSearchQuiz);
                }
            }
        }
        else {
            if (!empty($this->selectedCourseFilter)) {
                $quizzes = $this->quizzesReportingModel->getQuizzes($this->selectedCourseFilter, $sessionIdFilter, array(), $this->txtSearchQuiz);
                $exams = $this->quizzesReportingModel->getExams($this->selectedCourseFilter, $sessionIdFilter, array(), $this->txtSearchQuiz);
            }
            else {
                $quizzes = $this->quizzesReportingModel->getAllQuizzes($this->courses, $sessionIdFilter, $this->txtSearchQuiz);
                $exams = $this->quizzesReportingModel->getAllExams($this->courses, $sessionIdFilter, $this->txtSearchQuiz);
            }                        
            $this->quizzes = array_merge($quizzes, $exams);
        }
        $this->quizzesCbo = $this->quizzes;
        
        if (!empty($this->selectedQuizFilter)) {
            list($courseCode, $mode, $id) = explode('-', $this->selectedQuizFilter);
            if ($this->quizModes[$mode] == self::SELFLEARNING_QUIZ_MODE) {
                $this->quizzes = $this->quizzesReportingModel->getQuizzes($courseCode, $sessionIdFilter, array($id), $this->txtSearchQuiz);
            }
            else {
                $this->quizzes = $this->quizzesReportingModel->getExams($courseCode, $sessionIdFilter, array($id), $this->txtSearchQuiz);
            }
        }
        uasort($this->quizzes, array($this, 'sortQuizzesItems'));      
    }
    
    public function setCourses($userId = null) {
        if (!isset($userId)) {
            $userId = $this->selectedTrainerFilter;
        }
        
        $sessionId = $this->selectedSessionFilter;
        $courseCode = $this->selectedCourseFilter;

        if ($this->isPlatformAdmin) {
            $this->coursesReportingModel->setUserId($userId);
        }
        else {
            $this->coursesReportingModel->setUserId($userId, false, true);
        }  

        $this->coursesReportingModel->setSessionId($sessionId);
        $this->courses = $this->coursesReportingModel->getCourses($this->txtSearchCourse);
        
        if (!empty($this->selectedCategoryFilter)) {
            $this->courses = $this->coursesReportingModel->getCoursesInCategorySessions($this->selectedCategoryFilter, null, $this->txtSearchCourse);
            $this->sessions = $this->sessionsReportingModel->getCategorySessions($this->selectedCategoryFilter);
        }
        
        if (!empty($this->selectedSessionFilter)) {
            $this->courses = $this->coursesReportingModel->getCourses($this->txtSearchCourse);
            $this->sessions = array($this->selectedSessionFilter => api_get_session_info($this->selectedSessionFilter));        
        }
        
        if (!empty($courseCode)) {
            $this->courses = $this->coursesReportingModel->getCourse($courseCode, $this->txtSearchCourse);
        }        
        
        $this->coursesCbo = $this->coursesReportingModel->getCourses($this->txtSearchCourse);
        if (!empty($this->selectedCategoryFilter) && empty($this->selectedSessionFilter)) {
            $this->coursesCbo = $this->coursesReportingModel->getCoursesInCategorySessions($this->selectedCategoryFilter, null, $this->txtSearchCourse);
        }
       
    }
    
    public function setTrainers() {
        $this->trainers = UserManager::get_user_list(array('status' => 1));
        $this->trainersCbo = $this->trainers;
    }
    
    public function setCategories() {        
        $trainerId = $this->selectedTrainerFilter;
        if (!empty($trainerId)) {            
            $this->categories = $this->sessionsReportingModel->getTutorCategories($trainerId);
            $this->coursesReportingModel->setUserId($trainerId, false, true);
        }
        else {
            if ($this->isPlatformAdmin) {
                $this->categories = $this->sessionsReportingModel->getAllCategories();
            }
            else {
                $this->categories = $this->sessionsReportingModel->getTutorCategories($this->currentUser);
            }            
        }
        $this->categoriesCbo = $this->categories;        
    }
    
    public function setSessions() {
        $trainerId = $this->selectedTrainerFilter;
        if (!empty($trainerId)) {
            $this->coursesReportingModel->setUserId($trainerId, false, true);
            $this->sessions = $this->sessionsReportingModel->getTutorAllSessions($trainerId, $this->txtSearchSession);
        }
        else {
            if ($this->isPlatformAdmin) {
                $this->sessions = $this->sessionsReportingModel->getAllSessions($this->txtSearchSession);
            }
            else if ($this->isCourseManager) {
                $this->sessions = $this->sessionsReportingModel->getTutorAllSessions($this->currentUser, $this->txtSearchSession);
            }
            else {
                $this->sessions = $this->sessionsReportingModel->getLearnerSessions($this->currentUser, $this->txtSearchSession);
            }
        }
        
        if (!empty($this->selectedCategoryFilter)) {
            $this->sessions = $this->sessionsReportingModel->getCategorySessions($this->selectedCategoryFilter, $this->txtSearchSession);
        }
        
        $this->sessionsCbo = $this->sessions;
        if (!empty($this->selectedSessionFilter)) {
            $this->sessions = array($this->selectedSessionFilter => api_get_session_info($this->selectedSessionFilter));
        }
        
    }
    
     public function unsetFilters() {
        $this->selectedCategoryFilter = null;
        $this->selectedSessionFilter = null;
        $this->selectedCourseFilter = null;
        $this->selectedTrainerFilter = null;      
        $this->selectedQuizFilter = null;
        $this->selectedQuizTypeFilter = null;
        $this->selectedActiveLearnerFilter = null;
        $this->selectedQuizRankingFilter = null;
        $this->txtSearchSession = '';
        $this->txtSearchCourse = '';
        $this->txtSearchModule = '';
        $this->txtSearchQuiz = '';
        $this->txtSearchLearner = '';
        $this->txtSearchDefault = '';
        
        $this->queryFilters = array();
        $this->getSession()->deleteProperty('selectedSession');
        $this->getSession()->deleteProperty('selectedCourse');
        $this->getSession()->deleteProperty('selectedTrainer');
        $this->getSession()->deleteProperty('selectedQuiz');
        $this->getSession()->deleteProperty('selectedQuizType');
        $this->getSession()->deleteProperty('selectedActiveLearner');
        $this->getSession()->deleteProperty('selectedQuizRanking');
    }
    
    public function getLearnerCourseModulesDetailValues($learnerId, $courseCode, $sessionId) {
        $learner = api_get_user_info($learnerId);
        $modulesData = array();
        $modules = $this->modulesReportingModel->getModules($courseCode);
        if (!empty($modules)) {
            foreach ($modules as $module) {                        
                $time = $this->modulesReportingModel->getModuleTotalTime($courseCode, $module['id'], $sessionId, array($learnerId => $learner));
                $progress = $this->modulesReportingModel->getModuleProgress($courseCode, $module['id'], $sessionId, array($learnerId => $learner));
                $score = $this->modulesReportingModel->getModuleScore($courseCode, $module['id'], $sessionId, array($learnerId => $learner));                
                $lpViews = $this->modulesReportingModel->getModuleUserViews($courseCode, $module['id'], $learnerId, $sessionId);                
                $modulesData[$module['id']]['module_id'] = $module['id'];
                $modulesData[$module['id']]['name'] = $module['name'];                                
                $modulesData[$module['id']]['time'] = api_format_time($time);
                $modulesData[$module['id']]['progress'] = $progress.' %';
                $modulesData[$module['id']]['score'] = $score !== false?$score.' %':'n.a.';   
                $modulesData[$module['id']]['lp_views'] = $lpViews;
                $modulesData[$module['id']]['course_code'] = $courseCode;
                $modulesData[$module['id']]['user_id'] = $learnerId;
                $modulesData[$module['id']]['session_id'] = $sessionId;
                if (empty($lpViews)) {
                    $modulesData[$module['id']]['time'] = 'n.a.';
                    $modulesData[$module['id']]['progress'] = 'n.a.';
                    $modulesData[$module['id']]['score'] = 'n.a.';   
                }
            }
        }
        return $modulesData;
    }
    
    public function getLearnerCourseScenarioDetailValues($learnerId, $courseCode, $sessionId) {                
        $scenarioActivities = $this->coursesReportingModel->getCourseScenarioActivities($courseCode, $sessionId);
        $data = array();
        if (!empty($scenarioActivities)) {
            $col = 0;
            $rows = array();
            foreach ($scenarioActivities as $scenarioActivity) {
                $stepId = $scenarioActivity['id'];                
                $data[$col][0]['step_name'] = $scenarioActivity['step_name'];
                $row = 1;                
                if (!empty($scenarioActivity['activities'])) {                    
                    foreach ($scenarioActivity['activities'] as $activity) {                              
                        $data[$col][$row]['activity_name'] = $activity['activity_name'];
                        $data[$col][$row]['activity_type'] = $activity['activity_type'];                        
                        $userActivity = $this->coursesReportingModel->getUserScenarioActivityView($activity['id'], $learnerId, $stepId);                        
                        $data[$col][$row]['status']  = $userActivity['status'];
                        $data[$col][$row]['score']   = $userActivity['score'];
                        $data[$col][$row]['td_class'] = 'scenario_noattempt';
                        if ($userActivity['status'] == 'completed') {
                            $data[$col][$row]['td_class'] = 'scenario_passed';
                        }
                        switch ($activity['activity_type']) {
                            case 'face2face':
                                $face2faceInfo = $this->face2faceReportingModel->getFace2FaceInfo($courseCode, $activity['activity_ref']);  
                                if ($face2faceInfo['ff_type'] == 2) {                                 
                                    $passedAndScore = $this->face2faceReportingModel->getUserPassedAndScore($courseCode, $activity['activity_ref'], $learnerId);
                                    $data[$col][$row]['score'] = isset($passedAndScore['score'])?$passedAndScore['score']:'n.a.';
                                    if (!isset($passedAndScore['passed'])) {
                                        $data[$col][$row]['score'] = 'n.a.';
                                    }
                                }
                                else {
                                    $passedAndScore = $this->face2faceReportingModel->getUserPassedAndComments($courseCode, $activity['activity_ref'], $learnerId);
                                    $data[$col][$row]['comment'] = isset($passedAndScore['comment'])?$passedAndScore['comment']:'n.a';
                                }
                                $data[$col][$row]['ff_passed'] = $this->encodingCharset($this->get_lang('NotAttempt'));
                                if (isset($passedAndScore['passed'])) {
                                    if ($passedAndScore['passed']) {
                                        $data[$col][$row]['td_class'] = 'scenario_passed';
                                        $data[$col][$row]['ff_passed'] = $this->encodingCharset($this->get_lang('Passed'));
                                    }
                                    else {
                                        $data[$col][$row]['td_class'] = 'scenario_failed';
                                        $data[$col][$row]['ff_passed'] = $this->encodingCharset($this->get_lang('Failed'));
                                    }
                                }
                                break;
                            case 'quiz':
                                $userQuizMaxScoreAndTime = $this->quizzesReportingModel->getUserQuizMaxScoreAndTime($courseCode, $learnerId, $sessionId, array($activity['activity_ref'] => array()));                                
                                $data[$col][$row]['score'] = $userQuizMaxScoreAndTime['score'] !== FALSE?$userQuizMaxScoreAndTime['score'].' %':'n.a.';
                                if (empty($userQuizMaxScoreAndTime['time'])) {
                                    $data[$col][$row]['score'] = 'n.a.';
                                } else {
                                    if ($userQuizMaxScoreAndTime['score'] !== FALSE) {
                                        if ($userQuizMaxScoreAndTime['score'] >= 50) {
                                            $data[$col][$row]['td_class'] = 'scenario_passed';
                                        }
                                        else {
                                            $data[$col][$row]['td_class'] = 'scenario_failed';
                                        }
                                    }
                                }                   
                                break;
                            case 'exam':
                                $userQuizLastScoreAndTime = $this->quizzesReportingModel->getUserExamLastScoreAndTime($courseCode, $learnerId, $sessionId, array($activity['activity_ref'] => array()));
                                $data[$col][$row]['score'] = $userQuizLastScoreAndTime['score'] !== FALSE?$userQuizLastScoreAndTime['score'].' %':'n.a.'; 
                                if ($userQuizLastScoreAndTime['score'] !== FALSE) {
                                    if ($userQuizLastScoreAndTime['score'] >= 50) {
                                        $data[$col][$row]['td_class'] = 'scenario_passed';
                                    }
                                    else {
                                        $data[$col][$row]['td_class'] = 'scenario_failed';
                                    }
                                }                                
                                break;
                            case 'module':
                                $time = $this->modulesReportingModel->getModuleTotalTime($courseCode, $activity['activity_ref'], $sessionId, array($learnerId => array()));
                                $progress = $this->modulesReportingModel->getModuleProgress($courseCode, $activity['activity_ref'], $sessionId, array($learnerId => array()));
                                $score = $this->modulesReportingModel->getModuleScore($courseCode, $activity['activity_ref'], $sessionId, array($learnerId => array()));                                
                                $data[$col][$row]['progress'] = $progress.' %';
                                $data[$col][$row]['score'] = $score !== FALSE?$score.' %':'n.a.';
                                if (empty($time)) {
                                    $data[$col][$row]['progress'] = 'n.a.';
                                    $data[$col][$row]['score'] = 'n.a.';
                                }
                                else {
                                    if ($score !== FALSE) {
                                        if ($score >= 50) {
                                            $data[$col][$row]['td_class'] = 'scenario_passed';
                                        }
                                        else {
                                            $data[$col][$row]['td_class'] = 'scenario_failed';
                                        }
                                    }
                                }
                                break;
                            case 'assignment':
                                $userAssignmentScore = $this->coursesReportingModel->getUserAssigmentScoreAverage($courseCode, $learnerId, $activity['activity_ref']);
                                $data[$col][$row]['score'] = $userAssignmentScore !== FALSE?$userAssignmentScore.' %':'n.a.';
                                if ($userAssignmentScore !== FALSE) {
                                    if ($userAssignmentScore >= 50) {
                                        $data[$col][$row]['td_class'] = 'scenario_passed';
                                    }
                                    else {
                                        $data[$col][$row]['td_class'] = 'scenario_failed';
                                    }
                                }
                                break;
                        }
                        $row++;
                        $rows[] = $row;
                    }
                }
                $col++;
            }            
        }
        return array('colums' => $col, 'rows' => max($rows), 'data' => $data);
    }
    
    public function getLearnerCourseFace2faceDetailValues($learnerId, $courseCode, $sessionId) {        
        $face2faceData = array();
        $face2faces = $this->face2faceReportingModel->getFace2Faces($courseCode, $sessionId);        
        $i = 1;
        if (!empty($face2faces)) {
            foreach ($face2faces as $face2face) {
                if ($face2face['ff_type'] == 2) {
                    $passedAndScore = $this->face2faceReportingModel->getUserPassedAndScore($face2face['course_code'], $face2face['id'], $learnerId);
                    $face2faceData[$i]['comment'] = '/';
                }
                else {
                    $passedAndScore = $this->face2faceReportingModel->getUserPassedAndComments($face2face['course_code'], $face2face['id'], $learnerId);
                    $face2faceData[$i]['comment'] = $passedAndScore['comment'];
                }           
                $face2faceData[$i]['name'] = $face2face['name'];
                $face2faceData[$i]['type'] = $this->face2faceTypes[$face2face['ff_type']];
                $face2faceData[$i]['passed'] = $passedAndScore['passed'];                
                $i++;
            }        
            // get passed average for the student
            $face2faceData[0]['total_passed'] = $this->face2faceReportingModel->getUserFace2faceTotalPassed($learnerId, $courseCode, $sessionId);
        }
        return $face2faceData;
    }
    
    public function getLearnerCourseQuizzesDetailValues($learnerId, $courseCode, $sessionId) {       
        $quizzes = $this->quizzesReportingModel->getQuizzes($courseCode, $sessionId);
        $exams = $this->quizzesReportingModel->getExams($courseCode, $sessionId);        
        $AllQuizzes = array_merge($quizzes, $exams);        
        $quizzesData = array();
        if (!empty($AllQuizzes)) {
            $i = 0;
            foreach ($AllQuizzes as $quiz) {                                         
                $nbAttempts = 0;
                if ($quiz['mode'] == 'exam') {
                    $scoreAndTime = $this->quizzesReportingModel->getUserExamLastScoreAndTime($courseCode, $learnerId, $sessionId, array($quiz['id'] => $quiz['title']));
                    $nbAttempts = $this->quizzesReportingModel->getUserExamNbAttempts($courseCode, $learnerId, $sessionId, array($quiz['id'] => $quiz['title']));
                }
                else {
                    $scoreAndTime = $this->quizzesReportingModel->getUserQuizMaxScoreAndTime($courseCode, $learnerId, $sessionId, array($quiz['id'] => $quiz['title']));
                    $nbAttempts = $this->quizzesReportingModel->getUserQuizNbAttempts($courseCode, $learnerId, $sessionId, array($quiz['id'] => $quiz['title']));
                }                 
                $quizzesData[$i]['id'] = $quiz['id'];
                $quizzesData[$i]['exe_exo_id'] = $quiz['exe_exo_id'];
                $quizzesData[$i]['course_code'] = $courseCode;
                $quizzesData[$i]['session_id'] = $sessionId;
                $quizzesData[$i]['learner_id'] = $learnerId;
                $quizzesData[$i]['attempt_id'] = $scoreAndTime['attempt_id'];
                $quizzesData[$i]['name'] = $quiz['title'];
                $quizzesData[$i]['mode'] = $quiz['mode'];                
                $quizzesData[$i]['attempts'] = $nbAttempts;
                $quizzesData[$i]['time'] = api_format_time($scoreAndTime['time']);             
                $quizzesData[$i]['score'] = $scoreAndTime['score'] !== false?$scoreAndTime['score'].' %':'n.a.';                
                if (empty($nbAttempts)) {
                    $quizzesData[$i]['time'] = 'n.a.';
                    $quizzesData[$i]['score'] = 'n.a.';
                    $quizzesData[$i]['attempts'] = 'n.a.';
                }
                $quizzesData[$i]['show_detail'] = ($nbAttempts > 0);
                $i++;
            }
        }
        return $quizzesData; 
    }
    
    public function isQuizInRanking($score) {
        $quizRanking = !empty($this->selectedQuizRankingFilter)?$this->selectedQuizRankingFilter:null;
        if (isset($quizRanking)) {            
            if ($score === false) {
                return false;
            }
            else {
                if (is_numeric($score)) {
                    list($maxScore, $minScore) = explode('-', $quizRanking);
                    if ($score > $maxScore || $score < $minScore) { 
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    public function setThemeColor() {
        switch ($this->stylesheets) {
            case 'dokeos2_black_tablet': 
                $this->themeColor = "#424242"; 
                break;
            case 'dokeos2_blue_tablet': 
                $this->themeColor = "#003C77"; 
                break;
            case 'dokeos2_medical_tablet': 
                $this->themeColor = "#134958"; 
                break;
            case 'dokeos2_orange_tablet': 
                $this->themeColor = "#D66B00"; 
                break;
            case 'dokeos2_red_tablet': 
                $this->themeColor = "#96040B"; 
                break;
            case 'dokeos2_tablet': 
                $this->themeColor = "#1084A7"; 
                break;
            case 'redhat_tablet': 
                $this->themeColor = "#cc0000"; 
                break;
            case 'orkyn_tablet': 
                $this->themeColor = "#1084A7"; 
                break;
            default: 
                $this->themeColor = "#424242";
        }        
    }
    
    public function sortLearnersItems($a, $b) {
        return strcasecmp($a["lastname"], $b["lastname"]);
    }
    
    public function sortCoursesItems($a, $b) {
        return strcasecmp($a["title"], $b["title"]);
    }
    
    public function sortModulesItems($a, $b) {
        return strcasecmp($a["name"], $b["name"]);
    }
    
    public function sortQuizzesItems($a, $b) {
        return strcasecmp($a["title"], $b["title"]);
    }
    
    public function displayLearnerAccessGraph() {
        $this->graphType = $this->getRequest()->getProperty('type', '');
        $learnerId = $this->getRequest()->getProperty('learnerId', '');        
        $this->learnerInfo = api_get_user_info($learnerId);
        $connections = $this->learnersReportingModel->getUserPlatformConnection($learnerId);        
        $main_year = $main_month_year = $main_day = array();
	// get last 8 days/months
	$last_days = 8;
	$last_months = 5;
	for ($i = $last_days; $i >= 0; $i--) {
            $main_day[date ('j-n-y', mktime () - $i * 3600 * 24)] = 0;
	}
	for ($i = $last_months; $i >= 0; $i--) {
            $main_month_year[date ('n-y', mktime () - $i * 30 * 3600 * 24)] = 0;
	}

	$i = 0;
	if (is_array($connections) && count($connections) > 0) {
		foreach ($connections as $data) {
                    //creating the main array
                    $main_month_year[date('n-y', $data['login'])] += float_format(($data['logout'] - $data['login']) / 60, 0);
                    $main_day[date('j-n-y', $data['login'])] += float_format(($data['logout'] - $data['login']) / 60, 0);
                    if ($i > 500) { break; }
                    $i++;
		}

		switch ($this->graphType) {
                    case 'day': $main_date = $main_day; break;
                    case 'month': $main_date = $main_month_year; break;
                    case 'year': $main_date = $main_year; break;
		}

		$labels = array_keys($main_date);
		if (count($main_date) == 1) {
                    $labels = $labels[0];
                    $main_date = $main_date[$labels];
		}

		$data_set = new pData;
		$data_set->AddPoint($main_date, 'Q');
		if (count($main_date)!= 1) {
                    $data_set->AddPoint($labels, 'Date');
		}
		$data_set->AddAllSeries();
		$data_set->RemoveSerie('Date');
		$data_set->SetAbsciseLabelSerie('Date');
		$data_set->SetYAxisName($this->get_lang('Minutes', ''));
		$graph_id = api_get_user_id().'AccessDetails'.api_get_course_id();
		$data_set->AddAllSeries();

		$cache = new pCache();		
		$data = $data_set->GetData();

		if ($cache->IsInCache($graph_id, $data_set->GetData())) {
                    $this->graphImg = $cache->GetHash($graph_id, $data_set->GetData());
		} else {
                    // if the image does not exist in the archive/ folder
                    // Initialise the graph
                    $test = new pChart(860, 280);

                    // Adding the color schemma
                    $test->loadColorPalette(api_get_path(LIBRARY_PATH).'pchart/palette/default.txt');

                    $test->setFontProperties(api_get_path(LIBRARY_PATH).'pchart/fonts/tahoma.ttf', 8);
                    $test->setGraphArea(70, 30, 780, 250);
                    $test->drawFilledRoundedRectangle(7, 7, 793, 243, 5, 240, 240, 240);
                    $test->drawRoundedRectangle(5, 5, 795, 245, 5, 230, 230, 230);
                    $test->drawGraphArea(255, 255, 255, TRUE);
                    $test->drawScale($data_set->GetData(), $data_set->GetDataDescription(), SCALE_START0, 150, 150, 150, TRUE, 0, 0);
                    $test->drawGrid(4, TRUE, 230, 230, 230, 50);
                    $test->setLineStyle(2);

                    // Draw the 0 line
                    $test->setFontProperties(api_get_path(LIBRARY_PATH).'pchart/fonts/tahoma.ttf', 6);
                    $test->drawTreshold(0, 143, 55, 72, TRUE, TRUE);

                    if (count($main_date) == 1) {
                        //Draw a graph
                        //echo '<strong>'.$labels.'</strong><br/>';
                        $test->drawBarGraph($data_set->GetData(), $data_set->GetDataDescription(), TRUE);
                    } else {
                        //Draw the line graph
                        $test->drawLineGraph($data_set->GetData(), $data_set->GetDataDescription());
                        $test->drawPlotGraph($data_set->GetData(), $data_set->GetDataDescription(), 3, 2, 255, 255, 255);
                    }

                    // Finish the graph
                    $test->setFontProperties(api_get_path(LIBRARY_PATH).'pchart/fonts/tahoma.ttf', 8);

                    $test->setFontProperties(api_get_path(LIBRARY_PATH).'pchart/fonts/tahoma.ttf', 10);
                    $test->drawTitle(60, 22, $this->get_lang('Connections'), 50, 50, 50, 585);

                    $cache->WriteToCache($graph_id, $data_set->GetData(), $test);
                    ob_start();
                    $test->Stroke();
                    ob_end_clean();
                    $this->graphImg = $cache->GetHash($graph_id, $data_set->GetData());
		}
	} 
        
        
        echo $this->setTemplate('learner_access_graph', 'Report');
        exit;
    }
    
    public function loadHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/css/sprites.min.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/css/responsive-tabs.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/css/custom-responsive.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/css/stacktable.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/1.8.2/themes/base/jquery.ui.autocomplete.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/1.8.2/themes/base/jquery.ui.theme.css', 'css'); 
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/css/reporting.css', 'css');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/1.8.2/ui/jquery.ui.core.js');
        //$this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/1.8.2/ui/jquery.ui.widget.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/1.8.2/ui/jquery.ui.position.js');
        //$this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/1.8.2/ui/jquery.ui.autocomplete.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/js/canvasjs.min.js');               
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/js/responsiveTabs.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/js/stacktable.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/js/reportingModel.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/reporting/assets/js/reportingController.js');        
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();  
        api_block_anonymous_users();
    }
    
}
