<?php
require_once '../../main/inc/global.inc.php';

$rowIndex = $_GET['rowIndex'];
$colIndex = $_GET['colIndex'];
$theme_color = $_GET['theme_color'];

?>
<div id="iconslist">	
            
</div>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.min.js"></script>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/vendor/jquery.ui.widget.js"></script>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.iframe-transport.js"></script>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="script/jquery.mousewheel.js"></script>
<script type="text/javascript" src="script/jquery.jscrollpane.min.js"></script>
<script>
        $(function () {
             'use strict';
			 var urlImages = '<?php echo api_get_path(WEB_CODE_PATH).'course_home/get_new_icons.php?'.api_get_cidreq(); ?>&rowIndex=<?php echo $rowIndex; ?>&colIndex=<?php echo $colIndex; ?>&theme_color=<?php echo $theme_color; ?>';
			 $.get(urlImages, function(data){
				$("#iconslist").html(data);
			 });
		 });
</script>