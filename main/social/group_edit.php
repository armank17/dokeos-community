<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * @package dokeos.social
 * @author Julio Montoya <gugli100@gmail.com>
 */

// Language files that should be included
$language_file = array('userInfo', 'group', 'admin');
$cidReset = true;
include '../inc/global.inc.php';
$this_section = SECTION_SOCIAL;

$libpath = api_get_path(LIBRARY_PATH);
require_once $libpath.'fileManage.lib.php';
require_once $libpath.'fileUpload.lib.php';
require_once $libpath.'group_portal_manager.lib.php';
require_once $libpath.'formvalidator/FormValidator.class.php';
require_once $libpath.'image.lib.php';
require_once $libpath.'mail.lib.inc.php';
require_once $libpath.'social.lib.php';
require_once $libpath.'group_portal_manager.lib.php';

// delete group
if (isset($_GET['action']) && $_GET['action'] == 'deletegroup') {
    $gid = intval($_GET['id']);
    if (api_is_platform_admin()) {
        if (GroupPortalManager :: delete($gid)) {
            header('Location: '.api_get_path(WEB_CODE_PATH).'social/groups.php?deleted=1');
            exit;
        }
    }
}

//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.js" type="text/javascript" language="javascript"></script>'; //jQuery
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="projection, screen" />';

$htmlHeadXtra[] = '<script type="text/javascript">
var textarea = "";
var num_characters_permited = 255;
function textarea_maxlength(){
   num_characters = document.forms[0].description.value.length;
  if (num_characters > num_characters_permited){
      document.forms[0].description.value = textarea;
   }else{
      textarea = document.forms[0].description.value;
   }
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
$htmlHeadXtra[] = '
	<style type="text/css">
	div.row div.label{
		width: 10%;
	}
	div.row div.formw{
		width: 85%;
	}

	</style>
	';
$group_id = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);
$tool_name = get_lang('GroupEdit');

$interbreadcrumb[] = array('url' => 'home.php','name' => get_lang('Social'));
$interbreadcrumb[] = array('url' => 'groups.php','name' => get_lang('Groups'));

$table_group = Database::get_main_table(TABLE_MAIN_GROUP);

$sql = "SELECT * FROM $table_group WHERE id = '".$group_id."'";
$res = Database::query($sql);
if (Database::num_rows($res) != 1) {
	header('Location: groups.php?id='.$group_id);
	exit;
}

//only group admins can edit the group
if (!GroupPortalManager::is_group_admin($group_id)) {
	api_not_allowed();
}

$group_data = Database::fetch_array($res, 'ASSOC');

// Create the form
$form = new FormValidator('group_edit', 'post', '', '', array('style' => 'width: 100%; float: '.($text_dir == 'rtl' ? 'right;' : 'left;')));
$form->addElement('hidden', 'id', $group_id);

// name
$form->addElement('text', 'name', get_lang('Name'), array('size'=>60, 'maxlength'=>120));
//$form->applyFilter('name', 'html_filter');
//$form->applyFilter('name', 'trim');
//$form->addRule('name', get_lang('ThisFieldIsRequired'), 'required');

// Description
$form->addElement('textarea', 'description', get_lang('Description'), array('rows'=>3, 'cols'=>58, onKeyDown => "textarea_maxlength()", onKeyUp => "textarea_maxlength()"));
//$form->applyFilter('description', 'html_filter');
//$form->applyFilter('description', 'trim');
//$form->addRule('name', '', 'maxlength',255);

// url
$form->addElement('text', 'url', get_lang('URL'), array('size'=>35));
//$form->applyFilter('url', 'html_filter');
//$form->applyFilter('url', 'trim');

// Picture
$form->addElement('file', 'picture', get_lang('AddPicture'));
$allowed_picture_types = array ('jpg', 'jpeg', 'png', 'gif');
$form->addRule('picture', get_lang('OnlyImagesAllowed').' ('.implode(',', $allowed_picture_types).')', 'filetype', $allowed_picture_types);
if (strlen($group_data['picture_uri']) > 0) {
	$form->addElement('checkbox', 'delete_picture', '', get_lang('DelImage'));
}

// Status
$status = array();
$status[GROUP_PERMISSION_OPEN] 		= get_lang('Open');
$status[GROUP_PERMISSION_CLOSED]	= get_lang('Closed');
$form->addElement('select', 'visibility', get_lang('GroupPermissions'), $status, array());


// Submit button
$form->addElement('style_submit_button', 'submit', get_lang('ModifyInformation'), 'class="save"');

// Set default values
$form->setDefaults($group_data);

// Validate form
if ( $form->validate()) {
	$group = $form->exportValues();
	$picture_element = & $form->getElement('picture');
	$picture = $picture_element->getValue();
	$picture_uri = $group_data['picture_uri'];

	if ($group['delete_picture']) {
            $picture_uri = GroupPortalManager::delete_group_picture($group_id);
	}
	elseif (!empty($picture['name'])) {
            $picture_uri = GroupPortalManager::update_group_picture($group_id, $_FILES['picture']['name'], $_FILES['picture']['tmp_name']);
	}

	$name 		= $group['name'];
	$description	= $group['description'];
	$url 		= $group['url'];
	$status 	= intval($group['visibility']);

	GroupPortalManager::update($group_id, $name, $description, $url, $status, $picture_uri);
	$tok = Security::get_token();
	header('Location: groups.php?id='.$group_id.'&amp;action=show_message&message='.urlencode(get_lang('GroupUpdated')).'&sec_token='.$tok);
	exit();
}

