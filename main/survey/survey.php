<?php
/* For licensing terms, see /dokeos_license.txt */

/**
*	@package dokeos.survey
* 	@author unknown
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University: cleanup, refactoring and rewriting large parts of the code
* 	@version $Id: survey.php 22573 2009-08-03 03:38:13Z yannoo $
*
* 	@todo use quickforms for the forms
*/

// name of the language file that needs to be included
$language_file = 'survey';

// including the global dokeos file
require ('../inc/global.inc.php');

// including additional libraries
require_once('survey.lib.php');
require_once (api_get_path(LIBRARY_PATH)."/course.lib.php");
require_once api_get_path(LIBRARY_PATH)  . 'searchengine.lib.php';
$add_lp_param = "";
if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
  $lp_id = Security::remove_XSS($_GET['lp_id']);
 $htmlHeadXtra[] = '<script type="text/javascript">
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
  $add_lp_param = "&lp_id=".$lp_id;
}
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.ui.all.js" type="text/javascript" language="javascript"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/functionsAlerts.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).on("ready",fload);
    function fload(){
        $("#submit_save").click(function(){
    var boxes = $("#contentLeft ul>li").find("input[type=checkbox]");
    var all = 0;
    boxes.each(function(){
       if( $(this).is(":checked") ){
          all = 1;
       }
    });
    if(all == 0) return;
            $.confirm(\''.get_lang("ConfirmYourChoice").'\',\''.get_lang("ConfirmationDialog").'\', function() {
                document.forms.survey_list.submit();
            });
        });
        }
</script>';

/** @todo this has to be moved to a more appropriate place (after the display_header of the code)*/
// coach can't view this page
$extend_rights_for_coachs = api_get_setting('extend_rights_for_coach_on_survey');
if (!api_is_allowed_to_edit(false,true) || (api_is_course_coach() && $extend_rights_for_coachs=='false'))
{
		// Display header
		Display::display_tool_header();
                // Tool introduction
                Display::display_introduction_section('survey', 'left');
		// start the content div
		echo '<div id="content">';

		Display :: display_error_message(get_lang('NotAllowed'), false, true);

		// close the content div
		echo '</div>';

		// Display the footer
		Display::display_footer();
		exit;
}

// Database table definitions
$table_survey 					= Database :: get_course_table(TABLE_SURVEY);
$table_survey_question 			= Database :: get_course_table(TABLE_SURVEY_QUESTION);
$table_survey_question_option 	= Database :: get_course_table(TABLE_SURVEY_QUESTION_OPTION);
$table_survey_question_group    = Database :: get_course_table(TABLE_SURVEY_QUESTION_GROUP);
$table_course 					= Database :: get_main_table(TABLE_MAIN_COURSE);
$table_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$user_info 						= Database :: get_main_table(TABLE_MAIN_SURVEY_REMINDER); // TODO: To be checked. TABLE_MAIN_SURVEY_REMINDER has not been defined.
$survey_id = intval($_GET['survey_id']);

// breadcrumbs
$interbreadcrumb[] = array ("url" => "survey_list.php", "name" => get_lang('SurveyList'));

// getting the survey information
if (isset($_GET['survey_id'])) {
	$course_code = api_get_course_id();
	if ($course_code!=-1) {
		$survey_data = survey_manager::get_survey($survey_id);
	} else {
		// Display header
		Display::display_tool_header();

		// start the content div
		echo '<div id="content">';

		Display :: display_error_message(get_lang('NotAllowed'), false, true);

		// close the content div
		echo '</div>';

		// Display the footer
		Display::display_footer();
		exit;
	}
}

if (api_substr($survey_data['title'],0,3)!='<p>'){
	$tool_name = strip_tags(api_substr(api_html_entity_decode($survey_data['title'],ENT_QUOTES,$charset), 0, 40));
}else{
	$tool_name = strip_tags(api_substr(api_html_entity_decode(api_substr($survey_data['title'],3,-4),ENT_QUOTES,$charset), 0, 40));
}
$is_survey_type_1 = ($survey_data['survey_type']==1)?true:false;
if (api_strlen(strip_tags($survey_data['title'])) > 40)
{
	$tool_name .= '...';
}

