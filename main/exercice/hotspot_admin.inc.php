<?php
//$id:$
/* For licensing terms, see /dokeos_license.txt */
//error_log(__FILE__);
/**
 * 	This script allows to manage answers. It is included from the script admin.php
 * 	@package dokeos.exercise
 * 	@author Toon Keppens
 * 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */
require_once(api_get_path(LIBRARY_PATH) . 'text.lib.php');

// ALLOWED_TO_INCLUDE is defined in admin.php
if (!defined('ALLOWED_TO_INCLUDE')) {
 exit();
}

$modifyAnswers = (int) $_GET['hotspotadmin'];

if (!is_object($objQuestion)) {
 $objQuestion = Question :: read($modifyAnswers);
}

$questionName = $objQuestion->selectTitle();
$answerType = $objQuestion->selectType();
$pictureName = $objQuestion->selectPicture();

$debug = 0; // debug variable to get where we are

$okPicture = empty($pictureName) ? false : true;

// if we come from the warning box "this question is used in serveral exercises"
if ($modifyIn) {
 if ($debug > 0) {
  echo '$modifyIn was set' . "<br />\n";
 }
 // if the user has chosed to modify the question only in the current exercise
 if ($modifyIn == 'thisExercise') {
  // duplicates the question
  $questionId = $objQuestion->duplicate();

  // deletes the old question
  $objQuestion->delete($exerciseId);

  // removes the old question ID from the question list of the Exercise object
  $objExercise->removeFromList($modifyAnswers);

  // adds the new question ID into the question list of the Exercise object
  $objExercise->addToList($questionId);

  // construction of the duplicated Question
  $objQuestion = Question :: read($questionId);

  // adds the exercise ID into the exercise list of the Question object
  $objQuestion->addToList($exerciseId);

  // copies answers from $modifyAnswers to $questionId
  $objAnswer->duplicate($questionId);

  // construction of the duplicated Answers

  $objAnswer = new Answer($questionId);
 }


 $color = unserialize($color);
 $reponse = unserialize($reponse);
 $comment = unserialize($comment);
 $weighting = unserialize($weighting);
 $hotspot_coordinates = unserialize($hotspot_coordinates);
 $hotspot_type = unserialize($hotspot_type);
 unset($buttonBack);
}

// the answer form has been submitted
if ($submitAnswers || $buttonBack) {
 if ($debug > 0) {
  echo '$submitAnswers or $buttonBack was set' . "<br />\n";
 }

 $questionWeighting = $nbrGoodAnswers = 0;

 for ($i = 1; $i <= $nbrAnswers; $i++) {
  if ($debug > 0) {
   echo str_repeat('&nbsp;', 4) . '$answerType is HOT_SPOT' . "<br />\n";
  }

  $reponse[$i] = trim($reponse[$i]);
  $comment[$i] = trim($comment[$i]);
  $weighting[$i] = $weighting[$i]; // it can be float
  // checks if field is empty
  if (empty($reponse[$i]) && $reponse[$i] != '0') {
   if($i == 1){
   $msgErr = get_lang('HotspotGiveAnswers');}

   // clears answers already recorded into the Answer object
   $objAnswer->cancel();
   break;
  }

  if ($weighting[$i] <= 0) {
   if($i == 1){
   $msgErr = get_lang('HotspotWeightingError');}
   // clears answers already recorded into the Answer object
   $objAnswer->cancel();
   break;
  }

  if ($hotspot_coordinates[$i] == '0;0|0|0' || empty($hotspot_coordinates[$i])) {
   if($i == 1){
   $msgErr = get_lang('HotspotNotDrawn');}
   // clears answers already recorded into the Answer object
   $objAnswer->cancel();
   break;
  }
 }  // end for()
 // Save and redirect to the question list
 if (empty($msgErr)) {
  //echo 'No error messages';
  for ($i = 1; $i <= $nbrAnswers; $i++) {
   if ($debug > 0) {
    echo str_repeat('&nbsp;', 4) . '$answerType is HOT_SPOT' . "<br />\n";
   }
	if($nbrAnswers == 1){
	 $comment[$i] = $comment[1].'~'.$comment[2];
   }
   $reponse[$i] = trim($reponse[$i]);
   $comment[$i] = trim($comment[$i]);
   $weighting[$i] = ($weighting[$i]); //it can be float
   if(!empty($reponse[$i]))
	  {
		   if ($weighting[$i]) {
			$questionWeighting+=$weighting[$i];
		   }
		   // creates answer
		   $objAnswer->createAnswer($reponse[$i], '', $comment[$i], $weighting[$i], $i, $hotspot_coordinates[$i], $hotspot_type[$i]);
	  }
  }  // end for()
  // saves the answers into the data base
  $objAnswer->save();

  // sets the total weighting of the question
  $objQuestion->updateWeighting($questionWeighting);
  $objQuestion->save($exerciseId);

  $editQuestion = $questionId;
  unset($modifyAnswers);
  if (!isset($_SESSION['fromlp'])) {

    echo '<script type="text/javascript">parent.location.href="admin.php?popup=1&exerciseId='.$exerciseId.'"</script>';
  } else {
   echo '<script type="text/javascript">window.location.href="admin.php?fromlp=Y&exerciseId='.$exerciseId.'"</script>';
  }
 }
 if ($debug > 0) {
  echo '$modifyIn was set - end' . "<br />\n";
 }
}

if ($modifyAnswers) {


 if ($debug > 0) {
  echo str_repeat('&nbsp;', 0) . '$modifyAnswers is set' . "<br />\n";
 }

 // construction of the Answer object
 $objAnswer = new Answer($objQuestion->id);

 api_session_register('objAnswer');

 if ($debug > 0) {
  echo str_repeat('&nbsp;', 2) . '$answerType is HOT_SPOT' . "<br />\n";
 }

 $TBL_ANSWERS = Database::get_course_table(TABLE_QUIZ_ANSWER);

 if (!$nbrAnswers) {
 //if (count($objAnswer->answer)  >= 1 ) {
  // Number answers
  //$nbrAnswers = count($objAnswer->answer);
  $nbrAnswers = 1;

  $reponse = Array();
  $comment = Array();
  $weighting = Array();
  $hotspot_coordinates = Array();
  $hotspot_type = array();

  for ($i = 1; $i <= $nbrAnswers; $i++) {
   $reponse[$i] = $objAnswer->selectAnswer($i);
   $comment[$i] = $objAnswer->selectComment($i);
   $weighting[$i] = $objAnswer->selectWeighting($i);
   $hotspot_coordinates[$i] = $objAnswer->selectHotspotCoordinates($i);
   $hotspot_type[$i] = $objAnswer->selectHotspotType($i);
   if($nbrAnswers == 1)
   {
	   list($comment1,$comment2) = explode("~",$comment[$i]);
	   $comment[1] = $comment1;
	   $comment[2] = $comment2;
   }
  }
 }

 $_SESSION['tmp_answers'] = array();
 $_SESSION['tmp_answers']['answer'] = $reponse;
 $_SESSION['tmp_answers']['comment'] = $comment;
 $_SESSION['tmp_answers']['weighting'] = $weighting;
 $_SESSION['tmp_answers']['hotspot_coordinates'] = $hotspot_coordinates;
 $_SESSION['tmp_answers']['hotspot_type'] = $hotspot_type;

 if ($lessAnswers) {
  // At least 1 answer
  if ($nbrAnswers > 1) {
   $nbrAnswers--;
   // Remove the last answer
   $tmp = array_pop($_SESSION['tmp_answers']['answer']);
   $tmp = array_pop($_SESSION['tmp_answers']['comment']);
   $tmp = array_pop($_SESSION['tmp_answers']['weighting']);
   $tmp = array_pop($_SESSION['tmp_answers']['hotspot_coordinates']);
   $tmp = array_pop($_SESSION['tmp_answers']['hotspot_type']);
  } else {
   $msgErr = get_lang('MinHotspot');
  }
 }

 if ($moreAnswers) {
  if ($nbrAnswers < 12) {
   $nbrAnswers++;
   // Add a new answer
   $_SESSION['tmp_answers']['answer'][] = '';
   $_SESSION['tmp_answers']['comment'][] = '';
   $_SESSION['tmp_answers']['weighting'][] = '1';
   $_SESSION['tmp_answers']['hotspot_coordinates'][] = '0;0|0|0';
   $_SESSION['tmp_answers']['hotspot_type'][] = 'square';
  } else {
   $msgErr = get_lang('MaxHotspot');
  }
 }

 if ($debug > 0) {
  echo str_repeat('&nbsp;', 2) . '$usedInSeveralExercises is untrue' . "<br />\n";
 }


 if ($debug > 0) {
  echo str_repeat('&nbsp;', 4) . '$answerType is HOT_SPOT' . "<br />\n";
 }
 $hotspot_colors = array("", // $i starts from 1 on next loop (ugly fix)
     "#4271B5",
     "#FE8E16",
     "#3B3B3B",
     "#BCD631",
     "#D63173",
     "#D7D7D7",
     "#90AFDD",
     "#AF8640",
     "#4F9242",
     "#F4EB24",
     "#ED2024",
     "#45C7F0",
     "#F7BDE2");
?>
 <div class="actions">
  <h2>
<?php echo get_lang('Question') . ": " . $questionName; ?>
  </h2>

<?php
 if (!empty($msgErr)) {
  //Display::display_normal_message($msgErr); //main API
     $_SESSION["display_normal_message"]=get_lang($msgErr);
 }
?>

 <form method="post" action="<?php echo api_get_self(); ?>?hotspotadmin=<?php echo $modifyAnswers . '&' . api_get_cidreq(); ?>" name="frm_exercise" id="frm_exercise">
  <table  border="0" cellpadding="0" cellspacing="2" width="100%">
   <tr>
    <td colspan="2" valign="top" >
 				<input type="hidden" name="formSent" value="1" />
 				<input type="hidden" name="nbrAnswers" value="<?php echo $nbrAnswers; ?>" /> 	  </td>
   </tr>
   <tr>


    <td colspan="2" valign="top"><table class="data_table">
      <tr>
       <th width="">&nbsp;</th>
       <th width="" ><?php echo get_lang('HotspotDescription'); ?>*</th>
       <th width=""><?php echo get_lang('QuestionWeighting'); ?>*</th>
      </tr>
<?php
     for ($i = 1; $i <= $nbrAnswers; $i++) {
      $class = ($i % 2 == 0) ? 'row_odd' : 'row_even';
?>
      <tr class="<?php echo $class; ?>">
       <td height="30" valign="top"><div style="height: 15px; width: 15px; background-color: <?php echo $hotspot_colors[$i]; ?>"> </div></td>
       <td height="30" align="left" valign="top"><input type="text" name="reponse[<?php echo $i; ?>]" value="<?php echo api_htmlentities($reponse[$i], ENT_QUOTES, api_get_system_encoding()); ?>" size="15" /></td>
<?php
      /*require_once(api_get_path(LIBRARY_PATH) . "/fckeditor/fckeditor.php");
        $oFCKeditor = new FCKeditor("comment[$i]") ;
        $content = $comment[$i];
        $oFCKeditor->ToolbarSet = 'TestProposedAnswer';
        $oFCKeditor->Config['ToolbarStartExpanded'] = 'false';
        $oFCKeditor->Width		= '100%';
        $oFCKeditor->Height		= '100';
        $oFCKeditor->Value		= $content;
        $return =	$oFCKeditor->CreateHtml(); */
      /* <td align="left"><textarea wrap="virtual" rows="1" cols="25" name="comment[<?php echo $i; ?>]" style="width: 100%"><?php echo api_htmlentities($comment[$i], ENT_QUOTES, api_get_system_encoding()); ?></textarea></td>*/
?>
       <!--<td align="left"><?php echo $return; ?></td>-->
       <td height="30" valign="top"><input type="text" name="weighting[<?php echo $i; ?>]" size="5" value="<?php echo (isset($weighting[$i]) ? float_format($weighting[$i], 1) : 10); ?>" />
        <input type="hidden" name="hotspot_coordinates[<?php echo $i; ?>]" value="<?php echo (empty($hotspot_coordinates[$i]) ? '0;0|0|0' : $hotspot_coordinates[$i]); ?>" />
        <input type="hidden" name="hotspot_type[<?php echo $i; ?>]" value="<?php echo (empty($hotspot_type[$i]) ? 'square' : $hotspot_type[$i]); ?>" /></td>
      </tr>
<?php
      }
?>
     </table></td>
	 <td colspan="2">
<?php
 $_SESSION['dbName'] = $_course['dbName'];
 $_SESSION['language'] = $_course['language'];
 $_SESSION['sysCode'] = $_course['sysCode'];
 $_SESSION['path'] = $_course['path'];
?>
     <script type="text/javascript">
      <!--
      // Version check based upon the values entered above in "Globals"
      var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);


      // Check to see if the version meets the requirements for playback
      if (hasReqestedVersion) {  // if we've detected an acceptable version
       var oeTags = '<object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_admin.swf?modifyAnswers=<?php echo $modifyAnswers; ?>" width="600" height="400">'
        + '<param name="movie" value="../plugin/hotspot/hotspot_admin.swf?modifyAnswers=<?php echo $modifyAnswers; ?>" />'
        + '<param name="test" value="OOoowww fo shooww" />'
        + '</object>';
       document.write(oeTags);   // embed the Flash Content SWF when all tests are passed
      } else {  // flash is too old or we can't detect the plugin
       var alternateContent = 'Error<br \/>'
        + 'This content requires the Macromedia Flash Player.<br \/>'
        + '<a href=http://www.macromedia.com/go/getflash/>Get Flash<\/a>';
       document.write(alternateContent);  // insert non-flash content
      }
      // -->
     </script>		</td>
   </tr>
   <tr>
    <td colspan="4" valign="top" style="text-align:right;">
