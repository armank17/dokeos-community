<?php
/* For licensing terms, see /license.txt */
/**
*	@package dokeos.messages
*/

$language_file = array('registration','messages','userInfo','group');
$cidReset= true;
require_once '../inc/global.inc.php';
api_block_anonymous_users();
if (api_get_setting('allow_message_tool')!='true'){
	api_not_allowed();
}
require_once api_get_path(LIBRARY_PATH).'message.lib.php';


/*
		HEADER
*/
//$htmlHeadXtra[] = '<script type="text/javascript" src="/main/inc/lib/javascript/jquery.js"></script>';
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="projection, screen">';


if ($_REQUEST['f']=='social') {
	$this_section = SECTION_SOCIAL;
	$interbreadcrumb[]= array ('url' => api_get_path(WEB_PATH).'main/social/home.php','name' => get_lang('Social'));
	$interbreadcrumb[]= array ('url' => 'inbox.php?f=social','name' => get_lang('Inbox'));	
} else {
	$this_section = SECTION_MYPROFILE;
	$interbreadcrumb[]= array ('url' => '#','name' => get_lang('Profile'));
	$interbreadcrumb[]= array ('url' => 'inbox.php','name' => get_lang('Inbox'));
}
$interbreadcrumb[]= array ('url' => '#','name' => get_lang('View'));
Display::display_header('');

// Display actions
echo '<div class="actions">';
echo '<a href="group.php?'.api_get_cidReq().'">'.Display::return_icon('pixel.gif',get_lang('MyGroup'),array('class'=>'toolactionplaceholdericon toolactiongroupimage')).get_lang('MyGroup').'</a>';
echo '<a href="inbox.php?'.api_get_cidReq().'">'.Display::return_icon('pixel.gif', get_lang('Inbox'),array('class'=>'toolactionplaceholdericon toolactioninbox')).get_lang('Inbox').'</a>';
echo '<a href="new_message.php?'.api_get_cidReq().'">'.Display::return_icon('pixel.gif', get_lang('ComposeMessage'),array('class'=>'toolactionplaceholdericon toolactionsinvite')).get_lang('ComposeMessage').'</a>';
echo '<a href="outbox.php?'.api_get_cidReq().'">'.Display::return_icon('pixel.gif', get_lang('Outbox'),array('class'=>'toolactionplaceholdericon toolactionoutbox')).get_lang('Outbox').'</a>';
echo '</div>';
// Start content
echo '<div id="content">';

echo '<div id="social-content">';	
	if (empty($_GET['id'])) {
		$id_message = $_GET['id_send'];
		$source = 'outbox';
		$show_menu = 'messages_outbox';
	} else {
		$id_message = $_GET['id'];
		$source = 'inbox';
		$show_menu = 'messages_inbox';
	}
	$id_content_right = '';
	//LEFT COLUMN
	if (api_get_setting('allow_social_tool') != 'true') { 
      //
	} else {
		require_once api_get_path(LIBRARY_PATH).'social.lib.php';
		$id_content_right = 'social-content-right';
		echo '<div id="social-content-left">';	
			//this include the social menu div
			
			SocialManager::show_social_menu($show_menu);
		echo '</div>';				
	}

	echo '<div id="'.$id_content_right.'">';
		//MAIN CONTENT
		$message = MessageManager::show_message_box($id_message,$source);
		
		if (!empty($message)) {
			echo $message;
		} else {
			api_not_allowed();
		}

	echo '</div>';

echo '</div>';

// End content
echo '</div>';

// Actions
//echo '<div class="actions">';
//echo '</div>';

/*
		FOOTER
*/
Display::display_footer();
?>