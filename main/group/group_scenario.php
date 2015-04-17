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

$this_section = SECTION_COURSES;

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// the section (fro the tabs)
$this_section = SECTION_COURSES;

// get all the information of the group
$current_group = GroupManager :: get_group_properties($_SESSION['_gid']);

$nameTools = get_lang('EditGroup');

// breadcrumbs
$interbreadcrumb[] = array ("url" => "group.php", "name" => get_lang('Groups'));

// access restriction
if (!api_is_allowed_to_edit(false,true)) {
	api_not_allowed(true);
}


/*
==============================================================================
		MAIN CODE
==============================================================================
*/


// display the header
Display :: display_header($nameTools, "Group");
Display::display_introduction_section(TOOL_USER, 'left');
?>
<script src="<?php echo api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js'; ?>" language="javascript"></script>
<div class="actions">
 <a href="group.php?<?php echo api_get_cidreq(); ?>"><?php  echo Display::return_icon('pixel.gif', get_lang('ReturnTo').' '.get_lang('GroupSpace'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('ReturnTo').' '.get_lang('Groups') ?></a>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#submit").live("click",function() {            
            $("#group_category").ajaxForm({
                type: "POST",
                url: "<?php echo api_get_path(WEB_AJAX_PATH) . 'saveGroupData.ajax.php?action=save&group_id='.$_GET['group_id']; ?>",                          
                success: function(){
                    window.location.href = "group.php";  
                }                                                       
            }).submit();            
        });
    });
</script>
<?php
//start the content div
echo '<div id="content">';

$table_group = Database :: get_course_table(TABLE_GROUP);
$sql = "SELECT * FROM $table_group WHERE id = ".$_REQUEST['group_id'];
$rs = Database::query($sql,__FILE__,__LINE__);
$row = Database::fetch_array($rs);

echo '<div id="scenario_custom" class="section">';
		echo '	<div class="sectiontitle">';
		echo 	get_lang('CreateNewScenario');
		echo '	</div>';
		echo '	<div class="sectioncontent">';
		$form = new FormValidator('group_category','post');
		$form->addElement('hidden', 'groupnames');
		$form->addElement('hidden', 'userspergroup');
		$defaults['groupnames']=str_replace('"','*',serialize($_POST['group_name']));
		$defaults['userspergroup']=str_replace('"','*',serialize($_POST['users_of_group']));

		$form->addElement('radio', 'scenario', null, '<b>'.get_lang('Tutoring').'</b>', 1);
		$form->addElement('html','<table width="100%" border="0"><tr><td width="21%">&nbsp;</td><td width="60%" valign="middle">'.get_lang('ScenarioText1').'<br />'.get_lang('ScenarioTools1').'</td><td width="200" align="right">'.Display::return_icon('scenario1.png').'</td></tr>  '.'</table>');
		$form->addElement('radio', 'scenario', null, '<b>'.get_lang('Collaboration').'</b>', 2);
		$form->addElement('html','<table width="100%" border="0"><tr><td width="21%">&nbsp;</td><td width="60%" valign="middle">'.get_lang('ScenarioText2').'<br />'.get_lang('ScenarioTools2').'</td><td width="200" align="right">'.Display::return_icon('scenario2.png').'</td></tr>  '.'</table>');
		$form->addElement('radio', 'scenario', null, '<b>'.get_lang('Competition').'</b>', 3);
		$form->addElement('html','<table width="100%" border="0"><tr><td width="21%">&nbsp;</td><td width="60%" valign="middle">'.get_lang('ScenarioText3').'<br />'.get_lang('ScenarioTools3').'</td><td width="200" align="right">'.Display::return_icon('scenario3.png').'</td></tr>  '.'</table>');
		$form->addElement('style_submit_button', 'butom', get_lang('Ok'), 'class="save" id="submit"' );

		$defaults['scenario'] = $row['category_id'];
		$form->setDefaults($defaults);
		$form->display();
		echo '	</div>';
		echo '</div>';

// close the content div
echo '</div>';
// display the footer
Display :: display_footer();
?>
