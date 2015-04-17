<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	Learning Path
* 	This is a learning path creation and player tool in Dokeos - previously learnpath_handler.php
*	@package dokeos.learnpath
*	@author	Yannick Warnier
*	@author Julio Montoya  - Improving the list of templates
*	@author Roan Embrechts, refactoring and code cleaning
*	@author Patrick Cool
*	@author Denes Nagy
*/


$this_section=SECTION_COURSES;

api_protect_course_script();

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

include('learnpath_functions.inc.php');
//include('../resourcelinker/resourcelinker.inc.php');
include('resourcelinker.inc.php');
//rewrite the language file, sadly overwritten by resourcelinker.inc.php
// name of the language file that needs to be included
$language_file = "learnpath";

if($_REQUEST['type'] == 'step'){
    if (empty($charset)) {
        // we set the encoding of the lp    
        if (!empty($_SESSION['oLP']->encoding)) {
            $charset = $_SESSION['oLP']->encoding;
            // Check if we have a valid api encoding
            $valid_encodings = api_get_valid_encodings();
            $has_valid_encoding = false;
            foreach ($valid_encodings as $valid_encoding) {
                if (strcasecmp($charset,$valid_encoding) == 0) {
                    $has_valid_encoding = true;
                }
            }
            // If the scorm packages has not a valid charset, i.e : UTF-16 we are displaying
            if ($has_valid_encoding === false) {
                $charset = api_get_system_encoding();
            }
        } else {
            $charset = api_get_system_encoding();
        }
    }
}
/*
-----------------------------------------------------------
	Header and action code
-----------------------------------------------------------
*/

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css">
        input.error { border: 1px solid red; }
    </style>

    <script type="text/javascript">
    /*<![CDATA[*/
    $(document).ready(function(){
        if ($("#form").length > 0) {
            $("#form").validate({
                rules: {
                    title: {
                      required: true
                    }
                },
                messages: {
                    title: {
                        required: "<img src=\"'.  api_get_path(WEB_IMG_PATH).'exclamation.png\" title=\''.get_lang('Required').'\' />"
                    }
                }
            });
        }
    });
    /*]]>*/
</script>';

$htmlHeadXtra[] = $_SESSION['oLP']->create_js();
/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/

$is_allowed_to_edit = api_is_allowed_to_edit(null,true);

$tbl_lp = Database::get_course_table(TABLE_LP_MAIN);
$tbl_lp_item = Database::get_course_table(TABLE_LP_ITEM);
$tbl_lp_view = Database::get_course_table(TABLE_LP_VIEW);

$isStudentView  = (int) $_REQUEST['isStudentView'];
$learnpath_id   = (int) $_REQUEST['lp_id'];
$submit			= $_POST['submit_button'];
/*
==============================================================================
		MAIN CODE
==============================================================================
*/
// using the resource linker as a tool for adding resources to the learning path
if ($action=="add" and $type=="learnpathitem") {
	 $htmlHeadXtra[] = "<script language='JavaScript' type='text/javascript'> window.location=\"../resourcelinker/resourcelinker.php?source_id=5&action=$action&learnpath_id=$learnpath_id&chapter_id=$chapter_id&originalresource=no\"; </script>";
}
if ((! $is_allowed_to_edit) or ($isStudentView)) {
	error_log('New LP - User not authorized in lp_add_item.php');
	header('location:lp_controller.php?action=view&amp;lp_id='.$learnpath_id);
}
 // from here on, we are admin because of the previous condition, so don't check anymore


