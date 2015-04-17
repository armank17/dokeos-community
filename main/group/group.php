<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	Main page for the group module.
*	@package dokeos.group
==============================================================================
*/

// settings (temporarily added here)
$_setting['group_overview'] = 'true';
$_setting['group_export_csv'] = 'true';
$_setting['group_export_xls'] = 'true';

// name of the language file that needs to be included
$language_file = array('group','userInfo','work');

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
include_once (api_get_path(LIBRARY_PATH).'export.lib.inc.php');
require_once api_get_path(SYS_CODE_PATH).'forum/forumfunction.inc.php';
require_once api_get_path(SYS_CODE_PATH).'forum/forumconfig.inc.php';
       
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'export'){
	
	$data = export_csv_data();
	
	Export::export_table_csv($data);	
	break;
}

// the section (for the tabs)
$this_section = SECTION_COURSES;

// access control
api_protect_course_script(true);

// tracking
event_access_tool(TOOL_GROUP);
$htmlHeadXtra[] = ' <script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script>';        
$htmlHeadXtra[] = ' <link href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/dokeos/jquery-ui-1.8.6.custom.css" rel="stylesheet" type="text/css">';
$htmlHeadXtra[] = ' <script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/mselect/jquery.multiselect.js" type="text/javascript"></script>';
$htmlHeadXtra[] = ' <link href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/mselect/jquery.multiselect.css" rel="stylesheet" type=" text/css">';

        

     
       $htmlHeadXtra[] = '
<script>
function listUser(groupId,codeCourse){ 
     
     $(document).ready(function(){
       
           $.ajax({
           
            url:"ajax.php?action=setGroupId&groupId="+groupId+"&courseCode="+codeCourse,
            success: function(data){
                $("#userList").html(data);
                $("#cbo-participants").multiselect({
                                                minWidth:            380,
                                                selectedList:        0,
                                                checkAllText:        "'.get_lang('CheckAll').'",
                                                uncheckAllText:      "'.get_lang('UncheckAll').'",
                                                noneSelectedText:    "'.get_lang('chooseUsers').'",					
                                                selectedText:        "# of # selected",
                                                autoOpen:            false
                });  
                 $("#userList").dialog({
                  width: 500,
                  height : 300,
                  resizable: false,
                  title: "'.  strtoupper(get_lang('AddUsersToTheGroup')).'"
                 });

            }
           });
           
     
           
      $("#groupId").val(groupId);

     
     });
              
}
    
</script>';
// display the header
Display::display_tool_header(get_lang('Groups'));
$usersArray = array();       
        
// name of the tool
$nameTools = get_lang('Groups');
$course=api_get_course_info();
$courseCode =$course['official_code'];

?>
<div id="userList" style ="display:none;"></div>
  <?php  
  
  
// Tool introduction
Display::display_introduction_section(TOOL_USER, 'left');

// unsetting some local $_SESSION variables
unset($_SESSION['groupmembers'.api_get_course_id()]);
unset($_SESSION['groupmembership'.api_get_course_id()]);
unset($_SESSION['grouplist'.api_get_course_id()]);

// display the tool title
//api_display_tool_title(get_lang('Groups'));
        
// action links
display_actions();
if (isset($_SESSION['display_confirmation_message'])) {
    echo Display::display_confirmation_message2 ($_SESSION['display_confirmation_message'], false, true);
    unset($_SESSION['display_confirmation_message']);
}
if (isset($_SESSION['group_members_message'])) {
    echo Display::display_confirmation_message2 ($_SESSION['group_members_message'], false, true);
    unset($_SESSION['group_members_message']);
}
//start the content div
echo '<div id="content">';

if (isset($_SESSION['group_limit_message'])) {
    echo Display::display_error_message ($_SESSION['group_limit_message'], false, true);
    unset($_SESSION['group_limit_message']);
}
// cleaning up session variables that have been used so that we always get 'fresh' information
$_SESSION['groupmembers'.api_get_course_id()] = null;
$_SESSION['coaches'.api_get_course_id()] = null;
$_SESSION['group_info'.api_get_course_id()] = null;

