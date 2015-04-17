<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Bart Mollet
* @package dokeos.admin
*/

// resetting the course id
$cidReset = true;

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// setting the help
$help_content = 'platformadministrationsessionadd';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// setting breadcrumbs
$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => "session_list.php","name" => get_lang('SessionList'));

// Database Table Definitions
$tbl_user		= Database::get_main_table(TABLE_MAIN_USER);
$tbl_session_category 	= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
$tbl_session	= Database::get_main_table(TABLE_MAIN_SESSION);

$user_id = api_get_user_id();

// clear pre proccess order
SessionManager::clear_catalogue_order_process();

//display the header
Display::display_header(get_lang('TrainingCategory'));

if (isset($_GET['res_cheque'])) {
    echo '<div id="content">';
    if (intval($_GET['res_cheque']) == 1) {        
        
        echo '<div class="normal-message">'.get_lang('YourDataHasBeenSavedTheAdministratorWillActivateYourAccount').'</div>';        
    }
    else {
        echo '<div class="error-message">'.get_lang('YourOperationHasFailed').'</div>';
    }
    echo '</div>';
    exit;
}

echo '<div class="actions">';
echo '<a href="javascript:void(0)">'.Display::return_icon('pixel.gif', get_lang("Catalogue"), array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('Catalogue').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/installment_payment.php">'.Display::return_icon('pixel.gif', get_lang("InstallmentPaymentInfo"), array('class' => 'toolactionplaceholdericon toolactionother')).get_lang('InstallmentPaymentInfo').'</a>&nbsp;';
echo '</div>';

// start the content div
echo '<div id="content">';
$topic_table 		= Database :: get_main_table(TABLE_MAIN_TOPIC);
$tbl_session_category 	= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
$catalogue_table 	= Database :: get_main_table(TABLE_MAIN_CATALOGUE);
$tbl_sess_cat_rel_user  = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_USER);

$sql_topic = "SELECT * FROM $topic_table WHERE visible = 1";
$res_topic = Database::query($sql_topic,__FILE__,__LINE__);
$num_topic = Database::num_rows($res_topic);

$sql_catalogue = "SELECT title FROM $catalogue_table";
$res_catalogue = Database::query($sql_catalogue,__FILE__,__LINE__);
$row_catalogue = Database::fetch_array($res_catalogue);

// Display courses and category list
echo '<div style="width:95%;padding-left:20px;"><div class="section" style="padding-left:10px;line-height:2.5em;">';
echo '<div class="row"><div class="form_header">'.$row_catalogue['title'].'</div></div>';
echo '<div><table width="100%">';
$i = 0;
while ($row_topic = Database::fetch_array($res_topic)) {
        $topic_id = $row_topic['id'];
        $topic = $row_topic['topic'];
        $sql_programme = "SELECT id,name FROM $tbl_session_category WHERE visible = 1 AND id NOT IN(SELECT category_id FROM $tbl_sess_cat_rel_user WHERE user_id=".api_get_user_id().") AND topic = ".$topic_id;			
        $rs_programme = Database::query($sql_programme,__FILE__,__LINE__);
        $num_programme = Database::num_rows($rs_programme);
        echo '<script>
        $(document).ready(function(){			
        $("a#show'.$i.'").click(function () {								
                                for(var k=0;k<='.$num_topic.';k++){							
                                        if(k == '.$i.'){								
                                                $("#show_programme"+k).show();								
                                        }
                                        else {
                                                $("#show_programme"+k).hide();	
                                        }
                                }
                        });			
        });       
        </script>';
        if($i%2 == 0){
        echo '<tr>';
        }
        echo '<td width="50%" valign="top"><div><a href="#" id="show'.$i.'">'.Display::return_icon('topic.png', '', array('style'=>'vertical-align:middle')).'&nbsp;&nbsp;<font size="2">'.$topic.'&nbsp;&nbsp;('.$num_programme.')</font></a></div>';
        echo '<div id= "show_programme'.$i.'" style="display:none;padding-left:12px;">';
        //$j = 1;
        $items = array();
        while($row_programme = Database::fetch_row($rs_programme)){            
            $rs_user_rel_cat = Database::query("SELECT * FROM $tbl_sess_cat_rel_user WHERE user_id=".api_get_user_id());                
            if (Database::num_rows($rs_user_rel_cat) > 0) {
                //continue;
            }            
            $items[] = '<a href="'.api_get_path(WEB_CODE_PATH).'catalogue/session_details.php?id='.$row_programme[0].'">'.$row_programme[1].'</a>';                
            //if($j<$num_programme)echo '&nbsp;-&nbsp;';
            //$j++;                
        }
        echo implode('-', $items);
        echo '</div></td>';
        if($i%2 <> 0){
                echo '</tr>';
        }
        $i++;
}
echo '</table></div>';
echo '</div></div>';

echo '</div>';

// display the footer
Display::display_footer();
?>