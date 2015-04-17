<?php // $Id: user_portal.php 22375 2009-07-26 18:54:59Z herodoto $

/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
*	This is the index file displayed when a user is logged in on Dokeos.
*
*	It displays:
*	- personal course list
*	- menu bar
*
*	Part of the what's new ideas were based on a rene haentjens hack
*
*	Search for
*	CONFIGURATION parameters
*	to modify settings
*
*	@todo rewrite code to separate display, logic, database code
*	@package dokeos.main
==============================================================================
*/

/**
 * @todo shouldn't the SCRIPTVAL_ and CONFVAL_ constant be moved to the config page? Has anybody any idea what the are used for?
 * 		 if these are really configuration settings then we can add those to the dokeos config settings
 * @todo move get_personal_course_list and some other functions to a more appripriate place course.lib.php or user.lib.php
 * @todo use api_get_path instead of $rootAdminWeb
 * @todo check for duplication of functions with index.php (user_portal.php is orginally a copy of index.php)
 * @todo display_digest, shouldn't this be removed and be made into an extension?
 */

/*
==============================================================================
		INIT SECTION
==============================================================================
*/
// Don't change these settings
define('SCRIPTVAL_No', 0);
define('SCRIPTVAL_InCourseList', 1);
define('SCRIPTVAL_UnderCourseList', 2);
define('SCRIPTVAL_Both', 3);
define('SCRIPTVAL_NewEntriesOfTheDay', 4);
define('SCRIPTVAL_NewEntriesOfTheDayOfLastLogin', 5);
define('SCRIPTVAL_NoTimeLimit', 6);
// End 'don't change' section


/*
-----------------------------------------------------------
	Included libraries
-----------------------------------------------------------
*/
require_once './main/inc/global.inc.php';
$libpath = api_get_path(LIBRARY_PATH);
require_once $libpath.'course.lib.php';
require_once $libpath.'debug.lib.inc.php';
require_once $libpath.'system_announcements.lib.php';
require_once $libpath.'groupmanager.lib.php';
require_once $libpath.'usermanager.lib.php';
require_once $libpath.'certificatemanager.lib.php';

api_block_anonymous_users(); // only users who are logged in can proceed

$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.toggle.js" type="text/javascript" language="javascript"></script>';

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.5.1.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';

$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/colorbox.css" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/jquery.colorbox.js" language="javascript"></script>';
/*
-----------------------------------------------------------
	Table definitions
-----------------------------------------------------------
*/
//Database table definitions
$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
$main_admin_table 		= Database :: get_main_table(TABLE_MAIN_ADMIN);
$main_course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
$main_course_user_table         = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$main_category_table            = Database :: get_main_table(TABLE_MAIN_CATEGORY);

/*
-----------------------------------------------------------
	Constants and CONFIGURATION parameters
-----------------------------------------------------------
*/
// ---- Course list options ----
define('CONFVAL_showCourseLangIfNotSameThatPlatform', true);
// Preview of course content
// to disable all: set CONFVAL_maxTotalByCourse = 0
// to enable all: set e.g. CONFVAL_maxTotalByCourse = 5
// by default disabled since what's new icons are better (see function display_digest() )
define('CONFVAL_maxValvasByCourse', 2); // Maximum number of entries
define('CONFVAL_maxAgendaByCourse', 2); // collected from each course
define('CONFVAL_maxTotalByCourse', 0); //  and displayed in summary.
define('CONFVAL_NB_CHAR_FROM_CONTENT', 80);
// Order to sort data
$orderKey = array('keyTools', 'keyTime', 'keyCourse'); // default "best" Choice
//$orderKey = array('keyTools', 'keyCourse', 'keyTime');
//$orderKey = array('keyCourse', 'keyTime', 'keyTools');
//$orderKey = array('keyCourse', 'keyTools', 'keyTime');
define('CONFVAL_showExtractInfo', SCRIPTVAL_UnderCourseList);
// SCRIPTVAL_InCourseList		// best choice if $orderKey[0] == 'keyCourse'
// SCRIPTVAL_UnderCourseList	// best choice
// SCRIPTVAL_Both // probably only for debug
//define('CONFVAL_dateFormatForInfosFromCourses', get_lang('dateFormatShort'));
define('CONFVAL_dateFormatForInfosFromCourses', get_lang('dateFormatLong'));
//define("CONFVAL_limitPreviewTo",SCRIPTVAL_NewEntriesOfTheDay);
//define("CONFVAL_limitPreviewTo",SCRIPTVAL_NoTimeLimit);
define("CONFVAL_limitPreviewTo", SCRIPTVAL_NewEntriesOfTheDayOfLastLogin);

// this is the main function to get the course list
$personal_course_list = UserManager::get_personal_session_course_list($_user['user_id']);

// check if a user is enrolled only in one course for going directly to the course after the login
if (api_get_setting('go_to_course_after_login') == 'true') {
	if (!isset($_SESSION['coursesAlreadyVisited']) && is_array($personal_course_list) && count($personal_course_list) == 1) {

		$key = array_keys($personal_course_list);
		$course_info = $personal_course_list[$key[0]];

		$course_directory = $course_info['d'];
		$id_session = isset($course_info['id_session'])?$course_info['id_session']:0;
		header('location:'.api_get_path(WEB_COURSE_PATH).$course_directory.'/?id_session='.$id_session);
		exit;
	}
}

/*if(api_is_allowed_to_create_course() && !isset($_GET['sessionview'])){
	$nosession = true;
} else {
	$nosession = false;
}*/

$nosession = false;

if (api_get_setting('use_session_mode') == 'true' && !$nosession) {
	$display_actives = !isset($_GET['inactives']);
}

$nameTools = get_lang('MyCourses');


