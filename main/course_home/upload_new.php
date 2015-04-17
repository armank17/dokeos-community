<?php
require_once '../../main/inc/global.inc.php';
require_once '../../main/appcore/library/jquery/jquery.upload/server/php/UploadAjaxHandler.php';

$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$course_directory = $course_info['path'];

$temp_sys_path = api_get_path(SYS_COURSE_PATH).'/document/icons/';
$temp_web_path = api_get_path(WEB_COURSE_PATH).'/document/icons/';

$move_temp_sys_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/';
$move_temp_web_path = api_get_path(WEB_COURSE_PATH).$course_directory.'/document/icons/';

$options['script_url'] = api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/server/php/';
$options['upload_dir'] = $temp_sys_path;
$options['upload_url'] = $temp_web_path;
$options['accept_file_types'] = '/\.(gif|jpe?g|png)$/i';
//$options['course_code'] = $course_code;
$options['thumbnail']['max_width'] = 120;
$options['thumbnail']['max_height'] = 90;
$uploadHandler = new UploadAjaxHandler($options);  

?>