// action handling
group_actions();

// display the groups
display_groups();
// close the content div
echo '</div>';
// Display the footer
Display::display_footer();
exit;


function display_groups() {
    $table = new SortableTable('groups', 'get_number_of_groups', 'get_groups_data',2);

    if (api_is_allowed_to_edit()) {
        // table headers
        $table->set_header(0, '', false);
        $table->set_header(1, '', false);
        $table->set_header(2, get_lang('GroupName'));
        $table->set_header(3, get_lang('Scenario'));
        $table->set_header(4, get_lang('Tutor'));
        $table->set_header(5, get_lang('Assignment'),false);
        $table->set_header(6, get_lang('Messages'),false);
        $table->set_header(7, get_lang('GroupAvailablePlaces'));
        $table->set_header(8, get_lang('Members'),false);
        $table->set_header(9, get_lang('Empty'));
        $table->set_header(10, get_lang('Fill'));
        $table->set_header(11, get_lang('Edit'));
        // table data filters
        $table->set_column_filter(1, 'group_icon_filter');
        $table->set_column_filter(2, 'group_name_filter');
        $table->set_column_filter(3, 'scenario_filter');
        $table->set_column_filter(4, 'tutor_filter');
        $table->set_column_filter(5, 'assignment_filter');
        $table->set_column_filter(6, 'message_filter');
        $table->set_column_filter(7, 'maxstudent_filter');
        $table->set_column_filter(8, 'group_members_filter');
        $table->set_column_filter(9, 'empty_filter');
        $table->set_column_filter(10, 'fill_filter');
        $table->set_column_filter(11, 'edit_filter');
    } else {
        // table headers
        $table->set_header(0, '', false);
        $table->set_header(1, get_lang('GroupName'));
        $table->set_header(2, get_lang('Tutor'));
        $table->set_header(3, get_lang('Assignment'),false);
        $table->set_header(4, get_lang('Messages'),false);
        $table->set_header(5, get_lang('GroupAvailablePlaces'));
        $table->set_header(6, get_lang('Members'),false);
        // table data filters
        $table->set_column_filter(0, 'group_icon_filter');
        $table->set_column_filter(1, 'group_name_filter');
        $table->set_column_filter(2, 'tutor_filter');
        $table->set_column_filter(3, 'assignment_filter');
        $table->set_column_filter(4, 'message_filter');
        $table->set_column_filter(5, 'maxstudent_filter');
        $table->set_column_filter(6, 'group_members_filter');
    }

    // table form actions (actions on selected items)
    if (api_is_allowed_to_edit(false,true)) {
        $table->set_form_actions(array ('delete' => get_lang('DeleteGroup')));
    }
    
    $table->display();
}

function get_number_of_groups(){
	global $_course;
	// Database table definition
	$table_group =	Database::get_course_table(TABLE_GROUP);
	$table_group_tutor = Database::get_course_table(TABLE_GROUP_TUTOR);
	$table_group_user =	Database::get_course_table(TABLE_GROUP_USER);
	
	if(api_is_allowed_to_edit()){
	$sql = "SELECT count(id) AS total_number_of_items FROM $table_group";
	}
	else if(api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
	$sql = "SELECT count(gp.id) AS total_number_of_items FROM $table_group gp, $table_group_tutor grt where gp.id = grt.group_id and grt.user_id = ".api_get_user_id();
	}
	else {
	$sql = "SELECT count(gp.id) AS total_number_of_items FROM $table_group gp, $table_group_user gu where gp.id = gu.group_id and gu.user_id = ".api_get_user_id();
	}
	$res = Database::query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}

