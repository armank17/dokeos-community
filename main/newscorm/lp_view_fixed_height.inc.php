<?php
include_once('../inc/reduced_header.inc.php');
//check if audio recorder needs to be in studentview
$course_id=$_SESSION["_course"]["id"];
if ($_SESSION["status"][$course_id]==5) {
    $audio_recorder_studentview = true;
} else {
    $audio_recorder_studentview = false;
}
//set flag to ensure lp_header.php is loaded by this script (flag is unset in lp_header.php)
$_SESSION['loaded_lp_view'] = true;
?>
<body>   
<div align="left"  style="margin-left:auto;margin-right: auto; width:960px;height:650px;position:relative;">
    <!-- New Header Dokeos 2.0-->
    <div id="courseHeader">
    <?php
        // get tocs from learnpath and convert for re-using in toggle menu
        $currentId = $_SESSION['oLP']->current;
        $menuItems = getMenuItemsFromToc($_SESSION['oLP']->get_toc(), $currentId);
        echo renderCourseHeader($nameTools, $_SESSION['oLP']->get_progress_bar_text(), $menuItems, $charset);
    ?>
    </div>
    <!-- Header for navigation in course tool -->
    <div class="actions" align="left">
    <?php
    $return = '';
    $author_lang_var = api_convert_encoding(get_lang('Modules'), $charset, api_get_system_encoding());
    $content_lang_var = api_convert_encoding(get_lang('Content'), $charset, api_get_system_encoding());
    $scenario_lang_var = api_convert_encoding(get_lang('Scenario'), $charset, api_get_system_encoding());
    $Messagelpview_lang_var = api_convert_encoding(get_lang('CourseEmpty'), $charset, api_get_system_encoding());
    // The lp_id parameter will be added by Javascript
    $my_lp_id = intval($_GET['lp_id']);
    if (api_is_allowed_to_edit()) {
         //$return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;lp_id='.$my_lp_id.'">' . Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionback')).$author_lang_var . '</a>';
         $return.= '<a href="'.api_get_path(WEB_CODE_PATH).'newscorm/lp_controller.php?action=course&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionback')).$author_lang_var . '</a>';
         if ($_SESSION['oLP']->type != 3 && $_SESSION['oLP']->type != 2) {
         $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;action=add_item&amp;type=step&amp;lp_id='.$my_lp_id.'">' . Display::return_icon('pixel.gif', $content_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')).$content_lang_var . '</a>';
         $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;gradebook=&amp;action=admin_view&amp;lp_id='.$my_lp_id.'">' .  Display::return_icon('pixel.gif', $scenario_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorscenario')).$scenario_lang_var . '</a>';
         }
         
    }
    /** Audiorecorder */
    if (api_get_setting('audio_recorder') == 'true' && api_is_allowed_to_edit()) {
        if (is_dir(api_get_path(SYS_CODE_PATH).'inc/lib/audiorecorder')) {
             $action = Security::remove_XSS($_GET['action']);
             $lp_id  = intval($_GET['lp_id']);
             $current_item = $_SESSION['oLP']->current;
             $item_info = $_SESSION['oLP']->get_item_info($_SESSION['oLP']->current);

             if ($provider == AudiorecorderFactory::SWFLASH) {
                $extra = array('action'=>$action, 'lp_id'=>$lp_id, 'host'=>$url['host'], 'time_limit'=>$time_limit);
                $dialog = $objAudiorecorder->getDialog($current_item, $item_info['title'], $extra);
                $event_click = !empty($item_info['audio'])?'deleteSoundMp3(' . $lp_id . ', \'' . $action . '\',\'' . api_get_course_id() . '\',' . $current_item . ',\'' . $item_info['audio'] . ' \'  );':'recordDialog('.$current_item.');';
             }
             else if ($provider == AudiorecorderFactory::NANOGONG) {
                $dialog = $objAudiorecorder->getDialog($current_item, $item_info['title']);
                $event_click = !empty($item_info['audio'])?'deleteAudio();':'recordDialog('.$current_item.');';
             }
             // display audiorecorder action
             $return .= '<span id="audio-recorder-action">';
             $return .= '<span id="audio-recorder-icon"><a href="javascript:void(0)" onclick="'.$event_click.'" ><img style="padding-left:22px;" src="../img/' . (!empty($item_info['audio'])?'sound_mp3.png':'record_mp3.png').'">&nbsp;'.(!empty($item_info['audio'])?get_lang('RemoveAudio'):get_lang('AudioRecorderTitle')).'</a></span>';             
             $return .= $dialog;
             $return .= '</span>';
        }
    }
    $mediaplayer = $_SESSION['oLP']->get_mediaplayer(true);    
    echo $return;
    ?>
    </div>
    <div id="content_with_secondary_actions"  style="width:940px;height:100%;" >
        <?php
            $tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
            $sel = "SELECT * FROM $tbl_lp_item where lp_id='".$my_lp_id."'";
            $res = Database::query($sel);
            $nrows = Database::num_rows($res);
            if ($nrows==0) {
                echo'<center><br><a href="lp_controller.php?'.api_get_cidreq().'&amp;gradebook=&amp;action=add_item&amp;type=step&amp;lp_id='.$my_lp_id.'">'.$Messagelpview_lang_var.'</a></center>';
            }
        ?>
        <!-- right Zone -->
        <div id="learning_path_right_zone" style="height:600px;width:100%;float:left;padding-top:20px;">
            <div class="title-resource-course" style="display:none;"><?php echo isset($_SESSION['oLP']->current)?$_SESSION['oLP']->items[$_SESSION['oLP']->current]->get_title():''; ?></div>
            <!-- media player layout -->
            <?php $style_media = 'style="float:right;margin:2px;"'; ?>
            <div id="media"  <?php echo $style_media ?>>
                    <?php echo (!empty($mediaplayer))?$mediaplayer:'&nbsp;' ?>
            </div>
            <!-- end media player layout -->
            <iframe id="content_id" name="content_name" class="course_view" width="100%" height="600px" frameborder="0" ></iframe>
        </div>
        <div id="lp-menu-right-collapsable" style="width:0%;float:left;position:relative;">
            <?php displayCourseToggleMenu($menuItems); ?>
        </div>
        <!-- end right Zone -->
        <?php if (!empty($_SESSION['oLP']->scorm_debug)) {//only show log ?>
        <!-- log message layout -->
            <div id="lp_log_name" name="lp_log_name" class="lp_log" style="height:150px;overflow:auto;margin:4px">
                    <div id="log_content"></div>
                    <div id="log_content_cleaner" style="color: white;">.</div>
            </div>
        <!-- end log message layout -->
         <?php } ?>
    </div>
</div>
<script language="JavaScript" type="text/javascript">
// now we load the content after havin loaded the dom, so that we are sure that scorm_api is loaded
window.onload = function () {
<?php if ($current_item_type == 'certificate') : ?>
        $.ajax({
            type: "POST",
            url: "lp_ajax.php?action=display_certificate&amp;lp_id="+olms.lms_lp_id,
            success: function(data) {
                $('#content_id').contents().find('html').html(data);
            }
         });
<?php else : ?>
    $('#content_id').attr('src','<?php echo addslashes($src); ?>');
<?php endif; ?>
};
</script>
</body>
