<?php
require_once '../../inc/global.inc.php';
// only for admin account
api_protect_admin_script();
// Script for add missing columns to dokeos course tables
$tbl_courses = Database::get_main_table(TABLE_MAIN_COURSE);
$rs_course = Database::query("SELECT code, db_name, directory FROM $tbl_courses");
$affected_rows =  0;
if (Database::num_rows($rs_course) > 0) {
    while ($row_course = Database::fetch_object($rs_course)) {
                
        $tbl_view = Database::get_course_table(TABLE_LP_VIEW, $row_course->db_name);           
        $check1 = Database::query("SHOW COLUMNS FROM $tbl_view LIKE 'session_id'");
        if (Database::num_rows($check1) == 0) {
            Database::query("ALTER TABLE $tbl_view ADD COLUMN session_id INT(11) NOT NULL DEFAULT 0");
            echo "<p>Added field session_id to $tbl_view</p>";
        }
               
        // Add content field to lp_item table
        $lp_item = Database::get_course_table(TABLE_LP_ITEM, $row_course->db_name);
        $check2 = Database::query("SHOW COLUMNS FROM $lp_item LIKE 'content'");
        if (Database::num_rows($check2) == 0) {
            Database::query("ALTER TABLE $lp_item ADD COLUMN content LONGTEXT NULL DEFAULT ''");
            echo "<p>Added field content to $lp_item</p>";
        }
                
        $tbl_exam = Database::get_course_table(TABLE_EXAM, $row_course->db_name);        
        $check3 = Database::query("SELECT * FROM $tbl_exam");
        if (!$check3) {
            $sql = "CREATE TABLE $tbl_exam (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                quiz_id int(11) unsigned NOT NULL,
                modality int(11) unsigned NOT NULL DEFAULT '1',
                min_score float NOT NULL DEFAULT '0',
                start_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                end_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                invitation_email_sentdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                feedback_email_sentdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                invitation_email longtext,
                feedback_email longtext,
                certif_id int(11) unsigned DEFAULT NULL,
                picture_name varchar(200) DEFAULT NULL,
                PRIMARY KEY (id)
              )ENGINE = MyISAM";
            Database::query($sql);        
            echo "<p>Created table $tbl_exam</p>";
        }
        
        $tbl_exam_user = Database::get_course_table(TABLE_EXAM_USER, $row_course->db_name);        
        $check4 = Database::query("SELECT * FROM $tbl_exam_user");
        if (!$check4) {
            $sql = "CREATE TABLE $tbl_exam_user (
                   exam_id int(11) NOT NULL,
                   user_id int(11) NOT NULL,
                   PRIMARY KEY (exam_id,user_id)
                )ENGINE = MyISAM";
            Database::query($sql, __FILE__, __LINE__);       
            echo "<p>Created table $tbl_exam_user</p>";
        }
       
    }
}
