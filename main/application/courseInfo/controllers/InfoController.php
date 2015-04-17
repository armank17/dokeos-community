<?php

/**
 * controller course_info
 * 
 * @package courseInfo 
 */
class application_courseInfo_controllers_Info extends appcore_command_Command {

    public $courseInfo;

    public function __construct() {
        $this->verifySession();

        $this->courseInfo = api_get_course_info();

//        $tk = api_get_crop_token();

//        $this->imageParam = array(
//            'LOGO_HOME' => array(
//                'PERMISSION' => array('FUNC' => api_is_platform_admin),
//                'PATH' => array(
//                    'SYS' => api_get_path(SYS_PATH) . 'home/logo/',
//                    'WEB' => api_get_path(WEB_PATH) . 'home/logo/',
//                    'SWITCH' => true
//                ),
//                'DIMENSION' => array(
//                    'W' => 200,
//                    'H' => 50,
//                    'Ratio' => 200/50,
//                    'Resize' => 'false',
//                ),
//                'NOMENCLATURE' => array(
//                    'FINAL' => 'logo_home',
//                    'TEMP' => 'temp_logo_home',
//                    'SEARCH' => 'temp_logo_home.*'
//                ),
//                'NOTFOUND' => array(
//                    'SRC' => api_get_path(WEB_CODE_PATH) . 'img/thumbnail-course.png'
//                ),
//            ),
//            'PICTURE_USER' => array(
//                'PERMISSION' => array('FUNC' => api_is_platform_admin),
//                'PATH' => array(
//                    'SYS' => api_get_path(SYS_PATH) . 'main/upload/users/',
//                    'WEB' => api_get_path(WEB_PATH) . 'main/upload/users/',
//                    'SWITCH' => false
//                ),
//                'DIMENSION' => array(
//                    'W' => 165,
//                    'H' => 155,
//                    'Ratio' => 165/155,
//                    'Resize' => 'false',
//                ),
//                'NOMENCLATURE' => array(
//                    'FINAL' => 'picture_user_' . $tk,
//                    'TEMP' => 'temp_picture_user_' . $tk,
//                    'SEARCH' => 'temp_picture_user_' . $tk . '.*',
//                ),
//                'NOTFOUND' => array(
//                    'SRC' => api_get_path(WEB_CODE_PATH) . 'img/thumbnail-course.png',
//                ),
//            ),
//            'LOGO_SESSION' => array(
//                'PERMISSION' => array(
//                    'FUNC' => api_is_platform_admin
//                ),
//                'PATH' => array(
//                    'SYS' => api_get_path(SYS_PATH) . 'home/default_platform_document/ecommerce_thumb/',
//                    'WEB' => api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/',
//                    'SWITCH' => false
//                ),
//                'DIMENSION' => array(
//                    'W' => 185,
//                    'H' => 140,
//                    'Ratio' => 185/140,
//                    'Resize' => 'false',
//                ),
//                'NOMENCLATURE' => array(
//                    'FINAL' => 'logo_session_' . $tk,
//                    'TEMP' => 'temp_logo_session_' . $tk,
//                    'SEARCH' => 'temp_logo_session_' . $tk . '.*'
//                ),
//                'NOTFOUND' => array(
//                    'SRC' => api_get_path(WEB_CODE_PATH) . 'img/thumbnail-course.png'
//                ),
//            ),
//            'LOGO_EXAM' => array(
//                'PERMISSION' => array(
//                    'FUNC' => api_is_allowed_to_edit
//                ),
//                'PATH' => array(
//                    'SYS' => api_get_path(SYS_PATH) . 'courses/' . $this->courseInfo['path'] . '/upload/exam/',
//                    'WEB' => api_get_path(WEB_PATH) . 'courses/' . $this->courseInfo['path'] . '/upload/exam/',
//                    'SWITCH' => false
//                ),
//                'DIMENSION' => array(
//                    'W' => 180,
//                    'H' => 164,
//                    'Ratio' => 180/164,
//                    'Resize' => 'false',
//                ),
//                'NOMENCLATURE' => array(
//                    'FINAL' => 'logo_exam_' . $tk,
//                    'TEMP' => 'temp_logo_exam_' . $tk,
//                    'SEARCH' => 'temp_logo_exam_' . $tk . '.*',
//                ),
//                'NOTFOUND' => array(
//                    'SRC' => api_get_path(WEB_CODE_PATH) . 'img/thumbnail-course.png',
//                ),
//            ),
//            'PICTURE_SLIDE' => array(
//                'PERMISSION' => array(
//                    'FUNC' => api_is_allowed_to_edit
//                ),
//                'PATH' => array(
//                    'SYS' => api_get_path(SYS_PATH) . 'home/default_platform_document/',
//                    'WEB' => api_get_path(WEB_PATH) . 'home/default_platform_document/',
//                    'SWITCH' => false
//                ),
//                'DIMENSION' => array(
//                    'W' => 720,
//                    'H' => 241,
//                    'Ratio' => 720/240,
//                    'Resize' => 'false',
//                ),
//                'NOMENCLATURE' => array(
//                    'FINAL' => 'picture_slide_' . $tk,
//                    'TEMP' => 'temp_picture_slide_' . $tk,
//                    'SEARCH' => 'temp_picture_slide_' . $tk . '.*',
//                ),
//                'NOTFOUND' => array(
//                    'SRC' => api_get_path(WEB_CODE_PATH) . 'img/thumbnail-course.png',
//                ),
//            ),
//            'PICTURE_AUTHOR' => array(
//                'PERMISSION' => array(
//                    'FUNC' => api_is_allowed_to_edit
//                ),
//                'PATH' => array(
//                    'SYS' => api_get_path(SYS_PATH) . 'courses/' . $this->courseInfo['path'] . '/document/',
//                    'WEB' => api_get_path(WEB_PATH) . 'courses/' . $this->courseInfo['path'] . '/document/',
//                    'SWITCH' => false
//                ),
//                'DIMENSION' => array(
//                    'W' => 601,
//                    'H' => 601,
//                    'Ratio' => 0,
//                    'Resize' => 'true',
//                ),
//                'NOMENCLATURE' => array(
//                    'FINAL' => 'picture_author_' . $tk,
//                    'TEMP' => 'picture_author_' . $tk,
//                    'SEARCH' => 'picture_author_' . $tk . '.*',
//                ),
//                'NOTFOUND' => array(
//                    'SRC' => api_get_path(WEB_CODE_PATH) . 'img/thumbnail-course.png',
//                ),
//                'RUN' => array(
//                    'AFTER' => '
//                        $.ajax({
//                            type : "POST",
//                            url:runParams.url,
//                            data: {imageFileName:obj.final},
//                            async: false,
//                            dataType: "json",
//                            success: function(r) {}
//                        });
//                        ',
//                ),
//            ),
//        );

//        $this->imageExt = array('jpg', 'jpeg', 'gif', 'png');
    }

