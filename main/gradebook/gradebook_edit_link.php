<?php

/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array('gradebook','link');

// setting the help
$help_content = 'links';

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// Including additional libraries.
require_once ('lib/be.inc.php');
require_once ('lib/gradebook_functions.inc.php');
require_once ('lib/fe/linkform.class.php');
require_once ('lib/fe/linkaddeditform.class.php');

// Access restriciton
api_block_anonymous_users();
block_students();

// additional javascript
$htmlHeadXtra[] = '<script type="text/javascript">
  $(document).ready(function (){
    $("div.label").attr("style","width: 100%;text-align:left");
    $("div.row").attr("style","width: 100%;");
    $("div.formw").attr("style","width: 100%;");
  });
</script>';

// variables
$course_table 		= Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_grade_links 	= Database :: get_main_table(TABLE_MAIN_GRADEBOOK_LINK);

//selected name of database
$my_db_name=get_database_name_by_link_id(Security::remove_XSS($_GET['editlink']));
$tbl_forum_thread = Database :: get_course_table(TABLE_FORUM_THREAD,$my_db_name);
$tbl_work = Database :: get_course_table(TABLE_STUDENT_PUBLICATION,$my_db_name);
$linkarray = LinkFactory :: load(Security::remove_XSS($_GET['editlink']));
$link = $linkarray[0];
$linkcat  = isset($_GET['selectcat']) ? Security::remove_XSS($_GET['selectcat']):'';
$linkedit = isset($_GET['editlink']) ? Security::remove_XSS($_GET['editlink']):'';

$form = new LinkAddEditForm(LinkAddEditForm :: TYPE_EDIT,
							null,
							null,
							$link,
							'edit_link_form',
							api_get_self() . '?selectcat=' . $linkcat
												 . '&amp;editlink=' . $linkedit);

if ($form->validate()) {
	$values = $form->exportValues();
	$link->set_weight($values['weight']);
	$link->set_date(strtotime($values['date']));
	$link->set_visible(empty ($values['visible']) ? 0 : 1);
	$link->save();
	//Update weight into forum thread
	$sql_t='UPDATE '.$tbl_forum_thread.' SET thread_weight='.$values['weight'].' WHERE thread_id=(SELECT ref_id FROM '.$tbl_grade_links.' where id='.Database::escape_string(Security::remove_XSS($_GET['editlink'])).' and type=5);';
	Database::query($sql_t);
	//Update weight into student publication(work)
	$sql_t='UPDATE '.$tbl_work.' SET weight='.$values['weight'].' WHERE id=(SELECT ref_id FROM '.$tbl_grade_links.' where id='.Database::escape_string(Security::remove_XSS($_GET['editlink'])).' and type=3);';
	Database::query($sql_t);
	header('Location: '.$_SESSION['gradebook_dest'].'?linkedited=&amp;selectcat=' . $link->get_category_id());
	exit;
}

$interbreadcrumb[] = array (
	'url' => $_SESSION['gradebook_dest'].'?selectcat='.$linkcat,
	'name' => get_lang('Gradebook'
));

Display::display_tool_header(get_lang('EditLink'));
echo '<div class="actions">';
echo '<a href="'.$_SESSION['gradebook_dest'].'?selectcat='.$linkcat.'&amp;'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.get_lang('Gradebook'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.get_lang('Gradebook').'</a>';
echo '</div>';

echo '<div id="content">';
$form->display();
echo '</div>';

// Actions bar
echo '<div class="actions">';
echo '</div>';
Display :: display_footer();
