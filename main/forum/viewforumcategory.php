<?php

// $Id: document.php 16494 2008-10-10 22:07:36Z yannoo $

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
$language_file = 'forum';
// including the global dokeos init file
require '../inc/global.inc.php';
require '../inc/lib/timezone.lib.php';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_CODE_PATH).'inc/lib/javascript/jquery.js" ></script>';
//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.ui.all.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript">
	$(document).ready(function(){ $(\'.hide-me\').slideUp() });
	function hidecontent(content){ $(content).slideToggle(\'normal\'); }
	</script>';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript">

		function advanced_parameters() {
			if(document.getElementById(\'options\').style.display == \'none\') {
					document.getElementById(\'options\').style.display = \'block\';
					document.getElementById(\'plus_minus\').innerHTML=\'&nbsp;' . Display::return_icon('div_hide.gif', get_lang('Hide'), array('style' => 'vertical-align:middle')) . '&nbsp;' . get_lang('AdvancedParameters') . '\';
			} else {
					document.getElementById(\'options\').style.display = \'none\';
					document.getElementById(\'plus_minus\').innerHTML=\'&nbsp;' . Display::return_icon('div_show.gif', get_lang('Show'), array('style' => 'vertical-align:middle')) . '&nbsp;' . get_lang('AdvancedParameters') . '\';
			}
		}
	</script>';

// the section (tabs)
$this_section = SECTION_COURSES;

// notice for unauthorized people.
api_protect_course_script(true);

// including additional library scripts
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
include_once (api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php');
$nameTools = get_lang('Forum');

/*
  -----------------------------------------------------------
  Including necessary files
  -----------------------------------------------------------
 */
require 'forumconfig.inc.php';
require_once 'forumfunction.inc.php';


/*
  ==============================================================================
  MAIN DISPLAY SECTION
  ==============================================================================
 */


/*
  -----------------------------------------------------------
  Header and Breadcrumbs
  -----------------------------------------------------------
 */
if (isset($_SESSION['gradebook'])) {
    $gradebook = $_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook == 'view') {
    $interbreadcrumb[] = array(
        'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
        'name' => get_lang('Gradebook')
    );
}

$current_forum_category = get_forum_categories($_GET['forumcategory']);
$interbreadcrumb[] = array("url" => "index.php?gradebook=$gradebook&amp;search=" . Security::remove_XSS(urlencode(isset($_GET['search']) ? $_GET['search'] : '')), "name" => $nameTools);
$interbreadcrumb[] = array("url" => "viewforumcategory.php?forumcategory=" . $current_forum_category['cat_id'] . "&amp;origin=" . $origin . "&amp;search=" . Security::remove_XSS(urlencode(isset($_GET['search']) ? $_GET['search'] : '')), "name" => prepare4display($current_forum_category['cat_title']));


if (!empty($_GET['action']) && !empty($_GET['content'])) {
    if ($_GET['action'] == 'add' && $_GET['content'] == 'forum') {
        $interbreadcrumb[] = array("url" => api_get_self() . '?' . api_get_cidreq() . '&amp;action=add&amp;content=forum', 'name' => get_lang('AddForum'));
    }
}

//are we in a lp ?
$origin = '';
if (isset($_GET['origin'])) {
    $origin = Security::remove_XSS($_GET['origin']);
}

if ($origin == 'learnpath') {
    include(api_get_path(INCLUDE_PATH) . 'reduced_header.inc.php');
} else {
    //Display :: display_tool_header(null);
    //api_display_tool_title($nameTools);
    Display::display_tool_header();
}


/*
  ------------------------------------------------------------------------------------------------------
  ACTIONS
  ------------------------------------------------------------------------------------------------------
 */
$whatsnew_post_info = $_SESSION['whatsnew_post_info'];

/*
  -----------------------------------------------------------
  Is the user allowed here?
  -----------------------------------------------------------
 */
// if the user is not a course administrator and the forum is hidden
// then the user is not allowed here.
if (!api_is_allowed_to_edit(false, true) AND $current_forum_category['visibility'] == 0) {
    forum_not_allowed_here();
}

/*
  -----------------------------------------------------------
  Action Links
  -----------------------------------------------------------
 */
echo '<div class="actions">';
echo '<span style="float:right;">' . search_link() . '</span>';
echo '<a href="index.php?gradebook=' . $gradebook . '">' . Display::return_icon('pixel.gif', get_lang('BackToForumOverview'), array('class' => 'toolactionplaceholdericon toolactionback')) . ' ' . get_lang('BackToForumOverview') . '</a>';
if (api_is_allowed_to_edit(false, true)) {
    //echo '<a href="'.api_get_self().'?forumcategory='.$_GET['forumcategory'].'&amp;action=add&amp;content=forumcategory">'.get_lang('AddForumCategory').'</a> | ';
    echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;forumcategory=' . Security::remove_XSS($_GET['forumcategory']) . '&amp;action=add&amp;content=forum"> ' . Display::return_icon('pixel.gif', get_lang('AddForum'), array('class' => 'toolactionplaceholdericon toolactionnewforum')) . ' ' . get_lang('AddForum') . '</a>';
    //echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;forumcategory='.Security::remove_XSS($_GET['forumcategory']).'&amp;action=add&amp;content=forum">'.Display::return_icon('forum_new.gif', get_lang('AddForum')).' '.get_lang('AddForum').'</a>';
}
echo '</div>';

$table_forums = Database::get_course_table(TABLE_FORUM);

$action = $_GET['action'];
$disporder = $_GET['disporder'];

if ($action == "updateRecordsListings") {
    $disparr = explode(",", $disporder);
    $len = sizeof($disparr);
    $listingCounter = 1;
    for ($i = 0; $i < sizeof($disparr); $i++) {
        $sql = "UPDATE $table_forums SET forum_order=" . $listingCounter . " WHERE forum_id = " . $disparr[$i];
        $res = Database::query($sql, __FILE__, __LINE__);
        $listingCounter = $listingCounter + 1;
    }
    echo '<script type="text/javascript">window.location.href = "viewforumcategory.php?' . api_get_cidReq() . '&forumcategory=' . $_GET['forumcategory'] . '";</script>';
}

/*
  ------------------------------------------------------------------------------------------------------
  ACTIONS
  ------------------------------------------------------------------------------------------------------
 */
$action_forums = isset($_GET['action']) ? $_GET['action'] : '';
if (api_is_allowed_to_edit(false, true)) {
    //if ($action_forums == 'add')
    if (in_array($action_forums, array('add', 'edit'))) {
        echo '<div id="content">';
    }
    handle_forum_and_forumcategories();
    if (in_array($action_forums, array('add', 'edit'))) {
        echo '</div>';
    }
}

if ($action_forums != 'add') {
    /*
      ------------------------------------------------------------------------------------------------------
      RETRIEVING ALL THE FORUM CATEGORIES AND FORUMS
      ------------------------------------------------------------------------------------------------------
      note: we do this here just after het handling of the actions to be sure that we already incorporate the
      latest changes
     */
    // Step 1: We store all the forum categories in an array $forum_categories
    $forum_categories = array();
    $forum_category = get_forum_categories($_GET['forumcategory']);
    // step 2: we find all the forums
    $forum_list = array();
    $forum_list = get_forums();
    /*
      ------------------------------------------------------------------------------------------------------
      RETRIEVING ALL GROUPS AND THOSE OF THE USER
      ------------------------------------------------------------------------------------------------------
     */
    // the groups of the user
    $groups_of_user = array();
    $groups_of_user = GroupManager::get_group_ids($_course['dbName'], $_user['user_id']);
    // all groups in the course (and sorting them as the id of the group = the key of the array
    $all_groups = GroupManager::get_group_list();
    if (is_array($all_groups)) {
        foreach ($all_groups as $group) {
            $all_groups[$group['id']] = $group;
        }
    }

    /*
      ------------------------------------------------------------------------------------------------------
      CLEAN GROUP ID FOR AJAXFILEMANAGER
      ------------------------------------------------------------------------------------------------------
     */
    if (isset($_SESSION['_gid'])) {
        unset($_SESSION['_gid']);
    }

    /*
      -----------------------------------------------------------
      Display Forum Categories and the Forums in it
      -----------------------------------------------------------
     */
    $draggable = "";
    if (api_is_allowed_to_edit()) {
        $draggable = "draggable";
        echo '<script type="text/javascript">
    /*<![CDATA[*/
	$(document).ready(function(){
		$(function() {
			$("#contentLeft ul").sortable({ 
                        opacity: 0.6,
                        handle : $("#ddrag"),
                        //cancel: ".nodrag", 
                        cursor: "move", 
                        update: function() {
				var order = $(this).sortable("serialize") + "&action=updateRecordsListings";
				var record = order.split("&");
                var recordlen = record.length;
                var disparr = new Array();
                for(var i=0;i<(recordlen-1);i++){
                    var recordval = record[i].split("=");
                    disparr[i] = recordval[1];
                }
                window.location.href = "viewforumcategory.php?action=updateRecordsListings&disporder="+disparr+"&forumcategory=' . Security::remove_XSS($_GET['forumcategory']) . '";
			}});
		});
	});
    /*]]>*/
	</script>';
    }
    echo '<div id="content">';

    // notification
    if ($action_forums == 'notify' AND isset($_GET['content']) AND isset($_GET['id'])) {
        $return_message = set_notification($_GET['content'], $_GET['id']);
        Display :: display_confirmation_message($return_message, false, true);
    }

    echo "<table class=\"data_table\" width='100%'>\n";
    $my_session = isset($_SESSION['id_session']) ? $_SESSION['id_session'] : null;
    $forum_categories_list = '';

    $i = 1;
    echo '<table class="data_table">';
    echo "\t<tr>\n";
    if (api_is_allowed_to_edit())
        echo "<th width='5%'>" . get_lang('Move') . "</th>";
    echo "\t\t<th width='30%' colspan='2'>" . get_lang('Forum') . "</th>\n";
    echo "\t\t<th width='8%'>" . get_lang('Topics') . "</th>\n";
    echo "\t\t<th width='8%'>" . get_lang('Posts') . "</th>\n";
    echo "\t\t<th width='19%'>" . get_lang('LastPosts') . "</th>\n";
    echo "\t\t<th>" . get_lang('Actions') . "</th>\n";
    echo "\t</tr>\n";
    echo '</table>';

    echo '<div id="contentWrap"><div id="contentLeft"><ul class="dragdrop">';
    foreach ($forum_list as $key => $forum) {
        // Here we clean the whatnew_post_info array a little bit because to display the icon we
        // test if $whatsnew_post_info[$forum['forum_id']] is empty or not.
        if (!empty($whatsnew_post_info)) {
            if (is_array(isset($whatsnew_post_info[$forum['forum_id']]) ? $whatsnew_post_info[$forum['forum_id']] : null)) {
                foreach ($whatsnew_post_info[$forum['forum_id']] as $key_thread_id => $new_post_array) {
                    if (empty($whatsnew_post_info[$forum['forum_id']][$key_thread_id])) {
                        unset($whatsnew_post_info[$forum['forum_id']][$key_thread_id]);
                        unset($_SESSION['whatsnew_post_info'][$forum['forum_id']][$key_thread_id]);
                    }
                }
            }
        }
        // note: this can be speeded up if we transform the $forum_list to an array that uses the forum_category as the key.
        if (prepare4display($forum['forum_category']) == prepare4display($forum_category['cat_id'])) {
            // the forum has to be showed if
            // 1.v it is a not a group forum (teacher and student)
            // 2.v it is a group forum and it is public (teacher and student)
            // 3. it is a group forum and it is private (always for teachers only if the user is member of the forum
            // if the forum is private and it is a group forum and the user is not a member of the group forum then it cannot be displayed
            //if (!($forum['forum_group_public_private']=='private' AND !is_null($forum['forum_of_group']) AND !in_array($forum['forum_of_group'], $groups_of_user)))
            //{
            $show_forum = false;

            // SHOULD WE SHOW THIS PARTICULAR FORUM
            // you are teacher => show forum

            if (api_is_allowed_to_edit(false, true)) {
                //echo 'teacher';
                $show_forum = true;
            } else {// you are not a teacher
                //echo 'student';
                // it is not a group forum => show forum (invisible forums are already left out see get_forums function)
                if ($forum['forum_of_group'] == '0') {
                    //echo '-gewoon forum';
                    $show_forum = true;
                } else {
                    // it is a group forum
                    //echo '-groepsforum';
                    // it is a group forum but it is public => show
                    if ($forum['forum_group_public_private'] == 'public') {
                        $show_forum = true;
                        //echo '-publiek';
                    } else if ($forum['forum_group_public_private'] == 'private') {
                        // it is a group forum and it is private
                        //echo '-prive';
                        // it is a group forum and it is private but the user is member of the group
                        if (in_array($forum['forum_of_group'], $groups_of_user)) {
                            //echo '-is lid';
                            $show_forum = true;
                        } else {
                            //echo '-is GEEN lid';
                            $show_forum = false;
                        }
                    } else {
                        $show_forum = false;
                    }
                }
            }

            //		echo '<li id="recordsArray_'.$form_count.'"><table><tr><td>God</td></tr></table></li>';
            echo '<li id="recordsArray_' . $forum['forum_id'] . '" class="' . $draggable . '"><table class="data_table" width="100%">';

            if ($i % 2 == 0)
                $css_class = 'row_odd';
            else
                $css_class = 'row_even';
            $i++;
            if ($show_forum) {
                $form_count++;
                $mywhatsnew_post_info = isset($whatsnew_post_info[$forum['forum_id']]) ? $whatsnew_post_info[$forum['forum_id']] : null;
                echo "\t<tr class='" . $css_class . "'>\n";
                if (api_is_allowed_to_edit())
                    echo "<td width='5%' id='ddrag'><img src='../img/drag-and-drop.png'></td>";
                // Showing the image
                if (!empty($forum['forum_image'])) {

                    $image_path = api_get_path(WEB_COURSE_PATH) . api_get_course_path() . '/upload/forum/images/' . $forum['forum_image'];
                    $image_size = api_getimagesize($image_path);

                    $img_attributes = '';
                    if (!empty($image_size)) {
                        if ($image_size[0] > 100 || $image_size[1] > 100) {
                            //limit display width and height to 100px
                            $img_attributes = 'width="100" height="100"';
                        }
                        echo "<img src=\"$image_path\" $img_attributes>";
                    }
                }
                echo "\t\t<td width='30%' colspan=\"2\" align=\"left\">";
                if ($forum['forum_of_group'] !== '0') {
                    if (is_array($mywhatsnew_post_info) and !empty($mywhatsnew_post_info)) {
                        echo icon('../img/forumgroupnew_22.png');
                    } else {
                        echo icon('../img/forumgroup.png', get_lang('GroupForum'));
                    }
                } else {

                    if (is_array($mywhatsnew_post_info) and !empty($mywhatsnew_post_info)) {
                        //echo icon('../img/forum_new.png', get_lang('Forum'));
                        echo Display::return_icon('pixel.gif', get_lang('Forum'), array('class' => 'actionplaceholdericon actionforum'));
                    } else {
                        //echo icon('../img/forum_middle.png');
                        echo Display::return_icon('pixel.gif', get_lang('Forum'), array('class' => 'actionplaceholdericon actionforum'));
                    }
                }
                //	echo "</td>\n";
            }
            //validacion when belongs to a session
            $session_img = api_get_session_image($forum['session_id'], $_user['status']);

            if ($forum['forum_of_group'] <> '0') {
                $my_all_groups_forum_name = isset($all_groups[$forum['forum_of_group']]['name']) ? $all_groups[$forum['forum_of_group']]['name'] : null;
                $my_all_groups_forum_id = isset($all_groups[$forum['forum_of_group']]['id']) ? $all_groups[$forum['forum_of_group']]['id'] : null;
                $group_title = api_substr($my_all_groups_forum_name, 0, 30);
                $forum_title_group_addition = ' (<a href="../group/group_space.php?' . api_get_cidreq() . '&amp;gidReq=' . $forum['forum_of_group'] . '" class="forum_group_link">' . get_lang('GoTo') . ' ' . $group_title . '</a>)' . $session_img;
            } else {
                $forum_title_group_addition = '';
            }

            if ((!isset($_SESSION['id_session']) || $_SESSION['id_session'] == 0) && !empty($forum['session_name'])) {
                $session_displayed = ' (' . $forum['session_name'] . ')';
            } else {
                $session_displayed = '';
            }
            $forum['forum_of_group'] == 0 ? $groupid = '' : $groupid = $forum['forum_of_group'];
            if($forum['forum_id']==1)
                echo "<a href=\"viewforum.php?" . api_get_cidreq() . "&amp;gidReq=" . Security::remove_XSS($groupid) . "&amp;forum=" . prepare4display($forum['forum_id']) . "\" " . class_visible_invisible(prepare4display($forum['visibility'])) . ">" . prepare4display(get_lang('ExampleForum')) . $session_displayed . '</a>' . $forum_title_group_addition . '<br />' . prepare4display($forum['forum_comment']) . "</td>\n";
            else
            echo "<a href=\"viewforum.php?" . api_get_cidreq() . "&amp;gidReq=" . Security::remove_XSS($groupid) . "&amp;forum=" . prepare4display($forum['forum_id']) . "\" " . class_visible_invisible(prepare4display($forum['visibility'])) . ">" . prepare4display($forum['forum_title']) . $session_displayed . '</a>' . $forum_title_group_addition . '<br />' . prepare4display($forum['forum_comment']) . "</td>\n";
            $number_threads = isset($forum['number_of_threads']) ? $forum['number_of_threads'] : null;
            $number_posts = isset($forum['number_of_posts']) ? $forum['number_of_posts'] : null;
            echo "\t\t<td width='8%'>" . $number_threads . "</td>\n";
            echo "\t\t<td width='8%'>" . $number_posts . "</td>\n";
            // the last post in the forum
            if ($forum['last_poster_name'] <> '') {
                $name = $forum['last_poster_name'];
                $poster_id = 0;
            } else {
                $name = api_get_person_name($forum['last_poster_firstname'], $forum['last_poster_lastname']);
                $poster_id = $forum['last_poster_id'];
            }
            echo "\t\t<td width='19%'>";
            if (!empty($forum['last_post_id'])) {
                echo TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), strtotime($forum['last_post_date'])) . "<br /> " . get_lang('By') . ' ' . display_user_link($poster_id, $name);
            }
            echo "</td>\n";
            echo "\t\t<td width='35%' align='center'>";
            echo "<table><tr>";
            if (api_is_allowed_to_edit(false, true) && !($forum['session_id'] == 0 && intval($session_id) != 0)) {
                echo "<td width='5%' align='center'><a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&amp;gradebook=$gradebook&amp;action=edit&amp;content=forum&amp;id=" . $forum['forum_id'] . "&amp;forumcategory=" . intval($_GET['forumcategory']) . "\">" . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . "</a></td>";
                $link = api_get_path(WEB_CODE_PATH) . "forum/index.php?" . api_get_cidreq() . "&amp;gradebook=$gradebook&amp;action=delete&amp;content=forum&amp;id=" . $forum['forum_id'] . "&amp;forumcategory=" . intval($_GET['forumcategory']);
                $title = get_lang("ConfirmationDialog");
                $text = get_lang("ConfirmYourChoice");
                echo "<td width='5%' align='center'><a href='javascript:void(0);' onclick='Alert_Confim_Delete(\"" . $link . "\",\"" . $title . "\",\"" . $text . "\");'>" . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . "</a></td>";
                echo "<td width='5%' align='center'>";
                display_visible_invisible_icon('forum', $forum['forum_id'], $forum['visibility']);
                echo "</td><td width='5%' align='center'>";
                display_lock_unlock_icon('forum', $forum['forum_id'], $forum['locked']);
                echo "</td>";
                //	display_up_down_icon('forum',$forum['forum_id'], $forums_in_category);
            }
            $iconnotify = Display::return_icon('pixel.gif', get_lang('NotifyMe'), array('class' => 'actionplaceholdericon actionsmessage'));
            $session_forum_noti = isset($_SESSION['forum_notification']['forum']) ? $_SESSION['forum_notification']['forum'] : false;
            if (is_array($session_forum_noti)) {
                if (in_array($forum['forum_id'], $session_forum_noti)) {
                    $iconnotify = Display::return_icon('pixel.gif', get_lang('NotifyMe'), array('class' => 'actionplaceholdericon actionsmessagereply'));
                }
            }

            if (!api_is_anonymous() && api_is_allowed_to_session_edit(false, true)) {
                echo "<td width='5%' align='center'><a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&amp;gradebook=$gradebook&amp;action=notify&amp;content=forum&amp;id=" . $forum['forum_id'] . "&amp;forumcategory=" . intval($_GET['forumcategory']) . "\">" . $iconnotify . "</a></td>";
            }
            echo "</tr></table></td>\n";
            echo "\t</tr>\n";

            echo '</table></li>';
        }// if allowed to edit
    } //foreach loop
    echo '</ul></div></div>';
    //	echo '</td></tr>';
    if (count($forum_list) == 0) {
        echo "<table><tr><td>" . get_lang('NoForumInThisCategory') . "</td></tr></table>";
    }
//	echo "</table>\n";
    echo '</div>';
}
/*
  ==============================================================================
  FOOTER
  ==============================================================================
 */
// footer
if ($origin != 'learnpath') {
    Display :: display_footer();
}