<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsessioncategorylist';

// including the global Dokeos file
require ('../inc/global.inc.php');

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sessionmanager.lib.php');
include_once (api_get_path(LIBRARY_PATH).'export.lib.inc.php');

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'export'){

	$data = export_csv_data();

	Export::export_table_csv($data);
	break;
}

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

// setting breadcrumbs
$interbreadcrumb[]=array("url" => "index.php","name" => get_lang('PlatformAdmin'));
$htmlHeadXtra[] = '<script type="text/javascript">
                        function moveItem (origin , destination, sideItem, currItem) {

                                for(var i = 0 ; i<origin.options.length ; i++) {
                                        if(origin.options[i].selected) {

                                                destination.options[destination.length] = new Option(origin.options[i].text,origin.options[i].value);

                                                    for (var x = 1; x <= $(".sessionset_left").length; x++) {
                                                        if (x == currItem) {
                                                            continue;
                                                        }
                                                        var iSelect = document.getElementById("sessionset_left"+x);
                                                        if (sideItem == "to-right") {
                                                            iSelect.options[i] = null;
                                                        } else if (sideItem == "to-left") {
                                                            iSelect.options[iSelect.length] = new Option(origin.options[i].text,origin.options[i].value);
                                                        }
                                                    }

                                                origin.options[i]=null;
                                                i = i-1;
                                        }
                                }

                                destination.selectedIndex = -1;
                                sortOptions(destination.options);

                                for (var x = 1; x <= $(".sessionset_left").length; x++) {
                                    if (x == currItem) {
                                        continue;
                                    }
                                    var iSelect = document.getElementById("sessionset_left"+x);
                                   if (sideItem == "to-left") {
                                        iSelect.selectedIndex = -1;
                                        sortOptions(iSelect.options);
                                    }
                                }

                        }

                        function sortOptions(options) {

                                newOptions = new Array();
                                for (i = 0 ; i<options.length ; i++)
                                        newOptions[i] = options[i];

                                newOptions = newOptions.sort(mysort);
                                options.length = 0;
                                for(i = 0 ; i < newOptions.length ; i++)
                                        options[i] = newOptions[i];
                        }

                        function mysort(a, b) {
                                if(a.text.toLowerCase() > b.text.toLowerCase()){
                                        return 1;
                                }
                                if(a.text.toLowerCase() < b.text.toLowerCase()){
                                        return -1;
                                }
                                return 0;
                        }

                        function valide() {
                                var nbanswer = document.getElementById("nb_answers").value;
                                for (var i = 1;i<=nbanswer;i++) {
                                        var options = document.getElementById("sessionset_right"+i).options;
                                        if (options.length != "0") {
                                        for (var j = 0 ; j<options.length ; j++)
                                            options[j].selected = true;
                                        }
                                }
                                document.programme.submit();
                        }
                        </script>';

// Displaying the header
Display::display_header($nameTools);

echo '<div class="actions">';
echo '<a href="'.api_get_self().'?action=add'.'">' . Display::return_icon('pixel.gif',get_lang('AddProgramme'), array('class' => 'toolactionplaceholdericon toolactionaddprogramme')) . get_lang('AddProgramme') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/catalogue_management.php">' . Display::return_icon('pixel.gif',get_lang('Catalogue'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('Catalogue') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/programme_list.php">' . Display :: return_icon('pixel.gif', get_lang('Programmes'),array('class' => 'toolactionplaceholdericon toolactionprogramme')) . get_lang('Programmes') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'),array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_export.php">'.Display::return_icon('pixel.gif',get_lang('ExportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionexportcourse')).get_lang('ExportSessionListXMLCSV').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/session_import.php">'.Display::return_icon('pixel.gif',get_lang('ImportSessionListXMLCSV'),array('class' => 'toolactionplaceholdericon toolactionimportcourse')).get_lang('ImportSessionListXMLCSV').'</a>';
echo '<a href="'.api_get_path(WEB_CODE_PATH).'coursecopy/copy_course_session.php">'.Display::return_icon('pixel.gif',get_lang('CopyFromCourseInSessionToAnotherSession'),array('class' => 'toolactionplaceholdericon toolsettings')).get_lang('CopyFromCourseInSessionToAnotherSession').'</a>';
echo '</div>';

$topic_table                = Database :: get_main_table(TABLE_MAIN_TOPIC);
$session_table              = Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_category       = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
$session_rel_category       = Database :: get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);
$tbl_session                = Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_session_cat_rel_user   = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_USER);

echo '<div id="content">';

