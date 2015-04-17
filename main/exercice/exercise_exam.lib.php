<?php

// $Id: exercise.lib.php 22247 2009-07-20 15:57:25Z ivantcholakov $

/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2004-2008 Dokeos SPRL
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
 * 	Exercise library
 * 	shows a question and its answers
 * 	@package dokeos.exercise
 * 	@author Olivier Brouckaert <oli.brouckaert@skynet.be>
 * 	@version $Id: exercise.lib.php 22247 2009-07-20 15:57:25Z ivantcholakov $
 */
// The initialization class for the online editor is needed here.
require_once '../inc/lib/fckeditor/fckeditor.php';
require_once '../inc/lib/geometry.lib.php';
include_once('answer.class.php');

/**
 * @param int question id
 * @param boolean only answers
 * @param boolean origin i.e = learnpath
 * @param int current item from the list of questions
 * @param int number of total questions
 * */
function showQuestion_exammode($questionId, $i, $exeId, $s) {
	$exercice_attemp_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

	if (!$objQuestionTmp = Question::read($questionId)) {
        // question not found
        return false;
    }

	$queryans = "select * from ".$exercice_attemp_table." where exe_id = '".Database::escape_string($exeId)."' and question_id= '".Database::escape_string($questionId)."'";
	$resultans = api_sql_query($queryans, __FILE__, __LINE__);
	while ($row = Database::fetch_array($resultans)) {
		$ind = $row['answer'];
		$choice[$ind] = 1;
	}
	
   $answerType = $objQuestionTmp->selectType();
   $questionName = $objQuestionTmp->selectTitle();
   $questionDescription = $objQuestionTmp->selectDescription();
   if($answerType == MATCHING){
   //echo '<script language="javascript" src="'. api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js'.'" type="text/javascript"></script>';
   echo '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
   }

  $s .= '<td><div class="divQuestionScrollAuto"><table border="0" width="100%"><tr>';
  
  if($answerType != MATCHING){
  $s .= '<td valign="top" style="width:375"><div style="width:375px;">';
  }
  else {  
  $s .= '<td valign="top"><div style="width:625px;">';  
  }
  $s .= '<b><span class="questiontitle">'.get_lang('Question'). '</span> : </b>' .$i.'<br/>';
  $s .= '<span class="questionname">'.$questionName.'</span>';

   
   if ($answerType == HOT_SPOT || $answerType == HOT_SPOT_DELINEATION) { // Question is of type HOT_SPOT
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();

        // Get the answers, make a list
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

        $answer_list = '<input type="hidden" name="choice[' . $questionId . '][1]" value="0" /><div><b>' . get_lang('HotspotZones') . '</b><dl>';
        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer_list .= '<span>' . $answerId . '.- ' . $objAnswerTmp->selectAnswer($answerId) . '</span>&nbsp;&nbsp;&nbsp;';
        }
        $answer_list .= '</dl></div>';
        
		$s .="<table class='exercise_questions'>
		<tr>
		  <td valign='top' colspan='2'>
			";
		$questionDescription = api_parse_tex($questionDescription);
		$s.=$questionDescription;
		$s.="
		  </td>
		</tr>";
        
		        
        if($answerType == HOT_SPOT)
        	$swf_file = 'hotspot_user';
        else if($answerType == HOT_SPOT_DELINEATION)
        {
        	$answer_list = '<input type="hidden" name="choice[' . $questionId . '][1]" value="0" />';
        	$swf_file = 'hotspot_delineation_user';
        }

        $canClick = isset($_GET['editQuestion']) ? '0' : (isset($_GET['modifyAnswers']) ? '0' : '1');
        //$tes = isset($_GET['modifyAnswers']) ? '0' : '1';
        //echo $tes;
        $s .= "<script type=\"text/javascript\" src=\"../plugin/hotspot/JavaScriptFlashGateway.js\"></script>
						<script src=\"../plugin/hotspot/hotspot.js\" type=\"text/javascript\"></script>
						<script language=\"JavaScript\" type=\"text/javascript\">
						<!--
						// -----------------------------------------------------------------------------
						// Globals
						// Major version of Flash required
						var requiredMajorVersion = 7;
						// Minor version of Flash required
						var requiredMinorVersion = 0;
						// Minor version of Flash required
						var requiredRevision = 0;
						// the version of javascript supported
						var jsVersion = 1.0;
						// -----------------------------------------------------------------------------
						// -->
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
						function JSGetSwfVer(i){
							// NS/Opera version >= 3 check for Flash plugin in plugin array
							if (navigator.plugins != null && navigator.plugins.length > 0) {
								if (navigator.plugins[\"Shockwave Flash 2.0\"] || navigator.plugins[\"Shockwave Flash\"]) {
									var swVer2 = navigator.plugins[\"Shockwave Flash 2.0\"] ? \" 2.0\" : \"\";
						      		var flashDescription = navigator.plugins[\"Shockwave Flash\" + swVer2].description;
									descArray = flashDescription.split(\" \");
									tempArrayMajor = descArray[2].split(\".\");
									versionMajor = tempArrayMajor[0];
									versionMinor = tempArrayMajor[1];
									if ( descArray[3] != \"\" ) {
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
							else
							{
								flashVer = -1;
							}
							return flashVer;
						}
						// When called with reqMajorVer, reqMinorVer, reqRevision returns true if that version or greater is available

						function DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision)
						{
						 	reqVer = parseFloat(reqMajorVer + \".\" + reqRevision);
						   	// loop backwards through the versions until we find the newest version
							for (i=25;i>0;i--) {
								if (isIE && isWin && !isOpera) {
									versionStr = VBGetSwfVer(i);
								} else {
									versionStr = JSGetSwfVer(i);
								}
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
									if ( (versionMajor > reqMajorVer) && (versionNum >= reqVer) ) {
										return true;
									} else {
										return ((versionNum >= reqVer && versionMinor >= reqMinorVer) ? true : false );
									}
								}
							}
						}
						// -->
						</script>";
		if($answerType == HOT_SPOT){
			$s .=	"<tr><td valign='top' colspan='2' align='left'><div class='exam_hotspot_answers_frame'>$answer_list</div></td></tr>";
		}
		if($answerType == HOT_SPOT_DELINEATION){
		//	$s.= "<tr><td valign='top'><div>$answer_list</div><table width='100%' border='0'><tr><td><div ><div >".get_lang('DrawPolygon')."</div><br/><br/><div>".get_lang('DelineationText1')."</div><br/><div>".get_lang('DelineationText2')."</div></div></td></tr><tr><td>&nbsp;</td></tr></table>";
			$s .=	"<tr><td valign='top' colspan='2' align='left'><div class='exam_hotspot_answers_frame'>$answer_list<br/>".get_lang('DelineationText1')."<br/>".get_lang('DelineationText2')."</div></td></tr>";
		}
        $s .= '<tr><td valign="top" colspan="2" width="450"><table><tr><td width="610"><div id="movie">' . "
					<script language=\"JavaScript\" type=\"text/javascript\">
						<!--
						// Version check based upon the values entered above in \"Globals\"
						var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);


						// Check to see if the version meets the requirements for playback
						if (hasReqestedVersion) {  // if we've detected an acceptable version
						    var oeTags = '<object type=\"application/x-shockwave-flash\" data=\"../plugin/hotspot/".$swf_file.".swf?modifyAnswers=" . $questionId . "&canClick:" . $canClick . "\" width=\"610\" height=\"485\">'
										+ '<param name=\"movie\" value=\"../plugin/hotspot/".$swf_file.".swf?modifyAnswers=" . $questionId . "&canClick:" . $canClick . "\" \/>'
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
					</script></div></td></tr>";						
			$s .= "</table></td></tr></table>";
			$s .= "</div></td>";
			$s .= '</tr></table></div></td>';
        echo $s;
        
    }

   if ($answerType != HOT_SPOT && $answerType != HOT_SPOT_DELINEATION) { // Question is not of type hotspot

   // construction of the Answer object
	$objAnswerTmp = new Answer($questionId);
	$nbrAnswers = $objAnswerTmp->selectNbrAnswers();  
	if ($answerType == MATCHING) {
		$cpt1 = 'A';
		$cpt2 = 1;
		$cntOption = 1;
		$Select = array();
		$QA = array();
		$s .= '<input type="hidden" name="questionid" value="' . $questionId . '">';
	}
	elseif ($answerType == FREE_ANSWER) {
		$choice = stripslashes($ind);
		$choice = str_replace('rn', '', $choice);

		// $oFCKeditor = new FCKeditor("choice[" . $questionId . "]");
		$oFCKeditor = new FCKeditor("newchoice");
		$oFCKeditor->ToolbarSet = 'TestFreeAnswer';
		$oFCKeditor->Width = '100%';
		$oFCKeditor->Height = '200';
		if(empty($choice)){
		$oFCKeditor->Value = '';
		}
		else {
		$oFCKeditor->Value = $choice;
		}

		$s .= $oFCKeditor->CreateHtml();
	}

	for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
			$studentChoice=$choice[$answerId];
			if ($answerType == UNIQUE_ANSWER) {
                $answer = api_parse_tex($answer);
				if($studentChoice){
					$s .= "<input id='radio-" . $questionId . "-" . $answerId . "' type='radio' name='choice[" . $questionId . "]' value='" . $answerId . "' checked   onclick='PlaySound()'><input type='hidden' name='choice2[" . $questionId . "]' value='0'>";
				}
				else {
					$s .= "<input id='radio-" . $questionId . "-" . $answerId . "' type='radio' name='choice[" . $questionId . "]' value='" . $answerId . "'   onclick='PlaySound()'><input type='hidden' name='choice2[" . $questionId . "]' value='0'>";
				}
				
                $s .= '<label for="radio-' . $questionId . '-' . $answerId . '">' . strip_tags($answer,'<a><span><img><sub><sup>') . '</label>';
            } 
			elseif ($answerType == MULTIPLE_ANSWER) {
                $answer = api_parse_tex($answer);
                // multiple answers
				if($studentChoice){
					$s.="<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1' checked onclick='PlaySound()' /><input type='hidden' name='choice2[" . $questionId . "][0]' value='0' />";
				}
				else {
					$s.="<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1' onclick='PlaySound()' /><input type='hidden' name='choice2[" . $questionId . "][0]' value='0' />";
				}
                
                $s .= "<label for='check-" . $questionId . "-" . $answerId . "'>".strip_tags($answer,'<a><span><img><sub><sup>')."</label>";

            }
			elseif ($answerType == REASONING) {
                // reasoning answers
                $answer = api_parse_tex($answer);
				if($studentChoice){
					$s.="<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1' checked onclick='PlaySound()'><input type='hidden' name='choice2[" . $questionId . "][0]' value='0'>";
				}
				else {
					$s.="<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1' onclick='PlaySound()'><input type='hidden' name='choice2[" . $questionId . "][0]' value='0'>";
				}
                
                $s .= '<label for="check-' . $questionId . '-' . $answerId . '">' . strip_tags($answer,'<a><span><img><sub><sup>') . '</label>';
            }
			elseif ($answerType == FILL_IN_BLANKS) {
                // splits text and weightings that are joined with the character '::'
                list($answer) = explode('::', $answer);

                // because [] is parsed here we follow this procedure:
                // 1. find everything between the [tex] and [/tex] tags
                $startlocations = api_strpos($answer, '[tex]');
                $endlocations = api_strpos($answer, '[/tex]');

                if ($startlocations !== false && $endlocations !== false) {
                    $texstring = api_substr($answer, $startlocations, $endlocations - $startlocations + 6);
                    // 2. replace this by {texcode}
                    $answer = str_replace($texstring, '{texcode}', $answer);
                }

                // 3. do the normal matching parsing
                // replaces [blank] by an input field
                //getting the matches
                //$answer = api_ereg_replace('\[[^]]+\]', '<input type="text" name="choice[' . $questionId . '][]" size="10">', ($answer));
                preg_match_all('/\[[^]]+]/', $answer, $matches);

				$queryfill = "SELECT answer FROM ".$exercice_attemp_table." WHERE exe_id = '".Database::escape_string($exeId)."' and question_id= '".Database::escape_string($questionId)."'";
				$resfill = api_sql_query($queryfill, __FILE__, __LINE__);
				$str=Database::result($resfill,0,"answer");
				$str = str_replace("<br />","",$str);

				preg_match_all('#\[([^[]*)\]#', $str, $arr);
				$choice = $arr[1];				
				$user_answer = array();
				for($k=0;$k<sizeof($choice);$k++){					
					$student_answer = $choice[$k];
					$student_answer = str_replace('<font color=\"red\"><s>','[',$student_answer);
					$student_answer = str_replace('<font color="red"><s>','[',$student_answer);
					$student_answer = str_replace('<font color="green"><b>','[',$student_answer);
					$student_answer = str_replace('&nbsp;',' ',$student_answer);
					$student_answer = str_replace('</s></font>','',$student_answer);
					$student_answer = str_replace('</b></font>','',$student_answer);
					
				/*	$startpos = strpos($student_answer, '[');
					$endpos = strpos($student_answer, '/');
					$diff = $endpos - $startpos;
					$usertext = substr($student_answer, $startpos+1, $diff-1);*/

					$answer_temp = explode("/",$student_answer);
					$usertext = str_replace("[","",$answer_temp[0]);
					$user_answer[$k] = trim($usertext);
					
				//	$user_answer[$k] = $usertext;
				}
				
				/*if(Database::num_rows($resfill) <> 0){
					$student_answer = stripslashes($str);
					$student_answer = str_replace('rn', '', $student_answer);					
				}
				else {
					$student_answer = '';
				}
				$student_answer = str_replace('<font color="red"><s>','',$student_answer);
				$student_answer = str_replace('<font color="green"><b>','',$student_answer);
				$student_answer = str_replace('</s></font>','',$student_answer);
				$student_answer = str_replace('</b></font>','',$student_answer);
				$startpos = strpos($student_answer, '[');
				$endpos = strpos($student_answer, '/');
				$diff = $endpos - $startpos;
				$usertext = substr($student_answer, $startpos+1, $diff-1);*/

				$z = 0;
                foreach ($matches[0] as $match) {
					$usertext1 = '';					
					$usertext1 = $user_answer[$z];
                    $answer_len = strlen($match) - 2;
                    if ($answer_len <= 4) {						
                        $size = "2";
                        $temp = str_replace($match, '<input type="text" name="choice[' . $questionId . '][]" size="' . $size . '" value="'.$usertext1.'">', $answer);
                        $answer = $temp;
                    } 
					else if ($answer_len > 4 && $answer_len <= 20) {
                        $size = "10";
                        $temp = str_replace($match, '<input type="text" name="choice[' . $questionId . '][]" size="' . $size . '" value="'.$usertext1.'">', $answer);
                        $answer = $temp;
                    } 
					else {
                        $size = "18";
                        $temp = str_replace($match, '<input type="text" name="choice[' . $questionId . '][]" size="' . $size . '" value="'.$usertext1.'">', $answer);
                        $answer = $temp;
                    }
					$z++;
                }
                $answer = $temp;                

                // 5. replace the {texcode by the api_pare_tex parsed code}
                $texstring = api_parse_tex($texstring);
                $answer = str_replace("{texcode}", $texstring, $answer);
				$s .= $answer;				
            }
			else {
                if (preg_match("/<img/i", $answer)) {
                    $image_match = "Y";
                }
                
                if (!$answerCorrect) {
                    // options (A, B, C, ...) that will be put into the list-box
                    //	$Select[$answerId]['Lettre']=$cpt1++;
                    // answers that will be shown at the right side
                    $cntOption++;
                    $answer = api_parse_tex($answer);
                    //var_dump($answer);exit;
                    $Select[$answerId]['Reponse'] = $answer;
                    $Select[$answerId]['Lettre'] = $answer;
                    $field_choice_name = "choice[" . $questionId . "][" . $answerId . "]";
                    //$answer = '<p>' . Security::remove_xss($answer) . '</p>';
                    //$s .= '<input type="hidden" name="' . $field_choice_name . '" id="' . $field_choice_name . '" value="' . $answer .'" />';
					$s .= "<input type='hidden' name='" . $field_choice_name . "' id='" . $field_choice_name . "' value='" . $answer . "' />";
                    //$s .= "<input type='hidden' name='" . $field_choice_name . "' id='" . $field_choice_name . "' value=\"" . $answer . "\" >";
                } else {
                    $s .= "<input type='hidden' name='choice[" . $questionId . "][" . $answerId . "]' id='choice[" . $questionId . "][" . $answerId . "]' value='0'/>";
                    //   if (!empty($answer)) {
                    $QA[] = $answer;
                    $option = array();
                    $option[] = (0);
                    foreach ($Select as $key => $val) {
                        $option[] = $val['Lettre'];
                    }
                    //    }
                    $cpt2++;
                }
            }
			
	}	

	//Div to show the question and answer options
	
	echo $s;

	if ($answerType == MATCHING) {//question_title
        $Qdiv_css = "Qdiv_exam";
        $destinationbox_css = "destinationBox_exam";
        $answerDiv_css = "answerDiv_exam";
        $drag_answer_css = "drag_answer_exam";
        if ($image_match == 'Y') {
            $Qdiv_css = "Qdiv_img_exam";
            $destinationbox_css = "destinationBox_img_exam";
            $answerDiv_css = "answerDiv_img_exam";
            $drag_answer_css = "drag_answer_img_exam";
        }
        echo '<script type="text/javascript">
$(document).ready(function(){';
        for ($i = 0; $i <= count($QA); $i++) {
            $ans_i = $i + 1;
            echo '
	  $("#a' . $questionId . '-' . $ans_i . '").draggable({
		  revert:true,	
		  revertDuration: 0.5,
                  helper: "clone"
	});
	  $("#q' . $questionId . '-' . $i . '").droppable({
		   //I used this to make only a1 acceptable for q1
			drop: function(event, ui) {
			var cntOption = $("[name=cntOption-' . $questionId . ']").val();				
			var dragid = ui.draggable.attr("id");		
			
			var ansidarr = dragid.split("-");
			var ansid = ansidarr[1];
			var dropid = $(this).attr("id");
			
			var numericIdarr = dropid.split("-");
			var numericId = numericIdarr[1];
			
			var ansOption = (numericId*1) + (cntOption*1);	
			var answer = document.getElementById("choice[' . $questionId . ']["+ansid+"]").value;
			';
            if ($image_match == 'Y') {
                echo '
			$(this).html("<div style=\"border:1px solid #000;font-size:12px;background-color:#fff;height:124px;width:218px;overflow:hidden;\">"+answer+"</div>");
			';
            } else {
                echo '
			$(this).html("<div style=\"border:1px solid #000;font-size:12px;background-color:#fff;height:103px;width:216px;overflow:hidden;padding-bottom:1px;\">"+answer+"</div>");
			';
            }
            echo '
			document.getElementById("choice[' . $questionId . ']["+ansOption+"]").value = ansid;
				
	}	
            });  ';
        }

        echo '   }); 
</script>';
			$table_ans = Database :: get_course_table(TABLE_QUIZ_ANSWER);
			$sql_select_answer = 'SELECT id, answer, correct, position FROM '.$table_ans.' WHERE question_id="'.Database::escape_string($questionId).'" AND correct<>0';								
			$res_answers = api_sql_query($sql_select_answer, __FILE__, __LINE__);
			$userans = array();
			while ($a_answers = Database::fetch_array($res_answers)) {			
				$i_answer_id = $a_answers['id']; //3
				$s_answer_label = $a_answers['answer'];  // your dady - you mother			
				$i_answer_correct_answer = $a_answers['correct']; //1 - 2			
				$i_answer_position = $a_answers['position']; // 3 - 4
				
				$sql_user_answer = 
						'SELECT answers.answer 
						FROM '.$exercice_attemp_table.' as track_e_attempt 
						INNER JOIN '.$table_ans.' as answers 
							ON answers.position = track_e_attempt.answer
							AND track_e_attempt.question_id=answers.question_id
						WHERE answers.correct = 0
						AND track_e_attempt.exe_id = "'.Database::escape_string($exeId).'"
						AND track_e_attempt.question_id = "'.Database::escape_string($questionId).'" 
						AND track_e_attempt.position="'.Database::escape_string($i_answer_position).'"';				
				
				$res_user_answer = api_sql_query($sql_user_answer, __FILE__, __LINE__);
				if (Database::num_rows($res_user_answer)>0 ) {
					$userans[] = Database::result($res_user_answer,0,0); //  rich - good looking
				} else { 
					$userans[] = '';
				}
			}

        
        echo '<div id="dragScriptContainer_exam"><div id="' . $Qdiv_css . '"><table width="100%">';
        for ($i = 0; $i < count($QA); $i++) {
            echo '<tr><td  width="50%"><div class="question_exam">'. $QA[$i] . '</div></td><td  width="50%"><div id="q'. $questionId . '-' . $i . '" class="' . $destinationbox_css . '">'.$userans[$i].'</div></td></tr>';
        }
        echo '</table></div><div id="' . $answerDiv_css . '" style="font-face:verdana;"><table width="100%">';
        for ($i = 1; $i < count($option); $i++) {
            if (!empty($option[$i])) {
                echo '<tr><td><div class="' . $drag_answer_css . '" id="a' . $questionId . '-' . $i . '">' . $option[$i] . '</div></td></tr>';
            }
        }
        echo '</table>';
        echo '</div><div id="dragContent1"></div>';
        echo '<input type="hidden" name="cntOption-' . $questionId . '" value="' . $cntOption . '">';
        
        echo '</div>';
    }
	
	echo '</div></td>';
	
	if($answerType != MATCHING){	
	//Div to show the media of that question
	echo '<td valign="top" align="left"><div class="divScrollAuto">';	
	echo $questionDescription;
	echo '</div></td>';
	}

	echo '</tr></table></div></td>';
	 // destruction of the Answer object
        unset($objAnswerTmp);

        // destruction of the Question object
        unset($objQuestionTmp);

        
    }   
	
}

function showPrintQuestion($questionId, $qnno) {
	// reads question informations
    if (!$objQuestionTmp = Question::read($questionId)) {
        // question not found
        return false;
    }

    $answerType = $objQuestionTmp->selectType();
	$questionName = api_parse_tex($objQuestionTmp->selectTitle());
	$questionName = str_replace("<p>","",$questionName);
	$questionName = str_replace("</p>","",$questionName);
    $pictureName = $objQuestionTmp->selectPicture();
    $questionDescription = api_parse_tex($objQuestionTmp->selectDescription());
	$pos = stripos($questionDescription, 'swfobject.js');
	$pos1 = stripos($questionDescription, '<embed');
	if ($pos === false) 
		$videoMedia = 'N';
	else
		$videoMedia = 'Y';

	if ($pos1 === false)
		$flashMedia = 'N';
	else
		$flashMedia = 'Y';

	if($videoMedia == 'Y'){
		$mediaDescription = '<div class="media_desc"><p>Video</p></div>';
	}
	else if($flashMedia == 'Y'){
		$mediaDescription = '<div class="media_desc"><p>Flash</p></div>';
	}
	else {
		$mediaDescription = $questionDescription;
	}

	$objAnswerTmp = new Answer($questionId);
    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

	if($answerType != MATCHING && $answerType != HOT_SPOT && $answerType != HOT_SPOT_DELINEATION) {
		echo '<table border="0"><tr>';
		echo '<td width="70%" valign="top">';
			echo '<table border="0" width="100%">';
			echo '<tr>';
			echo '<td>';
			echo '<table><tr><td valign="top" width="2%"><b>'.$qnno.'.</b></td><td>'.$questionName.'</td></tr></table>';
			echo '</td>';
			echo '</tr>';
			echo '<tr><td>&nbsp;</td></tr>';
			echo '<tr>';
			echo '<td>';

			for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);

			
			if ($answerType == FILL_IN_BLANKS) {
                // splits text and weightings that are joined with the character '::'
                list($answer) = explode('::', $answer);

                // because [] is parsed here we follow this procedure:
                // 1. find everything between the [tex] and [/tex] tags
                $startlocations = api_strpos($answer, '[tex]');
                $endlocations = api_strpos($answer, '[/tex]');

                if ($startlocations !== false && $endlocations !== false) {
                    $texstring = api_substr($answer, $startlocations, $endlocations - $startlocations + 6);
                    // 2. replace this by {texcode}
                    $answer = str_replace($texstring, '{texcode}', $answer);
                }                
                preg_match_all('/\[[^]]+]/', $answer, $matches);

                foreach ($matches[0] as $match) {
                    $answer_len = strlen($match) - 2;
                    if ($answer_len <= 4) {						
                        $size = "2";
                        $temp = str_replace($match, '<input type="text" name="choice[' . $questionId . '][]" size="' . $size . '" >', $answer);
                        $answer = $temp;
                    } 
					else if ($answer_len > 4 && $answer_len <= 20) {
                        $size = "10";
                        $temp = str_replace($match, '<input type="text" name="choice[' . $questionId . '][]" size="' . $size . '">', $answer);
                        $answer = $temp;
                    } 
					else {
                        $size = "18";
                        $temp = str_replace($match, '<input type="text" name="choice[' . $questionId . '][]" size="' . $size . '">', $answer);
                        $answer = $temp;
                    }
                }
                $answer = $temp;               

                // 5. replace the {texcode by the api_pare_tex parsed code}
                $texstring = api_parse_tex($texstring);
                $answer = str_replace("{texcode}", $texstring, $answer);
            }

			if ($answerType == UNIQUE_ANSWER) {
                $answer = api_parse_tex($answer);
                    $s .= "<input id='radio-" . $questionId . "-" . $answerId . "' type='radio' name='choice[" . $questionId . "]' value='" . $answerId . "'><input type='hidden' name='choice2[" . $questionId . "]' value='0'>";
                    $s .= '<label for="radio-' . $questionId . '-' . $answerId . '"><span style="font-size:12px;">' . strip_tags($answer,'<a><span><img>') . '</span></label>';
            }
			elseif ($answerType == MULTIPLE_ANSWER) {
                $answer = api_parse_tex($answer);                
					$s .= "<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1' /><input type='hidden' name='choice2[" . $questionId . "][0]' value='0' />";
                    $s .= "<label for='check-" . $questionId . "-" . $answerId . "'><span style='font-size:12px;'>".strip_tags($answer,'<a><span><img>')."</span></label>";

            }
			elseif ($answerType == REASONING) {
                $answer = api_parse_tex($answer);
					$s .= "<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1'><input type='hidden' name='choice2[" . $questionId . "][0]' value='0'>";
					$s .= '<label for="check-' . $questionId . '-' . $answerId . '"><span style="font-size:12px;">' . strip_tags($answer,'<a><span><img>') . '</span></label>';
            }
			elseif ($answerType == FILL_IN_BLANKS) {         
                $s .= "<div class='scroll'><table><tr><td>$answer</td></tr></table></div>";
            }
			elseif ($answerType == FREE_ANSWER) {
				/*$oFCKeditor = new FCKeditor("newchoice");
				$oFCKeditor->ToolbarSet = 'TestFreeAnswer';
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '200';
				$oFCKeditor->Value = '';

				$s .=  $oFCKeditor->CreateHtml();*/

				$s .= "<div class='print_textarea'></div>";
			}

			
			}
			echo $s;
			echo '</td>';
			echo '</tr>';
			echo '</table>';
		echo '</td>';
		echo '<td width="30%" valign="top">';
		echo $mediaDescription;
		echo '</td>';
		echo '</tr></table>';
	}

	if($answerType == MATCHING){
		$cpt1 = 'A';
		$cpt2 = 1;
		$cntOption = 1;
		$Select = array();
		$QA = array();
		$image_match = "N";
		for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);

			if (preg_match("/<img/i", $answer)) {
				$image_match = "Y";
			}
			
			if (!$answerCorrect) {
				// options (A, B, C, ...) that will be put into the list-box
				//	$Select[$answerId]['Lettre']=$cpt1++;
				// answers that will be shown at the right side
				$cntOption++;
				$answer = api_parse_tex($answer);
				$Select[$answerId]['Reponse'] = $answer;
				$Select[$answerId]['Lettre'] = $answer;
				$field_choice_name = "choice[" . $questionId . "][" . $answerId . "]";
				$s .= "<input type='hidden' name='" . $field_choice_name . "' id='" . $field_choice_name . "' value='" . $answer . "' />";

			} else {
				$s .= "<input type='hidden' name='choice[" . $questionId . "][" . $answerId . "]' id='choice[" . $questionId . "][" . $answerId . "]' value='0'/>";
				$QA[] = $answer;
				$option = array();
				$option[] = (0);
				foreach ($Select as $key => $val) {
					$option[] = $val['Lettre'];
				}
				$cpt2++;
			}
		}

		$Qdiv_css = "Qdiv";
        $destinationbox_css = "destinationBox";
        $answerDiv_css = "answerDiv";
        $drag_answer_css = "drag_answer";
        if ($image_match == 'Y') {
            $Qdiv_css = "Qdiv_img";
            $destinationbox_css = "destinationBox_img";
            $answerDiv_css = "answerDiv_img";
            $drag_answer_css = "drag_answer_img";
        }        

		//echo '<b>'.$qnno.'.</b>&nbsp;'.$questionName;
		echo '<table><tr><td valign="top" width="2%"><b>'.$qnno.'.</b></td><td>'.$questionName.'</td></tr></table>';

        echo '<div id="dragScriptContainer">';		
		echo '<div id="' . $Qdiv_css . '"><table width="100%">';
        for ($i = 0; $i < count($QA); $i++) {
            echo '<tr><td><div class="question" >' . $QA[$i] . '</div></td><td><div id="q' . $questionId . '-' . $i . '" class="' . $destinationbox_css . '"></div></td></tr>';
        }
        echo '</table></div><div id="' . $answerDiv_css . '" style="font-face:verdana;"><table width="100%">';
        for ($i = 1; $i < count($option); $i++) {
            if (!empty($option[$i])) {
                echo '<tr><td><div class="' . $drag_answer_css . '" id="a' . $questionId . '-' . $i . '">' . $option[$i] . '</div></td></tr>';
            }
        }
        echo '</table>';        

        echo '</div><div id="dragContent"></div>';
        echo '<input type="hidden" name="cntOption-' . $questionId . '" value="' . $cntOption . '">';
        
        echo '</div>';
	}

	if ($answerType == HOT_SPOT || $answerType == HOT_SPOT_DELINEATION) {
	//	echo '<b>'.$qnno.'.</b>&nbsp;'.$questionName;
		echo '<table><tr><td valign="top" width="2%"><b>'.$qnno.'.</b></td><td>'.$questionName.'</td></tr></table>';
		$answer_list = '<input type="hidden" name="choice[' . $questionId . '][1]" value="0" /><div><br/><br/><b>' . get_lang('HotspotZones') . '</b><dl>';
        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer_list .= '<dt>' . $answerId . '. ' . $objAnswerTmp->selectAnswer($answerId) . '</dt><br />';
        }
        $answer_list .= '</dl></div>';

		if($answerType == HOT_SPOT)
        	$swf_file = 'hotspot_user';
        else if($answerType == HOT_SPOT_DELINEATION)
        {
        	$answer_list = '<input type="hidden" name="choice[' . $questionId . '][1]" value="0" />';
        	$swf_file = 'hotspot_delineation_user';
        }

			$s .= "<td valign='top' align='left'><br/><img src='".api_get_path(WEB_COURSE_PATH).api_get_course_id()."/document/images/".$pictureName."'></td>";

			if($answerType == HOT_SPOT){
				$s .= "<td valign='top' align='left'><div class='hotspot_answers_frame'><div>$answer_list</div><div style='vertical-align:baseline;padding-top:100px;'><img src='../img/MouseHotspots.png'></div></td>";
			}
			if($answerType == HOT_SPOT_DELINEATION){
				$s.= "<td valign='top'><div>$answer_list</div><table width='100%' border='0'><tr><td><img src='../img/mousepolygon.png'></td></tr><tr><td>&nbsp;</td></tr><tr><td><div ><div class='quiz_header'>".get_lang('DrawPolygon')."</div><br/><br/><div>".get_lang('DelineationText1')."</div><br/><div>".get_lang('DelineationText2')."</div></div></td></tr><tr><td>&nbsp;</td></tr></table>";
			}			
			
			echo '<table border="0"><tr>';
			echo $s;
			echo '</tr></table>';
	}
}
?>
