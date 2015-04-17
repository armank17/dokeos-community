<?php

/* For licensing terms, see /dokeos_license.txt */

/**
 * @author Bart Mollet
 * @package dokeos.admin
 */
// name of the language file that needs to be included
$language_file = array('registration', 'admin', 'tracking');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationuserlist';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH) . 'sortabletable.class.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'security.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'xajax/xajax.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'tracking.lib.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// set additional profile field if its a string
if (isset($_GET['additional_profile_field']) && !is_array($_GET['additional_profile_field'])) {
    $_GET['additional_profile_field'] = explode(',', $_GET['additional_profile_field']);
} else if (isset($_GET['additional_profile_field_search']) && !empty($_GET['additional_profile_field_search'])) {
    $_GET['additional_profile_field'] = explode(',', $_GET['additional_profile_field_search']);
    unset($_GET['additional_profile_field_search']);
}

// Access restrictions
api_protect_admin_script(true);

// additional javascript
//$htmlHeadXtra[] = '<script src="../inc/lib/javascript/jquery.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = ' <script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.multiselect.js" type="text/javascript"></script>
                    <link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.multiselect.css"/>';
$htmlHeadXtra[]='<script type="text/javascript">
    
    $(document).on("ready",Jload);
    function Jload(){
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
    }
    </script>
';
$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){
       $("#additional_profile_field").multiselect({checkAllText: "' . get_lang('SelectAll') . '", uncheckAllText: "' . get_lang('UnSelectAll') . '", noneSelectedText: "' . get_lang('SelectOption') . '", selectedText: "' . '# ' . get_lang('SelectedOption') . '"});
       $("#btn-search").click(function() {
            if ($("#search").is(":hidden")) {
                $("#keyword").val("");
                $("#search").show();
                $("#keyword").focus();
            } else {
                $("#search").hide();
            }
       });
    });
</script>';


$htmlHeadXtra[] = '
<script type="text/javascript">
function showCourseList(my_user_id){ 
        $.ajax({
            url: "course_user_list.php?user_id="+my_user_id,
            success: function(data){
               var dialog_div = $("#html_user_info");
                dialog_div.html(data);
                dialog_div.dialog({
                    modal: true,
                    title: "'.get_lang('CourseList').'",
                    width: 294,
                    height : 352,
                    resizable:false
                });
               
            }
        });
}
</script>';

$htmlHeadXtra[] = '<script type="text/javascript">
function load_course_list (div_course,my_user_id) {
	 $.ajax({
		contentType: "application/x-www-form-urlencoded",
		beforeSend: function(objeto) {
		$("div#"+div_course).html("<img src=\'../inc/lib/javascript/indicator.gif\' />"); },
		type: "POST",
		url: "course_user_list.php",
		data: "user_id="+my_user_id,
		success: function(datos) {
			$("div#"+div_course).html(datos);
			$("div#div_"+my_user_id).attr("class","blackboard_show");
			$("div#div_"+my_user_id).attr("style","");
		}
	});
}
function clear_course_list (div_course) {
	$("div#"+div_course).html("&nbsp;");
	$("div#"+div_course).hide("");
}
</script>';
$htmlHeadXtra[] = '<style type="text/css" media="screen, projection">
.blackboard_show {
	float:left;
	position:absolute;
	border:1px solid black;
	width: 200px;
	background-color:white;
	z-index:99; padding: 3px;
	display: inline;
}
.blackboard_hide {
    display: none;
}

#additional_profile_field_form {
    margin: 10px 0px;
}

.secondary-actions {
    width:100%;
    overflow:hidden;
    height: 65px;
}

#secondary-actions-extra {
    height: 65px;
    overflow: hidden;
    width: 490px;
    float:left;
}

#search {
    height: 50px;
    margin-top: 10px;
    overflow: hidden;
    width: 442px;
    float:right;
    display:none;
}

#search input {
    margin-right: 12px;
    vertical-align: middle;
}
';

// xajax
$xajax = new xajax();
$xajax->registerFunction('courses_of_user');
$xajax->processRequests();

/**
 * Get a formatted list of courses for given user
 * @param   int     User ID
 * @return  resource    XAJAX response
 */
function courses_of_user($arg) {
    // do some stuff based on $arg like query data from a database and
    // put it into a variable like $newContent
    //$newContent = 'werkt het? en met een beetje meer text, wordt dat goed opgelost? ';
    $personal_course_list = UserManager::get_personal_session_course_list($arg);
    $newContent = '';
    if (count($personal_course_list) > 0) {
        foreach ($personal_course_list as $key => $course) {
            $newContent .= $course['i'] . '<br />';
        }
    } else {
        $newContent .= '- ' . get_lang('None') . ' -<br />';
    }
    $newContent = api_convert_encoding($newContent, 'utf-8', api_get_setting('platform_charset'));

    // Instantiate the xajaxResponse object
    $objResponse = new xajaxResponse();

    // add a command to the response to assign the innerHTML attribute of
    // the element with id="SomeElementId" to whatever the new content is
    $objResponse->addAssign("user" . $arg, "innerHTML", $newContent);
    $objResponse->addReplace("coursesofuser" . $arg, "alt", $newContent);
    $objResponse->addReplace("coursesofuser" . $arg, "title", $newContent);

    $objResponse->addAssign("user" . $arg, "style.display", "block");

    //return the  xajaxResponse object
    return $objResponse;
}

/**
 * Empties the XAJAX object representing the courses list
 * @param   int     User ID
 * @return  resource    XAJAX object
 */
function empty_courses_of_user($arg) {
    // do some stuff based on $arg like query data from a database and
    // put it into a variable like $newContent
    $newContent = '';
    // Instantiate the xajaxResponse object
    $objResponse = new xajaxResponse();
    // add a command to the response to assign the innerHTML attribute of
    // the element with id="SomeElementId" to whatever the new content is
    $objResponse->addAssign("user" . $arg, "innerHTML", $newContent);


    //return the  xajaxResponse object
    return $objResponse;
}

$htmlHeadXtra[] = $xajax->getJavascript('../inc/lib/xajax/');
$htmlHeadXtra[] = '<style type="text/css">
.tooltipLinkInner {
	position:relative;
	float:left;
	color:blue;
	text-decoration:none;
}
</style>';

