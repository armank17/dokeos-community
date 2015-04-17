<?php
/* For licensing terms, see /license.txt */

/**
* Layout (principal view) used for structuring other views
* @author Isaac flores <florespaz_isaac@hotmail.com>
*/

// protect a course script
api_protect_course_script(true);

// Header
Display :: display_tool_header('');

// Introduction section
Display::display_introduction_section(TOOL_DOCUMENT);

// Tracking
event_access_tool(TOOL_GLOSSARY);

// setting the tool constants
$tool = TOOL_GLOSSARY;
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
// Display
echo '<div class="actions" style="min-height: 40px;">';
echo '<ul class="new_li_actions">';
echo '<li><a href="' . api_get_path(WEB_CODE_PATH) . 'document/document.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . ' ' . get_lang('Documents') . '</a></li>';
if (api_is_allowed_to_edit(null,true)) {

    if (isset($_GET['action'])) {
        echo '<a href="'.api_get_self().'?'.api_get_cidreq().'">'.Display::return_icon('pixel.gif', get_lang('Glossary'), array('class' => 'toolactionplaceholdericon toolactionglossary')).' '.get_lang('Glossary').'</a>';
    }
    echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=add">'.Display::return_icon('pixel.gif', get_lang('NewTerm'), array('class' => 'toolactionplaceholdericon toolglossaryadd')) .'&nbsp;&nbsp;'.get_lang('NewTerm').'</a>';
    echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=import">'.Display::return_icon('pixel.gif',get_lang('ImportGlossaryTerms'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')).' '.get_lang('ImportGlossaryTerms').'</a>';
    echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=export">'.Display::return_icon('pixel.gif',get_lang('ExportGlossaryTerms'), array('class' => 'toolactionplaceholdericon toolactionauthorexport')).' '.get_lang('ExportGlossaryTerms').'</a>';
}
echo '</ul>';
echo '</div>';
echo '<div id="content" class="rel">';
echo $content;
echo '</div>';

  echo '<div class="actions">';
DocumentManager::show_simplifying_links(true, true);
  echo '</div>';  

// Footer
Display :: display_footer();