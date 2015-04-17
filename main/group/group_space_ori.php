<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.group
==============================================================================
*/

// name of the language file that needs to be included
$language_file = 'group';

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'groupmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'sortabletable.class.php';
require_once api_get_path(SYS_CODE_PATH).'forum/forumfunction.inc.php';
require_once api_get_path(SYS_CODE_PATH).'forum/forumconfig.inc.php';

// the section (for the tabs)
$this_section = SECTION_COURSES;

// current group
$current_group = GroupManager :: get_group_properties($_SESSION['_gid']);

// tracking
event_access_tool(TOOL_GROUP);

$nameTools = get_lang('GroupSpace');

// breadcrumbs
$interbreadcrumb[] = array ("url" => "group.php", "name" => get_lang("Groups"));

// getting all the forums of the groups
$forums_of_groups = get_forums_of_group($current_group['id']);

$forum_state_public=0;
if (is_array($forums_of_groups)) {
	foreach ($forums_of_groups as $key => $value) {
		if($value['forum_group_public_private'] == 'public') {
			$forum_state_public=1;
		}
	}
}

if ($current_group['doc_state']!=1 and $current_group['calendar_state']!=1 and $current_group['work_state']!=1 and $current_group['announcements_state']!=1 and $current_group['wiki_state']!=1 and $current_group['chat_state']!=1 and $forum_state_public!=1) {
	if (!api_is_allowed_to_edit(null,true) and !GroupManager :: is_user_in_group($_user['user_id'], $current_group['id'])) {
		echo api_not_allowed();
	}
}

// display the header
Display::display_header($nameTools.' '.$current_group['name'],'Group');

// display tool introduction
Display::display_introduction_section(group_space_.$_SESSION['_gid']);

// Action handling
// user registration
if (!empty($_GET['selfReg']) && GroupManager :: is_self_registration_allowed($_SESSION['_user']['user_id'], $current_group['id'])) {
	GroupManager :: subscribe_users($_SESSION['_user']['user_id'], $current_group['id']);
	Display :: display_normal_message(get_lang('GroupNowMember'));
}
// user unregistration
if (!empty($_GET['selfUnReg']) && GroupManager :: is_self_unregistration_allowed($_SESSION['_user']['user_id'], $current_group['id'])) {
	GroupManager :: unsubscribe_users($_SESSION['_user']['user_id'], $current_group['id']);
	Display::display_normal_message(get_lang('StudentDeletesHimself'));
}

// Actions
echo '<div class="actions">';
echo '<a href="group.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('BackToGroupList'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackToGroupList').'</a>';
/*
 * Edit the group
 */
if (api_is_allowed_to_edit(false,true) or GroupManager :: is_tutor($_user['user_id'])) {
	isset($origin)?$my_origin = $origin:$my_origin='';
	echo Display::return_icon('edit.png', get_lang("EditGroup"))."<a href=\"group_edit.php?".api_get_cidreq()."&amp;origin=$my_origin\">".get_lang("EditGroup")."</a>";
}

/*
 * Register to group
 */
if (GroupManager :: is_self_registration_allowed($_SESSION['_user']['user_id'], $current_group['id'])) {
	echo '<a href="'.api_get_self().'?selfReg=1&amp;group_id='.$current_group['id'].'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;">'.Display::return_icon('groupadd.gif').get_lang("RegIntoGroup").'</a>';
}

/*
 * Unregister from group
 */
if (GroupManager :: is_self_unregistration_allowed($_SESSION['_user']['user_id'], $current_group['id'])) {
	echo '<a href="'.api_get_self().'?selfUnReg=1" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;">'.Display::return_icon('group_delete.png').get_lang("StudentUnsubscribe").'</a>';
}
echo '&nbsp;</div>';

if( isset($_GET['action'])) {
	switch( $_GET['action']) {
		case 'show_msg':
			Display::display_normal_message(Security::remove_XSS($_GET['msg']));
			break;
	}
}

//start the content div
echo '<div id="content">';

/*
-----------------------------------------------------------
	Main Display Area
-----------------------------------------------------------
*/
$course_code = $_course['sysCode'];
$is_course_member = CourseManager :: is_user_subscribed_in_real_or_linked_course($_SESSION['_user']['user_id'], $course_code);

/*
 * Group title and comment
 */
//api_display_tool_title($nameTools.' '.stripslashes($current_group['name']));