/*=======================================================
			Add all content types into database, with a just click
 ========================================================*/
  $docid = Security::remove_XSS($_GET['file']);

 if (isset($_GET['work_id']) && $_GET['work_id'] > 0) {
   $docid = Security::remove_XSS($_GET['work_id']);
 } elseif (isset($_GET['forum_id']) && $_GET['forum_id'] > 0) {
   $docid = Security::remove_XSS($_GET['forum_id']);
 } elseif (isset($_GET['thread_id']) && $_GET['thread_id'] > 0) {
   $docid = Security::remove_XSS($_GET['thread_id']);
 } elseif (isset($_GET['survey_id']) && $_GET['survey_id'] > 0) {
   $docid = Security::remove_XSS($_GET['survey_id']);
 }
 if (is_numeric($docid) && $docid > 0 && isset($_GET['action']) && $_GET['action'] == 'add_item') {
  $parent = 0;
  // Get the previous item ID
  $previous = $_SESSION['oLP']->select_previous_item_id();

 switch ($_GET['type']) {
  case TOOL_LINK:
    // Get the link title
    $title = $_SESSION['oLP']->get_resource_title_by_resource_id(TOOL_LINK, $docid);
    // Add a link as Lp Item
    $_SESSION['oLP']->add_item($parent, $previous, TOOL_LINK, $docid, $title, '');
   break;

  case TOOL_STUDENTPUBLICATION:
    // Get the Student publication title
    $title = $_SESSION['oLP']->get_resource_title_by_resource_id(TOOL_STUDENTPUBLICATION, $docid);
    // Add a Student publication as Lp Item
    $_SESSION['oLP']->add_item($parent, $previous, TOOL_STUDENTPUBLICATION, $docid, $title, '');
   break;

  case TOOL_QUIZ:
    // Get the quiz title
    $title = $_SESSION['oLP']->get_resource_title_by_resource_id(TOOL_QUIZ, $docid);
    // Add a Quiz as Lp Item
    $_SESSION['oLP']->add_item($parent, $previous, TOOL_QUIZ, $docid, $title, '');
   break;

  case TOOL_DOCUMENT:
    // Get the document title
    $title = $_SESSION['oLP']->get_resource_title_by_resource_id(TOOL_DOCUMENT, $docid);
    // Add a Document as Lp item
    $_SESSION['oLP']->add_item($parent, $previous, TOOL_DOCUMENT, $docid, $title, '');
   break;

  case TOOL_FORUM:
    // Get the forum title
    $title = $_SESSION['oLP']->get_resource_title_by_resource_id(TOOL_FORUM, $docid);
    // Add a Forum as Lp item
    $_SESSION['oLP']->add_item($parent, $previous, TOOL_FORUM, $docid, $title, '');
   break;

  case 'thread':
    // Get the thread title
    $title = $_SESSION['oLP']->get_resource_title_by_resource_id('thread', $docid);
    // Add a thread as Lp item
    $_SESSION['oLP']->add_item($parent, $previous, 'thread', $docid, $title, '');
   break;

  case TOOL_SURVEY:
    // Get the survey title
    $title = $_SESSION['oLP']->get_resource_title_by_resource_id(TOOL_SURVEY, $docid);
    // Add a survey as Lp item
    $_SESSION['oLP']->add_item($parent, $previous, TOOL_SURVEY, $docid, $title, '');
   break;

 }
  header('location:lp_controller.php?'.api_get_cidreq() .'&action=add_item&type=step&lp_id='.$learnpath_id);
  exit;
 }

// Redirect to main page for add more content.
if (isset($submit)) {
  header('location:lp_controller.php?'.api_get_cidreq() .'&action=add_item&type=step&lp_id='.$learnpath_id);
  exit;
}

$sql_query = "SELECT * FROM $tbl_lp WHERE id = $learnpath_id";
$result=Database::query($sql_query);
$therow=Database::fetch_array($result);

//$admin_output = '';
/*
-----------------------------------------------------------
	Course admin section
	- all the functions not available for students - always available in this case (page only shown to admin)
-----------------------------------------------------------
*/
/*==================================================
			SHOWING THE ADMIN TOOLS
 ==================================================*/
if (isset($_SESSION['gradebook'])){
	$gradebook=	$_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook=='view') {
	$interbreadcrumb[]= array (
			'url' => '../gradebook/'.$_SESSION['gradebook_dest'],
			'name' => get_lang('Gradebook')
		);
}

$interbreadcrumb[]= array ("url"=>"lp_controller.php?action=list", "name"=> get_lang("_learning_path"));
$interbreadcrumb[]= array ("url"=>api_get_self()."?action=build&amp;lp_id=$learnpath_id", "name" => stripslashes("{$therow['name']}"));

