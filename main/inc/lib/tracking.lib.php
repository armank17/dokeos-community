<?php
// $Id: tracking.lib.php 2007-28-02 15:51:53
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) various contributors

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
	Mail: info@dokeos.com

==============================================================================
*/
/**
==============================================================================
*	This is the tracking library for Dokeos.
*	Include/require it in your code to use its functionality.
*
*	@package dokeos.library
*	@author Julio Montoya <gugli100@gmail.com> (Score average fixes)
==============================================================================
*/

class Tracking {

	/**
	 * Calculates the time spent on the platform by a user
	 * @param integer $user_id the user id
	 * @return timestamp $nb_seconds
	 */
	function get_time_spent_on_the_platform($user_id) {

		$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);

		$sql = 'SELECT login_date, logout_date FROM ' . $tbl_track_login . '
						WHERE login_user_id = ' . intval($user_id);

		$rs = Database::query($sql,__FILE__,__LINE__);

		$nb_seconds = 0;

		$wrong_logout_dates = false;

		while ($a_connections = Database::fetch_array($rs)) {

			$s_login_date = $a_connections["login_date"];
			$s_logout_date = $a_connections["logout_date"];

			$i_timestamp_login_date = strtotime($s_login_date);
			$i_timestamp_logout_date = strtotime($s_logout_date);

			if($i_timestamp_logout_date>0)
			{
				$nb_seconds += ($i_timestamp_logout_date - $i_timestamp_login_date);
			}
			else
			{ // there are wrong datas in db, then we can't give a wrong time
				$wrong_logout_dates = true;
			}

		}

