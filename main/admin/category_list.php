<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array ('index', 'admin');

// resetting the course id
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';

$tbl_session             = Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_course      = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_course              = Database::get_main_table(TABLE_MAIN_COURSE);
$topic_table 		 = Database :: get_main_table(TABLE_MAIN_TOPIC);
$tbl_session_category 	 = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
$session_rel_category 	 = Database :: get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);

// Clear order process, unset session used for this order
if (!isset($_REQUEST['prev'])) {
    SessionManager::clear_catalogue_order_process();
}

$error = array();
if (isset($_POST['register'])) {

    $cat_id = intval($_POST['id']);

    // validation
    $rs = Database::query("SELECT distinct(session_set), session_range FROM session_rel_category WHERE session_set <> '' AND category_id = ".$cat_id." ORDER BY session_set");
    if (Database::num_rows($rs) > 0) {
        while ($row = Database::fetch_array($rs)) {
            $session_range = $row['session_range'];
            $r_max = $r_min = 0;
            if (!empty($session_range)) {
                list($r_max, $r_min) = explode('|', $session_range);
            }
            $session_set   = $row['session_set'];
            if (isset($_POST['opt_courses'][$session_set])) {

                if ($_POST['opt_courses'][$session_set] == 'checkbox') {
                    if (!isset($_POST['opt_course_check'][$session_set]) || count($_POST['opt_course_check'][$session_set]) != $r_max) {
                        $error[] = get_lang('YouMustSelectOptionCoursesInSessionSet').' '.$session_set;
                    }
                } else if ($_POST['opt_courses'][$session_set] == 'radio') {
                    if (!isset($_POST['opt_course_radio'][$session_set])) {
                        $error[] = get_lang('YouMustSelectOptionCoursesInSessionSet').' '.$session_set;
                    }
                }

            }
        }
    }

    // if all ok redirect to step2
    if (empty($error)) {
        // get all selected courses
        $courses = array();
        $sessions = array();
        $cours_rel_session = array();
        // compository courses
        if (isset($_POST['comp_courses']) && !empty($_POST['comp_courses'])) {
            foreach ($_POST['comp_courses'] as $comp_course) {
                list($course, $session) = explode('@@', $comp_course);
                $courses[] = $course;
                $sessions[] = $session;
                $cours_rel_session[] = $comp_course;
            }
        }
        // optional courses in radio
        if (isset($_POST['opt_course_radio']) && !empty($_POST['opt_course_radio'])) {
            foreach ($_POST['opt_course_radio'] as $opt_course) {
                list($course, $session) = explode('@@', $opt_course);
                $courses[] = $course;
                $sessions[] = $session;
                $cours_rel_session[] = $opt_course;
            }
        }
        // optional courses in checkboxes
        if (isset($_POST['opt_course_check']) && !empty($_POST['opt_course_check'])) {
            foreach ($_POST['opt_course_check'] as $courses_check) {
                foreach ($courses_check as $chk_course) {
                    list($course, $session) = explode('@@', $chk_course);
                    $courses[] = $course;
                    $sessions[] = $session;
                    $cours_rel_session[] = $chk_course;
                }
            }
        }

        // set selected courses
        if (!empty($courses)) {
            $_SESSION['selected_courses'] = $courses;
        }
        // set selected sessions
        if (!empty($sessions)) {
            $_SESSION['selected_sessions'] = array_unique($sessions);
        }
        // set selected sessions
        if (!empty($cours_rel_session)) {
            $_SESSION['cours_rel_session'] = array_unique($cours_rel_session);
        }

        if (api_get_user_id()) {
            header('Location: '.api_get_path(WEB_CODE_PATH).'admin/feedback.php?id='.$cat_id.'&next=4');
        } else {
            header('Location: '.api_get_path(WEB_CODE_PATH).'admin/registration.php?id='.$cat_id.'&next=2');
        }
        exit;
    }
}

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/

