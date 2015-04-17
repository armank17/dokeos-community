
<style>
    #photoimg{
        opacity: 0;
        filter: alpha(opacity=0);
        position: absolute;
        left: 603px;
        top: 371px;
        position: absolute;
        z-index: 10;
        width: 65px;
        height: 15px;
    } 
</style>

<?php $W = $this->imageParam[$this->to]['DIMENSION']['W']; ?>
<?php $H = $this->imageParam[$this->to]['DIMENSION']['H']; ?>
<?php $Resize = $this->imageParam[$this->to]['DIMENSION']['Resize']; ?>
<?php $Ratio = $this->imageParam[$this->to]['DIMENSION']['Ratio']; ?>

<canvas id="canv1" width="600" height="600" style="display:none"></canvas>
<canvas id="canv2" width="<?php echo $W; ?>" height="<?php echo $H ?>" style="display:none"></canvas>

<div id="space1" style="width: 1px; height:1px;">
<div id="space2" style="width: 1px; height:1px;float: left;">
<img src="<?php echo $this->image; ?>" id="jcrop_target" >
</div>
</div>
<div id="space3" style="height:30px; margin-top:607px;">
<button class="save" style="float:right;" id="crop"><?php echo get_lang('Crop'); ?></button>
</div>

<style type="text/css">
/*    canvas, img {
        image-rendering: optimizeQuality;
	image-rendering: -moz-crisp-edges;
	image-rendering: -webkit-optimize-contrast;
	image-rendering: optimize-contrast;
	-ms-interpolation-mode: nearest-neighbor;
    }*/
</style>