if (!empty($current_group['description'])) {
	echo '<blockquote>'.stripslashes($current_group['description']).'</blockquote>';
}


/**
 * This function displays all the current tools of the group
 */
function display_group_tools($current_group){
	if (api_is_allowed_to_edit(false,true) OR GroupManager :: is_user_in_group($_SESSION['_user']['user_id'], $current_group['id'])) {
		display_group_forum($current_group);
		display_group_documents($current_group);
		display_group_calendar($current_group);
		display_group_assignments($current_group);
		display_group_announcements($current_group);
		display_group_wiki($current_group);
		display_group_chat($current_group);
	}
}

function display_group_forum($current_group){
	$forums_of_groups = get_forums_of_group($current_group['id']);
	if (is_array($forums_of_groups)) {

		if ( $current_group['forum_state'] != TOOL_NOT_AVAILABLE ) {
			foreach ($forums_of_groups as $key => $value) {
					if ($value['forum_group_public_private'] == 'public' || (/*!empty($user_subscribe_to_current_group) && */ $value['forum_group_public_private'] == 'private') || !empty($user_is_tutor) || api_is_allowed_to_edit(false,true)) {
						echo '<a href="../forum/viewforum.php?forum='.$value['forum_id'].'&amp;gidReq='.Security::remove_XSS($current_group['id']).'">'.Display::return_icon('forum.gif', get_lang("GroupForum"),array('align'=>'middle')).' '.get_lang("Forum").': '.$value['forum_title'].'</a>&nbsp;&nbsp;';
					}
			}
		}
	}
}

function display_group_documents($current_group){
	if( $current_group['doc_state'] != TOOL_NOT_AVAILABLE )	{
		echo '<a href="../document/document.php?'.api_get_cidreq().'&amp;gidReq='.$current_group['id'].'">'.Display::return_icon('folder_document.gif', get_lang('GroupDocument'),array('align'=>'middle')).'&nbsp;'.get_lang('GroupDocument').'</a>&nbsp;&nbsp;';
	}
}

function display_group_calendar($current_group){
	if ( $current_group['calendar_state'] != TOOL_NOT_AVAILABLE) {
		echo '<a href="../calendar/agenda.php?'.api_get_cidreq().'&amp;toolgroup='.$current_group['id'].'&amp;group='.$current_group['id'].'&amp;acces=0">'.Display::return_icon('agenda.png', get_lang('GroupCalendar'),array('align'=>'middle')).'&nbsp;'.get_lang('GroupCalendar').'</a>&nbsp;&nbsp;';
	}

}

function display_group_assignments($current_group) {
	if ( $current_group['work_state'] != TOOL_NOT_AVAILABLE) {
		echo '<a href="'.  api_get_path(WEB_VIEW_PATH).'work/index.php?'.api_get_cidreq().'&amp;toolgroup='.$current_group['id'].'">'.Display::return_icon('works.gif', get_lang('GroupWork'),array('align'=>'middle')).'&nbsp;'.get_lang('GroupWork').'</a>&nbsp;&nbsp;';
	}
}

function display_group_announcements($current_group) {
	if ( $current_group['announcements_state'] != TOOL_NOT_AVAILABLE) {
		echo  '<a href="../announcements/announcements.php?'.api_get_cidreq().'&amp;toolgroup='.$current_group['id'].'">'.Display::return_icon('valves.png', get_lang('GroupAnnouncements'),array('align'=>'middle')).'&nbsp;'.get_lang('GroupAnnouncements').'</a>&nbsp;&nbsp;';
	}
}

function display_group_chat($current_group) {
	if ( $current_group['chat_state'] != TOOL_NOT_AVAILABLE)
	{
		if(api_get_course_setting('allow_open_chat_window')==true) {
			echo '<a href="javascript: void(0);" onclick="window.open(\'../chat/chat.php?'.api_get_cidreq().'&toolgroup='.$current_group['id'].'\',\'window_chat_group_'.$_SESSION['_cid'].'_'.$_SESSION['_gid'].'\',\'height=500, width=930, left=2, top=2, toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, directories=no, status=no\')">'.Display::return_icon('chat.gif', get_lang('Chat'),array('align'=>'middle')).'&nbsp;'.get_lang('Chat').'</a>&nbsp;&nbsp;';
		}
		else
		{
			echo '<a href="../chat/chat.php?'.api_get_cidreq().'&amp;toolgroup='.$current_group['id'].'">'.Display::return_icon('chat.gif', get_lang('Chat'),array('align'=>'middle')).'&nbsp;'.get_lang('Chat').'</a>&nbsp;&nbsp;';
		}
	}
}