function get_groups_data($from, $number_of_items, $column, $direction){
    // Database table definition
    $table_group          = Database::get_course_table(TABLE_GROUP);
    $table_group_category = Database::get_course_table(TABLE_GROUP_CATEGORY);
    $table_group_tutor    = Database::get_course_table(TABLE_GROUP_TUTOR);
    $table_group_user     = Database::get_course_table(TABLE_GROUP_USER);

    if (api_is_allowed_to_edit(false,true)) {
        $sql = "SELECT 
                    groupinfo.id          AS col0,
                    groupinfo.id          AS col1,
                    groupinfo.id          AS col2,
                    groupinfo.category_id AS col3,
                    groupinfo.id          AS col4,
                    groupinfo.id          AS col5,
                    groupinfo.id          AS col6,
                    groupinfo.max_student AS col7,
                    groupinfo.id          AS col8,
                    groupinfo.id          AS col9,
                    groupinfo.id          AS col10,
                    groupinfo.id          AS col11
                FROM $table_group groupinfo
                LEFT JOIN $table_group_category group_category
                ON groupinfo.category_id = group_category.id
                ";       
        $sql .= " ORDER BY col$column $direction ";
        $sql .= " LIMIT $from,$number_of_items";
        $res = Database::query($sql, __FILE__, __LINE__);
        while ($group = Database::fetch_row($res)) {
            $return[] = $group;
        }
    } else if (api_is_grouptutor($_course, api_get_session_id(), api_get_user_id())) {		
        $sql = "SELECT 
                    groupinfo.id 		AS col0,				
                    groupinfo.id 		AS col1,
                    groupinfo.id	AS col2,
                    groupinfo.id	AS col3,
                    groupinfo.id	AS col4,
                    groupinfo.max_student	AS col5,
                    groupinfo.id		AS col6				
                FROM $table_group groupinfo,$table_group_tutor grt
                WHERE groupinfo.id = grt.group_id AND grt.user_id = ".api_get_user_id();
        $sql .= " ORDER BY col$column $direction ";
        $sql .= " LIMIT $from,$number_of_items";
        $res = Database::query($sql, __FILE__, __LINE__);
        while ($group = Database::fetch_row($res)) {
            $return[] = $group;
        }
    } else {
        $sql_main = "SELECT groupinfo.id AS grp_id,groupinfo.category_id FROM $table_group groupinfo,$table_group_user groupuser WHERE groupinfo.id = groupuser.group_id AND groupuser.user_id = ".api_get_user_id();
        $rs_main = Database::query($sql_main,__FILE__,__LINE__);
        while ($row_main = Database::fetch_array($rs_main)) {
            $grp_id = $row_main['grp_id'];
            $category_id = $row_main['category_id'];
        }

        $sql = "SELECT 				
                    groupinfo.id 		AS col0,
                    groupinfo.id 		AS col1,
                    groupinfo.id	AS col2,
                    groupinfo.id	AS col3,
                    groupinfo.id	AS col4,
                    groupinfo.max_student	AS col5,
                    groupinfo.id		AS col6				
                FROM $table_group groupinfo,$table_group_user groupuser
                WHERE groupinfo.id = groupuser.group_id AND groupuser.user_id = ".api_get_user_id();
        $sql .= " ORDER BY col$column $direction ";
        $sql .= " LIMIT $from,$number_of_items";
        $res = Database::query($sql, __FILE__, __LINE__);
        while ($group = Database::fetch_row($res)) {
            $return[] = $group;
        }
        if ($category_id <> 1) {
            $sql = "SELECT 				
                        groupinfo.id 		AS col0,
                        groupinfo.id 		AS col1,
                        groupinfo.id	AS col2,
                        groupinfo.id	AS col3,
                        groupinfo.id	AS col4,
                        groupinfo.max_student	AS col5,
                        groupinfo.id		AS col6		
                        FROM $table_group groupinfo WHERE id <> ".$grp_id." AND category_id = 2";
            $sql .= " ORDER BY col$column $direction ";
            $sql .= " LIMIT $from,$number_of_items";
            $res = Database::query($sql, __FILE__, __LINE__);
            while ($group = Database::fetch_row($res)) {
                $return[] = $group;
            }
        }
    }

    return $return;
}