if(!isset($_REQUEST['action'])){
	$default_column = api_is_allowed_to_edit() ? 1 : 0;
	$tablename = 'programme_table';
	$sortable_data = get_programme_data();

	$table = new SortableTableFromArrayConfig($sortable_data,$default_column,20,$tablename,$column_show,$column_order,'ASC');

	if (api_is_allowed_to_edit()){
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('Programme'));
	$table->set_header(3, get_lang('Sessions'));
	$table->set_header(4, get_lang('Start'));
	$table->set_header(5, get_lang('End'));
	$table->set_header(6, get_lang('Edit'));
	$table->set_header(7, get_lang('Delete'));
	}
	if (api_is_allowed_to_edit()){
		$table->set_form_actions(array ('delete_programme' => get_lang('Delete')));
	}
	$table->display();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') {
	echo '<script type="text/javascript">
	$(document).ready(function (){
		$("#currency").change(function() {
			var currency = $("#currency").val();
			if(currency == "840"){
				$("#comma").hide();
				$("#dot").show();
			}
			else {
				$("#comma").show();
				$("#dot").hide();
			}
		});
});
	</script>';

	$nb_answers = isset($_POST['nb_answers']) ? (int) $_POST['nb_answers'] : 3;
	$nb_answers += ( isset($_POST['lessAnswers']) ? -1 : (isset($_POST['moreAnswers']) ? 1 : 0));

	if($nb_answers == '0') $nb_answers = 1;
	//if($nb_answers > '3')$nb_answers = 3;

	$form = new FormValidator('programme', 'post',api_get_self().'?action=add');
	$renderer = & $form->defaultRenderer();
	$form->addElement('header', '', get_lang('ProgrammeSettings'));
	$form->addElement('text', 'programme_name', get_lang('ProgrammeName'), 'class="focus";style="width:300px;"');
        $form->addRule('programme_name', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('text', 'programme_code', get_lang('SessionCategoryCode'), 'class="focus";style="width:200px;"');
        $form->addRule('programme_code', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('html','<div class="row"><div style="width:100%;">'.get_lang('CatalogueDescription').'</div></div>');
	$renderer->setElementTemplate('<div class="row"><div style="width:100%;float:right;">{element}</div></div>', 'catalogue_description');
	$form->addElement('html_editor', 'catalogue_description',null,'style="vertical-align:middle"',array('ToolbarSet' => 'Documents', 'Width' => '100%', 'Height' => '200'));
	$sql = "SELECT id,topic FROM $topic_table";
	$res = Database::query($sql,__FILE__,__LINE__);
	$topics = array();
	while ($obj = Database::fetch_object($res)) {
		$topics[$obj->id] = $obj->topic;
	}
	$form->addElement('text', 'location', get_lang('Location'), 'class="focus";style="width:300px;"');
	$modality = array('E-learning' => get_lang('Elearning'), 'Blended' => get_lang('Blended'), 'Face to Face' => get_lang('FacetoFace'), 'Certifying' => get_lang('Certifying'));
	$form->addElement('select', 'modality', get_lang('Modality'), $modality);
	$form->addElement('text', 'keywords', get_lang('Keywords'), 'class="focus";style="width:600px;"');
	$form->addElement('datepicker', 'start', get_lang('Start'), array('form_name'=>'programme'));
	$form->addElement('datepicker', 'end', get_lang('End'), array('form_name'=>'programme'));
	$form->addElement('text', 'student_access', get_lang('StudentAccess'), 'class="focus";style="width:50px;"');
	$form->addElement('select_language', 'language', get_lang('CourseLanguage'));
	$form->applyFilter('select_language','html_filter');
	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('Yes'), '1');
	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('No'), '0');
	$form->addGroup($radios_catalogue_visible, 'catalogue_visible', get_lang('CatalogueVisible'));
/*	$form->addElement('text', 'unitprice', get_lang('UnitPrice'), 'class="focus";style="width:100px;"');
    $form->addRule('unitprice', get_lang('ThisFieldIsRequired'), 'required');
    $form->addRule('unitprice', get_lang('ThisFieldIsNumeric'), 'numeric'); */

	$group = array();
	$group[] = FormValidator::createElement('text', 'unitprice1', '', 'class="focus";style="width:100px;"');
	$group[] = FormValidator::createElement('static', 'unitprice2', '', '<img id="comma" src="../img/comma.png" style="vertical-align:bottom;"><img id="dot" src="../img/dot.png" style="display:none;vertical-align:bottom;">');
	$group[] = FormValidator::createElement('text', 'unitprice3', '', 'class="focus";style="width:100px;"');

	$element_template = <<<EOT
	<div class="row">
		<div style="display:inline;width:100%;">
			<table cellpadding="0" cellspacing="0" style="padding-left:30px;">
				<tr>
					<!-- BEGIN error --><span class="form_error" style="padding-left:470px;">{error}</span><br /><!-- END error -->	<td>{element}</td>
				</tr>
			</table>
		</div>
	</div>
EOT;
	$renderer = &$form->defaultRenderer();
	$renderer -> setElementTemplate($element_template, 'group');
	$form -> addGroup($group,'unitpricegroup',get_lang('UnitPrice'));
	$form->addRule('unitpricegroup', get_lang('ThisFieldIsRequired'), 'required');

	$currency = array('978' => 'EUR - Euros', '840' => 'USD - U.S.Dollars');
	$form->addElement('select', 'currency', get_lang('Currency'), $currency,'id="currency"');
	$form->addElement('hidden', 'tax', get_lang('Tax'), 'class="focus";style="width:50px;"');

        $form->addElement('html','<div class="row"><div style="width:100%;padding-left:30px;"><b>'.get_lang('InscriptionCalendar').'</b></div></div>');
        $form->addElement('datepicker', 'inscription_start', get_lang('Start'), array('form_name'=>'programme'));
	$form->addElement('datepicker', 'inscription_end', get_lang('End'), array('form_name'=>'programme'));

	$form->addElement('html','<div class="row"><div style="width:100%;padding-left:30px;"><b>'.get_lang('ProgrammeContentOptions').'</b></div></div>');
