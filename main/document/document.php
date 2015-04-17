<?php

// $Id: document.php 22201 2009-07-17 19:57:03Z cfasanando $

/* For licensing terms, see /dokeos_license.txt */


/**
  ==============================================================================
 * Main script for the documents tool
 *
 * This script allows the user to manage files and directories on your Dokeos installation
 *
 * The user can : - navigate through files and directories.
 * 				 - upload a file
 * 				 - delete, copy a file or a directory
 * 				 - edit properties & content (name, comments, html content)
 *
 * The script is organised in four sections.
 *
 * 1) Execute the command called by the user
 * 				Note: somme commands of this section are organised in two steps.
 * 			    The script always begins with the second step,
 * 			    so it allows to return more easily to the first step.
 *
 * 				Note (March 2004) some editing functions (renaming, commenting)
 * 				are moved to a separate page, edit_document.php. This is also
 * 				where xml and other stuff should be added.
 *
 * 2) Define the directory to display
 *
 * 3) Read files and directories from the directory defined in part 2
 * 4) Display all of that on an HTML page
 *
 * @todo eliminate code duplication between
 * document/document.php, scormdocument.php
 *
 * @package dokeos.document
  ==============================================================================
 */
// name of the language file that needs to be included
$language_file[] = 'document';
$language_file[] = 'slideshow';
$language_file[] = 'gradebook';

define('DOKEOS_DOCUMENT', true);

// include the global Dokeos file
require_once "../inc/global.inc.php";

// include additional libraries
require_once 'document.inc.php';
require_once '../inc/lib/usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';


// section (for the tabs)
$this_section = SECTION_COURSES;

api_protect_course_script(true);
//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.js" type="text/javascript" language="javascript"></script>'; //jQuery
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thickbox.css" type="text/css" media="screen" />';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/msdropdown/css/dd.css" type="text/css" media="screen" />';
//$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thickbox.js"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/dhtmlwindow.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/modal.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'jwplayer/jwplayer.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/msdropdown/js/jquery.dd.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" >
    /*<![CDATA[*/
     $("a.thickbox").live("click", function(e) {
//      var url = this.href;
//      var dialog = $(".dialog");
//      if ($(".dialog").length == 0) {
//     dialog = $("<div id=\"dialog\" style=\"display:hidden\"><\/div>").appendTo("body");
//      }   // load remote content
//        dialog.load(
//                url,
//                {},
//                function(responseText, textStatus, XMLHttpRequest) {
//                 dialog.html(responseText);
//                   dialog.dialog({
//                     modal: true,
//                     width:500,
//                     title:"' . get_lang('IntegrateDocInCourse') . '"
//                 });
//       }
//      );
//      //prevent the browser to follow the link
//      return false;

// LOAD SWF
        e.preventDefault();
            var url = this.href;
            var dat = $(this).text();
            //console.log($(this).text());
            var dialog = $(".dialog");
            //$(this).dialog("destroy").remove()
            $(".dialog").remove();
            if ($(".dialog").length == 0) {
            dialog = $("<div id=\"dialog\" style=\"display:hidden\"><\/div>").appendTo("body");
            }   // load remote content
              $.ajax({          
                    //contentType: "application/x-www-form-urlencoded",
                    type: "GET",
                    url: url,
                    data : "dat="+dat,
                    success: function(response) {       
                        //console.log(response);
                        dialog.html(response);
                            dialog.dialog({
                                modal: true,
                                height: "auto",
                                //title:"' . get_lang('IntegrateDocInCourse') . '",
                                title: dat,
                                width: "500",
                                resizable: false,
                                draggable: false ,
                                closeOnEscape: true,
                                    close: function(event, ui){
                                        $(this).dialog("destroy");
                                        $(this).remove();
                                }
                            });
                    }
                });
    });
    /*]]>*/
       </script>';
$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="' . api_get_path(WEB_PATH) . 'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
$htmlHeadXtra[] = '<script  type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
    /*<![CDATA[*/
        $(document).ready(function() {
        $("#curdirpath_id").change(function() {
           $("#selector").submit();
        });
        $(".open_media_audio_in_window").click(function(e){
            var attr_info = $(this).attr("id");
            var content_info = attr_info.split("media_content_");
            var content_id = content_info[1];
            var object_id = "#popUpDiv"+content_id;
            $(object_id).dialog({
                width:430 ,
                height:90 ,
                modal: true,
                resizable: false,
                /*close: function() {location.reload();}*/
                close: function() {}
            });
        });
        $(".open_media_video_in_window").click(function(e){
            var attr_info = $(this).attr("id");
            var content_info = attr_info.split("media_content_");
            var content_id = content_info[1];
            var object_id = "#popUpDiv"+content_id;
            $(object_id).dialog({
                width:430 ,
                height:355 ,
                modal: true,
                resizable: false,
                /*close: function() {location.reload();}*/
                close: function() {}
            });
        });
        $("select#curdirpath_id").msDropdown();
    });
    /*]]>*/
</script>';

$htmlHeadXtra[] = "<script>
    $(function() {
        $('#create_dir').click(function(e) {
            if ($('#dirname').val() == '')
                $('#dirname').focus();
            else
                $('#create_folder').submit();
        });
    });
</script>";

//session
if (isset($_GET['id_session']))
    $_SESSION['id_session'] = Security::remove_XSS($_GET['id_session']);


// Is the document tool visible?
// Check whether the tool is actually visible
$table_course_tool = Database::get_course_table(TABLE_TOOL_LIST, $_course['dbName']);
$tool_sql = 'SELECT visibility FROM ' . $table_course_tool . ' WHERE name = "' . TOOL_DOCUMENT . '" LIMIT 1';
$tool_result = api_sql_query($tool_sql, __FILE__, __LINE__);
$tool_row = Database::fetch_array($tool_result);
$tool_visibility = $tool_row['visibility'];
if ($tool_visibility == '0' && $to_group_id == '0' && !($is_allowed_to_edit || $group_member_with_upload_rights)) {
    api_not_allowed(true);
}