/*
-----------------------------------------------------------
	Check configuration parameters integrity
-----------------------------------------------------------
*/
if (CONFVAL_showExtractInfo != SCRIPTVAL_UnderCourseList and $orderKey[0] != "keyCourse") {
	// CONFVAL_showExtractInfo must be SCRIPTVAL_UnderCourseList to accept $orderKey[0] !="keyCourse"
	if (DEBUG || api_is_platform_admin()){ // Show bug if admin. Else force a new order
		die('
					<strong>config error:'.__FILE__.'</strong><br />
					set
					<ul>
						<li>
							CONFVAL_showExtractInfo = SCRIPTVAL_UnderCourseList
							(actually : '.CONFVAL_showExtractInfo.')
						</li>
					</ul>
					or
					<ul>
						<li>
							$orderKey[0] != "keyCourse"
							(actually : '.$orderKey[0].')
						</li>
					</ul>');
	}else {
		$orderKey = array ('keyCourse', 'keyTools', 'keyTime');
	}
}

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
Display :: display_header($nameTools);


/*
==============================================================================
    FUNCTIONS

    display_admin_links()
    display_create_course_link()
    display_edit_course_list_links()
    display_trainer_links
    display_digest($toolsList, $digest, $orderKey, $courses)
    show_notification($my_course)

    get_personal_course_list($user_id)
    get_logged_user_course_html($my_course)
    get_user_course_categories()
==============================================================================
*/
/*
-----------------------------------------------------------
	Database functions
	some of these can go to database layer.
-----------------------------------------------------------
*/

/**
* Database function that gets the list of courses for a particular user.
* @param $user_id, the id of the user
* @return an array with courses
*/
function get_personal_course_list($user_id) {
	// initialisation
	$personal_course_list = array();

	// table definitions
	$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
	$main_course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$tbl_session_course 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$tbl_session_course_user= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$tbl_session 			= Database :: get_main_table(TABLE_MAIN_SESSION);

	$user_id = Database::escape_string($user_id);
	$personal_course_list = array ();

	//Courses in which we suscribed out of any session
	$personal_course_list_sql = "SELECT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i,
										course.tutor_name t, course.course_language l, course_rel_user.status s, course_rel_user.sort sort,
										course_rel_user.user_course_cat user_course_cat
										FROM    ".$main_course_table."       course,".$main_course_user_table."   course_rel_user
										WHERE course.code = course_rel_user.course_code"."
										AND   course_rel_user.user_id = '".$user_id."'
										ORDER BY course_rel_user.user_course_cat, course_rel_user.sort ASC,i";

	$course_list_sql_result = Database::query($personal_course_list_sql, __FILE__, __LINE__);

	while ($result_row = Database::fetch_array($course_list_sql_result)) {
		$personal_course_list[] = $result_row;
	}

	//$personal_course_list = array_merge($personal_course_list, $course_list_sql_result);

	$personal_course_list_sql = "SELECT DISTINCT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i, course.tutor_name t, course.course_language l, 5 as s
								FROM $main_course_table as course, $tbl_session_course_user as srcru
								WHERE srcru.course_code=course.code AND srcru.id_user='$user_id'";

	$course_list_sql_result = Database::query($personal_course_list_sql, __FILE__, __LINE__);

	while ($result_row = Database::fetch_array($course_list_sql_result)) {
		$personal_course_list[] = $result_row;
	}

	//$personal_course_list = array_merge($personal_course_list, $course_list_sql_result);

	$personal_course_list_sql = "SELECT DISTINCT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i, course.tutor_name t, course.course_language l, 2 as s
								FROM $main_course_table as course, $tbl_session_course as src, $tbl_session as session
								WHERE session.id_coach='$user_id' AND session.id=src.id_session AND src.course_code=course.code";

	$course_list_sql_result = Database::query($personal_course_list_sql, __FILE__, __LINE__);

	//$personal_course_list = array_merge($personal_course_list, $course_list_sql_result);

	while ($result_row = Database::fetch_array($course_list_sql_result)) {
		$personal_course_list[] = $result_row;
	}
	return $personal_course_list;
}


/*
-----------------------------------------------------------
	Display functions
-----------------------------------------------------------
*/

/**
 * Warning: this function defines a global.
 * @todo use the correct get_path function
 */
function display_admin_links() {
	global $rootAdminWeb;
	echo "<li><a href=\"".$rootAdminWeb."\">".get_lang('PlatformAdmin')."</a></li>";
}

/**
 * Enter description here...
 *
 */
function display_create_course_link() {
	echo "<li><a href=\"main/create_course/add_course.php\">".get_lang('CourseCreate')."</a></li>";
}

/**
 * Enter description here...
 *
 */
function display_edit_course_list_links() {
	echo "<li><a href=\"main/auth/courses.php\">".get_lang('OrganiseCourses')."</a></li>";
}

/**
 * Trainers manual, Video tutorials and Trainers training is displayed.
 * 
 * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
 */
function display_trainer_links() {
    $user_selected_language = api_get_interface_language();
    if (!isset($user_selected_language)) {
        $user_selected_language = api_get_setting('platformLanguage');
    }
    $trainers_training_link = 'http://www.dokeos.com/en/training/';
    if ($user_selected_language == "french") {
        $trainers_training_link = 'http://www.dokeos.com/fr/training/';
    }
    echo "<li><a href=\"http://www.dokeos.com/en/trainer-manual/\">" . get_lang('TrainersManual') . "</a></li>";
    echo "<li><a href=\"http://www.dokeos.com/en/video-tutorials/\">" . get_lang('VideoTutorials') . "</a></li>";
    echo "<li><a href=\"" . $trainers_training_link . "\">" . get_lang('TrainersTraining') . "</a></li>";
}

/**
 * Show history sessions
 *
 */
function display_history_course_session() {
	if (isset($_GET['history']) && intval($_GET['history']) == 1) {
		echo "<li><a href=\"user_portal.php\">".get_lang('DisplayTrainingList')."</a></li>";
	} else {
		echo "<li><a href=\"user_portal.php?history=1\">".get_lang('HistoryTrainingSessions')."</a></li>";
	}

}

/**
*	Displays a digest e.g. short summary of new agenda and announcements items.
*	This used to be displayed in the right hand menu, but is now
*	disabled by default (see config settings in this file) because most people like
*	the what's new icons better.
*
*	@version 1.0
*/
function display_digest($toolsList, $digest, $orderKey, $courses) {
	if (is_array($digest) && (CONFVAL_showExtractInfo == SCRIPTVAL_UnderCourseList || CONFVAL_showExtractInfo == SCRIPTVAL_Both)) {
		// // // LEVEL 1 // // //
		reset($digest);
		echo "<br /><br />\n";
		while (list($key1) = each($digest)) {
			if (is_array($digest[$key1])) {
				// // // Title of LEVEL 1 // // //
				echo "<strong>\n";
				if ($orderKey[0] == 'keyTools') {
					$tools = $key1;
					echo $toolsList[$key1]['name'];
				} elseif ($orderKey[0] == 'keyCourse') {
					$courseSysCode = $key1;
					echo "<a href=\"", api_get_path(WEB_COURSE_PATH), $courses[$key1]['coursePath'], "\">", $courses[$key1]['courseCode'], "</a>\n";
				} elseif ($orderKey[0] == 'keyTime') {
					echo format_locale_date(CONFVAL_dateFormatForInfosFromCourses, strtotime($digest[$key1]));
				}
				echo "</strong>\n";
				// // // End Of Title of LEVEL 1 // // //
				// // // LEVEL 2 // // //
				reset($digest[$key1]);
				while (list ($key2) = each($digest[$key1])) {
					// // // Title of LEVEL 2 // // //
					echo "<p>\n", "\n";
					if ($orderKey[1] == 'keyTools') {
						$tools = $key2;
						echo $toolsList[$key2][name];
					} elseif ($orderKey[1] == 'keyCourse') {
						$courseSysCode = $key2;
						echo "<a href=\"", api_get_path(WEB_COURSE_PATH), $courses[$key2]['coursePath'], "\">", $courses[$key2]['courseCode'], "</a>\n";
					} elseif ($orderKey[1] == 'keyTime') {
						echo format_locale_date(CONFVAL_dateFormatForInfosFromCourses, strtotime($key2));
					}
					echo "\n";
					echo "</p>";
					// // // End Of Title of LEVEL 2 // // //
					// // // LEVEL 3 // // //
					reset($digest[$key1][$key2]);
					while (list ($key3, $dataFromCourse) = each($digest[$key1][$key2])) {
						// // // Title of LEVEL 3 // // //
						if ($orderKey[2] == 'keyTools') {
							$level3title = "<a href=\"".$toolsList[$key3]["path"].$courseSysCode."\">".$toolsList[$key3]['name']."</a>";
						} elseif ($orderKey[2] == 'keyCourse') {
							$level3title = "&#8226; <a href=\"".$toolsList[$tools]["path"].$key3."\">".$courses[$key3]['courseCode']."</a>\n";
						} elseif ($orderKey[2] == 'keyTime') {
							$level3title = "&#8226; <a href=\"".$toolsList[$tools]["path"].$courseSysCode."\">".format_locale_date(CONFVAL_dateFormatForInfosFromCourses, strtotime($key3))."</a>";
						}
						// // // End Of Title of LEVEL 3 // // //
						// // // LEVEL 4 (data) // // //
						reset($digest[$key1][$key2][$key3]);
						while (list ($key4, $dataFromCourse) = each($digest[$key1][$key2][$key3])) {
							echo $level3title, ' &ndash; ', api_substr(strip_tags($dataFromCourse), 0, CONFVAL_NB_CHAR_FROM_CONTENT);
							//adding ... (three dots) if the texts are too large and they are shortened
							if (api_strlen($dataFromCourse) >= CONFVAL_NB_CHAR_FROM_CONTENT) {
								echo '...';
							}
						}
						echo "<br />\n";
					}
				}
			}
		}
	}
} // end function display_digest

/**
 * Display code for one specific course a logged in user is subscribed to.
 * Shows a link to the course, what's new icons...
 *
 * $my_course['d'] - course directory
 * $my_course['i'] - course title
 * $my_course['c'] - visual course code
 * $my_course['k']  - system course code
 * $my_course['db'] - course database
 *
 * @version 1.0.3
 * @todo refactor into different functions for database calls | logic | display
 * @todo replace single-character $my_course['d'] indices
 * @todo move code for what's new icons to a separate function to clear things up
 * @todo add a parameter user_id so that it is possible to show the courselist of other users (=generalisation). This will prevent having to write a new function for this.
 */
function get_logged_user_course_html($course, $session_id = 0, $class='courses') {
	global $charset;
	global $nosession;

	$current_uid = api_get_user_id();
	$info = api_get_course_info($course['code']);
	$status_course = CourseManager::get_user_in_course_status($current_uid, $course['code']);

	if (!is_array($course['code'])) {
		$my_course = api_get_course_info($course['code']);
		$my_course['k'] = $my_course['id'];
		$my_course['db'] = $my_course['dbName'];
		$my_course['c'] = $my_course['official_code'];
		$my_course['i'] = $my_course['name'];
		$my_course['d'] = $my_course['path'];
		$my_course['t'] = $my_course['titular'];
		$my_course['id_session'] = $session_id;
		$my_course['status'] = ((empty($session_id))?$status_course:5);
	}

	if (api_get_setting('use_session_mode')=='true' && !$nosession) {
		global $now, $date_start, $date_end;
	}

	//initialise
	$result = '';

	// Table definitions
	//$statistic_database = Database::get_statistic_database();
	$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
	$tbl_session 			= Database :: get_main_table(TABLE_MAIN_SESSION);
	$tbl_session_category 	= Database :: get_main_table(TABLE_MAIN_SESSION_CATEGORY);
	$course_database = $my_course['db'];
	$course_tool_table 			= Database :: get_course_table(TABLE_TOOL_LIST, $course_database);
	$tool_edit_table 			= Database :: get_course_table(TABLE_ITEM_PROPERTY, $course_database);
	$course_group_user_table 	= Database :: get_course_table(TOOL_USER, $course_database);

	$user_id = api_get_user_id();
	$course_system_code = $my_course['k'];
	$course_visual_code = $my_course['c'];
	$course_title = $my_course['i'];
	$course_directory = $my_course['d'];
	$course_teacher = $my_course['t'];
	$course_teacher_email = isset($my_course['email'])?$my_course['email']:'';
	$course_info = Database :: get_course_info($course_system_code);
	//$course_access_settings = CourseManager :: get_access_settings($course_system_code);
    require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
    $percentage_score = Tracking :: get_avg_student_score($user_id, $course_system_code, array());
    $progress = Tracking :: get_avg_student_progress($user_id, $course_system_code);

	$course_id = isset($course_info['course_id'])?$course_info['course_id']:null;
	$course_visibility = $course_info['visibility'];


	$user_in_course_status = CourseManager :: get_user_in_course_status(api_get_user_id(), $course_system_code);

	//function logic - act on the data
	$is_virtual_course = CourseManager :: is_virtual_course_from_system_code($my_course['k']);
	if ($is_virtual_course) {
		// If the current user is also subscribed in the real course to which this
		// virtual course is linked, we don't need to display the virtual course entry in
		// the course list - it is combined with the real course entry.
		$target_course_code = CourseManager :: get_target_of_linked_course($course_system_code);
		$is_subscribed_in_target_course = CourseManager :: is_user_subscribed_in_course(api_get_user_id(), $target_course_code);
		if ($is_subscribed_in_target_course) {
			return; //do not display this course entry
		}
	}
	$has_virtual_courses = CourseManager :: has_virtual_courses_from_code($course_system_code, api_get_user_id());
	if ($has_virtual_courses) {
		$return_result = CourseManager :: determine_course_title_from_course_info(api_get_user_id(), $course_info);
		$course_display_title = $return_result['title'];
		$course_display_code = $return_result['code'];
	} else {
		$course_display_title = $course_title;
		$course_display_code = $course_visual_code;
	}

	$s_course_status = $my_course['status'];
	$is_coach = api_is_coach($my_course['id_session'],$course['code']);

    $course_image_syspath = api_get_path(SYS_COURSE_PATH).$my_course['d'].'/course_image.png';
    $course_image_webpath = api_get_path(WEB_COURSE_PATH).$my_course['d'].'/course_image.png';
    if (file_exists($course_image_syspath)) {
        $s_htlm_status_icon = "<img src='".$course_image_webpath."' class='preview' />";
    }
    else {
        $s_htlm_status_icon = Display::return_icon('thumbnail-course.png', ''.get_lang('ImageCourse').'');
    }

    


	//display course entry
	$result.="\n\t";
    $result .= '<div class="' . $class . '" style="padding: 8px; float:left;">';
	//show a hyperlink to the course, unless the course is closed and user is not course admin
	if ($course_visibility != COURSE_VISIBILITY_CLOSED || $user_in_course_status == COURSEMANAGER) {
		if(api_get_setting('use_session_mode')=='true' && !$nosession) {
			if(empty($my_course['id_session'])) {
				$my_course['id_session'] = 0;
			}
            if ($user_in_course_status == COURSEMANAGER || ($date_start <= $now && $date_end >= $now) || $date_start == '0000-00-00') {
                            $result .= '<a href="' . api_get_path(WEB_COURSE_PATH) . $course_directory . '/?id_session=' . $my_course['id_session'] . '"><div class="thumbnail-left">'.$s_htlm_status_icon.'</div></a>';
                            $result .= '<a href="' . api_get_path(WEB_COURSE_PATH) . $course_directory . '/?id_session=' . $my_course['id_session'] . '"><div class="description-center"><span style="font-size:20px; font-weight:bolder;"><strong>' . $course_display_title . '</strong></span></a>';
			}
		} else {
            $result .= '<a href="' . api_get_path(WEB_COURSE_PATH) . $course_directory . '/"><span class="coursestatusicons">' . $s_htlm_status_icon . '</span><strong>' . $course_display_title . '</strong></a>';
            
		}
	} else {
//		$result .= '<strong>'.$course_display_title.'</strong>'." "." ".get_lang('CourseClosed')."";
	}
    // show the course_code and teacher if chosen to display this
    if (api_get_setting('display_coursecode_in_courselist') == 'true' || api_get_setting('display_teacher_in_courselist') == 'true') {
        $result .= '<br />';
    }
    if (api_get_setting('display_teacher_in_courselist') == 'true') {
        if (api_get_setting('use_session_mode') == 'true' && !$nosession) {
            $coachs_course = api_get_coachs_from_course($my_course['id_session'], $course['code']);
            $course_coachs = array();
            if (is_array($coachs_course)) {
                foreach ($coachs_course as $coach_course) {
                    $course_coachs[] = api_get_person_name($coach_course['firstname'], $coach_course['lastname']);
                }
            }
            
            if ($s_course_status == 1 || ($s_course_status == 5 && empty($my_course['id_session']))) {
                
                $teacher .= $course_teacher;
            }

            if (($s_course_status == 5 && !empty($my_course['id_session'])) || ($is_coach && $s_course_status != 1)) {
               if(count($course_coachs)> 2){
                    echo '<div id="view_coachs_dialog" style="display:none;">';
                    echo $teachers =  implode(', ', $course_coachs);
                    echo '</div>
                    <script type="text/javascript">
                        $(document).ready(function() {
                            $("#view_coachs").click(function(){
                                $("#view_coachs_dialog").dialog({modal: true, title: "'.get_lang('Course' . ucwords($type)).'",width: "600px"});
                            });
                        });
                    </script>';
                   $teacher .=   '  <span style="font-weight: 400;">' . count($course_coachs) . '</span> <img src="' . api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/images/action/online.png" id="view_coachs" alt="" style="cursor: pointer;margin-top: -7px;" />';
               }else{
                   $teacher .=  implode(', ', $course_coachs);
               }
                
            }
        } else {
            $teacher .= $course_teacher;
            
        }

        if (!empty($course_teacher_email)) {
            //$teacher .= ' (' . $course_teacher_email . ')';
        }
    }
    
    $percentageScore = (!is_null($percentage_score)) ? $percentage_score.'%' : '0%';
    $progress_t = (!is_null($progress)) ? $progress.'%' : '0%';    
    $result .='<div class="list-middle">';
    $result .='<span>With : </span><span>'.$teacher.'</span><br/>';
    $result .='<span>Score : </span><span>'.$percentageScore.'</span><br/>';
    $result .='<span>Progress : </span><span>'.$progress_t.'</span><br/>';
    $result .='<br/>';
    $result .='</div>';
    $result .='</div>';
    
    $result .= '<a href="' . api_get_path(WEB_COURSE_PATH) . $course_directory . '/?id_session=' . $my_course['id_session'] . '"><div class="b-courselist">'.get_lang('Enter').'</div></a>';
    
	if (api_get_setting('display_coursecode_in_courselist') == 'true') {
//        $result .= $course_display_code;
    }
        if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
//        $result .= ' &ndash; ';
    }


	  // display the what's new icons
    if ($course_visibility != COURSE_VISIBILITY_CLOSED && $my_course['id_session'] == 0) {
        $result .= show_notification($my_course, $my_course['id_session']);
    }

	if ((CONFVAL_showExtractInfo == SCRIPTVAL_InCourseList || CONFVAL_showExtractInfo == SCRIPTVAL_Both) && $nbDigestEntries > 0) {

		reset($digest);
        $result .= '<ul>';
		while (list ($key2) = each($digest[$thisCourseSysCode])) {
			$result .= '<li>';
			if ($orderKey[1] == 'keyTools') {
				$result .= "<a href=\"$toolsList[$key2] [\"path\"] $thisCourseSysCode \">";
				$result .= "$toolsList[$key2][\"name\"]</a>";
			} else {
				$result .= format_locale_date(CONFVAL_dateFormatForInfosFromCourses, strtotime($key2));
			}
			$result .= '</li>';
			$result .= '<ul>';
            reset($digest[$thisCourseSysCode][$key2]);
			while (list ($key3, $dataFromCourse) = each($digest[$thisCourseSysCode][$key2])) {
				$result .= '<li>';
				if ($orderKey[2] == 'keyTools') {
					$result .= "<a href=\"$toolsList[$key3] [\"path\"] $thisCourseSysCode \">";
					$result .= "$toolsList[$key3][\"name\"]</a>";
				} else {
					$result .= format_locale_date(CONFVAL_dateFormatForInfosFromCourses, strtotime($key3));
				}
				$result .= '<ul compact="compact">';
				reset($digest[$thisCourseSysCode][$key2][$key3]);
				while (list ($key4, $dataFromCourse) = each($digest[$thisCourseSysCode][$key2][$key3])) {
					$result .= '<li>';
					$result .= htmlspecialchars(api_substr(strip_tags($dataFromCourse), 0, CONFVAL_NB_CHAR_FROM_CONTENT), ENT_QUOTES, $charset);
					$result .= '</li>';
				}
				$result .= '</ul>';
				$result .= '</li>';
			}
			$result .= '</ul>';
			$result .= '</li>';
		}
		$result .= '</ul>';
	}
	$result .= '</div>';


	if (api_get_setting('use_session_mode') == 'true' && !$nosession) {
		$session = '';
		$active = false;
		if (!empty($my_course['session_name'])) {

			// Request for the name of the general coach
			$sql = 'SELECT lastname, firstname,sc.name
			FROM '.$tbl_session.' ts
			LEFT JOIN '.$main_user_table .' tu
			ON ts.id_coach = tu.user_id
			INNER JOIN '.$tbl_session_category.' sc ON ts.session_category_id = sc.id
			WHERE ts.id='.(int) $my_course['id_session']. ' LIMIT 1';

			$rs = Database::query($sql, __FILE__, __LINE__);
			$sessioncoach = Database::store_result($rs);
			$sessioncoach = $sessioncoach[0];

			$session = array();
			$session['title'] = $my_course['session_name'];
			$session_category_id = CourseManager::get_session_category_id_by_session_id($my_course['id_session']);
			$session['category'] = $sessioncoach['name'];
			if ( $my_course['date_start']=='0000-00-00' ) {
				$session['dates'] = get_lang('WithoutTimeLimits');
				if (api_get_setting('show_session_coach') === 'true') {
					$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($sessioncoach['firstname'], $sessioncoach['lastname']);
				}
				$active = true;
			} else {
				$session ['dates'] = ' - '.get_lang('From').' '.$my_course['date_start'].' '.get_lang('To').' '.$my_course['date_end'];
				if (api_get_setting('show_session_coach') === 'true') {
					$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($sessioncoach['firstname'], $sessioncoach['lastname']);
				}
				$active = ($date_start <= $now && $date_end >= $now)?true:false;
			}
		}
		$output = array ($my_course['user_course_cat'], $result, $my_course['id_session'], $session, 'active'=>$active,'session_category_id'=>$session_category_id);
	} else {
		$output = array ($my_course['user_course_cat'], $result);
	}

	return $output;
}

