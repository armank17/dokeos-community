<?php
// name of the language file that needs to be included
$language_file = array('mobile');
// including the global dokeos file
require_once '../../../inc/global.inc.php';
require_once api_get_path(SYS_MODEL_PATH).'mobile/MobileModel.php';
require_once api_get_path(SYS_CONTROLLER_PATH).'mobile/MobileController.php';
$libpath = api_get_path(LIBRARY_PATH);


require_once $libpath.'course.lib.php';
require_once $libpath.'debug.lib.inc.php';
require_once $libpath.'system_announcements.lib.php';
require_once $libpath.'groupmanager.lib.php';
require_once $libpath.'usermanager.lib.php';
require_once $libpath.'certificatemanager.lib.php';
require_once $libpath.'sessionmanager.lib.php';

// get actions
$actions = array('login', 'logout', 'course', 'validateUser', 'courseview');
$action = 'login';
if (isset($_GET['action']) && in_array($_GET['action'],$actions)) {
	$action = $_GET['action'];
}

// testing controller object
$objMobile = new MobileController();

if(api_get_user_id() > 0)
{
    // distpacher actions to controller
    switch ($action) {
	case 'course':          $objMobile->course();break;
	case 'courseview':      $objMobile->courseview();break;
        case 'logout':          $objMobile->logout();break;
        case 'validateUser':    $objMobile->validateUser();break;
	default:                $objMobile->course();break;
}
}
else
{
    $objMobile->login();
}