//	$form->addElement('html','<div class="row"><div style="width:100%;padding-left:30px;"><b>'.get_lang('SessionSet').'</b></div></div>');
	$sql = "SELECT id, name, nbr_courses FROM $session_table";
	$res = Database::query($sql,__FILE__,__LINE__);
	$sessions = array();
        $nbr_courses = 0;
	while($obj = Database::fetch_object($res)) {
		$sessions[$obj->id] = $obj->name.' ('.$obj->nbr_courses.' '.get_lang('Courses').')';
                $nbr_courses += $obj->nbr_courses;
	}
	$sessionset_right1 = array();

	for($i=1;$i<=$nb_answers;$i++){
	$form->addElement('text', 'session_set'.$i, get_lang('SessionSet').'&nbsp;'.$i, 'class="focus";style="width:200px;"');
	$group = array();
	$group[] = FormValidator::createElement('select', 'sessionset_left'.$i, '', $sessions, 'id="sessionset_left'.$i.'" multiple=multiple size="7" style="width: 350px;" class="sessionset_left"');
	$group[] = FormValidator::createElement('select', 'sessionset_right'.$i, '', $sessionset_right1, 'id="sessionset_right'.$i.'" multiple=multiple size="7" style="width: 350px;" class="sessionset_right"');
	$element_template = <<<EOT
	<div class="row">
		<div style="display:inline;width:100%;">
			<table cellpadding="0" cellspacing="0" style="padding-left:30px;">
				<tr>
					<!-- BEGIN error --><span class="form_error" style="padding-left:470px;">{error}</span><br /><!-- END error -->	<td>{element}</td>
				</tr>
			</table>
		</div>
	</div>
EOT;
	$renderer = &$form->defaultRenderer();
	$renderer -> setElementTemplate($element_template, 'group'.$i);
	$form -> addGroup($group,'group'.$i,'','</td><td width="80" align="center">'.
		'<input class="arrowr" style="width:30px;height:30px;padding-right:22px" type="button" onclick="moveItem(document.getElementById(\'sessionset_left'.$i.'\'), document.getElementById(\'sessionset_right'.$i.'\'), \'to-right\', '.$i.')" ><br><br>' .
		'<input class="arrowl" style="width:30px;height:30px;padding-right:22px" type="button" onclick="moveItem(document.getElementById(\'sessionset_right'.$i.'\'), document.getElementById(\'sessionset_left'.$i.'\'), \'to-left\', '.$i.')" ></td><td>');
	$form->addRule('group1', get_lang('ThisFieldIsRequired'), 'required');

	$max = range(0, $nbr_courses);
        $maxgroup = array();
	$maxgroup[] = FormValidator::createElement('select', 'max'.$i, get_lang('Max'), $max, 'id="max'.$i.'" ');
	$maxgroup[] = FormValidator::createElement('select', 'min'.$i, get_lang('Min'), $max, 'id="min'.$i.'" ');

	$element_template = '<div class="row" ><div class="label"></div><div style="width:100%;padding-left:550px;">'.get_lang('ShouldSelect').'{element}</div></div>';
	$renderer -> setElementTemplate($element_template, 'maxgroup'.$i);
	$form->addGroup($maxgroup,'maxgroup'.$i,'','&nbsp;&nbsp;'.get_lang('Min'));

        if($i == 1){
            $defaults['maxgroup'.$i]['max'.$i] = 3;
            $defaults['maxgroup'.$i]['min'.$i] = 3;
	}
	elseif($i == 2){
            $defaults['maxgroup'.$i]['max'.$i] = 2;
            $defaults['maxgroup'.$i]['min'.$i] = 1;
	}
	elseif($i == 3){
            $defaults['maxgroup'.$i]['max'.$i] = 1;
            $defaults['maxgroup'.$i]['min'.$i] = 1;
	}


        }
	$form->addElement('html', '<table width="100%"><tr><td width="100%"><div style="padding-left:25px;">');
        if ($nb_answers > 1) {
            $form->addElement('submit', 'lessAnswers', '', 'style="background:url(\'../img/form-minus.png\') no-repeat;width:35px;height:40px;border:0px;"');
        } else {
            $form->addElement('static', 'lessAnswers', '', Display::return_icon('form-minus_na.png', '', array('style' => 'vertical-align:top;')));
        }

        $form->addElement('submit', 'moreAnswers', '', 'onclick="valide()"; style="background:url(\'../img/form-plus.png\') no-repeat;width:35px;height:40px;border:0px;"');
        $form->addElement('html', '</div></td></tr></table>');

        $renderer->setElementTemplate('{element}', 'lessAnswers');

	$renderer->setElementTemplate('{element}&nbsp;', 'moreAnswers');

        $form->addElement('hidden', 'nb_answers','','id="nb_answers"');
	$form->setConstants(array('nb_answers' => $nb_answers));
	//$form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
	$form->addElement('style_submit_button', 'submit', get_lang('Ok'),'onclick="valide()"; class="save"');

	$defaults['catalogue_visible'] = 1;
	$defaults['start'] = date('Y-m-d H:i:s');
	$defaults['end'] = date('Y-m-d H:i:s',time() + (7 * 24 * 60 * 60));


        $defaults['inscription_start'] = date('Y-m-d H:i:s');
	$defaults['inscription_end'] = date('Y-m-d H:i:s', time() + (7 * 24 * 60 * 60));

	$form->setDefaults($defaults);
	if($form -> validate()) {
		$programme = $form->exportValues();
		$nb_answer = $programme['nb_answers'];
                $programme['unitpricegroup']['unitprice1'] = empty($programme['unitpricegroup']['unitprice1'])?'0':intval($programme['unitpricegroup']['unitprice1']);
                $programme['unitpricegroup']['unitprice3'] = empty($programme['unitpricegroup']['unitprice3'])?'0':intval($programme['unitpricegroup']['unitprice3']);
                $cost = floatval($programme['unitpricegroup']['unitprice1'].'.'.$programme['unitpricegroup']['unitprice3']);
                /*
		$cost = $programme['unitpricegroup']['unitprice1'];

		if($programme['currency'] == '978'){
			$cost .= ',';
		}
		elseif($programme['currency'] == '840'){
			$cost .= '.';
		}

		$cost .= $programme['unitpricegroup']['unitprice3'];
                */

		if(isset($_POST['submit'])){

		$sql = "INSERT INTO " . $tbl_session_category . " SET " .
										   "name			= '".Database::escape_string($programme['programme_name'])."',
										   description	= '".Database::escape_string($programme['catalogue_description'])."',
										   location 		= '".Database::escape_string($programme['location'])."',
										   modality 				= '".Database::escape_string($programme['modality'])."',
										   keywords 		= '".Database::escape_string($programme['keywords'])."',
										   date_start 			= '".$programme['start']."',
										   date_end 				= '".$programme['end']."',
										   student_access 	= '".$programme['student_access']."',
										   language 	= '".Database::escape_string($programme['language'])."',
										   visible 	= '".$programme['catalogue_visible']."',
										   currency 	= '".$programme['currency']."',
										   tax 	= '".$programme['tax']."',
										   cost		= '".$cost."',
                                                                                   inscription_date_start = '".$programme['inscription_start']."',
										   inscription_date_end   = '".$programme['inscription_end']."',
										   code		= '".$programme['programme_code']."'";
					Database::query($sql, __FILE__, __LINE__);
					$id = Database::insert_id();

              if (Database::affected_rows()){
                   for($i=1;$i<=$nb_answer;$i++){
                           $sessionset = $_POST['group'.$i]['sessionset_right'.$i];
                           $sessionset_name = $_POST['session_set'.$i];
                           foreach($sessionset as $key){
                                   $r_max = $programme['maxgroup'.$i]['max'.$i];
                                   $r_min = $programme['maxgroup'.$i]['min'.$i];
                                   $range = $r_max.'|'.$r_min;
                                   $sql = "INSERT INTO " . $session_rel_category . " SET " .
                                                                                      "category_id = '".$id."',
                                                                                      session_set  = '".$i."',
                                                                                      session_set_name			= '".$sessionset_name."',
                                                                                      session_id				= ".$key.",
                                              session_range = '$range'";
                                           Database::query($sql, __FILE__, __LINE__);
                                   // update category in session
                                   Database::query("UPDATE $tbl_session SET session_category_id = $id WHERE id = $key");
                           }
                   }
              }

                echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';

		}
	//
	}

	$form->display();
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit'){

	echo '<script type="text/javascript">
	$(document).ready(function (){
		$("#currency").change(function() {
			var currency = $("#currency").val();
			if(currency == "840"){
				$("#comma").hide();
				$("#dot").show();
			}
			else {
				$("#comma").show();
				$("#dot").hide();
			}
		});
});
	</script>';

	$sql = "SELECT * FROM $tbl_session_category WHERE id = ".$_REQUEST['id'];
	$res = Database::query($sql,__FILE__,__LINE__);
	$programme_edit = Database::fetch_array($res);

	$sql_session = "SELECT count(DISTINCT(session_set)) AS session_set FROM $session_rel_category WHERE category_id = ".$_REQUEST['id'];
	$res_session = Database::query($sql_session,__FILE__,__LINE__);
	$session_set = Database::result($res_session, 0, 'session_set');

        $nb_answers = isset($_POST['nb_answers']) ? (int) $_POST['nb_answers'] : 3;
	$nb_answers += ( isset($_POST['lessAnswers']) ? -1 : (isset($_POST['moreAnswers']) ? 1 : 0));

	if($nb_answers == '0') $nb_answers = 1;

	$currency = $programme_edit['currency'];
	$cost = $programme_edit['cost'];
        $price1 = $price2 = 0;
        if (strpos($cost, '.') !== false) {
            list($price1, $price2) = explode('.', $cost);
        } else if (strpos($cost, ',') !== false) {
            list($price1, $price2) = explode(',', $cost);
        } else {
            $price1 = $cost;
            $price2 = 0;
        }

        /*
	if($currency == '978'){
		$find_string = ',';
	} else {
		$cost = str_replace('.',':',$cost);
		$find_string = ':';
	}
        if (strpos($cost,$find_string) !== false){
		list($price1,$price2) = split($find_string,$cost);
	}*/

	$form = new FormValidator('programme', 'post',api_get_self().'?action=edit&amp;id='.intval($_REQUEST['id']));
	$renderer = & $form->defaultRenderer();
	$form->addElement('header', '', get_lang('ProgrammeSettings'));

	$form->addElement('text', 'programme_name', get_lang('ProgrammeName'), 'class="focus";style="width:300px;"');
        $form->addRule('programme_name', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('text', 'programme_code', get_lang('SessionCategoryCode'), 'class="focus";style="width:200px;"');
        $form->addRule('programme_code', get_lang('ThisFieldIsRequired'), 'required');

	$form->addElement('html','<div class="row"><div style="width:100%;">'.get_lang('CatalogueDescription').'</div></div>');
	$renderer->setElementTemplate('<div class="row"><div style="width:100%;float:right;">{element}</div></div>', 'catalogue_description');
	$form->addElement('html_editor', 'catalogue_description',null,'style="vertical-align:middle"',array('ToolbarSet' => 'Documents', 'Width' => '100%', 'Height' => '200'));
	$form->addElement('text', 'location', get_lang('Location'), 'class="focus";style="width:300px;"');
//	$modality = array('E-learning','Blended','Face to Face','Certifying');
//	$modality = array('E-learning' => 'E-learning', 'Blended' => 'Blended', 'Face to Face' => 'Face to Face', 'Certifying' => 'Certifying');
	$modality = array('E-learning' => get_lang('Elearning'), 'Blended' => get_lang('Blended'), 'Face to Face' => get_lang('FacetoFace'), 'Certifying' => get_lang('Certifying'));
	$form->addElement('select', 'modality', get_lang('Modality'), $modality);
	$form->addElement('text', 'keywords', get_lang('Keywords'), 'class="focus";style="width:600px;"');
	$form->addElement('datepicker', 'start', get_lang('Start'), array('form_name'=>'programme'));
	$form->addElement('datepicker', 'end', get_lang('End'), array('form_name'=>'programme'));
	$form->addElement('text', 'student_access', get_lang('StudentAccess'), 'class="focus";style="width:50px;"');
	$form->addElement('select_language', 'language', get_lang('CourseLanguage'));
	$form->applyFilter('select_language','html_filter');
	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('Yes'), '1');
	$radios_catalogue_visible[] = FormValidator :: createElement('radio', null, null, get_lang('No'), '0');
	$form->addGroup($radios_catalogue_visible, 'catalogue_visible', get_lang('CatalogueVisible'));
