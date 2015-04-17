<?php //$Id: work.php 22201 2009-07-17 19:57:03Z cfasanando $
/* For licensing terms, see /dokeos_license.txt */
/**
*	@package dokeos.work
* 	@author Thomas, Hugues, Christophe - original version
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University - ability for course admins to specify wether uploaded documents are visible or invisible by default.
* 	@author Roan Embrechts, code refactoring and virtual course support
* 	@author Frederic Vauthier, directories management
*  	@version $Id: work.php 22201 2009-07-17 19:57:03Z cfasanando $
*
* 	@todo refactor more code into functions, use quickforms, coding standards, ...
*/
/**
==============================================================================
 * 	STUDENT PUBLICATIONS MODULE
 *
 * Note: for a more advanced module, see the dropbox tool.
 * This one is easier with less options.
 * This tool is better used for publishing things,
 * sending in assignments is better in the dropbox.
 *
 * GOALS
 * *****
 * Allow student to quickly send documents immediately
 * visible on the course website.
 *
 * The script does 5 things:
 *
 * 	1. Upload documents
 * 	2. Give them a name
 * 	3. Modify data about documents
 * 	4. Delete link to documents and simultaneously remove them
 * 	5. Show documents list to students and visitors
 *
 * On the long run, the idea is to allow sending realvideo . Which means only
 * establish a correspondence between RealServer Content Path and the user's
 * documents path.
 *
 * All documents are sent to the address /$_configuration['root_sys']/$currentCourseID/document/
 * where $currentCourseID is the web directory for the course and $_configuration['root_sys']
 * usually /var/www/html
 *
 *	Modified by Patrick Cool, february 2004:
 *	Allow course managers to specify wether newly uploaded documents should
 *	be visible or unvisible by default
 *	This is ideal for reviewing the uploaded documents before the document
 *	is available for everyone.
 *
 *	note: maybe the form to change the behaviour should go into the course
 *	properties page?
 *	note 2: maybe a new field should be created in the course table for
 *	this behaviour.
 *
 *	We now use the show_score field since this is not used.
 *
==============================================================================
 */

// configuration settings (that you may change)
$link_target_parameter = ""; //or e.g. "target=\"_blank\"";
$always_show_tool_options = false;
$always_show_upload_form = false;
global $_course;

if ($always_show_tool_options) {
	$display_tool_options = true;
}
if ($always_show_upload_form) {
	$display_upload_form = true;
}

// name of the language file that needs to be included
$language_file = array ('exercice','work','document','admin','group');
define('DOKEOS_WORK', true);

// include the global Dokeos file
require_once '../inc/global.inc.php';

// redirect to mvc pattern (temporally)
header('Location: '.api_get_path(WEB_VIEW_PATH).'work/index.php?'.http_build_query($_GET));
exit;

// including additional libraries
require_once 'work.lib.php';
require_once (api_get_path(LIBRARY_PATH) . 'course.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'debug.lib.inc.php');
require_once (api_get_path(LIBRARY_PATH) . 'security.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH) . 'document.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'mail.lib.inc.php');
require_once (api_get_path(LIBRARY_PATH) . 'text.lib.php');
include_once (api_get_path(LIBRARY_PATH) . 'fileManage.lib.php');
include_once (api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php');
include_once (api_get_path(LIBRARY_PATH) . 'fileDisplay.lib.php');
require_once '../newscorm/learnpath.class.php';
require_once '../newscorm/learnpathItem.class.php';

if(isset($_GET['action']) && $_GET['action']=="downloadfolder")
{
	include('downloadfolder.inc.php');
}

// Session stuff
if (isset ($_GET['id_session'])) {
	$_SESSION['id_session'] = intval($_GET['id_session']);
}
isset($_SESSION['id_session'])?$id_session=$_SESSION['id_session']:$id_session=null;

// Section (for the tabs)
$this_section = SECTION_COURSES;
$ctok = $_SESSION['sec_token'];
$stok = Security::get_token();
$is_course_member = CourseManager::is_user_subscribed_in_real_or_linked_course(api_get_user_id(), $_course['sysCode'],api_get_session_id());

// additional javascript
//$htmlHeadXtra[] = to_javascript_work();
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] =  '
	<style type="text/css">
	div.row div.label{
		width: 10%;
	}
	div.row div.formw{
		width: 85%;
	}

	</style>
	';
$add_lp_param = "";
if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
 $lp_id = intval($_GET['lp_id']);
 $htmlHeadXtra[] = '<script type="text/javasc">
    $(document).ready(function (){
      $("a[href]").attr("href", function(index, href) {
          var param = "lp_id=' . $lp_id . '";
           var is_javascript_link = false;
           var info = href.split("javascript");

           if (info.length >= 2) {
             is_javascript_link = true;
           }
           if ($(this).attr("class") == "course_main_home_button" || $(this).attr("class") == "course_menu_button"  || $(this).attr("class") == "next_button"  || $(this).attr("class") == "prev_button" || is_javascript_link) {
             return href;
           } else {
             if (href.charAt(href.length - 1) === "?")
                 return href + param;
             else if (href.indexOf("?") > 0)
                 return href + "&" + param;
             else
                 return href + "?" + param;
           }
      });
    });
  </script>';
 $add_lp_param = "&amp;lp_id=" . $lp_id;
}

