<?php

/* For licensing terms, see /license.txt */

// name of the language file that needs to be included
$language_file = "dropbox";

// setting the help
$help_content = 'dropbox';

// including the global dokeos file
require_once '../../../inc/global.inc.php';

// including additional libraries
require_once api_get_path(SYS_MODEL_PATH) . 'dropbox/DropboxModel.php';
require_once api_get_path(SYS_CONTROLLER_PATH) . 'dropbox/DropboxController.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'security.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'tablesort.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'timezone.lib.php';
require_once api_get_path(LIBRARY_PATH) . '/course.lib.php';
require_once api_get_path(LIBRARY_PATH) . '/groupmanager.lib.php';
require_once api_get_path(LIBRARY_PATH) . '/debug.lib.inc.php';
require_once api_get_path(LIBRARY_PATH) . '/fileDisplay.lib.php';
require_once api_get_path(LIBRARY_PATH) . '/document.lib.php';
require_once '../../../document/document.inc.php';
require_once '../../../dropbox/dropbox_config.inc.php';
require_once '../../../dropbox/dropbox_functions.inc.php';
require_once '../../../dropbox/dropbox_class.inc.php';

// setting the section (for the tabs)
$this_section = SECTION_COURSES;

define('DOKEOS_DROPBOX', true);

// do the tracking
event_access_tool(TOOL_DROPBOX);



// Access restrictions
api_protect_course_script(true);

$user_id = api_get_user_id();
$course_code = $_course['sysCode'];
$course_info = Database::get_course_info($course_code);

$session_id = api_get_session_id();
$is_course_member = CourseManager::is_user_subscribed_in_real_or_linked_course($user_id, $course_code, $session_id);

// we load the model object
$dropboxModel = new DropboxModel();

if ($_GET['action'] == 'add') {
    $dropbox_person = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);
}
$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="' . api_get_path(WEB_PATH) . 'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
$htmlHeadXtra[] = '<script  type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';
$htmlHeadXtra[] = '<script  type="text/javascript">
    $(document).on("ready",Jload);
        function Jload(){
            $("#deleteDropbox").click(function(){
          var boxes = $(".data_table tr").find("input[type=checkbox]");
          var all = 0;
          boxes.each(function(){
             if( $(this).is(":checked") ){
                all = 1;
             }
          });
          if(all == 0) return;
              var lang =\'' . get_lang("ConfirmYourChoice") . '\';
              var title = \'' . get_lang("Alert") . '\';
                  jConfirm(lang, title, function(r) {
                      //$("name=\"form_surveys\"").submit();
                      $(this).submit();
                  });
              });
        }
        
         </script>';
