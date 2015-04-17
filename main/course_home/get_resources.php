<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */
require_once '../../main/inc/global.inc.php';

$action = $_GET['action'];

switch ($action) {
	case 'get_doc_list' :
		$TBL_DOCUMENT = Database :: get_course_table(TABLE_DOCUMENT);
		$TABLE_ITEMPROPERTY = Database::get_course_table(TABLE_ITEM_PROPERTY);
                $session_condition = api_get_session_condition(api_get_session_id(), true, true);
		$sql = "SELECT *
				FROM  ".$TABLE_ITEMPROPERTY."  AS last, ".$TBL_DOCUMENT."  AS docs
				WHERE docs.id = last.ref
				AND docs.path LIKE '/%'
				AND docs.path NOT LIKE '/%/%'
				AND last.tool = '".TOOL_DOCUMENT."'
				AND last.lastedit_type != '".DocumentAddedFromLearnpath."'
				AND docs.filetype <> 'folder'
				AND last.visibility <> 2 $session_condition";

		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		
		echo '<script>
			$(document).ready(function(){
				var theme_color = $("#default_step_color").val();
				$(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				$(".jspDrag").css("background",theme_color); 
			});
		</script>';

		echo '<div class="scroll-pane1">';
		echo '<table class="new_table">';
		echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';

		if($num_rows == 0){
			echo '<tr><td colspan="2">'.get_lang("NoDocuments").'</td></tr>';
		}
		else {
			$i = 1;
			while($row = Database::fetch_array($res)) {
				if(($i%2) == 0){
					$class = "class='row_odd'";
				}
				else {
					$class = "class='row_even'";
				}
				if($i == 1) {
					$checked = 'checked';
				}
				else {
					$checked = '';
				}
				//echo '<tr><td><input type = "radio" name = "selDoc" class="doc_class" value="'.$row['id'].'" /></td><td>'.$row['title'].'</td></tr>';
				echo '<tr '.$class.'><td width="90%" class="doc_class" id="sel_'.$row['id'].'">'.api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding()).'</td><td width="10%" align="center"><input type="radio" id="radio_'.$row['id'].'" name="selDoc" class="regular-radio doc_class" value="'.$row['id'].'" /><label for="radio_'.$row['id'].'"></label></td></tr>';
				$i++;
			}
		}
		echo '</table>';
		echo '</div>';

	break;
   case 'get_page_list' :
                $api_get_current_access_url_id=( api_get_current_access_url_id()==-1)?1:api_get_current_access_url_id();
       
                $TBL_PAGE = Database::get_main_table(TABLE_MAIN_NODE);
                $TBL_PAGE_REL_COURSE = Database::get_main_table(TABLE_MAIN_NODE_REL_COURSE);
                             
		$sql = "SELECT n.id,n.title,n.content,n.access_url_id,n.active,n.language_id,n.enabled,n.node_type,c.course_code 
                FROM $TBL_PAGE n INNER JOIN $TBL_PAGE_REL_COURSE c 
                ON (n.id = c.node_id) 
                WHERE active = 1 
				AND course_code = '".api_get_course_id()."'
                AND access_url_id =".$api_get_current_access_url_id;

		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		
		echo '<script>
			$(document).ready(function(){
				var theme_color = $("#default_step_color").val();
				$(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				$(".jspDrag").css("background",theme_color); 
			});
		</script>';

		echo '<div class="scroll-pane1">';
		echo '<table class="new_table">';
		echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';

		if($num_rows == 0){
			echo '<tr><td colspan="2">'.get_lang("NoPages").'</td></tr>';
		}
		else {
			$i = 1;
			while($row = Database::fetch_array($res)) {
				if(($i%2) == 0){
					$class = "class='row_odd'";
				}
				else {
					$class = "class='row_even'";
				}
				if($i == 1) {
					$checked = 'checked';
				}
				else {
					$checked = '';
				}
				//echo '<tr><td><input type = "radio" name = "selDoc" class="doc_class" value="'.$row['id'].'" /></td><td>'.$row['title'].'</td></tr>';
				echo '<tr '.$class.'><td width="90%" class="page_class" id="sel_'.$row['id'].'">'.api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding()).'</td><td width="10%" align="center"><input type="radio" id="radio_'.$row['id'].'" name="selDoc" class="regular-radio page_class" value="'.$row['id'].'" /><label for="radio_'.$row['id'].'"></label></td></tr>';
				$i++;
			}
		}
		echo '</table>';
		echo '</div>';

	break;
   case 'get_quiz_list' :        
		$TBL_EXERCICES = Database :: get_course_table(TABLE_QUIZ_TEST);
		$TBL_QUIZ_QN = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$TBL_REL_QUIZ = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);
		$tbl_scenario_items   = Database::get_course_table(TABLE_COURSE_SCENARIO_ITEMS);		
		
                $session_condition = api_get_session_condition(api_get_session_id(), true, true);            
		$sql = "SELECT * FROM $TBL_EXERCICES WHERE active <> -1 $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);

		echo '<script>
			$(document).ready(function(){
				var theme_color = $("#default_step_color").val();
				$(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				$(".jspDrag").css("background",theme_color); 
			});
		</script>';
		
		echo '<div class="scroll-pane1">';
		echo '<table class="new_table">';
		echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';

		if($num_rows == 0){
			echo '<tr><td colspan="2">'.get_lang("NoQuiz").'</td></tr>';
		}
		else {
			$i = 1;
			while($row = Database::fetch_array($res)) {
				if(($i%2) == 0){
					$class = "class='row_odd'";
				}
				else {
					$class = "class='row_even'";
				}
				if($i == 1) {
					$checked = 'checked';
				}
				else {
					$checked = '';
				}
				echo '<tr '.$class.'><td width="90%" class="quiz_class" id="sel_'.$row['id'].'">'.api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding()).'</td><td align="center" width="10%"><input type="radio" id="radio_'.$row['id'].'" name="selQuiz" class="regular-radio quiz_class" value="'.$row['id'].'" /><label for="radio_'.$row['id'].'"></label></td></tr>';
				//echo '<tr><td><input type = "radio" name = "selQuiz" class="quiz_class" value="'.$row['id'].'" /></td><td>'.$row['title'].'</td></tr>';
				$i++;
			}
		}
		echo '</table>';
		echo '</div>';

	break;
	case 'get_exam_list' :        
		$TBL_EXERCICES = Database :: get_course_table(TABLE_QUIZ_TEST);
		$TBL_EXAM = Database :: get_course_table(TABLE_EXAM);		
		$tbl_scenario_items   = Database::get_course_table(TABLE_COURSE_SCENARIO_ITEMS);		
                $session_id = api_get_session_id();
		$sql = "SELECT exam.id AS exam_id, exam.exam_name FROM $TBL_EXAM exam, $TBL_EXERCICES quiz WHERE exam.quiz_id = quiz.id AND exam.session_id IN (0, $session_id)";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);

		echo '<script>
			$(document).ready(function(){
				var theme_color = $("#default_step_color").val();
				$(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				$(".jspDrag").css("background",theme_color); 
			});
		</script>';
		
		echo '<div class="scroll-pane1">';
		echo '<table class="new_table">';
		echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';

		if($num_rows == 0){
			echo '<tr><td colspan="2">'.get_lang("NoExam").'</td></tr>';
		}
		else {
			$i = 1;
			while($row = Database::fetch_array($res)) {
				if(($i%2) == 0){
					$class = "class='row_odd'";
				}
				else {
					$class = "class='row_even'";
				}
				if($i == 1) {
					$checked = 'checked';
				}
				else {
					$checked = '';
				}
				echo '<tr '.$class.'><td width="90%" class="exam_class" id="sel_'.$row['exam_id'].'">'.api_convert_encoding($row['exam_name'],'UTF-8',api_get_system_encoding()).'</td><td align="center" width="10%"><input type="radio" id="radio_'.$row['exam_id'].'" name="selExam" class="regular-radio exam_class" value="'.$row['exam_id'].'" /><label for="radio_'.$row['exam_id'].'"></label></td></tr>';
				//echo '<tr><td><input type = "radio" name = "selQuiz" class="quiz_class" value="'.$row['id'].'" /></td><td>'.$row['title'].'</td></tr>';
				$i++;
			}
		}
		echo '</table>';
		echo '</div>';

	break;
	case 'get_modules_list' :        
		$TBL_LP = Database :: get_course_table(TABLE_LP_MAIN);			
		
                $session_condition = api_get_session_condition(api_get_session_id(), false, true);
		$sql = "SELECT * FROM $TBL_LP $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);

		echo '<script>
			$(document).ready(function(){
				var theme_color = $("#default_step_color").val();
				$(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				$(".jspDrag").css("background",theme_color); 
			});
		</script>';
		
		echo '<div class="scroll-pane1">';
		echo '<table class="new_table">';
		echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';

		if($num_rows == 0){
			echo '<tr><td colspan="2">'.get_lang("NoModules").'</td></tr>';
		}
		else {

			$i = 1;
			while($row = Database::fetch_array($res)) {
				if(($i%2) == 0){
					$class = "class='row_odd'";
				}
				else {
					$class = "class='row_even'";
				}
				if($i == 1) {
					$checked = 'checked';
				}
				else {
					$checked = '';
				}
				echo '<tr '.$class.'><td width="90%" class="module_class" id="sel_'.$row['id'].'">'.api_convert_encoding($row['name'],'UTF-8',api_get_system_encoding()).'</td><td width="10%" align="center"><input type="radio" id="radio_'.$row['id'].'" name="selModule" class="regular-radio module_class" value="'.$row['id'].'" /><label for="radio_'.$row['id'].'"></label></td></tr>';
				//echo '<tr><td><input type = "radio" name = "selModule" class="module_class" value="'.$row['id'].'" /></td><td>'.$row['name'].'</td></tr>';
				$i++;
			}
		}
		echo '</table>';
		echo '</div>';

	break;
	case 'get_assignment_list' :        
		$TBL_ASSIGNMENT = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);			
		$session_condition = api_get_session_condition(api_get_session_id(), true, true);
		$sql = "SELECT * FROM $TBL_ASSIGNMENT WHERE filetype = 'folder' $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);

		echo '<script>
			$(document).ready(function(){
				var theme_color = $("#default_step_color").val();
				$(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				$(".jspDrag").css("background",theme_color); 
			});
		</script>';
		
		echo '<div class="scroll-pane1">';
		echo '<table class="new_table">';
		echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';

		if($num_rows == 0){
			echo '<tr><td colspan="2">'.get_lang("NoAssignment").'</td></tr>';
		}
		else {
			$i = 1;
			while($row = Database::fetch_array($res)) {
				if(($i%2) == 0){
					$class = "class='row_odd'";
				}
				else {
					$class = "class='row_even'";
				}
				if($i == 1) {
					$checked = 'checked';
				}
				else {
					$checked = '';
				}
				echo '<tr '.$class.'><td width="90%" class="assignment_class" id="sel_'.$row['id'].'">'.api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding()).'</td><td width="10%" align="center"><input type="radio" id="radio_'.$row['id'].'" name="selAssignment" class="regular-radio assignment_class" value="'.$row['id'].'"  /><label for="radio_'.$row['id'].'"></label></td></tr>';
				//echo '<tr><td><input type = "radio" name = "selAssignment" class="assignment_class" value="'.$row['id'].'" /></td><td>'.$row['title'].'</td></tr>';
				$i++;
			}
		}
		echo '</table>';
		echo '</div>';

	break;
	case 'get_survey_list' :        
		$TBL_SURVEY = Database :: get_course_table(TABLE_MAIN_SURVEY);			
		$session_condition = api_get_session_condition(api_get_session_id(), false, true);
		$sql = "SELECT * FROM $TBL_SURVEY $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);

		echo '<script>
			$(document).ready(function(){
				var theme_color = $("#default_step_color").val();
				$(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				$(".jspDrag").css("background",theme_color); 
			});
		</script>';
		
		echo '<div class="scroll-pane1">';
		echo '<table class="new_table">';
		echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';

		if($num_rows == 0){
			echo '<tr><td colspan="2">'.api_convert_encoding(get_lang("NoSurvey"),'UTF-8',api_get_system_encoding()).'</td></tr>';
		}
		else {
			$i = 1;
			while($row = Database::fetch_array($res)) {
				if(($i%2) == 0){
					$class = "class='row_odd'";
				}
				else {
					$class = "class='row_even'";
				}
				if($i == 1) {
					$checked = 'checked';
				}
				else {
					$checked = '';
				}
				echo '<tr '.$class.'><td width="90%" class="survey_class" id="sel_'.$row['survey_id'].'">'.api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding()).'</td><td width="10%" align="center"><input type="radio" id="radio_'.$row['survey_id'].'" name="selSurvey" class="regular-radio survey_class" value="'.$row['survey_id'].'" /><label for="radio_'.$row['survey_id'].'"></label></td></tr>';
				//echo '<tr><td><input type = "radio" name = "selSurvey" class="survey_class" value="'.$row['survey_id'].'" /></td><td>'.$row['title'].'</td></tr>';
				$i++;
			}
		}
		echo '</table>';
		echo '</div>';

	break;
	case 'get_steps' :
        $TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
		$colIndex = $_GET['colIndex'];
		$session_condition = api_get_session_condition(api_get_session_id(), true);
		$sql = "SELECT id, step_name FROM $TBL_SCENARIO_STEPS WHERE step_created_order < ".$colIndex." $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		$data[0] = get_lang("None");
		while($row = Database::fetch_array($res)) {
			$step_name = api_convert_encoding($row['step_name'],'UTF-8',api_get_system_encoding());
			$len = strlen($step_name);
			if($len > 22){
				$stepname = substr($step_name, 0, 22).'...';
			}
			else {
				$stepname = $step_name;
			}
			$data[$row['id']] = $stepname;
		}
		echo json_encode($data); 

	break;
	case 'get_steps_dropdown' :
                $session_condition = api_get_session_condition(api_get_session_id(), true);
                $TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
		$colIndex = $_GET['colIndex'];
		
		$sql = "SELECT id, step_name FROM $TBL_SCENARIO_STEPS WHERE step_created_order < ".$colIndex." $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		
		echo '<select style="width:125px;" id="prereq1_select_'.$colIndex.'">';
		echo '<option value="0">'.get_lang("None").'</option>';
		while($row = Database::fetch_array($res)) {
			$step_name = api_convert_encoding($row['step_name'],'UTF-8',api_get_system_encoding());
			$len = strlen($step_name);
			if($len > 22){
				$stepname = substr($step_name, 0, 22).'...';
			}
			else {
				$stepname = $step_name;
			}
			echo '<option value="'.$row['id'].'">'.$stepname.'</option>';
		}
		echo '</select>';		

	break;
	case 'get_facetoface_list' :	
		$colIndex = $_GET['colIndex'];
		$rowIndex = $_GET['rowIndex'];
		$param_id = $_GET['param_id'];		

		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);	
		
		if($param_id <> 0){
		list($activity_id, $step_id) = explode("_",$param_id);

		$sql = "SELECT ff.id, ff.name, ff.ff_type, ff.max_score FROM $TBL_FACE2FACE ff, $TBL_SCENARIO_ACTIVITY act WHERE act.step_id = ff.step_id AND act.activity_ref = ff.id AND act.step_id = ".$step_id." AND act.id = ".$activity_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		$ff_id = Database::result($res , 0, 0);
		$ff_name = Database::result($res , 0, 1);
		$ff_type = Database::result($res , 0, 2);
		$ff_max_score = Database::result($res , 0, 3);
		}
		else {
			$ff_name = '';
			$ff_type = 2;
			$ff_max_score = 0;
			$ff_id = 0;
		}

		if($ff_type == 1){
			$checked1 = "checked";
			$checked2 = "";
		}
		else {
			$checked1 = "";
			$checked2 = "checked";
		}

		echo '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput.jquery.js" language="javascript"></script>';
		echo '<script type="text/javascript">        
			// Run the script on DOM ready:

			$(function(){
						try {
				$("#add_facetoface input").customInput();
						} catch(e){}			
			});';
			if($ff_type == 1){
				echo '$(".radio2_label").css("color", "#CCC");$("#face_score").prop("disabled", true);';
			}
			else {
				echo '$(".radio1_label").css("color", "#CCC");$("#face_score").prop("disabled", false);';
			}
			echo '
				$(document).ready(function () {

				//function that hides and shows the given div 
				//when mouse on & off the page-object-area div
				$("#radio1").hover(

				function () {
					
					$("#face_score").prop("disabled", true);					
					$(".radio2_label").css("color", "#CCC");
					$(".radio1_label").css("color", "#000");
					
				}, function () {

					//$("#face_score").prop("disabled", false);					
					if($("input[type=\'radio\'].radio").is(":checked")) {
							var option_chosen = $("input[type=\'radio\'].radio:checked").val();
							if(option_chosen == 2){
								$(".radio1_label").css("color", "#CCC");
								$(".radio2_label").css("color", "#000");
							}
							else {
								$(".radio1_label").css("color", "#000");
								$(".radio2_label").css("color", "#CCC");
							}
					}								
					
				});

				$("#radio2").hover(

				function () {
					
					$("#face_score").prop("disabled", false);					
					$(".radio2_label").css("color", "#000");
					$(".radio1_label").css("color", "#CCC");
					
				}, function () {

					//$("#face_score").prop("disabled", true);
					if($("input[type=\'radio\'].radio").is(":checked")) {
							var option_chosen = $("input[type=\'radio\'].radio:checked").val();
							if(option_chosen == 2){
								$(".radio1_label").css("color", "#CCC");
								$(".radio2_label").css("color", "#000");
								$("#face_score").prop("disabled", false);
							}
							else {
								$(".radio1_label").css("color", "#000");
								$(".radio2_label").css("color", "#CCC");
								$("#face_score").prop("disabled", true);
							}
					}							
					
				});

			});
			</script>';
	
		echo '<form name="add_facetoface" id="add_facetoface"><br>';
		echo '<div class="FacetoFace1">';
		echo '<label for="name">'.api_convert_encoding(get_lang("PresenceActivityDescription"),'UTF-8',api_get_system_encoding()).'</label></br>';
		echo '<input autofocus type="text" name="name" id="name"  size="40" value="'.api_convert_encoding($ff_name,'UTF-8',api_get_system_encoding()).'" required placeholder="'.get_lang("NameRequired").'"  >';
		echo '</div>';
		echo "<div><table width='100%'>";		
		echo "<tr><td><div id='radio1'><input id='check-1' class='radio' type='radio' name='choice1' value='1' ".$checked1." /><label for='check-1' class='radio1_label'><ul style='list-style-type: none;'><li><b>".api_convert_encoding(get_lang('PassFail'),'UTF-8',api_get_system_encoding())."</b></li><li>".api_convert_encoding(get_lang('PassFailDesc1'),'UTF-8',api_get_system_encoding())."</li></ul></label></div></td></tr>";	
		echo "<tr><td><div id='radio2'><input id='check-2' class='radio' type='radio' name='choice1' value='2' ".$checked2." /><label for='check-2' class='radio2_label'><ul style='list-style-type: none;'><li><b>".api_convert_encoding(get_lang('ScoreF2F'),'UTF-8',api_get_system_encoding())."</b></li><li>".api_convert_encoding(get_lang('ScoreDesc1'),'UTF-8',api_get_system_encoding())."</li></ul></label>";
		echo "</div></td></tr>";
		echo '<tr><td style="padding-left:25px;">';
		echo '<select id="face_score" name="face_score" style="width:75px;">';
		for($i=0;$i<=20;$i++){
			if($ff_max_score == $i){
				$selected = " selected";
			}
			else {
				$selected = "";
			}
			echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
		}		
		echo '</select>';
		echo '</td></tr>';
		echo '</table>';
		echo '<button class="save" name="submit" id="submit_face2face">'.get_lang("Ok").'</button>';
		echo '<input type="hidden" name="hid_id" id="hid_id" value="'.$ff_id.'">';
		echo '</form>';
	break;
	
}