/**
 * Get the session box details as an array
 * @param	int	Session ID
 * @return	array	Empty array or session array ['title'=>'...','category'=>'','dates'=>'...','coach'=>'...','active'=>true/false,'session_category_id'=>int]
 */
function get_session_title_box($session_id) {
	global $nosession;
	$output = array();
//	if (api_get_setting('use_session_mode')=='true' && !$nosession) {

		$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
		$tbl_session 			= Database :: get_main_table(TABLE_MAIN_SESSION);
		$tbl_session_category 		= Database :: get_main_table(TABLE_MAIN_SESSION_CATEGORY);
		$active = false;
		// Request for the name of the general coach
		$sql =
		'SELECT tu.lastname, tu.firstname, ts.name, ts.date_start, ts.date_end, ts.session_category_id
		FROM '.$tbl_session.' ts
		LEFT JOIN '.$main_user_table .' tu
		ON ts.id_coach = tu.user_id
		WHERE ts.id='.intval($session_id);
		$rs = Database::query($sql, __FILE__, __LINE__);
		$session_info = Database::store_result($rs);
		$session_info = $session_info[0];
		$session = array();
		$session['title'] = $session_info[2];
		$session['coach'] = '';
		if ( $session_info[3]=='0000-00-00' ) {
			$session['dates'] = get_lang('WithoutTimeLimits');
			if ( api_get_setting('show_session_coach') === 'true' ) {
				$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($session_info[1], $session_info[0]);
			}
			$active = true;
		} else {
			$session ['dates'] = get_lang('From').' '.$session_info[3].' '.get_lang('Until').' '.$session_info[4];
			if ( api_get_setting('show_session_coach') === 'true' ) {
				$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($session_info[1], $session_info[0]);
			}
			$active = ($date_start <= $now && $date_end >= $now)?true:false;
		}
		$session['active'] = $active;
		$session['session_category_id'] = $session_info[5];
		$output = $session;
//	}
	return $output;
}

