<?php
/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.user
==============================================================================
*/

// name of the language file that needs to be included
$language_file[]="registration";
$language_file[]="chat";
// including the global Dokeos file
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'message.lib.php';

$action = Security::remove_XSS($_GET['action']);
if (isset($_GET['user_sender_id']) && $_GET['user_sender_id'] > 0) {
  $user_sender_id = Security::remove_XSS($_GET['user_sender_id']);
} else {
  $user_sender_id = api_get_user_id();
}

if (isset($_GET['user_receiver_id']) && $_GET['user_receiver_id'] > 0) {
  $user_receiver_id = Security::remove_XSS($_GET['user_receiver_id']);
} else {
  $user_receiver_id = api_get_user_id();
}

switch ($action) {
	case 'send':
		MessageManager::add_new_chat_invitation($user_sender_id, $user_receiver_id);
	break;
	case 'decline':
		MessageManager::decline_chat_invitation($user_sender_id, $user_receiver_id);
        // Add message in the chat file
        $date_chat = date('Y-m-d');//Date of the current chat file
        $chat_path = '/chat_files/messages-'.$date_chat.'.log.html';
        $user_info = api_get_user_info(api_get_user_id());
        $course_info = api_get_course_info(api_get_course_id());
        $path_to_chat_file = api_get_path(SYS_COURSE_PATH).$course_info['sysCode'].'/document'.$chat_path;
		$message = $user_info['firstName'].' '.get_lang('DeclinedYourInvitation');
		$msg = '<table width="100%"><tr style="font-size:smaller;"><td width="10%" valign="top" id="chat_login_name" width="90%"><b>'.get_lang("Message").':</b> </td><td><i>'.$message.'</i></td></tr></table>';
        if (is_file($path_to_chat_file)) {
		  file_put_contents($path_to_chat_file, $msg, FILE_APPEND);
        }

	break;
	case 'accept':
		MessageManager::accept_chat_invitation($user_sender_id, $user_receiver_id);
	break;	
}