if($is_survey_type_1 && ($_GET['action']=='addgroup')||($_GET['action']=='deletegroup'))
{
	$_POST['name'] = trim($_POST['name']);

	if(($_GET['action']=='addgroup'))
	{
		if(!empty($_POST['group_id']))
		{
			Database::query('UPDATE '.$table_survey_question_group.' SET description = \''.Database::escape_string($_POST['description']).'\' WHERE id = \''.Database::escape_string($_POST['group_id']).'\'');
			$sendmsg = 'GroupUpdatedSuccessfully';
		}
		elseif(!empty($_POST['name']))
		{
			Database::query('INSERT INTO '.$table_survey_question_group.' (name,description,survey_id) values (\''.Database::escape_string($_POST['name']).'\',\''.Database::escape_string($_POST['description']).'\',\''.Database::escape_string($survey_id).'\') ');
			$sendmsg = 'GroupCreatedSuccessfully';
		} else {
			$sendmsg = 'GroupNeedName';
		}
	}

	if($_GET['action']=='deletegroup'){
                $sql = 'DELETE FROM '.$table_survey_question_group.' WHERE id = '.Database::escape_string($_GET['gid']).' and survey_id = '.Database::escape_string($survey_id);
		Database::query($sql);
		$sendmsg = 'GroupDeletedSuccessfully';
	}

	header('Location:survey.php?'.api_get_cidreq().'&survey_id='.$survey_id.'&sendmsg='.$sendmsg);
	exit;
}

// Displaying the header
Display::display_tool_header();
// Tool introduction
Display::display_introduction_section('survey', 'left');

// Action handling
$my_action_survey		= Security::remove_XSS($_GET['action']);
$my_question_id_survey  = Security::remove_XSS($_GET['question_id']);
$my_survey_id_survey    = Security::remove_XSS($_GET['survey_id']);
$message_information    = Security::remove_XSS($_GET['message']);

if(isset($_REQUEST['chk_survey'])){
    $survey_id = $_REQUEST['survey_id'];
    $array_survey_ids = $_REQUEST['chk_survey'];
    foreach($array_survey_ids as $id => $value){
        $message = survey_manager::delete_survey_question($survey_id, $value, $survey_data['is_shared']);
    }
    $_SESSION['display_confirmation_message'] = $message;
    header('Location:survey.php?'.  api_get_cidreq().'&survey_id='.Security::remove_XSS($_GET['survey_id']));
    //exit();

}

if (isset($_GET['action'])) {
	if (($_GET['action'] == 'moveup' OR $_GET['action'] == 'movedown') AND isset($_GET['question_id'])) {
		survey_manager::move_survey_question($my_action_survey,$my_question_id_survey,$my_survey_id_survey);
		Display::display_confirmation_message(get_lang('SurveyQuestionMoved'));                
	}
	if ($_GET['action'] == 'delete' AND is_numeric($_GET['question_id'])) {
		survey_manager::delete_survey_question($my_survey_id_survey, $my_question_id_survey, $survey_data['is_shared']);
	}
			}


if(!empty($survey_data['survey_version']))
	echo '<b>'.get_lang('Version').': '.$survey_data['survey_version'].'</b>';

// We exit here is the first or last question is a pagebreak (which causes errors)
SurveyUtil::check_first_last_question($_GET['survey_id']);
$user_id = $_GET['survey_id'];
$sql = "SELECT question_per_page FROM $table_survey WHERE survey_id = '".Database::escape_string($user_id)."'";
$result = Database::query($sql, __FILE__, __LINE__);
$row = Database::fetch_array($result);
$allinone = ($row['question_per_page'] != 0) ? 'all' : '';

