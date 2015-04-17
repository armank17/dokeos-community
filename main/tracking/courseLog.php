<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file[] = 'admin';
$language_file[] = 'tracking';
$language_file[] = 'scorm';

// setting the help
$help_content = 'courselog';

// variable initialisation (@todo: sanitize this)
$pathopen = isset($_REQUEST['pathopen']) ? $_REQUEST['pathopen'] : null;

// resetting the course id
$cidReset = true;

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpath.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpathItem.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/learnpathList.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/scorm.class.php';
require_once api_get_path(SYS_CODE_PATH).'newscorm/scormItem.class.php';
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require api_get_path(LIBRARY_PATH).'statsUtils.lib.inc.php';
require api_get_path(SYS_CODE_PATH).'resourcelinker/resourcelinker.inc.php';

// the section (for the tabs)
$from_myspace = false;
if (isset($_GET['from']) && $_GET['from'] == 'myspace') {
	$from_myspace = true;
	$this_section = "session_my_space";
} else {
	$this_section = SECTION_COURSES;
}

// access restrictions
$is_allowedToTrack = api_is_course_admin() || api_is_platform_admin() || api_is_course_coach() || $is_sessionAdmin;
if (!$is_allowedToTrack) {
	Display :: display_tool_header(null);
	api_not_allowed();
	Display :: display_footer();
	exit;
}


// starting the output buffering when we are exporting the information
$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
if ($export_csv) {
	ob_start();
}
$csv_content = array();

// charset determination
if (!empty($_GET['scormcontopen'])) {
    $tbl_lp = Database::get_course_table(TABLE_LP_MAIN);
	$contopen = (int) $_GET['scormcontopen'];
	$sql = "SELECT default_encoding FROM $tbl_lp WHERE id = ".$contopen;
	$res = Database::query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($res);
	$lp_charset = $row['default_encoding'];
}

// Additional style definitions
$htmlHeadXtra[] = "<style type='text/css'>
/*<![CDATA[*/
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
/*]]>*/
</style>";

// Database table definitions
$TABLETRACK_ACCESS      = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
$TABLETRACK_LINKS       = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LINKS);
$TABLETRACK_DOWNLOADS   = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_DOWNLOADS);
$TABLETRACK_ACCESS_2    = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ACCESS);
$TABLETRACK_EXERCISES 	= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TABLECOURSUSER	        = Database::get_main_table(TABLE_MAIN_COURSE_USER);
$TABLECOURSE	        = Database::get_main_table(TABLE_MAIN_COURSE);
$TABLECOURSE_LINKS      = Database::get_course_table(TABLE_LINK);
$table_user = Database::get_main_table(TABLE_MAIN_USER);
$TABLEQUIZ = Database :: get_course_table(TABLE_QUIZ_TEST);

$tbl_learnpath_main = Database::get_course_table(TABLE_LP_MAIN);
$tbl_learnpath_item = Database::get_course_table(TABLE_LP_ITEM);
$tbl_learnpath_view = Database::get_course_table(TABLE_LP_VIEW);
$tbl_learnpath_item_view = Database::get_course_table(TABLE_LP_ITEM_VIEW);

// breadcrumbs
if (isset($_GET['origin']) && $_GET['origin'] == 'resume_session') {
    $interbreadcrumb[] = array('url' => '../admin/index.php','name' => get_lang('PlatformAdmin'));
    $interbreadcrumb[] = array('url' => '../admin/session_list.php','name' => get_lang('SessionList'));
    $interbreadcrumb[] = array('url' => '../admin/resume_session.php?id_session='.$_SESSION['id_session'], 'name' => get_lang('SessionOverview'));
}

$view = (isset($_REQUEST['view']) ? $_REQUEST['view'] : '');

$nameTools = get_lang('Tracking');

$htmlHeadXtra[] = '<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/excanvas.min.js"></script><![endif]-->';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/jquery.jqplot.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.barRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.pointLabels.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.cursor.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jqplot/jquery.jqplot.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/overcast/jquery-ui-1.8.4.custom.css" />';