function display_group_wiki($current_group) {
	if ( $current_group['wiki_state'] != TOOL_NOT_AVAILABLE) {
		echo '<a href="../wiki/index.php?'.api_get_cidreq()."&amp;toolgroup=".$current_group['id']."\">".Display::return_icon('wiki.gif', get_lang("GroupWiki"),array('align'=>'middle'))."&nbsp;".get_lang("GroupWiki")."</a>&nbsp;&nbsp;";
	}
}

/*
 * Group Tools
 */
// If the user is subscribed to the group or the user is a tutor of the group then
if (api_is_allowed_to_edit(false,true) OR GroupManager :: is_user_in_group($_SESSION['_user']['user_id'], $current_group['id'])) {
	$tools = '';


	echo '<div class="actions-message" style="margin-bottom:4px;"><b>'.get_lang("Tools").':</b></div>';
	echo '<div style="margin-left:5px;">';
    display_group_tools($current_group);
    echo '</div>';

} else {
	$tools = '';
	// link to the forum of this group
	$forums_of_groups = get_forums_of_group($current_group['id']);
	if (is_array($forums_of_groups)) {
		if ( $current_group['forum_state'] == TOOL_PUBLIC ) {
			foreach ($forums_of_groups as $key => $value) {
				if ($value['forum_group_public_private'] == 'public' ) {

					$tools.= Display::return_icon('forum.gif', get_lang("GroupForum"),array('align'=>'middle')) . ' <a href="../forum/viewforum.php?forum='.$value['forum_id'].'&amp;gidReq='.Security::remove_XSS($current_group['id']).'">'.$value['forum_title'].'</a>&nbsp;&nbsp;';
					//$tools.= Display::return_icon('forum.gif', get_lang("Forum")) . ' <a href="../forum/viewforum.php?forum='.$value['forum_id'].'">'.get_lang("Forum").': '.$value['forum_title'].'</a><br />';

				}
			}
		}
	}
	if( $current_group['doc_state'] == TOOL_PUBLIC )
	{
		// link to the documents area of this group
		$tools .= "<a href=\"../document/document.php?".api_get_cidreq()."&amp;gidReq=".$current_group['id']."&amp;origin=$origin\">".Display::return_icon('folder_document.gif', get_lang("GroupDocument"),array('align'=>'middle'))."&nbsp;".get_lang("GroupDocument")."</a>&nbsp;&nbsp;";
	}
	if ( $current_group['calendar_state'] == TOOL_PUBLIC )
	{
		//link to a group-specific part of agenda
		$tools .= "<a href=\"../calendar/agenda.php?".api_get_cidreq()."&amp;toolgroup=".$current_group['id']."&amp;group=".$current_group['id']."\">".Display::return_icon('agenda.png', get_lang("GroupCalendar"),array('align'=>'middle'))."&nbsp;".get_lang("GroupCalendar")."</a>&nbsp;&nbsp;";
	}
	if ( $current_group['work_state'] == TOOL_PUBLIC )
	{
		//link to the works area of this group
		$tools .= "<a href=".api_get_path(WEB_VIEW_PATH).'work/index.php?'.api_get_cidreq()."&amp;toolgroup=".$current_group['id']."\">".Display::return_icon('works.gif', get_lang("GroupWork"),array('align'=>'middle'))."&nbsp;".get_lang("GroupWork")."</a>&nbsp;&nbsp;";
	}
	if ( $current_group['announcements_state'] == TOOL_PUBLIC)
	{
		//link to a group-specific part of announcements
		$tools .= "<a href=\"../announcements/announcements.php?".api_get_cidreq()."&amp;toolgroup=".$current_group['id']."&amp;group=".$current_group['id']."\">".Display::return_icon('valves.png', get_lang("GroupAnnouncements"),array('align'=>'middle'))."&nbsp;".get_lang("GroupAnnouncements")."</a>&nbsp;&nbsp;";
	}

	if ( $current_group['wiki_state'] == TOOL_PUBLIC )
	{
		//link to the wiki area of this group
		$tools .= "<a href=\"../wiki/index.php?".api_get_cidreq()."&amp;toolgroup=".$current_group['id']."\">".Display::return_icon('wiki.gif', get_lang('GroupWiki'),array('align'=>'middle'))."&nbsp;".get_lang('GroupWiki')."</a>&nbsp;&nbsp;";
	}

	if ( $current_group['chat_state'] == TOOL_PUBLIC )
	{
		//link to the chat area of this group
		if(api_get_course_setting('allow_open_chat_window')==true)
		{
			$tools .= "&nbsp;<a href=\"javascript: void(0);\" onclick=\"window.open('../chat/chat.php?".api_get_cidreq()."&toolgroup=".$current_group['id']."','window_chat_group_".$_SESSION['_cid']."_".$_SESSION['_gid']."','height=380, width=625, left=2, top=2, toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, directories=no, status=no') \" >".Display::return_icon('chat.gif', get_lang("Chat"),array('align'=>'middle'))."&nbsp;".get_lang("Chat")."</a>&nbsp;&nbsp;";
		}
		else
		{
			$tools .= "&nbsp;<a href=\"../chat/chat.php?".api_get_cidreq()."&amp;toolgroup=".$current_group['id']."\">".Display::return_icon('chat.gif', get_lang("Chat"),array('align'=>'middle'))."&nbsp;".get_lang("Chat")."</a>&nbsp;&nbsp;";
		}
	}

	echo '<br/>';

	echo '<div class="actions-message" style="margin-bottom:4px;"><b>'.get_lang("Tools").':</b></div>';
	if (!empty($tools)) {
		echo '<div style="margin-left:5px;">'.$tools.'</div>';
	}
}

