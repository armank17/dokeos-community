<?php
/* For licensing terms, see /license.txt */

/**
* Layout (principal view) used for structuring other views  
* @author Alberto Flores <aflores609@gmail.com>
*/

// protect a course script
api_protect_course_script(true);

// Header
Display :: display_tool_header('');

// Introduction section
Display::display_introduction_section(TOOL_COURSE_DESCRIPTION);

// Tracking
event_access_tool(TOOL_COURSE_DESCRIPTION);

// setting the tool constants
$tool = TOOL_COURSE_DESCRIPTION;

$description_id = isset ($_REQUEST['description_id']) ? Security::remove_XSS($_REQUEST['description_id']) : null;

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

$courseDescriptionController = new CourseDescriptionController($description_id);

if (api_is_allowed_to_edit()) {
echo '<div class="actions">';
if(isset($_GET['action']) AND $_GET['action']== 'add' ){
    echo '<a href="'.api_get_self().'?'.api_get_cidreq().'">'.  Display::return_icon('pixel.gif',get_lang('Description'), array('class' => 'toolactionplaceholdericon toolactionlist')).get_lang('Description').'</a>';
}

$courseDescriptionController->display_action($default_description_titles,$default_description_class);
echo '</div>';
}

if(isset($_SESSION["display_confirmation_message"])){
    Display :: display_confirmation_message2($_SESSION["display_confirmation_message"],false,true);
    unset($_SESSION["display_confirmation_message"]);
}
echo '<div id="content">';
if(isset($_SESSION["display_normal_message"])){
    Display :: display_normal_message($_SESSION["display_normal_message"],false,true);
    unset($_SESSION["display_normal_message"]);
}
if(isset($_SESSION["display_warning_message"])){
    Display :: display_warning_message($_SESSION["display_warning_message"],false,true);
    unset($_SESSION["display_warning_message"]);
}
if(isset($_SESSION["display_error_message"])){
    Display :: display_error_message($_SESSION["display_error_message"],false,true);
    unset($_SESSION["display_error_message"]);
}
echo '<br>';
echo $content;
echo '</div>';
// Footer
Display :: display_footer();