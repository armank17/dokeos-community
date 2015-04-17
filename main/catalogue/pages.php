<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array ('index', 'admin');

// resetting the course id
$cidReset = true;

require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';


/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/

$tool_name = get_lang("SystemAnnouncements");
Display::display_header($tool_name);

// get catalogue information
$category_id = intval($_GET['cat_id']);
$catalogue = SessionManager::get_catalogue_info_by_category($category_id);

echo '<div class="actions">';    
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'catalogue/session_details.php?id='.$category_id.'">'.Display::return_icon('back.png').get_lang('Back').'</a>';
echo '</div>';

echo '<div id="content">';

    echo '<div id="content-dynamic-page" style="padding:20px;">';
        if (isset($_GET['page'])) {
            switch ($_GET['page']) {
                case 'terms_conditions':
                    echo $catalogue['terms_conditions'];
                    break;
                case 'tva':
                    echo $catalogue['tva_description'];
                    break;
            }
        }
    echo '</div>';

echo '</div>';


Display::display_footer();
?>