// Action links
$survey_actions = '<a href="survey_list.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Survey')),array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Survey')).'</a>';
$survey_actions .= '<a href="create_new_survey.php?'.api_get_cidreq().'&action=edit&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif', get_lang('Edit'),array('class' => 'toolactionplaceholdericon toolactionedit_32')).' '.get_lang('EditSurvey').'</a>';
//$survey_actions .= '<a href="survey_list.php?'.api_get_cidreq().'&action=delete&survey_id='.$survey_id.'" onclick="javascript:if(!confirm(\''.addslashes(api_htmlentities(get_lang("DeleteSurvey").'?',ENT_QUOTES,$charset)).'\')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'),array('class' => 'toolactionplaceholdericon toolactiondelete_32')).' '.get_lang('DeleteSurvey').'</a>';
//$survey_actions .= '<a href="create_survey_in_another_language.php?id_survey='.$survey_id.'">'.Display::return_icon('copy.gif', get_lang('Copy')).'</a>';
$survey_actions .= '<a href="preview'. $allinone .'.php?'.api_get_cidreq().'&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif', get_lang('Preview'),array('class' => 'toolactionplaceholdericon toolactionpreview')).' '.get_lang('Preview').'</a>';
$survey_actions .= '<a href="survey_invite.php?'.api_get_cidreq().'&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif', get_lang('Publish'),array('class' => 'toolactionplaceholdericon toolactionpublish_survey')).' '.get_lang('Publish').'</a>';
//$survey_actions .= '<a href="reporting.php?'.api_get_cidreq().'&survey_id='.$survey_id.'">'.Display::return_icon('report_32.png', get_lang('Reporting')).' '.get_lang('Reporting').'</a>';
$survey_actions .= '<a href="survey_question.php?'.api_get_cidreq().'&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif', get_lang('Printview'),array('class' => 'toolactionplaceholdericon toolactionprint32')).' '.get_lang('Printview').'</a>';
echo '<div class="actions">'.$survey_actions.'</div>';

if ($survey_data['survey_type']==0) {
	echo '<div class="actions cus-lsur">';
	echo '<a style="padding-left:0px;" href="question.php?'.api_get_cidreq().'&action=add&type=yesno&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('YesNo'),array('class' => 'surveytypeplaceholdericon toolactioyesno')).'<div align="center">'.get_lang('YesNo').'</div></a>';
	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=multiplechoice&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('MChoice'),array('class' => 'surveytypeplaceholdericon toolactionmultiplechoice')).'<div align="center">'.get_lang('MChoice').'</div></a>';
	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=multipleresponse&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('MultipleResponse'),array('class' => 'surveytypeplaceholdericon toolactionmultipleanswer')).'<div align="center">'.get_lang('MultipleResponse').'</div></a>';
	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=open&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('Open'),array('class' => 'surveytypeplaceholdericon toolactionopen')).'<div align="center">'.get_lang('Open').'</div></a>';
	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=dropdown&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('Dropdown'),array('class' => 'surveytypeplaceholdericon toolactiondropdown')).'<div align="center">'.get_lang('Dropdown').'</div></a>';
	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=percentage&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('Percentage'),array('class' => 'surveytypeplaceholdericon toolactionpercent')).'<div align="center">'.get_lang('Percentage').'</div></a>';
	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=score&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('Score'),array('class' => 'surveytypeplaceholdericon toolactionscore')).'<div align="center">'.get_lang('Score').'</div></a>';
	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=comment&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif',get_lang('Comment'),array('class' => 'surveytypeplaceholdericon toolactioncomment')).'<div align="center">'.get_lang('Comment').'</div></a>';
//	echo '<a href="question.php?'.api_get_cidreq().'&action=add&type=pagebreak&survey_id='.$survey_id.'">'.Display::return_icon('pageend.png',get_lang('Pageend')).'<div align="center">'.get_lang('Pageend').'</div></a>';
	echo '</div>';
} else {
	echo '<div class="actions">';
	//echo '<a href="group.php?'.api_get_cidreq().'&action=add&survey_id='.$survey_id.'"><img src="../img/yesno.gif" /><br />'.get_lang('Add groups').'</a></div>';
	echo '<a style="padding-left:0px;" href="question.php?'.api_get_cidreq().'&action=add&type=personality&survey_id='.$survey_id.'"><img src="../img/yesno.gif" />'.get_lang('PersonalityQuestion').'</a></div>';
	echo '</div>';
}

echo '<script type="text/javascript">
	$(document).ready(function(){
		$(function() {
			$("#contentLeft ul").sortable({ opacity: 0.6, cursor: "move", cancel: ".nodrag", update: function() {
				var order = $(this).sortable("serialize") + "&action=updateSurvey";
				var record = order.split("&");
			var recordlen = record.length;
			var disparr = new Array();
			for(var i=0;i<(recordlen-1);i++){
				var recordval = record[i].split("=");
				disparr[i] = recordval[1];
			}
			$.ajax({
			type: "GET",
			url: "survey.ajax.php?'.api_get_cidReq().'&action=updateSurvey&survey_id='.Security::remove_XSS($_GET['survey_id']).'&disporder="+disparr,
			success: function(msg){
                            document.location="survey.php?survey_id='.Security::remove_XSS($_GET['survey_id']).'&' . api_get_cidreq() . '";
                        }
		})
			}
			});
		});
	});
	</script>';