$tool_name = get_lang("SystemAnnouncements");

/*$htmlHeadXtra [] = '
<!-- stats -->
    <script type="text/javascript">
    <!--
    xtnv = document; //affiliation frameset : document, parent.document ou top.document
    xtsd = "http://logi3";
    xtsite = "257797";
    xtn2 = "3"; //utiliser le numero du niveau 2 dans lequel vous souhaitez ranger la page
    xtpage = "formations-DF::inscription"; //placer un libell� de page pour les rapports Xiti
    roimt = ""; //valeur du panier pour ROI (uniquement pour les pages d�finies en transformation)
    roitest = false; //� true uniquement si vous souhaitez effectuer des tests avant mise en ligne
    visiteciblee = false; //� true pour les pages qui caract�risent une visite cibl�e
    xtprm = ""; //Param�tres suppl�mentaires (optionnel)
    //-->
    </script>
    <script type="text/javascript" src="http://www.formation-publique.fr/xtroi.js"></script>
    <noscript>
    <img width="1" alt="" height="1" src="http://logi3.xiti.com/hit.xiti?s=257797&s2=3&p=formations-DF::matiere-30065778648&roimt=&roivc=&" >
    </noscript>
<!-- fin stats -->

';*/
$this_section = SECTION_PLATFORM_ADMIN;
Display::display_header($tool_name);
//echo '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/dokeos/jquery-ui-1.8.6.custom.css" type="text/css" />';

echo '<style type="text/css">
		div.row {
			width: 900px;
		}
		div.row div.label{
			width: 375px;
		}
		div.row div.formw{
			width: 500px;
		}

		</style>';

//$sql = "SELECT prg.*,count(DISTINCT(prs.session_set)) AS no_set,tp.topic AS topic FROM $programme_table prg,$programme_rel_session prs,$topic_table tp WHERE prg.topic = tp.id AND prg.id = prs.programme_id AND prg.id = ".$_REQUEST['id'];
$sql = "SELECT sc.*,count(DISTINCT(src.session_set)) AS no_set, tp.id as topic_id, tp.topic AS topic FROM $tbl_session_category sc,$session_rel_category src,$topic_table tp WHERE session_set <> '' AND sc.topic = tp.id AND sc.id = src.category_id AND sc.id = ".intval($_REQUEST['id']);
$rs = Database::query($sql,__FILE__,__LINE__);
$row = Database::fetch_array($rs);

list($Year,$Month,$Day) = split('-',$row['date_start']);
$start_date = mktime(12,0,0,$Month,$Day,$Year);
$start_date = date("F jS, Y", $start_date);
list($Year,$Month,$Day) = split('-',$row['date_end']);
$end_date = mktime(12,0,0,$Month,$Day,$Year);
$end_date = date("F jS, Y", $end_date);

if (isset($_GET['res_cheque'])) {
    echo '<div id="content">';
    if (intval($_GET['res_cheque']) == 1) {

        echo '<div class="normal-message">'.get_lang('YourDataHasBeenSavedTheAdministratorWillActivateYourAccount').'</div>';
    }
    else {
        echo '<div class="error-message">'.get_lang('YourOperationHasFailed').'</div>';
    }
    echo '</div>';
    exit;
}

if (api_get_user_id()) {
    echo '<div class="actions">';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/session_category_payments"">'.Display::return_icon('pixel.gif', get_lang("Catalogue"), array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('Catalogue').'</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/installment_payment.php">'.Display::return_icon('pixel.gif', get_lang("InstallmentPaymentInfo"), array('class' => 'toolactionplaceholdericon toolactionother')).get_lang('InstallmentPaymentInfo').'</a>&nbsp;';
    echo '</div>';
}
echo '<div id="content">';


// Steps breadcrumbs
SessionManager::display_steps_breadcrumbs();

