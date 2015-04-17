<?php

require_once dirname(__FILE__) . '/../../../global.inc.php';
//require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderFactory.php';

if (api_get_setting('audio_recorder') == 'true' && api_is_allowed_to_edit()) {
    if (is_dir(api_get_path(SYS_CODE_PATH).'inc/lib/audiorecorder')) {
        require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderFactory.php';
        $provider = 1; // @todo the value should be a setting
        $objAudiorecorder = AudiorecorderFactory::getAudiorecorderObject($provider, $_GET['tool'], $_GET['tool_id']);
        echo  $objAudiorecorder->returnCss();
        echo  $objAudiorecorder->returnJs();
    }
}
$action = $_GET['action'];
switch ($action) 
{
    case 'save_file':
        $autoplay = $_GET['auto'];
        $origin = strip_tags($_GET['origin']);
        $temp = !empty($origin);
        $return = $objAudiorecorder->saveAudio($_FILES['file'], $autoplay, $temp);
        echo $return;
        break;
    case 'delete_file':
        $return = $objAudiorecorder->deleteAudio();
        echo $return;
        break;
}

?>
