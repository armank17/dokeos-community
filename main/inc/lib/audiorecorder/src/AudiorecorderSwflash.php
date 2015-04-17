<?php

require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderAbstract.php';
require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderInterface.php';
require_once api_get_path(LIBRARY_PATH).'audiorecorder/audiorecorder_conf.php';
class AudiorecorderSwflash extends AudiorecorderAbstract implements AudiorecorderInterface
{

    public function __construct() {}

    public function getDialog($dialogId, $dialogTitle = '', $extra = array())
    {

        $dialog  = '<div id="record'.$dialogId.'" title="'.$dialogTitle.'" style="display: none">';
        $dialog .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/js/audiorecorder.js"></script>';
        $dialog .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/js/flashplayer.js"></script>';
        $dialog .='<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/css/audiorecorder.css" type="text/css" media="projection, screen">';
        $dialog .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/audiorecorder.php?'.api_get_cidreq().'&amp;id='.$dialogId.'&amp;action='.$extra['action'].'&amp;lp_id='.$extra['lp_id'].'&title='.$dialogTitle.'"></script>';

        $dialog .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
                            id="audioRecorder" width="600" height="300"
                            codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">';

        $dialog .= '<param name="movie" value="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/swf/audioRecorder.swf" />';
        $dialog .= '<param name="quality" value="high" />
                            <param name="bgcolor" value="#869ca7" />
                            <param name="allowScriptAccess" value="sameDomain" />';
        $dialog .= '<param name="flashvars" value="myServer=rtmp://' . $extra['host'] . '/oflaDemo&amp;timeLimit=' . $extra['time_limit'] . '&amp;urlDokeos=' . api_get_path(WEB_PATH) . 'main/document/upload_audio.php&mySound='.str_replace('','_',$dialogTitle).'">';
        $dialog .= '<embed src="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/swf/audioRecorder.swf" quality="high" bgcolor="#869ca7"
                                width="600" height="300" name="audioRecorder" align="middle"
                                play="true"
                                loop="false"
                                quality="high"
                                allowScriptAccess="sameDomain"
                                type="application/x-shockwave-flash"
                                flashvars="myServer=rtmp://'.$extra['host'].'/oflaDemo&amp;timeLimit=' . $extra['time_limit'] . '&amp;urlDokeos=' . api_get_path(WEB_PATH) . 'main/document/upload_audio.php&mySound='.str_replace('','_',$dialogTitle).'"
                                pluginspage="http://www.adobe.com/go/getflashplayer">
                             </embed>
                             </object>';
        $dialog .= '</div>';

        return $dialog;

    }

    public function returnJs()
    {
        $js  = parent::returnJs();

        $js .= '<script type="text/javascript">
                    function deleteSoundMp3(lp_id, action, cidReq, id, sound) {
                        if (confirm("Confirm Delete")) {
                            window.location.href="'.api_get_path(WEB_CODE_PATH).'document/upload_delete_audio.php?'.api_get_cidreq().'&amp;lp_id="+lp_id+"&amp;action="+action+"&amp;id="+id+"&sound="+sound;
                        }
                        else {
                            return false;
                        }
                    }
                </script>';

        return $js;
    }

}

?>
