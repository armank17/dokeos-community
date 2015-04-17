<?php
/* For licensing terms, see /license.txt */

/**
* Layout (principal view) used for structuring other views
* @author Alberto Flores <aflores609@gmail.com>
*/

// protect a course script
api_protect_course_script(true);

// Header
//Display :: display_tool_header('');
isset($_REQUEST['origin'])?$origin = Security :: remove_XSS($_REQUEST['origin']):$origin='';
// display the Dokeos header
if (isset($origin) && $origin == 'learnpath') {
	//we are in the learnpath tool
	include api_get_path(INCLUDE_PATH) . 'reduced_header.inc.php';
} else {
	// we are not in the learnpath tool
    Display :: display_tool_header(null);
}
// Tool introduction
Display :: display_introduction_section(TOOL_QUIZ);

// Tracking
event_access_tool(TOOL_STUDENTPUBLICATION);

// setting the tool constants
$tool = TOOL_STUDENTPUBLICATION;

isset($_REQUEST['assignment_id'])?$assignmentId = Security :: remove_XSS($_REQUEST['assignment_id']):$assignmentId='';
isset($_REQUEST['activity_id']) ? $activityId = Security :: remove_XSS($_REQUEST['activity_id']) : $activityId = '';
isset($_REQUEST['action']) ? $action = Security :: remove_XSS($_REQUEST['action']) : $action = '';

$workController = new WorkController($assignmentId,$activityId);
$workController->display_action();

if(isset($_SESSION["display_confirmation_message"])){
    Display :: display_confirmation_message2($_SESSION["display_confirmation_message"],false,true);
    unset($_SESSION["display_confirmation_message"]);
}
if(($action=='view_papers') && ($activityId!='') ){
	$courseInfo = api_get_course_info();
	
	echo '<div id="continueContainer" name="continueContainer" >';
	echo '<a onclick="goto(\''.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/index.php'.'\')">';
	echo '<button  id="continue" name="continue" class="continue" style="position: absolute; font-size: 18px; z-index: 100; ">' .get_lang('Continue').  '</button>';
	echo '</a>';
	echo '</div>';
	echo '<script>function goto (href) { window.parent.location.href = href }</script>';
}
echo '<div id="content">';

$group_id = '';
if (isset($_GET['group_id']) && $_GET['group_id'] != '') {
    $group_id = '&amp;group_id=' . $_GET['group_id'];
}
$content = str_replace('&amp;action=add', '&amp;action=add' . $group_id, $content);
echo $content;
echo '</div>';




if(($action=='view_papers') && ($activityId!='') ){

$navigator = api_get_navigator();

$offleft = "130";


echo '
		<script>
		
			function positioning_btnContinue()	{
				
				//$("#continue").hide();
				
				offset = $("#content").offset();
				width = $("#content").width();
				height = $("#content").height();
				console.log((offset.left + width) + " " + (offset.top + height) );
				$("#continue").css("width","140px");
				$("#continue").css("left",(offset.left + width)-'.$offleft.'-10);
				$("#continue").css("top",(offset.top + height)-38);
				
				//$("#continue").show();
			}
		
			$( window ).resize(function() {
				positioning_btnContinue();
			});
			
			
			$( document ).ready(function() {
				$(".data_table").append($("<tr></tr>"));
				positioning_btnContinue();
  
			});
		
		</script>
	';

}



// Footer
if (isset($origin) && $origin == 'learnpath') {
    //we are in the learnpath tool
    include api_get_path(INCLUDE_PATH) . 'tool_footer.inc.php';
} else {
    // we are not in the learnpath tool
    Display :: display_footer();
}
