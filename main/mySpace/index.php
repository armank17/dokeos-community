<?php

// $Id: index.php 16620 2008-10-25 20:03:54Z yannoo $
/* For licensing terms, see /dokeos_license.txt */
/**
 * @todo use constant for $this_section
 */
// name of the language file that needs to be included
$language_file = array('admin', 'registration', 'index', 'tracking');

// resetting the course id
$cidReset = true;

// including the global Dokeos file
require '../inc/global.inc.php';

// including additional libraries
require api_get_path(LIBRARY_PATH) . 'tracking.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'export.lib.inc.php';
// the section (for the tabs)
$this_section = "session_my_space";

ob_start();

$export_csv = isset($_GET['export']) && $_GET['export'] == 'csv' ? true : false;
$csv_content = array();

$nameTools = get_lang("MySpace");

$htmlHeadXtra[] = '<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/excanvas.min.js"></script><![endif]-->';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/jquery.jqplot.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/plugins/jqplot.barRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/plugins/jqplot.pointLabels.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/plugins/jqplot.cursor.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jqplot/jquery.jqplot.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/overcast/jquery-ui-1.8.4.custom.css" />';


// access control
api_block_anonymous_users();
if (!$export_csv) {
    Display :: display_header($nameTools);
} else {
    if ($_GET['view'] == 'admin' AND $_GET['display'] == 'useroverview') {
        export_tracking_user_overview();
        exit;
    }
}

// Database table definitions
$tbl_user = Database :: get_main_table(TABLE_MAIN_USER);
$tbl_course = Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_class = Database :: get_main_table(TABLE_MAIN_CLASS);
$tbl_sessions = Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_user = Database :: get_main_table(TABLE_MAIN_SESSION_USER);
$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_admin = Database :: get_main_table(TABLE_MAIN_ADMIN);
$tbl_track_cours_access = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);


/* * ******************
 * FUNCTIONS
 * ****************** */

function count_teacher_courses() {
    global $nb_teacher_courses;
    return $nb_teacher_courses;
}

function count_coaches() {
    global $total_no_coaches;
    return $total_no_coaches;
}

function sort_users($a, $b) {
    return api_strcmp(trim(api_strtolower($a[$_SESSION['tracking_column']])), trim(api_strtolower($b[$_SESSION['tracking_column']])));
}

function rsort_users($a, $b) {
    return api_strcmp(trim(api_strtolower($b[$_SESSION['tracking_column']])), trim(api_strtolower($a[$_SESSION['tracking_column']])));
}

/* * ************************
 * MAIN CODE
 * ************************* */

$is_coach = api_is_coach();
$is_platform_admin = api_is_platform_admin();

// why teacher role if is emtpy?
$view = isset($_GET['view']) ? $_GET['view'] : 'teacher';

if ($is_platform_admin) {
    $view = 'admin';
}

$menu_items = array();
global $_configuration;

if (api_is_allowed_to_create_course()) {
    $sql_nb_cours = "SELECT course_rel_user.course_code, course.title
			FROM $tbl_course_user as course_rel_user
			INNER JOIN $tbl_course as course
				ON course.code = course_rel_user.course_code
			WHERE course_rel_user.user_id='" . $_user['user_id'] . "' AND course_rel_user.status='1'
			ORDER BY course.title";

    if ($_configuration['multiple_access_urls'] == true) {
        $tbl_course_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
        $access_url_id = api_get_current_access_url_id();
        if ($access_url_id != -1) {
            $sql_nb_cours = "	SELECT course_rel_user.course_code, course.title
				FROM $tbl_course_user as course_rel_user
				INNER JOIN $tbl_course as course
					ON course.code = course_rel_user.course_code
			  	INNER JOIN $tbl_course_rel_access_url course_rel_url
					ON (course_rel_url.course_code= course.code)
			  	WHERE access_url_id =  $access_url_id  AND course_rel_user.user_id='" . $_user['user_id'] . "' AND course_rel_user.status='1'
			  	ORDER BY course.title";
        }
    }

    $result_nb_cours = Database::query($sql_nb_cours, __FILE__, __LINE__);
    $courses = Database::store_result($result_nb_cours);

    $nb_teacher_courses = count($courses);
    if ($nb_teacher_courses) {
        if (!$is_coach && !$is_platform_admin) {
            $view = 'teacher';
        }
        $menu_items[] = '<a href="trainings.php">' . Display::return_icon('pixel.gif', get_lang('TrackTrainings'), array('class' => 'toolactionplaceholdericon toolactionadmincourse')) . get_lang('TrackTrainings') . '</a>';
    }
}

