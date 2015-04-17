<?php //$Id: work.lib.php 22357 2009-07-24 17:44:17Z juliomontoya $
/* For licensing terms, see /dokeos_license.txt */
/**
*	@package dokeos.work
* 	@author Thomas, Hugues, Christophe - original version
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University - ability for course admins to specify wether uploaded documents are visible or invisible by default.
* 	@author Roan Embrechts, code refactoring and virtual course support
* 	@author Frederic Vauthier, directories management
* 	@version $Id: work.lib.php 22357 2009-07-24 17:44:17Z juliomontoya $
*/
/**
 * Displays action links (for admins, authorized groups members and authorized students)
 * @param	string	Current dir
 * @param	integer	Whether to show tool options
 * @param	integer	Whether to show upload form option
 * @return	void
 */

require_once dirname(__FILE__).'/../document/document.inc.php';
require_once dirname(__FILE__).'/../inc/lib/fileDisplay.lib.php';
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');

function display_action_links($cur_dir_path, $always_show_tool_options, $always_show_upload_form)
{
	global $gradebook;
	global $charset, $_course;
	$display_output = "";
	$origin = isset($_GET['origin'])?Security::remove_XSS($_GET['origin']):'';
	$work_table = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);

	//$origin = api_get_tools_lists($origin);

	echo '<div class="actions">';

	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'correct_paper'){
		$sql = "SELECT parent_id FROM $work_table WHERE id = ".$_REQUEST['id'];
		$res = Database::query($sql,__FILE__,__LINE__);
		$row = Database::fetch_row($res);
		$display_output .= '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=view_papers&amp;assignment_id='.$row[0].'&amp;origin='.$origin.'&amp;gradebook='.$gradebook.'&amp;curdirpath='.$parent_dir.'">'.Display::return_icon('pixel.gif',get_lang('BackToPapersList'),array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackToPapersList').'</a>';
	}
	else if(isset($_REQUEST['action'])){
		$display_output .= '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;origin='.$origin.'&amp;gradebook='.$gradebook.'&amp;curdirpath='.$parent_dir.'">'.Display::return_icon('pixel.gif',get_lang('BackToPapersList'),array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackToWorksList').'</a>';
	}

	if(api_is_platform_admin() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id()) || api_is_allowed_to_edit()){
	$display_output .=	'<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=new_assignment&amp;toolgroup='.Security::remove_XSS($_GET['toolgroup']).'&amp;curdirpath='.$cur_dir_path.'&amp;createdir=1&amp;origin='.$origin.'&amp;gradebook='.$gradebook.'">'.Display::return_icon('pixel.gif',get_lang('CreateAssignment'),array('class' => 'toolactionplaceholdericon toolactionnewassignment')).get_lang('CreateAssignment').' </a>';
	}

//	$sql = "SELECT * FROM $work_table WHERE filetype='folder' AND parent_id='0' AND session_id = ".api_get_session_id();
	$sql = "SELECT * FROM $work_table WHERE filetype='folder' AND parent_id='0'";
	$res = Database::query($sql,__FILE__,__LINE__);
	$no_of_assignment = Database::num_rows($res);

	if(!in_array($_REQUEST['action'], array('submit_work', 'new_assignment'))){
		if($no_of_assignment > 0){
			$display_output .= "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;curdirpath=".$cur_dir_path."&amp;action=submit_work&amp;display_upload_form=true&amp;origin=".$origin."&amp;gradebook=".$gradebook.(isset($_GET['assignment_id'])?'&amp;assignment_id='.intval($_GET['assignment_id']):'')."\">".Display::return_icon('pixel.gif',get_lang('UploadADocument'),array('class' => 'toolactionplaceholdericon toolactionupload')). get_lang("UploadADocument") .'</a>';
		}
	}

	if ($display_output != "")
	{
		echo $display_output;
	}
	echo '</div>';
}

function create_unexisting_work_directory($base_work_dir,$desired_dir_name)
{
	$nb = '';
	$base_work_dir = (substr($base_work_dir,-1,1)=='/'?$base_work_dir:$base_work_dir.'/');
	while ( file_exists($base_work_dir.$desired_dir_name.$nb) ) {
		$nb += 1;
	}
	//echo "creating ".$base_work_dir.$desired_dir_name.$nb."#...";
	$perm = api_get_setting('permissions_for_new_directories');
	$perm = octdec(!empty($perm)?$perm:'0770');
	if ( mkdir($base_work_dir.$desired_dir_name.$nb, $perm)) {
		chmod($base_work_dir.$desired_dir_name.$nb, $perm);
		return $desired_dir_name.$nb;
	} else {
	return false;
	}
}

function create_new_assignment($cur_dir_path, $stok, $ctok, $add_lp_param){
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG			= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);

	if (isset ($_REQUEST['createdir']) && (api_is_allowed_to_edit() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id()))) {
		$form = new FormValidator('new_assignment', 'post','work.php?action=new_assignment&amp;createdir='.$_REQUEST['createdir'].'origin='.api_get_tools_lists($_REQUEST['origin']).'&amp;gradebook='.(empty($_GET['gradebook'])?'':'view'). $add_lp_param);
		$form->addElement('header', '', get_lang('CreateAssignment'));
		$form->addElement('hidden','curdirpath',Security :: remove_XSS($cur_dir_path));
		$form->addElement('hidden','sec_token',$stok);
		$form->addElement('text', 'new_dir', get_lang('AssignmentName'), 'class="focus";style="width:500px;"');
	//	$form->addElement('textarea','description',get_lang('Description'),array ('rows' => '3', 'cols' => '62'));
		$form->add_html_editor('description',get_lang('Description'), false, false, array('ToolbarSet' => 'Survey', 'Width' => '600px', 'Height' => '200px'));
		$form->addElement('datepicker', 'deadline', get_lang('Deadline'), array('form_name'=>'new_assignment'));
		$form->addElement('radio', 'confidential', get_lang('Confidential'), get_lang('EachStudent'), 0);
		$form->addElement('radio', 'confidential', '', get_lang('AllStudent'), 1);
		$option = array();
		for($i=0;$i<=20;$i++){
			$option[] = $i;
		}
		$form->addElement('select', 'score', get_lang('Score'), $option);
		$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');

		$defaults = array();
		$defaults['confidential'] = 0;
		$defaults['score'] = 20;
		$defaults['deadline'] = date('Y-m-d 12:00:00');
		$form->setDefaults($defaults);
		if($form -> validate()) {
			$assignment = $form->exportValues();
			$new_dir = $assignment['new_dir'];
			$description = $assignment['description'];
			$deadline = $assignment['deadline'];
			$confidential = $assignment['confidential'];
			$score = $assignment['score'];
			$new_dir = str_replace(' ','_',$new_dir);
			$base_work_dir = api_get_path(SYS_COURSE_PATH) . $_course['path'] . '/work';
			$added_slash = (substr($cur_dir_path, -1, 1) == '/') ? '' : '/';
			$directory = Security::remove_XSS($new_dir);
			$directory = replace_dangerous_char($directory);
			$directory = disable_dangerous_file($directory);
			$dir_name = $cur_dir_path . $added_slash . $directory;
			$created_dir = create_unexisting_work_directory($base_work_dir, $dir_name);
			// we insert here the directory in the table $work_table
			$dir_name_sql='';

			if ($ctok==$assignment['sec_token']) {

				if (!empty($created_dir)) {
					if ($cur_dir_path=='/') {
						$dir_name_sql = $created_dir;
					} else {
						$dir_name_sql = '/'.$created_dir;
					}

					$sql_add_publication = "INSERT INTO " . $work_table . " SET " .
										   "url         = '".Database::escape_string($dir_name_sql)."',
										   title        = '".Database::escape_string($assignment['new_dir'])."',
										   description 	= '".Database::escape_string($assignment['description'])."',
										   author      	= '',
										   active	= '0',
										   accepted	= '1',
										   filetype 	= 'folder',
										   sent_date	= NOW(),
										   parent_id	= '',
										   qualificator_id	= '',
										   qualification = ".Database::escape_string($assignment['score']).",
										   view_properties = ".$confidential.",
										   date_of_qualification	= '0000-00-00 00:00:00',
										   weight   = '".Database::escape_string($assignment['score'])."',
										   session_id   = ".api_get_session_id();

					Database::query($sql_add_publication, __FILE__, __LINE__);

					// add the directory
					$id = Database::insert_id();
					api_item_property_update($_course, 'work', $id, 'DirectoryCreated', api_get_user_id());

					$sql_add_homework = "INSERT INTO $TSTDPUBASG SET " .
									    "expires_on         = '".$assignment['deadline']."',
										ends_on             = '".$assignment['deadline']."',
										add_to_calendar     = '1',
										enable_qualification = '1',
										publication_id = '".$id."'";

					Database::query($sql_add_homework, __FILE__, __LINE__);
					$datetime = explode(" ", $assignment['deadline']);
					$dateparts = explode("-", $datetime[0]);
					$deadline = $dateparts[1].'/'.$dateparts[2].'/'.$dateparts[0];
                                        $b_send_mail = api_get_course_setting('email_alert_manager_on_new_doc');
                                        if($b_send_mail){
                                            send_mail_to_students($new_dir,$description,$deadline);
                                        }


				}

			}
		echo '<script type="text/javascript">window.location.href = "'.api_get_self().'?'.api_get_cidReq().'"</script>';
		}
		$form->display();
	}
}

