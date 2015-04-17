<?php
require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderInterface.php';

abstract class AudiorecorderAbstract implements AudiorecorderInterface
{
    const AUDIORECORDER_LIB_NANOGONG = 1;
    const AUDIORECORDER_LIB_SWFLASH  = 2;

    public function __construct() {}
    public function getDialog($dialogId, $dialogTitle = '', $extra = array()) {}
    public function returnJs() {
        $js = '
              <script type="text/javascript">
                function recordDialog(id) {
                      $("#record"+id).dialog({
                        autoOpen: true,
                        width: 450,
                        height: 340,
                        modal: true
                    });
                }
              </script>';
        return $js;
    }
    public function returnCss() {
        $css = '<link href="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/css/audiorecorder.css" type="text/css" rel="stylesheet">';
        return $css;
    }

    public function saveAudio($aFile) {}

}

