<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array ('index', 'admin');
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';


$this_section = SECTION_CAMPUS;
$tool_name = get_lang('SessionOverview');

// Database Table Definitions
$tbl_session						= Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_session_rel_class				= Database::get_main_table(TABLE_MAIN_SESSION_CLASS);
$tbl_session_rel_course				= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_course							= Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_user							= Database::get_main_table(TABLE_MAIN_USER);
$tbl_session_rel_user				= Database::get_main_table(TABLE_MAIN_SESSION_USER);
$tbl_session_rel_course_rel_user	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_class							= Database::get_main_table(TABLE_MAIN_CLASS);
$tbl_class_rel_user					= Database::get_main_table(TABLE_MAIN_CLASS_USER);
$tbl_session_category				= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);

$id_session= (isset($_REQUEST['id'])) ? trim( $_REQUEST['id'] ) : NULL;

if ( is_null( $id_session ) || $id_session == '' )
{
    header('location: ../../index.php');
} 
 
$id_session = (int)$_GET['id'];

$sql = 'SELECT name, nbr_courses, nbr_users, nbr_classes, DATE_FORMAT(date_start,"%d-%m-%Y") as date_start, DATE_FORMAT(date_end,"%d-%m-%Y") as date_end, lastname, firstname, username, session_admin_id, nb_days_access_before_beginning, nb_days_access_after_end, session_category_id, visibility
		FROM '.$tbl_session.'
		LEFT JOIN '.$tbl_user.'
			ON id_coach = user_id
		WHERE '.$tbl_session.'.id='.$id_session;

$rs = Database::query($sql, __FILE__, __LINE__);
$session = Database::store_result($rs);
$session = $session[0];

$sql = 'SELECT name FROM  '.$tbl_session_category.' WHERE id = "'.intval($session['session_category_id']).'"';
$rs = Database::query($sql, __FILE__, __LINE__);
$session_category = '';
if(Database::num_rows($rs)>0) {
	$rows_session_category = Database::store_result($rs);
	$rows_session_category = $rows_session_category[0];
	$session_category = $rows_session_category['name'];
}
/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
Display::display_header($tool_name);

echo '<div id="content">';
api_display_tool_title($tool_name);
?>
<br/>
<div class="section">
<div class="sectiontitle"><?php echo $session['name'] ?></div>	
<div class="sectionvalue">
<table class="data_table" width="100%" cellpadding="3">
<tr>
	<td width="15%" align="right"><?php echo get_lang('SessionName');?> :</td>
	<td width="85%"><?php echo $session['name'] ?></td>
</tr>
<tr>
	<td width="15%" align="right"><?php echo get_lang('GeneralCoach'); ?> :</td>
	<td width="85%"><?php echo api_get_person_name($session['firstname'], $session['lastname']).' ('.$session['username'].')' ?></td>
</tr>
<?php if(!empty($session_category)): ?>
<tr>
	<td width="15%" align="right"><?php echo get_lang('SessionCategory') ?></td>
	<td width="85%"><?php echo $session_category;  ?></td>
</tr>
<?php endif; ?>
<tr>
	<td width="15%" align="right"><?php echo get_lang('Agenda'); ?> :</td>
	<td width="85%">
	<?php
		if($session['date_start']=='00-00-0000')
			echo get_lang('NoTimeLimits');
		else
			echo get_lang('From').' '.$session['date_start'].' '.get_lang('To').' '.$session['date_end'];
		 ?>
	</td>
</tr>
<tr>
	<td>
		<?php echo api_ucfirst(get_lang('SessionVisibility')) ?> :
	</td>
	<td>
		<?php if ($session['visibility']==1) echo get_lang('ReadOnly'); elseif($session['visibility']==2) echo get_lang('Visible');elseif($session['visibility']==3) echo api_ucfirst(get_lang('Invisible'))  ?>
	</td>
</tr>
</table>
</div>
</div>
<!--List of courses -->
<table style="width:100%" id="table_question_list" class="data_table data_table_exercise">
    <tr>
  <td colspan="4"><div class="row"><div class="form_header"><?php echo get_lang('Courses'); ?>
  	</div></div></td>  
</tr>
  <tr>
  <th width="25%"><?php echo get_lang('CourseTitle'); ?></th>
  <th width="15%"><?php echo get_lang('CourseCoach'); ?></th>
  <th width="8%"><?php echo get_lang('Volume'); ?></th>
  <th width="10%"><?php echo get_lang('Users'); ?></th>
