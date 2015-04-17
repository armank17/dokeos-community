<?php
require_once dirname(__FILE__).'/../../../../global.inc.php';


function checkPath() {    
    
    // dynamic streaming folder
    $webpath = api_get_path(WEB_PATH);
    $webpath = str_replace('http://', '', $webpath);
    $webpath = str_replace('https://', '', $webpath);
    $webpath = rtrim($webpath,'/');
 
    if (api_is_in_course()) {
	if (!api_is_in_group()) {
		// 1. We are inside a course and not in a group.
		if (api_is_allowed_to_edit()) {
                    if (isset($_GET['type']) && $_GET['type'] == 'document') {
                        $Config['UserFilesPath'] = api_get_path(REL_COURSE_PATH).api_get_course_path();
                    } 
                    else if (isset($_GET['type']) && $_GET['type'] == urlencode($webpath)) {
                        $Config['UserFilesPath'] = api_get_path(REL_COURSE_PATH).'streaming';
                    }
                    else {
                        $Config['UserFilesPath'] = api_get_path(REL_COURSE_PATH).api_get_course_path().'/document';
                    }	
		} else {
			// 1.2. Student
			$Config['UserFilesPath'] = api_get_path(REL_COURSE_PATH).api_get_course_path().'/document/shared_folder';
		}
	} else {
		// 2. Inside a course and inside a group.
		global $group_properties;
		$Config['UserFilesPath'] = api_get_path(REL_COURSE_PATH).api_get_course_path().'/document';
	}
    } else {
            if (api_is_platform_admin()) {
                    $Config['UserFilesPath'] = api_get_path(REL_PATH).'main/default_course_document';
            } else {
                    // 4. The user is outside courses.
                    $Config['UserFilesPath'] = api_get_path(REL_PATH).'main/upload/users/'.api_get_user_id();
            }
    }    

    $_SESSION['KCFINDER'] = array();
    $_SESSION['KCFINDER']['disabled'] = false;    
    $_SESSION['KCFINDER']['uploadURL'] = $Config['UserFilesPath'];
    $_SESSION['KCFINDER']['uploadDir'] = '';
    $_SESSION['KCFINDER']['theme'] = 'oxygen';    
    return true;
}

checkPath();

spl_autoload_register('__autoload');

?>
