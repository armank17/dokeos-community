<?php
/* For licensing terms, see /license.txt */
/**
 * @package dokeos.social
 * @author Julio Montoya <gugli100@gmail.com>
 */
$cidReset=true;
$language_file = array('userInfo', 'admin');
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'group_portal_manager.lib.php';
require_once api_get_path(LIBRARY_PATH).'social.lib.php';
require_once api_get_path(LIBRARY_PATH).'message.lib.php';
require_once api_get_path(LIBRARY_PATH).'text.lib.php';
require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'fckeditor/fckeditor.php';
require_once api_get_path(LIBRARY_PATH).'ckeditor/ckeditor_php5.php';

//include_once api_get_path(LIBRARY_PATH) .'formvalidator/FormValidator.class.php';
api_block_anonymous_users();

$this_section = SECTION_SOCIAL;

//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.js" type="text/javascript" language="javascript"></script>'; //jQuery
//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js" type="text/javascript" language="javascript"></script>';
//$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="projection, screen">';



$allowed_views = array('mygroups','newest','pop');
$interbreadcrumb[]= array ('url' =>'home.php','name' => get_lang('Social'));

if (isset($_GET['view']) && in_array($_GET['view'],$allowed_views)) {
	if ($_GET['view'] == 'mygroups') {
		$interbreadcrumb[]= array ('url' =>'groups.php','name' => get_lang('Groups'));
		$interbreadcrumb[]= array ('url' =>'#','name' => get_lang('MyGroups'));
	} else if ( $_GET['view'] == 'newest') {
		$interbreadcrumb[]= array ('url' =>'groups.php','name' => get_lang('Groups'));
		$interbreadcrumb[]= array ('url' =>'#','name' => get_lang('Newest'));
	} else  {
		$interbreadcrumb[]= array ('url' =>'groups.php','name' => get_lang('Groups'));
		$interbreadcrumb[]= array ('url' =>'#','name' => get_lang('Popular'));
	}
} else {
	$interbreadcrumb[]= array ('url' =>'groups.php','name' => get_lang('Groups'));
	$interbreadcrumb[]= array ('url' =>'#','name' => get_lang('GroupList'));
}

Display :: display_header($tool_name, 'Groups');

$tok = Security::get_token();

if (api_is_anonymous()) {
	api_not_allowed();
}

if (api_get_setting('allow_message_tool') != 'true' && api_get_setting('allow_social_tool') != 'true') {
	api_not_allowed();
}

if ( isset($_REQUEST['user_friend']) ) {
	$info_user_friend=array();
	$info_path_friend=array();
 	$userfriend_id=Security::remove_XSS($_REQUEST['user_friend']);
 	// panel=1  send message
 	// panel=2  send invitation
 	$panel=Security::remove_XSS($_REQUEST['view_panel']);
 	$info_user_friend=api_get_user_info($userfriend_id);
 	$info_path_friend=UserManager::get_user_picture_path_by_id($userfriend_id,'web',false,true);
}

$group_id = intval($_GET['group_id']);
$message_id = intval($_GET['message_id']);
$actions = array('add_message_group','edit_message_group','reply_message_group');

$allowed_action = (isset($_GET['action']) && in_array($_GET['action'],$actions))?Security::remove_XSS($_GET['action']):'';

$to_group = '';
$subject = '';
$message = '';
if (!empty($group_id) && $allowed_action) {
	$group_info = GroupPortalManager::get_group_data($group_id);
	$to_group   = $group_info['name'];
	if (!empty($message_id)) {
		$message_info = MessageManager::get_message_by_id($message_id);

		if ($allowed_action == 'reply_message_group') {
			//$subject  = utf8_encode(get_lang('Reply')).': '.api_xml_http_response_encode($message_info['title']);
                        $title_reply=api_xml_http_response_encode($message_info['title']);
                        $aray_reply=explode(':',$title_reply);
                       // $subject = $aray_reply[0];
                        //$subject = str_replace($aray_reply[0],'',$title_reply);
                        $dem = strpos($aray_reply[0],$title_reply);
                        if($dem===false){
                            $subject = str_replace($aray_reply[0],'',$title_reply);
                            //$subject = utf8_encode(get_lang('Reply')).$subject;
                            $subject = html_entity_decode(get_lang('Reply')).$subject;
                        }else{
                            $subject = get_lang('Reply').': '.api_xml_http_response_encode($message_info['title']);;
                            $subject = html_entity_decode($subject);
                        }
                        
		} else {
			$subject  = api_xml_http_response_encode(utf8_encode($message_info['title']));
			$message  = api_xml_http_response_encode(utf8_encode($message_info['content']));
		}	
	} 	
}