$htmlHeadXtra[] = '
    <script type="text/javascript">
    /*<![CDATA[*/
        function jqplotToImg() {
		 var obj = $(".jqplot-target").not(".ui-tabs-hide");
		var newCanvas = document.createElement("canvas");
		newCanvas.width = obj.find("canvas.jqplot-base-canvas").width();
		newCanvas.height = obj.find("canvas.jqplot-base-canvas").height()+10;
		var baseOffset = obj.find("canvas.jqplot-base-canvas").offset();

		// make white background for pasting
		var context = newCanvas.getContext("2d");
		context.fillStyle = "rgba(255,255,255,1)";
		context.fillRect(0, 0, newCanvas.width, newCanvas.height);

		obj.children().each(function () {
		// for the div\'s with the X and Y axis
			if ($(this)[0].tagName.toLowerCase() == "div") {
				// X axis is built with canvas
				$(this).children("canvas").each(function() {
					var offset = $(this).offset();
					newCanvas.getContext("2d").drawImage(this,
						offset.left - baseOffset.left,
						offset.top - baseOffset.top
					);
				});
				// Y axis got div inside, so we get the text and draw it on the canvas
				$(this).children("div").each(function() {
					var offset = $(this).offset();
					var context = newCanvas.getContext("2d");
					context.font = $(this).css("font-style") + " " + $(this).css("font-size") + " " + $(this).css("font-family");
					context.fillStyle = $(this).css("color");
					context.fillText($(this).text(),
						offset.left - baseOffset.left,
						offset.top - baseOffset.top + $(this).height()
					);
				});
			} else if($(this)[0].tagName.toLowerCase() == "canvas") {
				// all other canvas from the chart
				var offset = $(this).offset();
				newCanvas.getContext("2d").drawImage(this,
					offset.left - baseOffset.left,
					offset.top - baseOffset.top
				);
			}
		});

		// add the point labels
		obj.children(".jqplot-point-label").each(function() {
			var offset = $(this).offset();
			var context = newCanvas.getContext("2d");
			context.font = $(this).css("font-style") + " " + $(this).css("font-size") + " " + $(this).css("font-family");
			context.fillStyle = $(this).css("color");
			context.fillText($(this).text(),
				offset.left - baseOffset.left,
				offset.top - baseOffset.top + $(this).height()*3/4
			);
		});

		// add the title
		obj.children("div.jqplot-title").each(function() {
			var offset = $(this).offset();
			var context = newCanvas.getContext("2d");
			context.font = $(this).css("font-style") + " " + $(this).css("font-size") + " " + $(this).css("font-family");
			context.textAlign = $(this).css("text-align");
			context.fillStyle = $(this).css("color");
			context.fillText($(this).text(),
				newCanvas.width / 2,
				offset.top - baseOffset.top + $(this).height()
			);
		});

		// add the legend
		obj.children("table.jqplot-table-legend").each(function() {
			var offset = $(this).offset();
			var context = newCanvas.getContext("2d");
			context.strokeStyle = $(this).css("border-top-color");
			context.strokeRect(
				offset.left - baseOffset.left,
				offset.top - baseOffset.top,
				$(this).width(),$(this).height()
			);
			context.fillStyle = $(this).css("background-color");
			context.fillRect(
				offset.left - baseOffset.left,
				offset.top - baseOffset.top,
				$(this).width(),$(this).height()
			);
		});

		// add the rectangles
		obj.find("div.jqplot-table-legend-swatch").each(function() {
			var offset = $(this).offset();
			var context = newCanvas.getContext("2d");
			context.fillStyle = $(this).css("background-color");
			context.fillRect(
				offset.left - baseOffset.left,
				offset.top - baseOffset.top,
				$(this).parent().width(),$(this).parent().height()
			);
		});

		obj.find("td.jqplot-table-legend").each(function() {
			var offset = $(this).offset();
			var context = newCanvas.getContext("2d");
			context.font = $(this).css("font-style") + " " + $(this).css("font-size") + " " + $(this).css("font-family");
			context.fillStyle = $(this).css("color");
			context.textAlign = $(this).css("text-align");
			context.textBaseline = $(this).css("vertical-align");
			context.fillText($(this).text(),
				offset.left - baseOffset.left,
				offset.top - baseOffset.top + $(this).height()/2 + parseInt($(this).css("padding-top").replace("px",""))
			);
		});

		// convert the image to base64 format
		return newCanvas.toDataURL("image/png");
	}

	function printJqPlot() {
		var mywindow = window.open("", "print_div", "height=400,width=600");
		mywindow.document.write("<html><head><title>Print Window<\/title>");
		mywindow.document.write("<\/head><body >");
		mywindow.document.write("<img src=\'"+jqplotToImg()+"\' style=\'float:left\' \/>");
		mywindow.document.write("<\/body><\/html>");
		mywindow.document.close();
		mywindow.print();
		mywindow.close();
		return true;
	}
    /*]]>*/
    </script>
