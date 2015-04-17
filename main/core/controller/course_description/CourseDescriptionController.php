<?php
/* For licensing terms, see /license.txt */

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action 
 */
class CourseDescriptionController extends Controller {	
		
	private $toolname;
	private $view; 
        private $model;
	private $userId;
	private $courseCode;
	private $sessionId;
	private $descriptionId;
	private $descriptionType;

	/**
	 * Constructor
	 */
	public function __construct($descriptionId = null,$descriptionType = null) {		
            $this->userId = api_get_user_id();
            $this->courseCode = api_get_course_id();
            $this->sessionId = api_get_session_id();

            if (isset($descriptionId)) {				
                $this->descriptionId = $descriptionId;				
            }
			if (isset($descriptionType)) {				
                $this->descriptionType = $descriptionType;				
            }
            
            // we load the model object
            $this->model = new CourseDescriptionModel();            
                       
            // we load the view object
            $this->toolname = 'course_description';            
            $this->view = new View($this->toolname);
            $this->view->set_layout('layout');
	}

	public function listing() {
		$data = array();

		$data['descriptions'] = $this->model->getCourseDescriptionList();
				                
		// render to the view
		$this->view->set_data($data);
		$this->view->set_template('list');
		$this->view->render();
	}	

	public function display_action($default_description_titles,$default_description_class) {

		$display_icons = $this->model->getDisplayActionIcons($default_description_titles,$default_description_class,$show_form);	
		echo $display_icons;
	}

	public function add($default_description_titles,$default_description_title_editable,$question,$information) {
		
		$title = $default_description_titles[$this->descriptionType];
		$this->model->description_id = $this->descriptionId;
		$this->model->description_type = $this->descriptionType;
		$data = array();
		if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
                $check = Security::check_token();                 
        	if ($check) {
                        $this->model->title = $_POST['title'];

			if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
				$content = WCAG_Rendering::prepareXHTML();
			} else {
				$content = $_POST['contentDescription'];
			}
			$this->model->contentDescription = $content;
			$this->model->add = $_POST['add'];
			$lastInsertId = $this->model->save($default_description_titles,$default_description_title_editable);
                        $_SESSION["display_confirmation_message"]=get_lang("CourseDescriptionUpdated");
                        unset($_POST);
                        Security::clear_token();
			
                }
		$this->listing();	
		}
		else {
		 	if($show_peda_suggest) {
				$question_info = $this->descriptionId;
			}
			$data['show_peda_suggest'] = true;
			$data['question_info'] = $question_info;
			$data['courseDescriptionForm'] = $this->model->getForm($title);
                        
			// render to the view
			$this->view->set_data($data);
			$this->view->set_template('add');
			$this->view->render();
		}
	}

	public function edit($default_description_titles,$default_description_title_editable) {
		
		$title = $default_description_titles[$this->descriptionType];
		
		$this->model->description_id = $this->descriptionId;
		$this->model->description_type = $this->descriptionType;
		$this->model->edit = $_POST['edit'];
		$data = array();
		if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
			$this->model->title = $_POST['title'];
			if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
				$content = WCAG_Rendering::prepareXHTML();
			} else {
				$content = $_POST['contentDescription'];
			}
			$this->model->contentDescription = $content;
			$this->model->edit = $_POST['edit'];
			$lastInsertId = $this->model->save($default_description_titles,$default_description_title_editable);
                        unset($_POST);
                        Security::clear_token();
			$this->listing();
		}
		else {
			if($show_peda_suggest) {
                            $question_info = $this->descriptionId;
			}
			$data['show_peda_suggest'] = true;
			$data['question_info'] = $question_info;
			$data['courseDescriptionForm'] = $this->model->getForm($title);

			// render to the view
			$this->view->set_data($data);
			$this->view->set_template('add');
			$this->view->render();
		}
	}

	public function destroy() {
		$this->model->description_id = $this->descriptionId;
		$this->model->description_type = $this->descriptionType;
		$this->model->destroy();
                $_SESSION["display_confirmation_message"]=get_lang("CourseDescriptionDeleted");
                $url = api_get_path(WEB_CODE_PATH).'core/views/course_description/index.php?'.api_get_cidreq();
                header("Location: " . $url );
            	//$this->listing();
	}
	
	
}
?>
