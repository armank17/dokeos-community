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

<canvas id="canv1" width="500" height="375" style="display:none"></canvas>
<canvas id="canv2" width="185" height="140" style="display:none"></canvas>

<?php
$logo_sys_path = api_get_path(SYS_PATH).'home/default_platform_document/ecommerce_thumb/';
$logo_web_path = api_get_path(WEB_PATH).'home/default_platform_document/ecommerce_thumb/';


$logoimgpath="";
$logoimgext="";

	foreach (glob($logo_sys_path . 'temp_logo_session_'.$this->courseInfo["path"].'*') as $path_file) {
		
		$new_file_path = pathinfo($path_file);
		if ($new_file_path['extension'] == 'gif' || $new_file_path['extension'] == 'png' || $new_file_path['extension'] == 'jpg' || $new_file_path['extension'] == 'jpeg') {
			$imgsrc = $logo_web_path . $new_file_path['basename'];
			$logoimgpath = $path_file;
			$logoimgext = $new_file_path['extension'];
		}
	}

$imglogo = (!empty($imgsrc)) ? $imgsrc : api_get_path(WEB_CODE_PATH).'img/thumbnail-course.png';

?>    
<?php
list($swidth, $sheight, $stype, $sattr) = getimagesize($logoimgpath);

?>
<div style="width: 500px; height:375px;">
<div style="width: 500px; height:200px;float: left;">
<img src="<?php echo $imglogo . "?". time();  ?>" id="jcrop_target" >

</div>
<?php /*
<div style="width:185px;height:140px;overflow:hidden;float: left">
	<img src="<?php echo $imglogo?>" id="preview" >
</div>
*/ ?>
</div>
<div style="width: 500px; height:30px;">
<?php
/*
<button style="float: right;">BROWSER</button>
<form id="imageform" method="post" enctype="multipart/form-data" >
<input type="file" name="photoimg" id="photoimg">
</form>
*/
?>
<button class="save" style="float: right;" id="crop">CROP</button>
</div>

<script type="text/javascript" >


	
  $(document).ready(function() {
	  
	var session_id_input = window.parent.document.getElementById("session_id");
	var session_id = $(session_id_input).val(); 

	$("#session_id").val(session_id);

	$('body').css("background-image","none");

	if(isCanvasSupported())	{
		
		
		
		var i = window.parent.document.getElementById("image");
		var x = window.document.getElementById("jcrop_target");
		
		x.src = $(i).val();

		var set_width = 500;
		var set_height = 375;
		var maxSize = set_height;
		var ratio_orig = x.width / x.height;
		var width = maxSize;
		var height = maxSize;
		
		if (ratio_orig < 1) {
			width = height * ratio_orig;
		}
		else {
			height = width / ratio_orig;
		}
		
		x.width =width;
		x.height =height;

		ctx = document.getElementById('canv1').getContext('2d');
		ctx.clearRect(0,0,500,375);
		ctx.drawImage(x,0,0,x.width,x.height);
		
		cropImage({x:0,y:0,w:185,h:140});
		
		$('#jcrop_target').Jcrop({},function(){
			
			jcrop_api = this;
			jcrop_api.animateTo([0,0,185,140]);
			jcrop_api.setOptions({ 
				allowMove: true, 
				allowResize: true, 
				allowSelect: false,
				onSelect: cropImage,
				aspectRatio: 1.32142857143 ,
				bgColor: '',
				bgOpacity: 1
			});
		});
		
		function cropImage(c) {
			ctx2 = document.getElementById('canv2').getContext('2d');
			ctx2.clearRect(0,0,185,140);
			
			
			
			ctx2.drawImage(document.getElementById('canv1'), c.x, c.y, c.w, c.h, 0, 0, 185, 140);
		}
		
		function uploadSelection() {
			
			var imgData = document.getElementById('canv2').toDataURL("image/png");
			var postStr = "i=" + encodeURIComponent(imgData)+"&session_id="+session_id;
			$.post("<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=croplogosession'; ?>", postStr, function(r) { 
				
						obj = $.parseJSON(r);
					
						var logoFileName = window.parent.document.getElementById("logoFileName");
						$(logoFileName).val(obj.normal);
						
						var div = window.parent.document.getElementById("divImgPreview");
						var img = window.parent.document.createElement("img");
						img.setAttribute("src","<?php echo $logo_web_path ; ?>"+obj.temp);
						$(div).empty();
						$(div).append(img);
						
						window.parent.iframe.dialog("close");
						
				});
		}
		
		
		$('#crop').click(function() {
			
			uploadSelection();
		});
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
				
				jcrop_api.animateTo([0,0,200,50]);
				
		
				jcrop_api.setOptions({ 
					
					allowMove: true, 
					allowResize: true, 
					allowSelect: false,
					onChange: setCoordinates,
					onSelect: setCoordinates,
					aspectRatio: 1.32142857143,
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
                
                url: "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=croplogosessionEx'; ?>",
                success: function(data) {
					obj = $.parseJSON(data);
				
					var logoFileName = window.parent.document.getElementById("logoFileName");
					
					$(logoFileName).val(obj.src);
					var div = window.parent.document.getElementById("divImgPreview");
					var img = window.parent.document.createElement("img");
					img.setAttribute("src","/home/default_platform_document/ecommerce_thumb/"+obj.src);
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
<input name="x" id="x" type="hidden">
<input name="y" id="y" type="hidden">
<input name="w" id="w" type="hidden">
<input name="h" id="h" type="hidden">
<input name="ext" id="ext" type="hidden" value="<?php echo $logoimgext; ?>">
<input name="session_id" id="session_id" type="hidden" value="<?php echo "";?>">
</form>