$htmlHeadXtra[] =
        "<script type=\"text/javascript\">
function confirmation (name)
{
	if (confirm(\" " . get_lang("AreYouSureToDelete") . " \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>";

/*
  -----------------------------------------------------------
  Variables
  - some need defining before inclusion of libraries
  -----------------------------------------------------------
 */

//what's the current path?
//we will verify this a bit further down
if (isset($_GET['curdirpath']) && $_GET['curdirpath'] != '') {
    $curdirpath = Security::remove_XSS($_GET['curdirpath']);
} elseif (isset($_POST['curdirpath']) && $_POST['curdirpath'] != '') {
    $curdirpath = Security::remove_XSS($_POST['curdirpath']);
} else {
    $curdirpath = '/';
}

$curdirpathurl = $curdirpath;

$action = $_GET['action'];
switch ($action) {
    case 'createCms':
        header('Location: ' . api_get_path(WEB_PATH) . 'main/index.php?module=cms&cmd=Newpage&func=createPage&' . api_get_cidreq());
        break;
    case 'listCms':
        header('Location: ' . api_get_path(WEB_PATH) . 'main/index.php?module=cms&cmd=Index&' . api_get_cidreq());
        break;
}

$course_dir = $_course['path'] . "/document";
$sys_course_path = api_get_path(SYS_COURSE_PATH);
$base_work_dir = $sys_course_path . $course_dir;
$http_www = api_get_path('WEB_COURSE_PATH') . $_course['path'] . '/document';
$dbl_click_id = 0; // used to avoid double-click
$is_allowed_to_edit = api_is_allowed_to_edit();
$group_member_with_upload_rights = false;

//if the group id is set, we show them group documents
if (isset($_SESSION['_gid']) && $_SESSION['_gid'] != '') {
    //needed for group related stuff
    include_once(api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php');
    //get group info
    $group_properties = GroupManager::get_group_properties($_SESSION['_gid']);
    $noPHP_SELF = true;
    //let's assume the user cannot upload files for the group
    $group_member_with_upload_rights = false;

    if ($group_properties['doc_state'] == 2) { //documents are private
        if ($is_allowed_to_edit || GroupManager :: is_user_in_group($_user['user_id'], $_SESSION['_gid'])) { //only courseadmin or group members (members + tutors) allowed
            $to_group_id = $_SESSION['_gid'];
            $req_gid = '&gidReq=' . $_SESSION['_gid'];
            $interbreadcrumb[] = array("url" => "../group/group.php", "name" => get_lang('Groups'));
            $interbreadcrumb[] = array("url" => "../group/group_space.php?gidReq=" . $_SESSION['_gid'], "name" => get_lang('GroupSpace') . ' (' . $group_properties['name'] . ')');
            //they are allowed to upload
            $group_member_with_upload_rights = true;
        } else {
            $to_group_id = 0;
            $req_gid = '';
        }
    } elseif ($group_properties['doc_state'] == 1) {  //documents are public
        $to_group_id = $_SESSION['_gid'];
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
        $interbreadcrumb[] = array("url" => "../group/group.php", "name" => get_lang('Groups'));
        $interbreadcrumb[] = array("url" => "../group/group_space.php?gidReq=" . $_SESSION['_gid'], "name" => get_lang('GroupSpace') . ' (' . $group_properties['name'] . ')');
        //allowed to upload?
        if ($is_allowed_to_edit || GroupManager::is_subscribed($_user['user_id'], $_SESSION['_gid'])) { //only courseadmin or group members can upload
            $group_member_with_upload_rights = true;
        }
    } else { //documents not active for this group
        $to_group_id = 0;
        $req_gid = '';
    }
    $_SESSION['group_member_with_upload_rights'] = $group_member_with_upload_rights;
} else {
    $to_group_id = 0;
    $req_gid = '';
}
/*
  -----------------------------------------------------------
  Libraries
  -----------------------------------------------------------
 */
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

require_once api_get_path(LIBRARY_PATH) . 'fileDisplay.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'tablesort.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';


//-----------------------------------------------------------
//check the path
//if the path is not found (no document id), set the path to /
if (!DocumentManager::get_document_id($_course, $curdirpath)) {
    $curdirpath = '/';
    //urlencoded version
    $curdirpathurl = '%2F';
}
//if they are looking at group documents they can't see the root
if ($to_group_id != 0 && $curdirpath == '/') {
    $curdirpath = $group_properties['directory'];
    $curdirpathurl = Security::remove_XSS($group_properties['directory']);
}
//-----------------------------------------------------------
// check visibility of the current dir path. Don't show anything if not allowed
if (!(DocumentManager::is_visible($curdirpath, $_course) || $is_allowed_to_edit)) {
    api_not_allowed();
}
/*
  -----------------------------------------------------------
  Constants and variables
  -----------------------------------------------------------
 */

$course_quota = DocumentManager::get_course_quota();

/*
  ==============================================================================
  MAIN SECTION
  ==============================================================================
 */

//-------------------------------------------------------------------//
if (isset($_GET['action']) && $_GET['action'] == "download") {
    $my_get_id = Security::remove_XSS($_GET['id']);
    //check if the document is in the database
    if (!DocumentManager::get_document_id($_course, $my_get_id)) {
        //file not found!
        header('HTTP/1.0 404 Not Found');
        $error404 = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">';
        $error404 .= '<html><head>';
        $error404 .= '<title>404 Not Found</title>';
        $error404 .= '</head><body>';
        $error404 .= '<h1>Not Found</h1>';
        $error404 .= '<p>The requested URL was not found on this server.</p>';
        $error404 .= '<hr>';
        $error404 .= '</body></html>';
        echo($error404);
        exit;
    }
    // launch event
    event_download($my_get_id);

    // check visibility of document and paths
    if (!($is_allowed_to_edit || $group_member_with_upload_rights) && !DocumentManager::is_visible($my_get_id, $_course)) {
        api_not_allowed();
    }

    $doc_url = $my_get_id;
    $full_file_name = $base_work_dir . $doc_url;
    DocumentManager::file_send_for_download($full_file_name, true);
    exit;
}
//-------------------------------------------------------------------//
//download of an completed folder
if (isset($_GET['action']) && $_GET['action'] == "downloadfolder") {
    include_once 'downloadfolder.inc.php';
}
//-------------------------------------------------------------------//
// slideshow inititalisation
$_SESSION['image_files_only'] = '';
$image_files_only = array();

/*
  -----------------------------------------------------------
  Header
  -----------------------------------------------------------
 */

//------interbreadcrumb for the current directory root path

$dir_array = explode("/", $curdirpath);
$array_len = count($dir_array);

$dir_acum = '';
for ($i = 0; $i < $array_len; $i++) {
    if ($dir_array[$i] == 'shared_folder') {
        $dir_array[$i] = get_lang('SharedFolder');
    } elseif (strstr($dir_array[$i], 'sf_user_')) {
        $userinfo = Database::get_user_info_from_id(substr($dir_array[$i], 8));
        $dir_array[$i] = $userinfo['lastname'] . ', ' . $userinfo['firstname'];
    }
    $url_dir = 'document.php?&curdirpath=' . $dir_acum . $dir_array[$i];
    $dir_acum.=$dir_array[$i] . '/';
}

Display::display_tool_header('', 'Doc');

/*
 * Lib for event log, stats & tracking
 * plus record of the access
 */
event_access_tool(TOOL_DOCUMENT);

/*
  ==============================================================================
  DISPLAY
  ==============================================================================
 */
if ($to_group_id != 0) { //add group name after for group documents
    $add_group_to_title = ' (' . $group_properties['name'] . ')';
}
//api_display_tool_title($tool_name.$add_group_to_title);

/*
  -----------------------------------------------------------
  Introduction section
  (editable by course admins)
  -----------------------------------------------------------
 */
$fck_attribute['Width'] = '100%';
$fck_attribute['Height'] = '300';
$fck_attribute['ToolbarSet'] = 'Introduction';
if (!empty($_SESSION['_gid'])) {
    Display::display_introduction_section(TOOL_DOCUMENT . $_SESSION['_gid']);
} else {
    Display::display_introduction_section(TOOL_DOCUMENT);
}
$fck_attribute = null; // Clearing this global variable immediatelly after it has been used.

/* ============================================================================ */

if ($is_allowed_to_edit) { // TEACHER ONLY
    /* ======================================
      MOVE FILE OR DIRECTORY
      ====================================== */
    $my_get_move = Security::remove_XSS($_GET['move']);
    if (isset($_GET['move']) && $_GET['move'] != '') {
        if (!$is_allowed_to_edit) {
            if (DocumentManager::check_readonly($_course, $_user['user_id'], $my_get_move)) {
                api_not_allowed();
            }
        }

        if (DocumentManager::get_document_id($_course, $my_get_move)) {
            $folders = DocumentManager::get_all_document_folders($_course, $to_group_id, $is_allowed_to_edit || $group_member_with_upload_rights);
            //	echo '<div class="row"><div class="form_header">'.get_lang('Move').'</div></div>';
            echo build_move_to_selector($folders, Security::remove_XSS($_GET['curdirpath']), $my_get_move, $group_properties['directory']);
        }
    }

    if (isset($_POST['move_to']) && isset($_POST['move_file'])) {
        if (!$is_allowed_to_edit) {
            if (DocumentManager::check_readonly($_course, $_user['user_id'], $my_get_move)) {
                api_not_allowed();
            }
        }

        include_once(api_get_path(LIBRARY_PATH) . 'fileManage.lib.php');
        //this is needed for the update_db_info function
        //$dbTable = $_course['dbNameGlu']."document";
        $dbTable = Database::get_course_table(TABLE_DOCUMENT);

        //security fix: make sure they can't move files that are not in the document table
        if (DocumentManager::get_document_id($_course, $_POST['move_file'])) {
            if (move($base_work_dir . $_POST['move_file'], $base_work_dir . $_POST['move_to'])) {
                update_db_info("update", $_POST['move_file'], $_POST['move_to'] . "/" . basename($_POST['move_file']));
                //set the current path
                $curdirpath = $_POST['move_to'];
                $curdirpathurl = Security::remove_XSS($_POST['move_to']);
                //Display::display_confirmation_message(get_lang('DirMv'), false, true);
                $_SESSION["show_message"] = get_lang('DirMv');
            } else {
                //Display::display_error_message(get_lang('Impossible'), false, true);
                $_SESSION["show_message_error"] = get_lang('Impossible');
            }
        } else {
            //Display::display_error_message(get_lang('Impossible'), false, true);
            $_SESSION["show_message_error"] = get_lang('Impossible');
        }
    }

    /* ======================================
      DELETE FILE OR DIRECTORY
      ====================================== */

    if (isset($_GET['delete'])) {
        if (!$is_allowed_to_edit) {
            if (DocumentManager::check_readonly($_course, $_user['user_id'], $_GET['delete'], '', true)) {
                api_not_allowed();
            }
        }

        include_once(api_get_path(LIBRARY_PATH) . 'fileManage.lib.php');

        if (DocumentManager::delete_document($_course, $_GET['delete'], $base_work_dir)) {
            if (isset($_GET['type']) && $_GET['type'] == 'media') {
                echo '<script type="text/javascript">window.location.href="mediabox_view.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']) . '&slide_id=' . Security::remove_XSS($_GET['slide_id']) . '&msg=DEL"</script>';
            } elseif (isset($_GET['type']) && $_GET['type'] == 'list') {
                //Display::display_confirmation_message(get_lang('DocDeleted'), false, true);
                $_SESSION["show_message"] = get_lang('DocDeleted');
            } else {
                echo '<script type="text/javascript">window.location.href="slideshow.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']);
                if ($_GET['new_id'] != '') {
                    echo "&slide_id=" . Security::remove_XSS($_GET['new_id']);
                }
                echo "&msg=DEL'</script>";
            }
        } else {
            //	Display::display_error_message(get_lang('DocDeleteError'));
            if (isset($_GET['type']) && $_GET['type'] == 'media') {
                echo '<script type="text/javascript">window.location.href="mediabox_view.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']) . '&slide_id=' . Security::remove_XSS($_GET['slide_id']) . '&msg=ERR"</script>';
            } elseif (isset($_GET['type']) && $_GET['type'] == 'list') {
                //Display::display_confirmation_message(get_lang('DocDeleteError'), false, true);
                $_SESSION["show_message"] = get_lang('DocDeleteError');
            } else {
                echo '<script type="text/javascript">window.location.href="slideshow.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']) . '&slide_id=' . Security::remove_XSS($_GET['slide_id']) . '&msg=ERR"</script>';
            }
        }
    }

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':

                foreach ($_POST['path'] as $index => $path) {
                    if (!$is_allowed_to_edit) {
                        if (DocumentManager::check_readonly($_course, $_user['user_id'], $path)) {
                            //Display::display_error_message(get_lang('CantDeleteReadonlyFiles'));
                            $_SESSION["show_message_error"] = get_lang('CantDeleteReadonlyFiles');
                            break 2;
                        }
                    }
                }

                foreach ($_POST['path'] as $index => $path) {
                    // These folders are not allowed be removed, because we need that folders in the mediabox tool
                    if (strcmp($path, '/audio') === 0 or strcmp($path, '/flash') === 0 or strcmp($path, '/images') === 0 or strcmp($path, '/shared_folder') === 0 or strcmp($path, '/video') === 0 or strcmp($path, '/screencasts') === 0 or strcmp($path, '/animations') === 0 or strcmp($path, '/mascot') === 0 or strcmp($path, '/mindmaps') === 0 or strcmp($path, '/podcasts') === 0 or strcmp($path, '/photos') === 0) {
                        continue;
                    } else {
                        $delete_document = DocumentManager::delete_document($_course, $path, $base_work_dir);
                    }
                }
                if (!empty($delete_document)) {
                    //Display::display_confirmation_message(get_lang('DocDeleted'), false, true);
                    $_SESSION["show_message"] = get_lang('DocDeleted');
                }
                break;
        }
    }

    /* ======================================
      CREATE DIRECTORY
      ====================================== */

    //create directory with $_POST data
    if ($_POST['dirname'] != '') {
        //needed for directory creation
        include_once(api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php');
        $post_dir_name = str_replace('?', '', Database::escape_string(Security::remove_XSS($_POST['dirname'])));

        if ($post_dir_name == '../' || $post_dir_name == '.' || $post_dir_name == '..') {
            //Display::display_error_message(get_lang('CannotCreateDir'));
            $_SESSION["show_message_error"] = get_lang('CannotCreateDir');
        } else {
            $added_slash = ($curdirpath == '/') ? '' : '/';
            $dir_name = $curdirpath . $added_slash . replace_dangerous_char($post_dir_name, 'strict');
            $clean_val = disable_dangerous_file($dir_name);
            $clean_val = replace_accents($dir_name);
            $dir_name = $clean_val;
            $dir_check = $base_work_dir . '' . $dir_name;

            if (!is_dir($dir_check)) {
                $created_dir = create_unexisting_directory($_course, $_user['user_id'], $to_group_id, $to_user_id, $base_work_dir, $dir_name, $post_dir_name);

                if ($created_dir) {
                    //Display::display_confirmation_message('<span title="' . $created_dir . '">' . get_lang('DirCr') . '</span>', false, true);
                    $_SESSION["show_message"] = '<span title="' . $created_dir . '">' . get_lang('DirCr') . '</span>';
                } else {
                    //Display::display_error_message(get_lang('CannotCreateDir'), false, true);
                    $_SESSION["show_message_error"] = get_lang('CannotCreateDir');
                }
            } else {
                //Display::display_error_message(get_lang('CannotCreateDir'), false, true);
                $_SESSION["show_message_error"] = get_lang('CannotCreateDir');
            }
        }
    }

    //show them the form for the directory name
    if (isset($_GET['createdir'])) {
        //show the form
        echo create_dir_form();
    }


    /* ======================================
      VISIBILITY COMMANDS
      ====================================== */

    if (isset($_GET['shf'])) {
        $shf = Database::escape_string($_GET['shf']);
        $TABLE_COURSE_SETTING = Database::get_course_table(TABLE_COURSE_SETTING, $_course['dbName']);
        $sql = "UPDATE $TABLE_COURSE_SETTING SET  value = '$shf' WHERE variable = 'show_hidden_files'  ";
        Database::query($sql, __FILE__, __LINE__);
        echo '<script>
        window.location.href = "' . api_get_path(WEB_CODE_PATH) . 'document/document.php?' . api_get_cidreq() . '"; 
    </script>';
    }
    if ((isset($_GET['set_invisible']) && !empty($_GET['set_invisible'])) || (isset($_GET['set_visible']) && !empty($_GET['set_visible'])) AND $_GET['set_visible'] <> '*' AND $_GET['set_invisible'] <> '*') {
        //make visible or invisible?
        if (isset($_GET['set_visible'])) {
            $update_id = Security::remove_XSS($_GET['set_visible']);
            $visibility_command = 'visible';
        } else {
            $update_id = Security::remove_XSS($_GET['set_invisible']);
            $visibility_command = 'invisible';
        }

        if (!$is_allowed_to_edit) {
            if (DocumentManager::check_readonly($_course, $_user['user_id'], '', $update_id)) {
                api_not_allowed();
            }
        }

        //update item_property to change visibility
        if (api_item_property_update($_course, TOOL_DOCUMENT, $update_id, $visibility_command, $_user['user_id'])) {
            //Display::display_confirmation_message(get_lang("ViMod"));
            if (isset($_GET['type']) && $_GET['type'] == 'media') {
                echo '<script type="text/javascript">window.location.href="mediabox_view.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']) . '&msg=ViMod&slide_id=' . $_GET['slide_id'] . '"</script>';
            } elseif (isset($_GET['type']) && $_GET['type'] == 'list') {
                Display::display_confirmation_message(get_lang("ViMod"));
            } else {
                echo '<script type="text/javascript">window.location.href="slideshow.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']) . '&msg=ViMod&slide_id=' . $_GET['slide_id'] . '"</script>';
            }
        } else {
            //Display::display_error_message(get_lang("ViModProb"));
            if (isset($_GET['type']) && $_GET['type'] == 'media') {
                echo '<script type="text/javascript">window.location.href="mediabox_view.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']) . '&msg=ViModProb&slide_id=' . $_GET['slide_id'] . '"</script>';
            } elseif (isset($_GET['type']) && $_GET['type'] == 'list') {
                //Display::display_error_message(get_lang("ViModProb"));
                $_SESSION["show_message_error"] = get_lang("ViModProb");
            } else {
                echo '<script type="text/javascript">window.location.href="slideshow.php?' . api_get_cidReq() . '&curdirpath=' . urldecode($_GET['curdirpath']) . '&msg=ViModProb&slide_id=' . $_GET['slide_id'] . '"</script>';
            }
        }
    }


    /* ======================================
      TEMPLATE ACTION
      ====================================== */

    if (isset($_GET['add_as_template']) && !isset($_POST['create_template'])) {

        $document_id_for_template = intval($_GET['add_as_template']);
        //create the form that asks for the directory name
        $template_text = '<form  class="outer_form" name="set_document_as_new_template" enctype="multipart/form-data" action="' . api_get_self() . '?' . api_get_cidreq() . '&add_as_template=' . $document_id_for_template . '" method="post">';
        $template_text .= '<input type="hidden" name="curdirpath" value="' . $curdirpath . '" />';
        $template_text .= '<table><tr><td>';
        $template_text .= get_lang('TemplateName') . ' : </td>';
        $template_text .= '<td><input type="text" name="template_title" /></td></tr>';
        //$template_text .= '<tr><td>'.get_lang('TemplateDescription').' : </td>';
        //$template_text .= '<td><textarea name="template_description"></textarea></td></tr>';
        $template_text .= '<tr><td>' . get_lang('TemplateImage') . ' : </td>';
        $template_text .= '<td><input type="file" name="template_image" id="template_image" /></td></tr>';
        $template_text .= '</table>';
        $template_text .= '<button style="margin-right:10px" type="submit" class="add" name="create_template">' . get_lang('CreateTemplate') . '</button>';
        $template_text .= '</form>';
        //show the form
        //Display::display_normal_message($template_text,false);
        // Display form for create templates
        echo $template_text;
    } elseif (isset($_GET['add_as_template']) && isset($_POST['create_template'])) {

        $document_id_for_template = intval(Database::escape_string($_GET['add_as_template']));

        $title = Security::remove_XSS($_POST['template_title']);
        //$description = Security::remove_XSS($_POST['template_description']);
        $course_code = api_get_course_id();
        $user_id = api_get_user_id();

        // create the template_thumbnails folder in the upload folder (if needed)
        if (!is_dir(api_get_path(SYS_CODE_PATH) . 'upload/template_thumbnails/')) {
            $perm = api_get_setting('permissions_for_new_directories');
            $perm = octdec(!empty($perm) ? $perm : '0770');
            $res = @mkdir(api_get_path(SYS_CODE_PATH) . 'upload/template_thumbnails/', $perm);
        }

        // upload the file
        if (!empty($_FILES['template_image']['name'])) {
            include_once (api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php');
            $upload_ok = process_uploaded_file($_FILES['template_image']);

            if ($upload_ok) {
                // Try to add an extension to the file if it hasn't one
                $new_file_name = $_course['sysCode'] . '-' . add_ext_on_mime(stripslashes($_FILES['template_image']['name']), $_FILES['template_image']['type']);

                // upload dir
                $upload_dir = api_get_path(SYS_CODE_PATH) . 'upload/template_thumbnails/';

                // resize image to max default and end upload
                require_once (api_get_path(LIBRARY_PATH) . 'image.lib.php');
                $temp = new image($_FILES['template_image']['tmp_name']);
                $picture_infos = getimagesize($_FILES['template_image']['tmp_name']);

                $max_width_for_picture = 100;

                if ($picture_infos[0] > $max_width_for_picture) {
                    $thumbwidth = $max_width_for_picture;
                    if (empty($thumbwidth) or $thumbwidth == 0) {
                        $thumbwidth = $max_width_for_picture;
                    }
                    $new_height = round(($thumbwidth / $picture_infos[0]) * $picture_infos[1]);

                    $temp->resize($thumbwidth, $new_height, 0);
                }

                $type = $picture_infos[2];

                switch (!empty($type)) {
                    case 2 : $temp->send_image('JPG', $upload_dir . $new_file_name);
                        break;
                    case 3 : $temp->send_image('PNG', $upload_dir . $new_file_name);
                        break;
                    case 1 : $temp->send_image('GIF', $upload_dir . $new_file_name);
                        break;
                }
            }
        }

        DocumentManager::set_document_as_template($title, $description, $document_id_for_template, $course_code, $user_id, $new_file_name);
        //Display::display_confirmation_message(get_lang('DocumentSetAsTemplate'), false, true);
        $_SESSION["show_message"] = get_lang('DocumentSetAsTemplate');
    }

    if (isset($_GET['remove_as_template'])) {
        $document_id_for_template = intval($_GET['remove_as_template']);
        $course_code = api_get_course_id();
        $user_id = api_get_user_id();
        DocumentManager::unset_document_as_template($document_id_for_template, $course_code, $user_id);
        //Display::display_confirmation_message(get_lang('DocumentUnsetAsTemplate'), false, true);
        $_SESSION["show_message"] = get_lang('DocumentUnsetAsTemplate');
    }
} // END is allowed to edit
//Build form to Add doc to course
if (isset($_GET['add_to_course']) && isset($_POST['doc_add'])) {
    $doc_id = Security::remove_XSS($_GET['add_to_course']);
    $doc_name = $_POST['doc_name'];
    list($lp_name, $lp_id) = explode("@", $_POST['course']);
    $tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
    $query = "SELECT max(id) AS maxid FROM " . $tbl_lp_item;
    $result = api_sql_query($query, __FILE__, __LINE__);
    while ($obj = Database::fetch_object($result)) {
        $maxid = $obj->maxid;
    }
    $query = "SELECT max(display_order) AS disporder FROM " . $tbl_lp_item . " WHERE lp_id=" . $lp_id;
    $result = api_sql_query($query, __FILE__, __LINE__);
    while ($obj = Database::fetch_object($result)) {
        $display_order = $obj->disporder;
    }

    $sql = "INSERT INTO " . $tbl_lp_item . " (lp_id,
						item_type,
						ref,
						title,
						description,
						path,
						previous_item_id,
						next_item_id,
						display_order) VALUES(" . $lp_id . ",
						'document',
						'" . ($maxid + 1) . "',
						'" . Database::escape_string($doc_name) . "',
						'',
						'" . Database::escape_string($doc_id) . "',
						'" . $maxid . "',
						" . ($maxid + 2) . ",
						" . ($display_order + 1) . ")";
    $result = Database::query($sql, __FILE__, __LINE__);
}//End

