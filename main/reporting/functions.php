<?php

/**
 * Prints a linkable icon
 * 
 * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
 * 
 * @param string $icon_class Class of icon to be printed
 * @param string $text Text to be printed on right of the icon
 * @param string $href Link to be followed by the clicked icon
 * @param string $title Title of the link
 * @param string $class Class of link to be printed
 * @param array $datas Array of data attributes to the link
 */
function returnIcon($icon_class, $text = '', $href = '', $title = '', $class = '', $datas = array()) {
    $icon = '<i class="' . $icon_class . '"></i>' . $text;
    if ($href != '' || $title != '') {
        if ($href != '') $href = ' href="' . $href . '"';
        if ($title != '') $title = ' title="' . $title . '"';
        if ($class != '') $class = ' class="' . $class . '"';
        if (count($datas)) {
            $data_text = "";
            foreach ($datas as $key => $value) {
                $data_text.= ' data-' . $key . '="' . $value . '"';
            }
        }
        $icon = '<a' . $href . $title . $class . $data_text . '>' . $icon . '</a>';
    }
    return $icon;
}

/**
 * Returns the course code and session id for make a shared url.
 * This will be used to extract data from course database
 * with no need to create a conflict with active system course
 * 
 * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
 * 
 * @return string Contains the course code and session id parameters
 */
function shared_url() {
    return empty($GLOBALS['_cid']) ? '' : htmlspecialchars($GLOBALS['_cid']) . (api_get_session_id() == 0 ? '' : ',' . api_get_session_id());
}

/**
 * Writes in a file created dynamiclly by a token the progress form value.
 * You need to create at the beginning of submit form the $progress_token 
 * from the html form by the display_progressbar function.
 * 
 * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
 * 
 * @param integer $value The progress value
 */
function progress_form($value) {
    global $progress_token;
    $filename  = dirname(__FILE__) . '/../../upload/webtv/' . $progress_token . '.txt';
    file_put_contents($filename, intval($value));
    usleep(500000);
    if ($value == 100) {
        unlink($filename);
    }
}

/**
 * Prints a html progressbar for the submit form and optionally
 * for upload file. Also a hidden input with a token generator for
 * the current form.
 * 
 * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
 * 
 * @param boolean $show_upload_file Option to print a progressbar for upload file form. Default: false
 * 
 * @return string Contains the html progressbar
 */
function display_progressbar($show_upload_file = false) {
    $tabs = str_repeat("\t", 10);
    $html_upload_file = '<div id="upload_progress" class="progress progress-striped active">' . "\n\t$tabs";
    $html_upload_file.= '<div class="progress-label">' . get_lang('Loading') . '...</div>' . "\n\t$tabs";
    $html_upload_file.= '<div class="bar"></div>' . "\n$tabs";
    $html_upload_file.= '</div>' . "\n$tabs";
    $html_submit_form = '<div id="all_progress" class="progress progress-success progress-striped active">' . "\n\t$tabs";
    $html_submit_form.= '<div class="progress-label">' . get_lang('Loading') . '...</div>' . "\n\t$tabs";
    $html_submit_form.= '<div class="bar"></div>' . "\n$tabs";
    $html_submit_form.= '</div>' . "\n$tabs";
    $html_progress_token = '<input type="hidden" name="progress_token" id="progress_token" value="' . uniqid(mt_rand()) . '" />' . "\n";
    return ($show_upload_file ? $html_upload_file : '') . $html_submit_form . $html_progress_token;
}

function pagination($per_page = 10, $page = 1, $url = '', $total, $active_tab){

	$adjacents = "1";

	$page = ($page == 0 ? 1 : $page);
	$start = ($page - 1) * $per_page;

	$prev = $page - 1;
	$next = $page + 1;
	$lastpage = ceil($total/$per_page);
	$lpm1 = $lastpage - 1;

	$pagination = "";
	if($lastpage > 1)
	{
		if($active_tab == 'courses') {
			$pagination .= "<div class='pagination'><ul id='course_pagination'>";
		}
		elseif($active_tab == 'modules') {
			$pagination .= "<div class='pagination'><ul id='module_pagination'>";
		}
		elseif($active_tab == 'quizzes') {
			$pagination .= "<div class='pagination'><ul id='quiz_pagination'>";
		}
		elseif($active_tab == 'learners') {
			$pagination .= "<div class='pagination'><ul id='learners_pagination'>";
		}
		if($prev > 0) 
		$pagination .= "<li><a class = 'test' href='{$url}$prev'>Prev</a></li>";
		//if ($lastpage < 7 + ($adjacents * 2))
		if ($lastpage < 4 + ($adjacents * 2))
		{			
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
					$pagination.= "<li class='active'><a>$counter</a></li>";
				else
					$pagination.= "<li><a class = 'test' href='{$url}$counter'>$counter</a></li>";
			}
		}
		//elseif($lastpage > 5 + ($adjacents * 2))
		elseif($lastpage > 3 + ($adjacents * 2))
		{
			if($page < 1 + ($adjacents * 2))
			{
				for ($counter = 1; $counter < 3 + ($adjacents * 2); $counter++)
				//for ($counter = 1; $counter < 5; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<li class='active'><a>$counter</a></li>";
					else
						$pagination.= "<li><a class = 'test' href='{$url}$counter'>$counter</a></li>";
				}
				$pagination.= "<li><a href='#'>...</a></li>";
				//$pagination.= "<li><a href='{$url}$lpm1'>$lpm1</a></li>";
				$pagination.= "<li><a class = 'test' href='{$url}$lastpage'>$lastpage</a></li>";
			}
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				$pagination.= "<li><a class = 'test' href='{$url}1'>1</a></li>";
				//$pagination.= "<li><a href='{$url}2'>2</a></li>";
				$pagination.= "<li><a href='#'>...</a></li>";
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<li class='active'><a>$counter</a></li>";
					else
						$pagination.= "<li><a class = 'test' href='{$url}$counter'>$counter</a></li>";
				}
				$pagination.= "<li><a href='#'>...</a></li>";
				//$pagination.= "<li><a href='{$url}$lpm1'>$lpm1</a></li>";
				$pagination.= "<li><a class = 'test' href='{$url}$lastpage'>$lastpage</a></li>";
			}
			else
			{
				$pagination.= "<li><a class = 'test' href='{$url}1'>1</a></li>";
				//$pagination.= "<li><a href='{$url}2'>2</a></li>";
				$pagination.= "<li><a href='#'>...</a></li>";
				//for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
				for ($counter = $lastpage - (2 + ($adjacents * 1)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<li class='active'><a>$counter</a></li>";
					else
						$pagination.= "<li><a class = 'test' href='{$url}$counter'>$counter</a></li>";
				}
			}
		}
		if ($page < $counter - 1){
		//if ($page < $counter){
			$pagination.= "<li><a class = 'test' href='{$url}$next'>Next</a></li>";
			// $pagination.= "<li><a href='{$url}$lastpage'>Last</a></li>";
		}else{
			//$pagination.= "<li><a class='current'>Next</a></li>";
			// $pagination.= "<li><a class='current'>Last</a></li>";
		}
		$pagination.= "</ul></div>";
	}
	return $pagination;
}

/**
 * Displays the list of courses in the platform
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return array of courses
 */
 function get_course_list($page1 = 0, $limit = 0) {
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
        $access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
        $access_url_id = api_get_current_access_url_id();
        if ($access_url_id < 0) {
            $access_url_id = 1;
        }
	if(api_is_allowed_to_edit()){
		$sql = "SELECT code, title 
                        FROM $course_table course 
                        INNER JOIN $access_url_rel_course_table rel ON (course.code=rel.course_code) 
                        WHERE rel.access_url_id = $access_url_id ORDER BY title";
                
		if($limit <> 0) {
			$sql .= " LIMIT $page1,$limit";
		}
		$res = Database::query($sql, __FILE__, __LINE__);
		while($course = Database::fetch_array($res)) {
			$courses[] = $course;
		}	
	}
	else {
		$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
		if(api_is_allowed_to_create_course()){
			$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";

			$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
		}
		else {
			$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
		}
		$sql .= " ORDER BY title";
		if($limit <> 0) {
			$sql .= " LIMIT $page1,$limit";
		}

		$res = Database::query($sql, __FILE__, __LINE__);
		while($course = Database::fetch_array($res)) {
			$courses[] = $course;
		}		

	}
	return $courses;
 }

 /**
 * Displays the list of trainers in the platform
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return array of trainers
 */
 function get_trainers() {
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT user_id, firstname, lastname FROM $user_table WHERE status = 1 ORDER BY lastname";
	$res = Database::query($sql, __FILE__, __LINE__);
	$trainers = array();
	while ($trainer = Database::fetch_array($res, 'ASSOC')) {
		$trainers[] = $trainer;
	}
	return $trainers;
 }

 /**
 * Displays the list of sessions in the platform
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return array of sessions
 */
 function get_sessions() {
	$session_table = Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

	$user_id = api_get_user_id();
	$sql = "SELECT DISTINCT(id), name FROM $session_table sess";
	if(!api_is_allowed_to_edit() && api_is_allowed_to_create_course()){
		$sql .= " ,$session_course_user_table scr WHERE sess.id = scr.id_session AND id_coach = ".$user_id." AND "; 
	}
	elseif(!api_is_allowed_to_edit()){
		$sql .= " ,$session_course_user_table scr WHERE sess.id = scr.id_session AND scr.id_user = ".$user_id." AND ";
	}
	else {
		$sql .= " WHERE ";
	}
	$sql .= " sess.visibility = 1 ORDER BY name";
	$res = Database::query($sql, __FILE__, __LINE__);
	$sessions = array();
	while ($session = Database::fetch_array($res, 'ASSOC')) {
		$sessions[] = $session;
	}
	return $sessions;
 }

 /**
 * Displays the total number of learners in the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , session_id by default is zero
 *
 * @return int - total number of learners in the course
 */
 function total_numberof_learners($course_code, $session_id = 0) {

	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

	if($session_id == 0) {
	//$sql = "SELECT * FROM $course_user_table WHERE status = 5 AND course_code = '".$course_code."'";
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.status = 5 AND cru.course_code = '".$course_code."'
		  		UNION DISTINCT SELECT id_user as user_id FROM $session_course_user_table srcu WHERE  srcu. course_code='$course_code'";
	}
	else {
		$sql = "SELECT id_user FROM $session_user_table WHERE id_session = ".$session_id;
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$num_learners = Database::num_rows($res);
	return $num_learners;
 } 

 /**
 * Displays the total list of learners in the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , session_id by default is zero
 *
 * @return int - total list of learners in the course
 */
 function learners_list($course_code, $session_id = 0) {

	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

	if($session_id == 0) {
		//$sql = "SELECT * FROM $course_user_table WHERE status = 5 AND course_code = '".$course_code."'";
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.status = 5 AND cru.course_code = '".$course_code."'
		  		UNION DISTINCT SELECT id_user as user_id FROM $session_course_user_table srcu WHERE  srcu. course_code='$course_code'";
	}
	else {
	$sql = "SELECT id_user AS user_id FROM $session_user_table WHERE id_session = ".$session_id;
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$num_learners = Database::num_rows($res);
	$users = array();
	while($user = Database::fetch_array($res)) {
		$users[] = $user['user_id'];
	}
	return $users;
 }

 /**
 * Displays the total time spent in the modules of the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , session_id by default is zero
 *
 * @return total time spent in all the modules of the course
 */
 function total_modules_time($course_code, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);

	/*$sql_session_user = "SELECT id_user FROM $session_user_table WHERE id_session = ".$session_id;
	$res = Database::query($sql_session_user, __FILE__, __LINE__);
	$users = array();
	while($row = Database::fetch_array($res)) {
		$users[] = $row['id_user'];
	}*/

	$users = learners_list($course_code,$session_id);

	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);
	$t_lpiv = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $info_course['db_name']);

	$sql = "SELECT id FROM $t_lp WHERE session_id IN (0, $session_id) ORDER BY name";
	$res = Database::query($sql, __FILE__, __LINE__);
	$sum_time = 0;
	while($row = Database::fetch_array($res)) {
		$lp_id = $row['id'];
		$sql_time = 'SELECT SUM(total_time)	FROM ' . $t_lpiv . ' AS item_view
											INNER JOIN ' . $t_lpv . ' AS view
											ON item_view.lp_view_id = view.id
											AND view.lp_id = ' . $lp_id;
		if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
			if($session_id <> 0) {
				$sql_time .= " AND view.user_id IN ('".implode("','",$users)."')";
			}
		}
		else {
			$sql_time .= " AND view.user_id =".api_get_user_id();
		}

		$rs = Database::query($sql_time, __FILE__, __LINE__);
		$total_time = 0;
		if (Database :: num_rows($rs) > 0) {
			$total_time = Database :: result($rs, 0, 0);					
		}
		$sum_time = $sum_time + $total_time;
	}
	
	return display_time_format($sum_time);
 }

 function display_time_format($seconds) {

	//if seconds = -1, it means we have wrong datas in the db
	if ($seconds == -1) {
            return get_lang('Unknown').Display::return_icon('info2.gif', get_lang('WrongDatasForTimeSpentOnThePlatform'), array('align' => 'middle', 'hspace' => '3px'));
	}

	//How many hours ?
	$hours = floor($seconds / 3600);

	//How many minutes ?
	$min = floor(($seconds - ($hours * 3600)) / 60);

	//How many seconds
	$sec = floor($seconds - ($hours * 3600) - ($min * 60));

	if ($sec < 10) {
            $sec = "0$sec";
	}

	if ($min < 10) {
            $min = "0$min";
	}

	return "$hours".'h'."$min'$sec''";
}

