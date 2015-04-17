<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */
require_once '../../main/inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';

$rowIndex = $_GET['rowIndex'];
$colIndex = $_GET['colIndex'];

$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);

$sql = "SELECT * FROM $TBL_SCENARIO_STEPS step, $TBL_SCENARIO_ACTIVITY act WHERE step.id =act.step_id AND step.step_created_order = ".$colIndex." AND (act.activity_type = 'quiz' OR act.activity_type = 'module')";
$res = Database::query($sql, __FILE__, __LINE__);
$num_rows = Database::num_rows($res);

if($num_rows <> 0){

	$sql_steps = "SELECT step_completion_option, step_completion_percent FROM $TBL_SCENARIO_STEPS WHERE step_created_order = ".$colIndex." AND session_id = ".api_get_session_id();
	$res_steps = Database::query($sql_steps, __FILE__, __LINE__);
	$step_completion_option = Database::result($res_steps, 0, 0);
	$step_completion_percent = Database::result($res_steps, 0, 1);

	if(strpos($step_completion_option,"@") !== false){
		list($option, $sub_option) = split("@",$step_completion_option);
		$checkbox2_checked = "";
		$selected_quiz = '';
		$selected_module = 'selected';
		$class_name = "module_completion_visible";
		if($sub_option == 'Progress'){
			$checkbox3_checked = "checked";
			$selected_progress = 'selected';
			$selected_score = '';
		}
		else {
			$checkbox3_checked = "";
			$selected_progress = '';
			$selected_score = 'selected';
		}
	}
	else if(trim($step_completion_option) == 'Quiz'){
		$selected_quiz = 'selected';
		$selected_module = '';		
		$checkbox2_checked = "checked";
		$checkbox3_checked = "";
		$class_name = "module_completion";
		$selected_progress = '';
		$selected_score = 'selected';
	}
	else if(trim($step_completion_option) == 'Module'){
		$selected_quiz = '';
		$selected_module = 'selected';		
		$checkbox2_checked = "";
		$checkbox3_checked = "";
		$class_name = "module_completion_visible";
		$selected_progress = '';
		$selected_score = 'selected';
	}
	else {
		$checkbox2_checked = "checked";
		$checkbox3_checked = "checked";
		$selected_quiz = 'selected';
		$selected_module = '';	
		$selected_progress = 'selected';
		$selected_score = '';
		$class_name = "module_completion";
	}
	
echo '<style>
.ui-slider .ui-slider-handle { position: absolute; z-index: 2; height: 18px;width:15px; cursor: default;background:#E0E0E0; top:-4px; }
.module_completion { display:none;}
.module_completion_visible { display:;}
</style>';
}

echo '<script>
		$(document).ready(function(){
			
			$("#cb_quiz").click(function(){
				$("#hid_tr").removeClass("module_completion_visible");
				$("#hid_tr").addClass("module_completion");
				var parent = $(this).parents(".switch");
				$("#cb_module",parent).removeClass("selected");
				$(this).addClass("selected");
				$("#checkbox2").attr("checked",true);
			});
			$("#cb_module").click(function(){
				$("#hid_tr").removeClass("module_completion");
				$("#hid_tr").addClass("module_completion_visible");
				var parent = $(this).parents(".switch");
				$("#cb_quiz",parent).removeClass("selected");
				$(this).addClass("selected");
				$("#checkbox2").attr("checked",false);
			});
			$("#cb_progress").click(function(){				
				var parent = $(this).parents(".switch");
				$("#cb_score",parent).removeClass("selected");
				$(this).addClass("selected");
				$("#checkbox3").attr("checked",true);
			});
			$("#cb_score").click(function(){			
				var parent = $(this).parents(".switch");
				$("#cb_progress",parent).removeClass("selected");
				$(this).addClass("selected");
				$("#checkbox3").attr("checked",false);
			});
		});
	</script>';

if($num_rows > 0){

$form .= '<div id="div1">';
$form .= '<form id="add_quiz_to_scenario" name="add_quiz_to_scenario"  method="GET">';
$form .= '<input type="hidden" name="rowIndex" id="rowIndex" value="'.$rowIndex.'">';
$form .= '<input type="hidden" name="colIndex" id="colIndex" value="'.$colIndex.'">';
$form .= '<table width="100%" class="datatable1" cellpadding="10" cellspacing="10">';	
$form .= '<tr><td width="45%" align="right">'.api_convert_encoding(get_lang("ChooseCompletion"),'UTF-8',api_get_system_encoding()).'</td><td><span class="field switch">
		<label id="cb_quiz" class="cb-enable '.$selected_quiz.'"><span>Quiz</span></label>
		<label id="cb_module" class="cb-disable '.$selected_module.'"><span>Module</span></label>
		<span style="display:none;"><input type="checkbox" id="checkbox2" class="checkbox" name="field2" '.$checkbox2_checked.' /></span>
	</span></td></tr>';
$form .= '<tr id="hid_tr" class="'.$class_name.'"><td width="45%" align="right">'.api_convert_encoding(get_lang("ModuleCompletion"),'UTF-8',api_get_system_encoding()).'</td><td><span class="field switch">
		<label id="cb_progress" class="cb-enable '.$selected_progress.'"><span>Progress</span></label>
		<label id="cb_score" class="cb-disable '.$selected_score.'"><span>Score</span></label>
		<span style="display:none;"><input type="checkbox" id="checkbox3" class="checkbox" name="field2" '.$checkbox3_checked.' /></span>
	</span></td></tr>';
//$form .= '<tr><td><input type = "radio" name = "showQuiz" id = "score_quiz" value="Quiz" /></td><td>Quiz</td></tr>';
//$form .= '<tr><td><input type = "radio" name = "showQuiz" id = "score_module" value="Module" /></td><td>Module</td></tr>';
//$form .= '<tr><td width="35%">&nbsp;</td><td><span class="labelcss">' . get_lang('Score') . ' :&nbsp;</span><input type="text" id="amount" name="amount" style="border: 0; color: #f6931f; font-weight: bold;" size="3" />&nbsp;%</td></tr>';
//$form .= '<tr><td colspan="2"><div class="mo-slider" id="slider-range-min"></div></td></tr>';
$form .= '<tr><td width="45%" align="right">'.api_convert_encoding(get_lang("MinPercentCompletion"),'UTF-8',api_get_system_encoding()).'</td><td><select name="min_score" id="min-score" style="width:25%;">';
$form .= '<option value="0">0</option>';
foreach (range(10, 100, 10) as $num):
	if($step_completion_percent == $num){
	$form .= '<option value="'.$num.'" selected>'.$num.'</option>';
	}
	else {
	$form .= '<option value="'.$num.'">'.$num.'</option>';
	}
endforeach;
$form .= '</select>';
$form .= '</td></tr>';
$form .= '<tr><td width="45%">&nbsp;</td><td><button type="submit" id="submitbtn" class="save">' . get_lang('Validate') . '</button></td></tr>';
$form .= '</table>';
$form .= '</form>';
$form .= '</div>';
}
else {
	$form = '<br><br><div>'.api_convert_encoding(get_lang("AtleastOneQuizOrModule"),'UTF-8',api_get_system_encoding()).'</div>';
}

echo $form;

?>