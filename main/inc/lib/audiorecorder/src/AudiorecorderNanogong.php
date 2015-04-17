<?php

require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderAbstract.php';
require_once api_get_path(LIBRARY_PATH).'audiorecorder/src/AudiorecorderInterface.php';
class AudiorecorderNanogong extends AudiorecorderAbstract implements AudiorecorderInterface
{
    public $tool;
    public $toolId;

    public function __construct($tool = null, $toolId = null) {
        if (!empty($tool)) {
            $this->tool = $tool;
        }
        if (!empty($toolId)) {
            $this->toolId = $toolId;
        }
    }

    public function getDialog($dialogId, $dialogTitle = '', $extra = array())
    {
        $dialog  = '<div id="record'.$dialogId.'" title="'.$dialogTitle.'" style="display: none">';
        $dialog .= '<div id="nanogong-container">';
        $dialog .= '<div id="nanogong-top-image">'.Display::return_icon('audiorecorder.png').'</div>';
        $dialog .= '<applet id="nanogong" archive="'.api_get_path(WEB_LIBRARY_PATH).'audiorecorder/nanogong.jar" code="gong.NanoGong" width="250" height="40" align="middle">';
        $dialog .= '<param name="ShowTime" value="true" />';
        $dialog .= '<param name="Color" value="#FFFFFF" />';
        $dialog .= '<param name="AudioFormat" value="ImaADPCM" />';
        $dialog .= '<param name="ShowSaveButton" value="false" />';
        $dialog .= '<param name="ShowSpeedButton" value="false" />';
        $dialog .= '</applet>';
        $dialog .= '<br /><br /><form name="form_nanogong_advanced">';
        $dialog .= '<input type="checkbox" name="autoplay" value="1" id="autoplay" />&nbsp;'.get_lang('Autoplay');
        $dialog .= '<a href="#" class="save"  onclick="saveAudio()">'.get_lang('SaveAudio').'</a>';
        $dialog .= '</form>';
        $dialog .= '</div>';
        $dialog .= '</div>';
        return $dialog;
    }

    public function  returnJs($origin = '') {
        $url_save_ajax   = api_get_path(WEB_LIBRARY_PATH).'audiorecorder/ajax/nanogong.ajax.php?'.api_get_cidreq().'&action=save_file&origin='.$origin.'&tool='.$this->tool.'&tool_id='.$this->toolId;        
        $url_delete_ajax = api_get_path(WEB_LIBRARY_PATH).'audiorecorder/ajax/nanogong.ajax.php?'.api_get_cidreq().'&action=delete_file&origin='.$origin.'&tool='.$this->tool.'&tool_id='.$this->toolId;
        $js = parent::returnJs();
        $audioTemp = api_get_path(WEB_COURSE_PATH).api_get_course_path().'/temp/audionano-'.$this->toolId.'.mp3';
        $authorScript = $origin == 'author' ? 'loadAuthorAudio();' : '';
        $js .= '
                <script type="text/javascript">
                        function loadAuthorAudio() {
                            var courseCode = decodeURIComponent($("#courseCode").val());
                            var webPath = decodeURIComponent($("#webPath").val());
                            var path = "'.$audioTemp.'"; 
                            loadFromIframeAudioPlayer(path, "play");
                            window.parent.iframe.dialog("close");
                        }
                        function saveAudio() {
                            //try {
                                var gong = document.getElementById("nanogong");
                                if (!gong || !navigator.javaEnabled()) {
                                    alert("'.api_utf8_encode(get_lang('NanogongNoApplet')).'");
                                    return false;
                                }
                                var duration = parseInt(document.getElementById("nanogong").sendGongRequest("GetMediaDuration", "audio")) || 0;
                                if (duration <= 0) {
                                    alert("'.api_utf8_encode(get_lang('NanogongRecordBeforeSave')).'");
                                    return false;
                                }
                                var autoplay = 0;
                                if ($("form #autoplay").is(":checked")) {
                                    autoplay = 1;
                                }
                                var ret = document.getElementById("nanogong").sendGongRequest("PostToForm", "'.$url_save_ajax.'&auto="+autoplay, "file", "", "audionano.wav"); // PostToForm, postURL, inputname, cookie, filename                                
                                '.$authorScript.'
                                //alert("'.api_utf8_encode(get_lang('UplUploadSucceeded')).'");
                                //location.href = "'.api_get_path(WEB_CODE_PATH).'newscorm/lp_controller.php?'.api_get_cidreq().'&action=view&lp_id='.intval($_GET['lp_id']).'&item_id='.$this->toolId.'";
                                //return false;
                            //} catch (e) {}
                        }

                        function deleteAudio() {
                            try {
                                $.ajax({
                                    url: "'.$url_delete_ajax.'",
                                    success:function(data) {
                                        if (data) {
                                            $("#audio-recorder-icon").html("<a href=\'javascript:void(0)\' onclick=\'recordDialog('.$this->toolId.');\' ><img style=\'padding-left:22px;\' src=\''.api_get_path(WEB_IMG_PATH).'record_mp3.png\' >&nbsp;'.get_lang('AudioRecorderTitle').'</a>");
                                        }
                                    }
                                });
                                $.ajax({
                                    url: "'.api_get_path(WEB_CODE_PATH).'newscorm/lp_nav.php?'.api_get_cidreq().'",
                                    success: function(data){
                                        $("#media").html(data);
                                    }
                                });
                            } catch (e) {}
			}

                </script>
              ';
        return $js;
    }