		if($nb_seconds>0 || !$wrong_logout_dates)
		{
			return $nb_seconds;
		}
		else
		{
			return -1; //-1 means we have wrong datas in the db
		}
	}

	/**
	 * Calculates the time spent on the course
	 * @param integer $user_id the user id
	 * @param string $course_code the course code
	 * @return timestamp $nb_seconds
	 */
	function get_time_spent_on_the_course($user_id, $course_code) {
		// protect datas
		$user_id = intval($user_id);
		$course_code = addslashes($course_code);
		$tbl_track_course = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
		$condition_user = "";
		if (is_array($user_id)) {
			$condition_user = " AND user_id IN (".implode(',',$user_id).") ";
		} else {
			$condition_user = " AND user_id = '$user_id' ";
		}
		$sql = " SELECT SUM(UNIX_TIMESTAMP(logout_course_date)-UNIX_TIMESTAMP(login_course_date)) as nb_seconds
				FROM $tbl_track_course
				WHERE course_code='$course_code' $condition_user";
		$rs = Database::query($sql,__FILE__,__LINE__);
		$row = Database::fetch_array($rs);
		return $row['nb_seconds'];
	}

	function get_first_connection_date($student_id) {
		$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
		$sql = 'SELECT login_date FROM ' . $tbl_track_login . '
						WHERE login_user_id = ' . intval($student_id) . '
						ORDER BY login_date ASC LIMIT 0,1';

		$rs = Database::query($sql,__FILE__,__LINE__);
		if(Database::num_rows($rs)>0)
		{
			if ($first_login_date = Database::result($rs, 0, 0)) {
				return format_locale_date(get_lang('DateFormatLongWithoutDay'), strtotime($first_login_date));
			}
		}
		return false;
	}

	function get_last_connection_date($student_id, $warning_message = false, $return_timestamp = false) {
		$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
		$sql = 'SELECT login_date FROM ' . $tbl_track_login . '
						WHERE login_user_id = ' . intval($student_id) . '
						ORDER BY login_date DESC LIMIT 0,1';
		$rs = Database::query($sql,__FILE__,__LINE__);
		if(Database::num_rows($rs)>0)
		{
			if ($last_login_date = Database::result($rs, 0, 0))
			{
				if ($return_timestamp)
				{
					return strtotime($last_login_date);
				}
				else
				{
					if (!$warning_message)
					{
						return format_locale_date(get_lang('DateFormatLongWithoutDay'), strtotime($last_login_date));
					}
					else
					{
						$timestamp = strtotime($last_login_date);
						$currentTimestamp = mktime();

						//If the last connection is > than 7 days, the text is red
						//345600 = 7 days in seconds
						if ($currentTimestamp - $timestamp > 604800)
						{
							return '<span style="color: #F00;">' . format_locale_date(get_lang('DateFormatLongWithoutDay'), strtotime($last_login_date)) . '</span>';
						}
						else
						{
							return format_locale_date(get_lang('DateFormatLongWithoutDay'), strtotime($last_login_date));
						}
					}
				}
			}
		}
		return false;
	}

	function get_first_connection_date_on_the_course($student_id, $course_code) {
		$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
		$sql = 'SELECT login_course_date FROM ' . $tbl_track_login . '
						WHERE user_id = ' . intval($student_id) . '
						AND course_code = "' . Database::escape_string($course_code) . '"
						ORDER BY login_course_date ASC LIMIT 0,1';

		$rs = Database::query($sql,__FILE__,__LINE__);
		if(Database::num_rows($rs)>0)
		{
			if ($first_login_date = Database::result($rs, 0, 0)) {
				return format_locale_date(get_lang('dateFormatShortWithLongYear'), strtotime($first_login_date));
			}
		}
		return false;
	}

	function get_last_connection_date_on_the_course($student_id, $course_code) {
		$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
		$sql = 'SELECT login_course_date FROM ' . $tbl_track_login . '
						WHERE user_id = ' . intval($student_id) . '
						AND course_code = "' . Database::escape_string($course_code) . '"
						ORDER BY login_course_date DESC LIMIT 0,1';

		$rs = Database::query($sql,__FILE__,__LINE__);
		if(Database::num_rows($rs)>0)
		{
			if ($last_login_date = Database::result($rs, 0, 0)) {
				$timestamp = strtotime($last_login_date);
				$currentTimestamp = mktime();
				//If the last connection is > than 7 days, the text is red
				//345600 = 7 days in seconds
				if ($currentTimestamp - $timestamp > 604800) {
					return format_locale_date(get_lang('dateFormatShortWithLongYear'), strtotime($last_login_date)) . (api_is_allowed_to_edit()?' <a href="'.api_get_path(WEB_VIEW_PATH).'announcement/index.php?cidReq='.$course_code.'&action=add&amp;remind_inactive='.$student_id.'" title="'.get_lang('RemindInactiveUser').'"><img align="middle" title="&nbsp;" alt="&nbsp;" src="'.api_get_path(WEB_IMG_PATH).'messagebox_warning.gif" /></a>':'');
				} else {
					return format_locale_date(get_lang('dateFormatShortWithLongYear'), strtotime($last_login_date));
				}
			}
		}
		return false;
	}

	function count_course_per_student($user_id) {

		$user_id = intval($user_id);
		$tbl_course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$tbl_session_course_rel_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

		$sql = 'SELECT DISTINCT course_code
						FROM ' . $tbl_course_rel_user . '
						WHERE user_id = ' . $user_id;
		$rs = Database::query($sql, __FILE__, __LINE__);
		$nb_courses = Database::num_rows($rs);

		$sql = 'SELECT DISTINCT course_code
						FROM ' . $tbl_session_course_rel_user . '
						WHERE id_user = ' . $user_id;
		$rs = Database::query($sql, __FILE__, __LINE__);
		$nb_courses += Database::num_rows($rs);

		return $nb_courses;
	}

	/**
	 * This function gets the score average from all tests in a course by student
	 * @param int $student_id - or array for multiples User id (array(0=>1,1=>2))
	 * @param string $course_code - Course id
	 * @return string value (number %) Which represents a round integer about the score average.
	 */
	function get_avg_student_exercise_score($student_id, $course_code) {

		// protect datas
		$course_code = Database::escape_string($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		if(!empty($a_course['db_name'])) {
			// table definition
			$tbl_course_quiz = Database::get_course_table(TABLE_QUIZ_TEST,$a_course['db_name']);
			$tbl_stats_exercise = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
			$count_quiz = Database::fetch_row(Database::query("SELECT count(id) FROM $tbl_course_quiz WHERE active <> -1",__FILE__,__LINE__));
			$quiz_avg_total_score = 0;
			if (!empty($count_quiz[0]) && !empty($student_id)) {
				$condition_user = "";
				if (is_array($student_id)) {
					$condition_user = " AND exe_user_id IN (".implode(',',$student_id).") ";
				} else {
					$condition_user = " AND exe_user_id = '$student_id' ";
				}
				$sql = "SELECT SUM(exe_result/exe_weighting*100) as avg_score
						FROM $tbl_stats_exercise
						WHERE exe_exo_id IN (SELECT id FROM $tbl_course_quiz WHERE active <> -1)
						$condition_user
						AND orig_lp_id = 0
						AND exe_cours_id = '$course_code'
						AND orig_lp_item_id = 0
						ORDER BY exe_date DESC";
				$res = Database::query($sql, __FILE__, __LINE__);
				$row = Database::fetch_array($res);
				$quiz_avg_score = 0;
				if (!empty($row['avg_score'])) {
					$quiz_avg_score = round($row['avg_score'],2);
				}
				$count_attempt = Database::fetch_row(Database::query("SELECT count(*) FROM $tbl_stats_exercise WHERE exe_exo_id IN (SELECT id FROM $tbl_course_quiz WHERE active <> -1) $condition_user AND orig_lp_id = 0 AND exe_cours_id = '$course_code' AND orig_lp_item_id = 0 ORDER BY exe_date DESC",__FILE__,__LINE__));
				if(!empty($count_attempt[0])) {
					$quiz_avg_score = $quiz_avg_score / $count_attempt[0];
		        }
		        $quiz_avg_total_score = $quiz_avg_score;
				return $quiz_avg_total_score/$count_quiz[0];
			}
		}
		return null;
	}

	/*function get_avg_student_progress($student_id, $course_code) {
		// protect datas
		$course_code = addslashes($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		if (!empty($a_course['db_name'])) {
			// table definition
			$tbl_course_lp_view = Database :: get_course_table(TABLE_LP_VIEW, $a_course['db_name']);
			$tbl_course_lp = Database :: get_course_table(TABLE_LP_MAIN, $a_course['db_name']);
			$count_lp = Database::fetch_row(Database::query("SELECT count(id) FROM $tbl_course_lp",__FILE__,__LINE__));
			$avg_progress = 0;
			if (!empty($count_lp[0]) && !empty($student_id)) {
				$condition_user = "";
				if (is_array($student_id)) {
					$condition_user = " lp_view.user_id IN (".implode(',',$student_id).") AND ";
				} else {
					$condition_user = " lp_view.user_id = '$student_id' AND ";
				}
				$sqlProgress = "SELECT SUM(progress) FROM $tbl_course_lp_view AS lp_view WHERE $condition_user lp_view.lp_id IN (SELECT id FROM $tbl_course_lp)";
				$resultItem  = Database::query($sqlProgress, __FILE__, __LINE__);
				$rowItem = Database::fetch_row($resultItem);
				$avg_progress = round($rowItem[0] / $count_lp[0], 1);
				return $avg_progress;
			}
		}
		return null;
	}*/

        /**
         * Returns the average student progress in the learning paths of the given course.
         * 
         * @param   int/array    Student id(s)
         * @param   string       Course code
         * @param   array        Limit average to listed lp ids
         * @param   int          Session id (optional), if parameter $session_id is null(default) it'll return results including sessions, 0 = session is not filtered
         * @param   bool         Will return an array of the type: [sum_of_progresses, number] if it is set to true
         * @return  double       Average progress of the user in this course
         */
        public static function get_avg_student_progress($student_id, $course_code, $lp_ids = array(), $session_id = 0, $return_array = false) {
            require_once api_get_path(LIBRARY_PATH).'course.lib.php';
            // get the informations of the course
            $a_course = CourseManager :: get_course_information($course_code);
            $result = false;
            if (!empty($a_course)) {
                $course_info = api_get_course_info($course_code);       
                $tbl_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);
                $tbl_lp_view = Database :: get_course_table(TABLE_LP_VIEW, $course_info['dbName']);
                $tbl_lp_item_view = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $course_info['dbName']);
                $tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM, $course_info['dbName']);
                            
                // get the modules
                $rs_modules = Database::query("SELECT id, name FROM $tbl_lp WHERE session_id IN (0, $session_id)");
                $nb_modules = Database::num_rows($rs_modules);
                $avg_progress = $total_views = 0;                   
                if (Database::num_rows($rs_modules) > 0) {
                    while ($row_modules = Database::fetch_object($rs_modules)) {
                        $rs_views = Database::query("SELECT id, user_id FROM $tbl_lp_view WHERE lp_id = {$row_modules->id} AND session_id = $session_id AND user_id = $student_id");
                        $sum_progress = 0;
                        // get module items
                        $rs_items = Database::query("SELECT count(id) FROM $tbl_lp_item WHERE lp_id = {$row_modules->id}");
                        $row_items = Database::fetch_row($rs_items);
                        $total_items = intval($row_items[0]);
                        $nb_views = Database::num_rows($rs_views);
                        
                        if ($nb_views > 0) {
                            while ($row_views = Database::fetch_object($rs_views)) {
                                $rs_completed = Database::query("SELECT count(id) FROM $tbl_lp_item_view WHERE lp_view_id = {$row_views->id} AND status = 'completed'");
                                $row_completed = Database::fetch_row($rs_completed);
                                $nb_completed = intval($row_completed[0]);                                
                                if ($total_items > 0) {
                                    $progress = round(($nb_completed * 100) / $total_items);
                                    if ($progress > 100) { 
                                        $progress = 100;                    
                    }
                                    $sum_progress += $progress;
                }
                                $total_views++;
                }
                            $avg_progress += round($sum_progress / $nb_views);
                }
                    }
                    if ($total_views > 0) {
                        $result = round($avg_progress / $nb_modules);
                    }
                    }
                    }
        return $result;
                }

        /**
         * This function gets:
         * 1. The score average from all SCORM Test items in all LP in a course-> All the answers / All the max scores.
         * 2. The score average from all Tests (quiz) in all LP in a course-> All the answers / All the max scores.
         * 3. And finally it will return the average between 1. and 2.
         * This function does not take the results of a Test out of a LP
         *
         * @param   mixed       Array of user ids or an user id
         * @param   string      Course code
         * @param   array       List of LP ids
         * @param   int         Session id (optional), if param $session_id is null(default) it'll return results including sessions, 0 = session is not filtered
         * @param   bool        Returns an array of the type [sum_score, num_score] if set to true
         * @param   bool        get only the latest attempts or ALL attempts
         * @return  string      Value (number %) Which represents a round integer explain in got in 3.
         */
	function get_avg_student_score($student_id, $course_code, $lp_ids = array(), $session_id = null, $return_array = false, $get_only_latest_attempt_results = false){
                    require_once api_get_path(LIBRARY_PATH).'course.lib.php';
                    // get global tables names
			$tbl_stats_exercices        = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
			$tbl_stats_attempts         = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

			$course = CourseManager::get_course_information($course_code);
			if (!empty($course)) {
				// get course tables names
				$tbl_quiz_questions = Database :: get_course_table(TABLE_QUIZ_QUESTION, $course['db_name']);
				$lp_table           = Database :: get_course_table(TABLE_LP_MAIN, $course['db_name']);
				$lp_item_table      = Database :: get_course_table(TABLE_LP_ITEM, $course['db_name']);
				$lp_view_table      = Database :: get_course_table(TABLE_LP_VIEW, $course['db_name']);
				$lp_item_view_table = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $course['db_name']);

				// Compose a filter based on optional learning paths list given
				$condition_lp = "";
				if (count($lp_ids) > 0) { $condition_lp =" AND id IN(".implode(',',$lp_ids).") "; }

				// Compose a filter based on optional session id
				$condition_session = "";
                                if (isset($session_id)) {
                                    $session_id = intval($session_id);
                                    $condition_session = api_get_session_condition($session_id);
                                }


				// Check the real number of LPs corresponding to the filter in the
				// database (and if no list was given, get them all)
				$sql = "SELECT DISTINCT(id) FROM $lp_table WHERE 1 = 1 $condition_session $condition_lp ";

				$res_row_lp   = Database::query($sql);
				$count_row_lp = Database::num_rows($res_row_lp);

				$lp_list = $use_max_score = array();
				while ($row_lp = Database::fetch_array($res_row_lp)) { $lp_list[] = $row_lp['id']; }

				// Init local variables that will be used through the calculation
				$progress = 0;
				// prepare filter on users
				$condition_user1 = "";
				if (is_array($student_id)) {
					array_walk($student_id, 'intval');
					$condition_user1 =" AND user_id IN (".implode(',', $student_id).") ";
				} else {
					$condition_user1 =" AND user_id = $student_id ";
				}

				if ($count_row_lp > 0 && !empty($student_id)) {
                                    // Getting latest LP result for a student
                                    //@todo problem when a  course have more than 1500 users
                                    $sql = "SELECT MAX(view_count) as vc, id, progress, lp_id, user_id  FROM $lp_view_table
                                                WHERE lp_id IN (".implode(',',$lp_list).") $condition_user1 GROUP BY lp_id, user_id";


                                    $rs_last_lp_view_id = Database::query($sql);
                                    $global_result = 0;
                                    if (Database::num_rows($rs_last_lp_view_id) > 0) {
                                            // Cycle through each line of the results (grouped by lp_id, user_id)
                                            while ($row_lp_view = Database::fetch_array($rs_last_lp_view_id)) {
                                                    $count_items = 0;
                                                    $lp_partial_total = 0;

                                                    $list = array();
                                                    $lp_view_id = $row_lp_view['id'];
                                                    $progress   = $row_lp_view['progress'];
                                                    $lp_id      = $row_lp_view['lp_id'];
                                                    $user_id    = $row_lp_view['user_id'];
                                                    if ($get_only_latest_attempt_results) {
                                                            //Getting lp_items done by the user
                                                            $sql  = "SELECT DISTINCT lp_item_id FROM $lp_item_view_table
                                                                            WHERE lp_view_id = $lp_view_id ORDER BY lp_item_id";
                                                            $res_lp_item = Database::query($sql);

                                                            while ($row_lp_item = Database::fetch_array($res_lp_item,'ASSOC')) {
                                                                    $my_lp_item_id = $row_lp_item['lp_item_id'];

                                                                    //Getting the most recent attempt
                                                                    $sql = "SELECT lp_iv.id as lp_item_view_id, lp_iv.score as score,lp_i.max_score, lp_iv.max_score as max_score_item_view, lp_i.path, lp_i.item_type, lp_i.id as iid
                                        FROM $lp_item_view_table as lp_iv INNER JOIN $lp_item_table as lp_i ON lp_i.id = lp_iv.lp_item_id AND (lp_i.item_type='sco' OR lp_i.item_type='".TOOL_QUIZ."')
                                        WHERE
                                                                                                    lp_item_id = $my_lp_item_id AND
                                                                                                    lp_view_id = $lp_view_id
                                                                                    ORDER BY view_count DESC
                                                                                    LIMIT 1";
                                                                    $res_lp_item_result = Database::query($sql);
                                                                    while ($row_max_score = Database::fetch_array($res_lp_item_result,'ASSOC')) {
                                                                            $list[]= $row_max_score;
                                                                    }
                                                            }
                                                    } else {
                                                            // For the currently analysed view, get the score and
                                                            // max_score of each item if it is a sco or a TOOL_QUIZ
                                                            $sql_max_score = "SELECT lp_iv.id as lp_item_view_id, lp_iv.score as score,lp_i.max_score, lp_iv.max_score as max_score_item_view, lp_i.path, lp_i.item_type, lp_i.id as iid
                                              FROM $lp_item_view_table as lp_iv INNER JOIN $lp_item_table as lp_i ON lp_i.id = lp_iv.lp_item_id AND (lp_i.item_type='sco' OR lp_i.item_type='".TOOL_QUIZ."')
                                              WHERE lp_view_id = $lp_view_id ";
                                                            if ($debug) echo $sql_max_score.'<br />';
                                                            $res_max_score = Database::query($sql_max_score);
                                                            while ($row_max_score = Database::fetch_array($res_max_score,'ASSOC')) {
                                                                    $list[]= $row_max_score;
                                                            }
                                                    }

                                                    // Go through each scorable element of this view

                                                    $score_of_scorm_calculate = 0;

                                                    foreach ($list as $row_max_score) {
                                                            $max_score              = $row_max_score['max_score'];  //Came from the original lp_item
                                                            $max_score_item_view    = $row_max_score['max_score_item_view']; //Came from the lp_item_view
                                                            $score                  = $row_max_score['score'];
                                                            if($score > $max_score) {
                                                                    $score = $max_score;
                                                            }

                                                            if ($debug) echo '<h3>Item Type: ' .$row_max_score['item_type'].'</h3>';

                                                            if ($row_max_score['item_type'] == 'sco') {
                                                                    //var_dump($row_max_score);
                                                                    // Check if it is sco (easier to get max_score)
                                                                    //when there's no max score, we assume 100 as the max score, as the SCORM 1.2 says that the value should always be between 0 and 100.
                                                                    if ($max_score == 0 || is_null($max_score) || $max_score == '') {
                                                                            //Chamilo style
                                                                            if ($use_max_score[$lp_id]) {
                                                                                    $max_score = 100;
                                                                            } else {
                                                                                    //Overwrites max score = 100 to use the one that came in the lp_item_view see BT#1613
                                                                                    $max_score = $max_score_item_view;
                                                                            }
                                                                    }
                                                                    //Avoid division by zero errors
                                                                    if (!empty($max_score)) {
                                                                            $lp_partial_total += $score/$max_score;
                                                                    }
                                                                    if ($debug) echo '<b>$lp_partial_total, $score, $max_score '.$lp_partial_total.' '.$score.' '.$max_score.'</b><br />';
                                                            } else {
                                                                    // Case of a TOOL_QUIZ element
                                                                    $item_id    = $row_max_score['iid'];
                                                                    $item_path  = $row_max_score['path'];
                                                                    $lp_item_view_id  = $row_max_score['lp_item_view_id'];

                                                                    // Get last attempt to this exercise  through
                                                                    // the current lp for the current user
                                                                    $sql_last_attempt = "SELECT exe_id FROM $tbl_stats_exercices WHERE
                                                                                            exe_exo_id           = '$item_path' AND
                                                                                            exe_user_id          = $user_id AND
                                                                                            orig_lp_item_id      = $item_id AND
                                                                                            orig_lp_id IN (".implode(',',$lp_list).") AND
                                                                                            exe_cours_id         = '$course_code'
                                                                                            $condition_session
                                                                                            ORDER BY exe_date DESC LIMIT 1";
                                                                    if ($debug) echo $sql_last_attempt .'<br />';
                                                                    $result_last_attempt = Database::query($sql_last_attempt);
                                                                    $num = Database :: num_rows($result_last_attempt);
                                                                    if ($num > 0 ) {
                                                                            $id_last_attempt = Database :: result($result_last_attempt, 0, 0);
                                                                            if ($debug) echo $id_last_attempt.'<br />';
                                                                            // Within the last attempt number tracking, get the sum of
                                                                            // the max_scores of all questions that it was
                                                                            // made of (we need to make this call dynamic
                                                                            // because of random questions selection)
                                                                            $sql = "SELECT SUM(t.ponderation) as maxscore FROM
                                            ( SELECT distinct question_id, marks, ponderation FROM $tbl_stats_attempts AS at INNER JOIN  $tbl_quiz_questions AS q ON (q.id = at.question_id)
                                              WHERE exe_id ='$id_last_attempt' ) AS t";
                                                                            $res_max_score_bis = Database::query($sql);
                                                                            $row_max_score_bis = Database :: fetch_array($res_max_score_bis);
                                                                            if (!empty($row_max_score_bis['maxscore'])) {
                                                                                    $max_score = $row_max_score_bis['maxscore'];
                                                                            }
                                                                            if (!empty($max_score)) {
                                                                                    $lp_partial_total            += $score/$max_score;
                                                                            }
                                                                            if ($debug) echo '$lp_partial_total, $score, $max_score <b>'.$lp_partial_total.' '.$score.' '.$max_score.'</b><br />';
                                                                    }
                                                            }

                                                            if (in_array($row_max_score['item_type'], array('quiz','sco'))) {
                                                                    // Normal way
                                                                    if ($use_max_score[$lp_id]) {
                                                                            $count_items++;
                                                                    } else {
                                                                            if ($max_score != '') {
                                                                                    $count_items++;
                                                                            }
                                                                    }
                                                                    if ($debug) echo '$count_items: '.$count_items;
                                                            }
                                                    } //end for

                                                    $score_of_scorm_calculate += $count_items?(($lp_partial_total/$count_items)*100):0;

                                                    if ($debug) echo '<h3>$count_items '.$count_items.'</h3>';
                                                    if ($debug) echo '<h3>$score_of_scorm_calculate '.$score_of_scorm_calculate.'</h3>';

                                                    // var_dump($score_of_scorm_calculate);
                                                    $global_result += $score_of_scorm_calculate;
                                                    if ($debug) echo '<h3>$global_result '.$global_result.'</h3>';
                                            } // end while
                                    }

                                    $lp_with_quiz = 0;
                                    if ($debug) var_dump($lp_list);
                                    foreach ($lp_list as $lp_id) {
                                            //Check if LP have a score we asume that all SCO have an score
                                            $sql = "SELECT count(id) as count FROM $lp_item_table WHERE (item_type = 'quiz' OR item_type = 'sco') AND lp_id = ".$lp_id;
                                            if ($debug) echo $sql;
                                            $result_have_quiz = Database::query($sql);

                                            if (Database::num_rows($result_have_quiz) > 0 ) {
                                                    $row = Database::fetch_array($result_have_quiz,'ASSOC');
                                                    if (is_numeric($row['count']) && $row['count'] != 0) {
                                                            $lp_with_quiz ++;
                                                    }
                                            }
                                    }

                                    if ($debug) echo '<h3>$lp_with_quiz '.$lp_with_quiz.' </h3>';
                                    if ($debug) echo '<h3>Final return</h3>';

                                    if ($lp_with_quiz != 0 ) {
                                            if (!$return_array) {
                                                    $score_of_scorm_calculate = round(($global_result/$lp_with_quiz),2);
                                                    if ($debug) var_dump($score_of_scorm_calculate);
                                                    if (empty($lp_ids)) {
                                                            //$score_of_scorm_calculate = round($score_of_scorm_calculate/count($lp_list),2);
                                                            if ($debug) echo '<h2>All lps fix: '.$score_of_scorm_calculate.'</h2>';
                                                    }
                                                    return $score_of_scorm_calculate;
                                            } else {
                                                    if ($debug) var_dump($global_result, $lp_with_quiz);
                                                    return array($global_result, $lp_with_quiz);
                                            }
                                    } else {
                                            return '-';
                                    }
                            }
			}
			return null;

	}

	/**
	 * gets the list of students followed by coach
	 * @param integer $coach_id the id of the coach
	 * @return Array the list of students
	 */
	function get_student_followed_by_coach($coach_id) {
		$coach_id = intval($coach_id);

		$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session_user = Database :: get_main_table(TABLE_MAIN_SESSION_USER);
		$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);

		$a_students = array ();

		//////////////////////////////////////////////////////////////
		// At first, courses where $coach_id is coach of the course //
		//////////////////////////////////////////////////////////////
		$sql = 'SELECT id_session, course_code FROM ' . $tbl_session_course_user . ' WHERE id_user=' . $coach_id.' AND status=2';

		global $_configuration;
		if ($_configuration['multiple_access_urls']==true) {
			$tbl_session_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
			$access_url_id = api_get_current_access_url_id();
			if ($access_url_id != -1) {
				$sql = 'SELECT scu.id_session, scu.course_code
						FROM ' . $tbl_session_course_user . ' scu INNER JOIN '.$tbl_session_rel_access_url.'  sru
						ON (scu.id_session=sru.session_id)
						WHERE scu.id_user=' . $coach_id.' AND scu.status=2 AND sru.access_url_id = '.$access_url_id;
			}
		}

		$result = Database::query($sql,__FILE__,__LINE__);

		while ($a_courses = Database::fetch_array($result)) {

			$course_code = $a_courses["course_code"];
			$id_session = $a_courses["id_session"];

			$sql = "SELECT distinct	srcru.id_user
								FROM $tbl_session_course_user AS srcru, $tbl_session_user sru
								WHERE srcru.id_user = sru.id_user AND srcru.id_session = sru.id_session AND srcru.course_code='$course_code' AND srcru.id_session='$id_session'";

			$rs = Database::query($sql,__FILE__,__LINE__);

			while ($row = Database::fetch_array($rs)) {
				$a_students[$row['id_user']] = $row['id_user'];
			}
		}

		//////////////////////////////////////////////////////////////
		// Then, courses where $coach_id is coach of the session    //
		//////////////////////////////////////////////////////////////

		$sql = 'SELECT session_course_user.id_user
						FROM ' . $tbl_session_course_user . ' as session_course_user
						INNER JOIN 	'.$tbl_session_user.' sru ON session_course_user.id_user = sru.id_user AND session_course_user.id_session = sru.id_session
						INNER JOIN ' . $tbl_session_course . ' as session_course
							ON session_course.course_code = session_course_user.course_code
							AND session_course_user.id_session = session_course.id_session
						INNER JOIN ' . $tbl_session . ' as session
							ON session.id = session_course.id_session
							AND session.id_coach = ' . $coach_id;
		if ($_configuration['multiple_access_urls']==true) {
			$tbl_session_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
			$access_url_id = api_get_current_access_url_id();
			if ($access_url_id != -1){
				$sql = 'SELECT session_course_user.id_user
				FROM ' . $tbl_session_course_user . ' as session_course_user
				INNER JOIN 	'.$tbl_session_user.' sru ON session_course_user.id_user = sru.id_user AND session_course_user.id_session = sru.id_session
				INNER JOIN ' . $tbl_session_course . ' as session_course
					ON session_course.course_code = session_course_user.course_code
					AND session_course_user.id_session = session_course.id_session
				INNER JOIN ' . $tbl_session . ' as session
					ON session.id = session_course.id_session
					AND session.id_coach = ' . $coach_id.'
				INNER JOIN '.$tbl_session_rel_access_url.'  session_rel_url
					ON session.id = session_rel_url.session_id WHERE access_url_id = '.$access_url_id;
			}
		}

		$result = Database::query($sql,__FILE__,__LINE__);

		while ($row = Database::fetch_array($result)) {
			$a_students[$row['id_user']] = $row['id_user'];
		}
		return $a_students;
	}

	function get_student_followed_by_coach_in_a_session($id_session, $coach_id) {

		$coach_id = intval($coach_id);

		$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);

		$a_students = array ();

		//////////////////////////////////////////////////////////////
		// At first, courses where $coach_id is coach of the course //
		//////////////////////////////////////////////////////////////
		$sql = 'SELECT course_code FROM ' . $tbl_session_course_user . ' WHERE id_session="' . $id_session . '" AND id_user=' . $coach_id.' AND status=2';

		$result = Database::query($sql,__FILE__,__LINE__);

		while ($a_courses = Database::fetch_array($result)) {
			$course_code = $a_courses["course_code"];

			$sql = "SELECT distinct	srcru.id_user
								FROM $tbl_session_course_user AS srcru
								WHERE course_code='$course_code' and id_session = '" . $id_session . "'";

			$rs = Database::query($sql, __FILE__, __LINE__);

			while ($row = Database::fetch_array($rs)) {
				$a_students[$row['id_user']] = $row['id_user'];
			}
		}

		//////////////////////////////////////////////////////////////
		// Then, courses where $coach_id is coach of the session    //
		//////////////////////////////////////////////////////////////

		$dsl_session_coach = 'SELECT id_coach FROM ' . $tbl_session . ' WHERE id="' . $id_session . '" AND id_coach="' . $coach_id . '"';
		$result = Database::query($dsl_session_coach, __FILE__, __LINE__);
		//He is the session_coach so we select all the users in the session
		if (Database::num_rows($result) > 0) {
			$sql = 'SELECT DISTINCT srcru.id_user FROM ' . $tbl_session_course_user . ' AS srcru WHERE id_session="' . $id_session . '"';
			$result = Database::query($sql,__FILE__,__LINE__);
			while ($row = Database::fetch_array($result)) {
				$a_students[$row['id_user']] = $row['id_user'];
			}
		}
		return $a_students;
	}

	function is_allowed_to_coach_student($coach_id, $student_id) {
		$coach_id = intval($coach_id);
		$student_id = intval($student_id);

		$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);

		//////////////////////////////////////////////////////////////
		// At first, courses where $coach_id is coach of the course //
		//////////////////////////////////////////////////////////////
		/*$sql = 'SELECT 1
						FROM ' . $tbl_session_course_user . ' AS session_course_user
						INNER JOIN ' . $tbl_session_course . ' AS session_course
							ON session_course.course_code = session_course_user.course_code
							AND id_coach=' . $coach_id . '
						WHERE id_user=' . $student_id;*/

		$sql = 'SELECT 1 FROM ' . $tbl_session_course_user . ' WHERE id_user=' . $coach_id .' AND status=2';

		$result = Database::query($sql, __FILE__, __LINE__);
		if (Database::num_rows($result) > 0) {
			return true;
		}

		//////////////////////////////////////////////////////////////
		// Then, courses where $coach_id is coach of the session    //
		//////////////////////////////////////////////////////////////

		$sql = 'SELECT session_course_user.id_user
						FROM ' . $tbl_session_course_user . ' as session_course_user
						INNER JOIN ' . $tbl_session_course . ' as session_course
							ON session_course.course_code = session_course_user.course_code
						INNER JOIN ' . $tbl_session . ' as session
							ON session.id = session_course.id_session
							AND session.id_coach = ' . $coach_id . '
						WHERE id_user = ' . $student_id;
		$result = Database::query($sql, __FILE__, __LINE__);
		if (Database::num_rows($result) > 0) {
			return true;
		}

		return false;

	}

	function get_courses_followed_by_coach($coach_id, $id_session = '')
	{

		$coach_id = intval($coach_id);
		if (!empty ($id_session))
			$id_session = intval($id_session);

		$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
		$tbl_course = Database :: get_main_table(TABLE_MAIN_COURSE);

		//////////////////////////////////////////////////////////////
		// At first, courses where $coach_id is coach of the course //
		//////////////////////////////////////////////////////////////
		$sql = 'SELECT DISTINCT course_code FROM ' . $tbl_session_course_user . ' WHERE id_user=' . $coach_id.' AND status=2';

		global $_configuration;
		if ($_configuration['multiple_access_urls']==true) {
			$tbl_course_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
			$access_url_id = api_get_current_access_url_id();
			if ($access_url_id != -1){
				$sql = 'SELECT DISTINCT scu.course_code FROM ' . $tbl_session_course_user . ' scu INNER JOIN '.$tbl_course_rel_access_url.' cru
						ON (scu.course_code = cru.course_code)
						WHERE scu.id_user=' . $coach_id.' AND scu.status=2 AND cru.access_url_id = '.$access_url_id;
			}
		}

		if (!empty ($id_session))
			$sql .= ' AND id_session=' . $id_session;
		$result = Database::query($sql, __FILE__, __LINE__);
		while ($row = Database::fetch_array($result)) {
			$a_courses[$row['course_code']] = $row['course_code'];
		}

		//////////////////////////////////////////////////////////////
		// Then, courses where $coach_id is coach of the session    //
		//////////////////////////////////////////////////////////////
		$sql = 'SELECT DISTINCT session_course.course_code
						FROM ' . $tbl_session_course . ' as session_course
						INNER JOIN ' . $tbl_session . ' as session
							ON session.id = session_course.id_session
							AND session.id_coach = ' . $coach_id . '
						INNER JOIN ' . $tbl_course . ' as course
							ON course.code = session_course.course_code';

		if ($_configuration['multiple_access_urls']==true) {
			$tbl_course_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
			$access_url_id = api_get_current_access_url_id();
			if ($access_url_id != -1){
				$sql = 'SELECT DISTINCT session_course.course_code
						FROM ' . $tbl_session_course . ' as session_course
						INNER JOIN ' . $tbl_session . ' as session
							ON session.id = session_course.id_session
							AND session.id_coach = ' . $coach_id . '
						INNER JOIN ' . $tbl_course . ' as course
							ON course.code = session_course.course_code
						 INNER JOIN '.$tbl_course_rel_access_url.' course_rel_url
						ON (session_course.course_code = course_rel_url.course_code)';
			}
		}

		if (!empty ($id_session)) {
			$sql .= ' WHERE session_course.id_session=' . $id_session;
			if ($_configuration['multiple_access_urls']==true)
				$sql .=  ' AND access_url_id = '.$access_url_id;
		}  else {
			if ($_configuration['multiple_access_urls']==true)
				$sql .=  ' WHERE access_url_id = '.$access_url_id;
		}

		$result = Database::query($sql, __FILE__, __LINE__);

		while ($row = Database::fetch_array($result)) {
			$a_courses[$row['course_code']] = $row['course_code'];
		}

		return $a_courses;
	}

	function get_sessions_coached_by_user($coach_id) {
		// table definition
		$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
		$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

		// protect datas
		$coach_id = intval($coach_id);

		// session where we are general coach
		$sql = 'SELECT DISTINCT id, name, date_start, date_end
						FROM ' . $tbl_session . '
						WHERE id_coach=' . $coach_id;

		global $_configuration;
		if ($_configuration['multiple_access_urls']==true) {
			$tbl_session_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
			$access_url_id = api_get_current_access_url_id();
			if ($access_url_id != -1){
				$sql = 'SELECT DISTINCT id, name, date_start, date_end
						FROM ' . $tbl_session . ' session INNER JOIN '.$tbl_session_rel_access_url.' session_rel_url
						ON (session.id = session_rel_url.session_id)
						WHERE id_coach=' . $coach_id.' AND access_url_id = '.$access_url_id;
			}
		}

		$rs = Database::query($sql,__FILE__,__LINE__);

		while ($row = Database::fetch_array($rs))
		{
			$a_sessions[$row["id"]] = $row;
		}

		// session where we are coach of a course
		$sql = 'SELECT DISTINCT session.id, session.name, session.date_start, session.date_end
						FROM ' . $tbl_session . ' as session
						INNER JOIN ' . $tbl_session_course_user . ' as session_course_user
							ON session.id = session_course_user.id_session
							AND session_course_user.id_user=' . $coach_id.' AND session_course_user.status=2';

		global $_configuration;
		if ($_configuration['multiple_access_urls']==true) {
			$tbl_session_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
			$access_url_id = api_get_current_access_url_id();
			if ($access_url_id != -1){
				$sql = 'SELECT DISTINCT session.id, session.name, session.date_start, session.date_end
						FROM ' . $tbl_session . ' as session
						INNER JOIN ' . $tbl_session_course_user . ' as session_course_user
							ON session.id = session_course_user.id_session AND session_course_user.id_user=' . $coach_id.' AND session_course_user.status=2
						INNER JOIN '.$tbl_session_rel_access_url.' session_rel_url
						ON (session.id = session_rel_url.session_id)
						WHERE access_url_id = '.$access_url_id;
			}
		}

		$rs = Database::query($sql,__FILE__,__LINE__);

		while ($row = Database::fetch_array($rs))
		{
			$a_sessions[$row["id"]] = $row;
		}

		if (is_array($a_sessions)) {
			foreach ($a_sessions as & $session) {
				if ($session['date_start'] == '0000-00-00') {
					$session['status'] = get_lang('SessionActive');
				}
				else {
					$date_start = explode('-', $session['date_start']);
					$time_start = mktime(0, 0, 0, $date_start[1], $date_start[2], $date_start[0]);
					$date_end = explode('-', $session['date_end']);
					$time_end = mktime(0, 0, 0, $date_end[1], $date_end[2], $date_end[0]);
					if ($time_start < time() && time() < $time_end) {
						$session['status'] = get_lang('SessionActive');
					}
					else{
						if (time() < $time_start) {
							$session['status'] = get_lang('SessionFuture');
						}
						else{
							if (time() > $time_end) {
								$session['status'] = get_lang('SessionPast');
							}
						}
					}
				}
			}
		}

		return $a_sessions;

	}

	function get_courses_list_from_session($session_id) {
		//protect datas
		$session_id = intval($session_id);

		// table definition
		$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
		$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);

		$sql = 'SELECT DISTINCT course_code
						FROM ' . $tbl_session_course . '
						WHERE id_session=' . $session_id;

		$rs = Database::query($sql, __FILE__, __LINE__);
		$a_courses = array ();
		while ($row = Database::fetch_array($rs)) {
			$a_courses[$row['course_code']] = $row;
		}
		return $a_courses;
	}

	function count_student_assignments($student_id, $course_code) {
		require_once (api_get_path(LIBRARY_PATH) . 'course.lib.php');

		// protect datas
		$course_code = Database::escape_string($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		if (!empty($a_course['db_name'])) {
			// table definition
			$tbl_item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY, $a_course['db_name']);
			$condition_user = "";
			if (is_array($student_id)) {
				$condition_user = " AND insert_user_id IN (".implode(',',$student_id).") ";
			} else {
				$condition_user = " AND insert_user_id = '$student_id' ";
			}
			$sql = "SELECT count(tool) FROM $tbl_item_property WHERE tool='work' $condition_user ";
			$rs = Database::query($sql, __LINE__, __FILE__);
			$row = Database::fetch_row($rs);
			return $row[0];
		}
		return null;
	}

	function count_student_messages($student_id, $course_code) {
		require_once (api_get_path(LIBRARY_PATH) . 'course.lib.php');

		// protect datas
		$course_code = addslashes($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		if (!empty($a_course['db_name'])) {
			// table definition
			$tbl_messages = Database :: get_course_table(TABLE_FORUM_POST, $a_course['db_name']);
			$condition_user = "";
			if (is_array($student_id)) {
				$condition_user = " WHERE poster_id IN (".implode(',',$student_id).") ";
			} else {
				$condition_user = " WHERE poster_id = '$student_id' ";
			}
			$sql = "SELECT count(post_id) FROM $tbl_messages $condition_user ";
			$rs = Database::query($sql, __LINE__, __FILE__);
			$row = Database::fetch_row($rs);
			return $row[0];
		}
		return null;
	}

/**
* This function counts the number of post by course
* @param  string $course_code - Course ID
* @return	int the number of post by course
* @author Christian Fasanando <christian.fasanando@dokeos.com>,
* @version enero 2009, dokeos 1.8.6
*/
	function count_number_of_posts_by_course($course_code) {
		//protect data
		$course_code = addslashes($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		$count = 0;
		if (!empty($a_course['db_name'])) {
			$tbl_posts = Database :: get_course_table(TABLE_FORUM_POST, $a_course['db_name']);
			$sql = "SELECT count(*) FROM $tbl_posts";
			$result = Database::query($sql, __FILE__, __LINE__);
			$row = Database::fetch_row($result);
			$count = $row[0];
			return $count;
		} else {
			return null;
		}
	}

/**
* This function counts the number of threads by course
* @param  string $course_code - Course ID
* @return	int the number of threads by course
* @author Christian Fasanando <christian.fasanando@dokeos.com>,
* @version enero 2009, dokeos 1.8.6
*/
	function count_number_of_threads_by_course($course_code) {
		//protect data
		$course_code = addslashes($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		$count = 0;
		if (!empty($a_course['db_name'])) {
			$tbl_threads = Database :: get_course_table(TABLE_FORUM_THREAD, $a_course['db_name']);
			$sql = "SELECT count(*) FROM $tbl_threads";
			$result = Database::query($sql, __FILE__, __LINE__);
			$row = Database::fetch_row($result);
			$count = $row[0];
			return $count;
		} else {
			return null;
		}
	}

/**
* This function counts the number of forums by course
* @param  string $course_code - Course ID
* @return	int the number of forums by course
* @author Christian Fasanando <christian.fasanando@dokeos.com>,
* @version enero 2009, dokeos 1.8.6
*/
	function count_number_of_forums_by_course($course_code) {
		//protect data
		$course_code = addslashes($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		$count = 0;
		if (!empty($a_course['db_name'])) {
			$tbl_forums = Database :: get_course_table(TABLE_FORUM, $a_course['db_name']);
			$sql = "SELECT count(*) FROM $tbl_forums";
			$result = Database::query($sql, __FILE__, __LINE__);
			$row = Database::fetch_row($result);
			$count = $row[0];
			return $count;
		} else {
			return null;
		}
	}

/**
* This function counts the chat last connections by course in x days
* @param  string $course_code - Course ID
* @param  int $last_days -  last x days
* @return	int the chat last connections by course in x days
* @author Christian Fasanando <christian.fasanando@dokeos.com>,
* @version enero 2009, dokeos 1.8.6
*/
	function chat_connections_during_last_x_days_by_course($course_code,$last_days) {
		//protect data
		$last_days = intval($last_days);
		$course_code = addslashes($course_code);
		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		$count = 0;
		if (!empty($a_course['db_name'])) {
			$tbl_stats_access = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ACCESS, $a_course['db_name']);

			$sql = "SELECT count(*) FROM $tbl_stats_access WHERE DATE_SUB(NOW(),INTERVAL $last_days DAY) <= access_date
					AND access_cours_code = '$course_code' AND access_tool='".TOOL_CHAT."'";
			$result = Database::query($sql, __FILE__, __LINE__);
			$row = Database::fetch_row($result);
			$count = $row[0];
			return $count;
		} else {
			return null;
		}
	}


/**
* This function gets the last student's connection in chat
* @param  int $student_id - Student ID
* @param  string $course_code - Course ID
* @return string the last connection
* @author Christian Fasanando <christian.fasanando@dokeos.com>,
* @version enero 2009, dokeos 1.8.6
*/
	function chat_last_connection($student_id,$course_code) {
		require_once (api_get_path(LIBRARY_PATH) . 'course.lib.php');

		//protect datas
		$student_id = intval($student_id);
		$course_code = addslashes($course_code);

		// get the informations of the course
		$a_course = CourseManager :: get_course_information($course_code);
		$date_time = '';
		if (!empty($a_course['db_name'])) {
			// table definition
			$tbl_stats_access = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ACCESS, $a_course['db_name']);
			$sql = "SELECT access_date FROM $tbl_stats_access
					 WHERE access_tool='".TOOL_CHAT."' AND access_user_id='$student_id' AND access_cours_code = '$course_code' ORDER BY access_date DESC limit 1";

			$rs = Database::query($sql, __LINE__, __FILE__);
			$row = Database::fetch_array($rs);
			$last_connection = $row['access_date'];
			if (!empty($last_connection)) {
				$date_format_long = format_locale_date(get_lang('DateFormatLongWithoutDay'), strtotime($last_connection));
				$time = explode(' ',$last_connection);
				$date_time = $date_format_long.' '.$time[1];
			}

			return $date_time;
		} else {
				return null;
		}
	}

	function count_student_visited_links($student_id, $course_code) {
		// protect datas
		$student_id = intval($student_id);
		$course_code = addslashes($course_code);

		// table definition
		$tbl_stats_links = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LINKS);

		$sql = 'SELECT 1
						FROM ' . $tbl_stats_links . '
						WHERE links_user_id=' . $student_id . '
						AND links_cours_id="' . $course_code . '"';

		$rs = Database::query($sql, __LINE__, __FILE__);
		return Database::num_rows($rs);
	}

	function count_student_downloaded_documents($student_id, $course_code) {
		// protect datas
		$student_id = intval($student_id);
		$course_code = addslashes($course_code);

		// table definition
		$tbl_stats_documents = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_DOWNLOADS);

		$sql = 'SELECT 1
						FROM ' . $tbl_stats_documents . '
						WHERE down_user_id=' . $student_id . '
						AND down_cours_id="' . $course_code . '"';

		$rs = Database::query($sql, __LINE__, __FILE__);
		return Database::num_rows($rs);
	}

	function get_course_list_in_session_from_student($user_id, $id_session) {
		$user_id = intval($user_id);
		$id_session = intval($id_session);
		$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$sql = 'SELECT course_code FROM ' . $tbl_session_course_user . ' WHERE id_user="' . $user_id . '" AND id_session="' . $id_session . '"';
		$result = Database::query($sql, __LINE__, __FILE__);
		$a_courses = array ();
		while ($row = Database::fetch_array($result)) {
			$a_courses[$row['course_code']] = $row['course_code'];
		}
		return $a_courses;
	}

	function get_inactives_students_in_course($course_code, $since, $session_id=0)
	{
		$tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
		$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$table_course_rel_user			= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$inner = '';
		if($session_id!=0)
		{
			$inner = ' INNER JOIN '.$tbl_session_course_user.' session_course_user
						ON stats_login.course_code = session_course_user.course_code
						AND session_course_user.id_session = '.intval($session_id).'
						AND session_course_user.id_user = stats_login.user_id
						';
		} else {
			$inner = ' INNER JOIN '.$table_course_rel_user.' course_user
						ON course_user.user_id = stats_login.user_id AND course_user.course_code = stats_login.course_code
					   WHERE stats_login.course_code = \''.Database::escape_string($course_code).'\'
				     ';
		}

		$sql = 'SELECT stats_login.user_id, MAX(login_course_date) max_date FROM '.$tbl_track_login.' stats_login'.$inner.'
				GROUP BY stats_login.user_id
				HAVING DATE_SUB( NOW(), INTERVAL '.$since.' DAY) > max_date ';
		//HAVING DATE_ADD(max_date, INTERVAL '.$since.' DAY) < NOW() ';

		//echo $sql;
                //quit $inner. in $since == 'never'
		if ($since == 'never') {
			$sql = 'SELECT course_user.user_id FROM '.$table_course_rel_user.' course_user
						LEFT JOIN '. $tbl_track_login.' stats_login
						ON course_user.user_id = stats_login.user_id'.
						'
					WHERE course_user.course_code = \''.Database::escape_string($course_code).'\'
					AND stats_login.login_course_date IS NULL
					GROUP BY course_user.user_id';
		}
		$rs = api_sql_query($sql,__FILE__,__LINE__);
		$inactive_users = array();
		while($user = Database::fetch_array($rs))
		{
			$inactive_users[] = $user['user_id'];
		}
		return $inactive_users;
	}

	function count_login_per_student($student_id, $course_code) {
		$student_id = intval($student_id);
		$course_code = addslashes($course_code);
		$tbl_course_rel_user = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ACCESS);

		$sql = 'SELECT '.$student_id.'
		FROM ' . $tbl_course_rel_user . '
		WHERE access_user_id=' . $student_id . '
		AND access_cours_code="' . $course_code . '"';

		$rs = Database::query($sql, __FILE__, __LINE__);
		$nb_login = Database::num_rows($rs);

		return $nb_login;
	}


	function get_student_followed_by_drh($hr_dept_id) {

		$hr_dept_id = intval($hr_dept_id);
		$a_students = array ();

		$tbl_organism = Database :: get_main_table(TABLE_MAIN_ORGANISM);
		$tbl_user = Database :: get_main_table(TABLE_MAIN_USER);

		$sql = 'SELECT DISTINCT user_id FROM '.$tbl_user.' as user
				WHERE hr_dept_id='.$hr_dept_id;
		$rs = Database::query($sql, __FILE__, __LINE__);

		while($user = Database :: fetch_array($rs))
		{
			$a_students[$user['user_id']] = $user['user_id'];
		}


		return $a_students;
	}
	/**
	 * allow get average  of test of scorm and lp
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @param int the user id
	 * @param string the course id
	 */
	function get_average_test_scorm_and_lp ($user_id,$course_id) {

		/**
		 * this function returned inconsistent values (e.g. 3000%).
		 * Moreover it's a duplicate of get_avg_student_score
		 * That's why we redirect to get_avg_student_score
		 */

		return Tracking::get_avg_student_score($user_id, $course_id);
	}

 function count_item_resources() {
	$table_item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$table_user = Database :: get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT count(tool) AS total_number_of_items FROM $table_item_property track_resource, $table_user user" .
			" WHERE track_resource.insert_user_id = user.user_id";

	if (isset($_GET['keyword'])) {
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " AND (user.username LIKE '%".$keyword."%' OR lastedit_type LIKE '%".$keyword."%' OR tool LIKE '%".$keyword."%')";
	}

	$sql .= " AND tool IN ('document', 'learnpath', 'quiz', 'glossary', 'link', 'course_description')";
	$res = Database::query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}

