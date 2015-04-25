<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.main
*/

// Language files that should be included
$language_file = array ('courses', 'index', 'admin');

// forcing the 'current course' reset, as we're not inside a course anymore
$cidReset = true;

// global Dokeos file
require_once './main/inc/global.inc.php';
// the section (for the tabs)
$this_section = SECTION_COURSES;

// Load the default course home page 
require_once 'tablet_user_portal.php';