function edit_assignment($assignment_id, $cur_dir_path, $stok, $ctok, $add_lp_param){
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG			= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);

	if (isset ($assignment_id) && (api_is_allowed_to_edit() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id()))) {
		$form = new FormValidator('edit_assignment', 'post','work.php?action=edit_assignment&amp;edit_dir='.$_REQUEST['edit_dir'].'&amp;assignment_id='.$assignment_id.'&amp;origin='.api_get_tools_lists($_REQUEST['origin']).'&amp;gradebook='.(empty($_GET['gradebook'])?'':'view'). $add_lp_param);
		$form->addElement('header', '', get_lang('EditAssignment'));
		$form->addElement('hidden','curdirpath',Security :: remove_XSS($cur_dir_path));
		$form->addElement('hidden','sec_token',$stok);
		$form->addElement('text', 'edit_dir', get_lang('AssignmentName'), 'class="focus";style="width:500px;"');
	//	$form->addElement('textarea','description',get_lang('Description'),array ('rows' => '3', 'cols' => '62'));
		$form->add_html_editor('description',get_lang('Description'), false, false, array('ToolbarSet' => 'Survey', 'Width' => '600px', 'Height' => '150px'));
		$form->addElement('datepicker', 'deadline', get_lang('Deadline'), array('form_name'=>'new_assignment'));
		$form->addElement('radio', 'confidential', get_lang('Confidential'), get_lang('EachStudent'), 0);
		$form->addElement('radio', 'confidential', '', get_lang('AllStudent'), 1);
		$option = array();
		for($i=0;$i<=20;$i++){
			$option[] = $i;
		}
		$form->addElement('select', 'score', get_lang('Score'), $option);
		$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');

		$sql = "SELECT work.*, workpub.ends_on AS deadline FROM $work_table work, $TSTDPUBASG workpub WHERE work.id = workpub.publication_id AND work.id = ".$assignment_id;
		$result = Database::query($sql, __FILE__, __LINE__);
		$row = Database :: fetch_array($result);

		$defaults = array();
		$defaults['edit_dir'] = $row['title'];
		$defaults['description'] = $row['description'];
		$defaults['confidential'] = $row['view_properties'];
		$defaults['score'] = $row['qualification'];
		$defaults['deadline'] = $row['deadline'];
		$defaults['curdirpath'] = $row['title'];
		$form->setDefaults($defaults);
		if($form -> validate()) {
			$assignment = $form->exportValues();
			$edit_dir = $assignment['edit_dir'];
			$description = $assignment['description'];
			$deadline = $assignment['deadline'];
			$confidential = $assignment['confidential'];
			$score = $assignment['score'];

			$mydir = $assignment['curdirpath'];
			$dir_name = replace_dangerous_char($edit_dir);
			$dir_name = disable_dangerous_file($dir_name);

			update_dir_name($mydir,$dir_name);

			$sql_update_assignment = "UPDATE $work_table SET title = '".Database::escape_string($edit_dir)."',
															 description = '".Database::escape_string($description)."',
															 view_properties = ".$confidential.",
															 qualification = ".$score."
									       WHERE id = ".$assignment_id;
			Database::query($sql_update_assignment, __FILE__, __LINE__);

			$sql_update_publication = "UPDATE $TSTDPUBASG SET expires_on = '".Database::escape_string($deadline)."',
															 ends_on = '".Database::escape_string($deadline)."'
									        WHERE publication_id = ".$assignment_id;
			Database::query($sql_update_publication, __FILE__, __LINE__);

			echo '<script type="text/javascript">window.location.href = "'.api_get_self().'?'.api_get_cidReq().'"</script>';

		}
		$form->display();

	}
}

function assignment_list($cur_dir_path){
	global $_course;
	if (api_is_allowed_to_edit() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
		$default_column = 2;
	}
	else {
		$default_column = 1;
	}
	$tablename = 'assignment_table';
	$sortable_data = get_assignment_data($cur_dir_path);

	$table = new SortableTableFromArrayConfig($sortable_data,$default_column,20,$tablename,$column_show,$column_order,'ASC');

	if (api_is_allowed_to_edit() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
            $table->set_header(0, '', false);
            $table->set_header(1, get_lang('Edit'), false);
            $table->set_header(2, get_lang('Assignment'),true,'style="width:12%;"');
            $table->set_header(3, get_lang('Summary'),true,'style="width:60%;"');
            $table->set_header(4, get_lang('Deadline'));
	}
	else {
            $table->set_header(0, '', false);
            $table->set_header(1, get_lang('Assignment'),'','style="width:12%;"');
            $table->set_header(2, get_lang('Summary'),'','style="width:60%;"');
            $table->set_header(3, get_lang('Deadline'));
	}
	if (api_is_allowed_to_edit() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
            $table->set_header(5, get_lang('Papers'));
            $table->set_header(6, get_lang('Corrected'));
            $table->set_header(7, get_lang('Average'));
            $table->set_header(8, get_lang('DownloadAll'));
	}
	if (api_is_allowed_to_edit() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
		$table->set_form_actions(array ('delete_assignment' => get_lang('Delete')));
	}
	$table->display();
}

function get_assignment_data($cur_dir_path) {
	global $_course, $origin;

	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG			= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);
	$itemproperty_table = Database :: get_course_table(TABLE_ITEM_PROPERTY);

	//condition for the session
	$session_id = api_get_session_id();
	$condition_session = api_get_session_condition($session_id);

	if (!empty($_SESSION['toolgroup'])) {
		$group_query = " AND post_group_id = '".$_SESSION['toolgroup']."' "; // set to select only messages posted by the user's group
	} else {
		$group_query = " AND post_group_id = '0' ";
	}

        $sql = "SELECT work.id AS assignment_id,work.title,work.filetype, work.description,work.url,work.parent_id,work.qualification,workassign.ends_on
                FROM $work_table work LEFT JOIN $TSTDPUBASG workassign ON work.id = workassign.publication_id
                WHERE parent_id = 0 ".$group_query;

	$res = Database::query($sql, __FILE__, __LINE__);
	$assignments = array ();
	while ($assignment = Database::fetch_array($res)) {
		$assignment_id = $assignment['assignment_id'];
		$assignment_title = !empty($assignment['title']) ? $assignment['title'] : substr($assignment['url'], 1);
		$assignment_description = $assignment['description'];
                (strlen($assignment_description)>300) ? $assignment_description = substr($assignment_description,0,300).'...':$assignment_description = $assignment_description;
		$assignment_url = $assignment['url'];
		$parent_id = $assignment['parent_id'];
		$qualification = $assignment['qualification'];
		$assignment_deadline = $assignment['ends_on'];

                if ($assignment['filetype'] == 'folder') {
		$icon_link = '<center>'.Display::return_icon('pixel.gif','',array('class' => 'actionplaceholdericon actionstudentviewwork')).'</center>';

		$edit_link = '<center><a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=edit_assignment&amp;assignment_id='.$assignment_id.'&amp;origin='.$origin.'&amp;edit_dir='.$assignment_title.'">'.Display::return_icon('pixel.gif',get_lang('Edit'),array('class' => 'actionplaceholdericon actionedit')).'</a></center>';

		$title_link = '<a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=view_papers&amp;curdirpath='.$assignment_title.'&amp;assignment_id='.$assignment_id.'">'.$assignment_title.'</a>';
                } else {
                    $icon_link = $edit_link = build_document_icon_tag('file', $assignment['url']);
                    $title_link = '<a href="download.php?file='.$assignment['url'].'">'.$assignment_title.'</a>';
                }

		$datetime = explode(" ", $assignment_deadline);
		$dateparts = explode("-", $datetime[0]);
		if(api_get_interface_language() == 'french'){
		$deadline = $dateparts[2].'/'.$dateparts[1].'/'.$dateparts[0];
		}
		else {
		$deadline = $dateparts[1].'/'.$dateparts[2].'/'.$dateparts[0];
		}

		if(api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
		$users_list = get_grouptutor_users(api_get_user_id());

		$sql_paper = "SELECT * FROM $work_table work,$itemproperty_table ip WHERE work.id = ip.ref AND ip.insert_user_id IN (".$users_list.") AND work.parent_id = ".$assignment_id." AND work.filetype = 'file'";
		}
		else {
		$sql_paper = "SELECT * FROM $work_table WHERE parent_id = ".$assignment_id." AND filetype = 'file'";
		}

		$res_paper = Database::query($sql_paper, __FILE__, __LINE__);
		$count_papers = Database::num_rows($res_paper);
		$number_of_papers = '<center>'.$count_papers.'</center>';

		if(api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
		$users_list = get_grouptutor_users(api_get_user_id());
		$sql_corrected = "SELECT * FROM $work_table work,$itemproperty_table ip WHERE work.id = ip.ref AND ip.insert_user_id IN (".$users_list.") AND parent_id = ".$assignment_id." AND qualificator_id = 1 AND filetype = 'file'";
		}
		else {
		$sql_corrected = "SELECT * FROM $work_table WHERE parent_id = ".$assignment_id." AND qualificator_id = 1 AND filetype = 'file'";
		}
		$res_corrected = Database::query($sql_corrected, __FILE__, __LINE__);
		$number_of_papers_corrected = '<center>'.Database::num_rows($res_corrected).'</center>';

		if(api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
		$users_list = get_grouptutor_users(api_get_user_id());
		$sql_sum = "SELECT sum(qualification) AS totalmarks FROM $work_table  work,$itemproperty_table ip WHERE work.id = ip.ref AND ip.insert_user_id IN (".$users_list.") AND parent_id = ".$assignment_id." AND qualificator_id = 1 AND filetype = 'file'";
		}
		else {
		$sql_sum = "SELECT sum(qualification) AS totalmarks FROM $work_table WHERE parent_id = ".$assignment_id." AND qualificator_id = 1 AND filetype = 'file'";
		}
		$res_sum = Database::query($sql_sum, __FILE__, __LINE__);
		while($row_sum = Database::fetch_row($res_sum)){
			$totalmarks = $row_sum[0];
		}
		if($totalmarks <> 0){
			$average = $totalmarks/Database::num_rows($res_corrected);
		}
		else {
			$average = 0;
		}
		$average_marks = $average.'/'.round($qualification,2);

                $download_link = '';
                if ($assignment['filetype'] == 'folder') {
                    if ($count_papers >= 1) {
		       $download_link = '<center><a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=downloadfolder&amp;path='.$assignment_url.'">'.Display::return_icon('pixel.gif',get_lang('DownloadAll'),array('class' => 'actionplaceholdericon actionsavebackup')).'</a></center>';
                    } else {
		       $download_link = '<center>'.Display::return_icon('pixel.gif',get_lang('DownloadAll'),array('class' => 'actionplaceholdericon actionsavebackup')).'</center>';
		    }
                } else {
                    $download_link = '<center><a href="download.php?file='.$assignment['url'].'">'.Display::return_icon('pixel.gif',get_lang('Download'),array('class' => 'actionplaceholdericon actionsavebackup')).'</a>&nbsp;';
                    $download_link .= '<a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=move_form&amp;id='.$assignment['assignment_id'].'">'.Display::return_icon('pixel.gif',get_lang('Move'),array('class' => 'actionplaceholdericon actionworkmove')).'</a></center>';
                    $deadline = $number_of_papers = $number_of_papers_corrected = $average_marks = '';
                }
		if (api_is_allowed_to_edit() || api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
		$assignments[] = array($assignment_id,$edit_link,$title_link,strip_tags($assignment_description,'<p><a><span><br><b>'),'<center>'.$deadline.'</center>',$number_of_papers,$number_of_papers_corrected,'<center>'.$average_marks.'</center>',$download_link);
		}
		else {
            $assignments[] = array($icon_link,$title_link,strip_tags($assignment_description,'<p><a><span><br><b>'),'<center>'.$deadline.'</center>');
		}
	}
	return $assignments;
}