/**
 * 	Make sure this function is protected because it does NOT check password!
 *
 * 	This function defines globals.
 *   @param  int     User ID
 *   @return bool    False on failure, redirection on success
 * 	@author Evie Embrechts
 *   @author Yannick Warnier <yannick.warnier@dokeos.com>
 */
function login_user($user_id) {
    //init ---------------------------------------------------------------------
    //Load $_user to be sure we clean it before logging in
    global $uidReset, $loginFailed, $_configuration, $_user;

    $main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
    $main_admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
    $track_e_login_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);

    //logic --------------------------------------------------------------------
    unset($_user['user_id']); // uid not in session ? prevent any hacking
    if (!isset($user_id)) {
        $uidReset = true;
        return;
    }
    if ($user_id != strval(intval($user_id))) {
        return false;
    }

    $sql_query = "SELECT * FROM $main_user_table WHERE user_id='$user_id'";
    $sql_result = Database::query($sql_query, __FILE__, __LINE__);
    $result = Database :: fetch_array($sql_result);

    // check if the user is allowed to 'login_as'
    $can_login_as = (api_is_platform_admin() OR (api_is_session_admin() && $result['status'] == 5 ));
    if (!$can_login_as) {
        return false;
    }

    $firstname = $result['firstname'];
    $lastname = $result['lastname'];
    $user_id = $result['user_id'];

    //$message = "Attempting to login as ".api_get_person_name($firstname, $lastname)." (id ".$user_id.")";
    if (api_is_western_name_order()) {
        $message = sprintf(get_lang('AttemptingToLoginAs'), $firstname, $lastname, $user_id);
    } else {
        $message = sprintf(get_lang('AttemptingToLoginAs'), $lastname, $firstname, $user_id);
    }

    $loginFailed = false;
    $uidReset = false;

    if ($user_id) { // a uid is given (log in succeeded)
        if ($_configuration['tracking_enabled']) {
            $sql_query = "SELECT user.*, a.user_id is_admin,
				UNIX_TIMESTAMP(login.login_date) login_date
				FROM $main_user_table
				LEFT JOIN $main_admin_table a
				ON user.user_id = a.user_id
				LEFT JOIN $track_e_login_table login
				ON user.user_id = login.login_user_id
				WHERE user.user_id = '" . $user_id . "'
				ORDER BY login.login_date DESC LIMIT 1";
        } else {
            $sql_query = "SELECT user.*, a.user_id is_admin
				FROM $main_user_table
				LEFT JOIN $main_admin_table a
				ON user.user_id = a.user_id
				WHERE user.user_id = '" . $user_id . "'";
        }

        $sql_result = Database::query($sql_query, __FILE__, __LINE__);


        if (Database::num_rows($sql_result) > 0) {
            // Extracting the user data

            $user_data = Database::fetch_array($sql_result);

            //Delog the current user

            LoginDelete($_SESSION["_user"]["user_id"]);

            // Cleaning session variables
            unset($_SESSION['_user']);
            unset($_SESSION['is_platformAdmin']);
            unset($_SESSION['is_allowedCreateCourse']);
            unset($_SESSION['_uid']);


            $_user['firstName'] = $user_data['firstname'];
            $_user['lastName'] = $user_data['lastname'];
            $_user['mail'] = $user_data['email'];
            $_user['lastLogin'] = $user_data['login_date'];
            $_user['official_code'] = $user_data['official_code'];
            $_user['picture_uri'] = $user_data['picture_uri'];
            $_user['user_id'] = $user_data['user_id'];
            $_user['status'] = $user_data['status'];

            $is_platformAdmin = (bool) (!is_null($user_data['is_admin']));
            $is_allowedCreateCourse = (bool) ($user_data['status'] == 1);

            // Filling session variables with new data
            $_SESSION['_uid'] = $user_id;
            $_SESSION['_user'] = $_user;
            $_SESSION['is_platformAdmin'] = $is_platformAdmin;
            $_SESSION['is_allowedCreateCourse'] = $is_allowedCreateCourse;
            $_SESSION['login_as'] = true; // will be usefull later to know if the user is actually an admin or not (example reporting)s

            $target_url = api_get_path(WEB_PATH) . "user_portal.php";
            //$message .= "<br/>Login successful. Go to <a href=\"$target_url\">$target_url</a>";
            $message .= '<br />' . sprintf(get_lang('LoginSuccessfulGoToX'), '<a href="' . $target_url . '">' . $target_url . '</a>');
            Display :: display_header(get_lang('UserList'));
            echo '<div id="content">'; // Start main content
            Display :: display_normal_message($message, false, true);
            echo '</div>'; // End main content
            Display :: display_footer();
            exit;
        } else {
            exit("<br />WARNING UNDEFINED UID !! ");
        }
    }
}

/**
 * Get the total number of users on the platform
 * @see SortableTable#get_total_number_of_items()
 */
