<?php

require_once '../inc/global.inc.php';
require_once '../inc/lib/fileUpload.lib.php';

// only for admin account
api_protect_admin_script();

$tbl_courses = Database::get_main_table(TABLE_MAIN_COURSE);
$rs_course = Database::query("SELECT code, db_name, directory FROM $tbl_courses");

$sys_course_path = api_get_path(SYS_COURSE_PATH);
$sys_user = api_get_user_id();

if (Database::num_rows($rs_course) > 0) {
    while ($row_course = Database::fetch_object($rs_course)) {

        $_course['dbName'] = $row_course->db_name;
        
        //Images(Photos) folder for tool AUHTOR
        $Folder_source = api_get_path(SYS_CODE_PATH) . "default_course_document/images";
        $Folder_target = $sys_course_path . $row_course->directory . '/document/images/';
        full_copy($Folder_source, $Folder_target);
        //insert data files
        insert_images_author($_course);
        insert_diagrams_author($_course);
        echo 'Restaurando carpeta images para: ' . $row_course->directory . '<br>';

        //Avatars(mascot) folder for tool AUTHOR
        $Folder_source = api_get_path(SYS_CODE_PATH) . "default_course_document/mascot";
        $Folder_target = $sys_course_path . $row_course->directory . '/document/mascot/';
        full_copy($Folder_source, $Folder_target);
        //insert data files
        insert_avatar_author($_course);
        echo 'Restaurando carpeta mascot(Avatar) para: ' . $row_course->directory . '<br>';

        //Mindmaps folder for tool AUTHOR
        $Folder_source = api_get_path(SYS_CODE_PATH) . "default_course_document/mindmaps";
        $Folder_target = $sys_course_path . $row_course->directory . '/document/mindmaps/';
        full_copy($Folder_source, $Folder_target);
        //insert data files
        insert_mindmaps_author($_course);
        echo 'Restaurando carpeta mindmaps para: ' . $row_course->directory . '<br><br>';
    }
}

function full_copy($source, $target) {
    if (is_dir($source)) {
        @mkdir($target);
        $d = opendir($source);
        while ($entry = readdir($d)) {
            if ($entry == '.' || $entry == '..') {
                continue;
            } 
            $Entry = $source . '/' . $entry;
            if (is_dir($Entry)) {
                full_copy($Entry, $target . '/' . $entry);
                continue;
            } copy($Entry, $target . '/' . $entry);
        }
        closedir($d);
    } else {
        copy($source, $target);
    }
}

function insert_images_author($_course) {
    global $sys_user;
    $id = add_document($_course, '/images/author/h3-man-office-smile.jpg', 'file', '69713', 'h3-man-office-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/i2-man-office-sitting.jpg', 'file', '45932', 'i2-man-office-sitting.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/d2-man-arrow-left.jpg', 'file', '69723', 'd2-man-arrow-left.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/g3-man-casual-smile.jpg', 'file', '83556', 'g3-man-casual-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/f1-man-pointing-right.jpg', 'file', '76354', 'f1-man-pointing-right.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/a3-man-casual-smile.jpg', 'file', '99018', 'a3-man-casual-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/b1-man-denying.jpg', 'file', '86305', 'b1-man-denying.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/j3-woman-casual-smile.jpg', 'file', '38584', 'j3-woman-casual-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/c1-man-medic-note.jpg', 'file', '33620', 'c1-man-medic-note.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/i1-man-office-watching.jpg', 'file', '41176', 'i1-man-office-watching.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/j1-woman-casual-presentation.jpg', 'file', '59607', 'j1-woman-casual-presentation.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/images/author/h1-man-office-read.jpg', 'file', '51667', 'h1-man-office-read.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/g1-man-casual-pointing-left.jpg', 'file', '57032', 'g1-man-casual-pointing-left.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author//images/author/k2-taking-care.jpg', 'file', '83621', 'k2-taking-care.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/c2-man-medic-success.jpg', 'file', '76595', 'c2-man-medic-success.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/j2-woman-casual-success.jpg', 'file', '103331', 'j2-woman-casual-success.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/g2-man-casual-win.jpg', 'file', '60266', 'g2-man-casual-win.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/e3-man-office-explain.jpg', 'file', '72193', 'e3-man-office-explain.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/e1-man-office-smile.jpg', 'file', '70433', 'e1-man-office-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/b2-man-question.jpg', 'file', '72890', 'b2-man-question.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/f2-man-pointing-left.jpg', 'file', '93506', 'f2-man-pointing-left.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/d1-man-arrow-right.jpg', 'file', '26350', 'd1-man-arrow-right.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/c3-man-medic-write.jpg', 'file', '73163', 'c3-man-medic-write.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/i3-man-office-smile.jpg', 'file', '81377', 'i3-man-office-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/a2-man-card.jpg', 'file', '103529', 'a2-man-card.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/e2-man-office-result.jpg', 'file', '80602', 'e2-man-office-result.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/b3-man-casual-smile.jpg', 'file', '111029', 'b3-man-casual-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/k3-woman-office-smile.jpg', 'file', '33530', 'k3-woman-office-smile.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/k1-woman-office-pointing-right.jpg', 'file', '58514', 'k1-woman-office-pointing-right.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/h2-man-office-thinking.jpg', 'file', '70306', 'h2-man-office-thinking.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/f3-man-read-ipad.jpg', 'file', '92132', 'f3-man-read-ipad.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/d3-man-pointing.jpg', 'file', '64098', 'd3-man-pointing.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/author/a1-man-casual-right.jpg', 'file', '77911', 'a1-man-casual-right.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
}

function insert_diagrams_author($_course) {
    global $sys_user;
    $id = add_document($_course, '/images/diagrams/author/gearbox.jpg', 'file', '28646', 'gearbox.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/diagrams/author/alaska_chart.jpg', 'file', '9429', 'alaska_chart.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/images/diagrams/author/argandgaussplane.jpg', 'file', '7754', 'argandgaussplane.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
}

function insert_avatar_author($_course) {
    global $sys_user;
    $id = add_document($_course, '/mascot/author/medical_2.png', 'file', '19994', 'medical_2.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/agreement.png', 'file', '38500', 'agreement.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/medical_1.png', 'file', '29227', 'medical_1.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/img5.png', 'file', '25672', 'img5.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/happened.png', 'file', '29398', 'happened.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/expositor.png', 'file', '32283', 'expositor.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/attention.png', 'file', '35636', 'attention.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/Cbooks.png', 'file', '48931', 'Cbooks.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/avatar_mirrow.png', 'file', '23137', 'avatar_mirrow.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/answer_unknow.png', 'file', '21690', 'answer_unknow.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mascot/author/answer_shuffle.png', 'file', '43421', 'answer_shuffle.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
}

function insert_mindmaps_author($_course) {
    global $sys_user;
    $id = add_document($_course, '/mindmaps/author/Distress.png', 'file', '24176', 'Distress.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mindmaps/author/Menu.png', 'file', '32078', 'Menu.png');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
    $id = add_document($_course, '/mindmaps/author/Project.jpg', 'file', '60337', 'Project.jpg');
    api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAddedFromLearnpath', $sys_user);
}