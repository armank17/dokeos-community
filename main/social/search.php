<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * @package dokeos.social
 * @author Julio Montoya <gugli100@gmail.com>
 */

// name of the language file that needs to be included
$language_file = array('registration','admin','userInfo');
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'social.lib.php';
require_once api_get_path(LIBRARY_PATH).'group_portal_manager.lib.php';

api_block_anonymous_users();

$this_section = SECTION_SOCIAL;
$tool_name = get_lang('Search');
$interbreadcrumb[]= array ('url' =>'profile.php','name' => get_lang('Social'));

//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.js" type="text/javascript" language="javascript"></script>'; //jQuery
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="projection, screen">';


$htmlHeadXtra[] = '<script type="text/javascript">

function show_icon_edit(element_html) {
	ident="#edit_image";
	$(ident).show();
}

function hide_icon_edit(element_html)  {
	ident="#edit_image";
	$(ident).hide();
}

function action_database_panel (option_id, myuser_id) {


if (option_id==5) {
	my_txt_subject=$("#txt_subject_id").val();
} else {
	my_txt_subject="clear";
}
my_txt_content=$("#txt_area_invite").val();

$.ajax({
	contentType: "application/x-www-form-urlencoded",
	beforeSend: function(objeto) {
	$("#display_response_id").html("<img src=\'../inc/lib/javascript/indicator.gif\' />"); },
	type: "POST",
	url: "../messages/send_message.php",
	data: "panel_id="+option_id+"&user_id="+myuser_id+"&txt_subject="+my_txt_subject+"&txt_content="+my_txt_content,
	success: function(datos) {

        $("#social_dialog").dialog({
                modal: true,
                title: "'.get_lang('Message').'",
                height: "auto",
                width: "380",
                closeText:"'.get_lang('Close').'",
                resizable: false,
                draggable: false ,
                position: "center",
                closeOnEscape: true,
                close: function() {
                  location.reload();
                }                
         });

         $("#social_dialog").html("<div style=\"padding-top:30px;text-align:center;\">"+datos+"</div>");

        /* Event for when make click inside of the div then the dialog will be closed */
        $("#social_dialog").mousedown(function(){
                $("#social_dialog").remove();
                location.reload();
        });

        // alert(datos);
	 $("#display_response_id").html(datos);
	 self.parent.tb_remove();
	}
});
}
</script>';

Display :: display_header($tool_name);

// Display actions
echo '<div class="actions">';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/home.php">'.Display::return_icon('pixel.gif',get_lang('Home'),array('class' => 'toolactionplaceholdericon toolactionshome')).get_lang('Home').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/inbox.php?f=social">'.Display::return_icon('pixel.gif', get_lang('Messages'), array('class' => 'toolactionplaceholdericon toolactionsmessage')).get_lang('Messages').$count_unread_message.'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/invitations.php">'.Display::return_icon('pixel.gif',get_lang('Invitations'), array('class' => 'toolactionplaceholdericon toolactionsinvite')).get_lang('Invitations').$total_invitations.'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/profile.php">'.Display::return_icon('pixel.gif',get_lang('ViewMySharedProfile'), array('class' => 'toolactionplaceholdericon toolactionsprofile')).get_lang('ViewMySharedProfile').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/friends.php">'.Display::return_icon('pixel.gif',get_lang('Friends'), array('class' => 'toolactionplaceholdericon toolactionsfriend')).get_lang('Friends').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/groups.php">'.Display::return_icon('pixel.gif',get_lang('MyGroups'), array('class' => 'toolactionplaceholdericon toolactionsgroup')).get_lang('Groups').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/search.php">'.Display::return_icon('pixel.gif',get_lang('Search'), array('class' => 'toolactionplaceholdericon toolactionsearch')).get_lang('Search').'</a>';
echo '</div>';
// Start content
echo '<div id="content">';

