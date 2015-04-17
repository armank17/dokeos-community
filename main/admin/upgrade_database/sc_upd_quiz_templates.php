<?php
require_once '../../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

$tbl_quiz_question_templates = Database::get_main_table(TABLE_MAIN_QUIZ_QUESTION_TEMPLATES);

$html_img8 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
<tbody><tr><td height=\"323px\" align=\"center\">           
                <div id=\"player504837-parent\">
                <div style=\"border-style: none; height: 240px; width: 320px; overflow: hidden; background-color: rgb(220, 220, 220);\"><script src=\"/main/inc/lib/swfobject/swfobject.js\" type=\"text/javascript\"></script>
                <div id=\"player504837\"><a href=\"http://www.macromedia.com/go/getflashplayer\" target=\"_blank\">Get the Flash Player</a> to see this video.
                <div id=\"player504837-config\" style=\"display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;\">url=".api_get_path(WEB_CODE_PATH)."default_course_document/video/flv/OpenofficeSlideshow.flv width=320 height=240 loop=false play=false downloadable=false fullscreen=true displayNavigation=true displayDigits=true align=left playlistThumbs=false</div>
                </div>
                <script type=\"text/javascript\">
var s1 = new SWFObject(\"/main/inc/lib/mediaplayer/player.swf\",\"single\",\"320\",\"240\",\"7\");
s1.addVariable(\"width\",\"320\");
s1.addVariable(\"height\",\"240\");
s1.addVariable(\"autostart\",\"false\");
s1.addVariable(\"file\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/OpenofficeSlideshow.flv'."\");
s1.addVariable(\"repeat\",\"false\");
s1.addVariable(\"showdownload\",\"false\");
s1.addVariable(\"link\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/OpenofficeSlideshow.flv'."\");
s1.addParam(\"allowfullscreen\",\"true\");
s1.addVariable(\"showdigits\",\"true\");
s1.addVariable(\"shownavigation\",\"true\");
s1.addVariable(\"logo\",\"\");
s1.write(\"player504837\");
</script></div></div><p>&nbsp;</p></td></tr></tbody></table>";

//Question and answer set 8
Database::query("UPDATE $tbl_quiz_question_templates SET `description` = '".$html_img8."' WHERE `question` = 'DefaultQuizQuestion8'");
echo "UPDATE $tbl_quiz_question_templates `question` 'DefaultQuizQuestion8'   [OK]" ;
echo "<br/>";
	$html_img10 = "<p style=\"text-align: center;\">&nbsp;</p>
	<div id=\"player28445-parent\">
	<div style=\"border-style: none; height: 240px; width: 320px; overflow: hidden; background-color: rgb(220, 220, 220); margin-left: auto; margin-right: auto;\"><script src=\"/main/inc/lib/swfobject/swfobject.js\" type=\"text/javascript\"></script>
	<div id=\"player28445\"><a target=\"_blank\" href=\"http://www.macromedia.com/go/getflashplayer\">Get the Flash Player</a> to see this video.
	<div id=\"player28445-config\" style=\"display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;\">url=".api_get_path(WEB_CODE_PATH)."default_course_document/video/flv/Bloedstolling.flv width=320 height=240 loop=false play=false downloadable=false fullscreen=true displayNavigation=true displayDigits=true align=center playlistThumbs=false</div>
	</div><script type=\"text/javascript\">
		var s1 = new SWFObject(\"/main/inc/lib/mediaplayer/player.swf\",\"single\",\"320\",\"240\",\"7\");
		s1.addVariable(\"width\",\"320\");
		s1.addVariable(\"height\",\"240\");
		s1.addVariable(\"autostart\",\"false\");
		s1.addVariable(\"file\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/Bloedstolling.flv'."\");
		s1.addVariable(\"repeat\",\"false\");
		s1.addVariable(\"showdownload\",\"false\");
		s1.addVariable(\"link\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/Bloedstolling.flv'."\");
		s1.addParam(\"allowfullscreen\",\"true\");
		s1.addVariable(\"showdigits\",\"true\");
		s1.addVariable(\"shownavigation\",\"true\");
		s1.addVariable(\"logo\",\"\");
		s1.write(\"player28445\");
	</script></div></div>";

	//Question and answer set 10
Database::query("UPDATE $tbl_quiz_question_templates SET `description` = '".$html_img10."' WHERE `question` =  'DefaultQuizQuestion10'");
echo "UPDATE $tbl_quiz_question_templates `question` 'DefaultQuizQuestion10'   [OK]" ;
?>
