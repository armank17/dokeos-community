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
Display::display_introduction_section(TOOL_ANNOUNCEMENT);

// Tracking
event_access_tool(TOOL_ANNOUNCEMENT);

// setting the tool constants
$tool = TOOL_ANNOUNCEMENT;

// Display

if(api_is_allowed_to_edit()){
   echo '<div class="actions">';
   echo '<a href="index.php?'.api_get_cidreq().'&amp;action=add">'.Display::return_icon('pixel.gif', get_lang('AddAnnouncement'), array('class' => 'toolactionplaceholdericon toolactionannoucement')).get_lang('AddAnnouncement').'</a>';
   echo '<a href="index.php?'.api_get_cidreq().'&amp;action=showAll">'.Display::return_icon('pixel.gif', get_lang('ShowAllAnnouncement'), array('class' => 'toolactionplaceholdericon toolactionlist')).get_lang('ShowAllAnnouncement').'</a>';
   echo '</div>';
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

echo $content;
echo '</div>';
if(isset($_SESSION["display_confirmation_message"])){
    Display :: display_confirmation_message2($_SESSION["display_confirmation_message"],false,true);
    unset($_SESSION["display_confirmation_message"]);
}
// Footer
Display :: display_footer();