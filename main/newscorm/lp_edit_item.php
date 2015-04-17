<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 *  Learning Path
 * This is a learning path creation and player tool in Dokeos - previously learnpath_handler.php
 * @package dokeos.learnpath
 * @author Yannick Warnier - cleaning and update for new SCORM tool
 * @author Patrick Cool
 * @author Denes Nagy
 * @author Roan Embrechts, refactoring and code cleaning
 * @author Julio Montoya  - Improving the list of templates
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
include('resourcelinker.inc.php');
//rewrite the language file, sadly overwritten by resourcelinker.inc.php
// name of the language file that needs to be included
$language_file = "learnpath";

/*
-----------------------------------------------------------
	Header and action code
-----------------------------------------------------------
*/
$htmlHeadXtra[] = '
<script type="text/javascript">

function FCKeditor_OnComplete( editorInstance )
{
	//document.getElementById(\'frmModel\').innerHTML = "<iframe height=600px; width=230px; frameborder=0 src=\''.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/fckdialogframe.html \'>";
}

function InnerDialogLoaded()
{
	if (document.all)
	{
		// if is iexplorer
		var B=new window.frames.content_lp___Frame.FCKToolbarButton(\'Templates\',window.content_lp___Frame.FCKLang.Templates);
	}
	else
	{
		var B=new window.frames[0].FCKToolbarButton(\'Templates\',window.frames[0].FCKLang.Templates);
	}
	return B.ClickFrame();
};

</script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css">
        input.error { border: 1px solid red; }
    </style>

    <script type="text/javascript">

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
if ($action=="add" and $type=="learnpathitem")
{
	 $htmlHeadXtra[] = "<script language='JavaScript' type='text/javascript'> window.location=\"../resourcelinker/resourcelinker.php?source_id=5&action=$action&learnpath_id=$learnpath_id&chapter_id=$chapter_id&originalresource=no\"; </script>";
}
if ( (! $is_allowed_to_edit) or ($isStudentView) )
{
	error_log('New LP - User not authorized in lp_add_item.php');
	header('location:lp_controller.php?action=view&amp;lp_id='.$learnpath_id);
}
//from here on, we are admin because of the previous condition, so don't check anymore

$sql_query = "SELECT * FROM $tbl_lp WHERE id = $learnpath_id";
$result=Database::query($sql_query);
$therow=Database::fetch_array($result);

// Get item info
$item_id = Security::remove_XSS($_GET['id']);

// Get the item type
$sql = "SELECT item_type FROM " . $tbl_lp_item . "
		WHERE id = " . Database::escape_string($item_id);
$rs_item = Database::query($sql,__FILE__,__LINE__);
$row_item = Database::fetch_array($rs_item,'ASSOC');
$item_type = $row_item['item_type'];

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
$interbreadcrumb[]= array ("url"=>api_get_self()."?action=build&amp;lp_id=$learnpath_id", "name" =>stripslashes("{$therow['name']}"));
//Theme calls
$show_learn_path=true;
$lp_theme_css=$_SESSION['oLP']->get_theme();

Display::display_tool_header(null,'Path');

$suredel = trim(get_lang('AreYouSureToDelete'));
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

echo '<div class="actions">';
$gradebook = Security::remove_XSS($_GET['gradebook']);
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'newscorm/lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step&lp_id=' . $_SESSION['oLP']->lp_id . '" title="' . get_lang('MyModule') . '">' . Display::return_icon('pixel.gif', get_lang('MyModule'), array('class' => 'toolactionplaceholdericon toolactionauthorcontent')) . ' ' . get_lang('MyModule') . '</a>';
if ($item_type != 'dokeos_chapter' && $item_type != 'chapter') {
    echo '<a href="' . api_get_self() . '?cidReq=' . Security :: remove_XSS($_GET['cidReq']) . '&amp;action=edit_item_prereq&amp;view=build&amp;id=' . $item_id . '&amp;lp_id=' . Security::remove_XSS($_GET['lp_id']) . '" title="' . get_lang('Prerequisites') . '">'.Display::return_icon('pixel.gif',get_lang('Prerequisites'),array('class'=>'actionplaceholdericon actionauthorprerequisites','align'=>'absbottom') ). '' . get_lang('Prerequisites') . '</a>';
}
echo '</div>';

echo '<div id="content_with_secondary_actions">';
echo '<table style="width:100%" cellpadding="0" cellspacing="0" class="lp_build">';
		echo '<tr>';
		echo '<td class="workspace" valign="top" style="width:100%">';
			if(isset($is_success) && $is_success === true) {
				$msg = '<div class="lp_message" style="margin-bottom:10px;">';
					$msg .= 'The item has been edited.';
				$msg .= '</div>';
				echo '<script type="text/javascript">window.location.href="lp_controller.php?'.api_get_cidReq().'&action=admin_view&lp_id='.$learnpath_id.'"</script>';
			} else {
				echo $_SESSION['oLP']->display_edit_item($_GET['id']);
			}
		echo '</td>';
	echo '</tr>';
echo '</table>';
echo '</div>';
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();