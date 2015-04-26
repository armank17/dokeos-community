<?php
/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	Exercise Results (uses tracking tool)
 * 	@package dokeos.exercise
  ==============================================================================
 */
// Language files that should be included
$language_file = array('exercice', 'admin');

// including the global library
require_once '../inc/global.inc.php';

require_once api_get_path(LIBRARY_PATH) . 'statsUtils.lib.inc.php';
require_once (api_get_path(LIBRARY_PATH).'ezpdf/class.ezpdf.php');
require_once ('exercise_result.class.php');

// setting the tabs
$this_section = SECTION_COURSES;

// access control
api_protect_course_script(true);

$is_allowedToEdit = api_is_allowed_to_edit();
$is_tutor = api_is_allowed_to_edit(true);
$is_tutor_course = api_is_course_tutor();

$TBL_EXERCICES = Database :: get_course_table(TABLE_QUIZ_TEST);
$TBL_TRACK_EXERCICES = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_USER = Database :: get_main_table(TABLE_MAIN_USER);
$TBL_TRACK_HOTPOTATOES = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTPOTATOES);
$tbl_course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT_RECORDING);
$tbl_e_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
$tbl_quiz_questions = Database :: get_course_table(TABLE_QUIZ_QUESTION);
$tbl_quiz_relqn = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);

if(isset($_GET['exerciceId'])){
     $exerciceId= $_GET['exerciceId'];
}



function export_pdf_report($pdf,$newarray,$header_names,$format) {
	$course_code = api_get_course_id();
	$course_info = api_get_course_info($course_code);
	$pdf->selectFont(api_get_path(LIBRARY_PATH).'ezpdf/fonts/Courier.afm');
	$pdf->ezSetCmMargins(0,0,0,0);
	$pdf->ezSetY(($format=='portrait')?'820':'570');
	$pdf->selectFont(api_get_path(LIBRARY_PATH).'ezpdf/fonts/Courier.afm');
	$pdf->ezText($course_info['name'],12,array('left'=>'40'));
	$pdf->ezText(get_lang('Printed').' : ('. date('j/n/Y') .')',12,array('left'=>'40'));
	if ($format=='portrait') {
		$pdf->line(40,790,540,790);
	} else {
		$pdf->line(40,540,790,540);
	}
	$pdf->ezSetY(($format=='portrait')?'750':'520');
	$pdf->ezTable($newarray,$header_names,'',array('showHeadings'=>1,'shaded'=>1,'showLines'=>1,'rowGap'=>3,'width'=>(($format=='portrait')?'500':'750')));
	$pdf->ezStream();

}

function get_printable_data($exerciceId) {

		$data_array = get_printablereport_data($exerciceId);
		$newarray = array();
		foreach ($data_array as $data) {
			$newarray[] = array_slice($data, 1);
		}

		$header_names = array();

			$header_names[] = get_lang('LastName');
			$header_names[] = get_lang('FirstName');
			$header_names[] = get_lang('Quiz');
			$header_names[] = get_lang('Score');
			$header_names[] = get_lang('Percentage');
			$header_names[] = get_lang('Time');
			$header_names[] = get_lang('Success');
			return array ($header_names, $newarray);
	}
       
        
