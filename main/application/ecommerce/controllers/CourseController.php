<?php

/**
 * controlador para editar información del curso
 * @author Johnny, <johnny1402@gmail.com>
 * @package ecommerce 
 */
class application_ecommerce_controllers_Course extends appcore_command_Command {

    private $model;
    private $cod_course;
    public $read_only = '';
    public $disabled = '';
    private $ref;
    public $title = '';
    public $course = array();

    public function __construct() {
        $this->verifySession();
        $this->load_ref();
        $this->setLanguageFile(array('admin', 'courses', 'index'));
        $this->loadAjax();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->cod_course = $this->getRequest()->getProperty('course_code', 0);
        $this->language_file = array("admin", "userInfo", "agenda");
    }

    /**
     * load variable ref to form input will readonly
     */
    private function load_ref() {
        $this->ref = $this->getRequest()->getProperty('ref', 'none');
        switch ($this->ref) {
            case 'home_course':
                $this->read_only = 'readonly="readonly"';
                $this->disabled = 'disabled="disabled"';
                break;
            case 'none':
                $this->read_only = '';
                $this->disabled = '';
                break;
            default:
                $this->read_only = '';
                $this->disabled = '';
                break;
        }
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
        //$this->ajax->setCharEncoding('ISO-8859-1');
        $this->ajax->setFlag("decodeUTF8Input", true);
        $this->ajax->setFlag("debug", false);
        $this->ajax->register(XAJAX_FUNCTION, array('uploadImage', $this, 'uploadImage'));
        $this->ajax->register(XAJAX_FUNCTION, array('editCourse', $this, 'editCourse'));
        $this->ajax->register(XAJAX_FUNCTION, array('calculateCostHT', $this, 'calculateCostHT'));
        $this->ajax->register(XAJAX_FUNCTION, array('calculateCostTTC', $this, 'calculateCostTTC'));
        $this->ajax->register(XAJAX_FUNCTION, array('loadPreviewCatalog', $this, 'loadPreviewCatalog'));
        $this->ajax->register(XAJAX_FUNCTION, array('updateCourseShop', $this, 'updateCourseShop'));
        $this->ajax->processRequest();
    }

    public function updateCourseShop($form) {
        $objResponse = new xajaxResponse();
        $objCourseBean = new stdClass();
        $objCourseBean->description = $form['description'];
        $objCourseBean->summary = $form['new_summary'];
//        if(api_is_platform_admin()){
//            $objCourseBean->cost = api_floatval($form['txtCostHT']);
//            $objCourseBean->cost_ttc = api_floatval($form['txtCostTTC']);
//            $objCourseBean->chr_type_cost = $form['priceType'];
//        }
        $this->updateCourse($objCourseBean, $form['id_item_commerce']);//actualiza los datos del curso
        
        if(strlen(trim($form['txtImageFile'])) > 0) {
            $this->updateImageById($form['txtImageFile'], $form['id_item_commerce']);
            $this->moveImage($objCourseBean);
        }
        if (isset($form['remove_img'])) {
            $this->updateImageById('', $form['id_item_commerce']);
            $html_image_preview = '<p class="fontPreview">100 x 100<p>';
            $objResponse->assign('divImgPreview', 'innerHTML', $html_image_preview);
        }
        ob_start();
        Display :: display_confirmation_message2(get_lang('Saved'), false, true);
        $html_message = ob_get_contents();
        ob_end_clean();
        $objResponse->assign('divMessage', 'innerHTML', $html_message);
        //$js = '$(".close_message_box").click(function() {$("#divMessage").html(\'\');});';
        $js = '$("#content").before($("#divMessage"));';
        $objResponse->script($js);
        return $objResponse;
    }