function get_item_resources_data($from, $number_of_items, $column, $direction) {
	global $dateTimeFormatLong;
	$table_item_property = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$table_user = Database :: get_main_table(TABLE_MAIN_USER);
	$table_session = Database :: get_main_table(TABLE_MAIN_SESSION);
	$sql = "SELECT
			 	tool as col0,
				lastedit_type as col1,
				ref as ref,
				user.username as col3,
				insert_date as col5,
				visibility as col6
			FROM $table_item_property track_resource, $table_user user
			WHERE track_resource.insert_user_id = user.user_id ";

	if (isset($_GET['keyword'])) {
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " AND (user.username LIKE '%".$keyword."%' OR lastedit_type LIKE '%".$keyword."%' OR tool LIKE '%".$keyword."%') ";
	}

	$sql .= " AND tool IN ('document', 'learnpath', 'quiz', 'glossary', 'link', 'course_description')";

	if ($column == 0) { $column = '0'; }
	if ($column != '' && $direction != '') {
		if ($column != 2 && $column != 4) {
			$sql .=	" ORDER BY col$column $direction";
		}
	} else {
		$sql .=	" ORDER BY col5 DESC ";
	}

	$sql .=	" LIMIT $from, $number_of_items ";

	$res = Database::query($sql, __FILE__, __LINE__) or die(mysql_error());
	$resources = array ();

	while ($row = Database::fetch_array($res)) {
		$ref = $row['ref'];
		$table_name = Tracking::get_tool_name_table($row['col0']);
		$table_tool = Database :: get_course_table($table_name['table_name']);
		$id = $table_name['id_tool'];
		$query = "SELECT session.id, session.name, user.username FROM $table_tool tool, $table_session session, $table_user user" .
					" WHERE tool.session_id = session.id AND session.id_coach = user.user_id AND tool.$id = $ref";
		$recorset = Database::query($query, __FILE__, __LINE__);

		if (!empty($recorset)) {

			$obj = Database::fetch_object($recorset);

			$name_session = '';
			$coach_name = '';
			if (!empty($obj)) {
				$name_session = $obj->name;
				$coach_name = $obj->username;
			}

			$url_tool = api_get_path(WEB_CODE_PATH).$table_name['link_tool'];

			$row[0] = '';
			if ($row['col6'] != 2) {
				$row[0] = '<a href="'.$url_tool.'?'.api_get_cidreq().'&'.$obj->id.'">'.api_ucfirst($row['col0']).'</a>';
			} else {
				$row[0] = api_ucfirst($row['col0']);
			}

			$row[1] = get_lang($row[1]);

			$row[5] = api_ucfirst(format_locale_date($dateTimeFormatLong, strtotime($row['col5'])));

			$row[4] = '';
			if ($table_name['table_name'] == 'document') {
				$condition = 'tool.title as title';
				$query_document = "SELECT $condition FROM $table_tool tool" .
									" WHERE id = $ref";
				$rs_document = Database::query($query_document, __FILE__, __LINE__) or die(mysql_error());
				$obj_document = Database::fetch_object($rs_document);
				$row[4] = $obj_document->title;
			}

			$row2 = $name_session;
			if (!empty($coach_name)) {
				$row2 .= '<br />'.get_lang('Coach').': '.$coach_name;
			}
			$row[2] = $row2;

			$resources[] = $row;
		}

	}

	return $resources;
}

