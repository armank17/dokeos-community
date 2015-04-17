<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * Script allowing simple edition of learnpath information (title, description, etc)
 * @package dokeos.learnpath
 * @author Yannick Warnier
 */

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

// we set the encoding of the lp
if (!empty($_SESSION['oLP']->encoding)) {
	$charset = $_SESSION['oLP']->encoding;
    // Check if we have a valid api encoding
    $valid_encodings = api_get_valid_encodings();
    $has_valid_encoding = false;
    foreach ($valid_encodings as $valid_encoding) {
      if (strcasecmp($charset,$valid_encoding) == 0) {
        $has_valid_encoding = true;
      }
    }
    // If the scorm packages has not a valid charset, i.e : UTF-16 we are displaying
    if ($has_valid_encoding === false) {
      $charset = api_get_system_encoding();
    }
} else {
	$charset = api_get_system_encoding();
}
if (empty($charset)) {
	$charset = 'ISO-8859-1';
}

$show_description_field = false; //for now
$nameTools = get_lang("Doc");
event_access_tool(TOOL_LEARNPATH);
if (! $is_allowed_in_course) api_not_allowed();

if (isset($_SESSION['gradebook'])){
	$gradebook=	$_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook=='view') {
	$interbreadcrumb[]= array (
			'url' => '../gradebook/'.$_SESSION['gradebook_dest'],
			'name' => get_lang('Gradebook')
		);
}
$interbreadcrumb[]= array ("url"=>"lp_controller.php?action=list", "name"=> get_lang("_learning_path"));
$interbreadcrumb[]= array ("url"=>api_get_self()."?action=admin_view&amp;lp_id=$learnpath_id", "name" => $_SESSION['oLP']->get_name());

$htmlHeadXtra[] = '
    <style type="text/css">
        .media { display:none;}
        #quiz-certificate-thumb {
            text-align:left;
            margin-top: 42px;
           
        }
        #quiz-certificate-thumb img {
            border: 2px solid #aaa;
             margin-left: 95px;
        }
        #quiz-certificate-score {
            display:none;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
           // change certificate template
           $("#quiz-certificate").change(function(){
                var tpl_id = $(this).val();
                $("#quiz-certificate-score").hide();
                if (tpl_id == 0) {
                    $("#quiz-certificate-score input[name=\'certificate_min_score\']").val(\'\');
                    $("#quiz-certificate-score").hide();
                } else {
                    $("#quiz-certificate-score").show();
                }
                $.ajax({
                    type: "GET",
                    url: "'.api_get_path(WEB_CODE_PATH).'exercice/exercise.ajax.php?'.api_get_cidReq().'&action=displayCertPicture&tpl_id="+tpl_id,
                    success: function(data) {
                        $("#quiz-certificate-thumb").show();
                        $("#quiz-certificate-thumb").html(data);
                    }
                });
           });
           
           // Onchange selectbox
           $("#scorm_export").change(function(e) {
             var export_type = $(this).attr("value");
             if (export_type == "default_export") {
              $("#default_scorm_message").show();
              $("#select_navigation").hide();
             } else {
              $("#default_scorm_message").hide();
              $("#select_navigation").show();
             }
              $("#current_export_type").attr("value",export_type);
            });
           // export dialog
           $(".export-link").click(function(e) {
                e.preventDefault();
                $("#export-dialog").dialog({
                    height: 300,
                    width: 380,
                    modal: true,
                    title: "'.get_lang('ScormExportType').'",
                    buttons: {
                        "'.get_lang("Cancel").'": function() {
                          $(this).dialog("close");
                        },
                        "'.get_lang("Export").'": function() { 
                            var export_type = $("#current_export_type").val();
                            var href = $(".export-link").attr("href");
                            if (export_type == "layout_export") {
                            var navigation = $("input[type=\'radio\'].export-nav");
                            if (navigation.is(":checked")) {
                                var nav = $("input[type=\'radio\'].export-nav:checked").val();
                                location.href = href+"&navigation="+nav+"&export_mode=layout_export";
                             } 
                            } else {
                              location.href = href+"&export_mode=default_export";
                            }
                            $(this).dialog("close");
                        }
                    },
                    close: function() {}
                });
           });

        });
    </script>