if ($is_coach) {
    if ($nb_teacher_courses == 0 && !$is_platform_admin) {
        $view = 'coach';
    }
    if ($view == 'coach') {
        $menu_items[] = get_lang('CoachInterface');
        $title = get_lang('YourStatistics');
    } else {
        $menu_items[] = '<a href="' . api_get_self() . '?view=coach">' . Display::return_icon('pixel.gif', get_lang('TrackTrainings'), array('class' => 'toolactionplaceholdericon toolactiontutorview')) . get_lang('CoachInterface') . '</a>';
    }
}
if ($is_platform_admin) {
    if (!$is_coach && $nb_teacher_courses == 0) {
        $view = 'admin';
    }
    //if ($view == 'admin') {
    //$menu_items[] = Display::return_icon('adminsession.png', get_lang('AdminInterface')).get_lang('AdminInterface');
// var $menu_items
    $menu_items[] = '<a href="trainings.php?view=admin">' . Display::return_icon('pixel.gif', get_lang('TrackSessions'), array('class' => 'toolactionplaceholdericon toolactionatimesession')) . get_lang('TrackSessions') . '</a>';
    $title = get_lang('CoachList');
    /* } else {
      $menu_items[] = '<a href="'.api_get_self().'?view=admin">'.Display::return_icon('adminsession.png', get_lang('AdminInterface')).get_lang('AdminInterface').'</a>';
      } */
}
if ($_user['status'] == DRH) {
    $view = 'drh';
    $title = get_lang('DrhInterface');
    $menu_items[] = '<a href="' . api_get_self() . '?view=drh">' . get_lang('DrhInterface') . '</a>';
}

echo '<div class="actions print_invisible">';
echo '<a href="index.php">' . Display::return_icon('pixel.gif', get_lang('Report'), array('class' => 'toolactionplaceholdericon toolactionstatistics')) . get_lang('Report') . '</a>';
// var $menu_items,
$nb_menu_items = count($menu_items);
if ($nb_menu_items > 1) {
    foreach ($menu_items as $key => $item) {
        echo $item;
        if ($key != $nb_menu_items - 1) {
            echo '';
        }
    }
}