//if (isset($_GET['message'])) {
//	// we have created the survey or updated the survey
//	if (in_array($_GET['message'], array('SurveyUpdatedSuccesfully','SurveyCreatedSuccesfully'))) {
//		//Display::display_confirmation_message(get_lang($message_information).','.PHP_EOL.api_strtolower(get_lang('YouCanNowAddQuestionToYourSurvey')), false, true);
//                //$_SESSION['survey_message']=get_lang($message_information).','.PHP_EOL.api_strtolower(get_lang('YouCanNowAddQuestionToYourSurvey'));
//	}
//	// we have added a question
//	if (in_array($_GET['message'], array('QuestionAdded','QuestionUpdated'))) {
//		//Display::display_confirmation_message(get_lang($message_information), false, true);
//                $_SESSION['survey_message']=get_lang($message_information);
//	}
//
//	if (in_array($_GET['message'], array('YouNeedToCreateGroups'))) {
//		Display::display_warning_message(get_lang($message_information), false, true);
//	}
//}


if (isset($_SESSION['display_confirmation_message'])) {
    echo Display::display_confirmation_message2($_SESSION['display_confirmation_message'], false, true);
    unset($_SESSION['display_confirmation_message']);
}
// start the content div
echo '<div id="content">';

if(isset($_GET['show_message']) AND $_GET['show_message']== '1' ){
    echo Display::display_warning_message(get_lang('SelectToSurveyToDeleted'), false, true);
}


echo '<form name="survey_list" id="survey_list" method="post">';
// Displaying the table header with all the questions
echo '<table class="data_table">';
echo '	<tr class="row_odd">';
echo '		<th width="5%">'.get_lang('Move').'</th>';
echo '		<th width="5%">'.get_lang('Delete').'</th>';
echo '		<th width="4%">'.get_lang('QuestionNumber').'</th>';
echo '		<th width="34%">'.get_lang('Title').'</th>';
echo '		<th width="15%">'.get_lang('Type').'</th>';
echo '		<th width="8%" >'.get_lang('NumberOfOptions').'</th>';
echo '		<th width="5%">'.get_lang('Edit').'</th>';
//echo '		<th width="5%">'.get_lang('Delete').'</th>';
if($is_survey_type_1) {
	echo '<th width="10%">'.get_lang('Condition').'</th>';
    echo '<th width="5%">'.get_lang('Group').'</th>';
}
echo '	</tr>';
echo '</table>';


//echo '<tr><td colspan="7">';
echo '<div id="contentWrap"><div id="contentLeft"><ul class="dragdrop nobullets" id="checkboxes"> ';
// Displaying the table contents with all the questions
$question_counter = 1;
$sql = "SELECT * FROM $table_survey_question_group WHERE survey_id = '".Database::escape_string($survey_id)."' ORDER BY id";
$result = Database::query($sql, __FILE__, __LINE__);
$groups = array();
while($row = Database::fetch_array($result)) {
    $groups[$row['id']] = $row['name'];
}
$sql = "SELECT survey_question.*, count(survey_question_option.question_option_id) as number_of_options
			FROM $table_survey_question survey_question
			LEFT JOIN $table_survey_question_option survey_question_option
			ON survey_question.question_id = survey_question_option.question_id
			WHERE survey_question.survey_id = '".Database::escape_string($survey_id)."'
			GROUP BY survey_question.question_id
			ORDER BY survey_question.sort ASC";