function get_printablereport_data($exerciceId){
	global $_cid;       

	$TBL_EXERCICES = Database :: get_course_table(TABLE_QUIZ_TEST);
	$TBL_TRACK_EXERCICES = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
	$TBL_USER = Database :: get_main_table(TABLE_MAIN_USER);
	$TBL_TRACK_HOTPOTATOES = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTPOTATOES);
	$tbl_course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$tbl_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT_RECORDING);
	$tbl_e_attempt = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
    $tbl_quiz_questions = Database :: get_course_table(TABLE_QUIZ_QUESTION);

	$is_allowedToEdit = api_is_allowed_to_edit();
	$is_tutor = api_is_allowed_to_edit(true);

	if (!isset($_GET['filter'])) {
    $filter_by_not_revised = true;
    $filter = 1;
   } else {
    $filter = Security::remove_XSS($_GET['filter']);
    $filter = (int) $_GET['filter'];
   }


   switch ($filter) {
    case 1 :
     $filter_by_not_revised = true;
     break;
    case 2 :
     $filter_by_revised = true;
     break;
    default :
     null;
   }

	$sql_filter = '';
	if ($filter_by_not_revised) {
		 $sql_filter .= " AND te.exe_id NOT IN(SELECT exe_id FROM $tbl_attempt WHERE author != '')";
	  }
	  else if ($filter_by_revised) {
		 $sql_filter .= " AND te.exe_id IN(SELECT exe_id FROM $tbl_attempt WHERE author != '')";
	  }

	if ($is_allowedToEdit || $is_tutor) {
	   $user_id_and = '';
	   if (!empty($_POST['filter_by_user'])) {
		  if ($_POST['filter_by_user'] == 'all') {
		   $user_id_and = " AND user_id like '%'";
		  } else {
		   $user_id_and = " AND user_id = '" . Database :: escape_string((int) $_POST['filter_by_user']) . "' ";
		  }
	   }
	}
	$sql = "SELECT lastname, firstname, ce.title, te.exe_result ,
						  te.exe_weighting, UNIX_TIMESTAMP(te.exe_date) as end_date, te.exe_id, email, UNIX_TIMESTAMP(te.start_date) as start_date, steps_counter,exe_user_id as user_id,te.exe_duration,ce.id,ce.results_disabled,ce.score_pass,ce.expired_time,(te.exe_result/te.exe_weighting)*100 as total_percentage,te.exe_exo_id
						  FROM $TBL_EXERCICES AS ce
						  INNER JOIN $TBL_TRACK_EXERCICES AS te
						  ON te.exe_exo_id = ce.id
						  INNER JOIN $TBL_USER AS user
						  ON te.exe_user_id = user.user_id
						  WHERE te.status != 'incomplete'
						  AND te.status != 'left_incomplete'                                                  
						  AND te.exe_cours_id='" . Database :: escape_string($_cid) . "'
   						  $user_id_and
   						  $session_id_and
                                                  AND exe_exo_id= $exerciceId
						  AND ce.active <>-1
						  AND orig_lp_id = 0
						  AND orig_lp_item_id = 0
                          AND user.status <> 1
                          $sql_filter
						  ";
		if(isset($_SESSION['report_column']) && !empty($_SESSION['report_column'])) {
			switch ($_SESSION['report_column']) {
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
			$sql .= " ORDER BY ".$orderby. " " .$_SESSION['report_direction'];
		}

		$res = Database::query($sql,__FILE__,__LINE__);
		$data = array();
		while($row = Database::fetch_array($res)){

			$sum_marks = 0;
		    $exe_id = $row['exe_id'];
			$quiz_id = $row['exe_exo_id'];

			$qn_id = array();
			$sqlqn = "SELECT DISTINCT(question_id) FROM $tbl_e_attempt WHERE exe_id = ".$exe_id;
			$resqn = Database::query($sqlqn, __FILE__, __LINE__);
			while($rowqn = Database::fetch_array($resqn)){
				$qn_id[] = $rowqn['question_id'];
			}
			
			$sql_weight = "SELECT sum(ponderation) FROM $tbl_quiz_questions qn WHERE qn.id IN ('".implode("','",$qn_id)."')";
			$res_weight = Database::query($sql_weight, __FILE__, __LINE__);
			$total_weight = Database::result($res_weight, 0, 0);		
			
			$result = new ExerciseResult();
			$sum_marks = $result->getRealScore($exe_id, $quiz_id);

			$lastname = $row['lastname'];
			$firstname = $row['firstname'];
			$title = $row['title'];
			//$my_res = $row['exe_result'];
			$my_res = round($sum_marks);
			//$my_total = $row['exe_weighting'];
			$my_total = round($total_weight);
			$score = round($my_res).'/'.round($my_total);
			$percentage = round(($my_res / ($my_total != 0 ? $my_total : 1)) * 100, 2);

			$expired_time = $row['expired_time'];
			$add_end_real_date = $row['end_date'];
			$add_start_real_date = $row['start_date'];
			 if (($add_end_real_date == 0) || ($add_start_real_date > $add_end_real_date)) {
				 $add_end_real_date = $add_start_real_date;
			 }
			 if ($add_start_real_date > 1) {
			  $final_time = round((($add_end_real_date - $add_start_real_date) / 60), 1);
			  /*if($expired_time <> 0 && ($final_time > $expired_time)){
				  $final_time = $expired_time;
			  }	*/
			  $duration = $final_time. ' '. get_lang('MinMinutes');
			  if ($row['steps_counter'] > 1) {
			   $duration =  ' ( ' . $results[$i][8] . ' ' . get_lang('Steps') . ' )';
			  }
			  $add_start_date = format_locale_date('%b %d, %Y %H:%M', $add_start_real_date) . ' / ';
			 } else {
			  $duration =  get_lang('NoLogOfDuration');
			 }
			 $score_pass = $row['score_pass'];
			 if($percentage >= $score_pass){
				 $success =  get_lang('Passed');
			 }
			 else {
				 $success =  get_lang('Failed');
			 }
			$data[] = array($lastname,$lastname,$firstname,$title,$score,$percentage,$duration,$success);
		}

	return $data;
}