<?php
      $navigator_info = api_get_navigator();
      //ie6 fix
      if ($navigator_info['name'] == 'Internet Explorer' && $navigator_info['version'] == '6') {
?>
    <!--     <input type="submit" class="minus" name="lessAnswers" style="margin:10px 5px;" value="<?php echo get_lang('LessHotspots'); ?>" />
         <input type="submit" class="plus" name="moreAnswers" style="margin:10px 5px;" value="<?php echo get_lang('MoreHotspots'); ?>" />-->
		 <input type="image" value="lessAnswers" src="../img/form-minus.png" name="lessAnswers" style="border:0px;background:transparent;">
		 <input type="image" value="moreAnswers" src="../img/form-plus.png"  name="moreAnswers" style="border:0px;background:transparent;">
<?php
        } else {
?>
     <!--    <button type="submit" class="minus" name="lessAnswers" style="margin:10px 5px;" value="<?php echo get_lang('LessHotspots'); ?>" ><?php echo get_lang('LessHotspots'); ?></button>
         <button type="submit" class="plus" name="moreAnswers" style="margin:10px 5px;" value="<?php echo get_lang('MoreHotspots'); ?>" /><?php echo get_lang('MoreHotspots'); ?></button>-->
		 <input type="image" value="lessAnswers" src="../img/form-minus.png" name="lessAnswers" style="border:0px;background:transparent;">
		 <input type="image" value="moreAnswers" src="../img/form-plus.png"  name="moreAnswers" style="border:0px;background:transparent;">
<?php
        }