function get_tool_name_table($tool) {
	switch ($tool) {
		case 'document':
			$table_name = TABLE_DOCUMENT;
			$link_tool = 'document/document.php';
			$id_tool = 'id';
			break;
		case 'learnpath':
			$table_name = TABLE_LP_MAIN;
			$link_tool = 'newscorm/lp_controller.php';
			$id_tool = 'id';
			break;
		case 'quiz':
			$table_name = TABLE_QUIZ_TEST;
			$link_tool = 'exercice/exercice.php';
			$id_tool = 'id';
			break;
		case 'glossary':
			$table_name = TABLE_GLOSSARY;
			$link_tool = 'glossary/index.php';
			$id_tool = 'glossary_id';
			break;
		case 'link':
			$table_name = TABLE_LINK;
			$link_tool = 'link/link.php';
			$id_tool = 'id';
			break;
		case 'course_description':
			$table_name = TABLE_COURSE_DESCRIPTION;
			$link_tool = 'course_description/';
			$id_tool = 'id';
			break;
		default:
			$table_name = $tool;
			break;
	}
	return array('table_name' => $table_name,
				 'link_tool' => $link_tool,
				 'id_tool' => $id_tool);
}
/**
 * This function gets all the information of a certrain ($field_id) additional profile field for a specific list of users is more efficent than  get_additional_profile_information_of_field() function
 * It gets the information of all the users so that it can be displayed in the sortable table or in the csv or xls export
 *
 * @author	Julio Montoya <gugli100@gmail.com>
 * @param	int field id
 * @param	array list of user ids
 * @return	array
 * @since	Nov 2009
 * @version	1.8.6.2
 */
