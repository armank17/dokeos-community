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
require_once '../inc/global.inc.php';
//$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
//$htmlHeadXtra[] = '<script  type="text/javascript" src="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';


// access url
$access_url_id = api_get_current_access_url_id();
if($access_url_id <= 0){
   $access_url_id = 1; 
}


$htmlHeadXtra[] ='<script type="text/javascript">
$(document).ready(function(){
	$(function() {
		$(".dragdrop").sortable({ 
                opacity: 0.6,
                cursor: "move",
                handle: $(".ddrag"),
                //cancel: ".nodrag", 
                update: function(event, ui) {
                    var order = $(this).sortable("serialize") + "&amp;action=change_field_position";			
                    var record = order.split("&");
                    var recordlen = record.length;
                    var disparr = new Array();
                    for (var i=0;i<(recordlen-1);i++) {
                     var recordval = record[i].split("=");
                     disparr[i] = recordval[1];
                    }

		  // call ajax to save new position
		  $.ajax({
			   type: "GET",
			   url: "'.api_get_path(WEB_AJAX_PATH).'user_fields.ajax.php?action=change_field_position&neworder="+disparr,
			   success: function(response){
			   document.location="user_fields.php";                               
                           }
                        })
                    }
		});
	});

});
</script> ';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'security.lib.php';

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();

// Database table definitions
$table_admin	= Database :: get_main_table(TABLE_MAIN_ADMIN);
$table_user 	= Database :: get_main_table(TABLE_MAIN_USER);
$table_uf	= Database :: get_main_table(TABLE_MAIN_USER_FIELD);
$table_uf_opt 	= Database :: get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
$table_uf_val 	= Database :: get_main_table(TABLE_MAIN_USER_FIELD_VALUES);

// setting the breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

