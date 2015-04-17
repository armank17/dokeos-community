<?php

/* For licensing terms, see /license.txt */

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action 
 */
class DropboxController extends Controller {

    private $toolname;
    private $view;
    private $model;
    private $userId;
    private $courseCode;
    private $sessionId;
    private $catId;

    /**
     * Constructor
     */
    public function __construct($catId = null) {
        $this->userId = api_get_user_id();
        $this->courseCode = api_get_course_id();
        $this->sessionId = api_get_session_id();
        if (isset($catId)) {
            $this->catId = $catId;
            $this->model->cat_id = $this->catId;
        }
        // we load the model object
        $this->model = new DropboxModel();
        // we load the view object
        $this->toolname = 'dropbox';
        $this->view = new View($this->toolname);
        $this->view->set_layout('layout');
    }

    public function listing() {
        $data = array();
        isset($_REQUEST['view']) ? $view = Security :: remove_XSS($_REQUEST['view']) : $view = 'sent';
        if (empty($view)) {
            $view = 'sent';
        }

        // constructing the array that contains the total number of feedback messages per document.
        $number_feedback = $this->model->get_total_number_feedback();

        if ($view == 'sent') {
            $sentDropboxData = $this->model->getSentDropboxData();
            $data['sentDropboxData'] = $sentDropboxData;
        } else {
            $receivedDropboxData = $this->model->getReceivedDropboxData();
            $data['receivedDropboxData'] = $receivedDropboxData;
        }

        // preparing the response
        $data['totalNumberFeedback'] = $number_feedback;

        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('list');
        $this->view->render();
    }

    public function addCategory($action, $id = '') {
        $data = array();
        isset($_REQUEST['view']) ? $view = Security :: remove_XSS($_REQUEST['view']) : $view = 'sent';
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            $check = Security::check_token();
            if ($check) {
                if (empty($_POST['category_name'])) {
                    $data['errormessage'] = get_lang('FormHasErrorsPleaseComplete');
                    $data['addCategoryForm'] = $this->model->getCategoryForm($action, $id);
                } else {
                    $this->model->category_name = $_POST['category_name'];
                    $this->model->action = $_POST['action'];
                    $this->model->target = $_POST['target'];
                    $this->model->edit_id = $_POST['edit_id'];
                    $message = $this->model->savecategory();
                    $_SESSION["display_confirmation_message"] = $message;

                    unset($_POST);
                    $data['message'] = $message;
                    $data['addCategoryForm'] = $this->model->getCategoryForm($action, $id);
                }
            }
        } else {
            $data['addCategoryForm'] = $this->model->getCategoryForm($action, $id);
        }
        if ((strtoupper($_SERVER['REQUEST_METHOD']) == "POST")) {
            Security::clear_token();
        }

        // constructing the array that contains the total number of feedback messages per document.
        $number_feedback = $this->model->get_total_number_feedback();
        if ($view == 'sent') {
            $sentDropboxData = $this->model->getSentDropboxData();
            $data['sentDropboxData'] = $sentDropboxData;
        } else {
            $receivedDropboxData = $this->model->getReceivedDropboxData();
            $data['receivedDropboxData'] = $receivedDropboxData;
        }
        // preparing the response
        $data['totalNumberFeedback'] = $number_feedback;
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('list');
        $this->view->render();
    }

    public function deleteCategory($action, $id) {
        if (!empty($id)) {
            $this->model->category_id = $id;
            $message = $this->model->deletecategory($action, $id);
            //$_SESSION["display_confirmation_message"] = $message;
        }
        $this->listing();
    }

    public function deleteSentFile($id) {
        if (!empty($id)) {
            $this->model->dropboxfile_id = $id;
            $message = $this->model->deleteSentDropboxfile();
        }
        $this->listing();
    }

    public function add() {
        $data = array();
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            $this->model->cb_overwrite = $_POST['db_overwrite'];
            $this->model->recipients = $_POST['recipients'];
            $this->model->authors = $_POST['authors'];
            $this->model->description = $_POST['description'];
            $message = $this->model->addDropbox();
            $_SESSION["display_confirmation_message"] = $message;
            $this->listing();
        } else {
            // preparing the response
            $data['addForm'] = $this->model->getAddForm();
            // render to the view
            $this->view->set_data($data);
            $this->view->set_template('add');
            $this->view->render();
        }
    }

    public function deleteReceivedFile($id) {
        if (!empty($id)) {
            $this->model->dropboxfile_id = $id;
            $message = $this->model->deleteReceivedDropboxfile();
        }
        $this->listing();
    }

    public function storeFeedback() {
        $this->model->feedback = $_POST['feedback'];
        $message = $this->model->storeFeedback();
        $view = Security :: remove_XSS($_REQUEST['view']);
        $location = '';
        if ($view == 'received') {
            $location = "&view=received";
        }
        echo '<script type="text/javascript">window.location.href="' . api_get_path(WEB_PATH) . "main/core/views/dropbox/index.php?" . api_get_cidreq() . $location . '";</script>';
    }

    public function moveForm() {
        $data = array();

        isset($_REQUEST['view']) ? $view = Security :: remove_XSS($_REQUEST['view']) : $view = 'sent';
        // preparing the response
        $data['addCategoryForm'] = $this->model->getMoveForm();

        // constructing the array that contains the total number of feedback messages per document.
        $number_feedback = $this->model->get_total_number_feedback();

        if ($view == 'sent') {
            $sentDropboxData = $this->model->getSentDropboxData();
            $data['sentDropboxData'] = $sentDropboxData;
        } else {
            $receivedDropboxData = $this->model->getReceivedDropboxData();
            $data['receivedDropboxData'] = $receivedDropboxData;
        }

        // preparing the response
        $data['totalNumberFeedback'] = $number_feedback;

        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('list');
        $this->view->render();
    }

    public function storeMove() {
        $this->model->id = $_POST['id'];
        $this->model->move_target = $_POST['move_target'];
        $this->model->part = $_POST['part'];
        $message = $this->model->storeMove();
        $this->listing();
    }

    public function deleteReceived($checked_file_ids) {
        $message = $this->model->deleteReceived($checked_file_ids);
        $this->listing();
    }

    public function downloadFile($checked_file_ids) {
        $this->model->downloadDropbox($checked_file_ids);
        $this->listing();
    }

    public function display_action() {
        $display_icons = $this->model->getDisplayActionIcons($this->catId);
        echo $display_icons;
    }
}
?>