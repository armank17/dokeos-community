<script type="text/javascript">
$(document).ready(function() {
        var bgcolor = $("#header_background").css("background-color");
        $("#slideed").niceScroll({cursorcolor: bgcolor, cursorwidth: "8px", horizrailenabled: false});
    if ($(".audio-actions").length > 0) {
            $(".audio-actions").live('click', function(e) {
        e.preventDefault();           
        var action = $(this).attr("id");
        if (action == 'unmute') {
             $(this).attr("id", "mute");
             $(".audio-actions").find('img').attr("id", "speaker_mute");
        }
        else if (action == 'mute') {
            $(this).attr("id", "unmute");
            $(".audio-actions").find('img').attr("id", "speaker_on");  
        }
        loadAudioPlayer('', action);           
     });    
 }
        $('#thumb_image_streaming').css('display', 'none');
// var containerHeight = $("#player-view-middle-left").height();
// var viewHeight = $(".player-actions").height();
// 
// if(containerHeight>viewHeight)
// {
//	 $("#slider").slider({
//		animate: true,
//		orientation: "vertical",
//		change: handleSliderChange,
//		slide: handleSliderSlide,
//		min: 0,
//		max: 100,
//		value: 100,
//		create: function( event, ui ) {$(ui.handle).css("bottom","0");}
//	  });
// }
//function handleSliderChange(e, ui)
//{
//  var amount = 	100 - ui.value;
//  var maxScroll = $(".player-actions")[0].scrollHeight - $(".player-actions").height();
//  $(".player-actions").animate({scrollTop: amount * (maxScroll / 100) }, 200);
//
//	
//
//}
//
//function handleSliderSlide(e, ui)
//{
////  var amount = 	100 - ui.value;
////  var maxScroll = $(".player-actions").attr("scrollHeight") - $(".player-actions").height();
////  $(".player-actions").attr({scrollTop: amount * (maxScroll / 100) });
//	//console.log(ui);
//	//console.log(ui.value);
//	
//	var sliderButton = $(ui.handle);
//	var sliderRail = sliderButton.parent();
//	
//	var nof =  ((sliderButton.width() - sliderRail.width())/2);
//	
//	sliderButton.css("left",-1*nof);  
//	
//	
//	  var value = $("#slider").slider( "option", "value" );
//
//  if(value<50)
//	$(".ui-slider-handle").css("margin-bottom","0.1em");
//  else
//	$(".ui-slider-handle").css("margin-bottom","-1.6em");
//	
//}

         
    
});
</script>
<?php
//clear session_timer
unset($_SESSION["notime"]);

if (!empty($this->currentItem['audio']) && file_exists($this->documentSysPath . $this->currentItem['audio'])) {
    $autoplay = true;
    $audiopath = $this->documentWebPath . $this->currentItem['audio'];
} else {
    $autoplay = false;
    $audiopath = 'audio.mp3';
}       

echo $this->getAudio($audiopath, 'item-record', 1, 1, $autoplay, true);
?>
<div id="main">
    <div id="continueContainer" name="continueContainer"><a onclick="goto('<?php echo api_get_path(WEB_COURSE_PATH) . $this->courseInfo['path']; ?>/index.php')"><button id="continue" name="continue" class="continue" style="display:none;position: absolute; font-size: 18px; z-index: 100;">Continue</button></a></div>    
    <div id="content_sup">
        <div class="toogle-slide-top" style="width: 100%;">
            <div id="player-view-top" style="height:60px;width:100%;">
                <?php $this->setTemplate('view_top'); ?>
            </div>
            <a class="ssOpen arrow_up" href="#"></a>
        </div>
    </div>
    <div id="content_mid">
         <div id="player-view-middle" class="">
            
				<div class="toogle-slide-left  player-actions" id="slideed" style="height: 85%; overflow: hidden"> 
                <div id="player-view-middle-left" style="background-color:white;">
						<?php $this->setTemplate('view_left'); ?>
					</div>
                <!--<div id="slider" class="slider" style="display:none;"></div>-->
					<a class="ssOpenG arrow_right" href="#"></a>
				</div>
            <div id="player-view-middle-right" class="">
                <div id="player-view-content">
                    <?php 
                        if (!empty($this->contentLink)): 
                    ?>
                        <iframe id="author-iframe" src="<?php echo $this->contentLink; ?>" width="100%" frameborder="0" scrolling="no"></iframe>
                    <?php 
                        else: 
                            echo $this->content;
                        endif; 
                    ?>
                </div>               
            </div>
            <div id="ajax-loading"><img src="<?php echo api_get_path(WEB_IMG_PATH) . 'bx_loader.gif'; ?>" /></div> 
         </div>
    </div>
    <div id="content_inf"></div>
</div>
<input type="hidden" id="cid-req" value="<?php echo urlencode(api_get_cidreq()); ?>" />
<input type="hidden" id="web-path" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />