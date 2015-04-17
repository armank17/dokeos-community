<?php
// $Id: question_pool.php 20451 2009-05-10 12:02:22Z ivantcholakov $

/*
 ==============================================================================
 Dokeos - elearning and course management software

 Copyright (c) 2004-2009 Dokeos SPRL
 Copyright (c) 2003 Ghent University (UGent)
 Copyright (c) 2001 Universite catholique de Louvain (UCL)
 Copyright (c) various contributors

 For a full list of contributors, see "credits.txt".
 The full license can be read in "license.txt".

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 See the GNU General Public License for more details.

 Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
 Mail: info@dokeos.com
 ==============================================================================
 */

/**
 * 	Question Pool
 * 	This script allows administrators to manage questions and add them into their exercises.
 * 	One question can be in several exercises
 * 	@package dokeos.exercise
 * 	@author Olivier Brouckaert
 * 	@version $Id: question_pool.php 20451 2009-05-10 12:02:22Z ivantcholakov $
 */
// name of the language file that needs to be included
$language_file = 'exercice';

include_once 'exercise.class.php';
include_once 'question.class.php';
include_once 'answer.class.php';
include_once '../inc/global.inc.php';
require_once '../newscorm/learnpath.class.php';

$this_section = SECTION_COURSES;

$is_allowedToEdit = api_is_allowed_to_edit();

$origin = Security::remove_XSS($_GET['origin']);
$examId = Security::remove_XSS($_GET['examId']);

// Variable
$learnpath_id = Security::remove_XSS($_GET['lp_id']);
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


// Add the extra lp_id parameter to some links
$add_params_for_lp = '';
if (isset($_GET['lp_id'])) {
  $add_params_for_lp = "&lp_id=".$learnpath_id;
}

if(isset($_REQUEST['fromExercise']))
{
	$fromExercise = $_REQUEST['fromExercise'];
}

if (!empty($gradebook) && $gradebook == 'view') {
	$interbreadcrumb[] = array(
     'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
     'name' => get_lang('Gradebook')
	);
}

$nameTools = get_lang('QuestionPool');
$interbreadcrumb[] = array("url" => "exercice.php", "name" => get_lang('Exercices'));
$exercice_id = Security::remove_XSS($_REQUEST['fromExercise']);
/*if (api_get_setting('enable_document_templates') !== 'true') {
    header('Location:'.api_get_path(WEB_CODE_PATH).'exercice/admin.php?exerciseId='.$exercice_id.'&'.api_get_cidreq());
    exit;
}*/


// if admin of course
if ($is_allowedToEdit) {
	Display::display_tool_header($nameTools, 'Exercise');

	 
	 
	// Main buttons
	 echo '<div class="actions">';
	 if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
     
    //$lp_id = Security::remove_XSS($_GET['lp_id']);
    // The lp_id parameter will be added by javascript
        $return = "";
        $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang("Author"), array('class' => 'toolactionplaceholdericon toolactionauthor')).get_lang("Author") . '</a>';
        $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step">' . Display::return_icon('pixel.gif', get_lang("Content"), array('class' => 'toolactionplaceholdericon toolactionauthorcontent')).get_lang("Content") . '</a>';
        echo $return;
    }
    echo '<a href="admin.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '&examId='.$examId . '&origin=' . $origin . '">' . Display::return_icon('pixel.gif', get_lang('QuizMaker'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('QuizMaker') . '</a>';
    echo '</div>';
 
    ?>
<div id="content">
    
<style type="text/css">
.quiztpl_actions {
    background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #CCCCCC;
    border-radius: 5px;
    min-height: 505px;
    margin-bottom: 5px;
    margin-left: 17px;
    overflow: hidden;
    padding: 10px;
    vertical-align: middle;
}

.quiztpl_actions span {
    font-weight:bolder;
}

</style>

<?php

$TBL_MAIN_QUESTIONS = Database::get_main_table(TABLE_MAIN_QUIZ_QUESTION_TEMPLATES);
$question_id = array();

$sql = "SELECT id FROM $TBL_MAIN_QUESTIONS ORDER BY id";
$rs = Database::query($sql,__FILE__,__LINE__);
while($row = Database::fetch_array($rs)){
	$question_id[] = $row['id'];
}

echo '<table>
    <tr>
    
<td>

<div class="quiztpl_actions">
<table><span style="display:block; text-align:center; margin-bottom:5px;line-height: 28px;">'.get_lang('UniqueAnswer').'</span>
    <tr>
        <td style="padding-right:10px;"><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[0].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Truefalse'), array('class' => 'quiztemplateplaceholdericon quiztpl_01true_false')).'</a></td>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[5].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoiceimage'), array('class' => 'quiztemplateplaceholdericon quiztpl_06mc_image')).'</td>
    </tr>
    <tr>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[1].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoice'), array('class' => 'quiztemplateplaceholdericon quiztpl_02multiple_choice')).'</a></td>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[6].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoicesound'), array('class' => 'quiztemplateplaceholdericon quiztpl_07mc_audio')).'</a></td>
    </tr>
    <tr>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[2].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoicesequence'), array('class' => 'quiztemplateplaceholdericon quiztpl_03multiple_choice_sequence')).'</a></td>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[7].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoicescreencast'), array('class' => 'quiztemplateplaceholdericon quiztpl_08mc_screencast')).'</a></td>
    </tr>
    <tr>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[3].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Justifiedmultiplechoice'), array('class' => 'quiztemplateplaceholdericon quiztpl_04true_false_justified')).'</a></td>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[8].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoiceflash'), array('class' => 'quiztemplateplaceholdericon quiztpl_09mc_flash')).'</a></td>
    </tr>
    <tr>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[4].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Noneoftheabove'), array('class' => 'quiztemplateplaceholdericon quiztpl_05none_of_the_above')).'</a></td>
        <td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[9].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoicevideo'), array('class' => 'quiztemplateplaceholdericon quiztpl_10mc_video')).'</a></td>
    </tr>