    public function saveAudio($aFile, $autoplay = 0, $temp = false) {
        $return = false;
        if ($aFile['error'] == UPLOAD_ERR_OK) {            
            if ($temp) {  
                $audioTempDirPath = api_get_path(SYS_COURSE_PATH).api_get_course_path().'/temp/';
                $audioName = 'audionano.wav';
                if (!is_uploaded_file($aFile['tmp_name'])) { return false; }
                if (move_uploaded_file($aFile['tmp_name'], $audioTempDirPath.$audioName)) {
                    $audioMp3Name = 'audionano-'.$this->toolId.'.mp3';
                    // convert to mp3 width ffmpeg
                    @exec("ffmpeg -i ".$audioTempDirPath.$audioName." -acodec libmp3lame ".$audioTempDirPath.$audioMp3Name);
                    if (file_exists($audioTempDirPath.$audioMp3Name)) {
                        @unlink($audioTempDirPath.$audioName);
                        $return = $audioTempDirPath.$audioMp3Name;
                    }
                }
            }
            else {
                $audioName = 'audionano.wav';            
                switch ($this->tool) {
                    case 'module':
                        if (!is_uploaded_file($aFile['tmp_name'])) { return false; }
                        //first we delete everything before uploading the file
                        $deleted = $this->deleteAudio();
                        $audioDirPath = api_get_path(SYS_COURSE_PATH).api_get_course_path().'/upload/learning_path/items/'.$this->toolId.'/';
                        if (!is_dir($audioDirPath)) {
                            $perm = api_get_setting('permissions_for_new_directories');
                            $perm = octdec(!empty($perm) ? $perm : '0770');
                            mkdir($audioDirPath, $perm, true);
                        }
                        if (move_uploaded_file($aFile['tmp_name'], $audioDirPath.$audioName)) {
                            // convert to mp3 width ffmpeg
                            chmod($audioDirPath."SolidWaste.wav",'777');
                            @exec("ffmpeg -i ".$audioDirPath.$audioName." -acodec ac3 ".$audioDirPath."audionano.mp3");
                            if (file_exists($audioDirPath."audionano.mp3")) {
                                $audioName = 'audionano.mp3';
                            }
                            $return = true;
                            $tblLpItems = Database::get_course_table(TABLE_LP_ITEM);
                            $audio .= $audioName.'&autoplay='.intval($autoplay);
                            Database::query("UPDATE $tblLpItems SET audio = '$audio' WHERE id = '".intval($this->toolId)."'");
                        }
                        break;
                }
            }
        }
        return $return;
    }

    public function deleteAudio() {
        $return = false;
        if ($this->toolId) {
            switch ($this->tool) {
                case 'module':
                    // get the audio
                    $tblLpItems = Database::get_course_table(TABLE_LP_ITEM);
                    $rs = Database::query("SELECT audio FROM $tblLpItems WHERE id = '".intval($this->toolId)."'");
                    if (Database::num_rows($rs) > 0) {
                        $row = Database::fetch_array($rs, 'ASSOC');
                        $audioplay = 0;
                        $audioName = $row['audio'];
                        if (strpos($row['audio'], '&autoplay=') !== FALSE) {
                            list($audioName, $attr) = explode('&', $row['audio']);
                            if (!empty($attr)) {
                                    list($name, $autoplay) = explode('=', $attr);
                            }
                        }
                        $audioDirPath = api_get_path(SYS_COURSE_PATH).api_get_course_path().'/upload/learning_path/items/'.$this->toolId.'/';
                        if (!empty($audioName)) {
                            if (file_exists($audioDirPath.$audioName)) {
                                @unlink($audioDirPath.$audioName);
                                @unlink($audioDirPath.'audionano.wav');
                            }
                            if (!file_exists($audioDirPath.$audioName)) {
                                Database::query("UPDATE $tblLpItems SET audio='' WHERE id = '".intval($this->toolId)."'");
                                $return = Database::affected_rows();
                            }
                        }
                    }
                    break;
            }
        }
        return $return;
    }

}

?>