function get_number_of_users() {
    $user_table = Database :: get_main_table(TABLE_MAIN_USER);
    $sql = "SELECT COUNT(u.user_id) AS total_number_of_items FROM $user_table u";

    // adding the filter to see the user's only of the current access_url
    global $_configuration;
    if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
        $access_url_rel_user_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
        $sql.= " INNER JOIN $access_url_rel_user_table url_rel_user ON (u.user_id=url_rel_user.user_id)";
    }

    if (isset($_GET['keyword'])) {
        $keyword = Database::escape_string($_GET['keyword']);
        $sql .= " WHERE (u.firstname LIKE '%" . $keyword . "%' OR u.lastname LIKE '%" . $keyword . "%'  OR u.username LIKE '%" . $keyword . "%' OR u.email LIKE '%" . $keyword . "%'  OR u.official_code LIKE '%" . $keyword . "%') ";
    } elseif (isset($_GET['keyword_firstname'])) {
        $admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
        $keyword_firstname = Database::escape_string($_GET['keyword_firstname']);
        $keyword_lastname = Database::escape_string($_GET['keyword_lastname']);
        $keyword_email = Database::escape_string($_GET['keyword_email']);
        $keyword_officialcode = Database::escape_string($_GET['keyword_officialcode']);
        $keyword_username = Database::escape_string($_GET['keyword_username']);
        $keyword_status = Database::escape_string($_GET['keyword_status']);
        $query_admin_table = '';
        $keyword_admin = '';
        if ($keyword_status == SESSIONADMIN) {
            $keyword_status = '%';
            $query_admin_table = " , $admin_table a ";
            $keyword_admin = ' AND a.user_id = u.user_id ';
        }
        $keyword_active = isset($_GET['keyword_active']);
        $keyword_inactive = isset($_GET['keyword_inactive']);
        $sql .= $query_admin_table .
                " WHERE (u.firstname LIKE '%" . $keyword_firstname . "%' " .
                "AND u.lastname LIKE '%" . $keyword_lastname . "%' " .
                "AND u.username LIKE '%" . $keyword_username . "%'  " .
                "AND u.email LIKE '%" . $keyword_email . "%'   " .
                "AND u.official_code LIKE '%" . $keyword_officialcode . "%'    " .
                "AND u.status LIKE '" . $keyword_status . "'" .
                $keyword_admin;
        if ($keyword_active && !$keyword_inactive) {
            $sql .= " AND u.active='1'";
        } elseif ($keyword_inactive && !$keyword_active) {
            $sql .= " AND u.active='0'";
        }
        $sql .= " ) ";
    }

    // adding the filter to see the user's only of the current access_url
    if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
        $sql.= " AND url_rel_user.access_url_id=" . api_get_current_access_url_id();
    }

    $res = Database::query($sql, __FILE__, __LINE__);
    $obj = Database::fetch_object($res);
    return $obj->total_number_of_items;
}

/**
 * Get the users to display on the current page (fill the sortable-table)
 * @param   int     offset of first user to recover
 * @param   int     Number of users to get
 * @param   int     Column to sort on
 * @param   string  Order (ASC,DESC)
 * @see SortableTable#get_table_data($from)
 */
function get_user_data($from, $number_of_items, $column, $direction) {
    global $_configuration, $origin;
    $user_table = Database :: get_main_table(TABLE_MAIN_USER);
    $payment_log_table = Database :: get_main_table(TABLE_MAIN_PAYMENT_LOG);
    $admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
    $t_u_f_values = Database :: get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
    $tbl_user_field = Database :: get_main_table(TABLE_MAIN_USER_FIELD);

    //number of column max = 8
    if ($column < 10) {
        $sql = "SELECT
                         u.user_id			AS col0,
                         u.user_id			AS col1,
                         u.official_code		AS col2,
                         " . (api_is_western_name_order() ? "u.firstname 		AS col3,
                         u.lastname 			AS col4," : "u.lastname 			AS col3,
                         u.firstname 			AS col4,") . "
                         u.username			AS col5,
                         u.email			AS col6,
                         u.status			AS col7,
                         u.active			AS col8,
                         u.user_id			AS col9,
                         u.expiration_date              AS exp
                         FROM $user_table u   ";

        // adding the filter to see the user's only of the current access_url
        if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $access_url_rel_user_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
            $sql.= " INNER JOIN $access_url_rel_user_table url_rel_user ON (u.user_id=url_rel_user.user_id)";
        }

        if (isset($_GET['keyword'])) {
            $keyword = Database::escape_string($_GET['keyword']);
            $sql .= " WHERE (u.firstname LIKE '%" . $keyword . "%' OR u.lastname LIKE '%" . $keyword . "%'  OR u.username LIKE '%" . $keyword . "%'  OR u.official_code LIKE '%" . $keyword . "%' OR u.email LIKE '%" . $keyword . "%' )AND  u.status NOT LIKE '%6%' ";
        } elseif (isset($_GET['keyword_firstname'])) {
            $keyword_firstname = Database::escape_string($_GET['keyword_firstname']);
            $keyword_lastname = Database::escape_string($_GET['keyword_lastname']);
            $keyword_email = Database::escape_string($_GET['keyword_email']);
            $keyword_officialcode = Database::escape_string($_GET['keyword_officialcode']);
            $keyword_username = Database::escape_string($_GET['keyword_username']);
            $keyword_status = Database::escape_string($_GET['keyword_status']);
            $query_admin_table = '';
            $keyword_admin = '';

            if ($keyword_status == SESSIONADMIN) {
                $keyword_status = '%';
                $query_admin_table = " , $admin_table a ";
                $keyword_admin = ' AND a.user_id = u.user_id ';
            }
            $keyword_active = isset($_GET['keyword_active']);
            $keyword_inactive = isset($_GET['keyword_inactive']);
            $sql .= $query_admin_table . " WHERE (u.firstname LIKE '%" . $keyword_firstname . "%' " .
                    "AND u.lastname LIKE '%" . $keyword_lastname . "%' " .
                    "AND u.username LIKE '%" . $keyword_username . "%'  " .
                    "AND u.email LIKE '%" . $keyword_email . "%'   " .
                    "AND u.official_code LIKE '%" . $keyword_officialcode . "%'    " .
                    "AND u.status LIKE '" . $keyword_status . "'" .
                    $keyword_admin;

            if ($keyword_active && !$keyword_inactive) {
                $sql .= " AND u.active='1'";
            } elseif ($keyword_inactive && !$keyword_active) {
                $sql .= " AND u.active='0'";
            }
            $sql .= " ) ";
        } else {
            $sql .= " WHERE  u.status NOT LIKE '%6%' ";
        }

        // adding the filter to see the user's only of the current access_url
        if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $sql.= " AND url_rel_user.access_url_id=" . api_get_current_access_url_id();
        }

        if (!in_array($direction, array('ASC', 'DESC'))) {
            $direction = 'ASC';
        }
        $column = intval($column);
        $from = intval($from);
        $number_of_items = intval($number_of_items);

        $sql .= " ORDER BY col$column $direction ";
        $sql .= " LIMIT $from,$number_of_items";

        $res = Database::query($sql, __FILE__, __LINE__);

        // Columns Extra Field
    } else {
        //if (isset(additional_profile_field))
        //get column selected
        $id_column = $column;
        //default columns
        $def_column = 8;

        //Position Selected
        $position_id = $id_column - $def_column - 1;

        //Array Field actived
        $array_field_activate = array();

        if (isset($_GET['additional_profile_field']) && count($_GET['additional_profile_field']) > 0) {
            $array_field_activate = UserManager::get_active_extra_fields($_GET['additional_profile_field']);
            $field_sort = $array_field_activate[$position_id];
        }

        /* if ($field_sort < 9) {
          $field_sort = 9;
          } */

        //id field to sort
        //type ASC, DESC
        if (!in_array($direction, array('ASC', 'DESC'))) {
            $direction = 'ASC';
        }

        /* Start recicled code */
        $sql_add = "";
        if (isset($_GET['keyword'])) {
            $keyword = Database::escape_string($_GET['keyword']);
            $sql_add .= " WHERE (u.firstname LIKE '%" . $keyword . "%' OR u.lastname LIKE '%" . $keyword . "%'  OR u.username LIKE '%" . $keyword . "%'  OR u.official_code LIKE '%" . $keyword . "%' OR u.email LIKE '%" . $keyword . "%' )";
        } elseif (isset($_GET['keyword_firstname'])) {
            $keyword_firstname = Database::escape_string($_GET['keyword_firstname']);
            $keyword_lastname = Database::escape_string($_GET['keyword_lastname']);
            $keyword_email = Database::escape_string($_GET['keyword_email']);
            $keyword_officialcode = Database::escape_string($_GET['keyword_officialcode']);
            $keyword_username = Database::escape_string($_GET['keyword_username']);
            $keyword_status = Database::escape_string($_GET['keyword_status']);
            $query_admin_table = '';
            $keyword_admin = '';

            if ($keyword_status == SESSIONADMIN) {
                $keyword_status = '%';
                $query_admin_table = " , $admin_table a ";
                $keyword_admin = ' AND a.user_id = u.user_id ';
            }
            $keyword_active = isset($_GET['keyword_active']);
            $keyword_inactive = isset($_GET['keyword_inactive']);
            $sql_add .= $query_admin_table . " WHERE (u.firstname LIKE '%" . $keyword_firstname . "%' " .
                    "AND u.lastname LIKE '%" . $keyword_lastname . "%' " .
                    "AND u.username LIKE '%" . $keyword_username . "%'  " .
                    "AND u.email LIKE '%" . $keyword_email . "%'   " .
                    "AND u.official_code LIKE '%" . $keyword_officialcode . "%'    " .
                    "AND u.status LIKE '" . $keyword_status . "'" .
                    $keyword_admin;

            if ($keyword_active && !$keyword_inactive) {
                $sql_add .= " AND u.active='1'";
            } elseif ($keyword_inactive && !$keyword_active) {
                $sql_add .= " AND u.active='0'";
            }
            $sql_add .= " ) ";
        } else {
            if (!empty($array_field_activate) && !isset($_GET['users_page_nr'])) {
                $sql_add = ' WHERE u_f.id IN(' . implode(',', $array_field_activate) . ')';
            }
        }

        if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls'] == true && api_get_current_access_url_id() != -1) {
            $sql_add .= " AND url_rel_user.access_url_id=" . api_get_current_access_url_id();
        }

        /* End recicled code */
        $from = intval($from);
        $number_of_items = intval($number_of_items);

        $res = UserManager::get_active_sorted_extra_fields($field_sort, $direction, $from, $number_of_items, $keyword, $keyword_firstname, $keyword_lastname, $keyword_username, $keyword_email, $keyword_officialcode, $keyword_status, $keyword_admin, $keyword_active, $keyword_inactive, $sql_add, $from, $number_of_items);
    }
    // $res = Return an array of SQL query

    $users = array();
    $t = time();

    //Return an array active extra field
    if (isset($_GET['additional_profile_field']) && count($_GET['additional_profile_field']) > 0) {
        $array_field_activate = UserManager::get_active_extra_fields($_GET['additional_profile_field']);
    }
    while ($user = Database::fetch_row($res)) {
        $image_path = UserManager::get_user_picture_path_by_id($user[0], 'web', false, true);
        $image_info = explode('.', $image_path['file']);
        if (strcmp($image_info['0'], 'unknown') === 0) {
            $image_tag = Display::return_icon('pixel.gif', api_get_person_name($user[3], $user[4]), array('class' => 'actionplaceholdericon actionunknown'));
        } else {
            $user_profile = UserManager::get_picture_user($user[0], $image_path['file'], 22, 'small_', ' width="22" height="22" ');
            $image_tag = '<img src="' . $user_profile['file'] . '" ' . $user_profile['style'] . ' alt="' . api_get_person_name($user[3], $user[4]) . '" title="' . api_get_person_name($user[3], $user[4]) . '" />';
        }

        if (!api_is_anonymous()) {
            //$photo = '<center><a href="' . api_get_path(WEB_PATH) . 'whoisonline.php?origin=user_list&amp;id=' . $user[0] . '" title="' . get_lang('Info') . '"  >' . $image_tag . '</a></center>';
            $photo =  '<center>'.sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);" title="' . get_lang('Info') . '"  >' . $image_tag . '</a>',$user[0]).'</center>';
        } else {
            $photo = '<center>' . $image_tag . '</center>';
        }

        if ($user[8] == 1 && $user[10] != '0000-00-00 00:00:00') {
            // check expiration date
            $expiration_time = convert_mysql_date($user[10]);
            // if expiration date is passed, store a special value for active field
            if ($expiration_time < $t) {
                $user[8] = '-1';
            }
        }

        $e_commerce_enabled = intval(api_get_setting("e_commerce"));
        if ($e_commerce_enabled <> 0) {
            $sql_payment = "SELECT CONCAT(id,'::',pay_type) AS payment_type FROM " . $payment_log_table . " WHERE status = 0 AND user_id = " . $user[0] . " LIMIT 0,1";
            $res_payment = Database::query($sql_payment, __FILE__, __LINE__);
            $user[11] = Database :: fetch_row($res_payment);
            //use the explode for obtain the id of the cheque and payment type
            $exp_user = explode('::', $user[11][0]);
            $cheque_id = $exp_user[0];
            $user[11] = $exp_user[1];
            if ($user[11] == '2') {
                $user[11] = '<center><a href="user_list.php?action=unlocked_cheque&amp;user_id=' . $user[0] . '&amp;cheque_id=' . $cheque_id . '&amp;sec_token=' . $_SESSION['sec_token'] . '"><img src="' . api_get_path(WEB_PATH) . 'main/img/cheque.png" width="22" height="22" title="' . get_lang('Cheque') . '"></a></center>&nbsp;&nbsp;';
            } else {
                $user[11] = '&nbsp;&nbsp;';
            }
            $users[$i] = array($user[0], $photo, $user[2], $user[3], $user[4], $user[5], $user[6], $user[7], $user[8], $user[11]);
        } else {
            $users[$i] = array($user[0], $photo, $user[2], $user[3], $user[4], $user[5], $user[6], $user[7], $user[8]);
        }

        //Return an array active extra field user
        if (isset($_GET['additional_profile_field']) && count($_GET['additional_profile_field']) > 0) {
            $array_user_active = UserManager::get_active_user_extra_fields($user[0]);
            for ($k = 0; $k < count($array_field_activate); $k++) {
                if (in_array($array_field_activate[$k], $array_user_active)) {
                    $field_value = UserManager::get_user_name_field($user[0], $array_field_activate[$k]);
                    array_push($users[$i], $field_value);
                } else {
                    array_push($users[$i], '');
                }
            }
        }

        array_push($users[$i], $user[9]);
        $i++;
    }

    return $users;
}