if(1)
{
	$tool_name = get_lang('UserFields');

	// display the header
	Display :: display_header($tool_name, "");

	// action links
	echo '<div class="actions">';
	echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_list.php">'.Display::return_icon('pixel.gif',get_lang('UserList'), array('class' => 'toolactionplaceholdericon toolactionadminusers')).get_lang('UserList').'</a>';
	echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_add.php">'.Display::return_icon('pixel.gif',get_lang('AddUsers'), array('class' => 'toolactionplaceholdericon toolactionaddusertocourse')).get_lang('AddUsers').'</a>';
	echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_export.php">'.Display::return_icon('pixel.gif',get_lang('Export'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('Export').'</a>';
	echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/user_import.php">'.Display::return_icon('pixel.gif',get_lang('Import'), array('class' => 'toolactionplaceholdericon toolactionupload')).get_lang('Import').'</a>';
	echo '<a href="user_fields_add.php?action=fill">'.Display::return_icon('pixel.gif',get_lang('AddUserField'), array('class' => 'toolactionplaceholdericon toolactionsprofile')).get_lang('AddUserField').'</a>';
	echo '</div>';

	// display the tool title
	//api_display_tool_title($tool_name);

	// action handling
	if (isset ($_GET['action']))
	{                
		$check = Security::check_token('get');
                //force true check temporary        
                $check = ($check == false) ? true : $check;
		if($check){
			switch ($_GET['action']) {
				case 'show_message' :
					Display :: display_normal_message($_GET['message']);
					break;
				case 'show_field' :
					if (api_is_platform_admin() && !empty($_GET['field_id']) && UserManager :: update_extra_field($_GET['field_id'],array('field_visible'=>'1'))) {
						Display :: display_confirmation_message(get_lang('FieldShown'));
					} else {
						Display :: display_error_message(get_lang('CannotShowField'));
					}
					break;
				case 'hide_field' :
					if (api_is_platform_admin() && !empty($_GET['field_id']) && UserManager :: update_extra_field($_GET['field_id'],array('field_visible'=>'0'))) {
						Display :: display_confirmation_message(get_lang('FieldHidden'));
					} else {
						Display :: display_error_message(get_lang('CannotHideField'));
					}
					break;
				case 'thaw_field' :
					if (api_is_platform_admin() && !empty($_GET['field_id']) && UserManager :: update_extra_field($_GET['field_id'],array('field_changeable'=>'1'))) {
						Display :: display_confirmation_message(get_lang('FieldMadeChangeable'));
					} else {
						Display :: display_error_message(get_lang('CannotMakeFieldChangeable'));
					}
					break;
				case 'freeze_field' :
					if (api_is_platform_admin() && !empty($_GET['field_id']) && UserManager :: update_extra_field($_GET['field_id'],array('field_changeable'=>'0'))) {
						Display :: display_confirmation_message(get_lang('FieldMadeUnchangeable'));
					} else {
						Display :: display_error_message(get_lang('CannotMakeFieldUnchangeable'));
					}
					break;
				case 'moveup' :
					if (api_is_platform_admin() && !empty($_GET['field_id'])) {
						if (move_user_field('moveup', $_GET['field_id'])) {
							Display :: display_confirmation_message(get_lang('FieldMovedUp'));
						} else {
							Display :: display_error_message(get_lang('CannotMoveField'));
						}
					}
					break;
				case 'movedown' :
					if (api_is_platform_admin() && !empty($_GET['field_id'])) {
						if (move_user_field('movedown', $_GET['field_id'])) {
							Display :: display_confirmation_message(get_lang('FieldMovedDown'));
						} else {
							Display :: display_error_message(get_lang('CannotMoveField'));
						}
					}
					break;
				case 'filter_on' :
					if (api_is_platform_admin() && !empty($_GET['field_id']) && UserManager :: update_extra_field($_GET['field_id'],array('field_filter'=>'1'))) {
						Display :: display_confirmation_message(get_lang('FieldFilterSetOn'));
					} else {
						Display :: display_error_message(get_lang('CannotShowField'));
					}
					break;
				case 'filter_off' :
					if (api_is_platform_admin() && !empty($_GET['field_id']) && UserManager :: update_extra_field($_GET['field_id'],array('field_filter'=>'0'))) {
						Display :: display_confirmation_message(get_lang('FieldFilterSetOff'));
					} else {
						Display :: display_error_message(get_lang('CannotShowField'));
					}
					break;

				case 'delete':
					if (api_is_platform_admin() && !empty($_GET['field_id'])) {
						if (delete_user_fields($_GET['field_id'])) {
							Display :: display_confirmation_message(get_lang('FieldDeleted'));
						} else {
							Display :: display_error_message(get_lang('CannotDeleteField'));
						}
					}
					break;
			}
               }else{
                        Security::get_token('get'); 
               }
                        }
	if (isset ($_POST['action'])) {
		$check = Security::check_token('get');
		if($check) {
			switch ($_POST['action']) {
				default:
					break;
			}
			Security::clear_token();
		}
	}

	// action links
/*	echo '<div class="actions">';
	echo '<a href="user_fields_add.php?action=fill">'.Display::return_icon('fieldadd.gif', get_lang('AddUserField')).get_lang('AddUserField').'</a>';
	echo '</div>';*/
	// start the content div
	echo '<div id="content" class="maxcontent">';	

    $sql = "SELECT * FROM $table_uf WHERE access_url_id = $access_url_id ORDER BY field_order";
    $res = Database::query($sql,__FILE__,__LINE__);	
	
    echo '<div class="row"><div class="form_header">'.get_lang('ProfilingList').'</div></div>';
    
	echo '<table style="width:100%" id="slidelist" class="data_table data_table_exercise">';
    echo '<thead>';
	echo '<tr>';
    echo '<th width="12%">'.get_lang('Move').'</th>';
	echo '<th width="15%">'.get_lang('FieldLabel').'</th>';
	echo '<th width="20%">'.get_lang('FieldType').'</th>';
	echo '<th width="12%">'.get_lang('FieldTitle').'</th>';
	echo '<th width="15%">'.get_lang('FieldDefaultValue').'</th>';
	echo '<th width="15%">'.get_lang('FieldVisibility').'</th>';
	echo '<th width="12%">'.get_lang('FieldChangeability').'</th>';
    echo '<th width="10%">'. get_lang('FieldFilter').'</th>';
	echo '<th width="5%">'.get_lang('Edit').'</th>';
	echo '<th width="5%">'.get_lang('Delete').'</th>';
	echo '</tr>';
	echo '</thead>';

	echo '
        <tbody id="categories" class="dragdrop nobullets  ui-sortable">';
        $is_field_order_empty = is_field_order_empty();
        $ind = 1;
	while($slide = Database::fetch_array($res)){

		if($i%2 == 0){
			$class = "row_odd";
		}
		else {
			$class = "row_even";
		}
        
//		$thumbimg_dir = api_get_path(WEB_PATH). 'home/default_platform_document/template_thumb/';		
//		$picture = "<div align='center'><img src='".$thumbimg_dir."thumb_".$slide['image']."'></div>";
        
		$edit_link = '<center><a href="user_fields_add.php?action=edit&field_id='.$slide['id'].'&field_type='.$slide['field_type'].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a>';
		//$delete_link = '<a href="'.api_get_self().'?action=delete&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'].'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>';
                $link = api_get_self().'?action=delete&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'];
                $delete_link = '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>';
        
        if($slide['field_visible'] == '1'){
            $vvisible ='<a href="'.api_get_self().'?action=hide_field&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif',get_lang('Hide'), array('class' => 'actionplaceholdericon actionvisible')).'</a>';
        }
        else{
            $vvisible = '<a href="'.api_get_self().'?action=show_field&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif',get_lang('Show'), array('class' => 'actionplaceholdericon actionvisible invisible')).'</a>';
        }
        if($slide['field_changeable'] == '1'){
            $changeable ='<a href="'.api_get_self().'?action=freeze_field&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('MakeUnchangeable'),array('class'=>'actionplaceholdericon actionsvalidate')).'</a>';
        }
        else{
            $changeable ='<a href="'.api_get_self().'?action=thaw_field&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('MakeChangeable'),array('class'=>'actionplaceholdericon actionwrongconvertir')).'</a>';
        }
        if($slide['field_filter'] == '1'){
            $filter = '<a href="'.api_get_self().'?action=filter_off&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('FilterOff'),array('class'=>'actionplaceholdericon actionsvalidate')).'</a>';
        }
        else{
            $filter = '<a href="'.api_get_self().'?action=filter_on&field_id='.$slide['id'].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('FilterOn'),array('class'=>'actionplaceholdericon actionwrongconvertir')).'</a>';
        }
        if($slide['field_default_value']==''){
            $field_defaul = '-';
        }
        $type = $slide['field_type'];
        
        
       
        
	echo '<tr id="lp_row_'.$slide['id'].'" class="category '.$class.'" style="opacity: 1;">';
           
    echo '	
		   
                    <td align="center" class="ddrag" width="12%" style="cursor:pointer">'.Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actiondragdrop')).'</td>
					<td width="15%">'.$slide['field_variable'].'</td>
                    <td width="20%">'.type_filter($type).'</td>
					<td width="12%">'.$slide['field_display_text'].'</td>
					<td width="15%">'.$field_defaul.'</td>
					<td width="15%">'.$vvisible.'</a></td>
					<td width="10%">'.$changeable.'</td>
                    <td width="12%">'.$filter.'</td>
					<td width="5%">'.$edit_link.'</td>
					<td width="5%">'.$delete_link.'</td>';
	echo '</tr>';

        if ($is_field_order_empty)  {
            update_field_order($slide['id'], $ind);
        }
        
	$i++;
        $ind++;
	}
	echo '</tbody>';
    echo '</table>';

	// close the content div
	echo '</div>';
}

