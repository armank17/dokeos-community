<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL

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
*	ExerciseResult class: This class allows to instantiate an object of type ExerciseResult
*	which allows you to export exercises results in multiple presentation forms
*	@package dokeos.exercise
* 	@author Yannick Warnier
* 	@version $Id: $
*/


if(!class_exists('ExerciseResult')):

class ExerciseResult
{
	private $exercises_list = array(); //stores the list of exercises
	private $results = array(); //stores the results
	
	/**
	 * constructor of the class
	 */
	public function ExerciseResult($get_questions=false,$get_answers=false)
	{
		//nothing to do
		/*
		$this->exercise_list = array();
		$this->readExercisesList();
		if($get_questions)
		{
			foreach($this->exercises_list as $exe)
			{
				$this->exercises_list['questions'] = $this->getExerciseQuestionList($exe['id']);
			}
		}
		*/
	}

	/**
	 * Reads exercises information (minimal) from the data base
	 * @param	boolean		Whether to get only visible exercises (true) or all of them (false). Defaults to false.
	 * @return	array		A list of exercises available
	 */
	private function _readExercisesList($only_visible = false)
	{
		$return = array();
    	$TBL_EXERCISES          = Database::get_course_table(TABLE_QUIZ_TEST);

		$sql="SELECT id,title,type,random,active FROM $TBL_EXERCISES";
		if($only_visible)
		{
			$sql.= ' WHERE active=1';
		}
		$sql .= ' ORDER BY title';
		$result=api_sql_query($sql,__FILE__,__LINE__);

		// if the exercise has been found
		while($row=Database::fetch_array($result,'ASSOC'))
		{
			$return[] = $row;
		}
		// exercise not found
		return $return;
	}

	/**
	 * Gets the questions related to one exercise
	 * @param	integer		Exercise ID
	 */
	private function _readExerciseQuestionsList($e_id)
	{
		$return = array();
    	$TBL_EXERCISE_QUESTION  = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
    	$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);
		$sql="SELECT q.id, q.question, q.ponderation, eq.question_order, q.type, q.picture " .
			" FROM $TBL_EXERCISE_QUESTION eq, $TBL_QUESTIONS q " .
			" WHERE eq.question_id=q.id AND eq.exercice_id='".Database::escape_string($e_id)."' " .
			" ORDER BY eq.question_order";
		$result=api_sql_query($sql,__FILE__,__LINE__);