';

Display::display_tool_header(null,'Path');

$author_lang_var = api_convert_encoding(get_lang('Author'), $charset, api_get_system_encoding());
$content_lang_var = api_convert_encoding(get_lang('Content'), $charset, api_get_system_encoding());
$scenario_lang_var = api_convert_encoding(get_lang('Scenario'), $charset, api_get_system_encoding());
$publication_lang_var = api_convert_encoding(get_lang('Publication'), $charset, api_get_system_encoding());
$export_lang_var = api_convert_encoding(get_lang('Export'), $charset, api_get_system_encoding());

// actions link
echo '<div class="actions">';
$gradebook = Security::remove_XSS($_GET['gradebook']);

if($_SESSION['oLP']->type == '2' || $_SESSION['oLP']->type == '3'){
echo '<a href="' . api_get_self() . '?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '">' . Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionback')).$author_lang_var . '</a>';
}else{
echo '<a href="' . api_get_self() . '?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '">' . Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionback')).$author_lang_var . '</a>';
echo '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&gradebook='.$gradebook.'&action=add_item&type=step&lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('pixel.gif', $content_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')).$content_lang_var . '</a>';
echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&gradebook=&action=admin_view&lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('pixel.gif', $scenario_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorscenario')).$scenario_lang_var . '</a>';
echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&gradebook=&action=edit&lp_id=' . $_SESSION['oLP']->lp_id . '">' . Display::return_icon('pixel.gif', $publication_lang_var, array('class' => 'toolactionplaceholdericon toolsettings')).$publication_lang_var . '</a>';
}
/*   Export  */