/*
  -----------------------------------------------------------
  GET ALL DOCUMENT DATA FOR CURDIRPATH
  -----------------------------------------------------------
 */

$docs_and_folders = DocumentManager::get_all_document_data($_course, $curdirpath, 0, NULL, $is_allowed_to_edit || $group_member_with_upload_rights);

$folders = DocumentManager::get_all_document_folders($_course, $to_group_id, $is_allowed_to_edit || $group_member_with_upload_rights);
if ($folders === false) {
    $folders = array();
}
//	echo '<div class="actions">';
echo '<div>';

if (isset($docs_and_folders) && is_array($docs_and_folders)) {
    //*************************************************************************************************
    //do we need the title field for the document name or not?
    //we get the setting here, so we only have to do it once
    $use_document_title = api_get_setting('use_document_title');
    //create a sortable table with our data
    $sortable_data = array();

    $i = 0;

    //evaluated if folder is visible
    $tbl_document = Database::get_course_table(TABLE_DOCUMENT);
    $TABLE_ITEMPROPERTY = Database::get_course_table(TABLE_ITEM_PROPERTY);
    $query_visible = "SELECT DISTINCT i.visibility,d.path FROM $tbl_document d INNER JOIN $TABLE_ITEMPROPERTY i ON i.ref=d.id WHERE d.path = '$curdirpathurl' ";
    $result_visible = Database::query($query_visible, __FILE__, __LINE__);
    $row_visible = Database::fetch_array($result_visible);
    $row_visible = $row_visible['visibility'];
    if ($row_visible != 0 || $row_visible == null || api_is_platform_admin() || api_is_allowed_to_edit()) { //folder is visible
        while (list ($key, $id) = each($docs_and_folders)) {
            if ($id['visibility'] == 0) {
                if (!$is_allowed_to_edit) {
                    continue;
                }
            }
            $row = array();

            //if the item is invisible, wrap it in a span with class invisible
            $invisibility_span_open = ($id['visibility'] == 0) ? '<span class="invisible">' : '';
            $invisibility_span_close = ($id['visibility'] == 0) ? '&nbsp;</span>' : '';
            //size (or total size of a directory)
            $size = $id['filetype'] == 'folder' ? get_total_folder_size($id['path'], $is_allowed_to_edit) : $id['size'];
            //get the title or the basename depending on what we're using
            if ($use_document_title == 'true' AND $id['title'] <> '') {
                $document_name = $id['title'];
            } else {
                $document_name = basename($id['path']);
                //TODO: check if is also necessary (above else)
                if (strstr($document_name, 'sf_user_')) {
                    $userinfo = Database::get_user_info_from_id(substr($document_name, 8));
                    $document_name = $userinfo['lastname'] . ', ' . $userinfo['firstname'];
                } elseif (strstr($document_name, 'shared_folder')) {
                    $document_name = get_lang('SharedFolder');
                }
            }
            $exists = end(explode('/', $id['path']));
            if ($exists != '') {
                //data for checkbox
                if (($is_allowed_to_edit || $group_member_with_upload_rights) && count($docs_and_folders) >= 1) {
                    $row[] = $id['path'];
                }

                // Show the Owner of the file only in groups
                $user_link = '';

                if (isset($_SESSION['_gid']) && $_SESSION['_gid'] != '') {
                    if (!empty($id['insert_user_id'])) {
                        $user_info = UserManager::get_user_info_by_id($id['insert_user_id']);
                        $user_name = $user_info['firstname'] . ' ' . $user_info['lastname'];
                        $user_link = '<div class="document_owner">' . get_lang('Owner') . ': ' . display_user_link_document($id['insert_user_id'], $user_name) . '</div>';
                    }
                }

                //icons (clickable)
                $row[] = create_document_link($curdirpath, $http_www, $document_name, $id['path'], $id['filetype'], $size, $id['visibility'], true, $id['id'], $i);
                //$row[]= build_document_icon_tag($id['filetype'],$id['path']);
                //document title with hyperlink
                $row[] = create_document_link($curdirpath, $http_www, $document_name, $id['path'], $id['filetype'], $size, $id['visibility'], false, $id['id'], $i) . '<br />' . $invisibility_span_open . nl2br(htmlspecialchars($id['comment'], ENT_QUOTES, $charset)) . $invisibility_span_close . $user_link;

                //comments => display comment under the document name
                //$row[] = $invisibility_span_open.nl2br(htmlspecialchars($id['comment'])).$invisibility_span_close;
                $display_size = format_file_size($size);
                $row[] = '<span style="display:none;">' . $size . '</span>' . $invisibility_span_open . $display_size . $invisibility_span_close;

                //last edit date
                $last_edit_date = $id['lastedit_date'];
                //	$display_date = date_to_str_ago($last_edit_date).'<br><span class="dropbox_date">'.$last_edit_date.'</span>';
                $display_date = date_to_str_ago($last_edit_date);
                $row[] = $invisibility_span_open . $display_date . $invisibility_span_close;


                //admins get an edit column
                if ($is_allowed_to_edit) {
                    $is_template = (isset($id['is_template']) ? $id['is_template'] : false);
                    // if readonly, check if it the owner of the file or if the user is an admin
                    if ($id['insert_user_id'] == $_user['user_id'] || api_is_platform_admin()) {
                        $edit_icons = build_edit_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, 0, $_GET['selectcat']);
                        $move_icons = build_move_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, 0, $_GET['selectcat']);
                        $visible_icons = build_visible_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, 0, $_GET['selectcat']);
                        $template_icons = build_template_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, 0);
                    } else {
                        $edit_icons = build_edit_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, $id['readonly'], $_GET['selectcat']);
                        $move_icons = build_move_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, $id['readonly'], $_GET['selectcat']);
                        $visible_icons = build_visible_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, $id['readonly'], $_GET['selectcat']);
                        $template_icons = build_template_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $is_template, $id['readonly']);
                    }
                    $row[] = '<div align="center">' . $edit_icons . '</div>';
                    $row[] = '<div align="center">' . $move_icons . '</div>';
                    $row[] = '<div align="center">' . $visible_icons . '</div>';
                    if ($template_icons == '') {
                        $row[] = ' ';
                    } else {
                        $row[] = $template_icons;
                    }
                }
                //new row to add course icon
                $docid = $id['id'];
                $tmpext = explode(".", $id['path']);
                $tmpext[1] = strtolower($tmpext[1]);
                /* if ($id['filetype'] == 'file' && preg_match("@(html|mp3|wma|wav|mpg|mpeg|avi|vob|mp4|flv|mkv|3gp|mov|swf|jpg|jpeg|gif|png|bmp|tar|zip|rar|csv|xls|xlsx|xlsx|doc|docx|ppt|pptx|pdf|txt)$@", $tmpext[1])) {
                  $row[] = build_addcourse_icons($curdirpath, $id['filetype'], $id['path'], $id['visibility'], $key, $document_name, $id['readonly']);
                  } else {
                  $row[] = ' ';
                  } */
                /* $row[] = $last_edit_date;
                  $row[] = $size;
                  $total_size=$total_size+$size; */
                // Add certificate icons
                $row[] = $last_edit_date;
                $row[] = $size;
                $sortable_data[] = $row;
                $i++;
            }
        }
    }
    //end evaluate folder visible
    //*******************************************************************************************
} else {
    $sortable_data = '';
//					echo '<div>'.get_lang('NoDocsInFolder').'</div>';
}