function group_icon_filter($group_id,$url_params,$row){
	return '<center>'.Display::return_icon('pixel.gif','',array('class' => 'actionplaceholdericon actiongroupstudentview')).'</center>';
}

function group_name_filter($group_id,$url_params,$row){
	$table_group 		= Database::get_course_table(TABLE_GROUP);

	$sql = "SELECT * FROM $table_group WHERE id = ".$group_id;
	$rs = Database::query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($rs);
	
	return '<a href="group_space.php?'.api_get_cidreq().'&amp;gidReq='.$row['id'].'&amp;group_id='.$row['id'].'">'.$row['name'].'</a>';
}

function scenario_filter($category_id,$url_params,$row){
	if($category_id == 1){
		$category_name = get_lang('Tutoring');
	}
	elseif($category_id == 2){
		$category_name = get_lang('Collaboration');
	}
	else {
		$category_name = get_lang('Competition');
	}
	
	return $category_name;
}

function tutor_filter($group_id, $url_params, $row){
	$table_group 	  = Database::get_course_table(TABLE_GROUP);
	$table_group_tutor = Database :: get_course_table(TABLE_GROUP_TUTOR);
	$table_user = Database :: get_main_table(TABLE_MAIN_USER);

	$sql = "SELECT usr.firstname,usr.lastname FROM $table_group gp, $table_group_tutor gpt, $table_user usr WHERE gp.id = gpt.group_id AND gpt.user_id = usr.user_id AND gp.id = ".$group_id;
	$rs = Database::query($sql,__FILE__,__LINE__);
	while ($row = Database::fetch_row($rs)) {
		$firstname = $row[0];
		$lastname = $row[1];

		$tutor_name = $firstname.' '.$lastname;		
	}
	return $tutor_name;
}

function assignment_filter($group_id, $url_params, $row){
	$tbl_work 	  = Database::get_course_table(TABLE_STUDENT_PUBLICATION);

	//$sql = "SELECT * FROM $tbl_work WHERE filetype='folder' AND session_id = ".api_get_session_id();
	$sql = "SELECT * FROM $tbl_work WHERE filetype='folder' AND post_group_id = ".$group_id." AND session_id = ".api_get_session_id();
	$res = Database::query($sql,__FILE__,__LINE__);
	$num_assignment = Database::num_rows($res);

	return '<center>'.$num_assignment.'</center>';	
}

function message_filter($group_id, $url_params, $row){
	// Database table definition
	$table_message = Database::get_main_table(TABLE_MESSAGE);
	$tbl_forum_thread = Database::get_course_table(TABLE_FORUM_THREAD);

	$users = GroupManager :: get_subscribed_users($group_id);
	foreach($users as $user){
		$userids[] = $user['user_id'];		
	}
	$tutors = GroupManager :: get_group_tutors($group_id);
	foreach($tutors as $tutor){
		$userids[] = $tutor['user_id'];		
	}	
	$users_list = implode(",",$userids);
	
	$sql = "SELECT * FROM $table_message WHERE msg_status = 4 AND (user_sender_id IN (".$users_list.") OR user_receiver_id IN (".$users_list."))";
	$res = Database::query($sql,__FILE__,__LINE__);
	$num_messages = Database::num_rows($res);

	$forums_of_groups = get_forums_of_group($group_id);
	foreach ($forums_of_groups as $key => $value) {
		$forum_id = $value['forum_id'];
		$sql = "SELECT * FROM $tbl_forum_thread WHERE forum_id = $forum_id";
		$res = Database::query($sql,__FILE__,__LINE__);
		$num_threads = Database::num_rows($res);
	}
	$num_messages = $num_messages + $num_threads;

	return '<center>'.$num_messages.'</center>';
}

