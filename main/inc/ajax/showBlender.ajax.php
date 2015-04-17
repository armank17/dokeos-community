<?php
$language_file = array ('course_home', 'widgets', 'chat', 'trad4all');
require_once '../global.inc.php';
if(isset($_GET['action'])){
    $action = Security::remove_XSS($_GET['action']);
    $moduleId = $_GET['moduleId'];
    $TBL_INTRODUCTION = Database::get_course_table(TABLE_TOOL_INTRO);
    switch ($action){
        case 'show_template' :        
            $html_buttons = '<div align="center ">
            <table class="gallery sectiontablet"><tbody>
            <tr><td><a id="activity" >
            <div class="width_tablet_scenario_button height_tablet_scenario_button border_tablet_button" >
                            <div>'.Display::return_icon('pixel.gif',get_lang('Activities'), array('class' => 'toolscenarioactionplaceholdericon toolscenarioactionactivity')).'</div>
                            <div class="tablet_scenario_title">'. get_lang('Activities').'</div>
            </div></a></td>
            <td><a id="social" >
            <div class="width_tablet_scenario_button height_tablet_scenario_button border_tablet_button">
                            <div class="">'.Display::return_icon('pixel.gif',get_lang('Social'), array('class' => 'toolscenarioactionplaceholdericon toolscenarioactionsocial')).'</div>
                            <div class="tablet_scenario_title">'.get_lang('Social').'</div>
                    </div></a></td>
            <td><a id="week">
            <div class="width_tablet_scenario_button height_tablet_scenario_button border_tablet_button" >
                            <div class="">'.Display::return_icon('pixel.gif',get_lang('Progressive'), array('class' => 'toolscenarioactionplaceholdericon toolscenarioactionstep')).'</div>
                            <div class="tablet_scenario_title">'.get_lang('Progressive').'</div>
                    </div></a></td>
            <td><a id="corporate">
            <div class="width_tablet_scenario_button height_tablet_scenario_button border_tablet_button" >
                            <div class="">'.Display::return_icon('pixel.gif',get_lang('Corporate'), array('class' => 'toolscenarioactionplaceholdericon toolscenarioactioncorporate')).'</div>
                            <div class="tablet_scenario_title">'.get_lang('Corporate').'</div>
                    </div></a></td>
            <td><a id="none">
            <div class="width_tablet_scenario_button height_tablet_scenario_button" >
                            <div class="">'.Display::return_icon('pixel.gif',get_lang('NoScenario'), array('class' => 'toolscenarioactionplaceholdericon toolscenarioactionscenario')).'</div>
                            <div class="tablet_scenario_title">'.get_lang('NoScenario').'</div>
                    </div></a></td>
            </tr></tbody>
            </table></div>';

        echo $html_buttons;
        break;   
        case 'save' :
            $moduleId = $_GET['moduleId'];
            
            $document_content = Security::remove_XSS(stripslashes($_POST['document_content']), COURSEMANAGERLOWSECURITY);
           
            if (empty($document_content) ) {
                $document_content = "";
            }
            if (! empty($document_content) ) {
                    $sql = "REPLACE $TBL_INTRODUCTION SET id='$moduleId', intro_text='".Database::escape_string($document_content)."'";
                    Database::query($sql,__FILE__,__LINE__);
        
            }
            $sql = "SELECT intro_text FROM $TBL_INTRODUCTION WHERE id='".$moduleId."'";
            $intro_dbQuery = Database::query($sql,__FILE__,__LINE__);
            $intro_dbResult = Database::fetch_array($intro_dbQuery);
            $document_content = $intro_dbResult['intro_text'];
            
            $document_content = preg_replace('/(?<=<head>|<\/title>)\s*?(?=<\/head>|<title>)/is', '', $document_content);
            $document_content = preg_replace('/(?<=<body>)\s*?(?=<\/body>)/is', '', $document_content);
            $tmp_check_content = preg_replace_callback('~\<[^>]+\>.*\</[^>]+\>~ms','stripNewLines', $document_content); 
            function stripNewLines($match) {
                return str_replace(array("\r", "\n"), '', $match[0]);   
            }
            
            if(empty($tmp_check_content)){
                $html1 .= '<div class="introtext"><a id="blender" href="#">'.get_lang("IntroductionText").'</a></div>';
               
            echo $html1;
                
            }else{
                $html1 .= '<div id="courseintroduction" style="margin-top:25px;" ><div class="scroll_feedback">';                  
                //$html1 .= '<div class="courseintro-sectiontitle">'.Display::return_icon('pixel.gif', utf8_encode(get_lang('Scenario')), array('class'=>'toolactionplaceholdericon toolactionscenario', 'style'=>'vertical-align:middle')).' '.utf8_encode(get_lang('Scenario')).'</div>';
                $html1 .= $tmp_check_content;
                $html1 .= '</div></div>';  
                $html1 .= "<div id='courseintro_icons'>";
                $html1 .= "<a id='delete'  >" . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete', 'style'=>'vertical-align:middle')).' '.get_lang('Delete').' '.get_lang('Scenario'). '</a>' . PHP_EOL;
                $html1 .= "<a id='edit' >".Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit', 'style'=>'vertical-align:middle')).' '.get_lang('Edit').' '.get_lang('Scenario').'</a>' . PHP_EOL;
                $html1 .= "</div>";
                echo $html1;
            }

        break;
        case 'edit':
            $moduleId = $_GET['moduleId'];
            $sql = "SELECT intro_text FROM $TBL_INTRODUCTION WHERE id='".$moduleId."'";
            $intro_dbQuery = Database::query($sql,__FILE__,__LINE__);
            $intro_dbResult = Database::fetch_array($intro_dbQuery);
            $document_content = $intro_dbResult['intro_text'];
            
            include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php'); 
 
            $html_buttons = '
            <table class="gallery sectiontablet"><tbody>
            <tr><td><a id="activity" >
            '.Display::return_icon('pixel.gif',get_lang('Activities'), array('class' => 'toolactionplaceholdericon toolactionsactivity')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Activities').'</span></a></td>
            <td><a id="social" >'.Display::return_icon('pixel.gif',get_lang('Social'), array('class' => 'toolactionplaceholdericon toolactionssocial')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Social').'</span></a></td>
            <td><a id="week">'.Display::return_icon('pixel.gif',get_lang('Progressive'), array('class' => 'toolactionplaceholdericon toolactionstep')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Progressive').'</span></a></td>
            <td><a id="corporate">'.Display::return_icon('pixel.gif',get_lang('Corporate'), array('class' => 'toolactionplaceholdericon toolactionscorporate')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Corporate').'</span></a></td>
            <td><a id="none">'.Display::return_icon('pixel.gif',get_lang('NoScenario'), array('class' => 'toolactionplaceholdericon toolactionsscenario')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('NoScenario').'</span></a></td>
            </tr></tbody>
            </table>';
            $form = new FormValidator('introduction_text','post', "?".api_get_cidreq()."&course_scenario=1");
            $form->addElement('html',$html_buttons);
            $form->addElement('textarea','document_content',null,array('rows' => 3, 'cols' => '115','id'=>'document_content'));
            $form->addElement('style_submit_button', 'submit', get_lang('SaveIntroText'), 'class="save" id="submit"');
            
            $default['document_content'] = $document_content;
            $form->setDefaults($default);

            $form->display();

            
        break;
    
            case 'delete':
            $moduleId = $_GET['moduleId'];
            Database::query("DELETE FROM $TBL_INTRODUCTION WHERE id='".$moduleId."'",__FILE__,__LINE__);
            Display::display_confirmation_message(get_lang('IntroductionTextDeleted'),null,null,null,null,null,false);
            echo '<div class="introtext"><a id="blender" href="#">'.get_lang("IntroductionText").'</a></div>';

        break;
    
    }
}

