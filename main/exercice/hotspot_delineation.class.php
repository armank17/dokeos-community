<?php //$id:$
/* For licensing terms, see /dokeos_license.txt */
//error_log(__FILE__);

/**
*	File containing the HotSpot class.
*	@package dokeos.exercise
* 	@author Eric Marguin
* 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
*/


if(!class_exists('HotSpotDelineation')):

/**
	CLASS HotSpot
 *
 *	This class allows to instantiate an object of type HotSpot (MULTIPLE CHOICE, UNIQUE ANSWER),
 *	extending the class question
 *
 *	@author Eric Marguin
 *	@package dokeos.exercise
 **/

class HotSpotDelineation extends Question {

	static $typePicture = 'hotspot.gif';
	static $explanationLangVar = 'HotspotDelineation';


	function HotSpotDelineation(){
		parent::question();
		$this -> type = HOT_SPOT_DELINEATION;
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
        
        public function getHtmlQuestionResult($objQuestion, $attemptId, &$totalScore, &$totalWeighting, $dbName = '', $leanerId = null) {
            require_once api_get_path(LIBRARY_PATH) . 'geometry.lib.php';
            $feedback_if_true = $feedback_if_false = '';
            
            $TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $dbName);
            $TBL_TRACK_EXERCICES = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
            $TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION, $dbName);
            $TBL_TRACK_ATTEMPT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
            $TBL_TRACK_HOTSPOT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
            
            $user_restriction = " AND user_id=" . intval($leanerId)." ";
            $query = "SELECT attempts.question_id, answer  from " . $TBL_TRACK_ATTEMPT . " as attempts
						INNER JOIN " . $TBL_TRACK_EXERCICES . " as stats_exercices ON stats_exercices.exe_id=attempts.exe_id
						INNER JOIN " . $TBL_QUESTIONS . " as questions ON questions.id=attempts.question_id
                                                INNER JOIN " . $TBL_EXERCICE_QUESTION . " as rel_questions ON rel_questions.question_id = questions.id AND rel_questions.exercice_id = stats_exercices.exe_exo_id
                                                WHERE attempts.exe_id='" . intval($attemptId) . "' $user_restriction
                                                GROUP BY attempts.question_id
                                                ORDER BY rel_questions.question_order ASC";
            $result = Database::query($query);
            
            $questionList = array();
            $exerciseResult = array();
            $k = 0;
            $counter = 0;
            while ($row = Database::fetch_array($result)) {
                $questionList[] = $row['question_id'];
                $exerciseResult[] = $row['answer'];
            }
            
            $questionId = $objQuestion->selectId();
            $objAnswerTmp = new Answer($questionId, $dbName);
            $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
            $questionScore = 0;
            $totalScoreHotDel = 0;
            //based on exercise_submit modal
            /*  Hot spot delinetion parameters */
            $choice = $exerciseResult[$questionId];
            $destination = array();
            $comment = '';
            $next = 1;
            $_SESSION['hotspot_coord'] = array();
            $_SESSION['hotspot_dest'] = array();
            $overlap_color = $missing_color = $excess_color = false;
            $organs_at_risk_hit = 0;

