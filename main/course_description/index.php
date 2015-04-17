<?php
/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	This script edits the course description.
*	This script is reserved for users with write access on the course.
*
*	@author Thomas Depraetere
*	@author Hugues Peeters
*	@author Christophe Gesché
*	@author Olivier brouckaert
*	@package dokeos.course_description
==============================================================================
*/

// name of the language file that needs to be included
$language_file = array ('course_description', 'pedaSuggest', 'accessibility');

// setting the help
$help_content = 'coursedescription';

// including the global Dokeos file
require ('../inc/global.inc.php');

// redirect to mvc pattern (temporally)
header('Location: '.api_get_path(WEB_VIEW_PATH).'course_description/index.php?'.api_get_cidreq());
exit;

// including additional libraries
include api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
include_once api_get_path(LIBRARY_PATH).'WCAG/WCAG_rendering.php';

// setting the section (for the tabs)
$this_section = SECTION_COURSES;

// Access restrictions
api_protect_course_script(true);

// setting breadcrumbs
$interbreadcrumb[] = array ('url' => 'index.php', 'name' => get_lang('CourseProgram'));

// Database table definitions
$tbl_course_description = Database::get_course_table(TABLE_COURSE_DESCRIPTION);

// Load javascript functions
if (api_get_setting('show_glossary_in_documents') != 'none'){
  $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
  if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/glossary_description.js"></script>';
  } else {
    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/glossary_description.js"></script>';
  }
}


// variable initialisation
$session_id = api_get_session_id();
$description_type = isset ($_REQUEST['description_type']) ? Security::remove_XSS($_REQUEST['description_type']) : null;
$description_id = isset ($_REQUEST['description_id']) ? Security::remove_XSS($_REQUEST['description_id']) : null;
$action = isset($_GET['action'])?Security::remove_XSS($_GET['action']):'';
$edit = isset($_POST['edit'])?Security::remove_XSS($_POST['edit']):'';
$add = isset($_POST['add'])?Security::remove_XSS($_POST['add']):'';

if(intval($description_id) == 1) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('Objectives'));
if(intval($description_id) == 2) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('HumanAndTechnicalResources'));
if(intval($description_id) == 3) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('Assessment'));
if(intval($description_id) == 4) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('GeneralDescription'));
if(intval($description_id) == 5) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('Other'));



$show_description_list = true;
$show_peda_suggest = true;
define('ADD_BLOCK', 5);
// Default descriptions
$default_description_titles = array();
$default_description_titles[1]= get_lang('Objectives');
$default_description_titles[2]= get_lang('HumanAndTechnicalResources');
$default_description_titles[3]= get_lang('Assessment');
$default_description_titles[4]= get_lang('GeneralDescription');
$default_description_titles[5]= get_lang('Agenda');
$default_description_class = array();
$default_description_class[1]= 'skills';
$default_description_class[2]= 'resources';
$default_description_class[3]= 'assessment';
$default_description_class[4]= 'prerequisites';
$default_description_class[5]= 'other';
$question = array();
$question[1]= get_lang('ObjectivesQuestions');
$question[2]= get_lang('HumanAndTechnicalResourcesQuestions');
$question[3]= get_lang('AssessmentQuestions');
$question[4]= get_lang('GeneralDescriptionQuestions');
$information = array();
$information[1]= get_lang('ObjectivesInformation');
$information[2]= get_lang('HumanAndTechnicalResourcesInformation');
$information[3]= get_lang('AssessmentInformation');
$information[4]= get_lang('GeneralDescriptionInformation');
$default_description_title_editable = array();
$default_description_title_editable[1] = true;
$default_description_title_editable[2] = true;
$default_description_title_editable[3] = true;
$default_description_title_editable[4] = true;

// tracking
event_access_tool(TOOL_COURSE_DESCRIPTION);

/*
==============================================================================
		MAIN CODE
==============================================================================
*/

$condition_session = api_get_session_condition($session_id, false, true);
$current_session_id = api_get_session_id();


$sql = "SELECT description_type,title FROM $tbl_course_description $condition_session ORDER BY description_type ";

$result = Database::query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($result)) {
  $default_description_titles[$row['description_type']] = $row['title'];
}
$show_form = false;
$actions = array('add','delete','edit');

