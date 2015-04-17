<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls 
 */
$language_file = array('admin', 'registration');
require_once '../global.inc.php';
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
echo '<script src="'.api_get_path(WEB_CODE_PATH).'course_home/script/jquery.jscrollpane.min.js"></script>';
	echo '<script src="'.api_get_path(WEB_CODE_PATH).'course_home/script/jquery.mousewheel.js"></script>';
	echo '<style>
	.scroll-pane11
	{
		width: auto;
		height: 280px;
		overflow: auto;
	}
	</style>';
	echo '<script>
        $(function () {			
			$(".scroll-pane11").jScrollPane();
	});
	</script>';