$column_show = array();

// ACTIONS
echo '<div class="actions rel" style="min-height: 40px;">';
include_once("document_slideshow.inc.php");
DocumentManager::show_li_eeight($_GET['document'], $_GET['gidReq'], $_GET['curdirpath'], $curdirpath, $group_properties['directory'], $image_present, 'document', $file, $req_gid, null, null, null, null, $group_member_with_upload_rights, $is_certificate_mode, $_GET['selectcat']);
echo '</div>';
//	if (!$is_certificate_mode)
//		echo(build_directory_selector($folders,$curdirpath,(isset($group_properties['directory'])?$group_properties['directory']:array()),true));
//==============================================================================


if (($is_allowed_to_edit || $group_member_with_upload_rights) AND count($docs_and_folders) >= 1) {
    $column_show[] = 1;
}

$column_show[] = 1;
$column_show[] = 1;
$column_show[] = 1;
$column_show[] = 1;
// Show columns
if ($is_allowed_to_edit) {
    $column_show[] = 1;
    $column_show[] = 1;
    $column_show[] = 1;
    //$column_show[] = 1;
    //$column_show[] = 1;
    if ($curdirpath == '/certificates') {
        $column_show[] = 1; // Display certificate column in the sortetable
        $column_show[] = 1; // Display preview column in the sortetable
    }
}