Display::display_header($tool_name);

// Display actions
echo '<div class="actions">';
echo '<a href="'.api_get_path(WEB_PATH).'main/social/home.php">'.Display::return_icon('pixel.gif',get_lang('Home'),array('class' => 'toolactionplaceholdericon toolactionshome')).get_lang('Home').'</a>';
// Only admins and teachers can create groups
if (api_is_allowed_to_edit(null,true)) {
    echo '<a href="'.api_get_path(WEB_PATH).'main/social/group_add.php">'.Display::return_icon('pixel.gif',get_lang('CreateAgroup'),array('class' => 'toolactionplaceholdericon toolactionsgroup')).get_lang('CreateAgroup').'</a>';
}
echo '<a href="'.api_get_path(WEB_PATH).'main/social/groups.php">'.Display::return_icon('pixel.gif',get_lang('MyGroups'),array('class' => 'toolactionplaceholdericon toolactiongroupimage')).get_lang('MyGroups').'</a>';
if (isset($_GET['id']) && $_GET['id'] >= 0) {
  $group_id = Security::remove_XSS($_GET['id']);
  $relation_group_title = get_lang('IamAnAdmin');
  $links .=  '<a href="groups.php?id='.$group_id.'">'.Display::return_icon('pixel.gif',get_lang('MessageList'),array('class' => 'toolactionplaceholdericon toolsocialmessagelist')).get_lang('MessageList').'</a>';
  $links .=  '<a href="group_edit.php?id='.$group_id.'">'.Display::return_icon('pixel.gif',get_lang('EditGroup'),array('class' => 'toolactionplaceholdericon tooledithome')).get_lang('EditGroup').'</a>';

  if (api_is_platform_admin()) {
      $links .=  '<a href="group_edit.php?action=deletegroup&amp;id='.$group_id.'" onclick="javascript:if(!confirm(\''.addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset)).'\')) return false;" >'.Display::return_icon('pixel.gif',get_lang('DeleteGroup'),array('class' => 'toolactionplaceholdericon tooldeletegroup')).get_lang('Delete').'</a>';
  }

  echo $links;
}
echo '</div>';
// Start content
echo '<div id="content">';

// Group picture
$image_path = GroupPortalManager::get_group_picture_path_by_id($group_id,'web');
$image_dir = $image_path['dir'];
$image = $image_path['file'];
$image_file = ($image != '' ? $image_dir.$image : api_get_path(WEB_CODE_PATH).'img/unknown_group.jpg');
$image_size = api_getimagesize($image_file);

$img_attributes = 'src="'.$image_file.'?rand='.time().'" '
	.'alt="'.api_get_person_name($user_data['firstname'], $user_data['lastname']).'" '
	.'style="float:'.($text_dir == 'rtl' ? 'left' : 'right').'; padding:5px;" ';

if ($image_size[0] > 300) { //limit display width to 300px
	$img_attributes .= 'width="300" ';
}

// get the path,width and height from original picture
$big_image = $image_dir.'big_'.$image;
$big_image_size = api_getimagesize($big_image);
$big_image_width = $big_image_size[0];
$big_image_height = $big_image_size[1];
$url_big_image = $big_image.'?rnd='.time();
/*
if ($image == '') {
	echo '<img '.$img_attributes.' />';
} else {
	echo '<input type="image" '.$img_attributes.' onclick="javascript: return show_image(\''.$url_big_image.'\',\''.$big_image_width.'\',\''.$big_image_height.'\');"/>';
}
*/
//Shows left column
//echo GroupPortalManager::show_group_column_information($group_id, api_get_user_id());

echo '<div id="social-content">';
	echo '<div id="social-content-left">';
	//this include the social menu div
	SocialManager::show_social_menu('group_edit',$group_id);
	echo '</div>';
	echo '<div id="social-content-right">';
		// Display form
		$form->display();
	echo '</div>';
echo '</div>';

// End content
echo '</div>';

// Actions
echo '<div class="actions">';
if (isset($_GET['id']) && $_GET['id'] >= 0) {
  $group_id = Security::remove_XSS($_GET['id']);
  $links =  '<a href="group_members.php?id='.$group_id.'">'.		Display::return_icon('pixel.gif', get_lang('MemberList'), array('class' => 'actionplaceholdericon actiongroupstudentview')).get_lang('MemberList').'</a>';
  $links .=  '<a href="group_waiting_list.php?id='.$group_id.'">'.	Display::return_icon('pixel.gif', get_lang('WaitingList'), array('class' => 'actionplaceholdericon actionlatestchanges')).get_lang('WaitingList').'</a>';
  $links .=  '<a href="group_invitation.php?id='.$group_id.'">'.	Display::return_icon('pixel.gif', get_lang('InviteFriends'), array('class' => 'actionplaceholdericon actionadduser')).get_lang('InviteFriends').'</a>';
  echo $links;
}
echo '</div>';

// Footer
Display::display_footer();