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
Display::display_introduction_section(TOOL_NOTEBOOK);

// Tracking
event_access_tool(TOOL_NOTEBOOK);

// setting the tool constants
$tool = TOOL_NOTEBOOK;

// Display
echo '<div class="actions">';
if (!api_is_anonymous(api_get_user_id(),true)) {
  echo '<a href="index.php?'.api_get_cidreq().'&amp;action=add">'.Display::return_icon('pixel.gif', get_lang('NewNote'), array('class' => 'toolactionplaceholdericon tooladdnewnote')).get_lang('NewNote').'</a>';
  //echo '<a href="index.php?'.api_get_cidreq().'&amp;action=listview">'.Display::return_icon('pixel.gif', get_lang('List'), array('class' => 'toolactionplaceholdericon toolactionlist')).get_lang('List').'</a>';
}
//echo '<a href="index.php?'.api_get_cidreq().'&amp;action=view">'.Display::return_icon('pixel.gif', get_lang('BookView'), array('class' => 'toolactionplaceholdericon tooladdviewnote')).get_lang('BookView').'</a>';
//echo '<a href="#"  onclick="runDialog()">'.Display::return_icon('pixel.gif', get_lang('SearchNote'), array('class' => 'toolactionplaceholdericon tooladdsearchnote')).get_lang('SearchNote').'</a>';
echo '</div>';

echo '<div id="content">';


echo $content;

echo '</div>';


// secondary actions
echo '<div class="actions"> </div>';

// Footer
Display :: display_footer();



        