                $final_answer = 0;

                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {

                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    $answerWeighting = $objAnswerTmp->selectWeighting($answerId);

                    //delineation
                    $answer_delineation_destination = $objAnswerTmp->selectDestination(1);
                    $delineation_cord = $objAnswerTmp->selectHotspotCoordinates(1);

                    if ($answerId === 1) {
                        $_SESSION['hotspot_coord'][1] = $delineation_cord;
                        $_SESSION['hotspot_dest'][1] = $answer_delineation_destination;
                    }

                    // getting the user answer                    
                    $query = "select hotspot_correct, hotspot_coordinate from " . $TBL_TRACK_HOTSPOT . " where hotspot_exe_id = '" . intval($attemptId) . "' and hotspot_question_id= '" . intval($questionId) . "' AND hotspot_answer_id='1'"; //by default we take 1 because it's a delineation
                    $resq = api_sql_query($query);
                    $row = Database::fetch_array($resq, 'ASSOC');
                    $choice = $row['hotspot_correct'];
                    $user_answer = $row['hotspot_coordinate'];

                    $queryfree = "select marks from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . intval($attemptId) . "' and question_id= '" . intval($questionId) . "'";
                    $resfree = api_sql_query($queryfree, __FILE__, __LINE__);
                    $questionScore = Database::result($resfree, 0, "marks");
                    $totalScoreHotDel = $questionScore;

                    // THIS is very important otherwise the poly_compile will throw an error!!
                    // round-up the coordinates
                    $coords = explode('/', $user_answer);
                    $user_array = '';
                    foreach ($coords as $coord) {
                        list($x, $y) = explode(';', $coord);
                        $user_array .= round($x) . ';' . round($y) . '/';
                    }
                    $user_array = substr($user_array, 0, -1);

                    if ($next) {
                        $user_answer = $user_array;

                        // we compare only the delineation not the other points
                        $answer_question = $_SESSION['hotspot_coord'][1];
                        $answerDestination = $_SESSION['hotspot_dest'][1];

                        //calculating the area
                        $poly_user = convert_coordinates($user_answer, '/');
                        $poly_answer = convert_coordinates($answer_question, '|');
                        $max_coord = array('x' => 600, 'y' => 400); //poly_get_max($poly_user,$poly_answer);
                        $poly_user_compiled = poly_compile($poly_user, $max_coord);
                        $poly_answer_compiled = poly_compile($poly_answer, $max_coord);
                        $poly_results = poly_result($poly_answer_compiled, $poly_user_compiled, $max_coord);

                        $overlap = $poly_results['both'];
                        $poly_answer_area = $poly_results['s1'];
                        $poly_user_area = $poly_results['s2'];
                        $missing = $poly_results['s1Only'];
                        $excess = $poly_results['s2Only'];

                        //$overlap = round(polygons_overlap($poly_answer,$poly_user)); //this is an area in pixels
                        if ($dbg_local > 0) {
                            error_log(__LINE__ . ' - Polygons results are ' . print_r($poly_results, 1), 0);
                        }
                        if ($overlap < 1) {
                            //shortcut to avoid complicated calculations
                            $final_overlap = 0;
                            $final_missing = 100;
                            $final_excess = 100;
                        } else {
                            // the final overlap is the percentage of the initial polygon that is overlapped by the user's polygon
                            $final_overlap = round(((float) $overlap / (float) $poly_answer_area) * 100);
                            if ($dbg_local > 1) {
                                error_log(__LINE__ . ' - Final overlap is ' . $final_overlap, 0);
                            }
                            // the final missing area is the percentage of the initial polygon that is not overlapped by the user's polygon
                            $final_missing = 100 - $final_overlap;
                            if ($dbg_local > 1) {
                                error_log(__LINE__ . ' - Final missing is ' . $final_missing, 0);
                            }
                            // the final excess area is the percentage of the initial polygon's size that is covered by the user's polygon outside of the initial polygon
                            $final_excess = round((((float) $poly_user_area - (float) $overlap) / (float) $poly_answer_area) * 100);
                            if ($dbg_local > 1) {
                                error_log(__LINE__ . ' - Final excess is ' . $final_excess, 0);
                            }
                        }

                        //checking the destination parameters parsing the "@@"
                        $destination_items = explode('@@', $answerDestination);
                        $threadhold_total = $destination_items[0];
                        $threadhold_items = explode(';', $threadhold_total);
                        $threadhold1 = $threadhold_items[0]; // overlap
                        $threadhold2 = $threadhold_items[1]; // excess
                        $threadhold3 = $threadhold_items[2];  //missing
                        // if is delineation
                        if ($answerId === 1) {
                            //setting colors
                            if ($final_overlap >= $threadhold1) {
                                $overlap_color = true; //echo 'a';
                            }
                            //echo $excess.'-'.$threadhold2;
                            if ($final_excess <= $threadhold2) {
                                $excess_color = true; //echo 'b';
                            }
                            //echo '--------'.$missing.'-'.$threadhold3;
                            if ($final_missing <= $threadhold3) {
                                $missing_color = true; //echo 'c';
                            }

                            // if pass
                            if ($final_overlap >= $threadhold1 && $final_missing <= $threadhold3 && $final_excess <= $threadhold2) {
                                $next = 1; //go to the oars
                                $result_comment = get_lang('Acceptable');
                                $final_answer = 1; // do not update with  update_exercise_attempt
                                //$comment = '<span style="font-weight: bold; color: #008000;">' . $answerDestination = $objAnswerTmp->selectComment(1) . '</span>';
                                $answer_ok = true;
                            } else {
                                $next = 1; //Go to the oars. If $next =  0 we will show this message: "One (or more) area at risk has been hit" instead of the table resume with the results
                                $result_comment = get_lang('Unacceptable');
                                //$comment = '<span style="font-weight: bold; color: #FF0000;">' . $answerDestination = $objAnswerTmp->selectComment(2) . '</span>';
                                $answer_ok = false;
                                $answerDestination = $objAnswerTmp->selectDestination(1);
                                //checking the destination parameters parsing the "@@"
                                $destination_items = explode('@@', $answerDestination);                                
                            }
                        } elseif ($answerId > 1) {
                            if ($objAnswerTmp->selectHotspotType($answerId) == 'noerror') {
                                if ($dbg_local > 0) {
                                    error_log(__LINE__ . ' - answerId is of type noerror', 0);
                                }
                                //type no error shouldn't be treated
                                $next = 1;
                                continue;
                            }
                            if ($dbg_local > 0) {
                                error_log(__LINE__ . ' - answerId is >1 so we\'re probably in OAR', 0);
                            }
                            //check the intersection between the oar and the user
                            $inter = $result['success'];

                            $delineation_cord = $objAnswerTmp->selectHotspotCoordinates($answerId);

                            $poly_answer = convert_coordinates($delineation_cord, '|');
                            $max_coord = poly_get_max($poly_user, $poly_answer);
                            $poly_answer_compiled = poly_compile($poly_answer, $max_coord);
                            $overlap = poly_touch($poly_user_compiled, $poly_answer_compiled, $max_coord);

                            if ($overlap == false) {
                                //all good, no overlap
                                $next = 1;
                                continue;
                            } else {
                                if ($dbg_local > 0) {
                                    error_log(__LINE__ . ' - Overlap is ' . $overlap . ': OAR hit', 0);
                                }
                                $organs_at_risk_hit++;
                                //show the feedback
                                $next = 0;
                                $comment = $answerDestination = $objAnswerTmp->selectComment(1);
                                $answerDestination = $objAnswerTmp->selectDestination($answerId);
                                $destination_items = explode('@@', $answerDestination);
                            }
                        }
                    } else { // the first delineation feedback
                        if ($dbg_local > 0) {
                            error_log(__LINE__ . ' first', 0);
                        }
                    }
                } // end for

