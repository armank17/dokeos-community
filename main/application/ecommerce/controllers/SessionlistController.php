<?php
/**
 * controlador para editar informaci�n del curso
 * @author Johnny, <johnny1402@gmail.com>
 * @package ecommerce 
 */
class application_ecommerce_controllers_Sessionlist extends appcore_command_Command {
    
    private $model;

    public function __construct() {
        $this->model = new application_ecommerce_models_ModelEcommerce();
    }
    
    public function getCoursePaginator() {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        $id_category = $this->getRequest()->getProperty('id_category', 0);
        $count = $this->getRequest()->getProperty('count', 1);
        $page = $this->getRequest()->getProperty('page', 1);
        $start = ceil(($page - 1) * 10);
        $objCollectionCourse = $this->model->getCoursePaginator($id_category);
        $input = ( array_slice($objCollectionCourse, $start, $count) );
        echo json_encode($input);
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



}