<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Bart Mollet
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = 'admin';
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

// setting the section (for the tabs)
$this_section=SECTION_PLATFORM_ADMIN;
api_protect_admin_script(true);
$tool_name = get_lang('SessionOverview');
$interbreadcrumb[]=array('url' => 'index.php','name' => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => 'session_list.php','name' => get_lang('SessionList'));

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

$id_session = (int)$_GET['id_session'];

$sql = 'SELECT name, nbr_courses, nbr_users, nbr_classes, DATE_FORMAT(date_start,"%d-%m-%Y") as date_start, DATE_FORMAT(date_end,"%d-%m-%Y") as date_end, lastname, firstname, username, session_admin_id, nb_days_access_before_beginning, nb_days_access_after_end, session_category_id, visibility
		FROM '.$tbl_session.'
		LEFT JOIN '.$tbl_user.'
			ON id_coach = user_id
		WHERE '.$tbl_session.'.id='.$id_session;

$rs = Database::query($sql, __FILE__, __LINE__);
$session = Database::store_result($rs);
$session = $session[0];

if(!api_is_platform_admin() && $session['session_admin_id']!=$_user['user_id'])
{
	api_not_allowed(true);
}

$sql = 'SELECT name FROM  '.$tbl_session_category.' WHERE id = "'.intval($session['session_category_id']).'"';
$rs = Database::query($sql, __FILE__, __LINE__);
$session_category = '';
if(Database::num_rows($rs)>0) {
	$rows_session_category = Database::store_result($rs);
	$rows_session_category = $rows_session_category[0];
	$session_category = $rows_session_category['name'];
}

if($_GET['action'] == 'delete')
{
	$idChecked = $_GET['idChecked'];
	if(is_array($idChecked)) {
		$my_temp = array();
		foreach ($idChecked as $id){
			$my_temp[]= Database::escape_string($id);// forcing the escape_string
		}
		$idChecked = $my_temp;

		$idChecked="'".implode("','",$idChecked)."'";

		Database::query("DELETE FROM $tbl_session_rel_course WHERE id_session='$id_session' AND course_code IN($idChecked)",__FILE__,__LINE__);

		$nbr_affected_rows=Database::affected_rows();

		Database::query("DELETE FROM $tbl_session_rel_course_rel_user WHERE id_session='$id_session' AND course_code IN($idChecked)",__FILE__,__LINE__);

		Database::query("UPDATE $tbl_session SET nbr_courses=nbr_courses-$nbr_affected_rows WHERE id='$id_session'",__FILE__,__LINE__);
	}

	if(!empty($_GET['class'])){
		Database::query("DELETE FROM $tbl_session_rel_class WHERE session_id='$id_session' AND class_id=".Database::escape_string($_GET['class']),__FILE__,__LINE__);

		$nbr_affected_rows=Database::affected_rows();

		Database::query("UPDATE $tbl_session SET nbr_classes=nbr_classes-$nbr_affected_rows WHERE id='$id_session'",__FILE__,__LINE__);

	}

	if (!empty($_GET['user'])) {
		Database::query("DELETE FROM $tbl_session_rel_user WHERE id_session='$id_session' AND id_user=".intval($_GET['user']),__FILE__,__LINE__);
		$nbr_affected_rows=Database::affected_rows();
		Database::query("UPDATE $tbl_session SET nbr_users=nbr_users-$nbr_affected_rows WHERE id='$id_session'",__FILE__,__LINE__);

		Database::query("DELETE FROM $tbl_session_rel_course_rel_user WHERE id_session='$id_session' AND id_user=".intval($_GET['user']),__FILE__,__LINE__);
		$nbr_affected_rows=Database::affected_rows();
		Database::query("UPDATE $tbl_session_rel_course SET nbr_users=nbr_users-$nbr_affected_rows WHERE id_session='$id_session'",__FILE__,__LINE__);
	}
}