function get_additional_profile_information_of_field_by_user($field_id, $users) {
	// Database table definition
	$table_user 				= Database::get_main_table(TABLE_MAIN_USER);
	$table_user_field_values 	= Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
	$result_extra_field 		= UserManager::get_extra_field_information($field_id);

	if (!empty($users)) {
		if ($result_extra_field['field_type'] == USER_FIELD_TYPE_TAG ) {
			foreach($users as $user_id) {
				$user_result = UserManager::get_user_tags($user_id, $field_id);
				$tag_list = array();
				foreach($user_result as $item) {
					$tag_list[] = $item['tag'];
				}
				$return[$user_id][] = implode(', ',$tag_list);
			}
		} else {
			$new_user_array = array();
			foreach($users as $user_id) {
				$new_user_array[]= "'".$user_id."'";
			}
			$users = implode(',',$new_user_array);
			//selecting only the necessary information NOT ALL the user list
			$sql = "SELECT user.user_id, field.field_value FROM $table_user user INNER JOIN $table_user_field_values field
					ON (user.user_id = field.user_id)
					WHERE field.field_id=".intval($field_id)." AND user.user_id IN ($users)";

			$result = api_sql_query($sql,__FILE__,__LINE__);
			while($row = Database::fetch_array($result)) {
				// get option value for field type double select by id
				if (!empty($row['field_value'])) {
					if ($result_extra_field['field_type'] == USER_FIELD_TYPE_DOUBLE_SELECT) {
						$id_double_select = explode(';',$row['field_value']);
						if (is_array($id_double_select)) {
							$value1 = $result_extra_field['options'][$id_double_select[0]]['option_value'];
							$value2 = $result_extra_field['options'][$id_double_select[1]]['option_value'];
							$row['field_value'] = ($value1.';'.$value2);
						}
					}
				}
				// get other value from extra field
				$return[$row['user_id']][] = $row['field_value'];
			}
		}
	}
	return $return;
}

/**
 * Get data for users list in sortable with pagination
 * @return array
 */
