<?php //$id:$
/* For licensing terms, see /dokeos_license.txt */
//error_log(__FILE__);

/**
*	File containing the HotSpot class.
*	@package dokeos.exercise
* 	@author Eric Marguin
* 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
*/


if(!class_exists('HotSpot')):

/**
	CLASS HotSpot
 *
 *	This class allows to instantiate an object of type HotSpot (MULTIPLE CHOICE, UNIQUE ANSWER),
 *	extending the class question
 *
 *	@author Eric Marguin
 *	@package dokeos.exercise
 **/

class HotSpot extends Question {

	static $typePicture = 'hotspot.gif';
	static $explanationLangVar = 'Hotspot';


	function HotSpot(){
		parent::question();
		$this -> type = HOT_SPOT;
	}

	function display(){

	}
	
	function createForm ($form) {
		parent::createForm ($form);
		global $text, $class;
		if(!isset($_GET['editQuestion'])) {
			$renderer = $form->defaultRenderer();
   $form->addElement('html','<div class="hotspot-form">');
   $form->addElement('html','<div class="upload-form" style="float:left">');
			$form->addElement('html', '<div class="row"><div class="label"></div><div class="formw">'.get_lang('UploadJpgPicture').'</div></div>');
			//$form->addElement('file','imageUpload','<span class="form_required">*</span><img src="../img/hotspots.png" />');
			$form->addElement('file','imageUpload');
			// setting the save button here and not in the question class.php
   $form->addElement('html','</div>');
   $form->addElement('html','<div class="button-form" style="float:right;margin-top:14px;">');
			// Saving a question
			$form->addElement('style_submit_button','submitQuestion',get_lang('Upload'), 'class="'.$class.'" style="float:right"');
			$renderer->setElementTemplate('<div class="row"><div class="label" style="margin-top:-30px;">{label}</div><div class="formw" >{element}</div></div>','imageUpload');
			$form->addRule('imageUpload', get_lang('OnlyImagesAllowed'), 'filetype', array ('jpg', 'jpeg', 'png', 'gif'));
			$form->addRule('imageUpload', get_lang('NoImage'), 'uploadedfile');
   $form->addElement('html','</div></div>');
		} else {
			// setting the save button here and not in the question class.php
   $form->addElement('html','<div class="button-form" style="float:right;margin-top:14px;">');
			// Editing a question
			$form->addElement('style_submit_button','submitQuestion',get_lang('Upload'), 'class="'.$class.'" style="float:right"');
   $form->addElement('html','</div>');
		}
		$form -> addElement('hidden', 'submitform');
		$form->addElement('hidden', 'questiontype','7');
  //$form->addElement('html','</div>');

  // Hotspot Screen
  
	}

	function processCreation ($form, $objExercise) {
		$file_info = $form -> getSubmitValue('imageUpload');
		parent::processCreation ($form, $objExercise);
		if(!empty($file_info['tmp_name']))
		{
			$this->uploadPicture($file_info['tmp_name'], $file_info['name']);
			//list($width,$height) = @getimagesize($file_info['tmp_name']);
			list($width,$height) = api_getimagesize($file_info['tmp_name']);
			if($width>=$height) {
				$this->resizePicture('width',600);
			} else {
				$this->resizePicture('height',350);
			}
			$this->save();
		}
	}

	function createAnswersForm ($form) {

    	// nothing

	}

	function processAnswersCreation ($form) {

		// nothing

	}
	
		
	/**
	 * Display the question in tracking mode (use templates in tracking/questions_templates)
	 * @param $nbAttemptsInExercise the number of users who answered the quiz
	 */
	function displayTracking($exerciseId, $nbAttemptsInExercise){
		
		if(!class_exists('Answer'))
			require_once(api_get_path(SYS_CODE_PATH).'exercice/answer.class.php');
			
		$stats = $this->getAverageStats($exerciseId, $nbAttemptsInExercise);
		include(api_get_path(SYS_CODE_PATH).'exercice/tracking/questions_templates/hotspot.page');
		
	}
	