function assignment_paper_list($cur_dir_path){
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);

	if (isset($_REQUEST['assignment_id'])) {
		$my_params = array ('assignment_id' => intval($_REQUEST['assignment_id']));
	}
	if (isset($_REQUEST['action'])) {
		$my_params['action'] = Security::remove_XSS($_REQUEST['action']);
	}
	$sql = "SELECT title,description FROM $work_table WHERE id = ".intval($_REQUEST['assignment_id']);
	$res = Database::query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($res);

	echo '<table width="75%" cellpadding="3" cellspacing="3"><tr><td><span style="color:orange;text-transform:uppercase;font-weight:bold;">'.$row['title'].'</td></tr>
	<tr><td><span>'.$row['description'].'</td></tr></table>';

	$default_column = 1;
	$tablename = 'paperlist_table';
	$sortable_data = get_papers_data();

	$table = new SortableTableFromArrayConfig($sortable_data,$default_column,20,$tablename,$column_show,$column_order,'ASC');
	$table->set_additional_parameters($my_params);
	if(api_is_allowed_to_edit()){
	$table->set_header(0, get_lang('Type'), true);
	$table->set_header(1, get_lang('Paper'));
	$table->set_header(2, get_lang('Author'));
	$table->set_header(3, get_lang('GroupName'));
	$table->set_header(4, get_lang('DateSent'));
	$table->set_header(5, get_lang('Score'));
	}
	else if(api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){
	$table->set_header(0, get_lang('Type'), true);
	$table->set_header(1, get_lang('Paper'));
	$table->set_header(2, get_lang('Summary'));
	$table->set_header(3, get_lang('Author'));
	$table->set_header(4, get_lang('GroupName'));
	$table->set_header(5, get_lang('DateSent'));
	$table->set_header(6, get_lang('Corrected'));
	$table->set_header(7, get_lang('Score'));
	}
	else {
	$table->set_header(0, get_lang('Type'), true);
	$table->set_header(1, get_lang('Paper'));
	$table->set_header(2, get_lang('Summary'));
	$table->set_header(3, get_lang('GroupName'));
	$table->set_header(4, get_lang('Corrected'));
	$table->set_header(5, get_lang('Remark'));
	}
	if(api_is_allowed_to_edit()){
//	$table->set_header(5, get_lang('Corrected'));
	$table->set_header(6, get_lang('Move'));
	$table->set_header(7, get_lang('Download'));
	$table->set_header(8, get_lang('Delete'));
	}

	$table->display();
}

function get_papers_data(){
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG			= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);
	$itemproperty_table = Database :: get_course_table(TABLE_ITEM_PROPERTY);

//	$sql = "SELECT * FROM $work_table WHERE filetype='folder' AND parent_id='0' AND id != ".$_REQUEST['assignment_id']." AND session_id = ".api_get_session_id();
	$sql = "SELECT * FROM $work_table WHERE filetype='folder' AND parent_id='0' AND id != ".intval($_REQUEST['assignment_id']);
	$res = Database::query($sql,__FILE__,__LINE__);
	$no_of_assignment = Database::num_rows($res);

	$sql = "SELECT work.weight,work.view_properties,workassign.ends_on FROM $work_table work, $TSTDPUBASG workassign WHERE work.id = workassign.publication_id AND work.id = ".intval($_REQUEST['assignment_id']);
	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_row($res)){
		$assignment_weight = $row[0];
		$confidential = $row[1];
		$ends_on = $row[2];
	}

	//condition for the session
	$session_id = api_get_session_id();
	$condition_session = api_get_session_condition($session_id);

//	$is_coach = api_is_coach($session_id,api_get_course_id());

/*	if (!empty($_SESSION['toolgroup'])) {
		$group_query = " AND work.post_group_id = '".$_SESSION['toolgroup']."' "; // set to select only messages posted by the user's group
	} else {
		$group_query = " AND work.post_group_id = '0' ";
	}	*/

	if($confidential == 0){
		$users = get_user_grouptutor(api_get_user_id());
		$confidential_query = " , $itemproperty_table ip WHERE work.id = ip.ref AND ip.tool = 'work' AND ip.insert_user_id IN (".$users.") AND ";
	//	$confidential_query = " , $itemproperty_table ip WHERE work.id = ip.ref AND ip.tool = 'work' AND ip.insert_user_id = ".api_get_user_id()." AND ";
	}

	$sql = "SELECT * FROM $work_table work";
	if(api_is_grouptutor($_course,$session_id,api_get_user_id())){
		$users_list = get_grouptutor_users(api_get_user_id());

		$sql .= "  , $itemproperty_table ip WHERE work.id = ip.ref AND ip.tool = 'work' AND ip.insert_user_id IN (".$users_list.") AND ";
	}
	else if(api_is_allowed_to_edit()){
		$sql .= " WHERE ";
	}
	else {
		if($confidential == 0){
			$sql .= $confidential_query;
		}
		else {
			$sql .= " WHERE ";
		}
	}
	$sql .= "  work.parent_id = ".intval($_REQUEST['assignment_id']).$group_query;

	$res = Database::query($sql, __FILE__, __LINE__);
	$papers = array ();
	while ($paper = Database::fetch_array($res)) {
		$paper_id = $paper['id'];
		$paper_title = $paper['title'];
		$paper_url = $paper['url'];
		$paper_description = $paper['description'];
		$author = $paper['author'];
		$sent_date = $paper['sent_date'];
		$qualification = $paper['qualification'];
		$corrected = $paper['qualificator_id'];
		$note = $paper['remark'];

		$datetime = explode(" ", $sent_date);
		$dateparts = explode("-", $datetime[0]);
		if(api_get_interface_language() == 'french'){
		$date = $dateparts[2].'/'.$dateparts[1].'/'.$dateparts[0];
		}
		else {
		$date = $dateparts[1].'/'.$dateparts[2].'/'.$dateparts[0];
		}

		if(strtotime($sent_date) > strtotime($ends_on)){
			$date_str = '<span style="align:center;color:red;">'.$date.'</span>';
		}
		else {
			$date_str = '<span style="align:center;">'.$date.'</span>';
		}

		$group_name = '';
		$group_name = get_group_name($paper_id);

		$paper_icon =  '<center><a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=correct_paper&amp;id='.$paper_id.'&amp;assignment_id='.intval($_REQUEST['assignment_id']).'" >'.build_document_icon_tag('file',$paper_url).'</a></center>';
		if(api_is_allowed_to_edit()){
                    $title_tag = '<a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=correct_paper&amp;id='.$paper_id.'&amp;assignment_id='.intval($_REQUEST['assignment_id']).'">'.$paper_title.'<br/>'.$paper_description.'</a>';
		}
		elseif(api_is_grouptutor($_course,$session_id,api_get_user_id())){
                    $title_tag = '<a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=correct_paper&amp;id='.$paper_id.'&amp;assignment_id='.intval($_REQUEST['assignment_id']).'">'.$paper_title.'</a>';
		}
		else {
                    $paper_icon =  '<center>'.build_document_icon_tag('file',$paper_url).'</center>';
                    $title_tag = '<a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=view_paper&amp;id='.$paper_id.'&amp;assignment_id='.intval($_REQUEST['assignment_id']).'">'.$paper_title.'</a>';
		}

		if($qualification == 0){
			$assignment_score = '<center>/</center>';
		}
		else {
			$assignment_score = '<center>'.round($qualification,2).'/'.round($assignment_weight,2).'</center>';
		}

		if($corrected == 0){
			$corrected_img = '<center>-</center>';
		}
		else {
			$path_parts = pathinfo($paper_url);
			$new_file_name = $path_parts['filename'].'_corr.'.$path_parts['extension'];
			$new_correct_filename = $path_parts['dirname'].'/'.$new_file_name;
			if(file_exists(api_get_path(SYS_COURSE_PATH.$new_correct_filename))){
			$corrected_img = '<center><a href="download.php?file='.$paper_url.'">'.Display::return_icon('pixel.gif','',array('class' => 'actionplaceholdericon actionworkcorrect')).'</a></center>';
			}
			else {
			$corrected_img = '<center><a href="download.php?file='.$new_correct_filename.'">'.Display::return_icon('pixel.gif','',array('class' => 'actionplaceholdericon actionworkcorrect')).'</a></center>';
			}
		}

		if($no_of_assignment > 0){
		$move_icon = '<center><a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=move_paper&amp;assignment_id='.intval($_REQUEST['assignment_id']).'&amp;id='.$paper_id.'">'.Display::return_icon('pixel.gif',get_lang('Move'),array('class' => 'actionplaceholdericon actionworkmove')).'</a></center>';
		}
		else {
		$move_icon = '<center>'.Display::return_icon('pixel.gif',get_lang('Move'),array('class' => 'actionplaceholdericon actionworkmove')).'</center>';
		}
		$download_icon = '<center><a href="download.php?file='.$paper_url.'">'.Display::return_icon('pixel.gif',get_lang('Download'),array('class' => 'actionplaceholdericon actionsavebackup')).'</a></center>';
		$delete_icon = '<center><a href="'.api_get_self().'?'.api_get_cidReq().'&amp;action=delete_paper&amp;id='.$paper_id.'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"), ENT_QUOTES, api_get_system_encoding()))."'".')) return false;">'.Display::return_icon('pixel.gif',get_lang('Delete'),array('class' => 'actionplaceholdericon actiondelete')).'</a></center>';

		if(api_is_allowed_to_edit()){
		$papers[] = array($paper_icon,$title_tag,'<center>'.$author.'</center>','<center>'.$group_name.'</center>','<center>'.$date_str.'</center>',$assignment_score,$move_icon,$download_icon,$delete_icon);
		}
		else if(api_is_grouptutor($_course,$session_id,api_get_user_id())){
		$papers[] = array($paper_icon,$title_tag,'<center>'.$paper_description.'</center>','<center>'.$author.'</center>','<center>'.$group_name.'</center>','<center>'.$date_str.'</center>','<center>'.$corrected_img.'</center>',$assignment_score);
		}
		else {
		$papers[] = array($paper_icon,$title_tag,$paper_description,'<center>'.$group_name.'</center>','<center>'.$corrected_img.'</center>','<center>'.$note.'</center>');
		}

	}

	return $papers;
}