/**
 * Display code for one specific course a logged in user is subscribed to.
 * Shows a link to the course, what's new icons...
 *
 * $my_course['d'] - course directory
 * $my_course['i'] - course title
 * $my_course['c'] - visual course code
 * $my_course['k']   - system course code
 * $my_course['db']  - course database
 *
 * @version 1.0.3
 * @todo refactor into different functions for database calls | logic | display
 * @todo replace single-character $my_course['d'] indices
 * @todo move code for what's new icons to a separate function to clear things up
 * @todo add a parameter user_id so that it is possible to show the courselist of other users (=generalisation). This will prevent having to write a new function for this.
 */
function get_global_courses_list($user_id) {
	//1. get list of sessions the user is subscribed to
	//2. build an ordered list of session categories and sessions
	//3. fill this ordered list with course details
	global $charset;
	global $nosession;

	if (api_get_setting('use_session_mode')=='true' && !$nosession) {
		global $now, $date_start, $date_end;
	}

	$output = array();
	return $output;
}

/**
 * Returns the "what's new" icon notifications
 * @param	array	Course information array, containing at least elements 'db' and 'k'
 * @return	string	The HTML link to be shown next to the course
 * @version
 */
function show_notification($my_course, $session_id = '') {
    $session_part = $session_index = '';
    if ($session_id != '') {
        $session_part = " AND access_session_id = '$session_id'";
        $session_index = ', access_session_id';
    }
    $user_id = api_get_user_id();
    $course_database = $my_course['db'];
    $course_tool_table = Database::get_course_table(TABLE_TOOL_LIST, $course_database);
    $tool_edit_table = Database::get_course_table(TABLE_ITEM_PROPERTY, $course_database);
    $t_track_e_access = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
    // get the user's last access dates to all tools of this course
    $sqlLastTrackInCourse = "SELECT access_tool,access_date FROM $t_track_e_access USE INDEX (access_cours_code, access_user_id $session_index)
									 WHERE access_cours_code = '" . $my_course['k'] . "'
                            AND access_user_id = '$user_id' $session_part";
    $resLastTrackInCourse = Database::query($sqlLastTrackInCourse, __FILE__, __LINE__);
    $oldestTrackDate = "3000-01-01 00:00:00";
    while ($lastTrackInCourse = Database::fetch_array($resLastTrackInCourse)) {
        $lastTrackInCourseDate[$lastTrackInCourse['access_tool']] = $lastTrackInCourse['access_date'];
        if ($oldestTrackDate > $lastTrackInCourse['access_date']) {
            $oldestTrackDate = $lastTrackInCourse['access_date'];
        }
    }
    // get the last edits of all tools of this course
    $sql = "SELECT tet.lastedit_date,tet.to_user_id,tet.visibility, tet.lastedit_date last_date, tet.tool tool, tet.ref ref,
						tet.lastedit_type type, tet.to_group_id group_id,
						ctt.image image, ctt.link link
					FROM $tool_edit_table tet, $course_tool_table ctt
					WHERE tet.lastedit_date > '$oldestTrackDate'
					AND ctt.name = tet.tool
					AND ctt.visibility = '1'
					AND tet.lastedit_user_id != $user_id
					ORDER BY tet.lastedit_date";

    $res = Database::query($sql);
    //get the group_id's with user membership
    $group_ids = GroupManager :: get_group_ids($course_database, $user_id);
    $group_ids[] = 0; //add group 'everyone'
    //filter all selected items
    while ($res && ($item_property = Database::fetch_array($res, 'ASSOC'))) {
        if ((!isset($lastTrackInCourseDate[$item_property['tool']])
                || $lastTrackInCourseDate[$item_property['tool']] < $item_property['lastedit_date'])
                && (in_array($item_property['to_group_id'], $group_ids)
                || $item_property['to_user_id'] == $user_id)
                && ($item_property['visibility'] == '1'
                || ($my_course['s'] == '1' && $item_property['visibility'] == '0')
                || !isset($item_property['visibility']))) {

            if ($item_property['tool'] == 'calendar_event') {
                continue;
            }
            $notifications[$item_property['tool']] = $item_property;
        }
    }
    //show all tool icons where there is something new
    $retvalue = '&nbsp;';
    if (isset($notifications)) {
        while (list ($key, $notification) = each($notifications)) {
            $lastDate = date('d/m/Y H:i', convert_mysql_date($notification['lastedit_date']));
            $type = $notification['lastedit_type'];

            if (empty($my_course['id_session'])) {
                $my_course['id_session'] = 0;
            }
            //check if there is a question mark
            $link = strrpos($notification['link'], "?") !== FALSE ? $notification['link'] . '&amp;cidReq=' . $my_course['k'] : $notification['link'] . '?cidReq=' . $my_course['k'];
            $link = str_replace('blog_admin.php', 'blog.php', $link);
            $actionicon = 'actionplaceholdericon actionmini_' . $notification['tool'];
            $retvalue .= '<a href="' . api_get_path(WEB_CODE_PATH) . $link . '&amp;ref=' . $notification['ref'] . '&amp;gidReq=' . $notification['to_group_id'] . '&amp;id_session=' . $my_course['id_session'] . '">' . Display::return_icon('pixel.gif', get_lang(ucfirst($notification['tool'])) . ' -- ' . get_lang('_title_notification') . ': ' . get_lang($type) . ' (' . $lastDate . ')', array('class' => $actionicon)) . '</a>&nbsp;';
        }
    }
    return $retvalue;
}

