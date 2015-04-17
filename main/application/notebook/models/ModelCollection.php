<?php

/**
 * model collection
 * @author Johnny, <johnny1402@gmail.com>
 * @package notebook 
 */
class application_notebook_models_ModelCollection extends ArrayObject
{
    public function getNotesList($user_id = null)
    {
        $where = "1=1";
        if(isset($user_id))
            $where = "user_id =".$user_id;
        $datos= new application_notebook_models_tables_TablesNotebook();
        $array=$datos->find($where);
        $this->exchangeArray($array);
        return $this;
    }
    
    public function addNotebook($objNotebook)
    {
        $objTable = new application_notebook_models_tables_TablesNotebook();
        $objNotebook = $objTable->Save($objNotebook);
        api_item_property_update(api_get_course_info(api_get_course_id()), TOOL_NOTEBOOK, $objNotebook->notebook_id, 'NotebookAdded', api_get_user_id());
    }
}