function get_user_data($from = null, $number_of_items = null, $column = null, $direction = null, $get_extra_field = true, $add_limit = true) {

	global $user_ids, $course_code, $additional_user_profile_info, $export_csv, $is_western_name_order, $csv_content;

	$course_code = Database::escape_string($course_code);
	$course_info = CourseManager :: get_course_information($course_code);
	$tbl_track_cours_access = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
	$tbl_user 				= Database :: get_main_table(TABLE_MAIN_USER);
	$tbl_item_property 		= Database :: get_course_table(TABLE_ITEM_PROPERTY, $course_info['db_name']);
	$tbl_forum_post  		= Database :: get_course_table(TABLE_FORUM_POST, $course_info['db_name']);
	$tbl_course_lp_view 	= Database :: get_course_table(TABLE_LP_VIEW, $course_info['db_name']);
	$tbl_course_lp 			= Database :: get_course_table(TABLE_LP_MAIN, $course_info['db_name']);

	// get all users data from a course for sortable with limit
	$condition_user = "";
	if (is_array($user_ids)) {
		$condition_user = " WHERE user.user_id IN (".implode(',',$user_ids).") ";
	} else {
		$condition_user = " WHERE user.user_id = '$user_ids' ";
	}
	$sql = "SELECT user.user_id as col0,
			user.official_code as col1,
			user.lastname as col2,
			user.firstname as col3 ";

	// getting additional user fields (of the user table). These can be defined in in the function display_additional_profile_fields and
	// need to be added to the array of valid fields (a security measure)
	if (isset($_GET['additional_profile_field']) AND in_array($_GET['additional_profile_field'], array('active','email'))) {
		$sql .= ", user.".$_GET['additional_profile_field']." as col10 ";
	}
	$sql .= "FROM $tbl_user as user
			$condition_user ";

	if (!in_array($direction, array('ASC','DESC'))) {
    	$direction = 'ASC';
    }
    $column = intval($column);
    $from = intval($from);
    $number_of_items = intval($number_of_items);
	$sql .= " ORDER BY col$column $direction ";
	if ($add_limit === true) {
	  $sql .= " LIMIT $from,$number_of_items";
	}
	$res = Database::query($sql, __FILE__, __LINE__);
	$users = array ();
    $t = time();
   	$row = array();

	while ($user = Database::fetch_row($res)) {
		$row[0] = $user[1];
		if ($is_western_name_order) {
			$row[1] = $user[3];
			$row[2] = $user[2];
		} else {
			$row[1] = $user[2];
			$row[2] = $user[3];
		}
		$time = Tracking::get_time_spent_on_the_course($user[0], $course_code);
		$row[3] = api_time_to_hms($time);
		$avg_student_score = Tracking::get_avg_student_score($user[0], $course_code);
		$avg_student_progress = Tracking::get_avg_student_progress($user[0], $course_code);
		if (empty($avg_student_progress)) {$avg_student_progress=0;}
		$row[4] = $avg_student_progress.'%';
		$row[5] = empty($avg_student_score) ? '-' : $avg_student_score.'%';
		$row[6] = Tracking::count_student_assignments($user[0], $course_code);$user[4];
		$row[7] = Tracking::count_student_messages($user[0], $course_code);//$user[5];
		$row[8] = Tracking::get_first_connection_date_on_the_course($user[0], $course_code);
		$row[9] = Tracking::get_last_connection_date_on_the_course($user[0], $course_code);

		// we need to display an additional profile field
		if (isset($_GET['additional_profile_field']) AND is_numeric($_GET['additional_profile_field'])) {
			if (is_array($additional_user_profile_info[$user[0]])) {
				$row[10]=implode(', ', $additional_user_profile_info[$user[0]]);
			} else {
				$row[10]='&nbsp;';
			}
		}

		// getting additional user fields (of the user table). These can be defined in in the function display_additional_profile_fields and
		// need to be added to the array of valid fields (a security measure)
		if (isset($_GET['additional_profile_field']) AND in_array($_GET['additional_profile_field'], array('active','email'))) {
			if ($_GET['additional_profile_field'] == 'active'){
				if ($user[4] == '1'){
					$row[10] = Display::return_icon('pixel.gif','',array('class'=>'actionplaceholdericon actionsvalidate'));
				} else {
					$row[10] = Display::return_icon('wrong.png');
				}
			}
			if ($_GET['additional_profile_field'] == 'email'){
					$row[10] = '<a href="mailto:'.$user[4].'">'.$user[4].'</a>';
			}
		}



  if ($get_extra_field === true) {
    $row[11] = '<center><a href="../mySpace/myStudents.php?student='.$user[0].'&amp;details=true&amp;course='.$course_code.'&amp;origin=tracking_course">'.Display::return_icon('pixel.gif','',array('class'=>'actionplaceholdericon actionstatisticsdetails','border'=>'0')).'</a></center>';
  } else {
    $row[10] = '<center><a href="../mySpace/myStudents.php?student='.$user[0].'&amp;details=true&amp;course='.$course_code.'&amp;origin=tracking_course">'.Display::return_icon('pixel.gif','',array('class'=>'actionplaceholdericon actionstatisticsdetails','border'=>'0')).'</a></center>';
  }
    if ($export_csv) {
            $row[8] = strip_tags($row[8]);
            $row[9] = strip_tags($row[9]);
            unset($row[11]);
            unset($row[10]);

            $extra = array();
            if (isset($_GET['additional_profile_field']) AND is_numeric($_GET['additional_profile_field'])) {
                // extra user values
                $ind = 10;
                $student_data= UserManager :: get_extra_user_data($user[0],true,false);
                foreach($student_data as $key=>$value) {
                        $key= substr($key,6);
                        if (is_array($value))
                                $extra[$ind]= $value[$key];
                        else {
                                $extra[$ind]= $value;
                        }
                        $ind++;
                }
            }
            $csv_content[] = array_merge($row, $extra);
    }
    if ($get_extra_field === true) {
        // store columns in array $users
        $users[] = array($row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9],$row[10],$row[11]);
    } else {
        // store columns in array $users
        $users[] = array($row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9],$row[10]);
    }

    $GLOBALS['chart_data'][$user[0]] = array('lastname'=>$row[2], 'firstname'=>$row[1], 'progress'=>intval($avg_student_progress), 'score'=>intval($avg_student_score), 'time'=>intval($time));
	}
	return $users;
}
/**
 * This function gets all the information of a certrain ($field_id) additional profile field.
 * It gets the information of all the users so that it can be displayed in the sortable table or in the csv or xls export
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @since October 2009
 * @version 1.8.7
 */
function get_additional_profile_information_of_field($field_id){
	// Database table definition
	$table_user 			= Database::get_main_table(TABLE_MAIN_USER);
	$table_user_field_values 	= Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);

	$sql = "SELECT user.user_id, field.field_value FROM $table_user user, $table_user_field_values field
		WHERE user.user_id = field.user_id
		AND field.field_id='".intval($field_id)."'";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	while($row = Database::fetch_array($result))
	{
		$return[$row['user_id']][] = $row['field_value'];
	}
	return $return;
}
/**
 * Display all the additionally defined user profile fields
 * This function will only display the fields, not the values of the field because it does not act as a filter
 * but it adds an additional column instead.
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @since October 2009
 * @version 1.8.7
 */
function display_additional_profile_fields($btn_submit = true, $multiple = false, $only_extra_fields = false) {
	// getting all the extra profile fields that are defined by the platform administrator
	$extra_fields = UserManager :: get_extra_fields(0,50,5,'ASC');

	// creating the form
	$return = '<form action="'.api_get_self().'?'.api_get_cidreq().'" method="get" name="additional_profile_field_form" id="additional_profile_field_form">';

	// the select field with the additional user profile fields (= this is where we select the field of which we want to see
	// the information the users have entered or selected.
	$return .= '<select name="'.($multiple?'additional_profile_field[]':'additional_profile_field').'" id="additional_profile_field" '.($multiple?' multiple="multiple"':'').'>';
        if (!$multiple) {
            $return .= '<option value="-">'.get_lang('SelectFieldToAdd').'</option>';
        }

        if (!$only_extra_fields) {
            if ($_GET['additional_profile_field'] == 'active'){ $activeselected = 'selected="selected"'; }
            $return .= '<option value="active" '.$activeselected.'>'.get_lang('Active').'</option>';
            if ($_GET['additional_profile_field'] == 'email') { $emailselected = 'selected="selected"'; }
            $return .= '<option value="email" '.$emailselected.'>'.get_lang('Email').'</option>';
        }

	foreach ($extra_fields as $key=>$field) {
		// show only extra fields that are visible, added by J.Montoya
		if ($field[6]==1) {
			if ($field[0] == $_GET['additional_profile_field'] || ($multiple && in_array($field[0], $_GET['additional_profile_field']))) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			$return .= '<option value="'.$field[0].'" '.$selected.'>'.$field[3].'</option>';
		}
	}
	$return .= '</select>';

	// the form elements for the $_GET parameters (because the form is passed through GET
	foreach ($_GET as $key=>$value){
		if ($key <> 'additional_profile_field')	{
			$return .= '<input type="hidden" name="'.$key.'" value="'.Security::Remove_XSS($value).'" />';
		}
	}
	// the submit button
        if ($btn_submit) {
            $return .= '<button class="save" type="submit" style="float:none;margin-left:10px;" name="add_field">'.get_lang('Validate').'</button>';
        }
	$return .= '</form>';
	return $return;
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
	$tbl_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);

	$is_western_name_order = api_is_western_name_order(PERSON_NAME_DATA_EXPORT);
	$sort_by_first_name = api_sort_by_first_name();

	// the values of the sortable table
	if ($_GET['tracking_user_overview_page_nr']) {
		$from = $_GET['tracking_user_overview_page_nr'];
	} else {
		$from = 0;
	}
	if ($_GET['tracking_user_overview_column']) {
		$orderby = $_GET['tracking_user_overview_column'];
	} else {
		$orderby = 0;
	}
	if ($is_western_name_order != api_is_western_name_order() && ($orderby == 1 || $orderby == 2)) {
		// Swapping the sorting column if name order for export is different than the common name order.
		$orderby = 3 - $orderby;
	}
	if ($_GET['tracking_user_overview_direction']) {
		$direction = $_GET['tracking_user_overview_direction'];
	} else {
		$direction = 'ASC';
	}

	$user_data = Tracking::get_user_data_tracking_overview($from, 1000, $orderby, $direction);

	// the first line of the csv file with the column headers
	$csv_row = array();
	$csv_row[] = get_lang('OfficialCode');
	if ($is_western_name_order) {
		$csv_row[] = get_lang('FirstName', '');
		$csv_row[] = get_lang('LastName', '');
	} else {
		$csv_row[] = get_lang('LastName', '');
		$csv_row[] = get_lang('FirstName', '');
	}
	$csv_row[] = get_lang('LoginName');
	$csv_row[] = get_lang('CourseCode');
	// the additional user defined fields (only those that were selected to be exported)
	require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
	$fields = UserManager::get_extra_fields(0, 50, 5, 'ASC');
	if (is_array($_SESSION['additional_export_fields'])) {
		foreach ($_SESSION['additional_export_fields'] as $key => $extra_field_export) {
			$csv_row[] = $fields[$extra_field_export][3];
			$field_names_to_be_exported[] = 'extra_'.$fields[$extra_field_export][1];
		}
	}
	$csv_row[] = get_lang('AvgTimeSpentInTheCourse', '');
	$csv_row[] = get_lang('AvgStudentsProgress', '');
	$csv_row[] = get_lang('AvgCourseScore', '');
	$csv_row[] = get_lang('AvgExercisesScore', '');
	$csv_row[] = get_lang('AvgMessages', '');
	$csv_row[] = get_lang('AvgAssignments', '');
	$csv_row[] = get_lang('TotalExercisesScoreObtained', '');
	$csv_row[] = get_lang('TotalExercisesScorePossible', '');
	$csv_row[] = get_lang('TotalExercisesAnswered', '');
	$csv_row[] = get_lang('TotalExercisesScorePercentage', '');
	$csv_row[] = get_lang('FirstLogin', '');
	$csv_row[] = get_lang('LatestLogin', '');
	$csv_content[] = $csv_row;

	// the other lines (the data)
	foreach ($user_data as $key => $user) {
		// getting all the courses of the user
		$sql = "SELECT * FROM $tbl_course_user WHERE user_id = '".Database::escape_string($user[4])."'";
		$result = Database::query($sql, __FILE__, __LINE__);
		while ($row = Database::fetch_row($result)) {
			$csv_row = array();
			// user official code
			$csv_row[] = $user[0];
			// user first|last name
			$csv_row[] = $user[1];
			// user last|first name
			$csv_row[] = $user[2];
			// user login name
			$csv_row[] = $user[3];
			// course code
			$csv_row[] = $row[0];
			// the additional defined user fields
			$extra_fields = get_user_overview_export_extra_fields($user[4]);
			if (is_array($field_names_to_be_exported)) {
				foreach ($field_names_to_be_exported as $key => $extra_field_export) {
					$csv_row[] = $extra_fields[$extra_field_export];
				}
			}
			// time spent in the course
			$csv_row[] = api_time_to_hms(Tracking :: get_time_spent_on_the_course ($user[4], $row[0]));
			// student progress in course
			$csv_row[] = round(Tracking :: get_avg_student_progress ($user[4], $row[0]), 2);
			// student score
			$csv_row[] = round(Tracking :: get_avg_student_score ($user[4], $row[0]), 2);
			// student tes score
			$csv_row[] = round(Tracking :: get_avg_student_exercise_score ($user[4], $row[0]), 2);
			// student messages
			$csv_row[] = Tracking :: count_student_messages ($user[4], $row[0]);
			// student assignments
			$csv_row[] = Tracking :: count_student_assignments ($user[4], $row[0]);
			// student exercises results
			$exercises_results = exercises_results($user[4], $row[0]);
			$csv_row[] = $exercises_results['score_obtained'];
			$csv_row[] = $exercises_results['score_possible'];
			$csv_row[] = $exercises_results['questions_answered'];
			$csv_row[] = $exercises_results['percentage'];
			// first connection
			$csv_row[] = Tracking :: get_first_connection_date_on_the_course ($user[4], $row[0]);
			// last connection
			$csv_row[] = strip_tags(Tracking :: get_last_connection_date_on_the_course ($user[4], $row[0]));

			$csv_content[] = $csv_row;
		}
	}
	Export :: export_table_csv($csv_content, 'reporting_user_overview');
}