/**
 * retrieves the user defined course categories
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return array containing all the titles of the user defined courses with the id as key of the array
*/
function get_user_course_categories() {
	global $_user;
	$output = array();
	$output[0] = api_get_setting('default_category_course');
	$table_category = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
	$sql = "SELECT * FROM ".$table_category." WHERE user_id='".Database::escape_string($_user['user_id'])."' ORDER BY sort ASC";
	$result = Database::query($sql,__FILE__,__LINE__);
	while ($row = Database::fetch_array($result)) {
		$output[$row['id']] = $row['title'];
	}
	return $output;
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/
/*
==============================================================================
		PERSONAL COURSE LIST
==============================================================================
*/
if (!isset ($maxValvas)) {
	$maxValvas = CONFVAL_maxValvasByCourse; // Maximum number of entries
}
if (!isset ($maxAgenda)) {
	$maxAgenda = CONFVAL_maxAgendaByCourse; // collected from each course
}
if (!isset ($maxCourse)) {
	$maxCourse = CONFVAL_maxTotalByCourse; // and displayed in summary.
}
$maxValvas = (int) $maxValvas;
$maxAgenda = (int) $maxAgenda;
$maxCourse = (int) $maxCourse; // 0 if invalid
if ($maxCourse > 0) {
	unset ($allentries); // we shall collect all summary$key1 entries in here:
	$toolsList['agenda']['name'] = get_lang('Agenda');
	$toolsList['agenda']['path'] = api_get_path(WEB_CODE_PATH)."calendar/agenda.php?cidReq=";
	$toolsList['valvas']['name'] = get_lang('Valvas');
	$toolsList['valvas']['path'] = api_get_path(WEB_CODE_PATH)."announcements/announcements.php?cidReq=";
}

echo '	<div id="content" class="maxcontent">'; // start of content for logged in users
// Plugins for the my courses main area
api_plugin('mycourses_main');

/*
-----------------------------------------------------------------------------
	System Announcements
-----------------------------------------------------------------------------
*/
/*$announcement = isset($_GET['announcement']) ? $_GET['announcement'] : -1;
$visibility = api_is_allowed_to_create_course() ? VISIBLE_TEACHER : VISIBLE_STUDENT;
SystemAnnouncementManager :: display_announcements($visibility, $announcement);*/

if (!empty ($_GET['include']) && preg_match('/^[a-zA-Z0-9_-]*\.html$/',$_GET['include'])) {
	include ('./home/'.$_GET['include']);
	$pageIncluded = true;
} else {
	/*--------------------------------------
	              DISPLAY COURSES
	   --------------------------------------*/
	// compose a structured array of session categories, sessions and courses
	// for the current user
	require_once $libpath.'sessionmanager.lib.php';

	if (isset($_GET['history']) && intval($_GET['history']) == 1) {
		$courses_tree = UserManager::get_sessions_by_category($_user['user_id'],true,true);
	} else {
		$courses_tree = UserManager::get_sessions_by_category($_user['user_id'],true);
	}
	foreach ($courses_tree as $cat => $sessions) {
		$courses_tree[$cat]['details'] = SessionManager::get_session_category($cat);
		if ($cat == 0) {
			$courses_tree[$cat]['courses'] = CourseManager::get_courses_list_by_user_id($_user['user_id'],false);
		}
		$courses_tree[$cat]['sessions'] = array_flip(array_flip($sessions));
		if (count($courses_tree[$cat]['sessions'])>0) {
			foreach ($courses_tree[$cat]['sessions'] as $k => $s_id) {
				$courses_tree[$cat]['sessions'][$k] = array('details' => SessionManager::fetch($s_id));
				$courses_tree[$cat]['sessions'][$k]['courses'] = UserManager::get_courses_list_by_session($_user['user_id'],$s_id);
			}
		}
	}

	$list = '';

	// this is the main function to get the course list
	$personal_course_list = UserManager::get_personal_session_course_list($_user['user_id']);
	foreach ($personal_course_list as $my_course) {
		$thisCourseDbName = $my_course['db'];
		$thisCourseSysCode = $my_course['k'];
		$thisCoursePublicCode = $my_course['c'];
		$thisCoursePath = $my_course['d'];
		$sys_course_path = api_get_path(SYS_COURSE_PATH);
		$dbname = $my_course['k'];
		$status[$dbname] = $my_course['s'];

		$nbDigestEntries = 0; // number of entries already collected
		if ($maxCourse < $maxValvas) {
			$maxValvas = $maxCourse;
		}
		if ($maxCourse > 0) {
			$courses[$thisCourseSysCode]['coursePath'] = $thisCoursePath;
			$courses[$thisCourseSysCode]['courseCode'] = $thisCoursePublicCode;
		}
		/*
		-----------------------------------------------------------
			Announcements
		-----------------------------------------------------------
		*/
		$course_database = $my_course['db'];
		$course_tool_table = Database::get_course_table(TABLE_TOOL_LIST, $course_database);
		$query = "SELECT visibility FROM $course_tool_table WHERE link = 'announcements/announcements.php' AND visibility = 1";
		$result = Database::query($query);
		// collect from announcements, but only if tool is visible for the course
		if ($result && $maxValvas > 0 && Database::num_rows($result) > 0) {
			//Search announcements table
			//Take the entries listed at the top of advalvas/announcements tool
			$course_announcement_table = Database::get_course_table(TABLE_ANNOUNCEMENT);
			$sqlGetLastAnnouncements = "SELECT end_date publicationDate, content
											FROM ".$course_announcement_table;
			switch (CONFVAL_limitPreviewTo) {
				case SCRIPTVAL_NewEntriesOfTheDay :
					$sqlGetLastAnnouncements .= "WHERE DATE_FORMAT(end_date,'%Y %m %d') >= '".date("Y m d")."'";
					break;
				case SCRIPTVAL_NoTimeLimit :
					break;
				case SCRIPTVAL_NewEntriesOfTheDayOfLastLogin :
					// take care mysql -> DATE_FORMAT(time,format) php -> date(format,date)
					$sqlGetLastAnnouncements .= "WHERE DATE_FORMAT(end_date,'%Y %m %d') >= '".date("Y m d", $_user["lastLogin"])."'";
			}
			$sqlGetLastAnnouncements .= "ORDER BY end_date DESC LIMIT ".$maxValvas;
			$resGetLastAnnouncements = Database::query($sqlGetLastAnnouncements, __FILE__, __LINE__);
			if ($resGetLastAnnouncements) {
				while ($annoncement = Database::fetch_array($resGetLastAnnouncements)) {
					$keyTools = 'valvas';
					$keyTime = $annoncement['publicationDate'];
					$keyCourse = $thisCourseSysCode;
					$digest[$$orderKey[0]][$$orderKey[1]][$$orderKey[2]][] = htmlspecialchars(api_substr(strip_tags($annoncement["content"]), 0, CONFVAL_NB_CHAR_FROM_CONTENT), ENT_QUOTES, $charset);
					$nbDigestEntries ++; // summary has same order as advalvas
				}
			}
		}
		/*
		-----------------------------------------------------------
			Agenda
		-----------------------------------------------------------
		*/
		$course_database = $my_course['db'];
		$course_tool_table = Database :: get_course_table(TABLE_TOOL_LIST,$course_database);
		$query = "SELECT visibility FROM $course_tool_table WHERE link = 'calendar/agenda.php' AND visibility = 1";
		$result = Database::query($query);
		$thisAgenda = $maxCourse - $nbDigestEntries; // new max entries for agenda
		if ($maxAgenda < $thisAgenda) {
			$thisAgenda = $maxAgenda;
		}
		// collect from agenda, but only if tool is visible for the course
		if ($result && $thisAgenda > 0 && Database::num_rows($result) > 0) {
			$tableCal = $courseTablePrefix.$thisCourseDbName.$_configuration['db_glue']."calendar_event";
			$sqlGetNextAgendaEvent = "SELECT start_date, title content, start_time
											FROM $tableCal
											WHERE start_date >= CURDATE()
											ORDER BY start_date, start_time
											LIMIT $maxAgenda";
			$resGetNextAgendaEvent = Database::query($sqlGetNextAgendaEvent, __FILE__, __LINE__);
			if ($resGetNextAgendaEvent) {
				while ($agendaEvent = Database::fetch_array($resGetNextAgendaEvent)) {
					$keyTools = 'agenda';
					$keyTime = $agendaEvent['start_date'];
					$keyCourse = $thisCourseSysCode;
					$digest[$$orderKey[0]][$$orderKey[1]][$$orderKey[2]][] = htmlspecialchars(api_substr(strip_tags($agendaEvent["content"]), 0, CONFVAL_NB_CHAR_FROM_CONTENT), ENT_QUOTES, $charset);
					$nbDigestEntries ++; // summary has same order as advalvas
				}
			}
		}
		/*
		-----------------------------------------------------------
			Digest Display
			take collected data and display it
		-----------------------------------------------------------
		*/
		//$list[] = get_logged_user_course_html($my_course);
	} //end while mycourse...
}

echo '<div id="content_with_menu">';
if (isset($_GET['history']) && intval($_GET['history']) == 1) {
	echo '<h3>'.get_lang('HistoryTrainingSession').'</h3>';
}

//if (api_get_setting('catalogue_category_session') == 'true') {
    $tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
    $tbl_session_cat_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_USER);
    $tbl_session_rel_category = Database::get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);
    $tbl_session_rel_course   = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
    $tbl_session_rel_course_user   = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

    // get category users
	$sql = "SELECT DISTINCT(uc.category_id), c.name, c.date_start, c.date_end FROM $tbl_session_cat_rel_user uc INNER JOIN $tbl_session_category c ON  uc.category_id = c.id  WHERE uc.user_id=".api_get_user_id();
    $rs_cat_users = Database::query($sql);
    if (Database::num_rows($rs_cat_users)) {
        while ($row_cat_users = Database::fetch_array($rs_cat_users)) {
			$date_start = $row_cat_users['date_start'];
			$date_end = $row_cat_users['date_end'];

			$today = strtotime("now");
			$start_date = strtotime($date_start);
			$end_date = strtotime($date_end);

			$datetime = explode(" ", $date_start);
			$dateparts = explode("-", $datetime[0]);
			$date_start = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0];

			$datetime = explode(" ", $date_end);
			$dateparts = explode("-", $datetime[0]);
			$date_end = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0];

            echo '<div class="catalogue-session-category" >';
            echo '<h2>'.$row_cat_users['name'].'</h2>';
			if(($start_date > $today) || ($end_date < $today)){
				echo get_lang('Programme').' '.get_lang('AvailableFrom').' '.$date_start.' '.get_lang('To').' '.$date_end;
				$programme_status = 'N';
			}
		//	$ids_session = array();
            // session in the category
            $rs_sess = Database::query("SELECT DISTINCT(session_id) FROM $tbl_session_rel_category WHERE category_id = {$row_cat_users['category_id']}");
            if (Database::num_rows($rs_sess)) {
                while ($row_sess = Database::fetch_array($rs_sess, ASSOC)) {
                    //$rs_sess_cat = Database::query("SELECT course_code FROM $tbl_session_rel_course_user WHERE id_session = {$row_sess['session_id']} AND id_user = ".api_get_user_id());
                    $rs_sess_cat = Database::query("SELECT course_code FROM $tbl_session_rel_course_user WHERE id_session = {$row_sess['session_id']} AND id_user = ".api_get_user_id()." AND course_code IN(SELECT course_code FROM $tbl_session_cat_rel_user WHERE session_id = {$row_sess['session_id']} AND user_id = ".api_get_user_id()." AND category_id = {$row_cat_users['category_id']})");
                    if (Database::num_rows($rs_sess_cat)) {
                       echo '<div class="session-category-courses">';
							while ($row_sess_cat = Database::fetch_object($rs_sess_cat)) {
								// if user in course session
								$my_course = api_get_course_info($row_sess_cat->course_code);
								$my_course['k']  = $my_course['id'];
								$my_course['db'] = $my_course['dbName'];
								$my_course['c']  = $my_course['official_code'];
								$my_course['i']  = $my_course['name'];
								$my_course['d']  = $my_course['path'];
								$my_course['t']  = $my_course['titular'];
								$my_course['id_session'] = $row_sess['session_id'];
								echo '<div class="session-category-course-item">'.Display::return_icon('miscellaneous22x22.png', $my_course['name'], array('style'=>'vertical-align:middle')).' <strong>';
								if($programme_status == 'N'){
								echo $my_course['name'].'</strong>';
								}
								else {
								echo '<a href="'.api_get_path(WEB_COURSE_PATH).$my_course['path'].'/?id_session='.$my_course['id_session'].'">';
								echo $my_course['name'].'</a></strong>'.show_notification($my_course);
								}
								echo '</div>';
							}
                       echo '</div>';
                    }
                }
            }
            echo '</div>';
        }
    }
 else {

if ( is_array($courses_tree) ) {

	foreach ($courses_tree as $key => $category) {


		if ($key == 0) {

		// sessions and courses that are not in a session category
                if (!isset($_GET['history']) && count($category['courses']) > 0) { // check if it's not history trainnign session list
                    echo '<ul class="courseslist">';
                    foreach ($category['courses'] as $course) {
                        $c = get_logged_user_course_html($course, 0, 'independent_course_item');
                        echo '<li>' . $c[1] . '<div class="clear"></div></li>';
                    }
                    echo '</ul>';
                }

			//independent sessions
		foreach ($category['sessions'] as $session) {
                    //don't show empty sessions
                    if (count($session['courses'])<1) {
                            continue;
                    }
                    $s = get_session_title_box($session['details']['id']);
                    echo '<div class="catalogue-session-category">';
                    echo '<h2>'.$s['title'].'</h2>';
                    echo '<div class="session-category-courses">';
                    // check if user is allowed to get certificate
                    $obj_certificate = new CertificateManager();
                    $certif_available = $obj_certificate->isUserAllowedGetCertificate(api_get_user_id(), 'session', $session['details']['id']);
                        if ($certif_available) {
                            echo '<a href="#" class="certificate-'.$session['details']['id'].'-link" >'.Display::return_icon('certificate48x48.png', get_lang('GetCertificate'), array('style'=>'position:absolute;top:15px;right:10px;')).'</a>';
                            $obj_certificate->displayCertificate('html', 'session', $session['details']['id'], null, null, true);
                        }
                        foreach ($session['courses'] as $course) {
                            $c = get_logged_user_course_html($course, $session['details']['id'], 'session_course_item');
                            echo '<div class="session-category-course-item">'.$c[1].'</div>';
                        }
                        echo '	</div>';
                        echo '</div>';
                    }

		} else {

			// all sessions included in
			if (!empty($category['details'])) {

					//changed like as we have for DILA
					echo '<div class="catalogue-session-category" >';
					echo '<h2>'.$category['details']['name'].'</h2>';
					foreach ($category['sessions'] as $session) {
					if (count($session['courses'])<1) { continue; }
					echo '<div class="session-category-courses">';
                                        echo '<div class="home-session-name">'.$session['details']['name'].'</div>';
					foreach ($session['courses'] as $course) {
						$c = get_logged_user_course_html($course, $session['details']['id'], 'session_course_item');
						//var_dump($c);
						echo $c[1];
					}
					echo '</div>';
					}
					echo '</div>';

                           //else {
                            /*    echo '<div class="session_category" id="session_category_'.$category['details']['id'].'" style="background-color:#fbfbfb; border:1px solid #dddddd; padding:5px; margin-top: 10px;">';
                                echo '<div class="session_category_title_box" id="session_category_title_box_'.$category['details']['id'].'" style="font-size:larger; color: #555555;">'. Display::return_icon('div_show.gif', get_lang('Expand'), array('align' => 'middle', 'id' => 'category_img_'.$category['details']['id'])) . ' ' . get_lang('SessionCategory') . ': ' . $category['details']['name'].'  -  '.get_lang('From').' '.$category['details']['date_start'].' '.get_lang('Until').' '.$category['details']['date_end'].'</div>';

                                foreach ($category['sessions'] as $session) {
                                        //don't show empty sessions
                                        if (count($session['courses'])<1) { continue; }
                                        echo '<ul class="session_box" id="session_'.$session['details']['id'].'">';
                                        echo '<li class="session_box_title" id="session_'.$session['details']['id'].'">';
                                        echo Display::return_icon('div_show.gif', get_lang('Expand'), array('align' => 'middle', 'id' => 'session_img_'.$session['details']['id'])) . ' ';
                                        $s = get_session_title_box($session['details']['id']);
                                        echo get_lang('SessionName') . ': ' . $s['title']. ' - '.(!empty($s['coach'])?$s['coach'].' - ':'').$s['dates'];
                                        echo '</li>';

                                        foreach ($session['courses'] as $course) {
                                                //echo '<li class="session_course_item" id="session_course_item_'.$course['code'].'" style="padding:5px">';
                                                $c = get_logged_user_course_html($course, $session['details']['id'], 'session_course_item');
                                                //var_dump($c);
                                                echo $c[1];
                                                //echo $course['code'];
                                                //echo '</li>';
                                        }
                                        echo '</ul>';
                                }
                                echo '</div>';*/
                                //}
                            //}
			} // end condition outstanding
		}
	}
    }

}