// display the footer
Display::display_footer();


/**
 *  Check if there are some fields with order null or zero
 */
function is_field_order_empty() {
    $tbl_field = Database::get_main_table(TABLE_MAIN_USER_FIELD);
    $check = Database::query("SELECT id FROM $tbl_field WHERE (field_order is null OR field_order <= 0)");
    return (bool)Database::num_rows($check);
}

/**
 * Update field_order field of a row in user_field table 
 */
function update_field_order($field_id, $order) {
    $tbl_field = Database::get_main_table(TABLE_MAIN_USER_FIELD);
    Database::query("UPDATE $tbl_field SET field_order = '".intval($order)."' WHERE id = '".intval($field_id)."'");
    return Database::affected_rows();    
}
 
//gateway functions to the UserManager methods (provided for SorteableTable callback mechanism)
function get_number_of_extra_fields() 
{
	return UserManager::get_number_of_extra_fields();
}

function get_extra_fields($f,$n,$o,$d)
{
	return UserManager::get_extra_fields($f,$n,$o,$d);
}

/**
 * This functions translates the id of the form type into a human readable description
 *
 * @param integer $type the id of the form type
 * @return string the huma readable description of the field type (text, date, select drop-down, ...)
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version July 2008
 * @since Dokeos 1.8.6
 */