';

// display the header
Display::display_tool_header($nameTools, 'Tracking');

// getting all the students of the course
$a_students = CourseManager :: get_student_list_from_course_code($_course['id'], true, (empty($_SESSION['id_session']) ? null : $_SESSION['id_session']));
$nbStudents = count($a_students);

// gettting all the additional information of an additional profile field
if (isset($_GET['additional_profile_field']) && is_numeric($_GET['additional_profile_field'])) {
	//$additional_user_profile_info = get_addtional_profile_information_of_field($_GET['additional_profile_field']);
	$user_array = array();
	foreach ($a_students as $key=>$item) {
		$user_array[] = $key;
	}
	//fetching only the user that are loaded NOT ALL user in the portal
	//$additional_user_profile_info = get_addtional_profile_information_of_field_by_user($_GET['additional_profile_field'],$user_array);
}



function count_item_resources() {
  return Tracking::count_item_resources();
}

function get_item_resources_data($from, $number_of_items, $column, $direction) {
	return Tracking::get_item_resources_data($from, $number_of_items, $column, $direction);
}

function get_tool_name_table($tool) {
 return Tracking::get_tool_name_table($tool);
}




/*
==============================================================================
		MAIN CODE
==============================================================================
*/

echo '<div class="actions print_invisible">';
if ($_GET['studentlist'] == 'false') {
	echo '<a href="learners.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Students'), array('class' => 'toolactionplaceholdericon toolactionadminusers')).get_lang('Students').'</a>';
 //echo ' | '.get_lang('CourseTracking').'&nbsp;|&nbsp;<a href="courseLog.php?'.api_get_cidreq().'&amp;studentlist=resources">'.get_lang('ResourcesTracking');
} elseif($_GET['studentlist'] == 'resources') {
	echo '<a href="learners.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Students'), array('class' => 'toolactionplaceholdericon toolactionadminusers')).get_lang('Students').'</a>';
 //echo '| <a href="courseLog.php?'.api_get_cidreq().'&amp;studentlist=false">'.get_lang('CourseTracking').'</a> | '.get_lang('ResourcesTracking');
} elseif($_GET['studentlist'] == '' || $_GET['studentlist'] == 'true') {
	echo '<a href="learners.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Students'), array('class' => 'toolactionplaceholdericon toolactionadminusers')).get_lang('Students').'</a>';
	//echo '<a href="courseLog.php?'.api_get_cidreq().'&amp;studentlist=false">'.get_lang('CourseTracking').'</a> | <a href="courseLog.php?'.api_get_cidreq().'&amp;studentlist=resources">'.get_lang('ResourcesTracking').'</a>';
}

echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;export=csv&amp;additional_profile_field=1'.$addional_param.'">'.Display::return_icon('pixel.gif', get_lang('ExportAsXLS'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportAsXLS').'</a>';
echo '<a href="javascript:void(0);" onclick="printJqPlot();">'.Display::return_icon('pixel.gif', get_lang('Print'), array('class' => 'toolactionplaceholdericon toolactionprint32')).get_lang('Print').'</a>';
echo '</div>';