//	$form->addElement('text', 'unitprice', get_lang('UnitPrice'), 'class="focus";style="width:100px;"');
//	$currency = array('EUR - Euros','USD - U.S.Dollars');

	$group = array();
	$group[] = FormValidator::createElement('text', 'unitprice1', '', 'class="focus";style="width:100px;"');
	if($currency == '978'){
	$group[] = FormValidator::createElement('static', 'unitprice2', '', '<img id="comma" src="../img/comma.png" style="vertical-align:bottom;"><img id="dot" src="../img/dot.png" style="display:none;vertical-align:bottom;">');
	}
	else {
	$group[] = FormValidator::createElement('static', 'unitprice2', '', '<img id="comma" src="../img/comma.png" style="display:none;vertical-align:bottom;"><img id="dot" src="../img/dot.png" style="vertical-align:bottom;">');
	}
	$group[] = FormValidator::createElement('text', 'unitprice3', '', 'class="focus";style="width:100px;"');

	$element_template = <<<EOT
	<div class="row">
		<div style="display:inline;width:100%;">
			<table cellpadding="0" cellspacing="0" style="padding-left:30px;">
				<tr>
					<!-- BEGIN error --><span class="form_error" style="padding-left:470px;">{error}</span><br /><!-- END error -->	<td>{element}</td>
				</tr>
			</table>
		</div>
	</div>