$page_item = !empty($_GET['topics_page_nr'])?intval($_GET['topics_page_nr']):1;

$param_item_page = isset($_GET['items_page_nr']) && isset($_GET['topic_id'])?('&items_'.intval($_GET['topic_id']).'_page_nr='.(!empty($_GET['topics_page_nr'])?intval($_GET['topics_page_nr']):1)):'';
$page_topic  = !empty($_GET['topics_page_nr'])?intval($_GET['topics_page_nr']):1;

// Display actions
echo '<div  class="actions">';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/home.php">'.Display::return_icon('pixel.gif',get_lang('Home'),array('class' => 'toolactionplaceholdericon toolactionshome')).get_lang('Home').'</a>';
// Only admins and teachers can create groups
if (api_is_allowed_to_edit(null,true)) {
    echo '<a href="'.api_get_path(WEB_PATH).'main/social/group_add.php">'.Display::return_icon('pixel.gif',get_lang('CreateAgroup'),array('class' => 'toolactionplaceholdericon toolactionsgroup')).get_lang('CreateAgroup').'</a>';
}
echo '<a href="'.api_get_path(WEB_PATH).'main/social/groups.php">'.Display::return_icon('pixel.gif',get_lang('MyGroups'),array('class' => 'toolactionplaceholdericon toolactiongroupimage')).get_lang('MyGroups').'</a>';

if (isset($_GET['id']) && $_GET['id'] >= 0) {
  $group_id = Security::remove_XSS($_GET['id']);
  $relation_group_title = get_lang('IamAnAdmin');  
  //$links .=  '<a href="'.api_get_path(WEB_CODE_PATH).'social/topic_add.php?view_panel=1&height=400&width=610&user_friend='.api_get_user_id().'&group_id='.$group_id.'&action=add_message_group"  title="'.get_lang('ComposeMessage').'">'.Display::return_icon('pixel.gif',get_lang('NewTopic'),array('class' => 'toolactionplaceholdericon toolsocialnewtopic')).get_lang('NewTopic').'</a>';
  $links .=  '<a href="groups.php?id='.$group_id.'">'.	Display::return_icon('pixel.gif',get_lang('MessageList'),array('class' => 'toolactionplaceholdericon toolsocialmessagelist')).get_lang('MessageList').'</a>';
  // only group admins can edit the group
  if (GroupPortalManager::is_group_admin($group_id)) {
    $links .=  '<a href="group_edit.php?id='.$group_id.'">'.Display::return_icon('pixel.gif',get_lang('EditGroup'),array('class' => 'toolactionplaceholdericon tooledithome')).get_lang('EditGroup').'</a>';
  }

  //my relation with the group is set here
  $my_group_role = GroupPortalManager::get_user_group_role(api_get_user_id(), $group_id);
  if ($my_group_role == GROUP_USER_PERMISSION_READER) {
      $links .=  '<a href="groups.php?id='.$group_id.'&action=leave&u='.api_get_user_id().'">'.	Display::return_icon('pixel.gif', get_lang('LeaveGroup'), array('class'=>'toolactionplaceholdericon tooldeletegroup')).'<span class="social-menu-text4" >'.get_lang('LeaveGroup').'</span></a>';
  }

  echo $links;
}
echo '</div>';

// Start content
echo '<div id="content">';

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    echo '<div class="confirmation-message">'.get_lang('GroupDeleted').'</div>';
}


echo '<div id="social-content">';
echo '<div id="social-content-all">';



