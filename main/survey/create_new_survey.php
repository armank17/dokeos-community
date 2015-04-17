<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * ==============================================================================
 * 	@package dokeos.survey
 * 	@author unknown, the initial survey that did not make it in 1.8 because of bad code
 * 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University: cleanup, refactoring and rewriting large parts (if not all) of the code
 * 	@author Julio Montoya Armas <gugli100@gmail.com>, Dokeos: Personality Test modification and rewriting large parts of the code
 * 	@version $Id: create_new_survey.php 22297 2009-07-22 22:08:30Z cfasanando $
 *
 * 	@todo only the available platform languages should be used => need an api get_languages and and api_get_available_languages (or a parameter)
 * ==============================================================================
 */
// Language files that should be included
$language_file = 'survey';

// including the global dokeos file
require_once ('../inc/global.inc.php');

define('DOKEOS_SURVEY', true);

// including additional libraries
require_once('survey.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'fileManage.lib.php');
require_once (api_get_path(CONFIGURATION_PATH) . "add_course.conf.php");
require_once (api_get_path(LIBRARY_PATH) . "add_course.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH) . "course.lib.php");
require_once (api_get_path(LIBRARY_PATH) . "groupmanager.lib.php");
require_once (api_get_path(LIBRARY_PATH) . "usermanager.lib.php");
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');

require_once '../newscorm/learnpath.class.php';
require_once '../newscorm/learnpathItem.class.php';
require_once api_get_path(LIBRARY_PATH)  . 'searchengine.lib.php';
require_once(api_get_path(LIBRARY_PATH).'timezone.lib.php');

$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$file_ = api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/1.8.2/ui/i18n/jquery.ui.datepicker-'.api_get_language_isocode().'-GB.js';
$file2_ = api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/1.8.2/ui/i18n/jquery.ui.datepicker-'.api_get_language_isocode().'.js';
if((file($file_))){
    $htmlHeadXtra[] = '<script type="text/javascript" src="' .$file_.'" language="javascript"></script>';
}elseif (file($file2_)) {
    $htmlHeadXtra[] = '<script type="text/javascript" src="' .$file2_.'" language="javascript"></script>';
}

// Additional javascript
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript">

		function advanced_parameters() {
			if(document.getElementById(\'options\').style.display == \'none\') {
					document.getElementById(\'options\').style.display = \'block\';
					document.getElementById(\'plus_minus\').innerHTML=\'&nbsp;' . Display::return_icon('div_hide.gif', get_lang('Hide'), array('style' => 'vertical-align:middle')) . '&nbsp;' . get_lang('AdvancedParameters') . '\';
			} else {
					document.getElementById(\'options\').style.display = \'none\';
					document.getElementById(\'plus_minus\').innerHTML=\'&nbsp;' . Display::return_icon('div_show.gif', get_lang('Show'), array('style' => 'vertical-align:middle')) . '&nbsp;' . get_lang('AdvancedParameters') . '\';
			}
		}
	</script>';
/*================ Translate lang calendar ================*/
if(($isocode = api_get_language_isocode())!="en")
    $htmlHeadXtra [] = '<script src="' . api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-'.$isocode.'.js" type="text/javascript" language="javascript"></script>';
/*================ ================ ================*/
$add_lp_param = "";
if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
 $lp_id = Security::remove_XSS($_GET['lp_id']);
 $htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function (){
      $("a[href]").attr("href", function(index, href) {
          var param = "lp_id=' . $lp_id . '";
           var is_javascript_link = false;
           var info = href.split("javascript");

           if (info.length >= 2) {
             is_javascript_link = true;
           }
           if ($(this).attr("class") == "course_main_home_button" || $(this).attr("class") == "course_menu_button"  || $(this).attr("class") == "next_button"  || $(this).attr("class") == "prev_button" || is_javascript_link) {
             return href;
           } else {
             if (href.charAt(href.length - 1) === "?")
                 return href + param;
             else if (href.indexOf("?") > 0)
                 return href + "&" + param;
             else
                 return href + "?" + param;
           }
      });
    });
  </script>';
 $add_lp_param = "&amp;lp_id=" . $lp_id;
}
 $htmlHeadXtra[] = '<script type="text/javascript">
    $(document).on("ready",Jload);
    function Jload(){
        $("#survey").submit(function(){
        $("#survey").toggle();
            $("#loading").show();
        });
    }
  </script>';
     $htmlHeadXtra[] = '<script type="text/javascript">
        $(document).ready(function() {
            $( "#start_date" ).datepicker({
               showOn: "button",
               buttonImage: "' . api_get_path(WEB_IMG_PATH) . 'calendar.gif",
               buttonImageOnly: true,
               dateFormat: "dd-mm-yy"

           });
           $( "#end_date" ).datepicker({
               showOn: "button",
               buttonImage: "' . api_get_path(WEB_IMG_PATH) . 'calendar.gif",
               buttonImageOnly: true,
               dateFormat: "dd-mm-yy"
           });
        });
        </script>';
// Lp object
if (isset($_SESSION['lpobject'])) {
 if ($debug > 0)
  error_log('New LP - SESSION[lpobject] is defined', 0);
 $oLP = unserialize($_SESSION['lpobject']);
 if (is_object($oLP)) {
  if ($debug > 0)
   error_log('New LP - oLP is object', 0);
  if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
   if ($debug > 0)
    error_log('New LP - Course has changed, discard lp object', 0);
   if ($myrefresh == 1) {
    $myrefresh_id = $oLP->get_id();
   }
   $oLP = null;
   api_session_unregister('oLP');
   api_session_unregister('lpobject');
  } else {
   $_SESSION['oLP'] = $oLP;
   $lp_found = true;
  }
 }
}

// Database table definitions
$table_survey = Database :: get_course_table(TABLE_SURVEY);
$table_user = Database :: get_main_table(TABLE_MAIN_USER);
$table_course = Database :: get_main_table(TABLE_MAIN_COURSE);
$table_course_survey_rel = Database :: get_main_table(TABLE_MAIN_COURSE_SURVEY);

/** @todo this has to be moved to a more appropriate place (after the display_header of the code) */
// if user is not teacher or if he's a coach trying to access an element out of his session
if (!api_is_allowed_to_edit()) {
 if (!api_is_course_coach() || (!empty($_GET['survey_id']) && !api_is_element_in_the_session(TOOL_SURVEY, intval($_GET['survey_id'])))) {
  // Display header
  Display::display_tool_header();

  // start the content div
  echo '<div id="content">';

  Display :: display_error_message(get_lang('NotAllowed'), false, true);

  // close the content div
  echo '</div>';

  // Display the footer
  Display::display_footer();
  exit;
 }
}

// getting the survey information
$survey_id = Security::remove_XSS($_GET['survey_id']);
$survey_data = survey_manager::get_survey($survey_id);

$urlname = strip_tags(api_substr(api_html_entity_decode($survey_data['title'], ENT_QUOTES, $charset), 0, 40));
if (api_strlen(strip_tags($survey_data['title'])) > 40) {
 $urlname .= '...';
}

// breadcrumbs
if ($_GET['action'] == 'add') {
 $interbreadcrumb[] = array("url" => "survey_list.php", "name" => get_lang('SurveyList'));
 $tool_name = get_lang('CreateNewSurvey');
}
if ($_GET['action'] == 'edit' && is_numeric($survey_id)) {
 $interbreadcrumb[] = array("url" => "survey_list.php", "name" => get_lang('SurveyList'));
 $interbreadcrumb[] = array("url" => "survey.php?survey_id=" . $survey_id, "name" => strip_tags($urlname));
 $tool_name = get_lang('EditSurvey');
}

// getting the default values
if ($_GET['action'] == 'edit' AND isset($survey_id) AND is_numeric($survey_id)) {
 $defaults = $survey_data;
 $defaults['survey_id'] = $survey_id;
 //get the keyword
 $searchkey = new SearchEngineManager();
 $keyword = $searchkey->getKeyWord(TOOL_SURVEY, $survey_id);
 $defaults['search_terms'] = $keyword;
 $defaults['start_date']   = date('d-m-Y', strtotime($defaults['start_date'])); //date('d-m-Y', $defaults['start_date']);
 $defaults['end_date']     = date('d-m-Y', strtotime($defaults['end_date'])); //date('d-m-Y', $defaults['end_date']);
 /*
   $defaults['survey_share'] = array();
   $defaults['survey_share']['survey_share'] = $survey_data['survey_share'];

   if (!is_numeric($survey_data['survey_share']) OR $survey_data['survey_share'] == 0)
   {
   $form_share_value = 'true';
   }
   else
   {
   $form_share_value = $defaults['survey_share']['survey_share'];
   }
  */

 $defaults['anonymous'] = $survey_data['anonymous'];
} else {
$survey_question_per_page = isset($survey_data['question_per_page']) ? $survey_data['question_per_page'] : 0;
 $defaults['survey_language'] = $_course['language'];
 $user_id = api_get_user_id();
 $date = TimeZone::ConvertTimeFromUserToServer($user_id, $date);
 $defaults['start_date'] = date('d-m-Y', strtotime($date));
 $startdateandxdays = time() + 864000; // today + 10 days
 $startdateandxdays = TimeZone::ConvertTimeFromUserToServer($user_id, $startdateandxdays);
 $defaults['end_date'] = date('d-m-Y', strtotime($startdateandxdays));
 //$defaults['survey_share']['survey_share'] = 0;
 //$form_share_value = 1;
 $defaults['anonymous'] = 0;
 $defaults['question_per_page'] = $survey_question_per_page;
}

// initiate the object
$form = new FormValidator('survey', 'post', api_get_self() . '?' . api_get_cidreq() . '&action=' . Security::remove_XSS($_GET['action']) . '&survey_id=' . $survey_id . $add_lp_param);

$form->addElement('header', '', $tool_name);

// settting the form elements
if ($_GET['action'] == 'edit' AND isset($survey_id) AND is_numeric($survey_id)) {
 $form->addElement('hidden', 'survey_id');
}

//$survey_code = $form->addElement('text', 'survey_code', get_lang('SurveyCode'), array('size' => '20','maxlength'=>'20'));
//$form->applyFilter('survey_code', 'html_filter');
$survey_code = $form->addElement('hidden', 'survey_code');

if ($_GET['action'] == 'edit') {
//	$survey_code->freeze();
//	$form->applyFilter('survey_code', 'api_strtoupper');
}

//$form->addElement('html_editor', 'survey_title', get_lang('SurveyTitle'), null, array('ToolbarSet' => 'Survey', 'Width' => '100%', 'Height' => '200'));
//$form->addElement('html_editor', 'survey_subtitle', get_lang('SurveySubTitle'), null, array('ToolbarSet' => 'Survey', 'Width' => '100%', 'Height' => '100', 'ToolbarStartExpanded' => false));
//$form->addElement('html_editor', 'survey_title', get_lang('SurveyTitle'), null, array('ToolbarSet' => 'Survey', 'Width' => '100%', 'Height' => '100', 'ToolbarStartExpanded' => false));
$form->addElement('text', 'survey_title', get_lang('SurveyTitle'), array('style' => 'width:550px;', 'class' => 'focus CusInputNewSurvey'));

//$form->addElement('datepickerdate', 'start_date', get_lang('StartDate'), array('form_name' => 'survey'));
//$form->addElement('datepickerdate', 'end_date', get_lang('EndDate'), array('form_name' => 'survey'));
$form->addElement('text', 'start_date', get_lang('StartDate'), array('form_name' => 'survery', 'id' => 'start_date'));
$form->addElement('text', 'end_date', get_lang('EndDate'), array('form_name' => 'survery', 'id' => 'end_date'));
$form->addElement('checkbox', 'anonymous', get_lang('Anonymous'));

$q_for_page_lang_var = api_convert_encoding(get_lang('QuestionsByPage'), $charset, api_get_system_encoding());
$q_one_lang_var = api_convert_encoding(get_lang('One'), $charset, api_get_system_encoding());
$q_all_lang_var = api_convert_encoding(get_lang('All'), $charset, api_get_system_encoding());

$form->addElement('radio', 'question_per_page', $q_for_page_lang_var, $q_one_lang_var , 0,array('style'=>'vertical-align:bottom'));
$form->addElement('radio', 'question_per_page', '', $q_all_lang_var, 1,array('style'=>'vertical-align:bottom'));

$form->add_html_editor('survey_introduction',get_lang('SurveyIntroduction'), false, false, array('ToolbarSet' => 'Survey', 'Width' => '750', 'Height' => '75','Placeholder' => get_lang("WriteSurveyIntroHere")));
$form->add_html_editor('survey_thanks',get_lang('SurveyThanks'), false, false, array('ToolbarSet' => 'Survey', 'Width' => '750', 'Height' => '75'));

if (api_get_setting('search_enabled') == 'true') {
   //TODO: include language file
    $form->addElement('hidden','index_document',1);
    $form->addElement('hidden','language',api_get_setting('platformLanguage'));
    //$form->addElement('textarea','search_terms',get_lang('SearchKeywords'),array ('cols' => '65'));
    if (extension_loaded('xapian')) {
        $form->addElement('text', 'search_terms', '<br/>' . get_lang('SearchKeywords') . ': ', array('class' => 'tag-it'));
    }
}

// Personality/Conditional Test Options
$surveytypes[0] = get_lang('Normal');
$surveytypes[1] = get_lang('Conditional');


if ($_GET['action'] == 'add') {
 $form->addElement('hidden', 'survey_type', 0);
 $form->addElement('html', '<div id="options" style="display: none;">');
 require_once(api_get_path(LIBRARY_PATH) . 'surveymanager.lib.php');
 $survey_tree = new SurveyTree();
 $list_surveys = $survey_tree->createList($survey_tree->surveylist);
 $list_surveys[0] = '';
 $form->addElement('select', 'parent_id', get_lang('ParentSurvey'), $list_surveys);
 $defaults['parent_id'] = 0;
}

if ($survey_data['survey_type'] == 1 || $_GET['action'] == 'add') {
 $form->addElement('checkbox', 'one_question_per_page', get_lang('OneQuestionPerPage'));
 $form->addElement('checkbox', 'shuffle', get_lang('ActivateShuffle'));
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && !empty($survey_id)) {
 if ($survey_data['anonymous'] == 0) {  

  $form->addElement('html', '<div id="options" style="display:none">');
  $form->addElement('checkbox', 'show_form_profile', get_lang('ShowFormProfile'), '', 'onclick="javascript:if(this.checked==true){document.getElementById(\'options_field\').style.display = \'block\';}else{document.getElementById(\'options_field\').style.display = \'none\';}"');

  if ($survey_data['show_form_profile'] == 1) {
   $form->addElement('html', '<div id="options_field" style="display:block">');
  } else {
   $form->addElement('html', '<div id="options_field" style="display:none">');
  }

  $field_list = SurveyUtil::make_field_list();
  if (is_array($field_list)) {
   //TODO hide and show the list in a fancy DIV
   foreach ($field_list as $key => $field) {
    if ($field['visibility'] == 1) {
     $form->addElement('checkbox', 'profile_' . $key, ' ', '&nbsp;&nbsp;' . $field['name']);
     $input_name_list.= 'profile_' . $key . ',';
    }
   }
   // necesary to know the fields
   $form->addElement('hidden', 'input_name_list', $input_name_list);

   //set defaults form fields
   if ($survey_data['form_fields']) {
    $form_fields = explode('@', $survey_data['form_fields']);
    foreach ($form_fields as $field) {
     $field_value = explode(':', $field);
     if ($field_value[0] != '' && $field_value[1] != '') {
      $defaults[$field_value[0]] = $field_value[1];
     }
    }
   }
  }
  $form->addElement('html', '</div></div>');
  $form->addElement('html', '<div class="row"></div>');
 }
}
if ($_GET['action'] == 'add') {
 $form->addElement('html', '</div><br />');
}
if (isset($_GET['survey_id']) && $_GET['action'] == 'edit') {
 $class = "save";
 $text = get_lang('ModifySurvey');
} else {
 $class = "save";
 $text = get_lang('CreateSurvey');
}
$form->addElement('html','<div class="pull-bottom">');
$form->addElement('style_submit_button', 'submit_survey', $text, 'class="' . $class . '"');
$form->addElement('html','</div>');

// setting the rules
if ($_GET['action'] == 'add') {
    
}
$form->addRule('survey_title', '<div class="required">' . get_lang('ThisFieldIsRequired'), 'required');
//$form->addRule('start_date', get_lang('InvalidDate'), 'date');
//$form->addRule('end_date', get_lang('InvalidDate'), 'date');
//$form->addRule(array('start_date', 'end_date'), get_lang('StartDateShouldBeBeforeEndDate'), 'date_compare', 'lte');

// setting the default values
$form->setDefaults($defaults);

// The validation or display
if ($form->validate()) {
 // exporting the values
 $values = $form->exportValues();
 // storing the survey
 $return = survey_manager::store_survey($values);
 $_SESSION["survey_message"] = get_lang($return['message']);
 $survey_id = $return['id'];
 $survey_data = (object)$return;
 survey_manager::save_survey_into_learning_path($survey_data);

 if ($return['type'] == 'error') {
  // Displaying the header
  Display::display_tool_header($tool_name);

  // Displaying the tool title
  //api_display_tool_title($tool_name);
  // display the error
  Display::display_error_message(get_lang($return['message']), false, true);

  // start the content div
  echo '<div id="content">';
  
  // display the form
  $form->display();
  
  // close the content div
  echo '</div>';
 }
 if ($config['survey']['debug']) {
  // displaying a feedback message
  Display::display_confirmation_message($return['message'], false, true);
 } else {
  // redirecting to the survey page (whilst showing the return message
  header('location:survey.php?'.  api_get_cidreq().'&survey_id=' . $return['id'] . '&message=' . $return['message'] . $add_lp_param);
 }
} else {
 // Displaying the header
 Display::display_tool_header($tool_name);
// Tool introduction
Display::display_introduction_section('survey', 'left');
 // Displaying the tool title
 //api_display_tool_title($tool_name);
 // actions
 $actions = '<div class="actions">';
 $actions .= '<a href="survey_list.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('BackToSurvey'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('BackToSurvey') . '</a>';
 $actions .= '</div>';
 echo $actions;

 // start the content div
 echo '<div id="content">';
if (isset($_SESSION['display_normal_message'])) {
    display::display_normal_message($_SESSION['display_normal_message'], false, true);
    unset($_SESSION['display_normal_message']);
}
if (isset($_SESSION['display_warning_message'])) {
    display::display_warning_message($_SESSION['display_warning_message'], false, true);
    unset($_SESSION['display_warning_message']);
}
if (isset($_SESSION['display_confirmation_message'])) {
    display::display_confirmation_message($_SESSION['display_confirmation_message'], false, true);
    unset($_SESSION['display_confirmation_message']);
}
if (isset($_SESSION['display_error_message'])) {
    display::display_error_message($_SESSION['display_error_message'], false, true);
    unset($_SESSION['display_error_message']);
}
 // display the form
 $form->display();
    echo '<div id="loading" style=\'text-align: center;width:100%;display:none;\'><img style=\' margin: 50px auto auto;\' src=\''.api_get_path(WEB_CODE_PATH).'img/progress_bar.gif\' /></div>';
 // close the content div
 echo '</div>';

}
// echo '<div class="actions">';
// echo '&nbsp;';
// echo '</div>';
// Footer
Display :: display_footer();
?>