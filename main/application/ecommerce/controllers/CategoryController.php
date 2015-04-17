<?php

/**
 * controlador para administrar las categorias
 * @author Johnny, <johnny1402@gmail.com>
 * @package ecommerce 
 */
class application_ecommerce_controllers_Category extends appcore_command_Command {

    private $model;
    public $css;

    public function __construct() {
        $this->verifySession();
        $this->css = 'application/ecommerce/assets/css/style.css';
        $this->setLanguageFile(array('admin', 'courses', 'index'));
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->loadAjax();
        $this->language_file = array("admin", "scorm","exercice");
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
//        $this->ajax->setCharEncoding('UTF-8'); --
//        $this->ajax->setFlag("decodeUTF8Input", true);
        $this->ajax->setFlag("debug", false);
        $this->ajax->register(XAJAX_FUNCTION, array('editCategory', $this, 'editCategory'));
        $this->ajax->register(XAJAX_FUNCTION, array('saveCategory', $this, 'saveCategory'));
        $this->ajax->register(XAJAX_FUNCTION, array('addCategory', $this, 'addCategory'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteCategory', $this, 'deleteCategory'));
        $this->ajax->register(XAJAX_FUNCTION, array('delete', $this, 'delete'));
        $this->ajax->register(XAJAX_FUNCTION, array('selectAll', $this, 'selectAll'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteAll', $this, 'deleteAll'));
        $this->ajax->register(XAJAX_FUNCTION, array('deleteAllCategory', $this, 'deleteAllCategory'));
        $this->ajax->register(XAJAX_FUNCTION, array('active', $this, 'active'));
        $this->ajax->register(XAJAX_FUNCTION, array('orderListCategory', $this, 'orderListCategory'));
        $this->ajax->processRequest();
    }

    public function orderListCategory($field) {
        $objResponse = new xajaxResponse();
        if (!$this->getSession()->getProperty('order')) {
            $this->getSession()->setProperty('order', 'ASC');
        } else {
            switch ($this->getSession()->getProperty('order'))
            {
                case 'ASC': $this->getSession()->setProperty('order', 'DESC');break;
                case 'DESC': $this->getSession()->setProperty('order', 'ASC');break;
            }
        }
        $parameters['orderby'] = $field;
        $parameters['order'] = $this->getSession()->getProperty('order');
        $html = $this->getHtmlListCategory($parameters);
        $objResponse->assign('divListCategory', "innerHTML", $html);
        return $objResponse;
    }

    public function active($value, $id_category) {
        $objResponse = new xajaxResponse();
        $form['bool_active'] = $value;
        $this->model->updateActive($form, $id_category);
        $div = 'div_' . $id_category;
        $html = '<span class="ecommerce_link" onclick="xajax_active(1, ' . $id_category . ')"><img class="actionplaceholdericon actioninvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span>';
        if ($value)
            $html = '<span class="ecommerce_link" onclick="xajax_active(0, ' . $id_category . ')"><img class="actionplaceholdericon actionvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span>';
        $objResponse->assign($div, "innerHTML", $html);
        return $objResponse;
    }

    public function deleteAllCategory($arrayCategory) {
        $objResponse = new xajaxResponse();
        $this->model->deleteAllCategory($arrayCategory);
        $html = $this->getHtmlListCategory();
        $objResponse->assign("divListCategory", "innerHTML", $html);
        return $objResponse;
    }

    public function deleteAll($form) {
        $objResponse = new xajaxResponse();
        if (count($form['checkdelete']) > 0) {
            
            
            $id = implode(",", $form['checkdelete']);
            $jquery = "jConfirm('".get_lang('langConfirmYourChoice')."', '".get_lang('Category')."', function(r) {
                        if(r)
                            xajax_deleteAllCategory('" . $id . "');
                   });";
            $objResponse->script($jquery);
            
        $js="$.confirm('".get_lang('langAreYouSureToDelete')."', '".get_lang('ConfirmationDialog')."', function() {
            xajax_deleteAllCategory(" . $id . ");
            });";
        $objResponse->script($js);            
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

    public function delete($id_category) {
        $objResponse = new xajaxResponse();
        $this->model->deleteCategory($id_category);
        $html = ($this->getHtmlListCategory());
        $objResponse->assign("divListCategory", "innerHTML", $html);
        return $objResponse;
    }

    public function deleteCategory($id_category) {
        $objResponse = new xajaxResponse();
        
        $jquery = "jConfirm('".get_lang('langConfirmYourChoice')."', '".  get_lang('Category')."', function(r) {
                        if(r)
                            xajax_delete(" . $id_category . ");
                   });";
        $objResponse->script($jquery);
        $js="$.confirm('".get_lang('langAreYouSureToDelete')."', '".get_lang('ConfirmationDialog')."', function() {
            xajax_delete(" . $id_category . ");
            });";
        

        $objResponse->script($js);
        return $objResponse;
    }

    public function addCategory() {
        $objResponse = new xajaxResponse();                
        $html = $this->getHtmlFormCategory($id_category);       
        $objResponse->assign("divFormCategory", "innerHTML", $html);
        $jquery = '$(function(){
                                
                                $("#divFormCategory").dialog("open");
                                $("#divFormCategory").dialog({
                                autoOpen: true,
                                closeText:"'.$this->get_lang('Close').'",
                                title:"' . $this->get_lang('EcommerceAddCategoryProducts') . '",
                                resizable: false,
                                width: 400,
                                height: 300,
                                modal:true,
                                buttons: {
                                        "' . $this->get_lang('langCancel') . '": function() {
                                        $(this).dialog("close");
                                        }, 
                                    "' . $this->get_lang('Save') . '": function() {
                                        xajax_saveCategory(xajax.getFormValues(\'formCategory\'));
                                        }
                                }
                        });
                    });';
        $objResponse->script($jquery);        
        return $objResponse;
    }

    public function editCategory($id_category) {
        $objResponse = new xajaxResponse();
        $html = $this->getHtmlFormCategory($id_category);
        $objResponse->assign("divFormCategory", "innerHTML", $html);
        $jquery = '$(function(){
                                $("#divFormCategory").dialog("open");
                                $("#divFormCategory").dialog({
                                autoOpen: true,
                                title:"' . $this->get_lang('EcommerceEditCategoryProduct') . '",
                                resizable: false,
                                width: 400,
                                height: 300,
                                modal:true,
                                buttons: {
                                        "' . $this->get_lang('langCancel') . '": function() {
                                        $(this).dialog("close");
                                        }, 
                                    "' . $this->get_lang('Save') . '": function() {
                                        xajax_saveCategory(xajax.getFormValues(\'formCategory\'));
                                        }
                                }
                        });
                    });';
        $objResponse->script($jquery);
        return $objResponse;
    }

    public function saveCategory($form) {
        $objResponse = new xajaxResponse();
        if (strlen(trim($form['name_category'])) > 0) {
            $objCategoryBeans = new stdClass();
            $objCategoryBeans->id_category = $form['id_category'];
            $objCategoryBeans->chr_category = $form['name_category'];
            $objCategoryBeans->chr_language = preg_replace('([^A-Za-z0-9])', '', $form['language']); //strip_tags($form['language']);
            $objCategoryBeans->bool_active = $form['bool_active'];
            $this->model->saveCategory($objCategoryBeans);
            $jquery = '$("#divFormCategory").dialog("close");';
            $objResponse->script($jquery);
            $html = $this->getHtmlListCategory();
            $objResponse->assign("divListCategory", "innerHTML", $html);
        } else {
            $jquery = '$("#divMessage").empty();
            $("#divMessage").addClass("ui-state-error ui-corner-all");
            $("#divMessage").append("<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><strong>' . $this->get_lang('Alert') . ':</strong> ' . $this->get_lang('EcommerceFieldNameRequired') . '</p>");
            $("#divMessage").show(1000);
            $("#divMessage").delay(3000).hide(2000);';
            $objResponse->script($jquery);
        }
        return $objResponse;
    }

    private function getHtmlFormCategory($id_category) {
        $objCategory = $this->getCategory($id_category);
        $html = '<div id="divMessage"></div><form name="formCategory" id="formCategory" method="POST" action="" onsubmit="return false;">
            <table style="width:100%; padding:5px; margin:5px" cellspacing="5px" cellpadding="5px">
        <tr>
            <td>
                <span>' . $this->get_lang('EcommerceNameOfCategory') . '</span>';
        $html.='<input type="hidden" name="id_category" id="id_category" value="' . $objCategory->id_category . '" />';
        $html.='</td>
            <td><input name="name_category" id="name_category" value="' . $objCategory->chr_category . '" /></td>
        </tr>
        <tr>
            <td>' . $this->get_lang('Language') . '</td>
            <td>' . api_get_languages_combo() . '</td>
        </tr>
        <tr>
            <td>
                <span>' . $this->get_lang('EcommerceVisibleCatalog') . '</span>
            </td>
            <td>';
        if ($objCategory->bool_active) {
            $html.='<input id="bool_active" type="radio" value="1" name="bool_active" checked>' . $this->get_lang('langYes');
            //$html.='&nbsp;/&nbsp;';
            $html.='<input id="bool_active" type="radio" value="0" name="bool_active">' . $this->get_lang('langNo') . '</td>';
        } else {
            $html.='<input id="bool_active" type="radio" value="1" name="bool_active" >' . $this->get_lang('langYes');
            //$html.='&nbsp;/&nbsp;';
            $html.='<input id="bool_active" type="radio" value="0" name="bool_active" checked>' . $this->get_lang('langNo') . '</td>';
        }
        $html.='</tr></table></form>';        
        return $html;
    }

    public function getCategory($id_category) {
        $objCategory = $this->model->getCategory($id_category);
        if ($objCategory == null) {
            $objCategory = new stdClass();
            $objCategory->id_category = 0;
            $objCategory->chr_category = '';
            $objCategory->bool_active = 1;
            $objCategory->chr_language = api_get_setting('platformLanguage');
        }
        return $objCategory;
    }

    public function getAction() {
        $objCatalog = $this->getCatalogSettings();
        $html = '<div class="actions">';
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_settings.php">' . Display::return_icon('pixel.gif', $this->get_lang('Settings'), array('class' => 'toolactionplaceholdericon toolsettings')) . $this->get_lang('Settings') . '</a>';
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_catalog.php">' . Display::return_icon('pixel.gif', $this->get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncatalog')) . $this->get_lang('Catalog') . '</a>';
        $html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'index.php?module=ecommerce&cmd=Category" class="active">' . Display::return_icon('pixel.gif', $this->get_lang('Invoices'), array('class' => 'toolactionplaceholdericon toolactioncategory')) . $this->get_lang('langCategories') . '</a>';
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

    public function getCatalogSettings() {
        $returnValue = $this->model->getCatalogSettings();
        return $returnValue;
    }

    public function getListCategory($parameters = null) {
        $returnValue = $this->model->getListCategory($parameters);
        return $returnValue;
    }

    public function getCountCategory($id_category) {
        $returnValue = $this->model->getCountCategory($id_category);
        return $returnValue;
    }

    public function getHtmlListCategory($parameters = null) {
        $html = '<table style="100%" class="data_table">
            <tr class="row_odd">
                <th style="width:10px;">Suppr.</th>
                <th style="width:400px;"><div class="ecommerce_link" id="divTitleCategory" onclick="xajax_orderListCategory(\'chr_category\')">' . $this->get_lang('EcommerceCategory') . '</div></th>
                <th style="width:100px;"><div class="ecommerce_link" id="divTitleCountProduct" onclick="xajax_orderListCategory(\'amount\')">' . $this->get_lang('EcommerceProduct') . '</div></th>
                <th style="width:100px;">' . $this->get_lang('Language') . '</th>
                <th style="width:80px;">' . $this->get_lang('langVisible') . '</th>
                <th style="width:80px;">' . $this->get_lang('EcommerceAction') . '</th>
            </tr>';
        if (count($this->getListCategory($parameters)) > 0) {
            $collectionCategory = $this->getListCategory($parameters);
            foreach ($collectionCategory as $index => $arrayCategory) {
                $html.='<tr class="' . (($index) % 2 ? 'row_odd' : 'row_even') . '">';
                $html.='<td><input type="checkbox" name="checkdelete[]" id="checkdelete" value="' . $arrayCategory['id_category'] . '"></td>';
                $html.='<td>' . $arrayCategory['chr_category'] . '</td>';
                $html.='<td><center>' . $this->model->getCountCategory($arrayCategory['id_category']) . '</center></td>';
                $html.='<td><center>' . $arrayCategory['chr_language'] . '</center></td>';
                if ($arrayCategory['bool_active'])
                    $html.='<td><center><div id="div_' . $arrayCategory['id_category'] . '"><span class="ecommerce_link" onclick="xajax_active(0, ' . $arrayCategory['id_category'] . ')"><img title="' . $this->get_lang('langVisible') . '" class="actionplaceholdericon actionvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div></center></td>';
                else
                    $html.='<td><center><div  id="div_' . $arrayCategory['id_category'] . '"><span class="ecommerce_link" onclick="xajax_active(1, ' . $arrayCategory['id_category'] . ')"><img title="' . $this->get_lang('langInvisible') . '" class="actionplaceholdericon actioninvisible" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></div></center></td>';
                $html.='<td><span class="ecommerce_link" onclick="xajax_editCategory(' . $arrayCategory['id_category'] . ')"><img title="' . $this->get_lang('langModify') . '" class="actionplaceholdericon actionedit" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span> <span class="ecommerce_link" onclick="xajax_deleteCategory(' . $arrayCategory['id_category'] . ')"><img title="' . $this->get_lang('langDelete') . '" class="actionplaceholdericon actiondelete" src="' . api_get_path(WEB_PATH) . 'main/img/pixel.gif" /></span></td>';
                $html.='</tr>';
            }
        }
        else {
            $html.='<tr><td style="width:100%;" colspan="6">' . $this->get_lang('TheListIsEmpty') . '</td></tr>';
        }
        $html.='</table>';
        return $html;
    }

}