if (!empty($error)) {
    echo '<center><span style="color: red;">';
    foreach ($error as $err) {
        echo $err.'<br />';
    }
    echo '</span></center>';
}
echo '<div class="row"><div class="form_header register-payment-steps-name"><h2>'.$row['name'].'</h2></div></div>';

$topic     = SessionManager::get_topic_info($row['topic_id']);
$catalogue = SessionManager::get_catalogue_info($topic['catalogue_id']);
if(!empty($catalogue['options_selection'])){
    echo '<div class="quiz_content_actions" style="margin-bottom:20px;padding:4px;">'.$catalogue['options_selection'].'</div>';
}

//echo '<div class="register-payment-steps">1/6 '.get_lang('SelectioningOptions').'</div><br />';

//echo '<div class="row"><div class="form_header">'.get_lang('Programme').'</div></div><br/>';
$z = 1;
$j = 0;
for($i=1;$i<=$row['no_set'];$i++){

//$sql_course = "SELECT cr.code, cr.title, cr.db_name FROM programme prg, programme_rel_session prs, session_rel_course src, course cr WHERE prg.id = prs.programme_id  AND prs.session_id = src.id_session AND cr.code = src.course_code AND prs.session_set = ".$i." AND prg.id =".$_REQUEST['id'];
$sql_course = "SELECT cr.code, cr.title, cr.db_name, session_range, session_set, src.session_id FROM $tbl_session_category sc, $session_rel_category src, $tbl_session_course tsc, course cr WHERE sc.id = src.category_id  AND src.session_id = tsc.id_session AND cr.code = tsc.course_code AND src.session_set = ".$i." AND sc.id =".$_REQUEST['id']." ORDER BY tsc.position";
$rs_course = Database::query($sql_course,__FILE__,__LINE__);

$sql_sessionset = "SELECT session_set_name FROM $session_rel_category WHERE session_set = ".$i." AND category_id = ".$_REQUEST['id'];
$rs_sessionset = Database::query($sql_sessionset,__FILE__,__LINE__);
while($row_sessionset = Database::fetch_array($rs_sessionset)){
	$session_set_name = $row_sessionset['session_set_name'];
}

$count_sess_set = 0;
$rs_sess_set = Database::query("SELECT count(*) FROM $tbl_session_category sc, $session_rel_category src, $tbl_session_course tsc, course cr WHERE sc.id = src.category_id  AND src.session_id = tsc.id_session AND cr.code = tsc.course_code AND src.session_set = ".$i." AND sc.id =".$_REQUEST['id']);
if (Database::num_rows($rs_sess_set)) {
    $row_sess_set = Database::fetch_row($rs_sess_set);
    $count_sess_set = $row_sess_set[0];
}
echo '<form method="post" name="frm" action="'.api_get_path(WEB_CODE_PATH).'admin/category_list.php?next=1">';
echo '<input type="hidden" name="id" value="'.intval($_REQUEST['id']).'"/>';
echo '<input type="hidden" name="prev" value="1"/>';
echo '<div class="section">';
if(empty($session_set_name)){
echo '<div class="sectiontitle">'.get_lang('CourseSet').' '.$i.'</div>';
}
else {
echo '<div class="sectiontitle">'.$session_set_name.'</div>';
}
echo '<div class="sectionvalue" id="sectionvalue-'.$i.'">';




while($row_course = Database::fetch_array($rs_course)) {

    $session_range = $row_course['session_range'];
    $rs_max = $rs_min = 0;
    if (!empty($session_range)) {
        list($rs_max, $rs_min) = explode('|', $session_range);
    }

    if ($rs_max == $rs_min && $rs_min == $count_sess_set) {
        echo '<input type="hidden" name="comp_courses[]" value="'.$row_course['code'].'@@'.$row_course['session_id'].'" />';
        echo '<div><span style="font-size:35px">.</span>&nbsp;<b>'.$row_course[1].'</b></div>';
    } else if ($rs_max == $rs_min && $rs_min != $count_sess_set) {
        echo '<input type="hidden" name="opt_courses['.$row_course['session_set'].']" value="radio" />';
        echo '<div><input type="radio" name="opt_course_radio['.$i.']" id="opt-course-radio-'.$z.'" class="opt-course-radio" value="'.$row_course['code'].'@@'.$row_course['session_id'].'" '.((!empty($_POST['opt_course_radio'][$i]) && $_POST['opt_course_radio'][$i] == $row_course['code'].'@@'.$row_course['session_id']) || (isset($_SESSION['selected_courses']) && in_array($row_course['code'].'@@'.$row_course['session_id'], $_SESSION['cours_rel_session']))?' checked=""':'').'>&nbsp;<b>'.$row_course[1].'</b></div>';
        $j++;
    } else {
        echo '<input type="hidden" name="opt_courses['.$row_course['session_set'].']" value="checkbox" />';
        echo '<div><input type="checkbox" name="opt_course_check['.$i.'][]" id="opt_course_check_'.$z.'" class="opt_course_check" '.(!empty($_POST['opt_course_radio'][$i]) || (isset($_SESSION['selected_courses']) && in_array($row_course['code'], $_SESSION['selected_courses']))?' checked="checked"':'').' value="'.$row_course['code'].'@@'.$row_course['session_id'].'" >&nbsp;<b>'.$row_course[1].'</b></div>';
        $j++;
    }

    echo '<script type="text/javascript">
    $(document).ready(function() {
            $("a#full_course'.$z.'").click(function() {
            $("#somediv'.$z.'").dialog({
                modal: true,
                title: "'.$row_course[1].'",width: "600px"});
                return false;
            });
    });
    </script>';

    $sql_desc = "SELECT content FROM ".$row_course[2].".course_description WHERE description_type = 1";
    $rs_desc = Database::query($sql_desc,__FILE__,__LINE__);
    $row_desc = Database::fetch_array($rs_desc);
    $course_desc = strip_tags($row_desc['content']);
    $course_desc = str_replace('/**/','',$course_desc);
    $coursedesc_len = strlen($course_desc);
    if($coursedesc_len > 300){
            $course_desc = substr($course_desc,1,300);
    }
    if($coursedesc_len <> 0){
    echo '<div>'.$course_desc.'... &nbsp;&nbsp;';
            if($coursedesc_len > 300){
                    echo '<b><a href="#" id="full_course'.$z.'">'.get_lang('LearnMore').'&nbsp;></a></b>';
            }
            echo '</div>';
    }
    echo '<br/>';
    $course_fulldescription = get_complete_course_description($row_course[2]);
    echo '<div id="somediv'.$z.'" style="display:none;">'.$course_fulldescription.'</div>';

    $z++;
}
echo '</div></div>';
}
echo '<button type="submit" class="save" name="register" value="Register" >'.(api_get_user_id()?get_lang('Ok'):get_lang('Register')).'</button>';
echo '</div>';
echo '</form>';



function get_complete_course_description($db_name){
	$sql = "SELECT * FROM ".$db_name.".course_description WHERE session_id = 0 ORDER BY description_type ";
	$result = Database::query($sql, __FILE__, __LINE__);
	$descriptions = array();
	while ($description = Database::fetch_object($result)) {
		$descriptions[$description->description_type] = $description;
		//reload titles to ensure we have the last version (after edition)
		$default_description_titles[$description->description_type] = $description->title;
	}
	$return = '';
	if (isset($descriptions) && count($descriptions) > 0) {
			foreach ($descriptions as $id => $description) {

				$return .= '<div class="section_white">';
				$return .= '<div class="sectiontitle">'.$description->title.'</div>';
				$return .= '<div class="sectioncontent">';
				$return .= text_filter($description->content);
				$return .= '</div>';
				$return .= '</div>';
			}
	}
	return $return;
}

Display::display_footer();

