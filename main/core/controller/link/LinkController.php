<?php
/* For licensing terms, see /license.txt */

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action 
 */
class LinkController extends Controller {	
		
    private $toolname;
    private $view; 
    private $model;
    private $userId;
    private $courseCode;
    private $sessionId;
    private $linkId;

    /**
     * Constructor
     */
    public function __construct($linkId = null) {		
            $this->userId = api_get_user_id();
            $this->courseCode = api_get_course_id();
            $this->sessionId = api_get_session_id();
            if (isset($linkId)) {
                    $this->linkId = $linkId;			
            }

            // we load the model object
            $this->model = new LinkModel();              

            // we load the view object
            $this->toolname = 'link';            
            $this->view = new View($this->toolname);
            $this->view->set_layout('layout');

    }

    public function listing() {
            $data = array();
            
            $no_of_links = $this->model->getNumberOfLinks();	
            $zeroCategoryLinks = $this->model->getLinksofCategory(0);
            $linkCategories = $this->model->getCategoryLinks();
            $totalCategories = $this->model->getTotalCategories();

            // preparing the response
            $data['no_of_links'] = $no_of_links;		
            $data['zeroCategoryLinks'] = $zeroCategoryLinks;
            $data['linkCategories'] = $linkCategories;
            $data['totalCategories'] = $totalCategories;
            // render to the view
            $this->view->set_data($data);
            $this->view->set_template('list');
            $this->view->render();

    }	

    public function display_action() {

            $display_icons = $this->model->getDisplayActionIcons($this->linkId);	
            echo $display_icons;
    }

    public function addcategory() {
            $data = array();

            if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {

                    if(empty($_POST['category_title'])) {
                            $data['errormessage'] = get_lang('GiveCategoryName');
                            $data['addCategoryForm'] = $this->model->getAddCategoryForm();
                            // render to the view
                            $this->view->set_data($data);
                            $this->view->set_template('add');
                            $this->view->render();
                    }
                    else {
                            $this->model->category_title			 = $_POST['category_title'];
                            $this->model->savecategory();
                            unset($_POST);

                            $this->listing();
                    }

            }
            else {
                    $data['addCategoryForm'] = $this->model->getAddCategoryForm();
                    // render to the view
                    $this->view->set_data($data);
                    $this->view->set_template('add');
                    $this->view->render();
            }
    }

    public function editcategory($categoryId) {
            $data = array();

            if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {

                    if(empty($_POST['category_title'])) {
                            $data['errormessage'] = get_lang('GiveCategoryName');
                            $data['addCategoryForm'] = $this->model->getAddCategoryForm();
                            // render to the view
                            $this->view->set_data($data);
                            $this->view->set_template('add');
                            $this->view->render();
                    }
                    else {
                            $this->model->category_title			 = $_POST['category_title'];
                            $this->model->savecategory($categoryId);
                            unset($_POST);

                            $this->listing();
                    }

            }
            else {

                    $categoryInfo = $this->model->getCategoryInfo($categoryId);
                    $this->model->category_title = $categoryInfo['category_title'];
                    $data['addCategoryForm'] = $this->model->getAddCategoryForm($categoryId);
                    // render to the view
                    $this->view->set_data($data);
                    $this->view->set_template('add');
                    $this->view->render();
            }
    }

    public function deletecategory($categoryId) {
            if (!empty($categoryId)) {
                    $this->model->category_id = $categoryId;			
                    $this->model->deletecategory();			
            }
            $this->listing();
    }