$htmlHeadXtra[] ='<script type="text/javascript">
$(document).ready(function() { 
	$(function() {
		$("#contentLeft ul").sortable({ 
                opacity: 0.6, 
                cursor: "move", 
                handle: $(".ddrag"),
                update: function() {
                    var order = $(this).sortable("serialize") + "&amp;action=changeCourseSessionOrder";
                    var record = order.split("&");
                    var recordlen = record.length;
                    var disparr = new Array();
                    for (var i=0;i<(recordlen-1);i++) {
                        var recordval = record[i].split("=");
                        disparr[i] = recordval[1];			 
                    }
                    $.ajax({
                    type: "GET",
                    url: "'.api_get_path(WEB_AJAX_PATH).'courses_session.ajax.php?action=changeCourseSessionOrder&disporder="+disparr,
                    success: function(msg){}
		})			
		}
		});
	});
//Load User Information
    $(".user_info").click(function () {
        var myuser_id = $(this).attr("id");
        var user_info_id = myuser_id.split("user_id_");
        my_user_id = user_info_id[1];
        $.ajax({
            url: "'.api_get_path(WEB_AJAX_PATH).'user_manager.ajax.php?a=get_user_info&user_id="+my_user_id,
            success: function(data){
               var dialog_div = $("<div id=\'html_user_info\'></div>");
                dialog_div.html(data);
                dialog_div.dialog({
                modal: true,
                title: "'.get_lang('UserInfo').'",
                width: 640,
                height : 240,
                resizable:false
                });
            }
        });
    });
});
</script> ';


Display::display_header($tool_name);

echo '<div class="actions">';
//echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/catalogue_management.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('Catalogue') . '</a>';
//echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/topic_list.php">' . Display :: return_icon('pixel.gif', get_lang('Topics'),array('class' => 'toolactionplaceholdericon toolactiontopic')) . get_lang('Topics') . '</a>';
//echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/programme_list.php">' . Display :: return_icon('pixel.gif', get_lang('Programmes'),array('class' => 'toolactionplaceholdericon toolactionprogramme')) . get_lang('Programmes') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'),array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('Sessions') . '</a>';
echo '</div>';

// Start content page
echo '<div id="content">';

if (!empty($_GET['warn'])) {
    Display::display_warning_message(urldecode($_GET['warn']));
}

api_display_tool_title($tool_name);
?>
<!-- General properties -->
<br/>
<div class="section">
<div class="sectiontitle"><?php echo get_lang('SessionSettings'); ?>&nbsp;&nbsp;<a href="session_edit.php?page=resume_session.php&amp;id=<?php echo $id_session; ?>"><?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?></a></div>	
<div class="sectionvalue">
<table class="data_table" width="100%" cellpadding="3">
<!--<tr>
  <th colspan="2"><?php echo get_lang('GeneralProperties'); ?>
  	<a href="session_edit.php?page=resume_session.php&amp;id=<?php echo $id_session; ?>"><?php Display::display_icon('pixel.gif', get_lang('Edit'),array('class'=>'actionplaceholdericon actionedit')); ?></a></th>
  </th>
</tr>-->
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
<!--<tr>
	<td><?php echo get_lang('Date'); ?> :</td>
	<td>
	<?php
		if($session['date_start']=='00-00-0000')
			echo get_lang('NoTimeLimits');
		else
			echo get_lang('From').' '.$session['date_start'].' '.get_lang('To').' '.$session['date_end'];
		 ?>
	</td>
</tr>-->

<!-- show nb_days_before and nb_days_after only if they are different from 0 -->
<!--<tr>
	<td>
		<?php echo api_ucfirst(get_lang('DaysBefore')) ?> :
	</td>
	<td>
		<?php echo intval($session['nb_days_access_before_beginning']) ?>
	</td>
</tr>
<tr>
	<td>
		<?php echo api_ucfirst(get_lang('DaysAfter')) ?> :
	</td>
	<td>
		<?php echo intval($session['nb_days_access_after_end']) ?>
	</td>
</tr>

<tr>
	<td>
		<?php echo api_ucfirst(get_lang('SessionVisibility')) ?> :
	</td>
	<td>
		<?php if ($session['visibility']==1) echo get_lang('ReadOnly'); elseif($session['visibility']==2) echo get_lang('Visible');elseif($session['visibility']==3) echo api_ucfirst(get_lang('Invisible'))  ?>
	</td>
</tr>-->

</table>
</div>
	</div>

