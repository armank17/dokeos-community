<?php

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	@package dokeos.user
  ==============================================================================
 */
// name of the language file that needs to be included
$language_file = array('registration', 'admin', 'userInfo','course_home');

// including the global Dokeos file
require_once '../inc/global.inc.php';

// the section (for the tabs)
$this_section = SECTION_COURSES;

// including additional libraries
require_once api_get_path(LIBRARY_PATH) . 'debug.lib.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'export.lib.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'sortabletable.class.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'sessionmanager.lib.php';

$use_anonymous = true;

// notice for unauthorized people.
api_protect_course_script(true);

//CHECK KEYS
if (!isset($_cid)) {
    header('location: ' . $_configuration['root_web']);
}
$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
$htmlHeadXtra[] = '<script  type="text/javascript" src="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';

//adding additional javascript and css
// Load jquery library
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js"></script>';
//$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="screen" />';
// Save the message for the chat invitation
$htmlHeadXtra[] = '<script type="text/javascript">
    function closeWindow(){   
            $("div[role=\'dialog\']").html("close");
        }
  $(document).ready(function (){
    $(".chat_with_me").click(function(){
    	var id = $(this).attr("id");
    	var user_data = new Array();
    	var user_info = id.split("chat_with_");
    	_user_receiver_id = user_info[1];// Get the user(receiver) ID
		$.ajax({
			type: "GET",
			url: "user_ajax_requests.php?' . api_get_cidreq() . '&amp;action=send&user_receiver_id="+_user_receiver_id,
			success: function(msg){}
		})
    });
  });
  
    function runDialog(idphoto){
        //$("#"+idphoto).dialog({
        $("div[id^="+idphoto+"]").dialog({
                modal: true,
                width: 230,
                height: 270,
                resizable: false,
                open: function(event,e){
                            $(".ui-icon-closethick").css("display","none");                   
                            $("#closeButton").remove();              
                            $("div[class=\"ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix\"]").append("<span id=\"closeButton\" style=\"float:right;cursor:pointer;margin-right:4px;\" onclick=closeWindow()>'
                                                    .get_lang("Close").
                                                        '</span>"); 
                }
         }); $(".ui-corner-all").removeClass("ui-dialog-titlebar-close");
            }
</script>';
$is_western_name_order = api_is_western_name_order();
$sort_by_first_name = api_sort_by_first_name();

/* --------------------------------------
  Unregistering a user section
  --------------------------------------
 */
if (api_is_allowed_to_edit()) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'unsubscribe' :
                // Make sure we don't unsubscribe current user from the course

                if (is_array($_POST['user'])) {
                    $user_ids = array_diff($_POST['user'], array($_user['user_id']));
                    if (count($user_ids) > 0) {
                        CourseManager::unsubscribe_user($user_ids, $_SESSION['_course']['sysCode']);
                        $message = get_lang('UsersUnsubscribed');
                    }
                }
        }
    }
}