function type_filter($type)
{
	$types[USER_FIELD_TYPE_TEXT]  				= get_lang('FieldTypeText');
	$types[USER_FIELD_TYPE_TEXTAREA] 			= get_lang('FieldTypeTextarea');
	$types[USER_FIELD_TYPE_RADIO] 				= get_lang('FieldTypeRadio');
	$types[USER_FIELD_TYPE_SELECT] 				= get_lang('FieldTypeSelect');
	$types[USER_FIELD_TYPE_SELECT_MULTIPLE] 	= get_lang('FieldTypeSelectMultiple');
	$types[USER_FIELD_TYPE_DATE] 				= get_lang('FieldTypeDate');
	$types[USER_FIELD_TYPE_DATETIME] 			= get_lang('FieldTypeDatetime');
	$types[USER_FIELD_TYPE_DOUBLE_SELECT] 		= get_lang('FieldTypeDoubleSelect');
	$types[USER_FIELD_TYPE_DIVIDER] 			= get_lang('FieldTypeDivider');
	$types[USER_FIELD_TYPE_TAG] 				= get_lang('FieldTypeTag');
	return $types[$type];
}

/**
 * Modify the display order field into up and down arrows
 *
 * @param unknown_type $field_order
 * @param	array	Url parameters
 * @param	array	The results row
 * @return	string	The link
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version July 2008
 * @since Dokeos 1.8.6
 */
function order_filter($field_order,$url_params,$row)
{
	global $number_of_extra_fields;

	// the up icon only has to appear when the row can be moved up (all but the first row)
	if ($row[5]<>1)
	{
		$return .= '<a href="'.api_get_self().'?action=moveup&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('Up'),array('class'=>'actionplaceholdericon actioniconup')).'</a>';
	}
	else
	{
		$return .= Display::return_icon('blank.gif','',array('width'=>'21px'));
	}

	// the down icon only has to appear when the row can be moved down (all but the last row)
	if ($row[5]<>$number_of_extra_fields)
	{
		$return .= '<a href="'.api_get_self().'?action=movedown&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('Down'),array('class'=>'actionplaceholdericon actionicondown')).'</a>';
	}

	return $return;
}
/**
 * Modify the visible field to show links and icons
 * @param	int 	The current visibility
 * @param	array	Url parameters
 * @param	array	The results row
 * @return	string	The link
 */
function modify_visibility($visibility,$url_params,$row)
{
	return ($visibility?'<a href="'.api_get_self().'?action=hide_field&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif',get_lang('Hide'), array('class' => 'actionplaceholdericon actionvisible')).'</a>':'<a href="'.api_get_self().'?action=show_field&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif',get_lang('Show'), array('class' => 'actionplaceholdericon actionvisible invisible')).'</a>');
}
/**
 * Modify the changeability field to show links and icons
 * @param	int 	The current changeability
 * @param	array	Url parameters
 * @param	array	The results row
 * @return	string	The link
 */
