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


<?php
$logo_sys_path = api_get_path(SYS_CODE_PATH).'application/ecommerce/assets/images/';
$logo_web_path = api_get_path(WEB_CODE_PATH).'application/ecommerce/assets/images/';
$logoimgpath="";
$logoimgext="";

	foreach (glob($logo_sys_path . 'temp_shop_logo_'.$this->courseInfo["path"].'*') as $path_file) {
		
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
<div style="width: 500px; height:375px;float: left;">
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
<script type="text/javascript">
var jcrop_api;
var rWidth=<?php echo $swidth; ?>;
var rHeight=<?php echo $sheight; ?>;
var image = "<?php echo $imglogo; ?>";

			$(function($){

				 // Holder for the API
				initJcrop();
				
				function initJcrop()//{{{
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
							onChange: showPreview,
							onSelect: showPreview,
							aspectRatio: 1 ,
							bgColor: '',
							bgOpacity: 1
						});
						
						
						

					});

				};
		
		});
		
		function showPreview(coords)
		{
			var rx = 200 / coords.w;
			var ry = 50 / coords.h;
			
			$('#preview').css({
				width: Math.round(rx * rWidth) + 'px',
				height: Math.round(ry * rHeight) + 'px',
				marginLeft: '-' + Math.round(rx * coords.x) + 'px',
				marginTop: '-' + Math.round(ry * coords.y) + 'px'
			});
			
			

			$('#x').val(coords.x);
			$('#y').val(coords.y);
			$('#w').val(coords.w);
			$('#h').val(coords.h);

			
		}

		</script>

<script type="text/javascript" >
	
    $(document).ready(function() {
		
		$('body').css("background-image","none");
		
        $('#photoimg').change(function() {

            $("#imageform").ajaxForm({
                type: "POST",
                url: "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadlogocourseEx'; ?>",
                beforeSend: function() {
                    
                },
                success: function(data) {
					console.log(data);
					obj = $.parseJSON(data);
                    rWidth = obj.width;
                    rHeight = obj.height;
                    var src = "<?php echo api_get_path(WEB_COURSE_PATH) . $this->courseInfo['path'] . '/temp/'; ?>" + obj.src + "?<?php echo time();?>" ;
					
					$("#ext").val(obj.ext);
					
					jcrop_api.setImage(src,function(){
						//this.setOptions({aspectRatio: 1 })
						this.animateTo([0,0,200,50]);
						
						
					});
					
					
					
					
					$("#preview").attr("src","");
					$("#preview").attr("src",src);
					
                }

            }).submit();
        });
        
       
        
        $('#crop').click(function() {
			
			var x = $("#x").val();
			var y = $("#y").val();
			var w = $("#w").val();
			var h = $("#h").val();
			
			
			
			$("#cropForm").ajaxForm({
                type: "POST",
                
                url: "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=croplogoshop'; ?>",
                success: function(data) {
					
				
					var input = window.parent.document.getElementById("txtImageFile");
					$(input).val(data);
					var div = window.parent.document.getElementById("divImgPreview");
					var img = window.parent.document.createElement("img");
					img.setAttribute("src","application/ecommerce/assets/images/"+data);
					$(div).empty();
					$(div).append(img);
					
					
					window.parent.iframe.dialog("close");
				}
			}).submit();
			
			
		});
        
    });
</script>
<form id="cropForm">
<input name="x" id="x" type="hidden">
<input name="y" id="y" type="hidden">
<input name="w" id="w" type="hidden">
<input name="h" id="h" type="hidden">
<input name="ext" id="ext" type="hidden" value="<?php echo $logoimgext; ?>">
</form>