/*   Export  */
if ($_SESSION['oLP']->get_type() == 1) {
    $dsp_disk = "<a href='".api_get_path(WEB_CODE_PATH)."newscorm/lp_controller.php?".api_get_cidreq()."&action=export&lp_id=".$_SESSION['oLP']->lp_id ."' class='export-link'>" .Display::return_icon('pixel.gif', $export_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorexport')).$export_lang_var."</a>"; 
} elseif ($_SESSION['oLP']->get_type() == 2) {
    //$dsp_disk = "<a class='export-link' href='".api_get_path(WEB_CODE_PATH)."newscorm/lp_controller.php?".api_get_cidreq()."&action=export&lp_id=".$_SESSION['oLP']->lp_id ."&export_name=".replace_dangerous_char($_SESSION['oLP']->get_name(),'strict').".zip'>" .Display::return_icon('pixel.gif', $export_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorexport')).$export_lang_var."</a>";
}
echo $dsp_disk;
$DefaultScormExport = api_convert_encoding(get_lang('DefaultScormExport'), $charset, api_get_system_encoding());
$ScormExportWithNavigationButtons = api_convert_encoding(get_lang('ScormExportWithNavigationButtons'), $charset, api_get_system_encoding());
$DefaultScormExportDescription = api_convert_encoding(get_lang('ScormExportWithNavigationButtons'), $charset, api_get_system_encoding());
$TopNavigation = api_convert_encoding(get_lang('TopNavigation'), $charset, api_get_system_encoding());
$BottomNavigation = api_convert_encoding(get_lang('BottomNavigation'), $charset, api_get_system_encoding());

echo '<div id="export-dialog" style="display:none;">
        <form id="export-form">
            <select name="scorm_export" id="scorm_export">
                <option selected="selected" value="default_export">'.$DefaultScormExport.'</option>
                <option value="layout_export">'.$ScormExportWithNavigationButtons.'</option>
            </select> 
            <input type="hidden" value ="default_export" name="current_export_type" id="current_export_type">
            <div id="default_scorm_message">'.$DefaultScormExportDescription.'</div>
            <div id="select_navigation" style="display:none;">
            <p><input type="radio" class="export-nav" name="export_nav" value="top" />'.$TopNavigation.'</p>
            <p><input checked="checked" type="radio" class="export-nav" name="export_nav" value="bottom" />'.$BottomNavigation.'</p>
            </div>
        </form>
      </div>';
echo '</div>';

$defaults=array();
$form = new FormValidator('form1', 'post', 'lp_controller.php?'.api_get_cidreq());

$editlpsettings_lang_var = api_convert_encoding(get_lang('EditLPSettings'), $charset, api_get_system_encoding());

// form title
$form->addElement('header',null, $editlpsettings_lang_var);

//Title
$title_lang_var = api_convert_encoding(get_lang('_title'), $charset, api_get_system_encoding());
$form->addElement('text', 'lp_name', api_ucfirst($title_lang_var),array('size'=>43));
$form->applyFilter('lp_name', 'html_filter');
$form->addRule('lp_name', get_lang('ThisFieldIsRequired'), 'required');

//Encoding
$charset_lang_var = api_convert_encoding(get_lang('Charset'), $charset, api_get_system_encoding());
$encoding_select = &$form->addElement('select', 'lp_encoding', $charset_lang_var);
$encodings = array('UTF-8','ISO-8859-1','ISO-8859-15','cp1251','cp1252','KOI8-R','BIG5','GB2312','Shift_JIS','EUC-JP');
foreach($encodings as $encoding){
	if (api_equal_encodings($encoding, $_SESSION['oLP']->encoding)) {
  		$s_selected_encoding = $encoding;
  	}
  	$encoding_select->addOption($encoding,$encoding);
}

//Origin
$origin_lang_var = api_convert_encoding(get_lang('Origin'), $charset, api_get_system_encoding());
$origin_select = &$form->addElement('select', 'lp_maker', $origin_lang_var);
$lp_orig = $_SESSION['oLP']->get_maker();

include('content_makers.inc.php');
foreach($content_origins as $origin){
	if($lp_orig == $origin){
		$s_selected_origin = $origin;
	}
	$origin = api_convert_encoding($origin, $charset, api_get_system_encoding());
	$origin_select->addOption($origin,$origin);
}

//Content proximity
$contprox_lang_var = api_convert_encoding(get_lang('ContentProximity'), $charset, api_get_system_encoding());
$local_lang_var = api_convert_encoding(get_lang('Local'), $charset, api_get_system_encoding());
$remote_lang_var = api_convert_encoding(get_lang('Remote'), $charset, api_get_system_encoding());
$content_proximity_select = &$form->addElement('select', 'lp_proximity', $contprox_lang_var);
$lp_prox = $_SESSION['oLP']->get_proximity();
if ($lp_prox != 'local') {
	$s_selected_proximity = 'remote';
} else {
	$s_selected_proximity = 'local';
}
$content_proximity_select->addOption($local_lang_var, 'local');
$content_proximity_select->addOption($remote_lang_var, 'remote');


if (api_get_setting('allow_course_theme') == 'true')
{
    $mycourselptheme=api_get_course_setting('allow_learning_path_theme');
    $theme_lang_var = api_convert_encoding(get_lang('Theme'), $charset, api_get_system_encoding());
    if (!empty($mycourselptheme) && $mycourselptheme!=-1 && $mycourselptheme== 1)
    {
            //LP theme picker
            $theme_select = &$form->addElement('select_theme', 'lp_theme', $theme_lang_var);
            $form->applyFilter('lp_theme', 'trim');

            $s_theme = $_SESSION['oLP']->get_theme();
            $theme_select ->setSelected($s_theme); //default
    }
}

// Course interface
$dfcourseint_lang_var = api_convert_encoding(get_lang('LpSelectDefaultCourseInterface'), $charset, api_get_system_encoding());
$lpdfmode_lang_var = api_convert_encoding(get_lang('LpDefaultMode'), $charset, api_get_system_encoding());
$lpoldmode_lang_var = api_convert_encoding(get_lang('LpOldInterfaceMode'), $charset, api_get_system_encoding());
$lpnewmode_lang_var = api_convert_encoding(get_lang('LpNewInterfaceMode'), $charset, api_get_system_encoding());
$lpnewwindowmode_lang_var = api_convert_encoding(get_lang('LpNewWindowInterfaceMode'), $charset, api_get_system_encoding());
$dfcourseint_fixed_lang_var = api_convert_encoding(get_lang('LpSelectDefaultCourseInterfaceFixed'), $charset, api_get_system_encoding());

$course_interface_select = &$form->addElement('select', 'lp_interface', $dfcourseint_lang_var);
$lp_interfaz = $_SESSION['oLP']->get_course_interface();
if ($lp_interfaz == 0) {
	$s_selected_lp_interfaz = '0';
} elseif ($lp_interfaz == 2) {
	$s_selected_lp_interfaz = '2';
} elseif ($lp_interfaz == 3) {
	$s_selected_lp_interfaz = '3';
} elseif ($lp_interfaz == 4) {
	$s_selected_lp_interfaz = '4';
} else {
	$s_selected_lp_interfaz = '1';
}
$course_interface_select->addOption($lpdfmode_lang_var, '0');
$course_interface_select->addOption($lpoldmode_lang_var, '1');
$course_interface_select->addOption($lpnewmode_lang_var, '2');
$course_interface_select->addOption($lpnewwindowmode_lang_var, '3');
$course_interface_select->addOption($dfcourseint_fixed_lang_var, '4');
//Author
//$form->addElement('html_editor', 'lp_author', get_lang('Author'), array('size'=>80), array('ToolbarSet' => 'LearningPathAuthor', 'Width' => '100%', 'Height' => '150px') );
$author_lang_var = api_convert_encoding(get_lang('Author'), $charset, api_get_system_encoding());
$form->addElement('text', 'lp_author', $author_lang_var, '');
$form->applyFilter('lp_author', 'html_filter');
/* Request #12288 
// LP image
$form->add_progress_bar();
if( strlen($_SESSION['oLP']->get_preview_image() ) > 0)
{
	$imagepreview_lang_var = api_convert_encoding(get_lang('ImagePreview'), $charset, api_get_system_encoding());
	$delimage_lang_var = api_convert_encoding(get_lang('DelImage'), $charset, api_get_system_encoding());

	$show_preview_image='<img src='.api_get_path(WEB_COURSE_PATH).api_get_course_path().'/upload/learning_path/images/'.$_SESSION['oLP']->get_preview_image().'>';
	$div = '<div class="row">
	<div class="label">'.$imagepreview_lang_var.'</div>
	<div class="formw">
	'.$show_preview_image.'
	</div>
	</div>';
	$form->addElement('html', $div .'<br/>');
	$form->addElement('checkbox', 'remove_picture', null, $delimage_lang_var);
}
$updateimage_lang_var = api_convert_encoding(get_lang('UpdateImage'), $charset, api_get_system_encoding());
$addimage_lang_var = api_convert_encoding(get_lang('AddImage'), $charset, api_get_system_encoding());

$form->addElement('file', 'lp_preview_image', ($_SESSION['oLP']->get_preview_image() != '' ? $updateimage_lang_var : $addimage_lang_var));

$imgresize_lang_var = api_convert_encoding(get_lang('ImageWillResizeMsg'), $charset, api_get_system_encoding());
$form->addElement('static', null, null, $imgresize_lang_var);


if($_SESSION['oLP']->type == '3')
{ // then an update of source exists
	$updateaicc_lang_var = api_convert_encoding(get_lang('UpdateAiccSource'), $charset, api_get_system_encoding());
	$form->addElement('file', 'lp_update_source', $updateaicc_lang_var);
}


$onlyimages_lang_var = api_convert_encoding(get_lang('OnlyImagesAllowed'), $charset, api_get_system_encoding());
$form->addRule('lp_preview_image', $onlyimages_lang_var, 'filetype', array ('jpg', 'jpeg', 'png', 'gif'));
 */
// Search terms (only if search is activated)
if (api_get_setting('search_enabled') === 'true' && extension_loaded('xapian'))
{
	$specific_fields = get_specific_field_list();
	foreach ($specific_fields as $specific_field) {
		$form -> addElement ('text', $specific_field['code'], $specific_field['name']);
		$filter = array('course_code'=> "'". api_get_course_id() ."'", 'field_id' => $specific_field['id'], 'ref_id' => $_SESSION['oLP']->lp_id, 'tool_id' => '\''. TOOL_LEARNPATH .'\'');
		$values = get_specific_field_values_list($filter, array('value'));
		if ( !empty($values) ) {
			$arr_str_values = array();
			foreach ($values as $value) {
				$arr_str_values[] = $value['value'];
			}
			$defaults[$specific_field['code']] = implode(', ', $arr_str_values);
		}
	}
}

if (api_get_setting('enable_certificate') === 'true') {
    // certificate option
    require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';
    $course_info = api_get_course_info();
    $current_language = !empty($course_info['language'])?$course_info['language']:api_get_interface_language();
    $certificates = CertificateManager::create()->getCertificatesList($current_language);
    $selCertificates = array();
    if (!empty($certificates)) {
        $selCertificates[0] = get_lang('None');
        foreach ($certificates as $tpl_id => $certificate) {
            $selCertificates[$tpl_id] = $certificate['title'];
        }
    }

    $certif_thumb = '';
    if (!empty($_SESSION['oLP']->certif_template)) {
        $certif_thumb = CertificateManager::create()->returnCertificateThumbnailImg($_SESSION['oLP']->certif_template);
    }

    $form->addElement('select', 'certificate_template', get_lang('Certificate'), $selCertificates, array('id'=>'quiz-certificate'));
    $form->addElement('html', '<div id="quiz-certificate-thumb" style="'.(!empty($_SESSION['oLP']->certif_template)?'display:block;':'display:none;').'">'.$certif_thumb.'</div>');

    $form->addElement('html', '<div id="quiz-certificate-score" style="'.(!empty($_SESSION['oLP']->certif_template)?'display:block;':'display:none;').'">');
    $rdn_certif_score_opt = array();
    $Progress = api_convert_encoding(get_lang('CertifEvaluationType'), $charset, api_get_system_encoding());
    $rdn_certif_score_opt[] = FormValidator :: createElement('static', '', null, $Progress. '<br>');
    $Progress = api_convert_encoding(get_lang('Progress'), $charset, api_get_system_encoding());
    $Score = api_convert_encoding(get_lang('Score'), $charset, api_get_system_encoding());
    $rdn_certif_score_opt[] = FormValidator :: createElement('radio', 'certif_evaluation_type', null, $Progress, '1');
    $rdn_certif_score_opt[] = FormValidator :: createElement('radio', 'certif_evaluation_type', null, $Score, '2');
    $form->addGroup($rdn_certif_score_opt, null, '', array('class'=>'certif-evalua-type'));



    $form->addElement('text', 'certificate_min_score', get_lang('CertificateMinEvaluation').' (%)');
    $form->addElement('html', '</div>');

    $certif_evaluation = '';
    $certif_type = 1;
    if (!empty($_SESSION['oLP']->certif_min_score) && $_SESSION['oLP']->certif_min_score != '0.00') {
        $certif_evaluation = $_SESSION['oLP']->certif_min_score;
        $certif_type = 2;
    } else {
        $certif_evaluation = $_SESSION['oLP']->certif_min_progress;
        $certif_type = 1;
    }
    
    $defaults['certif_evaluation_type'] = $certif_type;
    $defaults['certificate_template'] = $_SESSION['oLP']->certif_template;
    $defaults['certificate_min_score'] = $certif_evaluation;
}

$showdebug_lang_var = api_convert_encoding(get_lang('ShowDebug'), $charset, api_get_system_encoding());
$hidedebug_lang_var = api_convert_encoding(get_lang('HideDebug'), $charset, api_get_system_encoding());
$selectModule_lang_var = api_convert_encoding(get_lang('StartModuleToolType'), $charset, api_get_system_encoding());
//$selectDebug = &$form->addElement('select', 'enable_debug', $showdebug_lang_var);
$selectDebug = &$form->addElement('checkbox', 'enable_debug', $showdebug_lang_var);
//$selectDebug->addOption($showdebug_lang_var, 0);
//$selectDebug->addOption($hidedebug_lang_var, 1);

//start module tool
$rdn_start_module_tool_opt = array();
$smt_value_options = array("last", "first");
$LastItemChecked = api_convert_encoding(get_lang('LastItemChecked'), $charset, api_get_system_encoding());
$AlwaysFirstItem = api_convert_encoding(get_lang('AlwaysFirstItem'), $charset, api_get_system_encoding());
$rdn_start_module_tool_opt[] = FormValidator :: createElement('radio', 'start_module_tool_type', null, $LastItemChecked, $smt_value_options[0]);
$rdn_start_module_tool_opt[] = FormValidator :: createElement('radio', 'start_module_tool_type', null, $AlwaysFirstItem, $smt_value_options[1]);
$form->addGroup($rdn_start_module_tool_opt, null, $selectModule_lang_var, array('class'=>'start-module-tool-type'));
$start_module_tool_value = $smt_value_options[0];
$description_value = unserialize($_SESSION['oLP']->description);
if ($description_value && in_array($description_value['start_module_tool_type'], $smt_value_options)) {
    $start_module_tool_value = $description_value['start_module_tool_type'];
}
$defaults['start_module_tool_type'] = $start_module_tool_value;

//default values
$content_proximity_select -> setSelected($s_selected_proximity);
$origin_select -> setSelected($s_selected_origin);
$course_interface_select->  setSelected($s_selected_lp_interfaz);
$encoding_select -> setSelected($s_selected_encoding);
$defaults['lp_name'] = Security::remove_XSS(api_convert_encoding($_SESSION['oLP']->get_name(), $charset, api_get_system_encoding()));
$defaults['lp_author'] = Security::remove_XSS(api_convert_encoding($_SESSION['oLP']->get_author(), $charset, api_get_system_encoding()));
$defaults['enable_debug'] = $_SESSION['oLP']->scorm_debug;

//get the keyword
$searchkey = new SearchEngineManager();
$keyword = $searchkey->getKeyWord(TOOL_LEARNPATH, $_SESSION['oLP']->lp_id);

$defaults['search_terms'] = $keyword;
if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian')) {
    $search_lang_var = api_convert_encoding(get_lang('SearchKeywords'), $charset, api_get_system_encoding());
    //TODO: include language file
    $form -> addElement('html','<input type="hidden" name="index_document" value="1"/>'.
     '<input type="hidden" name="language" value="' . api_get_setting('platformLanguage') . '"/>');
   // $form-> addElement('textarea','search_terms',$search_lang_var,array('cols'=>65));
    $form->addElement('text', 'search_terms', '<br/>' . $search_lang_var . ': ', array('class' => 'tag-it'));
}