// start the content div
echo '<div id="content">';
if ($_GET['studentlist'] == 'false') {
	echo'<br /><br />';

	// learning path tracking
	 echo '<div class="report_section">
			<h4>'.Display::return_icon('scormbuilder.gif',get_lang('AverageProgressInLearnpath')).get_lang('AverageProgressInLearnpath').'</h4>
			<table class="data_table">';

	$list = new LearnpathList($student);
	$flat_list = $list->get_flat_list();

	if ($export_csv) {
		$temp = array(get_lang('AverageProgressInLearnpath', ''), '');
		$csv_content[] = array('', '');
		$csv_content[] = $temp;
	}

	if (count($flat_list) > 0) {
		foreach ($flat_list as $lp_id => $lp) {
			$lp_avg_progress = 0;
			foreach ($a_students as $student_id => $student) {
				// get the progress in learning pathes
				$lp_avg_progress += learnpath::get_db_progress($lp_id, $student_id);
			}
			if ($nbStudents > 0) {
				$lp_avg_progress = $lp_avg_progress / $nbStudents;
			} else {
				$lp_avg_progress = null;
			}
			// Separated presentation logic.
			if (is_null($lp_avg_progress)) {
				$lp_avg_progress = '0%';
			} else {
				$lp_avg_progress = round($lp_avg_progress, 1).'%';
			}
			echo '<tr><td>'.$lp['lp_name'].'</td><td align="right">'.$lp_avg_progress.'</td></tr>';
			if ($export_csv) {
				$temp = array($lp['lp_name'], $lp_avg_progress);
				$csv_content[] = $temp;
			}
		}
	} else {
		echo '<tr><td>'.get_lang('NoLearningPath').'</td></tr>';
		if ($export_csv) {
    		$temp = array(get_lang('NoLearningPath', ''), '');
			$csv_content[] = $temp;
    	}
	}
	echo '</table></div>';
	echo '<div class="clear"></div>';

	 // Exercices tracking
	 echo '<div class="report_section">
				<h4>'.Display::return_icon('quiz.gif',get_lang('AverageResultsToTheExercices')).get_lang('AverageResultsToTheExercices').'&nbsp;-&nbsp;<a href="../exercice/exercice.php?'.api_get_cidreq().'&amp;show=result">'.get_lang('SeeDetail').'</a></h4>
			<table class="data_table">';

	$sql = "SELECT id, title
			FROM $TABLEQUIZ WHERE active <> -1";
	$rs = Database::query($sql, __FILE__, __LINE__);

	if ($export_csv) {
    	$temp = array(get_lang('AverageProgressInLearnpath'), '');
    	$csv_content[] = array('', '');
    	$csv_content[] = $temp;
    }

	if (Database::num_rows($rs) > 0) {
		// gets course actual administrators
		$sql = "SELECT user.user_id FROM $table_user user, $TABLECOURSUSER course_user
			WHERE course_user.user_id=user.user_id AND course_user.course_code='".api_get_course_id()."' AND course_user.status <> '1' ";
		$res = Database::query($sql, __FILE__, __LINE__);

		$student_ids = array();

		while($row = Database::fetch_row($res)) {
			$student_ids[] = $row[0];
		}
		$count_students = count($student_ids);
		while ($quiz = Database::fetch_array($rs)) {
			$quiz_avg_score = 0;
			if ($count_students > 0) {
				foreach ($student_ids as $student_id) {
					// get the scorn in exercises
					$sql = 'SELECT exe_result , exe_weighting
						FROM '.$TABLETRACK_EXERCISES.'
						WHERE exe_exo_id = '.$quiz['id'].'
							AND exe_user_id = '.(int)$student_id.'
							AND exe_cours_id = "'.api_get_course_id().'"
						AND orig_lp_id = 0
						AND orig_lp_item_id = 0
						ORDER BY exe_date DESC';
					$rsAttempt = Database::query($sql, __FILE__, __LINE__);
					$nb_attempts = 0;
					$avg_student_score = 0;
					while ($attempt = Database::fetch_array($rsAttempt)) {
						$nb_attempts++;
						$exe_weight = $attempt['exe_weighting'];
						if ($exe_weight > 0) {
							$avg_student_score += round(($attempt['exe_result'] / $exe_weight * 100), 2);
						}
					}
					if ($nb_attempts > 0) {
						$avg_student_score = $avg_student_score / $nb_attempts;
					}
					$quiz_avg_score += $avg_student_score;
				}
			}
            $count_students = ($count_students == 0 || is_null($count_students) || $count_students == '') ? 1 : $count_students;
			echo '<tr><td>'.$quiz['title'].'</td><td align="right">'.round(($quiz_avg_score / $count_students), 2).'%'.'</td></tr>';
			if ($export_csv) {
				$temp = array($quiz['title'], $quiz_avg_score);
				$csv_content[] = $temp;
			}
		}
	} else {
		echo '<tr><td>'.get_lang('NoExercises').'</td></tr>';
		if ($export_csv) {
    		$temp = array(get_lang('NoExercises', ''), '');
			$csv_content[] = $temp;
    	}
	}

	echo '</table></div>';
	echo '<div class="clear"></div>';

	 // forums tracking

	echo '<div class="report_section">
			<h4>'.Display::return_icon('forum.gif', get_lang('Forum')).get_lang('Forum').'&nbsp;-&nbsp;<a href="../forum/index.php?cidReq='.$_course['id'].'">'.get_lang('SeeDetail').'</a></h4>
			<table class="data_table">';
	$count_number_of_posts_by_course = Tracking :: count_number_of_posts_by_course($_course['id']);
	$count_number_of_forums_by_course = Tracking :: count_number_of_forums_by_course($_course['id']);
	$count_number_of_threads_by_course = Tracking :: count_number_of_threads_by_course($_course['id']);
	if ($export_csv) {
		$csv_content[] = array(get_lang('Forum'), '');
    	$csv_content[] = array(get_lang('ForumForumsNumber', ''), $count_number_of_forums_by_course);
    	$csv_content[] = array(get_lang('ForumThreadsNumber', ''), $count_number_of_threads_by_course);
    	$csv_content[] = array(get_lang('ForumPostsNumber', ''), $count_number_of_posts_by_course);
    }
	echo '<tr><td>'.get_lang('ForumForumsNumber').'</td><td align="right">'.$count_number_of_forums_by_course.'</td></tr>';
	echo '<tr><td>'.get_lang('ForumThreadsNumber').'</td><td align="right">'.$count_number_of_threads_by_course.'</td></tr>';
	echo '<tr><td>'.get_lang('ForumPostsNumber').'</td><td align="right">'.$count_number_of_posts_by_course.'</td></tr>';
	echo '</table></div>';
	echo '<div class="clear"></div>';

	// chat tracking

	echo '<div class="report_section">
			<h4>'.Display::return_icon('chat.gif',get_lang('Chat')).get_lang('Chat').'</h4>
			<table class="data_table">';
	$chat_connections_during_last_x_days_by_course = Tracking :: chat_connections_during_last_x_days_by_course($_course['id'], 7);
	if ($export_csv) {
		$csv_content[] = array(get_lang('Chat', ''), '');
    	$csv_content[] = array(sprintf(get_lang('ChatConnectionsDuringLastXDays', ''), '7'), $chat_connections_during_last_x_days_by_course);
    }
	echo '<tr><td>'.sprintf(get_lang('ChatConnectionsDuringLastXDays'), '7').'</td><td align="right">'.$chat_connections_during_last_x_days_by_course.'</td></tr>';

	echo '</table></div>';
	echo '<div class="clear"></div>';

	// tools tracking
	echo '<div class="report_section">
				<h4>'.Display::return_icon('acces_tool.gif', get_lang('ToolsMostUsed')).get_lang('ToolsMostUsed').'</h4>
			<table class="data_table">';

	$sql = "SELECT access_tool, COUNT(DISTINCT access_user_id),count( access_tool ) as count_access_tool
            FROM $TABLETRACK_ACCESS
            WHERE access_tool IS NOT NULL
                AND access_cours_code = '$_cid'
            GROUP BY access_tool
			ORDER BY count_access_tool DESC
			LIMIT 0, 3";
	$rs = Database::query($sql, __FILE__, __LINE__);

	if ($export_csv) {
    	$temp = array(get_lang('ToolsMostUsed'), '');
    	$csv_content[] = $temp;
    }

	while ($row = Database::fetch_array($rs)) {
		echo '	<tr>
					<td>'.get_lang(ucfirst($row['access_tool'])).'</td>
					<td align="right">'.$row['count_access_tool'].' '.get_lang('Clicks').'</td>
				</tr>';
		if ($export_csv) {
			$temp = array(get_lang(ucfirst($row['access_tool']), ''), $row['count_access_tool'].' '.get_lang('Clicks', ''));
			$csv_content[] = $temp;
		}
	}

	echo '</table></div>';
	echo '<div class="clear"></div>';

	// Documents tracking
	if ($_GET['num'] == 0 or empty($_GET['num'])) {
		$num = 3;
		$link='&nbsp;-&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;studentlist=false&amp;num=1#documents_tracking">'.get_lang('SeeDetail').'</a>';
	} else {
		$num = 1000;
		$link='&nbsp;-&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;studentlist=false&amp;num=0#documents_tracking">'.get_lang('ViewMinus').'</a>';
	}

	 echo '<a name="documents_tracking" id="a"></a><div class="report_section">
				<h4>'.Display::return_icon('documents.gif',get_lang('DocumentsMostDownloaded')).'&nbsp;'.get_lang('DocumentsMostDownloaded').$link.'</h4>
			<table class="data_table">';

	$sql = "SELECT down_doc_path, COUNT(DISTINCT down_user_id), COUNT(down_doc_path) as count_down
            FROM $TABLETRACK_DOWNLOADS
            WHERE down_cours_id = '$_cid'
            GROUP BY down_doc_path
			ORDER BY count_down DESC
			LIMIT 0,  $num";
    $rs = Database::query($sql, __FILE__, __LINE__);

    if ($export_csv) {
    	$temp = array(get_lang('DocumentsMostDownloaded', ''), '');
    	$csv_content[] = array('', '');
    	$csv_content[] = $temp;
    }

    if (Database::num_rows($rs) > 0) {
	    while($row = Database::fetch_array($rs)) {
	    	echo '	<tr>
						<td>'.$row['down_doc_path'].'</td>
						<td align="right">'.$row['count_down'].' '.get_lang('Clicks').'</td>
					</tr>';
			if ($export_csv) {
				$temp = array($row['down_doc_path'], $row['count_down'].' '.get_lang('Clicks', ''));
				$csv_content[] = $temp;
			}
	    }
    } else {
    	echo '<tr><td>'.get_lang('NoDocumentDownloaded').'</td></tr>';
    	if ($export_csv) {
    		$temp = array(get_lang('NoDocumentDownloaded', ''),'');
			$csv_content[] = $temp;
    	}
    }
	echo '</table></div>';
#coursemanager#coursemanager
	echo '<div class="clear"></div>';

	// links tracking
	 echo '<div class="report_section">
				<h4>'.Display::return_icon('link.gif',get_lang('LinksMostClicked')).'&nbsp;'.get_lang('LinksMostClicked').'</h4>
			<table class="data_table">';

	$sql = "SELECT cl.title, cl.url,count(DISTINCT sl.links_user_id), count(cl.title) as count_visits
            FROM $TABLETRACK_LINKS AS sl, $TABLECOURSE_LINKS AS cl
            WHERE sl.links_link_id = cl.id
                AND sl.links_cours_id = '$_cid'
            GROUP BY cl.title, cl.url
			ORDER BY count_visits DESC
			LIMIT 0, 3";
    $rs = Database::query($sql, __FILE__, __LINE__);

    if ($export_csv) {
    	$temp = array(get_lang('LinksMostClicked'),'');
    	$csv_content[] = array('','');
    	$csv_content[] = $temp;
    }

    if (Database::num_rows($rs) > 0) {
	    while ($row = Database::fetch_array($rs)) {
	    	echo '	<tr>
						<td>'.$row['title'].'</td>
						<td align="right">'.$row['count_visits'].' '.get_lang('Clicks').'</td>
					</tr>';
			if ($export_csv){
				$temp = array($row['title'],$row['count_visits'].' '.get_lang('Clicks', ''));
				$csv_content[] = $temp;
			}
	    }
    } else {
    	echo '<tr><td>'.get_lang('NoLinkVisited').'</td></tr>';
    	if ($export_csv) {
    		$temp = array(get_lang('NoLinkVisited'), '');
			$csv_content[] = $temp;
    	}
    }
	echo '</table></div>';
	echo '<div class="clear"></div>';

	// send the csv file if asked
	if ($export_csv) {
		ob_end_clean();
		Export :: export_table_csv($csv_content, 'reporting_course_tracking');
	}
} elseif ($_GET['studentlist'] == 'true' or $_GET['studentlist'] == '') {
// else display student list with all the informations
	if ($export_csv) {
		$is_western_name_order = api_is_western_name_order(PERSON_NAME_DATA_EXPORT);
	} else {
		$is_western_name_order = api_is_western_name_order();
	}
	$sort_by_first_name = api_sort_by_first_name();

	$tracking_column = isset($_GET['tracking_column']) ? $_GET['tracking_column'] : 0;
	$tracking_direction = isset($_GET['tracking_direction']) ? $_GET['tracking_direction'] : 'DESC';

	if (count($a_students) > 0) {

	    if ($export_csv) {
			$csv_content[] = array ();
		}

	    $all_datas = array();
	    $course_code = $_course['id'];

		$user_ids = array_keys($a_students);
		$table = new SortableTable('users', 'get_number_of_users', 'get_user_data', (api_is_western_name_order() xor api_sort_by_first_name()) ? 3 : 2);

		$parameters['cidReq'] 		= Security::remove_XSS($_GET['cidReq']);
		$parameters['studentlist'] 	= Security::remove_XSS($_GET['studentlist']);
		$parameters['from'] 	= Security::remove_XSS($_GET['myspace']);

		$table->set_additional_parameters($parameters);

		$table -> set_header(0, get_lang('OfficialCode'), false, 'align="center"');
		if ($is_western_name_order) {
			$table -> set_header(1, get_lang('FirstName'), false, 'align="center"');
			$table -> set_header(2, get_lang('LastName'), true, 'align="center"');
		} else {
    		$table -> set_header(1, get_lang('LastName'), true, 'align="center"');
			$table -> set_header(2, get_lang('FirstName'), false, 'align="center"');
		}
		$table -> set_header(3, get_lang('TrainingTime'),false);
		$table -> set_header(4, get_lang('CourseProgress'),false);
		$table -> set_header(5, get_lang('Score'),false);
		$table -> set_header(6, get_lang('Student_publication'),false);
		$table -> set_header(7, get_lang('Messages'),false);
		$table -> set_header(8, get_lang('FirstLogin'), false, 'align="center"');
		$table -> set_header(9, get_lang('LatestLogin'), false, 'align="center"');
		$table -> set_header(10, get_lang('AdditionalProfileField'),false);

		$table -> set_header(11, get_lang('Details'),false);
		//$html_table = $table->get_all_table_html();
        $all_data = Tracking::get_user_data(null,null,null,null,null,null,false);

  		echo '<br/>';
		include_once api_get_path(SYS_CODE_PATH).'mySpace/charts/users.js.php';

	} else {
		echo get_lang('NoUsersInCourseTracking');
	}
	// send the csv file if asked
	if ($export_csv) {
		if ($is_western_name_order) {
			$csv_headers = array (
				get_lang('OfficialCode', ''),
				get_lang('FirstName', ''),
				get_lang('LastName', ''),
				get_lang('TrainingTime', ''),
				get_lang('CourseProgress', ''),
				get_lang('Score', ''),
				get_lang('Student_publication', ''),
				get_lang('Messages', ''),
				get_lang('FirstLogin', ''),
				get_lang('LatestLogin', '')
			);
		} else {
			$csv_headers = array (
				get_lang('OfficialCode', ''),
				get_lang('LastName', ''),
				get_lang('FirstName', ''),
				get_lang('TrainingTime', ''),
				get_lang('CourseProgress', ''),
				get_lang('Score', ''),
				get_lang('Student_publication', ''),
				get_lang('Messages', ''),
				get_lang('FirstLogin', ''),
				get_lang('LatestLogin', '')
			);
		}

		if (isset($_GET['additional_profile_field']) AND is_numeric($_GET['additional_profile_field'])) {
                        $extra_fields = Usermanager::get_extra_fields(0, 0, 5, 'ASC');
                        foreach($extra_fields as $extra) {
                            $csv_headers[]=$extra[1];
                        }
		}
		ob_end_clean();



		array_unshift($csv_content, $csv_headers); // adding headers before the content
		Export :: export_table_csv($csv_content, 'reporting_student_list');
	}

} elseif($_GET['studentlist'] == 'resources') {

	// Create a search-box
	$form = new FormValidator('search_simple','get',api_get_path(WEB_CODE_PATH).'tracking/courseLog.php?'.api_get_cidreq().'&amp;studentlist=resources','','width=200px',false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span>');
	$form->addElement('hidden','studentlist','resources');
	$form->addElement('hidden','cidReq',Security::remove_XSS($_GET['cidReq']));
        $form->addElement('style_submit_button', 'submit', get_lang('Search'),'class="search"');
	$form->addElement('text','keyword',get_lang('keyword'),array('style'=>'float:right;margin-right:10px;margin-top:5px'));

	echo '<div class="actions print_invisible">';
		$form->display();
	echo '</div>';

	$table = new SortableTable('resources', 'count_item_resources', 'get_item_resources_data', 5, 20, 'DESC');
	$parameters = array();

	if (isset($_GET['keyword'])) {
		$parameters['keyword'] = Security::remove_XSS($_GET['keyword']);
	}

	$parameters['studentlist'] = 'resources';
	$parameters['cidReq'] = Security::remove_XSS($_GET['cidReq']);

	$table->set_additional_parameters($parameters);
	$table->set_header(0, get_lang('Tool'));
	$table->set_header(1, get_lang('EventType'));
	$table->set_header(2, get_lang('Session'), false);
	$table->set_header(3, get_lang('UserName'));
	$table->set_header(4, get_lang('Document'), false);
	$table->set_header(5, get_lang('Date'));
	$table->display();

}
?>
<?php
// close the content div
echo '</div>';
echo '<div class="actions print_invisible">';
$return = '<a href="courseLog.php?'.api_get_cidreq().'&amp;studentlist=resources">'.Display::return_icon('pixel.gif', get_lang('Traffic'), array('class' => 'actionplaceholdericon actionquota')).get_lang('Traffic').'</a>';
$return .= '<a href="../exercice/exercice.php?'.api_get_cidreq().'&amp;reporting=true&amp;page=courselog&amp;show=result">'.Display::return_icon('pixel.gif', get_lang('Quiz'), array('class' => 'actionplaceholdericon actionstudentviewquiz')).get_lang('Quiz').'</a>';
$return .= '<a href="notification.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Notification'), array('class' => 'actionplaceholdericon actionannouncement')).get_lang('Notification').'</a>';
$return .= '<a href="profiling.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Profiling'), array('class' => 'actionplaceholdericon actioncoach')).get_lang('Profiling').'</a>';
 echo $return;
