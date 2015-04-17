<?php

//$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
  ==============================================================================
 * @desc The dropbox is a personal (peer to peer) file exchange module that allows
 * you to send documents to a certain (group of) users.
 *
 * @version 1.3
 *
 * @author Jan Bols <jan@ivpv.UGent.be>, main programmer, initial version
 * @author René Haentjens <rene.haentjens@UGent.be>, several contributions  (see RH)
 * @author Roan Embrechts, virtual course support
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University (see history version 1.3)
 *
 * @package dokeos.dropbox
 *
 * @todo complete refactoring. Currently there are about at least 3 sql queries needed for every individual dropbox document.
 * 			first we find all the documents that were sent (resp. received) by the user
 * 			then for every individual document the user(s)information who received (resp. sent) the document is searched
 * 			then for every individual document the feedback is retrieved
 * @todo 	the implementation of the dropbox categories could (on the database level) have been done more elegantly by storing the category
 * 			in the dropbox_person table because this table stores the relationship between the files (sent OR received) and the users
  ==============================================================================
 */
/**
  ==============================================================================
  HISTORY
  ==============================================================================
  Version 1.1
  ------------
  - dropbox_init1.inc.php: changed include statements to require statements. This way if a file is not found, it stops the execution of a script instead of continuing with warnings.
  - dropbox_init1.inc.php: the include files "claro_init_global.inc.php" & "debug.lib.inc.php" are first checked for their existence before including them. If they don't exist, in the .../include dir, they get loaded from the .../inc dir. This change is necessary because the UCL changed the include dir to inc.
  - dropbox_init1.inc.php: the databasetable name in the variable $dropbox_cnf["introTbl"] is chnged from "introduction" to "tool_intro"
  - install.php: after submit, checks if the database uses accueil or tool_list as a tablename
  - index.php: removed the behaviour of only the teachers that are allowed to delete entries
  - index.php: added field "lastUploadDate" in table dropbox_file to store information about last update when resubmiting a file
  - dropbox.inc.php: added $lang["lastUpdated"]
  - index.php: entries in received list show when file was last updated if it is updated
  - index.php: entries in sent list show when file was last resent if it was resent
  - dropbox_submit.php: add a unique id to every uploaded file
  - index.php: add POST-variable to the upload form with overwrite data when user decides to overwrite the previous sent file with new file
  - dropbox_submit.php: add sanity checks on POST['overwrite'] data
  - index.php: remove title field in upload form
  - dropbox_submit.php: remove use of POST['title'] variable
  - dropbox_init1.inc.php: added $dropbox_cnf["version"] variable
  - dropbox_class.inc.php: add $this->lastUploadDate to Dropbox_work class
  - dropbox.inc.php: added $lang['emptyTable']
  - index.php: if the received or sent list is empty, a message is displayed
  - dropbox_download.php: the $file var is set equal to the title-field of the filetable. So not constructed anymore by substracting the username from the filename
  - index.php: add check to see if column lastUploadDate exists in filetable
  - index.php: moved javascripts from dropbox_init2.inc.php to index.php
  - index.php: when specifying an uploadfile in the form, a checkbox allowing the user to overwrite a previously sent file is shown when the specified file has the same name as a previously uploaded file of that user.
  - index.php: assign all the metadata (author, description, date, recipient, sender) of an entry in a list to the class="dropbox_detail" and add css to html-header
  - index.php: assign all dates of entries in list to the class="dropbox_date" and add CSS
  - index.php: assign all persons in entries of list to the class="dropbox_person" and add CSS
  - dropbox.inc.php: added $lang['dropbox_version'] to indicate the lates version. This must be equal to the $dropbox_cnf['version'] variable.
  - dropbox_init1.inc.php: if the newest lang file isn't loaded by claro_init_global.inc.php from the .../lang dir it will be loaded locally from the .../plugin/dropbox/ dir. This way an administrator must not install the dropbox.inc.php in the .../lang/english dir, but he can leave it in the local .../plugin/dropbox/ dir. However if you want to present multiple language translations of the file you must still put the file in the /lang/ dir, because there is no language management system inside the .../plugin/dropbox dir.
  - mime.inc.php: created this file. It contains an array $mimetype with all the mimetypes that are used by dropbox_download.php to give hinst to the browser during download about content
  - dropbox_download.php: remove https specific headers because they're not necessary
  - dropbox_download.php: use application/octet-stream as the default mime and inline as the default Content-Disposition
  - dropbox.inc.php: add lang vars for "order by" action
  - dropbox_class.inc.php: add methods orderSentWork, orderReceivedWork en _cmpWork and propery _orderBy to class Dropbox_person to take care of sorting
  - index.php: add selectionlist to headers of sent/received lists to select "order by" and add code to keep selected value in sessionvar.
  - index.php: moved part of a <a> hyperlink to previous line to remove the underlined space between symbol and title of a work entry in the sent/received list
  - index.php: add filesize info in sent/received lists
  - dropbox_submit.php: resubmit prevention only for GET action, because it gives some annoying behaviour in POST situation: white screen in IE6

  Version 1.2
  -----------
  - adapted entire dropbox tool so it can be used as a default tool in Dokeos 1.5
  - index.php: add event registration to log use of tool in stats tables
  - index.php: upload form checks for correct user selection and file specification before uploading the script
  - dropbox_init1.inc.php: added dropbox_cnf["allowOverwrite"] to allow or disallow overwriting of files
  - index.php: author name textbox is automatically filled in
  - mailing functionality (see RH comments in code)
  - allowStudentToStudent and allowJustUpload options (id.)
  - help in separate window (id.)

  Version 1.3 (Patrick Cool)
  --------------------------
  - sortable table
  - categories
  - fixing a security hole
  - tabs (which can be disabled: see $dropbox_cnf['sent_received_tabs'])
  - same action on multiple documents ([zip]download, move, delete)
  - consistency with the docuements tool (open/download file, icons of documents, ...)
  - zip download of complete folder

  Version 1.4 (Yannick Warnier)
  -----------------------------
  - removed all self-built database tables names
  ==============================================================================
 */
