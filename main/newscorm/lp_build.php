<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	Learning Path
*	This is a learning path creation and player tool in Dokeos - previously learnpath_handler.php
*	@package dokeos.learnpath
*	@author	Yannick Warnier - cleaning and update for new SCORM tool
*	@author Patrick Cool
*	@author Denes Nagy
*	@author Roan Embrechts, refactoring and code cleaning
*/

$_SESSION['whereami'] = 'lp/build';
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
$chapter_id     = $_GET['chapter_id'];
$title          = $_POST['title'];
$description   = $_POST['description'];
$Submititem     = $_POST['Submititem'];
$action         = $_REQUEST['action'];
$id             = (int) $_REQUEST['id'];
$type           = $_REQUEST['type'];
$direction      = $_REQUEST['direction'];
$moduleid       = $_REQUEST['moduleid'];
$prereq         = $_REQUEST['prereq'];
$type           = $_REQUEST['type'];
*/
/*
==============================================================================
		MAIN CODE
==============================================================================
*/
// using the resource linker as a tool for adding resources to the learning path
if ($action=="add" and $type=="learnpathitem")
{
	 $htmlHeadXtra[] = "<script language='JavaScript' type='text/javascript'> window.location=\"../resourcelinker/resourcelinker.php?source_id=5&action=$action&learnpath_id=$learnpath_id&chapter_id=$chapter_id&originalresource=no\"; </script>";
}
if ( (! $is_allowed_to_edit) or ($isStudentView) )
{
	error_log('New LP - User not authorized in lp_build.php');
	header('location:lp_controller.php?action=view&amp;lp_id='.$learnpath_id);
}
//from here on, we are admin because of the previous condition, so don't check anymore

/* learnpath is just created, go get the last id */
$is_new = false;

if($learnpath_id == 0)
{
	$is_new = true;

	$sql = "
		SELECT id
		FROM " . $tbl_lp . "
		ORDER BY id DESC
		LIMIT 0, 1";
	$result = Database::query($sql);
	$row = Database::fetch_array($result);

	$learnpath_id = $row['id'];
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



/*==================================================
	prerequisites setting end
 ==================================================*/
if (!empty($_GET['gradebook']) && $_GET['gradebook']=='view' ) {
	$_SESSION['gradebook']=Security::remove_XSS($_GET['gradebook']);
	$gradebook=	$_SESSION['gradebook'];
} elseif (empty($_GET['gradebook'])) {
	unset($_SESSION['gradebook']);
	$gradebook=	'';
}

if (!empty($gradebook) && $gradebook=='view') {
	$interbreadcrumb[] = array (
		'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
		'name' => get_lang('Gradebook')
	);
}
$interbreadcrumb[]= array ("url"=>"lp_controller.php?action=list", "name"=> get_lang("_learning_path"));
$interbreadcrumb[]= array ("url"=>api_get_self()."?action=build&amp;lp_id=$learnpath_id", "name" => stripslashes("{$therow['name']}"));

//Theme calls
$lp_theme_css=$_SESSION['oLP']->get_theme();
$show_learn_path=true;
Display::display_tool_header(null,'Path');

//api_display_tool_title($therow['name']);

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

//echo $_SESSION['oLP']->build_action_menu();
echo '<div class="actions">';
  echo '<a href="' . api_get_self() . '?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '">' . Display::return_icon('pixel.gif', get_lang('Author'), array('class' => 'toolactionplaceholdericon toolactionauthor')).get_lang("Author") . '</a>';
  echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;gradebook=&amp;action=add_item&amp;type=step&amp;lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('content.png', get_lang('Content')).get_lang("Content") . '</a>';
  echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;gradebook=&amp;action=admin_view&amp;lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('organize.png', get_lang('Scenario')).get_lang("Scenario") . '</a>';
  echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&amp;gradebook=&amp;action=edit&amp;lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('publication_setup.png', get_lang('Publication')).get_lang("Publication") . '</a>';
  echo '</div>';

echo '<div id="content_with_secondary_actions">';
echo '<table style="width:100%" cellpadding="0" cellspacing="0" class="lp_build">';
	echo '<tr>';
		echo '<td style="width:18%" class="tree" valign="top">';
			echo '<div class="lp_tree">';
				//build the tree with the menu items in it
				echo $_SESSION['oLP']->build_tree();
			echo '</div>';
		echo '</td>';
		echo '<td style="width:82%" class="workspace" valign="top">';
			if(isset($is_success) && $is_success === true) {
				Display::display_confirmation_message(get_lang('ItemRemoved'));
			} else {
				if($is_new) {
					Display::display_normal_message(get_lang('LearnpathAdded'), false);
				}
					// Display::display_normal_message(get_lang('LPCreatedAddChapterStep'), false);
					$gradebook = Security::remove_XSS($_GET['gradebook']);
					$learnpathadded = Display::return_icon('gallery/creative.gif','',array('align'=>'right'));
					$learnpathadded .= '<p><strong>'.get_lang('LearnPathAddedTitle').'</strong><br /><br />';
					$learnpathadded .= '<a href="lp_controller.php?'.api_get_cidreq().'&amp;action=build&amp;lp_id='.Security::remove_XSS($_GET['lp_id']).'" target="_parent">'.Display::return_icon('learnpath_build.gif', get_lang('Build'),array('style'=> 'vertical-align: middle;')).' '.get_lang('Build')."</a>: ".get_lang('BuildComment').'<br />';
					$learnpathadded .= '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;gradebook='.$gradebook.'&amp;action=admin_view&amp;lp_id=' . $_SESSION['oLP']->lp_id . '" title="'.get_lang("Scenario").'">'.Display::return_icon('learnpath_organize.gif', get_lang('Scenario'),array('style'=> 'vertical-align: middle;')).' '.get_lang('Scenario').'</a>: '.get_lang('BasicOverviewComment').'<br />';
					$learnpathadded .= '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;gradebook='.$gradebook.'&amp;action=view&amp;lp_id='.$_SESSION['oLP']->lp_id.'">'.Display::return_icon('learnpath_view.gif', get_lang('Display'),array('style'=> 'vertical-align: middle;')).' '.get_lang('Display').'</a>: '.get_lang('DisplayComment').'<br />';
					$learnpathadded .= '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;gradebook='.$gradebook.'&amp;action=add_item&amp;type=chapter&amp;lp_id=' . $_SESSION['oLP']->lp_id . '" title="'.get_lang("NewChapter").'">'.Display::return_icon('lp_dokeos_chapter_add.gif', get_lang('NewChapter'),array('style'=> 'vertical-align: middle;')).' '.get_lang('NewChapter').'</a>: '.get_lang('NewChapterComment').'<br />';
					$learnpathadded .= '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;gradebook='.$gradebook.'&amp;action=add_item&amp;type=step&amp;lp_id=' . $_SESSION['oLP']->lp_id . '" title="'.get_lang("AddActivity").'">'.Display::return_icon('new_test.gif', get_lang('AddActivity'),array('style'=> 'vertical-align: middle;')).' '.get_lang('AddActivity').'</a>: '.get_lang('NewStepComment').'<br />';
					$learnpathadded .= '<br /><br /><br /><br /></p>';
					Display::display_normal_message($learnpathadded, false);
			}
		echo '</td>';
	echo '</tr>';
echo '</table>';
echo '</div>';

// bottom actions bar
echo '<div class="actions">';
echo '</div>';

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