$result = Database::query($sql, __FILE__, __LINE__);
$question_counter_max = Database::num_rows($result);
$i=1;
while ($row = Database::fetch_array($result,'ASSOC')) {
	echo '<li id="recordsArray_'.$row['question_id'].'" class="category"><table class="data_table">';
	if($i%2==0) $css_class = 'row_odd';
				else $css_class = 'row_even';
				$i++;
	echo '<tr class="'.$css_class.'">';
	echo '	<td width="5%" align="center">'.Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon actionsdraganddrop')).'</td>';
	echo '	<td class="nodrag" width="5%" align="center"><input type="checkbox" name="chk_survey[]" value="'.$row['question_id'].'"></td>';
	echo '	<td class="nodrag" width="4%" align="center">'.$question_counter.'</td>';
	echo '	<td class="nodrag" width="34%" style="text-align:left; padding-left:5px; padding-right:5px;">';
	if (api_strlen($row['survey_question']) > 100) {
		echo api_substr(strip_tags($row['survey_question']),0, 100).' ... ';
	} else {
		echo $row['survey_question'];
	}

/*	if ($row['type'] == 'yesno') {
		$tool_name = get_lang('YesNo');
	} else if ($row['type'] == 'multiplechoice') {
		$tool_name = get_lang('UniqueSelect');
	} else {
		$tool_name = get_lang(api_ucfirst(Security::remove_XSS($row['type'])));
	}*/

	if ($row['type'] == 'yesno') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('YesNo'),array('class' => 'surveytypeplaceholdericon_list toolactionyesno_list'));
	}
	elseif ($row['type'] == 'multiplechoice') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('MChoice'),array('class' => 'surveytypeplaceholdericon_list toolactionmultiplechoice_list'));
	}
	elseif ($row['type'] == 'multipleresponse') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('MultipleResponse'),array('class' => 'surveytypeplaceholdericon_list toolactionmultipleanswer_list'));
	}
	elseif ($row['type'] == 'open') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('Open'),array('class' => 'surveytypeplaceholdericon_list toolactionopen_list'));
	}
	elseif ($row['type'] == 'dropdown') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('Dropdown'),array('class' => 'surveytypeplaceholdericon_list toolactiondropdown_list'));
	}
	elseif ($row['type'] == 'percentage') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('Percentage'),array('class' => 'surveytypeplaceholdericon_list toolactionpercent_list'));
	}
	elseif ($row['type'] == 'score') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('Score'),array('class' => 'surveytypeplaceholdericon_list toolactionscore_list'));
	}
	elseif ($row['type'] == 'comment') {
		$tool_name = Display::return_icon('pixel.gif',get_lang('Comment'),array('class' => 'surveytypeplaceholdericon_list toolactioncomment_list'));
	}


	echo '</td>';
	echo '	<td class="nodrag" width="15%" align="center">'.$tool_name.'</td>';
	echo '	<td class="nodrag" width="8%" align="center">'.$row['number_of_options'].'</td>';
	echo '	<td class="nodrag" width="5%" align="center">';
	echo '		<a href="question.php?'.api_get_cidreq().'&action=edit&type='.$row['type'].'&survey_id='.$survey_id.'&question_id='.$row['question_id'].'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a></td>';
/*	echo '	<td width="5%" align="center">';
	echo '		<a href="survey.php?'.api_get_cidreq().'&action=delete&survey_id='.$survey_id.'&question_id='.$row['question_id'].'" onclick="javascript:if(!confirm(\''.addslashes(api_htmlentities(get_lang("DeleteSurveyQuestion").'?',ENT_QUOTES,$charset)).'\')) return false;">'.Display::return_icon('delete_link.png', get_lang('Delete')).'</a></td>';*/
/*	if ($question_counter > 1)
	{
		echo '		<a href="survey.php?'.api_get_cidreq().'&action=moveup&survey_id='.$survey_id.'&question_id='.$row['question_id'].'">'.Display::return_icon('up.gif', get_lang('MoveUp')).'</a>';
	} else {
		Display::display_icon('up_na.gif');
	}
	if ($question_counter < $question_counter_max)
	{
		echo '		<a href="survey.php?'.api_get_cidreq().'&action=movedown&survey_id='.$survey_id.'&question_id='.$row['question_id'].'">'.Display::return_icon('down.gif', get_lang('MoveDown')).'</a>';
	} else {
		Display::display_icon('down_na.gif');
	}
	echo '	</td>';*/
	$question_counter++;

	if($is_survey_type_1)
    {
    	echo '<td class="nodrag" width="10%">'.(($row['survey_group_pri']==0)?get_lang('Secondary'):get_lang('Primary')).'</td>';
        echo '<td class="nodrag" width="5%">'.(($row['survey_group_pri']==0)?$groups[$row['survey_group_sec1']].'-'.$groups[$row['survey_group_sec2']]:$groups[$row['survey_group_pri']]).'</td>';
    }
	echo '</tr>';
	echo '</table></li>';
}
echo '</ul></div></div>';
//echo '</table>';
if($question_counter_max <> 0)
	 {
		//echo '<tr><td><br/><button  class="cancel" type="submit" name="submit_save" onclick="javascript:if(!confirm(\'Please confirm your choice\')) return false;" id="submit_save" style="float:left;" >'.get_lang('Delete').'</button></td></tr>';
                echo '<tr><td><br/><button  class="cancel" type="button" name="submit_save"  id="submit_save" style="float:left;" >'.get_lang('Delete').'</button></td></tr>';
	 }
  echo '</form>';