	/**
	 * Returns learners choices for each question in percents
	 * @param $nbAttemptsInExercise the number of users who answered the quiz
	 * @return array the percents
	 */
	function getAverageStats($exerciseId, $nbAttemptsInExercise){
		
		$preparedSql = 'SELECT COUNT(1) as nbCorrectAttempts
						FROM '.Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT).' as attempts
						INNER JOIN '.Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES).' as exercises
							ON exercises.exe_id = attempts.exe_id
						WHERE course_code = "%s"
						AND exercises.exe_exo_id = %d
						AND attempts.question_id = %d
						AND marks = %d
						GROUP BY answer';
		$sql = sprintf($preparedSql, api_get_course_id(), $exerciseId, $this->id, $this->weighting);
		$rs = Database::query($sql, __FILE__, __LINE__);
		
		$stats['correct'] = array();
		$stats['correct']['total'] = intval(@mysql_result($rs, 0 ,'nbCorrectAttempts'));
		$stats['correct']['average'] = $stats['correct']['total'] / $nbAttemptsInExercise * 100;
		
		$stats['wrong'] = array();
		$stats['wrong']['total'] = $nbAttemptsInExercise - $stats['correct']['total'];
		$stats['wrong']['average'] = 100 - $stats['correct']['average'];
		
		
		return $stats;
		
	}
        
        public function getHtmlQuestionResult($objQuestion, $attemptId, &$totalScore, &$totalWeighting, $dbName = '') {
            
            $feedback_if_true = $feedback_if_false = '';
            $questionId = $objQuestion->selectId();
            $objAnswerTmp = new Answer($questionId, $dbName);
            $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
            $questionScore = 0;
            $correctComment = array();
            $answerOk = 'N';
            $answerWrong = 'N';
            $totalScoreHot = 0;
            $tbl_track_hotspot = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
            $tbl_track_attempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
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

            $s .= '<table width="100%" border="0"><tr><td><div align="center"><object type="application/x-shockwave-flash" data="'.api_get_path(WEB_CODE_PATH).'plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $attemptId . '&from_db=1" width="610" height="410">
                        <param name="movie" value="'.api_get_path(WEB_CODE_PATH).'plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $attemptId . '&from_db=1" />
                    </object></div></td><td width="40%" valign="top"><div class="quiz_content_actions quit_border" style="height:400px;overflow:auto;"><div class="quiz_header">' . get_lang('Feedback') . '</div><div align="center"><img src="'.api_get_path(WEB_IMG_PATH).'MouseHotspots64.png"></div><br/>';

            $s .= '<div><table width="90%" border="1" class="data_table">';
            for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                $answer = $objAnswerTmp->selectAnswer($answerId);
                $answerComment = $objAnswerTmp->selectComment($answerId);
                $correctComment[] = $objAnswerTmp->selectComment($answerId);
                $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                if ($nbrAnswers == 1) {
                    $correctComment = explode("~", $objAnswerTmp->selectComment($answerId));
                } else {
                    if ($answerId == 1) {
                        $correctComment[] = $objAnswerTmp->selectComment(1);
                        $correctComment[] = $objAnswerTmp->selectComment(2);
                    } else {
                        $correctComment[] = $objAnswerTmp->selectComment($answerId);
                    }
                }
                
                $query = "select hotspot_correct from " . $tbl_track_hotspot . " where hotspot_exe_id = '" . intval($attemptId) . "' and hotspot_question_id= '" . intval($questionId) . "' AND hotspot_answer_id='" . intval($answerId) . "'";
                $resq = Database::query($query);
                $choice = Database::result($resq, 0, "hotspot_correct");

                $queryfree = "select marks from " . $tbl_track_attempt . " where exe_id = '" . intval($attemptId) . "' and question_id= '" . intval($questionId) . "'";
                $resfree = api_sql_query($queryfree, __FILE__, __LINE__);
                $questionScore = Database::result($resfree, 0, "marks");

                if ($choice) {
                    $answerOk = 'Y';
                    $img_choice = get_lang('Right');
                } else {
                    $answerOk = 'N';
                    $answerWrong = 'Y';
                    $img_choice = get_lang('Wrong');
                }
                $s .= '<tr><td><div style="height:11px; width:11px; background-color:' . $hotspot_colors[$answerId] . '; display:inline; float:left; margin-top:3px;"></div>&nbsp;' . $answerId . '</td><td>' . $answer . '</td><td>' . $img_choice . '</td></tr>';
            }
            $s .= '</table></div><br/><br/>';
            $s .= '</div></td></tr></table>';            
            

