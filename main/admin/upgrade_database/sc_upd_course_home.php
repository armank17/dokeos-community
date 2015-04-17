<?php
require_once __DIR__ . '/../../inc/global.inc.php';

// only for admin account
//api_protect_admin_script();

// Script for add missing columns to dokeos course tables
$tbl_courses = Database::get_main_table(TABLE_MAIN_COURSE);
$rs_course = Database::query("SELECT code, db_name, directory FROM $tbl_courses");
$affected_lp_rows = 0;
if (Database::num_rows($rs_course) > 0) {
    while ($row_course = Database::fetch_object($rs_course)) {
        // Document table
        $tbl_course_document = Database::get_course_table(TABLE_TOOL_LIST, $row_course->db_name);
        Database::query("ALTER TABLE $tbl_course_document CHANGE `category` `category` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;");
        $affected_lp_rows++;
        
        $fields = array(
            'announcement' => array('category' => 'common', 'admin' => '0'), 'calendar_event' => array('category' => 'common', 'admin' => '0'), 'document' => array('category' => 'common', 'admin' => '0'), 'dropbox' => array('category' => 'common', 'admin' => '0'), 'forum' => array('category' => 'common', 'admin' => '0'), 'course_setting' => array('category' => 'common', 'admin' => '1', 'visibility' => '1'), 'survey' => array('category' => 'common', 'admin' => '0'), 'user' => array('category' => 'common', 'admin' => '0'),  'wiki' => array('category' => 'common', 'admin' => '0'),
            'blog_management' => array('category' => 'deprecated', 'admin' => '0'), 'chat' => array('category' => 'deprecated', 'admin' => '0'), 'copy_course_content' => array('category' => 'deprecated', 'admin' => '0'), 'course_maintenance' => array('category' => 'deprecated', 'admin' => '0'), 'glossary' => array('category' => 'deprecated', 'admin' => '0'), 'gradebook' => array('category' => 'deprecated', 'admin' => '0'), 'group' => array('category' => 'deprecated', 'admin' => '0'), 'link' => array('category' => 'deprecated', 'admin' => '0'), 'mediabox' => array('category' => 'deprecated', 'admin' => '0'), 'mindmap' => array('category' => 'deprecated', 'admin' => '0'), 'notebook' => array('category' => 'deprecated', 'admin' => '0'), 'oogie' => array('category' => 'deprecated', 'admin' => '0'), 'student_publication' => array('category' => 'deprecated', 'admin' => '0'), 'tracking' => array('category' => 'deprecated', 'admin' => '0'), 'visio_conference' => array('category' => 'deprecated', 'admin' => '0'),
            'course_description' => array('category' => 'free', 'admin' => '0'), 'learnpath' => array('category' => 'free', 'admin' => '0'), 'quiz' => array('category' => 'free', 'admin' => '0'),
            'author' => array('category' => 'pro', 'admin' => '0'), 'Control' => array('link' => 'exercice/exercice.php', 'image' => 'control.png', 'visibility' => '1', 'admin' => '0', 'address' => '', 'added_tool' => '0', 'target' => '_self', 'category' => 'pro', 'session_id' => '0'), 'SeriousGames' => array('category' => 'pro', 'admin' => '0'), 'Shop' => array('link' => 'course_description/', 'image' => 'shop.png', 'visibility' => '1', 'admin' => '0', 'address' => '', 'added_tool' => '0', 'target' => '_self', 'category' => 'pro', 'session_id' => '0'), 'visio_classroom' => array('category' => 'pro', 'admin' => '0'), 'WebTv' => array('category' => 'pro', 'admin' => '0')
        );
        foreach ($fields as $field => $attribs) {
            $record_exists = Database::query("SELECT `id` FROM $tbl_course_document WHERE `name` = '$field';");
            if (Database::num_rows($record_exists) > 0) {
                $insert = array();
                foreach ($attribs as $attrib => $value) {
                    $insert[] = "`$attrib` = '$value'";
                }
                $insert = implode(', ', $insert);
                Database::query("UPDATE $tbl_course_document SET $insert WHERE `name` = '$field';");
                $affected_lp_rows++;
            } else {
                if ($field == 'Control' || $field == 'Shop') {
                    $att = $valu = array();
                    foreach ($attribs as $attrib => $value) {
                        $att[] = "`$attrib`";
                        $valu[] = "'$value'";
                    }
                    $att = implode(', ', $att);
                    $valu = implode(', ', $valu);
                    Database::query("INSERT INTO $tbl_course_document (`name`, $att) VALUES ('$field', $valu);");
                    $affected_lp_rows++;
                }
            }
        }
        // End document table
    }
}
