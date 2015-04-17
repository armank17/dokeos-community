<?php

/**
 * controlador para editar informaci�n de la session
 * @author Johnny, <johnny1402@gmail.com>
 * @package ecommerce 
 */
class application_ecommerce_controllers_Module extends appcore_command_Command {

    private $model;
    public $css;
    private $id_item;

    public function __construct() {
        $this->verifySession();
        $this->setLanguageFile(array('admin', 'index'));
        $this->loadAjax();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->css = 'application/ecommerce/assets/css/style.css';
        $this->id_item = $this->getRequest()->getProperty('id_item', 0);
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
        $this->ajax->register(XAJAX_FUNCTION, array('sentForm', $this, 'sentForm'));
        $this->ajax->register(XAJAX_FUNCTION, array('uploadImage', $this, 'uploadImage'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteItem', $this, 'deleteItem'));
        $this->ajax->register(XAJAX_FUNCTION, array('delete', $this, 'delete'));
        $this->ajax->register(XAJAX_FUNCTION, array('selectAll', $this, 'selectAll'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteAll', $this, 'deleteAll'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteAllModule', $this, 'deleteAllModule'));
        $this->ajax->register(XAJAX_FUNCTION, array('active', $this, 'active'));
        $this->ajax->register(XAJAX_FUNCTION, array('calculateCostHT', $this, 'calculateCostHT'));
        $this->ajax->register(XAJAX_FUNCTION, array('calculateCostTTC', $this, 'calculateCostTTC'));
        $this->ajax->register(XAJAX_FUNCTION, array('loadPreviewCatalog', $this, 'loadPreviewCatalog'));
        $this->ajax->processRequest();
    }

    public function loadPreviewCatalog() {
        $objResponse = new xajaxResponse();
        $html = '<img src="application/ecommerce/assets/images/thumb_prod_large.jpg" />';
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
        //$prixTTC = api_floatval($form['txtCostTTC']);
        $prixTTC = $form['txtCostTTC'];
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
        //$prix = api_floatval($form['txtCost']);
        $prix = $form['txtCost'];
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
        $html = '<span class="ecommerce_link" onclick="xajax_active(1, ' . $id_item . ')"><img title="' . $this->get_lang('langInvisible') . '" class="actionplaceholdericon actioninvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span>';
        if ($active)
            $html = '<span class="ecommerce_link" onclick="xajax_active(0, ' . $id_item . ')"><img title="' . $this->get_lang('langInvisible') . '" class="actionplaceholdericon actionvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span>';
        $objResponse->assign($div, "innerHTML", $html);
        return $objResponse;
    }

    public function deleteAll($form) {
        $objResponse = new xajaxResponse();
        if (count($form['checkdelete']) > 0) {
            $id = implode(",", $form['checkdelete']);
            $jquery = "jConfirm('Are you sure?', 'Modules', function(r) {
                        if(r)
                            xajax_deleteAllModule('" . $id . "');
                   });";
            $objResponse->script($jquery);
        }
        return $objResponse;
    }

