<?php
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Dokeos Module. It can be inserted on any
 * Dokeos Module, provided a connection to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction"
 * in the course Database. Each module introduction has an Id stored on
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   intro_text :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
*
*	@package dokeos.include
==============================================================================
*/
include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/
$TBL_INTRODUCTION = Database::get_course_table(TABLE_TOOL_INTRO);
$intro_editAllowed = api_is_allowed_to_edit();

global $charset,$course_home_visibility_type;
/*===========================================
  INTRODUCTION MICRO MODULE - DISPLAY SECTION
  ===========================================*/
/* Retrieves the module introduction text, if exist */

$sql = "SELECT intro_text FROM $TBL_INTRODUCTION WHERE id='".$moduleId."'";
$intro_dbQuery = Database::query($sql,__FILE__,__LINE__);
$intro_dbResult = Database::fetch_array($intro_dbQuery);
$document_content = $intro_dbResult['intro_text'];
/* Executes the display */
if (strpos($document_content, '../../courses/') !== FALSE) {
    $document_content = str_replace('../../courses/', '/courses/', $document_content);
}
    
$document_content = preg_replace('/(?<=<head>|<\/title>)\s*?(?=<\/head>|<title>)/is', '', $document_content);
$document_content = preg_replace('/(?<=<body>)\s*?(?=<\/body>)/is', '', $document_content);
$tmp_check_content = preg_replace_callback('~\<[^>]+\>.*\</[^>]+\>~ms','stripNewLines', $document_content); 
function stripNewLines($match) {
    return str_replace(array("\r", "\n"), '', $match[0]);   
}
if ( (empty($tmp_check_content) AND $intro_editAllowed) OR ($intro_editAllowed AND $tmp_check_content == '&nbsp;')   ) {	
    echo "<div id='courseintro'>";    
    echo '<div class="introtext"><a id="blender" href="#">'.get_lang("IntroductionText").'</a></div>';
    echo '<div class="clear"></div>';   
    echo "</div>";
}

if($tmp_check_content !== '<html><head><title></title></head><body></body></html>' AND $tmp_check_content != '&nbsp;') {
    $document_content = text_filter($document_content); // parse [tex] codes
    if (!empty($document_content) ){
        $mt = $intro_editAllowed ? '25px' : '-5px';
        $mtcss = 'style="margin-top:'.$mt.';"';            
        echo '<div id="courseintro">';
        echo '<div id="courseintroduction" '.$mtcss.' >';
        echo '<div class="scroll_feedback">'; 
        if (api_is_allowed_to_edit()) {  
            //echo '<div class="courseintro-sectiontitle">'.Display::return_icon('pixel.gif', get_lang('Scenario'), array('class'=>'toolactionplaceholdericon toolactionscenario', 'style'=>'vertical-align:middle')).' '.get_lang('Scenario').'</div>';
        }
        if (strpos($document_content, '../../courses/') !== FALSE) {
            $document_content = str_replace('../../courses/', '/courses/', $document_content);
        }
        echo $document_content;
        echo '</div>';
        echo '</div>';  
        show_courseintro_icons($tmp_check_content,$intro_editAllowed);
        echo '</div>';
    }
      
}    
function show_courseintro_icons($tmp_check_content,$intro_editAllowed){
    if($intro_editAllowed){
        if($tmp_check_content !== '<html><head><title></title></head><body></body></html>' AND $tmp_check_content != '&nbsp;') {  
        // displays "edit intro && delete intro" Commands
        echo "<div id='courseintro_icons'>";
        if (!empty ($GLOBALS["_cid"])) {
        echo "<a id='delete' >".Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete', 'style'=>'vertical-align:middle')).' '.get_lang('Delete').' '.get_lang('Scenario').'</a>' . PHP_EOL;
        echo "<a id='edit' >".Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit', 'style'=>'vertical-align:middle')).' '.get_lang('Edit').' '.get_lang('Scenario').'</a>' . PHP_EOL;
        } else {
        echo "<a id='delete'  >" . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete', 'style'=>'vertical-align:middle')).' '.get_lang('Delete').' '.get_lang('Scenario'). '</a>' . PHP_EOL;
        echo "<a id='edit' >".Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit', 'style'=>'vertical-align:middle')).' '.get_lang('Edit').' '.get_lang('Scenario').'</a>' . PHP_EOL;
        }
        echo "</div>";
        }
    }    
}