echo '</div>';

/*
if ( is_array($list) ) {
	//Courses whithout sessions
	$old_user_category = 0;
	foreach ($list as $key => $value) {
		if (empty($value[2])) { //if out of any session

			$userdefined_categories = get_user_course_categories();
			echo '<ul class="courseslist">';

			if ($old_user_category<>$value[0]) {
				if ($key <> 0 || $value[0] <> 0) {// there are courses in the previous category
					echo "\n</ul>";
				}
				echo "\n\n\t<ul class=\"user_course_category\"><li>".$userdefined_categories[$value[0]]."</li></ul>\n";
				if ($key<>0 OR $value[0]<>0){ // there are courses in the previous category
					echo "<ul class=\"courseslist\">";
				}
				$old_user_category = $value[0];

			}
			echo $value[1];
			echo "</ul>\n";
		}
	}

	$listActives = $listInactives = $listCourses = array();
	foreach ($list as $key => $value) {
		if ($value['active']) { //if the session is still active (as told by get_logged_user_course_html())
			$listActives[] = $value;
		} else if (!empty($value[2])) { //if there is a session but it is not active
			$listInactives[] = $value;
		}
	}
	$old_user_category = 0;
	$userdefined_categories = get_user_course_categories();

	if (count($listActives) > 0 && $display_actives) {
		echo "<ul class=\"courseslist\">\n";

		$name_category = array();
		$i = 0;
		$j=0;
		foreach ($listActives as $key => $value) {
			$session_category_id=$value['session_category_id'];
			if (!empty($value[3]['category'])) {
				if (!in_array($value[3]['category'], $name_category)) {

					if ($key != 0) {
						echo '</ul>';
					}
					//Category
					$name_category['name'] = $value[3]['category'];
					echo '<ul class="category_box" id="category_box_'.$session_category_id.'">' .
							'<li class="category_box_title" id="category_box_title_'.$session_category_id.'">'.$name_category['name'].'</li>';
					echo "</ul>\n";
				}

			}

			if (!empty($value[2])) {
				if ((isset($old_session) && $old_session != $value[2]) or ((!isset($old_session)) && isset($value[2]))) {
					$old_session = $value[2];
					if ($key != 0) {
						echo '</ul>';
					}
					//Session

					echo '<ul style="display:none" class="session_box_'.$session_category_id.'"  >' .
							'<li class="session_box_title" >'.$value[3]['title'].' '.$value[3]['dates'].'</li>';
					if ( !empty($value[3]['coach']) ) {
						echo '<li class="session_box_coach">'.$value[3]['coach'].'</li>';
					}
					echo "</ul>\n";


					echo '<ul  class="session_course_item" id="session_course_item_'.$i.'">';

				}
			}
			//Courses
			echo $value[1];
			$i++;
		}

		echo "\n</ul><br /><br />\n";

	}

	if (count($listInactives) > 0 && !$display_actives) {
		echo '<ul class="sessions_list_inactive">';
		foreach ($listInactives as $key => $value) {
			if (!empty($value[2])) {
				if ($old_session != $value[2]) {
					$old_session = $value[2];
					if ($key != 0) {
						echo '</ul>';
					}
					echo '<ul class="session_box">' .
							'<li class="session_box_title">'.$value[3]['title'].' '.$value[3]['dates'].'</li>';
					if (!empty($value[3]['coach'])) {
						echo '<li class="session_box_coach">'.$value[3]['coach'].'</li>';
					}
					echo "</ul>\n";
					echo '<ul>';
				}
			}
			echo $value[1];
		}
		echo "\n</ul><br /><br />\n";
	}
} */