/**
 * Displays the module progress of the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , session_id by default is zero
 *
 * @return modules progress of the course in percentage
 */
 function total_modules_progress($course_code, $total_learners, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);
	
	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);

	$learners = learners_list($course_code,$session_id);
		
	$sql = "SELECT id FROM $t_lp WHERE session_id IN (0, $session_id) ORDER BY name";
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_modules = Database::num_rows($res);
	$sum_progress = 0;
	while($row = Database::fetch_array($res)) {
		$lp_id = $row['id'];

		if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
		$sql_progress = "SELECT SUM(progress) AS total_progress FROM $t_lpv WHERE lp_id = $lp_id AND user_id IN ('".implode("','",$learners)."')";
		}
		else {
		$sql_progress = "SELECT SUM(progress) AS total_progress FROM $t_lpv WHERE lp_id = $lp_id AND user_id =".api_get_user_id();
		$total_learners = 1;
		}
		$res_progress = Database::query($sql_progress, __FILE__, __LINE__);		
		$row_progress = Database :: fetch_array($res_progress);

		$sum_progress = $sum_progress + ($row_progress['total_progress']/$total_learners);

	}
	
	$final_progress = round(($sum_progress / $num_modules));

	return $final_progress;
 }

 /**
 * Displays the modules score of the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , session_id by default is zero
 *
 * @return modules score of the course in percentage
 */
 function total_modules_score($course_code, $total_learners, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);

	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);	
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	
	if($total_learners == 0) {
		$final_score = "n.a";
		return $final_score;
	}
	
	if($session_id == 0) {
            $sql = "SELECT * FROM $course_user_table WHERE status = 5 AND course_code = '".$course_code."'";
	}
	else {
            $sql = "SELECT id_user AS user_id FROM $session_user_table WHERE id_session = ".$session_id;
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$users = array();
	while ($user = Database::fetch_array($res, 'ASSOC')) {
		$users[] = $user;
	}
	if (!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()) {
            unset($users);
            $users[] = api_get_user_id();
            $total_learners = 1;
	}
	$sum_student_score = 0;
	foreach($users as $user) {
            $count = count_modules($user['user_id'], $course_code, $session_id);
            $student_score = Tracking :: get_average_test_scorm_and_lp($user['user_id'], $course_code);
            $sum_student_score = $sum_student_score + $student_score;
	}
	
	$avg_student_score = round(($sum_student_score / $total_learners),2) ;

	$avg_module_score = round(($avg_student_score / $count));

	return $avg_module_score.' %';
 }

 /**
 * Displays the quizzes score of the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , total learner , session_id by default is zero
 *
 * @return quiz score of the course in percentage
 */
 function total_quizzes_score($course_code, $total_learners, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

	if($total_learners == 0) {
		$total_score = "n.a";
		return $total_score;
	}

	$learners = learners_list($course_code,$session_id);
	$course_search = '';

	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){	 
		$learners = array();
		$learners[] = api_get_user_id();
		$total_learners = 1;
	}

	foreach ($learners as $user_id) {
		$quiz_score = get_student_quiz_score($user_id, $course_code, $session_id, $course_search);
		$total_score = $total_score + $quiz_score;
	}

	/*echo $sql = "SELECT exe_result , exe_weighting FROM $track_exercices WHERE exe_cours_id = '".Database::escape_string($course_code)."'
			AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND exe_user_id IN ('".implode("','",$learners)."')";

	$result = Database::query($sql, __FILE__, __LINE__);
	$score_obtained = 0;
	$score_possible = 0;
	while ($row = Database::fetch_array($result)) {
		$score_obtained += $row['exe_result'];
		$score_possible += $row['exe_weighting'];
	}

	if ($score_possible != 0) {
		$percentage = round(($score_obtained / $score_possible * 100), 2);
	} else {
		$percentage = 0;
	}
	echo 'percentage=='.$percentage;
	echo 'learners==='.$total_learners;*/
	$total_score = round(($total_score / $total_learners)); 
	if($total_score > 100) {
		$total_score = 100;
	}
	return $total_score.' %';
 }

 /**
 * Displays the title of the course 
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return title of the course
 */
 function get_course_title($course_code) {
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		
	$sql = "SELECT title FROM $course_table WHERE code = '".$course_code."'";	
	$res = Database::query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_array($res)) {
		$course_name = $row['title'];
	}
	return $course_name;
 }

 /**
 * Displays the list of modules in all courses of the platform
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return array of modules of all courses
 */
 function get_modules_list($course_search = '', $trainer_id = 0, $session_id = 0, $module_search = '',$action = '') {
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$sql_check = 'N';			

	if(isset($course_search) && !empty($course_search)) {
		$sqlSearch_if = " WHERE (code LIKE '".$course_search."%' OR title LIKE '".$course_search."%' OR tutor_name LIKE '".$course_search."%')";
		$sqlSearch_else = " AND (c.code LIKE '".$course_search."%' OR c.title LIKE '".$course_search."%' OR c.tutor_name LIKE '".$course_search."%')";		
	}
	
	if(api_is_allowed_to_edit()){
		$sql = "SELECT code, title, db_name FROM $course_table c";	
		if($trainer_id == 0 && $session_id == 0) {
			$sql .= $sqlSearch_if;
		}
		else {
			$sql_check = 'N';
			if(!empty($trainer_id) && !empty($session_id)){
				$sql .= " , $course_user_table cr, $session_course_table scr WHERE c.code = cr.course_code AND c.code = scr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1) AND scr.id_session = ".$session_id;
				$sql_check = 'Y';
			}
			else if(!empty($trainer_id) && $sql_check == 'N'){
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1)";
			}
			else if(!empty($session_id) && $sql_check == 'N'){
				$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
			}
			$sql .= $sqlSearch_else;				
		}
		$sql .= " ORDER BY c.title";
	}
	else {
		$sql = "SELECT code, title, db_name FROM $course_table c";
		
		if(api_is_allowed_to_create_course()){
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$session_id;
			}
		}
		else {
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$session_id;
			}				
		}	
		$sql .= $sqlSearch_else;
		$sql .= " ORDER BY title";
	}
	if(!empty($course_search)){
	Database::query("SET NAMES 'utf8'");
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$modules = array();
	while ($row = Database::fetch_array($res, 'ASSOC')) {
		$db_name = $row['db_name'];	
		$code = $row['code'];

		$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $db_name);					
		
		$sql_module = "SELECT id, name FROM $t_lp";
		if(!empty($module_search)){
		$sql_module .= " WHERE name LIKE '".$module_search."%'";		
		}
		$sql_module .= " ORDER BY name";
		
		//if($action != 'export'){
		Database::query("SET NAMES 'utf8'");
		//}

		$res_module = Database::query($sql_module, __FILE__, __LINE__);
		while($module = Database::fetch_array($res_module, 'ASSOC')) {
			$modules[] = $code.'@'.$module['id'].'@'.$module['name'];
		}				
	}

	return $modules;
 }

 function get_limited_modules($page1 = 0, $limit = 0, $modules) {
	$limited_module = array();
	
	$midvalue = $page1 + $limit;
	for($i = $page1; ($i < $midvalue && $i < sizeof($modules)); $i++) {		
		$limited_module[] = $modules[$i];
	}
	return $limited_module;
 }

 function get_modules_list1($page1 = 0, $limit = 0) {
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		
	$sql = "SELECT code, title, db_name FROM $course_table WHERE visibility = 1";	
	$res = Database::query($sql, __FILE__, __LINE__);
	$modules = array();
	while ($row = Database::fetch_array($res, 'ASSOC')) {
		$db_name = $row['db_name'];	
		$code = $row['code'];

		$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $db_name);	

		$sql_module = "SELECT id, name FROM $t_lp ORDER BY name";
		$res_module = Database::query($sql_module, __FILE__, __LINE__);
		while($module = Database::fetch_array($res_module, 'ASSOC')) {
			$modules[$code][$module['id']] = $module['name'];
		}			
	}
	return $modules;
 }

 /**
 * Displays the total time spent in the paritcular module of the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , module name by default is zero
 *
 * @return total time spent in particular modules of the course
 */
 function get_module_time($course_code, $lp_id) {

	$info_course = CourseManager :: get_course_information($course_code);
	
	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);
	$t_lpiv = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $info_course['db_name']);
	
	$sql_time = 'SELECT SUM(total_time)	FROM ' . $t_lpiv . ' AS item_view
										INNER JOIN ' . $t_lpv . ' AS view
										ON item_view.lp_view_id = view.id
										AND view.lp_id = ' . $lp_id;	
	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()) {
		$sql_time .= ' AND view.user_id = '.api_get_user_id();
	}
	$rs = Database::query($sql_time, __FILE__, __LINE__);
	$total_time = 0;
	if (Database :: num_rows($rs) > 0) {
		$total_time = Database :: result($rs, 0, 0);					
	}
		
	return display_time_format($total_time);
 }

 /**
 * Displays the module progress of the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , module id 
 *
 * @return progress of a module in percentage
 */
 function get_module_progress($course_code, $lp_id, $total_learners) {

	$info_course = CourseManager :: get_course_information($course_code);
	
	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);

	$learners = learners_list($course_code);
			
	if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()) {
	$sql_progress = "SELECT progress FROM $t_lpv WHERE lp_id = $lp_id AND user_id IN ('".implode("','",$learners)."')";
	}
	else {
	$sql_progress = "SELECT progress FROM $t_lpv WHERE lp_id = $lp_id AND user_id = ".api_get_user_id();
	$total_learners = 1;
	}
	$res_progress = Database::query($sql_progress, __FILE__, __LINE__);
	$sum_progress = 0;
	while($row_progress = Database :: fetch_array($res_progress)){

		$sum_progress = $sum_progress + $row_progress['progress'];
	}
	
	$final_progress = round(($sum_progress / $total_learners));

	return $final_progress.'%';
 }

 /**
 * Displays the score of a particular module
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , number of learners in the course
 *
 * @return score of the module in percentage
 */
 function get_module_score($course_code, $lp_id, $total_learners) {

	$info_course = CourseManager :: get_course_information($course_code);

	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);	
	$lp_item_table  = Database :: get_course_table(TABLE_LP_ITEM, $info_course['db_name']);
			
	if($total_learners == 0) {
		$final_score = "n.a";
		return $final_score;
	}

	$learners = learners_list($course_code);

	$sql = "SELECT count(id) as count FROM $lp_item_table WHERE (item_type = 'quiz' OR item_type = 'sco') AND lp_id = ".$lp_id;
	if ($debug) echo $sql;
	$result_have_quiz = Database::query($sql);

	if (Database::num_rows($result_have_quiz) > 0 ) {
		$row = Database::fetch_array($result_have_quiz,'ASSOC');
		if (is_numeric($row['count']) && $row['count'] != 0) {
			$lp_with_quiz ++;
		}
	}

	if($lp_with_quiz <= 0){
		$final_score = "n.a";
		return $final_score;
	}

	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()) {
		$learners = array();
		$learners[] = api_get_user_id();
		$total_learners = 1;
	}
	
	$sum_student_score = 0;
	foreach($learners as $user_id) {		
		$student_score = Tracking :: get_avg_student_score($user_id, $course_code, array ($lp_id));
		$sum_student_score = $sum_student_score + $student_score;
	}
	
	$avg_student_score = round(($sum_student_score / $total_learners)) ;

	return $avg_student_score.' %';
 }

 /**
 * Displays the list of quizzes in all courses of the platform
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return array of quizzes of all courses
 */
 function get_quiz_list($quiztype = 0, $session_id = 0, $search = '', $trainer_id = 0, $course_search = '', $action = '') {
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$sql_check = 'N';	
		
	if(isset($course_search) && !empty($course_search)) {
		$sqlSearch_if = " WHERE (code LIKE '".$course_search."%' OR title LIKE '".$course_search."%' OR tutor_name LIKE '".$course_search."%')";
		$sqlSearch_else = " AND (c.code LIKE '".$course_search."%' OR c.title LIKE '".$course_search."%' OR c.tutor_name LIKE '".$course_search."%')";
	}
	
	if(api_is_allowed_to_edit()){
		$sql = "SELECT code, title, db_name FROM $course_table c";	
		if($trainer_id == 0 && $session_id == 0) {
			$sql .= $sqlSearch_if;
		}
		else {
			$sql_check = 'N';
			if(!empty($trainer_id) && !empty($session_id)){
				$sql .= " , $course_user_table cr, $session_course_table scr WHERE c.code = cr.course_code AND c.code = scr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1) AND scr.id_session = ".$session_id;
				$sql_check = 'Y';
			}
			else if(!empty($trainer_id) && $sql_check == 'N'){
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1)";
			}
			else if(!empty($session_id) && $sql_check == 'N'){
				$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
			}
			$sql .= $sqlSearch_else;				
		}
		$sql .= " ORDER BY c.title";
	}
	else {
		$sql = "SELECT code, title, db_name FROM $course_table c";
		
		if(api_is_allowed_to_create_course()){
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$session_id;
			}
		}
		else {
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$session_id;
			}				
		}	
		$sql .= $sqlSearch_else;
		$sql .= " ORDER BY title";
	}

	if(!empty($course_search) && $action != 'export' && $action != 'print'){
	Database::query("SET NAMES 'utf8'");
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$quizzes = array();
	while ($row = Database::fetch_array($res, 'ASSOC')) {
		$db_name = $row['db_name'];	
		$code = $row['code'];

		$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $db_name);	
		$where_added = 0;
		$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0 AND active <> -1";
		if($quiztype <> 0){
			$sql_quiz .= " AND quiz_type = ".$quiztype;
		}
		if($session_id <> 0) {
			$sql_quiz .= " OR session_id = ".$session_id;
		}
		if(!empty($search)) {
			$sql_quiz .= " AND title LIKE '".$search."%'";
		}
		
		$sql_quiz .= " ORDER BY title";

		if($action != 'export'){
		Database::query("SET NAMES 'utf8'");
		}
		$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
		while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
			//$modules[$code][$module['id']] = $module['name'];
			$quizzes[] = $code.'@'.$quiz['id'].'@'.$quiz['title'];
		}	
	}

	return $quizzes;
 }

 /**
 * Displays the quiz average score
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return quiz average score
 */
 function get_quiz_average_score($quiz_id, $code, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$track_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

	if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
	$sql = "SELECT DISTINCT(exe_user_id) AS user_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_users = Database::num_rows($res);
	$users = array();
	if($num_users == 0) {
		return "/";
	}
	else {
		while($row = Database::fetch_array($res)) {
			$users[] = $row['user_id'];
		}
	}
	}
	else {
		$users = array();
		$users[] = api_get_user_id();
		$num_users = 1;
	}
	$score = 0;
	foreach($users as $user_id) {
		$sum_marks = 0;
		$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_id DESC LIMIT 1";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$exe_result = $row['exe_result'];
			$exe_weighting = $row['exe_weighting'];
			$exe_id = $row['exe_id'];		
                        if (!empty($row['exam_id'])) {
                            $score = $score + round($row['manual_exe_result'], 2);
							//As Quiz is added in evaluation tool - quiz weighting is 100
							$score = round(($score / 100),2);
                        }
                        else {
                            $sql_attempt = "SELECT DISTINCT(question_id),marks AS exe_result FROM $track_attempt WHERE exe_id = ".$exe_id;
                            $res_attempt = Database::query($sql_attempt, __FILE__,__LINE__);
                            while($row_attempt = Database::fetch_array($res_attempt)) {
                                    $sum_marks = $sum_marks + $row_attempt['exe_result'];
                            }		
                            $score = $score + round((round($sum_marks) / $exe_weighting),2);
                        }                        			
		}
	}
	
	$avg_score = round(($score / $num_users)*100);
	
	return $avg_score.' %';
 }

 function get_quiz_attempts($quiz_id, $code, $user_id, $exe_id = 0, $session_id = 0) {

	 $track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

	 $sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id;
	 if($exe_id <> 0) {
		 $sql .= " AND exe_id = ".$exe_id;
	 }
	 $sql .= " ORDER BY exe_id";

	 $res = Database::query($sql, __FILE__, __LINE__);
	 $num_rows = Database::num_rows($res);

	 return $num_rows;
 }

 /**
 * Displays the highest quiz score
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return highest quiz score
 */
 function get_highest_score($quiz_id, $code, $session_id = 0) {

	 $track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

	 if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
	 $sql = "SELECT max(exe_result / exe_weighting) AS highest FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
	 }
	 else {
	 $sql = "SELECT max(exe_result / exe_weighting) AS highest FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".api_get_user_id();
	 }
	 $res = Database::query($sql, __FILE__, __LINE__);
	
	 $highest = Database::result($res, 0, 0);
	 if(is_null($highest))
		 return "/";
	 $highest_score = round(($highest * 100));

	 return $highest_score.' %';
 }

 /**
 * Displays the lowest quiz score
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return lowest quiz score
 */
 function get_lowest_score($quiz_id, $code, $session_id = 0) {

	 $track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

	 if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
            $sql = "SELECT min(exe_result / exe_weighting) AS highest FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
	 }
	 else {
            $sql = "SELECT min(exe_result / exe_weighting) AS highest FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".api_get_user_id();
	 }
	 $res = Database::query($sql, __FILE__, __LINE__);
	
	 $lowest = Database::result($res, 0, 0);
	 if(is_null($lowest))
		 return "/";
	 $lowest_score = round(($lowest * 100));

	 return $lowest_score.' %';
 }

 /**
 * Displays how many learners took the quiz
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return no of learners taken the quiz
 */
 function get_quiz_participants($quiz_id, $code, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);

	$sql = "SELECT DISTINCT(exe_user_id) AS user_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_users = Database::num_rows($res);	
	
	return $num_users;
 }

  /**
 * Displays how many learners took the quiz
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return no of learners taken the quiz
 */
 function get_average_time($quiz_id, $code, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	
	$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_users = Database::num_rows($res);	

	if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()) {
	$sql = "SELECT SUM(UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
	}
	else {
	$sql = "SELECT SUM(UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id =".api_get_user_id();
	$num_users = 1;
	}
	$res = Database::query($sql, __FILE__, __LINE__);
	$sum_time = Database::result($res,0,0);

	$avg_time = round(($sum_time / $num_users),2);
	
	return display_time_format($avg_time);
 }

 function get_quiz_total_time($quiz_id, $code, $user_id, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);	
	
	$sql = "SELECT SUM(UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date)) AS nbseconds FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$sum_time = Database::result($res,0,0);	
	
	return display_time_format($sum_time);
 }

 function get_quiz_total_weight($quiz_id, $user_id, $code, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$track_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
	$total_score = 0;

	$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_id DESC LIMIT 1";
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);
	
	if($num_rows <> 0) {
                while($row = Database::fetch_array($res)) {
                    $exe_id = $row['exe_id'];
                    $total_score = $row['exe_weighting'];
                }
	}
	
	return round($total_score);
 }

 function get_limited_quizzes($page1 = 0, $limit = 0, $quizzes) {

	$limited_quiz = array();
	
	$midvalue = $page1 + $limit;
	for($i = $page1;($i < $midvalue && $i < sizeof($quizzes)); $i++) {
		
		$limited_quiz[] = $quizzes[$i];
	}
	return $limited_quiz;
 }

 /**
 * Displays the list of quizzes in all courses of the platform
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return array of quizzes of all courses
 */
 function get_facetoface_list($search = '', $trainer_id = 0) {
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$sql_check = 'N';	
	$session_id = 0;
		
	if(isset($course_search) && !empty($course_search)) {
		$sqlSearch_if = " WHERE (code LIKE '".$course_search."%' OR title LIKE '".$course_search."%' OR tutor_name LIKE '".$course_search."%')";
		$sqlSearch_else = " AND (c.code LIKE '".$course_search."%' OR c.title LIKE '".$course_search."%' OR c.tutor_name LIKE '".$course_search."%')";
	}
	
	if(api_is_allowed_to_edit()){
		$sql = "SELECT code, title, db_name FROM $course_table c";	
		if($trainer_id == 0 && $session_id == 0) {
			$sql .= $sqlSearch_if;
		}
		else {
			$sql_check = 'N';
			if(!empty($trainer_id) && !empty($session_id)){
				$sql .= " , $course_user_table cr, $session_course_table scr WHERE c.code = cr.course_code AND c.code = scr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1) AND scr.id_session = ".$session_id;
				$sql_check = 'Y';
			}
			else if(!empty($trainer_id) && $sql_check == 'N'){
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1)";
			}
			else if(!empty($session_id) && $sql_check == 'N'){
				$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
			}
			$sql .= $sqlSearch_else;				
		}
		$sql .= " ORDER BY c.title";
	}
	else {
		$sql = "SELECT code, title, db_name FROM $course_table c";
		
		if(api_is_allowed_to_create_course()){
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$session_id;
			}
		}
		else {
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$session_id;
			}				
		}	
		$sql .= $sqlSearch_else;
		$sql .= " ORDER BY title";
	}
	
	if(!empty($course_search) && $action != 'export' && $action != 'print'){
	Database::query("SET NAMES 'utf8'");
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$facetofaces = array();
	while ($row = Database::fetch_array($res, 'ASSOC')) {
		$db_name = $row['db_name'];	
		$code = $row['code'];

		$table_facetoface = Database :: get_course_table(TABLE_FACE_2_FACE, $db_name);			
		$sql_facetoface = "SELECT id, name FROM $table_facetoface WHERE session_id = 0";
		if(!empty($search)){
			$sql_facetoface .= " AND name LIKE '".$search."%'";
		}
		$sql_facetoface .= " ORDER BY name";

		if($action != 'export'){
		Database::query("SET NAMES 'utf8'");
		}

		$res_facetoface = Database::query($sql_facetoface, __FILE__, __LINE__);
		while($facetoface = Database::fetch_array($res_facetoface, 'ASSOC')) {
			//$modules[$code][$module['id']] = $module['name'];
			$facetofaces[] = $code.'@'.$facetoface['id'].'@'.$facetoface['name'];
		}	
	}

	return $facetofaces;
 }

 function get_facetoface_maxscore($facetoface_id, $code, $session_id){

	$info_course = CourseManager :: get_course_information($code);

	$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $info_course['db_name']);
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $info_course['db_name']);
	$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);

	$sql = "SELECT max(score) as max_score FROM $TBL_SCENARIO_ACTIVITY_VIEW view, $TBL_SCENARIO_ACTIVITY act WHERE view.activity_id = act.id AND act.activity_ref = ".$facetoface_id." AND act.activity_type = 'face2face'";
	$res = Database::query($sql, __FILE__, __LINE__);
	$max_score = Database::result($res,0,0);
	
	return $max_score;
 }

 function get_facetoface_minscore($facetoface_id, $code, $session_id){

	$info_course = CourseManager :: get_course_information($code);

	$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $info_course['db_name']);
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $info_course['db_name']);
	$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);

	$sql = "SELECT min(score) as min_score FROM $TBL_SCENARIO_ACTIVITY_VIEW view, $TBL_SCENARIO_ACTIVITY act WHERE view.activity_id = act.id AND act.activity_ref = ".$facetoface_id." AND act.activity_type = 'face2face'";
	$res = Database::query($sql, __FILE__, __LINE__);
	$min_score = Database::result($res,0,0);
	
	return $min_score;
 }

 function get_facetoface_score($user_id, $ff_id, $course_code) {

	$info_course = CourseManager :: get_course_information($course_code);

	$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $info_course['db_name']);
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $info_course['db_name']);
	$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);

	$sql = "SELECT score FROM $TBL_SCENARIO_ACTIVITY_VIEW view, $TBL_SCENARIO_ACTIVITY act WHERE view.activity_id = act.id AND act.activity_ref = ".$ff_id." AND view.user_id = ".$user_id;

	$res = Database::query($sql, __FILE__, __LINE__);
	$score = Database::result($res,0,0);
	
	return $score;
 }

 /**
 * Displays the list of users in the platform
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * @return array of users
 */
 function get_users_list($status = 1, $search = '', $trainer_id = 0, $course_search = '', $page1 = 0, $limit = 0, $action = '') {
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$sql_check = 'N';
 
	if(isset($course_search) && !empty($course_search)) {
		$sqlSearch_if = " WHERE (code LIKE '".$course_search."%' OR title LIKE '".$course_search."%' OR tutor_name LIKE '".$course_search."%')";
		$sqlSearch_else = " AND (c.code LIKE '".$course_search."%' OR c.title LIKE '".$course_search."%' OR c.tutor_name LIKE '".$course_search."%')";
	}
	
	if(api_is_allowed_to_edit()){
		$sql = "SELECT code, title, db_name FROM $course_table c";	
		if($trainer_id == 0 && $session_id == 0) {
			$sql .= $sqlSearch_if;
		}
		else {
			$sql_check = 'N';
			if(!empty($trainer_id) && !empty($session_id)){
				$sql .= " , $course_user_table cr, $session_course_table scr WHERE c.code = cr.course_code AND c.code = scr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1) AND scr.id_session = ".$session_id;
				$sql_check = 'Y';
			}
			else if(!empty($trainer_id) && $sql_check == 'N'){
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$trainer_id." AND (cr.status = 1 OR cr.tutor_id = 1)";
			}
			else if(!empty($session_id) && $sql_check == 'N'){
				$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
			}
			$sql .= $sqlSearch_else;				
		}
		$sql .= " ORDER BY c.title";
	}
	else {
		$sql = "SELECT code, title, db_name FROM $course_table c";
		
		if(api_is_allowed_to_create_course()){
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$session_id;
			}
		}
		else {
			if($session_id == 0) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title, c.db_name FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$session_id;
			}				
		}	
		$sql .= $sqlSearch_else;
		$sql .= " ORDER BY title";
	}
	if(!empty($course_search)){
	Database::query("SET NAMES 'utf8'");
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$users = array();
	$unique_users = array();	
	
	while($row = Database::fetch_array($res)) {
		$sql_user = "SELECT u.user_id, lastname, firstname FROM $user_table u,$course_user_table cr WHERE u.user_id = cr.user_id AND cr.course_code = '".$row['code']."' AND u.active = ".$status;
			if(!empty($search)){
				$sql_user .= " AND (lastname LIKE '".$search."%' OR firstname LIKE '".$search."%' OR username LIKE '".$search."%')";
			}
			$sql_user .= " ORDER BY lastname";
			if($limit <> 0) {
			$sql_user .= " LIMIT $page1,$limit";
			}	
			//if($action != 'export'){
			Database::query("SET NAMES 'utf8'");
			//}

			$res_user = Database::query($sql_user, __FILE__, __LINE__);		
			while ($user = Database::fetch_array($res_user, 'ASSOC')) {						
				$users[] = $user;					
			}
	}

	$unique_users = unique_sort($users);
	//print_r($unique_users);
	return $unique_users;
 }

 /*function for get unique value then sort them*/