                if ($overlap_color) {
                    $overlap_color = 'green';
                } else {
                    $overlap_color = 'red';
                }

                if ($missing_color) {
                    $missing_color = 'green';
                } else {
                    $missing_color = 'red';
                }
                if ($excess_color) {
                    $excess_color = 'green';
                } else {
                    $excess_color = 'red';
                }


                if (!is_numeric($final_overlap)) {
                    $final_overlap = 0;
                }

                if (!is_numeric($final_missing)) {
                    $final_missing = 0;
                }
                if (!is_numeric($final_excess)) {
                    $final_excess = 0;
                }

                if ($final_excess > 100) {
                    $final_excess = 100;
                }
                $totalScore+=$totalScoreHotDel;

                
                if ($next == 0) {
                    $try = $try_hotspot;
                    $lp = $lp_hotspot;
                    $destinationid = $select_question_hotspot;
                    $url = $url_hotspot;
                } else {
                    $answerDestination = $objAnswerTmp->selectDestination($nbrAnswers);
                }
                
                $s = '';
                
                $s .= '<table width="100%" border="0">';
                $s .= '<tr><td><object type="application/x-shockwave-flash" data="'.api_get_path(WEB_CODE_PATH).'plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . $questionId . '&exe_id=' . $attemptId . '&from_db=1" width="610" height="410">
                            <param name="movie" value="'.api_get_path(WEB_CODE_PATH).'plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . $questionId . '&exe_id=' . $attemptId . '&from_db=1" />