</tr>
</table>

 <div id="contentWrap"><div id="contentLeft"><ul id="categories" class="dragdrop nobullets  ui-sortable">
                          
             <?php
             
             // select the courses
            $sql = "SELECT code,title,visual_code, nbr_users
                            FROM $tbl_course,$tbl_session_rel_course			
                            WHERE course_code = code			
                            AND	id_session='$id_session'
                            ORDER BY position";

            $result=Database::query($sql,__FILE__,__LINE__);
            $courses=Database::store_result($result);
             
            foreach ($courses as $course) {
		$sql = "SELECT DISTINCT(sru.id_user) FROM $tbl_session_rel_user sru, $tbl_session_rel_course_rel_user srcru
				WHERE  srcru.id_session = sru.id_session AND srcru.course_code = '".Database::escape_string($course['code'])."'
				AND srcru.id_session = '".intval($id_session)."'";

		$rs = Database::query($sql, __FILE__, __LINE__);
		$num_users = Database::num_rows($rs);
		$course['nbr_users'] = $num_users;
	//	$course['nbr_users'] = Database::result($rs,0,0);

		//course properties
		$tbl_session_course	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
		$sql_property = "SELECT * FROM $tbl_session_course WHERE id_session = ".intval($id_session). " AND course_code = '".Database::escape_string($course['code'])."'";
		$res_property = Database::query($sql_property,__FILE__,__LINE__);
		$row_property = Database::fetch_array($res_property);
		
		$schedule = $row_property['repeats_on'];
		if($schedule == 'M')$schedule_day = 'Monday';
		if($schedule == 'T')$schedule_day = 'Tuesday';
		if($schedule == 'W')$schedule_day = 'Wednesday';
		if($schedule == 'TH')$schedule_day = 'Thursday';
		if($schedule == 'F')$schedule_day = 'Friday';
		if($schedule == 'ST')$schedule_day = 'Saturday';
		if($schedule == 'S')$schedule_day = 'Sunday';
		if($schedule == 'NULL')$schedule_day = '';

		list($from_hours, $from_mins) = split(':', $row_property['time_from']);
		list($to_hours, $to_mins) = split(':', $row_property['time_to']);
		
		if($from_hours > 6 && $from_hours < 13){$ampm = 'AM';}else{$ampm = 'PM';}		

		$schedule = $schedule_day.'&nbsp;&nbsp;'.$from_hours.'-'.$to_hours.'&nbsp;'.$ampm;

		if($from_hours == '00'){
			$schedule = '';
		}

		if($row_property['repeats'] != 'NULL'){
			$frequency = $row_property['repeats'];
		}
		else {
			$frequency = '';
		}

		// Get coachs of the courses in session
		$sql = "SELECT user.lastname,user.firstname,user.username FROM $tbl_session_rel_course_rel_user session_rcru, $tbl_user user
				WHERE session_rcru.id_user = user.user_id AND session_rcru.id_session = '".intval($id_session)."' AND session_rcru.course_code ='".Database::escape_string($course['code'])."' AND session_rcru.status=2";
		$rs = Database::query($sql,__FILE__,__LINE__);

		$coachs = array();
		if (Database::num_rows($rs) > 0) {
			while($info_coach = Database::fetch_array($rs)) {
				$coachs[] = api_get_person_name($info_coach['firstname'], $info_coach['lastname']).' ('.$info_coach['username'].')';
			}
		} else {
			$coach = get_lang('None');
		}


		if (count($coachs) > 0) {
			$coach = implode('<br />',$coachs);
		} else {
			$coach = get_lang('None');
		}

		$orig_param = '&origin=resume_session';
		//hide_course_breadcrumb the parameter has been added to hide the name of the course, that appeared in the default $interbreadcrumb
		if($i%2 == 0){
			$class = "row_odd";
		}
		else {
			$class = "row_even";
		}
               
                echo '<li id="recordsArray_'.$id_session.'|'.$course['code'].'" class="category" style="opacity: 1;">';
                echo '<div>';                
                echo '<table width="100%" class="data_table">	
		<tr class="'.$class.'">                  
                    <td width="25%">'.$course['title'].' ('.$course['visual_code'].')</td>
                    <td width="15%">'.$coach.'</td>
                    <td width="8%">'.$row_property['hours'].'</td>
                    <td width="10%">'.$course['nbr_users'].'</td>
		</tr></table>';
		$i++;
                
                echo '</div>';
                echo '</li>';
                
                
            }
            
             ?>
   
<br />
<?php
// End content
echo '</div>';

// footer
Display :: display_footer();
?>
