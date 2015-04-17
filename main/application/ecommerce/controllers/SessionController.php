<?php

/**
 * controlador para editar información de la session
 * @author Johnny, <johnny1402@gmail.com>
 * @package ecommerce 
 */
class application_ecommerce_controllers_Session extends appcore_command_Command {

    public $model;
    private $session_id;
    private $action;
    public $css;

    public function __construct() {
        $this->verifySession();
        $this->setLanguageFile(array('courses', 'admin', 'index'));
        $this->loadAjax();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->session_id = $this->getRequest()->getProperty('id_session', 0);
        $this->action = $this->getRequest()->getProperty('action', '');

        if ($this->action === 'delete') {
            $arrayItem = $this->getRequest()->getProperty('id', array());
            $this->deleteAllSession($arrayItem);
        }

        $this->css = 'application/ecommerce/assets/css/style.css';
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
        $this->ajax->register(XAJAX_FUNCTION, array('editSession', $this, 'editSession'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteSession', $this, 'deleteSession'));
        $this->ajax->register(XAJAX_FUNCTION, array('delete', $this, 'delete'));
        $this->ajax->register(XAJAX_FUNCTION, array('selectAll', $this, 'selectAll'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteAll', $this, 'deleteAll'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteAllSession', $this, 'deleteAllSession'));
        $this->ajax->register(XAJAX_FUNCTION, array('addSession', $this, 'addSession'));
        $this->ajax->register(XAJAX_FUNCTION, array('addSessionItemCommerce', $this, 'addSessionItemCommerce'));
        $this->ajax->register(XAJAX_FUNCTION, array('active', $this, 'active'));
        $this->ajax->register(XAJAX_FUNCTION, array('calculateCostHT', $this, 'calculateCostHT'));
        $this->ajax->register(XAJAX_FUNCTION, array('calculateCostTTC', $this, 'calculateCostTTC'));
        $this->ajax->register(XAJAX_FUNCTION, array('loadPreviewCatalog', $this, 'loadPreviewCatalog'));
        $this->ajax->processRequest();
    }

    public function loadPreviewCatalog($sys_image,$web_image) {
        $objResponse = new xajaxResponse();
        //$html = '<img src="application/ecommerce/assets/images/thumb_prod_large.jpg" />';
        $image = (file_exists($sys_image) ? $web_image : 'application/ecommerce/assets/images/thumb_shop_small.png' );
       $html = $image ? $image :"application/ecommerce/assets/images/thumb_prod_large.jpg";
        $html = '<img id="img1" class="border-img-e"
            style=" display: block;
                    left: 58px;
                    position: absolute;
                    top: 65px;
                    z-index: 2;" 
            src="'.$image.'" />
                <img style=" position: absolute;
                            z-index: 1;" 
               src="application/ecommerce/assets/images/thumb_prod_large.jpg"/>';
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

    public function active($active, $id_item) {
        $objResponse = new xajaxResponse();
        $form['status'] = $active;
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->model->updateActiveItem($form, $id_item);
        $div = 'div_' . $id_item;
        $html = '<span class="ecommerce_link" onclick="xajax_active(1, ' . $id_item . ')"><img title="' . $this->get_lang('langVisible') . '" class="actionplaceholdericon actioninvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span>';
        if ($active)
            $html = '<span class="ecommerce_link" onclick="xajax_active(0, ' . $id_item . ')"><img title="' . $this->get_lang('langVisible') . '" class="actionplaceholdericon actionvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span>';
        $objResponse->assign($div, "innerHTML", $html);
        return $objResponse;
    }

    public function addSessionItemCommerce($id_session) {
        $objResponse = new xajaxResponse();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $aSession = $this->model->loadSession($id_session);
        $arrayItem['code'] = '-';
        $arrayItem['cost'] = api_floatval($aSession['cost']);
        $arrayItem['item_type'] = 3;
        $arrayItem['status'] = 1;
        $arrayItem['currency'] = 0;
        $arrayItem['date_start'] = $aSession['date_start'];
        $arrayItem['date_end'] = $aSession['date_end'];
        $arrayItem['duration'] = $aSession['duration'];
        $arrayItem['duration_type'] = $aSession['duration_type'];
        $arrayItem['image'] = $aSession['image'];
        $arrayItem['description'] = $aSession['description'];
        $arrayItem['id_category'] = 0;
        $arrayItem['id_session'] = $aSession['id'];
        $this->model->saveItem($arrayItem);
        $html = $this->getHtmlList();
        $htmlListMain = $this->getHtmlListSession();
        $objResponse->assign("divFormCategory", "innerHTML", $html);
        $objResponse->assign("divListCategory", "innerHTML", $htmlListMain);
        return $objResponse;
    }

    public function addSession() {
        $objResponse = new xajaxResponse();
        $html = $this->getHtmlList();
        $objResponse->assign("divFormCategory", "innerHTML", $html);
        $jquery = '$(function(){
                                $("#divFormCategory").dialog("open");
                                $("#divFormCategory").dialog({
                                autoOpen: true,
                                title:"' . get_lang('AddProducts') . '",
                                resizable: false,
                                width: 400,
                                height: 300,
                                modal:true,
                                close: function(event, ui) { 
                                   location.href = "' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Session";
                                   return false;
                                },
                                buttons: {
                                  "' . get_lang('Close') . '": function() {
                                            //$(this).dialog("close");
                                            location.href = "' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Session";
                                            return false;
                                        }
                                }
                        });
                    });';
        $objResponse->script($jquery);
        return $objResponse;
    }

    public function getHtmlList() {
        $html = '<table style="100%" class="data_table">
            <tr class="row_odd">
                <th style="width:5%;"></th>
                <th style="width:85%;">Session</th>
                <th style="width:10%;">Action</th>
            </tr>';
        $objCollectionSessionOuter = $this->listSessionOuter();
        if (count($objCollectionSessionOuter) > 0) {
            foreach ($objCollectionSessionOuter as $index => $arraySession) {
                $html.='<tr class="' . (($index) % 2 ? 'row_odd' : 'row_even') . '">';
                $html.='<td>' . ($index + 1) . '</td>';
                $html.='<td>' . ($arraySession['name']) . '</td>';
                $html.='<td><center><div id="divSession_' . $arraySession['id'] . '" onclick="xajax_addSessionItemCommerce(' . $arraySession['id'] . ')"><img src="application/ecommerce/assets/images/action-slide-down.png" /></div></center></td>';
                $html.='</tr>';
            }
        }
        else
            $html.='<tr><td style="width:100%;" colspan="3">' . $this->get_lang('TheListIsEmpty') . '</td></tr>';
        return $html;
    }

    public function listSessionOuter() {
        $listSessionItem = $this->getListSession();
        $arrayIdSessionItem = array();
        if (count($listSessionItem) > 0) {
            foreach ($listSessionItem as $index => $arrayItem)
                if ($arrayItem['id_session'] > 0 && $arrayItem['id_session'] != NULL)
                    array_push($arrayIdSessionItem, $arrayItem['id_session']);
        }

        $returnValue = $this->model->listSessionOuter($arrayIdSessionItem);

        return $returnValue;
    }

    public function deleteAllSession($arrayItem) {
        if (!empty($arrayItem)) {
            $imploded = implode(',', $arrayItem);
            $this->model->deleteAllSession($imploded);
        }
        /*
          $objResponse = new xajaxResponse();
          $this->model->deleteAllSession($arrayItem);
          $html = $this->getHtmlListSession();
          $objResponse->assign("divListCategory", "innerHTML", $html);
          return $objResponse;
         */
    }

    public function deleteAll($form) {
        $objResponse = new xajaxResponse();
        if (count($form['checkdelete']) > 0) {
            $id = implode(",", $form['checkdelete']);
            $jquery = "jConfirm('Are you sure?', 'Session', function(r) {
                        if(r)
                            xajax_deleteAllSession('" . $id . "');
                   });";
            $objResponse->script($jquery);
        }
        return $objResponse;
    }

    public function selectAll() {
        $objResponse = new xajaxResponse();
        $jquery = "$('input').each( function() {
                        $(this).attr('checked',status);
                    })";
        $objResponse->script($jquery);
        return $objResponse;
    }

    public function delete($id_session = null) {

        if (!isset($id_session)) {
            $id_session = $this->session_id;
        }
        $this->model->deleteItemBySession($id_session);
        /*
          $objResponse = new xajaxResponse();
          $this->model = new application_ecommerce_models_ModelEcommerce();
          $this->model->deleteItemBySession($id_session);
          $html = $this->getHtmlListSession();
          $objResponse->assign("divListCategory", "innerHTML", $html);
          return $objResponse; */
    }

    public function deleteSession($id_session) {
        $objResponse = new xajaxResponse();
        $jquery = "jConfirm('Are you sure?', 'Category', function(r) {
                        if(r)
                            xajax_delete(" . $id_session . ");
                   });";
        $objResponse->script($jquery);
        return $objResponse;
    }

    public function getHtmlListSession() {
        $html = '<table style="100%" class="data_table">
            <tr class="row_odd">
                <th style="width:10px;"><input onclick="xajax_selectAll()" type="checkbox" name="checkdelete[]" id="checkdelete"></th>
                <th style="width:20px;">Id</th>
                <th style="width:300px;">' . $this->get_lang('Title') . '</th>
                <th style="width:150px;">' . ($this->get_lang('Category')) . '</th>
                <th style="width:100px;">' . $this->get_lang('Price') . '</th>
                <th style="width:100px;">' . ($this->get_lang('Duration')) . '</th>
                <th style="width:100px;">' . $this->get_lang('Catalog') . '</th>
                <th style="width:100px;">' . $this->get_lang('EcommerceAction') . '</th>
            </tr>';
        if (count($this->getListSession()) > 0) {
            $collectionSession = $this->getListSession();
            foreach ($collectionSession as $index => $arraySession) {
                $html.='<tr class="' . (($index) % 2 ? 'row_odd' : 'row_even') . '">';
                $html.='<td><input type="checkbox" name="checkdelete[]" id="checkdelete" value="' . $arraySession['id_category'] . '"></td>';
                $html.='<td><center>' . $arraySession['id_session'] . '</center></td>';
                $html.='<td>' . ($this->getTitleSession($arraySession['id_session'])) . '</td>';
                if ($arraySession['id_category'] == null)
                    $html.='<td>' . $arraySession['id_category'] . '</td>';
                else
                    $html.='<td>' . $this->getCategory($arraySession['id_category'])->chr_category . '</td>';
                $html.='<td><center>' . $arraySession['cost'] . '</center></td>';
                $html.='<td><center>' . $arraySession['duration'] . ' ' . $arraySession['duration_type'] . '</center></td>';
                if ($arraySession['status'])
                    $html.='<td><center><div id="div_' . $arraySession['id_session'] . '"><span class="ecommerce_link" onclick="xajax_active(0, ' . $arraySession['id_session'] . ')"><img title="' . $this->get_lang('langVisible') . '" class="actionplaceholdericon actionvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div></center></td>';
                else
                    $html.='<td><center><div  id="div_' . $arraySession['id_session'] . '"><span class="ecommerce_link" onclick="xajax_active(1, ' . $arraySession['id_session'] . ')"><img title="' . $this->get_lang('langInvisible') . '" class="actionplaceholdericon actioninvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div><c/enter></td>';
                $html.='<td><center><a href="index.php?module=ecommerce&cmd=Session&func=edit&id_session=' . $arraySession['id_session'] . '" ><img title="' . $this->get_lang('langModify') . '" class="actionplaceholdericon actionedit" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></a>  <span class="ecommerce_link" onclick="xajax_deleteSession(' . $arraySession['id_session'] . ')"><img title="' . $this->get_lang('langDelete') . '" class="actionplaceholdericon actiondelete" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></center></td>';
                $html.='</tr>';
            }
        }
        else {
            $html.='<tr><td style="width:100%;" colspan="8">' . $this->get_lang('TheListIsEmpty') . '</td></tr>';
        }
        $html.='</table>';
        return $html;
    }

    public function uploadImage($image) {
        $image = 'session_' . $image;
        $objResponse = new xajaxResponse();
        //init crop again
        $path_image = api_get_path(SYS_PATH).'main/application/ecommerce/assets/images/'.$image;
        $info = pathinfo($path_image);
        if($info['extension'] == 'png'){
            $mythumb = new appcore_library_thumbnail_Thumbnail();
            $new_image = $mythumb->resize2($path_image, 100, 100, api_get_path(SYS_PATH).'home/default_platform_document/ecommerce_thumb/'.$image);
            $html = '<img style="width:100px; height:100px" src="' . api_get_path(WEB_PATH).'home/default_platform_document/ecommerce_thumb/'.$image.'" />';            
        }else{
            $html = '<img style="width:100px; height:100px" src="' . api_get_path(WEB_PATH) . 'home/default_platform_document/ecommerce_thumb/' . $image . '" />';
        }        
        //$html = '<img src="' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Course&func=showImage&image=' . $image . '" />';
        $objResponse->assign("divImgPreview", "innerHTML", $html);
        $objResponse->assign("txtImageFile", "value", $image);
        $objResponse->assign("upload_image", "value", '1');
        return $objResponse;
    }

    public function showImage() {
        //header("Content-type: image/jpg");
        header('Content-Type: image/png');
        $image = $this->getRequest()->getProperty('image');
        $mythumb = new appcore_library_thumbnail_Thumbnail();
        $mythumb->loadImage('application/ecommerce/assets/images/' . $image);
        $mythumb->crop(120, 120);
        $mythumb->save('application/ecommerce/assets/images/' . $image);
        $mythumb->show();
    }

    public function editSession($form) {
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
        if(!($admin)) {
            $objCourseBean = new stdClass();
            //$objCourseBean->image = $form['txtImageFile'];
            //if (isset($form['remove_img']))
            //    $objCourseBean->image = '';
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
        }else{
            //$jquery = "jAlert('".$admin."', 'Alert');";
            if ($form['id_category'] > 0) {
                $objSessionBean = new stdClass();
                //$objCourseBean->item_type;
                $objSessionBean->status = $form['status'];
                //$objCourseBean->currency = $form['txtCost'];
                if (isset($form['duration_start']))
                    $objSessionBean->date_start = $this->setTime($form['duration_start']);
                if (isset($form['duration_end']))
                    $objSessionBean->date_end = $this->setTime($form['duration_end']);
                $objSessionBean->duration = $form['txtDuration'];
                $objSessionBean->duration_type = $form['duration_type'];
                //$objSessionBean->image = $form['txtImageFile'];
                //if (isset($form['remove_img']))
                //    $objSessionBean->image = '';
                $objSessionBean->description = str_replace('&nbsp;', ' ',$form['textareaDescription']);
                $objSessionBean->id_category = $form['id_category'];
                /*
                if(!empty($form['txtCostTTC'])){
                    $objCourseBean->cost_ttc = api_floatval($form['txtCostTTC']);
                }else{
                    $objCourseBean->cost_ttc = '0.00';
                }
                 * 
                 */
                if(!empty($form['priceType'])){
                    //error_log($form['priceType']);
                    $objSessionBean->chr_type_cost = $form['priceType'];
                    $objSessionBean->cost = api_floatval($form['txtCostHT']);
                    $objSessionBean->cost_ttc = api_floatval($form['txtCostTTC']);
                }else{
                    //0 = FREE
                    $objSessionBean->chr_type_cost = '0';
                    $objSessionBean->cost = '0.00';
                    $objSessionBean->cost_ttc = '0.00';
                }
                $objSessionBean->id_session = $form['id_session'];
                //$this->vd($form);
                //$this->vd($objSessionBean);die();
                $objSessionBean = $this->updateCourse($objSessionBean, $form['id_item_commerce']);
                //$this->updateImagenBySession($objSessionBean, $form['id_item_commerce']);
                if (!isset($form['remove_img']))
                    if ($form['upload_image'] > 0)
                        $this->moveImage($objSessionBean);
                /* $jquery = '$("<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>' . $objSessionBean->message . '</p>").dialog({title:"Alert", modal:true, buttons:{OK: function(){location.href="'.api_get_path(WEB_CODE_PATH).'index.php?module=ecommerce&cmd=Session";}}})';
                  $objResponse->script($jquery); */
                ob_start();
                Display :: display_confirmation_message2(get_lang('Saved'), false, true);
                $html_message = ob_get_contents();
                ob_end_clean();
                $objResponse->assign('divMessage', 'innerHTML', $html_message);
                //$js = '$(".close_message_box").click(function() {$("#divMessage").html(\'\');});';
                $js = '$("#content").before($("#divMessage"));';
                $objResponse->script($js);
            }
            else {
                $jquery = "$.alert('".  get_lang("SelectACategory") ."', 'Error', 'error');";
                $objResponse->script($jquery);
            }
        }
        return $objResponse;
    }
    public function updateImagenBySession($objSessionBean,$id_item_commerce ){
         $model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->updateImageBySession($objSessionBean, $id_item_commerce);
        return $returnValue;
    }
    public function updateCourse($objSessionBean, $id_item_commerce) {
        $model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->updateCourse($objSessionBean, $id_item_commerce);
        return $returnValue;
    }
    public function updateCourseTrainer($objCourseBean, $id_item_commerce) {
        $model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->updateCourseTrainer2($objCourseBean, $id_item_commerce);
        return $returnValue;
    }
    public function moveImage($objSessionBean) {
        $file = 'application/ecommerce/assets/images/' . $objSessionBean->image;
        $newfile = '../home/default_platform_document/ecommerce_thumb/' . $objSessionBean->image;
        umask(0);
        copy($file, $newfile);
    }

    public function getSessionEcommerce() {
        $model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->getSession($this->session_id);
        return $returnValue;
    }
    
    public function getSessionById() {
		$model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $model->getSessionById($this->session_id);
        return $returnValue;
		
	}

    public function getSessionCatalogButtonList($session_id, $url_params, $row) {
        $link = 'index.php?module=ecommerce&cmd=Session&func=delete&id_session='.$session_id;
        return
                '<a href="index.php?module=ecommerce&cmd=Session&func=edit&id_session=' . $session_id . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . '</a>&nbsp;&nbsp;' .
                '<a href="javascript:void(0);"  onclick="Alert_Confim_Delete(\'' .$link. '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
//                '<a href="index.php?module=ecommerce&cmd=Session&func=edit&id_session=' . $session_id . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . '</a>&nbsp;&nbsp;' .
//                '<a href="index.php?module=ecommerce&cmd=Session&func=delete&id_session=' . $session_id . '"  onclick="javascript:if(!confirm(' . "'" . addslashes(api_htmlentities(get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . "'" . ')) return false;">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
    }

    public function getListCategory() {
        $returnValue = $this->model->getListCategory();
        return $returnValue;
    }

    public function getTextarea($name, $init) {
        $editor = new HTML_QuickForm_html_editor($name, get_lang('Description'), array(), array('ToolbarSet' => 'AnnouncementsStudent', 'Width' => '685px', 'Height' => '300'));
        $editor->setValue($init);
        $CKEditorOutput = $editor->toHtml();
        return $CKEditorOutput;
    }

    public function getListSession() {
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $this->model->getListSession();
        return $returnValue;
    }

    public function getTitleSession($id_session) {
        $returnValue = $this->model->getTitleSession($id_session);
        return $returnValue;
    }

    public function getCategory($id_category) {
        $returnValue = $this->model->getCategory($id_category);
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

    public function getAction() {

        $html = '<div class="actions">';
        $objCatalog = $this->model->getCatalogSettings();
        $html.='<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_settings.php">' . Display::return_icon('pixel.gif', $this->get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . $this->get_lang('Settings') . '</a>';
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_catalog.php">' . Display::return_icon('pixel.gif', $this->get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncatalog')) . $this->get_lang('Catalog') . '</a>';
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Category">' . Display::return_icon('pixel.gif', $this->get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncategory')) . $this->get_lang('langCategories') . '</a>';
        switch ($objCatalog->selected_value) {
            case CATALOG_TYPE_SESSIONS:
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce">' . Display::return_icon('pixel.gif', $this->get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . $this->get_lang('Products') . '</a>';
                break;
            case CATALOG_TYPE_COURSES:
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce">' . Display::return_icon('pixel.gif', $this->get_lang('Courses'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . $this->get_lang('Products') . '</a>';
                break;
            case CATALOG_TYPE_MODULES:
                $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce">' . Display::return_icon('pixel.gif', $this->get_lang('ModulePacks'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . $this->get_lang('Products') . '</a>';
                break;
        }
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_payment.php">' . Display::return_icon('pixel.gif', $this->get_lang('Payment'), array('class' => 'toolactionplaceholdericon toolactionpayment')) . $this->get_lang('Payment') . '</a>';
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', $this->get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioninvoice')) . $this->get_lang('Invoices') . '</a>';

        $html.='</div>';
        return $html;
    }

    public function edit() {
        
    }

}