</table></div><div class="clear"></div>
</td>
 
<td valign="top"><div class="quiztpl_actions"><table><span style="display:block; text-align:center; margin-bottom:5px;width:93px;">'.get_lang('MultipleAnswer').' / '.get_lang('Reasoning').'</span><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[10].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multipleinclusion'), array('class' => 'quiztemplateplaceholdericon quiztpl_11ma_identify')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[11].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multipleexclusion'), array('class' => 'quiztemplateplaceholdericon quiztpl_12ma_remove')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[12].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multipleanswerimage'), array('class' => 'quiztemplateplaceholdericon quiztpl_13ma_identify_image')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[13].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Allitemsneeded'), array('class' => 'quiztemplateplaceholdericon quiztpl_14reasoning')).'</a></td></tr><tr><td class="quiz_tpl_table">&nbsp;</td></tr></table></div><div class="clear"></div></td>
<td valign="top"><div class="quiztpl_actions"><table><span style="display:block; text-align:center; margin-bottom:5px;line-height: 28px;">'.get_lang('FillBlanks').'</span><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[14].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=3>"'.Display::return_icon('pixel.gif', get_lang('Fillinaword'), array('class' => 'quiztemplateplaceholdericon quiztpl_15fill_blank_text')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[15].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=3">'.Display::return_icon('pixel.gif', get_lang('Calculatedanswer'), array('class' => 'quiztemplateplaceholdericon quiztpl_16fill_math')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[16].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=3">'.Display::return_icon('pixel.gif', get_lang('Itemtable'), array('class' => 'quiztemplateplaceholdericon quiztpl_17fill_table')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[17].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=3">'.Display::return_icon('pixel.gif', get_lang('Listeningcomprehension'), array('class' => 'quiztemplateplaceholdericon quiztpl_18listening_comprehension')).'</a></td></tr><tr><td style="height:81px;"><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[18].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=3">'.Display::return_icon('pixel.gif', get_lang('Crosswords'), array('class' => 'quiztemplateplaceholdericon quiztpl_crosswords')).'</a></td><tr><td class="quiz_tpl_table">&nbsp;</td></tr></tr></table></div><div class="clear"></div></td>
<td valign="top"><div class="quiztpl_actions"><table><span style="display:block; text-align:center; margin-bottom:5px;line-height: 28px;">'.get_lang('FreeAnswer').'</span><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[19].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Openquestion'), array('class' => 'quiztemplateplaceholdericon quiztpl_19open_question')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[20].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Multiplechoicejustified'), array('class' => 'quiztemplateplaceholdericon quiztpl_20bopen_justify_mc')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[21].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'">'.Display::return_icon('pixel.gif', get_lang('Commentmap'), array('class' => 'quiztemplateplaceholdericon quiztpl_20open_map')).'</a></td></tr><tr><td class="quiz_tpl_table">&nbsp;</td></tr><tr><td class="quiz_tpl_table">&nbsp;</td></tr></table></div><div class="clear"></div></td>
<td valign="top"><div class="quiztpl_actions"><table><span style="display:block; text-align:center; margin-bottom:5px;line-height: 28px;">'.get_lang('Matching').'</span><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[22].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=4">'.Display::return_icon('pixel.gif', get_lang('Wordsmatching'), array('class' => 'quiztemplateplaceholdericon quiztpl_21matching')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[23].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=4">'.Display::return_icon('pixel.gif', get_lang('Makerightsequence'), array('class' => 'quiztemplateplaceholdericon quiztpl_22ordering')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[24].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=4">'.Display::return_icon('pixel.gif', get_lang('Logicevidence'), array('class' => 'quiztemplateplaceholdericon quiztpl_23bmatch_assemble_proof')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[25].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=4">'.Display::return_icon('pixel.gif', get_lang('Imagesmatching'), array('class' => 'quiztemplateplaceholdericon quiztpl_23match_image')).'</a></td></tr><tr><td class="quiz_tpl_table">&nbsp;</td></tr></table></div><div class="clear"></div></td>
<td valign="top"><div class="quiztpl_actions"><table><span style="display:block; text-align:center; margin-bottom:5px;line-height: 28px;">'.get_lang('HotSpot').'</span><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[26].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=6">'.Display::return_icon('pixel.gif', get_lang('Imagezone'), array('class' => 'quiztemplateplaceholdericon quiztpl_24hotspots')).'</a></td></tr>';
echo '<tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[27].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=6">'.Display::return_icon('pixel.gif', get_lang('Sequencediagram'), array('class' => 'quiztemplateplaceholdericon quiztpl_25hotspots_organigram')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[28].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=6">'.Display::return_icon('pixel.gif', get_lang('Sequencescreenshot'), array('class' => 'quiztemplateplaceholdericon quiztpl_26hotspots_screen')).'</a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion='.$question_id[29].'&fromExercise='.$fromExercise.$add_params_for_lp. '&examId='.$examId . '&origin=' . $origin .'&answerType=6">'.Display::return_icon('pixel.gif', get_lang('Datatable'), array('class' => 'quiztemplateplaceholdericon quiztpl_27hotspots_table')).'</a></td></tr>';
echo '<tr><td class="quiz_tpl_table">&nbsp;</td></tr></table></div></td>
</tr></table>';
	
         } else {
          // if not admin of course
          api_not_allowed(true);
         }
?>
 </div>
<?php
if (api_is_allowed_to_edit ()) {
    $organize_lang_var = api_convert_encoding(get_lang('Organize'), $charset, api_get_system_encoding());	  
}
Display::display_footer();