/*
 * list all the tutors of the current group
 */
$tutors = GroupManager::get_subscribed_tutors($current_group['id']);

$tutor_info = '';
if (count($tutors) == 0)
{
	$tutor_info = get_lang("GroupNoneMasc");
}
else
{
	isset($origin)?$my_origin = $origin:$my_origin='';
	foreach($tutors as $index => $tutor)
	{
		$image_path = UserManager::get_user_picture_path_by_id($tutor['user_id'],'web',false, true);
		$image_repository = $image_path['dir'];
		$existing_image = $image_path['file'];
		$photo= '<img src="'.$image_repository.$existing_image.'" align="absbottom" alt="'.api_get_person_name($tutor['firstname'], $tutor['lastname']).'"  width="32" height="32" title="'.api_get_person_name($tutor['firstname'], $tutor['lastname']).'" />';
		$tutor_info .= "<div style='margin-bottom: 5px;'><a href='../user/userInfo.php?origin=".$my_origin."&uInfo=".$tutor['user_id']."'>".$photo."&nbsp;".api_get_person_name($tutor['firstname'], $tutor['lastname'])."</a></div>";
	}
}
// Group tutor always is empty due that this feature was removed by this changeset : 19453fe512c3 , see group_edit.php file

/*echo '<div class="actions-message" style="margin-bottom:4px;"><b>'.get_lang("GroupTutors").':</b></div>';
if (!empty($tutor_info)) {
	echo '<div style="margin-left:5px;">'.$tutor_info.'</div>';
}*/
//echo '<br/>';

/*
 * list all the members of the current group
 */
echo '<b>'.get_lang("GroupMembers").':</b>';

$table = new SortableTable('group_users', 'get_number_of_group_users', 'get_group_user_data', (api_is_western_name_order() xor api_sort_by_first_name()) ? 2 : 1);
$my_cidreq=isset($_GET['cidReq']) ? Security::remove_XSS($_GET['cidReq']) : '';
$my_origin=isset($_GET['origin']) ? Security::remove_XSS($_GET['origin']) : '';
$my_gidreq=isset($_GET['gidReq']) ? Security::remove_XSS($_GET['gidReq']) : '';
$parameters = array('cidReq' => $my_cidreq, 'origin'=> $my_origin, 'gidReq' => $my_gidreq);
$table->set_additional_parameters($parameters);
$table->set_header(0, '');
if (api_is_western_name_order()) {
	$table->set_header(1, get_lang('FirstName'));
	$table->set_header(2, get_lang('LastName'));
} else {
	$table->set_header(1, get_lang('LastName'));
	$table->set_header(2, get_lang('FirstName'));
}

if (api_get_setting("show_email_addresses") == "true")
{
	$table->set_header(3, get_lang('Email'));
	$table->set_column_filter(3, 'email_filter');
}
else
{
	if (api_is_allowed_to_edit()=="true")
	{
		$table->set_header(3, get_lang('Email'));
		$table->set_column_filter(3, 'email_filter');
	}
}
$table->set_column_filter(0, 'user_icon_filter');
$table->display();