//directories management
$base_work_dir = api_get_path(SYS_COURSE_PATH) . $_course['path'] . '/work';
$http_www = api_get_path('WEB_COURSE_PATH') . $_course['path'] . '/work';
$cur_dir_path = '';
if (isset ($_GET['curdirpath']) && $_GET['curdirpath'] != '') {
	//$cur_dir_path = preg_replace('#[\.]+/#','',$_GET['curdirpath']); //escape '..' hack attempts
	//now using common security approach with security lib
	$in_course = Security :: check_abs_path($base_work_dir . '/' . $_GET['curdirpath'], $base_work_dir);
	if (!$in_course) {
		$cur_dir_path = "/";
	} else {
		$cur_dir_path = $_GET['curdirpath'];
	}
} elseif (isset ($_POST['curdirpath']) && $_POST['curdirpath'] != '') {
	//$cur_dir_path = preg_replace('#[\.]+/#','/',$_POST['curdirpath']); //escape '..' hack attempts
	//now using common security approach with security lib
	$in_course = Security :: check_abs_path($base_work_dir . '/' . $_POST['curdirpath'], $base_work_dir);
	if (!$in_course) {
		$cur_dir_path = "/";
	} else {
		$cur_dir_path = $_POST['curdirpath'];
	}
} else {
	$cur_dir_path = '/';
}
if ($cur_dir_path == '.') {
	$cur_dir_path = '/';
}
$cur_dir_path_url = urlencode($cur_dir_path);

//prepare a form of path that can easily be added at the end of any url ending with "work/"
$my_cur_dir_path = $cur_dir_path;
if ($my_cur_dir_path == '/') {
	$my_cur_dir_path = '';
} elseif (substr($my_cur_dir_path, -1, 1) != '/') {
	$my_cur_dir_path = $my_cur_dir_path . '/';
}

// access control
api_protect_course_script(true);

// Lp object
if (isset($_SESSION['lpobject'])) {
 if ($debug > 0)
  error_log('New LP - SESSION[lpobject] is defined', 0);
 $oLP = unserialize($_SESSION['lpobject']);
 if (is_object($oLP)) {
  if ($debug > 0)
   error_log('New LP - oLP is object', 0);
  if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
   if ($debug > 0)
    error_log('New LP - Course has changed, discard lp object', 0);
   if ($myrefresh == 1) {
    $myrefresh_id = $oLP->get_id();
   }
   $oLP = null;
   api_session_unregister('oLP');
   api_session_unregister('lpobject');
  } else {
   $_SESSION['oLP'] = $oLP;
   $lp_found = true;
  }
 }
}

isset($_REQUEST['origin'])?$origin = Security :: remove_XSS($_REQUEST['origin']):$origin='';
isset($_REQUEST['assignment_id'])?$assignment_id = Security :: remove_XSS($_REQUEST['assignment_id']):$assignment_id='';
isset($_REQUEST['id'])?$paper_id = Security :: remove_XSS($_REQUEST['id']):$paper_id='';



// display the Dokeos header
if (isset($origin) && $origin == 'learnpath') {
	//we are in the learnpath tool
	include api_get_path(INCLUDE_PATH) . 'reduced_header.inc.php';
} else {
	// we are not in the learnpath tool
		Display :: display_tool_header(null);
}
//tracking
event_access_tool(TOOL_STUDENTPUBLICATION);

// access control
$is_allowed_to_edit = api_is_allowed_to_edit(); //has to come after display_tool_view_option();

display_action_links($cur_dir_path, $always_show_tool_options,$always_show_upload_form);

echo '<div id="content">';
switch ($_REQUEST['action']) {
	case 'new_assignment' :
		create_new_assignment ($cur_dir_path, $stok, $ctok, $add_lp_param);
		break;
	case 'edit_assignment' :
		edit_assignment ($assignment_id, $cur_dir_path, $stok, $ctok, $add_lp_param);
		break;
	case 'delete_assignment' :
		delete_assignment ($_POST['id']);
		break;
	case 'submit_work' :
		submit_work ($cur_dir_path,$origin,$stok,$ctok,$is_course_member);
		break;
	case 'view_papers' :
		assignment_paper_list ($cur_dir_path);
		break;
	case 'correct_paper' :
		correct_paper ($paper_id);
		break;
	case 'view_paper' :
		view_paper ($paper_id);
		break;
	case 'move_paper' :
		move_paper ($assignment_id,$paper_id);
		break;
	case 'delete_paper' :
		delete_paper ($paper_id);
		break;
    case 'move_form':
        move_form($paper_id);
        assignment_list($cur_dir_path);
        break;
    case 'move_to':
        move_to($_POST['move_file'], $_POST['move_to']);
        assignment_list($cur_dir_path);
            break;
	default :
		assignment_list($cur_dir_path);
		break;
}


echo '</div>';

echo '<div class="actions"></div>';

if ($origin != 'learnpath') {
	//we are not in the learning path tool
	Display :: display_footer();
}