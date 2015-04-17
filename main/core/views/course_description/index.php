<?php
/* For licensing terms, see /license.txt */

// name of the language file that needs to be included
$language_file = array ('course_description', 'pedaSuggest', 'accessibility');

// setting the help
$help_content = 'coursedescription';

// including the global dokeos file
require_once '../../../inc/global.inc.php';

// including additional libraries
require_once api_get_path(SYS_MODEL_PATH).'course_description/CourseDescriptionModel.php';
require_once api_get_path(SYS_CONTROLLER_PATH).'course_description/CourseDescriptionController.php';
include api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
include_once api_get_path(LIBRARY_PATH).'WCAG/WCAG_rendering.php';

// setting the section (for the tabs)
$this_section = SECTION_COURSES;

// Access restrictions
api_protect_course_script(true);

// setting breadcrumbs
$interbreadcrumb[] = array ('url' => 'index.php', 'name' => get_lang('CourseProgram'));

// Load javascript functions
if (api_get_setting('show_glossary_in_documents') != 'none'){
  $htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
  if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//    $htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/glossary_description.js"></script>';
  } else {
    $htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/glossary_description.js"></script>';
  }
}
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/fix.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css">
        input.error { border: 1px solid red; }
    </style>

    <script type="text/javascript">
    /*<![CDATA[*/
    $(document).ready(function(){
        if ($("#course_description").length > 0) {
            $("#course_description").validate({
                rules: {
                    title: {
                      required: true
                    }
                },
                messages: {
                    title: {
                        required: "<img src=\"'.  api_get_path(WEB_IMG_PATH).'exclamation.png\" title=\''.get_lang('Required').'\' \/>"
                    }
                }
            });
        }
   });
/*]]>*/
</script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/functionsAlerts.js"></script>';
if(intval($description_id) == 1) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('Objectives'));
if(intval($description_id) == 2) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('HumanAndTechnicalResources'));
if(intval($description_id) == 3) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('Assessment'));
if(intval($description_id) == 4) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('GeneralDescription'));
if(intval($description_id) == 5) $interbreadcrumb[] = array ('url' => '#', 'name' => get_lang('Other'));

// get actions
$actions = array('listing','add','edit','delete');
$action = 'listing';
if (isset($_GET['action']) && in_array($_GET['action'],$actions)) {
	$action = $_GET['action'];
}

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

$default_description_title_editable = array();
$default_description_title_editable[1] = true;
$default_description_title_editable[2] = true;
$default_description_title_editable[3] = true;
$default_description_title_editable[4] = true;

$question = array();
$question[1]= get_lang('ObjectivesQuestions');
$question[2]= get_lang('HumanAndTechnicalResourcesQuestions');
$question[3]= get_lang('AssessmentQuestions');
$question[4]= get_lang('GeneralDescriptionQuestions');

// variable initialisation
$session_id = api_get_session_id();
$description_type = isset ($_REQUEST['description_type']) ? Security::remove_XSS($_REQUEST['description_type']) : null;
$description_id = isset ($_REQUEST['description_id']) ? Security::remove_XSS($_REQUEST['description_id']) : null;
$action = isset($_GET['action'])?Security::remove_XSS($_GET['action']):'';
$edit = isset($_POST['edit'])?Security::remove_XSS($_POST['edit']):'';
$add = isset($_POST['add'])?Security::remove_XSS($_POST['add']):'';

define('ADD_BLOCK', 5);

// tracking
event_access_tool(TOOL_COURSE_DESCRIPTION);

// work controller object
$courseDescriptionController = new CourseDescriptionController($description_id,$description_type,$show_form);

// distpacher actions to controller
switch ($action) {
	case 'listing':
		$courseDescriptionController->listing();
		break;
	case 'add':
		$courseDescriptionController->add($default_description_titles,$default_description_title_editable,$question,$information);
		break;
	case 'edit':
		$courseDescriptionController->edit($default_description_titles,$default_description_title_editable,$question,$information);
		break;
	case 'delete':
		$courseDescriptionController->destroy();
		break;
	default:
		$courseDescriptionController->listing();
}
?>
