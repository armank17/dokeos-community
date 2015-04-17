<?php
//* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.booking
==============================================================================
*/

require_once('rsys.php');

// language file
$language_file = 'admin';

// not inside a course
$cidReset = true;

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// setting the tabs
$this_section = SECTION_PLATFORM_ADMIN;

// access restriction
api_protect_admin_script();
Rsys :: protect_script('m_category');

$tool_name = get_lang('ViewResourceTypes');
$interbreadcrumb[] = array ("url" => "../admin/index.php", "name" => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array ("url" => "../admin/m_category.php", "name" => get_lang('BookingSystem'));

/**
    ---------------------------------------------------------------------
 */

/**
 *  Filter to display the modify-buttons
 *
 *  @param - int $id The ResourceType-id
 */
function modify_filter($id) {
	$return = '';
	// view the booking periods for this resource category
	$return .= '<a href="m_reservation.php?action=overviewsubscriptions&amp;resource_category='.$id.'">'.Display::return_icon('calendar_week.gif', get_lang('ViewBookingPeriods')).'</a>';
	// edit this resource category
	$return .= '<a href="m_category.php?action=edit&amp;id='.$id.'" title="'.get_lang("EditResourceType").'">'.Display::return_icon('edit.png',get_lang('Edit')).'</a>';
	// delete this resource category
	$return .= '<a href="m_category.php?action=delete&amp;id='.$id.'" title="'.get_lang("DeleteResourceType").'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmDeleteResourceType")))."'".')) return false;">'.Display::return_icon('delete.png',get_lang('Delete')).'</a>';
	return $return; 
}

function category_filter($category_name,$url_params,$row)
{
	return '<a href="m_item.php?cat='.$row[0].'">'.$category_name.'</a>';
}

// action handling (GET)
switch ($_GET['action']) {
	case 'add' :
		// the tool name
		$tool_name = get_lang('AddNewResourceType');
		// the tool title
		//api_display_tool_title(get_lang('AddNewResourceType'));
		// the form (output buffered)
		ob_start();
		$form = new FormValidator('category', 'post', 'm_category.php?action=add');
		$form->addElement('header', 'form_header',get_lang('AddNewResourceType'));
		$form->add_textfield('name', get_lang('ResourceTypeName'), true, array ('maxlength' => '128'));
		$form->addElement('style_submit_button', 'submit', get_lang('CreateResourceType'),'class="add"');
		if ($form->validate()) {
			$values = $form->exportValues();
			if (Rsys :: add_category($values['name'])) {
				Display :: display_confirmation_message(Rsys :: get_return_msg(get_lang('ResourceTypeAdded'), "m_category.php", $tool_name),false);
			} else {
				Display :: display_error_message(Rsys :: get_return_msg(get_lang('ResourceTypeExist'), "m_category.php?action=add", get_lang('AddNewResourceType')),false);
			}
		} else {
			$form->display();
		}
		$msg = ob_get_contents();
		ob_end_clean();
		break;
	case 'edit' :
		// breadcrumbs
		$interbreadcrumb[] = array ("url" => "m_category.php", "name" => $tool_name);
		// the tool name
		$tool_name = get_lang('EditResourceType');
		// the tool title
		//api_display_tool_title(get_lang('EditResourceType'));
		// the form (output buffered)
		ob_start();		
		$form = new FormValidator('category', 'post', 'm_category.php?action=edit');
		$form->addElement('header', 'form_header',get_lang('EditResourceType'));
		$form->add_textfield('name', get_lang('ResourceTypeName'), true, array ('maxlength' => '128'));
		$form->addElement('hidden', 'id', $_GET['id']);
		$form->addElement('style_submit_button', 'submit', get_lang('ModifyResourceType'),'class="save"');
		$form->setDefaults(Rsys :: get_category($_GET['id']));
		if ($form->validate()) {
			$values = $form->exportValues();
			if (Rsys :: edit_category($values['id'], $values['name'])) {
				Display :: display_confirmation_message(Rsys :: get_return_msg(get_lang('ResourceTypeEdited'), "m_category.php", $tool_name),false);
			} else {
				Display :: display_error_message(Rsys :: get_return_msg(get_lang('ResourceTypeExist'), "m_category.php?action=edit&amp;id=".$values['id'], get_lang('EditRight')),false);
			}
		} else {
			$form->display();
		}
		$msg = ob_get_contents();
		ob_end_clean();			
		break;
	case 'delete' :
		$result = Rsys :: delete_category($_GET['id']);
		ob_start();
		if ($result == 0) {
			Display :: display_confirmation_message(get_lang('ResourceTypeDeleted'),false);
		}
		else {
			Display :: display_error_message(str_replace('#NUM#', $result, get_lang('ResourceTypeHasItems')),false);
		}
		$msg = ob_get_contents();
		ob_end_clean();
		break;
}

// action handling (post)
if (isset ($_POST['action'])) {
			switch ($_POST['action']) {
				case 'delete_categories' :
					$ids = $_POST['categories'];
					if (count($ids) > 0) {
						foreach ($ids as $index => $id) {
							$result = Rsys :: delete_category($id);
							if ($result != 0)
								$warning = true;
						}
					}
					if ($warning) {
						ob_start();
						Display :: display_normal_message(get_lang('ResourceTypeNotDeleted'),false);
						$msg2 = ob_get_contents();
						ob_end_clean();
					}
					break;
			}
}


$NoSearchResults = get_lang('NoCategories');

// Displaying the header
Display :: display_header($tool_name);

// the tool title
//api_display_tool_title($tool_name);

// additional content (forms, return messages, ...)
echo $msg;
echo $msg2;


// actions bar
echo '<div class="actions">';
echo '<a href="m_category.php">'.Display::return_icon('folder_document.gif', get_lang('ViewResourceTypes')).get_lang('ViewResourceTypes').'</a>';
echo '<a href="m_category.php?action=add">'.Display::return_icon('folder_new.gif',get_lang('AddNewResourceType')).get_lang('AddNewResourceType').'</a>';
echo '<a href="m_item.php">'.Display::return_icon('cube.png',get_lang('ViewResources')).get_lang('ViewResources').'</a>';
echo '<a href="m_item.php?action=add">'.Display::return_icon('cube_add.png',get_lang('AddNewResource')).get_lang('AddNewResource').'</a>';
echo '<a href="m_reservation.php?action=overviewsubscriptions">'.Display::return_icon('calendar_week.gif',get_lang('ViewBookingPeriods')).get_lang('ViewBookingPeriods').'</a>';
echo '<a href="m_reservation.php?action=add">'.Display::return_icon('calendar_add.gif',get_lang('AddNewBookingPeriod')).get_lang('AddNewBookingPeriod').'</a>';
echo '</div>';

// start the content div
echo '<div id="content">';

// The table with all the resource categories (types)
$table = new SortableTable('category', array ('Rsys', 'get_num_categories'), array ('Rsys', 'get_table_categories'), 1);
$table->set_header(0, '', false, array ('style' => 'width:10px'));
$table->set_header(1, '', false);
$table->set_header(2, '', false, array ('style' => 'width:50px;'));
$table->set_column_filter(2, 'modify_filter');
$table->set_column_filter(1, 'category_filter');
$table->set_form_actions(array ('delete_categories' => get_lang('DeleteSelectedCategories')), 'categories');
$table->display();

// close the content div
echo '</div>';

// footer
Display :: display_footer();
?>
