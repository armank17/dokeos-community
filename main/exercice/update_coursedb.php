<?php
//Dokeos upgrade script for quiz template question in old courses.

// include the Dokeos Global File
$language_file = array('exercice','create_course');
include('../inc/global.inc.php');
include_once(api_get_path(LIBRARY_PATH).'add_course.lib.inc.php');

// Display the header
Display::display_header();

// making the connection to the MySQL server;
$conn=mysql_connect($_configuration['db_host'],$_configuration['db_user'], $_configuration['db_password'])	or die("Cannot connect. " . mysql_error());
// connect to the Dokeos Main database
mysql_select_db($_configuration['main_database']) or die("Cannot select table.". mysql_error());

// Displaying the title
echo "<h3>Update All Course Databases for Quiz question template gallery</h3>";

if ($_GET["action"]!=="go")
{
echo "<p><b>This file will update the course database with quiz template questions.</b><br>";

echo "<p>If you are sure that you want to do add quiz template questions, click <a href='$PHP_SELF?action=go'>continue ></a>";
}

if ($_GET["action"]=="go")
{
	$html_img1 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"350\" vspace=\"0\" hspace=\"0\" height=\"328\" alt=\"Price_elasticity_of_demand2.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Price_elasticity_of_demand2.png'."\" /></td></tr></tbody></table>";

	//Question and answer set 1
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion1', '".$html_img1."', 20.00, 1, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_1a', 0, 'Feedback_qn1_true', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", 'QuizAnswer_1b', 1, 'Feedback_qn1_true', 20.00, 2, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);	

	$html_img2 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"310\" vspace=\"0\" hspace=\"0\" height=\"310\" alt=\"Cross_elasticity_of_demand_complements.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Cross_elasticity_of_demand_complements.png'."\" /></td>
	</tr></tbody></table>";	

	//Question and answer set 2
	Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion2', '".$html_img2."', 20.00, 2, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(4, ".$question_id.", 'QuizAnswer_2d', 0, 'Feedback_qn2_true', 0.00, 4, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", 'QuizAnswer_2c', 0, 'Feedback_qn2_true', 0.00, 3, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", 'QuizAnswer_2b', 1, 'Feedback_qn2_true', 20.00, 2, '', '', '0@@0@@0@@0'),
	(1, ".$question_id.", 'QuizAnswer_2a', 0, 'Feedback_qn2_true', 0.00, 1, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);	

	$html_img3 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td height=\"323px\" align=\"center\"><img  src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/heartArrows4Numbers300.png'."\"  /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>";

	//Question and answer set 3
	Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion3', '".$html_img3."', 20.00, 3, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(4, ".$question_id.", 'QuizAnswer_3d', 0, '', 0.00, 4, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", 'QuizAnswer_3c', 0, '', 0.00, 3, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", 'QuizAnswer_3b', 1, '', 20.00, 2, '', '', '0@@0@@0@@0'),
	(1, ".$question_id.", 'QuizAnswer_3a', 0, '', 0.00, 1, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	$html_img4 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td height=\"323px\" align=\"center\"><img height=\"310px\" src=\"../img/instructor-faq.png\"  /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>";	

	//Question and answer set 4
	Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion4', '".$html_img4."', 20.00, 4, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_4a', 0, 'Feedback_qn4_true', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", 'QuizAnswer_4b', 0, 'Feedback_qn4_true', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", 'QuizAnswer_4c', 0, 'Feedback_qn4_true', 0.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", 'QuizAnswer_4d', 1, 'Feedback_qn4_true', 20.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);
	
	$html_img5 = "<p style=\"text-align: center;\">".lang2db(get_lang('html_img5_text'))."</p><p style=\"text-align: center;\"></p><p style=\"text-align: center;\"><img border=\"0\" align=\"middle\" width=\"200\" vspace=\"0\" hspace=\"0\" height=\"133\" alt=\"Cornell_dormitories2.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Cornell_dormitories2.jpg'."\" /></p>";
	
	//Question and answer set 5
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion5', '".$html_img5."', 20.00, 5, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_5a', 0, 'Feedback_qn5_true', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", 'QuizAnswer_5b', 0, 'Feedback_qn5_true', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", 'QuizAnswer_5c', 0, 'Feedback_qn5_true', 0.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", 'QuizAnswer_5d', 1, 'Feedback_qn5_true', 20.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);	

	$html_ans_6a = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer1_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer1_1.png'."\" /></p>";
	$html_ans_6b = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer2.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer2.png'."\" /></p>";
	$html_ans_6c = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer3_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer3_1.png'."\" /></p>";
	$html_ans_6d = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer4_3.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer4_3.png'."\" /></p>";

	$html_img6 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"377\" vspace=\"0\" hspace=\"0\" height=\"300\" alt=\"HPQuestion_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPQuestion_1.png'."\" /></td></tr></tbody></table>";

	$html_img6_feedback_true = "<p><img border=\"0\" align=\"middle\" width=\"376\" vspace=\"0\" hspace=\"0\" height=\"300\" alt=\"HPfeedback_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPfeedback_1.png'."\" />".lang2db(get_lang('html_img6_feedback_text'))."</p>";

	//Question and answer set 6
	Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion6', '".$html_img6."', 20.00, 6, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_6a."', 0, '".$html_img6_feedback_true."', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".$html_ans_6b."', 0, '".$html_img6_feedback_true."', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".$html_ans_6c."', 1, '".$html_img6_feedback_true."', 20.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", '".$html_ans_6d."', 0, '".$html_img6_feedback_true."', 0.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);
	
	$html_img7 = "<table cellspacing=\"2\" cellpadding=\"0\ width=\"98%\" height=\100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
    <tbody><tr><td align=\"center\" height=\"323px\"><p><img height=\"310px\" alt=\"\" src=\"../img/instructor-faq.png\" /></p>
	<p><embed width=\"300\" height=\"20\" flashvars=\"file=".api_get_path(WEB_CODE_PATH)."default_course_document/audio/EconomicCensus.mp3&autostart=false\" allowscriptaccess=\"always\" allowfullscreen=\"false\" src=\"/main/inc/lib/mediaplayer/player.swf\" bgcolor=\"#FFFFFF\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></p></td></tr></tbody></table>";

	//Question and answer set 7
	Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion7', '".$html_img7."', 20.00, 7, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(4, ".$question_id.", 'QuizAnswer_7a', 1, 'Feedback_qn7_true', 20.00, 4, '', '', ''),
	(3, ".$question_id.", 'QuizAnswer_7b', 0, 'Feedback_qn7_true', 0.00, 3, '', '', ''),
	(2, ".$question_id.", 'QuizAnswer_7c', 0, 'Feedback_qn7_true', 0.00, 2, '', '', ''),
	(1, ".$question_id.", 'QuizAnswer_7d', 0, 'Feedback_qn7_true', 0.00, 1, '', '', '')", __FILE__, __LINE__);
	
	$html_img8 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td height=\"323px\" align=\"center\">           
			<div id=\"player504837-parent\" style=\"text-align: center;\">
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
	Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion8', '".$html_img8."', 20.00, 8, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_8a', 0, '', 0.00, 1, '', '', ''),
	(2, ".$question_id.", 'QuizAnswer_8b', 0, '', 0.00, 2, '', '', ''),
	(3, ".$question_id.", 'QuizAnswer_8c', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", 'QuizAnswer_8d', 1, '', 20.00, 4, '', '', '')", __FILE__, __LINE__);
	
	$html_img9 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td height=\"323px\" align=\"center\"><embed height=\"300\" width=\"350\" menu=\"true\" loop=\"true\" play=\"true\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/animations/SpinEchoSequence.swf'."\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></td></tr></tbody></table>";

	//Question and answer set 9
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion9', '".$html_img9."', 20.00, 9, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_9a', 0, 'Feedback_qn9_true', 0.00, 1, '', '', ''),
	(2, ".$question_id.", 'QuizAnswer_9b', 0, 'Feedback_qn9_true', 0.00, 2, '', '', ''),
	(3, ".$question_id.", 'QuizAnswer_9c', 0, 'Feedback_qn9_true', 0.00, 3, '', '', ''),
	(4, ".$question_id.", 'QuizAnswer_9d', 1, 'Feedback_qn9_true', 20.00, 4, '', '', '')", __FILE__, __LINE__);
	
	$html_img10 = "<p style=\"text-align: center;\">&nbsp;</p>
	<div id=\"player28445-parent\" style=\"text-align: center;\">
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
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion10', '".$html_img10."', 20.00, 10, 1, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_10a', 1, 'Feedback_qn10_true', 20.00, 1, '', '', ''),
	(2, ".$question_id.", 'QuizAnswer_10b', 0, 'Feedback_qn10_true', 0.00, 2, '', '', ''),
	(3, ".$question_id.", 'QuizAnswer_10c', 0, 'Feedback_qn10_true', 0.00, 3, '', '', ''),
	(4, ".$question_id.", 'QuizAnswer_10d', 0, 'Feedback_qn10_true', 0.00, 4, '', '', '')", __FILE__, __LINE__);
	
	$html_img11 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"143\" alt=\"sleeping_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/sleeping_1.png'."\" /></td></tr></tbody></table>";

	//Question and answer set 11
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion11', '".$html_img11."', 20.00, 11, 2, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_11a', 1, 'Feedback_qn8_true', 10.00, 1, '', '', ''),
	(2, ".$question_id.", 'QuizAnswer_11b', 1, 'Feedback_qn8_true', 10.00, 2, '', '', ''),
	(3, ".$question_id.", 'QuizAnswer_11c', 0, 'Feedback_qn8_true', 0.00, 3, '', '', ''),
	(4, ".$question_id.", 'QuizAnswer_11d', 1, 'Feedback_qn8_true', 10.00, 4, '', '', '')", __FILE__, __LINE__);	

	$html_img12 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"239\" alt=\"Solar_sys.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Solar_sys.jpg'."\" /></td></tr></tbody></table>";

	//Question and answer set 12
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion12', '".$html_img12."', 20.00, 12, 8, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_12a', 1, 'Feedback_qn12_true', 10.00, 1, '', '', ''),
	(2, ".$question_id.", 'QuizAnswer_12b', 0, 'Feedback_qn12_true', 0.00, 2, '', '', ''),
	(3, ".$question_id.", 'QuizAnswer_12c', 0, 'Feedback_qn12_true', 0.00, 3, '', '', ''),
	(4, ".$question_id.", 'QuizAnswer_12d', 1, 'Feedback_qn12_true', 10.00, 4, '', '', '')", __FILE__, __LINE__);
	
	$html_img13 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td height=\"323px\" align=\"center\"><img hspace=\"0\" height=\"345\" width=\"350\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Traffic_lights.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Traffic_lights.jpg'."\" /></td></tr></tbody></table>";

	$html_ans_13a = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"truck.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/truck.jpg'."\" /></p>";
	$html_ans_13b = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"railroad.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/railroad.jpg'."\" /></p>";
	$html_ans_13c = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"deer.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/deer.jpg'."\" /></p>";
	$html_ans_13d = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"pedestrian.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/pedestrian.jpg'."\" /></p>";

	//Question and answer set 13
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion13', '".$html_img13."', 20.00, 13, 2, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_13a."', 1, 'Feedback_qn13_true', 10.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".$html_ans_13b."', 0, 'Feedback_qn13_true', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".$html_ans_13c."', 0, 'Feedback_qn13_true', 0.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", '".$html_ans_13d."', 1, 'Feedback_qn13_true', 10.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);
	
	$html_img14 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"300\" vspace=\"0\" hspace=\"0\" height=\"227\" alt=\"ViolentCrimeAmerica.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ViolentCrimeAmerica.png'."\" /></td></tr></tbody></table>";

	//Question and answer set 14
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion14', '".$html_img14."', 20.00, 14, 8, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'QuizAnswer_14a', 1, '', 10.00, 1, '', '', ''),
	(2, ".$question_id.", 'QuizAnswer_14b', 0, '', 0.00, 2, '', '', ''),
	(3, ".$question_id.", 'QuizAnswer_14c', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", 'QuizAnswer_14d', 1, '', 10.00, 4, '', '', '')", __FILE__, __LINE__);	

	$html_img15 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img alt=\"\" src=\"../img/KnockOnWood.png\" /></td></tr></tbody></table>";

	$html_ans_15 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td style=\"text-align: center;\"><strong>Treatment</strong></td><td style=\"text-align: center;\"><strong>Y</strong> or<strong> N</strong></td><td><p><strong>1</strong> = on day 1</p><p><strong>0</strong> = none</p><p><strong>D</strong> = discharge day</p></td></tr><tr><td style=\"text-align: center;\"><strong>Malaria </strong></td>
	<td style=\"text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">&nbsp;[<u>1</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Polio </strong></td><td style=\"text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u> D</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Pneumococcus vaccin </strong></td><td style=\"text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>0</u>]&nbsp;&nbsp;</td></tr></tbody></table><p>&nbsp;</p>::10,10,10,10,10,10@";

	$comment_15 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

	//Question and answer set 15
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion15', '".$html_img15."', 60.00, 15, 3, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_15."', 1, '".$comment_15."', 0.00, 0, '', '', '')", __FILE__, __LINE__);	

	$html_img16 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"270\" vspace=\"0\" hspace=\"0\" height=\"320\" alt=\"balance_scale_redone.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/balance_scale_redone.jpg'."\" /></td></tr></tbody></table>";

	$html_ans_16 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"380\"><tbody><tr><td bgcolor=\"#f5f5f5\" style=\"text-align: right;\"><strong>Patient</strong></td><td bgcolor=\"#f5f5f5\" style=\"text-align: center;\"><strong>Laura</strong></td><td bgcolor=\"#f5f5f5\" style=\"text-align: center;\"><strong>Bill</strong></td></tr><tr><td style=\"text-align: right;\">Age</td><td style=\"text-align: center;\">38</td><td style=\"text-align: center;\">44</td></tr><tr><td style=\"text-align: right;\">Height</td><td style=\"text-align: center;\">1.72 m</td><td style=\"text-align: center;\">1.88 m</td>   </tr><tr><td style=\"text-align: right;\">Weight</td><td style=\"text-align: center;\">65 kg</td><td style=\"text-align: center;\">[<u>103</u>] kg</td></tr><tr><td style=\"text-align: right;\">Blood Pressure</td><td style=\"text-align: center;\">120/75</td><td style=\"text-align: center;\">11/65</td></tr> <tr><td style=\"vertical-align: top; text-align: right;\">BMI</td><td style=\"vertical-align: top; text-align: center;\">[<u>22</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">&nbsp;29</td></tr></tbody></table>::10,10@";

	$comment_16 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

	//Question and answer set 16
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion16', '".$html_img16."', 20.00, 16, 3, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_16."', 1, '".$comment_16."', 0.00, 0, '', '', '')", __FILE__, __LINE__);

	$html_ans_17 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td>&nbsp;</td><td style=\"text-align: center;\"><strong>H</strong></td><td style=\"text-align: center;\"><strong>W</strong></td><td style=\"text-align: center;\"><strong>M</strong></td><td style=\"text-align: center;\"><strong>O</strong></td><td style=\"text-align: center;\"><strong>NS<br /></strong></td></tr><tr><td style=\"text-align: center;\"><strong>Laura</strong></td><td style=\"text-align: center;\">89</td><td style=\"text-align: center;\">12.3</td><td style=\"text-align: center;\">140</td><td style=\"text-align: center;\">Y</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>John</strong></td><td style=\"text-align: center;\">73.5</td><td style=\"text-align: center;\">6.3</td><td style=\"text-align: center;\">124</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Anna</strong></td><td style=\"text-align: center;\">94.5</td><td style=\"text-align: center;\">10</td><td style=\"text-align: center;\">108</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Bill</strong></td><td style=\"text-align: center;\">120</td><td style=\"text-align: center;\">13.8</td><td style=\"text-align: center;\">112</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Peter</strong></td><td style=\"text-align: center;\">67</td><td style=\"text-align: center;\">7.4</td><td style=\"text-align: center;\">130</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td></tr></tbody></table><p>H = Height in cm, W = Weight in kg, M = Muac in mm, O = Oedema present Yes/No</p>::10,10,10,10,10@";

	$comment_17 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

	//Question and answer set 17
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion17', '".$html_img15."', 50.00, 17, 3, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_17."', 1, '".$comment_17."', 0.00, 0, '', '', '')", __FILE__, __LINE__);
	
	$html_ans_18 = "<p>".lang2db(get_lang('html_ans_18_text'))."<sqdf></sqdf></p>::10,10,10@";

	$html_img18 = "<table cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px; width: 375px; height: 277px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"254\" vspace=\"0\" hspace=\"0\" height=\"200\" alt=\"SpeechMike.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/mascot/SpeechMike.png'."\" /><embed width=\"300\" height=\"20\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" bgcolor=\"#FFFFFF\" src=\"/main/inc/lib/mediaplayer/player.swf\" allowfullscreen=\"false\" allowscriptaccess=\"always\" flashvars=\"file=".api_get_path(WEB_CODE_PATH)."default_course_document/audio/EconCensus64.mp3&autostart=false\"></embed></td></tr></tbody></table>";

	$comment_18 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

	//Question and answer set 18
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion18', '".$html_img18."', 30.00, 18, 3, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_18."', 1, '".$comment_18."', 0.00, 0, '', '', '')", __FILE__, __LINE__);
	
	$html_ans_19 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td>&nbsp;</td><td style=\"text-align: center;\">[<u>M</u>]&nbsp;&nbsp;</td><td>&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"text-align: center;\">[<u>V</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>O</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td>
   <td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td>
   <td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr>
	<tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>T</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>R</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>A</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">[<u>T</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>E</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>G</u>]&nbsp;&nbsp;</td>
	<td style=\"vertical-align: top; text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top; text-align: center;\">[<u>P</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">[<u>O</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>L</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>C</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr></tbody></table><p>&nbsp;</p>::10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10@";

	$html_img19 = "<p>&nbsp;</p><p>1 Vertical&nbsp; : In the B-bath Company, it is to make soap<br />1 Horizontal : Intended direction <br />2 Horizontal : provides a guideline to managers decision making<br />3 Horizontal contains rules</p><p style=\"text-align: center;\">&nbsp;</p><p style=\"text-align: center;\"><img border=\"0\" align=\"middle\" width=\"239\" vspace=\"0\" hspace=\"0\" height=\"150\" alt=\"240business_meeting.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/240business_meeting.jpg'."\" /></p>";

	//Question and answer set 19
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion19', '".$html_img19."', 250.00, 19, 3, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_19."', 1, '".$comment_19."', 0.00, 0, '', '', '')", __FILE__, __LINE__);
	
	$html_img20 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td height=\"323px\" align=\"center\"><img hspace=\"0\" height=\"205\" width=\"350\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"6Hats_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/6Hats_1.png'."\" /></td></tr></tbody></table>";	

	//Question and answer set 20
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion20', '".$html_img20."', 20.00, 20, 5, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();	

	$html_img21 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody><tr>  <td align=\"center\" height=\"323px\"><img alt=\"\" src=\"../img/instructor-idea.jpg\" /></td></tr></tbody></table>";

	//Question and answer set 21
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion21', '".$html_img21."', 20.00, 21, 5, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	$html_img22 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody><tr>  <td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"309\" alt=\"Board2_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Board2_1.png'."\" /></td></tr></tbody></table>";

	//Question and answer set 22
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion22', '".$html_img22."', 20.00, 22, 5, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	//Question and answer set 23
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuestion18', '', 20.00, 23, 4, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p>Columbia River <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ColumbiaRiverTr64.png'."\" alt=\"ColumbiaRiverTr64.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn18_true'))."', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p>Rio Grande <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/RioGrandeTr64.png'."\" alt=\"RioGrandeTr64.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn18_false'))."', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p>Tenesse River <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/TenesseeRiverTr64.png'."\" alt=\"TenesseeRiverTr64.png\" /></p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '<p>Arkanas River&nbsp; <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ArkansasRiverTr64.png'."\" alt=\"ArkansasRiverTr64.png\" /></p>', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", '<p>New Mexico <img hspace=\"0\" height=\"64\" width=\"68\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/New_Mexico2tr64.png'."\" alt=\"New_Mexico2tr64.png\" /></p>', 1, '', 5.00, 5, '', '', ''),
	(6, ".$question_id.", '<p>Alabama <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AlabampaMapOutlineBlue2Tr64.png'."\" alt=\"AlabampaMapOutlineBlue2Tr64.png\" /></p>', 1, '', 5.00, 6, '', '', ''),
	(7, ".$question_id.", '<p>Oklahoma&nbsp; <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/OklahomaMapOutline3Tr64.png'."\" alt=\"OklahomaMapOutline3Tr64.png\" /></p>', 1, '', 5.00, 7, '', '', ''),
	(8, ".$question_id.", '<p>Washington <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/WashingtonStateMapOutline2tr64.png'."\" alt=\"WashingtonStateMapOutline2tr64.png\" /></p>', 1, '', 5.00, 8, '', '', '')", __FILE__, __LINE__);
	
	//Question and answer set 24
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuestion19', '', 20.00, 24, 4, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medical15.png'."\" alt=\"medical15.png\" />&nbsp; Check Skin Temperature</p>', 0, '', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medic25.png'."\" alt=\"medic25.png\" />&nbsp; Call Ambulance</p>', 0, '', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medicalhandpointing.png'."\" alt=\"medicalhandpointing.png\" /> Tell casuality not to move</p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/01.png'."\" alt=\"01.png\" /></p>', 3, '', 6.67, 5, '', '', ''),
	(6, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/02.png'."\" alt=\"02.png\" /></p>', 2, '', 6.67, 6, '', '', ''),
	(7, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/03.png'."\" alt=\"03.png\" /></p>', 1, '', 6.67, 7, '', '', '')", __FILE__, __LINE__);

	//Question and answer set 25
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion24', '', 20.00, 25, 4, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"31\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/A.png'."\" alt=\"A.png\" /></p>', 0, 'Feedback_qn24_true', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"37\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/B_1.png'."\" alt=\"B_1.png\" /></p>', 0, 'Feedback_qn24_true', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"36\" width=\"199\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/_AorB_andnonA.png'."\" alt=\"_AorB_andnonA.png\" /></p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"145\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AandnonA.png'."\" alt=\"AandnonA.png\" /></p>', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"111\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AorB.png'."\" alt=\"AorB.png\" /></p>', 0, '', 0.00, 5, '', '', ''),
	(6, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/01.png'."\" alt=\"01.png\" /></p>', 4, '', 4.00, 6, '', '', ''),
	(7, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/02.png'."\" alt=\"02.png\" /></p>', 1, '', 4.00, 7, '', '', ''),
	(8, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/03.png'."\" alt=\"03.png\" /></p>', 5, '', 4.00, 8, '', '', ''),
	(9, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/04.png'."\" alt=\"04.png\" /></p>', 3, '', 4.00, 9, '', '', ''),
	(10, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/05.png'."\" alt=\"05.png\" /></p>', 2, '', 4.00, 10, '', '', '')", __FILE__, __LINE__);

	//Question and answer set 26
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion25', '', 20.00, 26, 4, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Compression.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Compression.jpeg'."\" /></p>', 0, 'Feedback_qn25_true', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Emission.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Emission.jpeg'."\" /></p>', 0, 'Feedback_qn25_true', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Ignition.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Ignition.jpeg'."\" /></p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Induction.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Induction.jpeg'."\" /></p>', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", 'langQuizAnswer_25a', 3, '', 5.00, 5, '', '', ''),
	(6, ".$question_id.", 'langQuizAnswer_25b', 1, '', 5.00, 6, '', '', ''),
	(7, ".$question_id.", 'langQuizAnswer_25c', 4, '', 5.00, 7, '', '', ''),
	(8, ".$question_id.", 'langQuizAnswer_25d', 2, '', 5.00, 8, '', '', '')", __FILE__, __LINE__);

	//Question and answer set 27	
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion27', '', 40.00, 27, 6, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-27.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');
	
	Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'langQuizAnswer_27a', 0, '', 10.00, 1, '42;166|32|38', 'square', ''),
	(2, ".$question_id.", 'langQuizAnswer_27b', 0, '', 10.00, 2, '122;283|75|120', 'circle', ''),
	(3, ".$question_id.", 'langQuizAnswer_27c', 0, '', 10.00, 3, '116;45|13|55', 'square', ''),
	(4, ".$question_id.", 'langQuizAnswer_27d', 0, '', 10.00, 4, '116;152|50|90', 'square', '')", __FILE__, __LINE__);

	//Question and answer set 28
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion28', '', 30.00, 28, 6, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-28.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');
	
	Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'langQuizAnswer_28a', 0, '', 10.00, 1, '114;221|27|28', 'square', ''),
	(2, ".$question_id.", 'langQuizAnswer_28b', 0, '', 10.00, 2, '164;53|39|18', 'square', ''),
	(3, ".$question_id.", 'langQuizAnswer_28c', 0, '', 10.00, 3, '158;87|48|26', 'square', '')", __FILE__, __LINE__);
	
	//Question and answer set 29
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion29', '', 30.00, 29, 6, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();	
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-29.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');

	Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'langQuizAnswer_29a', 0, '', 10.00, 1, '203;17|23|30', 'square', ''),
	(2, ".$question_id.", 'langQuizAnswer_29b', 0, '', 10.00, 2, '133;294|59|20', 'square', ''),
	(3, ".$question_id.", 'langQuizAnswer_29c', 0, '', 10.00, 3, '306;184|93|22', 'square', '')", __FILE__, __LINE__);
	
	//Question and answer set 30
	Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
	('DefaultQuizQuestion30', '', 30.00, 30, 6, '', 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();	
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-30.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');

	Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", 'langQuizAnswer_30a', 0, '', 10.00, 1, '37;31|8|13', 'square', ''),
	(2, ".$question_id.", 'langQuizAnswer_30b', 0, '', 10.00, 2, '52;71|9|14', 'square', ''),
	(3, ".$question_id.", 'langQuizAnswer_30c', 0, '', 10.00, 3, '22;98|11|14', 'square', '')", __FILE__, __LINE__);	

}
// Display the footer
Display::display_footer();