if (api_is_allowed_to_edit()) {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'makeTeacherStudent':
                $table_course_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);
                $sql = "UPDATE " . $table_course_user . " SET status=5 WHERE user_id=" . Database::escape_string(Security::Remove_XSS($_GET['user_id'])) . " AND course_code='" . api_get_course_id() . "'";
                Database::query($sql);
                break;
            case 'makeStudentTeacher':
                $table_course_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);
                $sql = "UPDATE " . $table_course_user . " SET status=1 WHERE user_id=" . Database::escape_string(Security::Remove_XSS($_GET['user_id'])) . " AND course_code='" . api_get_course_id() . "'";
                Database::query($sql);
                break;
            case 'export' :
                $table_course_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);
                $table_users = Database::get_main_table(TABLE_MAIN_USER);
                $session_id = 0;
                $is_western_name_order = api_is_western_name_order(PERSON_NAME_DATA_EXPORT);

                $data = array();
                $a_users = array();

                // users subscribed to the course through a session
                if (api_get_setting('use_session_mode') == 'true') {
                    $session_id = intval($_SESSION['id_session']);
                    $table_session_course_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
                    $sql_query = "SELECT DISTINCT user.user_id, " . ($is_western_name_order ? "user.firstname, user.lastname" : "user.lastname, user.firstname") . ", user.email, user.official_code
				  FROM $table_session_course_user as session_course_user, $table_users as user
				  WHERE `course_code` = '" . Database::escape_string($_course['sysCode']) . "' AND session_course_user.id_user = user.user_id ";
                    if ($session_id != 0) {
                        $sql_query .= ' AND id_session = ' . $session_id;
                    }
                    $sql_query .= $sort_by_first_name ? ' ORDER BY user.firstname, user.lastname' : ' ORDER BY user.lastname, user.firstname';
                    $rs = Database::query($sql_query, __FILE__, __LINE__);
                    while ($user = Database:: fetch_array($rs, 'ASSOC')) {
                        $data[] = $user;
                        $a_users[$user['user_id']] = $user;
                    }
                }

                if ($session_id == 0) {
                    // users directly subscribed to the course
                    $table_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
                    $sql_query = "SELECT DISTINCT user.user_id, " . ($is_western_name_order ? "user.firstname, user.lastname" : "user.lastname, user.firstname") . ", user.email, user.official_code
				  FROM $table_course_user as course_user, $table_users as user WHERE `course_code` = '" . Database::escape_string($_course['sysCode']) . "' AND course_user.user_id = user.user_id " . ($sort_by_first_name ? "ORDER BY user.firstname, user.lastname" : "ORDER BY user.lastname, user.firstname");
                    $rs = Database::query($sql_query, __FILE__, __LINE__);
                    while ($user = Database::fetch_array($rs, 'ASSOC')) {
                        $data[] = $user;
                        $a_users[$user['user_id']] = $user;
                    }
                }

                switch ($_GET['type']) {
                    case 'csv' :
                        Export::export_table_csv($a_users);
                    case 'xls' :
                        Export::export_table_xls($a_users);
                }
        }
    }
} // end if allowed to edit

if (api_is_allowed_to_edit()) {
    // Unregister user from course
    if ($_REQUEST['unregister']) {
        if (isset($_GET['user_id']) && is_numeric($_GET['user_id']) && $_GET['user_id'] != $_user['user_id']) {
            $tbl_user = Database::get_main_table(TABLE_MAIN_USER);
            $tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
            $tbl_session_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_USER);

            $sql = 'SELECT ' . $tbl_user . '.user_id FROM ' . $tbl_user . ' user 
		    INNER JOIN ' . $tbl_session_rel_user . ' reluser ON user.user_id = reluser.id_user 
                    INNER JOIN ' . $tbl_session_rel_course . ' rel_course ON rel_course.id_session = reluser.id_session 
                    WHERE user.user_id = "' . Database::escape_string(Security::Remove_XSS($_GET['user_id'])) . '"
                    AND rel_course.course_code = "' . Database::escape_string($_course['sysCode']) . '"';

            $result = Database::query($sql, __FILE__, __LINE__);
            $row = Database::fetch_array($result, 'ASSOC');
            if ($row['user_id'] == $user_id || $row['user_id'] == "") {
                CourseManager::unsubscribe_user($_GET['user_id'], $_SESSION['_course']['sysCode']);
                $message = get_lang('UserUnsubscribed');
            } else {
                $message = get_lang('ThisStudentIsSubscribeThroughASession');
            }
        }
    }
} // end if allowed to edit


/*
  ==============================================================================
  FUNCTIONS
  ==============================================================================
 */

function display_user_search_form() {
    echo '<form method="get" action="user.php?' . api_get_cidreq() . '">';
    echo get_lang("SearchForUser") . "&nbsp;&nbsp;";
    echo '<input type="hidden" name="cidReq" value="' . api_get_course_id() . '" />';
    echo '<input type="text" name="keyword" value="' . Security::Remove_XSS($_GET['keyword']) . '"/>';
    echo '<input type="submit" value="' . get_lang('SearchButton') . '"/>';
    echo '</form>';
}

/**
 * 	This function displays a list if users for each virtual course linked to the current
 * 	real course.
 *
 * 	defines globals
 *
 * 	@version 1.0
 * 	@author Roan Embrechts
 * 	@todo users from virtual courses always show "-" for the group related output. Edit and statistics columns are disabled *	for these users, for now.
 */
