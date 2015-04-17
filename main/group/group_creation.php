<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.group
==============================================================================
*/

// name of the language file that needs to be included
$language_file = "group";

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'classmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');


// the section (for the tabs)
$this_section = SECTION_COURSES;

// temporary style definition
$htmlHeadXtra[] = '<style type="text/css">
    .pr5{padding-right: 5px;}
    .pl5{padding-left: 5px;}
	.columns2 {width: 45%; min-height: 150px;}
	.gradient {border: 1px solid grey;}
	#groupcalculator {float: left;clear:none;}
	#group_to_create {float: left;clear:none;}
	.sectioncontent{ padding: 5px;}
	.selectscenario{float: right;}
	</style>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/msdropdown/css/dd.css" type="text/css" media="screen" />';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/msdropdown/js/jquery.dd.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
			$(document).ready(function() {
                            $("#number_of_groups").msDropdown({visibleRows:10, rowHeight:16});
                            $("#number_of_users_per_group").msDropdown({visibleRows:10, rowHeight:16});
				var number_of_groups = $("#number_of_groups").val();
                            var number_of_users_per_group = $("#number_of_users_per_group").val();
                            $.ajax({
                                            url: "ajax.php",
                                            data: {action: "group_name_form_elements", number_of_groups: number_of_groups, number_of_users_per_group: number_of_users_per_group, cidReq:"'.Security::remove_XSS($_GET['cidReq']).'"},
                                            success: function(data){
                                                    $("#group_to_create .sectioncontent").html(data);
                                                    }
                                    });
				
                            $("#number_of_groups, #number_of_users_per_group").change(function() {
                                var number_of_groups = $("#number_of_groups").val();
				var number_of_users_per_group = $("#number_of_users_per_group").val();
	
				$.ajax({
					  url: "ajax.php",
					  data: {action: "group_name_form_elements", number_of_groups: number_of_groups, number_of_users_per_group: number_of_users_per_group, cidReq:"'.Security::remove_XSS($_GET['cidReq']).'"},
					  success: function(data){
						$("#group_to_create .sectioncontent").html(data);
						}
				});

                            });


                                function group_to_create(){
                                
                                    // number of groups that need to be created
                                    var number_of_groups = $("#number_of_groups").val();

                                    // number of users per group
                                    var number_of_users_per_group = $("#number_of_users_per_group").val();

                                    

                                }

				

				$(".groupcategory").hover(
					function (){
						// display the submit button
						$(".selectscenario",this).toggle();
						// change the style of the group category (scenario)
						$(this).toggleClass("groupcategoryselected");
						// store the id of the group category (scenario) in the form						
						var cat_id = $(this).attr("id").replace("scenario","");
						$("#selected_group_category").val(cat_id);
					},
					function (){
						// hide the submit button
						$(".selectscenario",this).toggle();
						// change the style of the group category (scenario)
						$(this).toggleClass("groupcategoryselected");
						// store the id of the group category (scenario) in the form						
						$("#selected_group_category").val("");
						}				
				);


			});
		</script>';

// access control
api_protect_course_script(true);
if (!api_is_allowed_to_edit(false,true))
{
	api_not_allowed();
}

// tracking
event_access_tool('group_creation');

// name of the tool
$nameTools = get_lang('GroupManagement');

// header actions
group_header_actions();

// display the header
Display::display_tool_header(get_lang('Groups'));
Display::display_introduction_section(TOOL_USER, 'left');
// action handling
//group_actions();

// action links
display_actions();

$newgroupseats = intval(api_get_setting('new_group_seats'));
$new_group_seats = (!empty($newgroupseats)) ?  ($newgroupseats) : (20);
$default_number_of_users_per_group = (5 < $newgroupseats ) ? (5) : (1);
//start the content div
echo '<div id="content">';

// display the groups
display_group_creation($new_group_seats,$default_number_of_users_per_group);

// close the content div
echo '</div>';
// display the footer
Display :: display_footer();
exit;

