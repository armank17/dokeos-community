<?php
$language_file[] = 'course_home';
$language_file[] = 'admin';
//$use_anonymous = true;

global $_user;

require_once ('../inc/global.inc.php');
$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');

api_block_anonymous_users();

$displayNewReporting = true;
if ($displayNewReporting) {    
    if (!api_is_allowed_to_edit() && $_user['status'] == STUDENT) {
        header('Location: '.api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=learner&learnerId='.$_user['user_id']);
    }
    else {
        header('Location: '.api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=index');
    }    
    exit;
}

$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" media="print" href="'.api_get_path(WEB_PATH).'main/css/'.api_get_setting('stylesheets').'/print.css" />';

include_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'tracking.lib.php';
require 'functions.php';



/*if (!api_is_allowed_to_edit() && !api_is_coach() && !api_is_platform_admin(true)) {
	api_not_allowed(true);
}*/

if ($_GET['c'] == 'export') {
    if ($_GET['module'] == 'courses') {
        exportcourses();
        exit;
    }
    if ($_GET['module'] == 'modules') {
        exportmodules();
        exit;
    }
    if ($_GET['module'] == 'quizzes') {
        exportquizzes();
        exit;
    }
	if ($_GET['module'] == 'face2face') {
        exportfacetofaces();
        exit;
    }
    if ($_GET['module'] == 'learners') {
        exportlearners();
        exit;
    }
}
if ($_GET['c'] == 'print') {
    if ($_GET['module'] == 'courses') {
        printcourses();
    }
    if ($_GET['module'] == 'modules') {
        printmodules();
    }
    if ($_GET['module'] == 'quizzes') {
        printquizzes();
    }
	if ($_GET['module'] == 'face2face') {
        printfacetofaces();
    }
    if ($_GET['module'] == 'learners') {
        printlearners();
    }
    echo "<script type='text/javascript'>window.print();</script>";
    exit;
}

if(!api_is_allowed_to_edit()){
	//header('Location: '.api_get_path(WEB_CODE_PATH).'reporting/learners_reporting.php');
	//exit;
}

Display::display_responsive_reporting_header();
// Top actions bar
?>
<link rel="stylesheet" href="css/sprites.min.css" />
<link rel="stylesheet" href="css/responsive-tabs.css">	
<!--<link href="css/bootstrap-responsive.css" rel="stylesheet">-->
<link rel="stylesheet" href="css/custom-responsive.css" />
<link rel="stylesheet" href="css/stacktable.css" />
<link type="text/css" href="jquery-ui-1.8.2/themes/base/jquery.ui.autocomplete.css" rel="stylesheet" />
<link type="text/css" href="jquery-ui-1.8.2/themes/base/jquery.ui.theme.css" rel="stylesheet" />

<script type="text/javascript" src="jquery-ui-1.8.2/jquery-1.4.2.js"></script>
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.position.js"></script> 
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="js/canvasjs.min.js"></script>

<!-- Content -->
<?php
$page = 1;
$limit = 20;
if (isset($_GET['page']) && $_GET['page'] != '') {
    $page = $_GET['page'];
}
$page1 = ($page - 1) * $limit;

$total_courses = get_course_list();
$courses = get_course_list($page1, $limit);
$trainers = get_trainers();
$sessions = get_sessions();
$modules = get_modules_list();
//$limit_modules = get_limited_modules($page1, $limit, $modules);
$list_courses = get_course_list();
$quizzes = get_quiz_list();
?>

<div class="row-fluid">
    <div class="span12">
        <!--<div class="container" id="">-->
                   <div class="row-fluid">
            <div class="responsive-tabs">



                <!--This section is courses -->

                <h3 id="courseshead"><?php echo get_lang('Courses'); ?></h3>
                <div>	
                    <div class="clearfix"></div>

                    <div class="row-fluid">

                        <form>
                            <div class="span12 pull-right text-align-right"> 
                                <input id="course_search" name="course_search" type="text" class="input-medium search-query">  
                                <button id="coursebtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>
                                <button id="course_reset" type="button" name="reset" class="btn" /><?php echo get_lang("ResetFilter"); ?></button> 
                            </div>
                            <br/><br/>

                            <div class="span12 pull-right text-align-right">
							<?php
								if(api_is_allowed_to_edit()){
								?>
                                <select id="select_trainer">                                        
                                    <?php
                                    echo '<option value="0">' . get_lang('SelectTrainer') . '</option>';
                                    foreach ($trainers as $trainer) {
                                        echo '<option value="' . $trainer['user_id'] . '" >' . $trainer['lastname'] . ' ' . $trainer['firstname'] . '</option>';
                                    }
                                    echo "\n";
                                    ?>
                                </select>
								<?php
								}
								?>

                                <select id="select_session">
                                    <?php
                                    echo '<option value="0">' . get_lang('SelectSession') . '</option>';
                                    foreach ($sessions as $session) {
                                        echo '<option value="' . $session['id'] . '" >' . $session['name'] . '</option>';
                                    }
                                    echo "\n";
                                    ?>
                                </select>
                            </div>
                        </form>  
                    </div>                   



                    <span><h4><?php echo get_lang('CourseAverageValues'); ?></h4></span>
                    <span id="course_pages">
                        <?php
                        if (count($total_courses) > 0) {
                            //	echo "<div class='pagination'><ul id='course_pagination'>";
                            echo pagination($limit, $page, 'index.php?tab=courses&page=', count($total_courses), 'courses');
                            //	echo "</ul></div>";
                        }
                        ?>
                    </span>
					<div class="span11 chart_print" id="chartContainer" style="height:300px;display:none;"></div>
                    <table name="courses" id="courses" class="responsive large-only table-striped">
                        <thead>
                            <tr>
                                <th><?php echo get_lang('Course'); ?></th>
                                <th><?php echo get_lang('Learners'); ?></th>
                                <th><?php echo get_lang('ModulesTime'); ?></th>
                                <th><?php echo get_lang('ModulesProgress'); ?></th>
                                <th><?php echo get_lang('ModulesScore'); ?></th>
                                <th><?php echo get_lang('QuizzesScore'); ?></th>
								<th class="print_invisible"><?php echo get_lang('Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($courses as $course) {
                                $total_learners = total_numberof_learners($course['code']);
                                $module_time = total_modules_time($course['code']);
                                $module_progress = total_modules_progress($course['code'], $total_learners);
                                $module_score = total_modules_score($course['code'], $total_learners);
                                $quiz_score = total_quizzes_score($course['code'], $total_learners);
                                //$course_title = api_convert_encoding(Database::escape_string($course['title']),'UTF-8','ISO-8859-15');
                                echo "<tr>
													<td>" . $course['title'] . "</td>
													<td align='center'>" . $total_learners . "</td>
													<td align='center'>" . $module_time . "</td>
													<td align='center'>" . $module_progress . " %</td>
													<td align='center'>" . $module_score . "</td>
													<td align='center'>" . $quiz_score . "</td>
                                                                                                        <td class='print_invisible' align='center'><a class='action_module' id='hid_".$course['code']."' href='#'><img src='$pathStyleSheets/images/action/scorm_32.png'></a></td>
												  </tr>";
                            }
                            ?>		

                        </tbody>
                    </table>
                    </br>
					<input type="hidden" name="hid_action_code" id="hid_action_code" />
                           <!-- <span id="course_reset"><button type="button" name="reset" /><?php echo get_lang("ResetFilter"); ?></button></span>-->
                    <span class="pull-right"><a href="#" id="course_export" ><?php echo get_lang("Export"); ?></a> / <a href="#" id="course_print"><?php echo get_lang("Print"); ?></a></span>
                    <div class="clearfix"></div>

                </div>

                <!--Finish section is courses -->























                <!--This section is modules-->

                <h3 id="moduleshead"><?php echo get_lang('Modules'); ?></h3>


                <div>
                    <div class="row-fluid">

                        <form>
                            <div class="span12 text-align-right pull-right">
                                <input id="module_search" name="module_search" type="text" class="input-medium search-query">  
                                <button id="modulebtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>
                                <span id="module_reset"><button type="button" name="reset" class="btn" /><?php echo get_lang("ResetFilter"); ?></button></span>
                            </div>
                            <br/><br/> 

                            <div class="span12 pull-right text-align-right">
                                <div class="text-align-right">
                                    <select name="select_courses" id="select_courses">
                                        <?php
                                        echo '<option value="0">' . get_lang('SelectCourses') . '</option>';
                                        foreach ($total_courses as $course) {
                                            echo '<option value="' . $course['code'] . '" >' . $course['title'] . '</option>';
                                        }
                                        echo "\n";
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </form>

                        <br/> 


                        <span><h4><?php echo get_lang('ModulesAverageValues'); ?></h4></span>
                        <span id="module_pages">
                            <?php
                            if (count($modules) > 0) {
                                //	echo "<div class='pagination'><ul id='course_pagination'>";
                                echo pagination($limit, $page, 'index.php?tab=modules&page=', count($modules), 'modules');
                                //	echo "</ul></div>";
                            }
                            ?>
                        </span>
						<div class="span11 chart_div" id="chartModuleContainer" style="height:300px;display:none;"></div>
                        <table class="responsive large-only table-striped" id="modules">
                            <thead>
                                <tr>
                                    <th><?php echo get_lang('Modules'); ?></th>
                                    <th><?php echo get_lang('InCourse'); ?></th>
                                    <th><?php echo get_lang('Time'); ?></th>
                                    <th><?php echo get_lang('Progress'); ?></th>
                                    <th><?php echo get_lang('Score'); ?></th>						
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        </br>

                         <!--   <span id="module_reset"><button type="button" name="reset" /><?php echo get_lang("ResetFilter"); ?></button></span> -->
                        <span class="pull-right"><a href="#" id="module_export"><?php echo get_lang("Export"); ?></a> / <a href="#" id="module_print"><?php echo get_lang("Print"); ?></a></span>
                        <div class="clearfix"></div>


                    </div>
                </div>

                <!--Finish section is modules-->









                <h3 id="quizhead"><?php echo get_lang('Quizzes'); ?></h3>							
                <div>

                    <form>
                        <div class="span12 pull-right text-align-right">
                            <input id="quiz_search" name="quiz_search" type="text" class="input-medium search-query">  
                            <button id="quizbtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>							
                            <span id="quiz_reset"><button type="button" name="reset" class="btn" /><?php echo get_lang("ResetFilter"); ?></button></span>	
                        </div> 
                        <br/><br/>

                        <div class="span12 pull-right text-align-right">
                            <select name="list_courses" id="list_courses">
                                <?php
                                echo '<option value="0">' . get_lang('SelectCourses') . '</option>';
                                foreach ($total_courses as $course) {
                                    echo '<option value="' . $course['code'] . '" >' . $course['title'] . '</option>';
                                }
                                echo "\n";
                                ?>
                            </select>
                            <select name="list_quiz" id="list_quiz">
                                <?php
                                echo '<option value="0">' . get_lang('SelectQuiz') . '</option>';
                                foreach ($quizzes as $quiz) {
                                    list($code, $quiz_id, $quiz_title) = split("@", $quiz);
                                    echo '<option value="' . $code . '@' . $quiz_id . '" >' . api_convert_encoding($quiz_title,api_get_system_encoding(),'UTF-8') . '</option>';
                                }
                                echo "\n";
                                ?>
                            </select>
                            <select id="select_type" id="select_type">
                                <option value="0"><?php echo get_lang("SelectType"); ?></option>
                                <option value="1"><?php echo get_lang("SelfLearning"); ?></option>
                                <option value="2"><?php echo get_lang("ExamMode"); ?></option>                                        
                            </select>
                            <select id="list_session" id="list_session">
                                <?php
                                echo '<option value="0">' . get_lang('SelectSession') . '</option>';
                                foreach ($sessions as $session) {
                                    echo '<option value="' . $session['id'] . '" >' . $session['name'] . '</option>';
                                }
                                echo "\n";
                                ?>
                            </select> 
                        </div>

                    </form>

                    <br/> 





                    <span><h4><?php echo get_lang('QuizzesAverageValues'); ?></h4></span>								
                    <span id="quiz_pages">
                        <?php
                        if (count($quizzes) > 0) {
                            //	echo "<div class='pagination'><ul id='course_pagination'>";
                            echo pagination($limit, $page, 'index.php?tab=quizzes&page=', count($quizzes), 'quizzes');
                            //	echo "</ul></div>";
                        }
                        ?>
                    </span>
					<div class="span11 chart_div" id="chartQuizContainer" style="height:300px;display:none;"></div>
                    <table class="responsive large-only table-striped" id="quizzes">
                        <thead>
                            <tr>
                                <th><?php echo get_lang('Quiz'); ?></th>
                                <th><?php echo get_lang('InCourse'); ?></th>
                                <th><?php echo get_lang('AverageScore'); ?></th>
                                <th><?php echo get_lang('Highest'); ?></th>
                                <th><?php echo get_lang('Lowest'); ?></th>
                                <th><?php echo get_lang('Participation'); ?></th>
                                <th><?php echo get_lang('AverageTime'); ?></th>
                                <th><?php echo get_lang('ListLearners'); ?></th>			
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>                                
                    </br>





<!-- <span id="quiz_reset"><button type="button" name="reset" /><?php echo get_lang("ResetFilter"); ?></button></span> -->
                    <span class="pull-right"><a href="#" id="quiz_export"><?php echo get_lang("Export"); ?></a> / <a href="#" id="quiz_print"><?php echo get_lang("Print"); ?></a></span>
                    <div class="clearfix"></div>
                </div>
				
				<?php
				if(api_get_setting('enable_course_scenario') == 'true'){
				?>
				<!-- Face to face tab -->
				<h3 id="facetofacehead"><?php echo get_lang('Facetoface'); ?></h3>
                <div>	
                    <div class="clearfix"></div>

                    <div class="row-fluid">

                        <form>
                            <div class="span12 pull-right text-align-right"> 
                                <input id="facetoface_search" name="facetoface_search" type="text" class="input-medium search-query">  
                                <button id="facetofacebtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>
                                <button id="facetoface_reset" type="button" name="reset" class="btn" /><?php echo get_lang("ResetFilter"); ?></button> 
                            </div>
                            <br/><br/>

                            <div class="span12 pull-right text-align-right">
							<?php
								if(api_is_allowed_to_edit()){
								?>
                                <select name="list_courses_ff" id="list_courses_ff">
                                <?php
                                echo '<option value="0">' . get_lang('SelectCourses') . '</option>';
                                foreach ($total_courses as $course) {
                                    echo '<option value="' . $course['code'] . '" >' . $course['title'] . '</option>';
                                }
                                echo "\n";
                                ?>
                            </select>
								<?php
								}
								?>
                                
                            </div>
                        </form>  
                    </div>                   



                    <span><h4><?php echo get_lang('FacetofaceAverageValues'); ?></h4></span>
                    <span id="facetoface_pages">
                        <?php
                        //if (count($total_courses) > 0) {
                            //	echo "<div class='pagination'><ul id='course_pagination'>";
                            //echo pagination($limit, $page, 'index.php?tab=facetoface&page=', count($total_courses), 'courses');
                            //	echo "</ul></div>";
                        //}
                        ?>
                    </span>
					<div class="span11 chart_print" id="chartContainer" style="height:300px;display:none;"></div>
                    <table name="facetoface" id="facetoface" class="responsive large-only table-striped">
                        <thead>
                            <tr>
                                <th><?php echo get_lang('Facetoface'); ?></th>
								<th><?php echo get_lang('Course'); ?></th>
                                <th><?php echo get_lang('MaxScore'); ?></th>
                                <th><?php echo get_lang('MinScore'); ?></th>                                
								<th class="print_invisible"><?php echo get_lang('Action'); ?></th>
                            </tr>
                        </thead>
                        
                    </table>
                    </br>
					
                           <!-- <span id="course_reset"><button type="button" name="reset" /><?php echo get_lang("ResetFilter"); ?></button></span>-->
                    <span class="pull-right"><a href="#" id="face2face_export" ><?php echo get_lang("Export"); ?></a> / <a href="#" id="face2face_print"><?php echo get_lang("Print"); ?></a></span>
                    <div class="clearfix"></div>

                </div>

                <!--Finish section is face to face -->
				<?php
				}				
				?>


                <h2 id="learnershead"><?php echo get_lang('Learners'); ?></h2>
                <div>								



                    <form>
                        <div class="span12 pull-right text-align-right">								
                            <input id="learner_search" name="learner_search" type="text" class="input-medium search-query" />  
                            <button id="learnerbtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>	
                            <span id="learner_reset"><button type="button" name="reset" class="btn" /><?php echo get_lang("ResetFilter"); ?></button></span>
                        </div>
                        <br/><br/>

                        <div class="span12 pull-right text-align-right">
                            <select name="course_list" id="course_list">
                                <?php
                                echo '<option value="0">' . get_lang('SelectCourses') . '</option>';
                                foreach ($total_courses as $course) {
                                    echo '<option value="' . $course['code'] . '" >' . $course['title'] . '</option>';
                                }
                                echo "\n";
                                ?>
                            </select>
                            <select name="session_list" id="session_list">
                                <?php
                                echo '<option value="0">' . get_lang('SelectSession') . '</option>';
                                foreach ($sessions as $session) {
                                    echo '<option value="' . $session['id'] . '" >' . $session['name'] . '</option>';
                                }
                                echo "\n";
                                ?>
                            </select>
                            <select name="learners_filter" id="learners_filter">
                                <option value="-1"><?php echo get_lang('SelectStatus'); ?></option>
                                <option value = "1" selected><?php echo get_lang('ActiveLearners'); ?></option>
                                <option value="0"><?php echo get_lang('InActiveLearners'); ?></option>                                        
                            </select>
                            <select name="quiz_ranking" id="quiz_ranking">
                                <option value="0"><?php echo get_lang('QuizzesRanking'); ?></option>
                                <option value="100">100-91%</option>
                                <option value="90">90-81%</option>
                                <option value="80">80-71%</option>                                       
                                <option value="70">70-61%</option>
                                <option value="60">60-51%</option>
                                <option value="50">50-41%</option>
                                <option value="40">40-31%</option>
                                <option value="30">30-21%</option>
                                <option value="20">20-11%</option>
                                <option value="10">10-0%</option>
                            </select>
                        </div>

                    </form>
                    <br/> 

                    
                    
                    
                    <span><h4><?php echo get_lang('LearnersAverageValues'); ?></h4></span>
                    <span id="learners_pages">
                        <?php
                        if (count($user_list) > 0) {
                            //	echo "<div class='pagination'><ul id='course_pagination'>";
                            echo pagination($limit, $page, 'index.php?tab=learners&page=', count($user_list), 'learners');
                            //	echo "</ul></div>";
                        }
                        ?>
                    </span>
					<div class="span11 chart_div" id="chartUserContainer" style="height:300px;display:none;"></div>
                    <table class="responsive large-only table-striped" id="learners">
                        <thead>
                            <tr>
                                <th><?php echo get_lang('LastName'); ?></th>
                                <th><?php echo get_lang('FirstName'); ?></th>
                                <th><?php echo get_lang('LatestConnection'); ?></th>
                                <th><?php echo get_lang('ModulesTime'); ?></th>
                                <th><?php echo get_lang('ModulesProgress'); ?></th>
                                <th><?php echo get_lang('ModulesScore'); ?></th>
                                <th><?php echo get_lang('QuizzesScore'); ?></th>
                                <th><?php echo get_lang('IndividualReporting'); ?></th>			
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            /* foreach ($limit_users as $user) {										
                              $last_connection_date = Tracking :: get_last_connection_date($user['user_id'], true);
                              if(empty($last_connection_date)) {
                              $last_connection_date = '/';
                              }
                              $time_spent = get_time_spent($user['user_id']);
                              $progress = get_student_progress($user['user_id']);
                              $score = get_student_score($user['user_id']);
                              $quiz_score = get_student_quiz_score($user['user_id']);
                              if($score == '- %'){
                              $score = 'n.a';
                              }
                              if($last_connection_date == '/') {
                              $time_spent = '/';
                              $progress = '/';
                              $score = '/';
                              $quiz_score = '/';
                              }
                              //$progress = Tracking :: get_avg_student_progress($info_user['user_id'], $course_code);
                              echo "<tr>
                              <td>".strtoupper($user['lastname'])."</td>
                              <td>".$user['firstname']."</td>
                              <td align='center'>".$last_connection_date."</td>
                              <td align='center'>".$time_spent."</td>
                              <td align='center'>".$progress."</td>
                              <td align='center'>".$score."</td>
                              <td align='center'>".$quiz_score."</td>
                              <td align='center'><a id='inreport' href='individual_reporting.php?user_id=".$user['user_id']."'>>></a></td>
                              </tr>";
                              } */
                            ?>                                

                        </tbody>
                    </table>
                    <br/>
<!--<span id="learner_reset"><button type="button" name="reset" /><?php echo get_lang("ResetFilter"); ?></button></span>-->
                    <span class="pull-right"><a href="#" id="learner_export"><?php echo get_lang("Export"); ?></a> / <a href="#" id="learner_print"><?php echo get_lang("Print"); ?></a></span>
                    <div class="clearfix"></div>
                </div>
            </div></div>

 
        <!--</div>-->
</div>        
</div>
<input type="hidden" name="current_tab" id="current_tab" value="courses" />


<script src="js/responsiveTabs.js"></script>
<script>
    $(document).ready(function() {
        RESPONSIVEUI.responsiveTabs();
        /*$(window).bind("load", function() {
         alert("Anjan all are loaded");
         if(window.location.hash) {
         window.location.href = "http://localhost/breetha/dok23/main/reporting/index.php";
         
         }
         });*/
    })

</script>
<?php
if(api_get_setting('enable_course_scenario') == 'true'){
?>
<script src="js/scripts_pro.js" type="text/javascript"></script>
<?php
}
else {
	?>
	<script src="js/scripts.js" type="text/javascript"></script>
	<?php
}
?>
<script src="js/stacktable.js" type="text/javascript"></script>
<script>
    $('.responsive').stacktable({myClass: 'stacktable small-only'});
</script>
<!-- End content -->
<?php
Display::display_responsive_reporting_footer();