		// fills the array with the question ID for this exercise
		// the key of the array is the question position
		while($row=Database::fetch_array($result,'ASSOC'))
		{
			$return[] = $row;
		}
		return true;		
	}
	/**
	 * Gets the results of all students (or just one student if access is limited)
	 * @param	string		The document path (for HotPotatoes retrieval)
	 * @param	integer		User ID. Optional. If no user ID is provided, we take all the results. Defauts to null
	 */
	function _getExercisesReporting($document_path,$user_id=null)
	{
		$return = array();
    	$TBL_EXERCISES          = Database::get_course_table(TABLE_QUIZ_TEST);
    	$TBL_EXERCISE_QUESTION  = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
    	$TBL_QUESTIONS 			= Database::get_course_table(TABLE_QUIZ_QUESTION);
		$TBL_USER          	    = Database::get_main_table(TABLE_MAIN_USER);
		$TBL_DOCUMENT          	= Database::get_course_table(TABLE_DOCUMENT);
		$TBL_ITEM_PROPERTY      = Database::get_course_table(TABLE_ITEM_PROPERTY);
		$TBL_TRACK_EXERCISES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$TBL_TRACK_HOTPOTATOES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTPOTATOES);
		$TBL_TRACK_ATTEMPT		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
		$tbl_course_rel_user	= Database::get_main_table(TABLE_MAIN_COURSE_USER);
		$tbl_e_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
		$tbl_quiz_questions = Database :: get_course_table(TABLE_QUIZ_QUESTION);

    	$cid = api_get_course_id();
		if(empty($user_id))
		{
			
			$session_id_and = '';
			if (api_get_session_id() != 0) {
				$session_id_and = ' AND session_id = ' . api_get_session_id() . ' ';
			}
			
			//get all results (ourself and the others) as an admin should see them
			//AND exe_user_id <> $_user['user_id']  clause has been removed
			$sql = "SELECT CONCAT(lastname,' ',firstname) as users, ce.title, te.exe_result ,
								 te.exe_weighting, UNIX_TIMESTAMP(te.exe_date), te.exe_id, email, UNIX_TIMESTAMP(te.start_date), steps_counter,cuser.user_id,te.exe_duration,te.exe_exo_id
						  FROM $TBL_EXERCISES AS ce , $TBL_TRACK_EXERCISES AS te, $TBL_USER AS user,$tbl_course_rel_user AS cuser
						  WHERE  user.user_id=cuser.user_id AND te.exe_exo_id = ce.id AND te.status != 'incomplete' AND cuser.user_id=te.exe_user_id AND te.exe_cours_id='" . Database :: escape_string($cid) . "'
						  AND cuser.status<>1 $session_id_and AND ce.active <>-1 AND orig_lp_id = 0 AND orig_lp_item_id = 0
						  AND cuser.course_code=te.exe_cours_id ORDER BY users, te.exe_cours_id ASC, ce.title ASC, te.exe_date DESC";
						

			$hpsql="SELECT CONCAT(tu.lastname,' ',tu.firstname), tth.exe_name,
						tth.exe_result , tth.exe_weighting, UNIX_TIMESTAMP(tth.exe_date), tu.email, tu.user_id
					FROM $TBL_TRACK_HOTPOTATOES tth, $TBL_USER tu
					WHERE  tu.user_id=tth.exe_user_id AND tth.exe_cours_id = '".$cid."'
					ORDER BY tth.exe_cours_id ASC, tth.exe_date ASC";

		}
		else
		{ // get only this user's results
			  $sql="SELECT '',ce.title, te.exe_result , te.exe_weighting, " .
			  		"UNIX_TIMESTAMP(te.exe_date),te.exe_id,te.exe_exo_id
				  FROM $TBL_EXERCISES ce , $TBL_TRACK_EXERCISES te
				  WHERE te.exe_exo_id = ce.id AND te.exe_user_id='".Database::escape_string($user_id)."' AND te.exe_cours_id='".Database::escape_string($cid)."'
				  ORDER BY te.exe_cours_id ASC, ce.title ASC, te.exe_date ASC";

			$hpsql="SELECT '',exe_name, exe_result , exe_weighting, UNIX_TIMESTAMP(exe_date)
					FROM $TBL_TRACK_HOTPOTATOES
					WHERE exe_user_id = '".Database::escape_string($user_id)."' AND exe_cours_id = '".Database::escape_string($cid)."'
					ORDER BY exe_cours_id ASC, exe_date ASC";

		}

		$results=getManyResultsXCol($sql,9);
		$hpresults=getManyResultsXCol($hpsql,7);

		$NoTestRes = 0;
		$NoHPTestRes = 0;
		$j=0;
		//Print the results of tests
		if(is_array($results))
		{
			for($i = 0; $i < sizeof($results); $i++)
			{
				$sum_marks = 0;

				$return[$i] = array();
				$id = $results[$i][5];
				$quiz_id = $results[$i][11];

				$qn_id = array();
				$sqlqn = "SELECT DISTINCT(question_id) FROM $tbl_e_attempt WHERE exe_id = ".$id;
				$resqn = Database::query($sqlqn, __FILE__, __LINE__);
				while($rowqn = Database::fetch_array($resqn)){
					$qn_id[] = $rowqn['question_id'];
				}
				
				$sql_weight = "SELECT sum(ponderation) FROM $tbl_quiz_questions qn WHERE qn.id IN ('".implode("','",$qn_id)."')";
				$res_weight = Database::query($sql_weight, __FILE__, __LINE__);
				$total_weight = Database::result($res_weight, 0, 0);				
								
				$sum_marks = $this->getRealScore($id, $quiz_id);	

				$mailid = $results[$i][6];
				$user = $results[$i][0];
				$test = $results[$i][1];
				$dt = strftime(get_lang('dateTimeFormatLong'),$results[$i][4]);
				//$res = $results[$i][2];
				$res = round($sum_marks);
				if(empty($user_id))
				{
					$user = $results[$i][0];
					$return[$i]['user'] = $user;
					$return[$i]['user_id'] = $results[$i][7];
				}
				$return[$i]['title'] = $test;
				$return[$i]['time'] = format_locale_date(get_lang('dateTimeFormatLong'),$results[$i][4]);
				$return[$i]['result'] = $res;
				//$return[$i]['max'] = $results[$i][3];
				$return[$i]['max'] = round($total_weight);
				$j=$i;
			}
		}
		$j++;
		// Print the Result of Hotpotatoes Tests
		if(is_array($hpresults))
		{
			for($i = 0; $i < sizeof($hpresults); $i++)
			{
				$return[$j+$i] = array();
				$title = GetQuizName($hpresults[$i][1],$document_path);
				if ($title =='')
				{
					$title = basename($hpresults[$i][1]);
				}
				if(empty($user_id))
				{
					$return[$j+$i]['user'] = $hpresults[$i][0];
					$return[$j+$i]['user_id'] = $results[$i][6];
					
				}
				$return[$j+$i]['title'] = $title;
				$return[$j+$i]['time'] = strftime(get_lang('dateTimeFormatLong'),$hpresults[$i][4]);
				$return[$j+$i]['result'] = $hpresults[$i][2];
				$return[$j+$i]['max'] = $hpresults[$i][3];
			}
		}
		$this->results = $return;
		return true;
	}
	/**
	 * Exports the complete report as a CSV file
	 * @param	string		Document path inside the document tool
	 * @param	integer		Optional user ID
	 * @param	boolean		Whether to include user fields or not
	 * @return	boolean		False on error
	 */
	public function exportCompleteReportCSV($document_path='',$user_id=null, $export_user_fields)
	{
		global $charset;
		$this->_getExercisesReporting($document_path,$user_id);
		$filename = 'exercise_results_'.date('YmdGis').'.csv';
		if(!empty($user_id))
		{
			$filename = 'exercise_results_user_'.$user_id.'_'.date('YmdGis').'.csv';
		}
		$data = '';
		//build the results
		//titles
		if(!empty($this->results[0]['user']))
		{
			$data .= get_lang('User').';';
		}
		if($export_user_fields)
		{
			//show user fields section with a big th colspan that spans over all fields
			$extra_user_fields = UserManager::get_extra_fields(0,0,5,'ASC',false);
			$num = count($extra_user_fields);
			foreach($extra_user_fields as $field)
			{
				$data .= '"'.str_replace("\r\n",'  ',api_html_entity_decode(strip_tags($field[3]), ENT_QUOTES, $charset)).'";';
			}
			$display_extra_user_fields = true;
		}
		$data .= get_lang('Title').';';
		$data .= get_lang('Date').';';
		$data .= get_lang('Results').';';
		$data .= get_lang('Weighting').';';
		$data .= "\n";
		//results
		foreach($this->results as $row)
		{
			if(!empty($row['user']))
			{
				$data .= str_replace("\r\n",'  ',api_html_entity_decode(strip_tags($row['user']), ENT_QUOTES, $charset)).';';
			}
			if($export_user_fields)
			{
				//show user fields data, if any, for this user
				$user_fields_values = UserManager::get_extra_user_data(intval($row['user_id']),false,false);
				foreach($user_fields_values as $value)
				{
					$data .= '"'.str_replace('"','""',api_html_entity_decode(strip_tags($value), ENT_QUOTES, $charset)).'";';
				}
			}
			$data .= str_replace("\r\n",'  ',api_html_entity_decode(strip_tags($row['title']), ENT_QUOTES, $charset)).';';
			$data .= str_replace("\r\n",'  ',$row['time']).';';
			$data .= str_replace("\r\n",'  ',$row['result']).';';
			$data .= str_replace("\r\n",'  ',$row['max']).';';
			$data .= "\n";
		}
		//output the results
		$len = strlen($data);
		header('Content-type: application/octet-stream');
		header('Content-Type: application/force-download');
		header('Content-length: '.$len);
		if (preg_match("/MSIE 5.5/", $_SERVER['HTTP_USER_AGENT']))
		{
			header('Content-Disposition: filename= '.$filename);
		}
		else
		{
			header('Content-Disposition: attachment; filename= '.$filename);
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
		{
			header('Pragma: ');
			header('Cache-Control: ');
			header('Cache-Control: public'); // IE cannot download from sessions without a cache
		}
		header('Content-Description: '.$filename);
		header('Content-transfer-encoding: binary');
		echo $data;
		return true;
	}
	/**
	 * Exports the complete report as an XLS file
	 * @return	boolean		False on error
	 */
	public function exportCompleteReportXLS($document_path='',$user_id=null, $export_user_fields)
	{
		global $charset;
		$this->_getExercisesReporting($document_path,$user_id);
		$filename = 'exercise_results_'.date('YmdGis').'.xls';
		if(!empty($user_id))
		{
			$filename = 'exercise_results_user_'.$user_id.'_'.date('YmdGis').'.xls';
		}		//build the results
		require_once(api_get_path(LIBRARY_PATH).'pear/Spreadsheet_Excel_Writer/Writer.php');
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->send($filename);
		$worksheet =& $workbook->addWorksheet('Report '.date('YmdGis'));
		$line = 0;
		$column = 0; //skip the first column (row titles)
		if(!empty($this->results[0]['user']))
		{
			$worksheet->write($line,$column,get_lang('User'));
			$column++;
		}
		if($export_user_fields)
		{
			//show user fields section with a big th colspan that spans over all fields
			$extra_user_fields = UserManager::get_extra_fields(0,0,5,'ASC',false);
			//show the fields names for user fields
			foreach($extra_user_fields as $field)
			{
				$worksheet->write($line,$column,api_html_entity_decode(strip_tags($field[3]), ENT_QUOTES, $charset));
				$column++;
			}
		}
		$worksheet->write($line,$column,get_lang('Title'));
		$column++;
		$worksheet->write($line,$column,get_lang('Date'));
		$column++;
		$worksheet->write($line,$column,get_lang('Results'));
		$column++;
		$worksheet->write($line,$column,get_lang('Weighting'));
		$column++;
		$worksheet->write($line,$column,'%');
		$line++;

		foreach($this->results as $row)
		{
			$column = 0;
			if(!empty($row['user']))
			{
				$worksheet->write($line,$column,api_html_entity_decode(strip_tags($row['user']), ENT_QUOTES, $charset));
				$column++;
			}
			if($export_user_fields)
			{
				//show user fields data, if any, for this user
				$user_fields_values = UserManager::get_extra_user_data(intval($row['user_id']),false,false);
				foreach($user_fields_values as $value)
				{
					$worksheet->write($line,$column,api_html_entity_decode(strip_tags($value), ENT_QUOTES, $charset));
					$column++;
				}
			}
			$worksheet->write($line,$column,api_html_entity_decode(strip_tags($row['title']), ENT_QUOTES, $charset));
			$column++;
			$worksheet->write($line,$column,$row['time']);
			$column++;
			$worksheet->write($line,$column,$row['result']);
			$column++;
			$worksheet->write($line,$column,$row['max']);
			$column++;
			$percent = $row['max'] > 0 ? number_format($row['result'] / $row['max'] * 100, 2).'%' : ''; 
			$worksheet->write($line,$column,$percent);
			$line++;
		}
		//output the results
		$workbook->close();
		return true;
	}

	/**
	 * Gets the results of all students (or just one student if access is limited)
	 * @param	string		The document path (for HotPotatoes retrieval)
	 * @param	integer		User ID. Optional. If no user ID is provided, we take all the results. Defauts to null
	 */
	function _getNewExercisesReporting($document_path,$user_id=null,$filter)
	{
		$return = array();
    	$TBL_EXERCISES          = Database::get_course_table(TABLE_QUIZ_TEST);
    	$TBL_EXERCISE_QUESTION  = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
    	$TBL_QUESTIONS 			= Database::get_course_table(TABLE_QUIZ_QUESTION);
		$TBL_USER          	    = Database::get_main_table(TABLE_MAIN_USER);
		$TBL_DOCUMENT          	= Database::get_course_table(TABLE_DOCUMENT);
		$TBL_ITEM_PROPERTY      = Database::get_course_table(TABLE_ITEM_PROPERTY);
		$TBL_TRACK_EXERCISES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$TBL_TRACK_HOTPOTATOES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTPOTATOES);
		$TBL_TRACK_ATTEMPT		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT_RECORDING);
		$tbl_course_rel_user	= Database::get_main_table(TABLE_MAIN_COURSE_USER);
		$tbl_e_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
		$tbl_quiz_questions = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$tbl_quiz_relqn = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);
		$user_id = '';

    	$cid = api_get_course_id();

	   switch ($filter) {
		case 1 :
		 $filter_by_not_revised = true;
		 break;
		case 2 :
		 $filter_by_revised = true;
		 break;
		default :
		 $filter_by_not_revised = true;
		 break;
	   }  
		
		$sql_filter = '';
		if ($filter_by_not_revised) {
			 $sql_filter .= " AND te.exe_id NOT IN(SELECT exe_id FROM $TBL_TRACK_ATTEMPT WHERE author != '')";
		  }
		  else if ($filter_by_revised) {
			 $sql_filter .= " AND te.exe_id IN(SELECT exe_id FROM $TBL_TRACK_ATTEMPT WHERE author != '')";
		  }
		
		if(empty($user_id))
		{			
			$session_id_and = '';
			if (api_get_session_id() != 0) {
				$session_id_and = ' AND session_id = ' . api_get_session_id() . ' ';
			}
			
			//get all results (ourself and the others) as an admin should see them
			//AND exe_user_id <> $_user['user_id']  clause has been removed
			$sql = "SELECT lastname, firstname, ce.title, te.exe_result ,
						  te.exe_weighting, UNIX_TIMESTAMP(te.exe_date) as end_date, te.exe_id, email, UNIX_TIMESTAMP(te.start_date) as start_date, steps_counter,exe_user_id as user_id,te.exe_duration,ce.id,ce.results_disabled,ce.score_pass,ce.expired_time,(te.exe_result/te.exe_weighting)*100 as total_percentage,te.exe_exo_id
						  FROM $TBL_EXERCISES AS ce 
						  INNER JOIN $TBL_TRACK_EXERCISES AS te
						  ON te.exe_exo_id = ce.id 
						  INNER JOIN $TBL_USER AS user
						  ON te.exe_user_id = user.user_id
						  WHERE te.status != 'incomplete'
						  AND te.status != 'left_incomplete'
						  AND te.exe_cours_id='" . Database :: escape_string($cid) . "'   						  
   						  $session_id_and 
						  AND ce.active <>-1 
						  AND orig_lp_id = 0 
						  AND orig_lp_item_id = 0
                          AND user.status <> 1                          
						  $sql_filter";

				if(isset($_SESSION['report_column'])) {
					$report_column = $_SESSION['report_column'];
					$report_direction = $_SESSION['report_direction'];
				}
				else {
					$report_column = 0;
					$report_direction = 'ASC';
				}

				switch ($report_column) {
					case 0 :
						$orderby = 'lastname';
						break;
					case 1 :
						$orderby = 'firstname';
						break;
					case 2 :
						$orderby = 'ce.title';
						break;
					case 3 :
						$orderby = 'te.exe_result';
						break;
					case 4 :
						$orderby = 'total_percentage';
						break;
					case 5 :
						$orderby = 'te.exe_duration';
						break;
					case 6 :
						$orderby = 'te.exe_result';
						break;
					default :
						$orderby = 'lastname';
						break;
				}
				$sql .= " ORDER BY ".$orderby. " " .$report_direction;			
		}
		else
		{ // get only this user's results
			  $sql="SELECT '',ce.title, te.exe_result , te.exe_weighting, " .
			  		"UNIX_TIMESTAMP(te.exe_date),te.exe_id, UNIX_TIMESTAMP(te.start_date), steps_counter, te.exe_duration, te.exe_exo_id
				  FROM $TBL_EXERCISES ce , $TBL_TRACK_EXERCISES te
				  WHERE te.exe_exo_id = ce.id AND te.exe_user_id='".Database::escape_string($user_id)."' AND te.exe_cours_id='".Database::escape_string($cid)."'
				  ORDER BY te.exe_cours_id ASC, ce.title ASC, te.exe_date ASC";
		}

		$results=getManyResultsXCol($sql,18);
		
		$NoTestRes = 0;
		$NoHPTestRes = 0;
		$j=0;
		//Print the results of tests
		if(is_array($results))
		{
			for($i = 0; $i < sizeof($results); $i++)
			{
				$sum_marks = 0;

				$return[$i] = array();
				$id = $results[$i][6];
				$quiz_id = $results[$i][17];
				
				$qn_id = array();
				$sqlqn = "SELECT DISTINCT(question_id) FROM $tbl_e_attempt WHERE exe_id = ".$id;
				$resqn = Database::query($sqlqn, __FILE__, __LINE__);
				while($rowqn = Database::fetch_array($resqn)){
					$qn_id[] = $rowqn['question_id'];
				}
				
				$sql_weight = "SELECT sum(ponderation) FROM $tbl_quiz_questions qn WHERE qn.id IN ('".implode("','",$qn_id)."')";
				$res_weight = Database::query($sql_weight, __FILE__, __LINE__);
				$total_weight = Database::result($res_weight, 0, 0);				
								
				$sum_marks = $this->getRealScore($id, $quiz_id);	

				$mailid = $results[$i][7];
				$lastname = $results[$i][0];
				$firstname = $results[$i][1];
				$dt = strftime(get_lang('dateTimeFormatLong'),$results[$i][5]);
				//$res = round($results[$i][3]);
				$res = round($sum_marks);
				$test = $results[$i][2];

				$expired_time = $results[$i][15];
				$add_end_real_date = $results[$i][5];
				$add_start_real_date = $results[$i][8];
				$score_pass = $results[$i][14];

				if (($add_end_real_date == 0) || ($add_start_real_date > $add_end_real_date)) {
					 $add_end_real_date = $add_start_real_date;
				 }
				 if ($add_start_real_date > 1) {
				  $final_time = round((($add_end_real_date - $add_start_real_date) / 60), 1);
				  /*if($expired_time <> 0 && ($final_time > $expired_time)){
					  $final_time = $expired_time;
				  }	*/
				  $duration = $final_time. ' '. get_lang('MinMinutes');
				  if ($results[$i][9] > 1) {
				   $duration =  ' ( ' . $results[$i][9] . ' ' . get_lang('Steps') . ' )';
				  }
				  $add_start_date = format_locale_date('%b %d, %Y %H:%M', $add_start_real_date) . ' / ';
				 } else {
				  $duration =  get_lang('NoLogOfDuration');
				 }

				if(empty($user_id))
				{
					$return[$i]['lastname'] = $lastname;
					$return[$i]['firstname'] = $firstname;
					$return[$i]['user_id'] = $results[$i][10];
				}
				$return[$i]['title'] = $test;				
				$return[$i]['result'] = $res;
				//$return[$i]['max'] = $results[$i][4];
				$return[$i]['max'] = round($total_weight);
				$return[$i]['time'] = $duration;	
				$return[$i]['score_pass'] = $score_pass;
				
				$j=$i;
			}
		}
		$j++;
		
		$this->results = $return;
		return true;
	}

	/**
	 * Exports the complete report as an XLS file with additional fields
	 * @return	boolean		False on error
	 */
	public function exportCompleteNewReportXLS($document_path='',$user_id=null, $export_user_fields, $filter)
	{
		global $charset;
		$this->_getNewExercisesReporting($document_path,$user_id,$filter);
		$filename = 'exercise_results_'.date('YmdGis').'.xls';
		if(!empty($user_id))
		{
			$filename = 'exercise_results_user_'.$user_id.'_'.date('YmdGis').'.xls';
		}		//build the results
		require_once(api_get_path(LIBRARY_PATH).'pear/Spreadsheet_Excel_Writer/Writer.php');
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->send($filename);

		if ($charset == 'UTF-8') {
			$workbook->setVersion(8, 'utf-8');
			$worksheet =& $workbook->addWorksheet('Report '.date('YmdGis'));
			$worksheet->setInputEncoding('utf-8');
		} 
		else {
			$worksheet =& $workbook->addWorksheet('Report '.date('YmdGis'));
		}

	//	$worksheet =& $workbook->addWorksheet('Report '.date('YmdGis'));
		$line = 0;
		$column = 0; //skip the first column (row titles)
		if(!empty($this->results[0]['user_id']))
		{
			$worksheet->write($line,$column,get_lang('lastname'));
			$column++;
			$worksheet->write($line,$column,get_lang('firstname'));
			$column++;
		}
		if($export_user_fields)
		{
			//show user fields section with a big th colspan that spans over all fields
			$extra_user_fields = UserManager::get_extra_fields(0,0,5,'ASC',false);
			//show the fields names for user fields
			foreach($extra_user_fields as $field)
			{
				$worksheet->write($line,$column,api_html_entity_decode(strip_tags($field[3]), ENT_QUOTES, $charset));
				$column++;
			}
		}
		$worksheet->write($line,$column,get_lang('Title'));
		$column++;		
		$worksheet->write($line,$column,get_lang('Results'));
		$column++;
		$worksheet->write($line,$column,get_lang('Weighting'));
		$column++;
		$worksheet->write($line,$column,'%');
		$column++;
		$worksheet->write($line,$column,get_lang('Time'));
		$column++;
		$worksheet->write($line,$column,get_lang('Success'));
		$line++;

		foreach($this->results as $row)
		{
			$column = 0;
			if(!empty($row['user_id']))
			{
				$worksheet->write($line,$column,api_html_entity_decode(strip_tags($row['lastname']), ENT_QUOTES, $charset));
				$column++;
				$worksheet->write($line,$column,api_html_entity_decode(strip_tags($row['firstname']), ENT_QUOTES, $charset));
				$column++;
			}
			if($export_user_fields)
			{
				//show user fields data, if any, for this user
				$user_fields_values = UserManager::get_extra_user_data(intval($row['user_id']),false,false);
				foreach($user_fields_values as $value)
				{
					$worksheet->write($line,$column,api_html_entity_decode(strip_tags($value), ENT_QUOTES, $charset));
					$column++;
				}
			}
			$worksheet->write($line,$column,api_html_entity_decode(strip_tags($row['title']), ENT_QUOTES, $charset));
			$column++;			
			$worksheet->write($line,$column,$row['result']);
			$column++;
			$worksheet->write($line,$column,$row['max']);
			$column++;
			$percentage = $row['max'] > 0 ? number_format($row['result'] / $row['max'] * 100, 2) : 0; 
			$percent = $row['max'] > 0 ? number_format($row['result'] / $row['max'] * 100, 2).'%' : ''; 
			$worksheet->write($line,$column,$percent);
			$column++;
			$worksheet->write($line,$column,$row['time']);
			$column++;
			if($percentage >= $row['score_pass']){
			$worksheet->write($line,$column,get_lang('Passed'));
			}
			else {
			$worksheet->write($line,$column,get_lang('Failed'));
			}
			$line++;
		}
		//output the results
		$workbook->close();
		return true;
	}

	function getRealScore($exe_id,$quiz_id){

		require_once 'exercise.class.php';
		require_once 'question.class.php'; 
		require_once 'answer.class.php';
		require_once api_get_path(LIBRARY_PATH) . 'geometry.lib.php';

		$TBL_EXERCICE_QUESTION 	= Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
		$TBL_EXERCICES         	= Database::get_course_table(TABLE_QUIZ_TEST);
		$TBL_QUESTIONS         	= Database::get_course_table(TABLE_QUIZ_QUESTION);
		$TBL_REPONSES          	= Database::get_course_table(TABLE_QUIZ_ANSWER);		
		$TBL_TRACK_EXERCICES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$TBL_TRACK_ATTEMPT		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

		$sql = "SELECT quiz_type FROM $TBL_EXERCICES WHERE id = ".$quiz_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		$quiz_type = Database::result($res, 0, 0);

		$sql_questions = "SELECT question_id FROM $TBL_EXERCICE_QUESTION WHERE exercice_id = ".Database::escape_string($quiz_id);
		$res_questions = Database::query($sql_questions,__FILE__,__LINE__);
		$total_questions = array();
		while($row_questions = Database::fetch_array($res_questions)) {
			$total_questions[] = $row_questions['question_id'];
		}

		$sql_attempts = "SELECT attempts.question_id, answer  from ".$TBL_TRACK_ATTEMPT." as attempts  
					INNER JOIN ".$TBL_TRACK_EXERCICES." as stats_exercices ON stats_exercices.exe_id=attempts.exe_id 
					INNER JOIN ".$TBL_QUESTIONS." as questions ON questions.id=attempts.question_id 
											INNER JOIN ".$TBL_EXERCICE_QUESTION." as rel_questions ON rel_questions.question_id = questions.id AND rel_questions.exercice_id = stats_exercices.exe_exo_id
											WHERE attempts.exe_id='".Database::escape_string($exe_id)."'
											GROUP BY attempts.question_id 
											ORDER BY rel_questions.question_order ASC";    
		$result = Database::query($sql_attempts, __FILE__, __LINE__);
		while ($row = Database::fetch_array($result)) {
			$questionList[] = $row['question_id'];
			$exerciseResult[] = $row['answer'];
		}		
		
		if($quiz_type == 2){
			$diff_question = array_diff($total_questions,$questionList);
			$diff_question_no = sizeof($diff_question);

			if($diff_question_no <> 0){

				foreach($diff_question as $missed_question){
					$questionList[] = $missed_question;
					$exerciseResult[] = '';
				}
			}
		}

		$final_score = 0;
		foreach($questionList as $questionId) {

			$choice=$exerciseResult[$questionId];
			// creates a temporary Question object
			$objQuestionTmp = Question::read($questionId);
			
			$questionWeighting=$objQuestionTmp->selectWeighting();
			$answerType=$objQuestionTmp->selectType();
			$quesId =$objQuestionTmp->selectId(); //added by priya saini

			if ($answerType == MULTIPLE_ANSWER) {
				$choice=array();
				$objAnswerTmp=new Answer($questionId);
				$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
				$questionScore=0;				
				$totalScoreMA = 0;

				for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
					$answer=$objAnswerTmp->selectAnswer($answerId);					
					$answerCorrect=$objAnswerTmp->isCorrect($answerId);
					$answerWeighting=$objAnswerTmp->selectWeighting($answerId);
					$queryans = "select * from ".$TBL_TRACK_ATTEMPT." where exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
					$resultans = api_sql_query($queryans, __FILE__, __LINE__);
					while ($row = Database::fetch_array($resultans)) {
						$ind = $row['answer'];
						$choice[$ind] = 1;
					}
					$studentChoice=$choice[$answerId];
					if ($studentChoice) {
						$count_ans++;
						$questionScore+=$answerWeighting;
						$totalScoreMA+=$answerWeighting;					
					}
                 
				}
				if($totalScoreMA>0){
					$final_score+=$totalScoreMA;
				}
			}
			elseif ($answerType == REASONING) {
				$choice=array();
				$objAnswerTmp=new Answer($questionId);
				$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
				$questionScore=0;
				$correctChoice = 'Y';
				$noStudentChoice='N';
				$answerWrong = 'N';
				for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {				
					$answer=$objAnswerTmp->selectAnswer($answerId);					
					$answerCorrect=$objAnswerTmp->isCorrect($answerId);
					$answerWeighting=$objAnswerTmp->selectWeighting($answerId);
					$queryans = "select * from ".$TBL_TRACK_ATTEMPT." where exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
					$resultans = api_sql_query($queryans, __FILE__, __LINE__);
					while ($row = Database::fetch_array($resultans)) {
						$ind = $row['answer'];
						$choice[$ind] = 1;
					}
					$studentChoice=$choice[$answerId];					
					
					if($answerId == '2')
					{
						$wrongAnswerWeighting = $answerWeighting;
					}
					if($answerCorrect && $studentChoice == '1' && $correctChoice == 'Y')
					{				
						$correctChoice = 'Y';
						$noStudentChoice = 'Y';
					}
					elseif($answerCorrect && !$studentChoice)
					{				
						$correctChoice = 'N';
						$noStudentChoice = 'Y';	
						$answerWrong = 'Y';	
					}
					elseif(!$answerCorrect && $studentChoice == '1')
					{				
						$correctChoice = 'N';
						$noStudentChoice = 'Y';		
						$answerWrong = 'Y';	
					}	
				}
				if ($answerType == REASONING  && $noStudentChoice == 'Y'){						
					if($correctChoice == 'Y')
					{						
					$questionScore += $questionWeighting;
					$final_score += $questionWeighting;
					}
					else
					{						
					$questionScore += $wrongAnswerWeighting;
					$final_score += $wrongAnswerWeighting;
					}
				}
			}				
			elseif ($answerType == UNIQUE_ANSWER) {
				$objAnswerTmp=new Answer($questionId);
				$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
				$questionScore=0;				
				for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {				
					$answer=$objAnswerTmp->selectAnswer($answerId);					
					$answerCorrect=$objAnswerTmp->isCorrect($answerId);					
					$answerWeighting=$objAnswerTmp->selectWeighting($answerId);
					$queryans = "select answer from ".$TBL_TRACK_ATTEMPT." where exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
					$resultans = api_sql_query($queryans, __FILE__, __LINE__);
					$choice = Database::result($resultans,0,"answer");
					$studentChoice=($choice == $answerId)?1:0;
					if ($studentChoice) {
						$questionScore+=$answerWeighting;
						$final_score+=$answerWeighting;						
					}					
				}
			}
			elseif ($answerType == FILL_IN_BLANKS) {
				$objAnswerTmp=new Answer($questionId);
				$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
				$questionScore=0;
				for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
					$answer = $objAnswerTmp->selectAnswer($answerId);
					$answerComment = $objAnswerTmp->selectComment($answerId);
					$answerCorrect = $objAnswerTmp->isCorrect($answerId);
					$answerWeighting = $objAnswerTmp->selectWeighting($answerId);
					
					// the question is encoded like this
					// [A] B [C] D [E] F::10,10,10@1
					// number 1 before the "@" means that is a switchable fill in blank question
					// [A] B [C] D [E] F::10,10,10@ or  [A] B [C] D [E] F::10,10,10
					// means that is a normal fill blank question

					$pre_array = explode('::', $answer);	
					
					// is switchable fill blank or not			
					$is_set_switchable = explode('@', $pre_array[1]);
					$switchable_answer_set=false;
					if ($is_set_switchable[1]==1) {
						$switchable_answer_set=true;
					}								
									
					$answer = $pre_array[0];
					
					// splits weightings that are joined with a comma
					$answerWeighting = explode(',',$is_set_switchable[0]);			
					//list($answer,$answerWeighting)=explode('::',$multiple[0]);				
					
					//$answerWeighting=explode(',',$answerWeighting);
					// we save the answer because it will be modified
					$temp=$answer;
			
					// TeX parsing
					// 1. find everything between the [tex] and [/tex] tags
					$startlocations=api_strpos($temp,'[tex]');
					$endlocations=api_strpos($temp,'[/tex]');
					if ($startlocations !== false && $endlocations !== false) {
						$texstring=api_substr($temp,$startlocations,$endlocations-$startlocations+6);
						// 2. replace this by {texcode}
						$temp=str_replace($texstring,'{texcode}',$temp);
					}
					$j=0;
					// the loop will stop at the end of the text
					$i=0;
					$feedback_anscorrect = array();
					$feedback_usertag = array();
					$feedback_correcttag = array();
					//normal fill in blank
					if (!$switchable_answer_set) {
						while (1) {
							// quits the loop if there are no more blanks
							if (($pos = api_strpos($temp,'[')) === false) {
								// adds the end of the text
								$answer.=$temp;				
								// TeX parsing
								$texstring = api_parse_tex($texstring);
								break;
							}
							$temp=api_substr($temp,$pos+1);
							// quits the loop if there are no more blanks
							if (($pos = api_strpos($temp,']')) === false) {
								break;
							}
							
							$queryfill = "select answer from ".$TBL_TRACK_ATTEMPT." where exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
							$resfill = api_sql_query($queryfill, __FILE__, __LINE__);
							$str = Database::result($resfill,0,"answer");
							$str = str_replace("<br />","",$str);
							$str = str_replace("<s>","",$str);
							$str = str_replace("</s>","",$str);

							preg_match_all('#\[([^[]*)\]#', $str, $arr);
							$choice = $arr[1];
							$tmp=strrpos($choice[$j],' / ');
							$choice[$j]=substr($choice[$j],0,$tmp);
							$choice[$j]=trim($choice[$j]);
							$choice[$j]=stripslashes($choice[$j]);
							$feedback_usertag[] = $choice[$j];
							$feedback_correcttag[] = api_strtolower(api_substr($temp,0,$pos));

							$str = str_replace("["," <u>",$str);
							$str = str_replace("]","</u> ",$str);
							// if the word entered by the student IS the same as the one defined by the professor
							if (trim(api_strtolower(api_substr($temp,0,$pos))) == trim(api_strtolower($choice[$j]))) {
								$feedback_anscorrect[] = "Y";
								// gives the related weighting to the student
								$questionScore+=$answerWeighting[$j];
								// increments total score
								$final_score+=$answerWeighting[$j];
							}
							else
							{
								$feedback_anscorrect[] = "N";
							}
							// else if the word entered by the student IS NOT the same as the one defined by the professor
							$j++;
							$temp=api_substr($temp,$pos+1);
							$i=$i+1;
						}
						$answer = stripslashes($str);
					} else {
						//multiple fill in blank	
						while (1) {
							// quits the loop if there are no more blanks
							if (($pos = api_strpos($temp,'[')) === false) {
								// adds the end of the text
								$answer.=$temp;
								// TeX parsing
								$texstring = api_parse_tex($texstring);
								//$answer=str_replace("{texcode}",$texstring,$answer);
								break;
							}
							// adds the piece of text that is before the blank and ended by [
							$real_text[]=api_substr($temp,0,$pos+1);
							$answer.=api_substr($temp,0,$pos+1);					
							$temp=api_substr($temp,$pos+1);

							// quits the loop if there are no more blanks
							if (($pos = api_strpos($temp,']')) === false) {
								// adds the end of the text
								//$answer.=$temp;
								break;
							}

							$queryfill = "SELECT answer FROM ".$TBL_TRACK_ATTEMPT." WHERE exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
							$resfill = api_sql_query($queryfill, __FILE__, __LINE__);
							$str=Database::result($resfill,0,"answer");
							$str = str_replace("<br />","",$str);

							preg_match_all ('#\[([^[/]*)/#', $str, $arr);
							$choice = $arr[1];
							
							$choice[$j]=trim($choice[$j]);					
							$user_tags[]=api_strtolower($choice[$j]);
							$correct_tags[]=api_strtolower(api_substr($temp,0,$pos));	
							
							$j++;
							$temp=api_substr($temp,$pos+1);
							$i=$i+1;
						}
						$answer='';
						for ($i=0;$i<count($correct_tags);$i++) {		 							
							if (in_array($user_tags[$i],$correct_tags)) {
								// gives the related weighting to the student
								$questionScore+=$answerWeighting[$i];
								// increments total score
								$final_score+=$answerWeighting[$i];
							}					
						}
						$answer = stripslashes($str);
						$answer = str_replace('rn', '', $answer);
					}	
					
					$fy = 0;
					$fn = 0;
					
					for ($k = 0; $k < sizeof($feedback_anscorrect); $k++) {
						if ($feedback_anscorrect[$k] == "Y") {
							$fy++;
						} else {
							$fn++;
						}
					}					
					$i++;
				}
			}
			elseif ($answerType == FREE_ANSWER) {
				$answer = $str;	
				$objAnswerTmp = new Answer($questionId);
				$nbrAnswers = $objAnswerTmp->selectNbrAnswers();
				$questionScore = 0;
				$query = "select answer, marks from ".$TBL_TRACK_ATTEMPT." where exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
				$resq = api_sql_query($query);
				$choice = Database::result($resq,0,"answer");
				$choice = stripslashes($choice);
				$choice = str_replace('rn', '', $choice);
				
				$questionScore = Database::result($resq,0,"marks");
				if ($questionScore==-1) {
					$final_score+=0;
				} else {
					$final_score+=$questionScore;
				}
			}
			else if ($answerType == MATCHING) {
				$objAnswerTmp=new Answer($questionId);					
				$questionScore = 0;
				$table_ans = Database :: get_course_table(TABLE_QUIZ_ANSWER);
				$TBL_TRACK_ATTEMPT= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
				$answer_ok = 'N';
				$answer_wrong = 'N';
				$sql_select_answer = 'SELECT id, answer, correct, position FROM '.$table_ans.' WHERE question_id="'.Database::escape_string($questionId).'" AND correct<>0 ORDER BY position';		
				$sql_answer = 'SELECT position, answer FROM '.$table_ans.' WHERE question_id="'.Database::escape_string($questionId).'" AND correct=0 ORDER BY position';
				$res_answer = api_sql_query($sql_answer, __FILE__, __LINE__);
				// getting the real answer
				$real_list =array();		
				while ($real_answer = Database::fetch_array($res_answer)) {			
					$real_list[$real_answer['position']]= $real_answer['answer'];
				}	
				
				$res_answers = api_sql_query($sql_select_answer, __FILE__, __LINE__);						

				while ($a_answers = Database::fetch_array($res_answers)) {			
					$i_answer_id = $a_answers['id']; //3
					$s_answer_label = $a_answers['answer'];  // your dady - you mother			
					$i_answer_correct_answer = $a_answers['correct']; //1 - 2			
					$i_answer_position = $a_answers['position']; // 3 - 4
					
					$sql_user_answer = 
							'SELECT answers.answer 
							FROM '.$TBL_TRACK_ATTEMPT.' as track_e_attempt 
							INNER JOIN '.$table_ans.' as answers 
								ON answers.position = track_e_attempt.answer
								AND track_e_attempt.question_id=answers.question_id
							WHERE answers.correct = 0
							AND track_e_attempt.exe_id = "'.Database::escape_string($exe_id).'"
							AND track_e_attempt.question_id = "'.Database::escape_string($questionId).'" 
							AND track_e_attempt.position="'.Database::escape_string($i_answer_position).'"';
					
					
					$res_user_answer = api_sql_query($sql_user_answer, __FILE__, __LINE__);
					if (Database::num_rows($res_user_answer)>0 ) {
						$s_user_answer = Database::result($res_user_answer,0,0); //  rich - good looking
					} else { 
						$s_user_answer = '';
					}
					
					//$s_correct_answer = $s_answer_label; // your ddady - your mother
					$s_correct_answer = $real_list[$i_answer_correct_answer];				
					$i_answerWeighting=$objAnswerTmp->selectWeighting($i_answer_id);				
					if ($s_user_answer == $real_list[$i_answer_correct_answer]) { // rich == your ddady?? wrong
						$questionScore+=$i_answerWeighting;
						$final_score+=$i_answerWeighting;						
					}										
				}
			}
			elseif ($answerType == HOT_SPOT) {
				$objAnswerTmp = new Answer($questionId);
				$nbrAnswers = $objAnswerTmp->selectNbrAnswers();
				$questionScore = 0;				
				$answerOk = 'N';
				$answerWrong = 'N';
				$totalScoreHot = 0;
				$hotspot_colors = array("", // $i starts from 1 on next loop (ugly fix)
											"#4271B5",
											"#FE8E16",
											"#3B3B3B",
											"#BCD631",
											"#D63173",
											"#D7D7D7",
											"#90AFDD",
											"#AF8640",
											"#4F9242",
											"#F4EB24",
											"#ED2024",
											"#45C7F0",
											"#F7BDE2");

				
				 for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
					$answer = $objAnswerTmp->selectAnswer($answerId);					
					$answerCorrect = $objAnswerTmp->isCorrect($answerId);
					
					$TBL_TRACK_HOTSPOT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
					$query = "select hotspot_correct from " . $TBL_TRACK_HOTSPOT . " where hotspot_exe_id = '" . Database::escape_string($exe_id) . "' and hotspot_question_id= '" . Database::escape_string($questionId) . "' AND hotspot_answer_id='" . Database::escape_string($answerId) . "'";
					$resq = api_sql_query($query);
					$choice = Database::result($resq, 0, "hotspot_correct");
					
					$queryfree = "select marks from ".$TBL_TRACK_ATTEMPT." where exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
					$resfree = api_sql_query($queryfree, __FILE__, __LINE__);
					$questionScore= Database::result($resfree,0,"marks");
					
					
				 }
				 
				 $final_score+=$questionScore;
			}
			else if($answerType == HOT_SPOT_DELINEATION) {
				$objAnswerTmp=new Answer($questionId);
				$nbrAnswers=$objAnswerTmp->selectNbrAnswers();				
				$questionScore=0;		
				$totalScoreHotDel= 0;
				//based on exercise_submit modal
				/*  Hot spot delinetion parameters */		
				$choice=$exerciseResult[$questionid];
				$destination=array();
				$comment='';
				$next=1;
				$_SESSION['hotspot_coord']=array();
				$_SESSION['hotspot_dest']=array();
				$overlap_color=$missing_color=$excess_color=false;
				$organs_at_risk_hit=0;

				$final_answer = 0;
						for($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
							
							$answer			=$objAnswerTmp->selectAnswer($answerId);
							$answerComment	=$objAnswerTmp->selectComment($answerId);
							$answerCorrect	=$objAnswerTmp->isCorrect($answerId);
							$answerWeighting=$objAnswerTmp->selectWeighting($answerId);
							
							//delineation						
							$answer_delineation_destination=$objAnswerTmp->selectDestination(1);
							$delineation_cord=$objAnswerTmp->selectHotspotCoordinates(1);					
							
							if ($answerId===1) {					
								$_SESSION['hotspot_coord'][1]=$delineation_cord;
								$_SESSION['hotspot_dest'][1]=$answer_delineation_destination;
							}	
												
							// getting the user answer 
							$TBL_TRACK_HOTSPOT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
							$query = "select hotspot_correct, hotspot_coordinate from ".$TBL_TRACK_HOTSPOT." where hotspot_exe_id = '".Database::escape_string($exe_id)."' and hotspot_question_id= '".Database::escape_string($questionId)."' AND hotspot_answer_id='1'"; //by default we take 1 because it's a delineation 
							$resq=api_sql_query($query);
							$row = Database::fetch_array($resq,'ASSOC');
							$choice = $row['hotspot_correct'];
							$user_answer = $row['hotspot_coordinate'];	
							
							$queryfree = "select marks from ".$TBL_TRACK_ATTEMPT." where exe_id = '".Database::escape_string($exe_id)."' and question_id= '".Database::escape_string($questionId)."'";
							$resfree = api_sql_query($queryfree, __FILE__, __LINE__);
							$questionScore= Database::result($resfree,0,"marks");
							$totalScoreHotDel=$questionScore;	
									
							// THIS is very important otherwise the poly_compile will throw an error!!
							// round-up the coordinates
							$coords = explode('/',$user_answer);
							$user_array = '';
							foreach ($coords as $coord) {
								list($x,$y) = explode(';',$coord);
								$user_array .= round($x).';'.round($y).'/';
							}
							$user_array = substr($user_array,0,-1);									
									
							if ($next) {							                    
								//$tbl_track_e_hotspot = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
										
							// Save into db
												
								$user_answer = $user_array;
							
								// we compare only the delineation not the other points
								$answer_question	= $_SESSION['hotspot_coord'][1];	
								$answerDestination	= $_SESSION['hotspot_dest'][1];
								
								//calculating the area
								$poly_user 			= convert_coordinates($user_answer,'/'); 
								$poly_answer		= convert_coordinates($answer_question,'|');
								$max_coord 			= array('x'=>600,'y'=>400);//poly_get_max($poly_user,$poly_answer);	                   
								$poly_user_compiled = poly_compile($poly_user,$max_coord);	                             
								$poly_answer_compiled = poly_compile($poly_answer,$max_coord);
								$poly_results 		= poly_result($poly_answer_compiled,$poly_user_compiled,$max_coord);
									  
								$overlap = $poly_results['both'];
								$poly_answer_area = $poly_results['s1'];
								$poly_user_area = $poly_results['s2'];
								$missing = $poly_results['s1Only'];
								$excess = $poly_results['s2Only'];
								
								//$overlap = round(polygons_overlap($poly_answer,$poly_user)); //this is an area in pixels
								if ($dbg_local>0) { error_log(__LINE__.' - Polygons results are '.print_r($poly_results,1),0);}
								if ($overlap < 1) {
									//shortcut to avoid complicated calculations
									$final_overlap = 0;
									$final_missing = 100;
									$final_excess = 100;
								} else {
									// the final overlap is the percentage of the initial polygon that is overlapped by the user's polygon
									$final_overlap = round(((float)$overlap / (float)$poly_answer_area)*100);
									if ($dbg_local>1) { error_log(__LINE__.' - Final overlap is '.$final_overlap,0);}
									// the final missing area is the percentage of the initial polygon that is not overlapped by the user's polygon
									$final_missing = 100 - $final_overlap;
									if ($dbg_local>1) { error_log(__LINE__.' - Final missing is '.$final_missing,0);}
									// the final excess area is the percentage of the initial polygon's size that is covered by the user's polygon outside of the initial polygon
									$final_excess = round((((float)$poly_user_area-(float)$overlap)/(float)$poly_answer_area)*100);
									if ($dbg_local>1) { error_log(__LINE__.' - Final excess is '.$final_excess,0);}
								}
								
								//checking the destination parameters parsing the "@@"				
								$destination_items= explode('@@', $answerDestination);	                        
								$threadhold_total = $destination_items[0];			            
								$threadhold_items=explode(';',$threadhold_total);				        		            
								$threadhold1 = $threadhold_items[0]; // overlap
								$threadhold2 = $threadhold_items[1]; // excess
								$threadhold3 = $threadhold_items[2];	 //missing          
								
								// if is delineation
								if ($answerId===1) {
									//setting colors
									if ($final_overlap>=$threadhold1) {	
										$overlap_color=true; //echo 'a';
									}
									//echo $excess.'-'.$threadhold2;
									if ($final_excess<=$threadhold2) {	
										$excess_color=true; //echo 'b';
									}
									//echo '--------'.$missing.'-'.$threadhold3;
									if ($final_missing<=$threadhold3) {	
										$missing_color=true; //echo 'c';
									}					
									
									// if pass
									if ($final_overlap>=$threadhold1 && $final_missing<=$threadhold3 && $final_excess<=$threadhold2) {																
										$next=1; //go to the oars	
										$result_comment=get_lang('Acceptable');	
										$final_answer = 1;	// do not update with  update_exercise_attempt
										//$comment='<span style="font-weight: bold; color: #008000;">'.$answerDestination=$objAnswerTmp->selectComment(1).'</span';
									} else {									
										$next=1; //Go to the oars. If $next =  0 we will show this message: "One (or more) area at risk has been hit" instead of the table resume with the results	
										$result_comment=get_lang('Unacceptable');									
										//$comment='<span style="font-weight: bold; color: #FF0000;">'.$answerDestination=$objAnswerTmp->selectComment(2).'</span>';
										$answerDestination=$objAnswerTmp->selectDestination(1);
										//checking the destination parameters parsing the "@@"	
										$destination_items= explode('@@', $answerDestination);
										/*
										$try_hotspot=$destination_items[1];
										$lp_hotspot=$destination_items[2];
										$select_question_hotspot=$destination_items[3];
										$url_hotspot=$destination_items[4]; */	 		            											
										 //echo 'show the feedback';
									}
								} elseif($answerId>1) {
									if ($objAnswerTmp->selectHotspotType($answerId) == 'noerror') {
										if ($dbg_local>0) { error_log(__LINE__.' - answerId is of type noerror',0);}
										//type no error shouldn't be treated
										$next = 1;
										continue;
									}
									if ($dbg_local>0) { error_log(__LINE__.' - answerId is >1 so we\'re probably in OAR',0);}
									//check the intersection between the oar and the user												
									//echo 'user';	print_r($x_user_list);		print_r($y_user_list);
									//echo 'official';print_r($x_list);print_r($y_list);												
									//$result = get_intersection_data($x_list,$y_list,$x_user_list,$y_user_list);
									$inter= $result['success'];

									//$delineation_cord=$objAnswerTmp->selectHotspotCoordinates($answerId);
									$delineation_cord=$objAnswerTmp->selectHotspotCoordinates($answerId);

									$poly_answer 			= convert_coordinates($delineation_cord,'|');
									$max_coord 				= poly_get_max($poly_user,$poly_answer);                            
									$poly_answer_compiled 	= poly_compile($poly_answer,$max_coord); 
									$overlap 				= poly_touch($poly_user_compiled, $poly_answer_compiled,$max_coord);
																
									if ($overlap == false) {
										//all good, no overlap
										$next = 1;
										continue;
									} else {								
										if ($dbg_local>0) { error_log(__LINE__.' - Overlap is '.$overlap.': OAR hit',0);}
										$organs_at_risk_hit++;  
										//show the feedback
										$next=0;								
										$comment=$answerDestination=$objAnswerTmp->selectComment($answerId);                                
										$answerDestination=$objAnswerTmp->selectDestination($answerId);
															
										$destination_items= explode('@@', $answerDestination);
										 /*
										$try_hotspot=$destination_items[1];
										$lp_hotspot=$destination_items[2];
										$select_question_hotspot=$destination_items[3];
										$url_hotspot=$destination_items[4];*/                                                                                 
									}
								}
							}
									
						} // end for				
								
				if ($overlap_color) {
					$overlap_color='green';
				} else {
					$overlap_color='red';
				}
				
				if ($missing_color) {
					$missing_color='green';
				} else {
					$missing_color='red';
				}
				if ($excess_color) {
					$excess_color='green';
				} else {
					$excess_color='red';
				}
				
				
				if (!is_numeric($final_overlap)) {
				$final_overlap = 0;
				}
				
				if (!is_numeric($final_missing)) {
					$final_missing = 0;
				}
				if (!is_numeric($final_excess)) {
					$final_excess = 0;
				}
				
				if ($final_excess>100) {
					$final_excess = 100;
				}$final_score+=$totalScoreHotDel;
			}
			//echo '</br>';
		}
		return $final_score;
	}
}

endif;
?>