/*
  ==============================================================================
  INIT SECTION
  ==============================================================================
 */
require("../inc/global.inc.php");
if ($_SESSION['_user']['user_id'] == 2 && $_GET['action'] == 'add') {
    echo "<script type='text/javascript'>
        location.href = 'index.php?" . api_get_cidReq() . "' ;
</script>";
}
// the file that contains all the initialisation stuff (and includes all the configuration stuff)
require_once( "dropbox_init.inc.php");

//
// get the last time the user accessed the tool
if ($_SESSION[$_course['id']]['last_access'][TOOL_DROPBOX] == '') {
    $last_access = get_last_tool_access(TOOL_DROPBOX, $_course['code'], $_user['user_id']);
    $_SESSION[$_course['id']]['last_access'][TOOL_DROPBOX] = $last_access;
} else {
    $last_access = $_SESSION[$_course['id']]['last_access'][TOOL_DROPBOX];
}

require_once api_get_path(LIBRARY_PATH) . 'mindmap.lib.php';
// do the tracking
event_access_tool(TOOL_DROPBOX);

//this var is used to give a unique value to every page request. This is to prevent resubmiting data
$dropbox_unid = md5(uniqid(rand(), true));

// Tool introduction
Display::display_introduction_section(TOOL_DOCUMENT);


/*
  -----------------------------------------------------------
  ACTIONS: add a dropbox file, add a dropbox category.
  -----------------------------------------------------------
 */
if (isset($_REQUEST['dispaction'])) {
    $dispaction = $_REQUEST['dispaction'];
    $updateRecordsArray = $_REQUEST['order'];

    $tbl_documents = Database::get_course_table(TABLE_DOCUMENT);

    if ($dispaction == "updateRecordsListings") {

        $listingCounter = 1;
        $disp = explode(",", $updateRecordsArray);
        $cntdispid = sizeof($disp);
        for ($i = 0; $i < $cntdispid; $i++) {

            $dispid = substr($disp[$i], 8, strlen($disp[$i]));
            $query = "UPDATE $tbl_documents SET display_order = " . $listingCounter . " WHERE id = " . $dispid;
            $result = api_sql_query($query, __FILE__, __LINE__);
            $listingCounter = $listingCounter + 1;
        }
        echo '<script type="text/javascript">window.location.href="index.php?' . api_get_cidReq() . '&view=' . Security::remove_XSS($_REQUEST['view']) . '"</script>';
    }
}