// Register whether full admin or null admin course
// by course through an array dbname x user status
api_session_register('status');

/*
==============================================================================
		RIGHT MENU
==============================================================================
*/
echo '	<div class="menu" id="menu">';

$show_menu = false;
$show_course_link = false;
$show_digest_link = false;

$display_add_course_link = api_is_allowed_to_create_course() && ($_SESSION['studentview'] != 'studentenview');
if ($display_add_course_link) {
    $show_menu = true;
}

if (api_is_platform_admin() || api_is_course_admin() || api_is_allowed_to_create_course()) {
	$show_course_link = true;
} else {
	if (api_get_setting('allow_students_to_browse_courses')=='true') {
		$show_course_link = true;
	}
}

if (isset($toolsList) and is_array($toolsList) and isset($digest)) {
	$show_digest_link = true;
	$show_menu = true;
}

// My account section
if ($show_menu) {
	echo '<div class="section">';
	echo '<div class="sectiontitle">'.get_lang('MenuUser').'</div>';
	echo '<div class="sectioncontent">';
	//user image
	/*	@todo add a platform setting to add the user image
	$img_array= UserManager::get_user_picture_path_by_id(api_get_user_id(),'web',true,true);
	$img_array = UserManager::get_picture_user(api_get_user_id(), $img_array['file'], 92, 'medium_', ' width="90" height="90" ');
	echo '<div id="picture" style="">';
		echo '<a href="/main/auth/profile.php"><img src="'.$img_array['file'].'" '.$img_array['style'].' border="1"></a>';
	echo '</div><br />';
	*/
	//@todo add the Inbox, pending invitations, etc...
	//echo get_lang('Inbox');



	echo '<ul class="menulist nobullets">';
        display_create_course_link();
        display_trainer_links();
	if ($show_digest_link) {
		display_digest($toolsList, $digest, $orderKey, $courses);
	}
	echo '</ul>';
	echo '</div>';
	echo '</div>';
} else {
    echo '<script type="text/javascript">document.getElementById("content_with_menu").style.width = "930px";</script>';
}