    public function loadPreviewCatalog($logo_sys_path,$logo_web_path) {
        $objResponse = new xajaxResponse();
        //$html = '<img src="application/ecommerce/assets/images/thumb_prod_large.jpg" />';
        foreach (glob($logo_sys_path . 'course_logo.*') as $path_file) {
                $new_file_path = pathinfo($path_file);
                $exts = array("jpg","jpeg","gif","png");
                if(in_array($new_file_path['extension'],$exts))
                        $image = $logo_web_path . $new_file_path['basename'];
        }
        if ($image){
            $image = $image;
        } else {
            $image = api_get_path('WEB_CSS_PATH').api_get_setting('stylesheets').'/images/logo1.png';
        }
        
        $html = ' <img id="img1" class="border-img-e" src="'.$image.' " /> 
                  <img style=" position: absolute; z-index: -1; left:0px" src="application/ecommerce/assets/images/thumb_prod_large.jpg"/>
';
        $objResponse->assign("divPreviewCatalog", "innerHTML", $html);
        $jquery = '$.fx.speeds._default = 1000;
            $(function(){
                                $("#divPreviewCatalog").dialog("open");
                                $("#divPreviewCatalog").dialog({
                                autoOpen: true,
                                title:"Preview",
                                resizable: false,
                                width: 730,
                                height: 348,
                                closeText:"'.get_lang("Close").'",
                                modal:true
                        });
                    });';
        $objResponse->script($jquery);
        return $objResponse;
    }

    public function calculateCostHT($form) {
        $objResponse = new xajaxResponse();
        $prixTTC = api_floatval($form['txtCostTTC']);
        $prixHT = api_get_price_ht($prixTTC);
        $prix = $prixTTC;

        if ($form['priceType'] == 'HT') {
            $prix = api_get_price_ht($prixTTC);
        }
        $prixHT = api_number_format($prixHT);
        $prix = api_number_format($prix);
        $objResponse->assign('txtCostHT', 'value', $prixHT);
        $objResponse->assign('txtCost', 'value', $prix);

        return $objResponse;
    }

    public function calculateCostTTC($form) {
        $objResponse = new xajaxResponse();
        $prix = api_floatval($form['txtCost']);
        $prixHT = api_get_price_ht($prix);
        $prixTTC = $prix;

        if ($form['priceType'] == 'HT') {
            $prixHT = $prix;
            $prixTTC = api_get_price_ttc($prix);
        }
        $prixHT = api_number_format($prixHT);
        $prixTTC = api_number_format($prixTTC);
        $objResponse->assign('txtCostHT', 'value', $prixHT);
        $objResponse->assign('txtCostTTC', 'value', $prixTTC);

        return $objResponse;
    }

