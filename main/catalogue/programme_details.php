<?php
/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = array ('index', 'admin');
$cidReset = true;
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';


$this_section = SECTION_CAMPUS;

$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

// clear pre proccess order
SessionManager::clear_catalogue_order_process();

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
$tool_name = get_lang("Catalogue");
Display::display_header($tool_name);

echo '<style>		
div.row {
    width: 900px;						
}
div.row div.label {
    width: 375px; 					
} 
div.row div.formw {
    width: 500px; 					
}
</style>';

$topic_table 		= Database :: get_main_table(TABLE_MAIN_TOPIC);
$tbl_session_category 	= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
$session_rel_category 		= Database :: get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);

//$sql = "SELECT prg.*,count(DISTINCT(prs.session_set)) AS no_set,tp.topic AS topic FROM $programme_table prg,$programme_rel_session prs,$topic_table tp WHERE prg.topic = tp.id AND prg.id = prs.programme_id AND prg.id = ".$_REQUEST['id'];
$sql = "SELECT sc.*,count(DISTINCT(src.session_set)) AS no_set, tp.id as topic_id, tp.topic AS topic FROM $tbl_session_category sc,$session_rel_category src,$topic_table tp WHERE session_set <> '' AND sc.topic = tp.id AND sc.id = src.category_id AND sc.id = ".intval($_REQUEST['id']);
$rs = Database::query($sql,__FILE__,__LINE__);
$row = Database::fetch_array($rs);

list($Year,$Month,$Day) = split('-',$row['date_start']); 
$start_date = mktime(12,0,0,$Month,$Day,$Year); 
$start_date = date("F jS, Y", $start_date);

$start_dateyear = explode(',',$start_date); 
$month = explode(' ',$start_dateyear[0]); 
$start = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;

list($Year,$Month,$Day) = split('-',$row['date_end']); 
$end_date = mktime(12,0,0,$Month,$Day,$Year); 
$end_date = date("F jS, Y", $end_date);

$end_dateyear = explode(',',$end_date); 
$month = explode(' ',$end_dateyear[0]); 
$end = $Day.'&nbsp;'.get_lang($month[0].'Long').'&nbsp;'.$Year;

if (api_get_user_id()) {
    echo '<div class="actions">';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/session_category_payments"">'.Display::return_icon('pixel.gif', get_lang("Catalogue"), array('class' => 'toolactionplaceholdericon toolactioncatalogue')).get_lang('Catalogue').'</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH).'payment/installment_payment.php">'.Display::return_icon('pixel.gif', get_lang("InstallmentPaymentInfo"), array('class' => 'toolactionplaceholdericon toolactionother')).get_lang("InstallmentPaymentInfo").'</a>&nbsp;';
    echo '</div>';
}

$topic     = SessionManager::get_topic_info($row['topic_id']);
$catalogue = SessionManager::get_catalogue_info($topic['catalogue_id']);

$config = array();
if (!empty($catalogue['catalogue_display'])) {
    $config = explode(', ', $catalogue['catalogue_display']);
}

echo '<div id="content">';
if (in_array('ProgrammeName', $config)) {
    echo '<div class="row"><div class="form_header">'.$row['name'].'</div></div>';
}
if(!empty($row['description'])) {
    if (in_array('Description', $config)) {
        echo '<div class="quiz_content_actions">'.$row['description'].'</div>';
    }
}
echo '<div class="quiz_content_actions" style="overflow:hidden; padding-bottom: 20px; position:relative;'.(in_array('Image', $config)?" min-height:250px":"").'">';

if (in_array('Image', $config)) {    
    // resize image 250x220
    $filename = api_get_path(SYS_PATH).'home/default_platform_document/'.$catalogue['company_logo'];
    $imagesize = getimagesize($filename);
    $w = $h = '';
    if ($imagesize[0] > 250) { $w = ' width="250px"'; }
    if ($imagesize[1] > 220) { $h = ' width="220px"'; }
    echo '<div class="catalogue-detail-image-"><img src="'.  api_get_path(WEB_PATH).'home/default_platform_document/'.$catalogue['company_logo'].'" '.($w.$h).' /></div>';
}    
if (in_array('Topic', $config)) {
    echo '<div class="row"><div class="label"><b>'.get_lang('Topic').'</b></div><div class="formw">'.$row['topic'].'</div></div>';
}
if (in_array('Location', $config)) {
    echo '<div class="row"><div class="label"><b>'.get_lang('Location').'</b></div><div class="formw">'.$row['location'].'</div></div>';
}
if (in_array('Modality', $config)) {
    echo '<div class="row"><div class="label"><b>'.get_lang('Modality').'</b></div><div class="formw">'.$row['modality'].'</div></div>';
}
if (in_array('Availability', $config)) {
    echo '<div class="row"><div class="label"><b>'.get_lang('Availability').'</b></div><div class="formw">'.($row['visible']?get_lang('Yes'):get_lang('No')).'</div></div>';
}
if (in_array('Start', $config)) {
    echo '<div class="row"><div class="label"><b>'.get_lang('Start').'</b></div><div class="formw">'.$start.'</div></div>';
}
if (in_array('End', $config)) {
    echo '<div class="row"><div class="label"><b>'.get_lang('End').'</b></div><div class="formw">'.$end.'</div></div>';
}

