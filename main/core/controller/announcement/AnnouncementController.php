<?php

/* For licensing terms, see /license.txt */

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action 
 */
class AnnouncementController extends Controller {

    private $toolname;
    private $view;
    private $model;
    private $userId;
    private $courseCode;
    private $sessionId;
    private $announcementId;

    /**
     * Constructor
     */
    public function __construct($announcementId = null) {
        $this->userId = api_get_user_id();
        $this->courseCode = api_get_course_id();
        $this->sessionId = api_get_session_id();
        if (isset($announcementId)) {
            $this->announcementId = $announcementId;
        }

        // we load the model object
        $this->model = new AnnouncementModel();

        // load objects (eg: helper)
        $this->load('pagination', 'helper');

        // we load the view object
        $this->toolname = 'announcement';
        $this->view = new View($this->toolname);
        $this->view->set_layout('layout');
    }

    public function listing() {
        $data = array();

        $announcementList = $this->pagination_helper->generate($this->model->getAnnouncementList());

        // preparing the response
        $data['announcementList'] = $announcementList;
        $data['pagerLinks'] = $this->pagination_helper->links();
        $data['prevAndNext'] = $this->pagination_helper->prevAndNext();
        $data['pages'] = $this->pagination_helper->pages;        
        $this->model->announcement_id = $this->announcementId;
        if (api_is_allowed_to_edit()) {
            $data['announcementForm'] = $this->model->getForm();
        } else {
            $this->showannouncement($this->announcementId);
        }

        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('list');
        $this->view->render();
    }

    public function add() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            $check = Security::check_token();
            if ($check) {
                $this->model->user_id = $this->userId;
                $this->model->course = $this->courseCode;
                $this->model->session_id = $this->sessionId;
                $this->model->title = $_POST['title'];
                $this->model->description = $_POST['description'];
                $this->model->send_receivers = $_POST['send_to']['receivers'];
                
                if ($_POST['send_to']['receivers'] == 2) {
                    $this->model->send_to = $_POST['send_filter'];
                } else {
                    $this->model->send_to = $_POST['send_to']['to'];
                }
                $this->model->scenario_filter	 = $_POST['scenario_filter'];
                $lastInsertId = $this->model->save();
                $_SESSION["display_confirmation_message"] = get_lang("AnnouncementAdded");
            }
        }
        if (($_SERVER['REQUEST_METHOD']) == "POST") {
            $this->showannouncement($this->announcementId);
            Security::clear_token();
        } else {
            $this->listing();
        }
    }

    public function edit() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            $check = Security::check_token();
            if ($check) {
                $this->model->user_id = $this->userId;
                $this->model->course = $this->courseCode;
                $this->model->session_id = $this->sessionId;
                $this->model->title = $_POST['title'];
                $this->model->description = $_POST['description'];
                $this->model->send_receivers = $_POST['send_to']['receivers'];

                if ($_POST['send_to']['receivers'] == 2) {
                    $this->model->send_to = $_POST['send_filter'];
                } else {
                    $this->model->send_to = $_POST['send_to']['to'];
                }
                $this->model->scenario_filter = $_POST['scenario_filter'];
                $this->model->announcement_id = $this->announcementId;
                $lastInsertId = $this->model->save();
                $_SESSION["display_confirmation_message"] = get_lang("AnnouncementModified");
            }
        }

        if (($_SERVER['REQUEST_METHOD']) == "POST") {
            $this->showannouncement($this->announcementId);
            unset($_POST);
            unset($this->announcementId);
            Security::clear_token();
        } else {
            $this->listing();
        }
    }

    /**
     * view announcement
     */
    public function showannouncement($announcementId) {

        global $charset;

        if (empty($announcementId)) {
            $announcementId = $this->model->getLastAnnouncement();
        }

        $data = array();
        $announcement_info = array();
        $announcementList = $this->pagination_helper->generate($this->model->getAnnouncementList());
        $announcement_info = $this->model->getAnnouncementInfo($announcementId);


        if (strpos($announcement_info['announcement_content'], '../../courses/') !== FALSE) {
            $announcement_info['announcement_content'] = str_replace('../../courses/', '/courses/', $announcement_info['announcement_content']);
        }

        // preparing the response        
        $data['announcement_id'] = $announcementId;
        $data['ann_title'] = $announcement_info['announcement_title'];
        $data['ann_content'] = $this->model->replacePatternContent($announcement_info['announcement_content']);
        $data['ann_date'] = $announcement_info['announcement_date'];
        $data['firstname'] = $announcement_info['firstname'];
        $data['lastname'] = $announcement_info['lastname'];
        $data['insert_user_id'] = $announcement_info['insert_user_id'];

        $data['announcementList'] = $announcementList;
        $data['pagerLinks'] = $this->pagination_helper->links();
		$data['prevAndNext'] = $this->pagination_helper->prevAndNext();
        $data['pages'] = $this->pagination_helper->pages;


        $this->model->id = $announcementId;
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('showannouncement');
        $this->view->render();
    }

	/**
     * view all announcement
     */
    public function showallannouncement() {

        global $charset;        

        $data = array();
        $announcement_info = array();
        $announcementList = $this->pagination_helper->generate($this->model->getAnnouncementList());        
		$allAnnouncement = $this->model->getAnnouncementList();    
		
		foreach($allAnnouncement as $announcement){
			$announcement_info = $this->model->getAnnouncementInfo($announcement->id);

			$data['ann_title'][$announcement->id] = $announcement_info['announcement_title'];
			$data['ann_content'][$announcement->id] = $this->model->replacePatternContent($announcement_info['announcement_content']);
			$data['ann_date'][$announcement->id] = $announcement_info['announcement_date'];
			$data['firstname'][$announcement->id] = $announcement_info['firstname'];
			$data['lastname'][$announcement->id] = $announcement_info['lastname'];
			$data['insert_user_id'][$announcement->id] = $announcement_info['insert_user_id'];
		}
		 
        $data['pagerLinks'] = $this->pagination_helper->links();
        $data['prevAndNext'] = $this->pagination_helper->prevAndNext();
        $data['pages'] = $this->pagination_helper->pages;       
                
        $data['announcementList'] = $announcementList;       
		$data['allAnnouncement'] = $allAnnouncement;       
        
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('showallannouncement');
        $this->view->render();
    }

    /**
     * delete announcement
     */
    public function destroy() {
        if ($this->announcementId) {
            $this->model->announcement_id = $this->announcementId;
            $this->model->user_id = $this->userId;
            $this->model->delete();
            unset($this->announcementId);
            $_SESSION["display_confirmation_message"] = get_lang("AnnouncementDeleted");
        }
        $url = api_get_path(WEB_CODE_PATH).'core/views/announcement/index.php?'.api_get_cidreq();
        header("Location: " . $url );
        
    }

}

?>
