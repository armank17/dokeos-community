<?php
require_once '../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

ini_set('memory_limit', '-1');

$tbl_courses = Database::get_main_table(TABLE_MAIN_COURSE);
$rs_course = Database::query("SELECT code, db_name, directory FROM $tbl_courses");

$affected_lp_rows = 0;
     
if (Database::num_rows($rs_course) > 0) {    
    while ($row_course = Database::fetch_array($rs_course)) {  
        
        $t_exam = Database :: get_course_table(TABLE_EXAM, $row_course['db_name']);
                
   

        $Check1 = Database::query("SHOW COLUMNS FROM $t_exam LIKE 'feedback_exam_done' ");

        if (Database::num_rows($Check1) == 0) {   
            $rs = Database::query("ALTER TABLE $t_exam ADD COLUMN feedback_exam_done longtext"); 
            $affected_lp_rows++;  
        }
        
           
 
    }   
    
}


echo '<br>Success';
