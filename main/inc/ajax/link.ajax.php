<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */
require_once '../global.inc.php';
require_once api_get_path(SYS_MODEL_PATH).'link/LinkModel.php';
$link_id = intval($_GET['id']);
$action = $_GET['action'];

switch ($action) {
   case 'integrateincourse' :
		$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);
		$tbl_link = Database :: get_course_table(TABLE_LINK);
                // Get LPs
		$sql_course = "SELECT * FROM " . $tbl_lp;
		$result = Database::query($sql_course, __FILE__, __LINE__);
		$course_numrows = Database::num_rows($result);
                
                // Get link info
		$sql_course = "SELECT * FROM " . $tbl_link." WHERE id=$link_id";
		$result_link = Database::query($sql_course, __FILE__, __LINE__);
                $linkObject = Database::fetch_object($result_link);
                
		if ($course_numrows <> 0) {
		//create form to link to course
		$form .= '<form name="add_link_to_course" action="index.php?action=integrateincourse&amp;id=' . $link_id .$add_params_for_lp. '&done=Y" method="post">';
		$form .= '<table cellpadding="3" cellspacing="3">';
		$form .= '<tr><td>' . get_lang('Title') . ' :&nbsp;</td><td><input type="text" name="link_title" value="' . $linkObject->title . '" size="35"></td></tr>';
		$form .= '<tr><td>' . get_lang('Url') . ' :&nbsp;</td><td><input type="text" name="link_url" value="' . $linkObject->url . '" size="35"></td></tr>';
		$form .= '<tr><td>' . get_lang('Course') . ' :&nbsp;</td><td><select name="course" size="1">';

		while ($obj = Database::fetch_object($result)) {
		$lp_name = $obj->name;
		$lp_id = $obj->id;
		$form .= '<option value="' . $lp_name . '@' . $lp_id . '">' . $lp_name . '</option>';
		}
		$form .= '</select></td></tr>';
		$form .= '<tr><td>&nbsp;</td><td><button type="submit" class="add" name="link_add">' . get_lang('Validate') . '</button></td></tr>';
		$form .= '</table>';
		$form .= '</form>';
		} else {
		$form .= get_lang('NoCourseCreatedPleaseCreateOne');
		}
                echo utf8_encode($form);
        break;
     case 'updateRecordsListings':
        $linkModel = new LinkModel();         
        $linkModel->disporder = implode(',', $_GET['disporder']);
        $linkModel->type = $_GET['type'];                
        if($linkModel->type != 'categories'){			
            $linkModel->itemId = $_GET['itemId'];
            $linkModel->category_id = intval($_GET['category_id']);
        }
        $linkModel->updateRecordsListings();
        break;
}