echo '<div id="social-content">';
	echo '<div id="social-content-left">';
		//show the action menu
		SocialManager::show_social_menu('search');
                echo '</div>';
                echo '<div id="social-content-right">';
		$query = !empty($_GET['q'])?Security::remove_XSS($_GET['q']):'%';
                    echo '<div >';
                    api_display_tool_title(get_lang('Search'));
                    echo UserManager::get_search_form($_GET['q']);
                    echo '</div>';

		//I'm searching something
		if ($query != '') {
			if (isset($query) && $query!='') {
				//get users from tags
				$users = UserManager::get_all_user_tags($query);
				$groups = GroupPortalManager::get_all_group_tags($query);

				if (empty($users) && empty($groups)) {
                                    echo '<br/><br/><br/><br/>';
					echo get_lang('SorryNoResults');
				}

				$results = array();
				if (is_array($users) && count($users)> 0) {
					foreach($users as $user) {
                                            if(!SocialManager::is_friend(api_get_user_id(), $user['user_id'])){
						$picture = UserManager::get_picture_user($user['user_id'], $user['picture_uri'],80);
						$url_open = '<a href="'.api_get_path(WEB_PATH).'main/social/profile.php?u='.$user['user_id'].'">';
						$url_close ='</a>';
						$img = $url_open.'<img src="'.$picture['file'].'" />'.$url_close;
						$user['firstname'] = $url_open.$user['firstname'].$url_close;
						$user['lastname'] = $url_open.$user['lastname'].$url_close;
                                                // Link should be removed
						$link = '<a id="friendInvitation" href="'.api_get_path(WEB_PATH).'main/messages/send_message_to_userfriend.inc.php?view_panel=2&user_friend=260&user_friend=610&user_friend='.$user['user_id'].'" class="thickbox '.get_lang("CloseXorEscKey").'" title="'.get_lang('SendInvitation').'">'.Display :: return_icon('pixel.gif', get_lang('SocialInvitationToFriends'),array('class'=>'actionplaceholdericon actioninvitejoinfriends')).'&nbsp;'.get_lang('SendInvitation').'</a>';
						$results[] = array($img, $user['firstname'],$user['lastname'], $user['tag'], $link);
                                            }
					}
					echo '<div class="social-box-container2 quiz_content_actions">';
					echo '<div id="div_content_table" class="social-box-content2 scroll1">';
						Display::display_sortable_grid('search_user', array(), $results, array('hide_navigation'=>true, 'per_page' => 5), $query_vars, false ,true);
					echo '</div>';
					echo '</div>';
				}



				//get users from tags
				if (is_array($results) && count($results) > 0) {
					foreach ($results as $result) {
						$id = $result['id'];
						$url_open  = '<a href="groups.php?id='.$id.'">';
						$url_close = '</a>';

						$name = api_strtoupper(cut($result['name'],25,true));
						if ($result['relation_type'] == GROUP_USER_PERMISSION_ADMIN) {
							$name .= Display::return_icon('admin_star.png', get_lang('Admin'), array('style'=>'vertical-align:middle'));
						} elseif ($result['relation_type'] == GROUP_USER_PERMISSION_MODERATOR) {
							$name .= Display::return_icon('moderator_star.png', get_lang('Moderator'), array('style'=>'vertical-align:middle'));
						}
						$count_users_group = count(GroupPortalManager::get_all_users_by_group($id));
						if ($count_users_group == 1 ) {
							$count_users_group = $count_users_group.' '.get_lang('Member');
						} else {
							$count_users_group = $count_users_group.' '.get_lang('Members');
						}

						$picture = GroupPortalManager::get_picture_group($result['id'], $result['picture_uri'],80);
						$result['picture_uri'] = '<img class="social-groups-image" src="'.$picture['file'].'" hspace="4" height="50" border="2" align="left" width="50" />';
						$grid_item_1 = '';
						$item_1 = '<div>'.$url_open.$result['picture_uri'].'<p class="social-groups-text1"><strong>'.$name.'<br />('.$count_users_group.')</strong></p>'.$url_close.'</div>';

						if ($result['description'] != '') {
							$item_2 = '<div class="box_description_group_title" ><span class="social-groups-text2">'.get_lang('Description').'</span></div>';
							$item_3 = '<div class="box_description_group_content" >'.cut($result['description'],100,true).'</div>';
						} else {
							$item_2 = '<div class="box_description_group_title" ><span class="social-groups-text2"></span></div>';
							$item_3 = '<div class="box_description_group_content" ></div>';
						}
						$item_4 = '<div class="box_description_group_actions" style="margin-bottom:10px !important">'.$url_open.get_lang('SeeMore').$url_close.'</div>';
						$grid_item_2 = $item_1.$item_2.$item_3.$item_4;
						$grid_my_groups[]= array($grid_item_1,$grid_item_2);
					}
				}

				$grid_groups = array();
				if (is_array($groups) && count($groups)>0) {
					echo '<h2>'.get_lang('Groups').'</h2>';
					foreach($groups as $group) {

						$id = $group['id'];
						$url_open  = '<a href="groups.php?id='.$id.'">';
                                                $url_open2  = '<a class="link-buttons" href="groups.php?id='.$id.'">';
						$url_close = '</a>';

						$name = api_strtoupper(cut($group['name'],22,true));
						$count_users_group = count(GroupPortalManager::get_all_users_by_group($id));
						if ($count_users_group == 1 ) {
							$count_users_group = $count_users_group.' '.get_lang('Member');
						} else {
							$count_users_group = $count_users_group.' '.get_lang('Members');
						}
						$picture = GroupPortalManager::get_picture_group($group['id'], $group['picture_uri'],80);
						$tags = GroupPortalManager::get_group_tags($group['id']);
						$group['picture_uri'] = '<img class="social-groups-image" src="'.$picture['file'].'" hspace="4" height="50" border="2" align="left" width="50" />';
						$grid_item_1 = '';
						$item_1 = '<div class="title-group" style="margin-bottom:10px !important;"><p class="social-groups-text1"> <strong style="text-transform:uppercase;">'.$name.' ('.$count_users_group.')</strong></p>'.$url_close.'</div><div style="float:left; margin-left:5px"> '.$url_open.$group['picture_uri'].'</div>';
						if ($group['description'] != '') {
							$item_2 = '<div class="box_description_group_title" ><span class="social-groups-text2">'.get_lang('Description').'</span></div>';
							$item_3 = '<div class="box_description_group_content" >'.cut($group['description'],100,true).'</div>';
						} else {
							$item_2 = '<div class="box_description_group_title" ><span class="social-groups-text2"></span></div>';
							$item_3 = '<div class="box_description_group_content" ></div>';
						}
						$item_4 = '<div class="box_description_group_tags" >'.$tags.'</div>';
						$item_5 = '<div class="box_description_group_actions" style="margin-bottom:10px !important">'.$url_open2.get_lang('SeeMore').$url_close.'</div>';
						$grid_item_2 = $item_1.$item_2.$item_3.$item_4.$item_5;
						$grid_groups[]= array($grid_item_1,$grid_item_2);

					}
				}
				Display::display_sortable_grid('search_group', array(), $grid_groups, array('hide_navigation'=>true, 'per_page' => 5), $query_vars,  false, array(true,true,true,true,true));
			}
		} else {
			//we should show something
		}

	echo '</div>';
echo '</div>';

// End content
echo '</div>';

//echo '<div class="actions">';
//echo '</div>';
Display :: display_footer();

echo '<div id="social_dialog" style="display:none;">';
echo '<center></center>';
echo '</div>';

?>