if($is_survey_type_1)
{
	echo '<br /><br /><b>'.get_lang('ManageGroups').'</b><br /><br />';

	if (in_array($_GET['sendmsg'], array('GroupUpdatedSuccessfully','GroupDeletedSuccessfully','GroupCreatedSuccessfully'))){
		echo Display::display_confirmation_message(get_lang($_GET['sendmsg']), false, true);
	}

	if (in_array($_GET['sendmsg'], array('GroupNeedName'))){
		echo Display::display_warning_message(get_lang($_GET['sendmsg']), false, true);
	}

	echo '<table border="0"><tr><td width="100">'.get_lang('Name').'</td><td>'.get_lang('Description').'</td></tr></table>';

	echo '<form action="survey.php?action=addgroup&survey_id='.$survey_id.'" method="post">';
	if($_GET['action']=='editgroup') {
		$sql = 'SELECT name,description FROM '.$table_survey_question_group.' WHERE id = '.Database::escape_string($_GET['gid']).' AND survey_id = '.Database::escape_string($survey_id).' limit 1';
		$rs = Database::query($sql,__FILE__,__LINE__);
		$editedrow = Database::fetch_array($rs,'ASSOC');

		echo	'<input type="text" maxlength="20" name="name" value="'.$editedrow['name'].'" size="10" disabled>';
		echo	'<input type="text" maxlength="150" name="description" value="'.$editedrow['description'].'" size="40">';
		echo	'<input type="hidden" name="group_id" value="'.Security::remove_XSS($_GET['gid']).'">';
		echo	'<input type="submit" value="'.get_lang('Save').'"'.'<input type="button" value="'.get_lang('Cancel').'" onclick="window.location.href = \'survey.php?survey_id='.Security::remove_XSS($survey_id).'\';" />';
	} else {
		echo	'<input type="text" maxlength="20" name="name" value="" size="10">';
		echo	'<input type="text" maxlength="250" name="description" value="" size="80">';
		echo	'<input type="submit" value="'.get_lang('Create').'"';
	}
	echo	'</form><br />';

	echo '<table class="data_table">';
	echo '	<tr class="row_odd">';
	echo '		<th width="200">'.get_lang('Name').'</th>';
	echo '		<th>'.get_lang('Description').'</th>';
	echo '		<th width="100">'.get_lang('Modify').'</th>';
	echo '	</tr>';

	$sql = 'SELECT id,name,description FROM '.$table_survey_question_group.' WHERE survey_id = '.Database::escape_string($survey_id).' ORDER BY name';

	$rs = Database::query($sql,__FILE__,__LINE__);
	while($row = Database::fetch_array($rs,ASSOC)){
		$grouplist .= '<tr><td>'.$row['name'].'</td><td>'.$row['description'].'</td><td>'.
		'<a href="survey.php?survey_id='.$survey_id.'&gid='.$row['id'].'&action=editgroup">'.
		Display::return_icon('edit_link.png', get_lang('Edit')).'</a> '.
		'<a href="survey.php?survey_id='.$survey_id.'&gid='.$row['id'].'&action=deletegroup" onclick="javascript:if(!confirm(\''.addslashes(api_htmlentities(sprintf(get_lang('DeleteSurveyGroup'),$row['name']).'?',ENT_QUOTES,$charset)).'\')) return false;">'.
		Display::return_icon('', get_lang('Delete')).'</a>'.
		'</td></tr>';
	}
	echo $grouplist.'</table>';
}

// close the content div
echo '</div>';

 // bottom actions bar
echo '<div class="actions">';
echo '&nbsp;<a href="reporting.php?'.api_get_cidreq().'&survey_id='.$survey_id.'">'.Display::return_icon('pixel.gif', get_lang('Reporting'), array('class' => 'actionplaceholdericon actiontracking')).get_lang('Reporting').'</a>';
echo '</div>';


// Display the footer
Display :: display_footer();
?>
