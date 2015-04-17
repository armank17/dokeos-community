<?php

/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2008 Dokeos SPRL
  Copyright (c) 2006 Ghent University (UGent)

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
 * 	@package dokeos.forum
 */
//delete attachment file
$origin = 'viewthread';
//if(isset($_GET['origin']))
//{
//    $origin =  Security::remove_XSS($_GET['origin']);
//}
if ((isset($_GET['action']) && $_GET['action'] == 'delete_attach') && isset($_GET['id_attach'])) {
    delete_attachment(0, $_GET['id_attach']);
}

$thread_session_id = $_GET['id_session'];
if ($thread_session_id == '')
    $thread_session_id = 0;

if (isset($current_thread['thread_id'])) {

    $rows = get_posts($current_thread['thread_id']);
    $increment = 0;

    foreach ($rows as $row) {
        if ($row['session_id'] == $thread_session_id) {
            echo "<table width=\"100%\" class=\"post\" cellspacing=\"5\" border=\"0\">\n";
            // the style depends on the status of the message: approved or not
            if ($row['visible'] == '0') {
                $titleclass = 'forum_message_post_title_2_be_approved';
                $messageclass = 'forum_message_post_text_2_be_approved';
                $leftclass = 'forum_message_left_2_be_approved';
            } else {
                $titleclass = 'forum_message_post_title';
                $messageclass = 'forum_message_post_text';
                $leftclass = 'forum_message_left';
            }

            echo "\t<tr>\n";
            echo "\t\t<td rowspan=\"3\" class=\"$leftclass\">";

            if ($row['user_id'] == '0') {
                $name = prepare4display($row['poster_name']);
            } else {
                $name = api_get_person_name($row['firstname'], $row['lastname']);
            }
            if ($origin != 'learnpath') {
                if (api_get_course_setting('allow_user_image_forum')) {
                    echo '<br />' . display_user_image($row['user_id'], $name, $origin, 'popup') . '<br />';
                }
                echo display_user_link($row['user_id'], $name, '', 'popup') . '<br />';
            } else {
                if (api_get_course_setting('allow_user_image_forum')) {
                    echo '<br />' . display_user_image($row['user_id'], $name, $origin, 'popup') . '<br />';
                }
                echo $name . '<br />';
            }
            $date = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), strtotime($row['post_date']));
            echo date('d-m-Y H:i:s', strtotime($date)). '<br /><br />';

            // get attach id
            $attachment_list = get_attachment($row['post_id']);
            $id_attach = !empty($attachment_list) ? $attachment_list['id'] : '';
            // The user who posted it can edit his thread only if the course admin allowed this in the properties of the forum
            // The course admin him/herself can do this off course always
            if (($current_forum['allow_edit'] == 1 AND $row['user_id'] == $_user['user_id'] AND !api_is_anonymous()) or (api_is_allowed_to_edit(false, true) && !(api_is_course_coach() && $current_forum['session_id'] != $_SESSION['id_session']))) {
                if (api_is_allowed_to_session_edit(false, true))
                    echo "<a href=\"editpost.php?" . api_get_cidreq() . "&forum=" . Security::remove_XSS($_GET['forum']) . "&thread=" . Security::remove_XSS($_GET['thread']) . "&post=" . $row['post_id'] . "&origin=" . $origin . "&edit=edition&id_attach=" . $id_attach . "\">" . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . "</a>&nbsp;&nbsp;&nbsp;\n";
            }

            if ($origin != 'learnpath') {
                if (api_is_allowed_to_edit(false, true) && !(api_is_course_coach() && $current_forum['session_id'] != $_SESSION['id_session'])) {
                    //echo "<a href=\"".api_get_self()."?".api_get_cidreq()."&forum=".Security::remove_XSS($_GET['forum'])."&thread=".Security::remove_XSS($_GET['thread'])."&action=delete&content=post&id=".$row['post_id']."&origin=".$origin."\" onclick=\"javascript:if(!confirm('".addslashes(api_htmlentities(get_lang("DeletePost"),ENT_QUOTES,$charset))."')) return false;\">".Display::return_icon('pixel.gif',get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'))."</a>&nbsp;&nbsp;&nbsp;\n";
                    $link = api_get_self() . "?" . api_get_cidreq() . "&forum=" . Security::remove_XSS($_GET['forum']) . "&thread=" . Security::remove_XSS($_GET['thread']) . "&action=delete&content=post&id=" . $row['post_id'] . "&origin=" . $origin;
                    $title = get_lang("ConfirmationDialog");
                    $text = get_lang("ConfirmYourChoice");
                    echo "<a href='javascript:void(0);' onclick='Alert_Confim_Delete(\"" . $link . "\",\"" . $title . "\",\"" . $text . "\");' >" . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . "</a>&nbsp;&nbsp;&nbsp;\n";
                    display_visible_invisible_icon('post', $row['post_id'], $row['visible'], array('forum' => Security::remove_XSS($_GET['forum']), 'thread' => Security::remove_XSS($_GET['thread']), 'origin' => $origin));
                    echo "&nbsp;&nbsp;&nbsp;\n";
                    if ($increment > 0) {
                        echo "<a href=\"viewthread.php?" . api_get_cidreq() . "&forum=" . Security::remove_XSS($_GET['forum']) . "&thread=" . Security::remove_XSS($_GET['thread']) . "&action=move&post=" . $row['post_id'] . "&origin=" . $origin . "\">" . Display::return_icon('pixel.gif', get_lang('MovePost'), array('class' => 'actionplaceholdericon actionworkmove')) . "</a>&nbsp;&nbsp;&nbsp;\n";
                    }
                }
            }

            $userinf = api_get_user_info($row['user_id']);
            $user_status = api_get_status_of_user_in_course($row['user_id'], api_get_course_id());
            $current_qualify_thread = show_qualify('1', $_GET['cidReq'], $_GET['forum'], $row['poster_id'], $_GET['thread']);
            if (api_is_allowed_to_edit(null, true) && $origin != 'learnpath') {
                if (isset($_GET['gradebook'])) {
                    if ($increment > 0 && $user_status != 1) {
                        $info_thread = get_thread_information(Security::remove_XSS($_GET['thread']));
                        echo "<a href=\"forumqualify.php?" . api_get_cidreq() . "&forum=" . $info_thread['forum_id'] . "&thread=" . Security::remove_XSS($_GET['thread']) . "&action=list&post=" . $row['post_id'] . "&user=" . $row['poster_id'] . "&user_id=" . $row['poster_id'] . "&origin=" . $origin . "&idtextqualify=" . $current_qualify_thread . "&gradebook=" . Security::remove_XSS($_GET['gradebook']) . "\" >" . icon('../img/checkok.png', get_lang('Qualify')) . "</a>&nbsp;&nbsp;&nbsp;\n";
                    }
                } else {
                    if ($increment > 0 && $user_status != 1) {
                        echo "<a href=\"forumqualify.php?" . api_get_cidreq() . "&forum=" . Security::remove_XSS($_GET['forum']) . "&thread=" . Security::remove_XSS($_GET['thread']) . "&action=list&post=" . $row['post_id'] . "&user=" . $row['poster_id'] . "&user_id=" . $row['poster_id'] . "&origin=" . $origin . "&idtextqualify=" . $current_qualify_thread . "\" >" . icon('../img/checkok.png', get_lang('Qualify')) . "</a>&nbsp;&nbsp;&nbsp;\n";
                    }
                }
            }
            //echo '<br /><br />';
            if ($current_forum_category['locked'] == 0 AND $current_forum['locked'] == 0 AND $current_thread['locked'] == 0 OR api_is_allowed_to_edit(false, true)) {
                if ($_user['user_id'] OR ($current_forum['allow_anonymous'] == 1 AND !$_user['user_id']) OR api_is_allowed_to_session_edit(false, true)) {
                    if (!api_is_anonymous() && api_is_allowed_to_session_edit(false, true)) {
                        echo '<a href="reply.php?' . api_get_cidreq() . '&forum=' . Security::remove_XSS($_GET['forum']) . '&thread=' . Security::remove_XSS($_GET['thread']) . '&post=' . $row['post_id'] . '&action=replymessage&origin=' . $origin . '">' . Display::return_icon('pixel.gif', get_lang('ReplyToMessage'), array('class' => 'actionplaceholdericon actionsmessagereply')) . "</a>&nbsp;&nbsp;&nbsp;\n";
                        echo '<a href="reply.php?' . api_get_cidreq() . '&forum=' . Security::remove_XSS($_GET['forum']) . '&thread=' . Security::remove_XSS($_GET['thread']) . '&post=' . $row['post_id'] . '&action=quote&origin=' . $origin . '">' . Display :: return_icon('quote.gif', get_lang('QuoteMessage')) . "</a>&nbsp;&nbsp;&nbsp;\n";
                    }
                }
            } else {
                if ($current_forum_category['locked'] == 1) {
                    echo get_lang('ForumcategoryLocked') . '<br />';
                }
                if ($current_forum['locked'] == 1) {
                    echo get_lang('ForumLocked') . '<br />';
                }
                if ($current_thread['locked'] == 1) {
                    echo get_lang('ThreadLocked') . '<br />';
                }
            }

            echo "</td>\n";
            // show the
            if (isset($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']][$row['post_id']]) and !empty($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']][$row['post_id']]) and !empty($whatsnew_post_info[$_GET['forum']][$row['thread_id']])) {
                $post_image = icon('../img/forumpostnew.gif');
            } else {
                $post_image = icon('../img/forumpost.gif');
            }
            if ($row['post_notification'] == '1' AND $row['poster_id'] == $_user['user_id']) {
                $post_image.=icon('../img/forumnotification.gif', get_lang('YouWillBeNotified'));
            }


            // The post title
            echo "\t\t<td class=\"$titleclass\">" . prepare4display($row['post_title']) . "</td>\n";
            echo "\t</tr>\n";

            // The post message
            echo "\t<tr>\n";
            echo "\t\t<td class=\"$messageclass\">" . prepare4display($row['post_text']) . "</td>\n";
            echo "\t</tr>\n";

            // The reply thread
            if ($_user['user_id'] OR ($current_forum['allow_anonymous'] == 1 AND !$_user['user_id'])) {
                //reply link
                if (!api_is_anonymous()) {
                    echo "\t<tr>\n";
                    echo "\t\t<td class=\"$messageclass\">";

                    if (($current_forum['allow_edit'] == 1 AND $row['user_id'] == $_user['user_id'] AND !api_is_anonymous()) or (api_is_allowed_to_edit(false, true) && !(api_is_course_coach() && $current_forum['session_id'] != $_SESSION['id_session']))) {
                        if (api_is_allowed_to_session_edit(false, true)) {
                            echo "<form method='post' action='reply.php?" . api_get_cidreq() . '&forum=' . Security::remove_XSS($_GET['forum']) . '&thread=' . Security::remove_XSS($_GET['thread']) . '&action=replythread&origin=' . $origin . "'>	
                </form>";
                        }
                    } else {
                        if ($current_forum_category['locked'] <> 1) {
                            if ($current_forum['locked'] <> 1) {
                                if ($current_thread['locked'] <> 1) {
                                    echo "<form method='post' action='reply.php?" . api_get_cidreq() . '&forum=' . Security::remove_XSS($_GET['forum']) . '&thread=' . Security::remove_XSS($_GET['thread']) . '&action=replythread&origin=' . $origin . "'>	
                    <button type='submit' >" . get_lang('ReplyToThread') . "</button></form>";
                                } else {
                                    echo "<button type='submit' >" . get_lang('ThreadLocked') . "</button>";
                                }
                            } else {
                                echo "<button type='submit' >" . get_lang('ForumLocked') . "</button>";
                            }
                        } else {
                            echo "<button type='submit' >" . get_lang('ForumcategoryLocked') . "</button>";
                        }
                    }
                    echo "</td>\n";
                    echo "\t</tr>\n";
                }
            }

            // The check if there is an attachment
            $attachment_list = get_attachment($row['post_id']);

            if (!empty($attachment_list)) {
                echo '<tr><td height="50%" width="150px">';
                $realname = $attachment_list['path'];
                $user_filename = $attachment_list['filename'];

                echo Display::return_icon('attachment.gif', get_lang('Attachment'));
                echo '<a href="download.php?file=';
                echo $realname;
                echo ' "> ' . $user_filename . ' </a>';
                echo '<span class="forum_attach_comment" >' . $attachment_list['comment'] . '</span>';
                if (($current_forum['allow_edit'] == 1 AND $row['user_id'] == $_user['user_id']) or (api_is_allowed_to_edit(false, true) && !(api_is_course_coach() && $current_forum['session_id'] != $_SESSION['id_session']))) {
                    echo '&nbsp;&nbsp;<a href="' . api_get_self() . '?' . api_get_cidreq() . '&origin=' . Security::remove_XSS($_GET['origin']) . '&action=delete_attach&id_attach=' . $attachment_list['id'] . '&forum=' . Security::remove_XSS($_GET['forum']) . '&thread=' . Security::remove_XSS($_GET['thread']) . '" onclick="javascript:if(!confirm(\'' . addslashes(api_htmlentities(get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . '\')) return false;">' . Display::return_icon('delete_link.png', get_lang('Delete')) . '</a><br />';
                }
                echo '</td></tr>';
            }

            // The post has been displayed => it can be removed from the what's new array
            unset($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']][$row['post_id']]);
            unset($_SESSION['whatsnew_post_info'][$current_forum['forum_id']][$current_thread['thread_id']][$row['post_id']]);
            // and we also add it to the whatsnew_post_viewed session so that whenever a new topic arrives (while being logged in)
            // this one is no longer marked ad new (because we have already seen it)
            $_SESSION['whatsnew_post_viewed'][$current_forum['forum_id']][$current_thread['thread_id']][$row['post_id']] = true;

            echo "</table>";
            $increment++;
        }
    }
    // the thread has been displayed => it can be removed if its emtpy (will not be the case if there are multiple pages of posts (long threads))
    if (empty($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']])) {
        unset($whatsnew_post_info[$current_forum['forum_id']][$current_thread['thread_id']]);
        unset($_SESSION['whatsnew_post_info'][$current_forum['forum_id']][$current_thread['thread_id']]);
    }
}