?>
        </div></td>
   </tr>
   <tr>
    <td colspan="4" valign="top" >
    	<div class="coast2coast" style="text-aligne:left;">
      		<?php echo get_lang('FeedbackIfTrue'); ?><br/>
                <?php
                    $editor_config = array('ToolbarSet' => 'profile', 'Width' => '420', 'Height' => '120', 'FullPage' => false, 'InDocument' => true);
                    api_disp_html_area('comment[1]', $comment[1], '', '', '', $editor_config);
                ?>
      	</div>
    	<div class="coast2coast" style="text-aligne:left;">
      		<?php echo get_lang('FeedbackIfFalse'); ?><br/>
                <?php api_disp_html_area('comment[2]', $comment[2], '', '', '', $editor_config); ?>
     	</div>
     </td>
   </tr>
   <tr>
    <td colspan="2" valign="top" >&nbsp;</td>
    <td colspan="2" valign="top" >
     <div align="right">
<?php
         $navigator_info = api_get_navigator();
         //ie6 fix
         if ($navigator_info['name'] == 'Internet Explorer' && $navigator_info['version'] == '6') {
?>
           <!--><input type="submit" class="cancel" name="cancelAnswers" value="<?php echo get_lang('Cancel'); ?>" onclick="javascript:if(!confirm('<?php echo addslashes(api_htmlentities(get_lang('ConfirmYourChoice'))); ?>')) return false;" /><!-->
          <input type="submit" class="save" name="submitAnswers" value="<?php echo get_lang('Ok'); ?>" />
<?php
         } else {
?>
          <!--><button type="submit" class="cancel" name="cancelAnswers" value="<?php echo get_lang('Cancel'); ?>" onclick="javascript:if(!confirm('<?php echo addslashes(api_htmlentities(get_lang('ConfirmYourChoice'))); ?>')) return false;" ><?php echo get_lang('Cancel'); ?></button><!-->
          <button type="submit" class="save" name="submitAnswers" value="<?php echo get_lang('Ok'); ?>" ><?php echo get_lang('Validate'); ?></button>
<?php
         }
?>
        </div></td>
      </tr>
     </table>
    </form>
<?php
          if ($debug > 0) {
            echo str_repeat('&nbsp;', 0) . '$modifyAnswers was set - end' . "<br />\n";
          }
        }
?>
       </div>
<div class="actions">
  <a href="exercice.php?<?php echo api_get_cidreq(); ?>&show=result"><?php echo Display :: return_icon('reporting22.png', get_lang('Tracking')) . get_lang('Tracking') ?></a>
</div>