if (isset ($_GET['exportpdf']))	{

    $attributes = array('class' => 'outer_form');
    $export_pdf_form= new DataForm(DataForm :: TYPE_EXPORT_PDF, 'export_pdf_form', null, api_get_self().'?exportpdf=1&filter='.  intval($_GET['filter']).'&exerciceId='.$exerciceId,'_blank', $attributes);

	if ($export_pdf_form->validate()) {
		$printable_data = get_printable_data (intval($_GET['exerciceId']));
		$export= $export_pdf_form->exportValues();
		$format = $export['orientation'];
		$pdf = new Cezpdf('a4',$format); //format is 'portrait' or 'landscape'
		$clear_printable_data=array();
		$clear_send_printable_data=array();

		for ($i=0;$i<count($printable_data[1]);$i++) {
			for ($k=0;$k<count($printable_data[1][$i]);$k++) {
				$text = api_convert_encoding($printable_data[1][$i][$k], "ISO-8859-1", "UTF-8");
			//	$clear_printable_data[]=strip_tags($printable_data[1][$i][$k]);
				$clear_printable_data[] = $text;
			}
			$clear_send_printable_data[]=$clear_printable_data;
			$clear_printable_data=array();
		}

		export_pdf_report($pdf,$clear_send_printable_data,$printable_data[0],$format);
		exit;
	}
}


Display::display_header();


if (isset ($_GET['exportpdf']))	{
	echo '<div>';
		$export_pdf_form->display();
		echo '</div>';
}