function show_users_in_virtual_courses() {
    global $_course, $_user, $origin;
    $real_course_code = $_course['sysCode'];
    $real_course_info = Database::get_course_info($real_course_code);
    $user_subscribed_virtual_course_list = CourseManager::get_list_of_virtual_courses_for_specific_user_and_real_course($_user['user_id'], $real_course_code);
    $number_of_virtual_courses = count($user_subscribed_virtual_course_list);
    $row = 0;
    $column_header[$row++] = "ID";
    $column_header[$row++] = get_lang("FullUserName");
    $column_header[$row++] = get_lang("Role");
    $column_header[$row++] = get_lang("Group");
    if (api_is_allowed_to_edit()) {
        $column_header[$row++] = get_lang("Tutor");
    }
    if (api_is_allowed_to_edit()) {
        $column_header[$row++] = get_lang("CourseManager");
    }

    if (!is_array($user_subscribed_virtual_course_list)) {
        return;
    }

    foreach ($user_subscribed_virtual_course_list as $virtual_course) {
        $virtual_course_code = $virtual_course["code"];
        $virtual_course_user_list = CourseManager::get_user_list_from_course_code($virtual_course_code);
        $message = get_lang("RegisteredInVirtualCourse") . " " . $virtual_course["title"] . "&nbsp;&nbsp;(" . $virtual_course["code"] . ")";
        echo "<br/>";
        echo "<h4>" . $message . "</h4>";
        $properties["width"] = "100%";
        $properties["cellspacing"] = "1";
        Display::display_complex_table_header($properties, $column_header);
        foreach ($virtual_course_user_list as $this_user) {
            $user_id = $this_user["user_id"];
            $loginname = $this_user["username"];
            $lastname = $this_user["lastname"];
            $firstname = $this_user["firstname"];
            $status = $this_user["status"];
            $role = $this_user["role"];
            if ($status == "1") {
                $status = get_lang("CourseManager");
            } else {
                $status = " - ";
            }

            $full_name = api_get_person_name($firstname, $lastname);
            if ($lastname == '' || $firstname == '') {
                $full_name = $loginname;
            }

            $user_info_hyperlink = "<a href=\"userInfo.php?" . api_get_cidreq() . "&origin=" . $origin . "&uInfo=" . $user_id . "&virtual_course=" . $virtual_course["code"] . "\">" . $full_name . "</a>";
            $row = 0;
            $table_row[$row++] = $user_id;
            $table_row[$row++] = $user_info_hyperlink; //Full name
            $table_row[$row++] = $role; //Description
            $table_row[$row++] = " - "; //Group, for the moment groups don't work for students in virtual courses
            if (api_is_allowed_to_edit()) {
                $table_row[$row++] = " - "; //Tutor column
                $table_row[$row++] = $status; //Course Manager column
            }
            Display::display_table_row(null, $table_row, true);
        }
        Display::display_table_footer();
    }
}

if (!$is_allowed_in_course) {
    api_not_allowed(true);
}

// Display the header
Display::display_tool_header($tool_name, "User");


//tracking
event_access_tool(TOOL_USER);


// Access restriction
$is_allowed_to_track = (api_is_allowed_to_edit() || $is_courseTutor) && $_configuration['tracking_enabled'];