function group_header_actions(){
	global $form;
	switch ($_GET['action']){
		case 'save_groups2':
			// unserializing the group names and the number of users per group
			$group_names = unserialize(str_replace('*','"',$_POST['groupnames']));
			$userspergroup = unserialize(str_replace('*','"',$_POST['userspergroup']));

			// creating the groups
			$number_of_created_groups = create_groups($group_names,$userspergroup,$_POST['selected_group_category']);
			header('Location: group.php?'.api_get_cidreq().'&action=display_message&msg=GroupsCreated&number_of_groups='.$number_of_created_groups);
			break;
		case 'save_groups':
			// building the form to create a new group category			
			build_group_scenario_form();

			if ($form->validate()){
				// exporting the values of the form
				$values = $form->exportValues();

				// unserializing the group names and the number of users per group
				$group_names = unserialize(str_replace('*','"',$values['groupnames']));
				$userspergroup = unserialize(str_replace('*','"',$values['userspergroup']));  
				$group_category = $values['scenario'];

				// creating the groups in this newly created category
				$number_of_created_groups = create_groups($group_names,$userspergroup,$group_category);
                                $_SESSION["display_confirmation_message"] = get_lang('langGroupCreation');
				header('Location: group.php?'.api_get_cidreq().'&action=display_message&msg=GroupsCreated&number_of_groups='.$number_of_created_groups);
			}
			break;
	}
}


function display_group_creation($new_group_seats,$default_number_of_users_per_group){
	global $form;

	if (!$_GET['action']){
		echo '<form action="group_creation.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'" id="group_calculator" name="group_calculator" method="post" >';
		echo '<div id="groupcalculator" class="section columns2">';
		echo '	<div class="sectiontitle">';
		echo    Display::return_icon('pixel.gif', get_lang('GroupCalculator'), array ('class' => 'toolactionplaceholdericon toolactioncalculator')).' '. get_lang('GroupCalculator');
		echo '	</div>';
		echo '	<div class="sectioncontent">';
		echo 	group_calculator($new_group_seats,$default_number_of_users_per_group);
		echo '	</div>';
		echo '</div>';
		echo '</form>';

		echo '<div id="group_to_create" class="section columns2">';
		echo '	<div class="sectiontitle">';
		echo 	Display::return_icon('pixel.gif',get_lang('GroupUsers'),array('class' => 'toolactionplaceholdericon toolactionunknown')).' '. get_lang('GroupUsers');
		echo '	</div>';
		echo '	<div class="sectioncontent">';
		echo '	</div>';
		echo '</div>';
	} elseif ($_GET['action'] == 'save_groups'){
		
		echo '<div id="scenario_custom" class="section">';
		echo '	<div class="sectiontitle">';
		echo 	get_lang('CreateNewScenario');
		echo '	</div>';
		echo '	<div class="sectioncontent">';
//		echo 		display_group_category_form();
		echo   display_group_scenario_form();
		echo '	</div>';
		echo '</div>';
	}
}

function display_group_scenario_form(){
	global $form;
	$form->display();
}

function build_group_scenario_form(){
	global $form;

	$form = new FormValidator('group_category','post','?'.api_get_cidreq().'&action=save_groups');
	$form->addElement('hidden', 'groupnames');
	$form->addElement('hidden', 'userspergroup');
	$defaults['groupnames']=str_replace('"','*',serialize($_POST['group_name']));	
	$defaults['userspergroup']=str_replace('"','*',serialize($_POST['users_of_group']));

/*	$form->addElement('radio', 'scenario', null, get_lang('Tutoring').'<br/><span style="padding-left:20px;">'.get_lang('ScenarioText1').'</span>'.'<br/>'.'<span style="padding-left:20px;">'.get_lang('ScenarioTools1').'</span>', 1);
	$form->addElement('radio', 'scenario', null, get_lang('Collaboration').'<br/><span style="padding-left:20px;">'.get_lang('ScenarioText2').'</span>'.'<br/>'.'<span style="padding-left:20px;">'.get_lang('ScenarioTools2').'</span>', 2);
	$form->addElement('radio', 'scenario', null, get_lang('Competition').'<br/><span style="padding-left:20px;">'.get_lang('ScenarioText3').'</span>'.'<br/>'.'<span style="padding-left:20px;">'.get_lang('ScenarioTools3').'</span>', 3);*/


// add image to table
        
        
	$form->addElement('radio', 'scenario', null, '<b>'.get_lang('Tutoring').'</b>', 1);
      //$form->addElement('html','<table width="100%" border="0"><tr><td width="15%" text-align=""></td><td>'.get_lang('ScenarioText1').'</td></tr>'.'<tr><td width="15%">&nbsp;</td><td>'.get_lang('ScenarioTools1').'</td></tr></table>');//
        $form->addElement('html','<table width="100%" border="0"><tr><td width="21%">&nbsp;</td><td width="60%" valign="middle">'.get_lang('ScenarioText1').'<br />'.get_lang('ScenarioTools1').'</td><td width="200" align="right">'.Display::return_icon('scenario1.png').'</td></tr>  '.'</table>');
        
	$form->addElement('radio', 'scenario', null, '<b>'.get_lang('Collaboration').'</b>', 2);
      //$form->addElement('html','<table width="100%" border="0"><tr><td width="15%">&nbsp;</td><td>'.get_lang('ScenarioText2').'</td></tr>'.'<tr><td width="15%">&nbsp;</td><td>'.get_lang('ScenarioTools2').'</td></tr></table>');
        $form->addElement('html','<table width="100%" border="0"><tr><td width="21%">&nbsp;</td><td width="60%" valign="middle">'.get_lang('ScenarioText2').'<br />'.get_lang('ScenarioTools2').'</td><td width="200" align="right">'.Display::return_icon('scenario2.png').'</td></tr>  '.'</table>');
        
       	$form->addElement('radio', 'scenario', null, '<b>'.get_lang('Competition').'</b>', 3);
      //$form->addElement('html','<table width="100%" border="0"><tr><td width="15%">&nbsp;</td><td>'.get_lang('ScenarioText3').'</td></tr>'.'<tr><td width="15%">&nbsp;</td><td>'.get_lang('ScenarioTools3').'</td></tr></table>');
	$form->addElement('html','<table width="100%" border="0"><tr><td width="21%">&nbsp;</td><td width="60%" valign="middle">'.get_lang('ScenarioText3').'<br />'.get_lang('ScenarioTools3').'</td><td width="200" align="right">'.Display::return_icon('scenario3.png').'</td></tr>  '.'</table>');
        
        $form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');

	$defaults['scenario'] = 1;
	$form->setDefaults($defaults);	
}

function group_calculator($new_group_seats,$default_number_of_users_per_group){
	// default value
        $number_of_groups = (empty($_POST['number_of_groups'])) ? 1 : $_POST['number_of_groups'];
        $number_of_users_per_group = (empty($_POST['number_of_users_per_group'])) ? $default_number_of_users_per_group : $_POST['number_of_users_per_group'];
        //var txt
        $txt_Create = get_lang('Create');
        $txt_GroupsOf = get_lang('GroupsOf');
        $txt_Persons = get_lang('Persons');

	echo '<span class="pr5">'.$txt_Create.'</span>';        
	echo '<select name="number_of_groups" id="number_of_groups">';
        for ($i = 1; $i <= $new_group_seats; $i++) {
            $selected = ($i == $number_of_groups) ? ' selected="selected" ' : '';
            echo '<option value="'.$i.'" '.$selected.'> '.$i.'</option>';
        }
	echo '</select>';
        
	echo '<span class="pl5 pr5">'.$txt_GroupsOf.'</span>';        
	echo '<select name="number_of_users_per_group" id="number_of_users_per_group">';        
        for($i=1; $i <= $new_group_seats;$i++){
            $selected = ($i == $number_of_users_per_group) ? ' selected="selected" ' : '';
            echo '<option value="'.$i.'" '.$selected.' >'.$i.'</option>';
        }              
	echo '</select>';
	echo '<span class="pl5">'.$txt_Persons.'</span>';
}

function display_actions(){

	if (api_is_allowed_to_edit(false,true))
	{
		echo '<div class="actions">';
		echo '<a href="group.php?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif',get_lang('Groups'),array('class' => 'toolactionplaceholdericon toolactiongroupimage')).get_lang('Groups').'</a>';
		echo '</div>';
	}
}


/**
 * Function to check the given max number of members per group 
 */
function check_max_number_of_members($value)
{
	$max_member_no_limit = $value['max_member_no_limit'];
	if( $max_member_no_limit == MEMBER_PER_GROUP_NO_LIMIT)
	{
		return true;	
	}
	$max_member = $value['max_member'];
	return is_numeric($max_member);
}

function create_groups($group_names, $userspergroup, $group_category){
	$counter = 0;
	foreach ($group_names as $key=>$groupname){
	//	$created_groups[] = GroupManager::create_group($groupname,$group_category,'',$userspergroup[$key]);
		$created_groups[] = create_new_group($groupname,$group_category,'',$userspergroup[$key]);
		$counter++;
	}
	return $counter;
}

function create_new_group($name, $category_id, $tutor, $places){
	global $_course,$_user;

	// Database table initialisation
	$table_group = Database :: get_course_table(TABLE_GROUP);
	$table_forums = Database::get_course_table(TABLE_FORUM);

	GroupManager::update_group_category();	

	$sql = "SELECT * FROM $table_group WHERE name = '".$name."'";
	$rs = Database::query($sql,__FILE__,__LINE__);
	$num_rows = Database::num_rows($rs);
	if($num_rows <> 0){
		$sql = "SELECT * FROM $table_group WHERE name like '".get_lang('Group')." %' ORDER BY id";
		$rs = Database::query($sql,__FILE__,__LINE__);
		if (Database::num_rows($rs) == 0) {
			$group_no = 1;		
			$new_group_name = get_lang('Group').' '.$group_no;
		}
		else {
			while($row = Database::fetch_array($rs)){
				$group_name = $row['name'];
			}							
			list($grp_name,$grp_id) = split(' ',$group_name);
			$new_grp_id = $grp_id + 1;		
			$new_group_name = get_lang('Group').' '.$new_grp_id;
		}
		$check = Database::query("SELECT * FROM $table_group WHERE name = '".$new_group_name."'");
		if(Database::num_rows($check) == 0){		
		$name = $new_group_name;
		}	
	}	
	
	isset($_SESSION['id_session'])?$my_id_session = intval($_SESSION['id_session']):$my_id_session=0;
	$currentCourseRepository = $_course['path'];

	$sql = "INSERT INTO ".$table_group." SET
				category_id='".Database::escape_string($category_id)."', max_student = '".$places."', work_state = 1, doc_state = 1, forum_state = 1, wiki_state = 1, session_id='".Database::escape_string($my_id_session)."'";
	Database::query($sql,__FILE__,__LINE__);
	$lastId = Database::insert_id();
	
	$desired_dir_name= '/'.replace_dangerous_char($name,'strict').'_groupdocs';
	$dir_name = create_unexisting_directory($_course,$_user['user_id'],$lastId,NULL,api_get_path(SYS_COURSE_PATH).$currentCourseRepository.'/document',$desired_dir_name);
	/* Stores the directory path into the group table */
	$sql = "UPDATE ".$table_group." SET   name = '".Database::escape_string($name)."', secret_directory = '".$dir_name."' WHERE id ='".$lastId."'";
	Database::query($sql,__FILE__,__LINE__);	

	$sql="SELECT MAX(forum_order) as sort_max FROM ".$table_forums." WHERE forum_category=0";
	$result=Database::query($sql,__FILE__,__LINE__);
	$row=Database::fetch_array($result);
	$new_max=$row['sort_max']+1;

	if(empty($new_max)){
		$new_max = null;
	}

	$sql = "INSERT INTO ".$table_forums."
		(forum_title, forum_category, allow_anonymous, allow_edit, approval_direct_post, allow_attachments, allow_new_threads, default_view, forum_of_group, forum_group_public_private, forum_order, session_id)
		VALUES ('".Database::escape_string($name)."', 0, 0, 0, 0, 1, 1, '".api_get_setting('default_forum_view')."', '".$lastId."', 'public', ".$new_max.", ".$my_id_session.")";
	Database::query($sql,__FILE__,__LINE__);
	$last_id = Database::insert_id();
	if ($last_id > 0) {
		api_item_property_update($_course, TOOL_FORUM, $last_id, 'ForumAdded', api_get_user_id());
	}
	
	return $lastId;
}


// CODE BELOW IS OLD CODE 

/*
--------------------------------------
        Create the groups
--------------------------------------
*/
if (isset ($_POST['action']))
{
	switch ($_POST['action'])
	{
		case 'create_groups' :
			$groups = array ();

			for ($i = 0; $i < $_POST['number_of_groups']; $i ++)
			{
				$group1['name'] = api_strlen($_POST['group_'.$i.'_name']) == 0 ? get_lang('Group').' '.$i : $_POST['group_'.$i.'_name'] ;
				$group1['category'] = isset($_POST['group_'.$i.'_category'])?$_POST['group_'.$i.'_category']:null;
				$group1['tutor'] = isset($_POST['group_'.$i.'_tutor'])?$_POST['group_'.$i.'_tutor']:null;
				$group1['places'] = isset($_POST['group_'.$i.'_places'])?$_POST['group_'.$i.'_places']:null;
				$groups[] = $group1;
			}

			foreach ($groups as $index => $group)
			{
				if (!empty($_POST['same_tutor']))
				{
					$group['tutor'] = $_POST['group_0_tutor'];
				}
				if (!empty($_POST['same_places']))
				{
					$group['places'] = $_POST['group_0_places'];
				}
				if (api_get_setting('allow_group_categories') == 'false')
				{
					$group['category'] = DEFAULT_GROUP_CATEGORY;
				}
				elseif ($_POST['same_category'])
				{
					$group['category'] = $_POST['group_0_category'];
				}
				GroupManager :: create_group(strip_tags($group['name']),$group['category'],$group['tutor'] , $group['places']);
			}
			$msg = urlencode(count($groups).' '.get_lang('GroupsAdded'));
			header('Location: group.php?action=show_msg&msg='.$msg);
			break;
		case 'create_subgroups' :
			GroupManager :: create_subgroups($_POST['base_group'], $_POST['number_of_groups']);
			$msg = urlencode($_POST['number_of_groups'].' '.get_lang('GroupsAdded'));
			header('Location: group.php?action=show_msg&msg='.$msg);
			break;
		case 'create_class_groups' :
			$ids = GroupManager :: create_class_groups($_POST['group_category']);
			$msg = urlencode(count($ids).' '.get_lang('GroupsAdded'));
			header('Location: group.php?action=show_msg&msg='.$msg);
			break;
	}
}
$nameTools = get_lang('GroupCreation');
$interbreadcrumb[] = array ("url" => "group.php", "name" => get_lang('Groups'));
Display :: display_header($nameTools, "Group");

