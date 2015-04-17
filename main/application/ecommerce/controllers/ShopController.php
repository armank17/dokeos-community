<?php

/**
 * controller shop only to course
 * @author Johnny, <johnny1402@gmail.com>
 * @package ecommerce 
 */
class application_ecommerce_controllers_Shop extends appcore_command_Command {

    private $model;
    public $cod_course;
    public $message = '';
    public $is_payment = FALSE;

    public function __construct() {
        $this->verifySession();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->setLanguageFile(array('admin', 'courses'));
        $this->verifyCourse();
        $this->loadAjax();
        $this->cod_course = $this->getRequest()->getProperty('course_code', 0);
    }

    public function verifySession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        if (api_get_setting('enable_shop_tool') === 'false') {
            api_not_allowed();
        }
    }

    private function loadAjax() {
        $this->ajax = new xajax();
        $this->ajax->setCharEncoding('ISO-8859-1');
        $this->ajax->setFlag("decodeUTF8Input", true);
        $this->ajax->setFlag("debug", false);
        $this->ajax->register(XAJAX_FUNCTION, array('uploadImage', $this, 'uploadImage'));
        $this->ajax->processRequest();
    }

    private function verifyCourse() {
        $this->cod_course = $this->getRequest()->getProperty('cidReq', 0);
        if (strlen(trim($this->cod_course)) > 0) {
            $arrayCourse = $this->model->getCourse($this->cod_course);
            if (count($arrayCourse) > 0) {
                if ($arrayCourse['payment'] == '1') {
                    $this->is_payment = TRUE;
                    $user = api_get_user_info(api_get_user_id());
                    if($user['status']== 1){//is admin
                        header('location: ' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Course&func=description&course_code=' . $this->cod_course.'&ref=home_course&cidReq='.$this->cod_course);
                    }else{
                        header('location: ' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Course&func=setting&ref=shop&cidReq=' . $this->cod_course);
                    }
                    exit();
                }
            }
        }
    }
    
    public function updatePayment(){
        $this->cod_course = $this->getRequest()->getProperty('cidReq', 0);
        $this->model->updatePayment($this->cod_course);
        header('location: ' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Course&func=description&course_code=' . $this->cod_course.'&'.api_get_cidreq().'&ref=home_course');
        exit();
    }
    
    public function vd($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

}