function unique_sort($arrs) {
    $unique_arr = array();
    foreach ($arrs AS $key => $arr) {

        if (!in_array($arr['user_id'], $unique_arr)) {
            $unique_arr[] = $arr['user_id'];
        }
		else {	
			unset($arrs[$key]);
		}
    }
    //sort($unique_arr);
    return $arrs;
}

 /**
 * Displays the total time spent in the modules of the course
 * 
 * @author Breetha Mohan  <breetha@dokeos.net>
 * 
 * param course code , session_id by default is zero
 *
 * @return total time spent in all the modules of the course
 */
 function user_modules_time($user_id, $course_code, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);

	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);
	$t_lpiv = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $info_course['db_name']);

	$sql = "SELECT id FROM $t_lp WHERE session_id IN (0, ".$session_id.") ORDER BY name";
	$res = Database::query($sql, __FILE__, __LINE__);
	$sum_time = 0;
	while($row = Database::fetch_array($res)) {
		$lp_id = $row['id'];
		$sql_time = 'SELECT SUM(total_time)	FROM ' . $t_lpiv . ' AS item_view
											INNER JOIN ' . $t_lpv . ' AS view
											ON item_view.lp_view_id = view.id
											AND view.lp_id = ' . $lp_id .'
											AND view.user_id = '.$user_id;

		$rs = Database::query($sql_time, __FILE__, __LINE__);
		$total_time = 0;
		if (Database :: num_rows($rs) > 0) {
			$total_time = Database :: result($rs, 0, 0);					
		}
		$sum_time = $sum_time + $total_time;
	}
	
	return $sum_time;
 }

 function specific_modules_time($user_id, $course_code, $lp_id, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);

	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);
	$t_lpiv = Database :: get_course_table(TABLE_LP_ITEM_VIEW, $info_course['db_name']);

	$sql_time = 'SELECT SUM(total_time)	FROM ' . $t_lpiv . ' AS item_view
										INNER JOIN ' . $t_lpv . ' AS view
										ON item_view.lp_view_id = view.id
										AND view.lp_id = ' . $lp_id .'
										AND view.user_id = '.$user_id;

	$rs = Database::query($sql_time, __FILE__, __LINE__);
	$total_time = 0;
	if (Database :: num_rows($rs) > 0) {
		$total_time = Database :: result($rs, 0, 0);					
	}
	
	return $total_time;
 }

 function get_search_coursecodes($course_search){
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);

	$sql = "SELECT code FROM $course_table WHERE code LIKE '%".$course_search."%' OR title LIKE '%".$course_search."%' OR tutor_name LIKE '%".$course_search."%'";
	Database::query("SET NAMES 'utf8'");
	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		$search_codes[] = $row['code'];
	}
	return $search_codes;
 }

 function get_time_spent($user_id, $course_code = 0, $session_id = 0, $course_search = '') {
	
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$course_codes = array();
	$search_codes = array();
	$sum_time = 0;

	if(empty($session_id)) {
		$session_id = 0;
	}

	if(!empty($course_search)){
		$search_codes = get_search_coursecodes($course_search);		
	}

	if($session_id == 0) {	
		$sql = "SELECT DISTINCT(course_code) FROM $course_user_table cru WHERE cru.user_id = '".$user_id."'";
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
		$sql .= " UNION DISTINCT SELECT DISTINCT(course_code) FROM $session_course_user_table srcu WHERE  srcu. id_user =".$user_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}
	else {
		$sql = "SELECT DISTINCT(course_code) FROM $session_course_user_table WHERE id_session = ".$session_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		$course_codes[] = $row['course_code'];

		//user_modules_time($user_id, $course_code, );
	}
	if($course_code == '0') {
		foreach($course_codes as $code) {
			$time = user_modules_time($user_id, $code, $session_id);
			$sum_time = $sum_time + $time;
		}
		//$sum_time = round(($sum_time / sizeof($course_codes)),2);
	}
	else {
		$sum_time = user_modules_time($user_id, $course_code, $session_id);
	}

	return display_time_format($sum_time);
 }

 function get_student_progress($user_id, $course_code = 0, $session_id = 0, $course_search = '') {
	
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$course_codes = array();
	$sum_progress = 0;
	$module_count = 0;

	if(empty($session_id)) {
		$session_id = 0;
	}

	if(!empty($course_search)){
		$search_codes = get_search_coursecodes($course_search);		
	}

	if($session_id == 0) {	
		$sql = "SELECT DISTINCT(course_code) FROM $course_user_table cru WHERE cru.user_id = '".$user_id."'";
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
		$sql .= " UNION DISTINCT SELECT DISTINCT(course_code) FROM $session_course_user_table srcu WHERE  srcu. id_user =".$user_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}
	else {
		$sql = "SELECT DISTINCT(course_code) FROM $session_course_user_table WHERE id_session = ".$session_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		$course_codes[] = $row['course_code'];

		//user_modules_time($user_id, $course_code, );
	}
	
	if($course_code == '0') {
		foreach($course_codes as $code) {
			$count = count_modules($user_id, $code, $session_id);
			$progress = user_modules_progress($user_id, $code, $session_id);
			$sum_progress = $sum_progress + $progress;
			$module_count = $module_count + $count;
		}
				
	}
	else {		
		$module_count = count_modules($user_id, $course_code, $session_id);
		$sum_progress = user_modules_progress($user_id, $course_code, $session_id);
	}

	$sum_progress = round(($sum_progress / $module_count));
	return $sum_progress.' %';
 }

 function count_modules($user_id, $course_code, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);
	
	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	
	if(empty($session_id)) {
		$session_id = 0;
	}
			
	$sql = "SELECT id FROM $t_lp WHERE session_id IN (0, $session_id) ORDER BY name";
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_modules = Database::num_rows($res);

	return $num_modules;
 }

 function user_modules_progress($user_id, $course_code, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);
	
	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);

	if(empty($session_id)) {
		$session_id = 0;
	}
			
	$sql = "SELECT id FROM $t_lp WHERE session_id IN (0, $session_id) ORDER BY name";
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_modules = Database::num_rows($res);
	$sum_progress = 0;
	while($row = Database::fetch_array($res)) {
		$lp_id = $row['id'];

		$sql_progress = "SELECT SUM(progress) AS total_progress FROM $t_lpv WHERE lp_id = $lp_id AND user_id =".$user_id;
		$res_progress = Database::query($sql_progress, __FILE__, __LINE__);		
		$row_progress = Database :: fetch_array($res_progress);

		$sum_progress = $sum_progress + $row_progress['total_progress'];

	}
	
	//$final_progress = round(($sum_progress / $num_modules),2);

	return $sum_progress;
 }

 function specific_modules_progress($user_id, $course_code, $lp_id, $session_id = 0) {

	$info_course = CourseManager :: get_course_information($course_code);
	
	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);
	$t_lpv = Database :: get_course_table(TABLE_LP_VIEW, $info_course['db_name']);

	if(empty($session_id)) {
		$session_id = 0;
	}
			
	$sql_progress = "SELECT SUM(progress) AS total_progress FROM $t_lpv WHERE lp_id = $lp_id AND user_id =".$user_id;
	$res_progress = Database::query($sql_progress, __FILE__, __LINE__);		
	$row_progress = Database :: fetch_array($res_progress);

	//$sum_progress = $sum_progress + $row_progress['total_progress'];

	return $row_progress['total_progress'];
 }

 function get_student_score($user_id, $course_code = 0, $session_id = 0, $course_search = '') {
	
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$course_codes = array();
	$sum_score = 0;
	$module_count = 0;

	if(empty($session_id)) {
		$session_id = 0;
	}

	if(!empty($course_search)){
		$search_codes = get_search_coursecodes($course_search);		
	}	

	if($session_id == 0) {	
		$sql = "SELECT DISTINCT(course_code) FROM $course_user_table cru WHERE cru.user_id = '".$user_id."'";
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
		$sql .= " UNION DISTINCT SELECT DISTINCT(course_code) FROM $session_course_user_table srcu WHERE  srcu. id_user =".$user_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}
	else {
		$sql = "SELECT DISTINCT(course_code) FROM $session_course_user_table WHERE id_session = ".$session_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		
			$course_codes[] = $row['course_code'];
		
		//user_modules_time($user_id, $course_code, );
	}
	if($course_code == '0') {

		foreach($course_codes as $code) {	
			$count = count_modules($user_id, $code, $session_id);
			$student_score = Tracking :: get_average_test_scorm_and_lp($user_id, $code);
			$sum_score = $sum_score + $student_score;
			$module_count = $module_count + $count;
		}
	//	$sum_score = round(($sum_score / sizeof($course_codes)),2);
	}
	else {	
		
		$module_count = count_modules($user_id, $course_code, $session_id);
		$sum_score = Tracking :: get_average_test_scorm_and_lp($user_id, $course_code);
	}

	$sum_score = round(($sum_score / $module_count));

	return $sum_score.' %';
 }

 function get_student_quiz_score($user_id, $course_code = 0, $session_id = 0, $course_search = '') {
	
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$course_codes = array();
	$sum_score = 0;
	$no_quiz = 'Y';
	$count_quiz = 0;
	
	if(empty($session_id)) {
		$session_id = 0;
	}

	if(!empty($course_search)){
		$search_codes = get_search_coursecodes($course_search);		
	}

	if($session_id == 0) {			
		$sql = "SELECT DISTINCT(course_code) FROM $course_user_table cru WHERE cru.user_id = '".$user_id."'";
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
		$sql .= " UNION DISTINCT SELECT DISTINCT(course_code) FROM $session_course_user_table srcu WHERE  srcu. id_user =".$user_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}
	else {		
		$sql = "SELECT DISTINCT(course_code) FROM $session_course_user_table WHERE id_session = ".$session_id;
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
	}
	
	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		$course_codes[] = $row['course_code'];		
	}

	if($course_code == '0') {

		if(sizeof($course_codes) == 0) {
			return '/';
		}
		else {
			foreach($course_codes as $code) {

				$quiz_exist = is_quiz_present($code, $session_id);
				if($quiz_exist) {
					$no_quiz = 'N';
					$quizzes = get_all_quiz($code, $session_id);
					foreach($quizzes as $quiz_id) {
						$quiz_score = get_score($quiz_id, $user_id, $code, $session_id);
						$sum_score = $sum_score + $quiz_score;
					}
					//$sum_score = $sum_score / sizeof($quizzes);
					$count_quiz = $count_quiz + sizeof($quizzes);
				}
			}

			  $sum_score = round(($sum_score / $count_quiz)*100);
			//$sum_score = round(($sum_score / sizeof($course_codes))*100,2);
		}
	}
	else {		

		$quiz_exist = is_quiz_present($course_code, $session_id);
			if($quiz_exist) {
				$no_quiz = 'N';
				$quizzes = get_all_quiz($course_code, $session_id);
				foreach($quizzes as $quiz_id) {					
					$quiz_score = get_score($quiz_id, $user_id, $course_code, $session_id);
					$sum_score = $sum_score + $quiz_score;
				}

				$sum_score = round(($sum_score / sizeof($quizzes))*100);
			}
			else {
				return '/';
			}
	}

	return $sum_score.' %';
 }
 
 function is_quiz_present($code, $session_id = 0) {
	 $info_course = CourseManager :: get_course_information($code);
	
	 $tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);
	 $sql = "SELECT * FROM $tbl_quiz WHERE session_id = 0";
	 if($session_id <> 0) {
		 $sql .= " OR session_id = ".$session_id;
	 }
	 $res = Database::query($sql, __FILE__, __LINE__);
	 $num_rows = Database::num_rows($res);

	 if($num_rows == 0) {
		 return false;
	 }
	 else {
		 return true;
	 }
 }

 function get_all_quiz($code, $session_id = 0) {
	 $info_course = CourseManager :: get_course_information($code);
	
	 $tbl_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);
	 $quizzes = array();
	 $sql = "SELECT * FROM $tbl_quiz WHERE session_id = 0";
	 if($session_id <> 0) {
		 $sql .= " OR session_id = ".$session_id;
	 }
	 $res = Database::query($sql, __FILE__, __LINE__);
	 $num_rows = Database::num_rows($res);
	 while($row = Database::fetch_array($res,"ASSOC")) {
		 $quizzes[] = $row['id'];
	 }
	 return $quizzes;
 }

 function get_score($quiz_id, $user_id, $code, $session_id = 0) {
	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$track_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
	$score = 0;
	$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_id DESC LIMIT 1";
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);	
	if($num_rows <> 0) {
                while($row = Database::fetch_array($res)) {
                    $exe_id = $row['exe_id'];
                    $exe_weighting = $row['exe_weighting'];
                }                
                if (!empty($row['exam_id'])) {
                    $score = $score + round($row['manual_exe_result'], 2);
                }
                else {
                    $sql_attempt = "SELECT DISTINCT(question_id),marks AS exe_result FROM $track_attempt WHERE exe_id = ".$exe_id;
                    $res_attempt = Database::query($sql_attempt, __FILE__,__LINE__);
                    while($row_attempt = Database::fetch_array($res_attempt)) {
                        $sum_marks = $sum_marks + $row_attempt['exe_result'];
                    }
                    $score = $score + round(($sum_marks / $exe_weighting),2);
                }
	}
	else {
		$score = '/';
	}
	return $score;
 }

 function get_limited_users($page1 = 0, $limit = 0, $users) {
	$limited_users = array();
	
	$midvalue = $page1 + $limit;
	for($i = $page1; ($i < $midvalue && $i < sizeof($users)); $i++) {		
		$limited_users[] = $users[$i];
	}
	return $limited_users;
 }

 function get_user_quiz_score($user_id, $quiz_id, $code, $latest_attempt = 2, $exe_id = 0, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$track_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
	$score = 0;

	if($latest_attempt == 2) {
		$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_id DESC LIMIT 1";
	}
	else if($latest_attempt == 1) {
		//$sql = "SELECT MAX(exe_result) AS exe_result, exe_weighting FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND exe_user_id = ".$user_id;
		$sql = "SELECT exe_result, exe_weighting, exe_id, manual_exe_result, exam_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_result DESC LIMIT 1";
	}
	else {
		$sql = "SELECT exe_result, exe_weighting, exe_id, manual_exe_result, exam_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." AND exe_id = ".$exe_id;
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);
	
	if($num_rows <> 0) {
	while($row = Database::fetch_array($res)) {
		$exe_result = $row['exe_result'];
		$exe_weighting = $row['exe_weighting'];
		$exe_id = $row['exe_id'];
	}
        
            if (!empty($row['exam_id'])) {
                $score = $score + round($row['manual_exe_result'], 2);
            }
            else {
                $sql_attempt = "SELECT DISTINCT(question_id),marks AS exe_result FROM $track_attempt WHERE exe_id = ".$exe_id;
		$res_attempt = Database::query($sql_attempt, __FILE__,__LINE__);
		while($row_attempt = Database::fetch_array($res_attempt)) {
			$sum_marks = $sum_marks + $row_attempt['exe_result'];
		}	
                $score = $score + round((round($sum_marks) / $exe_weighting),2);
            }
	}
	else {
		$score = '/';
	}

	return $score;
 }

 function get_user_quiz_time($quiz_id, $code, $user_id, $latest_attempt = 2, $exe_id = 0, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);	
	
	if($latest_attempt == 2) {
	$sql = "SELECT UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date) AS nbseconds FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_id DESC LIMIT 1";
	}
	else if($latest_attempt == 1) {
	$sql_sel = "SELECT exe_result, exe_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_result DESC LIMIT 1";
	$res_sel = Database::query($sql_sel, __FILE__, __LINE__);
	$exeid = Database::result($res_sel,0,1);

	$sql = "SELECT UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date) AS nbseconds FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." AND exe_id = ".$exeid;
	}
	else {
	$sql = "SELECT UNIX_TIMESTAMP(exe_date)-UNIX_TIMESTAMP(start_date) AS nbseconds FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." AND exe_id = ".$exe_id;
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$sum_time = Database::result($res,0,0);	
	
	return display_time_format($sum_time);
 }

 function get_exe_id($user_id, $quiz_id, $code, $latest_attempt = 2, $exe_id = 0, $session_id = 0) {

	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$score = 0;

	if($latest_attempt == 2) {
		$sql = "SELECT exe_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_id DESC LIMIT 1";
	}
	else if($latest_attempt == 1) {
		$sql = "SELECT exe_result, exe_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." ORDER BY exe_result DESC LIMIT 1";
	}
	else {
		$sql = "SELECT exe_id FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".$user_id." AND exe_id = ".$exe_id;
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);
	
	if($num_rows <> 0) {
		while($row = Database::fetch_array($res)) {
			$exe_id = $row['exe_id'];		
		}	
	}
	
	return $exe_id;
 }

#Export courses list
function exportcourses(){
	$filename = "course_report";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);	
	
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$sqlSearch_if = " WHERE (code LIKE '".$_GET['search']."%' OR title LIKE '".$_GET['search']."%' OR tutor_name LIKE '".$_GET['search']."%')";
		$sqlSearch_else = " AND (c.code LIKE '".$_GET['search']."%' OR c.title LIKE '".$_GET['search']."%' OR c.tutor_name LIKE '".$_GET['search']."%')";
	}

	if(empty($_GET['sessionId']))
		$session_id = 0;
	else 
		$session_id = $_GET['sessionId'];
	
	if(api_is_allowed_to_edit()){
		$sql = "SELECT code, title FROM $course_table c";	
		if($_GET['userid'] == 0 && $session_id == 0) {
			$sql .= $sqlSearch_if;
		}
		else {
			$sql_check = 'N';
			if(!empty($_GET['userid']) && !empty($_GET['sessionId'])){
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = " .$_GET['userid']." AND sc.id_session = ".$session_id;
				$sql_check = 'Y';
			}
			else if(!empty($_GET['userid']) && $sql_check == 'N'){
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$_GET['userid']." AND (cr.status = 1 OR cr.tutor_id = 1)";
			}
			else if(!empty($_GET['sessionId']) && $sql_check == 'N'){

				$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
			}
			$sql .= $sqlSearch_else;				
		}
		$sql .= " ORDER BY c.title";

	}
	else {
		$sql = "SELECT code, title FROM $course_table c";
		
		if(api_is_allowed_to_create_course()){
			if(empty($_GET['sessionId'])) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$_GET['sessionId'];
			}
		}
		else {
			if(empty($_GET['sessionId'])) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$_GET['sessionId'];
			}				
		}	
		$sql .= $sqlSearch_else;
		$sql .= " ORDER BY title";

	}

	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);
	//start of printing column names
	echo dataformat(get_lang('Courses'));
    echo dataformat(get_lang('Learners'));                                        
    echo dataformat(get_lang('ModulesTime'));
    echo dataformat(get_lang('ModulesProgress'));
    echo dataformat(get_lang('ModulesScore'));
    echo dataformat(get_lang('QuizzesScore'));						
	print("\n");
	//end of printing column names
 	if($num_rows==0){
		$schema_insert = "";	
		$schema_insert .= dataformat(get_lang('NoRecords'));
		$schema_insert = str_replace($sep."$", "", $schema_insert);
 		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
	}
	else{
		//start while loop to get data
		while($row = Database::fetch_array($res)) {
			$no_learners = total_numberof_learners($row['code'],$session_id);
			$module_time = total_modules_time($row['code'],$session_id);
			$module_progress = total_modules_progress($row['code'], $no_learners,$session_id);
			$module_score = total_modules_score($row['code'], $no_learners, $session_id);
			$quiz_score = total_quizzes_score($row['code'], $no_learners, $session_id);
			$schema_insert = "";			
			
			$schema_insert .= dataformat($row['title']);
			$schema_insert .= dataformat($no_learners);
			$schema_insert .= dataformat($module_time);
			$schema_insert .= dataformat($module_progress." %");
			$schema_insert .= dataformat($module_score);
			$schema_insert .= dataformat($quiz_score);
		
			$schema_insert = str_replace($sep."$", "", $schema_insert);
			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
			$schema_insert .= "\t";
			print(trim($schema_insert));
			print "\n";
		}	
	}
}