/**
 * Display a sortable table that contains an overview off all the reporting progress of all users and all courses the user is subscribed to
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since October 2008
 */
function display_tracking_user_overview() {
	display_user_overview_export_options();

	$t_head .= '	<table style="width: 100%;border:0;padding:0;border-collapse:collapse;table-layout: fixed">';
	$t_head .= '	<caption>'.get_lang('CourseInformation').'</caption>';
	$t_head .=		'<tr>';
	$t_head .= '		<th width="155px" style="border-left:0;border-bottom:0"><span>'.get_lang('Course').'</span></th>';
	$t_head .= '		<th style="padding:0;border-bottom:0"><span>'.cut(get_lang('AvgTimeSpentInTheCourse'), 6, true).'</span></th>';
	$t_head .= '		<th style="padding:0;border-bottom:0"><span>'.cut(get_lang('AvgStudentsProgress'), 6, true).'</span></th>';
	$t_head .= '		<th style="padding:0;border-bottom:0"><span>'.cut(get_lang('AvgCourseScore'), 6, true).'</span></th>';
	//$t_head .= '		<th><div style="width:40px">'.get_lang('AvgExercisesScore').'</div></th>';
	$t_head .= '		<th style="padding:0;border-bottom:0"><span>'.cut(get_lang('AvgMessages'), 6, true).'</span></th>';
	$t_head .= '		<th style="padding:0;border-bottom:0"><span>'.cut(get_lang('AvgAssignments'), 6, true).'</span></th>';
	$t_head .= '		<th width="105px" style="border-bottom:0"><span>'.get_lang('TotalExercisesScoreObtained').'</span></th>';
	//$t_head .= '		<th><div>'.get_lang('TotalExercisesScorePossible').'</div></th>';
	$t_head .= '		<th style="padding:0;border-bottom:0"><span>'.cut(get_lang('TotalExercisesAnswered'), 6, true).'</span></th>';
	//$t_head .= '		<th><div>'.get_lang('TotalExercisesScorePercentage').'</div></th>';
	//$t_head .= '		<th><div style="width:60px">'.get_lang('FirstLogin').'</div></th>';
	$t_head .= '		<th style="padding:0;border-bottom:0;border-right:0;"><span>'.get_lang('LatestLogin').'</span></th>';
	$t_head .= '	</tr></table>';

	$addparams = array('view' => 'admin', 'display' => 'useroverview');

	$table = new SortableTable('tracking_user_overview', 'get_number_of_users_tracking_overview', 'get_user_data_tracking_overview', 0);
	$table->additional_parameters = $addparams;

	$table->set_header(0, get_lang('OfficialCode'), true, array('style' => 'font-size:8pt'), array('style' => 'font-size:8pt'));
	if (api_is_western_name_order()) {
		$table->set_header(1, get_lang('FirstName'), true, array('style' => 'font-size:8pt'), array('style' => 'font-size:8pt'));
		$table->set_header(2, get_lang('LastName'), true, array('style' => 'font-size:8pt'), array('style' => 'font-size:8pt'));
	} else {
		$table->set_header(1, get_lang('LastName'), true, array('style' => 'font-size:8pt'), array('style' => 'font-size:8pt'));
		$table->set_header(2, get_lang('FirstName'), true, array('style' => 'font-size:8pt'), array('style' => 'font-size:8pt'));
	}
	$table->set_header(3, get_lang('LoginName'), true, array('style' => 'font-size:8pt'), array('style' => 'font-size:8pt'));
	$table->set_header(4, $t_head, false, array('style' => 'width:90%;border:0;padding:0;font-size:7.5pt;'), array('style' => 'width:90%;padding:0;font-size:7.5pt;'));
	$table->set_column_filter(4, 'course_info_tracking_filter');
	$table->display();
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
	// database table definition
	$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);

	// query
	$sql = 'SELECT user_id FROM '.$main_user_table;
	$result = Database::query($sql, __FILE__, __LINE__);

	// return the number of results
	return Database::num_rows($result);
}

/**
 * get all the data for the sortable table of the reporting progress of all users and all the courses the user is subscribed to.
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version Dokeos 1.8.6
 * @since October 2008
 */
function get_user_data_tracking_overview($from, $number_of_items, $column, $direction) {
	// database table definition
	$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
        $tbl_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	global $export_csv;
	if ($export_csv) {
		$is_western_name_order = api_is_western_name_order(PERSON_NAME_DATA_EXPORT);
	} else {
		$is_western_name_order = api_is_western_name_order();
	}
        
	$sql = "SELECT
				official_code 	AS col0,
				".($is_western_name_order ? "
				firstname 		AS col1,
				lastname 		AS col2,
				" : "
				lastname 		AS col1,
				firstname 		AS col2,
				").
				"username		AS col3,
				user_id 		AS col4
			FROM
				$main_user_table m where exists(
                                    select * from $tbl_course_user c where c.user_id=m.user_id)
			";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
        
	$result = Database::query($sql, __FILE__, __LINE__);
	$return = array ();
	while ($user = Database::fetch_row($result)) {
		$return[] = $user;
	}
	return $return;
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
	// the table header
	$return .= '<table class="data_table" style="width: 100%;border:0;padding:0;border-collapse:collapse;table-layout: fixed">';
	/*$return .= '	<tr>';
	$return .= '		<th>'.get_lang('Course').'</th>';
	$return .= '		<th>'.get_lang('AvgTimeSpentInTheCourse').'</th>';
	$return .= '		<th>'.get_lang('AvgStudentsProgress').'</th>';
	$return .= '		<th>'.get_lang('AvgCourseScore').'</th>';
	$return .= '		<th>'.get_lang('AvgExercisesScore').'</th>';
	$return .= '		<th>'.get_lang('AvgMessages').'</th>';
	$return .= '		<th>'.get_lang('AvgAssignments').'</th>';
	$return .= '		<th>'.get_lang('TotalExercisesScoreObtained').'</th>';
	$return .= '		<th>'.get_lang('TotalExercisesScorePossible').'</th>';
	$return .= '		<th>'.get_lang('TotalExercisesAnswered').'</th>';
	$return .= '		<th>'.get_lang('TotalExercisesScorePercentage').'</th>';
	$return .= '		<th>'.get_lang('FirstLogin').'</th>';
	$return .= '		<th>'.get_lang('LatestLogin').'</th>';
	$return .= '	</tr>';*/

	// database table definition
	$tbl_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);

	// getting all the courses of the user
	$sql = "SELECT * FROM $tbl_course_user WHERE user_id = '".Database::escape_string($user_id)."'";
        
	$result = Database::query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_row($result)) {
		$return .= '<tr>';
		// course code
		$return .= '	<td width="157px" >'.cut($row[0], 20, true).'</td>';
		// time spent in the course
		$return .= '	<td><div>'.api_time_to_hms(Tracking :: get_time_spent_on_the_course($user_id, $row[0])).'</div></td>';
		// student progress in course
		$return .= '	<td><div>'.round(Tracking :: get_avg_student_progress($user_id, $row[0]), 2).'</div></td>';
		// student score
		$return .= '	<td><div>'.round(Tracking :: get_avg_student_score($user_id, $row[0]), 2).'</div></td>';
		// student tes score
		//$return .= '	<td><div style="width:40px">'.round(Tracking :: get_avg_student_exercise_score ($user_id, $row[0]),2).'%</div></td>';
		// student messages
		$return .= '	<td><div>'.Tracking :: count_student_messages($user_id, $row[0]).'</div></td>';
		// student assignments
		$return .= '	<td><div>'.Tracking :: count_student_assignments($user_id, $row[0]).'</div></td>';
		// student exercises results (obtained score, maximum score, number of exercises answered, score percentage)
		$exercises_results = exercises_results($user_id, $row[0]);
		$return .= '	<td width="105px"><div>'.(is_null($exercises_results['percentage']) ? '' : $exercises_results['score_obtained'].'/'.$exercises_results['score_possible'].' ( '.$exercises_results['percentage'].'% )').'</div></td>';
		//$return .= '	<td><div>'.$exercises_results['score_possible'].'</div></td>';
		$return .= '	<td><div>'.$exercises_results['questions_answered'].'</div></td>';
		//$return .= '	<td><div>'.$exercises_results['percentage'].'% </div></td>';
		// first connection
		//$return .= '	<td width="60px">'.Tracking :: get_first_connection_date_on_the_course ($user_id, $row[0]).'</td>';
		// last connection
		$return .= '	<td><div>'.Tracking :: get_last_connection_date_on_the_course ($user_id, $row[0]).'</div></td>';
		$return .= '</tr>';
	}
	$return .= '</table>';
	return $return;
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
	$questions_answered = 0;
	$sql = 'SELECT exe_result , exe_weighting
		FROM '.Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES)."
		WHERE exe_cours_id = '".Database::escape_string($course_code)."'
		AND exe_user_id = '".Database::escape_string($user_id)."'";
	$result = Database::query($sql, __FILE__, __LINE__);
	$score_obtained = 0;
	$score_possible = 0;
	$questions_answered = 0;
	while ($row = Database::fetch_array($result)) {
		$score_obtained += $row['exe_result'];
		$score_possible += $row['exe_weighting'];
		$questions_answered ++;
	}

	if ($score_possible != 0) {
		$percentage = round(($score_obtained / $score_possible * 100), 2);
	} else {
		$percentage = null;
	}

	return array('score_obtained' => $score_obtained, 'score_possible' => $score_possible, 'questions_answered' => $questions_answered, 'percentage' => $percentage);
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
	// include the user manager and formvalidator library
	require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
	require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

	if ($_GET['export'] == 'options') {
		// get all the defined extra fields
		$extrafields = UserManager::get_extra_fields(0, 50, 5, 'ASC'/*, false*/);

		// creating the form with all the defined extra fields
		$form = new FormValidator('exportextrafields', 'post', api_get_self()."?view=".Security::remove_XSS($_GET['view']).'&display='.Security::remove_XSS($_GET['display']).'&export='.Security::remove_XSS($_GET['export']));
		foreach ($extrafields as $key => $extra) {
			$form->addElement('checkbox', 'extra_export_field'.$extra[0], '', $extra[3]);
		}
		$form->addElement('style_submit_button','submit', get_lang('Ok'),'class="save"' );

		// setting the default values for the form that contains all the extra fields
		if (is_array($_SESSION['additional_export_fields'])) {
			foreach ($_SESSION['additional_export_fields'] as $key => $value) {
				$defaults['extra_export_field'.$value] = 1;
			}
		}
		$form->setDefaults($defaults);

		if ($form->validate()) {
			// exporting the form values
			$values = $form->exportValues();

			// re-initialising the session that contains the additional fields that need to be exported
			$_SESSION['additional_export_fields'] = array();

			// adding the fields that are checked to the session
			$message = '';
			foreach ($values as $field_ids => $value) {
				if ($value == 1 && strstr($field_ids,'extra_export_field')) {
					$_SESSION['additional_export_fields'][] = str_replace('extra_export_field', '', $field_ids);
				}
			}

			// adding the fields that will be also exported to a message string
			if (is_array($_SESSION['additional_export_fields'])) {
				foreach ($_SESSION['additional_export_fields'] as $key => $extra_field_export) {
					$message .= '<li>'.$extrafields[$extra_field_export][3].'</li>';
				}
			}

			// Displaying a feedback message
			if (!empty($_SESSION['additional_export_fields'])) {
				Display::display_confirmation_message(get_lang('FollowingFieldsWillAlsoBeExported').': <br /><ul>'.$message.'</ul>', false);
			} else  {
				Display::display_confirmation_message(get_lang('NoAdditionalFieldsWillBeExported'), false);
			}
			$message = '';
		} else {
			$form->display();
		}
	} else {
		if (!empty($_SESSION['additional_export_fields'])) {
			// get all the defined extra fields
			$extrafields = UserManager::get_extra_fields(0, 50, 5, 'ASC');

			foreach ($_SESSION['additional_export_fields'] as $key => $extra_field_export) {
				$message .= '<li>'.$extrafields[$extra_field_export][3].'</li>';
			}

			Display::display_normal_message(get_lang('FollowingFieldsWillAlsoBeExported').': <br /><ul>'.$message.'</ul>', false);
			$message = '';
		}
	}
}