if ((api_is_allowed_to_edit(null,true) && !is_null($description_type)) || in_array($action,$actions)) {

	$description_id = intval($description_id);
	$description_type = intval($description_type);

	// Delete a description block
	if ($action == 'delete') {
		$sql = "DELETE FROM $tbl_course_description WHERE id='".Database::escape_string($description_id)."'";
		Database::query($sql, __FILE__, __LINE__);
		//update item_property (delete)
		api_item_property_update(api_get_course_info(), TOOL_COURSE_DESCRIPTION, $description_id, 'delete', api_get_user_id());
		Display :: display_confirmation_message(get_lang('CourseDescriptionDeleted'));
	}
	// Add or edit a description block
	else {

		if (!empty($description_type)) {
			$sql = "SELECT * FROM $tbl_course_description WHERE description_type='".Database::escape_string($description_type)."' AND session_id='".Database::escape_string($current_session_id)."'";
			$result = Database::query($sql, __FILE__, __LINE__);
			if ($description = Database::fetch_array($result)) {
				$default_description_titles[$description_type] = $description['title'];
				$description_content = $description['content'];

			} else {
				$current_title = $default_description_titles[$description_type];
			}

		} else {
			$sql = "SELECT MAX(description_type) as MAX FROM $tbl_course_description $condition_session";
			$result = Database::query($sql, __FILE__, __LINE__);
			$max= Database::fetch_array($result);
			$description_type = $max['MAX']+1;
			if ($description_type < ADD_BLOCK) {
					$description_type=5;
			}
		}

		// Build the form

		$form = new FormValidator('course_description','POST','index.php?'.api_get_cidreq());
		$renderer = & $form->defaultRenderer();
		$form->addElement('hidden', 'description_type');

		if ($action == 'edit' || intval($edit) == 1 ) {
			$form->addElement('hidden', 'edit','1');
		}

		if ($action == 'add' || intval($add) == 1 ) {
			$form->addElement('hidden', 'add','1');
		}

		if (($description_type >= ADD_BLOCK) || $default_description_title_editable[$description_type] || $action == 'add' || intval($edit) == 1) {
			$renderer->setElementTemplate('<div class="row"><div>'.get_lang('Title').' {element}</div></div>', 'title');
			$form->add_textfield('title', get_lang('Title'), true, array('size'=>'width: 350px;','class'=>'focus'));
			$form->applyFilter('title','html_filter');
		}

		if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
			WCAG_rendering::prepare_admin_form($description_content, $form);
		} else {
			$renderer->setElementTemplate('<div class="row"><div>{element}</div></div>', 'contentDescription');
			//$form->add_html_editor('contentDescription',get_lang('Content'), true, false, $html_editor_config);
			$form->add_html_editor('contentDescription', get_lang('Content'), false, false, array('ToolbarSet' => 'TrainingDescription', 'Width' => '100%', 'Height' => '275', 'FullPage' => true));
			//$form->add_html_editor('contentDescription', '', false, false, array('ToolbarSet' => 'TrainingDescription', 'Width' => '100%', 'Height' => '275', 'FullPage' => true,'InDocument' => true,));
		}
		$form->addElement('style_submit_button', null, get_lang('Save'), 'class="save"');

		// Set some default values
		$default['title'] = $default_description_titles[$description_type];
		$default['contentDescription'] = $description_content;
		$default['description_id'] = $description_id;
		$default['description_type'] = $description_type;
		//if ($description_id >= ADD_BLOCK) {
			//$default['description_id'] = ADD_BLOCK;
		//}
                $show_form = false;
		$form->setDefaults($default);
		// If form validates: save the description block
		if ($form->validate()) {
			$description = $form->exportValues();
			if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
				$content = WCAG_Rendering::prepareXHTML();
			} else {
				$content = $description['contentDescription'];
			}
			$title = $description['title'];

			if ($description['description_type'] > ADD_BLOCK) {
				if ($description['add']=='1') { //if this element has been submitted for addition
					$result = Database::query($sql, __FILE__, __LINE__);
					$sql = "INSERT IGNORE INTO $tbl_course_description SET description_type='".Database::escape_string($description_type)."', title = '".Database::escape_string(Security::remove_XSS($title,COURSEMANAGERLOWSECURITY))."', content = '".Database::escape_string(Security::remove_XSS($content,COURSEMANAGERLOWSECURITY))."', session_id = '".Database::escape_string($current_session_id)."' ";
					Database::query($sql, __FILE__, __LINE__);
				} else {
					$sql = "UPDATE $tbl_course_description SET  title = '".Database::escape_string(Security::remove_XSS($title,COURSEMANAGERLOWSECURITY))."', content = '".Database::escape_string(Security::remove_XSS($content,COURSEMANAGERLOWSECURITY))."' WHERE description_type='".Database::escape_string($description_type)."' AND session_id = '".Database::escape_string($current_session_id)."'";
					Database::query($sql, __FILE__, __LINE__);
				}
			} else {
				//if title is not editable, then use default title
				if (!$default_description_title_editable[$description_type]) {
					$title = $default_description_titles[$description_type];
				}
				$sql = "DELETE FROM $tbl_course_description WHERE description_type = '".Database::escape_string($description_type)."' AND session_id = '".Database::escape_string($current_session_id)."'";
				Database::query($sql, __FILE__, __LINE__);
				$sql = "INSERT INTO $tbl_course_description SET description_type = '".Database::escape_string($description_type)."', title = '".Database::escape_string(Security::remove_XSS($title,COURSEMANAGERLOWSECURITY))."', content = '".Database::escape_string(Security::remove_XSS($content,COURSEMANAGERLOWSECURITY))."', session_id = '".Database::escape_string($current_session_id)."'";
				Database::query($sql, __FILE__, __LINE__);
			}
			$id = Database::insert_id();
			if ($id > 0) {
				//insert into item_property
				api_item_property_update(api_get_course_info(), TOOL_COURSE_DESCRIPTION, $id, 'CourseDescriptionAdded', api_get_user_id());
			}
                        header('Location: '.api_get_path(WEB_CODE_PATH).'course_description/index.php?'.api_get_cidreq());
                        exit;
			//Display :: display_confirmation_message(get_lang('CourseDescriptionUpdated'));
		}
		// Show the form
		else {
                    $show_form = true;
		}
	}
}