<!--List of courses -->
<table style="width:100%" id="table_question_list" class="data_table data_table_exercise">
    <tr>
  <td colspan="5">
      <div class="row">
          <div class="form_header">
              <?php echo get_lang('Courses'); ?>
              <a href="add_courses_to_session.php?page=resume_session.php&amp;id_session=<?php echo $id_session; ?>"><?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?></a>
          </div>
      </div>
  </td>  
</tr>
  <tr>
  <th width="8%">Move</th>
  <th width="25%"><?php echo get_lang('CourseTitle'); ?></th>
  <th width="15%"><?php echo get_lang('CourseCoach'); ?></th>
  <th width="10%"><?php echo get_lang('Users'); ?></th>
  <th width="10%"><?php echo get_lang('Actions'); ?></th>
</tr>
</table>

 <div id="contentWrap">
     <div id="contentLeft">
         <ul id="categories" class="dragdrop nobullets  ui-sortable">                          
             <?php             
            // select the courses
            $sql = "SELECT code,title,visual_code, nbr_users, directory FROM $tbl_course,$tbl_session_rel_course WHERE course_code = code AND id_session='$id_session' ORDER BY position";

            $result=Database::query($sql,__FILE__,__LINE__);
            $courses=Database::store_result($result);
             
            foreach ($courses as $course) {
                //select the number of users
                $sql = "SELECT DISTINCT(sru.id_user) FROM $tbl_session_rel_user sru, $tbl_session_rel_course_rel_user srcru
                                WHERE  srcru.id_session = sru.id_session AND srcru.course_code = '".Database::escape_string($course['code'])."'
                                AND srcru.id_session = '".intval($id_session)."'";

		$rs = Database::query($sql, __FILE__, __LINE__);
		$num_users = Database::num_rows($rs);
		$course['nbr_users'] = $num_users;

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

		$orig_param = '&amp;origin=resume_session';
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
                    <td align="center" class="ddrag" width="8%" style="cursor:pointer">'.Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actiondragdrop')).'</td>
                    <td width="25%"><a href="'.api_get_path(WEB_COURSE_PATH).$course['directory'].'/?id_session='.$id_session.'">'.$course['title'].' ('.$course['visual_code'].')</a></td>
                    <td width="15%">'.$coach.'</td>                    
                    <td width="10%">'.$course['nbr_users'].'</td>
                    <td width="10%">';
                            //<a href="../tracking/courseLog.php?id_session='.$id_session.'&amp;cidReq='.$course['code'].$orig_param.'&hide_course_breadcrumb=1">'.Display::return_icon('pixel.gif', get_lang('Tracking'), array('class' => 'actionplaceholdericon actionstatistics')).'</a>&nbsp;
                      echo '<a href="session_course_edit.php?id_session='.$id_session.'&page=resume_session.php&amp;course_code='.$course['code'].''.$orig_param.'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a>';
                    //echo '  <a href="'.api_get_self().'?id_session='.$id_session.'&amp;action=delete&amp;idChecked[]='.$course['code'].'" onclick="javascript:if(!confirm(\''.get_lang('ConfirmYourChoice').'\')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>
                    $link = api_get_self().'?id_session='.$id_session.'&amp;action=delete&amp;idChecked[]='.$course['code'];
                    $title = get_lang("ConfirmationDialog");
                    $text = get_lang("ConfirmYourChoice");
                      echo '  <a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>
                    </td>
		</tr></table>';
		$i++;
                
                echo '</div>';
                echo '</li>';
                
                
            }
            echo '</ul>';
             ?>
             
             
<br />
<!--List of participants -->
<table id="table_question_list" class="data_table data_table_exercise" width="100%">
    <tr><td colspan="7">

<div class="row">
    <div class="form_header">
        <?php echo get_lang('Participants'); ?>
  	<a href="add_users_to_session.php?page=resume_session.php&amp;id_session=<?php echo $id_session; ?>"><?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?></a>
    </div>
</div>

  </td></tr>
  <tr>
  <th width="5%"><?php echo get_lang('Photo'); ?></th>
  <th width="10%"><?php echo get_lang('Code'); ?></th>
  <th width="20%"><?php echo get_lang('FirstName'); ?></th>
  <th width="15%"><?php echo get_lang('LastName'); ?></th>
  <th width="15%"><?php echo get_lang('Login'); ?></th>
  <th width="20%"><?php echo get_lang('Email'); ?></th>
  <th width="10%"><?php echo get_lang('Actions'); ?></th>