if(isset($_GET['scenario'])){
    
 $scenario = Security::remove_XSS($_GET['scenario']);
 $moduleId = $_GET['moduleId'];
 include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php'); 
 
 $form = new FormValidator('introduction_text','post', "?".api_get_cidreq()."&course_scenario=1");
 $html_buttons = '
            <table class="gallery sectiontablet"><tbody>
            <tr><td><a id="activity" >
            '.Display::return_icon('pixel.gif',get_lang('Activities'), array('class' => 'toolactionplaceholdericon toolactionsactivity')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Activities').'</span></a></td>
            <td><a id="social" >'.Display::return_icon('pixel.gif',get_lang('Social'), array('class' => 'toolactionplaceholdericon toolactionssocial')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Social').'</span></a></td>
            <td><a id="week">'.Display::return_icon('pixel.gif',get_lang('Progressive'), array('class' => 'toolactionplaceholdericon toolactionstep')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Progressive').'</span></a></td>
            <td><a id="corporate">'.Display::return_icon('pixel.gif',get_lang('Corporate'), array('class' => 'toolactionplaceholdericon toolactionscorporate')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('Corporate').'</span></a></td>
            <td><a id="none">'.Display::return_icon('pixel.gif',get_lang('NoScenario'), array('class' => 'toolactionplaceholdericon toolactionsscenario')).'<span style="padding-left:10px;line-height:50px;">'.get_lang('NoScenario').'</span></a></td>
            </tr></tbody>
            </table>';
 

 $form->addElement('html',$html_buttons);
  $form->addElement('textarea','document_content',null,array('rows' => 3, 'cols' => '115','id'=>'document_content'));
  $form->addElement('style_submit_button', 'submit', get_lang('SaveIntroText'), 'class="save" id="submit"');
 
  ////////////
  if (isset($scenario) && $scenario != 'none' ) {
          
        if ($scenario == 'activity') {
          $image_arrow = Display::return_icon('media_playback_start_32.png',get_lang('Activity'),array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $lang_var1 = get_lang('ActivityOne');
          $lang_var2 = get_lang('ActivityTwo');
          $lang_var3 = get_lang('ActivityThree');
          $lang_var4 = get_lang('ActivityFour');
          $lang_var5 = get_lang('ActivityFive');

          $image1 = Display::return_icon('quiz_64.png', $lang_var1,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('applications_accessories_64.png', $lang_var2,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image3 = Display::return_icon('mouse_64.png', $lang_var3,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image4 = Display::return_icon('accessories-character-map.png', $lang_var4,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image5 = Display::return_icon('miscellaneous.png', $lang_var5,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));

        } elseif ($scenario == 'corporate') {
          $lang_var1 = get_lang('Corporate');
          $image1 = Display::return_icon('trainerleft.png', $lang_var1,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('textright.png', $lang_var1,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));

        } elseif ($scenario == 'social') {
          $image_arrow = Display::return_icon('media_playback_start_32.png', get_lang('Interaction'),array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $lang_var1 = get_lang('InteractionOne');
          $lang_var2 = get_lang('InteractionTwo');
          $lang_var3 = get_lang('InteractionThree');
          $lang_var4 = get_lang('InteractionFour');
          $lang_var5 = get_lang('InteractionFive');

          $image1 = Display::return_icon('group_blue.png', $lang_var1,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('group_orange.png', $lang_var2,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image3 = Display::return_icon('presence_64.png', $lang_var3,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image4 = Display::return_icon('group_red.png', $lang_var4,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image5 = Display::return_icon('group_grey.png', $lang_var5,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));

        } elseif ($scenario == 'week') {
          $image_arrow = Display::return_icon('media_playback_start_32.png', get_lang('Step'),array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $lang_var1 = get_lang('StepOne');
          $lang_var2 = get_lang('StepTwo');
          $lang_var3 = get_lang('StepThree');
          $lang_var4 = get_lang('StepFour');
          $lang_var5 = get_lang('StepFive');

          $image1 = Display::return_icon('step1.png', $lang_var1,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('step2.png', $lang_var2,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image3 = Display::return_icon('step3.png', $lang_var3,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image4 = Display::return_icon('step4.png', $lang_var4,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));
          $image5 = Display::return_icon('step5.png', $lang_var5,array('border' => '0', 'align' => 'middle', 'vspace'=> '0','hspace'=> '0'));

        }
        if ($scenario != 'corporate') {
         $document_content = '<div align="center"><table cellspacing="2" cellpadding="10" border="0" align="center" style="width: 800px; height: 130px;"><tbody>
              <tr>
                  <td style="text-align: center;">'.$image1.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image2.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image3.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image4.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image5.'</td>
              </tr>
              <tr>
                  <td style="text-align: center;">'.$lang_var1.'</td>
                  <td style="text-align: center;"></td>
                  <td style="text-align: center;">'.$lang_var2.'</td>
                  <td style="text-align: center;"></td>
                  <td style="text-align: center;">'.$lang_var3.'</td>
                  <td style="text-align: center;"></td>
                  <td style="text-align: center;">'.$lang_var4.'</td>
                  <td style="text-align: center;"></td>
                  <td style="text-align: center;">'.$lang_var5.'</td>
              </tr>
          </tbody>
        </table>
        </div>';
        } else {
          $document_content = '<div align="center">
          <table width="420" cellspacing="0" cellpadding="0" border="0" align="center">
          <tbody>
              <tr>
                  <td width="356">'.$image1.'</td>
                  <td width="58">'.$image2.'</td>
              </tr>
          </tbody>
        </table>
      </div>';
        }
    } elseif (isset($scenario) && $scenario == 'none' ) {
      $document_content = "";
    }

  $default['document_content'] = $document_content;
  $form->setDefaults($default); 
  $form->display();
}
?>