    public function verifySession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
    }

    public function uploadLogo() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
    }
    public function uploadImageQuiz() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
    }

    public function uploadHomeLogo() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
    }

    public function uploadShopLogo() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
    }

    public function uploadSessionLogo() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
    }

    public function uploadSlide() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
    }

//    public function cropImage() {
//        extract($_GET);
//        $to = strtoupper($to);
//
//        if (!$this->imageParam[$to]['PERMISSION']['FUNC']())
//            exit;
//
//        $this->loadIframeHtmlHeadXtra();
//        $this->setReducedHeader();
//        $this->disabledFooterCore();
//
//        $imagepath = $this->imageParam[$to]['PATH']['SYS'] . $this->imageParam[$to]['NOMENCLATURE']['SEARCH'];
//
//        foreach (glob($imagepath) as $pathfile) {
//            $pi = pathinfo($pathfile);
//            if (in_array(strtolower($pi['extension']), $this->imageExt)) {
//                $image_src = $this->imageParam[$to]['PATH']['WEB'] . $pi['basename'];
//                $ext = $pi['extension'];
//            }
//        }
//
//        $this->ext = $ext;
//        $this->to = $to;
//        $this->image = (!empty($image_src)) ? $image_src : $this->imageParam[$to]['NOTFOUND']['SRC'];
//    }

    public function loadIframeHtmlHeadXtra() {
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.form.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jquery.jcrop/js/jquery.Jcrop.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jquery.jcrop/css/jquery.Jcrop.min.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH) . 'application/courseInfo/assets/js/infoFunctions.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH) . 'application/author/assets/js/authorModel.js');
    }
}