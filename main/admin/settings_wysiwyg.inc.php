<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Patrick Cool
* @since Dokeos 2.0
* @package dokeos.admin
*/
?>
<style type="text/css">
.toolbarline, .toolbarlinedest{
	height: 30px;
	border: 1px solid yellow;
	color: grey;
	background-image:url("<?php echo api_get_path(WEB_CODE_PATH);?>inc/lib/fckeditor/editor/skins/default/images/toolbar.start.gif");
	background-repeat:no-repeat;
	padding-left: 5px;
}
.sorthelper {
	border: 1px dashed #CCC;
	background-color: #EFEFEF;
	height: 23px;
	width: 23px;
}
.TB_Button_Image {
	background-repeat:no-repeat;
	height:16px;
	margin:4px 3px 2px;
	overflow:hidden;
	width:16px;
}
.sortitem{cursor:move;}
</style>
<script type="text/javascript">
$(function() {
	$(".toolbarline").sortable({
		connectWith: '.toolbarlinedest',
		placeholder: 'sorthelper',
		forcePlaceholderSize: 'true',
		cursor: 'move',
		opacity: 0.6,
	        update: function() {
			var order1 = $('#toolbarline1').sortable('serialize');
			var order2 = $('#toolbarline2').sortable('serialize');
			var order3 = $('#toolbarline3').sortable('serialize');
			var order4 = $('#toolbarline4').sortable('serialize');
			$('#toolbarbuttonorder1').val(order1);
			$('#toolbarbuttonorder2').val(order2);
			$('#toolbarbuttonorder3').val(order3);
			$('#toolbarbuttonorder4').val(order4);
	        }
	});

});
</script>

<?php
// available buttons for the toolbar
// these are the default buttons of fckeditor as they appear in the image
$buttons[] = 'Source';
$buttons[] = 'DocProps';
$buttons[] = 'Save';
$buttons[] = 'NewPage';
$buttons[] = 'Preview';
$buttons[] = 'Templates';
$buttons[] = 'Cut';
$buttons[] = 'Copy';
$buttons[] = 'Paste';
$buttons[] = 'PasteText';
$buttons[] = 'PasteWord';
$buttons[] = 'Print';
$buttons[] = 'SpellCheck';
$buttons[] = 'Undo';
$buttons[] = 'Redo';
$buttons[] = 'Find';
$buttons[] = 'Replace';
$buttons[] = 'SelectAll';
$buttons[] = 'RemoveFormat';
$buttons[] = 'Bold';
$buttons[] = 'Italic';
$buttons[] = 'Underline';
$buttons[] = 'StrikeThrough';
$buttons[] = 'Subscript';
$buttons[] = 'Superscript';
$buttons[] = 'OrderedList';
$buttons[] = 'UnorderedList';
$buttons[] = 'Outdent';
$buttons[] = 'Indent';
$buttons[] = 'JustifyLeft';
$buttons[] = 'JustifyCenter';
$buttons[] = 'JustifyRight';
$buttons[] = 'JustifyFull';
$buttons[] = 'Link';
$buttons[] = 'Unlink';
$buttons[] = 'Anchor';
$buttons[] = 'Image';
$buttons[] = 'Flash';
$buttons[] = 'Table';
$buttons[] = 'Rule';
$buttons[] = 'Smiley';
$buttons[] = 'SpecialChar';
$buttons[] = 'PageBreak';
$buttons[] = '';

$buttons[] = 'TextColor';
$buttons[] = 'BGColor';
$buttons[] = 'About';
$buttons[] = 'Form';
$buttons[] = 'Checkbox';
$buttons[] = 'Radio';
$buttons[] = 'TextField';
$buttons[] = 'Textarea';
$buttons[] = 'Select';
$buttons[] = 'Button';
$buttons[] = 'ImageButton';
$buttons[] = 'HiddenField';
$buttons[] = 'TableCellProp';
$buttons[] = 'TableInsertCellAfter';
$buttons[] = 'TableDeleteCells';
$buttons[] = 'TableMergeCells';
$buttons[] = 'TableHorizontalSplitCell';
$buttons[] = 'TableInsertRowAfter';
$buttons[] = 'TableDeleteRows';
$buttons[] = 'TableInsertColumnAfter';
$buttons[] = 'TableDeleteColumns';
$buttons[] = 'FitWindow';
$buttons[] = '';
$buttons[] = '';
$buttons[] = '';
$buttons[] = '';
$buttons[] = '';
$buttons[] = 'ShowBlocks';
$buttons[] = 'Blockquote';
$buttons[] = 'CreateDiv';
$buttons[] = '';
$buttons[] = '';