EOT;
	$renderer = &$form->defaultRenderer();
	$renderer -> setElementTemplate($element_template, 'group');
	$form -> addGroup($group,'unitpricegroup',get_lang('UnitPrice'));
	$form->addRule('unitpricegroup', get_lang('ThisFieldIsRequired'), 'required');

	$defaults['unitpricegroup']['unitprice1'] = $price1;
	$defaults['unitpricegroup']['unitprice3'] = $price2;

	$currency = array('978' => 'EUR - Euros', '840' => 'USD - U.S.Dollars');
	$form->addElement('select', 'currency', get_lang('Currency'), $currency,'id="currency"');
	$form->addElement('hidden', 'tax', get_lang('Tax'), 'class="focus";style="width:50px;"');

        $form->addElement('html','<div class="row"><div style="width:100%;padding-left:30px;"><b>'.get_lang('InscriptionCalendar').'</b></div></div>');
        $form->addElement('datepicker', 'inscription_start', get_lang('Start'), array('form_name'=>'programme'));
	$form->addElement('datepicker', 'inscription_end', get_lang('End'), array('form_name'=>'programme'));

	$form->addElement('html','<div class="row"><div class="form_header" style="width:100%;padding-left:30px;"><b>'.get_lang('ProgrammeContentOptions').'</b></div></div>');

	for ($i=1;$i<=$nb_answers;$i++) {
       //   $form->addElement('html','<div class="row"><div style="width:100%;padding-left:30px;"><b>'.get_lang('SessionSet').'&nbsp;'.$i.'</b></div></div>');
			$form->addElement('text', 'session_set'.$i, get_lang('SessionSet').'&nbsp;'.$i, 'class="focus";style="width:200px;"');
            $sessions = array();
            $sessionset_right1 = array();
            $sessionid_arr = array();
			$sessionset_name = '';
            $sql1 = "SELECT * FROM $session_rel_category WHERE category_id = ".$_REQUEST['id']." AND session_set = '".$i."'";
            $res1 = Database::query($sql1,__FILE__,__LINE__);
            $num_prg = Database::num_rows($res1);
            $a_rel_cat = array();
            if ($num_prg == 0) {
                $sessionid_arr[] = '\' \'';
            }
            else {
                while($row1 = Database::fetch_array($res1)){
                    $a_rel_cat[] = array('session_set' => $row1['session_set'], 'range' => $row1['session_range']);
                    $sessionid_arr[] = $row1['session_id'];
					$sessionset_name = $row1['session_set_name'];
                }
            }
			$defaults['session_set'.$i] = $sessionset_name;

            $session_id1 = implode(",",$sessionid_arr);
            $sql = "SELECT id, name, nbr_courses FROM $session_table WHERE id NOT IN (SELECT session_id FROM $session_rel_category WHERE category_id = {$_REQUEST['id']})";

            $res = Database::query($sql,__FILE__,__LINE__);
            $sessions = array();
            $nbr_courses = 0;
            while ($obj = Database::fetch_object($res)) {
                    $sessions[$obj->id] = $obj->name.' ('.$obj->nbr_courses.' '.get_lang('Courses').')';
                    $nbr_courses += $obj->nbr_courses;
            }

            $sql = "SELECT id, name, nbr_courses FROM $session_table WHERE id IN (".$session_id1.")";
            $res = Database::query($sql,__FILE__,__LINE__);
            $sessionset_right1 = array();
            while ($obj = Database::fetch_object($res)) {
                    $sessionset_right1[$obj->id] = $obj->name.' ('.$obj->nbr_courses.' '.get_lang('Courses').')';
                    $nbr_courses += $obj->nbr_courses;
            }

	$group = array();
	$group[] = FormValidator::createElement('select', 'sessionset_left'.$i, '', $sessions, 'id="sessionset_left'.$i.'" multiple=multiple size="7" style="width: 350px;" class="sessionset_left"');
	$group[] = FormValidator::createElement('select', 'sessionset_right'.$i, '', $sessionset_right1, 'id="sessionset_right'.$i.'" multiple=multiple size="7" style="width: 350px;" class="sessionset_right"');
	$element_template = <<<EOT
	<div class="row">
		<div style="display:inline;width:100%;">
			<table cellpadding="0" cellspacing="0" style="padding-left:30px;">
				<tr><!-- BEGIN error --><span class="form_error" style="padding-left:470px;">{error}</span><br /><!-- END error --><td>{element}</td></tr>
			</table>
		</div>
	</div>
EOT;

            $renderer = &$form->defaultRenderer();
            $renderer -> setElementTemplate($element_template, 'group'.$i);
            $form -> addGroup($group,'group'.$i,'','</td><td width="80" align="center">'.
                    '<input class="arrowr" style="width:30px;height:30px;padding-right:22px" type="button" onclick="moveItem(document.getElementById(\'sessionset_left'.$i.'\'), document.getElementById(\'sessionset_right'.$i.'\'), \'to-right\', '.$i.')" ><br><br>' .
                    '<input class="arrowl" style="width:30px;height:30px;padding-right:22px" type="button" onclick="moveItem(document.getElementById(\'sessionset_right'.$i.'\'), document.getElementById(\'sessionset_left'.$i.'\'), \'to-left\', '.$i.')" ></td><td>');
            $form->addRule('group1', get_lang('ThisFieldIsRequired'), 'required');

            $max = range(0, $nbr_courses);
            $maxgroup = array();
            $maxgroup[] = FormValidator::createElement('select', 'max'.$i, get_lang('Max'), $max, 'id="max'.$i.'"');
            $maxgroup[] = FormValidator::createElement('select', 'min'.$i, get_lang('Min'), $max, 'id="min'.$i.'"');
            $element_template = '<div class="row" ><div class="label"></div><div style="width:100%;padding-left:550px;">'.get_lang('ShouldSelect').'{element}</div></div>';
            $renderer -> setElementTemplate($element_template, 'maxgroup'.$i);
            $form->addGroup($maxgroup,'maxgroup'.$i,'','&nbsp;&nbsp;'.get_lang('Min'));
            if (!empty($a_rel_cat)) {
                foreach ($a_rel_cat as $rel_cat) {
                    $r_max = $r_min = 0;
                    if (!empty($rel_cat['range'])) {
                        list($r_max, $r_min) = explode('|', $rel_cat['range']);
                    }
                    $defaults['maxgroup'.$rel_cat['session_set']]['max'.$rel_cat['session_set']] = $r_max;
                    $defaults['maxgroup'.$rel_cat['session_set']]['min'.$rel_cat['session_set']] = $r_min;
                }
            }
	}
	$form->addElement('html', '<table width="100%"><tr><td width="100%"><div style="padding-left:25px;">');
        if ($nb_answers > 1) {
            $form->addElement('submit', 'lessAnswers', '', 'style="background:url(\'../img/form-minus.png\') no-repeat;width:35px;height:40px;border:0px;"');
        } else {
            $form->addElement('static', 'lessAnswers', '', Display::return_icon('form-minus_na.png', '', array('style' => 'vertical-align:top;')));
        }
        $form->addElement('submit', 'moreAnswers', '', 'style="background:url(\'../img/form-plus.png\') no-repeat;width:35px;height:40px;border:0px;"');
	$form->addElement('html', '</div></td></tr></table>');
	$renderer->setElementTemplate('{element}', 'lessAnswers');
	$renderer->setElementTemplate('{element}&nbsp;', 'moreAnswers');
	$form->addElement('hidden', 'nb_answers','','id="nb_answers"');

	$form->setConstants(array('nb_answers' => $nb_answers));
	$form->addElement('style_submit_button', 'submit', get_lang('Ok'),'onclick="valide()"; class="save"');
	$defaults['programme_name'] = $programme_edit['name'];
	$defaults['programme_code'] = $programme_edit['code'];
	$defaults['catalogue_description'] = $programme_edit['description'];
	$defaults['location'] = isset($programme_edit['location'])?$programme_edit['location']:'';
	$defaults['modality'] = $programme_edit['modality'];
	$defaults['keywords'] = $programme_edit['keywords'];
	$defaults['student_access'] = isset($programme_edit['student_access'])?$programme_edit['student_access']:'';
	$defaults['language'] = $programme_edit['language'];
	$defaults['catalogue_visible'] = $programme_edit['visible'];