<script type="text/javascript" >
    
  $(document).ready(function() {
	$('body').css("background-image","none");
        
        $('#crop').click(function() {
            $("#crop").attr("disabled", true);
            uploadSelection();
	});
	
	var W = <?php echo $W ?>;
	var H = <?php echo $H ?>;
        var Resize = <?php echo $Resize ?>;
        var Ratio = <?php echo $Ratio ?>;
        
        //preview image
        preview_image = false;
        if (W === 165 || W === 185 || W === 180 || W === 720 || W === 720)
            preview_image = true;
        
        //Type Logo for CROP
        position = 'middle';
        if (W === 200 || W === 165 || W === 185 || W === 180) {
            max_size_picture = 350;
        } else if (W === 720) {
            max_size_picture = 720;
        } else {
            max_size_picture = 600;
            position = [5, 1];
        }

	if(isCanvasSupported())	{
            var x = window.document.getElementById("jcrop_target");
            var i = window.parent.document.getElementById("imageBuffer");
            
            function cropImage(c) {
                    $('#canv2').attr({width:c.w, height:c.h});
                    ctx2 = document.getElementById('canv2').getContext('2d');
                    ctx2.clearRect(0, 0, W, H);
                    ctx2.mozImageSmoothingEnabled = false;
                    ctx2.webkitImageSmoothingEnabled = false;
                    ctx2.msImageSmoothingEnabled = false;
                    ctx2.imageSmoothingEnabled = false;
                    ctx2.drawImage(document.getElementById('canv1'), c.x, c.y, c.w, c.h, 0, 0, c.w, c.h);
		}
                
                x.src = $(i).val();
                var hi = x.height;
                set_width = Math.min(x.width, max_size_picture);
                set_height = Math.min(x.height, max_size_picture);
                var maxSize = Math.max(set_height, set_width);
		var ratio_orig = x.width / x.height;
		var width = maxSize;
		var height = maxSize;
		
		if (ratio_orig < 1)
                    width = height * ratio_orig;
		else
                    height = width / ratio_orig;
		
		x.width = width;
		x.height = height;
                
                //fill the image
                var wi = -1;
                if ((set_width <= W || set_height <= H) && (max_size_picture !== 600)) {
                    wi = Math.min(set_width, W);
                    set_width = Math.max(set_width, W);
                    set_height = Math.max(set_height, H);
                    var pos_y = (set_height-x.height)/2;
                    $('#canv1').attr({width:set_width, height:set_height});
                    $('img').css({width:set_width, height:set_height, 'background-color':'white'});
                    ctx = document.getElementById('canv1').getContext('2d');
                    ctx.clearRect(0, 0, set_width, set_height);
                    ctx.drawImage(x, 0, 0, wi, hi, (set_width-wi)/2, pos_y, wi, hi);
                    $('img').attr('src', document.getElementById('canv1').toDataURL("image/png"));
                }
                //set size popup
                $('#space3').css({'margin-top': (set_height + 7) + 'px'});
                window.parent.iframe.dialog({
                    autoOpen: false,
                    modal: true,
                    position: position,
                    width: (set_width + 30),
                    height: (set_height + 60)
                });
                window.parent.iframe.dialog().css({width:(set_width + 10)+'px', height:(set_height + 60)+'px'});
	
		$('#jcrop_target').Jcrop({},function() {
                        if (wi === -1) {
                            $('#canv1').attr({width:set_width, height:set_height});
                            ctx = document.getElementById('canv1').getContext('2d');
                            ctx.clearRect(0, 0, set_width, set_height);
                            ctx.drawImage(x, 0, 0, set_width, set_height);
                        }
        
                        jcrop_api = this;
			jcrop_api.animateTo([0, 0, W, H]);
			jcrop_api.setOptions({ 
                            allowMove: true,
                            allowResize: Resize,
                            allowSelect: false,
                            onChange: cropImage,
                            onSelect: cropImage,
                            aspectRatio: Ratio,
                            bgColor: 'white',
                            bgOpacity: 0.7
			});
		});
		
		function uploadSelection() {
                    var imgData = document.getElementById('canv2').toDataURL("image/png");
                    var runparams = window.parent.document.getElementById("runparams");
                    var runParams = $.parseJSON($(runparams).val());
                    try {
                        var folder = runParams.folder;
                        switch (folder) {
                            case 'avatars':
                                folder = 'mascot';
                                break;
                            case 'diagrams':
                                folder = 'images/diagrams';
                                break;
                            }
                        folder = folder + '/';
                    } catch (err) {
                        folder = "";
                    }
                    
                    var postStr = "i=" + encodeURIComponent(imgData) + "&to=<?php echo $this->to ?>";
                    $.post("<?php echo api_get_path(WEB_CODE_PATH) ?>" + "index.php?module=courseInfo&cmd=InfoAjax&func=cropImage&category=" + folder, postStr, function(r) {
			obj = $.parseJSON(r);
                        
                        if (preview_image === true) {
                            var imageFileName = window.parent.document.getElementById("imageFileName");
                            $(imageFileName).val(obj.final);
                            var div = window.parent.document.getElementById("divImgPreview");
                            var img = window.parent.document.getElementById("divImgPreviewed");
                            if ($(div).length) {
                                img.setAttribute("src","<?php echo $this->imageParam[$this->to]['PATH']['WEB'] ; ?>" + folder + "/" + obj.temp);
                                $(div).empty();
                                $(div).append(img);
                            }
                        }
                        <?php echo $this->imageParam[$this->to]['RUN']['AFTER'] ; ?>
                    
                        window.parent.iframe.dialog("close");
                        if (max_size_picture === 600)
                            window.parent.location.reload();
                    });
		}
	}
	else	{
		
		var jcrop_api;

		initJcrop();
				
		function initJcrop()
		{
			$('.requiresjcrop').hide();

			$('#jcrop_target').Jcrop({},function(){

				$('.requiresjcrop').show();
				jcrop_api = this;
				jcrop_api.animateTo([0,0,W,H]);
				
				jcrop_api.setOptions({ 
					allowMove: true, 
					allowResize: true, 
					allowSelect: false,
					onChange: setCoordinates,
					onSelect: setCoordinates,
					aspectRatio: W/H,
					bgColor: '',
					bgOpacity: 1
});
			});
		};

		function setCoordinates(coords)
		{
			$('#x').val(coords.x);
			$('#y').val(coords.y);
			$('#w').val(coords.w);
			$('#h').val(coords.h);
		}
		
		$('#crop').click(function() {
			
			var x = $("#x").val();
			var y = $("#y").val();
			var w = $("#w").val();
			var h = $("#h").val();
			
			
			
			$("#cropForm").ajaxForm({
                type: "POST",
                url: "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=cropImage'; ?>",
                success: function(data) {
					obj = $.parseJSON(data);
					var imageFileName = window.parent.document.getElementById("imageFileName");
					
					$(imageFileName).val(obj.src);
					var div = window.parent.document.getElementById("divImgPreview");
					var img = window.parent.document.createElement("img");
					img.setAttribute("src","<?php echo $this->imageParam[$this->to]['PATH']['WEB'] ; ?>"+obj.src);

					$(div).empty();
					$(div).append(img);
					
					window.parent.iframe.dialog("close");
				}
			}).submit();
		});
	}
});	
		
</script>		


<form id="cropForm">
<input name="action" value="no-canvas" type="hidden">
<input name="to" value="<?php echo $this->to; ?>" type="hidden">
<input name="x" id="x" type="hidden">
<input name="y" id="y" type="hidden">
<input name="w" id="w" type="hidden">
<input name="h" id="h" type="hidden">
<input name="ext" id="ext" type="hidden" value="<?php echo $this->ext; ?>">
</form>