#Export modules list
function exportmodules(){
	$filename = "module_report";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	
	$modules = array();
	
	$user_id = $_GET['userid'];
	if(empty($_GET['sessionId']))
		$session_id = 0;
	else 
		$session_id = $_GET['sessionId'];
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}
	$course_search = checkUTF8($course_search);
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	$course_code = $_GET['courses'];
	if(empty($course_code))
			$course_code = 0;
	if($course_code == '0') {
		$modules = get_modules_list($course_search,$user_id,$session_id,$search);
	}
	else {
		$info_course = CourseManager :: get_course_information($course_code);
		$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

		$sql_module = "SELECT id,name FROM $t_lp";
		if(!empty($search)){
		$sql_module .= " WHERE name LIKE '".$search."%'";		
		}
		$sql_module .= " ORDER BY name";
		Database::query("SET NAMES 'utf8'");
		$res_module = Database::query($sql_module, __FILE__, __LINE__);
		while($module = Database::fetch_array($res_module, 'ASSOC')) {
			$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
		}
	}

	if(!empty($search)) {
		if(api_is_allowed_to_edit()){
			$sql = "SELECT code FROM $course_table";
			$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		else {
			$sql = "SELECT code,title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
			if(api_is_allowed_to_create_course()){
				$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		Database::query("SET NAMES 'utf8'");

		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		if($num_rows <> 0) {			
			$search_course_code = Database::result($res,0,0);
			$course_code = $search_course_code;
			$info_course = CourseManager :: get_course_information($search_course_code);
			$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

			$sql_module = "SELECT id,name FROM $t_lp ORDER BY name";
			$res_module = Database::query($sql_module, __FILE__, __LINE__);
			while($module = Database::fetch_array($res_module, 'ASSOC')) {				
				$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
			}
		}		
	}

	//start of printing column names
	echo dataformat(get_lang('Module'));
    echo dataformat(get_lang('InCourse'));                                        
    echo dataformat(get_lang('Time'));
    echo dataformat(get_lang('Progress'));
    echo dataformat(get_lang('Score'));
	print("\n");
	//end of printing column names
	if(count($modules)==0){
		$schema_insert = "";	
		$schema_insert .= dataformat(get_lang('NoRecords'));
		$schema_insert = str_replace($sep."$", "", $schema_insert);
 		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
	}
	else{
		//start while loop to get data
		foreach($modules as $module1) {	
			list($code,$lp_id,$module_name) = split("@",$module1);
			
			$course_name = api_convert_encoding(get_course_title($code),api_get_system_encoding(),'UTF-8');
						
			$total_learners = total_numberof_learners($code);

			$course_module_time = get_module_time($code, $lp_id);
			$course_module_progress = get_module_progress($code, $lp_id, $total_learners);
			$course_module_score = get_module_score($code, $lp_id, $total_learners);
 			$schema_insert = "";
	 	
	 		$schema_insert .= dataformat(api_convert_encoding($module_name,api_get_system_encoding(),'UTF-8'));
	 		$schema_insert .= dataformat($course_name);
	 		$schema_insert .= dataformat($course_module_time);
	 		$schema_insert .= dataformat($course_module_progress);
			$schema_insert .= dataformat($course_module_score);
		
        	$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}
	}	
}

function dataformat($fieldname){	
 	$data = '';
 	$sep = "\t"; //tabbed character
	if(!isset($fieldname))
        $data = "NULL".$sep;
    elseif ($fieldname != "")
        $data .= "$fieldname".$sep;
    else
        $data .= "0".$sep;	
	
    return '"'.$data.'";';
}

#Print courses list
function printcourses(){
	 
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);	

	$theme_color = get_theme_color();

	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$sqlSearch_if = " WHERE (code LIKE '".$_GET['search']."%' OR title LIKE '".$_GET['search']."%' OR tutor_name LIKE '".$_GET['search']."%')";
		$sqlSearch_else = " AND (c.code LIKE '".$_GET['search']."%' OR c.title LIKE '".$_GET['search']."%' OR c.tutor_name LIKE '".$_GET['search']."%')";
	}
	
	if(empty($_GET['sessionId']))
			$session_id = 0;
		else 
			$session_id = $_GET['sessionId'];
	
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Courses</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
			
		</style>
		<script type="text/javascript" src="js/canvasjs.min.js"></script>
		<link type="text/css" rel="stylesheet" media="print" href="'.api_get_path(WEB_PATH).'main/css/'.api_get_setting('stylesheets').'/print.css" />
	</head>
	<body>';
	if($_GET['userid']!=0){
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT firstname, lastname FROM $user_table WHERE user_id = '$_GET[userid]'";
		$res = Database::query($sql, __FILE__, __LINE__);
		while ($trainer = Database::fetch_array($res, 'ASSOC')) {
			$trainername = $trainer['firstname']."  ".$trainer['lastname'];
		}
	}
	else{
		$trainername = "N/A";
	}
	if($session_id!=0){
		$session_table = Database :: get_main_table(TABLE_MAIN_SESSION);

		$sql = "SELECT name FROM $session_table WHERE id='$session_id'";
		$res = Database::query($sql, __FILE__, __LINE__);
		while ($session = Database::fetch_array($res, 'ASSOC')) {
			$sessionname = $session['name'];
		}
	}
	else{
		$sessionname = "N/A";
	}

	if(api_is_allowed_to_edit()){
		$sql = "SELECT code, title FROM $course_table c";	
		if($_GET['userid'] == 0 && $session_id == 0) {
			$sql .= $sqlSearch_if;
		}
		else {
			$sql_check = 'N';
			if(!empty($_GET['userid']) && !empty($_GET['sessionId'])){
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = " .$_GET['userid']." AND sc.id_session = ".$session_id;
				$sql_check = 'Y';
			}
			else if(!empty($_GET['userid']) && $sql_check == 'N'){
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$_GET['userid']." AND (cr.status = 1 OR cr.tutor_id = 1)";
			}
			else if(!empty($_GET['sessionId']) && $sql_check == 'N'){

				$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
			}
			$sql .= $sqlSearch_else;				
		}
		$sql .= " ORDER BY c.title";
	}
	else {
		$sql = "SELECT code, title FROM $course_table c";
		
		if(api_is_allowed_to_create_course()){
			if(empty($_GET['sessionId'])) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$_GET['sessionId'];
			}
		}
		else {
			if(empty($_GET['sessionId'])) {
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
				$sql .= $sqlSearch_else;
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			else {
				$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$_GET['sessionId'];
			}				
		}	
		$sql .= $sqlSearch_else;
		$sql .= " ORDER BY title";
	}
	
	if(!empty($_GET['search'])){
//	Database::query("SET NAMES 'utf8'");
	}
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);	
	echo '<div id="chartContainer" style="height:300px;width:75%;padding-left:50px;"></div></br>';
	echo '<table border="0" align="center" cellspacing="1" cellpadding="1" width="80%"><tr><td><b>Trainer :</b> '.$trainername.'</td><td><b>Session: </b>'.$sessionname.'</td></tr></table>';
	echo '<table border="1" align="center" cellspacing="0" cellpadding="4" width="80%">';
	//start of printing column names
	echo '<tbody>
				<thead>
					<tr>
                         <th>'.get_lang("Course").'</th>
                         <th>'.get_lang("Learners").'</th>
                         <th>'.get_lang("ModulesTime").'</th>
                         <th>'.get_lang("ModulesProgress").'</th>
                         <th>'.get_lang("ModulesScore").'</th>
                         <th>'.get_lang("QuizzesScore").'</th>						
                    </tr>
				</thead>';
	//end of printing column names
	if($num_rows==0){
		echo "<tr><td colspan='6'>".get_lang("NoRecords")."</td></tr>";	
	} 
	else{
		$chart_code_arr = array();
		//start while loop to get data
		while($row = Database::fetch_array($res)) {
			$course_name = get_course_title($row['code']);
			$no_learners = total_numberof_learners($row['code'],$session_id);
			$module_time = total_modules_time($row['code'],$session_id);
			$module_progress = total_modules_progress($row['code'], $no_learners,$session_id);
			$module_score = total_modules_score($row['code'], $no_learners, $session_id);
			$quiz_score = total_quizzes_score($row['code'], $no_learners, $session_id);
			$tmp_quiz_score = str_replace("%","",$quiz_score);
			
			if(intval($tmp_quiz_score) > 0){
			$chart_code_arr[] = '{y: '.$tmp_quiz_score.', label: "'.$course_name.'" }';
			}
			echo "<tr>";
			if(!empty($_GET['search'])){				
				echo "<td>".api_convert_encoding(Database::escape_string($row['title']),api_get_system_encoding(),'UTF-8')."</td>";
			}
			else {
				echo "<td>".$row['title']."</td>";
			}
									
			echo "					<td>".$no_learners."</td>
									<td align='center'>".$module_time."</td>
									<td align='center'>".$module_progress." %</td>
									<td align='center'>".$module_score."</td>
									<td align='center'>".$quiz_score."</td>
								  </tr>";
		}
	}
	echo "</tbody></table></body></html>";
	$count_chart_code = sizeof($chart_code_arr);
	if($count_chart_code > 1 && $count_chart_code < 15 && $small_device != 'Y'){

		echo '<script>	
		window.onload = function () {
		var chart = new CanvasJS.Chart("chartContainer", {
			
			axisX:{
				interval: 1,
				gridThickness: 0,
				labelFontSize: 10,				
				labelFontStyle: "normal",
				labelFontWeight: "normal",
				labelFontFamily: "Verdana",					
			},
			axisY2:{
				interlacedColor: "#F9F9F9",
				gridColor: "#F9F9F9",				
				title: "'.get_lang('CourseVsQuizScore').'",
				titleFontColor: "#424242",
				titleFontWeight: "bold",
				indexOrientation: "vertical",
				maximum: 100,
			},
			toolTip:{
				enabled: false,   //enable here
			  },
			data: [
			{   				
				type: "bar",
                name: "chart",
				axisYType: "secondary",
				color: "'.$theme_color.'",
				indexLabel: "{y}  ",
				indexLabelPlacement: "outside",  
				indexLabelOrientation: "horizontal",
				indexLabelFontFamily: "Verdana",
				indexLabelFontColor: "#CCC",
				indexLabelFontSize: 14,
				indexLabelFontWeight: "normal",
				dataPoints: [
				
				'.implode(",",$chart_code_arr).'

				]
			},
			
			]
		});

chart.render();
		}
		
		</script>';
		}
		else {
			echo '<script>
		$("#chartContainer").css("display", "none");
			</script>';
		}
}

#Print modules list
function printmodules(){
	 
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	
	$theme_color = get_theme_color();
	
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Modules</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
		</style>
		<script type="text/javascript" src="js/canvasjs.min.js"></script>
		<link type="text/css" rel="stylesheet" media="print" href="'.api_get_path(WEB_PATH).'main/css/dokeos2_black_tablet/print.css" />
	</head>
	<body>';
	
	$modules = array();
	$user_id = $_GET['userid'];
	if(empty($_GET['sessionId']))
		$session_id = 0;
	else 
		$session_id = $_GET['sessionId'];
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}
	$course_search = checkUTF8($course_search);
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	$course_code = $_GET['courses'];
	if(empty($course_code))
			$course_code = 0;
	if($course_code == '0') {		
		$modules = get_modules_list($course_search,$user_id,$session_id,$search);
		$coursedata = "N/A";
	}	
	else {
	 	$sql = "SELECT title FROM $course_table WHERE code='$course_code'";	
		$res = Database::query($sql, __FILE__, __LINE__);
		while ($row = Database::fetch_array($res, 'ASSOC')) {
			$coursedata = $row['title'];	
		}
		$info_course = CourseManager :: get_course_information($course_code);
		$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

		$sql_module = "SELECT id,name FROM $t_lp";
		if(!empty($search)){
		$sql_module .= " WHERE name LIKE '".$search."%'";		
		}
		$sql_module .= " ORDER BY name";
		if(!empty($search)){
		Database::query("SET NAMES 'utf8'");
		}
		$res_module = Database::query($sql_module, __FILE__, __LINE__);
		while($module = Database::fetch_array($res_module, 'ASSOC')) {
			
			$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
		}
	}

	if(!empty($search)) {
		if(api_is_allowed_to_edit()){
			$sql = "SELECT code FROM $course_table";
			$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		else {
			$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
			if(api_is_allowed_to_create_course()){
				$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		Database::query("SET NAMES 'utf8'");

		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		if($num_rows <> 0) {			
			$search_course_code = Database::result($res,0,0);
			$course_code = $search_course_code;
			$info_course = CourseManager :: get_course_information($search_course_code);
			$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

			$sql_module = "SELECT id,name FROM $t_lp ORDER BY name";
			$res_module = Database::query($sql_module, __FILE__, __LINE__);
			while($module = Database::fetch_array($res_module, 'ASSOC')) {
				$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
			}
		}		
	}
	echo '<div id="chartModuleContainer" style="height:300px;width:75%;padding-left:50px;"></div></br>';
	echo '<table border="0" align="center" cellspacing="1" cellpadding="1" width="80%"><tr><td><b>Course :</b> '.$coursedata.'</td></tr></table>';
	echo '<table border="1" align="center" cellspacing="0" cellpadding="4" width="80%">';
	//start of printing column names
	echo '<tbody>
				<thead>
					<tr>
                         <th>'.get_lang("Module").'</th>
                         <th>'.get_lang("InCourse").'</th>
                         <th>'.get_lang("Time").'</th>
                         <th>'.get_lang("Progress").'</th>
                         <th>'.get_lang("Score").'</th>                         					
                    </tr>
				</thead>';
	//end of printing column names
 	if(count($modules)==0){
		echo "<tr><td colspan='5'>".get_lang("NoRecords")."</td></tr>";
	}
	else{
		$chart_module_arr = array();
		foreach($modules as $module1) {	
			list($code,$lp_id,$module_name) = split("@",$module1);
			$course_name = get_course_title($code);
			$total_learners = total_numberof_learners($code);

			$course_module_time = get_module_time($code, $lp_id);
			$course_module_progress = get_module_progress($code, $lp_id, $total_learners);
			$course_module_score = get_module_score($code, $lp_id, $total_learners);

			$tmp_module_score = str_replace("%","",$course_module_score);
			if(intval($tmp_module_score) > 0){
			$chart_module_arr[] = '{y: '.$tmp_module_score.', label: "'.api_convert_encoding($module_name,api_get_system_encoding(),'UTF-8').'" }';
			}
		
			echo "<tr>";
			if(!empty($search)){
				$course_name = api_convert_encoding($course_name,api_get_system_encoding(),'UTF-8');
				echo "<td>".api_convert_encoding($module_name,api_get_system_encoding(),'UTF-8')."</td>";
			}
			else if($course_code == '0'){
				$course_name = api_convert_encoding($course_name,api_get_system_encoding(),'UTF-8');
				echo "<td>".api_convert_encoding($module_name,api_get_system_encoding(),'UTF-8')."</td>";
			}
			else {
				echo "<td>".$module_name."</td>";
			}
			echo	"<td>".$course_name."</td>
					<td align='center'>".$course_module_time."</td>													
					<td align='center'>".$course_module_progress."</td>
					<td align='center'>".$course_module_score."</td>
				</tr>";				
		}		
	}
	echo "</tbody></table></body></html>";	
	$count_chart_module = sizeof($chart_module_arr);

	if($count_chart_module > 1 && $count_chart_module < 15 && $small_device != 'Y'){
	echo '<script>
	window.onload = function () {
		var chart = new CanvasJS.Chart("chartModuleContainer", {
		
		axisX:{
			interval: 1,
			gridThickness: 0,
			labelFontSize: 10,				
			labelFontStyle: "normal",
			labelFontWeight: "normal",
			labelFontFamily: "Verdana",				
		},
		axisY2:{
			interlacedColor: "#F9F9F9",
			gridColor: "#F9F9F9",				
			title: "'.get_lang('ModuleVsQuizScore').'",
			titleFontColor: "#424242",
			titleFontWeight: "bold",
			indexOrientation: "vertical",
			maximum: 100,
		},
		toolTip:{
			enabled: false,   //enable here
		  },
		data: [
		{   				
			type: "bar",
			name: "chart",
			axisYType: "secondary",
			color: "'.$theme_color.'",
			indexLabel: "{y}  ",
			indexLabelPlacement: "outside",  
			indexLabelOrientation: "horizontal",
			indexLabelFontFamily: "Verdana",
			indexLabelFontColor: "#CCC",
			indexLabelFontSize: 14,
			indexLabelFontWeight: "normal",
			dataPoints: [
			
			'.implode(",",$chart_module_arr).'
			]
		},
		
		]
	});

chart.render();
	}
	</script>';
	}
	else {
		echo '<script>
	$("#chartModuleContainer").css("display", "none");
		</script>';
	}
}

#Export quizzes list
function exportquizzes(){
	$filename = "quiz_report";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	$user_id = $_GET['user_id'];
	if(empty($_GET['session']))
		$session_id = 0;
	else 
		$session_id = $_GET['session'];
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}
	$course_search = checkUTF8($course_search);
		
	$course_code = $_GET['courses'];
		if(empty($course_code))
			$course_code = 0;
		if($course_code == '0' && empty($_GET['quiz'])) {
			/*if(empty($_GET['quiztype'])) {
			$quizzes = get_quiz_list();
			}
			else {
				$quizzes = get_quiz_list($_GET['quiztype']);
			}*/
			$quizzes = get_quiz_list($_GET['quiztype'],$session_id,$search,$user_id,$course_search);
		}
		else {

			if(!empty($_GET['quiz'])) {
				list($code,$quiz_id) = split("@",$_GET['quiz']);
				$course_code = $code;
			}

			$info_course = CourseManager :: get_course_information($course_code);
			$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);			
			$where_added = 0;
			$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0";
			if(!empty($_GET['quiz'])) {
				$sql_quiz .= " AND id = ".$quiz_id;
			}
			if(!empty($_GET['type'])) {
				$sql_quiz .= " AND quiz_type = ".$_GET['type'];
			}
			if(!empty($session_id)) {
				$sql_quiz .= " OR session_id = ".$session_id;
			}
			if(!empty($search)) {
				$sql_quiz .= " AND title LIKE '".$search."%'";
			}			
			$sql_quiz .= " ORDER BY title";			
			$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
			while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
				$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
			}
		}

	if(!empty($search)) {
		if(api_is_allowed_to_edit()){
			$sql = "SELECT code FROM $course_table";
			$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		else {
			$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
			if(api_is_allowed_to_create_course()){
				$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		Database::query("SET NAMES 'utf8'");

		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		if($num_rows <> 0) {			
			$search_course_code = Database::result($res,0,0);
			$course_code = $search_course_code;
			$info_course = CourseManager :: get_course_information($search_course_code);
			$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);

			$sql_quiz = "SELECT id, title FROM $table_quiz WHERE active <> -1 ORDER BY title";
			$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
			while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
				$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
			}
		}		
	}	

	//start of printing column names
	echo dataformat(get_lang("Quiz"));
    echo dataformat(get_lang("InCourse"));                                        
    echo dataformat(get_lang("AverageScore"));  
    echo dataformat(get_lang("Highest"));  
    echo dataformat(get_lang("Lowest"));  
    echo dataformat(get_lang("Participation"));  
    echo dataformat(get_lang("AverageTime"));  
	print("\n");
	//end of printing column names
	if(count($quizzes)==0){
		$schema_insert = "";	
		$schema_insert .= dataformat(get_lang("NoRecords"));
		$schema_insert = str_replace($sep."$", "", $schema_insert);
 		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
	}
	else{
		//start while loop to get data
		foreach($quizzes as $quiz1) {	
			list($code,$quiz_id,$quiz_name) = split("@",$quiz1);
			$course_name = api_convert_encoding(get_course_title($code),api_get_system_encoding(),'UTF-8');
			$average_score = get_quiz_average_score($quiz_id, $code, $session_id);
			$highest_score = get_highest_score($quiz_id, $code, $session_id);
			$lowest_score = get_lowest_score($quiz_id, $code, $session_id);
			$no_participants = get_quiz_participants($quiz_id, $code, $session_id);
			$average_time = get_average_time($quiz_id, $code, $session_id);
			$schema_insert = "";
	 	
	 		$schema_insert .= dataformat(api_convert_encoding($quiz_name,api_get_system_encoding(),'UTF-8'));
	 		$schema_insert .= dataformat($course_name);
	 		$schema_insert .= dataformat($average_score);
	 		$schema_insert .= dataformat($highest_score);
			$schema_insert .= dataformat($lowest_score);
			$schema_insert .= dataformat($no_participants);
			$schema_insert .= dataformat($average_time);
		
        	$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}
	}	
}