//	$defaults['unitprice'] = $programme_edit['cost'];
	$defaults['currency'] = $programme_edit['currency'];
	$defaults['tax'] = $programme_edit['tax'];
	$defaults['start'] = $programme_edit['date_start'];
	$defaults['end'] = $programme_edit['date_end'];
        $defaults['inscription_start'] = $programme_edit['inscription_date_start'];
	$defaults['inscription_end'] = $programme_edit['inscription_date_end'];
        $form->setDefaults($defaults);
	if($form -> validate()) {

            $programme = $form->exportValues();
            $nb_answer = $programme['nb_answers'];
            $programme['unitpricegroup']['unitprice1'] = empty($programme['unitpricegroup']['unitprice1'])?'0':intval($programme['unitpricegroup']['unitprice1']);
            $programme['unitpricegroup']['unitprice3'] = empty($programme['unitpricegroup']['unitprice3'])?'0':intval($programme['unitpricegroup']['unitprice3']);
            $cost = floatval($programme['unitpricegroup']['unitprice1'].'.'.$programme['unitpricegroup']['unitprice3']);

            /*
            $cost = $programme['unitpricegroup']['unitprice1'];
            if($programme['currency'] == '978'){
                    $cost .= ',';
            }
            elseif($programme['currency'] == '840'){
                    $cost .= '.';
            }
            $cost .= $programme['unitpricegroup']['unitprice3'];
             */

            if(isset($_POST['submit'])) {
                $sql = "UPDATE " . $tbl_session_category . " SET " .
                                                                                   "name			= '".Database::escape_string($programme['programme_name'])."',
                                                                                   description	= '".Database::escape_string($programme['catalogue_description'])."',
                                                                                   location 		= '".Database::escape_string($programme['location'])."',
                                                                                   modality 				= '".Database::escape_string($programme['modality'])."',
                                                                                   keywords 		= '".Database::escape_string($programme['keywords'])."',
                                                                                   date_start 			= '".$programme['start']."',
                                                                                   date_end 				= '".$programme['end']."',
                                                                                   student_access 	= '".$programme['student_access']."',
                                                                                   language 	= '".Database::escape_string($programme['language'])."',
                                                                                   visible 	= '".$programme['catalogue_visible']."',
                                                                                   currency 	= '".$programme['currency']."',
                                                                                   tax 	= '".$programme['tax']."',
                                                                                   cost		= '".$cost."',
                                                                                   inscription_date_start = '".$programme['inscription_start']."',
																				   inscription_date_end   = '".$programme['inscription_end']."',
                                                                                   code		= '".$programme['programme_code']."'
                                                                                   WHERE id = ".$_REQUEST['id'];

                                        Database::query($sql, __FILE__, __LINE__);

                 $sql = "DELETE FROM $session_rel_category WHERE category_id = ".$_REQUEST['id'];

                 Database::query($sql, __FILE__, __LINE__);
                for($i=1;$i<=$nb_answer;$i++){
                        $sessionset = $_POST['group'.$i]['sessionset_right'.$i];
						$sessionset_name = $_POST['session_set'.$i];
                        foreach($sessionset as $key){
                            $r_max = $programme['maxgroup'.$i]['max'.$i];
                            $r_min = $programme['maxgroup'.$i]['min'.$i];
                            $range = $r_max.'|'.$r_min;
                                $sql = "INSERT INTO $session_rel_category SET
                                            category_id = {$_REQUEST['id']},
                                            session_set	= $i,
											session_set_name	= '".$sessionset_name."',
                                            session_id	= $key,
                                            session_range = '$range';";
                                        Database::query($sql, __FILE__, __LINE__);
                            // update category in session
                            Database::query("UPDATE $tbl_session SET session_category_id = {$_REQUEST['id']} WHERE id = $key");
                        }
                }
                echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';
            }
	}
	$form->display();
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'){

	$sql = "DELETE FROM $session_rel_category WHERE category_id = ".intval($_REQUEST['id']);
	Database::query($sql,__FILE__,__LINE__);

	$sql = "DELETE FROM $tbl_session_category WHERE id = ".intval($_REQUEST['id']);
	Database::query($sql,__FILE__,__LINE__);

        // clean category rel user
        $verify = Database::query("SELECT * FROM $tbl_session_cat_rel_user WHERE category_id =".intval($_REQUEST['id']));
        if (Database::num_rows($verify)) {
            Database::query("DELETE FROM $tbl_session_cat_rel_user WHERE category_id =".intval($_REQUEST['id']));
        }

	echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';

}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_programme'){

	if(isset($_POST['id'])){
		$delete_id = $_POST['id'];
	}
	foreach ($delete_id as $index => $programme_id){
	$sql = "DELETE FROM $session_rel_category WHERE category_id = ".$programme_id;
	Database::query($sql,__FILE__,__LINE__);

	$sql = "DELETE FROM $tbl_session_category WHERE id = ".$programme_id;
	Database::query($sql,__FILE__,__LINE__);
	}
	echo '<script type="text/javascript">window.location.href = "'.api_get_self().'"</script>';
}