    public function addlink($add_params_for_lp) {
        $data = array();
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            $urllink = Security::remove_XSS($_POST['urllink']);
            if (empty ($urllink) OR $urllink == 'http://') {
                $data['errormessage'] = get_lang('GiveURL');
                $data['addLinkForm'] = $this->model->getAddLinkForm($add_params_for_lp);
                // render to the view
                $this->view->set_data($data);
                $this->view->set_template('addlink');
                $this->view->render();
            } else {
                $this->model->title = trim(Security::remove_XSS($_POST['title']));
                $this->model->urllink = trim(Security::remove_XSS($_POST['urllink']));
                if(empty($_POST['title'])){
                        $this->model->title = trim(Security::remove_XSS($_POST['urllink']));
                }

                $this->model->description = trim(Security::remove_XSS($_POST['description']));
                $this->model->selectcategory = Security::remove_XSS($_POST['selectcategory']);
                $this->model->onhomepage = Security::remove_XSS($_POST['onhomepage']);
                $this->model->target_link = Security::remove_XSS($_POST['target_link']);

                $this->model->savelink();
                $this->listing();
            }		

        } else {
            $this->model->urllink = 'http://';
            $data['addLinkForm'] = $this->model->getAddLinkForm($add_params_for_lp);
            // render to the view
            $this->view->set_data($data);
            $this->view->set_template('addlink');
            $this->view->render();
        }
    }

    public function editlink($add_params_for_lp) {
        global $charset;

        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
                $this->model->title = trim(Security::remove_XSS($_POST['title']));
                $this->model->urllink = trim(Security::remove_XSS($_POST['urllink']));
                $this->model->description = trim(Security::remove_XSS($_POST['description']));
                $this->model->selectcategory = Security::remove_XSS($_POST['selectcategory']);
                $this->model->onhomepage = Security::remove_XSS($_POST['onhomepage']);
                $this->model->target_link = Security::remove_XSS($_POST['target_link']);
                $this->model->link_id = $this->linkId;

                $this->model->updatelink();
                $this->listing();
        } else {
            $linkInfo = $this->model->getLinkInfo($this->linkId);

            if (empty($linkInfo['url'])) {
                $this->model->urllink = 'http://';
            } else {
                $this->model->urllink = api_htmlentities($this->urllink, ENT_COMPAT, $charset);
            }
            $this->model->urllink = $linkInfo['url'];
            $this->model->title = $linkInfo['title'];
            $this->model->description = $linkInfo['description'];
            $this->model->category = $linkInfo['category_id'];
            $this->model->target_link = $linkInfo['target'];
            if ($linkInfo['on_homepage'] <> 0) {
                $this->model->onhomepage = 'checked';
            }
            $data['addLinkForm'] = $this->model->getAddLinkForm($add_params_for_lp,$this->linkId);
            // render to the view
            $this->view->set_data($data);
            $this->view->set_template('addlink');
            $this->view->render();
        }
    }

    public function deletelink() {
        if (!empty($this->linkId)) {
            $this->model->link_id = $this->linkId;			
            $this->model->deletelink();			
        }
        $this->listing();
    }

    public function changeVisibility() {
        if (!empty($this->linkId)) {
            $this->model->link_id = $this->linkId;			
            $this->model->changeVisibility();			
        }
        $this->listing();
    }

    public function integrateLinkInCourse() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
            
            $this->model->link_id = $this->linkId;	
            $this->model->title = Security::remove_XSS($_POST['link_title']);
            $this->model->course = Security::remove_XSS($_POST['course']);
            $this->model->addLinkToCourse();

            $this->listing();
        } else {
            if (!empty($this->linkId)) {
                $this->model->link_id = $this->linkId;	
                $linkInfo = $this->model->getLinkInfo($this->linkId);
                $this->model->url = $linkInfo['url'];
                $this->model->title = $linkInfo['title'];
                $data['integrateInCourseForm'] = $this->model->integrateLinkInCourse();	

                // render to the view
                $this->view->set_data($data);
                $this->view->set_template('integrate_linkin_course');
                $this->view->render();
            }
        }
    }

    public function updateRecordsListings($disporder, $type) {
        $this->model->disporder = $disporder;
        $this->model->type = $type;
        if ($type != 'categories') {
            $this->model->itemId = $_GET['itemId'];
            $this->model->category_id = $_GET['category_id'];
        }
        $this->model->updateRecordsListings();

        $this->listing();
    }

    public function changeLinkCategory($itemId, $categoryId) {
        $this->model->itemId = $itemId;
        $this->model->category_id = $categoryId;
        $this->model->changeLinkCategory();
    }
}