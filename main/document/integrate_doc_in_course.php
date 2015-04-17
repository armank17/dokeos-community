<?php
$language_file = array('exercice');

require_once '../inc/global.inc.php';
require_once '../newscorm/learnpathList.class.php';

if(!api_is_allowed_to_edit()) {
  api_not_allowed(true);
}
$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);

Display::display_reduced_header();

$document_id = intval($_GET['add_to_course']);
$doc_name = Security::remove_XSS($_GET['docname']);

$sql_course = "SELECT * FROM " . $tbl_lp;
$result = api_sql_query($sql_course, __FILE__, __LINE__);

$num_of_courses = Database::num_rows($result);

if($num_of_courses <> 0)		
{		
//create the form that asks for the directory name
$adddoc_text = '<form name="add_doc_to_course" action="document.php?'.api_get_cidreq().'&add_to_course='.$document_id.'" method="post">';
$adddoc_text .= '<input type="hidden" name="curdirpath" value="'.$curdirpath.'" />';
$adddoc_text .= '<table><tr><td>';
$adddoc_text .= get_lang('FileName').' : </td>';
$adddoc_text .= '<td><input type="text" name="doc_name" value="'.$doc_name.'" /></td></tr>';		
$adddoc_text .= '<tr><td>'.get_lang('Course').' : </td>';
$adddoc_text .= '<td><select name="course" size="1">';

while($obj = Database::fetch_object($result))
	{
		$lp_name = $obj->name;	
		$lp_id = $obj->id;	
		$adddoc_text .= '<option value="'.$lp_name.'@'.$lp_id.'">'.$lp_name.'</option>';	
	}	
$adddoc_text .= '</select></td></tr>';		
$adddoc_text .= '</table>';
$adddoc_text .= '<button type="submit" class="add" name="doc_add">'.get_lang('Validate').'</button>';
$adddoc_text .= '</form><br/><br/><br/>';
//show the form
echo $adddoc_text;
//Display::display_normal_message($adddoc_text);	
} else {
//Display::display_normal_message(get_lang('NoCourseAtPresent'));
echo get_lang('NoCourseAtPresent');
}
?>