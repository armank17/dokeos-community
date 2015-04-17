<?php
require_once '../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

ini_set('memory_limit', '-1');

$tbl_courses = Database::get_main_table(TABLE_MAIN_COURSE);
$rs_course = Database::query("SELECT code, db_name, directory FROM $tbl_courses");


if (Database::num_rows($rs_course) > 0) {    
    while ($row_course = Database::fetch_array($rs_course)) {  
        
        $t_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $row_course['db_name']);
                
        $affected_lp_rows = 0;

        $Check1 = Database::query("SHOW COLUMNS FROM $t_quiz LIKE 'simplifymode' ");

        if (Database::num_rows($Check1) == 0) {   
            $rs = Database::query("ALTER TABLE $t_quiz ADD COLUMN simplifymode tinyint(1) DEFAULT 0"); 
            $affected_lp_rows++;  
        }
        
            $t_question = Database :: get_course_table(TABLE_QUIZ_QUESTION, $row_course['db_name']);
            
        
            
            $rs = Database::query("ALTER TABLE $t_question ADD COLUMN show_image_left tinyint(1) DEFAULT 0");
            $rs1 = Database::query("ALTER TABLE $t_question ADD COLUMN show_image_right tinyint(1) DEFAULT 0");
           
            $t_quiz_scenario = Database :: get_course_table(TABLE_QUIZ_SCENARIO, $row_course['db_name']);
            $rs2 = Database::query("ALTER TABLE $t_quiz_scenario  ADD COLUMN simplifymode tinyint(1) DEFAULT 0");
 
    }   
    
}


echo '<br>Success';