// additonal (Dokeos) buttons
$additional_buttons[]=array('name'=>'imgmapPopup',	'image'=>api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/imgmap/images/icon_silver.gif');
$additional_buttons[]=array('name'=>'MP3',			'image'=>api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/MP3/mp3.gif');
$additional_buttons[]=array('name'=>'flvPlayer',	'image'=>api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/flvPlayer/flvPlayer.gif');
$additional_buttons[]=array('name'=>'videoPlayer',	'image'=>api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/videoPlayer/videoPlayer.png');
$additional_buttons[]=array('name'=>'YouTube',		'image'=>api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/youtube/youtube.gif');
$additional_buttons[]=array('name'=>'googlemaps',	'image'=>api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/googlemaps/images/mapIcon.gif');

/*
	['Style','FontFormat','FontName','FontSize'],
*/

// action handling
wysiwyg_toolbar_action_handling();

// display the action of the wysiwyg settings
display_wysiwyg_toolbar_actions();

// display all the toolbars
display_available_wysiwyg_toolbars();

// display the footer
Display :: display_footer();

function display_wysiwyg_toolbar_actions(){
	echo '<div class="actions">';
	echo '<a href="settings.php?category=Wysiwyg&amp;action=addtoolbar">'.get_lang('AddToolbar').'</a>';
	echo '</div>';
}


/*
	the defined toolbars will be saved as
	variable = 'toolbardefinition'
	subkey = name of toolbar
	category = 'Wysiwyg'
	subcategory = 'toolbardefinition'
	selected_value = the active buttons (a serialised array)

	the places where the toolbar is activated is
	variable = 'toolbarapplication'
	subkey = the location where the toolbar is applied (example: agenda)
	category = 'Wysiwyg'
	subcategory = 'toolbarapplication'
	selected_value = the toolbar that is used in the location
*/
function display_available_wysiwyg_toolbars(){
	// database table definition
	$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

	// starting the table
	echo '<table>';
	echo '<tr>';
	echo '<th>'.get_lang('ToolbarName').'</th>';
	echo '<th>'.get_lang('ToolbarLocation').'</th>';
	echo '<th>'.get_lang('ToolbarActiveButtons').'</th>';
	echo '</tr>';

	// the locations where the toolbar is used
	$locations_of_toolbar = get_locations_by_wysiwyg_toolbar();

	// getting all the defined toolbars from the database
	$sql = "SELECT * FROM $table_settings_current WHERE category='Wysiwyg' AND variable='toolbardefinition'";
	$result	= Database::query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_array($result)){
		echo '<tr>';
		echo '<td valign="top">'.$row['subkey'].'</td>';
		echo '<td>'.implode(',',$locations_of_toolbar[$row['subkey']]).'</td>';
		$toolbar = unserialize($row['selected_value']);
		echo '<td>';
		foreach ($toolbar as $toolbarline=>$toolbarbuttons){
			echo '<div id="toolbarline'.$toolbarline.'" style="border: 1px solid #D0D0D0;">';
			foreach ($toolbarbuttons as $key=>$value){
				echo return_wysywig_button(1,$value);
			}
			echo '</div><div style="clear:both;"></div>';
		}
		echo '</td>';
	}

	echo '</table>';
}

function get_locations_by_wysiwyg_toolbar(){
	// database table definition
	$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

	$sql = "SELECT * FROM $table_settings_current WHERE category='Wysiwyg' AND subcategory='toolbarapplication'";
	$result	= Database::query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_array($resultsystemsettings)){
		$return[$row['selected_value']][] = $row['subkey'];
	}
	return $return;
}

function wysiwyg_toolbar_action_handling(){
	switch ($_GET['action']){
		case 'addtoolbar':
			display_wysiwyg_toolbar_add_form();
			break;
	}
}

function display_wysiwyg_toolbar_add_form(){
	global $_configuration;

	// creating the form
	$form = new FormValidator('toolbar', 'post', 'settings.php?category=Wysiwyg&amp;action=addtoolbar');
	$form->addElement('header', null, get_lang('AddToolbar'));
	$form->addElement('text', 'toolbartitle', get_lang('ToolbarTitle'));
	$form->addElement('text', 'toolbarbuttonorder1', get_lang('ToolbarButtonOrder'),array('id'=>'toolbarbuttonorder1'));
	$form->addElement('text', 'toolbarbuttonorder2', get_lang('ToolbarButtonOrder'),array('id'=>'toolbarbuttonorder2'));
	$form->addElement('text', 'toolbarbuttonorder3', get_lang('ToolbarButtonOrder'),array('id'=>'toolbarbuttonorder3'));
	$form->addElement('text', 'toolbarbuttonorder4', get_lang('ToolbarButtonOrder'),array('id'=>'toolbarbuttonorder4'));
	$form->addElement('static', 'toolbarbuttonsavailable', get_lang('ToolbarAvailableButtons'),display_wysiwyg_toolbar_available_buttons());
	$form->addElement('static', 'toolbarbuttonsavailable', '',get_lang('ToolbarDragDropExplanation'));
	$form->addElement('static', 'toolbarbuttonsavailable', get_lang('ToolbarYourConfiguration'),'<div id="toolbarline1" class="toolbarline toolbarlinedest"></div><div id="toolbarline2" class="toolbarline toolbarlinedest"></div><div id="toolbarline3" class="toolbarline toolbarlinedest"></div><div id="toolbarline4" class="toolbarline toolbarlinedest"></div>');
	$form->addElement('text', 'toolbarheight', get_lang('ToolbarHeight'),array('size'=>'5'));
	$form->addElement('text', 'toolbarwidth', get_lang('ToolbarWidth'),array('size'=>'5'));
	$form->addElement('style_submit_button', null,get_lang('SavePluginSettings'), 'class="save"');

	// processing the form
	if ($form->validate()){
		$values = $form->exportValues();
		// converting the toolbarbuttonorder 1-> 4 into an array
		$toolbarlines = array('toolbarbuttonorder1','toolbarbuttonorder2','toolbarbuttonorder3','toolbarbuttonorder4');
		foreach ($toolbarlines as $key=>$value){
			$toolbar[] = explode('&',str_replace('toolbarbutton[]=','',$values[$value]));
		}

		// storing the toolbar in the database
		// the parameters of the function : $value, $variable, $subkey, $type, $c = null, $title = '', $com = '', $sc = null, $subkeytext = null, $a = 1, $v = 0)
		// the defined toolbars will be saved as follows in the database: variable = 'toolbardefinition', subkey = name of toolbar, category = 'Wysiwyg', subcategory = 'toolbardefinition', selected_value = the active buttons (a serialised array)

		api_add_setting(serialize($toolbar),'toolbardefinition',$values['toolbartitle'],null,'Wysiwyg','toolbardefinition',null,null,null,$_configuration['access_url'],1);
	} else{
		// displaying the form
		$form->display();
	}
}

function display_wysiwyg_toolbar_available_buttons(){
	global $buttons,$additional_buttons;

	$return .= '<div id="toolbaravailablebuttons" class="toolbarline">';
	// default fck buttons
	foreach ($buttons as $key=>$button){
		if (!empty($button)){
			$return .= '<div id="toolbarbutton_'.$button.'" class="sortitem" title="'.$button.'" style="float: left;background:url(\''.api_get_path(WEB_CODE_PATH).'inc/lib/fckeditor/editor/skins/silver/images/toolbar.buttonbg.gif\') repeat-x scroll 0 0 #EFEFEF;opacity:0.7;height:21px;margin:1px;padding:1px;"><img src="'.api_get_path(WEB_CODE_PATH).'inc/lib/fckeditor/editor/images/spacer.gif" style="background-position: 0px -'.($key*16).'px; background-image: url(\''.api_get_path(WEB_CODE_PATH).'inc/lib/fckeditor/editor/skins/silver/fck_strip.gif\');" class="TB_Button_Image" title="'.$button.'" alt="'.$button.'"/></div>';
		}
	}
	// additional buttons (plugins)
	foreach ($additional_buttons as $key=>$button){
		if (!empty($button)){
		$return .= return_wysywig_button($key, $button);
		}
	}
	$return .= '</div>';
	return $return;
}

function return_wysywig_button($key, $button){
	$return .= '<div id="toolbarbutton_'.$button.'" class="sortitem" style="float: left;background:url(\''.api_get_path(WEB_CODE_PATH).'inc/lib/fckeditor/editor/skins/silver/images/toolbar.buttonbg.gif\') repeat-x scroll 0 0 #EFEFEF;opacity:0.7;height:21px;margin:1px;padding:1px;"><img src="'.api_get_path(WEB_CODE_PATH).'inc/lib/fckeditor/editor/images/spacer.gif" style="background-position: 0px -'.($key*16).'px; background-image: url(\''.api_get_path(WEB_CODE_PATH).'inc/lib/fckeditor/editor/skins/silver/fck_strip.gif\');" class="TB_Button_Image" /></div>';
	return $return;
}
?>
