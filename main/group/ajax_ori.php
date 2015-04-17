<?php
// name of the language file that needs to be included
$language_file = "group";

// including the global Dokeos file
require_once ('../inc/global.inc.php');

switch ($_GET['action']){
	case 'group_name_form_elements':
		group_name_form_elements($_GET['number_of_groups'],$_GET['number_of_users_per_group']);
		break;
}

function group_name_form_elements($number_of_groups,$number_of_users_per_group){
	echo '<form id="group_creation" name="group_creation" method="post" action="group_creation.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'&amp;action=save_groups">';
	for ($i = 0; $i < $number_of_groups; $i++) {
		echo '<div class="marginbottom">';
		echo '<input type="text" name="group_name[]" value="'.get_lang('Group').' '.($i + 1).'">';
		echo '<input type="text" name="users_of_group[]" value="'.Security::remove_XSS($number_of_users_per_group).'" size="3">';
		echo '</div>';
	}
	echo '<button type="submit" name="action" class="save">'.get_lang('SaveGroups').'</button>';
	echo '</form>';
}
?>	