function get_programme_data(){
	$topic_table 		= Database :: get_main_table(TABLE_MAIN_TOPIC);
	$tbl_session_category 		= Database :: get_main_table(TABLE_MAIN_SESSION_CATEGORY);
	$session_rel_category 		= Database :: get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);

	$sql = "SELECT * FROM $tbl_session_category";
	$res = Database::query($sql,__FILE__,__LINE__);
	$programmes = array ();
	while ($programme = Database::fetch_array($res)) {
		$num_session = 0;
		$programme_name = '<img align="top" src="'.api_get_path(WEB_IMG_PATH).'/topic_22.png" />&nbsp;&nbsp;'.$programme['name'];

		$sql_topic = "SELECT topic FROM $topic_table WHERE id = ".$programme['topic'];
		$res_topic = Database::query($sql_topic,__FILE__,__LINE__);
		$topic_name = Database::result($res_topic, 0, 'topic');

		$sql_session = "SELECT DISTINCT(session_id) AS no_session FROM $session_rel_category WHERE category_id = ".$programme['id'];
		$res_session = Database::query($sql_session,__FILE__,__LINE__);
		$num_session = Database::num_rows($res_session);
		if(empty($num_session))$num_session = 0;

		$datetime = explode(" ", $programme['date_start']);
		$dateparts = explode("-", $datetime[0]);
		$start = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0];

		$datetime = explode(" ", $programme['date_end']);
		$dateparts = explode("-", $datetime[0]);
		$end = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0];

		$edit_link = '<center><a href="'.api_get_self().'?action=edit&amp;id='.$programme['id'].'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a></center>';
		$delete_link = '<center><a href="'.api_get_self().'?action=delete&amp;id='.$programme['id'].'" onclick="javascript:if(!confirm(\''.get_lang('ConfirmYourChoice').'\')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a></center>';

		$programmes[] = array($programme['id'],$programme_name,'<center>'.$topic_name.'</center>','<center>'.$num_session.'&nbsp;'.get_lang('Sessions').'</center>','<center>'.$start.'</center>','<center>'.$end.'</center>',$edit_link,$delete_link);
	}
	return $programmes;
}

