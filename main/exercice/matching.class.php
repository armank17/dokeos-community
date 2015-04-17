<?php
/*
  DOKEOS - elearning and course management software

  For a full list of contributors, see documentation/credits.html

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.
  See "documentation/licence.html" more details.

  Contact:
  Dokeos
  Rue des Palais 44 Paleizenstraat
  B-1030 Brussels - Belgium
  Tel. +32 (2) 211 34 56
 */

/**
 * 	File containing the Matching class.
 * 	@package dokeos.exercise
 * 	@author Eric Marguin
 * 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */
if (!class_exists('Matching')) :

 /**
   CLASS Matching
  *
  * 	This class allows to instantiate an object of type MULTIPLE_ANSWER (MULTIPLE CHOICE, MULTIPLE ANSWER),
  * 	extending the class question
  *
  * 	@author Eric Marguin
  * 	@package dokeos.exercise
  * */
class Matching extends Question {

    static $typePicture = 'matching.gif';
    static $explanationLangVar = 'Matching';

    /**
     * Constructor
     */
    function Matching() {
        parent::question();
        $this->type = MATCHING;
    }

    /**
     * function which redifines Question::createAnswersForm
     * @param the formvalidator instance
     */
    function createAnswersForm($form, $my_quiz = null) {
        $simplifymode = 0;
        
        
     
        

        
        if (isset($my_quiz)) {
            $simplifyQuestionsAuthoring = ($my_quiz->selectSimplifymode()== 1) ? 'true' : 'false';
        }
        if($simplifyQuestionsAuthoring){
            echo "<style type='text/css'>
            .yckanswerdiv textarea, .yckoptiondiv textarea{
            font-size: 16px;
            width: 308px!important;
            height:114px!important;
            }
            .yckanswerdiv .formw1, .yckoptiondiv .formw1{
    
            }
            </style>";
        }
            /*
            $dataleft = ($this->selectShowimageleft() <> '') ? ($this->selectShowimageleft()) : 'null';
            $dataright =  ($this->selectShowimageright() <> '') ? $this->selectShowimageright() : 'null';
            */
        if($simplifyQuestionsAuthoring == 'true'){
            
       
         
            echo '<script src="' . api_get_path ( WEB_LIBRARY_PATH ) . 'javascript/iphone-style-checkboxes.js" type="text/javascript" language="javascript"></script>';
            echo '<script src="' . api_get_path ( WEB_LIBRARY_PATH ) . 'javascript/jquery.tools.min.js" type="text/javascript" language="javascript"></script>';
            echo '<script src="'. api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.form.js" language="javascript"></script>';           
            echo '<link rel="stylesheet" href="' . api_get_path ( WEB_LIBRARY_PATH ) . 'javascript/iphone-style-checkboxes.css" type="text/css" media="screen"  />';
            
            echo '<script type="text/javascript" src="' .  api_get_path(WEB_CODE_PATH) . 'application/author/assets/js/uploadify/jquery.uploadfile.min.js"></script>';
            echo '<link href="' .  api_get_path(WEB_CODE_PATH) . 'application/author/assets/js/uploadify/uploadfile.css" rel="stylesheet" >';
            echo '<script src="' .  api_get_path(WEB_PATH) . 'main/document/NiceUpload.js"></script>';
            echo '<script src="' .  api_get_path(WEB_CODE_PATH). 'appcore/library/jquery/jquery.upload/js/vendor/jquery.ui.widget.js"></script>';
            echo '<script src="' .  api_get_path(WEB_CODE_PATH). 'appcore/library/jquery/jquery.upload/js/jquery.iframe-transport.js"></script>';
            echo '<script src="' .  api_get_path(WEB_CODE_PATH). 'appcore/library/jquery/jquery.upload/js/jquery.fileupload.js"></script>';
            echo '<link rel="stylesheet" href="'. api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css">';
            
            
                    echo '
                <style>
                .div_answer_image, .div_option_image{
                    height:140px;
                    width:100%;
                    
                    float:left;
                    border: 1px solid #D8DAD8;
                    text-align:center;
                }
                .nckoptiondiv img, .nckanswerdiv img{                    
                    padding:0px!important;
                }
                #leftdiv, #rightdiv{
                   padding-top:10px;
                    width:101px;
                    margin:auto;
                }
                #leftdiv .label, #rightdiv .label{
                    width: 0 !important;
                }
                #leftdiv .iPhoneCheckContainer, #rightdiv .iPhoneCheckContainer{
                    margin-top: 0px;
                    clear:none;
                }
                #leftdiv .formw, #rightdiv .formw{
                    width: auto!important;
                }
                #leftdiv .formw1, #rightdiv .formw1{
                    margin: 0px;
                    width: auto!important;
                }
                #leftdiv .formw1 input, #rightdiv .formw1 input{
                    margin: 0px!important;
                    width: auto!important;
                }
                #table_match_left .formw .label{
                    width: 0 !important;
                }
                #left_buttons .formw, #right_buttons .formw{
                    display:none!important;
                }
                .iPhoneCheckContainer{
                    clear:both;
                }
                .iPhoneCheckContainer label{ 
                    text-transform: none ;
                }
                .iPhoneCheckHandleCenter{
                    text-transform: none ;

                }
                #content{
                    background: -moz-linear-gradient(center top , #FFFFFF, #FFFFFF) repeat scroll 0 0 transparent !important;
                    background: -webkit-gradient(linear,left top, left bottom, from(#ffffff), to(#FFFFFF))!important;
                    background: -ms-linear-gradient(top, #FFFFFF, #FFFFFF)!important;
                    background: -o-linear-gradient(top, #FFFFFF, #FFFFFF)!important;
                }
                </style>
                <script language="Javascript" src="http://www.codehelper.io/api/ips/?js"></script>
                <script type="text/javascript">
                $(document).ready(function() {
                

                
                $(".display_option_left").iphoneStyle({ 
                    checkedLabel: "' . get_lang('Text') . '",
                    uncheckedLabel: "' . get_lang('Image') . '"
                });

                $(".display_option_right").iphoneStyle({ 
                    checkedLabel: "' . get_lang('Text') . '",
                    uncheckedLabel: "' . get_lang('Image') . '"
                });

               
                   
//                $(".iPhoneCheckHandleCenter").css({ "text-align": "right" });
                
                    $("#leftdiv .iPhoneCheckContainer").click(function(){
                    
                        if($(".display_option_left").is(":checked")){
                            $("#leftdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");
                            $(".yckanswerdiv").show(); 
                            $(".nckanswerdiv").hide(); 
                            $(".display_option_left").val(1);
                            
                        }else{                            
                            $("#leftdiv .iPhoneCheckHandleCenter").text("' . get_lang('Text') . '");
                            $(".nckanswerdiv").show(); 
                            $(".yckanswerdiv").hide();     
                            $(".display_option_left").val(0);
                        }
                    });
                    


                    
                    $("#rightdiv .iPhoneCheckContainer").click(function(){
                    
                        if($(".display_option_right").is(":checked")){                               
                            $("#rightdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");
                            $(".yckoptiondiv").show();                           
                            $(".nckoptiondiv").hide();
                            $(".display_option_right").val(1);
                        }else{                           
                            $("#rightdiv .iPhoneCheckHandleCenter").text("' . get_lang('Text') . '");
                            $(".nckoptiondiv").show();
                            $(".yckoptiondiv").hide();    
                            $(".display_option_right").val(0);                            
                        }
                    });

                    
                                    
                    //upload file in to options
                    $("#table_match_left input").live("change",function(){                    
                        var classfile = $(this).attr("class");                        
                        var classfile2 = classfile.split("-");
                        var file_id = classfile2[1];
                        
                        $("#"+classfile).ajaxForm({
                        type: "POST",
                        url: "'. api_get_path(WEB_AJAX_PATH) . 'quizupload.ajax.php?action=matching&classname="+classfile,
                        beforeSend: function() {
                        },
                        uploadProgress: function(event, position, total, percentComplete) {
                        },
                        success: function(data) {
                            
                            //$( "div."+classfile+" #"+classfile).remove();
                            $(".answer_image_"+file_id +" img").remove();
                            $(".answer_image_"+file_id).append( "<img src=\'"+data+"\' />" );
                            //$( ".divfileanswer"+file_id ).append( "<img src=\'"+data+"\' />" );
                            $(".answer_"+file_id).val(data);
                        }
                        }).submit();   
                        
                        
                    });
            

                    });

                </script>
            ';
        }
        $mbfr = 'margin-bottom:42px;float: right;';
        $mbfl = 'margin-bottom:42px;float: left;';
        global $charset;
        $defaults = array();
        $navigator_info = api_get_navigator();

        if (isset($_POST['formsize'])) {
            $formsize = $_POST['formsize'];
        } else {
            $formsize = '';
        }

        if (empty($formsize) || $formsize == 'Low') {
            $formsize_px = "70px";
        } else {
            $formsize_px = "150px";
        }
        $nb_matches = 2;
        $nb_options = 2;        
        if ($form->isSubmitted()) {            
            $nb_matches = $form->getSubmitValue('nb_matches');
            $nb_options = $form->getSubmitValue('nb_options');
            if (isset($_POST['lessMatches']))
                $nb_matches--;
            if (isset($_POST['moreMatches']))
                $nb_matches++;
            if (isset($_POST['lessOptions']))
                $nb_options--;
            if (isset($_POST['moreOptions']))
                $nb_options++;

            if($simplifyQuestionsAuthoring == 'true'){                
                $v_text_left = ($form->getSubmitValue('display_option_left') == 1) ? 'style="display:inline;'.$mbfl.'"'   : 'style="display:none;'.$mbfl.'"';
                $v_file_left = ($form->getSubmitValue('display_option_left') == 1) ? 'style="display:none;"'   : 'style="display:inline;"';

                $v_text_right = ($form->getSubmitValue('display_option_right') == 1) ? 'style="display:inline;'.$mbfr.'"'   : 'style="display:none;'.$mbfr.'"';
                $v_file_right = ($form->getSubmitValue('display_option_right') == 1) ? 'style="display:none;"'   : 'style="display:inline;"';  
            }
            
            
            if (!empty($this->id)) {                               
                $answer = new Answer($this->id);
                $answer->read();
                for ($i = 1; $i <= $nb_options; $i++) {
                    if (($i == $nb_options)) {
                        $defaults['option[' . $i . ']'] = '';
                    } else {
                        $defaults['option[' . $i . ']'] = $answer->selectAnswer($i);
                        $defaults['comment[' . $i . ']'] = $answer->comment[$i];
                    }
                }
                ////
                if($simplifyQuestionsAuthoring == 'true'){
                 
                    if (count($answer->nbrAnswers) > 0) {
                        
                        $nb_matches_temp = $nb_options_temp = 0;
                        for ($i = 1; $i <= $answer->nbrAnswers; $i++) {
                            if ($answer->isCorrect($i)) {
                                $nb_matches_temp++;                        
                                echo '<script>                                
                                        $(document).ready(function() {                                     
                                            $(".answer_image_'.$nb_matches_temp.'").append("<img id=\"img_answer_image_'.$nb_matches_temp.'\" src=\"'.$answer->selectAnswer($i).'\" />");                   
                                        });
                                    </script>';
                            } else {
                                $nb_options_temp++;                        
                                echo '<script>
                                        $(document).ready(function() {                                    
                                            $(".option_image_'.$nb_options_temp.'").append("<img id=\"img_option_image_'.$nb_options_temp.'\" src=\"'.$answer->selectAnswer($i).'\" />");                   
                                        });
                                    </script>';
                            }
                        }
                        $value_left = ($form->getSubmitValue('display_option_left')!= null) ? $form->getSubmitValue('display_option_left') : 1;
                        $value_right = ($form->getSubmitValue('display_option_right')!= null) ? $form->getSubmitValue('display_option_right') : 0;
                        echo '<script>
                            $(document).ready(function() {        
                                $(".display_option_left").val("'.$value_left.'");
                                $(".display_option_right").val("'.$value_right.'");                   
                            });
                        </script>';
                    }
                    ///
                }
            }else{
                if($simplifyQuestionsAuthoring == 'true'){
                
           

                    $value_left = ($form->getSubmitValue('display_option_left')!= null) ? $form->getSubmitValue('display_option_left') : 1;
                    $value_right = ($form->getSubmitValue('display_option_right')!= null) ? $form->getSubmitValue('display_option_right') : 0;
                    echo '<script>
                        $(document).ready(function() {        
                            $(".display_option_left").val("'.$value_left.'");
                            $(".display_option_right").val("'.$value_right.'");                   
                        });
                    </script>';
                    ////
                    $array_answer = $form->getSubmitValue('answer');
                    $array_option = $form->getSubmitValue('option');

                    foreach($array_answer as $id_answer => $value_answer){
                        if(!empty($value_answer)){
                            echo '<script>                                
                            $(document).ready(function() {      

                                $(".answer_image_'.$id_answer.'").append("<img id=\"img_answer_image_'.$id_answer.'\" src=\"'.$value_answer.'\" />");                   
                            });
                        </script>';
                        }

                    }
                    foreach($array_option as $id_option => $value_option){
                        if(!empty($value_option)){
                            echo '<script>                                
                            $(document).ready(function() {                                     
                                $(".option_image_'.$id_option.'").append("<img id=\"img_option_image_'.$id_option.'\" src=\"'.$value_option.'\" />");                   
                            });
                        </script>';
                        }

                    }

                    ///
                }
            }
            if($simplifyQuestionsAuthoring == 'true'){
                echo '<script>
                        $(document).ready(function() {                    
                            if($(".display_option_left").is(":checked")){
                                $("#leftdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");                           
                            }else{                            
                                $("#leftdiv .iPhoneCheckHandleCenter").text("' . get_lang('Text') . '");                           
                            }                        
                            if($(".display_option_right").is(":checked")){
                                $("#rightdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");                           
                            }else{                            
                                $("#rightdiv .iPhoneCheckHandleCenter").text("' . get_lang('Text') . '");                           
                            }
    //                       
                        });
                    </script>';
            }
            
        } else if (!empty($this->id)) {
            $answer = new Answer($this->id);
            $answer->read();
            if($simplifyQuestionsAuthoring == 'true'){                    
                
                //edit matching
                $defaults['display_option_left'] = $this->selectShowimageleft();
                $defaults['display_option_right'] = $this->selectShowimageright();
                                
                $v_text_left = ($this->selectShowimageleft() == 1) ? 'style="display:inline;'.$mbfl.'"'   : 'style="display:none;'.$mbfl.'"';
                $v_file_left = ($this->selectShowimageleft() == 1) ? 'style="display:none;"'   : 'style="display:inline;"';
                               
                $v_text_right = ($this->selectShowimageright() == 1) ? 'style="display:inline;'.$mbfr.'"'   : 'style="display:none;'.$mbfr.'"';
                $v_file_right = ($this->selectShowimageright() == 1) ? 'style="display:none;"'   : 'style="display:inline;"';  
                
                $dataleft  =  $this->selectShowimageleft();
                $dataright =  $this->selectShowimageright();
                
                echo '<script>
                    $(document).ready(function() { 
                    
                        if($(".display_option_left").is(":checked")){
                            $("#leftdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");                           
                        }else{                            
                            $("#leftdiv .iPhoneCheckHandleCenter").text("' . get_lang('Text') . '");                           
                        }                        
                        if($(".display_option_right").is(":checked")){
                            $("#rightdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");                           
                        }else{                            
                            $("#rightdiv .iPhoneCheckHandleCenter").text("' . get_lang('Text') . '");                           
                        }
                                                
                        $(".display_option_left").val("'.$dataleft.'");
                        $(".display_option_right").val("'.$dataright.'");
                            
                    });    
                </script>';
            }  
                if (count($answer->nbrAnswers) > 0) {
                    $a_matches = $a_options = array();
                    $nb_matches = $nb_options = 0;
                    for ($i = 1; $i <= $answer->nbrAnswers; $i++) {
                        if ($answer->isCorrect($i)) {
                            $nb_matches++;
                            $defaults['answer[' . $nb_matches . ']'] = $answer->selectAnswer($i);
                            $defaults['weighting[' . $nb_matches . ']'] = float_format($answer->selectWeighting($i), 1);
                            $defaults['matches[' . $nb_matches . ']'] = $answer->correct[$i];
                            if($simplifyQuestionsAuthoring == 'true'){
                                echo '<script>                                
                                        $(document).ready(function() {                                     
                                            $(".answer_image_'.$nb_matches.'").append("<img id=\"img_answer_image_'.$nb_matches.'\" src=\"'.$answer->selectAnswer($i).'\" />");                   
                                        });
                                    </script>';
                            }
                        } else {
                            $nb_options++;
                            $defaults['option[' . $nb_options . ']'] = $answer->selectAnswer($i);
                            $defaults['comment[' . $nb_options . ']'] = $answer->comment[$i];
                            if($simplifyQuestionsAuthoring == 'true'){
                                echo '<script>
                                        $(document).ready(function() {                                    
                                            $(".option_image_'.$nb_options.'").append("<img id=\"img_option_image_'.$nb_options.'\"  src=\"'.$answer->selectAnswer($i).'\" />");                   
                                        });
                                    </script>';
                            }
                        }
                    }
                }                
            
         
            
        }else{
                                  
            if($simplifyQuestionsAuthoring == 'true'){ 
                //new matching, set mode content 
                $defaults['display_option_left']  = 1;
                $defaults['display_option_right'] = 1;
                
                $v_text_left =  'style="display:inline;'.$mbfl.'"' ;
                $v_file_left =  'style="display:none;"'  ;
                               
                $v_text_right =  'style="display:inline;'.$mbfr.'"';
                $v_file_right =  'style="display:none;"'; 
                
                $dataleft  = 1;
                $dataright = 0;
               
                echo '<script>
                    $(document).ready(function() {                    
                                         
                        $("#leftdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");
                        $("#rightdiv .iPhoneCheckHandleCenter").text("' . get_lang('Image') . '");
                    });
                </script>';
            }
        }
        
        $a_matches = array();
        for ($i = 1; $i <= $nb_options; ++$i) {
            $a_matches[$i] = chr(64 + $i) . ' ' . get_lang('MatchesTo') . ' ';  // fill the array with A, B, C.....
        }

        $form->addElement('hidden', 'nb_matches', $nb_matches);
        $form->addElement('hidden', 'nb_options', $nb_options);
        $form->addElement('hidden', 'submitform');
        $form->addElement('hidden', 'questiontype','6');
        $form->addElement('hidden', 'formsize');

        ////////////////////////
        // DISPLAY MATCHES ////
        //////////////////////


//    $form->addElement('html', '<div style="float:right;padding-right:25px;"><img src="../img/SmallFormFilled.png" alt="" onclick="lowlineform()" />&nbsp;<img src="../img/BigFormClosed.png" alt="" onclick="highlineform()" /></div>');
        $leftset_lang_var = api_convert_encoding(get_lang('LeftSet'), $charset, api_get_system_encoding());
        $matchesto_lang_var = api_convert_encoding(get_lang('MatchesTo'), $charset, api_get_system_encoding());
        $rightset_lang_var = api_convert_encoding(get_lang('RightSet'), $charset, api_get_system_encoding());

        // Main container
        $form->addElement('html', '<div style="float:left;width:100%">');

        $form->addElement('html', '<table width="100%" class="data_table">
                    <tr style="text-align: center" class="row_odd">
                        <th width="5%" >&nbsp;</hd>
                        <th width="35%" >' . $leftset_lang_var );
        if($simplifyQuestionsAuthoring == 'true'){    
            $form->addElement('html','<div id="leftdiv">');
            $form->addElement('checkbox', 'display_option_left', null, null, array('class'=>'display_option_left')); 
            $form->addElement('html','</div>');
        }
        $form->addElement('html','</th>
                        <th width="20%" >' . $matchesto_lang_var . '</th>
                        <th width="35%" >' . $rightset_lang_var);
        if($simplifyQuestionsAuthoring == 'true'){   
            $form->addElement('html','<div id="rightdiv">');
            $form->addElement('checkbox', 'display_option_right', null, null, array('class'=>'display_option_right'));   
            $form->addElement('html','<div>');
        }
        $form->addElement('html', '</th>
                        <th width="5%" >&nbsp;</hd>
                    </tr>
                </table>
                <table width="100%" class="data_table">');
        $form->addElement('html', '<tr class="row_odd"><td valign="top">&nbsp');
        // Match to column

        $form->addElement('html', '</td><td valign="top">');
        $form->addElement('html', '<div><table width="100%" id="table_match_left">');
        
        
        
        $tmp_matches = array();
        $tmp_matches = $a_matches;
        for ($i = 1; $i <= $nb_matches; ++$i) {
            $form->addElement('html', '<tr style="height:87px;"><td valign="top" style="padding-top:10px;">'.$i.'</td><td style="height:43px;">');
            
           
            if($simplifyQuestionsAuthoring == 'true'){
               
                $form->addElement('html', '<div class="yckanswerdiv" '.$v_text_left.'>');   
                $form->addElement('textarea', 'answer[' . $i . ']', false,array('class'=>'answer_'.$i,'id'=>'answer_'.$i,'rows' => 7, 'cols' => 30, 'placeholder'=> get_lang("TypeHere")));
                $form->addElement('html', '</div>');  
               
                $form->addElement('html', '<div id="answer_image_'.$i.'" class="answer_image_'.$i.' div_answer_image nckanswerdiv"  '.$v_file_left.'></div><div class="nckanswerdiv divfileanswer'.$i.' answer_file-'.$i.'" '.$v_file_left.' >');   
                
                
                $form->addElement('html', '</div>');   
                
            }else{
                
                $form->add_html_editor('answer[' . $i . ']','', false, false, array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '350px', 'Height' => ''.$formsize_px.''));
                
            }
            

            $form->addElement('html', '</td>');
            $group = array();
            $form->addElement('html', '<td valign="top" style="padding-top:5px;"><div id="div_'.$i.'">');
            for ($k = 1; $k <= $nb_options; $k++) {
                $tmp_matches[$k] = $tmp_matches[$k] . $i;
                
            }
            $group[] = FormValidator :: createElement('select', 'matches[' . $i . ']', null, $tmp_matches, 'id="matches[' . $i . ']""');
            $form->addGroup($group, null, null, '</div></td>');
            $tmp_matches = array();
            $tmp_matches = $a_matches;
            $form->addRule('answer['.$i.']', get_lang('ThisFieldIsRequired'), 'required');
        }
        //$form->addElement('html', '<tr><td>');

        //$form->addElement('html', '</td></tr></table></div>');
        $form->addElement('html', '</table></div>');


        $form->addElement('html', '</td><td valign="top">');
        // Match to column
        $form->addElement('html', '<div>');


        $form->addElement('html', '</div>');

        // End Match to column
        $form->addElement('html', '</td><td valign="top">');

        
        $form->addElement('html', '<div style="text-align:left"><table id="table_match_right" width="90%" align="right" border="0">');
                
        for ($i = 1; $i <= $nb_options; ++$i) {
            $form->addElement('html', '<tr style="height:87px;"><td style="text-align:right;">');
            if($simplifyQuestionsAuthoring == 'true'){
                                    
                 $form->addElement('html', '<div class="yckoptiondiv" '.$v_text_right.'>');   
                 $form->addElement('textarea', 'option[' . $i . ']', false,array('class'=>'option_'.$i,'id'=>'option_'.$i,'rows' => 7, 'cols' => 30, 'placeholder'=> get_lang("TypeHere")));
                 $form->addElement('html', '</div>');
                 
                 $form->addElement('html', '<div id="option_image_'.$i.'" class="option_image_'.$i.' div_option_image nckoptiondiv"  '.$v_file_right.'></div><div class="nckoptiondiv divfileoption'.$i.' option_file-'.$i.'" '.$v_file_right.'>');   
                 
                 $form->addElement('html', '</div>'); 
                
            }else{
                $form->add_html_editor('option[' . $i . ']','', false, false, array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '350px', 'Height' => ''.$formsize_px.''));
            }          
            $form->addRule('option['.$i.']', get_lang('ThisFieldIsRequired'), 'required');
            $form->addElement('html', '</td><td valign="top" style="padding-top:10px;">'.chr(64 + $i).'</td></tr>');
        }
        
        $form->addElement('html', '</table></div></td><td valign="top">');

        $form->addElement('html', '</td></tr><tr>');

        $form->addElement('html', '<td>&nbsp;</td>');
        $mrlb = ($simplifyQuestionsAuthoring == 'true') ? '180px' : '160px';
        $form->addElement('html', '<td id="left_buttons" style="float: right;margin-right: '.$mrlb.';">');

        $group = array();
        if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] >= '6')) {
            $form->addElement('html', '<div id="left_less" name="lessAnswers" class="button_less" onclick="removeAnswerMatch(\'left\')" style="float:left;"></div>');
            $form->addElement('html', '<div id="left_more" name="moreAnswers" class="button_more" onclick="addNewAnswerMatch('.MATCHING.',\'left\')" style="float:left;"></div>');
        } else {
            $form->addElement('html', '<div id="left_less" name="lessAnswers" class="button_less" onclick="removeAnswerMatch(\'left\')" style="float:left;"></div>');
            $form->addElement('html', '<div id="left_more" name="moreAnswers" class="button_more" onclick="addNewAnswerMatch('.MATCHING.',\'left\')" style="float:left;"></div>');
        }
        $form->addGroup($group);
        $form->addElement('html', '</td>');

        $form->addElement('html', '<td>&nbsp;</td>');
        $mrrb = ($simplifyQuestionsAuthoring == 'true') ? '0px' : '25px';
        $form->addElement('html', '<td id="right_buttons" style="float: right;margin-right: '.$mrrb.';">');

        $group = array();

        if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] >= '6')) {
            $form->addElement('html', '<div id="right_less" name="lessAnswers" class="button_less" onclick="removeAnswerMatch(\'right\')" style="float:left;"></div>');
            $form->addElement('html', '<div id="right_more" name="moreAnswers" class="button_more" onclick="addNewAnswerMatch('.MATCHING.', \'right\')" style="float:left;"></div>');
        } else {
            $form->addElement('html', '<div id="right_less" name="lessAnswers" class="button_less" onclick="removeAnswerMatch(\'right\')" style="float:left;"></div>');
            $form->addElement('html', '<div id="right_more" name="moreAnswers" class="button_more" onclick="addNewAnswerMatch('.MATCHING.',\'right\')" style="float:left;"></div>');
        }
        $form->addGroup($group);
        $form->addElement('html', '</td>');

        $form->addElement('html', '<td>&nbsp;</td>');

        $form->addElement('html', '</tr></table>');



        $group = array();
        // global $text, $class;

        if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] == '6' || $navigator_info['version'] == '7')) {
            // setting the save button here and not in the question class.php
            $group[] = FormValidator :: createElement('style_submit_button', 'submitQuestion', get_lang('Validate'), 'class="save"');
        } else {
            // setting the save button here and not in the question class.php
            $group[] = FormValidator :: createElement('style_submit_button', 'submitQuestion', get_lang('Validate'), 'class="save submitQuestion" type="button"');
        }

        // End main container
        $form->addElement('html', '</div>');

        // Feedback container
        $form->addElement('html', '<table width="100%"><tr><td>');
        $form->addElement('html', '<div style="float:left;width:50%;">' . get_lang('FeedbackIfTrue'));
        if($simplifyQuestionsAuthoring == 'true'){
            $form->addElement('textarea', 'comment[1]', false,array('rows' => 3, 'cols' => 50, 'placeholder'=> get_lang("TypeHere")));
        }else{
            $form->add_html_editor('comment[1]','', false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '400px', 'Height' => ''.$formsize_px.''));
        }
        
        $form->addElement('html', '</div></td><td>');

        $form->addElement('html', '<div style="float:right;text-align:right">');
        $form->addElement('html', '<div style="float:left;text-align:left">' . get_lang('FeedbackIfFalse'));
        if($simplifyQuestionsAuthoring == 'true'){
            $form->addElement('textarea', 'comment[2]', false,array('rows' => 3, 'cols' => 50, 'placeholder'=> get_lang("TypeHere")));
        }else{
            $form->add_html_editor('comment[2]','', false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '400px', 'Height' => ''.$formsize_px.''));
        }
        
        $form->addElement('html', '</div></td></tr></table>');
        $form->addElement('html', '<div style="float:right;text-align:left">');
        $form->addGroup($group);
        $form->addElement('html', '</div>');

        $form->setDefaults($defaults);
        $form->setConstants(array('nb_matches' => $nb_matches, 'nb_options' => $nb_options));
    }

    /**
     * abstract function which creates the form to create / edit the answers of the question
     * @param the formvalidator instance
     */
    function processAnswersCreation($form) {

        $_SESSION['editQn'] = '1';
        $nb_matches = $form->getSubmitValue('nb_matches');
        $nb_options = $form->getSubmitValue('nb_options');
        $this->weighting = 0;
        $objAnswer = new Answer($this->id);

        $position = 0;

        // Score for the correct answers
        $answer_score = $form->getSubmitValue('scoreQuestions');

        // insert the options
        for ($i = 1; $i <= $nb_options; ++$i) {
            $position++;
            $option = $form->getSubmitValue('option[' . $i . ']');
            $comment = $form->getSubmitValue('comment[' . $i . ']');
            $objAnswer->createAnswer($option, 0, $comment, 0, $position);
        }

        $real_score = 0;
        $real_score = ($answer_score/$nb_matches);
        // insert the answers
        for ($i = 1; $i <= $nb_matches; ++$i) {
            $position++;
            $answer = $form->getSubmitValue('answer[' . $i . ']');
            $matches = $form->getSubmitValue('matches[' . $i . ']');
            //$weighting = $form->getSubmitValue('weighting[' . $i . ']');
            $objAnswer->createAnswer($answer, $matches, '', $real_score, $position);
        }

         $this->weighting = $answer_score;

        $objAnswer->save();
        $this->save();
    }

    /**
     * Display the question in tracking mode (use templates in tracking/questions_templates)
     * @param $nbAttemptsInExercise the number of users who answered the quiz
     */
    function displayTracking($question_id, $nbAttemptsInExercise) {
        if (!class_exists('Answer'))
            require_once(api_get_path(SYS_CODE_PATH) . 'exercice/answer.class.php');

        $stats = $this->getAverageStats($question_id, $nbAttemptsInExercise);
        include(api_get_path(SYS_CODE_PATH) . 'exercice/tracking/questions_templates/matching.page');
    }

    /**
     * Returns learners choices for each question in percents
     * @param $nbAttemptsInExercise the number of users who answered the quiz
     * @return array the percents
     */
    function getAverageStats($question_id, $nbAttemptsInExercise) {
        $sql = "SELECT `correct` , `position`
        FROM " . Database :: get_course_table(TABLE_QUIZ_ANSWER) . "
        WHERE `question_id` = '" . Database::escape_string($question_id) . "'
        AND `correct` <> '0';";
        
        $result = Database::query($sql, __FILE__, __LINE__);
        $answers = array();
        
        while($answer = Database::fetch_array($result, "ASSOC")) {
            $answers[$answer['position']] = $answer['correct'];
        }

        $sql = "SELECT `exe_id`, `answer` , `position` 
        FROM " . Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT) . " 
        WHERE `question_id` = '" . Database::escape_string($question_id) . "' 
        AND `answer` <> '' 
        ORDER BY `exe_id`;";
        
        $result = Database::query($sql, __FILE__, __LINE__);
        $attempts = array();
        $count = 0;
        $total_attempts = 0;
        $correct_attempt = 0;
        $all_answers = count($answers);
        $arr = array();

        while($attempt = Database::fetch_array($result, "ASSOC")) {
            $attempts[] = ($answers[$attempt['position']] == $attempt['answer']) ? 1 : 0;
            if ($count == ($all_answers - 1)) {
                
                $sum_attempt = array_sum($attempts);
                if ($sum_attempt == $all_answers) $correct_attempt++;
                $total_attempts++;
                $attempts = array();
                $count = 0;
            } else {
                $count++;
            }
        }
        $stats['correct'] = array();
        $stats['correct']['total'] = $correct_attempt;
        if($nbAttemptsInExercise != 0) {
            $stats['correct']['average'] = ($stats['correct']['total'] / $nbAttemptsInExercise) * 100;
        } else {
            $stats['correct']['average'] = 0;
        }

        $stats['wrong'] = array();
        $stats['wrong']['total'] = $nbAttemptsInExercise - $stats['correct']['total'];
        if($nbAttemptsInExercise != 0) {
            $stats['wrong']['average'] = ($stats['wrong']['total'] / $nbAttemptsInExercise) * 100;
        } else {
            $stats['wrong']['average'] = 0;
        }

        return $stats;
    }
    
    public function getHtmlQuestionResult($objQuestion, $attemptId, &$totalScore, &$totalWeighting, $dbName = '') {
        $feedback_if_true = $feedback_if_false = '';
        
        $questionId = $objQuestion->selectId();
        
        $objAnswerTmp = new Answer($questionId, $dbName);
        $answerComment_true = $objAnswerTmp->selectComment(1);
        $answerComment_false = $objAnswerTmp->selectComment(2);
        if ($answerComment_true ==''){
            $answerComment_true = get_lang('NoTrainerComment');                    
        }
        if($answerComment_false==''){
            $answerComment_false = get_lang('NoTrainerComment');
        }

        $questionScore = 0;
        $questionWeighting = $objQuestion->selectWeighting();
        $totalWeighting += $questionWeighting;
        $table_ans = Database :: get_course_table(TABLE_QUIZ_ANSWER, $dbName);
        $tbl_track_attempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
        $answer_ok = 'N';
        $answer_wrong = 'N';
        $sql_select_answer = 'SELECT id, answer, correct, position, ponderation FROM '.$table_ans.' WHERE question_id= "'.intval($questionId).'" AND correct<>0 ORDER BY position';
        $sql_answer = 'SELECT position, answer FROM '.$table_ans.' WHERE question_id= "'.intval($questionId).'" AND correct=0 ORDER BY position';
        $res_answer = Database::query($sql_answer);
        
        // getting the real answer
        $real_list = array();
        while ($real_answer = Database::fetch_array($res_answer)) {
            $real_list[$real_answer['position']] = $real_answer['answer'];
        }
        $res_answers = Database::query($sql_select_answer);
        $s .= '<table cellspacing="0" cellpadding="0" align="center" class="feedback_actions fa_2">';
        $s .= '<thead>';
        $s .= '<tr>
                        <th align="center" width="30%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("ElementList") . '</span> </th>
                        <th align="center" width="35%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("YourAnswers") . '</span></th>
                        <th align="center" width="35%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("Correct") . '</span></th>
                    </tr>';
        $s .= '</thead>';

        while ($a_answers = Database::fetch_array($res_answers)) {
            $i_answer_id = $a_answers['id']; //3
            $s_answer_label = $a_answers['answer'];  // your dady - you mother
            $i_answer_correct_answer = $a_answers['correct']; //1 - 2
            $i_answer_position = $a_answers['position']; // 3 - 4

            $sql_user_answer = 'SELECT answers.answer
                FROM ' . $tbl_track_attempt . ' as track_e_attempt
                INNER JOIN ' . $table_ans . ' as answers
                    ON answers.position = track_e_attempt.answer
                    AND track_e_attempt.question_id=answers.question_id
                WHERE answers.correct = 0
                AND track_e_attempt.exe_id = "' . intval($attemptId) . '"
                AND track_e_attempt.question_id = "' . intval($questionId) . '"
                AND track_e_attempt.position="' . Database::escape_string($i_answer_position) . '"';

            $res_user_answer = api_sql_query($sql_user_answer, __FILE__, __LINE__);
            if (Database::num_rows($res_user_answer) > 0) {
                $s_user_answer = Database::result($res_user_answer, 0, 0); //  rich - good looking
            } else {
                $s_user_answer = '';
            }

            //$s_correct_answer = $s_answer_label; // your ddady - your mother
            $s_correct_answer = $real_list[$i_answer_correct_answer];
            $i_answerWeighting = $a_answers['ponderation']; //$objAnswerTmp->selectWeighting($ind);
            if ($s_user_answer == $real_list[$i_answer_correct_answer]) { // rich == your ddady?? wrong
                $questionScore+=$i_answerWeighting;
                $totalScore+=$i_answerWeighting;
                if ($answer_wrong == 'N') {
                    $answer_ok = 'Y';
                }
            } else {
                $s_user_answer = '<span style="color: #FF0000; text-decoration: line-through;">' . $s_user_answer . '</span>';
                $answer_wrong = 'Y';
            }
            if ($questionScore > 20) {
                $questionScore = round($questionScore);
            }
            $s .= '<tr>';
            $s .= '<td align="center"><div id="matchresult">' . $s_answer_label . '</div></td><td align="center" width="30%"><div id="matchresult">' . $s_user_answer . '</div></td><td align="center"><div id="matchresult"><b><span>' . $s_correct_answer . '</span></b></div></td>';
            $s .= '</tr>';
        }
        
        $s .= '<tfoot><tr>';

        /*if ($answer_ok == 'Y' && $answer_wrong == 'N') {            
            $feedbackDisplay = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #008000;">'.$answerComment_true.'</span>';
        } else {            
            $feedbackDisplay = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #FF0000;">'.$answerComment_false.'</span>';
        }*/
        $s .= '</tr></tfoot></table>';
        
        //$s .= '<div class="span11">'.$feedbackDisplay.'</div>';
        
        if ($questionWeighting - $questionScore < 0.50) {
            $myTotalScore = round(float_format($questionScore, 1));
        } else {
            $myTotalScore = float_format($questionScore, 1);
        }
        $myTotalWeight = float_format($questionWeighting, 1);
        if ($myTotalScore < 0) { $myTotalScore = 0; } 
        
        
        $s .= '<div class="clear"></div><table style="clear:both;width:100%;margin:15px 0;" border="0">';
        if ($answer_ok == 'Y' && $answer_wrong == 'N') {
            $s .=  '<tr ><td colspan="3" style="border:none; padding:2px;"><b><div class="feedback-right feed-custom-right" >' . get_lang('Feedback') . '</div></b></td></tr>';
            $s .=  '<tr><td colspan="3" style="border:none; padding:2px;"><div class="feedback-right">' . $answerComment_true . '</div></td>';
        } else {
            $s .=  '<tr><td colspan="3" style="border:none; padding:2px;"><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr>';
            $s .=  '<tr><td colspan="3" style="border:none; padding:2px;""><div class= "feedback-wrong">' . $answerComment_false . '</div></td>';
        }
        $s .= '</table>';
        
        
        
        $s .= '<div class="span2 quesion-answers-score"><b>'.get_lang('Score').' : </b> '.round($myTotalScore). ' / '.$myTotalWeight.'</div>';
        
        return $s;
    }
    
    public function getHtmlQuestionAnswer($objQuestion, $objExercise, $readonly = false, $examId = 0, $type = '') {
    $exerciseId = $objExercise->selectId();
    $questionId = $objQuestion->selectId();
    $objAnswer = new Answer($objQuestion->selectId());

    $nbrAnswers = $objAnswer->selectNbrAnswers();        
    $mediaPosition = $objQuestion->selectMediaPosition(); 
    $questionDescription = api_parse_tex($objQuestion->selectDescription());
    $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
    $media_w = in_array($mediaPosition, array('top', 'nomedia'))?'width:100%':'width:50%;float:left';

    $exeId   = $objExercise->getLastUserAttemptId($exerciseId, 'incomplete', null, null, null, $examId);
    $attempt = $objExercise->getQuestionAnswersTrackAttempt($exeId, $questionId);
    if (isset($objExercise)) {            
        $simplifyQuestionsAuthoring = ($objExercise->selectSimplifymode()== 1) ? 'true' : 'false';
    }
    if($simplifyQuestionsAuthoring== 'true'){
        $Showimageleft = $objQuestion->selectShowimageleft();
        $Showimageright = $objQuestion->selectShowimageright();

    }
    $cpt1 = 'A';
    $cpt2 = 1;
    $cntOption = 1;
    $Select = array();
    $QA = array();
    $s .= '<div class="span7 quizPart question-answers" style="width:95%">';
    $s .= '<input type="hidden" name="questionid" value="' . $questionId . '">';  
    if (count($nbrAnswers) > 0) {
        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer = $objAnswer->selectAnswer($answerId);
            $answer = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $answer);                    
            $answerCorrect = $objAnswer->isCorrect($answerId);
            if (!$answerCorrect) {
                $cntOption++;
                $answer = api_parse_tex($answer);
                $Select[$answerId]['Reponse'] = $answer;
                $Select[$answerId]['Lettre'] = $answer;
                $field_choice_name = "choice[" . $questionId . "][" . $answerId . "]";
                if($type=='export'){
                    $s .= "<input type='hidden' name='" . $field_choice_name . "' id='" . $field_choice_name . "' value='' />";
                }else{
                    $s .= '<input type="hidden" name="' . $field_choice_name . '" id="' . $field_choice_name . '" value="' . api_htmlentities($answer) . '" />';
                }
            } else {
                $s .= '<input type="hidden" name="choice[' . $questionId . '][' . $answerId . ']" id="choice[' . $questionId . '][' . $answerId . ']" value="0"/>';
                
                $QA[] = api_parse_tex($answer);
                $option = array();
                $option[] = (0);
                foreach ($Select as $key => $val) {
                    $option[] = $val['Lettre'];
                }
                $cpt2++;
            }                    
        }
    }
    $Qdiv_css = "Qdiv";
    $destinationbox_css = "destinationBox";
    $answerDiv_css = "answerDiv";
    $drag_answer_css = "drag_answer";
    if (!$readonly) {
        
    $s .= '<script type="text/javascript">                     
            $(document).ready(function(){ 
                var cidReq = decodeURIComponent($("#cidReq").val());
                var webPath = decodeURIComponent($("#webPath").val());';
    
    $nbDrags = (count($Select)>count($QA))?count($Select):count($QA);
                for ($i = 0; $i <= $nbDrags; $i++) {
                    $ans_i = $i + 1;                            
                    
                    $starimg = ($simplifyQuestionsAuthoring == 'true' AND $Showimageright == 0 ) ? ('<img src=\"'):('');
                    $endimg = ($simplifyQuestionsAuthoring == 'true' AND $Showimageright == 0 ) ? ('\" />'):('');
                    $stardivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style=\"padding:10px;width:auto;font-size:16px;text-align:left;\">' : '';
                    $enddivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
                    
                    if (!empty($attempt[$i])) {
                        $s .= 'selectMatchingAnswer("'.$questionId.'", "'.$attempt[$i].'", "'.$i.'","'.$simplifyQuestionsAuthoring.'","'.$Showimageright.'");';
                    }                            
                    $s .= '                                
                    $("#a' . $questionId . '-' . $ans_i . '").draggable({                        
                        revert:true,	
                        revertDuration: 0.5,
                        helper: "clone",
                        start: function(event, ui){
                        var cloneWidth = $(".matching-drag").width();
                            ui.helper.css("width", cloneWidth-2);
                        } 
                    });
                    $("#q' . $questionId . '-' . $i . '").droppable({
                        drop: function(event, ui) {
                            var cntOption = $("[name=cntOption-' . $questionId . ']").val();	
                            var dragid = ui.draggable.attr("id");		
                            var ansidarr = dragid.split("-");
                            var ansid = ansidarr[1];
                            var dropid = $(this).attr("id");
                            var numericIdarr = dropid.split("-");
                            var numericId = numericIdarr[1];
                            var ansOption = (numericId*1) + (cntOption*1);
                            var answer = document.getElementById("choice['.$questionId.']["+ansid+"]").value;
                            var h = ui.draggable.css("height");
                            
                            $(this).html("<div class=\"drop-answer\" style=\"height:"+h+"\">'.$stardivleft.$starimg.'"+answer+"'.$endimg.$enddivleft.'</div>");
                            document.getElementById("choice['.$questionId.']["+ansOption+"]").value = ansid;
                            saveQuiz(webPath, cidReq, "incomplete");
                        }
                    });  ';
                }
    $s .= ' });                     
          </script>';
    $s .= ' <div id="dragScriptContainer">
                <div id="' . $Qdiv_css . '">
                    <table width="100%">';
            $stardivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
            $enddivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
            for ($i = 0; $i < count($QA); $i++) {
                $matching_drag = ($Showimageleft == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$QA[$i].'" />') : ($QA[$i]);
                $s .= '<tr>
                            <td  width="50%" >
                                <div class="question matching-drag matching-drag-'. $questionId . '" id="c'. $questionId . '-' . $i . '"> '.$stardivleft . $matching_drag . $enddivleft.'</div>
                            </td>
                            <td  width="50%">
                                <div id="q'. $questionId . '-' . $i . '" class="' . $destinationbox_css . ' matching-drag matching-drag-'. $questionId . '"></div>
                            </td>
                       </tr>';
            }
    $s .= '         </table>
                </div>
                <div id="' . $answerDiv_css . '" style="font-face:verdana;">
                    <table width="100%">';
                    $stardivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
                    $enddivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
                    for ($i = 1; $i < count($option); $i++) {
                        if (!empty($option[$i])) {
                            $content_option = ($Showimageright == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$option[$i].'" />') : ($option[$i]);
                            $s .= '<tr><td><div class="'.$drag_answer_css.' matching-drag matching-drag-'. $questionId . '" id="a' . $questionId . '-' . $i . '" style="margin-bottom:1.88px">' .$stardivright . $content_option . $enddivright. '</div></td></tr>';
                        }
                    }
              $s .= '</table>';
         $s .= '</div>
                <div id="dragContent"></div>';
        $s .= ' </div>';
    }
    else {
        $s .= '<table style="width:800px;" border="0"><tr>';
        $s .= '<td style="width:400px;">';
        $s .= '<table style="width: 400px;">';
        for ($i = 0; $i < count($QA); $i++) {
        $s .= '<tr>
                    <td align="center" style="width:200px;">
                        <div class="question" style="text-align:center;border:none;">'. strip_tags($QA[$i], '<a><span><img><sub><sup>') . '</div>
                    </td>
                    <td align="center" style="width:200px;">
                        <div></div>
                    </td>
               </tr>';
        }
        $s .= '</table>';
        $s .= '</td>';
        $s .= '<td style="width:400px;">';
        $s .= '<table style="width:400px;">';
        for ($i = 1; $i < count($option); $i++) {
            if (!empty($option[$i])) {
                $s .= '<tr><td align="center" style="width:200px;"><div class="question" style="text-align:center;border:none;">' . strip_tags($option[$i], '<a><span><img><sub><sup>') . '</div></td></tr>';
            }
        }
        $s .= '</table>';
        $s .= '</td>';
        $s .= '</tr></table>';
    }
    
    $s .= '     <input type="hidden" name="cntOption-' . $questionId . '" value="' . $cntOption . '">';    
    $s .= ' </div>';
    return $s;
  }
}

endif;