// Display the header
Display :: display_tool_header();
// Display the tool introduction
Display::display_introduction_section(TOOL_COURSE_DESCRIPTION);

if ($show_form === false) {
    // Show the list of all description blocks
    if ($show_description_list) {
            $sql = "SELECT * FROM $tbl_course_description $condition_session ORDER BY description_type ";
            $result = Database::query($sql, __FILE__, __LINE__);
            $descriptions = array();
            while ($description = Database::fetch_object($result)) {
                    $descriptions[$description->description_type] = $description;
                    //reload titles to ensure we have the last version (after edition)
                    $default_description_titles[$description->description_type] = $description->title;
            }

            // the actions (categories of content in this case)
            if (api_is_allowed_to_edit(null,true)) {
                    $categories = array ();

                    foreach ($default_description_titles as $id => $title) {
                            $categories[$id] = $title;
                    }
                    $categories[ADD_BLOCK] = get_lang('NewBloc');

                    $i=1;
                    echo '<div class="actions">';
                    ksort($categories);
                    foreach ($categories as $id => $title) {
                            // We are displaying only 5 first items
                            if ($i <= 5) {
                                    echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&description_type='.$id.'">'.Display::return_icon('pixel.gif', $title, array('class' => 'toolactionplaceholdericon toolaction'.$default_description_class[$id])).' '.$title.'</a>';
                            }
                            $i++;
                    }
                    echo '</div>';

            }

            // start the content div
            echo '<div id="content">';

            // the actual content
            if (isset($descriptions) && count($descriptions) > 0) {
                    foreach ($descriptions as $id => $description) {
                            echo '<div class="section_white">';
                            echo '	<div class="sectiontitle">'.$description->title.'</div>';
                            echo '	<div class="sectioncontent">';
                            echo 	text_filter($description->content);
                            echo '	</div>';
                            echo '</div>';
                            echo '<div class="float_r">';
                            if (api_is_allowed_to_edit()) {
                                    //delete
                                    echo '<a class="" href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=delete&amp;description_id='.$description->id.'" onclick="javascript:if(!confirm(\''.addslashes(api_htmlentities(get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset)).'\')) return false;">';
                                    echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'));
                                    echo '</a> ';
                                    //edit
                                    echo '<a class="" href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=edit&amp;description_id='.$description->id.'&amp;description_type='.$description->description_type.'">';
                                    echo Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit'));
                                    echo '</a> ';
                            }
                            echo '</div>';
                            echo '<div>&nbsp;</div>';
                            echo '<div>&nbsp;</div>';
                    }
            } else {
                    echo '<em>'.get_lang('ThisCourseDescriptionIsEmpty').'</em>';
            }
    }
} else {
    // menu top
    //***********************************
    if (api_is_allowed_to_edit(null,true)) {
            $categories = array ();

            foreach ($default_description_titles as $id => $title) {
                    $categories[$id] = $title;
            }
            $categories[ADD_BLOCK] = get_lang('NewBloc');

            $i=1;
            echo '<div class="actions">';
            ksort($categories);
            foreach ($categories as $id => $title) {
                    if ($i==5) {
                            echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=add">'.Display::return_icon('pixel.gif', $title, array('class' => 'toolactionplaceholdericon toolaction'.$default_description_class[$id])).' '.$title.'</a>';
                            break;
                    } else {
                            echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&description_type='.$id.'">'.Display::return_icon('pixel.gif', $title, array('class' => 'toolactionplaceholdericon toolaction'.$default_description_class[$id])).' '.$title.'</a>';
                            $i++;
                    }
            }
            echo '</div>';
    }
    //***********************************

    // start the content div
    echo '<div id="content">';

    if ($show_peda_suggest) {
            if (isset ($question[$description_id])) {
                    $message = '<strong>'.get_lang('QuestionPlan').'</strong><br />';
                    $message .= $question[$description_id];
                    Display::display_normal_message($message, false);
            }
    }
    if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
            echo (WCAG_Rendering::editor_header());
    }
    $form->display();
    if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
            echo (WCAG_Rendering::editor_footer());
    }
    $show_description_list = false;
}

// close the content div
echo '</div>';

// bottom actions bar
//echo '<div class="actions">';
//echo '&nbsp';
//echo '</div>';

// display the footer
Display::display_footer();
?>
