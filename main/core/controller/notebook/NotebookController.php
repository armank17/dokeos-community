<?php
/* For licensing terms, see /license.txt */

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action 
 */
class NotebookController extends Controller {	
		
	private $toolname;
	private $view; 
        private $model;
	private $userId;
        private $courseCode;
        private $sessionId;
        private $notebookId;

	/**
	 * Constructor
	 */
	public function __construct($notebookId = null) {		
            $this->userId = api_get_user_id();
            $this->courseCode = api_get_course_id();
            $this->sessionId = api_get_session_id();
            if (isset($notebookId)) {
                $this->notebookId = $notebookId;
            }
            
            // we load the model object
            $this->model = new NotebookModel();            
            
            // load objects (eg: helper)
            $this->load('pagination', 'helper');
            
            // we load the view object
            $this->toolname = 'notebook';            
            $this->view = new View($this->toolname);
            $this->view->set_layout('layout');
	}

	public function listing($action) {
                $data = array();
				
                $noteList = $this->pagination_helper->generate($this->model->getNotesList($this->userId));
                
                // preparing the response
                $data['noteList'] = $noteList;
                $data['action'] = $action;
                $data['pagerLinks'] = $this->pagination_helper->links();
                $this->model->notebook_id = $this->notebookId;
                $data['notebookForm'] = $this->model->getForm();
                
		// render to the view
		$this->view->set_data($data);
		$this->view->set_template('list');
		$this->view->render();
	}

	public function add() {
            $data = array();
            if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
                $check = Security::check_token(); 
        	if ($check) {
                    $this->model->user_id     = $this->userId;
                    $this->model->course      = $this->courseCode;     
                    $this->model->session_id  = $this->sessionId;
                    $this->model->title       = !empty($_POST['title'])?$_POST['title']:get_lang('Notebook').' #'.count($this->model->getNotesList($this->userId));   
                    $this->model->description = $_POST['description'];    
                    $lastInsertId = $this->model->save();
                    $this->notebookId = $lastInsertId;
                    $this->model->notebook_id = $this->notebookId;
                    unset($_POST);
                    Security::clear_token(); 
                    $this->show();
                } else {
                    $noteList = $this->pagination_helper->generate($this->model->getNotesList($this->userId));

                     // preparing the response
                     $data['noteList'] = $noteList;
                     $data['pagerLinks'] = $this->pagination_helper->links();
                     $this->model->notebook_id = $this->notebookId;
                     $data['notebookForm'] = $this->model->getForm();

                     // render to the view
                     $this->view->set_data($data);
                     $this->view->set_template('add');
                     $this->view->render();
                } 
            }  else {		
                $noteList = $this->pagination_helper->generate($this->model->getNotesList($this->userId));
                
                // preparing the response
                $data['noteList'] = $noteList;
                $data['pagerLinks'] = $this->pagination_helper->links();
                $this->model->notebook_id = $this->notebookId;
                $data['notebookForm'] = $this->model->getForm();
                
		// render to the view
		$this->view->set_data($data);
		$this->view->set_template('add');
		$this->view->render();
            }
	}
	
	public function edit() {
            $data = array();
            if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
                $check = Security::check_token(); 
        	if ($check) {
                    $this->model->user_id     = $this->userId;
                    $this->model->course      = $this->courseCode;     
                    $this->model->session_id  = $this->sessionId;
                    $this->model->title       = !empty($_POST['title'])?$_POST['title']:get_lang('Notebook').' #'.count($this->model->getNotesList($this->userId));   
                    $this->model->description = $_POST['description']; 
                    $this->model->notebook_id = $this->notebookId;
                    $lastInsertId = $this->model->save();
                    unset($_POST);
                    //unset($this->notebookId);
                    Security::clear_token();                    
                } 
                $this->show();
            } else {		
                $noteList = $this->pagination_helper->generate($this->model->getNotesList($this->userId));
                
                // preparing the response
                $data['noteList'] = $noteList;
                $data['pagerLinks'] = $this->pagination_helper->links();
                $this->model->notebook_id = $this->notebookId;
                $data['notebookForm'] = $this->model->getForm();
                
		// render to the view
		$this->view->set_data($data);
		$this->view->set_template('edit');
		$this->view->render();
            }

        }
        
        public function destroy() {
            if ($this->notebookId) {
                $this->model->notebook_id = $this->notebookId;
                $this->model->user_id     = $this->userId;
                $this->model->delete();
                unset($this->notebookId);
            }
            $this->listing();
        }
        
        public function show() {
            global $charset;
            $data = array();
            $notebook_info = array();
            $notebook_id = $this->notebookId;
            $notebookList  = $this->pagination_helper->generate($this->model->getNotesList());
            $notebook_info = $this->model->getNoteInfo($notebook_id);
            $this->model->notebook_id = $notebook_id;
            // preparing the response
            $data['notebookList'] = $notebookList;
            $data['pagerLinks']   = $this->pagination_helper->links();
            $data['notebook_id']  = $notebook_id;
            $data['charset']      = $charset;
            $data['notebook_tittle'] = $notebook_info['title'];
            $data['notebook_comment'] = $notebook_info['description'];

            // render to the view
            $this->view->set_data($data);
            $this->view->set_template('show');
            $this->view->render();
        }
        
        public function view() {
            global $charset;
            $data = array();
            $notebook_info = array();
            $notebook_id = $this->notebookId;
            $notebookList  = $this->pagination_helper->generate($this->model->getNotesList());
            $notebook_info = $this->model->getNoteInfo($notebook_id);
            $this->model->notebook_id = $notebook_id;
            // preparing the response
            $data['notebookList'] = $notebookList;
            $data['pagerLinks']   = $this->pagination_helper->links();
            $data['notebook_id']  = $notebook_id;
            $data['charset']      = $charset;
            $data['notebook_tittle'] = $notebook_info['title'];
            $data['notebook_comment'] = $notebook_info['description'];

            // render to the view
            $this->view->set_data($data);
            $this->view->set_template('view');
            $this->view->render();
        }
        
         public function search() {
            global $charset;
            $data = array();
            $notebook_info = array();
            $notebook_id = $this->notebookId;
            $notebookList  = $this->pagination_helper->generate($this->model->getNotesList());
            $notebook_info = $this->model->getNoteInfo($notebook_id);
            $this->model->notebook_id = $notebook_id;
            // preparing the response
            $data['notebookList'] = $notebookList;
            $data['pagerLinks']   = $this->pagination_helper->links();
            $data['notebook_id']  = $notebook_id;
            $data['charset']      = $charset;
            $data['notebook_tittle'] = $notebook_info['title'];
            $data['notebook_comment'] = $notebook_info['description'];

             //render to the view
            $this->view->set_data($data);
            $this->view->set_template('search');
            $this->view->render();
        }
        
        public function showSearch() {
            global $charset;
            $data = array();
            $notebook_info = array();
            $notebook_id = $this->notebookId;
            $notebookList  = $this->pagination_helper->generate($this->model->getNotesList());
            $notebook_info = $this->model->getNoteInfo($notebook_id);
            $this->model->notebook_id = $notebook_id;
            // preparing the response
            $data['notebookList'] = $notebookList;
            $data['pagerLinks']   = $this->pagination_helper->links();
            $data['notebook_id']  = $notebook_id;
            $data['charset']      = $charset;
            $data['notebook_tittle'] = $notebook_info['title'];
            $data['notebook_comment'] = $notebook_info['description'];

             //render to the view
            $this->view->set_data($data);
            $this->view->set_template('showSearch');
            $this->view->render();
        }
	
}
?>