echo (isset($_GET['display']) && $_GET['display'] == 'useroverview') ? '' : '<a href="' . api_get_self() . '?export=csv&amp;view=' . $view . '">' . Display::return_icon('pixel.gif', get_lang('ExportAsXLS'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('ExportAsXLS') . '</a>';
echo '<a href="javascript:void(0);" onclick="javascript: window.print()">' . Display::return_icon('pixel.gif', get_lang('Print'), array('class' => 'toolactionplaceholdericon toolactionprint32')) . get_lang('Print') . '</a> ';
echo '</div>';

echo '<div id="content">';
//echo '<h4>'.$title.'</h4>';

if ($_user['status'] == DRH && $view == 'drh') {
    $students = Tracking :: get_student_followed_by_drh($_user['user_id']);
    $courses_of_the_platform = CourseManager :: get_real_course_list();
    foreach ($courses_of_the_platform as $course) {
        $courses[$course['code']] = $course['code'];
    }
}

if ($is_coach && $view == 'coach') {
    $students = Tracking :: get_student_followed_by_coach($_user['user_id']);
    $courses = Tracking :: get_courses_followed_by_coach($_user['user_id']);
}

if ($view == 'coach' || $view == 'drh') {
    $nb_students = count($students);
    $total_time_spent = 0;
    $total_courses = 0;
    $avg_total_progress = 0;
    $avg_results_to_exercises = 0;
    $nb_inactive_students = 0;
    $nb_posts = $nb_assignments = 0;
    foreach ($students as $student_id) {
        // inactive students
        $last_connection_date = Tracking :: get_last_connection_date($student_id, true, true);
        if ($last_connection_date != false) {
            /*
              list($last_connection_date, $last_connection_hour) = explode(' ', $last_connection_date);
              $last_connection_date = explode('-', $last_connection_date);
              $last_connection_hour = explode(':', $last_connection_hour);
              $last_connection_hour[0];
              $last_connection_time = mktime($last_connection_hour[0], $last_connection_hour[1], $last_connection_hour[2], $last_connection_date[1], $last_connection_date[2], $last_connection_date[0]);
             */
            if (time() - (3600 * 24 * 7) > $last_connection_time) {
                $nb_inactive_students++;
            }
        } else {
            $nb_inactive_students++;
        }

        $total_time_spent += Tracking :: get_time_spent_on_the_platform($student_id);
        $total_courses += Tracking :: count_course_per_student($student_id);
        $avg_student_progress = $avg_student_score = 0;
        $nb_courses_student = 0;
        foreach ($courses as $course_code) {
            if (CourseManager :: is_user_subscribed_in_course($student_id, $course_code, true)) {
                $nb_courses_student++;
                $nb_posts += Tracking :: count_student_messages($student_id, $course_code);
                $nb_assignments += Tracking :: count_student_assignments($student_id, $course_code);
                $avg_student_progress += Tracking :: get_avg_student_progress($student_id, $course_code);
                $avg_student_score += Tracking :: get_avg_student_score($student_id, $course_code);
                if ($nb_posts !== null && $nb_assignments !== null && $avg_student_progress !== null && $avg_student_score !== null) {
                    //if one of these scores is null, it means that we had a problem connecting to the right database, so don't count it in
                    $nb_courses_student++;
                }
            }
        }
        // average progress of the student
        $avg_student_progress = $avg_student_progress / $nb_courses_student;
        $avg_total_progress += $avg_student_progress;

        // average test results of the student
        $avg_student_score = $avg_student_score / $nb_courses_student;
        $avg_results_to_exercises += $avg_student_score;
    }

    if ($nb_students > 0) {
        // average progress
        $avg_total_progress = $avg_total_progress / $nb_students;
        // average results to the tests
        $avg_results_to_exercises = $avg_results_to_exercises / $nb_students;
        // average courses by student
        $avg_courses_per_student = round($total_courses / $nb_students, 2);
        // average time spent on the platform
        $avg_time_spent = $total_time_spent / $nb_students;
        // average assignments
        $nb_assignments = $nb_assignments / $nb_students;
        // average posts
        $nb_posts = $nb_posts / $nb_students;
    } else {
        $avg_total_progress = null;
        $avg_results_to_exercises = null;
        $avg_courses_per_student = null;
        $avg_time_spent = null;
        $nb_assignments = null;
        $nb_posts = null;
    }

    if ($export_csv) {
        //csv part
        $csv_content[] = array(get_lang('Probationers', ''));
        $csv_content[] = array(get_lang('InactivesStudents', ''), $nb_inactive_students);
        $csv_content[] = array(get_lang('AverageTimeSpentOnThePlatform', ''), $avg_time_spent);
        $csv_content[] = array(get_lang('AverageCoursePerStudent', ''), $avg_courses_per_student);
        $csv_content[] = array(get_lang('AverageProgressInLearnpath', ''), is_null($avg_total_progress) ? null : round($avg_total_progress, 2) . '%');
        $csv_content[] = array(get_lang('AverageResultsToTheExercices', ''), is_null($avg_results_to_exercises) ? null : round($avg_results_to_exercises, 2) . '%');
        $csv_content[] = array(get_lang('AveragePostsInForum', ''), $nb_posts);
        $csv_content[] = array(get_lang('AverageAssignments', ''), $nb_assignments);
        $csv_content[] = array();
    } else {
        // html part
        echo '
		 <div class="report_section">
			<h4>
				<a href="student.php"><img src="' . api_get_path(WEB_IMG_PATH) . 'students.gif">&nbsp;' . get_lang('Probationers') . ' (' . $nb_students . ')' . '</a>
			</h4>
			<table class="data_table">
				<tr>
					<td>
						' . get_lang('InactivesStudents') . '
					</td>
					<td align="right">
						' . $nb_inactive_students . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('AverageTimeSpentOnThePlatform') . '
					</td>
					<td align="right">
						' . (is_null($avg_time_spent) ? '' : api_time_to_hms($avg_time_spent)) . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('AverageCoursePerStudent') . '
					</td>
					<td align="right">
						' . (is_null($avg_courses_per_student) ? '' : $avg_courses_per_student) . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('AverageProgressInLearnpath') . '
					</td>
					<td align="right">
						' . (is_null($avg_total_progress) ? '' : round($avg_total_progress, 2) . '%') . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('AverageResultsToTheExercices') . '
					</td>
					<td align="right">
						' . (is_null($avg_results_to_exercises) ? '' : round($avg_results_to_exercises, 2) . '%') . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('AveragePostsInForum') . '
					</td>
					<td align="right">
						' . (is_null($nb_posts) ? '' : round($nb_posts, 2)) . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('AverageAssignments') . '
					</td>
					<td align="right">
						' . (is_null($nb_assignments) ? '' : round($nb_assignments, 2)) . '
					</td>
				</tr>
			</table>
			<a href="student.php">' . get_lang('SeeStudentList') . '</a>
		 </div>';
    }
}
if ($view == 'coach') {
    /*     * **************************************
     * Infos about sessions of the coach
     * ************************************** */
    $sessions = Tracking :: get_sessions_coached_by_user($_user['user_id']);
    $nb_sessions = count($sessions);
    $nb_sessions_past = $nb_sessions_future = $nb_sessions_current = 0;
    $courses = array();
    foreach ($sessions as $session) {
        if ($session['date_start'] == '0000-00-00') {
            $nb_sessions_current++;
        } else {
            $date_start = explode('-', $session['date_start']);
            $time_start = mktime(0, 0, 0, $date_start[1], $date_start[2], $date_start[0]);
            $date_end = explode('-', $session['date_end']);
            $time_end = mktime(0, 0, 0, $date_end[1], $date_end[2], $date_end[0]);
            if ($time_start < time() && time() < $time_end) {
                $nb_sessions_current++;
            } elseif (time() < $time_start) {
                $nb_sessions_future++;
            } elseif (time() > $time_end) {
                $nb_sessions_past++;
            }
        }
        $courses = array_merge($courses, Tracking::get_courses_list_from_session($session['id']));
    }

    if ($nb_sessions > 0) {
        $nb_courses_per_session = round(count($courses) / $nb_sessions, 2);
        $nb_students_per_session = round($nb_students / $nb_sessions, 2);
    } else {
        $nb_courses_per_session = null;
        $nb_students_per_session = null;
    }


    if ($export_csv) {
        //csv part
        $csv_content[] = array(get_lang('Sessions', ''));
        $csv_content[] = array(get_lang('NbActiveSessions', '') . ';' . $nb_sessions_current);
        $csv_content[] = array(get_lang('NbPastSessions', '') . ';' . $nb_sessions_past);
        $csv_content[] = array(get_lang('NbFutureSessions', '') . ';' . $nb_sessions_future);
        $csv_content[] = array(get_lang('NbStudentPerSession', '') . ';' . $nb_students_per_session);
        $csv_content[] = array(get_lang('NbCoursesPerSession', '') . ';' . $nb_courses_per_session);
        $csv_content[] = array();
    } else {
        // html part
        echo '
		 <div class="report_section">
			<h4>
				<a href="session.php"><img src="' . api_get_path(WEB_IMG_PATH) . 'sessions.gif">&nbsp;' . get_lang('Sessions') . ' (' . $nb_sessions . ')' . '</a>
			</h4>
			<table class="data_table">
				<tr>
					<td>
						' . get_lang('NbActiveSessions') . '
					</td>
					<td align="right">
						' . $nb_sessions_current . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('NbPastSessions') . '
					</td>
					<td align="right">
						' . $nb_sessions_past . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('NbFutureSessions') . '
					</td>
					<td align="right">
						' . $nb_sessions_future . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('NbStudentPerSession') . '
					</td>
					<td align="right">
						' . (is_null($nb_students_per_session) ? '' : $nb_students_per_session) . '
					</td>
				</tr>
				<tr>
					<td>
						' . get_lang('NbCoursesPerSession') . '
					</td>
					<td align="right">
						' . (is_null($nb_courses_per_session) ? '' : $nb_courses_per_session) . '
					</td>
				</tr>
			</table>
			<a href="session.php">' . get_lang('SeeSessionList') . '</a>
		 </div>';
    }
}
echo '<div class="clear">&nbsp;</div>';

if (api_is_allowed_to_create_course() && $view == 'teacher') {

    if ($nb_teacher_courses) {
        $table = new SortableTable('courses', 'get_number_of_courses', 'get_course_data');
        $parameters['view'] = 'teacher';
        $parameters['class'] = 'data_table';
        $table->set_additional_parameters($parameters);
        $table->set_header(0, get_lang('CourseTitle'), false, 'align="center"');
        $table->set_header(1, get_lang('NbStudents'), false);
        $table->set_header(2, get_lang('AvgTimeSpentInTheCourse') . Display :: return_icon('info3.png', get_lang('TimeOfActiveByTraining'), array('align' => 'middle', 'hspace' => '3px')), false);
        $table->set_header(3, get_lang('AvgStudentsProgress') . Display :: return_icon('info3.png', get_lang('AvgAllUsersInAllCourses'), array('align' => 'middle', 'hspace' => '3px')), false);
        $table->set_header(4, get_lang('AvgCourseScore') . Display :: return_icon('info3.png', get_lang('AvgAllUsersInAllCourses'), array('align' => 'middle', 'hspace' => '3px')), false);
        $table->set_header(5, get_lang('AvgExercisesScore') . Display :: return_icon('info3.png', get_lang('AvgAllUsersInAllCourses'), array('align' => 'middle', 'hspace' => '3px')), false);
        $table->set_header(6, get_lang('AvgMessages'), false);
        $table->set_header(7, get_lang('AvgAssignments'), false);
        $table->set_header(8, get_lang('Details'), false);

        $csv_content[] = array(
            get_lang('CourseTitle', ''),
            get_lang('NbStudents', ''),
            get_lang('AvgTimeSpentInTheCourse', ''),
            get_lang('AvgStudentsProgress', ''),
            get_lang('AvgCourseScore', ''),
            get_lang('AvgExercisesScore', ''),
            get_lang('AvgMessages', ''),
            get_lang('AvgAssignments', '')
        );

        //$html_table = $table->get_all_table_html();
        $all_data = Tracking::get_course_data(null, null, 1, null, false);
        //$html_table = $table->get_table_html();
        echo '<br/>';
        include(api_get_path(SYS_CODE_PATH) . 'mySpace/charts/trainings.js.php');
        echo '<br/>';
        //echo $html_table;
    }
}

if ($is_platform_admin && $view == 'admin') {
    echo '<a href="' . api_get_self() . '?view=admin&amp;display=coaches">' . get_lang('DisplayCoaches') . '</a> | ';
    echo '<a href="' . api_get_self() . '?view=admin&amp;display=useroverview">' . get_lang('DisplayUserOverview') . '</a>';
    if ($_GET['display'] == 'useroverview') {
        echo ' | <a href="' . api_get_self() . '?view=admin&amp;display=useroverview&amp;export=options">' . get_lang('ExportUserOverviewOptions') . '</a>';
    }
    if ($_GET['display'] === 'useroverview') {
        display_tracking_user_overview();
    } else {
        if ($export_csv) {
            $is_western_name_order = api_is_western_name_order(PERSON_NAME_DATA_EXPORT);
        } else {
            $is_western_name_order = api_is_western_name_order();
        }
        $sort_by_first_name = api_sort_by_first_name();
        $tracking_column = isset($_GET['tracking_list_coaches_column']) ? $_GET['tracking_list_coaches_column'] : ($is_western_name_order xor $sort_by_first_name) ? 1 : 0;
        $tracking_direction = (isset($_GET['tracking_list_coaches_direction']) && in_array(strtoupper($_GET['tracking_list_coaches_direction']), array('ASC', 'DESC', 'ASCENDING', 'DESCENDING', '0', '1'))) ? $_GET['tracking_list_coaches_direction'] : 'DESC';
        // Prepare array for column order - when impossible, use some of user names.
        if ($is_western_name_order) {
            $order = array(0 => 'firstname', 1 => 'lastname', 2 => ($sort_by_first_name ? 'firstname' : 'lastname'), 3 => 'login_date', 4 => ($sort_by_first_name ? 'firstname' : 'lastname'), 5 => ($sort_by_first_name ? 'firstname' : 'lastname'));
        } else {
            $order = array(0 => 'lastname', 1 => 'firstname', 2 => ($sort_by_first_name ? 'firstname' : 'lastname'), 3 => 'login_date', 4 => ($sort_by_first_name ? 'firstname' : 'lastname'), 5 => ($sort_by_first_name ? 'firstname' : 'lastname'));
        }
        $table = new SortableTable('tracking_list_coaches', 'count_coaches', null, ($is_western_name_order xor $sort_by_first_name) ? 1 : 0);
        $parameters['view'] = 'admin';
        $table->set_additional_parameters($parameters);
        if ($is_western_name_order) {
            $table->set_header(0, get_lang('FirstName'), true, 'align="center"');
            $table->set_header(1, get_lang('LastName'), true, 'align="center"');
        } else {
            $table->set_header(0, get_lang('LastName'), true, 'align="center"');
            $table->set_header(1, get_lang('FirstName'), true, 'align="center"');
        }
        $table->set_header(2, get_lang('TimeSpentOnThePlatform'), false);
        $table->set_header(3, get_lang('LastConnexion'), true, 'align="center"');
        $table->set_header(4, get_lang('NbStudents'), false);
        $table->set_header(5, get_lang('CountCours'), false);
        $table->set_header(6, get_lang('NumberOfSessions'), false);
        $table->set_header(7, get_lang('Sessions'), false, 'align="center"');

        if ($is_western_name_order) {
            $csv_header[] = array(
                get_lang('FirstName', ''),
                get_lang('LastName', ''),
                get_lang('TimeSpentOnThePlatform', ''),
                get_lang('LastConnexion', ''),
                get_lang('NbStudents', ''),
                get_lang('CountCours', ''),
                get_lang('NumberOfSessions', '')
            );
        } else {
            $csv_header[] = array(
                get_lang('LastName', ''),
                get_lang('FirstName', ''),
                get_lang('TimeSpentOnThePlatform', ''),
                get_lang('LastConnexion', ''),
                get_lang('NbStudents', ''),
                get_lang('CountCours', ''),
                get_lang('NumberOfSessions', '')
            );
        }

        $tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);

        $sqlCoachs = "SELECT DISTINCT scu.id_user as id_coach, user_id, lastname, firstname, MAX(login_date) as login_date
			FROM $tbl_user, $tbl_session_course_user scu, $tbl_track_login
			WHERE scu.id_user=user_id AND scu.status=2  AND login_user_id=user_id
			GROUP BY user_id ";

        if ($_configuration['multiple_access_urls'] == true) {
            $tbl_session_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
            $access_url_id = api_get_current_access_url_id();
            if ($access_url_id != -1) {
                $sqlCoachs = "SELECT DISTINCT scu.id_user as id_coach, user_id, lastname, firstname, MAX(login_date) as login_date
					FROM $tbl_user, $tbl_session_course_user scu, $tbl_track_login , $tbl_session_rel_access_url session_rel_url
					WHERE scu.id_user=user_id AND scu.status=2 AND login_user_id=user_id AND access_url_id = $access_url_id AND session_rel_url.session_id=id_session
					GROUP BY user_id ";
            }
        }
        if (!empty($order[$tracking_column])) {
            $sqlCoachs .= "ORDER BY " . $order[$tracking_column] . " " . $tracking_direction;
        }

        $result_coaches = Database::query($sqlCoachs, __FILE__, __LINE__);
        $total_no_coaches = Database::num_rows($result_coaches);
        $global_coaches = array();
        while ($coach = Database::fetch_array($result_coaches)) {
            $global_coaches[$coach['user_id']] = $coach;
        }

        $sql_session_coach = 'SELECT session.id_coach, user_id, lastname, firstname, MAX(login_date) as login_date
			FROM ' . $tbl_user . ',' . $tbl_sessions . ' as session,' . $tbl_track_login . '
			WHERE id_coach=user_id AND login_user_id=user_id
			GROUP BY user_id
			ORDER BY login_date ' . $tracking_direction;

        if ($_configuration['multiple_access_urls'] == true) {
            $tbl_session_rel_access_url = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
            $access_url_id = api_get_current_access_url_id();
            if ($access_url_id != -1) {
                $sql_session_coach = 'SELECT session.id_coach, user_id, lastname, firstname, MAX(login_date) as login_date
					FROM ' . $tbl_user . ',' . $tbl_sessions . ' as session,' . $tbl_track_login . ' , ' . $tbl_session_rel_access_url . ' as session_rel_url
					WHERE id_coach=user_id AND login_user_id=user_id  AND access_url_id = ' . $access_url_id . ' AND  session_rel_url.session_id=session.id
					GROUP BY user_id
					ORDER BY login_date ' . $tracking_direction;
            }
        }

        $result_sessions_coach = Database::query($sql_session_coach, __FILE__, __LINE__);
        $total_no_coaches += Database::num_rows($result_sessions_coach);
        while ($coach = Database::fetch_array($result_sessions_coach)) {
            $global_coaches[$coach['user_id']] = $coach;
        }

        $all_datas = array();

        foreach ($global_coaches as $id_coach => $coaches) {

            $time_on_platform = api_time_to_hms(Tracking :: get_time_spent_on_the_platform($coaches['user_id']));
            $last_connection = Tracking :: get_last_connection_date($coaches['user_id']);
            $nb_students = count(Tracking :: get_student_followed_by_coach($coaches['user_id']));
            $nb_courses = count(Tracking :: get_courses_followed_by_coach($coaches['user_id']));
            $nb_sessions = count(Tracking :: get_sessions_coached_by_user($coaches['user_id']));

            $table_row = array();
            if ($is_western_name_order) {
                $table_row[] = $coaches['firstname'];
                $table_row[] = $coaches['lastname'];
            } else {
                $table_row[] = $coaches['lastname'];
                $table_row[] = $coaches['firstname'];
            }
            $table_row[] = $time_on_platform;
            $table_row[] = $last_connection;
            $table_row[] = $nb_students;
            $table_row[] = $nb_courses;
            $table_row[] = $nb_sessions;
//            $table_row[] = '<center><a href="session.php?id_coach=' . $coaches['user_id'] . '">' . Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon actionstatisticsdetails')) . '</a></center>';
            $all_datas[] = $table_row;

            if ($is_western_name_order) {
                $csv_content[] = array(
                    api_html_entity_decode($coaches['firstname'], ENT_QUOTES, $charset),
                    api_html_entity_decode($coaches['lastname'], ENT_QUOTES, $charset),
                    $time_on_platform,
                    $last_connection,
                    $nb_students,
                    $nb_courses,
                    $nb_sessions
                );
            } else {
                $csv_content[] = array(
                    api_html_entity_decode($coaches['lastname'], ENT_QUOTES, $charset),
                    api_html_entity_decode($coaches['firstname'], ENT_QUOTES, $charset),
                    $time_on_platform,
                    $last_connection,
                    $nb_students,
                    $nb_courses,
                    $nb_sessions
                );
            }
        }

        if ($tracking_column != 3) {
            if ($tracking_direction == 'DESC') {
                usort($all_datas, 'rsort_users');
            } else {
                usort($all_datas, 'sort_users');
            }
        }

        if ($export_csv && $tracking_column != 3) {
            usort($csv_content, 'sort_users');
        }
        if ($export_csv) {
            $csv_content = array_merge($csv_header, $csv_content);
        }

        foreach ($all_datas as $row) {
            $table->addRow($row, 'align="right"');
        }

        $table->updateColAttributes(0, array('align' => 'center'));
        $table->updateColAttributes(1, array('align' => 'center'));
        $table->updateColAttributes(2, array('align' => 'center'));
        $table->updateColAttributes(3, array('align' => 'center'));
        $table->updateColAttributes(4, array('align' => 'center'));
        $table->updateColAttributes(5, array('align' => 'center'));
        $table->updateColAttributes(6, array('align' => 'center'));
        $table->updateColAttributes(7, array('align' => 'center'));
        $table->updateColAttributes(8, array('align' => 'center'));
        $table->display();
    }
}

// send the csv file if asked
if ($export_csv) {
    ob_end_clean();
    Export :: export_table_csv($csv_content, 'reporting_index');
}

// ending div#content
echo '</div>';

// bottom action toolbar
//echo '<div class="actions">';
//echo '</div>';

//footer
if (!$export_csv) {
    Display::display_footer();
}

/**
 * This function exports the table that we see in display_tracking_user_overview()
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since October 2008
 */
function export_tracking_user_overview() {
    // database table definitions
    Tracking::export_tracking_user_overview();
}

/**
 * Display a sortable table that contains an overview off all the reporting progress of all users and all courses the user is subscribed to
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since October 2008
 */
function display_tracking_user_overview() {
    Tracking::display_tracking_user_overview();
}

/**
 * get the numer of users of the platform
 *
 * @return integer
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since October 2008
 */
function get_number_of_users_tracking_overview() {
    return Tracking::get_number_of_users_tracking_overview();
}

/**
 * get all the data for the sortable table of the reporting progress of all users and all the courses the user is subscribed to.
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since October 2008
 */
function get_user_data_tracking_overview($from, $number_of_items, $column, $direction) {
    return Tracking::get_user_data_tracking_overview($from, $number_of_items, $column, $direction);
}

/**
 * Creates a small table in the last column of the table with the user overview
 *
 * @param integer $user_id the id of the user
 * @param array $url_params additonal url parameters
 * @param array $row the row information (the other columns)
 * @return html code
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since October 2008
 */
function course_info_tracking_filter($user_id, $url_params, $row) {
    return Tracking::course_info_tracking_filter($user_id, $url_params, $row);
}

/**
 * Get general information about the exercise performance of the user
 * the total obtained score (all the score on all the questions)
 * the maximum score that could be obtained
 * the number of questions answered
 * the success percentage
 *
 * @param integer $user_id the id of the user
 * @param string $course_code the course code
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since November 2008
 */
function exercises_results($user_id, $course_code) {
    return Tracking::exercises_results($user_id, $course_code);
}

/**
 * Displays a form with all the additionally defined user fields of the profile
 * and give you the opportunity to include these in the CSV export
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since November 2008
 */
function display_user_overview_export_options() {
    Tracking::display_user_overview_export_options();
}

/**
 * Get all information that the user with user_id = $user_data has
 * entered in the additionally defined profile fields
 *
 * @param integer $user_id the id of the user
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since November 2008
 */
function get_user_overview_export_extra_fields($user_id) {
    // include the user manager
    require_once api_get_path(LIBRARY_PATH) . 'usermanager.lib.php';

    $extra_data = UserManager::get_extra_user_data($user_id, true);
    return $extra_data;
}

/**
 * Get number of courses for sortable with pagination
 * @return int
 */
function get_number_of_courses() {
    global $courses;
    return count($courses);
}

/**
 * Get data for courses list in sortable with pagination
 * @return array
 */
function get_course_data($from, $number_of_items, $column, $direction) {
    return Tracking::get_course_data($from, $number_of_items, $column, $direction);
}