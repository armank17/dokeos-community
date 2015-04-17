<?php
/* For licensing terms, see /license.txt */

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action
 */
class GlossaryController extends Controller {

	private $toolname;
	private $view;
    private $model;
	private $userId;
    private $courseCode;
    private $sessionId;
    private $glossaryId;

	/**
	 * Constructor
	 */
	public function __construct($glossaryId = null) {
        $this->userId = api_get_user_id();
        $this->courseCode = api_get_course_id();
        $this->sessionId = api_get_session_id();
        if (isset($glossaryId)) {
           $this->glossaryId = $glossaryId;
        }

        // we load the model object
        $this->model = new GlossaryModel();
        
        // load objects (eg: helper)
        $this->load('pagination', 'helper');

        // we load the view object
        $this->toolname = 'glossary';
        $this->view = new View($this->toolname);
        $this->view->set_layout('layout');
        
        require_once (api_get_path(LIBRARY_PATH).'PHPExcel-1.7.7/Classes/PHPExcel.php');
	}

    /**
     * List the glossary terms
     */
	public function listing($glossaryTerm = null) {
           $data = array();
           
           // If term is 'a-z' we get all results in the view listing
           $glossaryTerm = ($glossaryTerm == 'az') ? null : $glossaryTerm;
           $glossaryList = $this->pagination_helper->generate($this->model->getGlossaryList($glossaryTerm));

           // preparing the response
           $data['glossaryList'] = $glossaryList;
           $data['pagerLinks']   = $this->pagination_helper->links();
           $this->model->glossary_id = $this->glossaryId;

           // render to the view
           $this->view->set_data($data);
           $this->view->set_template('list');
           $this->view->render();
	}
    /**
     *Add a glossary term
     */
	public function add() {
            $data = array();
            if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
                $check = Security::check_token();
        	if ($check) {
                    $this->model->user_id     = $this->userId;
                    $this->model->course      = $this->courseCode;
                    $this->model->session_id  = $this->sessionId;
                    $this->model->title       = $_POST['glossary_title'];
                    $this->model->description = $_POST['glossary_comment'];
                    $lastInsertId = $this->model->save();
                    unset($_POST);
                    unset($_GET);
                    Security::clear_token();
                }
               $this->listing();
            } else {
                // Display the import form
            $data['glossaryAddForm'] = $this->model->getForm();
                // render to the view
                $this->view->set_data($data);
                $this->view->set_template('add');
                $this->view->render();
            }


	}
    /**
     *Edit a glossary term
     */
	public function edit() {
            $data = array();
            if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
                $check = Security::check_token();
        	if ($check) {
                    $this->model->user_id     = $this->userId;
                    $this->model->course      = $this->courseCode;
                    $this->model->session_id  = $this->sessionId;
                    $this->model->title       = $_POST['glossary_title'];
                    $this->model->description = $_POST['glossary_comment'];
                    $this->model->glossary_id = $this->glossaryId;
                    $lastInsertId = $this->model->save();
                    unset($_POST);
                    Security::clear_token();
                }
               $this->showterm($this->glossaryId);
            } else {
                // Display the import form
            $this->model->glossary_id = $this->glossaryId;
            $data['glossaryEditForm'] = $this->model->getForm();
                // render to the view
                $this->view->set_data($data);
                $this->view->set_template('edit');
                $this->view->render();
            }
    }
    /**
     *Delete a glossary term
     */
    public function destroy() {
      if ($this->glossaryId) {
          $this->model->glossaryId = $this->glossaryId;
          $this->model->user_id     = $this->userId;
          $this->model->delete();
          unset($this->glossaryId);
      }
     $this->listing();
    }
    /**
     *Importe the glossary terms
     */
    public function import() {
        $formMessage = "";
        // Display the import form
        $data['glossaryImportForm'] = $this->model->getImportForm($formMessage);
        $data['formMessage'] = $formMessage;
		// render to the view
		$this->view->set_data($data);
		$this->view->set_template('import');
		$this->view->render();
    }
    /**
     *Importe the glossary terms
     */
    public function export() {
        // Display the export form
        $data['glossaryExportForm'] = $this->model->getExportForm();
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('export');
        $this->view->render();
    }
    /**
     * Allow show a glossary term
     */
    public function showterm($glossaryId) {
        global $charset;
        $data = array();
        $glossary_info = array();
        $glossaryList  = $this->pagination_helper->generate($this->model->getGlossaryList());
        $glossary_info = $this->model->getGlossaryInfo($glossaryId);

        // preparing the response
        $data['glossaryList'] = $glossaryList;
        $data['pagerLinks']   = $this->pagination_helper->links();
        $data['glossary_id']  = $glossaryId;
        $data['charset']      = $charset;
        $data['glossary_tittle'] = $glossary_info['glossary_title'];
        $data['glossary_comment'] = $glossary_info['glossary_comment'];

        $this->model->glossary_id = $glossaryId;
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('showterm');
        $this->view->render();
    }
}
?>