$form = new FormValidator('form', 'post','groups.php?id='.$group_id.'&anchor_topic='.Security::remove_XSS($_GET['anchor_topic']).'&topics_page_nr='.$page_topic.$param_item_page);	

        $form->addElement('hidden', 'action', $allowed_action);
        $form->addElement('hidden', 'group_id', $group_id);
        $form->addElement('hidden', 'parent_id', $message_id);
        $form->addElement('hidden', 'message_id', $message_id);
        $form->addElement('hidden', 'token', $tok);
        
        
        
        
        
        $form->addElement('html', "<div class='row'>
<div class='label' style='margin-top:0px !important'>
<div align='left' style='padding-left:15px;text-align: right;'>".api_xml_http_response_encode(get_lang('To')).":</div>
</div>
<div class='formw' style='font-weight:bolder;'>
".api_xml_http_response_encode($to_group)."
</div>
</div>");
 
        $form->add_textfield('title','<div align="left" style="padding-left:15px;text-align: right;">'. api_xml_http_response_encode(get_lang('Subject')).' : '.'</div>',false,array('size'=>'60','class'=>'focus','id'=>'note_title_id'));	
        
	$form->add_html_editor('content1','<div align="left" style="padding-left:15px;text-align: right;">'. api_xml_http_response_encode(get_lang('Message')).' : '.'</div>', false, false, api_is_allowed_to_edit()
		? array('ToolbarSet' => 'Messages', 'Width' => '99%', 'Height' => '270')
		: array('ToolbarSet' => 'Messages', 'Width' => '99%', 'Height' => '270', 'UserStatus' => 'student'));


        $form->addElement('file', 'attach_1',api_xml_http_response_encode(get_lang('AddedResources').':'));
        
        $form->addElement('style_submit_button', 'SubmitNote', api_xml_http_response_encode(get_lang('SendMessage')), 'class="save"');
        
        // setting the defaults        
    
                    
            $defaults['title'] = $subject;
            $defaults['content1'] = $message;
            $form->setDefaults($defaults);
     

        
	$form->display();

?>


<?php

	echo '</div>';

echo '</div>';

// End content
echo '</div>';
 if (isset($_GET['id']) && $_GET['id'] >= 0) { 
// Actions
echo '<div class="actions">';          
      $group_id = intval($_GET['id']);
      $user_role = GroupPortalManager::get_user_group_role(api_get_user_id(), $group_id);
      $links = '';
      if (in_array($user_role, array(GROUP_USER_PERMISSION_ADMIN, GROUP_USER_PERMISSION_MODERATOR, GROUP_USER_PERMISSION_READER))) {
        $links =  '<a href="group_members.php?id='.$group_id.'">'.Display::return_icon('pixel.gif', get_lang('MemberList'), array('class' => 'actionplaceholdericon actiongroupstudentview')).get_lang('MemberList').'</a>';
      }
      if (in_array($user_role, array(GROUP_USER_PERMISSION_ADMIN, GROUP_USER_PERMISSION_MODERATOR))) {
        $links .=  '<a href="group_waiting_list.php?id='.$group_id.'">'.Display::return_icon('pixel.gif', get_lang('WaitingList'), array('class' => 'actionplaceholdericon actionlatestchanges')).get_lang('WaitingList').'</a>';
      }
      if (GroupPortalManager::is_group_member($group_id)) {
        $links .=  '<a href="group_invitation.php?id='.$group_id.'">'.Display::return_icon('pixel.gif', get_lang('InviteFriends'), array('class' => 'actionplaceholdericon actionadduser')).get_lang('InviteFriends').'</a>';
      }
      echo $links;    
echo '</div>';
}

Display :: display_footer();

?>
<script type="text/javascript">
var $this, attribute,image;
$(".message-group-title-topic").on("click",function () {
    var span_display = $(this).data("display");
    var id = this.id;
    $this = $(this);
    image = $this.data("images")
    $("#topic_" + id).children(".items_" + id + "_grid_container").slideToggle();    
    if (span_display) {
        $(this).data("display",false);
        attribute = {
            src: image.hide.src,
            title: image.hide.title,
            alt: image.hide.alt
        };
        $("img", $this).attr(attribute);
    } else {
        $(this).data("display",true);
        attribute = {
            src: image.show.src,
            title: image.show.title,
            alt: image.show.alt
        };
        $("img", $this).attr(attribute);
    }
});    
</script>