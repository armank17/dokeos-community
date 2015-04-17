<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */
require_once '../global.inc.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
global $_course;
$action = Security::remove_XSS($_GET['action']);
switch ($action) {
    case 'get_users_from_course_level':
        $whoisonline = Who_is_online_in_this_course(api_get_user_id(), 1, $_course['id']);
        foreach ($whoisonline as $useronline) {
            $all_user_info =  api_get_user_info($useronline['0']);
            echo '<strong>'.Display::return_icon('pixel.gif',$all_user_info['username'],array('class' => 'actionplaceholdericon actioninfo') ).$all_user_info['username'].'</strong><br>';
        }
        break;
    case 'change_chatstatus':
        $connected = changeChatStatus();
        echo json_encode(array('connected'=>$connected));
        break;
    case 'checkstatus':
        echo UserManager::is_user_chat_connected();
        break;
}


function changeChatStatus() {
    $user_id = api_get_user_id();
    $user_dat = api_get_user_info($user_id);
    $connected = false;
    if (!empty($user_id)) {
        $user_data = UserManager::get_extra_user_data($user_id);
        $fname  = 'chat_connected';
        $newfvalue =  'true';
        if (isset($user_data[$fname])) {
            if ($user_data[$fname] == 'true') {
                $newfvalue =  'false';
                $connected = false;                
                //$_SESSION['chat_username'] = $_user['username'];
                $_SESSION['chat_username'] = $user_dat['username'];;
            }
            else {
                $newfvalue =  'true';
                $connected = true;
            }
        }
        // Save new fieldlabel into user_field table.
        $field_id = UserManager::create_extra_field($fname, 1, $fname, '');
        // Save the external system's id into user_field_value table.
        $res = UserManager::update_extra_field_value($user_id, $fname, $newfvalue);
    }
    return $connected;
}