$column_order = array();
if (count($row) == 12) {
    $column_order[] = 1;
    $column_order[] = 2;
    $column_order[] = 11;
    $column_order[] = 10;
    $column_order[] = 9;
} else if (count($row) == 7) {
    $column_order[] = 1;
    $column_order[] = 2;
    $column_order[] = 11;
    $column_order[] = 8;
}

$default_column = $is_allowed_to_edit ? 2 : 1;
$tablename = $is_allowed_to_edit ? 'teacher_table' : 'student_table';
$table = new SortableTableFromArrayConfig($sortable_data, $default_column, 20, $tablename, $column_show, $column_order, 'ASC');

if (isset($_SESSION['_gid'])) {
    $query_vars['gidReq'] = $_SESSION['_gid'];
}
$_GET['curdirpath'] = $curdirpath;
$query_vars['cidReq'] = api_get_course_id();
$query_vars['curdirpath'] = $curdirpath;
if (isset($_GET['document'])) {
    $query_vars['document'] = '0';
}


$table->set_additional_parameters($query_vars);
$column = 0;

if (($is_allowed_to_edit || $group_member_with_upload_rights) AND count($docs_and_folders) >= 1) {
    //$table->set_header($column++,'',false);
    $table->set_header($column++, get_lang('DEL'), false, "", "style='width:2%;align:center;'");
}
//$table->set_header($column++,get_lang('Type'));
//$table->set_header($column++,get_lang('Name'));
$table->set_header($column++, get_lang('Type'), true, "", "style='width:5%;align:center;'");
$table->set_header($column++, get_lang('Name'), true, 'align="left"');