</tr>
<?php
if($session['nbr_users']==0){
	echo '
		<tr>
			<td colspan="2">'.get_lang('NoUsersForThisSession').'</td>
		</tr>';
} else {

	// classe development, obsolete for the moment
	$order_clause = api_sort_by_first_name() ? ' ORDER BY firstname, lastname' : ' ORDER BY lastname, firstname';
	$sql = 'SELECT '.$tbl_user.'.user_id, lastname, firstname, username, official_code, email
			FROM '.$tbl_user.'
			INNER JOIN '.$tbl_session_rel_user.'
				ON '.$tbl_user.'.user_id = '.$tbl_session_rel_user.'.id_user
				AND '.$tbl_session_rel_user.'.id_session = '.$id_session.$order_clause;

	$result=Database::query($sql,__FILE__,__LINE__);
	$users=Database::store_result($result);
	$orig_param = '&amp;origin=resume_session&amp;id_session='.$id_session; // change breadcrumb in destination page
        $i = 0;
	foreach($users as $user){
		$image_path = UserManager::get_user_picture_path_by_id($user['user_id'], 'web', false, true);
		$user_profile = UserManager::get_picture_user($user['user_id'], $image_path['file'], 22, 'small_', ' width="22" height="22" ');
		if (!api_is_anonymous()) {
			//$photo = '<center><a href="'.api_get_path(WEB_PATH).'whoisonline.php?origin=user_list&amp;id='.$user['user_id'].'" title="'.get_lang('Info').'"  ><img src="'.$user_profile['file'].'" '.$user_profile['style'].' alt="'.api_get_person_name($user['firstname'],$user['lastname']).'"  title="'.api_get_person_name($user['firstname'], $user['lastname']).'" /></a></center>';
                        $photo = '<center>'.sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);" title="'.get_lang('Info').'"  ><img src="'.$user_profile['file'].'" '.$user_profile['style'].' alt="'.api_get_person_name($user['firstname'],$user['lastname']).'"  title="'.api_get_person_name($user['firstname'], $user['lastname']).'" /></a>',$user['user_id']).'</center>';
		} else {
			$photo = '<center><img src="'.$user_profile['file'].'" '.$user_profile['style'].' alt="'.api_get_person_name($user['firstname'], $user['lastname']).'" title="'.api_get_person_name($user['firstname'], $user['lastname']).'" /></center>';
		}	
		if($i%2 == 0){
			$class = "row_odd";
		}
		else {
			$class = "row_even";
		}
		echo '<tr class="'.$class.'">
		  <td width="5%">'.$photo.'</td>	
		  <td width="10%">'.$user['official_code'].'</td>	
		  <td width="20%">'.$user['firstname'].'</td>	
		  <td width="15%">'.$user['lastname'].'</td>	
		  <td width="15%">'.$user['username'].'</td>	
		  <td width="20%">'.$user['email'].'</td>			  
		  <td width="10%">';
		  //<a href="../mySpace/myStudents.php?student='.$user['user_id'].''.$orig_param.'">'.Display::return_icon('pixel.gif', get_lang('Tracking'), array('class' => 'actionplaceholdericon actionstatistics')).'</a>&nbsp;
		  echo '<a href="session_course_user.php?id_user='.$user['user_id'].'&amp;id_session='.$id_session.'">'.Display::return_icon('pixel.gif', get_lang('BlockCoursesForThisUser'), array('class' => 'actionplaceholdericon actioncourse')).'</a>&nbsp;';
		  //echo '<a href="'.api_get_self().'?id_session='.$id_session.'&amp;action=delete&user='.$user['user_id'].'" onclick="javascript:if(!confirm(\''.get_lang('ConfirmYourChoice').'\')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a></td>';
                    $link = api_get_self().'?id_session='.$id_session.'&amp;action=delete&user='.$user['user_id'];
                    $title = get_lang("ConfirmationDialog");
                    $text = get_lang("ConfirmYourChoice");
                    echo '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a></td>';		echo '</tr>';
                $i++;
	}
}
?>
</table><br/>
</div>
<?php
// End content
echo '</div>';

// footer
Display :: display_footer();
?>