/**
 * Update the url of a dir in the student_publication table
 * @param	string old path
 * @param	string new path
 */
function update_dir_name($path, $new_name) {

	if (!empty($new_name)){

		global $base_work_dir;
		include_once(api_get_path(LIBRARY_PATH) . "/fileManage.lib.php");
		include_once(api_get_path(LIBRARY_PATH) . "/fileUpload.lib.php");
		$path_to_dir = dirname($path);
		if($path_to_dir=='.') {
			$path_to_dir = '';
		} else {
			$path_to_dir .= '/';
		}
		$new_name = Security::remove_XSS($new_name);
		$new_name = replace_dangerous_char($new_name);
		$new_name = disable_dangerous_file($new_name);

		my_rename($base_work_dir.'/'.$path,$new_name);
		$table = Database::get_course_table(TABLE_STUDENT_PUBLICATION);

		//update all the files in the other directories according with the next query
		$sql = 'SELECT id, url FROM '.$table.' WHERE url LIKE BINARY "work/'.Database::escape_string($path).'/%"'; // like binary (Case Sensitive)

		$rs = Database::query($sql, __FILE__, __LINE__);
		$work_len=strlen('work/'.$path);

		while($work = Database :: fetch_array($rs)) {
			$new_dir=$work['url'];
			$name_with_directory=substr($new_dir,$work_len,strlen($new_dir));
			$sql = 'UPDATE '.$table.' SET url="work/'.$path_to_dir.$new_name.$name_with_directory.'" WHERE id= '.$work['id'];
			Database::query($sql, __FILE__, __LINE__);
		}

		//update all the directory's children according with the next query
		$sql = 'SELECT id, url FROM '.$table.' WHERE url LIKE BINARY "/'.Database::escape_string($path).'%"';
		$rs = Database::query($sql, __FILE__, __LINE__);
		$work_len=strlen('/'.$path);
		while($work = Database :: fetch_array($rs)) {
			$new_dir=$work['url'];
			$name_with_directory=substr($new_dir,$work_len,strlen($new_dir));
			$url=$path_to_dir.$new_name.$name_with_directory;
			$sql = 'UPDATE '.$table.' SET url="/'.$url.'" WHERE id= '.$work['id'];
			Database::query($sql, __FILE__, __LINE__);
		}
	}
}

function submit_work($cur_dir_path, $origin, $stok, $ctok, $is_course_member){
	echo '<script type="text/javascript">
	function updateDocumentTitle(value){
			var temp = value.indexOf("/");
			//linux path
			if(temp!=-1){
				var temp=value.split("/");
			}
			else{
				var temp=value.split("\\\");
			}
			document.getElementById("file_upload").value=temp[temp.length-1];
		}
	function getAssignmentId(id){
			document.getElementById("assignment_id").value=id;
	}
	</script>';
	global $_course,$_user;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG			= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);

	$my_cur_dir_path = $cur_dir_path;
	if ($my_cur_dir_path == '/') {
		$my_cur_dir_path = '';
	} elseif (substr($my_cur_dir_path, -1, 1) != '/') {
		$my_cur_dir_path = $my_cur_dir_path . '/';
	}

	$form = new FormValidator('submit_paper', 'post',api_get_self().'?'.api_get_cidReq().'&amp;action=submit_work&curdirpath='. rtrim(Security :: remove_XSS($cur_dir_path),'/').'&display_upload_form=true&amp;origin='.$origin.'&amp;gradebook='.Security::remove_XSS($_GET['gradebook']),'', 'enctype="multipart/form-data"');
	$form->addElement('header', '', get_lang('SubmitPaper'));
	$sql = "SELECT id,title FROM $work_table WHERE filetype='folder' AND parent_id='0'";
	$res = Database::query($sql,__FILE__,__LINE__);
	$assignments = array();
	$assignments[0] = '-- '.get_lang('Root').' --';
	while($obj = Database::fetch_object($res))
	{
		$assignments[$obj->id] = $obj->title;
	}

	$form->addElement('select', 'assignment', get_lang('AssignmentName'), $assignments,'onchange="getAssignmentId(this.value)"');
	$form->addElement('hidden','sec_token',$stok);
	$form->addElement('hidden','assignment_id','','id="assignment_id"');
	$form->addElement('file', 'file', get_lang('PaperUpload'), 'size="40" id="papper-file" onchange="updateDocumentTitle(this.value)"');
        $form->addRule('file', get_lang('ThisFieldIsRequired'), 'uploadedfile');

        $form->addElement('text', 'title', get_lang("TitleWork"), 'id="file_upload"  style="width: 350px;"');
	$form->addElement('textarea', 'summary', get_lang("Summary"), 'style="width: 350px; height: 60px;"');

	$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');

        $folder_id = get_folder_work_id($cur_dir_path);

        $default['assignment'] = $folder_id?$folder_id:(isset($_GET['assignment_id'])?intval($_GET['assignment_id']):0);

        $form->setDefaults($default);
	if($form -> validate()) {
		$paper = $form->exportValues();
		$assignment_id = $paper['assignment'];
		$description = $paper['summary'];
		$sql = "SELECT work.url,work.qualification,work.title,work.description,workassign.ends_on FROM $work_table work,$TSTDPUBASG workassign WHERE work.id = workassign.publication_id AND work.id = ".$assignment_id;
		$res = Database::query($sql,__FILE__,__LINE__);
		while($row = Database::fetch_array($res)){
			$url = $row['url'];
			$weight = $row['qualification'];
			$assignment_title = $row['title'];
			$assignment_description = $row['description'];
			$deadline = $row['ends_on'];
		}

		//if ($ctok==$paper['sec_token']) {
			if ((!empty($_FILES['file']['size']) && !empty($is_course_member)) || api_is_platform_admin()) {
				$updir = api_get_path(SYS_COURSE_PATH) . $_course["path"] . '/work'; //directory path to upload

				// Try to add an extension to the file if it has'nt one
				$new_file_name = add_ext_on_mime(stripslashes($_FILES['file']['name']), $_FILES['file']['type']);

				// Replace dangerous characters
				$new_file_name = replace_dangerous_char($new_file_name, 'strict');

				// Transform any .php file in .phps fo security
				$new_file_name = php2phps($new_file_name);
				//filter extension
				if (!filter_extension($new_file_name)) {
					Display :: display_error_message(get_lang('UplUnableToSaveFileFilteredExtension'), false, true);
					$succeed = false;
				} else {
					$title = $_FILES['file']['name'];
					$authors = api_get_person_name($_user['firstName'], $_user['lastName']);

					// compose a unique file name to avoid any conflict
					$new_file_name = uniqid('') . $new_file_name;
					if (isset ($_SESSION['toolgroup'])) {
						$post_group_id = $_SESSION['toolgroup'];
					} else {
						$post_group_id = '0';
					}

					//if we come from the group tools the groupid will be saved in $work_table
					@move_uploaded_file($_FILES['file']['tmp_name'], $updir . $my_cur_dir_path .$url.'/'. $new_file_name);
					$url = "work" . $my_cur_dir_path .$url.'/' . $new_file_name;

					$sql_add_publication = "INSERT INTO " . $work_table . " SET " .
										       "url         = '" . $url . "',
										       title       = '" . Database::escape_string($title) . "',
							                   description = '" . Database::escape_string($description) . "',
							                   author      = '" . Database::escape_string($authors) . "',
											   active		= '1',
											   accepted		= '1',
											   post_group_id = '" . $post_group_id . "',
											   sent_date	=  NOW(),
											   weight       = '".$weight."',
											   parent_id 	=  '".$assignment_id ."' ,
	                                           session_id = ".api_get_session_id();

					Database::query($sql_add_publication, __FILE__, __LINE__);

					$Id = Database::insert_id();
					api_item_property_update($_course, 'work', $Id, 'DocumentAdded', api_get_user_id());
					$succeed = true;

					// display the feedback message if the newly added documents are invisible by default
					if ($uploadvisibledisabled == 0){
						// we cannot used the display_xxx_message functions alone because this function depends on a setting
						// api_get_setting('display_feedback_messages') if the message is shown or not. In this case we ALWAYS
						// want to display the messages if the newly uploaded document is automatically invisible because
						// otherwise the student is NEVER notified that the document is successfully uploaded and the
						// student uploads a second and a third time. This is by consequence a useability problem that we solve here.
						// We need to check the setting to prevent double messages when the setting is set to ON
						if (api_get_setting('display_feedback_messages') == 'false') {
							/*echo '<div class="confirmation-message">';
							echo get_lang('DocumentUploadedButInvisible');
							echo '</div>';*/
						} else {
							//Display::display_confirmation_message(get_lang('DocumentUploadedButInvisible'));
						}
					}

					$datetime = explode(" ", $deadline);
					$dateparts = explode("-", $datetime[0]);
					if(api_get_interface_language() == 'french'){
					$deadline = $dateparts[2].'/'.$dateparts[1].'/'.$dateparts[0];
					}
					else {
					$deadline = $dateparts[1].'/'.$dateparts[2].'/'.$dateparts[0];
					}
					$user_id = api_get_user_id();
					sendmail_paper_to_tutor($authors,$Id,$user_id,$title,$assignment_title,$assignment_description,$deadline);
				}
			}
		//}
	}
	if($succeed) {
                $href = isset($assignment_id) && intval($assignment_id) == 0?api_get_self().'?'.api_get_cidReq():api_get_self().'?'.api_get_cidReq().'&action=view_papers&curdirpath='.$cur_dir_path.'&assignment_id='.$assignment_id;
		echo '<script type="text/javascript">window.location.href = "'.$href.'"</script>';
	}
	$form->display();

}

