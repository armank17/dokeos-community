<?php

/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2004-2008 Dokeos SPRL
  Copyright (c) 2003 Ghent University (UGent)
  Copyright (c) 2001 Universite catholique de Louvain (UCL)
  Copyright (c) various contributors

  For a full list of contributors, see "credits.txt".
  The full license can be read in "license.txt".

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  See the GNU General Public License for more details.

  Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
  Mail: info@dokeos.com
  ==============================================================================
 */

/**
 * 	These files are a complete rework of the forum. The database structure is
 * 	based on phpBB but all the code is rewritten. A lot of new functionalities
 * 	are added:
 * 	- forum categories and forums can be sorted up or down, locked or made invisible
 * 	- consistent and integrated forum administration
 * 	- forum options: 	are students allowed to edit their post?
 * 						moderation of posts (approval)
 * 						reply only forums (students cannot create new threads)
 * 						multiple forums per group
 * 	- sticky messages
 * 	- new view option: nested view
 * 	- quoting a message
 *
 * 	@Author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * 	@Copyright Ghent University
 * 	@Copyright Patrick Cool
 *
 * 	@package dokeos.forum
 */
// name of the language file that needs to be included
$language_file = array(
    'forum',
    'group'
);

// including the global dokeos file
require '../inc/global.inc.php';
require_once '../inc/lib/timezone.lib.php';


$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
      $(".user_info").click(function () {
        var myuser_id = $(this).attr("id");
        var user_info_id = myuser_id.split("user_id_");
        my_user_id = user_info_id[1];
        $("<div style=\'display:none;\' title=\''.get_lang('UserInfo').'\' id=\'html_user_info\'></div>").insertAfter(this);
        $.ajax({
            url: "'.api_get_path(WEB_AJAX_PATH).'user_manager.ajax.php?a=get_user_info&user_id="+my_user_id,
            success: function(data){
                //var dialog_div = $("<div id=\'html_user_info\'></div>");
                //dialog_div.html(data);
                $(\'#html_user_info\').html(\'<div style="text-align:justify;width:100%;max-height:580px">\'+data+\'</div>\');
                $("#html_user_info").dialog({
                    open: function(event, ui) {  
                                            $(".ui-dialog-titlebar-close").css("width","0px");
                                            $(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang("Close").'</span>");  											
                },                 
                    autoOpen: true,
                modal: true,
                    title: "'.get_lang('UserInfo').'",
                    closeText: "'.get_lang('Close').'",
                width: 640,
                height : 240,
                resizable:false
                });
            }
        });
    });
});
</script>';
// notice for unauthorized people.
api_protect_course_script(true);

// the section (tabs)
$this_section = SECTION_COURSES;