// Tool introduction
Display::display_introduction_section(TOOL_USER, 'left');
echo '<div class="actions" style="padding-bottom:5px;">';
// the action links
if (api_is_allowed_to_edit()) {
    $session = api_get_session_id();
    if ($session == 0) {
        $actions .= '<a href="subscribe_user.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('AddLearners'), array('class' => 'toolactionplaceholdericon tooladdlearner')) . get_lang("AddLearners") . '</a> ';
    }
    $actions .= '<a href="user.php?' . api_get_cidreq() . '&amp;action=export&amp;type=csv">' . Display::return_icon('pixel.gif', get_lang('Export'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . '&nbsp;' . get_lang('Export') . '</a>';
    //$actions .= '<a href="' . api_get_path(WEB_CODE_PATH) . 'reporting/">'.Display::return_icon('pixel.gif', get_lang('Report'), array('class' => 'toolactionplaceholdericon toolactionquizscores')).get_lang("Report").'</a> ';
}
$actions .= '<a href="' . api_get_path(WEB_CODE_PATH) . 'group/group.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('Group'), array('class' => 'toolactionplaceholdericon toolactiongroup')) . get_lang("Group") . '</a> ';
if (api_get_setting('use_session_mode') == 'false') {
    $actions .= ' <a href="class.php?' . api_get_cidreq() . '">' . get_lang('Classes') . '</a>';
}
if(api_is_coach() || api_is_allowed_to_edit()){
	$actions .=  '<a href="'.api_get_path(WEB_CODE_PATH).'course_home/score_face2face.php?'.api_get_cidReq().'">' . Display::return_icon('pixel.gif', get_lang('ScoreFace2Face'), array('class' => 'toolactionplaceholdericon toolactionface2face'))  . get_lang('ScoreFace2Face') . '</a>';
}
// Build search-form
$form = new FormValidator('search_user', 'get', '', '', null, false);
$renderer = & $form->defaultRenderer();
$renderer->setElementTemplate('<span>{element}</span> ');

$form->addElement('static', 'additionalactions', null, '<div style="width:auto;float:left; ">' . $actions . '</div>');
$form->addElement('static', 'div_float_right', null, '<div style="width:auto;float:right; text-align:right; ">'); //class="floatdiv";
$form->add_textfield('keyword', '', false, array('style' => 'width:225px;'));
$form->addElement('hidden', 'cidReq', api_get_course_id());
$form->addElement('style_submit_button', 'submit', get_lang('SearchButton'), 'class="search" style="float:none;"');
$form->addElement('static', 'div_float_right_end', null, '</div>');
$form->display();
echo '</div>';
// start the content div
if (isset($message)) {
    Display::display_confirmation_message2($message);
}
echo '<div id="content">';

/*
  --------------------------------------
  DISPLAY USERS LIST
  --------------------------------------
  Also shows a "next page" button if there are
  more than 50 users.

  There's a bug in here somewhere - some users count as more than one if they are in more than one group
  --> code for > 50 users should take this into account
  (Roan, Feb 2004)
 */
if (CourseManager::has_virtual_courses_from_code($course_id, $user_id)) {
    $real_course_code = $_course['sysCode'];
    $real_course_info = Database::get_course_info($real_course_code);
    $message = get_lang("RegisteredInRealCourse") . " " . $real_course_info["title"] . "&nbsp;&nbsp;(" . $real_course_info["official_code"] . ")";
    echo "<h4>" . $message . "</h4>";
}

/*
  ==============================================================================
  DISPLAY LIST OF USERS
  ==============================================================================
 */

/**
 *  * Get the users to display on the current page.
 */
function get_number_of_users() {
    $counter = 0;
    if (!empty($_SESSION["id_session"])) {
        $a_course_users = CourseManager :: get_user_list_from_course_code($_SESSION['_course']['id'], true, $_SESSION['id_session']);
    } else {
        $a_course_users = CourseManager :: get_user_list_from_course_code($_SESSION['_course']['id'], true);
    }
    foreach ($a_course_users as $user_id => $o_course_user) {
        if ((isset($_GET['keyword']) && search_keyword($o_course_user['firstname'], $o_course_user['lastname'], $o_course_user['username'], $o_course_user['official_code'], $_GET['keyword'])) || !isset($_GET['keyword']) || empty($_GET['keyword'])) {
            $counter++;
        }
    }
    return $counter;
}

function search_keyword($firstname, $lastname, $username, $official_code, $keyword) {
    if (api_strripos($firstname, $keyword) !== false || api_strripos($lastname, $keyword) !== false || api_strripos($username, $keyword) !== false || api_strripos($official_code, $keyword) !== false) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get the users to display on the current page.
 */
function get_user_data($from, $number_of_items, $column, $direction) {
    global $origin, $_user;
    global $is_western_name_order;
    global $sort_by_first_name;
    $a_users = array();

    // limit
    if (!isset($_GET['keyword']) || empty($_GET['keyword'])) {
        $limit = 'LIMIT ' . intval($from) . ',' . intval($number_of_items);
    }

    if (!in_array($direction, array('ASC', 'DESC'))) {
        $direction = 'ASC';
    }

    // order by
    if (api_is_allowed_to_edit()) {
        $column--;
    }
    switch ($column) {
        case 0:
            $order_by = 'ORDER BY user.picture_uri ' . $direction;
            break;
        case 2:
            $order_by = 'ORDER BY user.lastname ' . $direction . ', user.firstname ' . $direction;
            break;
        case 3:
            $order_by = 'ORDER BY user.firstname ' . $direction;
            break;
        default:
            $order_by = 'ORDER BY user.lastname ' . $direction . ', user.firstname ' . $direction;
            break;
    }

    if (!empty($_SESSION["id_session"])) {
        $a_course_users = CourseManager :: get_user_list_from_course_code($_SESSION['_course']['id'], true, $_SESSION['id_session'], $limit, $order_by);
    } else {
        $a_course_users = CourseManager :: get_user_list_from_course_code($_SESSION['_course']['id'], true, 0, $limit, $order_by);
        $user_list = Who_is_online_in_this_course($_user['user_id'], api_get_setting('time_limit_whosonline'), api_get_course_id());
        $online_users_list = array();
        foreach ($user_list as $user_online) {
            $online_users_list[] = $user_online['0'];
        }
    }

    foreach ($a_course_users as $user_id => $o_course_user) {
        global $_course;
        $user_rel_course = '';
        if ((isset($_GET['keyword']) && search_keyword($o_course_user['firstname'], $o_course_user['lastname'], $o_course_user['username'], $o_course_user['official_code'], $_GET['keyword'])) || !isset($_GET['keyword']) || empty($_GET['keyword'])) {
            $user_rel_course = UserManager::get_user_in_course($user_id, $_SESSION['_course']['id']);
            $session_name = UserManager::get_user_last_session_name_in_course($user_id, $_SESSION['_course']['id']);
            $is_course_coach = SessionManager::is_course_in_session_coach($user_id, $_SESSION['_course']['id']);

            if (api_is_allowed_to_edit()) {
                $temp = array();
                $temp[] = $user_id;
                $image_path = UserManager::get_user_picture_path_by_id($user_id, 'web', false, true);
                $user_profile = UserManager::get_picture_user($user_id, $image_path['file'], 22, 'small_', ' width="22" height="22" ');

                if (!api_is_anonymous()) {
                    $image_array = UserManager::get_user_picture_path_by_id($user_id, 'web', false, false);
                    if ($image_array['file'] != '') {
                        $cadena = explode("_", $image_array['file']);
                        $idphoto = $cadena[0];
                        $id_image = trim(preg_replace("/.png|.jpg/", "", $image_array['file']));  
                        $photo = '<center><a onClick="runDialog(' . "'" . $id_image . "'" . ')" class="" href="#" title="' . get_lang('Info') . '"  >
				  <img src="' . $user_profile['file'] . '" ' . $user_profile['style'] . ' alt="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '"  title="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" />';
                        
                        echo '<div id="' . $id_image . '" style="display: none;text-align:center;" title="' . get_lang('Photo') . '">
                              <img src="' . $image_array['dir'] . $image_array['file'] . '"></div>';
                    } else {
                        $photo = '<center>' . Display::return_icon('pixel.gif', "" . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . "", array('class' => 'actionplaceholdericon actionunknown')) . '</center>';
                    }
                } else {
                    $photo = '<center><img src="' . $user_profile['file'] . '" ' . $user_profile['style'] . ' alt="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" title="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" /></center>';
                }
                $temp[] = $photo;
                $temp[] = $o_course_user['lastname'];
                $temp[] = $o_course_user['firstname'];
                $temp[] = $user_rel_course;
                $temp[] = $session_name;
                if ($o_course_user['active'] == 0) {
                    $temp[] = '<div style="text-align:center;"><img src="' . api_get_path(WEB_IMG_PATH) . 'dialog-error.png" /></div>';
                } else {
                    if (CourseManager::is_course_teacher($user_id, $_SESSION['_course']['id'])) {
                        $temp[] = '<div style="text-align:center;"><a href="user.php?' . api_get_cidreq() . '&amp;action=makeTeacherStudent&amp;user_id=' . $user_id . '">' . Display::return_icon('pixel.gif', get_lang('Trainer'), array('class' => 'actionplaceholdericon actiontrainer')) . '</a></div>';
                    } elseif ($is_course_coach) {
                        $temp[] = '<div style="text-align:center;">' . Display::return_icon('pixel.gif', get_lang('Coach'), array('class' => 'actionplaceholdericon actioncoach')) . '</div>';
                    } else {
                        $temp[] = '<div style="text-align:center;"><a href="user.php?' . api_get_cidreq() . '&amp;action=makeStudentTeacher&amp;user_id=' . $user_id . '">' . Display::return_icon('pixel.gif', get_lang('User'), array('class' => 'actionplaceholdericon actionuser')) . '</a></div>';
                    }
                }
            } else {
                $temp = array();

                $image_path = UserManager::get_user_picture_path_by_id($user_id, 'web', false, true);
                $image_repository = $image_path['dir'];
                $existing_image = $image_path['file'];
                if (!api_is_anonymous()) {
                    $photo = '<center><a href="userInfo.php?' . api_get_cidreq() . '&origin=' . $origin . '&uInfo=' . $user_id . '" title="' . get_lang('Info') . '"  ><img src="' . $image_repository . $existing_image . '" alt="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '"  width="22" height="22" title="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" /></a></center>';
                } else {
                    $photo = '<center><img src="' . $image_repository . $existing_image . '" alt="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '"  width="22" height="22" title="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" /></center>';
                }
                $temp[] = $photo;
                $temp[] = $o_course_user['lastname'];
                $temp[] = $o_course_user['firstname'];
                $temp[] = ""; //sessions
                if (api_is_allowed_to_edit()) {
                    $temp[] = $user_id;
                }
            }
            $a_users[$user_id] = $temp;
        }
    }
    return $a_users;
}

/**
 * Build the active-column of the table to lock or unlock a certain user
 * lock = the user can no longer use this account
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $active the current state of the account
 * @param int $user_id The user id
 * @param string $url_params
 * @return string Some HTML-code with the lock/unlock button
 */
function active_filter($active, $url_params, $row) {
    global $_user;
    if ($active == '1') {
        $action = 'AccountActive';
        $image = 'right';
    }
    if ($active == '0') {
        $action = 'AccountInactive';
        $image = 'wrong';
    }
    if ($row['0'] <> $_user['user_id']) {  // you cannot lock yourself out otherwise you could disable all the accounts including your own => everybody is locked out and nobody can change it anymore.
        $result = '<center><img src="../img/' . $image . '.gif" border="0" style="vertical-align: middle;" alt="' . get_lang(ucfirst($action)) . '" title="' . get_lang(ucfirst($action)) . '"/></center>';
    }
    return $result;
}

/**
 * Build the modify-column of the table
 * @param int $user_id The user id
 * @return string Some HTML-code
 */
function modify_filter($user_id) {
    global $origin, $_user, $_course, $is_allowed_to_track, $charset;

    $result = "<div style='text-align: center'>";
    if ($is_allowed_to_track) {
        $result .= '<a href="../mySpace/myStudents.php?' . api_get_cidreq() . '&student=' . $user_id . '&amp;details=true&amp;course=' . $_course['id'] . '&amp;origin=user_course&amp;id_session=' . $_SESSION["id_session"] . '" title="' . get_lang('Tracking') . '"  >' . Display::return_icon('pixel.gif', get_lang('Tracking'), array('class' => 'actionplaceholdericon actiontracking')) . '</a>&nbsp;';
    }
    $result .= "</div>";
    return $result;
}

$default_column = ($is_western_name_order xor $sort_by_first_name) ? 3 : 2;
$default_column = api_is_allowed_to_edit() ? 3 : 2;
$table = new SortableTable('users', 'get_number_of_users', 'get_user_data', $default_column);
$parameters['keyword'] = $_GET['keyword'];
$table->set_additional_parameters($parameters);
$header_nr = 0;

if (api_is_allowed_to_edit()) {
    $table->set_header($header_nr++, get_lang('Remove'), false);
}
$table->set_header($header_nr++, get_lang('Photo'), true);
$table->set_header($header_nr++, get_lang('LastName'));
$table->set_header($header_nr++, get_lang('FirstName'));
$table->set_header($header_nr++, get_lang('Course'), false);
$table->set_header($header_nr++, get_lang('Session'), false);

if (api_is_allowed_to_edit()) {
    $table->set_header($header_nr++, get_lang('Role'), false);
    if (api_is_allowed_to_edit()) {
        $table->set_form_actions(array('unsubscribe' => get_lang('Unreg')), 'user');
    }
}

// display the table
$table->display();

// end the content div
echo '</div>';
if (!empty($_GET['keyword']) && !empty($_GET['submit'])) {
    $keyword_name = Security::remove_XSS($_GET['keyword']);
    echo '<br/>' . get_lang('SearchResultsFor') . ' <span style="font-style: italic ;"> ' . $keyword_name . ' </span><br>';
}

if (api_get_setting('allow_user_headings') == 'true' && $is_courseAdmin && api_is_allowed_to_edit() && $origin != 'learnpath') { // only course administrators see this line
    echo "<div align=\"right\">", "<form method=\"post\" action=\"userInfo.php\">", get_lang("CourseAdministratorOnly"), " : ", "<input type=\"submit\" name=\"viewDefList\" value=\"" . get_lang("DefineHeadings") . "\" />", "</form>", "</div>\n";
}

// display the footer
Display::display_footer();