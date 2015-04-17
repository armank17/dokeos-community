<?php
/* For licensing terms, see /license.txt */
require_once (api_get_path(LIBRARY_PATH).'timezone.lib.php');

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action
 */
class WorkController extends Controller {

	private $toolname;
	private $view;
    private $model;
	private $userId;
	private $courseCode;
	private $sessionId;
	private $assignmentId;
	private $activityId;

	/**
	 * Constructor
	 */
	public function __construct($assignmentId = null, $activityId = null) {
		$this->userId = api_get_user_id();
		$this->courseCode = api_get_course_id();
		$this->sessionId = api_get_session_id();
		if (isset($assignmentId)) {
			$this->assignmentId = $assignmentId;
			$this->model->assignment_id = $this->assignmentId;
		}

		if (isset($activityId)) {
			$this->activityId = $activityId;
			$this->model->activity_id = $this->activityId;
		}

		// we load the model object
		$this->model = new WorkModel();

		// we load the view object
		$this->toolname = 'work';
		$this->view = new View($this->toolname);
		$this->view->set_layout('layout');

	}

	public function listing() {
            $data = array();
            // Start group session if exists
            if (isset($_GET['group_id'])) {
                $_SESSION['toolgroup'] = intval($_GET['group_id']);
            } else {
                $_SESSION['toolgroup'] = '';
            }
			$this->model->activity_id		 = $this->activityId;
            $assignmentList = $this->model->getAssignmentTable();

            // preparing the response
            $data['assignmentList'] = $assignmentList;

            // render to the view
            $this->view->set_data($data);
            $this->view->set_template('list');
            
            $this->view->render();
	}

	public function display_action() {

		$display_icons = $this->model->getDisplayActionIcons($this->assignmentId, $this->activityId);
		echo $display_icons;
	}

	public function add($cur_dir_path) {
		$data = array();
		if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {                    
			$check = Security::check_token();

//			$deadlinearr = $_POST['deadline'];
//			$deadline = $this->model->getDeadlineDate($deadlinearr);
//                        
//			$datetime = explode(" ", $deadline);
//			$dateparts = explode("-", $datetime[0]);
//			$deadline = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0].' '.$datetime[1];
                        //$deadline = $_POST['deadline'];
                        $user_id = api_get_user_id();
                        //$datetime = TimeZone::ConvertTimeFromServerToUser($user_id, $_POST['deadline']);
                        $datetime = TimeZone::ConvertTimeFromUserToServer($user_id, $_POST['deadline']);
                        $deadline = date("Y-m-d H:i:00", strtotime($datetime));
		//if ($check) {
				$this->model->user_id			 = $this->userId;
				$this->model->course			 = $this->courseCode;
				$this->model->session_id		 = $this->sessionId;
				$this->model->new_dir			 = $_POST['new_dir'];
				$this->model->description		 = $_POST['description'];
				$this->model->deadline			 = $deadline;
				$this->model->confidential		 = $_POST['confidential'];
				$this->model->score			 = $_POST['score'];
				$this->model->sec_token			 = $_POST['sec_token'];
				$lastInsertId = $this->model->save($cur_dir_path);
                                $_SESSION["display_confirmation_message"] = get_lang('langNewStudentPublicationCreated');
				unset($_POST);
				Security::clear_token();
			//}
			$this->listing();
		}
		else {
			$data['assignmentAddForm'] = $this->model->getForm($cur_dir_path);
			// render to the view
			$this->view->set_data($data);
			$this->view->set_template('add');
			$this->view->render();
		}

	}

