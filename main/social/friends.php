<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * @package dokeos.social
 * @author Julio Montoya <gugli100@gmail.com>
 */

$language_file = array('userInfo');
$cidReset=true;
require '../inc/global.inc.php';
require_once api_get_path(CONFIGURATION_PATH).'profile.conf.php';
require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH).'image.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'social.lib.php';

api_block_anonymous_users();

$this_section = SECTION_SOCIAL;
//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" type="text/javascript" language="javascript"></script>';
//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.js" type="text/javascript" language="javascript"></script>'; //jQuery
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="projection, screen">';
$htmlHeadXtra[] = '<script type="text/javascript">

function delete_friend (element_div) {
	id_image=$(element_div).attr("id");
	user_id=id_image.split("_");
       title = "' . get_lang('ConfirmationDialog') . '";
       text = "' . get_lang('ConfirmYourChoice') . '";
       $.confirm(text, title, function() {
			 $.ajax({
				contentType: "application/x-www-form-urlencoded",
				type: "POST",
				url: "'.api_get_path(WEB_AJAX_PATH).'social.ajax.php?a=delete_friend",
				data: "delete_friend_id="+user_id[1],
				success: function(datos) {
				 $("div#"+"div_"+user_id[1]).hide("slow");
				 $("div#"+"div_"+user_id[1]).html("");
				 clear_form ();
				}
			});
       });
        
}
			
		
function search_image_social(element_html)  {
	name_search=$(element_html).attr("value");
	 $.ajax({
		contentType: "application/x-www-form-urlencoded",
		type: "POST",
		url: "'.api_get_path(WEB_AJAX_PATH).'social.ajax.php?a=show_my_friends",
		data: "search_name_q="+name_search,
		success: function(datos) {
			$("section#friends-wp").html(datos);
		}
	});
}
		
function show_icon_delete(element_html) {
	elem_id=$(element_html).attr("id");
	id_elem=elem_id.split("_");
	ident="#img_"+id_elem[1];
	$(ident).attr("src","../img/delete.gif");
	$(ident).attr("alt","'.get_lang('Delete', '').'");
	$(ident).attr("title","'.get_lang('Delete', '').'");
}
		

function hide_icon_delete(element_html)  {
	elem_id=$(element_html).attr("id");
	id_elem=elem_id.split("_");
	ident="#img_"+id_elem[1];
	$(ident).attr("src","../img/blank.gif");
	$(ident).attr("alt","");
	$(ident).attr("title","");
}
		
function clear_form () {
	$("input[@type=radio]").attr("checked", false);
	$("div#div_qualify_image").html("");
	$("div#div_info_user").html("");
}
	
		
function show_icon_edit(element_html) {	
	ident="#edit_image";
	$(ident).show();
}		

function hide_icon_edit(element_html)  {
	ident="#edit_image";
	$(ident).hide();
}		
		
</script>';

$interbreadcrumb[]= array ('url' =>'profile.php','name' => get_lang('Social'));
$interbreadcrumb[]= array ('url' =>'#','name' => get_lang('Friends'));

Display :: display_header($tool_name, 'Groups');

// Display actions
echo '<div class="actions">';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/home.php">'.Display::return_icon('pixel.gif',get_lang('Home'), array('class' => 'toolactionplaceholdericon toolactionshome')).get_lang('Home').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/inbox.php?f=social">'.Display::return_icon('pixel.gif', get_lang('Messages'), array('class' => 'toolactionplaceholdericon toolactionsmessage')).get_lang('Messages').$count_unread_message.'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/invitations.php">'.Display::return_icon('pixel.gif',get_lang('Invitations'), array('class' => 'toolactionplaceholdericon toolactionsinvite')).get_lang('Invitations').$total_invitations.'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/profile.php">'.Display::return_icon('pixel.gif',get_lang('ViewMySharedProfile'), array('class' => 'toolactionplaceholdericon toolactionsprofile')).get_lang('ViewMySharedProfile').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/friends.php">'.Display::return_icon('pixel.gif',get_lang('Friends'), array('class' => 'toolactionplaceholdericon toolactionsfriend')).get_lang('Friends').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/groups.php">'.Display::return_icon('pixel.gif',get_lang('Groups'), array('class' => 'toolactionplaceholdericon toolactionsgroup')).get_lang('Groups').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/search.php">'.Display::return_icon('pixel.gif',get_lang('Search'), array('class' => 'toolactionplaceholdericon toolactionsearch')).get_lang('Search').'</a>';
echo '</div>';
// Start content
echo '<div id="content">';

echo '<div id="social-content">';

	/*echo '<div id="social-content-left">';
		//this include the social menu div
		SocialManager::show_social_menu('friends');	
	echo '</div>';*/
	echo '<div id="social-content-all">';
	
$language_variable	= api_xml_http_response_encode(get_lang('Contacts'));
$user_id	= api_get_user_id();

$list_path_friends	= array();
$user_id	= api_get_user_id();
$name_search= Security::remove_XSS($_POST['search_name_q']);
$number_friends = 0;

if (!empty($name_search) && $name_search!='undefined') {
	$friends = SocialManager::get_friends($user_id,null,$name_search);
} else {
	$friends = SocialManager::get_friends($user_id);
        
}

if (count($friends) == 0 ) {
	echo get_lang('NoFriendsInYourContactList').'<br /><br />';
	//echo '<a href="search.php">'.get_lang('TryAndFindSomeFriends').'</a>';	
        //echo '<a href="search.php"><button class="search">'.get_lang('TryAndFindSomeFriends').'</button></a>';
} else {
	
	?>
	<div align="center" >
	<table width="100%" border="0" cellpadding="0" cellspacing="0" align="left" >
	  <tr>
	    <td height="25" valign="left">
	    <table width="100%" border="0" cellpadding="0" cellspacing="0" >
	      <tr>
              <td width="100%"  align="left" class="social-align-box">
                <?php api_display_tool_title(get_lang('Search')); ?>
                  <input class="social-search-image" type="text" id="id_search_image" name="id_search_image" value="" onkeyup="search_image_socialx(this)" />
              </td>
	      </tr>
	    </table></td>
	  </tr>
	  <tr>
	    <td height="175" valign="top">
	    <table width="100%" border="0" cellpadding="0" cellspacing="0" >
	      <tr>
			<td height="153" valign="top">                                              
                            <div id="friends-wp"></div>
                            <div id="more_friend">
                                <?php echo '<div align="center" style="float: left;width: 100%">';?>
                                <img style="margin-top: 20px" src="<?php echo api_get_path(WEB_IMG_PATH).'/' ?>loadingAnimation.gif" id="loading-gif"/>
                                <?php echo '</div>'; ?>
                                <?php echo '<div align="center" style="float: left;width: 100%">';
                                echo '<button id="see_more" class="save" value="'.get_lang('MoreResults').'" type="button" >'.get_lang('MoreResults').'</button>';
                                //echo '<input id="see_more" type="button"  value="'.get_lang('MoreResults').'" class="button_more_friends" />';
                                echo '</div>'; ?>
                            </div>
			</td>
	        </tr>
	    </table>
            </td>
	  </tr>
	</table>
	</div>
	<?php						
	}	
                 echo  '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/see_more_friends.js" type="text/javascript" language="javascript"></script>';
            echo '</div>';
	echo '</div>';	

// End content
echo '</div>';

// Actions
//echo '<div class="actions">';
//echo '</div>';

Display :: display_footer();
?>
