<?php
/* For licensing terms, see /license.txt */

/**
* Layout (principal view) used for structuring other views  
* @author Alberto Flores <aflores609@gmail.com>
*/

// setting the section (for the tabs)
$this_section = SECTION_COURSES;

define('DOKEOS_LINK', true);

// Access restrictions
api_protect_course_script(true);

// Header
if(isset($_GET['action']) && $_GET['action'] == 'integrateincourse' && $_GET['done'] != 'Y'){

	if(!api_is_allowed_to_edit()) {
	  api_not_allowed(true);
	}
	Display :: display_reduced_header();
	// setting the tool constants
	$tool = TOOL_LINK;
	echo $content;
}
else {
        
	Display :: display_tool_header('');

	// Introduction section
	Display::display_introduction_section(TOOL_DOCUMENT);

	// setting the tool constants
	$tool = TOOL_LINK;
        require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
	$linkController = new LinkController();
       
	echo '<div class="actions">';
        echo '<ul class="new_li_actions" style="min-height: 40px;">';
        $linkController->display_action();
        //DocumentManager::show_simplifying_links(!api_is_allowed_to_edit(), false);
        echo '</ul>';
	echo '</div>';

	echo '<div id="content">';
	echo $content;
	echo '</div>';
}

echo '<div class="link_to_course_dialog" style="display:none;"> </div>';

echo '<div class="actions">';
DocumentManager::show_simplifying_links(true, true);
echo '</div>';

// Footer
Display :: display_footer();