                        </object></td>';
                $s .= '<td width="40%" valign="top"><div class="quiz_content_actions quit_border" style="height:380px;"><div class="quiz_header">' . get_lang('Feedback') . '</div><p align="center"><img src="'.api_get_path(WEB_CODE_PATH).'img/mousepolygon64.png"></p><div><table width="100%" border="1" class="data_table"><tr class="row_odd"><td>&nbsp;</td><td>' . get_lang('Requirement') . '</td><td>' . get_lang('YourContour') . '</td></tr><tr class="row_even"><td align="right">' . get_lang('Overlap') . '</td><td align="center">' . get_lang('Min') . ' ' . $threadhold1 . ' %</td><td align="center"><div style="color:' . $overlap_color . '">' . (($final_overlap < 0) ? 0 : intval($final_overlap)) . '</div></td></tr><tr class="row_even"><td align="right">' . get_lang('Excess') . '</td><td align="center">' . get_lang('Max') . ' ' . $threadhold2 . ' %</td><td align="center"><div style="color:' . $excess_color . '">' . (($final_excess < 0) ? 0 : intval($final_excess)) . '</div></td></tr><tr class="row_even"><td align="right">' . get_lang('Missing') . '</td><td align="center">' . get_lang('Max') . ' ' . $threadhold3 . ' %</td><td align="center"><div style="color:' . $missing_color . '">' . (($final_missing < 0) ? 0 : intval($final_missing)) . '</div></td></tr>';

                
                if ($organs_at_risk_hit > 0) {
                    $message = get_lang('ResultIs') . ' <b>' . $result_comment . '</b>';
                    $message.= '<p style="color:#DC0A0A;"><b>' . get_lang('OARHit') . '</b></p>';
                } else {
                    $message = '<p>' . get_lang('ResultIs') . ' <b>' . $result_comment . '</b></p>';
                }
                $s .= '<tr><td colspan="3" align="center">' . $message . '</td></tr>';                
                $s .= '</table></div><br/><br/>';
                
                $s .= '</div></td></tr>';
                $s .= '</table>';
                
                
                // feedback
                $s .= '<table style="clear:both;width:100%;margin:15px 0;">';
                $answer = explode("~", $objAnswerTmp->selectComment(1));
                if ($answer_ok === true) {
                    $feedback = $answer[0];
                    $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-right">' . $feedback . '</div></td></tr>';
                } else {
                    $feedback = $answer[1];
                    $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-wrong">' . $feedback . '</div></td></tr>';
                }
                $s .= '</table>';
                
                /*if (!empty($comment)) {
                    $s .= '<div align="center" class="quiz_feedback"><b>' . get_lang('Feedback') . '</b> : ' . $comment . '</div>';
                }*/
                
                
                // score
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
            $media_w = 'width:95%';
            $questionDescription = api_parse_tex($objQuestion->selectDescription());
            $exeId   = $objExercise->getLastUserAttemptId($exerciseId, 'incomplete', null, null, null, $examId);   
            $answer_list = '<input type="hidden" name="choice[' . $questionId . '][1]" value="0" />';
            $canClick = isset($_GET['editQuestion']) ? '0' : (isset($_GET['modifyAnswers']) ? '0' : '1');    
            $swfPath = api_get_path(WEB_CODE_PATH)."plugin/hotspot/hotspot_delineation_user.swf?modifyAnswers=".$questionId."&canClick:$canClick";
            if (!empty($exeId) ) {
                $dataTracking = $objExercise->getDataTracking($exeId);
                if (in_array($questionId, $dataTracking)) {
                $swfPath = api_get_path(WEB_CODE_PATH)."plugin/hotspot/hotspot_solution.swf?modifyAnswers=".$questionId."&exe_id=".$exeId."&from_db=1";                    
                }
            }             
            $s = '';  
            $s  .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';    
            $s  .="     <table class='exercise_questions'>
                            <tr><td valign='top' colspan='2'>".$questionDescription."</td></tr>";                               
            $s  .= "            <script type='text/javascript' src=\"".api_get_path(WEB_CODE_PATH)."plugin/hotspot/JavaScriptFlashGateway.js\"></script>
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
        $s .= '             <tr>
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
                                                    var oeTags = '<object type=\"application/x-shockwave-flash\" data=\"".$swfPath. "\" width=\"610\" height=\"485\">'
                                                                                + '<param name=\"movie\" value=\"".$swfPath. "\" \/>'
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
        $s.= "                          </td>
                                        <td valign='top'>
                                            <div>$answer_list</div>
                                            <table width='100%' border='0'>
                                                <tr><td><img src='".api_get_path(WEB_CODE_PATH)."img/mousepolygon.png'></td></tr>
                                                <tr><td>&nbsp;</td></tr>
                                                <tr><td>&nbsp;</td></tr>
                                                <tr>
                                                    <td>
                                                        <div class='quiz_content_actions'>
                                                            <div class='quiz_header'>".get_lang('DrawPolygonTitle')."</div>
                                                            <div>".get_lang('DelineationMessage1')."</div>
                                                            <div>".get_lang('DelineationMessage2')."</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr><td>&nbsp;</td></tr>
                                            </table>";        
        $s .= "                          </td>
                                    </tr>
                                </table>
                                </td>
                            </tr>
                        </table>
                    </div>";  
            $s .= ' <div class="clear"></div>';  
            return $s;
          }
	

}

endif;
?>