echo '</div>';

echo '<div class="actions"><a href="programme_list.php?'.api_get_cidreq().'&amp;action=export&amp;type=csv">'.Display::return_icon('pixel.gif', get_lang('ExportAsCSV'), array('class' => 'actionplaceholdericon actionexport')).get_lang('ExportAsCSV').'</a></div>';

function export_csv_data(){

	$topic_table 		= Database :: get_main_table(TABLE_MAIN_TOPIC);
	$tbl_session_category 		= Database :: get_main_table(TABLE_MAIN_SESSION_CATEGORY);
	$session_rel_category 		= Database :: get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);

	$data = array();
	$data[] = array(get_lang('Programme'),get_lang('Topic'),get_lang('Sessions'),get_lang('Start'),get_lang('End'));

	$sql = "SELECT * FROM $tbl_session_category";
	$res = Database::query($sql,__FILE__,__LINE__);
	$programmes = array ();
	while ($programme = Database::fetch_array($res)) {
		$programme_name = $programme['name'];
		$sql_topic = "SELECT topic FROM $topic_table WHERE id = ".$programme['topic'];
		$res_topic = Database::query($sql_topic,__FILE__,__LINE__);
		$topic_name = Database::result($res_topic, 0, 'topic');

		$sql_session = "SELECT DISTINCT(session_id) AS no_session FROM $session_rel_category WHERE category_id = ".$programme['id'];
		$res_session = Database::query($sql_session,__FILE__,__LINE__);
		$num_session = Database::num_rows($res_session);
		if(empty($num_session))$num_session = 0;

		$datetime = explode(" ", $programme['date_start']);
		$dateparts = explode("-", $datetime[0]);
		$start = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0];

		$datetime = explode(" ", $programme['date_end']);
		$dateparts = explode("-", $datetime[0]);
		$end = $dateparts[2].'-'.$dateparts[1].'-'.$dateparts[0];
		$row = array();

		$row[] = $programme_name;
		$row[] = $topic_name;
		$row[] = $num_session;
		$row[] = $start;
		$row[] = $end;
		$data[] = $row;
	}
	return $data;
}

// display the footer
Display::display_footer();
?>

