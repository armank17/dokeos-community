<?php
/* For licensing terms, see /license.txt */
/**
*	@package dokeos.messages
*/

$language_file = array('registration','messages','userInfo');
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
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {

      $(".user_info").click(function () {
        var myuser_id = $(this).attr("id");        
        var user_info_id = myuser_id.split("user_id_");
        my_user_id = user_info_id[1];
        
        $.ajax({
            url: "'.api_get_path(WEB_AJAX_PATH).'user_manager.ajax.php?a=get_user_info&user_id="+my_user_id,
            success: function(data){
                var dialog_div = $("<div id=\'html_user_info\'></div>");
                dialog_div.html(data);
                dialog_div.dialog({
                modal: true,
                title: "'.get_lang('UserInfo').'",
                width: 640,
                height : 240,
                resizable:false
                });
            }
        });
    });
   
});
</script>'  ;

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
if (api_get_setting('allow_social_tool') == 'true') {
  echo '<a href="'.api_get_path(WEB_PATH).'main/social/home.php">'.Display::return_icon('pixel.gif',get_lang('Home'),array('class' => 'toolactionplaceholdericon toolactionshome')).get_lang('Home').'</a>';
} else {
  $social_parameter = '';
  if ($_GET['f']=='social' || api_get_setting('allow_social_tool') == 'true') {
    $social_parameter = '?f=social';
  } else {
    echo '<a href="'.api_get_path(WEB_PATH).'main/auth/profile.php?type=reduced">'.Display::return_icon('pixel.gif', get_lang('EditNormalProfile'),array('class'=>'actionplaceholdericon actionedit')).'&nbsp;'.get_lang('EditNormalProfile').'</a>';
  }
}
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/inbox.php?f=social">'.Display::return_icon('pixel.gif',get_lang('Inbox'), array('class' => 'toolactionplaceholdericon toolactioninbox')).get_lang('Inbox').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/new_message.php?f=social">'.Display::return_icon('pixel.gif',get_lang('Inbox'), array('class' => 'toolactionplaceholdericon toolactionsinvite')).get_lang('ComposeMessage').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/outbox.php?f=social">'.Display::return_icon('pixel.gif',get_lang('Outbox'), array('class' => 'toolactionplaceholdericon toolactionoutbox')).get_lang('Outbox').'</a>';
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