echo '</div>';
// display the footer
Display::display_footer();


/**
 * Display all the additionally defined user profile fields
 * This function will only display the fields, not the values of the field because it does not act as a filter
 * but it adds an additional column instead.
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @since October 2009
 * @version 1.8.7
 */
function display_additional_profile_fields() {
	return Tracking::display_additional_profile_fields();
}

/**
 * This function gets all the information of a certrain ($field_id) additional profile field.
 * It gets the information of all the users so that it can be displayed in the sortable table or in the csv or xls export
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @since October 2009
 * @version 1.8.7
 */
function get_addtional_profile_information_of_field($field_id){
	return Tracking::get_addtional_profile_information_of_field($field_id);
}

/**
 * This function gets all the information of a certrain ($field_id) additional profile field for a specific list of users is more efficent than  get_addtional_profile_information_of_field() function
 * It gets the information of all the users so that it can be displayed in the sortable table or in the csv or xls export
 *
 * @author	Julio Montoya <gugli100@gmail.com>
 * @param	int field id
 * @param	array list of user ids
 * @return	array
 * @since	Nov 2009
 * @version	1.8.6.2
 */
function get_addtional_profile_information_of_field_by_user($field_id, $users) {
 return Tracking::get_addtional_profile_information_of_field_by_user($field_id, $users);
}

/**
 * count the number of students in this course (used for SortableTable)
 */
function count_student_in_course() {
	global $nbStudents;
	return $nbStudents;
}

function sort_users($a, $b) {
	return strcmp(trim(api_strtolower($a[$_SESSION['tracking_column']])), trim(api_strtolower($b[$_SESSION['tracking_column']])));
}

function sort_users_desc($a, $b) {
	return strcmp( trim(api_strtolower($b[$_SESSION['tracking_column']])), trim(api_strtolower($a[$_SESSION['tracking_column']])));
}

/**
 * Get number of users for sortable with pagination
 * @return int
 */
function get_number_of_users() {
		global $user_ids;
		return count($user_ids);
}
/**
 * Get data for users list in sortable with pagination
 * @return array
 */
function get_user_data($from, $number_of_items, $column, $direction) {
 return Tracking::get_user_data($from, $number_of_items, $column, $direction);
}