#Print quizzes list
function printquizzes(){
	
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Quizzes</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
		</style>
		<script type="text/javascript" src="js/canvasjs.min.js"></script>		
		<link type="text/css" rel="stylesheet" media="print" href="'.api_get_path(WEB_PATH).'main/css/'.api_get_setting('stylesheets').'/print.css" />
	</head>
	<body>';
	
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

	$theme_color = get_theme_color();
	
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	$user_id = $_GET['user_id'];
	if(empty($_GET['session']))
		$session_id = 0;
	else 
		$session_id = $_GET['session'];
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}
	$course_search = checkUTF8($course_search);
	
	$course_code = $_GET['courses'];
		if(empty($course_code))
			$course_code = 0;
	if($course_code == '0' && empty($_GET['quiz'])) {
		$quizzes = get_quiz_list($_GET['quiztype'],$session_id,$search,$user_id,$course_search);
	}
	else {
		if(!empty($_GET['quiz'])) {
			list($code,$quiz_id) = split("@",$_GET['quiz']);
			$course_code = $code;
		}

		$info_course = CourseManager :: get_course_information($course_code);
		$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);			
		$where_added = 0;
		$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0";	
		if(!empty($_GET['quiz'])) {
			$sql_quiz .= " AND id = ".$quiz_id;
		}
		if(!empty($_GET['type'])) {
			$sql_quiz .= " AND quiz_type = ".$_GET['type'];
		}
		if(!empty($session_id)) {
			$sql_quiz .= " OR session_id = ".$session_id;
		}
		if(!empty($search)) {
			$sql_quiz .= " AND title LIKE '".$search."%'";
		}		
		$sql_quiz .= " ORDER BY title";

		if(!empty($search)){
		Database::query("SET NAMES 'utf8'");
		}
		$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
		while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
			$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
		}
	}
	if(empty($course_code)){
		$coursedata = 'N/A';
	}
	else{
		$coursedata = $info_course['title'];		
	}
	if(empty($_GET['quiz'])){
		$quizdata = 'N/A';
	}
	else{
		$sql_quiz1 = "SELECT title FROM $table_quiz where id='$quiz_id'";
		$res_quiz1 = Database::query($sql_quiz1, __FILE__, __LINE__);			
		$rowquiz = Database::fetch_array($res_quiz1, 'ASSOC');
		$quizdata = $rowquiz['title'];
	}
	if(empty($_GET['type'])){
		$typedata = 'N/A';
	}
	else{
		if($_GET['type']=='1'){
			$typedata = "Self Learning";
		}
		if($_GET['type']=='2'){
			$typedata = "Exam Mode";
		}
	}
	if(empty($_GET['session'])){
		$sessiondata = 'N/A';
	}
	else{
		$session_table = Database :: get_main_table(TABLE_MAIN_SESSION);

		$sql = "SELECT name FROM $session_table WHERE id='".$_GET['session']."'";
		$res = Database::query($sql, __FILE__, __LINE__);
		$session = Database::fetch_array($res, 'ASSOC');
		$sessiondata = $session['name'];
	}

	if(!empty($search)) {
		if(api_is_allowed_to_edit()){
			$sql = "SELECT code FROM $course_table";
			$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		else {
			$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
			if(api_is_allowed_to_create_course()){
				$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";
				$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
			}
			else {
				$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
			}
			$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
		}
		Database::query("SET NAMES 'utf8'");

		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		if($num_rows <> 0) {			
			$search_course_code = Database::result($res,0,0);
			$course_code = $search_course_code;
			$info_course = CourseManager :: get_course_information($search_course_code);
			$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);

			$sql_quiz = "SELECT id, title FROM $table_quiz WHERE active <> -1 ORDER BY title";
			$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
			while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
				$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
			}
		}		
	}	
	echo '<div id="chartQuizContainer" style="height:auto;min-height:300px;width:75%;padding-left:50px;"></div></br>';
	echo '<table border="0" align="center" cellspacing="1" cellpadding="1" width="80%"><tr><td><b>Course :</b> '.$coursedata.'</td><td><b>Quiz :</b> '.$quizdata.'</td><td><b>Quiz Type :</b> '.$typedata.'</td><td><b>Session :</b> '.$sessiondata.'</td></tr></table>';
	echo '<table border="1" align="center" cellspacing="0" cellpadding="4" width="80%">';
	//start of printing column names
	echo '<tbody>
				<thead>
					<tr>
                        <th>'.get_lang("Quiz").'</th>
						<th>'.get_lang("InCourse").'</th>
						<th>'.get_lang("AverageScore").'</th>
						<th>'.get_lang("Highest").'</th>
						<th>'.get_lang("Lowest").'</th>
						<th>'.get_lang("Participation").'</th>
						<th>'.get_lang("AverageTime").'</th>
                    </tr>
				</thead>';
	//end of printing column names
 	if(count($quizzes)==0){
		echo "<tr><td colspan='7'>".get_lang("NoRecords")."</td></tr>";
	}
	else{
		$chart_quiz_arr = array();
		foreach($quizzes as $quiz1) {	
			list($code,$quiz_id,$quiz_name) = split("@",$quiz1);
			$course_name = get_course_title($code);
			$average_score = get_quiz_average_score($quiz_id, $code, $session_id);
			$highest_score = get_highest_score($quiz_id, $code, $session_id);
			$lowest_score = get_lowest_score($quiz_id, $code, $session_id);
			$no_participants = get_quiz_participants($quiz_id, $code, $session_id);
			$average_time = get_average_time($quiz_id, $code, $session_id);

			$tmp_average_score = str_replace("%","",$average_score);
			if(intval($tmp_average_score) > 0){
			$chart_quiz_arr[] = '{y: '.$tmp_average_score.', label: "'.api_convert_encoding($quiz_name,api_get_system_encoding(),'UTF-8').'" }';
			}
			
			echo "<tr>";
			if(!empty($search)){
				$course_name = api_convert_encoding($course_name,api_get_system_encoding(),'UTF-8');
				echo "<td>".api_convert_encoding($quiz_name,api_get_system_encoding(),'UTF-8')."</td>";
			}
			else if($course_code == '0'){
				$course_name = api_convert_encoding($course_name,api_get_system_encoding(),'UTF-8');
				echo "<td>".api_convert_encoding($quiz_name,api_get_system_encoding(),'UTF-8')."</td>";
			}
			else {
				echo "<td>".$quiz_name."</td>";
			}
			echo "	<td>".$course_name."</td>
					<td align='center'>".$average_score."</td>													
					<td align='center'>".$highest_score."</td>
					<td align='center'>".$lowest_score."</td>
					<td align='center'>".$no_participants."</td>
					<td align='center'>".$average_time."</td>
				  </tr>";				
		}	}
	echo "</tbody></table></body></html>";	
	$count_chart_quiz = sizeof($chart_quiz_arr);

		if($count_chart_quiz > 1 && $small_device != 'Y'){
		echo '<script>
		window.onload = function () {
		var chart = new CanvasJS.Chart("chartQuizContainer", {
			
			axisX:{
				interval: 1,
				gridThickness: 0,
				labelFontSize: 10,				
				labelFontStyle: "normal",
				labelFontWeight: "normal",
				labelFontFamily: "Verdana",				
			},
			axisY2:{
				interlacedColor: "#F9F9F9",
				gridColor: "#F9F9F9",				
				title: "'.get_lang('QuizVsQuizScore').'",
				titleFontColor: "#424242",
				titleFontWeight: "bold",
				indexOrientation: "vertical",
				maximum: 100,
			},
			toolTip:{
				enabled: false,   //enable here
			  },
			data: [
			{   				
				type: "bar",
                name: "chart",
				axisYType: "secondary",
				color: "'.$theme_color.'",
				indexLabel: "{y}  ",
				indexLabelPlacement: "outside",  
				indexLabelOrientation: "horizontal",
				indexLabelFontFamily: "Verdana",
				indexLabelFontColor: "#CCC",
				indexLabelFontSize: 14,
				indexLabelFontWeight: "normal",
				dataPoints: [
				
				'.implode(",",$chart_quiz_arr).'
				]
			},
			
			]
		});

chart.render();
		}
		</script>';
		}
		else {
			echo '<script>
		$("#chartQuizContainer").css("display", "none");
			</script>';
		}
}