function correct_paper($paper_id){
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);

	$sql = "SELECT * FROM $work_table WHERE id = ".$paper_id;
	$result = Database::query($sql, __FILE__, __LINE__);
	$row = Database::fetch_array($result);

	$datetime = explode(" ", $row['sent_date']);
	$dateparts = explode("-", $datetime[0]);
	if(api_get_interface_language() == 'french'){
	$submittedon = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0].'&nbsp;'.$datetime[1];
	}
	else {
	$submittedon = $dateparts[1].'-'.$dateparts[2].'-'.$dateparts[0].'&nbsp;'.$datetime[1];
	}

	$form = new FormValidator('correct_paper', 'post',api_get_self().'?'.api_get_cidReq().'&amp;action=correct_paper&amp;id='.$paper_id,'', 'enctype="multipart/form-data"');
	$form->addElement('header', '', get_lang('CorrectPaper'));
	$form->addElement('static','paper',get_lang('Paper'),' :&nbsp;&nbsp;&nbsp;<span>'.$row['title'].'</span>');
	$form->addElement('static','summary',get_lang('Summary'),' :&nbsp;&nbsp;&nbsp;<span>'.$row['description'].'</span>');
	$form->addElement('static','author',get_lang('Author'),' :&nbsp;&nbsp;&nbsp;<span>'.$row['author'].'</span>');
	$form->addElement('static','date',get_lang('Submittedon'),' :&nbsp;&nbsp;&nbsp;<span>'.$submittedon.'</span>');
	$form->addElement('static','download',get_lang('DownloadPaper'),' :&nbsp;&nbsp;&nbsp;<a href="download.php?file='.$row['url'].'">'.Display::return_icon('pixel.gif',get_lang('Download'),array('class' => 'actionplaceholdericon actionsavebackup')).'</a>');
	$form->addElement('file', 'file', get_lang('CorrectionUpload'), 'size="40"');

        if ($row['qualificator_id'] == 0) {
                $corrected_img = '<center>-</center>';
                $form->addRule('file', get_lang('ThisFieldIsRequired'), 'uploadedfile');
        }
        else {
                $path_parts = pathinfo($row['url']);
                $new_file_name = $path_parts['filename'].'_corr.'.$path_parts['extension'];
                $new_correct_filename = $path_parts['dirname'].'/'.$new_file_name;
                if (file_exists(api_get_path(SYS_COURSE_PATH.$new_correct_filename))){
                    $corrected_img = '&nbsp;&nbsp;&nbsp;<a href="download.php?file='.$row['url'].'">'.Display::return_icon('pixel.gif','',array('class' => 'actionplaceholdericon actionsavebackup')).'</a>';
                }
                else {
                    $corrected_img = '&nbsp;&nbsp;&nbsp;<a href="download.php?file='.$new_correct_filename.'">'.Display::return_icon('pixel.gif','',array('class' => 'actionplaceholdericon actionsavebackup')).'</a>';
                }
                $form->addElement('static','correcteddownload', get_lang('DownloadCorrectedPaper'),' :'.$corrected_img);
        }

        $form->addElement('textarea', 'remark', get_lang("Remark"), 'style="width: 400px; height: 80px;"');

	$option = array();
	for($i=0;$i<=20;$i++){
		$option[] = $i;
	}
	$form->addElement('select', 'score', get_lang('Score'), $option);
	$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
	$defaults = array();
	$defaults['remark'] = $row['remark'];
	if($row['qualificator_id'] == 1){
		$defaults['score'] = $row['qualification'];
	}
	else {
		$defaults['score'] = $row['weight'];
	}
//	$defaults['score'] = 0;
	$form->setDefaults($defaults);
	if($form -> validate()) {
            $correctpaper = $form->exportValues();
            if (!empty($_FILES['file']['size'])) {
                    $updir = api_get_path(SYS_COURSE_PATH) . $_course["path"]; //directory path to upload
                    $path_parts = pathinfo('/'.$row['url']);
                    $new_file_name = $path_parts['filename'].'_corr.'.$path_parts['extension'];
                    @move_uploaded_file($_FILES['file']['tmp_name'], $updir .'/'.$path_parts['dirname'] .'/'. $new_file_name);
                    $sql = "UPDATE $work_table SET remark = '".$correctpaper['remark']."',
                                                                               qualification = ".$correctpaper['score'].",
                                                                               qualificator_id = 1
                                                                               WHERE id = ".$paper_id;

                    Database::query($sql, __FILE__, __LINE__);
                    sendmail_correctpaper_tostudent($paper_id,$row['parent_id'],$row['title'],$row['sent_date']);
            }
            elseif($row['qualificator_id'] == 1) {
                    $sql = "UPDATE $work_table SET remark = '".$correctpaper['remark']."',
                                                                               qualification = ".$correctpaper['score'].",
                                                                               qualificator_id = 1
                                                                               WHERE id = ".$paper_id;

                    Database::query($sql, __FILE__, __LINE__);
                    sendmail_correctpaper_tostudent($paper_id,$row['parent_id'],$row['title'],$row['sent_date']);
            }
            echo '<script type="text/javascript">window.location.href = "'.api_get_self().'?'.api_get_cidReq().'&action=view_papers&assignment_id='.$row['parent_id'].'"</script>';
	}
	$form->display();
}

function view_paper($paper_id){
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);

	$sql = "SELECT * FROM $work_table WHERE id = ".intval($paper_id);
	$result = Database::query($sql, __FILE__, __LINE__);
	$row = Database::fetch_array($result);

	if(strlen($row['remark']) > 400){
		$remark = substr($row['remark'],0,400);
		$remark .= '...';
	}
	else {
		$remark = $row['remark'];
	}
	$path_parts = pathinfo('/'.$row['url']);
	$new_file_name = $path_parts['filename'].'_corr.'.$path_parts['extension'];
	$corrected_url = $path_parts['dirname'] .'/'. $new_file_name;

	$datetime = explode(" ", $row['sent_date']);
	$dateparts = explode("-", $datetime[0]);
	if(api_get_interface_language() == 'french'){
	$submittedon = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0].'&nbsp;'.$datetime[1];
	}
	else {
	$submittedon = $dateparts[1].'-'.$dateparts[2].'-'.$dateparts[0].'&nbsp;'.$datetime[1];
	}

	$download_paper = '<a href="download.php?file='.$row['url'].'">'.Display::return_icon('pixel.gif',get_lang('Download'),array('class' => 'actionplaceholdericon actionsavebackup')).'</a>';
	$download_corrected_paper = '<a href="download.php?file='.$corrected_url.'">'.Display::return_icon('pixel.gif',get_lang('Download'),array('class' => 'actionplaceholdericon actionsavebackup')).'</a>';

	echo '<table width="100%" border="0" cellpadding="5" cellspacing="5"><tr><td colspan="2"><span style="color:orange;font-weight:bold;">'.get_lang('MyPaper').'</span></td></tr>
	<tr><td width="30" align="right">'.get_lang('Paper').' : </td><td width="70%">'.$row['title'].'</td></tr>
	<tr><td width="30" align="right">'.get_lang('Summary').' : </td><td width="70%">'.$row['description'].'</td></tr>
	<tr><td width="30" align="right">'.get_lang('Author').' : </td><td width="70%">'.$row['author'].'</td></tr>
	<tr><td width="30" align="right">'.get_lang('Submittedon').' : </td><td width="70%">'.$submittedon.'</td></tr>
	<tr><td width="30" align="right">'.get_lang('DownloadPaper').' : </td><td width="70%">'.$download_paper.'</td></tr>
	<tr><td width="30" align="right" valign="top">'.get_lang('Remark').' : </td><td width="70%"><div class="quiz_content_actions" style="width:65%;height:60px;">'.$remark.'</div></td></tr>';
	if($row['qualificator_id'] == 1){
	echo '<tr><td width="30" align="right">'.get_lang('DownloadCorrectedPaper').' : </td><td width="70%">'.$download_corrected_paper.'</td></tr>';
	}
	echo '<tr><td colspan="2" style="padding-left:220px;"><div class="quiz_content_actions" style="width:110px;">Mark &nbsp;&nbsp;&nbsp;&nbsp;'.round($row['qualification'],2).'/'.round($row['weight'],2).'</div></td></tr></table>';

}

function move_paper($assignment_id, $paper_id){
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);

	$form = new FormValidator('move_paper', 'post',api_get_self().'?'.api_get_cidReq().'&amp;action=move_paper&amp;assignment_id='. intval($assignment_id).'&amp;id='.intval($paper_id));
	$form->addElement('header', '', get_lang('MovePaper'));