    public function deleteAllModule($arrayItem) {
        $objResponse = new xajaxResponse();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->model->deleteAllModule($arrayItem);
        $this->model->clearModules($arrayItem);
        $html = $this->getHtmlListModules();
        $objResponse->assign("divListCategory", "innerHTML", $html);
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

    public function delete($id_item) {
        $objResponse = new xajaxResponse();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->model->deleteItem($id_item);
        $this->model->clearModules($id_item);
        $html = $this->getHtmlListModules();
        $objResponse->assign("divListCategory", "innerHTML", $html);
        return $objResponse;
    }

    public function getHtmlListModules() {
        $html = '<table style="100%" class="data_table">
            <tr class="row_odd">
                <th style="width:10px;"><input onclick="xajax_selectAll();" type="checkbox" name="checkdelete[]" id="checkdelete"></th>
                <th style="width:10px;"></th>
                <th style="width:20px;">ID</th>
                <th style="width:300px;">' . $this->get_lang('Title') . '</th>
                <th style="width:150px;">' . ($this->get_lang('Category')) . '</th>
                <th style="width:100px;">' . $this->get_lang('Price') . '</th>
                <th style="width:100px;">' . ($this->get_lang('Duration')) . '</th>
                <th style="width:100px;">' . $this->get_lang('Catalog') . '</th>
                <th style="width:100px;">' . $this->get_lang('EcommerceAction') . '</th>
            </tr>';
        if (count($this->getListModule()) > 0) {
            $collectionSession = $this->getListModule();
            foreach ($collectionSession as $index => $arrayModule) {
                $html.='<tr class="' . (($index) % 2 ? 'row_odd' : 'row_even') . '">';
                $html.='<td><input type="checkbox" name="checkdelete[]" id="checkdelete" value="' . $arrayModule['id'] . '"></td>';
                $html.='<td>' . Display::return_icon('pixel.gif', get_lang('Move'), array("class" => "actionplaceholdericon actionsdraganddrop")) . '</td>';
                $html.='<td>' . $arrayModule['id'] . '</td>';
                $html.='<td>' . $arrayModule['code'] . '</td>';
                if ($arrayModule['id_category'] == null)
                    $html.='<td><center>' . $arrayModule['id_category'] . '</center></td>';
                else
                    $html.='<td><center>' . $this->getCategory($arrayModule['id_category'])->chr_category . '</center></td>';
                $html.='<td><center>' . $arrayModule['cost'] . '</center></td>';
                $html.='<td><center>' . $arrayModule['duration'] . ' ' . $arrayModule['duration_type'] . '</center></td>';
                if ($arrayModule['status'])
                    $html.='<td><center><div id="div_' . $arrayModule['id'] . '"><span class="ecommerce_link" onclick="xajax_active(0, ' . $arrayModule['id'] . ')"><img title="' . $this->get_lang('langVisible') . '" class="actionplaceholdericon actionvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div><center></td>';
                else
                    $html.='<td><center><div  id="div_' . $arrayModule['id'] . '"><span class="ecommerce_link" onclick="xajax_active(1, ' . $arrayModule['id'] . ')"><img title="' . $this->get_lang('langInvisible') . '" class="actionplaceholdericon actioninvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div><center></td>';
                $html.='<td><center><a href="index.php?module=ecommerce&cmd=Module&func=add&id_item=' . $arrayModule['id'] . '" ><img title="' . $this->get_lang('langModify') . '" class="actionplaceholdericon actionedit" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></a> <span class="ecommerce_link" onclick="xajax_deleteItem(' . $arrayModule['id'] . ')"><img title="' . $this->get_lang('langDelete') . '" class="actionplaceholdericon actiondelete" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></center></td>';
                $html.='</tr>';
            }
        }
        else {
            $html.='<tr><td style="width:100%;" colspan="8">' . $this->get_lang('TheListIsEmpty') . '</td></tr>';
        }
        $html.='</table>';
        return $html;
    }

    public function deleteItem($id_item) {
        $objResponse = new xajaxResponse();
        $jquery = "jConfirm('Are you sure?', 'Module', function(r) {
                        if(r)
                            xajax_delete(" . $id_item . ");
                   });";
        $objResponse->script($jquery);
        return $objResponse;
    }

    public function sentForm($form, $description) {
        $objResponse = new xajaxResponse();
        $form['textareaDescription'] = $description;
        if ($form['id_category'] > 0) {
            if (strlen(trim($form['txtName'])) > 0) {
                if (strlen(trim($form['txtDuration'])) > 0) {
                    if ($this->existModule($form)) {
                        $objModuleBean = new stdClass();
                        $objModuleBean->cost = $form['txtCostHT'];
                        $objModuleBean->item_type = 1;
                        $objModuleBean->status = $form['status'];
                        $objModuleBean->code = $form['txtName'];
                        if (isset($form['duration_start']))
                            $objModuleBean->date_start = $this->setTime($form['duration_start']);
                        if (isset($form['duration_end']))
                            $objModuleBean->date_end = $this->setTime($form['duration_end']);
                        $objModuleBean->duration = $form['txtDuration'];
                        $objModuleBean->duration_type = $form['duration_type'];
                        $objModuleBean->image = $form['txtImageFile'];
                        if (isset($form['remove_img']))
                            $objModuleBean->image = '';
                        $objModuleBean->description = $form['textareaDescription'];
                        $objModuleBean->id_category = $form['id_category'];
                        $objModuleBean->id_session = $form['id_session'];
                        $objModuleBean->cost_ttc = $form['txtCostTTC'];
                        $objModuleBean->chr_type_cost = $form['priceType'];
                        //$this->vd($objModuleBean);die();
                        $objModuleBean = $this->model->saveModule($objModuleBean, $form);
                        if (!isset($form['remove_img']))
                            if ($form['upload_image'] > 0)
                                $this->moveImage($objModuleBean);
                        $jquery = '$("<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>' . $objModuleBean->message . '</p>").dialog({title:"Alert", modal:true, buttons:{OK: function(){location.href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Module";}}})';
                        $objResponse->script($jquery);
                    }
                    else {
                        $jquery = "jAlert('Select a module', 'Alert');";
                        $objResponse->script($jquery);
                    }
                } else {
                    $jquery = "jAlert('" . $this->get_lang('EcommerceFieldDurationRequired') . "', 'Alert');";
                    $objResponse->script($jquery);
                }
            } else {
                $jquery = "jAlert('" . $this->get_lang('EcommerceFieldNameRequired') . "', 'Alert');";
                $objResponse->script($jquery);
            }
        } else {
            $jquery = "jAlert('Select category', 'Alert');";
            $objResponse->script($jquery);
        }
        return $objResponse;
    }

    public function moveImage($objModuleBean) {
        $file = 'application/ecommerce/assets/images/' . $objModuleBean->image;
        $newfile = '../home/default_platform_document/ecommerce_thumb/' . $objModuleBean->image;
        umask(0);
        copy($file, $newfile);
    }

    public function existModule($form) {
        $returnValue = false;
        $collectionCourse = $this->getListCourse();
        foreach ($collectionCourse as $index => $arrayCourse) {
            if (isset($form[$arrayCourse['code']]))
                $returnValue = true;
        }
        return $returnValue;
    }

    public function uploadImage($image) {
        $image = 'module_' . $image;
        $objResponse = new xajaxResponse();
        //init crop again
        $path_image = api_get_path(SYS_PATH) . 'main/application/ecommerce/assets/images/' . $image;
        $info = pathinfo($path_image);
        if ($info['extension'] == 'png') {
            $mythumb = new appcore_library_thumbnail_Thumbnail();
            $new_image = $mythumb->resize2($path_image, 100, 100, api_get_path(SYS_PATH) . 'main/application/ecommerce/assets/images/' . $image);
            $html = '<img style="width:100px; height:100px" src="' . api_get_path(WEB_PATH) . 'main/application/ecommerce/assets/images/' . $image . '" />';
        } else {
            $html = '<img style="width:100px; height:100px" src="' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Course&func=showImage&image=' . $image . '" />';
        }
        //$html = '<img src="' . api_get_path(WEB_PATH) . 'main/index.php?module=ecommerce&cmd=Module&func=showImage&image=' . $image . '" />';
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

    public function getHtmlListModuleByCourse($arrayCourse) {
        $html = '<table width="100%"  class="data_table">';
        $html.= '<tr class="row_odd">
                    <th style="width:15px;">&nbsp;</th>
                    <th style="width:300px;">Module</th>
                    <th style="width:70px;">Action</th>
                    <th style="width:15px;">ID</th>
                 </tr>';
        $objCollectionModule = $this->model->getModulesByCourse($arrayCourse['code']);
        if (count($objCollectionModule) > 0) {
            foreach ($objCollectionModule as $index => $arrayModule) {
                $html.='<tr class="' . (($index) % 2 ? 'row_odd' : 'row_even') . '">';
                $html.='<td>' . ($index + 1) . '</td>';
                $html.='<td>' . $arrayModule['lp_title'] . '</td>';
                if ($this->id_item > 0) {
                    if ($this->belongModuleCourse($this->id_item, $arrayModule)) 
                        $html.='<td><center><input type="checkbox" name="' . $arrayModule['course_code'] . '[]" value="' . $arrayModule['lp_module_id'] . '" checked="checked" /></center></td>';
                    else
                        $html.='<td><center><input type="checkbox" name="' . $arrayModule['course_code'] . '[]" value="' . $arrayModule['lp_module_id'] . '" /></center></td>';
                }
                else
                    $html.='<td><center><input type="checkbox" name="' . $arrayModule['course_code'] . '[]" value="' . $arrayModule['lp_module_id'] . '" /></center></td>';
                $html.='<td><center>' . $arrayModule['lp_module_id'] . '</center></td>';
                $html.='</tr>';
            }
        }
        $html.='</table>';
        return $html;
    }

    public function belongModuleCourse($id_item, $arrayModule) {
        //Compare id_module with id_module_ecommerce
        $id_module = $this->model->belongModuleCourse($id_item, $arrayModule['course_code'], $arrayModule['lp_module_id']);
        if ($arrayModule['lp_module_id'] == $id_module)
            $returnValue = true;
        else
            $returnValue = false;
        return $returnValue;
    }

    public function getListCourse() {
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $returnValue = $this->model->getListCourse();
        return $returnValue;
    }

    public function getListCategory() {
        $returnValue = $this->model->getListCategory();
        return $returnValue;
    }

    public function getItemEcommerce() {
        if ($this->id_item == 0) {
            $objItem = new stdClass();
            $objItem->id = 0;
            $objItem->code = '-';
            $objItem->cost = api_number_format(0.00);
            $objItem->item_type = '1';
            $objItem->status = 1;
            $objItem->currency = 0;
            $objItem->date_start = $this->getTime(date("Y-m-d"));
            $objItem->date_end = $this->getTime(date("Y-m-d"));
            $objItem->duration = '';
            $objItem->duration_type = '';
            $objItem->image = '';
            $objItem->description = '';
            $objItem->id_category = 0;
            $objItem->id_session = null;
            $returnValue = $objItem;
        }
        else
            $returnValue = (object) $this->model->getItemEcommerce($this->id_item);

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
        $date = explode(" ", $date);
        $date = $date[0];
        //format 07/09/2012 to 2012-09-07
        $newdate = preg_replace("/(\d+)\D+(\d+)\D+(\d+)/", "$3-$1-$2", $date);
        return $newdate;
    }

    public function getListModule() {
        $returnValue = $this->model->getListModule();
        return $returnValue;
    }

    public function getTitleModule($id_item) {
        if ($id_item > 0)
            $returnValue = $this->model->getTitleModule($id_item);
        else
            $returnValue = '';
        return $returnValue;
    }

    public function getTextarea($name, $init) {
        $editor = new HTML_QuickForm_html_editor($name, get_lang('Description'), array(), array('ToolbarSet' => 'AnnouncementsStudent', 'Width' => '732px', 'Height' => '300'));
        $editor->setValue($init);
        $CKEditorOutput = $editor->toHtml();
        return $CKEditorOutput;
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
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_invoice.php">' . Display::return_icon('pixel.gif', $this->get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactionprogramme')) . $this->get_lang('Invoices') . '</a>';

        $html.='</div>';
        return $html;
    }

    public function hasModule($code_course) {
        $returnValue = $this->model->hasModule($code_course);
        return $returnValue;
    }

    public function add() {
        
    }

}