/**
 * Returns a mailto-link
 * @param string $email An email-address
 * @return string HTML-code with a mailto-link
 */
function email_filter($email) {
    return Display :: encrypted_mailto_link($email, $email);
}

/**
 * Build the modify-column of the table
 * @param   int     The user id
 * @param   string  URL params to add to table links
 * @param   array   Row of elements to alter
 * @return string Some HTML-code with modify-buttons
 */
function modify_filter($user_id, $url_params, $row) {
    global $charset;
    global $_user;
    global $_admins_list;
    $is_admin = in_array($user_id, $_admins_list);
    $statusname = api_get_status_langvars();

    if (api_is_anonymous($user_id, true)) {
        $user_is_anonymous = true;
    }
    if (!$user_is_anonymous) {
        /* 	$result .= '<a  href="javascript:void(0)" onclick="load_course_list(\'div_'.$user_id.'\','.$user_id.')">
          <img onclick="load_course_list(\'div_'.$user_id.'\','.$user_id.')" onmouseout="clear_course_list (\'div_'.$user_id.'\')" src="../img/course_22.png" title="'.get_lang('Courses').'" alt="'.get_lang('Courses').'"/>
          <div class="blackboard_hide" id="div_'.$user_id.'">&nbsp;&nbsp;</div>
          </a>&nbsp;&nbsp;'; */
        $result .= '<a  href="javascript:void(0)" onclick="showCourseList('.$user_id.')">' 
                        . Display::return_icon('pixel.gif', get_lang('Course'), 
                        array('class' => 'actionplaceholdericon actioncourse' )) . 
                        '<div class="blackboard_hide" id="div_' . $user_id . '">&nbsp;&nbsp;</div>
                    </a>
                    &nbsp;&nbsp;';
       
    } else {
        $result .= Display::return_icon('pixel.gif', get_lang('Course'), array('class' => 'actionplaceholdericon actioncourse invisible')) . '&nbsp;&nbsp;';
    }

    if (api_is_platform_admin()) {
        if (!$user_is_anonymous) {
            $result .= '<a href="user_information.php?user_id=' . $user_id . '">' . Display::return_icon('pixel.gif', get_lang('Info'), array('class' => 'actionplaceholdericon actioninfo')) . '</a>&nbsp;&nbsp;';
        } else {
            $result .= Display::return_icon('pixel.gif', get_lang('Info'), array('class' => 'actionplaceholdericon actioninfo invisible')) . '&nbsp;&nbsp;';
        }
    }

    //only allow platform admins to login_as, or session admins only for students (not teachers nor other admins)
    if (api_is_platform_admin() || (api_is_session_admin() && $row['6'] == $statusname[STUDENT])) {
        if (!$user_is_anonymous) {
            $result .= '<a href="user_list.php?action=login_as&amp;user_id=' . $user_id . '&amp;sec_token=' . $_SESSION['sec_token'] . '">' . Display::return_icon('pixel.gif', get_lang('LoginAs'), array('class' => 'actionplaceholdericon actionsloginas')) . '</a>&nbsp;&nbsp;';
        } else {
            $result .= Display::return_icon('pixel.gif', get_lang('LoginAs'), array('class' => 'actionplaceholdericon actionsloginas invisible')) . '&nbsp;&nbsp;';
        }
    } else {
        $result .= Display::return_icon('pixel.gif', get_lang('LoginAs'), array('class' => 'actionplaceholdericon actionsloginas invisible')) . '&nbsp;&nbsp;';
    }
    /* if ($row['7'] != $statusname[STUDENT]) {
      $result .= Display::return_icon('pixel.gif', get_lang('Reporting'),array("class" => "actionplaceholdericon actionstatistics_na")).'&nbsp;&nbsp;';
      } else {
      $result .= '<a href="../mySpace/myStudents.php?student='.$user_id.'">'.Display::return_icon('pixel.gif', get_lang('Reporting'), array('class' => 'actionplaceholdericon actiontracking')).'</a>&nbsp;&nbsp;';
      } */

    if (api_is_platform_admin()) {
        if (!$user_is_anonymous) {
            $result .= '<a href="user_edit.php?user_id=' . $user_id . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . '</a>&nbsp;&nbsp;';
        } else {
            $result .= Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit invisible')) . '</a>&nbsp;&nbsp;';
        }

        if ($row[0] <> $_user['user_id'] && $user_is_anonymous == false) {
            // you cannot lock yourself out otherwise you could disable all the accounts including your own => everybody is locked out and nobody can change it anymore.
            //$result .= '<a href="user_list.php?action=delete_user&amp;user_id=' . $user_id . '&amp;' . $url_params . '&amp;sec_token=' . $_SESSION['sec_token'] . '"  onclick="javascript:if(!confirm(' . "'" . addslashes(api_htmlentities(get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . "'" . ')) return false;">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>' . '&nbsp;&nbsp;';
                $link = 'user_list.php?action=delete_user&amp;user_id=' . $user_id . '&amp;' . $url_params . '&amp;sec_token=' . $_SESSION['sec_token'];
                $result .= '<a href="javascript:void(0);"  onclick="Alert_Confim_Delete(\''.$link.'\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>' . '&nbsp;&nbsp;';
                    
        } else {
            $result .= Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete invisible')) . '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
    }
    if ($is_admin) {
        //$result .= Display::return_icon('admin_star.png', get_lang('IsAdministrator'),array('width'=> 22, 'heigth'=> 22)).'&nbsp;&nbsp;';
    } else {
        //$result .= Display::return_icon('admin_star_na.png', get_lang('IsNotAdministrator')).'&nbsp;&nbsp;';
    }
    return $result;
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
        $action = 'lock';
        //	$image='right.png';
        $class = 'actionplaceholdericon actionsvalidate';
    } elseif ($active == '-1') {
        $action = 'edit';
        // $image='expired.gif';
        $class = 'actionplaceholdericon actionsuserexpired';
    } elseif ($active == '0') {
        $action = 'unlock';
        //	$image='wrong.png';
        $class = 'actionplaceholdericon actionslook';
    }

    if ($action == 'edit') {
        $result = Display::return_icon('pixel.gif', get_lang('AccountExpired'), array('class' => $class));
    } elseif ($row['0'] <> $_user['user_id']) { // you cannot lock yourself out otherwise you could disable all the accounts including your own => everybody is locked out and nobody can change it anymore.
        $result = '<a class="'.(intval($active) == 0 && !is_portal_attribute_valid('actived_users')?'sas-attribute-blocked':'').'" href="user_list.php?action=' . $action . '&user_id=' . $row['0'] . '&' . $url_params . '&sec_token=' . $_SESSION['sec_token'] . '">' . Display::return_icon('pixel.gif', get_lang(ucfirst($action)), array('class' => $class)) . '</a>';
    }
    return '<center>' . $result . '</center>';
}