echo '<div class="actions">';
if (api_is_allowed_to_edit ()) {
   if (!isset($_GET['filter'])) {
    $filter_by_not_revised = true;
    $filter = 1;
   } else {
    $filter = Security::remove_XSS($_GET['filter']);
    $filter = (int) $_GET['filter'];
   }
   

   switch ($filter) {
    case 1 :
     $filter_by_not_revised = true;
     break;
    case 2 :
     $filter_by_revised = true;
     break;
    default :
     null;
   }  
   
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'exercice/exercice.php?'.api_get_cidreq().'&show=test">' . Display::return_icon('pixel.gif', get_lang('BackToExercisesList'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('BackToExercisesList') . '</a>';
   if ($_GET['filter'] == '1' or !isset($_GET['filter']) or $_GET['filter'] == 0) {
    $view_result = '<a href="'.api_get_self().'?'.api_get_cidreq().'&filter=2&gradebook=' . $gradebook . '&exerciceId='.$exerciceId.' ">' . Display::return_icon('pixel.gif', get_lang('ShowCorrectedOnly'), array('class' => 'toolactionplaceholdericon toolactionlist')) . get_lang('ShowCorrectedOnly') . '</a>';
   } else {
    $view_result = '<a href="'.api_get_self().'?'.api_get_cidreq().'&filter=1&gradebook=' . $gradebook . '&exerciceId='.$exerciceId.' ">'. Display::return_icon('pixel.gif', get_lang('ShowUnCorrectedOnly'), array('class' => 'toolactionplaceholdericon toolactionlist')) . get_lang('ShowUnCorrectedOnly') . '</a>';
   }
   echo $view_result;
  } else {
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'exercice/exercice.php?'.api_get_cidreq().'&show=test">' . Display::return_icon('pixel.gif', get_lang('BackToExercisesList'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('BackToExercisesList'). '</a>';
  }
  
  // the form
  if (api_is_platform_admin() || api_is_course_admin() || api_is_course_tutor() || api_is_course_coach()) {
   if ($_SESSION['export_user_fields'] == true) {
    $alt = get_lang('ExportWithUserFields');
    $extra_user_fields = '<input type="hidden" name="export_user_fields" value="export_user_fields">';
   } else {
    $alt = get_lang('ExportWithoutUserFields');
    $extra_user_fields = '<input type="hidden" name="export_user_fields" value="do_not_export_user_fields">';
   }

   if($GLOBALS['learner_view'] == false){
   echo '<a href="#" onclick="document.form1b.submit();">' . Display::return_icon('pixel.gif', get_lang('ExportAsXLS'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('ExportAsXLS') . '</a>';
   echo '<a href="' . api_get_self() . '?exportpdf=1&filter='.$filter.'&exerciceId='.$exerciceId.'" >'.Display::return_icon('pixel.gif', get_lang('ExportToPDF'),array('class'=>'toolactionplaceholdericon toolaction32x32file_pdf')).' ' . get_lang('ExportToPDF') . '</a>';
   }
   
   // Add a link to reporting page
   if (isset($_GET['reporting'])) {
     $reporting_href = "#";
     $reporting_page = isset($_GET['page']);
     if (isset($reporting_page) && $_GET['page'] == 'profiling' ) {
       $reporting_href = 'profiling.php';
     } elseif (isset($reporting_page) && $_GET['page'] == 'notification') {
       $reporting_href = 'notification.php';
     } elseif (isset($reporting_page) && $_GET['page'] == 'courselog') {
       $reporting_href = 'courseLog.php';
     } elseif (isset($reporting_page) && $_GET['page'] == 'learners') {
       $reporting_href = 'learners.php';
     }
     $reporting_href.= '?'.api_get_cidreq();
     echo '<a href="../tracking/'.$reporting_href.'">' .Display::return_icon('pixel.gif', get_lang('Report'), array('class' => 'toolactionplaceholdericon toolactionquizscores')) . get_lang('Report') . '</a>';
   }
   echo '<form id="form1a" name="form1a" method="post" action="'.api_get_path(WEB_CODE_PATH).'exercice/exercice.php?show='.Security :: remove_XSS($_GET['show']).'">';
   echo '<input type="hidden" name="export_report" value="export_report">';
   echo '<input type="hidden" name="export_format" value="csv">';
   echo '</form>';
   echo '<form id="form1b" name="form1b" method="post" action="'.api_get_path(WEB_CODE_PATH).'exercice/exercice.php?show='.Security :: remove_XSS($_GET['show']).'">';
   echo '<input type="hidden" name="export_report" value="export_report">';
   echo '<input type="hidden" name="export_format" value="xls">';
   echo '</form>';
  }
  
 echo '</div>';
 echo '<div id="content">';
 
// if tracking is enabled
if ($_configuration['tracking_enabled']) {
    
  $limitExPage = 10;
  $page = intval($_GET['page']);
  $from = $page * $limitExPage;
  $count = 0;   
  
  $session_id_and = '';
  if (api_get_session_id() != 0) {
   $session_id_and = ' AND te.session_id = ' . api_get_session_id() . ' ';
  }
  
  $sql_filter = '';
  if ($filter_by_not_revised) {
     $sql_filter .= " AND te.exe_id NOT IN(SELECT exe_id FROM $tbl_attempt WHERE author != '')";
  }
  else if ($filter_by_revised) {
     $sql_filter .= " AND te.exe_id IN(SELECT exe_id FROM $tbl_attempt WHERE author != '')";
  }
  
  
  if ($is_allowedToEdit || $is_tutor) {
   $user_id_and = '';
   if (!empty($_POST['filter_by_user'])) {
      if ($_POST['filter_by_user'] == 'all') {
       $user_id_and = " AND user_id like '%'";
      } else {
       $user_id_and = " AND user_id = '" . Database :: escape_string((int) $_POST['filter_by_user']) . "' ";
      }
   }
   if ($_GET['gradebook'] == 'view') {
    $exercise_where_query = 'te.exe_exo_id =ce.id AND ';
   }

   $sql = "SELECT CONCAT(lastname,' ',firstname) as users, ce.title, te.exe_result ,
								 te.exe_weighting, UNIX_TIMESTAMP(te.exe_date), te.exe_id, email, UNIX_TIMESTAMP(te.start_date), steps_counter,exe_user_id as user_id,te.exe_duration,ce.id
						  FROM $TBL_EXERCICES AS ce 
						  INNER JOIN $TBL_TRACK_EXERCICES AS te
						  	ON te.exe_exo_id = ce.id 
						  INNER JOIN $TBL_USER AS user
						  	ON te.exe_user_id = user.user_id
						  WHERE te.status != 'incomplete' 
						  AND te.status != 'left_incomplete'
						  AND te.exe_cours_id='" . Database :: escape_string($_cid) . "'
   							$user_id_and 
   							$session_id_and 
						  AND ce.active <>-1 
						  AND orig_lp_id = 0 
						  AND orig_lp_item_id = 0
                                                  AND user.status <> 1
                                                  AND te.exe_exo_id=".$_GET['exerciceId']."
                                                  $sql_filter
						  ORDER BY users, te.exe_cours_id ASC, ce.title ASC, te.exe_date DESC LIMIT " . (int) $from . "," . (int) ($limitExPage + 1);
   
    
   $nbrRows = "SELECT count(*)
						  FROM $TBL_EXERCICES AS ce 
						  INNER JOIN $TBL_TRACK_EXERCICES AS te
						  	ON te.exe_exo_id = ce.id 
						  INNER JOIN $TBL_USER AS user
						  	ON te.exe_user_id = user.user_id
						  WHERE te.status != 'incomplete' 
						  AND te.status != 'left_incomplete'
						  AND te.exe_cours_id='" . Database :: escape_string($_cid) . "'
   							$user_id_and 
   							$session_id_and 
						  AND ce.active <>-1 
						  AND orig_lp_id = 0 
						  AND orig_lp_item_id = 0
                                                  AND user.status <> 1
                                                  AND te.exe_exo_id=".$_GET['exerciceId'].
                                                  $sql_filter;

    $rp_count = Database::query($nbrRows);
    $row_count = Database::fetch_row($rp_count);
    
    $count = $row_count[0];
    
   $hpsql = "SELECT CONCAT(tu.lastname,' ',tu.firstname), tth.exe_name,
								tth.exe_result , tth.exe_weighting, UNIX_TIMESTAMP(tth.exe_date)
							FROM $TBL_TRACK_HOTPOTATOES tth, $TBL_USER tu
							WHERE  tu.user_id=tth.exe_user_id AND tth.exe_cours_id = '" . Database :: escape_string($_cid) . " $user_id_and '
							ORDER BY tth.exe_cours_id ASC, tth.exe_date DESC";
   
//   $rs_count = Database::query("SELECT count(*)
//						  FROM $TBL_EXERCICES AS ce 
//						  INNER JOIN $TBL_TRACK_EXERCICES AS te ON te.exe_exo_id = ce.id 
//						  INNER JOIN $TBL_USER AS user ON te.exe_user_id = user.user_id
//						  WHERE te.status != 'incomplete' 
//						  AND te.exe_cours_id='" . Database :: escape_string($_cid) . "' $user_id_and $session_id_and 
//						  AND ce.active <>-1 
//						  AND orig_lp_id = 0 
//						  AND orig_lp_item_id = 0
//                                                  $sql_filter
//                                                  AND user.status <> 1");
//                                                  $row_count = Database::fetch_row($rs_count);
//                                                  $count = $row_count[0];
   
  } else {
   // get only this user's results
   $user_id_and = ' AND te.exe_user_id = ' . Database :: escape_string(api_get_user_id()) . ' ';

   $sql = "SELECT CONCAT(lastname,' ',firstname) as users, ce.title, te.exe_result ,
							 te.exe_weighting, UNIX_TIMESTAMP(te.exe_date), te.exe_id, email, UNIX_TIMESTAMP(te.start_date), steps_counter,cuser.user_id,te.exe_duration, ce.id, ce.results_disabled
						  FROM $TBL_EXERCICES AS ce , $TBL_TRACK_EXERCICES AS te, $TBL_USER AS user,$tbl_course_rel_user AS cuser
						  WHERE  user.user_id=cuser.user_id 
                                                    AND te.exe_exo_id = ce.id 
                                                    AND te.status != 'incomplete' 
                                                    AND cuser.user_id=te.exe_user_id 
                                                    AND te.exe_cours_id='" . Database :: escape_string($_cid) . "'
                                                    AND cuser.status<>1 $user_id_and $session_id_and 
                                                    AND ce.active <>-1 
                                                    AND orig_lp_id = 0 
                                                    AND orig_lp_item_id = 0
                                                    AND cuser.course_code=te.exe_cours_id
                                                    
                                                    $sql_filter
                                                    ORDER BY users, te.exe_cours_id ASC, ce.title ASC, te.exe_date DESC LIMIT " . (int) $from . "," . (int) ($limitExPage + 1);

   $hpsql = "SELECT '',exe_name, exe_result , exe_weighting, UNIX_TIMESTAMP(exe_date)
						FROM $TBL_TRACK_HOTPOTATOES
						WHERE exe_user_id = '" . $_user['user_id'] . "' AND exe_cours_id = '" . Database :: escape_string($_cid) . "'
						ORDER BY exe_cours_id ASC, exe_date DESC";
   
   
   
   
    $rs_count = Database::query("SELECT count(*)
                                  FROM $TBL_EXERCICES AS ce , $TBL_TRACK_EXERCICES AS te, $TBL_USER AS user,$tbl_course_rel_user AS cuser
                                  WHERE  user.user_id=cuser.user_id AND te.exe_exo_id = ce.id AND te.status != 'incomplete' AND cuser.user_id=te.exe_user_id AND te.exe_cours_id='" . Database :: escape_string($_cid) . "'
                                  AND cuser.status<>1 $user_id_and $session_id_and AND ce.active <>-1 AND orig_lp_id = 0 AND orig_lp_item_id = 0
                                  AND cuser.course_code=te.exe_cours_id  
                                  $sql_filter");
    
    
    
    $row_count = Database::fetch_row($rs_count);
    $count = $row_count[0];
  } 
 
  if ($count) {
    echo '<span>'.($page + 1).'/'.(ceil($count/$limitExPage)).'('.$count.')</span>';
  }
 echo '<span style="float:right">';
 //show pages navigation link for previous page
 if ($page) {
  echo "<a href=\"" . api_get_self() . "?".api_get_cidreq() .(!empty($filter)?'&filter='.intval($filter):'&filter=1')."&page=" . ($page - 1) . "&exerciceId=".$exerciceId."\">" . Display :: return_icon('prev.png', get_lang('Prev'), array('style'=>'vertical-align:middle')) . get_lang("PreviousPage") . "</a> | ";
 } elseif ($count > $limitExPage) {
  echo Display :: return_icon('prev.png', get_lang('Prev'), array('style'=>'vertical-align:middle')) . get_lang('PreviousPage') . " | ";
 }

 //show pages navigation link for previous page
 if ($count > (($page + 1) * $limitExPage)) {
  echo "<a href=\"" . api_get_self() . "?".api_get_cidreq().(!empty($filter)?'&filter='.intval($filter):'&filter=1')."&page=" . ($page + 1) . "&exerciceId=".$exerciceId. "\">" . get_lang("NextPage") . Display :: return_icon('next.png', get_lang('Next'), array('style'=>'vertical-align:middle')) . "</a>";
 } else if ($page) {
  echo get_lang("NextPage") . Display :: return_icon('next.png', get_lang('Next'), array('style'=>'vertical-align:middle'));
 }
 echo '</span>';    
?>
 <table class="data_table">
  <tr class="row_odd">
<?php if ($is_allowedToEdit || $is_tutor): ?>
    <th><?php echo get_lang('LastName'); ?></th>
    <th><?php echo get_lang('FirstName'); ?></th>
<?php endif; ?>
    <th><?php echo get_lang('Exercice'); ?></th>
    <th><?php echo get_lang('Duration'); ?></th>
    <th><?php echo get_lang('Date'); ?></th>
    <th><?php echo get_lang('Result'); ?></th>
    <th><?php echo '%'; ?></th>
    <th colspan="3"><?php echo (($is_allowedToEdit || $is_tutor) ? get_lang("CorrectTest") : get_lang("ViewTest")); ?></th>
   </tr>
<?php
  
 
  $results = getManyResultsXCol($sql, 12);
  
  $hpresults = getManyResultsXCol($hpsql, 5);

  $NoTestRes = 0;
  $NoHPTestRes = 0;
  //Print the results of tests
  $lang_nostartdate = get_lang('NoStartDate') . ' / ';

  if (is_array($results)) {
   $users_array_id = array();
   if ($_GET['gradebook'] == 'view') {
    $filter_by_no_revised = true;
    $from_gradebook = true;
   }
   $sizeof = sizeof($results);
   $user_list_name = $user_list_id = array();

   for ($i = 0; $i < $sizeof; $i++) {
    $revised = false;
    $sql_exe = 'SELECT exe_id FROM ' . Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT_RECORDING) . '
									  WHERE author != ' . "''" . ' AND exe_id = ' . "'" . Database :: escape_string($results[$i][5]) . "'" . ' LIMIT 1';
    $query = api_sql_query($sql_exe, __FILE__, __LINE__);

    if (Database :: num_rows($query) > 0) {
     $revised = true;
    }
    /*if ($filter_by_not_revised && $revised == true) {
     continue;
    }
    if ($filter_by_revised && $revised == false) {
     continue;
    }*/
    if ($from_gradebook && ($is_allowedToEdit || $is_tutor)) {
     if (in_array($results[$i][1] . $results[$i][0], $users_array_id)) {
    
      continue;
     }
     $users_array_id[] = $results[$i][1] . $results[$i][0];
    }

    $user_list_name[] = $results[$i][0];
    $user_list_id[] = $results[$i][9];
    $id = $results[$i][5];
	$quiz_id = intval($results[$i][11]);
    $mailid = $results[$i][6];
    $user = $results[$i][0];
    $test = $results[$i][1];
    $dt = utf8_encode(strftime($dateTimeFormatLong, $results[$i][4]));    
    $res = $results[$i][2];

    //$duration = intval($results[$i][10]);
	
        
    // we filter the results if we have the permission to
    if (isset($results[$i][12]))
     $result_disabled = intval($results[$i][12]);
    else
     $result_disabled = 0;
    if ($result_disabled == 0) {
     echo '<tr';
     if ($i % 2 == 0) {
      echo ' class="row_odd"';
     } else {
      echo ' class="row_even"';
     }
     echo '>';
     $add_start_date = $lang_nostartdate;

     if ($is_allowedToEdit || $is_tutor) {
      $user = $results[$i][0];
	  $url_user = urlencode($user);
      $user_info = array();
      // Split user data
      $user_info = explode(' ', $user);
      echo '<td>' . $user_info[0] . ' </td>';
      echo '<td>' . $user_info[1] . ' </td>';
     }
     echo '<td>' . $test . '</td>';
     echo '<td>';
     $add_end_real_date = $results[$i][4];
     $add_start_real_date = $results[$i][7];
     if (($results[$i][4] == 0) || ($add_start_real_date > $add_end_real_date)) {
         $add_end_real_date = $add_start_real_date;
     }
     if ($add_start_real_date > 1) {
        
      echo round((($add_end_real_date - $add_start_real_date) / 60), 1) . ' ' . get_lang('MinMinutes');
      if ($results[$i][8] > 1) {
       echo ' ( ' . $results[$i][8] . ' ' . get_lang('Steps') . ' )';
      }
      $add_start_date = format_locale_date('%b %d, %Y %H:%M', $add_start_real_date) . ' / ';
     } else {
      echo get_lang('NoLogOfDuration');
     }
     echo '</td>';

     echo '<td>' . $add_start_date . format_locale_date('%b %d, %Y %H:%M', $add_end_real_date) . '</td>'; //get_lang('dateTimeFormatLong')
    
     // there are already a duration test period calculated??
     //echo '<td>'.sprintf(get_lang('DurationFormat'), $duration).'</td>';
     // if the float look like 10.00 we show only 10

     //$my_res = $results[$i][2];
     //$my_total = $results[$i][3];

	 $qn_id = array();
	$sqlqn = "SELECT DISTINCT(question_id) FROM $tbl_e_attempt WHERE exe_id = ".$id;
	$resqn = Database::query($sqlqn, __FILE__, __LINE__);
	while($rowqn = Database::fetch_array($resqn)){
		$qn_id[] = $rowqn['question_id'];
	}
	
	$sql_weight = "SELECT sum(ponderation) FROM $tbl_quiz_questions qn WHERE qn.id IN ('".implode("','",$qn_id)."')";
	$res_weight = Database::query($sql_weight, __FILE__, __LINE__);
	$my_total = Database::result($res_weight, 0, 0);		
	
	$result = new ExerciseResult();
	$my_res = $result->getRealScore($id, $quiz_id);

     echo '<td>' . round($my_res) . ' / ' . round($my_total) . '</td>';
     echo '<td>' . round(($my_res / ($my_total != 0 ? $my_total : 1)) * 100) . '%</td>';
     
     if ($is_allowedToEdit || $is_tutor) {
      if ($revised) {
       echo "<td align='center'><a href='exercise_show.php?".api_get_cidreq()."&action=edit&user=$url_user&dt=$dt&res=$res&id=$id&email=$mailid&exerciseId=$quiz_id'>" . Display :: return_icon('pixel.gif', get_lang('Edit'),array('class'=>'actionplaceholdericon actionedit'))."</a></td>";
      } else {
       echo "<td align='center'><a href='exercise_show.php?".api_get_cidreq()."&action=qualify&user=$url_user&dt=$dt&res=$res&id=$id&email=$mailid&exerciseId=$quiz_id'>" . Display :: return_icon('pixel.gif', get_lang('Qualify'),array('class'=>'actionplaceholdericon actionmarkexercise'))."</a></td>";
      }   

      if (api_is_allowed_to_edit(false,true,true)) { // trainer is allowed to delete, or coach if he has extended rights 
       //echo '<td> <a href="exercice.php?'.api_get_cidreq().'&filter=' . (intval($filter)) . '&delete=delete&did=' . $id . '" onclick="javascript:if(!confirm(\'' . sprintf(get_lang('DeleteAttempt'), $user, $dt) . '\')) return false;">' . Display :: return_icon('pixel.gif', get_lang('Delete'),array('class'=>'actionplaceholdericon actiondelete')) . '</a></td>';
		echo '<td align="center"> <a href="javascript:void(0);" onclick="Alert_Confim_Delete( \'exercice.php?'.api_get_cidreq().'&filter=' . (intval($filter)) . '&delete=delete&did=' . $id. '\',\''.get_lang("ConfirmationDialog").'\',\''.get_lang("DeleteAttempt").'\');">' . Display :: return_icon('pixel.gif', get_lang('Delete'),array('class'=>'actionplaceholdericon actiondelete')) . '</a></td>';
      }
      if ($is_allowedToEdit) {
       echo '<td align="center"> <a href="exercice_history.php?'.api_get_cidreq().'&exe_id=' . $id . '">' . Display :: return_icon('pixel.gif', get_lang('ViewHistoryChange'),array('class'=>'actionplaceholdericon actionfootprint')) . '</a></td>';
      }
    } else {
      if ($revised)
       echo "<td><a href='exercise_show.php?dt=$dt&res=$res&id=$id&exerciseId=$quiz_id'><img src='../img/view.png'></a> </td>";
      else
       echo '<td>&nbsp;' . get_lang('NoResult').'</td>';
   }  

     echo '</tr>';
    }
   }
  } else {
   $NoTestRes = 1;
  }

  // Print the Result of Hotpotatoes Tests
  if (is_array($hpresults)) {
   for ($i = 0; $i < sizeof($hpresults); $i++) {
    $title = GetQuizName($hpresults[$i][1], $documentPath);
    if ($title == '') {
     $title = basename($hpresults[$i][1]);
    }
    echo '<tr>';
    if ($is_allowedToEdit) {
     echo '<td class="content">' . $hpresults[$i][0] . '</td>';
    }
    echo '<td class="content">' . $title . '</td>';
    echo '<td class="content">' . strftime($dateTimeFormatLong, $hpresults[$i][4]) . '</td>';
    echo '<td class="content">' . round(($hpresults[$i][2] / ($hpresults[$i][3] != 0 ? $hpresults[$i][3] : 1)) * 100) . '% (' . $hpresults[$i][2] . ' / ' . $hpresults[$i][3] . ')</td>';
    echo '<td>&nbsp;</td>'; //there is no possibility to edit the results of a Hotpotatoes test
    echo '</tr>';
   }
  } else {
   $NoHPTestRes = 1;
  }

  if ($NoTestRes == 1 && $NoHPTestRes == 1) {
?>
   <tr>
    <td colspan="6"><?php echo get_lang("NoResult"); ?></td>
   </tr>
 <?php
  }
 ?>
 </table>

<!--
 </div>
 <div class="actions"></div>
 -->
 <?php
 }
echo '</div>';
 Display::display_footer();

?>