if (in_array('Language', $config)) {
    //echo '<div class="row"><div class="label"><b>'.get_lang('Language').'</b></div><div class="formw">'.$row['language'].'</div></div>';    
    $languages = api_get_languages();
    echo '<div class="row"><div class="label"><b>'.get_lang('Language').'</b></div><div class="formw">'.(($row['language'] == 'french') ? $languages['name'][0]:$row['language']).'</div></div>';
}
//echo '<div class="row"><div class="label"><b>'.get_lang('Access').'</b></div><div class="formw">'.$row['student_access'].'&nbsp;'.get_lang('Days').'</div></div>';
if (in_array('Price', $config)) {
    $sym_dec = $row['currency'] == '978'?',':'.';
    echo '<div class="row"><div class="label"><b>'.get_lang('PriceNoExt').'</b></div><div class="formw">'.  number_format(SessionManager::get_user_amount_pay_atos($row['cost'], '001'), 2, $sym_dec, ' ').'&nbsp;'.($row['currency']=='978'?'&euro;':'$').' TTC ('.number_format($row['cost'], 2, $sym_dec, ' ').' '.($row['currency']=='978'?'&euro;':'$').' HT)</div></div>';
}
//echo '<div class="row"><div class="label">&nbsp;</div><div class="formw">Les <b>prix TTC</b> sont indiqués pour la France et l\'Union Européenne.<br>Pour l\'Europe hors UE, les DOM-TOM et autres pays, <a href="pages.php?page=terms_conditions&cat_id='.intval($_GET['id']).'" style="color: rgb(184, 65, 75);">le prix TTC adéquat sera indiqué lors de la finalisation de votre inscription.</a></div></div>';
echo '<br/><br/></div>';

if (isset($row['inscription_date_start'])) {
    // Calendar
    echo '<div class="row"><div class="form_header">'.get_lang('InscriptionCalendar').'</div></div>';
    // Start date inscription 
    list($Year1,$Month1,$Day1) = split('-', $row['inscription_date_start']); 
    $ins_start_date = mktime(12,0,0,$Month1,$Day1,$Year1); 
    $ins_start_date = date("F jS, Y", $ins_start_date);

	$ins_start_dateyear = explode(',',$ins_start_date); 
	$month1 = explode(' ',$ins_start_dateyear[0]); 
	$ins_start = $Day1.'&nbsp;'.get_lang($month1[0].'Long').'&nbsp;'.$Year1;

	list($Year2,$Month2,$Day2) = split('-',$row['inscription_date_end']); 
	$ins_end_date = mktime(12,0,0,$Month2,$Day2,$Year2); 
	$ins_end_date = date("F jS, Y", $ins_end_date);

	$ins_end_dateyear = explode(',',$ins_end_date); 
	$month2 = explode(' ',$ins_end_dateyear[0]);
	$ins_end = $Day2.'&nbsp;'.get_lang($month2[0].'Long').'&nbsp;'.$Year2;

	$today = strtotime("now");	
	$start_date = strtotime($ins_start_date);	
	$end_date = strtotime($ins_end_date);	

    echo '<div class="quiz_content_actions">'.get_lang('Inscriptions').' : '.get_lang('InscriptionFrom').' '.$ins_start.' '.get_lang('InscriptionTo').' '.$ins_end. '</div>';
}

if (($start_date <= $today) && ($end_date >= $today)) {
    echo '<form action="'.api_get_path(WEB_CODE_PATH).'admin/category_list.php">';
    echo '<input type="hidden" name="id" value="'.intval($_REQUEST['id']).'">';
    echo '<input type="hidden" name="next" value="1">';
    echo '<button type="submit" class="save" value="'.get_lang('AddToCart').'">'.get_lang('AddToCart').'</button>';
    echo '</form>';
} else {
    echo '<div align="right"><button value="'.get_lang('Inscriptions').' '.get_lang('From').' '.$ins_start.' '.get_lang('To').' '.$ins_end.'">'.get_lang('Inscriptions').' '.get_lang('From').' '.$ins_start.' '.get_lang('To').' '.$ins_end.'</button></div>';
}
echo '</div>';

function get_complete_course_description($db_name) {
	$sql = "SELECT * FROM ".$db_name.".course_description WHERE session_id = 0 ORDER BY description_type ";
	$result = Database::query($sql, __FILE__, __LINE__);
	$descriptions = array();
	while ($description = Database::fetch_object($result)) {
		$descriptions[$description->description_type] = $description;
		//reload titles to ensure we have the last version (after edition)
		$default_description_titles[$description->description_type] = $description->title;
	}
	$return = '';
	if (isset($descriptions) && count($descriptions) > 0) {
			foreach ($descriptions as $id => $description) {
	
				$return .= '<div class="section_white">';
				$return .= '<div class="sectiontitle">'.$description->title.'</div>';	
				$return .= '<div class="sectioncontent">';
				$return .= text_filter($description->content);
				$return .= '</div>';				
				$return .= '</div>';
			}
	}
	return $return;
}

Display::display_footer();
?>