//$column_header[] = array(get_lang('Comment'),true);  => display comment under the document name
//$table->set_header($column++,get_lang('Size'));
//$table->set_header($column++,get_lang('Date'));
$table->set_header($column++, get_lang('Size'), true, "align='left' style='width:5%;align:left;'");
$table->set_header($column++, get_lang('Date'), true, "", "align='center' style='width:12%;align:center;'");
//admins get an edit column
if ($is_allowed_to_edit) {
    $table->set_header($column++, get_lang('Edit'), true, "", "style='width:5%;align:center;'");
    $table->set_header($column++, get_lang('Move'), true, "", "style='width:5%;align:center;'");
    $table->set_header($column++, get_lang('Visible'), true, "", "style='width:5%;align:center;'");
    //$table->set_header($column++, get_lang('Template'), false, "", "style='width:8%;align:center;'");
    //$table->set_header($column++, get_lang('Learnpath'), false, "", "style='width:8%;align:center;'");
    // Header for certificate icons
    if ($curdirpath == '/certificates') {
        $table->set_header($column++, get_lang('Certificate'), false, "", "style='width:8%;align:center;'");
        $table->set_header($column++, get_lang('Preview'), false, "", "style='width:8%;align:center;'");
    }
}

//actions on multiple selected documents
//currently only delete action -> take only DELETE right into account
if (count($docs_and_folders) >= 1 && !empty($sortable_data)) {
    if ($is_allowed_to_edit || $group_member_with_upload_rights) {
        $form_actions = array();
        $form_action['delete'] = get_lang('Delete');
        $table->set_form_actions($form_action, 'path');
    }
}