function group_members_filter($group_id, $url_params, $row){
	global $_course;

	// Database table definition
	$table_group 	  = Database::get_course_table(TABLE_GROUP);
	$table_group_user = Database :: get_course_table(TABLE_GROUP_USER);

	// count the number of members in the group if none is set
	if (!is_array($_SESSION['groupmembers'.api_get_course_id()])){
		$sql = "SELECT group_id, count(id) as number_of_users FROM ".$table_group_user." group by group_id";
		$result = Database::query($sql,__FILE__,__LINE__);
		while ($row = Database::fetch_array($result)) {
			$_SESSION['groupmembers'.api_get_course_id()][$row['group_id']] = $row['number_of_users'];
		}
	}
        if (is_null($_SESSION['groupmembers'.api_get_course_id()][$group_id])){
           $_SESSION['groupmembers'.api_get_course_id()][$group_id]= 0;
        }
	return '<center>'.$_SESSION['groupmembers'.api_get_course_id()][$group_id].'</center>';
}

function maxstudent_filter($max_student, $url_params, $row){
	return '<center>'.$max_student.'</center>';
}

function empty_filter($group_id, $url_params, $row){
	$grp_session_id = get_groupsession_id($row[0]);
	if($grp_session_id == 0){
	return '<div align="center"><a href="'.api_get_path(WEB_CODE_PATH).'group/group.php?'.api_get_cidreq().'&amp;action=empty_one&amp;group_id='.$group_id.'">'.Display::return_icon('pixel.gif',get_lang('EmptyGroup'),array('class' => 'actionplaceholdericon actiongroupempty')).'</a></div>';
	}
	elseif($grp_session_id == api_get_session_id()){
	return '<div align="center"><a href="'.api_get_path(WEB_CODE_PATH).'group/group.php?'.api_get_cidreq().'&amp;action=empty_one&amp;group_id='.$group_id.'">'.Display::return_icon('pixel.gif',get_lang('EmptyGroup'),array('class' => 'actionplaceholdericon actiongroupempty')).'</a></div>';
	}
	else {
	return '<div align="center">-</div>';
	}
}

function fill_filter($group_id, $url_params, $row){
    
	$grp_session_id = get_groupsession_id($row[0]);
        $course=api_get_course_info();
        $courseCode =$course['official_code'];
	if($grp_session_id == 0){
	return '<div align="center"><a href="javascript:void(0)" onclick=\'listUser('.$group_id.',"'.$courseCode.'")\'>'.Display::return_icon('pixel.gif',get_lang('FillGroup'),array('class' => 'actionplaceholdericon actiongroupfill')).'</a></div>';
	}
	elseif($grp_session_id == api_get_session_id()){
	return '<div align="center"><a href="javascript:void(0)" onclick=\'listUser('.$group_id.',"'.$courseCode.'")\'>'.Display::return_icon('pixel.gif',get_lang('FillGroup'),array('class' => 'actionplaceholdericon actiongroupfill')).'</a></div>';
	}
	else {
	return '<div align="center">-</div>';
	}
}

function edit_filter($group_id, $url_params, $row){	
	$grp_session_id = get_groupsession_id($row[0]);
	if($grp_session_id == 0){
	return '<div align="center"><a href="'.api_get_path(WEB_CODE_PATH).'group/group_edit.php?'.api_get_cidreq().'&amp;group_id='.$group_id.'">'.Display::return_icon('pixel.gif',get_lang('EditGroup'),array('class' => 'actionplaceholdericon actionedit')).'</a></div>';
	}
	elseif($grp_session_id == api_get_session_id()){
	return '<div align="center"><a href="'.api_get_path(WEB_CODE_PATH).'group/group_edit.php?'.api_get_cidreq().'&amp;group_id='.$group_id.'">'.Display::return_icon('pixel.gif',get_lang('EditGroup'),array('class' => 'actionplaceholdericon actionedit')).'</a></div>';
	}
	else {
	return '<div align="center">-</div>';
	}

}

function delete_filter($group_id, $url_params, $row){
	return '<a href="'.api_get_path(WEB_CODE_PATH).'group/group.php?'.api_get_cidreq().'&amp;action=delete_one&amp;group_id='.$group_id.'">'.Display::return_icon('delete.png',get_lang('DeleteGroup')).'</a>';
}