#Export learners list
function exportlearners(){
	$filename = "user_report";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	$user_table 		= Database :: get_main_table(TABLE_MAIN_USER);	
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

	$users = array();
	$course_code = $_GET['courses'];
	$quiz_rank = $_GET['rank'];
	$status = $_GET['status'];
	$trainer_id = $_GET['trainer_id'];
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}
	$course_search = checkUTF8($course_search);
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	if(empty($course_code))
		$course_code = 0;
	if($status != '0'){
		$status = 1;
	}
	if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
		if($course_code == '0' && $_GET['sessionId'] == 0) {
			$users = get_users_list($status, $search, $trainer_id, $course_search);
		}
		else if($_GET['sessionId'] <> 0) {

			$sql = "SELECT u.user_id,u.lastname, u.firstname FROM $user_table u, $session_course_user_table srcu WHERE u.user_id = srcu.id_user AND u.active = ".$status." AND srcu.id_session = ".$_GET['sessionId'];
			if(!empty($search)){
				$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
			}
			$sql .= " ORDER BY u.lastname";

			Database::query("SET NAMES 'utf8'");
			$res = Database::query($sql, __FILE__, __LINE__);
			while($user = Database::fetch_array($res, 'ASSOC')) {
				$users[] = $user;
			}
		}
		else {

			$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
			
			$sql = "SELECT u.user_id, u.lastname, u.firstname FROM $user_table u, $course_user_table cr WHERE u.user_id = cr.user_id AND cr.course_code = '".$course_code."' AND u.active = ".$status;
			if(!empty($search)){
				$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
			}
			$sql .= " ORDER BY u.lastname";

			Database::query("SET NAMES 'utf8'");
			$res = Database::query($sql, __FILE__, __LINE__);
			while($user = Database::fetch_array($res, 'ASSOC')) {
				$users[] = $user;
			}
		}
	}
	else {

		$sql = "SELECT * FROM $user_table WHERE user_id = ".api_get_user_id();
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			$users[] = $row;
		}
		
	}
	
	$limited_users = sort($users);

	//start of printing column names
	echo dataformat(get_lang("LastName"));
    echo dataformat(get_lang("FirstName"));                                      
    echo dataformat(get_lang("LatestConnection"));
    echo dataformat(get_lang("ModulesTime"));
    echo dataformat(get_lang("ModulesProgress"));
    echo dataformat(get_lang("ModulesScore"));
    echo dataformat(get_lang("QuizzesScore"));
	print("\n");
	//end of printing column names
	if(count($users)==0){
		$schema_insert = "";	
		$schema_insert .= dataformat(get_lang("NoRecords"));
		$schema_insert = str_replace($sep."$", "", $schema_insert);
 		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
	}
	else{
		//start while loop to get data
		foreach($users as $user1) {	
			if(!empty($user1['user_id'])) {
				if($course_code == 0){
					$last_connection_date = Tracking :: get_last_connection_date($user1['user_id'], true);
				}
				else {
					$last_connection_date = Tracking :: get_last_connection_date_on_the_course($user1['user_id'], $course_code);				
				}
				if(empty($last_connection_date)) {
					$last_connection_date = '/';
				}
				$time_spent = get_time_spent($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
				$progress = get_student_progress($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
				$score = get_student_score($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
				$quiz_score = get_student_quiz_score($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
				if($score == '- %'){
					$score = '/';
				}
				if($last_connection_date == '/') {
					$time_spent = '/';
					$progress = '/';
					$score = '/';
					$quiz_score = '/';
				}
				list($quizscore,$percent) = split(" ",$quiz_score);
				$schema_insert = "";
				if($quiz_rank <> 0 && ($quizscore != '/') && ($quizscore > ($quiz_rank - 10)) && ($quizscore <= $quiz_rank)) {
					//$schema_insert .= dataformat(strtoupper(api_convert_encoding(Database::escape_string($user1['lastname']),'UTF-8',api_get_system_encoding())));
					//$schema_insert .= dataformat(api_convert_encoding(Database::escape_string($user1['firstname']),'UTF-8',api_get_system_encoding()));
					$schema_insert .= dataformat(strtoupper(api_convert_encoding(Database::escape_string($user1['lastname']),api_get_system_encoding(),'UTF-8')));
					$schema_insert .= dataformat(api_convert_encoding(Database::escape_string($user1['firstname']),api_get_system_encoding(),'UTF-8'));
					//$schema_insert .= dataformat(strtoupper($user1['lastname']));
					//$schema_insert .= dataformat($user1['firstname']);
					$schema_insert .= dataformat(strip_tags($last_connection_date));
					$schema_insert .= dataformat($time_spent);
					$schema_insert .= dataformat($progress);
					$schema_insert .= dataformat($score);
					$schema_insert .= dataformat($quiz_score);
				}		
				else if($quiz_rank == 0) {
					$schema_insert .= dataformat(strtoupper(api_convert_encoding(Database::escape_string($user1['lastname']),api_get_system_encoding(),'UTF-8')));
					$schema_insert .= dataformat(api_convert_encoding(Database::escape_string($user1['firstname']),api_get_system_encoding(),'UTF-8'));
					//$schema_insert .= dataformat(strtoupper($user1['lastname']));
					//$schema_insert .= dataformat($user1['firstname']);
					$schema_insert .= dataformat(strip_tags($last_connection_date));
					$schema_insert .= dataformat($time_spent);
					$schema_insert .= dataformat($progress);
					$schema_insert .= dataformat($score);
					$schema_insert .= dataformat($quiz_score);
				}
				$schema_insert = str_replace($sep."$", "", $schema_insert);
				$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
				$schema_insert .= "\t";
				print(trim($schema_insert));
				print "\n";
			}
		}
	}	
}

#Print learners list
function printlearners(){
	
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Learners</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
		</style>
		<script type="text/javascript" src="js/canvasjs.min.js"></script>
	</head>
	<body>';
	
	$user_table 		= Database :: get_main_table(TABLE_MAIN_USER);	
	$session_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);	
	
	$theme_color = get_theme_color();
	
	$users = array();
	$course_code = $_GET['courses'];
	$quiz_rank = $_GET['rank'];
	$status = $_GET['status'];
	$trainer_id = $_GET['trainer_id'];
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}
	$course_search = checkUTF8($course_search);
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	if(empty($course_code))
		$course_code = 0;
	if($status != '0'){
		$status = 1;
	}
	if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
		if($course_code == '0' && $_GET['sessionId'] == 0) {
			$users = get_users_list($status, $search, $trainer_id, $course_search);
		}
		else if($_GET['sessionId'] <> 0) {

			$sql = "SELECT u.user_id,u.lastname, u.firstname FROM $user_table u, $session_course_user_table srcu WHERE u.user_id = srcu.id_user AND u.active = ".$status." AND srcu.id_session = ".$_GET['sessionId'];
			if(!empty($search)){
				$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
			}
			$sql .= " ORDER BY u.lastname";

			Database::query("SET NAMES 'utf8'");
			$res = Database::query($sql, __FILE__, __LINE__);
			while($user = Database::fetch_array($res, 'ASSOC')) {
				$users[] = $user;
			}
		}
		else {

			$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
			
			$sql = "SELECT u.user_id, u.lastname, u.firstname FROM $user_table u, $course_user_table cr WHERE u.user_id = cr.user_id AND cr.course_code = '".$course_code."' AND u.active = ".$status;
			if(!empty($search)){
				$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
			}
			$sql .= " ORDER BY u.lastname";

			Database::query("SET NAMES 'utf8'");
			$res = Database::query($sql, __FILE__, __LINE__);
			while($user = Database::fetch_array($res, 'ASSOC')) {
				$users[] = $user;
			}
		}
	}
	else {

		$sql = "SELECT * FROM $user_table WHERE user_id = ".api_get_user_id();
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			$users[] = $row;
		}
		
	}
	
	$limited_users = sort($users);
	//echo $sql;
	if(empty($course_code)){
		$coursedata = 'N/A';
	}
	else{
		$sql_c = "SELECT title FROM $course_table WHERE code='$course_code'";	
		$res_c = Database::query($sql_c, __FILE__, __LINE__);
		$row_c = Database::fetch_array($res_c, 'ASSOC');
		$coursedata = $row_c['title'];	
	}
	if(empty($_GET['sessionId'])){
		$sessiondata = 'N/A';
	}
	else{
		$session_table = Database :: get_main_table(TABLE_MAIN_SESSION);

		$sql = "SELECT name FROM $session_table WHERE id='".$_GET['sessionId']."'";
		$res = Database::query($sql, __FILE__, __LINE__);
		$session = Database::fetch_array($res, 'ASSOC');
		$sessiondata = $session['name'];
	}
	if($_GET['filter']=='-1'){
		$statusdata = 'N/A';
	}
	else{
		if($_GET['filter']=='1'){
			$statusdata = "Active Learners";
		}
		if($_GET['filter']=='0'){
			$statusdata = "Inactive Learners";
		}
	}
	if($_GET['rank']=='0'){
		$rankdata = 'N/A';
	}
	else{
		if($_GET['rank']=='100'){
			$rankdata = "100-91%";
		}
		if($_GET['rank']=='90'){
			$rankdata = "90-81%";
		}
		if($_GET['rank']=='80'){
			$rankdata = "80-71%";
		}
		if($_GET['rank']=='70'){
			$rankdata = "70-61%";
		}
		if($_GET['rank']=='60'){
			$rankdata = "60-51%";
		}
		if($_GET['rank']=='50'){
			$rankdata = "50-41%";
		}
		if($_GET['rank']=='40'){
			$rankdata = "40-31%";
		}
		if($_GET['rank']=='30'){
			$rankdata = "30-21%";
		}
		if($_GET['rank']=='20'){
			$rankdata = "20-11%";
		}
		if($_GET['rank']=='10'){
			$rankdata = "10-0%";
		}
	}
	echo '<div id="chartUserContainer" style="height:300px;width:75%;padding-left:50px;"></div></br>';
	echo '<table border="0" align="center" cellspacing="1" cellpadding="1" width="80%"><tr><td><b>Course :</b> '.$coursedata.'</td><td><b>Session :</b> '.$sessiondata.'</td><td><b>Status :</b> '.$statusdata.'</td><td><b>Quiz Ranking :</b> '.$rankdata.'</td></tr></table>';
	echo '<table border="1" align="center" cellspacing="0" cellpadding="4" width="80%">';
	//start of printing column names
	echo '<tbody>
				<thead>
					<tr>
                        <th>'.get_lang("LastName").'</th>
						<th>'.get_lang("FirstName").'</th>
						<th>'.get_lang("LastestConnection").'</th>
						<th>'.get_lang("ModulesTime").'</th>
						<th>'.get_lang("ModulesProgress").'</th>
						<th>'.get_lang("ModulesScore").'</th>
						<th>'.get_lang("QuizzesScore").'</th>
                    </tr>
				</thead>';
	//end of printing column names
	//print_r($users);
 	if(count($users)==0){
		echo "<tr><td colspan='7'>".get_lang("NoRecords")."</td></tr>";
	}
	else{
		$chart_user_arr = array();
		foreach($users as $user1) {	
		if(!empty($user1['user_id'])) {
			if($course_code == 0){
				$last_connection_date = Tracking :: get_last_connection_date($user1['user_id'], true);
			}
			else {
				$last_connection_date = Tracking :: get_last_connection_date_on_the_course($user1['user_id'], $course_code);				
			}
			if(empty($last_connection_date)) {
				$last_connection_date = '/';
			}
			$time_spent = get_time_spent($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
			$progress = get_student_progress($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
			$score = get_student_score($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
			$quiz_score = get_student_quiz_score($user1['user_id'], $course_code, $_GET['sessionId'], $course_search);
			if($score == '- %'){
				$score = '/';
			}
			if($last_connection_date == '/') {
				$time_spent = '/';
				$progress = '/';
				$score = '/';
				$quiz_score = '/';
			}
			list($quizscore,$percent) = split(" ",$quiz_score);

			$tmp_quiz_score = str_replace("%","",$quiz_score);
			
			if($quiz_rank <> 0 && ($quizscore != '/') && ($quizscore > ($quiz_rank - 10)) && ($quizscore <= $quiz_rank)) {

				if(intval($tmp_quiz_score) > 0){
					$chart_user_arr[] = '{y: '.$tmp_quiz_score.', label: "'.strtoupper($user1['lastname']).' '.$user1['firstname'].'" }';
				}

				echo "<tr>";
				if(!empty($search)){					
					echo "<td>".api_convert_encoding($user1['lastname'],api_get_system_encoding(),'UTF-8')."</td>";
					echo "<td>".api_convert_encoding($user1['firstname'],api_get_system_encoding(),'UTF-8')."</td>";
				}
				else if($course_code == '0'){
					
					echo "<td>".api_convert_encoding($user1['lastname'],api_get_system_encoding(),'UTF-8')."</td>";
					echo "<td>".api_convert_encoding($user1['firstname'],api_get_system_encoding(),'UTF-8')."</td>";
				}
				else {
					echo "<td>".strtoupper($user1['lastname'])."</td>";
					echo "<td>".$user1['firstname']."</td>";
				}
				echo "
						<td align='center'>".$last_connection_date."</td>													
						<td align='center'>".$time_spent."</td>
						<td align='center'>".$progress."</td>
						<td align='center'>".$score."</td>
						<td align='center'>".$quiz_score."</td>
				  	</tr>";	
			}
			else if($quiz_rank == 0) {
				if(intval($tmp_quiz_score) > 0){
					$chart_user_arr[] = '{y: '.$tmp_quiz_score.', label: "'.strtoupper($user1['lastname']).' '.$user1['firstname'].'" }';
				}

				echo "<tr>";
				if(!empty($search)){					
					echo "<td>".api_convert_encoding($user1['lastname'],api_get_system_encoding(),'UTF-8')."</td>";
					echo "<td>".api_convert_encoding($user1['firstname'],api_get_system_encoding(),'UTF-8')."</td>";
				}
				else if($course_code == '0'){
					
					echo "<td>".api_convert_encoding($user1['lastname'],api_get_system_encoding(),'UTF-8')."</td>";
					echo "<td>".api_convert_encoding($user1['firstname'],api_get_system_encoding(),'UTF-8')."</td>";
				}
				else {
					echo "<td>".strtoupper($user1['lastname'])."</td>";
					echo "<td>".$user1['firstname']."</td>";
				}
				echo "	<td align='center'>".$last_connection_date."</td>													
						<td align='center'>".$time_spent."</td>
						<td align='center'>".$progress."</td>
						<td align='center'>".$score."</td>
						<td align='center'>".$quiz_score."</td>
				  	</tr>";
			}
		}
		}
	}
	echo "</tbody></table></body></html>";	
	$count_chart_user = sizeof($chart_user_arr);

	if($count_chart_user > 1 && $count_chart_user < 15){
	echo '<script>
	window.onload = function () {
		var chart = new CanvasJS.Chart("chartUserContainer", {	
		
		axisX:{
			interval: 1,
			gridThickness: 0,
			labelFontSize: 10,				
			labelFontStyle: "normal",
			labelFontWeight: "normal",
			labelFontFamily: "Verdana",				
		},
		axisY2:{
			interlacedColor: "#F9F9F9",
			gridColor: "#F9F9F9",				
			title: "'.get_lang('UserVsQuizScore').'",
			titleFontColor: "#424242",
			titleFontWeight: "bold",
			indexOrientation: "vertical",
			maximum: 100,
		},
		toolTip:{
			enabled: false,   //enable here
		  },
		data: [
		{   				
			type: "bar",
			name: "chart",
			axisYType: "secondary",
			color: "'.$theme_color.'",
			indexLabel: "{y}  ",
			indexLabelPlacement: "outside",  
			indexLabelOrientation: "horizontal",
			indexLabelFontFamily: "Verdana",
			indexLabelFontColor: "#CCC",
			indexLabelFontSize: 14,
			indexLabelFontWeight: "bold",
			dataPoints: [
			
			'.implode(",",$chart_user_arr).'
			]
		},
		
		]
	});

chart.render();
	}
	</script>';
	}
	else {
		echo '<script>
	$("#chartUserContainer").css("display", "none");
		</script>';
	}
}

#Export learners report list
function exportlearnersreport(){

	$filename = "learners_individual_report";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
	$course_code = $_GET['course_code'];
	if(empty($course_code))
		$course_code = 0;
	$session_id = $_GET['sessionId'];
	if(empty($session_id)){
		$session_id = 0;
	}
	$user_id = $_GET['userid'];
	$user_info = api_get_user_info($user_id);
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}
	$user_details = api_get_user_info_from_username($user_info['username']);
	$first_connection_date = Tracking :: get_first_connection_date($user_id);
	$last_connection_date = Tracking :: get_last_connection_date($user_id, true);
	$time_spent = get_time_spent($user_id, $course_code, $session_id, $course_search);

	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);

	if(!empty($course_search)){
		$search_codes = get_search_coursecodes($course_search);		
	}
	if($course_code == '0'){	
		$sql = "SELECT DISTINCT(course_code) FROM $course_user_table cru WHERE cru.user_id = '".$user_id."'";
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}

		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$course_codes[] = $row['course_code'];
		}
	}
	else {
		$course_codes[] = $course_code;
	}
	
	$progress = get_student_progress($user_id, $course_code, $session_id, $course_search);
	//list($module_progress, $percentage) = split(" ",$progress);
	//$mod_progress = round(($module_progress / sizeof($course_codes)),2);
	$score = get_student_score($user_id, $course_code, $session_id, $course_search);
	$quiz_score = get_student_quiz_score($user_id, $course_code, $session_id, $course_search);

	if($user_details['active'] == 1) {
		$status = get_lang('Active');
	}
	elseif($user_details['active'] == 0) {
		$status = get_lang('Inactive');
	}

	if(empty($first_connection_date)) {
		$first_connection_date = '/';
	}
	if(empty($last_connection_date)) {
		$last_connection_date = '/';
	}

	if($score == '- %'){
		$score = 'n.a';
	}
	if($last_connection_date == '/') {
		$time_spent = 'n.a';
		$progress = 'n.a';
		$score = 'n.a';
		$quiz_score = 'n.a';
	}

	echo get_lang("IndividualReporting").": \t".$user_info['lastname'].''.$user_info['firstname']."\t";
	print("\n");

	//start of printing column names
	echo dataformat(get_lang("OverallInformation"))."\t";
    echo dataformat(get_lang("AccessDetails"))."\t";                                        
    echo dataformat(get_lang("SendMail"))."\t";
    print("\n");
    echo get_lang("Status")." : ".$status;
    print("\n");
    echo get_lang("Email")." : ".$user_info['mail'];
    print("\n");
    echo get_lang("FirstConnection")." : ".$first_connection_date;
    print("\n");
    echo get_lang("LatestConnection")." : ".$last_connection_date;
    print("\n");
    echo get_lang("TimeSpentInModules")." : ".$time_spent;
    print("\n");
    echo get_lang("ScoreInModules")." : ".$score;
    print("\n");
    echo get_lang("ProgressInModules")." : ".$progress;
    print("\n");
    echo get_lang("QuizzesScore")." : ".$quiz_score;
    print("\n\n");
    
    foreach($course_codes as $code) {
		$modules = array();
		$quizzes = array();
		$course_info = api_get_course_info($code);
		$valid = 10;
		$current_date = date('Y-m-d H:i:s',time());
		$track_online_table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ONLINE);
		$lp_item_table      = Database :: get_course_table(TABLE_LP_ITEM, $course_info['dbName']);
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS, $course_info['dbName']);	
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $course_info['dbName']);
		$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $course_info['dbName']);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $course_info['dbName']);

		$sql_scenario = "SELECT * FROM $TBL_SCENARIO_STEPS WHERE session_id = 0 ORDER BY step_created_order";
		$res_scenario = Database::query($sql_scenario, __FILE__, __LINE__);
		$num_scenario = Database::num_rows($res_scenario);

		$query = "SELECT login_user_id,login_date FROM ".$track_online_table ." WHERE DATE_ADD(login_date,INTERVAL $valid MINUTE) >= '".$current_date."' AND course = '".$code."' ";
		$res = Database::query($query, __FILE__, __LINE__);
		$num_users_online = Database::num_rows($res);

		$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);	
		$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);	

		$sql_module = "SELECT id, name FROM $t_lp WHERE session_id = 0";
		if(!empty($session_id)) {
			$sql_module .= ' OR session_id = '.$session_id;
		}
		$res_module = Database::query($sql_module, __FILE__, __LINE__);
		while($module = Database::fetch_array($res_module, 'ASSOC')) {		
			$modules[] = $module;
		}	

		$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0";
		if(!empty($session_id)) {
			$sql_quiz .= ' OR session_id = '.$session_id;
		}
		$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
		while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {		
			$quizzes[] = $quiz;
		}
		
		if(sizeof($modules) > 0 || sizeof($quizzes) > 0) {
			if(!empty($session_id)) {
			$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
			$session_info = api_get_session_info($session_id);

			if ($session_id > 0) {
				$session_name = $session_info['name'];
				$session_coach_id = $session_info['id_coach'];
				// get coach of the course in the session
				$sql = 'SELECT id_user FROM ' . $tbl_session_course_user . '
								WHERE id_session=' . $session_id . '
								AND course_code = "' . Database :: escape_string($code) . '" AND status=2';
				$rs = Database::query($sql, __FILE__, __LINE__);
				$course_coachs = array();
				while ($row_coachs = Database::fetch_array($rs)) { $course_coachs[] = $row_coachs['id_user']; }
				if (!empty($course_coachs)) {
					$info_tutor_name = array();
					foreach ($course_coachs as $course_coach) {
						$coach_infos = UserManager :: get_user_info_by_id($course_coach);
						$info_tutor_name[] = api_get_person_name($coach_infos['firstname'], $coach_infos['lastname']);
					}
					$info_course['tutor_name'] = implode(",",$info_tutor_name);
				}
				elseif ($session_coach_id != 0) {
					$coach_infos = UserManager :: get_user_info_by_id($session_coach_id);
					$info_course['tutor_name'] = api_get_person_name($coach_infos['firstname'], $coach_infos['lastname']);
				}
				echo $session_name;
			}
			/*else {
				echo '<h5>';
			}*/
			
			}
		if(!empty($course_search)){
			echo api_convert_encoding($course_info['name'],api_get_system_encoding(),'UTF-8');
		}
		else {
			echo $course_info['name'];
		}
		//echo $course_info['name']; 
		print("\n");
	    echo get_lang("LoginsToTheCourse")." : ". $num_users_online." - ".get_lang("Tutor")." : ".$course_info['titular'];
	    print("\n");
		}

		$cnt_i = 0;
		$largest_row = 0;
		if($num_scenario > 0){
			print("\n");
			echo '<span style="background:#CCC;">'.get_lang("ScenarioOverview").'</span>';
			print("\n");
			while($row_scenario = Database::fetch_array($res_scenario)){
				$step_id = $row_scenario['id'];
				$step_name = $row_scenario['step_name'];

				$cnt_j = 0;
				$foo[$cnt_i][$cnt_j] = $step_name;
				$cnt_j++;

				$sql_activity = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." ORDER BY activity_created_order";
				$res_activity = Database::query($sql_activity, __FILE__, __LINE__);
				while($row_activity = Database::fetch_array($res_activity)) {
					$activity_id = $row_activity['id'];
					$activity_step_id = $row_activity['step_id'];
					$activity_type = $row_activity['activity_type'];
					$activity_ref = $row_activity['activity_ref'];
					$activity_created_order = $row_activity['activity_created_order'];
					$activity_name = $row_activity['activity_name'];

					$sql_view = "SELECT status, score FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE activity_id = ".$activity_id." AND user_id = ".$user_id." AND step_id = ".$step_id;
					$res_view = Database::query($sql_view, __FILE__, __LINE__);
					$scenario_status = Database::result($res_view, 0, 0);
					$scenario_score = Database::result($res_view, 0, 1);

					if($activity_type == 'face2face'){
						$sql_ff = "SELECT max_score FROM $TBL_FACE2FACE WHERE id = ".$activity_ref;
						$res_ff = Database::query($sql_ff, __FILE__, __LINE__);
						$activity_max_score = Database::result($res_ff,0,0);
					}
					else if($activity_type == 'quiz'){
						$activity_max_score = get_quiz_total_weight($activity_ref, $user_id, $code, $session_id);
					}
					else if($activity_type == 'module'){

						$sql_mod = "SELECT count(id) as count FROM $lp_item_table WHERE (item_type = 'quiz' OR item_type = 'sco') AND lp_id = ".$activity_ref;
						$result_have_quiz = Database::query($sql_mod, __FILE__, __LINE__);
						if (Database::num_rows($result_have_quiz) > 0 ) {
							$row = Database::fetch_array($result_have_quiz,'ASSOC');
							if (is_numeric($row['count']) && $row['count'] != 0) {
								$activity_max_score = 100;
							}
							else {
								$activity_max_score = 'n.a';
							}					
						}
					}

					$foo[$cnt_i][$cnt_j] = $activity_name.'~'.$scenario_status.'~'.$scenario_score.'~'.$activity_type.'~'.$activity_max_score;
					$cnt_j++;
				}
				$cnt_i++;
				if($cnt_j > $largest_row){
					$largest_row = $cnt_j;
				}
			}

			$row = $largest_row;
			$col = $cnt_i;
			
			echo '<div style="width:auto;max-width:900px;border:1px solid #eaeaea;overflow:auto;">';		
			echo '<table border="1" class="table_scenario">';

			for( $i = 0; $i < $row; $i++ )
			{
				echo '<tr>';
				for( $j = 0; $j < $col; $j++ ) {  
						if($i == 0){
							echo '<td><b>'.api_convert_encoding($foo[$j][$i],api_get_system_encoding(),'UTF-8').'</b></td>';        
						}
						else {
						list($activity_name, $activity_status, $activity_score, $activity_type, $activity_max_score) = explode("~",$foo[$j][$i]);					

						if($activity_status == 'completed'){

							if($activity_type == 'face2face' || $activity_type == 'quiz' || $activity_type == 'module'){
								echo '<td style="background:#579D1C;color:#FFF;">'.$activity_name;
								if($activity_max_score == 'n.a'){
									echo '<div style="text-align:bottom;text-align:center;">'.$activity_max_score.'</div></td>'; 
								}
								else {
									echo '<div style="text-align:bottom;text-align:center;">'.$activity_score.'/'.$activity_max_score.'</div></td>'; 
								}
							}
							else {
								echo '<td style="background:#579D1C;color:#FFF;">'.$activity_name.'</td>'; 
							}					
						}
						else {		
							
							echo '<td>'.$activity_name.'</td>'; 						
						}
						}
				}
				echo '</tr>';
			}

			echo '</table><br>';		
			echo '</div><br>';
		}
	    
		if(sizeof($modules) > 0){
	    echo dataformat(get_lang("Module"));
    	echo dataformat(get_lang("Time"));                                        
    	echo dataformat(get_lang("Progress"));
    	echo dataformat(get_lang("Score"));
		print("\n");
		}
		/*if(count($modules)==0){
			$schema_insert = "";	
			$schema_insert .= dataformat(get_lang("NoRecords"));
			$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}	
		else{*/
			foreach($modules as $module) {
				$module_time = specific_modules_time($user_id, $code, $module['id']);
				$module_progress = specific_modules_progress($user_id, $code, $module['id']);
				$module_score = Tracking :: get_avg_student_score($user_id, $code, array ($module['id']));
				//echo 'sc=='.$module_score = Tracking :: get_average_test_scorm_and_lp($user_id, $code);

				if (empty ($module_score)) {
					$module_score = 0;
				}

				if (empty ($module_progress)) {
					$module_progress = 0;
				}

				if($module_score != '0' && $module_score == "-"){
					$module_score = "n.a";
				}
				else {
					$module_score = $module_score." %";
				}
			
				$schema_insert = "";
				if(!empty($course_search)){
				$schema_insert .= dataformat(api_convert_encoding($module['name'],api_get_system_encoding(),'UTF-8'));
				}
				else {
				$schema_insert .= dataformat($module['name']);
				}
				$schema_insert .= dataformat(display_time_format($module_time));
				$schema_insert .= dataformat($module_progress);
				$schema_insert .= dataformat($module_score);
			
				$schema_insert = str_replace($sep."$", "", $schema_insert);
 				$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        		$schema_insert .= "\t";
        		print(trim($schema_insert));
        		print "\n";
			}
		//}	
		print "\n";	
		if(sizeof($quizzes) > 0) {
		echo dataformat(get_lang("StandaloneQuiz"));
		echo dataformat(get_lang("Attempts"));
		echo dataformat(get_lang("Score"));
		echo dataformat(get_lang("Time"));
		print("\n");
		}
		/*if(count($quizzes)==0){
			$schema_insert = "";	
			$schema_insert .= dataformat(get_lang("NoRecords"));
			$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}
		else{*/
			foreach($quizzes as $quiz) {
				$attempts = get_quiz_attempts($quiz['id'], $code, $user_id, '', $session_id);
				$quiz_score = get_score($quiz['id'], $user_id, $code, $session_id);
				$quiz_time = get_quiz_total_time($quiz['id'], $code, $user_id, $session_id);

				if($quiz_score != '/') {
					$quiz_score = round(($quiz_score*100),2).' %';
				}
				$schema_insert = "";
				if(!empty($course_search)){
				$schema_insert .= dataformat(api_convert_encoding($quiz['title'],api_get_system_encoding(),'UTF-8'));
				}
				else {
				$schema_insert .= dataformat($quiz['title']);
				}
				//$schema_insert .= dataformat($quiz['title']);
				$schema_insert .= dataformat($attempts);
				$schema_insert .= dataformat($quiz_score);
				$schema_insert .= dataformat($quiz_time);
				$schema_insert = str_replace($sep."$", "", $schema_insert);
 				$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        		$schema_insert .= "\t";
        		print(trim($schema_insert));
        		print "\n";
			}
		//}
		print "\n";
	}
}