            // feedback
            $s .= '<table style="clear:both;width:100%;margin:15px 0;">';
            if ($answerOk == 'Y' && $answerWrong == 'N') {
                /*
                if (empty($feedbackIfTrue)) {
                    $feedbackIfTrue = get_lang('NoTrainerComment');
                }
                $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-right">' . $feedbackIfTrue . '</div></td></tr>';
            } else {
                if (empty($feedbackIfFalse)) {
                    $feedbackIfFalse = get_lang('NoTrainerComment');
                }
                $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-wrong">' . $feedbackIfFalse . '</div></td></tr>';
                */
                
                if ($nbrAnswers == 1) {
                    $feedbackIfTrue = $correctComment[0];
                } else {
                    $feedbackIfTrue = $correctComment[1];
                }
                $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-right">' . $feedbackIfTrue . '</div></td></tr>';
            } else {
                if ($nbrAnswers == 1) {
                    $feedbackIfFalse = $correctComment[1];
                } else {
                    $feedbackIfFalse = $correctComment[2];
                }
                $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-wrong">' . $feedbackIfFalse . '</div></td></tr>';
            }
            $s .= '</table>';
            
            
            // score
            $totalScore+=$questionScore;
            $questionWeighting = $objQuestion->selectWeighting();
            $totalWeighting += $questionWeighting;
            
            if ($questionWeighting - $questionScore < 0.50) {
                $myTotalScore = round(float_format($questionScore, 1));
            } else {
                $myTotalScore = float_format($questionScore, 1);
            }
            $myTotalWeight = float_format($questionWeighting, 1);
            if ($myTotalScore < 0) { $myTotalScore = 0; } 
            
            $s .= '<div class="span2 quesion-answers-score"><b>'.get_lang('Score').' : </b> '.$myTotalScore. ' / '.$myTotalWeight.'</div>';
            
