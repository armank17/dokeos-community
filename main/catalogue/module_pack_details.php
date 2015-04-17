<?php


/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array ('index', 'admin');
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(SYS_PATH).'main/core/model/ecommerce/EcommerceCatalog.php';

$this_section = SECTION_CAMPUS;
$tool_name = get_lang('ModulePackOverwiev');

// Database Table Definitions
$tblEcommerceLpModulePack = Database::get_main_table( TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS );
$tblModuleMa¡n = Database::get_main_table(TABLE_MAIN_ECOMMERCE_LP_MODULE);
// clear pre proccess order

$moduleCode = (isset($_REQUEST['id'])) ? trim( $_REQUEST['id'] ) : NULL;

if ( is_null( $moduleCode ) || $moduleCode == '' )
{
    header('location: ../../index.php');
} 
 
$moduleDetailsObj = EcommerceCatalog::create()->getModuleByCodeObj($moduleCode);

if (  ! is_object($moduleDetailsObj ) || $moduleDetailsObj === false )
{
    header('location: ../../index.php');
}

$module_id = $moduleDetailsObj->id;
$sym_dec = $row['currency'] == '978'?',':'.';
$priceCourse = number_format( $moduleDetailsObj->cost, 2, $sym_dec, ' ');
$currencyCourse = $row['currency']=='978'?'&euro;':'$';

$courseAvailable = (intval($moduleDetailsObj->status, 10) == 1 ) ? 'Yes': 'No';

$langDateStart = get_lang('Start');
$langDateEnd = get_lang('End');
$langLanguage = get_lang('Language');
$langCourse = get_lang('Course');
$langQuantity = get_lang('Quantity');

list($Year,$Month,$Day) = split('-',$moduleDetailsObj->date_start); 
$start_date = mktime(12,0,0,$Month,$Day,$Year); 
$start_date = date("F jS, Y", $start_date);

$start_dateyear = explode(',',$start_date); 
$month = explode(' ',$start_dateyear[0]); 
$start = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;

list($Year,$Month,$Day) = split('-',$moduleDetailsObj->date_end); 
$end_date = mktime(12,0,0,$Month,$Day,$Year); 
$end_date = date("F jS, Y", $end_date);

$end_dateyear = explode(',',$end_date); 
$month = explode(' ',$end_dateyear[0]); 
$end = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;
$quantity = 0;

$sql = "SELECT * FROM $tblEcommerceLpModulePack WHERE ecommerce_items_id = '$module_id'";
$result = Database::query( $sql, __FILE__, __LINE__ ); 
$items = Database::fetch_row($result);
$quantity = sizeof($items).' '.get_lang('Modules');

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
Display::display_header($tool_name);

echo '<div id="content">';
api_display_tool_title($tool_name);
?>
<br/>
<div class="section">
<div class="sectiontitle"><?php echo $moduleDetailsObj->code ?></div>	
<div class="sectionvalue">
<table class="data_table" width="100%" cellpadding="3">
<tr>
	<td width="15%" align="right"><?php echo get_lang('ModulesPackName');?> :</td>
	<td width="85%"><?php echo $moduleDetailsObj->code ?></td>
</tr>
<tr>
	<td width="15%" align="right"><?php echo get_lang('Availability'); ?> :</td>
	<td width="85%"><?php echo $courseAvailable; ?></td>
</tr>
<tr>
	<td width="15%" align="right"><?php echo get_lang('Agenda'); ?> :</td>
	<td width="85%">
	<?php
		if($start=='00-00-0000')
			echo get_lang('NoTimeLimits');
		else
			echo get_lang('From').' '.$start.' '.get_lang('To').' '.$end;
		 ?>
	</td>
</tr>
<tr>
	<td width="15%" align="right"><?php echo get_lang('Price') ?></td>
	<td width="85%"><?php echo $currencyCourse.' '.$priceCourse;  ?></td>
</tr>
</table>
</div>
</div>
<!--List of Modules -->
<table style="width:100%" id="table_question_list" class="data_table data_table_exercise">
    <tr>
  <td colspan="4"><div class="row"><div class="form_header"><?php echo get_lang('Modules'); ?>
  	</div></div></td>  
</tr>
  <tr>
  <th width="25%"><?php echo get_lang('Module'); ?></th>
  <th width="15%"><?php echo get_lang('course'); ?></th>
</tr>
</table>

<div id="contentWrap"><div id="contentLeft"><ul id="categories" class="dragdrop nobullets  ui-sortable">
                          
             <?php
             
             $tblModuleMain = Database::get_main_table( TABLE_MAIN_ECOMMERCE_LP_MODULE);
             $tblCourseMain = Database::get_main_table( TABLE_MAIN_COURSE);
             $tblModuleMainPacks = Database::get_main_table( TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS);
        
             $sql ="SELECT DISTINCT m.lp_module_id,m.course_code,m.lp_title,c.title FROM $tblModuleMain AS m 
                    JOIN $tblModuleMainPacks AS mp ON m.lp_module_id = mp.lp_module_lp_module_id 
                    JOIN $tblCourseMain AS c ON m.course_code = c.code
                    AND m.course_code = mp.lp_module_course_code 
                    AND mp.ecommerce_items_id = $module_id                 
                    ORDER BY course_code";

            $result=Database::query($sql,__FILE__,__LINE__);
            $modules=Database::store_result($result);
             
            foreach ($modules as $module) {
                if($i%2 == 0){
			$class = "row_odd";
		}
		else {
			$class = "row_even";
		}
               
                echo '<li id="recordsArray" class="category" style="opacity: 1;">';
                echo '<div>';                
                echo '<table width="100%" class="data_table">	
		<tr class="'.$class.'">                  
                    <td width="25%">'.$module['lp_title'].'</td>
                    <td width="10%">'.$module['title'].'</td>
		</tr></table>';
		$i++;
                
                echo '</div>';
                echo '</li>';
   
            }
            
             ?>
<br />
<?php
// End content
echo '</div>'; 
Display :: display_footer();
?>