// Main navigation section
// tabs that are deactivated are added here
if (!empty($menu_navigation)) {
	echo '<div class="section">';
	echo '<div class="sectiontitle">'.get_lang('MainNavigation').'</div>';
	echo '<div class="sectioncontent">';
	echo '<ul class="menulist nobullets">';
	foreach ($menu_navigation as $section => $navigation_info) {
		$current = $section == $GLOBALS['this_section'] ? ' id="current"' : '';
		echo '<li'.$current.'>';
		echo '<a href="'.$navigation_info['url'].'" target="_self">'.$navigation_info['title'].'</a>';
		echo '</li>';
		echo "\n";
	}
	echo '</ul>';
	echo '</div>';
	echo '</div>';
}

// plugins for the my courses menu
if (isset($_plugins['mycourses_menu']) && is_array($_plugins['mycourses_menu'])) {
	echo '<div class="note">';
	api_plugin('mycourses_menu');
	echo '</div>';
}

if (api_get_setting('allow_reservation') == 'true' && api_is_allowed_to_create_course() ){
	//include_once('main/reservation/rsys.php');
	echo '<div class="section">';
	echo '<div class="sectiontitle">'.get_lang('Booking').'</div>';
	echo '<div class="sectioncontent">';
	echo '<ul class="menulist nobullets">';
	echo '<a href="main/reservation/reservation.php">'.get_lang('ManageReservations').'</a><br />';
	//echo '<a href="main/reservation/reservation.php">'.get_lang('ManageReservations').'</a><br />';

	/*require_once('main/reservation/rsys.php');
	if(api_is_platform_admin() || Rsys :: check_user_status() == 1) { // Only for admins & teachers...
		echo '<a href="main/reservation/m_item.php">'.get_lang('ManageItems').'</a><br />';
		echo '<a href="main/reservation/m_reservation.php">'.get_lang('ManageReservationPeriods').'</a><br />';
	}
	*/
	echo '</ul>';
	echo '</div>';
	echo '</div>';
}

// search textbox
/* do no work
if (api_get_setting('search_enabled') == 'true') {
	echo '<div class="searchbox">';
	$search_btn = get_lang('Search');
	$search_text_default = get_lang('YourTextHere');
echo <<<EOD
<br />
<form action="main/search/" method="post">
&nbsp;&nbsp;<input type="text" id="query" size="15" name="query" value="" />
&nbsp;&nbsp;<button class="save" type="submit" name="submit" value="$search_btn"/>$search_btn </button>
</form>
EOD;
	echo '</div>';
}*/

echo '</div>'; // end of menu

echo '</div>'; // end of content section

Display :: display_footer();
