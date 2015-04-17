<?php

/**
 * model for table
 * @author Johnny, <johnny1402@gmail.com>
 * @package notebook 
 */
class application_notebook_models_tables_TablesNotebook extends ADODB_Active_Record
{
    public $_table;
    
    public function __construct()
    {
        $this->_table = str_replace('`','',Database::get_course_table(TABLE_NOTEBOOK));
        parent::__construct();
    }
    
    public function Save($objNotebook) {
        $this->user_id = $objNotebook->user_id;
        $this->course = $objNotebook->course;
        $this->session_id = $objNotebook->session_id;
        $this->title = $objNotebook->title;
        $this->description = $objNotebook->description;
        $this->creation_date = $objNotebook->creation_date;
        $this->update_date = $objNotebook->update_date;
        $this->status = $objNotebook->status;
        parent::Save();
        $objNotebook->notebook_id = $this->notebook_id;
        return $objNotebook;
    }
}