    public function editCourse($form, $admin) {
        // $this->vd($form);

        $objResponse = new xajaxResponse();
        $admin = $form['admin'];
        $cost = strlen(floor($form['txtCost']));
        if($cost>4){
            $error = get_lang("IncorrectCost");
            $jquery = "$.alert('".$error."', 'Error', 'error');";
            $objResponse->script($jquery);
            return $objResponse;
        }
        if(intval(strlen((string)$form['txtDuration']))>2){
            $error = get_lang("IncorrectDuration");
            $jquery = "$.alert('".$error."', 'Error', 'error');";
            $objResponse->script($jquery);
            return $objResponse;
        }
        if (!$admin) {
            $objCourseBean = new stdClass();
            $objCourseBean->image = $form['txtImageFile'];
            if (isset($form['remove_img']))
                $objCourseBean->image = '';
            $objCourseBean->description = $form['textareaDescription'];
            $objCourseBean = $this->updateCourseTrainer($objCourseBean, $form['id_item_commerce']);

            if (!isset($form['remove_img']))
                if ($form['upload_image'] > 0)
                    $this->moveImage($objCourseBean);
            //$jquery = '$("<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>' . $objCourseBean->message . '</p>").dialog({title:"Alert", modal:true, buttons:{OK: function(){location.href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_courses.php";}}})';
            //$objResponse->script($jquery);
            ob_start();
                Display :: display_confirmation_message2(get_lang('Saved'), false, true);
                $html_message = ob_get_contents();
                ob_end_clean();
                $objResponse->assign('divMessage', 'innerHTML', $html_message);
                
                //$jquery = '$("<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>' . $objCourseBean->message . '</p>").dialog({title:"Alert", modal:true, buttons:{OK: function(){location.href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_courses.php";}}})';
                //$js = '$(".close_message_box").click(function() {$("#divMessage").html(\'\');});';
                $js = '$("#content").before($("#divMessage"));';
                $objResponse->script($js);
        }else {

            if ($form['id_category'] > 0) {
                $objCourseBean = new stdClass();
                $objCourseBean->cost = api_floatval($form['txtCostHT']);
                //$objCourseBean->item_type;
                $objCourseBean->status = $form['status'];
                //$objCourseBean->currency = $form['txtCost'];
                if (isset($form['duration_start']))
                    $objCourseBean->date_start = $this->setTime($form['duration_start']);
                if (isset($form['duration_end']))
                    $objCourseBean->date_end = $this->setTime($form['duration_end']);
                $objCourseBean->duration = $form['txtDuration'];
                $objCourseBean->duration_type = $form['duration_type'];
                $objCourseBean->image = $form['txtImageFile'];
                if (isset($form['remove_img']))
                    $objCourseBean->image = '';
                $objCourseBean->description = $form['textareaDescription'];
                $objCourseBean->summary = $form['new_summary'];
                $objCourseBean->id_category = $form['id_category'];
                /*
                if(!empty($form['txtCostTTC'])){
                    $objCourseBean->cost_ttc = api_floatval($form['txtCostTTC']);
                }else{
                    $objCourseBean->cost_ttc = '0.00';
                }
                 * 
                 */
                
                if(!empty($form['priceType'])){
                    $objCourseBean->chr_type_cost = $form['priceType'];
                    $objCourseBean->cost = api_floatval($form['txtCostHT']);
                    $objCourseBean->cost_ttc = api_floatval($form['txtCostTTC']);
                }else{
                    //0 = FREE
                    $objCourseBean->chr_type_cost = '0';
                    $objCourseBean->cost = '0.00';
                    $objCourseBean->cost_ttc = '0.00';
                }
                //$this->vd($objCourseBean);die();
                $objCourseBean = $this->updateCourse($objCourseBean, $form['id_item_commerce']);

                if (!isset($form['remove_img'])) {
                    if ($form['upload_image'] > 0) {
                        $this->moveImage($objCourseBean);
                    }
                } else {
                    $html_image_preview = '<p class="fontPreview">100 x 100<p>';
                    $objResponse->assign('divImgPreview', 'innerHTML', $html_image_preview);
                }
                ob_start();
                Display :: display_confirmation_message2(get_lang('Saved'), false, true);
                $html_message = ob_get_contents();
                ob_end_clean();
                $objResponse->assign('divMessage', 'innerHTML', $html_message);
                //$jquery = '$("<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>' . $objCourseBean->message . '</p>").dialog({title:"Alert", modal:true, buttons:{OK: function(){location.href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_courses.php";}}})';
                //$js = '$(".close_message_box").click(function() {$("#divMessage").html(\'\');});';
                $js = '$("#content").before($("#divMessage"));';
                $objResponse->script($js);
            } else {
                //$jquery = "jAlert('Select a category', 'Alert');";
                $jquery = "$.alert('".  get_lang("SelectACategory") ."', 'Error', 'error');";
                $objResponse->script($jquery);
            }
        }



        return $objResponse;
    }

    public function moveImage($objCourseBean) {
        $file = 'application/ecommerce/assets/images/' . $objCourseBean->image;
        $newfile = '../home/default_platform_document/ecommerce_thumb/' . $objCourseBean->image;
        umask(0);
        copy($file, $newfile);
    }

    public function updateCourse($objCourseBean, $id_item_commerce) {
        $model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->updateCourse($objCourseBean, $id_item_commerce);
        return $returnValue;
    }

    public function updateImageById($image, $id_item_commerce) {
        $model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->updateImageById($image, $id_item_commerce);
        return $returnValue;
    }

    public function updateCourseTrainer($objCourseBean, $id_item_commerce) {
        $model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->updateCourseTrainer2($objCourseBean, $id_item_commerce);
        return $returnValue;
    }