	public function edit($cur_dir_path) {
		$data = array();
		if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
			$check = Security::check_token();
			$assignment_info = $this->model->getAssignmentInfo($this->assignmentId);
			$curdirpath = str_replace(" ","_",$_POST['curdirpath']);

//			$deadlinearr = $_POST['deadline'];
//			$deadline = $this->model->getDeadlineDate($deadlinearr);
//
//			$datetime = explode(" ", $deadline);
//			$dateparts = explode("-", $datetime[0]);
//			$deadline = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0].' '.$datetime[1];
			//$deadline = $_POST['deadline'];
                        //$deadline = date("Y-m-d H:i:00", strtotime($_POST['deadline']));
                        //$deadline1 = TimeZone::ConvertTimeFromUserToServer($id_user, strtotime($deadline));
                        //$deadline = date('d-m-Y h:i:00 a', strtotime($deadline));
                        //$deadline = $_POST['deadline'];
                        //console.log("WorkController.php | 137 | Edit-> ".$deadline);
                        $id_user = api_get_user_id();
                        $ends = TimeZone::ConvertTimeFromUserToServer($id_user, $_POST['deadline']);
                        $deadline = date('Y-m-d H:i:00', strtotime($ends));
		//if ($check) {
				$this->model->user_id			 = $this->userId;
				$this->model->course			 = $this->courseCode;
				$this->model->session_id		 = $this->sessionId;
				$this->model->new_dir			 = $_POST['new_dir'];
				$this->model->description		 = $_POST['description'];
				$this->model->deadline			 = $deadline;
				$this->model->confidential		 = $_POST['confidential'];
				$this->model->score			 = $_POST['score'];
				$this->model->sec_token			 = $_POST['sec_token'];
				$this->model->curdirpath		 = $curdirpath;
				$this->model->old_dir_name 		 = $assignment_info['url'];
				$this->model->assignment_id		 = $this->assignmentId;

				$lastInsertId = $this->model->save($cur_dir_path);
                                $_SESSION["display_confirmation_message"] = get_lang('LangNewStudentPublicationUpdated');
				unset($_POST);
				unset($this->assignmentId);
				Security::clear_token();
			//}
			$this->listing();
		}
		else {
			$this->model->assignment_id		 = $this->assignmentId;
			$data['assignmentEditForm'] = $this->model->getForm($cur_dir_path);
			// render to the view
			$this->view->set_data($data);
			$this->view->set_template('edit');
			$this->view->render();
		}

	}

	public function destroy($delete_id){
		if (!empty($delete_id)) {
			$this->model->delete_assignment_id = $delete_id;
			$this->model->user_id     = $this->userId;
			$this->model->delete();
                        $_SESSION["display_confirmation_message"] = get_lang('AssignmentDeleted');
		}
		$this->listing();
	}

	public function submit_work($cur_dir_path,$activityId){
		$data = array();

		if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
			$this->model->summary			 = $_POST['summary'];
			if(empty($_POST['assignment_id'])){
				$this->model->assignment_id		 = $_POST['default_assignment_id'];
			}
			else {
				$this->model->assignment_id		 = $_POST['assignment_id'];
			}
			$this->model->save_submit_work($cur_dir_path);
                        $_SESSION["display_confirmation_message"] = get_lang('NewPaperSubmitted');
			$this->listing();
		}
		else {
			$this->model->assignment_id		 = $this->assignmentId;
			$this->model->activity_id		 = $activityId;
			$data['submitWorkForm'] = $this->model->getSubmitWorkForm($cur_dir_path);

			// render to the view
			$this->view->set_data($data);
			$this->view->set_template('submit_work');
			$this->view->render();
		}
	}

	public function view_papers($cur_dir_path) {
		$data = array();

		$this->model->assignment_id		 = $this->assignmentId;
		$paperList = $this->model->getPapersTable();
		$assignment_info = $this->model->getAssignmentInfo($this->assignmentId);

		// preparing the response
		$data['papersList'] = $paperList;
		$data['title'] = $assignment_info['title'];
		$data['description'] = $assignment_info['description'];

		// render to the view
		$this->view->set_data($data);
		$this->view->set_template('paper_list');
		$this->view->render();

	}

	public function move_paper($cur_dir_path,$paperId) {
		$this->model->assignment_id		 = $this->assignmentId;
		$this->model->paper_id			 = $paperId;
		$data['movePaperForm'] = $this->model->getMovePaperForm();

		if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
			$this->model->move_assignment_id		 = $_POST['assignment'];
			$lastInsertId = $this->model->save_move_paper($cur_dir_path);
                        $_SESSION["display_confirmation_message"] = get_lang('ThePaperWasMoved');
                        
		}
		else {
			// render to the view
			$this->view->set_data($data);
			$this->view->set_template('move_form');
			$this->view->render();
		}
		$this->view_papers();
	}

	public function delete_paper($paperId){
		$this->model->assignment_id		 = $this->assignmentId;
		$this->model->paper_id			 = $paperId;
		if (!empty($paperId)) {
			$this->model->delete_paper_id = $paperId;
			$this->model->user_id     = $this->userId;
			$this->model->delete_paper();
                        $_SESSION["display_confirmation_message"] = get_lang('ThePaperWasDeleted');
		}
		$this->view_papers();
	}

	public function correct_paper($paperId) {
		$this->model->assignment_id		 = $this->assignmentId;
		$this->model->paper_id			 = $paperId;
		$data['correctPaperForm'] = $this->model->getCorrectPaperForm();

		if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
			$this->model->remark		 = $_POST['remark'];
			$this->model->score			 = $_POST['score'];
			$lastInsertId = $this->model->save_correct_paper();
                        $_SESSION["display_confirmation_message"] = get_lang('ThePaperWasCorrected');
		}
		else {
			// render to the view
			$this->view->set_data($data);
			$this->view->set_template('correct_paper');
			$this->view->render();
		}
		$this->view_papers();
	}

	public function move_form($paperId) {
		$moveform =  $this->model->movehomeform($paperId);
		$assignmentList = $this->model->getAssignmentTable();

		// preparing the response
		$data['moveform'] = $moveform;
		$data['assignmentList'] = $assignmentList;

		$this->view->set_data($data);
		$this->view->set_template('move');
		$this->view->render();
	}

	public function move_to() {
		$this->model->move_file = $_POST['move_file'];
		$this->model->move_to = $_POST['move_to'];

		$moveform =  $this->model->moveto();
		$assignmentList = $this->model->getAssignmentTable();
		$data['moveform'] = $moveform;
		$data['assignmentList'] = $assignmentList;

		$this->view->set_data($data);
		$this->view->set_template('move');
		$this->view->render();

	}

	public function view_paper($paperId) {
		global $_course;
		$data = array();
		$paper_info = $this->model->get_paper_info($paperId);

		$data['url'] = $paper_info['url'];
		$data['corrected_url'] = $paper_info['corrected_file'];
		$data['title'] = $paper_info['title'];
		$data['description'] = $paper_info['description'];
		$data['author'] = $paper_info['author'];
		$data['submittedon'] = $submittedon;
		$data['qualificator_id'] = $paper_info['qualificator_id'];
		$data['qualification'] = $paper_info['qualification'];
		$data['weight'] = $paper_info['weight'];
		$data['sent_date'] = $paper_info['sent_date'];
		$data['remark'] = $paper_info['remark'];
                $data['_course'] = $_course;

		$this->view->set_data($data);
		$this->view->set_template('view_paper');
		$this->view->render();
	}
}
?>