$minheight = (api_is_allowed_to_edit()) ? '40px' : '40px';
echo '<div class="actions" style="min-height: ' . $minheight . ';">';
echo '<ul class="new_li_actions" >';
echo '<li><a href="' . api_get_path(WEB_CODE_PATH) . 'document/document.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . ' ' . get_lang('Documents') . '</a></li>';
if (!api_is_allowed_to_edit(null, true)) {
    $opacity_value = 'style="opacity: 0.2';

    //DocumentManager::show_simplifying_links(!api_is_allowed_to_edit(), false);
}
$MindMapManager = new MindMapManager();
$view = (isset($_GET['view'])) ? $_GET['view'] : 'received';
$MindMapManager->show_icons_mindmap($_GET['action'], $view);
echo '</ul>';
echo '<div class="clear"></div>';
echo '</div>';

// start the content div
echo '<div id="content">';
if (isset($_POST['submitWork'])) {
    $check = Security::check_token();
    if ($check) {
        $return_information = store_add_dropbox();
        if ($return_information['type'] == 'F') {
            Display :: display_error_message($return_information, null, true);
        } else {
            Display :: display_confirmation_message($return_information, null, true);
            Security::clear_token();
        }
    }
}

if (isset($_GET['curdirpath']) && !empty($_GET['curdirpath'])) {
    $curdirpath = '/mindmaps/' . Security::remove_XSS($_GET['curdirpath']) . '/';
} else {
    $curdirpath = '/mindmaps/';
}
echo "<style type='text/css'>.new_li_actions li {margin-top:2px; margin-bottom:2px;}</style>";
$course_name = explode("=", api_get_cidReq());
$SYS_COURSE_PATH = api_get_path(SYS_COURSE_PATH) . $_course['path'];
$WEB_COURSE_PATH = api_get_path(WEB_COURSE_PATH) . $_course['path'];
//$src_path = api_get_path(WEB_COURSE_PATH).$course_name[1].'/document/mindmaps/thumbs/';
$src_path = $SYS_COURSE_PATH . '/document' . $curdirpath;

$tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
$propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);

// *** display the form for adding a category ***
if ($_GET['action'] == "addreceivedcategory" or $_GET['action'] == "addsentcategory") {
    if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
        api_not_allowed();
    }
    display_addcategory_form($_POST['category_name'], '', $_GET['action']);
}

// *** editing a category: displaying the form ***
if ($_GET['action'] == 'editcategory' and isset($_GET['id'])) {
    if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
        api_not_allowed();
    }
    if (!$_POST) {
        if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
            api_not_allowed();
        }
        display_addcategory_form('', $_GET['id'], 'editcategory');
    }
}

// *** storing a new or edited category ***
if (isset($_POST['StoreCategory'])) {
    if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
        api_not_allowed();
    }
    $return_information = store_addcategory();
    if ($return_information['type'] == 'confirmation') {
        //	Display :: display_confirmation_message($return_information['message']);
    }
    if ($return_information['type'] == 'error') {
        Display :: display_error_message(get_lang('FormHasErrorsPleaseComplete') . '<br />' . $return_information['message']);
        display_addcategory_form($_POST['category_name'], $_POST['edit_id'], $_POST['action']);
    }
}

// *** Move a File ***
if (($_GET['action'] == 'movesent' OR $_GET['action'] == 'movereceived') AND isset($_GET['move_id'])) {
    if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
        api_not_allowed();
    }
    display_move_form(str_replace('move', '', $_GET['action']), $_GET['move_id'], get_dropbox_categories(str_replace('move', '', $_GET['action'])));
}
if ($_POST['do_move']) {
    Display :: display_confirmation_message(store_move($_POST['id'], $_POST['move_target'], $_POST['part']));
}

// *** Delete a file ***
if (($_GET['action'] == 'deletereceivedfile' OR $_GET['action'] == 'deletesentfile') AND isset($_GET['id']) AND is_numeric($_GET['id'])) {
    if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
        api_not_allowed();
    }
    $dropboxfile = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);
    if ($_GET['action'] == 'deletereceivedfile') {
        $dropboxfile->deleteReceivedWork($_GET['id']);
        $message = get_lang('ReceivedFileDeleted');
    }
    if ($_GET['action'] == 'deletesentfile') {
        $dropboxfile->deleteSentWork($_GET['id']);
        $message = get_lang('SentFileDeleted');
    }