    public function uploadImage($image) {
        $image = $this->heal_string($image);
        $image = 'course_' . $image;
        $objResponse = new xajaxResponse();
        //init crop again
        $path_image = api_get_path(SYS_PATH).'main/application/ecommerce/assets/images/'.$image;
        
        $info = pathinfo($path_image);
        if($info['extension'] == 'png'){
            $mythumb = new appcore_library_thumbnail_Thumbnail();
            $new_image = $mythumb->resize2($path_image, 100, 100, api_get_path(SYS_PATH).'main/application/ecommerce/assets/images/'.$image);
            $html = '<img style="width:100px; height:100px" src="' . api_get_path(WEB_PATH).'main/application/ecommerce/assets/images/'.$image.'" />';            
        }else{
            $html = '<img style="width:100px; height:100px" src="' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Course&func=showImage&image=' . $image . '" />';
        }
        $objResponse->assign("divImgPreview", "innerHTML", $html);
        $objResponse->assign("txtImageFile", "value", $image);
        $objResponse->assign("upload_image", "value", '1');
        return $objResponse;
    }

    public function heal_string($string) {

        $string = trim($string);

        $string = str_replace(
                array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string
        );

        $string = str_replace(
                array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string
        );

        $string = str_replace(
                array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string
        );

        $string = str_replace(
                array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string
        );

        $string = str_replace(
                array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string
        );

        $string = str_replace(
                array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string
        );

        $string = str_replace(
                array("\\", "?", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "?", "?",
            ">", "< ", ";", ",", ":", " "), '', $string
        );


        return $string;
    }

    public function showImage() {
        header("Content-type: image/jpg");
        $image = $this->getRequest()->getProperty('image');
        $mythumb = new appcore_library_thumbnail_Thumbnail();
        $mythumb->loadImage('application/ecommerce/assets/images/' . $image);
        $mythumb->crop(100, 100);
        $mythumb->save('application/ecommerce/assets/images/' . $image);
        $mythumb->show();
    }

    public function getAction() {
        $html = '';
        switch ($this->ref) {
            case 'home_course':
                $html = '';
                break;
            case 'shop':
                $html = '<div class="actions">';
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Course&func=setting&ref=shop&' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('ShopSettings'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . get_lang('ShopSettings') . '</a>';
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Course&func=description&ref=shop&' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('CourseDescription'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('CourseDescription') . '</a>';
                $html.='</div>';
                break;
            default:
                $html = '<div class="actions">';
                //$objCatalog = new EcommerceCatalog();
                $objCatalog = $this->model->getCatalogSettings();

                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_settings.php">' . Display::return_icon('pixel.gif', get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('Settings') . '</a>';
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_catalog.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncatalog')) . get_lang('Catalog') . '</a>';
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Category">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncategory')) . $this->get_lang('langCategories') . '</a>';
                switch ($objCatalog->selected_value) {
                    case CATALOG_TYPE_SESSIONS:
                        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce" class="active">' . Display::return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
                        break;
                    case CATALOG_TYPE_COURSES:
                        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce" class="active">' . Display::return_icon('pixel.gif', get_lang('Courses'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
                        break;
                    case CATALOG_TYPE_MODULES:
                        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce" class="active">' . Display::return_icon('pixel.gif', get_lang('ModulePacks'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . get_lang('Products') . '</a>';
                        break;
                }
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_payment.php">' . Display::return_icon('pixel.gif', get_lang('Payment'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . get_lang('Payment') . '</a>';
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . get_lang('Invoices') . '</a>';
                $html.='</div>';
                break;
        }
        return $html;
    }

    public function getTextarea($name, $init) {
        $editor = new HTML_QuickForm_html_editor($name, get_lang('Description'), array(), array('ToolbarSet' => 'AnnouncementsStudent', 'Width' => '685px', 'Height' => '300'));
        $editor->setValue($init);
        $CKEditorOutput = $editor->toHtml();
        /*
          $CKEditor = new CKEditor();
          $CKEditor->returnOutput = true;
          $CKEditor->config['width'] = 685;
          $config['toolbar'] = array(
          array('Bold', 'Italic', 'Underline'),
          array('TextColor', 'BulletedList', 'NumberedList'
          ),
          array('Copy', 'PasteFromWord', 'Link', 'Smiley','Image')
          );
          $CKEditorOutput = $CKEditor->editor($name, $init, $config);
         */
        return $CKEditorOutput;
    }

