<?php

/**
 * controlador para editar información del curso
 * @author Johnny, <johnny1402@gmail.com>
 * @package ecommerce 
 */
class application_ecommerce_controllers_Index extends appcore_command_Command {
    
    private $model;

    public function __construct() {
        $this->verifySession();
        $this->model = new application_ecommerce_models_ModelEcommerce();
        $this->router();
    }
    
    public function verifySession()
    {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        if (api_get_setting('enable_shop_tool') === 'false') {
            api_not_allowed();
        }
    }
    
    public function router()
    {
        $objCatalog = $this->model->getCatalogSettings();
        switch ($objCatalog->selected_value) {
            case CATALOG_TYPE_SESSIONS:
                $url =api_get_path(WEB_CODE_PATH).'index.php?module=ecommerce&cmd=Session';
                break;
            case CATALOG_TYPE_COURSES:
                $url =api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_courses.php';
                //$html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_courses.php">' . Display::return_icon('pixel.gif', $this->get_lang('Courses'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . $this->get_lang('Products') . '</a>';
                break;
            case CATALOG_TYPE_MODULES:
                $url =api_get_path(WEB_CODE_PATH).'index.php?module=ecommerce&cmd=Module';
                //$html.= '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/ecommerce_module_packs.php">' . Display::return_icon('pixel.gif', $this->get_lang('ModulePacks'), array('class' => 'toolactionplaceholdericon toolactionproduct')) . $this->get_lang('Products') . '</a>';
                break;
        }
        header("Location: $url");
    }


}