function get_groupsession_id($group_id){
	$table_group 		= Database::get_course_table(TABLE_GROUP);
	$table_group_category 	= Database :: get_course_table(TABLE_GROUP_CATEGORY);
	$sql = "SELECT session_id FROM $table_group gp, $table_group_category gpcat WHERE gp.category_id = gpcat.id AND gp.id = ".$group_id;
	$result = Database::query($sql,__FILE__,__LINE__);
	while ($row = Database::fetch_array($result)) {
		$grp_session_id = $row['session_id'];
	}
	return $grp_session_id;
}


function groupmembership_filter($group_id, $url_params, $row){
	global $_user;

	// count the number of members in the group if none is set
	if (!is_array($_SESSION['groupmembers'.api_get_course_id()])){
		$sql = "SELECT group_id, count(id) as number_of_users FROM ".$table_group_user." group by group_id";
		$result = Database::query($sql,__FILE__,__LINE__);
		while ($row = Database::fetch_array($result)) {
			$_SESSION['groupmembers'.api_get_course_id()][$row['group_id']] = $row['number_of_users'];
		}
	}

	// get all the group information (and cache it)
	if (!is_array($_SESSION['grouplist'.api_get_course_id()])){
		$groups_unsorted = GroupManager::get_group_list();
		foreach ($groups_unsorted as $key=>$group){
			$group_list[$group['id']] = $group;
		}
		$_SESSION['grouplist'.api_get_course_id()] = $group_list;
	}

	// get all the group the user is subscribed to
	if (!is_array($_SESSION['groupmembership'.api_get_course_id()])){
		$_SESSION['groupmembership'.api_get_course_id()] = GroupManager::get_group_ids(null,$_user['user_id']);
	}

	// display link to register to the group if
	// 1. the number of places is higher than the number of students already registerend AND 
	// 2. registration is allowed AND
	// 3. the user is not in the group yet
	if ($_SESSION['groupmembers'.api_get_course_id()][$group_id] < $_SESSION['grouplist'.api_get_course_id()][$group_id]['maximum_number_of_members']
		AND $_SESSION['grouplist'.api_get_course_id()][$group_id]['self_registration_allowed'] == 1 
		AND !in_array($group_id, $_SESSION['groupmembership'.api_get_course_id()])){
		return '<a href="group.php?action=self_reg&amp;group_id='.$group_id.'">register</a>';
	}
	
	// display the link to unregister if
	// 1. the user is registered AND
	// 2. unregistration is allowed
	if ( in_array($group_id, $_SESSION['groupmembership'.api_get_course_id()]) AND $_SESSION['grouplist'.api_get_course_id()][$group_id]['self_unregistration_allowed'] == 1){
		return '<a href="group.php?action=self_unreg&amp;group_id='.$group_id.'">unregister</a>';
	}

	// display 'group full' if 
	// 1. the number of places is equal than the number of students already registerend AND 
	// 2. the user is not in the group yet
	if ($_SESSION['groupmembers'.api_get_course_id()][$group_id] == $_SESSION['grouplist'.api_get_course_id()][$group_id]['maximum_number_of_members']
		AND !in_array($group_id, $_SESSION['groupmembership'.api_get_course_id()])){
		return 'group full';
	}
}