    public function getCourseEcommerce() {
        $returnValue = (object) $this->model->getCourseEcommerce($this->cod_course);
        return $returnValue;
    }

    public function getCourse($code) {
        $returnValue = (object) $this->model->getCourse($code);
        return $returnValue;
    }

    public function getTime($date) {
        //format 2012-09-07 to 07/09/2012
        $newDate = preg_replace("/(\d+)\D+(\d+)\D+(\d+)/", "$2/$3/$1", $date);
        return $newDate;
    }

    public function setTime($date) {
        //format 07/09/2012 to 2012-09-07
        $newdate = preg_replace("/(\d+)\D+(\d+)\D+(\d+)/", "$3-$1-$2", $date);
        return $newdate;
    }

    public function getCoursePaginator() {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        $id_category = $this->getRequest()->getProperty('id_category', 0);
        $count = $this->getRequest()->getProperty('count', 1);
        $page = $this->getRequest()->getProperty('page', 1);
        $start = ceil(($page - 1) * 3);
        $objCollectionCourse = $this->model->getCoursePaginator($id_category);
        $input = array_slice($objCollectionCourse, $start, $count);
        echo json_encode($input);
        die();
    }

    public function getListCategory() {
        $returnValue = $this->model->getListCategory();
        return $returnValue;
    }

    public function getCountCategory() {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        $id_category = $this->getRequest()->getProperty('id_category', 0);
        $count = $this->model->getCountCategory($id_category);
        $count = ceil($count / 3);
        echo json_encode(array('cantidad' => $count));
        die();
    }

    public function getItemDetail() {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        $id_item = $this->getRequest()->getProperty('id', 0);
        $objItem = $this->model->getItem($id_item);
        echo json_encode($objItem);
        die();
    }

    public function getHtmlCoursePaginator() {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        $count = $this->getRequest()->getProperty('count', 1);
        $page = $this->getRequest()->getProperty('page', 1);
        $start = ceil(($page - 1) * 3);
        $objCollectionCourse = $this->model->getCoursePaginator();
        $input = array_slice($objCollectionCourse, $start, $count);

        foreach ($input as $index => $objCourse) {
            $html.='<table width="525px" style="margin-bottom:20px;">';
            $html.='<tr>';
            $html.='<td>';
            $html.='<img src="home/default_platform_document/ecommerce_thumb/' . $objCourse['image'] . '" />';
            $html.='</td>';
            $html.='<td>';
            $html.='<table>';
            $html.='<tr style="height:100px;">';
            $html.='<td style="vertical-align:top; padding-left:10px;width:400px;">';
            $html.='<strong style="font-zise:12px;">' . $this->model->getNameCourse($objCourse['code']) . '</strong><br />';
            $html.=$objCourse['description'];
            $html.='</td>';
            $html.='</tr>';
            $html.='<tr  style="height:20px;">';
            $html.='<td style="text-align:right;">';
            $html.='<span style="margin-right:15px;" id="ide"><a href="#" id="learnmore">Learn more</a></span>';
            $html.='<a href="#">Add to cart</a>';
            $html.='</td>';
            $html.='</tr>';
            $html.='</table>';



            $html.='</td>';
            $html.='</tr>';
            $html.='</table>';

            echo $html;
            die();
        }
    }

    public function description() {
        $this->title = $this->get_lang('CourseDescription');
        $code_course = $this->getRequest()->getProperty('cidReq', 0);
        if ($code_course == '0') {
            $arrayCourse = array();
            $this->course = array();
        } else {
            $arrayCourse = $this->model->getItemByCourse($code_course);
            $this->course = api_get_course_info($arrayCourse['code']);
        }
        $this->item = $arrayCourse;
    }

    public function setting() {
        $this->title = $this->get_lang('ShopSettings');
        $code_course = $this->getRequest()->getProperty('cidReq', 0);
        if ($code_course == '0') {
            $objCourse = array();
        } else {
            $objCourse = $this->model->getItemByCourse($code_course);
        }
        $this->course = $objCourse;
    }

}
