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
function showQuestion($questionId, $onlyAnswers = false, $origin = false, $current_item, $total_item, $quiz_context = null, $simplifyQuestionsAuthoring = 'false') {
    
    $_SESSION['ValidateQn'] = 'N';
    $image_match = "N";
    // reads question informations
    if (!$objQuestionTmp = Question::read($questionId)) {
        // question not found
        return false;
    }

    $answerType = $objQuestionTmp->selectType();
    $pictureName = $objQuestionTmp->selectPicture();

    if ($answerType != HOT_SPOT && $answerType != HOT_SPOT_DELINEATION) { // Question is not of type hotspot
        if (!$onlyAnswers) {
            //$questionName = api_utf8_encode($objQuestionTmp->selectTitle());
            $questionName = $objQuestionTmp->selectTitle();            
            $questionName = str_replace("<p>&nbsp;</p>","",$questionName);
            $questionDescription = $objQuestionTmp->selectDescription();
            $mediaPosition = $objQuestionTmp->selectMediaPosition();
            if($simplifyQuestionsAuthoring== 'true'){
                $Showimageleft = $objQuestionTmp->selectShowimageleft();
                $Showimageright = $objQuestionTmp->selectShowimageright();
                
            }
            if ($mediaPosition == 'top' && !empty($questionDescription)) {
                if ($answerType != MATCHING) {
                    $s = "<div align='left'><div class='quiz_content_actions quit_border' style=\"float:left;\"><div align='center'><div class='media_scroll'>";
                    $questionDescription = api_parse_tex($questionDescription);
                    $s.=$questionDescription;
                    $s.="</div></div></div></div><br/>";
                    echo $s;
                    $s = '';
                }
            }

            $questionName = api_parse_tex($questionName);
            $questionName = trim($questionName);
            //id="content_with_secondary_actions"

            if ($answerType == MATCHING) {
                $s = "<div id=\"question_title\" class=\"quiz_content_actions quit_border custom_quiz\" style=\"margin-top:10px;\">";
                echo $s;
                echo $questionName . '</div>';
                if (!empty($questionDescription)) {
                    if ($mediaPosition == 'top') {
                        echo '<div style="padding-top:10px;" class=\'quiz_content_actions quit_border\'><div class=\'media_scroll\'>' . $questionDescription . '</div></div>';
                    }
                    if ($mediaPosition == 'right') {
                        echo '<div style="width:30%; padding-top:10px; float:right;" class=\'quiz_content_actions quit_border\'><div class=\'media_scroll\'>' . $questionDescription . '</div></div>';
                    }
                }
                // Only for preview
                if (isset($_SESSION['is_within_submit']) && $_SESSION['is_within_submit'] == 1) {
                    //   echo '<div class="quiz_content_actions">';
                    echo '<form name="frm_exercise" id="frm_exercise" style="width:926px; clear:both">';
                } else {
                    echo '<form name="frm_exercise" id="frm_exercise" style="width:926px; clear:both">';
                }
            } else {
                if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription) || empty($mediaPosition)) {
                    $s = "<div id=\"question_title\" class=\"quiz_content_actions quit_border custom_quiz\" style=\" overflow:auto; clear:both;margin-top:10px;\">";
                } elseif ($mediaPosition == 'right') {
                    $s = "<div id=\"question_title\" class=\"quiz_content_actions quit_border custom_quiz\" style=\"overflow:auto;clear:both;margin-top:-5px !important;\"><div class=\"sectscroll\">";
                }
                echo $s;
                if (!empty($quiz_context)) {
                    echo '<div style="margin-left:10px; padding-right:10px;margin-bottom:10px;">' . $quiz_context . '</div>';
                }
                echo '<div style="margin-left:10px; padding-right:10px;">' . $questionName . '</div></div></div>';
                if ($answerType == MATCHING) {
                    // Only for preview
                    if (isset($_SESSION['is_within_submit']) && $_SESSION['is_within_submit'] == 1) {
                        //   echo '<div class="quiz_content_actions">';
                        echo '<form name="frm_exercise" id="frm_exercise" style="width:926px; clear:both">';
                    } else {
                        echo '<form name="frm_exercise" id="frm_exercise" style="width:926px; clear:both">';
                    }
                }
            }
            $s = '';
            if (!empty($questionDescription)) {             
                if ($mediaPosition == 'right') {
                    if ($answerType != MATCHING) {
                        $s.="<div style='float:right; width: 500px; overflow:hidden; padding:0px; text-align:center;'>";
                        $s.="<div style='padding-right:5px;'><div class='media_scroll'>";
                        $questionDescription = api_parse_tex($questionDescription);
                        $s.=$questionDescription;
                        $s.="</div></div></div>";
                    }
                }
                if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
                    $widthaboutmedia = '100%';                   
                    $s.="<div class='quiz_content_actions' style='width:100%;overflow:auto;border:0px !important; box-shadow:none!important;overflow-x: hidden!IMPORTANT'>";
                    echo "<style>
                        .scroll1{
                            width:900px !important;
                        }
                    </style>";
                } elseif ($mediaPosition == 'right') {
                    $widthaboutmedia = '80%';
                    if ($answerType == MATCHING) {
                    $s.="<div class='quiz_content_actions quiz_content_list' style='border:0px !important; box-shadow:none!important; width:65%; float:left; clear:none'>";
					}
					else {
						$s.="<div class='quiz_content_actions quiz_content_list' style='border:0px !important; box-shadow:none!important; width:45%; float:left; clear:none'>";
					}
                }
            } elseif ($answerType != MATCHING) {
                $questionDescription = '';
            }

            if (empty($questionDescription)) {
                $s.="<div class='quiz_content_actions quit_border' style='float:left'>";
            }

            if (!empty($pictureName)) {
                $s.="<img src='../document/download.php?doc_url=%2Fimages%2F'" . $pictureName . "' border='0'>";
            }
        }

        if ($answerType != MATCHING) {
            //$s.="<div style='width:97%;height:auto;min-height:180px;clear:both;' class='quiz_content_actions'><table class='exercise_options' style=\"width: 100%;\">";
            $s.="<div style='width:97%;height:auto;min-height:180px;clear:both;' ><table class='exercise_options' style=\"width: 100%;\">";
        }
        // construction of the Answer object
        $objAnswerTmp = new Answer($questionId);

        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

        // only used for the answer type "Matching"
        if ($answerType == MATCHING) {
            $cpt1 = 'A';
            $cpt2 = 1;
            $cntOption = 1;
            $Select = array();
            $QA = array();
            $s .= '<input type="hidden" name="questionid" value="' . $questionId . '">';
        } elseif ($answerType == FREE_ANSWER) {
            $html_editor = api_return_html_area("newchoice", null, '200', '100%', null, array('ToolbarSet' => 'TestFreeAnswer'));
            $s .= "<tr><td colspan='2'>" . $html_editor . "</td></tr>";
        }

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

                // 3. do the normal matching parsing
                // replaces [blank] by an input field
                //getting the matches
                preg_match_all('/\[[^]]+]/', $answer, $matches);

                foreach ($matches[0] as $match) {
                    /* @var $answer_len type */
                    $answer_len = intval(strlen(str_replace('<u>', '', str_replace('</u>', '', $match))) - 2);
                    $answer = str_replace($match, '<input type="text" name="choice[u' . $questionId . '][]" size="' . ($answer_len) . '" />', $answer);
                }
                $answer = str_replace('choice[u', 'choice[', $answer);
                // 5. replace the {texcode by the api_pare_tex parsed code}
                $texstring = api_parse_tex($texstring);
                $answer = str_replace("{texcode}", $texstring, $answer);
            }

            // unique answer
            if ($answerType == UNIQUE_ANSWER) {
                $answer = api_parse_tex($answer);
                $s .= "<input id='radio-" . $questionId . "-" . $answerId . "' type='radio' name='choice[" . $questionId . "]' value='" . $answerId . "'><input type='hidden' name='choice2[" . $questionId . "]' value='0'>";
                $s.='<label for="radio-' . $questionId . '-' . $answerId . '">' . strip_tags($answer, '<a><span><img><sub><sup>') . '</label>';
            } elseif ($answerType == MULTIPLE_ANSWER) {
                $answer = api_parse_tex($answer);
                // multiple answers
                $s.="<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1' />
			<input type='hidden' name='choice2[" . $questionId . "][0]' value='0' />
                        <label for='check-" . $questionId . "-" . $answerId . "'>" . strip_tags($answer, '<a><span><img><sub><sup>') . "</label>";
            } elseif ($answerType == REASONING) {
                // reasoning answers
                $answer = api_parse_tex($answer);
                $s.="<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox' type='checkbox' name='choice[" . $questionId . "][" . $answerId . "]' value='1'>
                    <input type='hidden' name='choice2[" . $questionId . "][0]' value='0'>";
                $s.='<label for="check-' . $questionId . '-' . $answerId . '">' . strip_tags($answer, '<a><span><img><sub><sup>') . '</label>';
            } elseif ($answerType == FILL_IN_BLANKS) {
                // fill in blanks
                //$s.="<tr><td colspan='2'>$answer</td></tr>";                
                $width_fill = ($simplifyQuestionsAuthoring == 'true') ? ("style='width:100%!important;'") : ("");
                $s .= "<tr><td colspan='2'><div style='width:$widthaboutmedia'><div $width_fill class='scroll scroll1' ><table><tr><td>$answer</td></tr></table><div></div></td></tr>";
            }
            // free answer
            // matching
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
                    $s .= '<input type="hidden" name="' . $field_choice_name . '" id="' . $field_choice_name . '" value="' . api_htmlentities($answer) . '" />';                    
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
        } // end for()
        if (!ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) {
            if ($answerType != MATCHING) {
                $s .= '</table>';
                $s .= '</div></div></div>';
            }
        } else {
            if ($answerType != MATCHING) {
                $s .= '</table></div></div>';
            }
        }

        // destruction of the Answer object
        unset($objAnswerTmp);

        // destruction of the Question object
        unset($objQuestionTmp);

        if ($origin != 'export') {
            echo $s;
        } else {
            return($s);
        }
    } elseif ($answerType == HOT_SPOT || $answerType == HOT_SPOT_DELINEATION) { // Question is of type HOT_SPOT
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();

        // Get the answers, make a list
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

        $answer_list = '<input type="hidden" name="choice[' . $questionId . '][1]" value="0" /><div><b>' . get_lang('HotspotZones') . '</b><dl>';
        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer_list .= '<dt>' . $answerId . '.- ' . $objAnswerTmp->selectAnswer($answerId) . '</dt><br />';
        }
        $answer_list .= '</dl></div>';

        if (!$onlyAnswers) {
            $s = "<div id=\"question_title\" class=\"quiz_content_actions quit_border custom_quiz\">";
            if (!empty($quiz_context)) {
                $s .= '<div style="margin-left:10px; padding-right:10px;margin-bottom:10px;">' . $quiz_context . '</div>';
            }
            $s .= "<div style=\"float: left;    margin-left: 10px;    margin-right: 20px;    width: 97%;\">";
            $s .= $questionName . '</div><div class="clear"></div></div>';

            $s .="<table class='exercise_questions' style='float:right;'>
			<tr>
			  <td valign='top' colspan='2'>
				";
            $questionDescription = api_parse_tex($questionDescription);
            $s.=$questionDescription;
            $s.="
			  </td>
			</tr>";
        }

        if ($answerType == HOT_SPOT)
            $swf_file = 'hotspot_user';
        else if ($answerType == HOT_SPOT_DELINEATION) {
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
        $s .= '<tr><td valign="top" colspan="2" width="600"><table><tr><td width="610">' . "
					<script language=\"JavaScript\" type=\"text/javascript\">
						<!--
						// Version check based upon the values entered above in \"Globals\"
						var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);


						// Check to see if the version meets the requirements for playback
						if (hasReqestedVersion) {  // if we've detected an acceptable version
						    var oeTags = '<object type=\"application/x-shockwave-flash\" data=\"../plugin/hotspot/" . $swf_file . ".swf?modifyAnswers=" . $questionId . "&canClick:" . $canClick . "\" width=\"610\" height=\"485\">'
										+ '<param name=\"movie\" value=\"../plugin/hotspot/" . $swf_file . ".swf?modifyAnswers=" . $questionId . "&canClick:" . $canClick . "\" \/>'
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
					</script></td>";
        if ($answerType == HOT_SPOT) {
            $s .= "<td valign='top' align='left'><div class='hotspot_answers_frame'  style=\"border:none\"><div style='height:280px;overflow:auto;'>$answer_list</div><br/><div><img src='../img/MouseHotspots.png'></div></td></tr></table>";
        }
        if ($answerType == HOT_SPOT_DELINEATION) {
            $s.= "</td><td valign='top'><div>$answer_list</div><table width='100%' border='0'><tr><td><img src='../img/mousepolygon.png'></td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td><div class='quiz_content_actions'><div class='quiz_header'>" . get_lang('DrawPolygon') . "</div><br/><br/><div>" . get_lang('DelineationText1') . "</div><br/><div>" . get_lang('DelineationText2') . "</div></div></td></tr><tr><td>&nbsp;</td></tr></table>";
        }

        $s .= "</td></tr></table>";
        echo $s;
        echo '</td></tr></table>';
    }
    echo '<div class="clear"></div>';

    if ($answerType == MATCHING) {//question_title      
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
        echo '<script type="text/javascript">
$(document).ready(function(){
        $(window).load(function(){
    if ($(".matching-drag").length > 0) {
        equalHeight($(".matching-drag"));        
    }
    
    function equalHeight(columns) {   
    columns.each(function(i) {
        var classes = $(this).attr("class");  
        $(".matching-drag").height(getMaxHeight($(".matching-drag")));         
    });
    }
    
    function getMaxHeight(group) {
    var thisHeight, tallest = 0;            
    group.each(function(i) {
        thisHeight = $(group[i]).height();               
        if(thisHeight > tallest) {
            tallest = thisHeight;             
        }      
    });   
    return tallest;
    }

});';
//   if(!isset($_GET['clean'])){ //if is comming from Preview button don't realize 
//            echo '
//var itemNbr = $(".matching-drag").length;
//            
//    var i = 0;
//   if (itemNbr > 0) {
//        var rowNbr = itemNbr/3;
//       
//        for (var row=1; row<=rowNbr; row++){
//            setMaxHeight($(".matching-drag"),row);
//        }
//   }   
//    function setMaxHeight(group,row){
//    var tallest=0;
//    var leftHeight      = $(group[2*row-2]).height();
//    var rigthHeight     = $(group[itemNbr-rowNbr+i]).height(); 
//    tallest             = (leftHeight>rigthHeight)?leftHeight:rigthHeight;  
////    if(tallest < 25) {
////        tallest = 50;
////    }
//    $(group[2*row-2]).height(tallest);     
//    $(group[2*row-1]).height(tallest);
//    $(group[itemNbr-rowNbr+i]).height(tallest);
//    i++;
//    }';    
//   }           

$Items = (count($option) > count($QA)) ? count($option) : count($QA);
for ($i = 0; $i <= $Items; $i++) {
            $ans_i = $i + 1;
            echo '
	  $("#a' . $questionId . '-' . $ans_i . '").draggable({
                   
                    revert:true,	
                    revertDuration: 0.5,
                    helper: "clone",
                    start: function(event, ui){
                                var width1 = $(this).width();
                                ui.helper.css("width",width1);                                
                    }
                  
	}); 
        $("#q' . $questionId . '-' . $i . '").droppable({
		   //I used this to make only a1 acceptable for q1
                        //activeClass: "ui-state-hover",
                        //hoverClass: "ui-state-active",
			drop: function(event, ui) {
                        
                        //$( this ).addClass("ui-state-highlight" );                        
			var cntOption = $("[name=cntOption-' . $questionId . ']").val();				
			var dragid = ui.draggable.attr("id");		
			
			var ansidarr = dragid.split("-");
			var ansid = ansidarr[1];
			var dropid = $(this).attr("id");
			var drop_h = $(this).height();
                        
			var numericIdarr = dropid.split("-");
			var numericId = numericIdarr[1];
			
                        //var h = ui.draggable.height();
                        var w = ui.draggable.css("width");                       
                        var h = $(this).css("height");                                        

			var ansOption = (numericId*1) + (cntOption*1);	                        
			var answer = document.getElementById("choice[' . $questionId . ']["+ansid+"]").value;                         
			answer = answer.replace("<div", "<span");
			answer = answer.replace("</div>", "</span>");
			';
            
                        $stardivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style=\"padding:10px;width:auto;font-size:16px;text-align:left;\">' : '';
                        $enddivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
                        
                        $starimg = ($simplifyQuestionsAuthoring == 'true' AND $Showimageright == 0 ) ? ('<img src=\"'):('');
                        $endimg = ($simplifyQuestionsAuthoring == 'true' AND $Showimageright == 0 ) ? ('\" />'):('');
                        if ($image_match == 'Y') {
                            echo '
                                    $(this).html("<div style=\"border:1px solid #000;background-color:#fff;height:"+h+";width:99%; overflow:hidden\">'. $stardivright . $starimg.'"+answer+"'.$endimg. $enddivright . '</div>");
                                    ';
                        } else {
                            echo '
                                    $(this).html("<div style=\"border:1px solid #000;background-color:#fff;height:"+h+";width:99%;\">'. $stardivright . $starimg.'"+answer+"'.$endimg . $enddivright . '</div>");
                                    ';
                        }
            echo '
			document.getElementById("choice[' . $questionId . ']["+ansOption+"]").value = ansid;
				
	}	
            });  ';
        }

        echo '   }); 
</script>';


        //echo '<div id="dragScriptContainer" style="width:102%">
        echo '<div id="dragScriptContainer">
            <div id="' . $Qdiv_css . '" >
             ';
        $stardivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
        $enddivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
        
        $stardivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
        $enddivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
        
        for ($i = 0; $i < count($QA); $i++) {
            $content = preg_replace("/<\/?div[^>]*\>/i", "", $QA[$i]);            
            $content = ($Showimageleft == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$content.'" />') : ($content);
            $padd = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '' : 'padding:0px;';
            
            echo '<div class="' . $Qdiv_css . '-left"><div class="question matching-drag">'.$stardivleft . $content . $enddivleft. '</div>
                  <div id="q' . $questionId . '-' . $i . '" class="matching-drag ' . $destinationbox_css . '"></div></div>';
        }
        echo '
            </div><div id="' . $answerDiv_css . '" style="font-face:verdana;">';
        for ($i = 1; $i < count($option); $i++) {
            if (!empty($option[$i])) {
		    $content_option = preg_replace("/<\/?div[^>]*\>/i", "", $option[$i]);
                    $content_option = ($Showimageright == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$content_option.'" />') : ($content_option);
                    echo '<div class="' . $Qdiv_css . '-pb"><div class="matching-drag ' . $drag_answer_css . '"style="overflow-x:hidden" id="a' . $questionId . '-' . $i . '">' . $stardivright . $content_option . $enddivright . '</div></div>';
                }
            }

        echo '</div><div id="dragContent"></div>';
        echo '<input type="hidden" name="cntOption-' . $questionId . '" value="' . $cntOption . '">';
        // Only for preview
        if (isset($_SESSION['is_within_submit']) && $_SESSION['is_within_submit'] == 1) {
            echo '</div>';
        } else {
            echo '</form>';
        }
        echo '</div>';
    }

    return $nbrAnswers;
}

function showFeedback($questionId, $onlyAnswers = false, $origin = false, $current_item = 0, $total_item = 0, $exe_id = 0, $quiz_context = null, $simplifyQuestionsAuthoring = 'false') {
    require_once 'question.class.php';
    require_once 'answer.class.php';
    if (!ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) {
        if (isset($_REQUEST['quizpopup'])) {
            echo '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.js" type="text/javascript"></script>';
        }
    }

    $_SESSION['ValidateQn'] = 'Y';
    $TBL_TRACK_ATTEMPT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

    $objQuestionTmp = Question::read($questionId);
    $answerType = $objQuestionTmp->type;
    if ($answerType != HOT_SPOT || $answerType != HOT_SPOT_DELINEATION) { // Question is not of type hotspot
        $questionName = $objQuestionTmp->selectTitle();
        $questionName = api_parse_tex($questionName);
        $mediaPosition = $objQuestionTmp->selectMediaPosition();
        $questionDescription = $objQuestionTmp->selectDescription();
    }

    if ($mediaPosition == 'right') {
        $s = '<div id="question_title" class="quiz_content_actions quit_border custom_quiz" style="overflow:hidden;float:left;">';
        if (!empty($quiz_context)) {
            $s .= '<div style="margin-left:10px; padding-right:10px;margin-bottom:10px;">' . $quiz_context . '</div>';
        }
        $s .= '<div style="float: left; margin-left: 10px; margin-right: 20px; width: 97%;">' . $questionName . '</div></div>';
        if (!empty($questionDescription)) {
            if ($answerType != MATCHING) {
                $s .= '<div style="padding-right:5px; float:right; width:500px; text-align:center; overflow:hidden;"><div class="quit_border" style="clear:both;margin:auto;overflow:auto !important;min-height:270px">' . $questionDescription . '</div><div class="clear"></div></div>';
            }
        }
    } else if ($mediaPosition == 'top') {
        if (!empty($questionDescription)) {
            echo '<div align="left"><div class="quiz_content_actions quit_border" style="width:100%;overflow:hidden;float:left;text-align:center;">' . $questionDescription . '</div></div>';
        }
        $s = '<div id="question_title" class="quiz_content_actions custom_quiz quit_border" style="overflow:auto;float:left;">';
        if (!empty($quiz_context)) {
            $s .= '<div style="margin-left:10px; padding-right:10px;margin-bottom:10px;">' . $quiz_context . '</div>';
        }
        $s .= '<div style="margin-left:10px; padding-right:15px;">' . $questionName . '</div></div>';
    } else {
        $s = '<div id="question_title" class="quiz_content_actions custom_quiz quit_border" style="width:100%;overflow:auto;float:left;">';
        if (!empty($quiz_context)) {
            $s .= '<div style="margin-left:10px; padding-right:10px;margin-bottom:10px;">' . $quiz_context . '</div>';
        }
        $s .= '<div style="margin-left:10px;">' . $questionName . '</div></div>';
    }

    unset($objQuestionTmp);
    if ($answerType == UNIQUE_ANSWER) {
        $answerOK = 'N';
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $questionScore = 0;

        if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
            $s .= '<div class="quiz_content_actions" style="border:0px !important; box-shadow:none!important;width:100%;float:left;">';
        } elseif ($mediaPosition == 'right') {
            $s .= '<div class="quiz_content_actions quiz_content_list" style="float:left;width:45%;clear:none;">';
        }
        $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>' . get_lang("Choice") . '</td><td>' . get_lang("ExpectedChoice") . '</td><td>' . get_lang("Answer") . '</td></tr>';

        $correct = $not_correct = 0;
        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answerComment = $objAnswerTmp->selectComment($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
            if ($answerCorrect) {
                $correct = $answerId;
            } else {
                $not_correct = $answerId;
            }
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $queryans = "select answer from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
            $resultans = api_sql_query($queryans, __FILE__, __LINE__);
            $choice = Database::result($resultans, 0, "answer");
            $studentChoice = ($choice == $answerId) ? 1 : 0;
            if ($studentChoice && $answerCorrect) {
                $answerOK = 'Y';
                $questionScore+=$answerWeighting;
                $totalScore+=$answerWeighting;
                $feedback_if_true = $objAnswerTmp->selectComment($answerId);
            } else {
                $feedback_if_false = $objAnswerTmp->selectComment($answerId);
            }

            if ($answerId == 1) {
                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
            } else {
                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
            }
        }

        $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
        $s .= '</table></div>';

        $s .= '<table style="clear:both; width: 100%; padding-top:20px;">';
        if ($answerOK == 'Y') {
            $feedback_if_true = $objAnswerTmp->selectComment($correct);
            if (empty($feedback_if_true)) {
                $feedback_if_true = get_lang('NoTrainerComment');
            }
            $s .= '<tr><td colspan="3"><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-right"><span>' . $feedback_if_true . '</span></div></td></tr>';
        } else {
            $feedback_if_false = $objAnswerTmp->selectComment($not_correct);
            if (empty($feedback_if_false)) {
                $feedback_if_false = get_lang('NoTrainerComment');
            }
            $s .= '<tr><td colspan="3"><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-wrong"><span>' . $feedback_if_false . '</span></div></td></tr>';
        }
        $s .= '</table>';
    }
    if ($answerType == MULTIPLE_ANSWER) {
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $questionScore = 0;
        $answerWrong = 'N';
        //   $s .= "<table align='center' width='100%' class='feedback_actions'>";

        if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
            $s .= '<div class="quiz_content_actions quit_border" style="float:left;">';
        } elseif ($mediaPosition == 'right') {
            $s .= '<div class="quiz_content_actions quit_border" style="width:45%;float:left;clear:none;">';
        }
        $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>' . get_lang("Choice") . '</td><td>' . get_lang("ExpectedChoice") . '</td><td>' . get_lang("Answer") . '</td></tr>';

        $count_ans = $last_incorrect = 0;
        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answerComment = $objAnswerTmp->selectComment($answerId);
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
            $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
            $queryans = "select * from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
            $resultans = api_sql_query($queryans, __FILE__, __LINE__);

            while ($row = Database::fetch_array($resultans)) {
                $ind = $row['answer'];
                $choice[$ind] = 1;
            }
            $studentChoice = $choice[$answerId];
            if ($studentChoice) {
                $count_ans++;
                if ($studentChoice == $answerCorrect) {
                    $correctChoice = 'Y';
                    $feedback_if_true = $objAnswerTmp->selectComment($answerId);
                } else {
                    $answerWrong = 'Y';
                    $feedback_if_false = $objAnswerTmp->selectComment($answerId);
                }
            }
            if ($feedback_if_true == '') {
                $feedback_if_true = get_lang('NoTrainerComment');
            }
            if ($feedback_if_false == '') {
                $feedback_if_false = get_lang('NoTrainerComment');
            }
            if (!$answerCorrect) {
                $last_incorrect = $answerId;
            }


            if ($answerId == 1) {
                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
            } else {
                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
            }
        }
        $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
        $s .= '</table></div>';


        $s .= '<table style="clear:both; width:100%; padding-top:20px;">';
        if ($correctChoice == 'Y' && $answerWrong == 'N') {
            if (empty($feedback_if_true)) {
                $feedback_if_true = get_lang('NoTrainerComment');
            }
            $s .= '<tr><td colspan="3"><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-right"><span>' . $feedback_if_true . '</span></div></td></tr>';
        } else {
            if (empty($feedback_if_false)) {
                $feedback_if_false = get_lang('NoTrainerComment');
            }
            if (empty($count_ans)) {
                $feedback_if_false = $objAnswerTmp->selectComment($last_incorrect);
            }
            $s .= '<tr><td colspan="3"><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-wrong"><span>' . $feedback_if_false . '</span></div></td></tr>';
        }
        $s .= '</table>';
    }

    if ($answerType == REASONING) {
        $answerOK = 'N';
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $questionScore = 0;
        $correctChoice = 'Y';
        $noStudentChoice = 'N';
        $answerWrong = 'N';
        $expectedAnswer = '';
        $yourChoice = '';

        if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
            $s .= '<div class="quiz_content_actions quit_border" style="overflow:hidden;float:left;">';
        } elseif ($mediaPosition == 'right') {
            $s .= '<div class="quiz_content_actions quit_border" style="width:45%;float:left;overflow:auto;clear:none;">';
        }
        $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>' . get_lang("Choice") . '</td><td>' . get_lang("ExpectedChoice") . '</td><td>' . get_lang("Answer") . '</td></tr>';

        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerComment = $objAnswerTmp->selectComment($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
            $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
            $queryans = "select * from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
            $resultans = api_sql_query($queryans, __FILE__, __LINE__);
            while ($row = Database::fetch_array($resultans)) {
                $ind = $row['answer'];
                $choice[$ind] = 1;
            }
            $studentChoice = $choice[$answerId];

            if ($studentChoice) {
                if (empty($yourChoice)) {
                    $yourChoice = $objAnswerTmp->selectAnswer($answerId);
                } else {
                    $yourChoice = $yourChoice . " " . $objAnswerTmp->selectAnswer($answerId);
                }
            }

            if ($answerCorrect) {
                if (empty($expectedAnswer)) {
                    $expectedAnswer = $objAnswerTmp->selectAnswer($answerId);
                } else {
                    $expectedAnswer = $expectedAnswer . " " . $objAnswerTmp->selectAnswer($answerId);
                }
                $feedback_if_true = $objAnswerTmp->selectComment($answerId);
            } else {
                $feedback_if_false = $objAnswerTmp->selectComment($answerId);
            }

            if ($answerId == '2') {
                $wrongAnswerWeighting = $answerWeighting;
            }
            if ($answerCorrect && $studentChoice == '1' && $correctChoice == 'Y') {
                $correctChoice = 'Y';
                $noStudentChoice = 'Y';
            } elseif ($answerCorrect && !$studentChoice) {
                $correctChoice = 'N';
                $noStudentChoice = 'Y';
                $answerWrong = 'Y';
            } elseif (!$answerCorrect && $studentChoice == '1') {
                $correctChoice = 'N';
                $noStudentChoice = 'Y';
                $answerWrong = 'Y';
            }

            if ($answerId == 1) {
                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
            } else {
                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
            }
        }
        if ($noStudentChoice == 'Y') {
            if ($correctChoice == 'Y') {
                $answerOK = 'Y';
            }
        }

        $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
        $s .= '</table></div>';


        $s .= '<table style="clear:both; width:100%; padding-top:20px;">';
        if ($correctChoice == 'Y' && $answerWrong == 'N') {
            if (empty($feedback_if_true)) {
                $feedback_if_true = get_lang('NoTrainerComment');
            }
            $s .= '<tr><td colspan="3"><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-right"><span>' . $feedback_if_true . '</span></div></td></tr>';
        } else {
            if (empty($feedback_if_false)) {
                $feedback_if_false = get_lang('NoTrainerComment');
            }
            $s .= '<tr><td colspan="3"><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-wrong"><span>' . $feedback_if_false . '</span></div></td></tr>';
        }
        $s .= '</table>';
    }

    if ($answerType == HOT_SPOT) {
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $questionScore = 0;
        $correctComment = array();
        $answerOk = 'N';
        $answerWrong = 'N';

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

        $s .= '<table style="float:left;" width="100%" border="0"><tr><td><div align="center"><object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $exe_id . '&from_db=1" width="610" height="410">
            <param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $exe_id . '&from_db=1" />
          </object></div></td><td width="40%" valign="top"><div class="quiz_content_actions quit_border" style="height:400px;overflow:auto;"><div class="quiz_header">' . get_lang('Feedback') . '</div><div align="center"><img src="../img/MouseHotspots64.png"></div><br/>';

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

            if (empty($correctComment[1])) {
                $correctComment[1] = get_lang('NoTrainerComment');
            }
            if (empty($correctComment[2])) {
                $correctComment[2] = get_lang('NoTrainerComment');
            }

            $TBL_TRACK_HOTSPOT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
            $query = "select hotspot_correct from " . $TBL_TRACK_HOTSPOT . " where hotspot_exe_id = '" . Database::escape_string($exe_id) . "' and hotspot_question_id= '" . Database::escape_string($questionId) . "' AND hotspot_answer_id='" . Database::escape_string($answerId) . "'";
            $resq = api_sql_query($query);
            $choice = Database::result($resq, 0, "hotspot_correct");

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
        $s .= '</table><br/><br/>';
        if ($answerOk == 'Y' && $answerWrong == 'N') {
            if ($nbrAnswers == 1) {
                $feedback .= '<div class="feedback-right feed-custom-right" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-right"><span>' . $correctComment[0] . '</span></div>';
            } else {
                $feedback .= '<div class="feedback-right feed-custom-right" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-right"><span>' . $correctComment[1] . '</span></div>';
            }
        } else {
            if ($nbrAnswers == 1) {
//                               $feedback = '<span style="font-weight: bold; color: #FF0000;">'.$correctComment[1].'</span>'; 
                $feedback .= '<div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-wrong"><span>' . $correctComment[1] . '</span></div>';
            } else {
//				 $feedback = '<span style="font-weight: bold; color: #FF0000;">'.$correctComment[2].'</span>'; 
                $feedback .= '<div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-wrong"><span>' . $correctComment[2] . '</span></div>';
            }
        }
        $s .= '</div></td></tr></table>';

        if (!empty($feedback)) {
//                  $s .= '<div align="center" class="quiz_feedback"><b>'.get_lang('Feedback').'</b> : '.$feedback.'</div>';		 
            $s .= '<div align="center" class="quiz_feedback" style="clear:both; padding-top:20x; width:100%;">' . $feedback . '</div>';
        }
    }
    if ($answerType == HOT_SPOT_DELINEATION) {
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        //$nbrAnswers=1; // based in the code found in exercise_show.php
        $questionScore = 0;

        //based on exercise_submit modal
        /*  Hot spot delinetion parameters */
        $choice = $exerciseResult[$questionid];
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
            $TBL_TRACK_HOTSPOT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
            $query = "select hotspot_correct, hotspot_coordinate from " . $TBL_TRACK_HOTSPOT . " where hotspot_exe_id = '" . Database::escape_string($exe_id) . "' and hotspot_question_id= '" . Database::escape_string($questionId) . "' AND hotspot_answer_id='1'"; //by default we take 1 because it's a delineation 
            $resq = api_sql_query($query);
            $row = Database::fetch_array($resq, 'ASSOC');
            $choice = $row['hotspot_correct'];
            $user_answer = $row['hotspot_coordinate'];

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

                $answerDestination = $objAnswerTmp->selectComment(1);
                $answerDestination = $objAnswerTmp->selectComment(2);
                
                list($feedback_if_true, $feedback_if_false) = explode("~", $objAnswerTmp->selectComment(1));
//                                             
                if (empty($feedback_if_true)) {
                    $feedback_if_true = get_lang('NoTrainerComment');
                }
                if (empty($feedback_if_false)) {
                    $feedback_if_false = get_lang('NoTrainerComment');
                }


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
                        $comment = '<div class="feedback-right feed-custom-right" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-right"><span style="font-weight: bold; color: #008000;">' . $feedback_if_true . '</span></div>';
                    } else {
                        $next = 1; //Go to the oars. If $next =  0 we will show this message: "One (or more) area at risk has been hit" instead of the table resume with the results	
                        $result_comment = get_lang('Unacceptable');
                        $comment = '<div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-wrong"><span style="font-weight: bold; color: #FF0000;">' . $feedback_if_false . '</span></div>';
                        $answerDestination = $objAnswerTmp->selectDestination(1);
                        //checking the destination parameters parsing the "@@"	
                        $destination_items = explode('@@', $answerDestination);
                        /*
                          $try_hotspot=$destination_items[1];
                          $lp_hotspot=$destination_items[2];
                          $select_question_hotspot=$destination_items[3];
                          $url_hotspot=$destination_items[4]; */
                        //echo 'show the feedback';
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
                    //echo 'user';	print_r($x_user_list);		print_r($y_user_list);
                    //echo 'official';print_r($x_list);print_r($y_list);												
                    //$result = get_intersection_data($x_list,$y_list,$x_user_list,$y_user_list);
                    $inter = $result['success'];

                    //$delineation_cord=$objAnswerTmp->selectHotspotCoordinates($answerId);
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
                        $comment = $answerDestination = $objAnswerTmp->selectComment($answerId);
                        $answerDestination = $objAnswerTmp->selectDestination($answerId);

                        $destination_items = explode('@@', $answerDestination);
                        /*
                          $try_hotspot=$destination_items[1];
                          $lp_hotspot=$destination_items[2];
                          $select_question_hotspot=$destination_items[3];
                          $url_hotspot=$destination_items[4]; */
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

        if ($answerType != HOT_SPOT_DELINEATION) {
            $item_list = explode('@@', $destination);
            //print_R($item_list);
            $try = $item_list[0];
            $lp = $item_list[1];
            $destinationid = $item_list[2];
            $url = $item_list[3];
            $table_resume = '';
        } else {
            if ($next == 0) {
                $try = $try_hotspot;
                $lp = $lp_hotspot;
                $destinationid = $select_question_hotspot;
                $url = $url_hotspot;
            } else {
                //show if no error
                //echo 'no error';				
                //	$comment=$answerComment=$objAnswerTmp->selectComment($nbrAnswers);	
                //	$comment=$answerComment=$objAnswerTmp->selectComment(2);	
                $answerDestination = $objAnswerTmp->selectDestination($nbrAnswers);
            }
        }

        echo $s;
        $s = '';
        echo '<div style="float:left; width:100%;"><table width="100%" border="0">';
        echo '<tr><td><object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . $questionId . '&exe_id=' . $exe_id . '&from_db=1" width="610" height="410">
						<param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . $questionId . '&exe_id=' . $exe_id . '&from_db=1" />
						<param name="wmode" value="transparent"/>	
					</object></td>';
        echo '<td width="40%" valign="top"><div class="quiz_content_actions quit_border" style="min-height:380pxheight:auto;"><div class="quiz_header">' . get_lang('Feedback') . '</div><p align="center"><img src="../img/mousepolygon64.png"></p><div><table width="100%" border="1" class="data_table"><tr class="row_odd"><td>&nbsp;</td><td>' . get_lang('Requirement') . '</td><td>' . get_lang('YourContour') . '</td></tr><tr class="row_even"><td align="right">' . get_lang('Overlap') . '</td><td align="center">' . get_lang('Min') . ' ' . $threadhold1 . ' %</td><td align="center"><div style="color:' . $overlap_color . '">' . (($final_overlap < 0) ? 0 : intval($final_overlap)) . '</div></td></tr><tr class="row_even"><td align="right">' . get_lang('Excess') . '</td><td align="center">' . get_lang('Max') . ' ' . $threadhold2 . ' %</td><td align="center"><div style="color:' . $excess_color . '">' . (($final_excess < 0) ? 0 : intval($final_excess)) . '</div></td></tr><tr class="row_even"><td align="right">' . get_lang('Missing') . '</td><td align="center">' . get_lang('Max') . ' ' . $threadhold3 . ' %</td><td align="center"><div style="color:' . $missing_color . '">' . (($final_missing < 0) ? 0 : intval($final_missing)) . '</div></td></tr>';

        if ($answerType == HOT_SPOT_DELINEATION) {
            if ($organs_at_risk_hit > 0) {
                $message = get_lang('ResultIs') . ' <b>' . $result_comment . '</b>';
                $message.= '<p style="color:#DC0A0A;"><b>' . get_lang('OARHit') . '</b></p>';
            } else {
                $message = '<p>' . get_lang('ResultIs') . ' <b>' . $result_comment . '</b></p>';
            }

            echo '<tr><td colspan="3" align="center">' . $message . '</td></tr>';

            // by default we assume that the answer is ok but if the final answer after calculating the area in hotspot delineation =0 then update  
            if ($final_answer == 0) {
                $sql = 'UPDATE ' . $TBL_TRACK_ATTEMPT . ' SET answer="", marks = 0 WHERE question_id = ' . $questionId . ' AND exe_id = ' . $exe_id;
                Database::query($sql, __FILE__, __LINE__);
            }
        } else {
            //echo '<p>'.$comment.'</p>';
            echo '<tr><td colspan="3">' . $comment . '</td></tr>';
        }

        echo '</table></div><br/><br/>';
        echo '</div></td></tr>';
        echo '</table>';

        if (!empty($comment)) {
            //		echo '<div align="center" class="quiz_feedback"><b>'.get_lang('Feedback').'</b> : '.$comment.'</div>';
            echo '<div align="center" class="quiz_feedback" style="clear:both; padding:20px 0; width:100%;">' . $comment . '</div>';
        }
    }
    if ($answerType == FILL_IN_BLANKS) {
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $questionScore = 0;
        $feedback_data = unserialize($objAnswerTmp->comment[1]);
        $feedback_true = $feedback_data['comment[1]'];
        $feedback_false = $feedback_data['comment[2]'];


        if ($feedback_true == '') {
            $feedback_true = get_lang('NoTrainerComment');
        }
        if ($feedback_false == '') {
            $feedback_false = get_lang('NoTrainerComment');
        }

        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerComment = $objAnswerTmp->selectComment($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
            $answerWeighting = $objAnswerTmp->selectWeighting($answerId);

            $pre_array = explode('::', $answer);

            // is switchable fill blank or not
            $is_set_switchable = explode('@', $pre_array[1]);
            $switchable_answer_set = false;
            if ($is_set_switchable[1] == 1) {
                $switchable_answer_set = true;
            }

            $answer = $pre_array[0];

            // splits weightings that are joined with a comma
            $answerWeighting = explode(',', $is_set_switchable[0]);
            //list($answer,$answerWeighting)=explode('::',$multiple[0]);
            //$answerWeighting=explode(',',$answerWeighting);
            // we save the answer because it will be modified
            $temp = $answer;

            // TeX parsing
            // 1. find everything between the [tex] and [/tex] tags
            $startlocations = api_strpos($temp, '[tex]');
            $endlocations = api_strpos($temp, '[/tex]');
            if ($startlocations !== false && $endlocations !== false) {
                $texstring = api_substr($temp, $startlocations, $endlocations - $startlocations + 6);
                // 2. replace this by {texcode}
                $temp = str_replace($texstring, '{texcode}', $temp);
            }
            $j = 0;
            // the loop will stop at the end of the text
            $i = 0;
            $feedback_usertag = array();
            $feedback_correcttag = array();
            $feedback_anscorrect = array();
            //normal fill in blank
            if (!$switchable_answer_set) {
                while (1) {
                    // quits the loop if there are no more blanks
                    if (($pos = api_strpos($temp, '[')) === false) {
                        // adds the end of the text
                        $answer.=$temp;
                        // TeX parsing
                        $texstring = api_parse_tex($texstring);
                        break;
                    }
                    $temp = api_substr($temp, $pos + 1);
                    // quits the loop if there are no more blanks
                    if (($pos = api_strpos($temp, ']')) === false) {
                        break;
                    }

                    $queryfill = "select answer from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                    $resfill = api_sql_query($queryfill, __FILE__, __LINE__);
                    $str = Database::result($resfill, 0, "answer");
                    $str = str_replace("<br />", "", $str);
                    $str = str_replace("<s>", "", $str);
                    $str = str_replace("</s>", "", $str);

                    preg_match_all('#\[([^[]*)\]#', $str, $arr);
                    $choice = $arr[1];
                    $tmp = strrpos($choice[$j], ' / ');
                    $choice[$j] = substr($choice[$j], 0, $tmp);
                    $choice[$j] = trim($choice[$j]);
                    $choice[$j] = stripslashes($choice[$j]);
                    $feedback_usertag[] = $choice[$j];
                    $feedback_correcttag[] = api_strtolower(api_substr($temp, 0, $pos));

                    $str = str_replace("[", " <u>", $str);
                    $str = str_replace("]", "</u> ", $str);
                    // if the word entered by the student IS the same as the one defined by the professor
                    if (api_strtolower(trim(api_substr($temp, 0, $pos))) == api_strtolower($choice[$j])) {
                        $feedback_anscorrect[] = "Y";
                        // gives the related weighting to the student
                        $questionScore+=$answerWeighting[$j];
                        // increments total score
                        $totalScore+=$answerWeighting[$j];
                    } else {
                        $feedback_anscorrect[] = "N";
                    }
                    // else if the word entered by the student IS NOT the same as the one defined by the professor
                    $j++;
                    $temp = api_substr($temp, $pos + 1);
                    $i = $i + 1;
                }
                $answer = stripslashes($str);
            } else {
                //multiple fill in blank
                while (1) {
                    // quits the loop if there are no more blanks
                    if (($pos = api_strpos($temp, '[')) === false) {
                        // adds the end of the text
                        $answer.=$temp;
                        // TeX parsing
                        $texstring = api_parse_tex($texstring);
                        //$answer=str_replace("{texcode}",$texstring,$answer);
                        break;
                    }
                    // adds the piece of text that is before the blank and ended by [
                    $real_text[] = api_substr($temp, 0, $pos + 1);
                    $answer.=api_substr($temp, 0, $pos + 1);
                    $temp = api_substr($temp, $pos + 1);

                    // quits the loop if there are no more blanks
                    if (($pos = api_strpos($temp, ']')) === false) {
                        // adds the end of the text
                        //$answer.=$temp;
                        break;
                    }

                    $queryfill = "SELECT answer FROM " . $TBL_TRACK_ATTEMPT . " WHERE exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                    $resfill = api_sql_query($queryfill, __FILE__, __LINE__);
                    $str = Database::result($resfill, 0, "answer");
                    $str = str_replace("<br />", "", $str);

                    preg_match_all('#\[([^[/]*)/#', $str, $arr);
                    $choice = $arr[1];

                    $choice[$j] = trim($choice[$j]);
                    $user_tags[] = api_strtolower($choice[$j]);
                    $correct_tags[] = api_strtolower(api_substr($temp, 0, $pos));

                    $j++;
                    $temp = api_substr($temp, $pos + 1);
                    $i = $i + 1;
                }
                $answer = '';
                for ($i = 0; $i < count($correct_tags); $i++) {
                    if (in_array($user_tags[$i], $correct_tags)) {
                        // gives the related weighting to the student
                        $questionScore+=$answerWeighting[$i];
                        // increments total score
                        $totalScore+=$answerWeighting[$i];
                    }
                }
                $answer = stripslashes($str);
                $answer = str_replace('rn', '', $answer);
            }
            $i++;
        }

        if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
            $s .= '<div class="quiz_content_actions quit_border " style="overflow:hidden;float:left;">';
            $s .= '<div class="scroll_feedback"><b>' . $answer . '</b></div>';
        } elseif ($mediaPosition == 'right') {
            $s .= '<div class="quiz_content_actions" style="width:45%;float:left;height:auto;min-height:300px;overflow:hidden;clear:none;">';
            $s .= '<div class="scroll_feedback"><b>' . $answer . '</b></div>';
        }
        $s .= '</div>';