//	Display :: display_confirmation_message($message);
}

// *** Delete a category ***
if (($_GET['action'] == 'deletereceivedcategory' OR $_GET['action'] == 'deletesentcategory') AND isset($_GET['id']) AND is_numeric($_GET['id'])) {
    if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
        api_not_allowed();
    }
    $message = delete_category($_GET['action'], $_GET['id']);
//	Display :: display_confirmation_message($message);
}

// *** Do an action on multiple files ***
// only the download has is handled separately in dropbox_init_inc.php because this has to be done before the headers are sent
// (which also happens in dropbox_init.inc.php

if (!isset($_POST['feedback']) && (strstr($_POST['action'], 'move_received') OR
        $_POST['action'] == 'delete_received' OR $_POST['action'] == 'download_received' OR
        $_POST['action'] == 'delete_sent' OR $_POST['action'] == 'download_sent')) {
    $display_message = handle_multiple_actions();
    Display :: display_normal_message($display_message);
}

// *** Store Feedback ***
if ($_POST['feedback']) {
    if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
        api_not_allowed();
    }
    $display_message = store_feedback();
    Display :: display_normal_message($display_message);
}



// *** Error Message ***
if (isset($_GET['error']) AND !empty($_GET['error'])) {
    Display :: display_normal_message(get_lang($_GET['error']));
}



if ($_GET['action'] != "add") {
// getting all the categories in the dropbox for the given user
    $dropbox_categories = get_dropbox_categories();
// creating the arrays with the categories for the received files and for the sent files
    foreach ($dropbox_categories as $category) {
        if ($category['received'] == '1') {
            $dropbox_received_category[] = $category;
        }
        if ($category['sent'] == '1') {
            $dropbox_sent_category[] = $category;
        }
    }
}// else end by breetha for feedback
$MindMapManager = new MindMapManager();
$acction = isset($_GET['action']) ? $_GET['action'] : null;
switch ($acction) {
    case 'add':
        if (api_get_session_id() != 0 && api_is_allowed_to_session_edit(false, true) == false) {
            api_not_allowed();
        }
        display_add_form();
        break;
    case 'viewfeedback':
        $MindMapManager->viewfeedback($_GET['id'], $_GET['view'], $SYS_COURSE_PATH, $dropbox_cnf['tbl_feedback']);
        break;
}

switch ($view) {
    case 'received':
        $MindMapManager->show_list_received($curdirpath, $src_path);
        break;
    case 'sent':
        $MindMapManager->show_list_sent($curdirpath, $src_path);
        break;
    default:
        $MindMapManager->show_list_received($curdirpath, $src_path);
        break;
}

echo '<script type="text/javascript">
function changedir()
{
	var foldername = document.createdir.mindmapfolder.value;
	window.location.href = "' . api_get_self() . '?' . api_get_cidReq() . '&view=sent&curdirpath="+foldername;
}
</script>';

// close the content div
echo '</div>';

echo '<div class="actions">';
DocumentManager::show_simplifying_links(true, true);
echo '</div>';

// Display the footer
Display::display_footer();

/**
 * This functions display the inbox and outbox links
 */
function build_folder($curdirpath) {
    $tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
    $propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);
    $folders = array();
    $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'folder' AND doc.path LIKE '/mindmaps%' AND prop.visibility = 1";
    $result = api_sql_query($sql, __FILE__, __LINE__);
    $numrows = Database :: num_rows($result);
    if ($numrows <> 0) {
        while ($row = Database :: fetch_array($result)) {
            $folders[] = $row['title'];
        }
    }
    $return = '<form style="display:inline;" name="createdir" method="post"><select name="mindmapfolder" onchange="javascript:changedir()">';
    $return .= '<option value=""';
    if (empty($curdirpath)) {
        $return .= 'selected';
    } elseif ($curdirpath == 'Home') {
        $return .= 'selected';
    }
    $return .= '>Home</option>';
    foreach ($folders as $folder) {
        $return .= '<option value="' . $folder . '"';
        if ($curdirpath == $folder) {
            $return .= 'selected';
        }
        $return .= '>' . $folder . '</option>';
    }
    $return .= '</select></form></td>';

    return $return;
}
