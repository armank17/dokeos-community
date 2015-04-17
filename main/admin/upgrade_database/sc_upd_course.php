<?php
require_once '../../inc/global.inc.php';
// only for admin account
api_protect_admin_script();
// Script for add missing columns to dokeos course tables
$tbl_courses = Database::get_main_table(TABLE_MAIN_COURSE);
$rs_course = Database::query("SELECT code, db_name, directory FROM $tbl_courses");
$affected_lp_rows = $affected_note_rows = 0;
if (Database::num_rows($rs_course) > 0) {
    while ($row_course = Database::fetch_object($rs_course)) {
        // Document table
        $tbl_course_document = Database::get_course_table(TABLE_DOCUMENT, $row_course->db_name);
        $sql = "DESC $tbl_course_document";
        // Possible missing fields
        $missing_fields = array('is_template');
        // Execute this command if the field does not exists
        $command_field['is_template'] = "ALTER TABLE $tbl_course_document ADD COLUMN is_template TINYINT UNSIGNED NOT NULL default 0";
        echo '<pre>';
        echo "<strong>Database : ".$tbl_course_document."</strong><br/>";
        // Get the current document table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End document table
        
        // Quiz table
        $tbl_course_quiz = Database::get_course_table(TABLE_QUIZ_TEST, $row_course->db_name);
        $sql = "DESC $tbl_course_quiz";
        // Possible missing fields
        $missing_fields = array('certif_template','certif_min_score','score_pass','quiz_type','start_time','end_time');
        // Execute this command if the field does not exists
        $command_field['certif_template'] = "ALTER TABLE $tbl_course_quiz ADD COLUMN certif_template int(11) NOT NULL DEFAULT '1'";
        $command_field['certif_min_score'] = "ALTER TABLE $tbl_course_quiz ADD COLUMN certif_min_score float(6,2) NOT NULL DEFAULT '50.00'";
        $command_field['score_pass'] = "ALTER TABLE $tbl_course_quiz ADD COLUMN score_pass int(11) NOT NULL DEFAULT '50'";
        $command_field['quiz_type'] = "ALTER TABLE $tbl_course_quiz ADD COLUMN quiz_type int(11) NOT NULL DEFAULT '1'";
        $command_field['start_time'] = "ALTER TABLE $tbl_course_quiz ADD COLUMN start_time datetime NOT NULL default '0000-00-00 00:00:00'";
        $command_field['end_time'] = "ALTER TABLE $tbl_course_quiz ADD COLUMN end_time datetime NOT NULL default '0000-00-00 00:00:00'";
        echo '<pre>';
        echo "<strong>Database : ".$tbl_course_quiz."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End Quiz table
        
        // Quiz scenario table
        $tbl_course_quiz_scenario = Database::get_course_table(TABLE_QUIZ_SCENARIO, $row_course->db_name);
        $sql = "DESC $tbl_course_quiz_scenario";
        // Possible missing fields
        $missing_fields = array('certif_template','certif_min_score','score_pass','quiz_type');
        // Execute this command if the field does not exists
        $command_field['certif_template'] = "ALTER TABLE $tbl_course_quiz_scenario ADD COLUMN certif_template int(11) NOT NULL DEFAULT '1'";
        $command_field['certif_min_score'] = "ALTER TABLE $tbl_course_quiz_scenario ADD COLUMN certif_min_score float(6,2) NOT NULL DEFAULT '50.00'";
        $command_field['score_pass'] = "ALTER TABLE $tbl_course_quiz_scenario ADD COLUMN score_pass int(11) NOT NULL DEFAULT '50'";
        $command_field['quiz_type'] = "ALTER TABLE $tbl_course_quiz_scenario ADD COLUMN quiz_type int(11) NOT NULL DEFAULT '1'";
        echo '<pre>';
        echo "<strong>Database : ".$tbl_course_quiz_scenario."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End Quiz scenario table
        
        // Work table
        $tbl_course_work = Database::get_course_table(TABLE_STUDENT_PUBLICATION, $row_course->db_name);
        $sql = "DESC $tbl_course_work";
        // Possible missing fields
        $missing_fields = array('description','corrected_file');
        // Execute this command if the field does not exists
        $command_field['description'] = "ALTER TABLE $tbl_course_work CHANGE COLUMN description description text default NULL";
        $command_field['corrected_file'] = "ALTER TABLE $tbl_course_work ADD COLUMN corrected_file varchar(200) default NULL";
        echo '<pre>';
        echo "<strong>Database : ".$tbl_course_work."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End Work table
        
        // Lp table
        $tbl_course_lp = Database::get_course_table(TABLE_LP_MAIN, $row_course->db_name);
        $sql = "DESC $tbl_course_lp";
        // Possible missing fields
        $missing_fields = array('certif_template','certif_min_score','certif_min_progress');
        // Execute this command if the field does not exists
        $command_field['certif_template'] = "ALTER TABLE $tbl_course_lp ADD COLUMN certif_template  int	unsigned not null  default 1";
        $command_field['certif_min_score'] = "ALTER TABLE $tbl_course_lp ADD COLUMN certif_min_score float(6,2) NOT NULL DEFAULT '50.00'";
        $command_field['certif_min_progress'] = "ALTER TABLE $tbl_course_lp ADD COLUMN certif_min_progress float(6,2) NOT NULL DEFAULT '50.00'";
        echo '<pre>';
        echo "<strong>Database : ".$tbl_course_lp."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End Lp table
        
        // Survey table
        $tbl_course_survey = Database::get_course_table(TABLE_SURVEY, $row_course->db_name);
        $sql = "DESC $tbl_course_survey";
        // Possible missing fields
        $missing_fields = array('question_per_page');
        // Execute this command if the field does not exists
        $command_field['question_per_page'] = "ALTER TABLE $tbl_course_survey ADD COLUMN question_per_page enum('0','1') NOT NULL default '0'";
        echo '<pre>';
        echo "<strong>Database : ".$tbl_course_survey."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End Survey table
        
        // Couse settings
        $tbl_course_settings = Database::get_course_table(TABLE_COURSE_SETTING, $row_course->db_name);
        
        $check = Database::query("SELECT id FROM $tbl_course_settings WHERE variable = 'email_alert_manager_on_new_doc' ");
       
        if (Database::num_rows($check) == 0) 
        {
            Database::query("INSERT INTO $tbl_course_settings  (variable,value,category) VALUES ('email_alert_manager_on_new_doc',1,'work')");

           $affected_lp_rows += Database::affected_rows();
          
        }
        $check = Database::query("SELECT id FROM $tbl_course_settings WHERE variable = 'email_alert_to_user_subscribe_in_course' ");
       
        if (Database::num_rows($check) == 0) 
        {
            Database::query("INSERT INTO $tbl_course_settings  (variable,value,category) VALUES ('email_alert_to_user_subscribe_in_course',0,'announcement')");

           $affected_lp_rows += Database::affected_rows();
          
        }
        // End Couse settings
        
        
        
    }    
}
$tbl_group = Database::get_course_table(TABLE_MAIN_GROUP);
Database::query("ALTER TABLE $tbl_group RENAME TO group_social;");
echo '<p>'.$affected_lp_rows.' fields updated</p>';
// Script for add missing columns to dokeos_main tables