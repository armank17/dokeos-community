<?php
class application_announcement_models_table_TableAnnouncement extends ADODB_Active_Record
{
    public $_table;
    
    
    public function __construct() {
        $r =Database::get_course_table(TABLE_ANNOUNCEMENT);
        $r = str_replace('`','',$r);
        $this->_table = $r;
        parent::__construct();
        
    }

    
    
}