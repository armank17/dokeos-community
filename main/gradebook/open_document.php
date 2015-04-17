<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2008 Dokeos Latinoamerica SAC
	Copyright (c) 2006 Dokeos SPRL
	Copyright (c) 2006 Ghent University (UGent)
	Copyright (c) various contributors

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/

// include the global Dokeos file
require_once ('../inc/global.inc.php');

// include additional libraries
require_once ('../inc/lib/document.lib.php');

// access restrictions
api_block_anonymous_users();

// URL parameter checking
if (!is_numeric($_GET['itemid'])){
	Display::display_header();
	api_not_allowed();
	Display::display_footer();
}

// getting the course information
$course_info = Database :: get_course_info($_GET['coursecode']);
if (empty($course_info)){
	Display::display_header();
	api_not_allowed();
	Display::display_footer();
}

switch ($_GET['item']){
	case 'dropbox':
		$table = Database :: get_course_table(TABLE_DROPBOX_FILE, $course_info['db_name']);
		$sql = "SELECT filename FROM $table WHERE id='".Database::escape_string(Security::Remove_XSS($_GET['idemid']))."' AND uploader_id = '".Database::escape_string(Security::Remove_XSS($_GET['user_id']))."'";
		$result = Database::query($sql, __FILE__, __LINE__);
		$row = Database::fetch_row($result);
		$url = $row[0];
		break;
	case 'studentpublication':
		$table = Database :: get_course_table(TABLE_STUDENT_PUBLICATION, $course_info['db_name']);
		$table_ip = Database :: get_course_table(TABLE_ITEM_PROPERTY, $course_info['db_name']);

		$sql = "SELECT url FROM $table_ip prop, $table pub 
					WHERE prop.tool = 'work'
					AND prop.insert_user_id = '".Database::escape_string(Security::Remove_XSS($_GET['user_id']))."'
					AND prop.ref = pub.id'";
		$result = Database::query($sql, __FILE__, __LINE__);
		$row = Database::fetch_row($result);
		$url = $row[0];
		break;
}

DocumentManager :: file_send_for_download(api_get_path(SYS_COURSE_PATH).$course_info[''].'/'.$url);
