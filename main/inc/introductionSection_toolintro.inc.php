<?php
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Dokeos Module. It can be inserted on any
 * Dokeos Module, provided a connection to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction"
 * in the course Database. Each module introduction has an Id stored on
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   intro_text :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
*
*	@package dokeos.include
==============================================================================
*/

include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';

echo '<script src="'.api_get_path('WEB_CODE_PATH').'course_home/js/course_scenario.js" type="text/javascript" language="javascript"></script>';
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/flexcroll.js"></script>';
//echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'application/author/assets/js/functions.js"></script>';
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/homeFunctions.js"></script>';
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/homeModel.js"></script>';
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/homeController.js"></script>';
echo '<link   type="text/css"        href="'.api_get_path('WEB_CODE_PATH').'course_home/css/home.css" rel="stylesheet"></link>';

/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/
$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
$TBL_TOOL_INTRO = Database :: get_course_table(TABLE_TOOL_INTRO);
//$step_icon_path = api_get_path(WEB_PATH).'main/course_home/icons/';
$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$step_icon_path = api_get_path(WEB_COURSE_PATH).$course_code.'/document/icons/thumbnail/';
global $_user;

$sql = "SELECT intro_text FROM $TBL_TOOL_INTRO WHERE id = 'active_scenario'";
$res = Database::query($sql, __FILE__, __LINE__);
$active_scenario = Database::result($res,0,0);

if(1) {
	$sql = "SELECT intro_text FROM $TBL_TOOL_INTRO WHERE id = 'course_homepage'";
	$res = Database::query($sql, __FILE__, __LINE__);
	$intro_text = Database::result($res,0,0);

	if(empty($intro_text) || $intro_text == '&nbsp;'){
		if(api_is_allowed_to_edit()){
		echo "<div>";
//                echo '<div class="text-scenario-blender">'.get_lang("Scenario").'</div>';
		echo '<div class="introtext textCenter">';		
                echo '<a id="blender" href="'.api_get_path(WEB_CODE_PATH).'course_home/static_scenario.php?'.api_get_cidReq().'">'.get_lang("IntroductionText").'</a>';
//		echo '<button class="save" style=" float: right;    margin-right: -5px;"></button>';		
		echo '</div>';
                
		echo '<div class="clear"></div>';   
		echo "</div>";
	}
	}
	else{
		echo '<div id="courseintroduction">'; 		

		echo $intro_text;

		if(api_is_allowed_to_edit()) {		

			echo '</br></br>';
			//echo '<button class="save" name="edit_static_scenario" id="edit_static_scenario">'.get_lang("EditScenario").'</button>';
			echo '<div class="introtext" style="float:none;text-align:right;"><a id="blender" href="'.api_get_path(WEB_CODE_PATH).'course_home/static_scenario.php?'.api_get_cidReq().'">'.get_lang("ModifyIntroductionText").'</a></div>';
			//echo '</br></br>';
		}
                        echo '</div>';
                        
                }
	}        

?>        