function modify_changeability($changeability,$url_params,$row)
{
	return ($changeability?'<a href="'.api_get_self().'?action=freeze_field&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('MakeUnchangeable'),array('class'=>'actionplaceholdericon actionsvalidate')).'</a>':'<a href="'.api_get_self().'?action=thaw_field&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('MakeChangeable'),array('class'=>'actionplaceholdericon actionwrongconvertir')).'</a>');
}

function modify_field_filter ($changeability,$url_params,$row)
{
	return ($changeability?'<a href="'.api_get_self().'?action=filter_off&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('FilterOff'),array('class'=>'actionplaceholdericon actionsvalidate')).'</a>':'' .
						   '<a href="'.api_get_self().'?action=filter_on&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('FilterOn'),array('class'=>'actionplaceholdericon actionwrongconvertir')).'</a>');
}

function edit_filter($id,$url_params,$row)
{
	global $charset;
	$return = '<a href="user_fields_add.php?action=edit&field_id='.$row[0].'&field_type='.$row[2].'&sec_token='.$_SESSION['sec_token'].'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a>';
	//$return .= '<a href="'.api_get_self().'?action=delete&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'].'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>';
        $link = api_get_self().'?action=delete&field_id='.$row[0].'&sec_token='.$_SESSION['sec_token'];
        $lang = get_lang("ConfirmYourChoice");
        $title = get_lang("Alert");
        $return .= '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>';
	return $return;
}
/**
 * Move a user defined field up or down
 *
 * @param string $direction the direction we have to move the field to (up or down)
 * @param unknown_type $field_id
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version July 2008
 * @since Dokeos 1.8.6
 */
function move_user_field($direction,$field_id)
{
	// Databse table definitions
	$table_user_field = Database::get_main_table(TABLE_MAIN_USER_FIELD);

	// check the parameters
	if (!in_array($direction,array('moveup','movedown')) OR !is_numeric($field_id))
	{
		return false;
	}

	// determine the SQL sort direction
	if ($direction == 'moveup')
	{
		$sortdirection = 'DESC';
	}
	else
	{
		$sortdirection = 'ASC';
	}

	$found = false;

	$sql = "SELECT id, field_order FROM $table_user_field ORDER BY field_order $sortdirection";
	$result = Database::query($sql,__FILE__,__LINE__);
	while($row = Database::fetch_array($result))
	{
		if ($found)
		{
			$next_id = $row['id'];
			$next_order = $row['field_order'];
			break;
		}

		if ($field_id == $row['id'])
		{
			$this_id = $row['id'];
			$this_order = $row['field_order'];
			$found = true;
		}
	}

	$sql1 = "UPDATE ".$table_user_field." SET field_order = '".Database::escape_string($next_order)."' WHERE id =  '".Database::escape_string($this_id)."'";
	$sql2 = "UPDATE ".$table_user_field." SET field_order = '".Database::escape_string($this_order)."' WHERE id =  '".Database::escape_string($next_id)."'";
	Database::query($sql1,__FILE__,__LINE__);
	Database::query($sql2,__FILE__,__LINE__);

	return true;
}

/**
 * Delete a user field (and also the options and values entered by the users)
 *
 * @param integer $field_id the id of the field that has to be deleted
 * @return boolean true if the field has been deleted, false if the field could not be deleted (for whatever reason)
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version July 2008
 * @since Dokeos 1.8.6
 */
function delete_user_fields($field_id)
{
	// Database table definitions
	$table_user_field 			= Database::get_main_table(TABLE_MAIN_USER_FIELD);
	$table_user_field_options	= Database::get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
	$table_user_field_values 	= Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);

	// delete the fields
	$sql = "DELETE FROM $table_user_field WHERE id = '".Database::escape_string($field_id)."'";
	$result = Database::query($sql,__FILE__,__LINE__);
	if (Database::affected_rows() == 1)
	{
		// delete the field options
		$sql = "DELETE FROM $table_user_field_options WHERE field_id = '".Database::escape_string($field_id)."'";
		$result = Database::query($sql,__FILE__,__LINE__);

		// delete the field values
		$sql = "DELETE FROM $table_user_field_values WHERE field_id = '".Database::escape_string($field_id)."'";
		$result = Database::query($sql,__FILE__,__LINE__);

		// recalculate the field_order because the value is used to show/hide the up/down icon
		// and the field_order value cannot be bigger than the number of fields
		$sql = "SELECT * FROM $table_user_field ORDER BY field_order ASC";
		$result = Database::query($sql,__FILE__,__LINE__);
		$i = 1;
		while($row = Database::fetch_array($result))
		{
			$sql_reorder = "UPDATE $table_user_field SET field_order = '".Database::escape_string($i)."' WHERE id = '".Database::escape_string($row['id'])."'";
			$result_reorder = Database::query($sql_reorder,__FILE__,__LINE__);
			$i++;
		}

		// field was deleted so we return true
		return true;
	}
	else
	{
		// the field was not deleted so we return false
		return false;
	}
}
?>
