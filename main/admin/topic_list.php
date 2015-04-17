<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessioncategorylist';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
include_once (api_get_path(LIBRARY_PATH).'export.lib.inc.php');

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'export'){

	$data = export_csv_data();

	Export::export_table_csv($data);
	break;
}

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

// setting breadcrumbs
$interbreadcrumb[]=array("url" => "index.php","name" => get_lang('PlatformAdmin'));

// Displaying the header
Display::display_header($nameTools);

echo '<div class="actions">';
echo '<a href="'.api_get_self().'?action=add'.'">' . Display :: return_icon('pixel.gif', get_lang('AddTopic'),array('class' => 'toolactionplaceholdericon toolactiontopic')) . get_lang('AddTopic') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/catalogue_management.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('Catalogue') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/topic_list.php">' . Display :: return_icon('pixel.gif', get_lang('Topics'),array('class' => 'toolactionplaceholdericon toolactiontopic')) . get_lang('Topics') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/programme_list.php">' . Display :: return_icon('pixel.gif', get_lang('Programmes'),array('class' => 'toolactionplaceholdericon toolactionprogramme')) . get_lang('Programmes') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'),array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_export.php">'.Display::return_icon('pixel.gif',get_lang('ExportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportSessionListXMLCSV').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_import.php">'.Display::return_icon('pixel.gif',get_lang('ImportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionimportcourse')).get_lang('ImportSessionListXMLCSV').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'coursecopy/copy_course_session.php">'.Display::return_icon('pixel.gif',get_lang('CopyFromCourseInSessionToAnotherSession'),array('class' => 'toolactionplaceholdericon toolsettings')).get_lang('CopyFromCourseInSessionToAnotherSession').'</a>';
echo '</div>';

$topic_table 		= Database :: get_main_table(TABLE_MAIN_TOPIC);

echo '<style type="text/css">
		div.row {
			width: 900px;
		}
		div.row div.label{
			width: 275px;
		}
		div.row div.formw{
			width: 600px;
		}
		</style>';

echo '<div id="content">';

if(!isset($_REQUEST['action'])){
	$default_column = api_is_allowed_to_edit() ? 1 : 0;
	$tablename = 'topic_table';
	$sortable_data = get_topics_data();

	$table = new SortableTableFromArrayConfig($sortable_data,$default_column,20,$tablename,$column_show,$column_order,'ASC');

	if (api_is_allowed_to_edit()){
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('Topic'));
	$table->set_header(2, get_lang('Programmes'));
	$table->set_header(3, get_lang('Language'));
	$table->set_header(4, get_lang('Catalogue'));
	$table->set_header(5, get_lang('Edit'));
	$table->set_header(6, get_lang('Delete'));
	}
	if (api_is_allowed_to_edit()){
		$table->set_form_actions(array ('delete_topic' => get_lang('Delete')));
	}
	$table->display();
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add'){
	$form = new FormValidator('topic', 'post',api_get_self().'?action=add');
	$form->addElement('header', '', get_lang('TopicSettings'));
	$form->addElement('text', 'topic_name', get_lang('TopicName'), 'class="focus";style="width:400px;"');

	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('Yes'), '1');
	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('No'), '0');
	$form->addGroup($radios_catalogue_visible, 'catalogue_visible', get_lang('CatalogueVisible'));

	$form->addElement('select_language', 'topic_language', get_lang('TopicLanguage'));
	$form->applyFilter('select_language','html_filter');

        $catalogues = SessionManager::get_catalogue_info();
        if (!empty($catalogues)) {
            foreach ($catalogues as $key => $catalogue) {
                $opt_catalogue[$key] = $catalogue['title'];
            }
        }

        $form->addElement('select', 'catalogue', get_lang('Catalogue'), $opt_catalogue);

	$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
	$defaults['catalogue_visible'] = 0;
	$form->setDefaults($defaults);
	if( $form->validate()) {
		$topic = $form->exportValues();

		$sql = "INSERT INTO " . $topic_table . " SET " .
										   "topic	= '".Database::escape_string($topic['topic_name'])."',
										   language	= '".Database::escape_string($topic['topic_language'])."',
										   visible      = '".Database::escape_string($topic['catalogue_visible'])."',
                                                                                   catalogue_id = '".intval($topic['catalogue'])."'
                                                                                    ";

					Database::query($sql, __FILE__, __LINE__);
					echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';
	}

	$form->display();
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit'){
	$sql = "SELECT * FROM $topic_table WHERE id = ".$_REQUEST['id'];
	$res = Database::query($sql,__FILE__,__LINE__);
	$row = Database::fetch_array($res);

	$form = new FormValidator('topic', 'post',api_get_self().'?action=edit&amp;id='.$_REQUEST['id']);
	$form->addElement('header', '', get_lang('TopicSettings'));
	$form->addElement('text', 'topic_name', get_lang('TopicName'), 'class="focus";style="width:400px;"');

	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('Yes'), '1');
	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('No'), '0');
	$form->addGroup($radios_catalogue_visible, 'catalogue_visible', get_lang('CatalogueVisible'));

	$form->addElement('select_language', 'topic_language', get_lang('TopicLanguage'));
	$form->applyFilter('select_language','html_filter');

        $catalogues = SessionManager::get_catalogue_info();
        $opt_catalogue = array(0=>'');
        if (!empty($catalogues)) {
            foreach ($catalogues as $key => $catalogue) {
                $opt_catalogue[$key] = $catalogue['title'];
            }
        }

        $form->addElement('select', 'catalogue', get_lang('Catalogue'), $opt_catalogue);

	$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
	$defaults['topic_name'] = $row['topic'];
	$defaults['catalogue'] = $row['catalogue_id'];
        $defaults['catalogue_visible'] = $row['visible'];
	$defaults['topic_language'] = $row['language'];
	$form->setDefaults($defaults);
	if( $form->validate()) {
		$topic = $form->exportValues();

		$sql = "UPDATE " . $topic_table . " SET " .
										   "topic				= '".Database::escape_string($topic['topic_name'])."',
										   language				= '".Database::escape_string($topic['topic_language'])."',
										   visible				= '".Database::escape_string($topic['catalogue_visible'])."',
                                                                                   catalogue_id = '".intval($topic['catalogue'])."'
										   WHERE id = ".$_REQUEST['id'];


					Database::query($sql, __FILE__, __LINE__);
					echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';
	}

	$form->display();
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'){

	if(isset($_REQUEST['id'])){
	$sql = "DELETE FROM $topic_table WHERE id = ".$_REQUEST['id'];
	Database::query($sql, __FILE__, __LINE__);
	}
	echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_topic'){

	if(isset($_POST['id'])){
		$delete_id = $_POST['id'];
	}
	foreach ($delete_id as $index => $topic_id){
		$sql = "DELETE FROM $topic_table WHERE id = ".$topic_id;
		echo $sql;
		Database::query($sql, __FILE__, __LINE__);
	}
	echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';
}