// close the content div
echo '</div>';

// Actions bar
//echo '<div class="actions">';
//echo '</div>';
// footer
Display::display_footer();

/**
 * Get the number of subscribed users to the group
 *
 * @return integer
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version April 2008
 */
function get_number_of_group_users()
{
	global $current_group;

	// Database table definition
	$table_group_user = Database :: get_course_table(TABLE_GROUP_USER);

	// query
	$sql = "SELECT count(id) AS number_of_users
				FROM ".$table_group_user."
				WHERE group_id='".Database::escape_string($current_group['id'])."'";
	$result = Database::query($sql,__FILE__,__LINE__);
	$return = Database::fetch_array($result,'ASSOC');
	return $return['number_of_users'];
}

/**
 * Get the details of the users in a group
 *
 * @param integer $from starting row
 * @param integer $number_of_items number of items to be displayed
 * @param integer $column sorting colum
 * @param integer $direction sorting direction
 * @return array
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version April 2008
 */
function get_group_user_data($from, $number_of_items, $column, $direction)
{
	global $current_group;

	// Database table definition
	$table_group_user 	= Database :: get_course_table(TABLE_GROUP_USER);
	$table_user 		= Database :: get_main_table(TABLE_MAIN_USER);

	// query

	if (api_get_setting("show_email_addresses") == "true") {

		$sql = "SELECT
					user.user_id 	AS col0,
				".(api_is_western_name_order() ?
				"user.firstname 	AS col1,
				user.lastname 	AS col2,"
				:
				"user.lastname 	AS col1,
				user.firstname 	AS col2,"
				)."
					user.email		AS col3
					FROM ".$table_user." user, ".$table_group_user." group_rel_user
					WHERE group_rel_user.user_id = user.user_id
					AND group_rel_user.group_id = '".Database::escape_string($current_group['id'])."'";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
	}
	else
	{
		if (api_is_allowed_to_edit()=="true")
		{
			$sql = "SELECT
						user.user_id 	AS col0,
						".(api_is_western_name_order() ?
						"user.firstname 	AS col1,
						user.lastname 	AS col2,"
						:
						"user.lastname 	AS col1,
						user.firstname 	AS col2,"
						)."
						user.email		AS col3
						FROM ".$table_user." user, ".$table_group_user." group_rel_user
						WHERE group_rel_user.user_id = user.user_id
						AND group_rel_user.group_id = '".Database::escape_string($current_group['id'])."'";
			$sql .= " ORDER BY col$column $direction ";
			$sql .= " LIMIT $from,$number_of_items";
		}
		else
		{
			$sql = "SELECT
						user.user_id 	AS col0,
						". (api_is_western_name_order() ?
						"user.firstname 	AS col1,
						user.lastname 	AS col2 "
						:
						"user.lastname 	AS col1,
						user.firstname 	AS col2 "
						)."
						FROM ".$table_user." user, ".$table_group_user." group_rel_user
						WHERE group_rel_user.user_id = user.user_id
						AND group_rel_user.group_id = '".Database::escape_string($current_group['id'])."'";
			$sql .= " ORDER BY col$column $direction ";
			$sql .= " LIMIT $from,$number_of_items";
		}
	}

	$return = array ();
	$result = Database::query($sql,__FILE__,__LINE__);
	while ($row = Database::fetch_row($result))
	{
		$return[] = $row;
	}
	return $return;
}

/**
* Returns a mailto-link
* @param string $email An email-address
* @return string HTML-code with a mailto-link
*/
function email_filter($email)
{
	return Display :: encrypted_mailto_link($email, $email);
}

/**
 * Display a user icon that links to the user page
 *
 * @param integer $user_id the id of the user
 * @return html code
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version April 2008
 */
function user_icon_filter($user_id)
{
	global $origin;

	$userinfo=Database::get_user_info_from_id($user_id);
	$image_path = UserManager::get_user_picture_path_by_id($user_id,'web',false, true);
	$image_repository = $image_path['dir'];
	$existing_image = $image_path['file'];
	$photo= '<center><img src="'.$image_repository.$existing_image.'" alt="'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'"  width="22" height="22" title="'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'" /></center>';
	return "<a href='../user/userInfo.php?origin=".$origin."&uInfo=".$user_id."'>".$photo;
}
?>
