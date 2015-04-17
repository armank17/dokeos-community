<?php


/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array ('index', 'admin');
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(SYS_PATH).'main/core/model/ecommerce/EcommerceCatalog.php';

$this_section = SECTION_CAMPUS;
$tool_name = get_lang('CourseOverview');

$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

// clear pre proccess order
//SessionManager::clear_catalogue_order_process();

$courseCode = (isset($_REQUEST['course_code'])) ? trim( $_REQUEST['course_code'] ) : NULL;

if ( is_null( $courseCode ) || $courseCode == '' )
{
    header('location: ../../index.php');
} 
  
$courseDetailsObj = EcommerceCatalog::create()->getCourseByCodeObj($courseCode);

if (  ! is_object($courseDetailsObj ) || $courseDetailsObj === false )
{
    header('location: ../../index.php');
}

$courseObj = $courseDetailsObj->getCourseFull();

$catalogue = SessionManager::get_catalogue_info();
$catalogue = ( isset($catalogue[1]) ) ? $catalogue[1] : $catalogue;
$config = array();
if (!empty($catalogue['catalogue_display'])) {
    $config = explode(', ', $catalogue['catalogue_display']);
}

$languageCourse = ucfirst($courseObj->course_language);
$languages = api_get_languages();
$sym_dec = $row['currency'] == '978'?',':'.';
$priceCourse = number_format( $courseDetailsObj->cost, 2, $sym_dec, ' ');
$currencyCourse = $row['currency']=='978'?'&euro;':'$';

// set currency
$currencyIsoCode = CatalogueFactory::getObject()->getDefaultCatalogue();
$_SESSION['shopping_cart']['currency'] = Currency::create()->getCurrencyByIsoCode(api_get_setting('e_commerce_catalog_currency'));            

$currencyCode = $_SESSION['shopping_cart']['currency']['code'];
$currencySymbol = $_SESSION['shopping_cart']['currency']['symbol'];
$priceCourse = ($currencyCode == 'EUR')?$priceCourse.' '.$currencySymbol:$currencySymbol.' '.$priceCourse;

$courseAvailable = (intval($courseDetailsObj->status, 10) == 1 ) ? 'Yes': 'No';

$langAvailability = get_lang('Availability');
$langDateStart = get_lang('Start');
$langDateEnd = get_lang('End');

list($Year,$Month,$Day) = split('-',$courseDetailsObj->date_start); 
$start_date = mktime(12,0,0,$Month,$Day,$Year); 
$start_date = date("F jS, Y", $start_date);

$start_dateyear = explode(',',$start_date); 
$month = explode(' ',$start_dateyear[0]); 
$start = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;

list($Year,$Month,$Day) = split('-',$courseDetailsObj->date_end); 
$end_date = mktime(12,0,0,$Month,$Day,$Year); 
$end_date = date("F jS, Y", $end_date);

$end_dateyear = explode(',',$end_date); 
$month = explode(' ',$end_dateyear[0]); 
$end = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
Display::display_header($tool_name);
echo '<div class="actions">';
echo '<a href="'.  api_get_path(WEB_PATH).'index.php" >'.Display::return_icon('pixel.gif', get_lang('Back'), array('class'=> 'toolactionplaceholdericon toolactionback')).get_lang('Back').'</a>';
echo '</div>';

echo '<div id="content">';
api_display_tool_title($tool_name);
?>
<br/>
<div class="section">
<div class="sectiontitle"><?php echo $courseObj->title; ?></div>	
<div class="sectionvalue">
<table class="data_table" width="100%" cellpadding="3">
<tr>
	<td width="15%" align="right"><?php echo get_lang('Description');?> :</td>
	<td width="85%"><?php echo $courseObj->description ?></td>
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
	<td width="15%" align="right"><?php echo get_lang('Language'); ?> :</td>
	<td width="85%"><?php echo $languageCourse; ?></td>
</tr>
<tr>
	<td width="15%" align="right"><?php echo get_lang('Price') ?></td>
	<td width="85%"><?php echo $priceCourse;  ?></td>
</tr>
</table>
</div>
</div>
<?php
Display :: display_footer();
?>