#Print learners report list
function printlearnersreport(){
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Learners</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
			.table_scenario {
				width: auto;
				border-collapse: collapse;
			}
			.table_scenario td{
				border:1px solid #eaeaea;				
				min-width: 100px;
				min-height: 50px;
				border-bottom:2px solid #eaeaea;
				border-top:2px solid #eaeaea;
				border-right:2px solid #eaeaea;
				border-left:2px solid #eaeaea;
			}
		</style>
	</head>
	<body>';
	$course_code = $_GET['course_code'];
	if(empty($course_code))
		$course_code = 0;
	$session_id = $_GET['sessionId'];
	if(empty($session_id)){
		$session_id = 0;
	}
	$user_id = $_GET['userid'];
	$user_info = api_get_user_info($user_id);
	if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
		$course_search = $_GET['course_search'];
	}
	else {
		$course_search = '';
	}

	$user_details = api_get_user_info_from_username($user_info['username']);
	$first_connection_date = Tracking :: get_first_connection_date($user_id);
	$last_connection_date = Tracking :: get_last_connection_date($user_id, true);
	$time_spent = get_time_spent($user_id, $course_code, $session_id, $course_search);

	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);

	if(!empty($course_search)){
		$search_codes = get_search_coursecodes($course_search);		
	}
	if($course_code == '0'){	
		$sql = "SELECT DISTINCT(course_code) FROM $course_user_table cru WHERE cru.user_id = '".$user_id."'";
		if(sizeof($search_codes) <> 0){
			$sql .= " AND course_code IN ('".implode("','",$search_codes)."')";
		}
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$course_codes[] = $row['course_code'];
		}
	}
	else {
		$course_codes[] = $course_code;
	}

	$progress = get_student_progress($user_id, $course_code, $session_id, $course_search);
	//list($module_progress, $percentage) = split(" ",$progress);
	//$mod_progress = round(($module_progress / sizeof($course_codes)),2);
	$score = get_student_score($user_id, $course_code, $session_id, $course_search);
	$quiz_score = get_student_quiz_score($user_id, $course_code, $session_id, $course_search);

	if($user_details['active'] == 1) {
		$status = get_lang('Active');
	}
	elseif($user_details['active'] == 0) {
		$status = get_lang('Inactive');
	}

	if(empty($first_connection_date)) {
		$first_connection_date = '/';
	}
	if(empty($last_connection_date)) {
		$last_connection_date = '/';
	}

	if($score == '- %'){
		$score = 'n.a';
	}
	if($last_connection_date == '/') {
		$time_spent = 'n.a';
		$progress = 'n.a';
		$score = 'n.a';
		$quiz_score = 'n.a';
	}

	$sysdir_array = UserManager::get_user_picture_path_by_id($user_id,'system',false,true);
	$sysdir = $sysdir_array['dir'];
	$webdir_array = UserManager::get_user_picture_path_by_id($user_id,'web',false,true);
	$webdir = $webdir_array['dir'];
	$fullurl=$webdir.$webdir_array['file'];
	$system_image_path=$sysdir.$webdir_array['file'];
	list($width, $height, $type, $attr) = @getimagesize($system_image_path);
	$resizing = (($height > 200) ? 'height="200"' : '');
	$height += 30;
	$width += 30;
	
	echo "<h3>".get_lang('IndividualReporting')." : ".$user_info['lastname'].' '.$user_info['firstname'] ."<h3>";
	echo '<table>
			<thead>
				<tr>
					<th>'.get_lang("OverallInformation").'</th>
					<th>'.get_lang("AccessDetails").'</th>
					<th>'.get_lang("SendMail").'</th>						
				</tr>
			</thead>
		</table>

		<table border="0" width="100%">
			<tbody>
				<tr>
					<td valign="top" width="5%">';
	echo '<img src="'.$fullurl.'" '.$resizing.' alt="'.$alt.'"/></a>';
	echo '</td><td>';
	echo '<table width="100%" border="0">
			<tr><td>'.get_lang("Status").' :</td><td>'. $status.'</td></tr>
			<tr><td>'.get_lang("Email").' :</td><td>'.$user_info['mail'].'</td></tr>
			<tr><td>'.get_lang("FirstConnection").' :</td><td>'.$first_connection_date.'</td></tr>
			<tr><td>'.get_lang("LatestConnection").' :</td><td>'.$last_connection_date.'</td></tr>
			<tr><td>'.get_lang("TimeSpentInModules").' :</td><td>'.$time_spent.'</td></tr>
			<tr><td>'.get_lang("ScoreInModules").' :</td><td>'. $score.'</td></tr>
			<tr><td>'.get_lang("ProgressInModules").' :</td><td>'. $progress.'</td></tr>
			<tr><td>'.get_lang("QuizzesScore").' :</td><td>'.$quiz_score.'</td></tr>
		</table>
		</td>
		</tr>
		</tbody>
	</table>';
	
	foreach($course_codes as $code) {
		$modules = array();
		$quizzes = array();
		$course_info = api_get_course_info($code);
		$valid = 10;
		$current_date = date('Y-m-d H:i:s',time());
		$track_online_table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ONLINE);
		$lp_item_table      = Database :: get_course_table(TABLE_LP_ITEM, $course_info['dbName']);
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS, $course_info['dbName']);	
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $course_info['dbName']);
		$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $course_info['dbName']);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $course_info['dbName']);

		$sql_scenario = "SELECT * FROM $TBL_SCENARIO_STEPS WHERE session_id = 0 ORDER BY step_created_order";
		$res_scenario = Database::query($sql_scenario, __FILE__, __LINE__);
		$num_scenario = Database::num_rows($res_scenario);

		$query = "SELECT login_user_id,login_date FROM ".$track_online_table ." WHERE DATE_ADD(login_date,INTERVAL $valid MINUTE) >= '".$current_date."' AND course = '".$code."' ";
		$res = Database::query($query, __FILE__, __LINE__);
		$num_users_online = Database::num_rows($res);

		$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);	
		$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);	

		$sql_module = "SELECT id, name FROM $t_lp WHERE session_id = 0";
		if(!empty($session_id)) {
			$sql_module .= ' OR session_id = '.$session_id;
		}
		$res_module = Database::query($sql_module, __FILE__, __LINE__);
		while($module = Database::fetch_array($res_module, 'ASSOC')) {		
			$modules[] = $module;
		}	

		$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0";
		if(!empty($session_id)) {
			$sql_quiz .= ' OR session_id = '.$session_id;
		}
		$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
		while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {		
			$quizzes[] = $quiz;
		}

		if(sizeof($modules) > 0 || sizeof($quizzes) > 0) {
			if(!empty($session_id)) {
			$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
			$session_info = api_get_session_info($session_id);

			if ($session_id > 0) {
				$session_name = $session_info['name'];
				$session_coach_id = $session_info['id_coach'];
				// get coach of the course in the session
				$sql = 'SELECT id_user FROM ' . $tbl_session_course_user . '
								WHERE id_session=' . $session_id . '
								AND course_code = "' . Database :: escape_string($code) . '" AND status=2';
				$rs = Database::query($sql, __FILE__, __LINE__);
				$course_coachs = array();
				while ($row_coachs = Database::fetch_array($rs)) { $course_coachs[] = $row_coachs['id_user']; }
				if (!empty($course_coachs)) {
					$info_tutor_name = array();
					foreach ($course_coachs as $course_coach) {
						$coach_infos = UserManager :: get_user_info_by_id($course_coach);
						$info_tutor_name[] = api_get_person_name($coach_infos['firstname'], $coach_infos['lastname']);
					}
					$info_course['tutor_name'] = implode(",",$info_tutor_name);
				}
				elseif ($session_coach_id != 0) {
					$coach_infos = UserManager :: get_user_info_by_id($session_coach_id);
					$info_course['tutor_name'] = api_get_person_name($coach_infos['firstname'], $coach_infos['lastname']);
				}
				echo '<h5><b>'.$session_name.' - </b>';
			}
			else {
				echo '<h5>';
			}
			
			}
		
		if(!empty($course_search)){
			echo '<b>'.api_convert_encoding($course_info['name'],api_get_system_encoding(),'UTF-8').'</b></h5>';
		}
		else {
			echo '<b>'.$course_info['name'].'</b></h5>';
		}
		if(!empty($session_id)) {
		echo '<h5>'.get_lang("LoginToTheCourse"). ' : ' .$num_users_online." - ".get_lang("Tutor").' : '.$info_course['tutor_name'].'</h5>';
		}	
		else {
		echo get_lang("LoginToTheCourse").' : '.$num_users_online.' - '.get_lang("Tutor").' : '. $course_info['titular'].'</h5>';
		}
	}

		$cnt_i = 0;
		$largest_row = 0;
		if($num_scenario > 0){
			echo '<h5>'.get_lang("ScenarioOverview").'</h5>';
			while($row_scenario = Database::fetch_array($res_scenario)){
				$step_id = $row_scenario['id'];
				$step_name = $row_scenario['step_name'];

				$cnt_j = 0;
				$foo[$cnt_i][$cnt_j] = $step_name;
				$cnt_j++;

				$sql_activity = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." ORDER BY activity_created_order";
				$res_activity = Database::query($sql_activity, __FILE__, __LINE__);
				while($row_activity = Database::fetch_array($res_activity)) {
					$activity_id = $row_activity['id'];
					$activity_step_id = $row_activity['step_id'];
					$activity_type = $row_activity['activity_type'];
					$activity_ref = $row_activity['activity_ref'];
					$activity_created_order = $row_activity['activity_created_order'];
					$activity_name = $row_activity['activity_name'];

					$sql_view = "SELECT status, score FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE activity_id = ".$activity_id." AND user_id = ".$user_id." AND step_id = ".$step_id;
					$res_view = Database::query($sql_view, __FILE__, __LINE__);
					$scenario_status = Database::result($res_view, 0, 0);
					$scenario_score = Database::result($res_view, 0, 1);

					if($activity_type == 'face2face'){
						$sql_ff = "SELECT max_score FROM $TBL_FACE2FACE WHERE id = ".$activity_ref;
						$res_ff = Database::query($sql_ff, __FILE__, __LINE__);
						$activity_max_score = Database::result($res_ff,0,0);
					}
					else if($activity_type == 'quiz'){
						$activity_max_score = get_quiz_total_weight($activity_ref, $user_id, $code, $session_id);
					}
					else if($activity_type == 'module'){

						$sql_mod = "SELECT count(id) as count FROM $lp_item_table WHERE (item_type = 'quiz' OR item_type = 'sco') AND lp_id = ".$activity_ref;
						$result_have_quiz = Database::query($sql_mod, __FILE__, __LINE__);
						if (Database::num_rows($result_have_quiz) > 0 ) {
							$row = Database::fetch_array($result_have_quiz,'ASSOC');
							if (is_numeric($row['count']) && $row['count'] != 0) {
								$activity_max_score = 100;
							}
							else {
								$activity_max_score = 'n.a';
							}					
						}
					}

					$foo[$cnt_i][$cnt_j] = $activity_name.'~'.$scenario_status.'~'.$scenario_score.'~'.$activity_type.'~'.$activity_max_score;
					$cnt_j++;
				}
				$cnt_i++;
				if($cnt_j > $largest_row){
					$largest_row = $cnt_j;
				}
			}

			$row = $largest_row;
			$col = $cnt_i;
			
			echo '<div style="width:auto;border:0px solid #eaeaea;">';		
			echo '<table border="1" class="table_scenario">';

			for( $i = 0; $i < $row; $i++ )
			{
				echo '<tr>';
				for( $j = 0; $j < $col; $j++ ) {  
						if($i == 0){
							echo '<td><b>'.$foo[$j][$i].'</b></td>';        
						}
						else {
						list($activity_name, $activity_status, $activity_score, $activity_type, $activity_max_score) = explode("~",$foo[$j][$i]);					

						if($activity_status == 'completed'){

							if($activity_type == 'face2face' || $activity_type == 'quiz' || $activity_type == 'module'){
								echo '<td style="background:#579D1C;color:#FFF;">'.$activity_name;
								if($activity_max_score == 'n.a'){
									echo '<div style="text-align:bottom;text-align:center;">'.$activity_max_score.'</div></td>'; 
								}
								else {
									echo '<div style="text-align:bottom;text-align:center;">'.$activity_score.'/'.$activity_max_score.'</div></td>'; 
								}
							}
							else {
								echo '<td style="background:#579D1C;color:#FFF;">'.$activity_name.'</td>'; 
							}
						
						}
						else {		
							
							echo '<td>'.$activity_name.'</td>'; 						
						}
						}
				}
				echo '</tr>';
			}

			echo '</table>';		
			echo '</div><br>';
		}

		if(count($modules)>0){
		echo	' <table width="80%" cellspacing="0" cellpadding="4" border="1">
				<thead>
					<tr>
						<th>'.get_lang("Module").'</th>
						<th>'.get_lang("Time").'</th>
						<th>'.get_lang("Progress").'</th>
						<th>'.get_lang("Score").'</th>
					</tr>
				</thead>
			<tbody>';
		}
		if(count($modules)>0){
			foreach($modules as $module) {
				$module_time = specific_modules_time($user_id, $code, $module['id']);
				$module_progress = specific_modules_progress($user_id, $code, $module['id']);
				$module_score = Tracking :: get_avg_student_score($user_id, $code, array ($module['id']));
				//echo 'sc=='.$module_score = Tracking :: get_average_test_scorm_and_lp($user_id, $code);

				if (empty ($module_score)) {
					$module_score = 0;
				}

				if (empty ($module_progress)) {
					$module_progress = 0;
				}

				if($module_score != '0' && $module_score == "-"){
					$module_score = "n.a";
				}
				else {
					$module_score = $module_score." %";
				}

				echo "<tr>";
				if(!empty($course_search)){
					echo "<td>".api_convert_encoding($module['name'],api_get_system_encoding(),'UTF-8')."</td>";
				}
				else {
					echo "<td>".$module['name']."</td>";
				}
				echo "	<td align='center'>".display_time_format($module_time)."</td>
						<td align='center'>".$module_progress." %</td>
						<td align='center'>".$module_score."</td> 
				  	</tr>";	
			}
		}
		/*else{
			echo "<tr><td colspan='4'>".get_lang("NoRecords")."</td></tr>";
		}*/
		echo "</tbody>
				</table>";
		echo "</br>";
		if(count($quizzes)>0){
		echo '<table width="80%" cellspacing="0" cellpadding="4" border="1">
		<thead>
			<tr>
				<th>'.get_lang("StandaloneQuiz").'</th>
				<th>'.get_lang("Attempts").'</th>
				<th>'.get_lang("Score").'</th>
				<th>'.get_lang("Time").'</th>
			</tr>
		</thead>
		<tbody>';
		}
		if(count($quizzes)>0){
			foreach($quizzes as $quiz) {
				$attempts = get_quiz_attempts($quiz['id'], $code, $user_id, '', $session_id);
				$quiz_score = get_score($quiz['id'], $user_id, $code, $session_id);
				$quiz_time = get_quiz_total_time($quiz['id'], $code, $user_id, $session_id);

				if($quiz_score != '/') {
					$quiz_score = round(($quiz_score*100),2).' %';
				}

				echo "<tr>";
				if(!empty($course_search)){
					echo "<td>".api_convert_encoding($quiz['title'],api_get_system_encoding(),'UTF-8')."</td>";
				}
				else {
					echo "<td>".$quiz['title']."</td>";
				}
				echo "	<td align='center'>".$attempts."</td>
						<td align='center'>".$quiz_score."</td>
						<td align='center'>".$quiz_time."</td> 
				  	</tr>";	
			}
		}
		/*else{
			echo "<tr><td colspan='4'>".get_lang("NoRecords")."</td></tr>";
		}*/
		echo "</tbody>
				</table></body></html>";
	}
}

#Export learners list
function exportlearnerslist($quiz_id,$code,$attempt_id,$session_id){
	$filename = "learners_list";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	
 	$course_info = api_get_course_info($code);
	$course_user_table              = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);    
	$users = array();

	$sql = "SELECT title FROM $table_quiz WHERE id = ".$quiz_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$quiz_title = Database::result($res, 0, 0);

	if($session_id == 0) {
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['user_id'];
		}
	}
	else {
		$sql = "SELECT id_user FROM $session_course_user_table scru WHERE scru.course_code = '".$code."' AND id_session = ".$session_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['id_user'];
		}
	}

	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
		$users = array();
		$users[] = api_get_user_id();
	}
	
	echo get_lang("Quizzes")." :\t".$quiz_title."\t";
	print "\n\n";
	//start of printing column names
	echo dataformat(get_lang("LastName"));
    echo dataformat(get_lang("FirstName"));                                      
    echo dataformat(get_lang("Score"));
    echo dataformat(get_lang("Time"));
    echo dataformat(get_lang("Attempts"));
	print("\n");
	
	if($attempt_id == 0) {
        if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
		$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
		}
		else {
		$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".api_get_user_id();
		}
        $res = Database::query($sql, __FILE__, __LINE__);
        $numrows = Database::num_rows($res);
        if($numrows>0){
        	while($row = Database::fetch_array($res)) {
         		$schema_insert = '';
            	$user_id = $row['exe_user_id'];
            	$exe_id = $row['exe_id'];
            	$user_info = api_get_user_info($user_id);
            	$quiz_score = get_user_quiz_score($user_id, $quiz_id, $code, $attempt_id, $exe_id, $session_id);
				if($quiz_score == '0') {
					$quiz_score = $quiz_score.' %';
				}
            	if($quiz_score != '/') {
                	$quiz_score = round(($quiz_score*100),2).' %';
            	}
            	$quiz_time = get_user_quiz_time($quiz_id, $code, $user_id, $attempt_id, $exe_id, $session_id);
    			$attempts = get_quiz_attempts($quiz_id, $code, $user_id, $exe_id, $session_id);
    			$schema_insert .= dataformat($user_info['lastname']);
    			$schema_insert .= dataformat($user_info['firstname']);
    			$schema_insert .= dataformat($quiz_score);
    			$schema_insert .= dataformat($quiz_time);
    			$schema_insert .= dataformat($attempts);
    		
    			$schema_insert = str_replace($sep."$", "", $schema_insert);
 				$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        		$schema_insert .= "\t";
        		print(trim($schema_insert));
        		print "\n";
        	}
    	}
    	else{
			$schema_insert = "";	
			$schema_insert .= dataformat(get_lang("NoRecords"));
			$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}
    }
    else {
     	if(count($users)>0){
        	foreach($users as $user_id) {
         		$schema_insert = '';
        		$user_info = api_get_user_info($user_id);
            	$quiz_score = get_user_quiz_score($user_id, $quiz_id, $code, $attempt_id, $exe_id, $session_id);
            	if($quiz_score != '/') {
                	$quiz_score = round(($quiz_score*100),2).' %';
            	}
            	$quiz_time = get_user_quiz_time($quiz_id, $code, $user_id, $attempt_id, $exe_id, $session_id);
            	$attempts = get_quiz_attempts($quiz_id, $code, $user_id, $exe_id, $session_id);
            	$schema_insert .= dataformat($user_info['lastname']);
    			$schema_insert .= dataformat($user_info['firstname']);
    			$schema_insert .= dataformat($quiz_score);
    			$schema_insert .= dataformat($quiz_time);
    			$schema_insert .= dataformat($attempts);
    		
    			$schema_insert = str_replace($sep."$", "", $schema_insert);
 				$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        		$schema_insert .= "\t";
        		print(trim($schema_insert));
        		print "\n";
        	}
        }
    	else{
			$schema_insert = "";	
			$schema_insert .= dataformat(get_lang("NoRecords"));
			$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}
    }	
}

#Print learners report list
function printlearnerslist($quiz_id,$code,$attempt_id,$session_id){
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Learners</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
		</style>
	</head>
	<body>';
	$course_info = api_get_course_info($code);
	$course_user_table              = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);    
	$users = array();

	$sql = "SELECT title FROM $table_quiz WHERE id = ".$quiz_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$quiz_title = Database::result($res, 0, 0);

	if($session_id == 0) {
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['user_id'];
		}
	}
	else {
		$sql = "SELECT id_user FROM $session_course_user_table scru WHERE scru.course_code = '".$code."' AND id_session = ".$session_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['id_user'];
		}
	}
	
	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
		$users = array();
		$users[] = api_get_user_id();
	}
	echo "<h4>".get_lang("Quiz")." : ".$quiz_title."<h4>";
	echo '<table border="1" width="70%" cellpadding="4" cellspacing="0">
        <thead>
                <tr>
                        <th>'.get_lang("LastName").'</th>
                        <th>'.get_lang("FirstName").'</th>
                        <th>'.get_lang("Score").'</th>
                        <th>'.get_lang("Time").'</th>
                        <th>'.get_lang("Attempts").'</th>
                </tr>
        </thead>
        <tbody>';
		
	if($attempt_id == 0) {
        if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
		$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
		}
		else {
		$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".api_get_user_id();
		}
        $res = Database::query($sql, __FILE__, __LINE__);
        $numrows = Database::num_rows($res);
        if($numrows>0){
        	while($row = Database::fetch_array($res)) {
        		$user_id = $row['exe_user_id'];
            	$exe_id = $row['exe_id'];
            	$user_info = api_get_user_info($user_id);
            	$quiz_score = get_user_quiz_score($user_id, $quiz_id, $code, $attempt_id, $exe_id, $session_id);
				if($quiz_score == '0') {
					$quiz_score = $quiz_score.' %';
				}
            	if($quiz_score != '/') {
                	$quiz_score = round(($quiz_score*100),2).' %';
            	}
            	$quiz_time = get_user_quiz_time($quiz_id, $code, $user_id, $attempt_id, $exe_id, $session_id);
            	$attempts = get_quiz_attempts($quiz_id, $code, $user_id, $exe_id, $session_id);
            	echo "<tr>
                    	<td>".$user_info['lastname']."</td>
                    	<td>".$user_info['firstname']."</td>
                    	<td align='center'>".$quiz_score."</td>
                    	<td align='center'>".$quiz_time."</td>
                    	<td align='center'>".$attempts."</td>                    	
                	</tr>";                               
        	}
        }
    	else{
			echo "<tr><td colspan='6'>".get_lang("NoRecords")."</td></tr>";
		}
    }
    else {
     	if(count($users)>0){
        	foreach($users as $user_id) {
            	$user_info = api_get_user_info($user_id);
            	$quiz_score = get_user_quiz_score($user_id, $quiz_id, $code, $attempt_id, '', $session_id);
            	if($quiz_score != '/') {
                	$quiz_score = round(($quiz_score*100),2).' %';
            	}
            	$quiz_time = get_user_quiz_time($quiz_id, $code, $user_id, $attempt_id, '', $session_id);
            	$attempts = get_quiz_attempts($quiz_id, $code, $user_id, '', $session_id);
            	echo "<tr>
                    	<td>".$user_info['lastname']."</td>
                		<td>".$user_info['firstname']."</td>
                    	<td align='center'>".$quiz_score."</td>
                		<td align='center'>".$quiz_time."</td>
                		<td align='center'>".$attempts."</td>
                	</tr>";
        	}
        }
    	else{
			echo "<tr><td colspan='6'>".get_lang("NoRecords")."</td></tr>";
		}
    }
    echo "</tbody></table>";
}