//Submit button
$savelp_lang_var = api_convert_encoding(get_lang('SaveLPSettings'), $charset, api_get_system_encoding());
$form->addElement('style_submit_button', 'Submit',$savelp_lang_var,'class="save"');

//Hidden fields
$form->addElement('hidden', 'action', 'update_lp');
$form->addElement('hidden', 'lp_id', $_SESSION['oLP']->get_id());



$form->setDefaults($defaults);
echo '<div id="content_with_secondary_actions">';
echo '<table style="width:100%"><tr><td style="width:70%" valign="top">';
	$form -> display();
echo '</td><td style="width:30%" valign="top">
<table style="text-align: left;padding-left:20px; width: 100%;" border="0" cellpadding="2" cellspacing="2">
  <tbody>
    <tr>
      <td style="vertical-align: top;">'.$lpnewmode_lang_var.'</td>
    </tr>
    <tr>
      <td style="vertical-align: top;">'.Display::return_icon('full_screen.png',$lpnewmode_lang_var).'</td>
    </tr>
    <tr>
      <td style="vertical-align: top;">'.$lpdfmode_lang_var.'</td>
    </tr>
    <tr>
      <td style="vertical-align: top;">'.Display::return_icon('dokeos_navigation.png',$lpdfmode_lang_var).'<br>
    </td>
    </tr>
    <tr>
      <td style="vertical-align: top;">'.$lpoldmode_lang_var.'</td>
    </tr>
    <tr>
      <td style="vertical-align: top;">'.Display::return_icon('table_of_contents.png',$lpoldmode_lang_var).'<br>
    </td>
    </tr>
  </tbody>
</table>
</td></tr></table>';
echo '</div>';
// Actions bar
//echo '<div class="actions">';
//echo '</div>';
Display::display_footer();
?>