if (!api_is_allowed_to_edit(false,true))
{
	api_not_allowed();
}
/*
===============================================================================
       MAIN TOOL CODE
===============================================================================
*/
/*
--------------------------------------
        Show group-settings-form
--------------------------------------
*/
elseif (isset ($_POST['number_of_groups']))
{
	if (!is_numeric($_POST['number_of_groups']) || intval($_POST['number_of_groups']) < 1)
	{
		Display :: display_error_message(get_lang('PleaseEnterValidNumber').'<br/><br/><a href="group_creation.php?'.api_get_cidreq().'">&laquo; '.get_lang('Back').'</a>',false);
	}
	else
	{
	$number_of_groups = intval($_POST['number_of_groups']);
	if ($number_of_groups > 1)
	{
		
?>
	<script type="text/javascript">
	var number_of_groups = <?php echo $number_of_groups; ?>;
	function switch_state(key)
	{
		for( i=1; i<number_of_groups; i++)
		{
			element = document.getElementById(key+'_'+i);
			element.disabled = !element.disabled;
			disabled = element.disabled;
		}
		ref = document.getElementById(key+'_0');
		if( disabled )
		{
			ref.addEventListener("change", copy, false);
		}
		else
		{
			ref.removeEventListener("change", copy, false);
		}
		copy_value(key);
	}
	function copy(e)
	{
		key = e.currentTarget.id;
		var re = new RegExp ('_0', '') ;
		var key = key.replace(re, '') ;
		copy_value(key);
	}
	function copy_value(key)
	{
		ref = document.getElementById(key+'_0');
		for( i=1; i<number_of_groups; i++)
		{
			element = document.getElementById(key+'_'+i);
			element.value = ref.value;
		}
	}
	</script>
	<?php


	}
	$group_categories = GroupManager :: get_categories();
	$group_id = GroupManager :: get_number_of_groups() + 1;
	/*$tutors = GroupManager :: get_all_tutors();
	$tutor_options[0] = get_lang('GroupNoTutor');
	foreach ($tutors as $index => $tutor)
	{
		$tutor_options[$tutor['user_id']] = api_get_person_name($tutor['firstname'], $tutor['lastname']);
	}
	$cat_options = array ();
	*/
	foreach ($group_categories as $index => $category)
	{
		$cat_options[$category['id']] = $category['title'];
	}
	$form = new FormValidator('create_groups_step2');

	// Modify the default templates
	$renderer = & $form->defaultRenderer();
	$form_template = "<form {attributes}>\n<div>\n<table>\n{content}\n</table>\n</div>\n</form>";
	$renderer->setFormTemplate($form_template);
	$element_template = <<<EOT
	<tr>
		<td>
			<!-- BEGIN required --><span class="form_required">*</span> <!-- END required -->{label}
		</td>
		<td>
			<!-- BEGIN error --><span class="form_error">{error}</span><br /><!-- END error -->	{element}
		</td>
	</tr>

EOT;
	$renderer->setElementTemplate($element_template);
	$form->addElement('header', '', $nameTools);

	$form->addElement('hidden', 'action');
	$form->addElement('hidden', 'number_of_groups');
	$defaults = array ();
	// Table heading
	$group_el = array ();
	$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupName').'</b>');
	if (api_get_setting('allow_group_categories') == 'true')
	{
		$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupCategory').'</b>');
	}
	//$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupTutor').'</b>');
	$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupPlacesThis').'</b>');
	$form->addGroup($group_el, 'groups', null, "\n</td>\n<td>\n", false);
	// Checkboxes
	if ($_POST['number_of_groups'] > 1)
	{
		$group_el = array ();
		$group_el[] = & $form->createElement('static', null, null, ' ');
		if (api_get_setting('allow_group_categories') == 'true')
		{
			$group_el[] = & $form->createElement('checkbox', 'same_category', null, get_lang('SameForAll'), array ('onclick' => "javascript:switch_state('category')"));
		}
		//$group_el[] = & $form->createElement('checkbox', 'same_tutor', null, get_lang('SameForAll'), array ('onclick' => "javascript:switch_state('tutor')"));
		$group_el[] = & $form->createElement('checkbox', 'same_places', null, get_lang('SameForAll'), array ('onclick' => "javascript:switch_state('places')"));
		$form->addGroup($group_el, 'groups', null, '</td><td>', false);
	}
	// Properties for all groups
	for ($group_number = 0; $group_number < $_POST['number_of_groups']; $group_number ++)
	{
		$group_el = array ();
		$group_el[] = & $form->createElement('text', 'group_'.$group_number.'_name');
		if (api_get_setting('allow_group_categories') == 'true')
		{
			$group_el[] = & $form->createElement('select', 'group_'.$group_number.'_category', null, $cat_options, array ('id' => 'category_'.$group_number));
		}
		//$group_el[] = & $form->createElement('select', 'group_'.$group_number.'_tutor', null, $tutor_options, array ('id' => 'tutor_'.$group_number));
		$group_el[] = & $form->createElement('text', 'group_'.$group_number.'_places', null, array ('size' => 3, 'id' => 'places_'.$group_number));
		

		if($_POST['number_of_groups']<10000)
		{
			if ($group_id<10)
			{
				$prev='000';
			}
			elseif ($group_id<100)
			{
				$prev='00';
			}
			elseif ($group_id<1000)
			{
				$prev='0';
			}
			else
			{
				$prev='';
			}
		}		
				
		$defaults['group_'.$group_number.'_name'] = get_lang('GroupSingle').' '.$prev.$group_id ++;		
		
		$form->addGroup($group_el, 'group_'.$group_number, null, '</td><td>', false);
	}
	$defaults['action'] = 'create_groups';
	$defaults['number_of_groups'] = $_POST['number_of_groups'];
	$form->setDefaults($defaults);
	$form->addElement('style_submit_button', 'submit', get_lang('CreateGroup'), 'class="save"');
	$form->display();
	}
}
else
{
	/*
	 * Show form to generate new groups
	 */
	$categories = GroupManager :: get_categories();
	if (count($categories) > 1 || isset ($categories[0]))
	{
		$create_groups_form = new FormValidator('create_groups');
		$create_groups_form->addElement('header', '', $nameTools);
		$group_el = array ();
		$group_el[] = & $create_groups_form->createElement('static', null, null, get_lang('Create'));
		$group_el[] = & $create_groups_form->createElement('text', 'number_of_groups', null, array ('size' => 3));
		$group_el[] = & $create_groups_form->createElement('static', null, null, get_lang('NewGroups'));
		$group_el[] = & $create_groups_form->createElement('style_submit_button', 'submit', get_lang('ProceedToCreateGroup'), 'class="save"');
		$create_groups_form->addGroup($group_el, 'create_groups', null, ' ', false);
		$defaults = array ();
		$defaults['number_of_groups'] = 1;
		$create_groups_form->setDefaults($defaults);
		$create_groups_form->display();
	}
	else
	{
		echo get_lang('NoCategoriesDefined');
	}

	/*
	 * Show form to generate subgroups
	 */
	if (api_get_setting('allow_group_categories') == 'true' && count(GroupManager :: get_group_list()) > 0)
	{
            
		$base_group_options = array ();
		$groups = GroupManager :: get_group_list();
		foreach ($groups as $index => $group)
		{
			$number_of_students = GroupManager :: number_of_students($group['id']);
			if ($number_of_students > 0)
			{
				$base_group_options[$group['id']] = $group['name'].' ('.$number_of_students.' '.get_lang('Users').')';
			}
		}
		if (count($base_group_options) > 0)
		{
			echo '<b>'.get_lang('CreateSubgroups').'</b>';
			echo '<blockquote>';
			echo '<p>'.get_lang('CreateSubgroupsInfo').'</p>';
			$create_subgroups_form = new FormValidator('create_subgroups');
			$create_subgroups_form->addElement('hidden', 'action');
			$group_el = array ();
			$group_el[] = & $create_subgroups_form->createElement('static', null, null, get_lang('CreateNumberOfGroups'));
			$group_el[] = & $create_subgroups_form->createElement('text', 'number_of_groups', null, array ('size' => 3));
			$group_el[] = & $create_subgroups_form->createElement('static', null, null, get_lang('WithUsersFrom'));
			$group_el[] = & $create_subgroups_form->createElement('select', 'base_group', null, $base_group_options);
			$group_el[] = & $create_subgroups_form->createElement('submit', 'submit', get_lang('Ok'));
			$create_subgroups_form->addGroup($group_el, 'create_groups', null, ' ', false);
			$defaults = array ();
			$defaults['action'] = 'create_subgroups';
			$create_subgroups_form->setDefaults($defaults);
			$create_subgroups_form->display();
			echo '</blockquote>';
		}
	}
	/*
	 * Show form to generate groups from classes subscribed to the course
	 */
	$classes = ClassManager :: get_classes_in_course($_course['sysCode']);
	if (count($classes) > 0)
	{
		echo '<b>'.get_lang('GroupsFromClasses').'</b>';
		echo '<blockquote>';
		echo '<p>'.get_lang('GroupsFromClassesInfo').'</p>';
		echo '<ul>';
		foreach ($classes as $index => $class)
		{
			$number_of_users = count(ClassManager :: get_users($class['id']));
			echo '<li>';
			echo $class['name'];
			echo ' ('.$number_of_users.' '.get_lang('Users').')';
			echo '</li>';
		}
		echo '</ul>';

		$create_class_groups_form = new FormValidator('create_class_groups_form');
		$create_class_groups_form->addElement('hidden', 'action');
		if (api_get_setting('allow_group_categories') == 'true')
		{
			$group_categories = GroupManager :: get_categories();
			$cat_options = array ();
			foreach ($group_categories as $index => $category)
			{
				$cat_options[$category['id']] = $category['title'];
			}
			$create_class_groups_form->addElement('select', 'group_category', null, $cat_options);
		}
		else
		{
			$create_class_groups_form->addElement('hidden', 'group_category');
		}
		$create_class_groups_form->addElement('submit', 'submit', get_lang('Ok'));
		$defaults['group_category'] = DEFAULT_GROUP_CATEGORY;
		$defaults['action'] = 'create_class_groups';
		$create_class_groups_form->setDefaults($defaults);
		$create_class_groups_form->display();
		echo '</blockquote>';
	}
}
// display the footer
Display :: display_footer();