function display_secondary_actions(){
	echo '<div class="actions">';
	if (api_is_allowed_to_edit(false,true)){
		// group overview
		if (api_get_setting('group_overview') == 'true'){
			if( Database::count_rows(Database::get_course_table(TABLE_GROUP)) > 0) {
				echo '<a href="group_overview.php?'.api_get_cidreq().'">'.Display::return_icon('group.gif', get_lang('GroupOverview')).' '.get_lang('GroupOverview').get_lang('GroupOverview').'</a>';
			} else {
				echo '<a href="#" class="invisible">'.Display::return_icon('group.gif', get_lang('GroupOverview')).' '.get_lang('GroupOverview').'</a>';
			}
		}

		// export
		if (api_is_allowed_to_edit(false,true)){
			if (api_get_setting('group_export_csv') == 'true') {
				echo '<a href="group_overview.php?'.api_get_cidreq().'&amp;action=export&amp;type=csv">'.Display::return_icon('pixel.gif',get_lang('ExportAsCSV'),array('class' => 'actionplaceholdericon actionexport')).get_lang('ExportAsCSV').'</a> ';
		        }
			if (api_get_setting('group_export_xls') == 'true') {
				echo ' <a href="group_overview.php?'.api_get_cidreq().'&amp;action=export&amp;type=xls">'.Display::return_icon('pixel.gif',get_lang('ExportAsCSV'),array('class' => 'actionplaceholdericon actionexport')).get_lang('ExportAsXLS').'</a>';
			}
		}
		echo '</div>';
	}
	echo '</div>';
}




function display_actions() {
    echo '<div class="actions">';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'user/user.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Users'), array('class' => 'toolactionplaceholdericon toolactionuser')) . ' ' . get_lang('Users') . '</a>';
    if (api_is_allowed_to_edit(false,true)) {
        // link to add a group
        echo '<a href="group_creation.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif',get_lang('NewGroupCreate'),array('class' => 'toolactionplaceholdericon toolactionsgroupadd')).get_lang('NewGroupCreate').'</a>';
        echo '<a href="group.php?'.api_get_cidreq().'&amp;action=export&amp;type=csv">'.Display::return_icon('pixel.gif',get_lang('ExportAsCSV'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportAsCSV').'</a>';
    }    
    echo '</div>';
}

function export_csv_data(){

	$table_group 		= Database::get_course_table(TABLE_GROUP);
	$table_group_category 		= Database::get_course_table(TABLE_GROUP_CATEGORY);
	$table_group_tutor = Database::get_course_table(TABLE_GROUP_TUTOR);
	$table_group_user =	Database::get_course_table(TABLE_GROUP_USER);

	$data = array();
	$data[] = array(get_lang('GroupName'),get_lang('Tutor'),get_lang('Assignments'),get_lang('Messages'),get_lang('GroupAvailablePlaces'),get_lang('GroupMembers'));

		if (api_is_allowed_to_edit(false,true)){
			$sql = "SELECT groupinfo.*
				FROM $table_group groupinfo
				LEFT JOIN $table_group_category group_category
				ON groupinfo.category_id = group_category.id
				";			
		} else if(api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){		
			$sql = "SELECT groupinfo.*		
				FROM $table_group groupinfo,$table_group_tutor grt
				WHERE groupinfo.id = grt.group_id AND grt.user_id = ".api_get_user_id();			
		}
		else {
			$sql = "SELECT groupinfo.*							
				FROM $table_group groupinfo,$table_group_user groupuser
				WHERE groupinfo.id = groupuser.group_id AND groupuser.user_id = ".api_get_user_id();			
		}
		
		$res = Database::query($sql, __FILE__, __LINE__);
		while ($group = Database::fetch_array($res)) {
		
		$tutor = tutor_filter($group['id']);
		$assignments = assignment_filter($group['id']);
		$messages = message_filter($group['id']);
		$places = $group['max_student'];
		$group_members = group_members_filter($group['id']);
             
		$row = array();
					
		$row[] = $group['name'];
		$row[] = $tutor;	
		$row[] = strip_tags($assignments);	
		$row[] = strip_tags($messages);	
		$row[] = $places;	
		$row[] = strip_tags($group_members);	
		$data[] = $row;
	}
	return $data;
}