/**
 * Lock or unlock a user
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $status, do we want to lock the user ($status=lock) or unlock it ($status=unlock)
 * @param int $user_id The user id
 * @return language variable
 */
function lock_unlock_user($status, $user_id) {
    $user_table = Database :: get_main_table(TABLE_MAIN_USER);
    if ($status == 'lock') {
        $log = LOG_USER_LOCK;
        $status_db = '0';
        $return_message = get_lang('UserLocked');
    }
    if ($status == 'unlock') {
        $log = LOG_USER_UNLOCK;
        $status_db = '1';
        $return_message = get_lang('UserUnlocked');
    }

    if (($status_db == '1' OR $status_db == '0') AND is_numeric($user_id)) {
        $sql = "UPDATE $user_table SET active='" . Database::escape_string($status_db) . "' WHERE user_id='" . Database::escape_string($user_id) . "'";
        $result = Database::query($sql, __FILE__, __LINE__);
    }

    $time = time();
    $user_id_manager = api_get_user_id();
    event_system($log, LOG_USER_ID, $user_id, $time, $user_id_manager);


    if ($result) {
        return $return_message;
    }
}

/**
 * unlock a user registered using a cheque
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $status_cheque, do we want to unlock the user ($status_cheque =1)
 * @param int $user_id The user id
 * @param int $cheque_id The id of the payment log table
 * @return language variable
 */
function unlocked_cheque_of_user($cheque_id, $user_id) {
    $payment_log_table = Database :: get_main_table(TABLE_MAIN_PAYMENT_LOG);
    $status_cheque = 1;
    $sql = "UPDATE $payment_log_table SET status='" . Database::escape_string($status_cheque) . "' WHERE id='" . intval($cheque_id) . "' AND user_id='" . intval($user_id) . "'";
    $result = Database::query($sql, __FILE__, __LINE__);

    if ($result) {
        return $return_message;
    }
}

/**
 * Instead of displaying the integer of the status, we give a translation for the status
 *
 * @param integer $status
 * @return string translation
 *
 * @version march 2008
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function status_filter($status) {
    $statusname = api_get_status_langvars();
    return $statusname[$status];
}

/**
  ==============================================================================
  INIT SECTION
  ==============================================================================
 */
$action = $_GET["action"];
$login_as_user_id = Security::remove_XSS($_GET["user_id"]);


// Login as ...
if ($_GET['action'] == "login_as" && isset($_GET["user_id"])) {
    login_user($login_as_user_id);
}