#Export learners list
function exportlearnerslistff($ff_id,$code,$session_id){
	$filename = "learners_list_ff";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	
 	$course_info = api_get_course_info($code);
	$course_user_table              = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);	
	$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $course_info['dbName']); 
	$users = array();	

	$sql = "SELECT name, max_score FROM $TBL_FACE2FACE WHERE id = ".$ff_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$ff_name = Database::result($res, 0, 0);
	$ff_max_score = Database::result($res, 0, 1);

	if($session_id == 0) {
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['user_id'];
		}
	}
	else {
		$sql = "SELECT id_user FROM $session_course_user_table scru WHERE scru.course_code = '".$code."' AND id_session = ".$session_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['id_user'];
		}
	}

	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
		$users = array();
		$users[] = api_get_user_id();
	}
	
	echo get_lang("Facetoface")." :\t".$ff_name."\t";
	print "\n\n";
	//start of printing column names
	echo dataformat(get_lang("LastName"));
    echo dataformat(get_lang("FirstName"));                                      
    echo dataformat(get_lang("Score")." (".$ff_max_score.")");    
	print("\n");

		if(sizeof($users) > 0){
			foreach($users as $user_id) {
					$schema_insert = '';
					$score = 0;
					$user_info = api_get_user_info($user_id);
					$score = get_facetoface_score($user_id, $ff_id, $code);
					if(empty($score)){
						$score = 0;
					}

					$schema_insert .= dataformat($user_info['lastname']);
					$schema_insert .= dataformat($user_info['firstname']);
					$schema_insert .= dataformat($score);					
				
					$schema_insert = str_replace($sep."$", "", $schema_insert);
					$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
					$schema_insert .= "\t";
					print(trim($schema_insert));
					print "\n";				
			}
		}
    	else{
			$schema_insert = "";	
			$schema_insert .= dataformat(get_lang("NoRecords"));
			$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}
}

#Print learners report list
function printlearnerslistff($ff_id,$code,$session_id){
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Learners</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
		</style>
	</head>
	<body>';
	$course_info = api_get_course_info($code);
	$course_user_table              = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $course_info['dbName']); 
	$users = array();	

	$sql = "SELECT name, max_score FROM $TBL_FACE2FACE WHERE id = ".$ff_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$ff_name = Database::result($res, 0, 0);
	$ff_max_score = Database::result($res, 0, 1);

	if($session_id == 0) {
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0 AND status = 5";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['user_id'];
		}
	}
	else {
		$sql = "SELECT id_user FROM $session_course_user_table scru WHERE scru.course_code = '".$code."' AND id_session = ".$session_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['id_user'];
		}
	}
	
	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
		$users = array();
		$users[] = api_get_user_id();
	}
	echo "<h4>".get_lang("Facetoface")." : ".$ff_name."<h4>";
	echo '<table border="1" width="70%" cellpadding="4" cellspacing="0">
        <thead>
                <tr>
                        <th>'.get_lang("LastName").'</th>
                        <th>'.get_lang("FirstName").'</th>
                        <th>'.get_lang("Score")." (".$ff_max_score.")".'</th>                        
                </tr>
        </thead>
        <tbody>';
		
	
        	foreach($users as $user_id) {
            	$score = 0;
				$user_info = api_get_user_info($user_id);
				$score = get_facetoface_score($user_id, $ff_id, $code);
				if(empty($score)){
					$score = 0;
				}

            	echo "<tr>
                    	<td>".$user_info['lastname']."</td>
                		<td>".$user_info['firstname']."</td>
                    	<td align='center'>".$score."</td>                		
                	</tr>";
        	}
       
    echo "</tbody></table>";
}

#Print Module learners report list
function printmodulelearnerslist($lp_id,$code,$session_id){
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Learners</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
		</style>
	</head>
	<body>';
	$course_info = api_get_course_info($code);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	//$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$table_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);	
	$TBL_LP_VIEW = Database :: get_course_table(TABLE_LP_VIEW, $course_info['dbName']);
	$users = array();

	$sql = "SELECT name FROM $table_lp WHERE id = ".$lp_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$lp_name = Database::result($res, 0, 0);

	if($session_id == 0) {
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0 AND status = 5";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['user_id'];
		}
	}
	else {
		$sql = "SELECT id_user FROM $session_course_user_table scru WHERE scru.course_code = '".$code."' AND id_session = ".$session_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['id_user'];
		}
	}

	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
		$users = array();
		$users[] = api_get_user_id();
	}
	echo "<h4>".get_lang("Quiz")." : ".$lp_name."<h4>";
	echo '<table border="1" width="70%" cellpadding="4" cellspacing="0">
        <thead>
                <tr>
                        <th>'.get_lang("LastName").'</th>
                        <th>'.get_lang("FirstName").'</th>
                        <th>'.get_lang("Time").'</th>
                        <th>'.get_lang("Progress").'</th>
                        <th>'.get_lang("Score").'</th>
                </tr>
        </thead>
        <tbody>';
	if(count($users)>0){
		foreach($users as $user_id) {
				$user_info = api_get_user_info($user_id);
				$module_time = specific_modules_time($user_id, $code, $lp_id);
				$module_progress = specific_modules_progress($user_id, $code, $lp_id);
				$module_score = Tracking :: get_avg_student_score($user_id, $code, array ($lp_id));
				//echo 'sc=='.$module_score = Tracking :: get_average_test_scorm_and_lp($user_id, $code);

				$sql_view = "SELECT max(view_count) AS count FROM $TBL_LP_VIEW WHERE lp_id = ".$lp_id." AND user_id = '" . $user_id . "'";
				$res_view = Database::query($sql_view, __FILE__, __LINE__);
				$view_count = Database::result($res_view,0,0);

				if (empty ($module_score)) {
					$module_score = 0;
				}

				if (empty ($module_progress)) {
					$module_progress = 0;
				}

				if($module_score != '0' && $module_score == "-"){
					$module_score = "n.a";
				}
				else {
					$module_score = $module_score." %";
				}
				echo "<tr>
						<td>".$user_info['lastname']."</td>
						<td>".$user_info['firstname']."</td>
						<td align='center'>".display_time_format($module_time)."</td>
						<td align='center'>".$module_progress."</td>
						<td align='center'>".$module_score."</td>";						
						
					  echo "</tr>";	
			}	
	}
	else {
		echo "<tr><td colspan='5'>".get_lang("NoRecords")."</td></tr>";
	}
    echo "</tbody></table>";
}

#Export module learners list
function exportmodulelearnerslist($lp_id,$code,$session_id){
	$filename = "module_learners_list";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	
 	$course_info = api_get_course_info($code);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	//$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$table_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);	
	$TBL_LP_VIEW = Database :: get_course_table(TABLE_LP_VIEW, $course_info['dbName']);
	$users = array();

	$sql = "SELECT name FROM $table_lp WHERE id = ".$lp_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$lp_name = Database::result($res, 0, 0);

	if($session_id == 0) {
		$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['user_id'];
		}
	}
	else {
		$sql = "SELECT id_user FROM $session_course_user_table scru WHERE scru.course_code = '".$code."' AND id_session = ".$session_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)) {
			$users[] = $row['id_user'];
		}
	}

	if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
		$users = array();
		$users[] = api_get_user_id();
	}
	
	echo get_lang("Modules")." :\t".$lp_name."\t";
	print "\n\n";
	//start of printing column names
	echo dataformat(get_lang("LastName"));
    echo dataformat(get_lang("FirstName"));                                      
    echo dataformat(get_lang("Time"));
    echo dataformat(get_lang("Progress"));
    echo dataformat(get_lang("Score"));
	print("\n");
	
	if(count($users)>0){
		foreach($users as $user_id) {
			$schema_insert = '';
			$user_info = api_get_user_info($user_id);
			$module_time = specific_modules_time($user_id, $code, $lp_id);
			$module_progress = specific_modules_progress($user_id, $code, $lp_id);
			$module_score = Tracking :: get_avg_student_score($user_id, $code, array ($lp_id));
			//echo 'sc=='.$module_score = Tracking :: get_average_test_scorm_and_lp($user_id, $code);

			$sql_view = "SELECT max(view_count) AS count FROM $TBL_LP_VIEW WHERE lp_id = ".$lp_id." AND user_id = '" . $user_id . "'";
			$res_view = Database::query($sql_view, __FILE__, __LINE__);
			$view_count = Database::result($res_view,0,0);

			if (empty ($module_score)) {
				$module_score = 0;
			}

			if (empty ($module_progress)) {
				$module_progress = 0;
			}

			if($module_score != '0' && $module_score == "-"){
				$module_score = "n.a";
			}
			else {
				$module_score = $module_score." %";
			}
			$schema_insert .= dataformat($user_info['lastname']);
			$schema_insert .= dataformat($user_info['firstname']);
			$schema_insert .= dataformat($module_time);
			$schema_insert .= dataformat($module_progress);
			$schema_insert .= dataformat($module_score);
		
			$schema_insert = str_replace($sep."$", "", $schema_insert);
			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
			$schema_insert .= "\t";
			print(trim($schema_insert));
			print "\n";
		}
	}
	else{
		$schema_insert = "";	
		$schema_insert .= dataformat(get_lang("NoRecords"));
		$schema_insert = str_replace($sep."$", "", $schema_insert);
		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
		$schema_insert .= "\t";
		print(trim($schema_insert));
		print "\n";
	}
    
}

#Export quizzes list
function exportfacetofaces(){
	$filename = "facetoface_report";         //File Name
	$file_ending = "csv";
 
	//header info for browser
	header("Content-Type: application/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
 
	/*******Start of Formatting for Excel*******/
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
 	
 	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	$user_id = $_GET['user_id'];
	if(empty($_GET['session']))
		$session_id = 0;
	else 
		$session_id = $_GET['session'];
			
	$course_code = $_GET['courses'];
		if(empty($course_code))
			$course_code = 0;
		if($course_code == '0') {		
			$facetofaces = get_facetoface_list($search, $user_id);
		}
		else {			

			$info_course = CourseManager :: get_course_information($course_code);
			$table_facetoface = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);			
			$where_added = 0;
			$sql_facetoface = "SELECT * FROM $table_facetoface WHERE session_id = 0";					
			$sql_facetoface .= " ORDER BY name";

			Database::query("SET NAMES 'utf8'");
			$res_facetoface = Database::query($sql_facetoface, __FILE__, __LINE__);
			while($facetoface = Database::fetch_array($res_facetoface, 'ASSOC')) {
				$facetofaces[] = $info_course['code'].'@'.$facetoface['id'].'@'.$facetoface['name'];
			}
		}

		$unique_facetoface = array();

		if(!empty($search)) {
			if(api_is_allowed_to_edit()){
				$sql = "SELECT code FROM $course_table";
				$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
			}
			else {
				$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
				if(api_is_allowed_to_create_course()){
					$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";
					$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
					$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
				}
				else {
					$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
				}
				$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
			}
			Database::query("SET NAMES 'utf8'");

			$res = Database::query($sql, __FILE__, __LINE__);
			$num_rows = Database::num_rows($res);
			if($num_rows <> 0) {			
				$search_course_code = Database::result($res,0,0);
				$course_code = $search_course_code;
				$info_course = CourseManager :: get_course_information($search_course_code);
				$table_facetoface = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);

				$sql_facetoface = "SELECT id, name FROM $table_facetoface ORDER BY name";
				$res_facetoface = Database::query($sql_facetoface, __FILE__, __LINE__);
				while($facetoface = Database::fetch_array($res_facetoface, 'ASSOC')) {
					$facetofaces[] = $info_course['code'].'@'.$facetoface['id'].'@'.$facetoface['name'];
				}
			}		
		}	

	//start of printing column names
	echo dataformat(get_lang("Facetoface"));
    echo dataformat(get_lang("Course"));                                        
    echo dataformat(get_lang("MaxScore"));  
    echo dataformat(get_lang("MinScore"));      
	print("\n");
	//end of printing column names
	if(count($facetofaces)==0){
		$schema_insert = "";	
		$schema_insert .= dataformat(get_lang("NoRecords"));
		$schema_insert = str_replace($sep."$", "", $schema_insert);
 		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
	}
	else{
		//start while loop to get data
		foreach($facetofaces as $facetoface) {	
			list($code,$facetoface_id,$facetoface_name) = split("@",$facetoface);
			
			$course_name = get_course_title($code);
			$max_score = get_facetoface_maxscore($facetoface_id, $code, $session_id);
			$min_score = get_facetoface_minscore($facetoface_id, $code, $session_id);

			if(empty($max_score)){
				$max_score = 0;
			}

			if(empty($min_score)){
				$min_score = 0;
			}

			$schema_insert = "";
	 	
	 		$schema_insert .= dataformat(api_convert_encoding($facetoface_name,api_get_system_encoding(),'UTF-8'));
	 		$schema_insert .= dataformat(api_convert_encoding($course_name,api_get_system_encoding(),'UTF-8'));
	 		$schema_insert .= dataformat($max_score);
	 		$schema_insert .= dataformat($min_score);
					
        	$schema_insert = str_replace($sep."$", "", $schema_insert);
 			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        	$schema_insert .= "\t";
        	print(trim($schema_insert));
        	print "\n";
		}
	}	
}

function printfacetofaces(){
	
	echo '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reporting - Face-to-face</title>
        <style>
        	table,tr,td{
				font-family:Arial;
				font-size:12px;
			}
		</style>
		<script type="text/javascript" src="js/canvasjs.min.js"></script>		
		<link type="text/css" rel="stylesheet" media="print" href="'.api_get_path(WEB_PATH).'main/css/'.api_get_setting('stylesheets').'/print.css" />
	</head>
	<body>';
	
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

	$theme_color = get_theme_color();
	
	if(isset($_GET['search']) && !empty($_GET['search'])) {
		$search = $_GET['search'];
	}
	else {
		$search = '';
	}
	$search = checkUTF8($search);
	$user_id = $_GET['user_id'];
	if(empty($_GET['session']))
		$session_id = 0;
	else 
		$session_id = $_GET['session'];
		
	$course_code = $_GET['courses'];
		if(empty($course_code))
			$course_code = 0;
		if($course_code == '0') {		
				$facetofaces = get_facetoface_list($search, $user_id);
			}
			else {			

				$info_course = CourseManager :: get_course_information($course_code);
				$table_facetoface = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);			
				$where_added = 0;
				$sql_facetoface = "SELECT * FROM $table_facetoface WHERE session_id = 0";					
				$sql_facetoface .= " ORDER BY name";

				Database::query("SET NAMES 'utf8'");
				$res_facetoface = Database::query($sql_facetoface, __FILE__, __LINE__);
				while($facetoface = Database::fetch_array($res_facetoface, 'ASSOC')) {
					$facetofaces[] = $info_course['code'].'@'.$facetoface['id'].'@'.$facetoface['name'];
				}
			}

			$unique_facetoface = array();

			if(!empty($search)) {
				if(api_is_allowed_to_edit()){
					$sql = "SELECT code FROM $course_table";
					$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
				}
				else {
					$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
					if(api_is_allowed_to_create_course()){
						$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";
						$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
						$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
					}
					else {
						$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
					}
					$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
				}
				Database::query("SET NAMES 'utf8'");

				$res = Database::query($sql, __FILE__, __LINE__);
				$num_rows = Database::num_rows($res);
				if($num_rows <> 0) {			
					$search_course_code = Database::result($res,0,0);
					$course_code = $search_course_code;
					$info_course = CourseManager :: get_course_information($search_course_code);
					$table_facetoface = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);

					$sql_facetoface = "SELECT id, name FROM $table_facetoface ORDER BY name";
					$res_facetoface = Database::query($sql_facetoface, __FILE__, __LINE__);
					while($facetoface = Database::fetch_array($res_facetoface, 'ASSOC')) {
						$facetofaces[] = $info_course['code'].'@'.$facetoface['id'].'@'.$facetoface['name'];
					}
				}		
			}		

			
			$chart_facetoface_arr = array();
	//echo '<div id="chartFacetofaceContainer" style="height:auto;min-height:300px;width:75%;padding-left:50px;"></div></br>';	
	echo '<table border="1" align="center" cellspacing="0" cellpadding="4" width="80%">';
	//start of printing column names
	echo '<tbody>
				<thead>
					<tr>
                        <th>'.get_lang("Facetoface").'</th>
						<th>'.get_lang("Course").'</th>
						<th>'.get_lang("MaxScore").'</th>
						<th>'.get_lang("MinScore").'</th>						
                    </tr>
				</thead>';
	//end of printing column names
 	if(count($facetofaces)==0){
		echo "<tr><td colspan='4'>".get_lang("NoRecords")."</td></tr>";
	}
	else{
		$chart_facetoface_arr = array();
		foreach($facetofaces as $facetoface) {	
			list($code,$facetoface_id,$facetoface_name) = split("@",$facetoface);
			
			$course_name = get_course_title($code);
			$max_score = get_facetoface_maxscore($facetoface_id, $code, $session_id);
			$min_score = get_facetoface_minscore($facetoface_id, $code, $session_id);

			if(empty($max_score)){
				$max_score = 0;
			}

			if(empty($min_score)){
				$min_score = 0;
			}
			
			echo "<tr>";
			if(!empty($search)){
				$course_name = api_convert_encoding($course_name,api_get_system_encoding(),'UTF-8');
				echo "<td>".api_convert_encoding($facetoface_name,api_get_system_encoding(),'UTF-8')."</td>";
			}
			else if($course_code == '0'){
				$course_name = api_convert_encoding($course_name,api_get_system_encoding(),'UTF-8');
				echo "<td>".api_convert_encoding($facetoface_name,api_get_system_encoding(),'UTF-8')."</td>";
			}
			else {
				echo "<td>".$quiz_name."</td>";
			}
			echo "	<td>".$course_name."</td>
					<td align='center'>".$max_score."</td>													
					<td align='center'>".$min_score."</td>					
				  </tr>";				
		}	}
	echo "</tbody></table></body></html>";	
	
}

function checkUTF8($string){
	$pattern = '/^[\p{L} ]+$/u';

	preg_match($pattern, $string, $matches);

	if (count($matches) <= 0) {
		$string = api_convert_encoding($string,'UTF-8',api_get_system_encoding());		  
	}
	return $string;
}

function get_theme_color() {
	/*$mycourseid = api_get_course_id();

	if (!empty($mycourseid) && $mycourseid != -1)
	{
		if (api_get_setting('allow_course_theme') == 'true')
		{
			$mycoursetheme = api_get_course_setting('course_theme', null, true);
		}
	}
	else {
			$mycoursetheme = api_get_setting('stylesheets');
	}*/

	$platform_theme = api_get_setting('stylesheets');

	if($platform_theme == 'dokeos2_black_tablet') {
		$theme_color = "#424242";
	}
	else if($platform_theme == 'dokeos2_blue_tablet') {
		$theme_color = "#003C77";
	}
	else if($platform_theme == 'dokeos2_medical_tablet') {
		$theme_color = "#134958";
	}
	else if($platform_theme == 'dokeos2_orange_tablet') {
		$theme_color = "#D66B00";
	}
	else if($platform_theme == 'dokeos2_red_tablet') {
		$theme_color = "#96040B";
	}
	else if($platform_theme == 'dokeos2_tablet') {
		$theme_color = "#1084A7";
	}
	else if($platform_theme == 'redhat_tablet') {
		$theme_color = "#cc0000";
	}
	else if($platform_theme == 'orkyn_tablet') {
		$theme_color = "#1084A7";
	}

	return $theme_color;
}