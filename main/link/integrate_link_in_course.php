<?php
$language_file = array('link');

require_once '../inc/global.inc.php';
require_once '../newscorm/learnpathList.class.php';

if(!api_is_allowed_to_edit()) {
  api_not_allowed(true);
}
$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);
$tbl_link = Database::get_course_table(TABLE_LINK);

Display::display_reduced_header();

$link_id = intval($_GET['linkId']);
$sql = "SELECT url, title FROM $tbl_link WHERE id=" . $link_id;
$res = Database::query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($res)) {
$link_url = $row['url'];
$link_title = $row['title'];
}

$sql_course = "SELECT * FROM " . $tbl_lp;
$result = api_sql_query($sql_course, __FILE__, __LINE__);
$course_numrows = Database::num_rows($result);
if ($course_numrows <> 0) {
//create form to link to course
echo '<div><form name="add_link_to_course" action="link.php?add_to_course=' . $link_id .$add_params_for_lp. '" method="post">';
echo '<table cellpadding="4" cellspacing="4">';
echo '<tr><td>' . get_lang('Title') . ' :&nbsp;</td><td><input type="text" name="link_title" value="' . $link_title . '" size="35"></td></tr>';
echo '<tr><td>' . get_lang('Url') . ' :&nbsp;</td><td><input type="text" name="link_url" value="' . $link_url . '" size="35"></td></tr>';
echo '<tr><td>' . get_lang('Course') . ' :&nbsp;</td><td><select name="course" size="1">';

while ($obj = Database::fetch_object($result)) {
$lp_name = $obj->name;
$lp_id = $obj->id;
echo '<option value="' . $lp_name . '@' . $lp_id . '">' . $lp_name . '</option>';
}
echo '</select></td></tr>';
echo '<tr><td>&nbsp;</td><td><button type="submit" class="add" name="link_add">' . get_lang('Validate') . '</button></td></tr>';
echo '</table>';
echo '</form></div><br/>';
} else {
echo '<div class="actions">'.get_lang('NoCourseCreatedPleaseCreateOne').'<div>';
}
?>