            return $s;
            
        }
        
        public function getHtmlQuestionAnswer($objQuestion, $objExercise, $readonly = false, $examId = 0) {
            $exerciseId = $objExercise->selectId();
            $questionId = $objQuestion->selectId();
            $objAnswer = new Answer($objQuestion->selectId());
            $nbrAnswers = $objAnswer->selectNbrAnswers();            
            $media_w = 'width:95%'; //in_array($mediaPosition, array('top', 'nomedia'))?'width:100%':'width:50%;float:left';
            $questionDescription = api_parse_tex($objQuestion->selectDescription());
            $exeId   = $objExercise->getLastUserAttemptId($exerciseId, 'incomplete', null, null, null, $examId);
            $attempted = $objExercise->alreadyAttempted($exerciseId, $questionId, 'incomplete', null, null, null, $examId);            
            $answer_list = '<input type="hidden" name="choice[' . $questionId . '][1]" value="0" />
                             <div>
                                <b>' . get_lang('HotspotZones') . '</b>
                                <dl>';
                                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                                    $answer_list .= '<dt>' . $answerId . '.- ' . $objAnswer->selectAnswer($answerId) . '</dt><br />';
                                }
            $answer_list .= '   </dl>
                            </div>';
            
            $canClick = isset($_GET['editQuestion']) ? '0' : (isset($_GET['modifyAnswers']) ? '0' : '1');
            $swfPath = api_get_path(WEB_CODE_PATH)."plugin/hotspot/hotspot_user.swf?modifyAnswers=".$questionId."&canClick:$canClick";
            //if (!empty($exeId)) {
            if ($attempted) {
                $swfPath = api_get_path(WEB_CODE_PATH)."plugin/hotspot/hotspot_solution.swf?modifyAnswers=".$questionId."&exe_id=".$exeId."&from_db=1";
            }                       
            $s = '';  
            $s  .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">'; 
            $s  .=" <table class='exercise_questions'>
                        <tr><td valign='top' colspan='2'>".$questionDescription."</td></tr>";                                   
            $s  .= "    <script type='text/javascript' src=\"".api_get_path(WEB_CODE_PATH)."plugin/hotspot/JavaScriptFlashGateway.js\"></script>
                        <script src=\"".api_get_path(WEB_CODE_PATH)."plugin/hotspot/hotspot.js\" type=\"text/javascript\"></script>
                        <script language=\"JavaScript\" type=\"text/javascript\">
                            var requiredMajorVersion = 7;
                            var requiredMinorVersion = 0;
                            var requiredRevision = 0;
                            var jsVersion = 1.0;
                        </script>
                        <script language=\"VBScript\" type=\"text/vbscript\">
                            <!-- // Visual basic helper required to detect Flash Player ActiveX control version information
                                Function VBGetSwfVer(i)
                                  on error resume next
                                  Dim swControl, swVersion
                                  swVersion = 0
                                  set swControl = CreateObject(\"ShockwaveFlash.ShockwaveFlash.\" + CStr(i))
                                  if (IsObject(swControl)) then
                                    swVersion = swControl.GetVariable(\"\$version\")
                                  end if
                                  VBGetSwfVer = swVersion
                                End Function
                            // -->
                        </script>
                        <script language=\"JavaScript1.1\" type=\"text/javascript\">
                        <!-- // Detect Client Browser type
                        var isIE  = (navigator.appVersion.indexOf(\"MSIE\") != -1) ? true : false;
                        var isWin = (navigator.appVersion.toLowerCase().indexOf(\"win\") != -1) ? true : false;
                        var isOpera = (navigator.userAgent.indexOf(\"Opera\") != -1) ? true : false;
                        jsVersion = 1.1;
                        // JavaScript helper required to detect Flash Player PlugIn version information
                        function JSGetSwfVer(i) {
                            // NS/Opera version >= 3 check for Flash plugin in plugin array
                            if (navigator.plugins != null && navigator.plugins.length > 0) {
                                if (navigator.plugins[\"Shockwave Flash 2.0\"] || navigator.plugins[\"Shockwave Flash\"]) {
                                    var swVer2 = navigator.plugins[\"Shockwave Flash 2.0\"] ? \" 2.0\" : \"\";
                                    var flashDescription = navigator.plugins[\"Shockwave Flash\" + swVer2].description;
                                    descArray = flashDescription.split(\" \");
                                    tempArrayMajor = descArray[2].split(\".\");
                                    versionMajor = tempArrayMajor[0];
                                    versionMinor = tempArrayMajor[1];
                                    if (descArray[3] != \"\") {
                                        tempArrayMinor = descArray[3].split(\"r\");
                                    } else {
                                        tempArrayMinor = descArray[4].split(\"r\");
                                    }
                                    versionRevision = tempArrayMinor[1] > 0 ? tempArrayMinor[1] : 0;
                                    flashVer = versionMajor + \".\" + versionMinor + \".\" + versionRevision;
                                } else {
                                    flashVer = -1;
                                }
                            }
                            // MSN/WebTV 2.6 supports Flash 4
                            else if (navigator.userAgent.toLowerCase().indexOf(\"webtv/2.6\") != -1) flashVer = 4;
                            // WebTV 2.5 supports Flash 3
                            else if (navigator.userAgent.toLowerCase().indexOf(\"webtv/2.5\") != -1) flashVer = 3;
                            // older WebTV supports Flash 2
                            else if (navigator.userAgent.toLowerCase().indexOf(\"webtv\") != -1) flashVer = 2;
                            // Can't detect in all other cases
                            else {
                                flashVer = -1;
                            }
                            return flashVer;
                        }
                        // When called with reqMajorVer, reqMinorVer, reqRevision returns true if that version or greater is available
                        function DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision) {
                            reqVer = parseFloat(reqMajorVer + \".\" + reqRevision);
                            // loop backwards through the versions until we find the newest version
                            for (i=25;i>0;i--) {
                                if (isIE && isWin && !isOpera) { versionStr = VBGetSwfVer(i); } 
                                else { versionStr = JSGetSwfVer(i); }
                                if (versionStr == -1 ) {
                                    return false;
                                } else if (versionStr != 0) {
                                    if(isIE && isWin && !isOpera) {
                                        tempArray         = versionStr.split(\" \");
                                        tempString        = tempArray[1];
                                        versionArray      = tempString .split(\",\");
                                    } else {
                                        versionArray      = versionStr.split(\".\");
                                    }
                                    versionMajor      = versionArray[0];
                                    versionMinor      = versionArray[1];
                                    versionRevision   = versionArray[2];
                                    versionString     = versionMajor + \".\" + versionRevision;   // 7.0r24 == 7.24
                                    versionNum        = parseFloat(versionString);
                                    // is the major.revision >= requested major.revision AND the minor version >= requested minor
                                    if ((versionMajor > reqMajorVer) && (versionNum >= reqVer)) {
                                        return true;
                                    } else {
                                        return ((versionNum >= reqVer && versionMinor >= reqMinorVer) ? true : false );
                                    }
                                }
                            }
                        }
                        // -->
                        </script>";
        $s .= '     <tr>
                        <td valign="top" colspan="2" width="600">
                        <table>
                            <tr>
                                <td width="610">'."
                                    <script language=\"JavaScript\" type=\"text/javascript\">
                                        <!--
                                        // Version check based upon the values entered above in \"Globals\"
                                        var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
                                        // Check to see if the version meets the requirements for playback
                                        if (hasReqestedVersion) {  // if we've detected an acceptable version
                                            var oeTags = '<object type=\"application/x-shockwave-flash\" data=\"".$swfPath."\" width=\"610\" height=\"485\">'
                                                                        + '<param name=\"movie\" value=\"".$swfPath."\" \/>'
                                                                        + '<param name=\"wmode\" value=\"transparent\"/>'
                                                                        + '<\/object>';
                                            document.write(oeTags);   // embed the Flash Content SWF when all tests are passed
                                        } else {  // flash is too old or we can't detect the plugin
                                            var alternateContent = 'Error<br \/>'
                                                    + 'Hotspots requires Macromedia Flash 7.<br \/>'
                                                    + '<a href=http://www.macromedia.com/go/getflash/>Get Flash<\/a>';
                                            document.write(alternateContent);  // insert non-flash content
                                        }
                                        // -->
                                    </script>
                                </td>";
            $s .= "             <td valign='top' align='left'>
                                    <div class='hotspot_answers_frame'  style=\"border:none\">
                                        <div style='height:280px;overflow:auto;'>$answer_list</div>
                                    <div>
                                    <div><img src='".api_get_path(WEB_CODE_PATH)."img/MouseHotspots.png'></div>
                                </td>
                            </tr>
                        </table>";        
        $s .= "         </td>
                    </tr></table>";
        $s .= '</div>';
            $s .= '<div class="clear"></div>';  
            return $s;
          }
	

}

endif;
?>
