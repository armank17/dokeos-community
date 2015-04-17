<?php
$fullscreen = false;
if (isset($_GET['mode']) && $_GET['mode'] == 'full') {
    $fullscreen = true;
}
include_once('../inc/reduced_header.inc.php');
//check if audio recorder needs to be in studentview
$course_id=$_SESSION["_course"]["id"];
if($_SESSION["status"][$course_id] == 5) {
        $audio_recorder_studentview = true;
} else {
        $audio_recorder_studentview = false;
}
//set flag to ensure lp_header.php is loaded by this script (flag is unset in lp_header.php)
$_SESSION['loaded_lp_view'] = true;
?>
<!-- New Header Dokeos 2.0-->
<body>
<?php
if ($fullscreen) {
    echo '<style type="text/css">
            /*.actions {
                padding: 2px 15px;
                height: 38px;
            }*/
            #learningPathMain {
                margin-top: 5px;
            }
          </style>';
}
?>
<div align="center">
<div align="left"  style="margin-left:auto;margin-right: auto; width:<?php echo $fullscreen?'98%':'960px'; ?>">
    <div id="courseHeader">
    <?php
        // get tocs from learnpath and convert for re-using in toggle menu
        $currentId = $_SESSION['oLP']->current;
        $menuItems = getMenuItemsFromToc($_SESSION['oLP']->get_toc(), $currentId);
        echo renderCourseHeader($nameTools, $_SESSION['oLP']->get_progress_bar_text(), $menuItems, $charset);
    ?>
    </div>
    <!-- Header for navigation in course tool -->
    <input type="hidden" id="old_item" name ="old_item" value="0"/>
    <input type="hidden" id="current_item_id" name ="current_item_id" value="0" />
<?php
if (api_is_allowed_to_edit()) {
    if (!$fullscreen) {
        $return = '<div class="actions" align="left">';
        $author_lang_var = api_convert_encoding(get_lang('Modules'), $charset, api_get_system_encoding());
        $content_lang_var = api_convert_encoding(get_lang('Content'), $charset, api_get_system_encoding());
        $scenario_lang_var = api_convert_encoding(get_lang('Scenario'), $charset, api_get_system_encoding());
        $my_lp_id = intval($_GET['lp_id']);
        // The lp_id parameter will be added by Javascript
        //$return .= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;lp_id='.$my_lp_id.'">' . Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionback')).$author_lang_var . '</a>';
        $return.= '<a href="'.api_get_path(WEB_CODE_PATH).'newscorm/lp_controller.php?action=course&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionback')).$author_lang_var . '</a>';
        if ($_SESSION['oLP']->type != 3 && $_SESSION['oLP']->type != 2) {
        $return .= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;action=add_item&amp;type=step&amp;lp_id='.$my_lp_id.'">' . Display::return_icon('pixel.gif', $content_lang_var,array('class'=>'toolactionplaceholdericon toolactionauthorcontent')).$content_lang_var . '</a>';
        $return .= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;gradebook=&amp;action=admin_view&amp;lp_id='.$my_lp_id.'">' . Display::return_icon('pixel.gif',$scenario_lang_var,array('class'=>'toolactionplaceholdericon toolactionauthorscenario')).$scenario_lang_var . '</a>';
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


        $return .= '</div>';
        echo $return;
    } //end check actions
}
?>

<div id="learningPathMain" style="position:relative;">
<?php if (!$fullscreen): ?>
	<div id="learningPathLeftZone" style="float:left;width:200px;height:100%;">
            <!-- media player layaout -->
            <?php $style_media = (($show_audioplayer)?' style= "position:relative;top:10px;left:10px;margin:8px;font-size:32pt;height:20px;"':'style="height:15px"'); ?>
            <div id="media"  <?php echo $style_media ?>>
                <?php echo (!empty($mediaplayer))?$mediaplayer:'&nbsp;' ?>
            </div>
            <!-- end media player layaout -->
            <!-- toc layout -->
            <div id="toc_id" name="toc_name"  style="padding:0;margin-top:0px;height:60%;width:100%">
                <div id="learningPathToc" style="font-size:9pt;margin:0;"><?php echo $_SESSION['oLP']->get_html_toc(); ?>
                    <!-- log message layout -->
                    <?php if (!empty($_SESSION['oLP']->scorm_debug)) { //only show log ?>
                    <!-- log message layout -->
                            <div id="lp_log_name" name="lp_log_name" class="lp_log" style="height:150px;overflow:auto;margin:4px">
                                    <div id="log_content"></div>
                                    <div id="log_content_cleaner" style="color: white;">.</div>
                             <div style="color: white;" onClick="cleanlog();">.</div>
                            </div>
                    <!-- end log message layout -->
                  <?php } ?>
                <!-- end log message layout -->
                </div>
            </div>
	<!-- end toc layout -->
	</div>
<?php endif; ?>
    <!-- end left Zone -->
    <!-- right Zone -->
        <div id="learning_path_right_zone" style="<?php echo !$fullscreen?'margin-left:205px;':''; ?>height:100%;background-color:white;padding-top:20px;">
                <iframe id="content_id" name="content_name" src="<?php echo $src; ?>" border="0" frameborder="0" class="" style="width:100%;height:680px" ></iframe>
        </div>
    <!-- end right Zone -->
</div>
</div>
</div><!--Ended by breetha -->
<script language="JavaScript" type="text/javascript">
// now we load the content after havin loaded the dom, so that we are sure that scorm_api is loaded
window.onload = function () {
    <?php if ($current_item_type == 'certificate') : ?>
                $.ajax({
                    type: "POST",
                    url: "lp_ajax.php?action=display_certificate&lp_id="+olms.lms_lp_id,
                    success: function(data) {
                        $('#content_id').contents().find('html').html(data);
                    }
                 });
    <?php else : ?>
        $('#content_id').attr('src','<?php echo addslashes($src) ?>');
    <?php endif; ?>
};
</script>
</body>
