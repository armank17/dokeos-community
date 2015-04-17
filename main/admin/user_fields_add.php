<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/


// Language files that should be included
$language_file = array('admin','registration');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationprofiling';

// including the global Dokeos file
require ('../inc/global.inc.php');

// including additional libraries
include_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
include_once (api_get_path(LIBRARY_PATH).'logmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();

// Database table definitions
$table_admin	= Database :: get_main_table(TABLE_MAIN_ADMIN);
$table_user 	= Database :: get_main_table(TABLE_MAIN_USER);
$table_uf	 	= Database :: get_main_table(TABLE_MAIN_USER_FIELD);
$table_uf_opt 	= Database :: get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
$table_uf_val 	= Database :: get_main_table(TABLE_MAIN_USER_FIELD_VALUES);

// setting the breadcrumbs
$interbreadcrumb[] = array ('url' => 'index.php', 'name' => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array ('url' => 'user_fields.php', 'name' => get_lang('UserFields'));


// adding additional javascript
$htmlHeadXtra[] = '<script type="text/javascript">
function change_image_user_field (image_value) {
	
	if (image_value==1) {
		document.getElementById(\'options\').style.display = \'none\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class' => 'exampleaddprofilefield addprofiletext'))."'".');

	} else if (image_value==2) {
		document.getElementById(\'options\').style.display = \'none\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class' => 'exampleaddprofilefield addprofiletext_area'))."'".');

	} else if (image_value==3) {
		document.getElementById(\'options\').style.display = \'block\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';				
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofileradiobutton'))."'".');

	} else if (image_value==4) {
		document.getElementById(\'options\').style.display = \'block\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';		
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofileselectdropdown'))."'".');

	} else if (image_value==5) {
		document.getElementById(\'options\').style.display = \'block\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';		
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofilemultidropdown'))."'".');

	} else if (image_value==6) {
		document.getElementById(\'options\').style.display = \'none\';
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofiledate'))."'".');

	} else if (image_value==7) {
		document.getElementById(\'options\').style.display = \'none\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';		
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofiledateandtime'))."'".');

	} else if (image_value==8) {
		document.getElementById(\'options\').style.display = \'block\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';			
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofiledoubleselect'))."'".');

	} else if (image_value==9) {
		document.getElementById(\'options\').style.display = \'none\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofilevisualdividir'))."'".');

	} else if (image_value==10) {
		document.getElementById(\'options\').style.display = \'none\';
		document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';
		$("div#id_image_user_field").html("&nbsp;");
		$("div#id_image_user_field").html('."'<br />".Display::return_icon('pixel.gif', get_lang('UserTag'),array('class'=>'exampleaddprofilefield addprofileusertag'))."'".');

	}
}

	function advanced_parameters() {
			
			if(document.getElementById(\'options\').style.display == \'none\') {
				document.getElementById(\'options\').style.display = \'block\';
				document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';

			} else {
					document.getElementById(\'options\').style.display = \'none\';
					document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';
				}
		}

</script>';



if ($_GET['action']<>'edit')
{
	$tool_name = get_lang('AddUserFields');
}
else
{
	$tool_name = get_lang('EditUserFields');
}
// Create the form
$form = new FormValidator('user_fields_add');
$form->addElement('header', '', $tool_name);

// Field display name
$form->addElement('text','fieldtitle',get_lang('FieldTitle'),'class="focus"');
$form->applyFilter('fieldtitle','html_filter');
$form->applyFilter('fieldtitle','trim');
$form->addRule('fieldtitle', get_lang('ThisFieldIsRequired'), 'required');

// Field type
$types = array();
$types[USER_FIELD_TYPE_TEXT]  = get_lang('FieldTypeText');
$types[USER_FIELD_TYPE_TEXTAREA] = get_lang('FieldTypeTextarea');
$types[USER_FIELD_TYPE_RADIO] = get_lang('FieldTypeRadio');
$types[USER_FIELD_TYPE_SELECT] = get_lang('FieldTypeSelect');
$types[USER_FIELD_TYPE_SELECT_MULTIPLE] = get_lang('FieldTypeSelectMultiple');
$types[USER_FIELD_TYPE_DATE] = get_lang('FieldTypeDate');
$types[USER_FIELD_TYPE_DATETIME] = get_lang('FieldTypeDatetime');
$types[USER_FIELD_TYPE_DOUBLE_SELECT] 	= get_lang('FieldTypeDoubleSelect');
$types[USER_FIELD_TYPE_DIVIDER] 		= get_lang('FieldTypeDivider');
$types[USER_FIELD_TYPE_TAG] 		= get_lang('FieldTypeTag');

$form->addElement('select','fieldtype',get_lang('FieldType'),$types,array('onchange'=>'change_image_user_field(this.value)'));
$form->addRule('fieldtype', get_lang('ThisFieldIsRequired'), 'required');

//Advanced parameters
$form -> addElement('html','<div class="row">
			<div class="label">&nbsp;</div>
			<div class="formw">
				<a href="javascript://" onclick=" return advanced_parameters()"><span id="img_plus_and_minus"><div style="vertical-align:top;" ><img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'</div></span></a>
			</div>
			</div>');
//When edit, the combobox displey the field type displeyed else none 	
if ( (isset($_GET['action']) && $_GET['action'] == 'edit') && in_array($_GET['field_type'],array(3,4,5,8))) {
	$form -> addElement('html','<div id="options" style="display:block">');
} else {
	$form -> addElement('html','<div id="options" style="display:none">');
}

//field label
$form->addElement('hidden','fieldid',Security::remove_XSS($_GET['field_id']));
$form->addElement('text','fieldlabel',get_lang('FieldLabel'));
$form->applyFilter('fieldlabel','html_filter');
$form->addRule('fieldlabel', get_lang('OnlyLettersAndNumbersAllowed'), 'username');
$form->addRule('fieldlabel', '', 'maxlength',20);
//$form->addRule('fieldlabel', get_lang('FieldTaken'), 'fieldlabel_available');

// Field options possible
$form->addElement('text','fieldoptions',get_lang('FieldPossibleValues').Display::return_icon('info3.png', get_lang('FieldPossibleValuesComment'), array('align' => 'middle', 'hspace' => '3px')));
$form->applyFilter('fieldoptions','trim');

if (is_numeric($_GET['field_id']))
{
	$form->addElement('static', 'option_reorder', '', '<a href="user_fields_options.php?field_id='.Security::remove_XSS($_GET['field_id']).'">'.get_lang('ReorderOptions').'</a>');
}

// Field default value
$form->addElement('text','fielddefaultvalue',get_lang('FieldDefaultValue'));
$form->applyFilter('fielddefaultvalue','trim');

//Field for registration
$form->addElement('checkbox','fieldregistration',get_lang('FieldRegistration'));

// Set default values (only not empty when editing)
$defaults = array();
if (is_numeric($_GET['field_id']))
{
	$form_information = UserManager::get_extra_field_information((int)$_GET['field_id']);
	$defaults['fieldtitle'] = $form_information['field_display_text'];
	$defaults['fieldlabel'] = $form_information['field_variable'];
	$defaults['fieldtype'] = $form_information['field_type'];
	$defaults['fielddefaultvalue'] = $form_information['field_default_value'];
        $defaults['fieldregistration'] = $form_information['field_registration'];

	$count = 0;
	// we have to concatenate the options
	if (count($form_information['options'])>0) {
		foreach ($form_information['options'] as $option_id=>$option)
		{
			if ($count<>0)
			{
				$defaults['fieldoptions'] = $defaults['fieldoptions'].'; '.$option['option_display_text'];
			}
			else
			{
				$defaults['fieldoptions'] = $option['option_display_text'];
			}
			$count++;
		}
	}
}

$form->setDefaults($defaults);
	if(isset($_GET['field_id']) && !empty($_GET['field_id'])) {
		$class="save";
		$text=get_lang('buttonEditUserField');
	} else { 
		$class="add";
		$text=get_lang('buttonAddUserField');
	}
	
$form -> addElement('html','</div>');
// Submit button
$form->addElement('style_submit_button', 'submit',$text, 'class='.$class.'');
// Validate form
if( $form->validate())
{
        
	$check = Security::check_token('post'); 
        logmanager::write_log(date("Y-m-d H:i:s").': '.json_encode($check)."\n");
        logmanager::write_log(date("Y-m-d H:i:s").': '.json_encode($_SESSION)."\n");
        logmanager::write_log(date("Y-m-d H:i:s").': '.json_encode($_POST)."\n");
	if($check) {
            logmanager::write_log(date("Y-m-d H:i:s").': paso el token'. "\n");
		$field = $form->exportValues();
		$fieldlabel = empty($field['fieldlabel'])?$field['fieldtitle']:$field['fieldlabel'];		
		$fieldlabel = trim(strtolower(str_replace(" ","_",$fieldlabel)));	
		$fieldtype = $field['fieldtype'];
		$fieldtitle = $field['fieldtitle'];
		$fielddefault = $field['fielddefaultvalue'];		               
                $fieldoptions = $field['fieldoptions']; //comma-separated list of options 
                $fieldregistration = $field['fieldregistration']; 

		if (is_numeric($field['fieldid']) AND !empty($field['fieldid']))
		{
			UserManager:: save_extra_field_changes($field['fieldid'],$fieldlabel,$fieldtype,$fieldtitle,$fielddefault,$fieldoptions, $fieldregistration);
			$message = get_lang('FieldEdited');
		}
		else
		{
			$field_id = UserManager::create_extra_field($fieldlabel,$fieldtype,$fieldtitle,$fielddefault,$fieldoptions, $fieldregistration);
			$message = get_lang('FieldAdded');
		}
		//Security::clear_token();
		header('Location: user_fields.php?action=show_message&message='.urlencode(get_lang('FieldAdded')));
		exit ();
	}
}else{
	if(isset($_POST['submit'])){
        Security::clear_token();
}
	$token = Security::get_token();
	$form->addElement('hidden','sec_token');
	$form->setConstants(array('sec_token' => $token));
}

// display the header
Display::display_header($tool_name);

// action links
echo '<div class="actions">';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_list.php">'.Display::return_icon('pixel.gif',get_lang('UserList'), array('class' => 'toolactionplaceholdericon toolactionadminusers')).get_lang('UserList').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_add.php">'.Display::return_icon('pixel.gif',get_lang('AddUsers'), array('class' => 'toolactionplaceholdericon toolactionaddusertocourse')).get_lang('AddUsers').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_export.php">'.Display::return_icon('pixel.gif',get_lang('Export'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('Export').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_import.php">'.Display::return_icon('pixel.gif',get_lang('Import'), array('class' => 'toolactionplaceholdericon toolactionupload')).get_lang('Import').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_fields.php">'.Display::return_icon('pixel.gif',get_lang('ManageUserFields'), array('class' => 'toolactionplaceholdericon toolactionsprofile')).get_lang('ManageUserFields').'</a>';
echo '</div>';

// display the tool title
//api_display_tool_title($tool_name);

// display feedback messages
if(!empty($_GET['message'])) {
	Display::display_normal_message($_GET['message']);
} else {
	Display::display_normal_message(get_lang('UserFieldsAddHelp'),false);
}

// start the content div
echo '<div id="content" class="maxcontent">';
// display the form
$form->display();

echo '<div class="row"><div class="form_header">'.get_lang('Example').':</div></div>';
echo '<div id="id_image_user_field">';
if(!empty($defaults['fieldtype'])) {
	$image_value = $defaults['fieldtype'];
	if ($image_value==1) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class' => 'exampleaddprofilefield addprofiletext'));
	} else if ($image_value==2) {
		echo '<br />'.Display::return_icon('userfield_text_area.png', get_lang('AddUserFields'));
	} else if ($image_value==3) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofileradiobutton'));
	} else if ($image_value==4) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofileselectdropdown'));
	} else if ($image_value==5) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofilemultidropdown'));
	} else if ($image_value==6) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofiledate'));
	} else if ($image_value==7) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofiledateandtime'));
	} else if ($image_value==8) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofiledoubleselect'));
	} else if ($image_value==9) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class'=>'exampleaddprofilefield addprofilevisualdividir'));
	} else if ($image_value==10) {
		echo '<br />'.Display::return_icon('pixel.gif', get_lang('UserTag'),array('class'=>'exampleaddprofilefield addprofileusertag'));
	}
} else {
	echo '<br />'.Display::return_icon('pixel.gif', get_lang('AddUserFields'),array('class' => 'exampleaddprofilefield addprofiletext'));
}
echo '</div>';

// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>