// start the content div

if (isset($_SESSION["show_message"])) {
    Display::display_confirmation_message2($_SESSION["show_message"], false, true);
    unset($_SESSION["show_message"]);
}
if (isset($_SESSION["show_message_error"])) {
    Display::display_error_message2($_SESSION["show_message_error"], false, true);
    unset($_SESSION["show_message_error"]);
}
echo '<div id="content">';
echo '<img src="' . api_get_path(WEB_PATH) . 'main/img/progress_bar.gif" style="display:none;"/>';
// Display Message
//if ($_REQUEST['success'] == 'err') {
//    $_SESSION["show_message_error"] = get_lang('no se pudo');
//}
if (isset($_SESSION['dirpathsave'])) {
    $message1 = str_replace(',', ',<br>', $_SESSION['dirpathsave']);
    display::display_confirmation_message($message1, false, true);
    unset($_SESSION['dirpathsave']);
}
if (isset($_SESSION["show_message_normal"])) {
    display::display_normal_message($_SESSION["show_message_normal"], false, true);
    unset($_SESSION["show_message_normal"]);
}
// Currently we have many icons in the top bar,due to this we have the folder list in the content page
if ($curdirpath != '/' && $curdirpath != $group_properties['directory']) {
    if (!$is_certificate_mode && $is_allowed_to_edit) {
        echo build_directory_selector($folders, $curdirpath, (isset($group_properties['directory']) ? $group_properties['directory'] : array()), true);
        // directory navigation: one folder up, quickly select a certain directory
        DocumentManager::show_back_directory($curdirpath, $group_properties['directory']);
    } else {
        echo build_directory_selector($folders, $curdirpath, (isset($group_properties['directory']) ? $group_properties['directory'] : array()), true);
        // directory navigation: one folder up, quickly select a certain directory
        DocumentManager::show_back_directory($curdirpath, $group_properties['directory']);
    }
}
// display the sortable table
if (empty($sortable_data))
    $table->empty_message = get_lang('NoDocsInFolder');
