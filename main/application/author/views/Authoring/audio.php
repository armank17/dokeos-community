<?php 
    if ($this->displayGallery) {
?>
    <div id="audio-gallery">
       <div class="breadcrumb"><?php echo $this->breadcrumb; ?></div>  
       <div id="audio-gallery-upload">
           <span class="btn btn-success fileinput-button">
               <i class="icon-plus icon-white"></i>
               <span><?php echo $this->get_lang('AddAudios'); ?>...</span>
               <!-- The file input field used as target for the file upload widget -->
               <input id="fileupload" type="file" name="files[]" multiple title="<?php echo get_lang('AddAudios'); ?>" >
           </span>
           <br /><br />
           <!-- The global progress bar -->
           <div id="progress" class="progress progress-success progress-striped">
               <div class="bar"></div>
           </div>           
       </div>
       <div id="audios" class="audios">
           <?php echo $this->setTemplate('load_audios', 'Authoring'); ?>    
       </div>
    </div>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.min.js"></script>
    <script src="<?php echo api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js'; ?>"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/vendor/jquery.ui.widget.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.iframe-transport.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.fileupload.js"></script>
    <script>
        $(function () {
             'use strict';
             // Change this to the location of your server-side upload handler:
             var url = '<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=AuthoringAjax&func=uploadAudio&lpId='.$this->lpId.'&'.api_get_cidreq(); ?>';
             var urlAudios = '<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=AuthoringAjax&func=loadAudios&lpId='.$this->lpId.'&'.api_get_cidreq(); ?>'             
             $('#fileupload').fileupload({
                 url: url,
                 dataType: 'json',
                 acceptFileTypes: /(\.|\/)(mp3)$/i,
                 disableImageResize: /Android(?!.*Chrome)|Opera/
                    .test(window.navigator.userAgent),
                 previewMaxWidth: 100,
                 previewMaxHeight: 100,
                 previewCrop: true,                 
                 done: function (e, data) {
                     var error = '';
                     $.each(data.result.files, function (index, file) {
                        if (file.error) {
                            error += "<p>"+file.error+"</p>";                            
                        }                        
                     });                     
                     if (error != '') {
                        $(".progress").css("visibility", "hidden");
                        window.parent.$.alert(error, getLang('Error'), 'error', false);
                        return false;
                     }                     
                     $(".progress").css("visibility", "hidden");                     
                     $.get(urlAudios, function(data){
                         $("#audios").html(data);
                     });
                 },
                 progressall: function (e, data) {
                     $(".progress").css("visibility", "visible");
                     var progress = parseInt(data.loaded / data.total * 100, 10);
                     $('#progress .bar').css(
                         'width',
                         progress + '%'
                     );
                 },
                 fail: function(e, data) {                    
                     /*$.each(data.result.files, function (index, file) {
                        alert(file.error);
                    });*/
                 }
             }).prop('disabled', !$.support.fileInput)
                 .parent().addClass($.support.fileInput ? undefined : 'disabled');
             
             var bgcolor = window.parent.$("#header_background").css("background-color");    
             $("html").niceScroll({cursorcolor: bgcolor, cursorwidth:"8px"}); 
             
             var myiframe = window.parent.iframe;
             if (myiframe.length > 0) {                 
                 myiframe.on( "dialogbeforeclose", function( event, ui ) {                     
                    var players = $(".thePlayer");
                    if (players.length > 0) {
                        players.each(function() {
                            var playerId = "mediaplayer" + $(this).attr("id");
                            jwplayer(playerId).stop();
                        });
                    }                 
                 });
             }

         });
    </script>
<?php
    }
    else if ($this->displayUploadForm) {
        echo '<div class="breadcrumb">'.$this->breadcrumb.'</div><br />';     
?>   
        <div id="nanogong-container">
            <div id="nanogong-top-image"><?php echo Display::return_icon('audiorecorder.png'); ?></div>
            <applet id="nanogong" archive="<?php echo api_get_path(WEB_LIBRARY_PATH).'audiorecorder/nanogong.jar'; ?>" code="gong.NanoGong" width="250" height="40" align="middle">
                <param name="ShowTime" value="true" />
                <param name="Color" value="#FFFFFF" />
                <param name="AudioFormat" value="ImaADPCM" />
                <param name="ShowSaveButton" value="false" />
                <param name="ShowSpeedButton" value="false" />
            </applet>
            <div>
                <form name="form_nanogong_advanced">
                    <input type="checkbox" name="autoplay" value="1" id="autoplay" />&nbsp;<?php echo $this->get_lang('Autoplay'); ?>
                    <a href="#" class="save"  onclick="saveAudio()"><?php echo $this->get_lang('SaveAudio'); ?></a>
                </form>
            </div>
        </div>



<?php        
    }   
    else {
?>        
<ul class="mediabox-list audio-blocks">
    <li>
        <div class="ml-top">
            <a class="big_button  rounded grey_border create_recorder_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=audio&'.api_get_cidreq().'&lpId='.$this->lpId.'&category=recording&width=480&height=420&refresh=false'; ?>" title="<?php echo $this->get_lang("RecordMyOwnVoiceNow"); ?>"><?php echo $this->get_lang('RecordMyOwnVoiceNow'); ?></a>
        </div>
    </li>
    <li>
        <div class="ml-top">
            <a class="big_button  rounded grey_border create_audio_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=audio&'.api_get_cidreq().'&lpId='.$this->lpId.'&category=audio&width=580&height=440&refresh=false'; ?>" title="<?php echo $this->get_lang("PickExistingAudioFiles"); ?>"><?php echo $this->get_lang('PickExistingAudioFiles'); ?></a>
        </div>
    </li>
</ul>
<?php 
    }
 ?>
<input type="hidden" id="courseCode" value="<?php echo urlencode($this->courseCode); ?>" />
<input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />