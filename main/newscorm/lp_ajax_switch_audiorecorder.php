<?php
/* For licensing terms, see /dokeos_license.txt */
/**
* 	Learning Path
*	This script allow switch audiorecorder for each item
*	@package dokeos.learnpath
*/
require_once('../inc/global.inc.php');
$output = '';
if (api_get_setting('audio_recorder') == 'true' && api_is_allowed_to_edit()) {
    if (is_dir(api_get_path(SYS_CODE_PATH).'inc/lib/audiorecorder')) {
        require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderFactory.php';
        $provider = 1; // @todo the value should be a setting
        $objAudiorecorder = AudiorecorderFactory::getAudiorecorderObject($provider, 'module', $_GET['lp_item_id']);
        echo $objAudiorecorder->returnCss();
        echo $objAudiorecorder->returnJs();

        $action = Security::remove_XSS($_GET['action']);
        $lp_id  = intval($_GET['lp_id']);
        $current_item = $_GET['lp_item_id'];
        $item_info = get_lp_item_info($current_item);

        if ($provider == AudiorecorderFactory::SWFLASH) {
            $extra = array('action'=>$action, 'lp_id'=>$lp_id, 'host'=>$url['host'], 'time_limit'=>$time_limit);
            $dialog = $objAudiorecorder->getDialog($current_item, $item_info['title'], $extra);
            $event_click = !empty($item_info['audio'])?'deleteSoundMp3(' . $lp_id . ', \'' . $action . '\',\'' . api_get_course_id() . '\',' . $current_item . ',\'' . $item_info['audio'] . ' \'  );':'recordDialog('.$current_item.');';
        }
        else if ($provider == AudiorecorderFactory::NANOGONG) {
            $dialog = $objAudiorecorder->getDialog($current_item, $item_info['title']);
            $event_click = !empty($item_info['audio'])?'deleteAudio();':'recordDialog('.$current_item.');';
        }
    }
}
$output .= '<span id="audio-recorder-icon"><a href="javascript:void(0)" onclick="'.$event_click.'" ><img style="padding-left:22px;vertical-align:middle;" src="../img/' . (!empty($item_info['audio'])?'sound_mp3.png':'record_mp3.png').'">&nbsp;'.(!empty($item_info['audio'])?get_lang('RemoveAudio'):get_lang('AudioRecorderTitle')).'</a></span>';
$output .= $dialog;
echo $output;

/**
* Get audio by item 
*/
function get_lp_item_info($item_id) {
    $tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
    $info = '';
    $rs = Database::query("SELECT * FROM $tbl_lp_item WHERE id = ".intval($item_id));
    if (Database::num_rows($rs) > 0) {            
        $row = Database::fetch_array($rs, 'ASSOC');
        $info = $row;
    }
    return $info;
}