function get_topics_data(){
	$topic_table 			= Database :: get_main_table(TABLE_MAIN_TOPIC);
	$sess_category_table 	= Database :: get_main_table(TABLE_MAIN_SESSION_CATEGORY);

	$sql = "SELECT * FROM $topic_table";
	$res = Database::query($sql,__FILE__,__LINE__);
	$topics = array ();
	while ($topic = Database::fetch_array($res)) {
		$topic_name = '<img align="top" src="'.api_get_path(WEB_IMG_PATH).'/topic_22.png" />&nbsp;&nbsp;'.$topic['topic'];
		$programme = 0;

		//cho "SELECT count(*) as count FROM $sess_category_table WHERE topic={$topic['id']}";

		$rs_programme = Database::query("SELECT count(*) as count FROM $sess_category_table WHERE topic={$topic['id']}");
		if (Database::num_rows($rs_programme)) {
			$row_programme = Database::fetch_object($rs_programme);
			$programme = $row_programme->count;
		}

		if($topic['visible'] == '1'){
			$visible = get_lang('Yes');
		}
		else {
			$visible = get_lang('No');
		}
		$edit_link = '<center><a href="'.api_get_self().'?&amp;action=edit&amp;id='.$topic['id'].'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a></center>';
		$delete_link = '<center><a href="'.api_get_self().'?&amp;action=delete&amp;id='.$topic['id'].'" onclick="javascript:if(!confirm(\''.get_lang('ConfirmYourChoice').'\')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a></center>';

		$topics[] = array($topic['id'],$topic_name,'<center>'.$programme.'</center>','<center>'.$topic['language'].'</center>','<center>'.$visible.'</center>',$edit_link,$delete_link);
	}
	return $topics;
}

echo '</div>';

echo '<div class="actions"><a href="topic_list.php?'.api_get_cidreq().'&amp;action=export&amp;type=csv">'.Display::return_icon('pixel.gif', get_lang('ExportAsCSV'), array('class' => 'actionplaceholdericon actionexport')).get_lang('ExportAsCSV').'</a></div>';

function export_csv_data(){

	$topic_table 			= Database :: get_main_table(TABLE_MAIN_TOPIC);
	$sess_category_table 	= Database :: get_main_table(TABLE_MAIN_SESSION_CATEGORY);

	$data = array();
	$data[] = array(get_lang('Topic'),get_lang('Language'),get_lang('Programmes'),get_lang('Catalogue'));

	$sql = "SELECT * FROM $topic_table";
	$res = Database::query($sql,__FILE__,__LINE__);
	$topics = array ();
	while ($topic = Database::fetch_array($res)) {
		$topic_name = $topic['topic'];
		$topic_language = $topic['language'];
		$rs_programme = Database::query("SELECT count(*) as count FROM $sess_category_table WHERE topic={$topic['id']}");
		if (Database::num_rows($rs_programme)) {
			$row_programme = Database::fetch_object($rs_programme);
			$programme = $row_programme->count;
		}

		if($topic['visible'] == '1'){
			$visible = get_lang('Yes');
		}
		else {
			$visible = get_lang('No');
		}
		$row = array();

		$row[] = $topic_name;
		$row[] = $topic_language;
		$row[] = $programme;
		$row[] = $visible;
		$data[] = $row;
	}
	return $data;
}

// display the footer
Display::display_footer();
?>