$javascript = "<script type=\"text/javascript\">
	function confirmsend ()
	{
		if (confirm(\"" . $dropboxModel->dropbox_lang("mailingConfirmSend", "noDLTT") . "\")){
			return true;
		} else {
			return false;
		}
		return true;
	}

	function confirmation (name,link,title)
	{
//		if (confirm(\"" . $dropboxModel->dropbox_lang("confirmDelete", "noDLTT") . " : \"+ name )){
//			return true;
//		} else {
//			return false;
//		}
//		return true;
//                jConfirm(\"" . $dropboxModel->dropbox_lang("confirmDelete", "noDLTT") . " : \"+ name , title, function(r) {
//                            window.location.href=link;
//                });
                Alert_Confim_Delete(link);
	}
//
	function checkForm (frm)
	{
		if (frm.elements['recipients[]'].selectedIndex < 0){
			//alert(\"" . $dropboxModel->dropbox_lang("noUserSelected", "noDLTT") . "\");
                        jAlert(\"" . $dropboxModel->dropbox_lang("noUserSelected", "noDLTT") . "\");
			return false;
		} else if (frm.file.value == '') {
			//alert(\"" . $dropboxModel->dropbox_lang("noFileSpecified", "noDLTT") . "\");
                        jAlert(\"" . $dropboxModel->dropbox_lang("noFileSpecified", "noDLTT") . "\");
			return false;
		} else {
			return true;
		}
	}
	";
if ($dropboxModel->dropbox_cnf("allowOverwrite")) {
    $javascript .= "
		var sentArray = new Array("; //sentArray keeps list of all files still available in the sent files list
    //of the user.
    //This is used to show or hide the overwrite file-radio button of the upload form
    for ($i = 0; $i < count($dropbox_person->sentWork); $i++) {
        if ($i > 0) {
            $javascript .= ", ";
        }
        $javascript .= "'" . $dropbox_person->sentWork[$i]->title . "'";
        //echo '***'.$dropbox_person->sentWork[$i]->title;
    }
    $javascript .=");

		function checkfile(str)
		{

			ind = str.lastIndexOf('/'); //unix separator
			if (ind == -1) ind = str.lastIndexOf('\\\');	//windows separator
			filename = str.substring(ind+1, str.length);

			found = 0;
			for (i=0; i<sentArray.length; i++) {
				if (sentArray[i] == filename) found=1;
			}

			//always start with unchecked box
			el = $('#cb_overwrite');
			el.checked = false;

			//show/hide checkbox
			if (found == 1) {
				displayEl('overwrite');
			} else {
				undisplayEl('overwrite');
			}
		}

		function getElement(id)
		{
			return document.getElementById ? document.getElementById(id) :
			document.all ? document.all(id) : null;
		}

		function displayEl(id)
		{
			var el = getElement(id);
			if (el && el.style) el.style.display = '';
		}

		function undisplayEl(id)
		{
			var el = getElement(id);
			if (el && el.style) el.style.display = 'none';
		}";
}

$javascript .="
	</script>";

$htmlHeadXtra[] = $javascript;

$htmlHeadXtra[] = '<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="expires" content="-1">';


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

/*
 * ========================================
 *         AUTHORISATION SECTION
 * ========================================
 * Prevents access of all users that are not course members
 */

if ((!$is_allowed_in_course || !$is_course_member) && !api_is_allowed_to_edit(null, true)) {

    if ($origin != 'learnpath') {
        api_not_allowed(true); //print headers/footers
    } else {
        api_not_allowed();
    }
    exit();
}



// setting breadcrumbs
if ($_GET['view'] == 'received') {
    $interbreadcrumb[] = array("url" => "../dropbox/index.php", "name" => $dropboxModel->dropbox_lang("dropbox", "noDLTT"));
    $nameTools = get_lang('ReceivedFiles');

    if ($_GET['action'] == 'addreceivedcategory') {
        $interbreadcrumb[] = array("url" => "../dropbox/index.php?view=received", "name" => get_lang("ReceivedFiles"));
        $nameTools = get_lang('AddNewCategory');
    }
}
if ($_GET['view'] == 'sent' OR empty($_GET['view'])) {
    $interbreadcrumb[] = array("url" => "../dropbox/index.php", "name" => $dropboxModel->dropbox_lang("dropbox", "noDLTT"));
    $nameTools = get_lang('SentFiles');

    if ($_GET['action'] == 'addsentcategory') {
        $interbreadcrumb[] = array("url" => "../dropbox/index.php?view=sent", "name" => get_lang("SentFiles"));
        $nameTools = get_lang('AddNewCategory');
    }
    if ($_GET['action'] == 'add') {
        $interbreadcrumb[] = array("url" => "../dropbox/index.php?view=sent", "name" => get_lang("SentFiles"));
        $nameTools = get_lang('UploadNewFile');
    }
}

if ($origin != 'learnpath') {
    Display::display_tool_header($nameTools, "Dropbox");
} else { // if we come from the learning path we have to include the stylesheet and the required javascripts manually.
    echo '<link rel="stylesheet" type="text/css" href="', api_get_path(WEB_CODE_PATH), 'css/default.css">';
    echo $javascript;
}

if ($_POST['action'] == 'delete_received' || $_POST['action'] == 'download_received') {
    $part = 'received';
}
if ($_POST['action'] == 'delete_sent' || $_POST['action'] == 'download_sent') {
    $part = 'sent';
}

// STEP 2: at least one file has to be selected. If not we return an error message
foreach ($_POST as $key => $value) {
    if (strstr($value, $part . '_') AND $key != 'view_received_category' AND $key != 'view_sent_category') {
        $checked_files = true;
        $checked_file_ids[] = intval(substr($value, strrpos($value, '_')));
    }
}
$checked_file_ids = $_POST['id'];

// get actions
$actions = array('listing', 'addsentcategory', 'addreceivedcategory', 'editcategory', 'deletesentcategory', 'add', 'deletesentfile', 'deletereceivedfile', 'movesent', 'movereceived', 'delete_sent', 'delete_received', 'download_received', 'download_sent');
$action = 'listing';
if (isset($_GET['action']) && in_array($_GET['action'], $actions)) {
    $action = $_GET['action'];
}

isset($_REQUEST['view_sent_category']) ? $catId = Security :: remove_XSS($_REQUEST['cat_id']) : $catId = 0;
isset($_REQUEST['view']) ? $view = Security :: remove_XSS($_REQUEST['view']) : $view = 'sent';
isset($_REQUEST['action']) ? $action = Security :: remove_XSS($_REQUEST['action']) : $action = 'sent';
isset($_REQUEST['id']) ? $id = Security :: remove_XSS($_REQUEST['id']) : $id = '';

if ($_POST['feedback']) {

    $action = 'storeFeedback';
}
if ($_POST['do_move']) {

    $action = 'storeMove';
}


// Dropbox controller object
$dropboxController = new DropboxController($catId);

// distpacher actions to controller
switch ($action) {
    case 'listing':
        $dropboxController->listing();
        break;
    case 'addsentcategory':
    case 'addreceivedcategory':
    case 'editcategory':
        $dropboxController->addCategory($action, $id);
        break;
    case 'deletesentcategory':
        $dropboxController->deleteCategory($action, $id);
        break;
    case 'add':
        $dropboxController->add();
        break;
    case 'deletesentfile':
        $dropboxController->deleteSentFile($id);
        break;
    case 'deletereceivedfile':
    case 'deletereceivedcategory':
        $dropboxController->deleteReceivedFile($id);
        break;
    case 'storeFeedback':
        $dropboxController->storeFeedback();
        break;
    case 'movereceived':
    case 'movesent':
        $dropboxController->moveForm();
        break;
    case 'storeMove':
        $dropboxController->storeMove();
        break;
    case 'delete_received':
        $dropboxController->deleteReceived($checked_file_ids);
        break;
    case 'delete_sent':
        $dropboxController->deleteSent($checked_file_ids);
        break;
    case 'download_sent':
    case 'download_received':
        $dropboxController->downloadFile($checked_file_ids);
        break;
    default:
        $dropboxController->listing();
}


Display::display_footer();
?>