function group_actions(){
	global $_user;

	// actions that a student can do
	if (isset ($_GET['action']))
	{
		switch ($_GET['action'])
		{
			case 'self_reg' :
				if (GroupManager :: is_self_registration_allowed($_SESSION['_user']['user_id'], Security::remove_XSS($_GET['group_id']))) {
					GroupManager :: subscribe_users($_SESSION['_user']['user_id'],Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message2(get_lang('GroupNowMember'));
				}
				break;
			case 'self_unreg' :
				if (GroupManager :: is_self_unregistration_allowed($_SESSION['_user']['user_id'], Security::remove_XSS($_GET['group_id']))) {
					GroupManager :: unsubscribe_users($_SESSION['_user']['user_id'],Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message2(get_lang('StudentDeletesHimself'));
				}
				break;
			case 'show_msg' :
                                //asked for the messages settings active.
                                if(!api_get_setting('display_feedback_messages')){                            
                                    Display :: display_confirmation_message2(Security::remove_XSS($_GET['msg']));
                                }
				break;
		}
	}

	// actions that a student can do
	if (isset ($_POST['action']))
	{
		switch ($_POST['action']){
			case 'join': 
			foreach ($_POST['id'] as $key=>$group_id){
					GroupManager :: subscribe_users ($_user['user_id'], $group_id);
				}
				break;
		}
	}

	// actions that a teacher can do
	if (api_is_allowed_to_edit(false,true))
	{
		// Post-actions
		if (isset ($_POST['action']))
		{
			switch ($_POST['action'])
			{
				case 'delete' :
					if( is_array($_POST['id'])) {
						GroupManager :: delete_groups($_POST['id']);
						Display :: display_confirmation_message2(get_lang('SelectedGroupsDeleted'));
					}
					break;
				case 'empty_selected' :
					if( is_array($_POST['id']))	{
					    GroupManager :: unsubscribe_all_users($_POST['group']);
					    Display :: display_confirmation_message2(get_lang('SelectedGroupsEmptied'));
					}
					break;
				case 'fill_selected' :
					if( is_array($_POST['id']))	{
					    GroupManager :: fill_groups($_POST['group']);
					    Display :: display_confirmation_message2(get_lang('SelectedGroupsFilled'));
					}
					break;
			}
		}
		// Get-actions
		if (isset ($_GET['action']))
		{
			switch ($_GET['action'])
			{
				case 'swap_cat_order' :
					GroupManager :: swap_category_order($_GET['id1'],$_GET['id2']);
					Display :: display_confirmation_message2(get_lang('CategoryOrderChanged'));
					break;
				case 'delete_one' :
					GroupManager :: delete_groups($_GET['group_id']);
					Display :: display_confirmation_message2(get_lang('GroupDel'));
					break;
				case 'empty_one' :
					GroupManager :: unsubscribe_all_users($_GET['group_id']);
					Display :: display_confirmation_message2(get_lang('GroupEmptied'));
					break;
				case 'fill_one' :
					GroupManager :: fill_groups($_GET['group_id']);
					Display :: display_confirmation_message2(get_lang('GroupFilledGroups'));
					break;
				case 'delete_category' :
					GroupManager :: delete_category($_GET['group_id']);
					Display :: display_confirmation_message2(get_lang('CategoryDeleted'));
					break;
                                case 'saveUser':                                                                                                           
                                        $groupId= $_POST['group_id'];                                        
                                        if(count($_POST['participants'])>0){
                                            $num_userdb = 0;
                                            $num_userdb = GroupManager :: number_of_students($groupId);
                                            $limitExceeded = count($_POST['participants'])+ $num_userdb > GroupManager :: maximum_number_of_students($groupId);
                                            if(!$limitExceeded){
                                                foreach ($_POST['participants'] as $member){                                                
                                                    $isUserInGroup = GroupManager::is_user_in_group($member, $groupId);                                                //                                                                                  
                                                    if (!$isUserInGroup)                                            
                                                        GroupManager :: subscribe_users($member,$groupId);
                                                } 
                                            }else
                                                Display::display_error_message(get_lang('ExceedsLimitMembers'), false, true);
                                                
                                            Display :: display_confirmation_message2(get_lang('GroupFilledGroups'));
                                            }
                                        break;
			}
		}
	}
}
?>