//	$sql = "SELECT id,title FROM $work_table WHERE filetype='folder' AND parent_id='0' AND id != ".$assignment_id." AND session_id = ".api_get_session_id();
	$sql = "SELECT id,title FROM $work_table WHERE filetype='folder' AND parent_id='0' AND id != ".intval($assignment_id);
	$res = Database::query($sql,__FILE__,__LINE__);
	$assignments = array();
//	$assignments[0] = '-- '.get_lang('SelectAssignment').' --';
	while($obj = Database::fetch_object($res))
	{
		$assignments[$obj->id] = $obj->title;
	}

	$sql = "SELECT title FROM $work_table WHERE id = ".intval($assignment_id);
	$result = Database::query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($result);

	$sql_paper = "SELECT title,description,url FROM $work_table WHERE parent_id = ".intval($assignment_id)." AND id = ".intval($paper_id);
	$result_paper = Database::query($sql_paper,__FILE__,__LINE__);
	$rowpaper = Database::fetch_array($result_paper);

	$path_parts = pathinfo('/'.$rowpaper['url']);
	$exist_filename = $path_parts['filename'].'.'.$path_parts['extension'];

	$form->addElement('static','assignment_title',get_lang('FromAssignment'),' :&nbsp;&nbsp;&nbsp;<span>'.$row['title'].'</span>');
	$form->addElement('static','paper_title',get_lang('PaperTitle'),' :&nbsp;&nbsp;&nbsp;<span>'.$rowpaper['title'].'</span>');
	$form->addElement('static','paper_description',get_lang('Summary'),' :&nbsp;&nbsp;&nbsp;<span>'.$rowpaper['description'].'</span>');
	$form->addElement('select', 'assignment', get_lang('MoveToAssignment'), $assignments);
	$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
	if($form -> validate()) {
		$movepaper = $form->exportValues();
		$to_assignmentid = $movepaper['assignment'];

		$sql_move = "SELECT url FROM $work_table WHERE id = ".$to_assignmentid;
		$res_move = Database::query($sql_move,__FILE__,__LINE__);
		$rowmove = Database::fetch_array($res_move);

		$path_parts = pathinfo('/'.$rowmove['url']);
		$newfolder = $path_parts['basename'];

		$newurl = 'work/'.$newfolder.'/'.$exist_filename;

		move(api_get_path(SYS_COURSE_PATH) . $_course['path']. '/' . $rowpaper['url'], api_get_path(SYS_COURSE_PATH) . $_course['path'].'/work/'.$newfolder);

		$sql = "UPDATE $work_table SET url = '".$newurl."', parent_id = ".$to_assignmentid." WHERE id = ".intval($paper_id);
		Database::query($sql,__FILE__,__LINE__);

		api_item_property_update($_course, 'work', $to_assignmentid, 'FileUpdated', api_get_user_id());

		echo '<script type="text/javascript">window.location.href = "'.api_get_self().'?'.api_get_cidReq().'&action=view_papers&assignment_id='.$to_assignmentid.'"</script>';
	}
	$form->display();

}

function delete_paper($paper_id){
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG			= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);

	$currentCourseRepositorySys = api_get_path(SYS_COURSE_PATH) . $_course["path"] . "/";

	$sql_url = "SELECT url,parent_id FROM $work_table WHERE id = ".intval($paper_id);
	$res_url = Database::query($sql_url,__FILE__,__LINE__);

	$sql = "DELETE FROM $work_table WHERE id = ".intval($paper_id);
	Database::query($sql,__FILE__,__LINE__);

	if($res_url){
		api_item_property_update($_course, 'work', $paper_id, 'DocumentDeleted', api_get_user_id());
		$rowurl = Database::fetch_array($res_url);
		$work = $rowurl['url'];

		require_once(api_get_path(LIBRARY_PATH).'/fileManage.lib.php');
		$extension = pathinfo($work, PATHINFO_EXTENSION);
		$basename_file = basename($work, '.'.$extension);
		$new_dir = $work.'_DELETED_'.$delete.'.'.$extension;

		if (api_get_setting('permanently_remove_deleted_files') == 'true'){
			my_delete($currentCourseRepositorySys.'/'.$work);
		} else {
			rename($currentCourseRepositorySys."/".$work, $currentCourseRepositorySys."/".$new_dir);
		}
	}
	echo '<script type="text/javascript">window.location.href = "'.api_get_self().'?'.api_get_cidReq().'&action=view_papers&assignment_id='.$rowurl['parent_id'].'"</script>';
}

function delete_assignment($delete_assignment_id) {
	global $_course;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);

	$currentCourseRepositorySys = api_get_path(SYS_COURSE_PATH) . $_course["path"] . "/";

	foreach ($delete_assignment_id as $index => $assignment_id) {
		$sql_url = "SELECT url FROM $work_table WHERE id = ".$assignment_id;
		$res_url = Database::query($sql_url,__FILE__,__LINE__);

		$sql = "DELETE FROM $TSTDPUBASG WHERE publication_id = ".$assignment_id;
		Database::query($sql,__FILE__,__LINE__);

		$sql = "DELETE FROM $work_table WHERE id = ".$assignment_id;
		Database::query($sql,__FILE__,__LINE__);

		$sql = "DELETE FROM $work_table WHERE parent_id = ".$assignment_id;
		Database::query($sql,__FILE__,__LINE__);

		if ($res_url) {
			api_item_property_update($_course, 'work', $paper_id, 'FolderDeleted', api_get_user_id());
			$rowurl = Database::fetch_array($res_url);
			$work = $rowurl['url'];

			$path = $currentCourseRepositorySys."work/".basename($work)."/";
			$path_dir = $currentCourseRepositorySys."work/";
                        if (is_dir($path)) {
                            $d = dir($path);
                            if (api_get_setting('permanently_remove_deleted_files') == 'true'){
                                    while (false !== $entry = $d->read()) {
                                            if ($entry == '.' || $entry == '..') continue;
                                            rmdirr($path.$entry);
                                    }
                            } else {
                                    while (false !== $entry = $d->read()) {
                                            if ($entry == '.' || $entry == '..' || substr($entry,0,8) == 'DELETED_') continue;
                                            $new_file='DELETED_'.$entry;
                                            rename($path.$entry, $path.$new_file);
                                    }
                                    $new_dir_name = 'DELETED_'.basename($work);
                                    rename($path_dir.basename($work), $path_dir.$new_dir_name);
                            }
                        }
		}
	}
	echo '<script type="text/javascript">window.location.href = "'.api_get_self().'?'.api_get_cidReq().'"</script>';
}

