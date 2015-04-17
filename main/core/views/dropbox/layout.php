<?php
/* For licensing terms, see /license.txt */

/**
* Layout (principal view) used for structuring other views  
* @author Alberto Flores <aflores609@gmail.com>
*/

// setting the tool constants
$tool = TOOL_DROPBOX;

$dropboxController = new DropboxController();
// Tool introduction
Display::display_introduction_section(TOOL_DROPBOX);

echo '<div class="actions">';
$dropboxController->display_action();
echo '</div>';
if(isset($_SESSION["display_confirmation_message"])){
    if( $_SESSION["display_confirmation_message"]['type'] == 'confirmation')
    {
            Display :: display_confirmation_message2($_SESSION["display_confirmation_message"]['message']);
    }
    if( $_SESSION["display_confirmation_message"]['type'] == 'error')
    {
            Display :: display_error_message2(get_lang('FormHasErrorsPleaseComplete').'<br />'.$message['message']);			
    }
    unset($_SESSION["display_confirmation_message"]);
}
echo '<div id="content">';

echo $content;

echo '</div>';


// secondary actions
//echo '<div class="actions"> </div>';

// Footer
//Display :: display_footer();
