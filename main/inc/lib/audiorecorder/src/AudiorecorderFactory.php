<?php
//require_once dirname( __FILE__ ) . '/../../../../main/inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderNanogong.php';
require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderSwflash.php';
require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderAbstract.php';
require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderInterface.php';

class AudiorecorderFactory {

    const NONE = 0;
    const NANOGONG = 1;
    const SWFLASH  = 2;

    public static function getAudiorecorderObject($type = 1, $tool = null, $toolId = null) {

        switch ($type) {
            case AudiorecorderAbstract::AUDIORECORDER_LIB_NANOGONG:
                 return new AudiorecorderNanogong($tool, $toolId);
            case AudiorecorderAbstract::AUDIORECORDER_LIB_SWFLASH:
                 return new AudiorecorderSwflash();
        }

    }

}

?>