/**
 * Get data for courses list in sortable with pagination
 * @return array
 */
function get_course_data($from = null, $number_of_items = null, $column = null, $direction = null, $add_limit = true) {

	global $courses, $csv_content, $charset ;
	global $tbl_course, $tbl_course_user, $tbl_track_cours_access, $tbl_session_course_user;

	$a_course_students  = array();
	$course_data = $chart_data = array();
	$arr_course = $courses;
	foreach ($arr_course as &$cours) {
		$cours = "'{$cours[course_code]}'";
	}

	// get all courses with limit
	$sql = "SELECT course.code as col1, course.title as col2
			FROM $tbl_course course
			WHERE course.code IN (".implode(',',$arr_course).")";
	if (!in_array($direction, array('ASC','DESC'))) $direction = 'ASC';

    $column = intval($column);
    $from = intval($from);
    $number_of_items = intval($number_of_items);
	$sql .= " ORDER BY col$column $direction ";
	if ($add_limit == true) {
	  $sql .= " LIMIT $from,$number_of_items";
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	while ($row_course = Database::fetch_row($res)) {

		$course_code = $row_course[0];
		$course_info = api_get_course_info($course_code);
		$avg_assignments_in_course = $avg_messages_in_course = $nb_students_in_course = $avg_progress_in_course = $avg_score_in_course = $avg_time_spent_in_course = $avg_score_in_exercise = 0;
		$tbl_item_property 		= Database :: get_course_table(TABLE_ITEM_PROPERTY, $course_info['dbName']);
		$tbl_forum_post  		= Database :: get_course_table(TABLE_FORUM_POST, $course_info['dbName']);
		$tbl_course_lp_view = Database :: get_course_table(TABLE_LP_VIEW, $course_info['dbName']);
		$tbl_course_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);

		// students directly subscribed to the course
		$sql = "SELECT user_id FROM $tbl_course_user as course_rel_user WHERE course_rel_user.status='5' AND course_rel_user.course_code='$course_code'
		  		UNION DISTINCT SELECT id_user as user_id FROM $tbl_session_course_user srcu WHERE  srcu. course_code='$course_code'";
		$rs = Database::query($sql, __FILE__, __LINE__);
		$users = array();
		while ($row = Database::fetch_array($rs)) {
			$users[] = $row['user_id'];
		}
		if (count($users) > 0) {
			$nb_students_in_course = count($users);
			$avg_assignments_in_course = Tracking::count_student_assignments($users, $course_code);
			$avg_messages_in_course    = Tracking::count_student_messages($users, $course_code);
			$avg_time_spent_in_course  = $time_for_chart = Tracking::get_time_spent_on_the_course($users, $course_code);
			$avg_progress_in_course = Tracking::get_avg_student_progress($users, $course_code);
			$avg_score_in_course = Tracking :: get_avg_student_score($users, $course_code);
			$avg_score_in_exercise = Tracking::get_avg_student_exercise_score($users, $course_code);

			$avg_time_spent_in_course = api_time_to_hms($avg_time_spent_in_course / $nb_students_in_course);
			$avg_progress_in_course = round($avg_progress_in_course / $nb_students_in_course, 2);
			$avg_score_in_course = round($avg_score_in_course / $nb_students_in_course, 2);
			$avg_score_in_exercise = round($avg_score_in_exercise / $nb_students_in_course, 2);
		} else {
			$avg_time_spent_in_course = null;
			$avg_progress_in_course = null;
			$avg_score_in_course = null;
			$avg_score_in_exercise = null;
			$avg_messages_in_course = null;
			$avg_assignments_in_course = null;
			$time_for_chart = null;
		}
		$table_row = array();
		$table_row[] = $row_course[1];
		$table_row[] = $nb_students_in_course;
		$table_row[] = $avg_time_spent_in_course;
		$table_row[] = is_null($avg_progress_in_course) ? '' : $avg_progress_in_course.'%';
		$table_row[] = is_null($avg_score_in_course) ? '' : $avg_score_in_course.'%';
		$table_row[] = is_null($avg_score_in_exercise) ? '' : $avg_score_in_exercise.'%';
		$table_row[] = $avg_messages_in_course;
		$table_row[] = $avg_assignments_in_course;
		//set the "from" value to know if I access the Reporting by the Dokeos tab or the course link
		$table_row[] = '<center><a href="../tracking/courseLog.php?cidReq='.$course_code.'&amp;studentlist=true&amp;from=myspace">'.Display::return_icon('pixel.gif','&nbsp;',array('class'=>'actionplaceholdericon actionstatisticsdetails','border'=>'0')).'</a></center>';
		$csv_content[] = array(
			api_html_entity_decode($row_course[1], ENT_QUOTES, $charset),
			$nb_students_in_course,
			$avg_time_spent_in_course,
			is_null($avg_progress_in_course) ? null : $avg_progress_in_course.'%',
			is_null($avg_score_in_course) ? null : $avg_score_in_course.'%',
			is_null($avg_score_in_exercise) ? null : $avg_score_in_exercise.'%',
			$avg_messages_in_course,
			$avg_assignments_in_course,
		);
		$course_data[] = $table_row;
		$chart_data[$row_course[0]] = array('title'=>$row_course[1], 'progress'=>intval($avg_progress_in_course), 'score'=>intval($avg_score_in_course), 'time'=>intval($time_for_chart));
	}
	$GLOBALS['chart_data'] = $chart_data;
	return $course_data;
}

        /**
	 * This function gets all the information of a certrain ($field_id) additional profile field for a specific list of users is more efficent than  get_addtional_profile_information_of_field() function
	 * It gets the information of all the users so that it can be displayed in the sortable table or in the csv or xls export
	 *
	 * @param    int field id
	 * @param    array list of user ids
	 * @return    array
	 */
	function get_addtional_profile_information_of_field_by_user($field_id, $users) {
		// Database table definition
		$table_user                 = Database::get_main_table(TABLE_MAIN_USER);
		$table_user_field_values     = Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
		$result_extra_field         = UserManager::get_extra_field_information($field_id);

		if (!empty($users)) {
			if ($result_extra_field['field_type'] == USER_FIELD_TYPE_TAG ) {
				foreach($users as $user_id) {
					$user_result = UserManager::get_user_tags($user_id, $field_id);
					$tag_list = array();
					foreach($user_result as $item) {
						$tag_list[] = $item['tag'];
					}
					$return[$user_id][] = implode(', ',$tag_list);
				}
			} else {
				$new_user_array = array();
				foreach($users as $user_id) {
					$new_user_array[]= "'".$user_id."'";
				}
				$users = implode(',',$new_user_array);
				//selecting only the necessary information NOT ALL the user list
				$sql = "SELECT user.user_id, field.field_value FROM $table_user user INNER JOIN $table_user_field_values field
                        ON (user.user_id = field.user_id)
                        WHERE field.field_id=".intval($field_id)." AND user.user_id IN ($users)";

				$result = Database::query($sql);
				while($row = Database::fetch_array($result)) {
					// get option value for field type double select by id
					if (!empty($row['field_value'])) {
						if ($result_extra_field['field_type'] == USER_FIELD_TYPE_DOUBLE_SELECT) {
							$id_double_select = explode(';',$row['field_value']);
							if (is_array($id_double_select)) {
								$value1 = $result_extra_field['options'][$id_double_select[0]]['option_value'];
								$value2 = $result_extra_field['options'][$id_double_select[1]]['option_value'];
								$row['field_value'] = ($value1.';'.$value2);
							}
						}
					}
					// get other value from extra field
					$return[$row['user_id']][] = $row['field_value'];
				}
			}
		}
		return $return;
	}


    function get_avg_student_quiz_score($student_id, $course_code, $session_id = 0) {
            $course_info = api_get_course_info($course_code);
        $tbl_quiz = Database::get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);
        $tbl_track_exercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
        $sum_score = $attempts = $count = 0;
        $result = false;
        // get best attempts of quizzes
        $rs_quizzes = Database::query("SELECT id FROM $tbl_quiz WHERE active = 1 AND session_id IN (0, ".intval($session_id).")");
        $nb_quizzes = Database::num_rows($rs_quizzes);
        if ($nb_quizzes > 0) {
            while ($row_quizzes = Database::fetch_object($rs_quizzes)) {                    
                $sql  = "SELECT MAX(exe_result / exe_weighting) as score, exe_user_id, exe_weighting
                          FROM $tbl_track_exercise 
                          WHERE exe_cours_id = '$course_code' 
                            AND exe_exo_id = {$row_quizzes->id} 
                            AND status NOT IN('left_incomplete', 'incomplete') 
                            AND session_id = $session_id
                            AND exe_user_id = $student_id
                            AND exam_id = 0 
                                             AND orig_lp_id = 0
                                             AND orig_lp_item_id = 0
                          GROUP BY exe_user_id";   
                $rs_track = Database::query($sql);
                if (Database::num_rows($rs_track) > 0) {
                    while ($row_track = Database::fetch_object($rs_track)) {
                        if (!empty($row_track->exe_weighting)) {
                            $sum_score += ($row_track->score * 100);                                                                
                            $attempts++;
                        }
                    }
                }                
            }
        }
        // get last attempts of exams
        $tbl_exam = Database::get_course_table(TABLE_EXAM, $course_info['dbName']);
        $rs_exams = Database::query("SELECT id, quiz_id FROM $tbl_exam WHERE session_id IN (0, ".intval($session_id).")");
        $nb_exams = Database::num_rows($rs_exams);
        if ($nb_exams > 0) {
            while ($row_exams = Database::fetch_object($rs_exams)) {             
                $sql  = "SELECT MAX(exe_id) as last_exe_id, exe_user_id  
                          FROM $tbl_track_exercise 
                          WHERE exe_cours_id = '$course_code' 
                            AND exam_id = {$row_exams->id} 
                            AND status NOT IN('left_incomplete', 'incomplete') 
                            AND exam_id > 0 
                                             AND session_id = $session_id
                            AND exe_user_id = $student_id
                            AND orig_lp_id = 0 
                            AND orig_lp_item_id = 0
                          GROUP BY exe_user_id";   
                $rs_track = Database::query($sql);
                if (Database::num_rows($rs_track) > 0) {
                    while ($row_track = Database::fetch_object($rs_track)) {                        
                        $rs = Database::query("SELECT manual_exe_result FROM $tbl_track_exercise WHERE exe_id = {$row_track->last_exe_id}");
                        $row = Database::fetch_array($rs);  
                        if (!empty($row['manual_exe_result'])) {
                            $sum_score  += $row['manual_exe_result'];                            
                            $attempts++;
                             }
                     }
                }                     
            }
            }
        if ($attempts > 0) {
            $result = round($sum_score / $attempts);
        }
        return $result;
    }

}
?>
