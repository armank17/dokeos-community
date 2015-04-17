<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) Dokeos SPRL

	For a full list of contributors, see "credits.txt".
	For licensing terms, see "dokeos_license.txt"

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	http://www.dokeos.com
==============================================================================
*/

/**
==============================================================================
*                  HOME PAGE FOR EACH COURSE
*
*	This page, included in every course's index.php is the home
*	page. To make administration simple, the teacher edits his
*	course from the home page. Only the login detects that the
*	visitor is allowed to activate, deactivate home page links,
*	access to the teachers tools (statistics, edit forums...).
*
*	@package dokeos.course_home
==============================================================================
*/
// Name of the language file that needs to be included.
$language_file = 'course_home';
// include the global Dokeos file
require '../../main/inc/global.inc.php';
// include the functions
require 'course_home_functions.php';

$tool_table = Database::get_course_table(TABLE_TOOL_LIST);
$sql = "SELECT 
            `name`, 
            `link`, 
            `image`, 
            `admin`, 
            `address`, 
            `added_tool`, 
            `target`, 
            `category`, 
            `session_id` 
        FROM $tool_table 
        WHERE `id` = '".intval($_GET['id'])."' 
        LIMIT 1;";
$rs = Database::query($sql);
$row = Database::fetch_array($rs, 'ASSOC');

switch($_GET['action']) {
    case 'make_visible' :
        $visibility = 1;
        break;
    case 'make_invisible' :
        $visibility = 0;
        break;
}
//var_dump($sql, $row, 'visibility: ' . $visibility, 'row session_id: ' . $row['session_id'], 'api_get_session_id: ' . api_get_session_id(), $row['session_id'] != api_get_session_id());
if ($row['session_id'] != api_get_session_id()) {
    $sql = "SELECT 
                `id` 
            FROM $tool_table 
            WHERE `name` = '" . $row['name'] . "' AND `session_id` = '" . api_get_session_id() . "'
            LIMIT 1;";
    $rs = Database::query($sql);
    //var_dump($sql, Database::num_rows($rs));
//    if (Database::num_rows($rs) == 0) {
//        $sql = "INSERT INTO $tool_table (
//                    `name`, 
//                    `link`, 
//                    `image`, 
//                    `visibility`, 
//                    `admin`, 
//                    `address`, 
//                    `added_tool`, 
//                    `target`, 
//                    `category`, 
//                    `session_id`
//                ) VALUES (
//                    '" . $row['name'] . "', 
//                    '" . $row['link'] . "', 
//                    '" . $row['image'] . "', 
//                    '" . $visibility . "', 
//                    '" . $row['admin'] . "', 
//                    '" . $row['address'] . "', 
//                    '" . $row['added_tool'] . "', 
//                    '" . $row['target'] . "', 
//                    '" . $row['category'] . "', 
//                    '" . api_get_session_id() . "' 
//                );";
//       // Database::query($sql);
//    } else {
//        change_tool_visibility($_GET['id'], $visibility);
//    }
} else {
    change_tool_visibility($_GET['id'], $visibility);
}