?>
<script type="text/javascript"	src="<?php echo api_get_path(WEB_PATH) ?>main/inc/lib/ckeditor/ckeditor.js" ></script>
<script src="<?php echo api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js'; ?>" language="javascript"></script>
<script type="text/javascript">
    $(document).ready(function(){        
        $("#edit").live("click",function() {                                                
            $.ajax({
                type: "POST",
                url: "<?php echo api_get_path(WEB_AJAX_PATH) . 'showBlender.ajax.php?'.api_get_cidreq().'&action=edit&moduleId='.$moduleId;?>",               
                beforeSend: function(){
                    $("#courseintro").html("<div style='width:100%;text-align: center;'><span style='font-size:10px'><?php echo get_lang('LoadingEditor'); ?></span><br><img src='<?php echo api_get_path(WEB_CODE_PATH)."img/progress_bar.gif";?>' /></div>");
                },
                success: function(data){
                    $("#courseintro").html(data);
                    CKEDITOR.replace("document_content", {                
                        toolbar: "Introduction",
                        width : "100%",
                        height: "220px"                                
                    });
                }
            });
        });                
        $("#delete").live("click",function() {
            $.ajax({
                type: "POST",
                url: "<?php echo api_get_path(WEB_AJAX_PATH) . 'showBlender.ajax.php?'.api_get_cidreq().'&action=delete&moduleId='.$moduleId;?>",               
                beforeSend: function(){
                    $("#courseintro").html("<div style='width:100%;text-align: center;'><span style='font-size:10px'><?php echo get_lang('Deleting'); ?></span><br><img src='<?php echo api_get_path(WEB_CODE_PATH)."img/progress_bar.gif";?>' /></div>");
                },
                success: function(data){
                    $("#courseintro").html(data);
                    $(".confirmation-message").live("click",function() {
                    $(this).hide();
            });
                }
            });
        });                        
        $("#blender").live("click",function() {
            $.ajax({
                type: "POST",         
                url: "<?php echo api_get_path(WEB_AJAX_PATH) . 'showBlender.ajax.php?'.api_get_cidreq().'&action=show_template&moduleId='.$moduleId;?>",               
                beforeSend: function(){
                    $("#courseintro").html("<div style='width:100%;text-align: center;'><span style='font-size:10px'><?php echo get_lang('Loading'); ?></span><br><img src='<?php echo api_get_path(WEB_CODE_PATH)."img/progress_bar.gif";?>' /></div>");
                },
                success: function(data){
                    $("#courseintro").html(data);  
                }
            }); 
        });                 
        $("#submit").live("click",function() {
            $("#introduction_text").ajaxForm({
                type: "POST",
                url: "<?php echo api_get_path(WEB_AJAX_PATH) . 'showBlender.ajax.php?'.api_get_cidreq().'&action=save&moduleId='.$moduleId; ?>",
                beforeSerialize:function($Form, options) {
                    if (CKEDITOR.instances) {
                        for ( instance in CKEDITOR.instances ) {
                            CKEDITOR.instances[instance].updateElement();
                        }
                    }
                },
                beforeSend: function(){
                    $("#courseintro").html("<div style='width:100%;text-align: center;'><span style='font-size:10px'><?php echo get_lang('SavingDotted'); ?></span><br><img src='<?php echo api_get_path(WEB_CODE_PATH)."img/progress_bar.gif";?>' /></div>");
                },
                target: "#courseintro"                                        
            }).submit();
        });              
        
        $("table.gallery a").live("click",function() {
        var currentID = $(this).attr("id");                
        $.ajax({
                type: "POST",
                url: "<?php echo api_get_path(WEB_AJAX_PATH) . 'showBlender.ajax.php?'.api_get_cidreq().'&moduleId='.$moduleId.'&scenario=';?>"+currentID,               
                beforeSend: function(){
                    $("#courseintro").html("<div style='width:100%;text-align: center;'><span style='font-size:10px'><?php echo get_lang('LoadingEditor'); ?></span><br><img src='<?php echo api_get_path(WEB_CODE_PATH)."img/progress_bar.gif";?>' /></div>");
                },
                success: function(data){
                    $("#courseintro").html(data);                                                                                                
                    CKEDITOR.replace("document_content", {                
                        toolbar: "Introduction",
                        width : "100%",
                        height: "220px"                                
                    });
                }
            });
        });
               
    });
</script> 