function send_mail_to_students($new_dir,$description,$deadline){
	global $_course,$_user;
	$session_id = api_get_session_id();
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$session_table = Database :: get_main_table(TABLE_MAIN_SESSION);
	$session_user_table = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$emailtemplate_table = Database :: get_main_table(TABLE_MAIN_EMAILTEMPLATES);

	$sql = "SELECT content FROM $emailtemplate_table WHERE description = 'Newassignment' AND language = '".api_get_interface_language()."'";
	$res = Database::query($sql,__FILE__,__LINE__);
	$num_rows = Database::num_rows($res);
	if($num_rows == 0){
	$sql = "SELECT content FROM $emailtemplate_table WHERE description = 'Newassignment' AND language = 'english'";
	$res = Database::query($sql,__FILE__,__LINE__);
	}
	$row = Database::fetch_array($res);
	$mail_content = $row['content'];

	$siteUrl = api_get_setting('InstitutionUrl');
	$subject = get_lang('CreatedNewAssignment');
        
        $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";

	if($session_id == 0){
		//Mail to all students of the course and the session user of this course + trainer of the session
		$sql_course = "SELECT usr.user_id FROM $user_table usr, $course_user_table cusr WHERE usr.user_id = cusr.user_id AND cusr.course_code = '".api_get_course_id()."'";
		$res_course = Database::query($sql_course,__FILE__,__LINE__);
		$course_user = array();
		while($row_course = Database::fetch_row($res_course)){
			$course_user[] = $row_course[0];
		}

		$sql_session = "SELECT usr.user_id FROM $user_table usr, $session_user_table susr WHERE usr.user_id = susr.id_user AND susr.course_code = '".api_get_course_id()."'";
		$res_session = Database::query($sql_session,__FILE__,__LINE__);
		while($row_session = Database::fetch_row($res_session)){
			$course_user[] = $row_session[0];
		}

		$course_user[] = get_all_group_tutors();
		$users_list = array();
		$users_list = array_unique($course_user);

		foreach($users_list as $users){
			$sql_user = "SELECT email,firstname,lastname FROM $user_table WHERE user_id = ".$users;
			$res_user = Database::query($sql_user,__FILE__,__LINE__);
			$row_user = Database::fetch_array($res_user);

			$recipient_name = api_get_person_name($row_user['firstname'], $row_user['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
			$sender_name = $_user['firstName'].' '.$_user['lastName'];

			$message = $mail_content;
                        $message = str_replace("/main/default_course_document", "tmp_file", $message);
			$message = str_replace("{Name}",$recipient_name,$message);
			$message = str_replace("{courseName}",api_get_course_id(),$message);
			$message = str_replace("{assignmentName}",$new_dir,$message);
			$message = str_replace("{assignmentDescription}",$description,$message);
			$message = str_replace("{assignmentDeadline}",$deadline,$message);
			$message = str_replace("{siteName}",$siteUrl,$message);
			$message = str_replace("{authorName}",$sender_name,$message);
                        $message = str_replace("tmp_file", $domain_server, $message);

			api_mail_html($recipient_name, $row_user['email'], $subject, $message, $sender_name, $_user['email']);
		}
	}
	else {
		//Mail to session users and trainer of the session.

		$sql_session = "SELECT usr.user_id,session.id_coach FROM $user_table usr, $session_user_table susr, $session_table session WHERE usr.user_id = susr.id_user AND susr.id_session = session.id AND susr.course_code = '".api_get_course_id()."' AND susr.id_session = ".api_get_session_id();
		$res_session = Database::query($sql_session,__FILE__,__LINE__);
		while($row_session = Database::fetch_row($res_session)){
			$session_user[] = $row_session[0];
			$session_user[] = $row_session[1];
		}

		$session_user[] = get_all_group_tutors();
		$users_list = array();
		$users_list = array_unique($session_user);

		foreach($users_list as $users){
			$sql_user = "SELECT email,firstname,lastname FROM $user_table WHERE user_id = ".$users;
			$res_user = Database::query($sql_user,__FILE__,__LINE__);
			$row_user = Database::fetch_array($res_user);

			$recipient_name = api_get_person_name($row_user['firstname'], $row_user['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
			$sender_name = $_user['firstName'].' '.$_user['lastName'];

			$message = $mail_content;
                        $message = str_replace("/main/default_course_document", "tmp_file", $message);
			$message = str_replace("{Name}",$recipient_name,$message);
			$message = str_replace("{courseName}",api_get_course_id(),$message);
			$message = str_replace("{assignmentName}",$new_dir,$message);
			$message = str_replace("{assignmentDescription}",$description,$message);
			$message = str_replace("{assignmentDeadline}",$deadline,$message);
			$message = str_replace("{siteName}",$siteUrl,$message);
			$message = str_replace("{authorName}",$sender_name,$message);
                        $message = str_replace("tmp_file", $domain_server, $message);

			//$message = str_replace('<br />',"\n\n", $message);
			api_mail_html($recipient_name, $row_user['email'], $subject, $message, $sender_name, $_user['email']);
		}
	}

}

function sendmail_paper_to_tutor($author,$paper_id,$user_id,$title,$assignment_title,$assignment_description,$deadline){
	$emailtemplate_table 		= Database :: get_main_table(TABLE_MAIN_EMAILTEMPLATES);
	$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
	$table_group 	= Database :: get_course_table(TABLE_GROUP);
	$table_group_tutor 	= Database :: get_course_table(TABLE_GROUP_TUTOR);
	$table_group_user 	= Database :: get_course_table(TABLE_GROUP_USER);
	$user_table 		= Database :: get_main_table(TABLE_MAIN_USER);

	$session_id = api_get_session_id();
	$sql = "SELECT content FROM $emailtemplate_table WHERE description = 'Submitwork' AND language = '".api_get_interface_language()."'";
	$res = Database::query($sql,__FILE__,__LINE__);
	$num_rows = Database::num_rows($res);
	if($num_rows == 0){
	$sql = "SELECT content FROM $emailtemplate_table WHERE description = 'Submitwork' AND language = 'english'";
	$res = Database::query($sql,__FILE__,__LINE__);
	}
	$row = Database::fetch_array($res);
	$mail_content = $row['content'];

	$siteUrl = api_get_setting('InstitutionUrl');
	$subject = get_lang('NewPaperSubmitted');
	$sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);

        $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
        
	//Send mail to trainer of the course.
	$sql = "SELECT DISTINCT grt.user_id AS tutor_id FROM $table_group gp,$table_group_user gru, $table_group_tutor grt WHERE gp.id=gru.group_id AND gp.id=grt.group_id AND gru.group_id=grt.group_id AND gru.user_id=".intval($user_id)." AND gp.session_id=".intval($session_id);
	$rs = Database :: query($sql,__FILE__,__LINE__);
	while($row = Database::fetch_array($rs)){
		$sql_user = "SELECT email,firstname,lastname FROM $user_table WHERE user_id = ".$row['tutor_id'];
		$res_user = Database::query($sql_user,__FILE__,__LINE__);
		$row_user = Database::fetch_array($res_user);

		$recipient_name = api_get_person_name($row_user['firstname'], $row_user['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);

		if(api_get_interface_language() == 'french'){
		$sent_date = date("d/m/y");
		}
		else {
		$sent_date = date("m/d/y");
		}

		$message = $mail_content;
                $message = str_replace("/main/default_course_document", "tmp_file", $message);
		$message = str_replace("{authorName}",$recipient_name,$message);
		$message = str_replace("{studentName}",$author,$message);
		$message = str_replace("{paperName}",$title,$message);
		$message = str_replace("{assignmentName}",$assignment_title,$message);
		$message = str_replace("{assignmentDescription}",$assignment_description,$message);
		$message = str_replace("{courseName}",api_get_course_id(),$message);
		$message = str_replace("{assignmentDeadline}",$deadline,$message);
		$message = str_replace("{assignmentSentDate}",$sent_date,$message);
		$message = str_replace("{siteName}",$siteUrl,$message);
		$message = str_replace("{administratorSurname}",$sender_name,$message);
                $message = str_replace("tmp_file", $domain_server, $message);
                
		api_mail_html($recipient_name, $row_user['email'], $subject, $message, $sender_name, api_get_setting('emailAdministrator'));
	}

}

function sendmail_correctpaper_tostudent($paper_id,$parent_id,$paper_title,$sent_date){
	global $_user;
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$TSTDPUBASG			= Database :: get_course_table(TABLE_STUDENT_PUBLICATION_ASSIGNMENT);
	$itemproperty_table = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$emailtemplate_table 		= Database :: get_main_table(TABLE_MAIN_EMAILTEMPLATES);

	$sql = "SELECT content FROM $emailtemplate_table WHERE description = 'Correctwork' AND language = '".api_get_interface_language()."'";
	$res = Database::query($sql,__FILE__,__LINE__);
	$num_rows = Database::num_rows($res);
	if($num_rows == 0){
	$sql = "SELECT content FROM $emailtemplate_table WHERE description = 'Correctwork' AND language = 'english'";
	$res = Database::query($sql,__FILE__,__LINE__);
	}
	$row = Database::fetch_array($res);
	$message = $row['content'];

	$siteUrl = api_get_setting('InstitutionUrl');
	$subject = get_lang('AssignmentCorrected');

	$sql_user = "SELECT ip.insert_user_id FROM $work_table work,$itemproperty_table ip WHERE work.id = ip.ref AND ip.tool = 'work' AND work.id = ".intval($paper_id);
	$res_user = Database::query($sql_user,__FILE__,__LINE__);
	$row_user = Database::fetch_row($res_user);

	$sql_work = "SELECT work.title,work.description,workassign.ends_on,ip.insert_user_id FROM $work_table work, $TSTDPUBASG workassign, $itemproperty_table ip WHERE work.id = workassign.publication_id AND work.id = ip.ref AND ip.tool = 'work' AND work.id = ".intval($parent_id);
	$res_work = Database::query($sql_work,__FILE__,__LINE__);
	$row_work = Database::fetch_row($res_work);
	$deadline = $row_work[2];
	$datetime = explode(" ", $deadline);
	$dateparts = explode("-", $datetime[0]);
	if(api_get_interface_language() == 'french'){
	$deadline = $dateparts[2].'/'.$dateparts[1].'/'.$dateparts[0];
	}
	else {
	$deadline = $dateparts[1].'/'.$dateparts[2].'/'.$dateparts[0];
	}

	$user_info = Database::get_user_info_from_id($row_user[0]);
	$recipient_name = api_get_person_name($user_info['firstname'], $user_info['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
	$sender_name = $_user['firstname'].' '.$_user['lastname'];

	$datetime = explode(" ", $sent_date);
	$dateparts = explode("-", $datetime[0]);
	$sent_date = $dateparts[1].'/'.$dateparts[2].'/'.$dateparts[0];

        $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
        $message = str_replace("/main/default_course_document", "tmp_file", $message);
	$message = str_replace("{studentName}",$recipient_name,$message);
	$message = str_replace("{paperName}",$paper_title,$message);
	$message = str_replace("{assignmentName}",$row_work[0],$message);
	$message = str_replace("{assignmentDescription}",$row_work[1],$message);
	$message = str_replace("{courseName}",api_get_course_id(),$message);
	$message = str_replace("{assignmentDeadline}",$deadline,$message);
	$message = str_replace("{assignmentSentDate}",$sent_date,$message);
	$message = str_replace("{siteName}",$siteUrl,$message);
	$message = str_replace("{authorName}",$sender_name,$message);
        $message = str_replace("tmp_file", $domain_server, $message);
        
	api_mail_html($recipient_name, $user_info['email'], $subject, $message, $sender_name, $_user['email']);

}

function get_grouptutor_users($tutor_id){
	$table_group_tutor 	= Database :: get_course_table(TABLE_GROUP_TUTOR);

	$sql = "SELECT group_id FROM $table_group_tutor WHERE user_id = ".intval($tutor_id);
	$res = Database::query($sql,__FILE__,__LINE__);
	$groups = array();
	while($row = Database::fetch_array($res)){
		$groups[] = $row['group_id'];
	}

	foreach($groups as $group_id){
		$users = array();
		$users = GroupManager :: get_subscribed_users($group_id);
		foreach($users as $user){
			$userids[] = $user['user_id'];
		}
	}
	$userids[] = api_get_user_id();
	$users_list = implode(",",$userids);

	return $users_list;
}

function get_all_group_tutors(){
	global $_course;
	$tbl_group = Database::get_course_table(TABLE_GROUP, $_course['dbName']);
	$tbl_group_tutor = Database::get_course_table(TABLE_GROUP_TUTOR, $_course['dbName']);
	$tutors = array();
	$sql_tutor = "SELECT DISTINCT(grt.user_id) FROM $tbl_group gp, $tbl_group_tutor grt WHERE gp.id = grt.group_id AND  gp.session_id = ".api_get_session_id();
	$rs_tutor = Database::query($sql_tutor,__FILE__,__LINE__);
	while($row_tutor = Database::fetch_row($rs_tutor)){
            $tutors[] = $row_tutor[0];
	}
	return $tutors;
}

function get_group_name($paper_id){
	$work_table 		= Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$itemproperty_table = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$table_group =	Database::get_course_table(TABLE_GROUP);
	$table_group_user =	Database::get_course_table(TABLE_GROUP_USER);

	$sql = "SELECT ip.insert_user_id AS user_id FROM $itemproperty_table ip, $work_table work WHERE work.id = ip.ref AND work.id =".intval($paper_id)." AND ip.tool = 'work'";
	$rs = Database::query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($rs);

	$sql = "SELECT name FROM $table_group gp, $table_group_user gru WHERE gp.id = gru.group_id AND gru.user_id = ".$row['user_id'];
	$result = Database::query($sql,__FILE__,__LINE__);
	$row_group = Database::fetch_array($result);

	return $row_group['name'];
}

function get_user_grouptutor($user_id){
	$table_group_user 	= Database :: get_course_table(TABLE_GROUP_USER);
	$table_group_tutor 	= Database :: get_course_table(TABLE_GROUP_TUTOR);

	$sql = "SELECT group_id FROM $table_group_user WHERE user_id = ".intval($user_id);
	$res = Database::query($sql,__FILE__,__LINE__);
	while($row = Database::fetch_array($res)){
		$group_id = $row['group_id'];
	}
	$tutors = get_group_tutorslist($group_id);

	$tutors[] = api_get_user_id();
	$tutors_list = implode(",",$tutors);

	return $tutors_list;
}

function get_group_tutorslist ($group_id) {
		$table_user = Database :: get_main_table(TABLE_MAIN_USER);
		$table_group_tutor = Database :: get_course_table(TABLE_GROUP_TUTOR);
		//$group_id = Database::escape_string($group_id);
		$sql = "SELECT `ug`.`id`, `u`.`user_id`
			FROM ".$table_user." u, ".$table_group_tutor." ug
			WHERE `ug`.`group_id`='".intval($group_id)."'
			AND `ug`.`user_id`=`u`.`user_id`";
		$db_result = Database::query($sql,__FILE__,__LINE__);
		$tutors = array ();
		while ($user = Database::fetch_object($db_result))
		{
			$tutors[] = $user->user_id;
		}
		return $tutors;
}

/**
 * Builds the form thats enables the user to
 * move a document from one directory to another
 * This function has been copied from the document/document.inc.php library
 *
 * @param array $folders
 * @param string $curdirpath
 * @param string $move_file
 * @return string html form
 */
function build_work_move_to_selector($folders,$curdirpath,$move_file,$group_dir='')
{
	//gets file title
	$tbl_work = Database::get_course_table(TABLE_STUDENT_PUBLICATION);
	$sql = "SELECT title FROM $tbl_work WHERE id ='".(int)$move_file."'";
	$result = Database::query($sql,__FILE__,__LINE__);
	$title = Database::fetch_row($result);
	global $gradebook;

	$form = '<form class="outer_form" name="move_to" action="'.api_get_self().'?action=move_to&amp;gradebook='.$gradebook.'" method="post">'."\n";
	$form .= '<div class="row"><div class="form_header">'.get_lang('MoveFile').'</div></div>';
	$form .= '<input type="hidden" name="move_file" value="'.$move_file.'" />'."\n";

	$form .= '<div class="row">
                                <table><tr><td>
					<span class="form_required">*</span>'.sprintf(get_lang('MoveXTo'),$title[0]).'
                                </td><td>';
	$form .= ' <select name="move_to">'."\n";

	//group documents cannot be uploaded in the root
	if($group_dir=='') {
		if($curdirpath!='/') {
			$form .= '<option value="/">/ ('.get_lang('Root').')</option>';
		}
		if (is_array($folders)) {
			foreach ($folders as $folder) {
				//you cannot move a file to:
				//1. current directory
				//2. inside the folder you want to move
				//3. inside a subfolder of the folder you want to move
				if(($curdirpath!=$folder) && ($folder!=$move_file) && (substr($folder,0,strlen($move_file)+1) != $move_file.'/')) {
					$form .= '<option value="'.$folder.'">'.$folder.'</option>'."\n";
				}
			}
		}
	} else {
		if ($curdirpath!='/') {
			$form .= '<option value="/">/ ('.get_lang('Root').')</option>';
		}
		foreach ($folders as $folder) {
			if (($curdirpath!=$folder) && ($folder!=$move_file) && (substr($folder,0,strlen($move_file)+1) != $move_file.'/')) {
				//cannot copy dir into his own subdir
				$display_folder = substr($folder,strlen($group_dir));
				$display_folder = ($display_folder == '')?'/ ('.get_lang('Root').')':$display_folder;
				$form .= '<option value="'.$folder.'">'.$display_folder.'</option>'."\n";
			}
		}
	}

	$form .= '</select>'."\n";
	$form .= '	</td>
			<td>';
	$form .= '

						<button type="submit" class="save" name="move_file_submit">'.get_lang('MoveFile').'</button>
				</td></tr></table></div>';
	$form .= '</form>';
	$form .= '<div style="clear: both; margin-bottom: 10px;"></div>';

	return $form;
}

/**
 * Get the path of a document in the student_publication table (path relative to the course directory)
 * @param	integer	Element ID
 * @return	string	Path (or -1 on error)
 */
function get_work_path($id)
{
	$table = Database::get_course_table(TABLE_STUDENT_PUBLICATION);
	$sql = "SELECT * FROM $table WHERE id=".intval($id);
	$res = Database::query($sql);
	if(Database::num_rows($res)!=1) {
		return -1;
	} else {
		$row = Database::fetch_array($res);
		return $row['url'];
	}
}

/**
 * Update the url of a work in the student_publication table
 * @param	integer	ID of the work to update
 * @param	string	Destination directory where the work has been moved (must end with a '/')
 * @return	-1 on error, sql query result on success
 */
function update_work_url($id,$new_path)
{
	if(empty($id)) return -1;
	$table = Database::get_course_table(TABLE_STUDENT_PUBLICATION);
	$sql = "SELECT * FROM $table WHERE id=".intval($id);
	$res = Database::query($sql);
	if(Database::num_rows($res)!=1) {
		return -1;
	} else {
		$row = Database::fetch_array($res);
		$filename = basename($row['url']);
		$new_url = $new_path.$filename;
		$sql2 = "UPDATE $table SET url = '$new_url' WHERE id=".intval($id);
		$res2 = Database::query($sql2);
		return $res2;
	}
}

/**
 * Return an array with all the folder's ids that are in the given path
 * @param	string Path of the directory
 * @return	array The list of ids of all the directories in the path
 * @author 	Julio Montoya Dokeos
 * @version April 2008
 */

function get_parent_directories($my_cur_dir_path) {
	$list_id = array();
	if (!empty($my_cur_dir_path)) {
            $list_parents = explode('/', $my_cur_dir_path);
            $dir_acum = '';
            global $work_table;
            for ($i = 0; $i < count($list_parents) - 1; $i++) {
                    $item = Database::escape_string($list_parents[$i]);
                    $where_sentence = "url  LIKE BINARY '" . $dir_acum . "/" . $item."'";
                    $dir_acum .= '/' . $list_parents[$i];
                    $sql = "SELECT id FROM ". $work_table . " WHERE ". $where_sentence;
                    $result = Database::query($sql, __FILE__, __LINE__);
                    $row= Database::fetch_array($result);
                    $list_id[]=$row['id'];
            }
	}
	return $list_id;
}

/**
 *
 */
function move_form($paper_id) {
    $work_table = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
    $folders = array();
    $sql = "SELECT url FROM $work_table  WHERE url LIKE '/%' AND post_group_id = '".(empty($_SESSION['toolgroup'])?0:intval($_SESSION['toolgroup']))."'";
    $res = Database::query($sql,__FILE__,__LINE__);
    while($folder = Database::fetch_array($res)) {
        $folders[] = substr($folder['url'],1,(strlen($folder['url'])-1));
    }
    echo build_work_move_to_selector($folders, '', $paper_id);
}

/**
 *
 */
function move_to($move_file, $moveurl_to) {
    global $_course, $base_work_dir;
    $work_table = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
    $move_to = $moveurl_to;
    if ($move_to == '/' or empty ($move_to)) { $move_to = ''; }
    elseif (substr($move_to, -1, 1) != '/') { $move_to = $move_to . '/'; }
    //security fix: make sure they can't move files that are not in the document table
    if ($path = get_work_path($move_file)) {
        if (move(api_get_path(SYS_COURSE_PATH) . $_course['path'] . '/' . $path, $base_work_dir . '/' . $move_to)) {
            //update db
            update_work_url(Security::remove_XSS($move_file), 'work/' . $move_to);
            //set the current path
            $cur_dir_path = $move_to;
            $cur_dir_path_url = urlencode($move_to);
            // update all the parents in the table item propery
            $list_id=get_parent_directories($cur_dir_path);
            for ($i = 0; $i < count($list_id); $i++) {
                api_item_property_update($_course, 'work', $list_id[$i], 'FolderUpdated', api_get_user_id());
            }
            // update parent_id
            $rs_parent = Database::query("SELECT id FROM $work_table WHERE url = '/".Database::escape_string($moveurl_to)."'");
            if (Database::num_rows($rs_parent) > 0) {
                $row_parent = Database::fetch_row($rs_parent);
                $parent_id = $row_parent[0];
                Database::query("UPDATE $work_table SET parent_id = $parent_id WHERE id = '".intval($move_file)."'");
            }
            Display :: display_confirmation_message(get_lang('DirMv'), true, true);
        } else {
            Display :: display_error_message(get_lang('Impossible'), true, true);
        }
    } else {
            Display :: display_error_message(get_lang('Impossible'), true, true);
    }
}

/**
 * Gets the id of a student publication with a given path
 * @param string $path
 * @return The id if is found / false if not found
 */
function get_folder_work_id($path) {
	$TBL_STUDENT_PUBLICATION = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
	$rs = Database::query("SELECT id FROM $TBL_STUDENT_PUBLICATION WHERE url LIKE '/".Database::escape_string($path)."%' AND filetype='folder'");
	if (Database::num_rows($rs) > 0) {
                $row = Database::fetch_row($rs);
		return $row[0];
	} else {
		return false;
	}
}