if (isset($_GET['search']) && $_GET['search'] == 'advanced') {
    $interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
    $interbreadcrumb[] = array("url" => 'user_list.php', "name" => get_lang('UserList'));
    $tool_name = get_lang('SearchAUser');
    Display :: display_header($tool_name);

    //Actions
    echo '<div class="actions">';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_list.php">' . Display::return_icon('pixel.gif', get_lang('UserList'), array('class' => 'toolactionplaceholdericon toolactionadminusers')) . get_lang('UserList') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_add.php">' . Display::return_icon('pixel.gif', get_lang('AddUsers'), array('class' => 'toolactionplaceholdericon toolactionaddusertocourse')) . get_lang('AddUsers') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_fields.php">' . Display::return_icon('pixel.gif', get_lang('ManageUserFields'), array('class' => 'toolactionplaceholdericon toolactionsprofile')) . get_lang('ManageUserFields') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_export.php">' . Display::return_icon('pixel.gif', get_lang('Export'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('Export') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_import.php">' . Display::return_icon('pixel.gif', get_lang('Import'), array('class' => 'toolactionplaceholdericon toolactionimportcourse')) . get_lang('Import') . '</a>';
    echo '</div>';

    echo '<div id="content">';
    //api_display_tool_title($tool_name);
    $form = new FormValidator('advanced_search', 'get');
    $form->addElement('header', '', $tool_name);
    $form->add_textfield('keyword_firstname', get_lang('FirstName'), false, 'class="focus"');
    $form->add_textfield('keyword_lastname', get_lang('LastName'), false);
    $form->add_textfield('keyword_username', get_lang('LoginName'), false);
    $form->add_textfield('keyword_email', get_lang('Email'), false);
    $form->add_textfield('keyword_officialcode', get_lang('OfficialCode'), false);
    $status_options = array();
    $status_options['%'] = get_lang('All');
    $status_options[STUDENT] = get_lang('Student');
    $status_options[COURSEMANAGER] = get_lang('Teacher');
    $status_options[SESSIONADMIN] = get_lang('Administrator'); //
    $form->addElement('select', 'keyword_status', get_lang('Status'), $status_options);
    $active_group = array();
    $active_group[] = $form->createElement('checkbox', 'keyword_active', '', get_lang('Active'));
    $active_group[] = $form->createElement('checkbox', 'keyword_inactive', '', get_lang('Inactive'));
    $form->addGroup($active_group, '', get_lang('ActiveAccount'), '<br/>', false);
    $form->addElement('style_submit_button', 'submit', get_lang('SearchUsers'), 'style="float:none; margin-right:10px;" class="search"');
    $defaults['keyword_active'] = 1;
    $defaults['keyword_inactive'] = 1;
    $form->setDefaults($defaults);
    $form->display();
    echo '</div>';
} else {
    $interbreadcrumb[] = array("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
    $tool_name = get_lang('UserList');
    Display :: display_header($tool_name, "");

    //Actions
    echo '<div class="actions">';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_add.php">' . Display::return_icon('pixel.gif', get_lang('AddUsers'), array('class' => 'toolactionplaceholdericon toolactionaddusertocourse')) . get_lang('AddUsers') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_fields.php">' . Display::return_icon('pixel.gif', get_lang('ManageUserFields'), array('class' => 'toolactionplaceholdericon toolactionsprofile')) . get_lang('ManageUserFields') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_export.php">' . Display::return_icon('pixel.gif', get_lang('Export'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('Export') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/user_import.php">' . Display::return_icon('pixel.gif', get_lang('Import'), array('class' => 'toolactionplaceholdericon toolactionimportcourse')) . get_lang('Import') . '</a>';
//    echo '<a href="#" id="btn-search ">' . Display::return_icon('pixel.gif', get_lang('Search'), array('class' => 'toolactionplaceholdericon toolactionsearch')) . get_lang('Search') . '</a>';
    echo '<a href="javascript:void(0)" id="btn-search">' . Display :: return_icon('pixel.gif', get_lang('Search'), array('class' => 'toolactionplaceholdericon toolactionsearch')) . get_lang('Search') . '</a>';
    echo '</div>';

    //api_display_tool_title($tool_name);
    if (isset($_GET['action'])) {
        $check = Security::check_token('get');
        if ($check) {
            switch ($_GET['action']) {
                case 'show_message' :
                    if (!empty($_GET['warn'])) {
                        // to prevent too long messages
                        if ($_GET['warn'] == 'session_message') {
                            $_GET['warn'] = $_SESSION['session_message_import_users'];
                        }
                        //Display::display_warning_message(urldecode($_GET['warn']), false, true);
                        $_SESSION['display_warning_message']=urldecode($_GET['warn']);
                    }
                    if (!empty($_GET['message'])) {
                        //Display :: display_confirmation_message(stripslashes($_GET['message']), false, true);
                        $_SESSION['display_confirmation_message']=stripslashes($_GET['message']);
                    }
                    break;
                case 'delete_user' :
                    if (api_is_platform_admin()) {
                        if ($user_id != $_user['user_id'] && UserManager :: delete_user($_GET['user_id'])) {
                            //Display :: display_confirmation_message(get_lang('UserDeleted'), false, true);
                            $_SESSION['display_confirmation_message']=get_lang('UserDeleted');
                        } else {
                            //Display :: display_error_message(get_lang('CannotDeleteUserBecauseOwnsCourse'), false, true);
                            $_SESSION['display_error_message']=get_lang('CannotDeleteUserBecauseOwnsCourse');
                        }
                    }
                    break;
                case 'lock' :
                    $message = lock_unlock_user('lock', $_GET['user_id']);
                    //Display :: display_normal_message($message, false, true);
                    $_SESSION['display_normal_message']=$message;
                    break;
                case 'unlock';
                    $message = lock_unlock_user('unlock', $_GET['user_id']);
                   // Display :: display_normal_message($message, false, true);
                    $_SESSION['display_normal_message']=$message;
                    break;
                case 'unlocked_cheque';
                    unlocked_cheque_of_user($_GET['cheque_id'], $_GET['user_id']);
                    break;
            }
            Security::clear_token();
        }
    }
    if (isset($_POST['action'])) {
        $check = Security::check_token('get');
        if ($check) {
            switch ($_POST['action']) {
                case 'delete' :
                    if (api_is_platform_admin()) {
                        $number_of_selected_users = count($_POST['id']);
                        $number_of_deleted_users = 0;
                        if (is_array($_POST['id'])) {
                            foreach ($_POST['id'] as $index => $user_id) {
                                if ($user_id != $_user['user_id']) {
                                    if (UserManager :: delete_user($user_id)) {
                                        $number_of_deleted_users++;
                                    }
                                }
                            }
                        }
                        if ($number_of_selected_users == $number_of_deleted_users) {
                            //Display :: display_confirmation_message(get_lang('SelectedUsersDeleted'), false, true);
                            $_SESSION['display_confirmation_message']=get_lang('SelectedUsersDeleted');
                        } else {
                            //Display :: display_error_message(get_lang('SomeUsersNotDeleted'), false, true);
                            $_SESSION['display_error_message']=get_lang('SomeUsersNotDeleted');
                        }
                    }
                    break;
            }
            Security::clear_token();
        }
    }


    echo '<div id="content">';
/*
 * ===============================================
 * DISPLAY MESSAGE
 * ===============================================
 */
if(isset($_SESSION['display_normal_message'])){
display::display_normal_message($_SESSION['display_normal_message'], false,true);
unset($_SESSION['display_normal_message']);
}
if(isset($_SESSION['display_warning_message'])){
display::display_warning_message($_SESSION['display_warning_message'], false,true);
unset($_SESSION['display_warning_message']);
}

if(isset($_SESSION['display_error_message'])){
display::display_error_message($_SESSION['display_error_message'], false,true);
unset($_SESSION['display_error_message']);
}
    echo '<div class="secondary-actions" style="height:auto;">';
    // extra field options
    $extra_fields = UserManager :: get_extra_fields(0, 50, 5, 'ASC');
    if (!empty($extra_fields)) {
        echo '<div id="secondary-actions-extra">';
        echo Tracking::display_additional_profile_fields(true, true, true);
        echo '</div>';
    }
    // search form
    echo '<div id="search" style="' . (isset($_GET['keyword']) ? 'display:block;' : 'display:none') . '" >';
    // Create a search-box
    
    $form = new FormValidator('search_simple', 'get', '', '', null, false);

    if (isset($_GET['additional_profile_field']) && count($_GET['additional_profile_field']) > 0) {
        $form->addElement('hidden', 'additional_profile_field_search');
        $defaults['additional_profile_field_search'] = implode(',', $_GET['additional_profile_field']);
        $form->setDefaults($defaults);
    }

    $renderer = & $form->defaultRenderer();
    $renderer->setElementTemplate('<span>{element}</span>');

    $form->addElement('text', 'keyword', get_lang('keyword'), 'id="keyword"');
    $form->addElement('style_submit_button', 'submit', get_lang('Search'), 'class="search" style="float: none; margin-right:5px;"');
    $form->addElement('static', 'search_advanced_link', null, '<span style="display:block; clear:both; margin-bottom:20px; "><a href="user_list.php?search=advanced">' . get_lang('AdvancedSearch') . '</span></a>');
    $form->display();
    echo '</div>';
    echo '</div>';

    if (isset($_GET['keyword'])) {
        $parameters = array('keyword' => Security::remove_XSS($_GET['keyword']));
    } elseif (isset($_GET['keyword_firstname'])) {
        $parameters['keyword_firstname'] = Security::remove_XSS($_GET['keyword_firstname']);
        $parameters['keyword_lastname'] = Security::remove_XSS($_GET['keyword_lastname']);
        $parameters['keyword_email'] = Security::remove_XSS($_GET['keyword_email']);
        $parameters['keyword_officialcode'] = Security::remove_XSS($_GET['keyword_officialcode']);
        $parameters['keyword_status'] = Security::remove_XSS($_GET['keyword_status']);
        $parameters['keyword_active'] = Security::remove_XSS($_GET['keyword_active']);
        $parameters['keyword_inactive'] = Security::remove_XSS($_GET['keyword_inactive']);
    }

    if (isset($_GET['additional_profile_field']) && count($_GET['additional_profile_field']) > 0) {
        $parameters['additional_profile_field'] = implode(',', $_GET['additional_profile_field']);
    }

    // Create a sortable table with user-data
    $parameters['sec_token'] = Security::get_token();

    // get the list of all admins to mark them in the users list
    $admin_table = Database::get_main_table(TABLE_MAIN_ADMIN);
    $sql_admin = "SELECT user_id FROM $admin_table";
    $res_admin = Database::query($sql_admin);
    $_admins_list = array();
    while ($row_admin = Database::fetch_row($res_admin)) {
        $_admins_list[] = $row_admin[0];
    }

    $image_path = UserManager::get_user_picture_path_by_id($user_id, 'web', false, true);
    $user_profile = UserManager::get_picture_user($user_id, $image_path['file'], 22, 'small_', ' width="22" height="22" ');
    if (!api_is_anonymous()) {
        $photo = '<center><a href="userInfo.php?' . api_get_cidreq() . '&origin=' . $origin . '&uInfo=' . $user_id . '" title="' . get_lang('Info') . '"  ><img src="' . $user_profile['file'] . '" ' . $user_profile['style'] . ' alt="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '"  title="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" /></a></center>';
    } else {
        $photo = '<center><img src="' . $user_profile['file'] . '" ' . $user_profile['style'] . ' alt="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" title="' . api_get_person_name($o_course_user['firstname'], $o_course_user['lastname']) . '" /></center>';
    }


    $count = get_number_of_users();
    $table = new SortableTable('users', 'get_number_of_users', 'get_user_data', (api_is_western_name_order() xor api_sort_by_first_name()) ? 4 : 3);
    $table->set_additional_parameters($parameters);
    $table->set_header(0, '', false);
    $table->set_header(1, get_lang('Photo'));
    $table->set_header(2, get_lang('OfficialCode'));
    if (api_is_western_name_order()) {
        $table->set_header(3, get_lang('FirstName'));
        $table->set_header(4, get_lang('LastName'));
    } else {
        $table->set_header(3, get_lang('LastName'));
        $table->set_header(4, get_lang('FirstName'));
    }
    $table->set_header(5, get_lang('LoginName'));
    $table->set_header(6, get_lang('Email'));
    $table->set_header(7, get_lang('Status'));
    $table->set_header(8, get_lang('Active'));

    $band = 9;
    $e_commerce_enabled = intval(api_get_setting("e_commerce"));
    if ($e_commerce_enabled <> 0) {
        $table->set_header(9, get_lang('Payment'));
        $band = 10;
    }


    if (isset($_GET['additional_profile_field']) && count($_GET['additional_profile_field']) > 0) {
        $table->set_header($band, 1, true);
        foreach ($_GET['additional_profile_field'] as $field_id) {
            $field_info = UserManager::get_extra_field_information($field_id);
            $table->set_header($band, $field_info['field_display_text'], true);
            $band++;
        }
    }

    $table->set_header($band, get_lang('Action'), false, 'width="200px"');
    $table->set_column_filter(6, 'email_filter');
    $table->set_column_filter(7, 'status_filter');
    $table->set_column_filter(8, 'active_filter');
    $table->set_column_filter($band, 'modify_filter');
    if (api_is_platform_admin()) {
        $table->set_form_actions(array('delete' => get_lang('DeleteFromPlatform')));
    }
    echo '<div class="row"><div class="form_header">' . get_lang('UserList') . '</div></div>';
    $table->display();
    echo '</div>';
}

//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';
// displaying the footer
echo '<div style="display:none;" id="html_user_info">';
echo '</div>';
if(isset($_SESSION['display_confirmation_message'])){
    display::display_confirmation_message2($_SESSION['display_confirmation_message'], false,true);
    unset($_SESSION['display_confirmation_message']);
}
Display :: display_footer();
?>