$table->display();

// close the contend div
echo '</div>';

// bottom actions
echo '	<div class="actions document-footer">';
if ($is_allowed_to_edit) {
    echo '<a style="float:left;" href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpathurl . $req_gid . '&createdir=1">' . Display::return_icon('pixel.gif', get_lang('CreateDir'), array('class' => 'actionplaceholdericon actioncreatefolder')) . ' ' . get_lang('CreateDir') . '</a>';
}
if (empty($sortable_data))
    echo Display::return_icon('pixel.gif', get_lang('NoDocsInFolder'), array('class' => 'actionplaceholdericon actionsavezip')) . ' ' . get_lang('SaveZip');
else
    echo '<a style="float:left;" href="' . api_get_self() . '?' . api_get_cidreq() . '&action=downloadfolder">' . Display::return_icon('pixel.gif', get_lang('SaveZip'), array('class' => 'actionplaceholdericon actionsavezip')) . ' ' . get_lang('SaveZip') . '</a>';

$course_code = Database::escape_string($_GET['cidReq']);
$shf = api_get_course_setting('show_hidden_files', $course_code);
$showHiddenFolder = ($shf == 'false') ? 'ShowHiddenFolder' : 'HideHiddenFolder';
$actionVsisible = ($shf == 'false') ? 'actioninvisible' : 'actionvisible';
$shf = ($shf == 'true') ? 'false' : 'true';

if ($is_allowed_to_edit) {
    echo '<a style="float:left;" href="quota.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('DiskQuota'), array('class' => 'actionplaceholdericon actionquota')) . '  ' . get_lang("DiskQuota") . '</a>';
    echo '<a style="float:left;" href="' . api_get_self() . '?' . api_get_cidreq() . '&shf=' . $shf . '">' . Display::return_icon('pixel.gif', get_lang($showHiddenFolder), array('class' => 'actionplaceholdericon ' . $actionVsisible)) . ' ' . get_lang($showHiddenFolder) . '</a>';
}
DocumentManager::show_simplifying_links(true, true);


echo '<div class="clear"></div>';
echo '</div>';
echo '</div>';



// footer
Display::display_footer();
?>