switch($_GET['type']){
	case 'chapter':
		$interbreadcrumb[]= array ("url"=>"#", "name" => get_lang("NewChapter"));
	break;
	default:
		$interbreadcrumb[]= array ("url"=>"#", "name" => get_lang("NewStep"));
	break;
}

//Theme calls
$show_learn_path = true;
$lp_theme_css = $_SESSION['oLP']->get_theme();

Display::display_tool_header(null,'Path');

/*if(!isset($_GET['type'])) {
  Display::display_tool_header(null,'Path');
} else {
  Display::display_reduced_header(null,'Path');
}*/

$suredel = trim(get_lang('AreYouSureToDelete'));
//$suredelstep = trim(get_lang('AreYouSureToDeleteSteps'));
?>
<script type='text/javascript'>
/* <![CDATA[ */
function stripslashes(str) {
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\\\/g,'\\');
	str=str.replace(/\\0/g,'\0');
	return str;
}
function confirmation(name)
{
	name=stripslashes(name);
	if (confirm("<?php echo $suredel; ?> " + name + " ?"))
	{
		return true;
	}
	else
	{
		return false;
	}
}
</script>
<?php

//echo $admin_output;

/*
-----------------------------------------------------------
	DISPLAY SECTION
-----------------------------------------------------------
*/
$author_lang_var = api_convert_encoding(get_lang('Modules'), $charset, api_get_system_encoding());
$action_author = '&action=course';
if (api_get_setting('enable_pro_settings') == "true") {
    $author_lang_var = api_convert_encoding(get_lang('Author'), $charset, api_get_system_encoding());
    $action_author = '';
}
$mymodule_lang_var = api_convert_encoding(get_lang('MyModule'), $charset, api_get_system_encoding());
if (api_get_setting('enable_pro_settings') == "true") {
    $mymodule_lang_var = api_convert_encoding(get_lang('Builder'), $charset, api_get_system_encoding());
}
$scenario_lang_var = api_convert_encoding(get_lang('Scenario'), $charset, api_get_system_encoding());
$template_lang_var = api_convert_encoding(get_lang('Templates'), $charset, api_get_system_encoding());
$view_lang_var = api_convert_encoding(get_lang('ViewRight'), $charset, api_get_system_encoding());
echo $_SESSION['oLP']->build_action_menu();
if (isset($_GET['type']) && $_GET['type'] == 'step') {
    echo '<div class="actions">';
        echo '<a href="' . api_get_path(WEB_CODE_PATH) .'newscorm/lp_controller.php?'.api_get_cidreq().'&action=course">'.Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionback')).''.api_convert_encoding(get_lang('Modules'), $charset, api_get_system_encoding()).'</a>';
        echo '<a href="">' . Display::return_icon('pixel.gif', $mymodule_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')).$mymodule_lang_var . '</a>';
        echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;gradebook=&amp;action=admin_view&amp;lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('pixel.gif', $scenario_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorscenario')).$scenario_lang_var . '</a>';
        echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;gradebook=&amp;action=edit&amp;lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('pixel.gif', get_lang('Course_setting'), array('class' => 'toolactionplaceholdericon toolactionauthorsettings')).get_lang('Course_setting') . '</a>';
        echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;gradebook=&amp;action=view&amp;lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('pixel.gif', get_lang('Preview'), array('class' => 'toolactionplaceholdericon toolactionauthorpreview')).get_lang('Preview') . '</a>';
    echo '</div>';
} else {
  echo '<div class="actions">';
  echo '<a href="' . api_get_self() . '?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;action=add_item&amp;type=step&amp;lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('pixel.gif', $mymodule_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')).$mymodule_lang_var . '</a>';
  echo '</div>';
}
//echo '<div align="left" id="content_with_secondary_actions" class="overflow_h" style="min-height:inherit; padding-top:20px;">';
echo '<div id="content">';

		if (isset($_GET['type']) && $_GET['type']=='document' && !isset($_GET['file'])) {
			//echo '<td><div id="frmModel" style="display:none; height:890px;width:100px; position:relative;"></div></td>';
		}
		//echo '<td class="workspace" style="width:100%" valign="top">';
			if (isset($new_item_id) && is_numeric($new_item_id)) {
				switch ($_GET['type']) {

					case 'chapter':
						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewChapterCreated'));
						break;

					case TOOL_LINK:
						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewLinksCreated'));
						break;

					case TOOL_STUDENTPUBLICATION:

						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewStudentPublicationCreated'));
						break;

					case 'module':

						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewModuleCreated'));
						break;

					case TOOL_QUIZ:

						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewExerciseCreated'));
						break;

					case TOOL_DOCUMENT:
					  Display::display_confirmation_message(get_lang('NewDocumentCreated'));
						 echo $_SESSION['oLP']->display_item($new_item_id, true, $msg);
						 break;

					case TOOL_FORUM:
						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewForumCreated'));
						break;

					case 'thread':
						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewThreadCreated'));
						break;

					case TOOL_SURVEY:
						echo $_SESSION['oLP']->display_manipulate($new_item_id, $_GET['type']);
						Display::display_confirmation_message(get_lang('NewSurveyCreated'));
						break;

				}
			} else {                            
				switch ($_GET['type']) {
					case 'chapter':

						echo $_SESSION['oLP']->display_item_form($_GET['type'], get_lang("EnterDataNewChapter"));

						break;

					case 'module':

						echo $_SESSION['oLP']->display_item_form($_GET['type'], get_lang("EnterDataNewModule"));

						break;

					case 'document':

						if(isset($_GET['file']) && is_numeric($_GET['file']))
						{
							echo $_SESSION['oLP']->display_document_form('add', 0, $_GET['file']);
						}
						else
						{
							echo $_SESSION['oLP']->display_document_form('add', 0);
						}

						break;

					case 'hotpotatoes':

						echo $_SESSION['oLP']->display_hotpotatoes_form('add', 0, $_GET['file']);

						break;

					case 'quiz':

						echo $_SESSION['oLP']->display_quiz_form('add', 0, $_GET['file']);

						break;

					case 'forum':

						echo $_SESSION['oLP']->display_forum_form('add', 0, $_GET['forum_id']);

						break;

					case 'thread':

						echo $_SESSION['oLP']->display_thread_form('add', 0, $_GET['thread_id']);

						break;

					case 'link':

						echo $_SESSION['oLP']->display_link_form('add', 0, $_GET['file']);

						break;

					case 'student_publication':
      $extra_data = (isset($_GET['work_id']) && $_GET['work_id'] > 0) ? $_GET['work_id'] : $_GET['file'];
      $extra_info = Security::remove_XSS($extra_data);
						echo $_SESSION['oLP']->display_student_publication_form('add', 0, $extra_info);

						break;

					case 'step':
						echo '<div id="blanket" style="display:none;"></div>';
                                                
                                                if ($_SESSION['oLP']->type == 3 || $_SESSION['oLP']->type == 2) {
                                                    echo '<script type="text/javascript">window.location.href="lp_controller.php?'.api_get_cidreq() .'"</script>';
                                                    break;
                                                }
						echo $_SESSION['oLP']->display_resources();
                                                // Clear exercice session if it exists
                                                if (isset($_SESSION['objExercise'])) {
                                                 api_session_unregister('objExercise');
                                                }
                                                if (isset($_SESSION['objQuestion'])) {
                                                 api_session_unregister('objQuestion');
                                                }
                                                if (isset($_SESSION['objAnswer'])) {
                                                 api_session_unregister('objAnswer');
                                                }
                                                if (isset($_SESSION['questionList'])) {
                                                 api_session_unregister('questionList');
                                                }
                                                if (isset($_SESSION['exerciseResult'])) {
                                                 api_session_unregister('exerciseResult');
                                                }
						break;
					case 'survey':

						echo $_SESSION['oLP']->display_survey_form('add', 0, $_GET['survey_id']);

						break;
				}
			}
echo '</div>';
$view_lang_var = api_convert_encoding(get_lang('ViewRight'), $charset, api_get_system_encoding());
$settings_lang_var = api_convert_encoding(get_lang('Publication'), $charset, api_get_system_encoding());
// display the footer
Display::display_footer();