function add_quiz_template_questions($courseRepository,$course_language){
	global $language_interface;
	if($course_language == 'french'){		
		$file_to_include = "lang/".$course_language . "/create_course.inc.php";		
		if (file_exists(api_get_path(SYS_CODE_PATH) . $file_to_include)){		
		include (api_get_path(SYS_CODE_PATH) . $file_to_include);
		}
	}
	else {
		include (api_get_path(SYS_CODE_PATH) . "lang/english/create_course.inc.php");
	}	
	
	$language_interface_tmp = $language_interface;
	if($course_language == 'french'){
		$language_interface = 'french';
	}
	else {
		$language_interface = 'english';
	}
	
	$TABLEQUIZ = 'quiz';
	$TABLEQUIZSCENARIO = 'quiz_scenario';
	$TABLEQUIZQUESTIONLIST = 'quiz_question';
	$TABLEQUIZANSWERSLIST = 'quiz_answer';
	$TABLEQUIZQUESTION = 'quiz_rel_question';

	$html=addslashes('<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td width=\"110\" valign=\"top\" align=\"left\"><img src=\"'.api_get_path(WEB_CODE_PATH).'default_course_document/images/mr_dokeos/thinking.jpg\"></td><td valign=\"top\" align=\"left\">'.lang2db(get_lang('Antique')).'</td></tr></table>');	
	Database::query('INSERT INTO '.$TABLEQUIZ . ' (title, description, type, random, active, results_disabled ) VALUES ("'.lang2db(get_lang('DefaultExercise')) . '", "'.$html.'", "1", "0", "-1", "0")', __FILE__, __LINE__);	
	$quiz_id = Database::insert_id();

	Database::query('INSERT INTO '.$TABLEQUIZSCENARIO . ' (exercice_id, scenario_type, title, description, type, random, active, results_disabled ) VALUES ('.$quiz_id.', "1", "'.lang2db(get_lang('DefaultExercise')) . '", "'.$html.'", "1", "0", "-1", "0")', __FILE__, __LINE__);
	Database::query('INSERT INTO '.$TABLEQUIZSCENARIO . ' (exercice_id, scenario_type, title, description, type, random, active, results_disabled ) VALUES ('.$quiz_id.', "2", "'.lang2db(get_lang('DefaultExercise')) . '", "'.$html.'", "1", "0", "-1", "0")', __FILE__, __LINE__);

	$html_img1 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"350\" vspace=\"0\" hspace=\"0\" height=\"328\" alt=\"Price_elasticity_of_demand2.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Price_elasticity_of_demand2.png'."\" /></td></tr></tbody></table>";

	$html_img2 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"310\" vspace=\"0\" hspace=\"0\" height=\"310\" alt=\"Cross_elasticity_of_demand_complements.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Cross_elasticity_of_demand_complements.png'."\" /></td>
	</tr></tbody></table>";		
	
	$html_img3 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td height=\"323px\" align=\"center\"><img  src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/heartArrows4Numbers300.png'."\"  /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>";	

	$html_img4 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td height=\"323px\" align=\"center\"><img height=\"310px\" src=\"../img/instructor-faq.png\"  /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>";	

	$html_img5 = "<p style=\"text-align: center;\">".lang2db(get_lang('html_img5_text'))."</p><p style=\"text-align: center;\"></p><p style=\"text-align: center;\"><img border=\"0\" align=\"middle\" width=\"200\" vspace=\"0\" hspace=\"0\" height=\"133\" alt=\"Cornell_dormitories2.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Cornell_dormitories2.jpg'."\" /></p>";
	$html_ans_6a = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer1_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer1_1.png'."\" /></p>";
	$html_ans_6b = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer2.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer2.png'."\" /></p>";
	$html_ans_6c = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer3_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer3_1.png'."\" /></p>";
	$html_ans_6d = "<p><img border=\"0\" align=\"middle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer4_3.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer4_3.png'."\" /></p>";
	$html_img6 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"377\" vspace=\"0\" hspace=\"0\" height=\"300\" alt=\"HPQuestion_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPQuestion_1.png'."\" /></td></tr></tbody></table>";

	$html_img6_feedback_true = "<p><img border=\"0\" align=\"middle\" width=\"376\" vspace=\"0\" hspace=\"0\" height=\"300\" alt=\"HPfeedback_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPfeedback_1.png'."\" />".lang2db(get_lang('html_img6_feedback_text'))."</p>";

	$html_img7 = "<table cellspacing=\"2\" cellpadding=\"0\ width=\"98%\" height=\100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
   <tbody><tr><td align=\"center\" height=\"323px\"><p><img height=\"310px\" alt=\"\" src=\"../img/instructor-faq.png\" /></p>
		<p><embed width=\"300\" height=\"20\" flashvars=\"file=".api_get_path(WEB_CODE_PATH)."default_course_document/audio/EconomicCensus.mp3&autostart=false\" allowscriptaccess=\"always\" allowfullscreen=\"false\" src=\"/main/inc/lib/mediaplayer/player.swf\" bgcolor=\"#FFFFFF\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></p></td></tr></tbody></table>";

	$html_img8 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td height=\"323px\" align=\"center\">           
			<div id=\"player504837-parent\" style=\"text-align: center;\">
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

	$html_img9 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td height=\"323px\" align=\"center\"><embed height=\"300\" width=\"350\" menu=\"true\" loop=\"true\" play=\"true\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/animations/SpinEchoSequence.swf'."\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></td></tr></tbody></table>";

	$html_img10 = "<p style=\"text-align: center;\">&nbsp;</p>
	<div id=\"player28445-parent\" style=\"text-align: center;\">
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

	$html_img11 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
	<tbody><tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"143\" alt=\"sleeping_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/sleeping_1.png'."\" /></td></tr></tbody></table>";

	$html_img12 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"239\" alt=\"Solar_sys.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Solar_sys.jpg'."\" /></td></tr></tbody></table>";

	$html_img13 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td height=\"323px\" align=\"center\"><img hspace=\"0\" height=\"345\" width=\"350\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Traffic_lights.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Traffic_lights.jpg'."\" /></td></tr></tbody></table>";

	$html_ans_13a = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"truck.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/truck.jpg'."\" /></p>";
	$html_ans_13b = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"railroad.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/railroad.jpg'."\" /></p>";
	$html_ans_13c = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"deer.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/deer.jpg'."\" /></p>";
	$html_ans_13d = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"pedestrian.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/pedestrian.jpg'."\" /></p>";

	$html_img14 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"300\" vspace=\"0\" hspace=\"0\" height=\"227\" alt=\"ViolentCrimeAmerica.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ViolentCrimeAmerica.png'."\" /></td></tr></tbody></table>";

	$html_img15 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img alt=\"\" src=\"../img/KnockOnWood.png\" /></td></tr></tbody></table>";

	$html_ans_15 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td style=\"text-align: center;\"><strong>Treatment</strong></td><td style=\"text-align: center;\"><strong>Y</strong> or<strong> N</strong></td><td><p><strong>1</strong> = on day 1</p><p><strong>0</strong> = none</p><p><strong>D</strong> = discharge day</p></td></tr><tr><td style=\"text-align: center;\"><strong>Malaria </strong></td>
	<td style=\"text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">&nbsp;[<u>1</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Polio </strong></td><td style=\"text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u> D</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Pneumococcus vaccin </strong></td><td style=\"text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>0</u>]&nbsp;&nbsp;</td></tr></tbody></table><p>&nbsp;</p>::10,10,10,10,10,10@";

	$comment_15 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";
	$comment_16 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";
	$comment_17 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";
	$comment_18 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";
	$comment_19 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

	$html_img16 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"270\" vspace=\"0\" hspace=\"0\" height=\"320\" alt=\"balance_scale_redone.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/balance_scale_redone.jpg'."\" /></td></tr></tbody></table>";

	$html_ans_16 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"380\"><tbody><tr><td bgcolor=\"#f5f5f5\" style=\"text-align: right;\"><strong>Patient</strong></td><td bgcolor=\"#f5f5f5\" style=\"text-align: center;\"><strong>Laura</strong></td><td bgcolor=\"#f5f5f5\" style=\"text-align: center;\"><strong>Bill</strong></td></tr><tr><td style=\"text-align: right;\">Age</td><td style=\"text-align: center;\">38</td><td style=\"text-align: center;\">44</td></tr><tr><td style=\"text-align: right;\">Height</td><td style=\"text-align: center;\">1.72 m</td><td style=\"text-align: center;\">1.88 m</td>   </tr><tr><td style=\"text-align: right;\">Weight</td><td style=\"text-align: center;\">65 kg</td><td style=\"text-align: center;\">[<u>103</u>] kg</td></tr><tr><td style=\"text-align: right;\">Blood Pressure</td><td style=\"text-align: center;\">120/75</td><td style=\"text-align: center;\">11/65</td></tr> <tr><td style=\"vertical-align: top; text-align: right;\">BMI</td><td style=\"vertical-align: top; text-align: center;\">[<u>22</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">&nbsp;29</td></tr></tbody></table>::10,10@";

	$html_ans_17 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td>&nbsp;</td><td style=\"text-align: center;\"><strong>H</strong></td><td style=\"text-align: center;\"><strong>W</strong></td><td style=\"text-align: center;\"><strong>M</strong></td><td style=\"text-align: center;\"><strong>O</strong></td><td style=\"text-align: center;\"><strong>NS<br /></strong></td></tr><tr><td style=\"text-align: center;\"><strong>Laura</strong></td><td style=\"text-align: center;\">89</td><td style=\"text-align: center;\">12.3</td><td style=\"text-align: center;\">140</td><td style=\"text-align: center;\">Y</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>John</strong></td><td style=\"text-align: center;\">73.5</td><td style=\"text-align: center;\">6.3</td><td style=\"text-align: center;\">124</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Anna</strong></td><td style=\"text-align: center;\">94.5</td><td style=\"text-align: center;\">10</td><td style=\"text-align: center;\">108</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Bill</strong></td><td style=\"text-align: center;\">120</td><td style=\"text-align: center;\">13.8</td><td style=\"text-align: center;\">112</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Peter</strong></td><td style=\"text-align: center;\">67</td><td style=\"text-align: center;\">7.4</td><td style=\"text-align: center;\">130</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td></tr></tbody></table><p>H = Height in cm, W = Weight in kg, M = Muac in mm, O = Oedema present Yes/No</p>::10,10,10,10,10@";

	$html_ans_18 = "<p>".lang2db(get_lang('html_ans_18_text'))."<sqdf></sqdf></p>::10,10,10@";

	$html_img18 = "<table cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px; width: 375px; height: 277px;\"><tbody>
	<tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"254\" vspace=\"0\" hspace=\"0\" height=\"200\" alt=\"SpeechMike.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/mascot/SpeechMike.png'."\" /><embed width=\"300\" height=\"20\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" bgcolor=\"#FFFFFF\" src=\"/main/inc/lib/mediaplayer/player.swf\" allowfullscreen=\"false\" allowscriptaccess=\"always\" flashvars=\"file=".api_get_path(WEB_CODE_PATH)."default_course_document/audio/EconCensus64.mp3&autostart=false\"></embed></td></tr></tbody></table>";

	$html_ans_19 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td>&nbsp;</td><td style=\"text-align: center;\">[<u>M</u>]&nbsp;&nbsp;</td><td>&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"text-align: center;\">[<u>V</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>O</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td>
   <td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td>
   <td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr>
	<tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>T</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>R</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>A</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">[<u>T</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>E</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>G</u>]&nbsp;&nbsp;</td>
	<td style=\"vertical-align: top; text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top; text-align: center;\">[<u>P</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">[<u>O</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>L</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>C</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr></tbody></table><p>&nbsp;</p>::10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10@";

	$html_img19 = "<p>&nbsp;</p><p>1 Vertical&nbsp; : In the B-bath Company, it is to make soap<br />1 Horizontal : Intended direction <br />2 Horizontal : provides a guideline to managers decision making<br />3 Horizontal contains rules</p><p style=\"text-align: center;\">&nbsp;</p><p style=\"text-align: center;\"><img border=\"0\" align=\"middle\" width=\"239\" vspace=\"0\" hspace=\"0\" height=\"150\" alt=\"240business_meeting.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/240business_meeting.jpg'."\" /></p>";

	$html_img20 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
	<tr><td height=\"323px\" align=\"center\"><img hspace=\"0\" height=\"205\" width=\"350\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"6Hats_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/6Hats_1.png'."\" /></td></tr></tbody></table>";	
	
	$html_img21 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody><tr>  <td align=\"center\" height=\"323px\"><img alt=\"\" src=\"../img/instructor-idea.jpg\" /></td></tr></tbody></table>";

	$html_img22 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody><tr>  <td align=\"center\" height=\"323px\"><img border=\"0\" align=\"middle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"309\" alt=\"Board2_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Board2_1.png'."\" /></td></tr></tbody></table>";
	
	//Question and answer set 1
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion1')) ."', '".$html_img1."', 20.00, 1, 1, '', 1, 1)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_1a')) ."', 0, '".lang2db(get_lang('Feedback_qn1_true')) ."', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_1b')) ."', 1, '".lang2db(get_lang('Feedback_qn1_true')) ."', 20.00, 2, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 1)", __FILE__, __LINE__);

	//Question and answer set 2
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion2')) ."', '".$html_img2."', 20.00, 2, 1, '', 1, 2)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_2d')) ."', 0, '".lang2db(get_lang('Feedback_qn2_true')) ."', 0.00, 4, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_2c')) ."', 0, '".lang2db(get_lang('Feedback_qn2_true')) ."', 0.00, 3, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_2b')) ."', 1, '".lang2db(get_lang('Feedback_qn2_true')) ."', 20.00, 2, '', '', '0@@0@@0@@0'),
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_2a')) ."', 0, '".lang2db(get_lang('Feedback_qn2_true')) ."', 0.00, 1, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 2)", __FILE__, __LINE__);

	//Question and answer set 3
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion3')) ."', '".$html_img3."', 20.00, 3, 1, '', 1, 3)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_3d')) ."', 0, '', 0.00, 4, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_3c')) ."', 0, '', 0.00, 3, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_3b')) ."', 1, '', 20.00, 2, '', '', '0@@0@@0@@0'),
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_3a')) ."', 0, '', 0.00, 1, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 3)", __FILE__, __LINE__);

	//Question and answer set 4
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion4')) ."', '".$html_img4."', 20.00, 4, 1, '', 1, 4)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_4a')) ."', 0, '".lang2db(get_lang('Feedback_qn4_true')) ."', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_4b')) ."', 0, '".lang2db(get_lang('Feedback_qn4_true')) ."', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_4c')) ."', 0, '".lang2db(get_lang('Feedback_qn4_true')) ."', 0.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_4d')) ."', 1, '".lang2db(get_lang('Feedback_qn4_true')) ."', 20.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 4)", __FILE__, __LINE__);

	//Question and answer set 5
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion5')) ."', '".$html_img5."', 20.00, 5, 1, '', 1, 5)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_5a')) ."', 0, '".lang2db(get_lang('Feedback_qn5_true')) ."', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_5b')) ."', 0, '".lang2db(get_lang('Feedback_qn5_true')) ."', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_5c')) ."', 0, '".lang2db(get_lang('Feedback_qn5_true')) ."', 0.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_5d')) ."', 1, '".lang2db(get_lang('Feedback_qn5_true')) ."', 20.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 4)", __FILE__, __LINE__);

	//Question and answer set 6
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion6')) ."', '".$html_img6."', 20.00, 6, 1, '', 1, 6)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_6a."', 0, '".$html_img6_feedback_true."', 0.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".$html_ans_6b."', 0, '".$html_img6_feedback_true."', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".$html_ans_6c."', 1, '".$html_img6_feedback_true."', 20.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", '".$html_ans_6d."', 0, '".$html_img6_feedback_true."', 0.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 6)", __FILE__, __LINE__);

	//Question and answer set 7
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion7')) ."', '".$html_img7."', 20.00, 7, 1, '', 1, 7)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_7a')) ."', 1, '".lang2db(get_lang('Feedback_qn7_true')) ."', 20.00, 4, '', '', ''),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_7b')) ."', 0, '".lang2db(get_lang('Feedback_qn7_true')) ."', 0.00, 3, '', '', ''),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_7c')) ."', 0, '".lang2db(get_lang('Feedback_qn7_true')) ."', 0.00, 2, '', '', ''),
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_7d')) ."', 0, '".lang2db(get_lang('Feedback_qn7_true')) ."', 0.00, 1, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 7)", __FILE__, __LINE__);

	//Question and answer set 8
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion8')) ."', '".$html_img8."', 20.00, 8, 1, '', 1, 8)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_8a')) ."', 0, '', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_8b')) ."', 0, '', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_8c')) ."', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_8d')) ."', 1, '', 20.00, 4, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 8)", __FILE__, __LINE__);

	//Question and answer set 9
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion9')) ."', '".$html_img9."', 20.00, 9, 1, '', 1, 9)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_9a')) ."', 0, '".lang2db(get_lang('Feedback_qn9_true')) ."', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_9b')) ."', 0, '".lang2db(get_lang('Feedback_qn9_true')) ."', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_9c')) ."', 0, '".lang2db(get_lang('Feedback_qn9_true')) ."', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_9d')) ."', 1, '".lang2db(get_lang('Feedback_qn9_true')) ."', 20.00, 4, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 9)", __FILE__, __LINE__);

	//Question and answer set 10
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion10')) ."', '".$html_img10."', 20.00, 10, 1, '', 1, 10)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_10a')) ."', 1, '".lang2db(get_lang('Feedback_qn10_true')) ."', 20.00, 1, '', '', ''),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_10b')) ."', 0, '".lang2db(get_lang('Feedback_qn10_true')) ."', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_10c')) ."', 0, '".lang2db(get_lang('Feedback_qn10_true')) ."', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_10d')) ."', 0, '".lang2db(get_lang('Feedback_qn10_true')) ."', 0.00, 4, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 10)", __FILE__, __LINE__);

	//Question and answer set 11
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion11')) ."', '".$html_img11."', 20.00, 11, 2, '', 1, 11)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_11a')) ."', 1, '".lang2db(get_lang('Feedback_qn8_true')) ."', 10.00, 1, '', '', ''),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_11b')) ."', 1, '".lang2db(get_lang('Feedback_qn8_true')) ."', 10.00, 2, '', '', ''),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_11c')) ."', 0, '".lang2db(get_lang('Feedback_qn8_true')) ."', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_11d')) ."', 1, '".lang2db(get_lang('Feedback_qn8_true')) ."', 10.00, 4, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 11)", __FILE__, __LINE__);

	//Question and answer set 12
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion12')) ."', '".$html_img12."', 20.00, 12, 8, '', 1, 12)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_12a')) ."', 1, '".lang2db(get_lang('Feedback_qn12_true')) ."', 10.00, 1, '', '', ''),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_12b')) ."', 0, '".lang2db(get_lang('Feedback_qn12_true')) ."', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_12c')) ."', 0, '".lang2db(get_lang('Feedback_qn12_true')) ."', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_12d')) ."', 1, '".lang2db(get_lang('Feedback_qn12_true')) ."', 10.00, 4, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 12)", __FILE__, __LINE__);

	//Question and answer set 13
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion13')) ."', '".$html_img13."', 20.00, 13, 2, '', 1, 13)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_13a."', 1, '".lang2db(get_lang('Feedback_qn13_true'))."', 10.00, 1, '', '', '0@@0@@0@@0'),
	(2, ".$question_id.", '".$html_ans_13b."', 0, '".lang2db(get_lang('Feedback_qn13_true'))."', 0.00, 2, '', '', '0@@0@@0@@0'),
	(3, ".$question_id.", '".$html_ans_13c."', 0, '".lang2db(get_lang('Feedback_qn13_true'))."', 0.00, 3, '', '', '0@@0@@0@@0'),
	(4, ".$question_id.", '".$html_ans_13d."', 1, '".lang2db(get_lang('Feedback_qn13_true'))."', 10.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 13)", __FILE__, __LINE__);

	//Question and answer set 14
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion14')) ."', '".$html_img14."', 20.00, 14, 8, '', 1, 14)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('QuizAnswer_14a')) ."', 1, '', 10.00, 1, '', '', ''),
	(2, ".$question_id.", '".lang2db(get_lang('QuizAnswer_14b')) ."', 0, '', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '".lang2db(get_lang('QuizAnswer_14c')) ."', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '".lang2db(get_lang('QuizAnswer_14d')) ."', 1, '', 10.00, 4, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 14)", __FILE__, __LINE__);

	//Question and answer set 15
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion15')) ."', '".$html_img15."', 60.00, 15, 3, '', 1, 15)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_15."', 1, '".$comment_15."', 0.00, 0, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 15)", __FILE__, __LINE__);

	//Question and answer set 16
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion16')) ."', '".$html_img16."', 20.00, 16, 3, '', 1, 16)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_16."', 1, '".$comment_16."', 0.00, 0, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 16)", __FILE__, __LINE__);

	//Question and answer set 17
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion17')) ."', '".$html_img15."', 50.00, 17, 3, '', 1, 17)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_17."', 1, '".$comment_17."', 0.00, 0, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 17)", __FILE__, __LINE__);

	//Question and answer set 18
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion18')) ."', '".$html_img18."', 30.00, 18, 3, '', 1, 18)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_18."', 1, '".$comment_18."', 0.00, 0, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 18)", __FILE__, __LINE__);

	//Question and answer set 19
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion19')) ."', '".$html_img19."', 250.00, 19, 3, '', 1, 19)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".$html_ans_19."', 1, '".$comment_19."', 0.00, 0, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 19)", __FILE__, __LINE__);

	//Question and answer set 20
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion20')) ."', '".$html_img20."', 20.00, 20, 5, '', 1, 20)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	
	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 20)", __FILE__, __LINE__);

	//Question and answer set 21
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion21')) ."', '".$html_img21."', 20.00, 21, 5, '', 1, 21)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	
	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 21)", __FILE__, __LINE__);

	//Question and answer set 22
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion22')) ."', '".$html_img22."', 20.00, 22, 5, '', 1, 22)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	
	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 22)", __FILE__, __LINE__);

	//Question and answer set 23
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuestion18')) ."', '', 20.00, 23, 4, '', 1, 23)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_a'))." <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ColumbiaRiverTr64.png'."\" alt=\"ColumbiaRiverTr64.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn18_true'))."', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_b'))." <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/RioGrandeTr64.png'."\" alt=\"RioGrandeTr64.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn18_false'))."', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_c'))." <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/TenesseeRiverTr64.png'."\" alt=\"TenesseeRiverTr64.png\" /></p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_d'))."&nbsp; <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ArkansasRiverTr64.png'."\" alt=\"ArkansasRiverTr64.png\" /></p>', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_e'))." <img hspace=\"0\" height=\"64\" width=\"68\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/New_Mexico2tr64.png'."\" alt=\"New_Mexico2tr64.png\" /></p>', 1, '', 5.00, 5, '', '', ''),
	(6, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_f'))." <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AlabampaMapOutlineBlue2Tr64.png'."\" alt=\"AlabampaMapOutlineBlue2Tr64.png\" /></p>', 1, '', 5.00, 6, '', '', ''),
	(7, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_g'))."&nbsp; <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/OklahomaMapOutline3Tr64.png'."\" alt=\"OklahomaMapOutline3Tr64.png\" /></p>', 1, '', 5.00, 7, '', '', ''),
	(8, ".$question_id.", '<p>".lang2db(get_lang('langAnswer18_h'))." <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/WashingtonStateMapOutline2tr64.png'."\" alt=\"WashingtonStateMapOutline2tr64.png\" /></p>', 1, '', 5.00, 8, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 23)", __FILE__, __LINE__);

	//Question and answer set 24
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuestion19')) ."', '', 20.00, 24, 4, '', 1, 24)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medical15.png'."\" alt=\"medical15.png\" />&nbsp; ".lang2db(get_lang('langAnswer19_a'))."</p>', 0, '', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medic25.png'."\" alt=\"medic25.png\" />&nbsp; ".lang2db(get_lang('langAnswer19_b'))."</p>', 0, '', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medicalhandpointing.png'."\" alt=\"medicalhandpointing.png\" /> ".lang2db(get_lang('langAnswer19_c'))."</p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/01.png'."\" alt=\"01.png\" /></p>', 3, '', 6.67, 5, '', '', ''),
	(6, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/02.png'."\" alt=\"02.png\" /></p>', 2, '', 6.67, 6, '', '', ''),
	(7, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/03.png'."\" alt=\"03.png\" /></p>', 1, '', 6.67, 7, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 24)", __FILE__, __LINE__);

	//Question and answer set 25
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion24')) ."', '', 20.00, 25, 4, '', 1, 25)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"31\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/A.png'."\" alt=\"A.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn24_true'))."', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"37\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/B_1.png'."\" alt=\"B_1.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn24_true'))."', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"36\" width=\"199\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/_AorB_andnonA.png'."\" alt=\"_AorB_andnonA.png\" /></p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"145\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AandnonA.png'."\" alt=\"AandnonA.png\" /></p>', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"111\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AorB.png'."\" alt=\"AorB.png\" /></p>', 0, '', 0.00, 5, '', '', ''),
	(6, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/01.png'."\" alt=\"01.png\" /></p>', 4, '', 4.00, 6, '', '', ''),
	(7, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/02.png'."\" alt=\"02.png\" /></p>', 1, '', 4.00, 7, '', '', ''),
	(8, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/03.png'."\" alt=\"03.png\" /></p>', 5, '', 4.00, 8, '', '', ''),
	(9, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/04.png'."\" alt=\"04.png\" /></p>', 3, '', 4.00, 9, '', '', ''),
	(10, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"middle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/05.png'."\" alt=\"05.png\" /></p>', 2, '', 4.00, 10, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 25)", __FILE__, __LINE__);

	//Question and answer set 26
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion25')) ."', '', 20.00, 26, 4, '', 1, 26)", __FILE__, __LINE__);
	$question_id = Database::insert_id();

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Compression.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Compression.jpeg'."\" /></p>', 0, '".lang2db(get_lang('Feedback_qn25_true'))."', 0.00, 1, '', '', ''),
	(2, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Emission.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Emission.jpeg'."\" /></p>', 0, '".lang2db(get_lang('Feedback_qn25_true'))."', 0.00, 2, '', '', ''),
	(3, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Ignition.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Ignition.jpeg'."\" /></p>', 0, '', 0.00, 3, '', '', ''),
	(4, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"middle\" alt=\"Induction.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Induction.jpeg'."\" /></p>', 0, '', 0.00, 4, '', '', ''),
	(5, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_25a'))."', 3, '', 5.00, 5, '', '', ''),
	(6, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_25b'))."', 1, '', 5.00, 6, '', '', ''),
	(7, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_25c'))."', 4, '', 5.00, 7, '', '', ''),
	(8, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_25d'))."', 2, '', 5.00, 8, '', '', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 26)", __FILE__, __LINE__);

	//Question and answer set 27	
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion27')) ."', '', 40.00, 27, 6, '', 1, 27)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	$image_filename = 'quiz-'.$question_id.'.jpg';	
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-27.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-27.jpg');
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-27.jpg','0777');
	rename(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-27.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,'0777');
	copy(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename,'0777');
	unlink(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);	

	Database::query("UPDATE ".$TABLEQUIZQUESTIONLIST." SET picture = '".$image_filename."' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_27a'))."', 0, '', 10.00, 1, '42;166|32|38', 'square', ''),
	(2, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_27b'))."', 0, '', 10.00, 2, '122;283|75|120', 'circle', ''),
	(3, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_27c'))."', 0, '', 10.00, 3, '116;45|13|55', 'square', ''),
	(4, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_27d'))."', 0, '', 10.00, 4, '116;152|50|90', 'square', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 27)", __FILE__, __LINE__);

	//Question and answer set 28
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion28')) ."', '', 30.00, 28, 6, '', 1, 28)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	$image_filename = 'quiz-'.$question_id.'.jpg';	
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-28.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-28.jpg');
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-28.jpg','0777');
	rename(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-28.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,'0777');
	copy(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename,'0777');
	unlink(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);	

	Database::query("UPDATE ".$TABLEQUIZQUESTIONLIST." SET picture = '".$image_filename."' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_28a'))."', 0, '', 10.00, 1, '114;221|27|28', 'square', ''),
	(2, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_28b'))."', 0, '', 10.00, 2, '164;53|39|18', 'square', ''),
	(3, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_28c'))."', 0, '', 10.00, 3, '158;87|48|26', 'square', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 28)", __FILE__, __LINE__);

	//Question and answer set 29
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion29')) ."', '', 30.00, 29, 6, '', 1, 29)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	$image_filename = 'quiz-'.$question_id.'.jpg';	
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-29.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-29.jpg');
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-29.jpg','0777');
	rename(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-29.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,'0777');
	copy(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename,'0777');
	unlink(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);	

	Database::query("UPDATE ".$TABLEQUIZQUESTIONLIST." SET picture = '".$image_filename."' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_29a'))."', 0, '', 10.00, 1, '203;17|23|30', 'square', ''),
	(2, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_29b'))."', 0, '', 10.00, 2, '133;294|59|20', 'square', ''),
	(3, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_29c'))."', 0, '', 10.00, 3, '306;184|93|22', 'square', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 29)", __FILE__, __LINE__);

	//Question and answer set 30
	Database::query("INSERT INTO  ".$TABLEQUIZQUESTIONLIST . " (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`, `template_id`) VALUES
	('".lang2db(get_lang('DefaultQuizQuestion30')) ."', '', 30.00, 30, 6, '', 1, 30)", __FILE__, __LINE__);
	$question_id = Database::insert_id();
	$image_filename = 'quiz-'.$question_id.'.jpg';	
	copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-30.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-30.jpg');
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-30.jpg','0777');
	rename(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/quiz-30.jpg',api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,'0777');
	copy(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename,api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename);
	chmod(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/images/'.$image_filename,'0777');
	unlink(api_get_path('SYS_COURSE_PATH').$courseRepository.'/document/'.$image_filename);	

	Database::query("UPDATE ".$TABLEQUIZQUESTIONLIST." SET picture = '".$image_filename."' WHERE id = ".$question_id,__FILE__,__LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZANSWERSLIST . " (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
	(1, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_30a'))."', 0, '', 10.00, 1, '37;31|8|13', 'square', ''),
	(2, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_30b'))."', 0, '', 10.00, 2, '52;71|9|14', 'square', ''),
	(3, ".$question_id.", '".lang2db(get_lang('langQuizAnswer_30c'))."', 0, '', 10.00, 3, '22;98|11|14', 'square', '')", __FILE__, __LINE__);

	Database::query("INSERT INTO ".$TABLEQUIZQUESTION . " (question_id, exercice_id, question_order) VALUES (".$question_id.", ".$quiz_id.", 30)", __FILE__, __LINE__);

	$language_interface=$language_interface_tmp;
}
?>