// including additional library scripts
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
include_once (api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php');
$nameTools = get_lang('Forum');


//are we in a lp ?
$origin = '';
$origin_string = '';
if (isset($_GET['origin'])) {
    $origin = Security::remove_XSS($_GET['origin']);
    $origin_string = '&amp;origin=' . $origin;
}

/*
  -----------------------------------------------------------
  Including necessary files
  -----------------------------------------------------------
 */
require 'forumconfig.inc.php';
require_once 'forumfunction.inc.php';

$userid = api_get_user_id();
$userinf = api_get_user_info($userid);

/*
  ==============================================================================
  MAIN DISPLAY SECTION
  ==============================================================================
 */


/*
  -----------------------------------------------------------
  Retrieving forum and forum categorie information
  -----------------------------------------------------------
 */
// we are getting all the information about the current forum and forum category.
// note pcool: I tried to use only one sql statement (and function) for this
// but the problem is that the visibility of the forum AND forum cateogory are stored in the item_property table

$my_forum_group = isset($_GET['gidReq']) ? $_GET['gidReq'] : '';
$my_forum = isset($_GET['forum']) ? $_GET['forum'] : '';
$val = GroupManager::user_has_access($userid, $my_forum_group, GROUP_TOOL_FORUM);

if (!empty($my_forum_group)) {
    if (api_is_allowed_to_edit(false, true) || $val == true) {
        $current_forum = get_forum_information($my_forum); // note: this has to be validated that it is an existing forum.
        $current_forum_category = get_forumcategory_information($current_forum['forum_category']);
    }
} else {
    $result = get_forum_information($my_forum);
    if ($result['forum_of_group'] == 0) {
        $current_forum = get_forum_information($my_forum); // note: this has to be validated that it is an existing forum.
        $current_forum_category = get_forumcategory_information($current_forum['forum_category']);
    }
}


/*
  -----------------------------------------------------------
  Header and Breadcrumbs
  -----------------------------------------------------------
 */
$my_search = isset($_GET['search']) ? $_GET['search'] : '';
$my_action = isset($_GET['action']) ? $_GET['action'] : '';

if (isset($_SESSION['gradebook'])) {
    $gradebook = $_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook == 'view') {
    $interbreadcrumb[] = array(
        'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
        'name' => get_lang('Gradebook')
    );
}

if (!empty($_GET['gidReq'])) {
    $toolgroup = Database::escape_string($_GET['gidReq']);
    api_session_register('toolgroup');
}

if (!empty($_SESSION['toolgroup'])) {
    $_clean['toolgroup'] = (int) $_SESSION['toolgroup'];
    $group_properties = GroupManager :: get_group_properties($_clean['toolgroup']);
    $interbreadcrumb[] = array("url" => "../group/group.php", "name" => get_lang('Groups'));
    $interbreadcrumb[] = array("url" => "../group/group_space.php?gidReq=" . $_SESSION['toolgroup'], "name" => get_lang('GroupSpace') . ' (' . $group_properties['name'] . ')');
    //$interbreadcrumb[]=array("url" => "index.php?search=".Security::remove_XSS($my_search),"name" => $nameTools);
    //$interbreadcrumb[]=array("url" => "viewforumcategory.php?forumcategory=".$current_forum_category['cat_id']."&amp;search=".Security::remove_XSS(urlencode($my_search)),"name" => prepare4display($current_forum_category['cat_title']));
    $interbreadcrumb[] = array("url" => "#", "name" => prepare4display($current_forum['forum_title']));
    //viewforum.php?forum=".Security::remove_XSS($my_forum)."&amp;origin=".$origin."&amp;gidReq=".$_SESSION['toolgroup']."&amp;search=".Security::remove_XSS(urlencode($my_search)),
} else {
    $interbreadcrumb[] = array("url" => "index.php?gradebook=$gradebook&amp;search=" . Security::remove_XSS($my_search), "name" => $nameTools);
    $interbreadcrumb[] = array("url" => "viewforumcategory.php?forumcategory=" . $current_forum_category['cat_id'] . "&amp;search=" . Security::remove_XSS(urlencode($my_search)), "name" => prepare4display($current_forum_category['cat_title']));
    $interbreadcrumb[] = array("url" => "#", "name" => prepare4display($current_forum['forum_title']));
    //viewforum.php?forum=".Security::remove_XSS($my_forum)."&amp;origin=".$origin."&amp;search=".Security::remove_XSS(urlencode($my_search))
}

if ($origin == 'learnpath') {
    include(api_get_path(INCLUDE_PATH) . 'reduced_header.inc.php');
} else {
    // the last element of the breadcrumb navigation is already set in interbreadcrumb, so give empty string
    //Display :: display_tool_header('');
    //api_display_tool_title($nameTools);
    Display::display_tool_header();
}
// Tool introduction
Display::display_introduction_section(TOOL_FORUM);
/*
  -----------------------------------------------------------
  Actions
  -----------------------------------------------------------
 */
$table_link = Database :: get_main_table(TABLE_MAIN_GRADEBOOK_LINK);
// Change visibility of a forum or a forum category
if (($my_action == 'invisible' OR $my_action == 'visible') AND isset($_GET['content']) AND isset($_GET['id']) AND api_is_allowed_to_edit(false, true) && api_is_allowed_to_session_edit(false, true)) {
    $message = change_visibility($_GET['content'], $_GET['id'], $_GET['action']); // note: this has to be cleaned first
}
// locking and unlocking
if (($my_action == 'lock' OR $my_action == 'unlock') AND isset($_GET['content']) AND isset($_GET['id']) AND api_is_allowed_to_edit(false, true) && api_is_allowed_to_session_edit(false, true)) {
    $message = change_lock_status($_GET['content'], $_GET['id'], $my_action); // note: this has to be cleaned first
}
// deleting
if ($my_action == 'delete' AND isset($_GET['content']) AND isset($_GET['id']) AND api_is_allowed_to_edit(false, true) && api_is_allowed_to_session_edit(false, true)) {
    $message = delete_forum_forumcategory_thread($_GET['content'], $_GET['id']); // note: this has to be cleaned first
    //delete link
    $sql_link = 'DELETE FROM ' . $table_link . ' WHERE ref_id=' . Database::escape_string(Security::remove_XSS($_GET['id'])) . ' and type=5 and course_code="' . api_get_course_id() . '";';
    Database::query($sql_link);
}
// moving
if ($my_action == 'move' and isset($_GET['thread']) AND api_is_allowed_to_edit(false, true) && api_is_allowed_to_session_edit(false, true)) {
    $message = move_thread_form();
}
// notification
if ($my_action == 'notify' AND isset($_GET['content']) AND isset($_GET['id']) && api_is_allowed_to_session_edit(false, true)) {
    $return_message = set_notification($_GET['content'], $_GET['id']);
    //Display :: display_confirmation_message($return_message,false);
    $_SESSION["display_confirmation_message"] = $return_message;
}

// student list

if ($my_action == 'liststd' AND isset($_GET['content']) AND isset($_GET['id']) AND api_is_allowed_to_edit(null, true)) {

    switch ($_GET['list']) {
        case "qualify":
            $student_list = get_thread_users_qualify($_GET['id']);
            $nrorow3 = -2;
            break;
        case "notqualify":
            $student_list = get_thread_users_not_qualify($_GET['id']);
            $nrorow3 = -2;
            break;
        default:
            $student_list = get_thread_users_details($_GET['id']);
            $nrorow3 = Database::num_rows($student_list);
            break;
    }
    $table_list = '<p><br /><h3 class="orange">' . get_lang('ThreadUsersList') . '&nbsp;: ' . get_name_thread_by_id(Security::remove_XSS($_GET['id'])) . '</h3>';
    if ($nrorow3 > 0 || $nrorow3 == -2) {
        $url = 'cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;forum=' . Security::remove_XSS($my_forum) . '&amp;action=' . Security::remove_XSS($_GET['action']) . '&content=' . Security::remove_XSS($_GET['content']) . '&amp;id=' . Security::remove_XSS($_GET['id']);
        $table_list.= '<br />
				 <div style="width:50%">
				 <table class="data_table" border="0">
					<tr>
						<th height="22"><a href="viewforum.php?' . $url . '&amp;origin=' . $origin . '&list=all">' . get_lang('AllStudents') . '</a></th>
						<th><a href="viewforum.php?' . $url . '&amp;origin=' . $origin . '&list=qualify">' . get_lang('StudentsQualified') . '</a></th>
						<th><a href="viewforum.php?' . $url . '&amp;origin=' . $origin . '&list=notqualify">' . get_lang('StudentsNotQualified') . '</a></th>
					</tr>
				 </table></div>
				 <div>
				 ';

        $icon_qualify = 'checkok.png';
        $table_list.= '<div class="quiz_content_actions"><br /><table class="data_table" style="width:50%">';
        // The column headers (to do: make this sortable)
        $table_list.= '<tr >';
        $table_list.= '<th height="24">' . get_lang('NamesAndLastNames') . '</th>';

        if ($_GET['list'] == 'qualify') {
            $table_list.= '<th>' . get_lang('Qualification') . '</th>';
        }
        if (api_is_allowed_to_edit(null, true)) {
            $table_list.= '<th>' . get_lang('Qualify') . '</th>';
        }
        $table_list.= '</tr>';
        $max_qualify = show_qualify('2', $_GET['cidReq'], $my_forum, $userid, $_GET['id']);
        $counter_stdlist = 0;

        if (Database::num_rows($student_list) > 0) {
            while ($row_student_list = Database::fetch_array($student_list)) {
                if ($counter_stdlist % 2 == 0) {
                    $class_stdlist = "row_odd";
                } else {
                    $class_stdlist = "row_even";
                }
                $name_user_theme = api_get_person_name($row_student_list['firstname'], $row_student_list['lastname']);
                $table_list.= '<tr class="' . $class_stdlist . '"><td><a href="../user/userInfo.php?uInfo=' . $row_student_list['user_id'] . '&tipo=sdtlist&' . api_get_cidreq() . '&forum=' . Security::remove_XSS($my_forum) . $origin_string . '">' . $name_user_theme . '</a></td>';
                if ($_GET['list'] == 'qualify') {
                    $table_list.= '<td>' . $row_student_list['qualify'] . '/' . $max_qualify . '</td>';
                }
                if (api_is_allowed_to_edit(null, true)) {
                    $current_qualify_thread = show_qualify('1', $_GET['cidReq'], $my_forum, $row_student_list['user_id'], $_GET['id']);
                    $table_list.= '<td><a href="forumqualify.php?' . api_get_cidreq() . '&forum=' . Security::remove_XSS($my_forum) . '&thread=' . Security::remove_XSS($_GET['id']) . '&user=' . $row_student_list['user_id'] . '&user_id=' . $row_student_list['user_id'] . '&idtextqualify=' . $current_qualify_thread . '&origin=' . $origin . '">' . icon('../img/' . $icon_qualify, get_lang('Qualify')) . '</a></td></tr>';
                }
                $counter_stdlist++;
            }
        } else {
            if ($_GET['list'] == 'qualify') {
                $table_list.='<tr><td colspan="2">' . get_lang('ThereIsNotQualifiedLearners') . '</td></tr>';
            } else {
                $table_list.='<tr><td colspan="2">' . get_lang('ThereIsNotUnqualifiedLearners') . '</td></tr>';
            }
        }

        $table_list.= '</table></div>';
        $table_list .= '<br /></div>';
    } else {
        $table_list .= get_lang('NoParticipation');
    }
}


/*
  -----------------------------------------------------------
  Is the user allowed here?
  -----------------------------------------------------------
 */
// if the user is not a course administrator and the forum is hidden
// then the user is not allowed here.
/* if (!api_is_allowed_to_edit(false,true) AND ($current_forum_category['visibility']==0 OR $current_forum['visibility']==0)) {
  forum_not_allowed_here();
  } */

if (!api_is_allowed_to_edit(false, true)) {
    if ($current_forum['forum_category'] <> 0 AND ($current_forum_category['visibility'] == 0 OR $current_forum['visibility'] == 0)) {
        forum_not_allowed_here();
    }
}

if ($origin == 'learnpath') {
    echo '<div style="height:15px">&nbsp;</div>';
}



// getting all the information that is new for this user
get_whats_new();
$whatsnew_post_info = array();
$whatsnew_post_info = $_SESSION['whatsnew_post_info'];


/*
  -----------------------------------------------------------
  Display the action messages
  -----------------------------------------------------------
 */
if (!empty($message)) {
    //Display :: display_confirmation_message($message);
    $_SESSION["display_confirmation_message"] = $message;
}


/*
  -----------------------------------------------------------
  Action Links
  -----------------------------------------------------------
 */
if ($origin != 'learnpath') {
    echo '<div class="actions">';
    if (empty($_SESSION['toolgroup'])) {
        echo '<span style="float:right;">' . search_link() . '</span>';
    }
    if (!empty($_SESSION['toolgroup']) || !empty($_GET['gidReq'])) {
        echo '<a href="../group/group_space.php?' . str_replace('&', '&amp;', api_get_cidreq()) . '&amp;group_id=' . $_SESSION['toolgroup'] . '">' . Display::return_icon('pixel.gif', get_lang('Groups'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Groups') . '</a>';
    } else {
        if ($_REQUEST['id_session'] == '')
            echo '<a href="index.php?cidReq=' . $_course[id] . '">' . Display::return_icon('pixel.gif', get_lang('BackToForumOverview'), array('class' => 'toolactionplaceholdericon toolactionback')) . ' ' . get_lang('BackToForumOverview') . '</a>';
        else
            echo '<a href="index.php?cidReq=' . $_course[id] . '&id_session=' . $_REQUEST['id_session'] . '">' . Display::return_icon('pixel.gif', get_lang('BackToForumOverview'), array('class' => 'toolactionplaceholdericon toolactionback')) . ' ' . get_lang('BackToForumOverview') . '</a>';
    }
    // The link should appear when
    // 1. the course admin is here
    // 2. the course member is here and new threads are allowed
    // 3. a visitor is here and new threads AND allowed AND  anonymous posts are allowed
    if (api_is_allowed_to_edit(false, true) OR ($current_forum['allow_new_threads'] == 1 AND isset($_user['user_id'])) OR ($current_forum['allow_new_threads'] == 1 AND !isset($_user['user_id']) AND $current_forum['allow_anonymous'] == 1)) {
        if ($current_forum['locked'] <> 1 AND $current_forum['locked'] <> 1) {
            if (!api_is_anonymous()) {
                if ($my_forum == strval(intval($my_forum))) {
                    echo '<a href="newthread.php?' . str_replace('&', '&amp;', api_get_cidreq()) . '&amp;forum=' . Security::remove_XSS($my_forum) . $origin_string . '">' . Display::return_icon('pixel.gif', get_lang('NewTopic'), array('class' => 'toolactionplaceholdericon toolactionsinvite')) . ' ' . get_lang('NewTopic') . '</a>';
                } else {
                    $my_forum = strval(intval($my_forum));
                    echo '<a href="newthread.php?' . str_replace('&', '&amp;', api_get_cidreq()) . '&amp;forum=' . $my_forum . $origin_string . '">' . Display::return_icon('pixel.gif', get_lang('NewTopic'), array('class' => 'toolactionplaceholdericon toolactionsinvite')) . ' ' . get_lang('NewTopic') . '</a>';
                }
            }
        } else {
            echo '<img src="' . api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/images/action/lock.png"/>' . get_lang('ForumLocked');
        }
    }
    echo '</div>';
}

/*
  -----------------------------------------------------------
  Display
  -----------------------------------------------------------
 */
if (isset($_SESSION["display_confirmation_message"])) {
    Display :: display_confirmation_message2($_SESSION["display_confirmation_message"], false, true);
    unset($_SESSION["display_confirmation_message"]);
}
echo '<div id="content">'; // Main content

if (isset($_SESSION["display_warning_message"])) {
    //display_warning_message
    unset($_SESSION["display_warning_message"]);
}
if (isset($_SESSION["display_error_message"])) {
    //display_error_message
    unset($_SESSION["display_error_message"]);
}
if (!empty($current_forum['forum_image'])) {
    $image_path = api_get_path(WEB_COURSE_PATH) . api_get_course_path() . '/upload/forum/images/' . $current_forum['forum_image'];
    $image_size = api_getimagesize($image_path);
    $img_attributes = '';
    if (!empty($image_size)) {
        if ($image_size[0] > 100 || $image_size[1] > 100) {
            //limit display width and height to 100px
            $img_attributes = 'width="50" height="50" class="plr5"';
        }
        $img_forum = "<img style='padding-right:5px;'  src=\"$image_path\" $img_attributes>";
    } else {
        $img_forum = "<img class='plr5'  src='" . api_get_path(WEB_IMG_PATH) . "noforum.png' width='50' height='50' >";
    }
} else {
    $img_forum = "<img class='plr5' src='" . api_get_path(WEB_IMG_PATH) . "noforum.png' width='50' height='50' >";
}
// the current forum
if ($origin != 'learnpath') {
    echo '<div class="forum_thread_content rounded" style="padding-left: 5px;">';
    echo '<table><tr><td>' . $img_forum . '</td><td>';
    if($current_forum['forum_id']==1)
        echo '<span class="forum_title">' . prepare4display(get_lang('ExampleForum')) . '</span>';
    else 
    echo '<span class="forum_title">' . prepare4display($current_forum['forum_title']) . '</span>';
    if (!empty($current_forum['forum_comment'])) {
        echo '<br><span class="forum_description fz10">' . prepare4display($current_forum['forum_comment']) . '</span>';
    }
    if (!empty($current_forum_category['cat_title'])) {
        if($current_forum_category['cat_id']==1)
            echo '<br /><span class="forum_low_description">' . prepare4display(get_lang("ExampleForumCategory")) . "</span><br />";
        else
        echo '<br /><span class="forum_low_description">' . prepare4display($current_forum_category['cat_title']) . "</span><br />";
    }
    echo '</td></tr></table>';
    echo '</div>';
}


$parameters['forum'] = $my_forum;
$parameters['cidReq'] = api_get_course_id();
$parameters['gidReq'] = $_SESSION['_gid'];

$table = new SortableTable('threads', 'get_number_of_threads', 'get_threads_data', 2, 100);
$table->set_additional_parameters($parameters);
$table->set_header(0, '');
$table->set_header(1, get_lang('Title'));
$table->set_header(2, get_lang('Replies'));
$table->set_header(3, get_lang('Views'));
$table->set_header(4, get_lang('Author'));
$table->set_header(5, get_lang('LastPost'), false);
$table->set_header(6, get_lang('Actions'), false);

$table->display();




// Display users list of the thread
echo isset($table_list) ? $table_list : '';
// Close main content
echo "</div>";
/*
  ==============================================================================
  FOOTER
  ==============================================================================
 */
if ($origin != 'learnpath') {
    Display::display_tool_footer();
}
