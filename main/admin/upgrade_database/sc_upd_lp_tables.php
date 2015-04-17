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
        $tbl_lp = Database::get_course_table(TABLE_LP_MAIN, $row_course->db_name);
        echo "<p>Alter $tbl_lp</p>";
        Database::query("ALTER TABLE $tbl_lp ADD origin_tool enum('author', 'module') NULL");
    }
}