//		$s .= '<table width="100%" border="0"><tr><td>'.get_lang('Feedback').'</td></tr>';
        $s .= '<table style="width:100%;clear:both; padding-top:20px;" border="0">';
        $fy = 0;
        $fn = 0;
        for ($k = 0; $k < sizeof($feedback_anscorrect); $k++) {
            if ($feedback_anscorrect[$k] == "Y") {
                $fy++;
            } else {
                $fn++;
            }
        }
        if ($fy >= $fn && $fy > 0) {
            $s .= '<tr><td><div class="feedback-right feed-custom-right" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-right"><span>' . $feedback_true . '</span></div></td></tr>';
        } else {
            $s .= '<tr><td><div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-wrong"><span>' . $feedback_false . '</span></div></td></tr>';
        }
        $s .= '</table>';
    }
    if ($answerType == MATCHING) {
        $objQuestionTmp = Question::read($questionId);
        $questionWeighting = $objQuestionTmp->selectWeighting();
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $answerComment_true = $objAnswerTmp->selectComment(1);
        $answerComment_false = $objAnswerTmp->selectComment(2);
        
        if($simplifyQuestionsAuthoring== 'true'){        
            $Showimageleft = $objQuestionTmp->selectShowimageleft();
            $Showimageright = $objQuestionTmp->selectShowimageright();
        }

        if ($answerComment_true == '') {
            $answerComment_true = get_lang('NoTrainerComment');
        }
        if ($answerComment_false == '') {
            $answerComment_false = get_lang('NoTrainerComment');
        }
        $questionScore = 0;
        $answer_ok = 'N';
        $answer_wrong = 'N';
        $table_ans = Database :: get_course_table(TABLE_QUIZ_ANSWER);
        $TBL_TRACK_ATTEMPT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

        $sql_select_answer = 'SELECT id, answer, correct, position FROM ' . $table_ans . ' WHERE question_id="' . Database::escape_string($questionId) . '" AND correct<>0 ORDER BY position';
        $sql_answer = 'SELECT position, answer, id FROM ' . $table_ans . ' WHERE question_id="' . Database::escape_string($questionId) . '" AND correct=0 ORDER BY position';     
        $res_answer = api_sql_query($sql_answer, __FILE__, __LINE__);
        // getting the real answer
        $real_list = array();
        while ($real_answer = Database::fetch_array($res_answer)) {
            $real_list[$real_answer['position']] = array('id'=>$real_answer['id'],'answer'=>$real_answer['answer']);
        }

        if ($mediaPosition == 'top' || $mediaPosition == 'nomedia') {
            $width = '100%';
        } elseif ($mediaPosition == 'right') {
            $width = '46%';
        }
        $res_answers = api_sql_query($sql_select_answer, __FILE__, __LINE__);

        //$s .= '<div class="quiz_content_actions quit_border" style="float:left; width :' . $width . '; clear:none;"><table cellspacing="0" cellpadding="0" align="center" class="feedback_actions fa_2">';
        $s .= '<div class="quiz_content_actions quit_border" style="float:left; width:100%; clear:none;"><table cellspacing="0" cellpadding="0" align="center" class="feedback_actions fa_2">';
        $s .= '<thead>';
        $s .= '<tr>
                <th align="center" width="33%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("ElementList") . '</span> </th>
		<th align="center" width="33%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("YourAnswers") . '</span></th>
		<th align="center" width="33%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("Correct") . '</span></th>
               </tr>';
        $s .= '</thead>';

        while ($a_answers = Database::fetch_array($res_answers)) {
            $i_answer_id = $a_answers['id']; 
            $s_answer_label = $a_answers['answer'];  
            $i_answer_correct_answer = $a_answers['correct']; 
            $i_answer_position = $a_answers['position']; 

            $sql_user_answer =
                    'SELECT track_e_attempt.answer, answers.answer
						FROM ' . $TBL_TRACK_ATTEMPT . ' as track_e_attempt
						INNER JOIN ' . $table_ans . ' as answers
							ON answers.position = track_e_attempt.answer
							AND track_e_attempt.question_id=answers.question_id
						WHERE answers.correct = 0
						AND track_e_attempt.exe_id = "' . Database::escape_string($exe_id) . '"
						AND track_e_attempt.question_id = "' . Database::escape_string($questionId) . '"
						AND track_e_attempt.position="' . Database::escape_string($i_answer_position) . '"';

            $res_user_answer = api_sql_query($sql_user_answer, __FILE__, __LINE__);
            if (Database::num_rows($res_user_answer) > 0) {
                $s_user_answer = Database::fetch_row($res_user_answer, 0, 0); 
                //$s_user_answer = preg_replace("/<\s*p[^>]*>([^<]*)<\s*\/\s*p\s*>/", "", $s_user_answer);
            } else {
                $s_user_answer = '';
            }

            $s_correct_answer = $real_list[$i_answer_correct_answer];
            $i_answerWeighting = $objAnswerTmp->selectWeighting($i_answer_id);
            //$real_list[$i_answer_correct_answer] = preg_replace("/<\s*p[^>]*>([^<]*)<\s*\/\s*p\s*>/", "", $real_list[$i_answer_correct_answer]);      
            if ($s_user_answer[0] == $real_list[$i_answer_correct_answer]['id']) { 
                $questionScore+=$i_answerWeighting;
                $totalScore+=$i_answerWeighting;
                if ($answer_wrong == 'N') {
                    $answer_ok = 'Y';
                }
            } else {
                $s_user_answer[1] = ($simplifyQuestionsAuthoring == 'true' AND $Showimageright == '0') ? ($s_user_answer[1]) : ('<span style="color: #FF0000; text-decoration: line-through;">' . $s_user_answer[1] . '</span>');
                $answer_wrong = 'Y';
            }
            $s .= '<tr>';            
            $s_answer_label = ($Showimageleft == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$s_answer_label.'" />') : ($s_answer_label);
            $s_user_answer[1] = ($Showimageright == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$s_user_answer[1].'" />') : ($s_user_answer[1]);
            $stardivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
            $enddivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
            $stardivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
            $enddivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
            $final =  ($Showimageright == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$real_list[$i_answer_correct_answer]['answer'].'" />') : ($real_list[$i_answer_correct_answer]['answer']);
            $s .= '<td align="center"><div id="matchresult">' .$stardivleft . $s_answer_label . $enddivleft.'</div></td>
                  <td align="center" ><div id="matchresult">' .$stardivright . $s_user_answer[1] . $enddivright . '</div></td>
                  <td align="center"><div id="matchresult"><b><span>' . $stardivright . $final . $enddivright.'</span></b></div></td>';
            $s .= '</tr>';
        }
        $s .= '</table></div>';

        $s .= '<table style="width:100%; clear:both; padding-top:20px;">';
//        $s .= '<tr ><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr>';
        $s .= '<tr><td colspan="3">';
        if ($answer_ok == 'Y' && $answer_wrong == 'N') {
//          $s .=  '<td colspan="3" style="border-top: none; padding-top: 0px;"><span style="font-weight: bold; color: #008000;">' . $answerComment_true . '</span></td>';
            $s .= '<div class="feedback-right feed-custom-right" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-right"><span>' . $answerComment_true . '</span></div></tr>';
        } else {
//          $s .=  '<td colspan="3" style="border:1px solid red; border-top: none; padding-top: 0px;"><span style="font-weight: bold; color: #FF0000;">' . $answerComment_false . '</span></td>';
            $s .= '<div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">' . get_lang('Feedback') . '</div><div class="feedback-wrong"><span>' . $answerComment_false . '</span></div></tr>';
        }
        $s .= '</tr></td></table>';
    }
    if ($answerType == FREE_ANSWER) {
        $objQuestionTmp = Question::read($questionId);
        $questionWeighting = $objQuestionTmp->selectWeighting();

        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $answerComment_true = $objAnswerTmp->selectComment(1);
        $answerComment_false = $objAnswerTmp->selectComment(2);
        $questionScore = 0;

        if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
            $s .= '<div class="quiz_content_actions" style="width:95%;float:left;">';
        } elseif ($mediaPosition == 'right') {
            $s .= '<div class="quiz_content_actions quit_border" style="width:45%;float:left;height:auto !important;min-height:200px;clear:none;">';
        }
        $s .= '<table align="center" class="feedback_actions" width="70%"><tr><td>&nbsp;</td></tr><tr><td valign="top">' . get_lang("MarksAfterCorrection") . '</td></tr><tr><td>&nbsp;</td></tr></table>';
    }
    echo $s;
    echo '</div>';
}

function display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $ans) {
    if ($answerType == UNIQUE_ANSWER) {
        $img = 'radio';
    } else {
        $img = 'checkbox';
    }
    if ($studentChoice) {
        $your_choice = $img . '_on' . '.gif';
    } else {
        $your_choice = $img . '_off' . '.gif';
    }

    if ($answerCorrect) {
        $expected_choice = $img . '_on' . '.gif';
    } else {
        $expected_choice = $img . '_off' . '.gif';
    }

    $s .= '
	<tr>
	<td width="5%" align="center">
		<img src="../img/' . $your_choice . '"
		border="0" alt="" />
	</td>
	<td width="5%" align="center">
		<img src="../img/' . $expected_choice . '"
		border="0" alt=" " />
	</td>
	<td width="40%" style="border-bottom: 1px solid #4171B5;">' . api_parse_tex($answer) . '	
	</td>		
	</tr>';
    return $s;
}

?>
