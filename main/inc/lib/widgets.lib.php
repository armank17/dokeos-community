<?php
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once ('../global.inc.php');

// POST action handling
switch ($_POST ['action']) {
	case 'savewidgetsinlocation' :
		savewidgetsinlocation ();
		break;
	case 'savewidgetstatus' :
		savewidgetstatus ();
		break;
	case 'movewidgetdown' :
		movewidgetdown ();
		break;
	case 'movewidgetup' :
		movewidgetup ();
		break;
	case 'widget_settings_form' :
		widget_settings_form ();
		break;
	case 'displayactivationform' :
		displayactivationform ();
		break;
	case 'load_widgets' :
		load_widgets ();
		break;
	case 'savewidgetsettings' :
		savewidgetsettings ();
		break;
	case 'saverss':
		saverss();
		break;
}

// GET action handling
switch ($_GET ['action']) {
	case 'addwidgets' :
		addwidgets ();
		break;
	case 'addrsswidget' :
		addrsswidget();
		break;
	case 'get_widget_location' :
		get_widget_location ();
		break;
	case 'save_widget_location' :
		save_widget_location ();
		break;
	case 'savewidgetsettings' :
		savewidgetsettings ();
		break;
	case 'saverss':
		saverss();
		break;
}

/**
 * @todo the page layout file should define the valid locations on the page and these should be used to validate the $_POST['location']
 * 		 to prevent that hackers put there an illegal value which would cause other course settings to be deleted.
 * @todo limit this action to the course admin
 */
function savewidgetsinlocation() {
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// before continuing we cache all the rss feeds (if any)
	cache_rss();

	// first we delete all the existing widgets in the location
	$sql = "DELETE FROM $table_setting WHERE variable='" . Database::escape_string ( $_POST ['location'] ) . "' AND subcategory='".Database::escape_string($script)."'";
	$result = api_sql_query ( $sql );

	// now we loop through all the widgets and add them to the course settings table
	foreach ( $_POST ['widget'] as $key => $value ) {
		// we check if it is a RSS feed or not. If it is we have to retrieve the rss information from the $cached_rss
		if (strstr(trim($value),'rss') AND trim($value)<> 'tabbedrss'){
			$value = $_SESSION['cached_rss'][str_replace('rss','',$value)];
		}

		$sql = "INSERT INTO $table_setting (variable, subkey, $value_field, category, subcategory) VALUES ('" . Database::escape_string ( $_POST ['location'] ) . "','" . Database::escape_string ( $value ) . "','" . Database::escape_string ( $key + 1 ) . "','widget','".Database::escape_string($script)."')";
		$result = api_sql_query ( $sql );

		// we also have to remove the widgets from other locations (when we drag and drop a widget from location X to location Y)
		$sql = "DELETE FROM $table_setting WHERE variable <> '" . Database::escape_string ( $_POST ['location'] ) . "' AND subkey = '" . Database::escape_string ( $value ) . "' AND subcategory = '".Database::escape_string($script)."'";
		$result = api_sql_query ( $sql );
	}
}
/**
 *
 */
function savewidgetstatus() {
	global $_course;

	// Database table definition
	$table_user_settings = Database::get_user_personal_table ( 'user_setting' );

	// first we delete the status of the widget
	$sql = "DELETE FROM $table_user_settings
					WHERE variable='" . Database::escape_string ( $_POST ['widget'] ) . "'
					AND subkey='status'
					AND user_id='" . Database::escape_string ( api_get_user_id () ) . "'
					AND course_code='" . Database::escape_string ( $_course ['id'] ) . "'";
	//r($sql);
	$result = api_sql_query ( $sql );

	// now we save the new status of the widget (update statement cannot work because it is uncertain that there is already a status for this widget)
	if (api_get_user_id () <> 0) {
		$sql = "INSERT INTO $table_user_settings (user_id, course_code, variable, subkey, value) VALUES (
						'" . Database::escape_string ( api_get_user_id () ) . "',
						'" . Database::escape_string ( $_course ['id'] ) . "',
						'" . Database::escape_string ( $_POST ['widget'] ) . "',
						'status',
						'" . Database::escape_string ( $_POST ['status'] ) . "')";
		$result = api_sql_query ( $sql );
		//r($sql);
	}
}

/**
 * This function generates the javascript for the portals that need to be collapsed
 *
 */
function collapsed_portals() {
	global $_user, $_course;

	// Database table definition
	$table_user_settings = Database::get_user_personal_table ( 'user_setting' );

	$sql = "SELECT * FROM $table_user_settings
				WHERE user_id='" . Database::escape_string ( $_user ['user_id'] ) . "'
				AND course_code = '" . Database::escape_string ( $_course ['id'] ) . "'
				AND subkey = 'status'
				AND value = 'collapsed'";
	$result = api_sql_query ( $sql );
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		echo "collapse_portlet('" . $row ['variable'] . "');";
	}
}

/**
 * This function generates the javascript for the portals that need to be collapsed
 *
 */
function hide_titles() {
	global $_user, $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	$sql = "SELECT * FROM $table_setting
				WHERE subkey = 'hide_title'
				AND value = '1'
				AND subcategory = '".Database::escape_string($script)."'";
	$result = api_sql_query ( $sql );
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		// hide the title
		echo 'hide_titles("widget_' . $row ['variable'] . '");';
	}
}

function movewidgetdown() {
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// we loop through the widget of the given location and if we find the given widget then we know that we have to
	// take the next one and switch these of place
	$sql = "SELECT * FROM $table_setting WHERE variable='" . Database::escape_string ( $_POST ['location'] ) . "' AND subcategory = '".Database::escape_string($script)."' ORDER by $value_field ASC";
	echo $sql;
	$result = api_sql_query ( $sql );
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		// in the previous iteration (while) we have found the widget we are moving down so this iteration finds the widget we have to switch place with
		if ($found == true) {
			$id2 = $row ['id'];
			$value2 = $row [$value_field];
			break;
		}

		// we have found the widget we want to move down so we store the needed information in $id1 and $value1
		if ($row ['subkey'] == str_replace ( 'widget_', '', $_POST ['widget'] )) {
			$found = true;
			$id1 = $row ['id'];
			$value1 = $row [$value_field];
		}
	}

	// doing the switch of the values
	$sql = "UPDATE $table_setting SET $value_field='" . Database::escape_string ( $value1 ) . "', subcategory = '".Database::escape_string($script)."'  WHERE id='" . Database::escape_string ( $id2 ) . "'";
	$sql2 = "UPDATE $table_setting SET $value_field='" . Database::escape_string ( $value2 ) . "', subcategory = '".Database::escape_string($script)."' WHERE id='" . Database::escape_string ( $id1 ) . "'";
	echo $sql;
	echo $sql2;
	if (! empty ( $value1 ) and ! empty ( $value2 ) and ! empty ( $id1 ) and ! empty ( $id2 )) {
		$result = api_sql_query ( $sql );
		$result2 = api_sql_query ( $sql2 );
		r($sql);
		r($sql2);
	}
}

function movewidgetup() {
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// we loop through the widget of the given location and if we find the given widget then we know that we have to
	// take the next one and switch these of place
	$sql = "SELECT * FROM $table_setting WHERE variable='" . Database::escape_string ( $_POST ['location'] ) . "' AND subcategory = '".Database::escape_string($script)."' ORDER by $value_field DESC";
	$result = api_sql_query ( $sql );
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		// in the previous iteration (while) we have found the widget we are moving down so this iteration finds the widget we have to switch place with
		if ($found == true) {
			$id2 = $row ['id'];
			$value2 = $row [$value_field];
			break;
		}

		// we have found the widget we want to move down so we store the needed information in $id1 and $value1
		if ($row ['subkey'] == str_replace ( 'widget_', '', $_POST ['widget'] )) {
			$found = true;
			$id1 = $row ['id'];
			$value1 = $row [$value_field];
		}
	}

	// doing the switch of the values
	$sql = "UPDATE $table_setting SET $value_field='" . Database::escape_string ( $value1 ) . "', subcategory = '".Database::escape_string($script)."' WHERE id='" . Database::escape_string ( $id2 ) . "'";
	$sql2 = "UPDATE $table_setting SET $value_field='" . Database::escape_string ( $value2 ) . "', subcategory = '".Database::escape_string($script)."' WHERE id='" . Database::escape_string ( $id1 ) . "'";

	if (! empty ( $value1 ) and ! empty ( $value2 ) and ! empty ( $id1 ) and ! empty ( $id2 )) {
		$result = api_sql_query ( $sql );
		$result2 = api_sql_query ( $sql2 );
		r($sql);
		r($sql2);
	}
}
/**
 * Display the form for changing the settings of the widget (by clicking the gear icon)
 * This displays first the generic settings that all the widgets have
 * and after this the widget specifi settings
 *
 */
function widget_settings_form() {
	global $_course;
	global $_setting;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// load the specific widget settings
	$widget_settings = api_load_widget_settings ();
	$_setting = array_merge($_setting, $widget_settings);

	echo '<form id="widget_settings" method="post" action="' . api_get_path ( WEB_PATH ) . 'main/inc/lib/widgets.lib.php?action=savewidgetsettings&widget=' . Security::Remove_XSS ( $_POST ['widget'] ) . '">';
	//echo '<div class="dialogfeedback ui-state-highlight" style="display:none;"></div>';
	echo '<input type="hidden" name="widget" value="' . Security::Remove_XSS ( $_POST ['widget'] ) . '">';

	// getting the real widget name
	// before continuing we cache all the rss feeds (if any)
	cache_rss();

	$widget = $_POST ['widget'];

	// check if it is a RSS feed
	if (strstr(trim($_POST ['widget']),'widget_rss')){
		$realwidgetname = $_SESSION['cached_rss'][str_replace('widget_rss','',$_POST ['widget'])];
	} else {
		$realwidgetname = str_replace ( 'widget_', '', $_POST ['widget'] );
	}

	// the default widget settings form element: widget title
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">'.get_lang('WidgetTitle').'</div>';
	echo '<label><input type="text" name="widget_setting_title" id="widget_setting_title" value="'.api_get_setting($realwidgetname,'title').'"/></label>';
	echo '</div>';

	// the default widget settings form element: display title
	if (api_get_setting ( $realwidgetname, 'hide_title' ) == '1') {
		$selected1 = 'checked="checked""';
		$selected2 = '';
	} else {
		$selected1 = '';
		$selected2 = 'checked="checked""';
	}
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">' . get_lang ( 'HideWidgetTitle' ) . '</div>';
	echo '<label><input type="radio" name="widget_setting_hide_title" id="widget_setting_hide_title" value="1" ' . $selected1 . '/>' . get_lang ( 'Hide' ) . '</label>';
	echo '<label><input type="radio" name="widget_setting_hide_title" id="widget_setting_show_title" value="0" ' . $selected2 . '/>' . get_lang ( 'Show' ) . '</label>';
	echo '</div>';

	// the default widget settings form element: location
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">' . get_lang ( 'Location' ) . '</div>';
	echo '<select id="widget_setting_location" name="widget_setting_location" onchange="load_widgets_in_location_for_widgetsettings(\'\',\'widget_setting_display_order\');">
			<option value="disable">' . get_lang ( 'Disabled' ) . '</option>';
	echo '</select>';
	echo '</div>';
	echo '<script type="text/javascript">
			// get all the locations of the current layout
			var arrayLocations = $(".location");

			// populate the #widget_setting_location dropdown list with all the locations
			$.each(arrayLocations, function() {
				$("#widget_setting_location").append($(\'<option class="selectlocation"></option>\').val($(this).attr("id")).html($(this).attr("title")));
			});

			// if the widget is already activated then we have to make sure that the dropdown displays the correct location for that widget
			$.get("' . api_get_path ( WEB_PATH ) . 'main/inc/lib/widgets.lib.php", {action:"get_widget_location",widget:"' . str_replace ( 'widget_', '', $_POST ['widget'] ) . '"},
				function(activeinlocation) {
					$("#widget_setting_location").val(activeinlocation);
				});

			// when we change the location we have to reload the display order
    		$("#widget_setting_location option").live("click", function() {
    			//load_widgets_in_location_for_widgetsettings($("#widget_setting_location").val(),"widget_setting_display_order");
    		});
		  </script>';

	// the default widget setting form element: display order
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">' . get_lang ( 'DisplayOrder' ) . '</div>';
	echo '<select id="widget_setting_display_order" name="widget_setting_display_order">';
	echo '<option class="selectdisplayorder" value="FIRST">' . get_lang ( 'FirstPosition' ) . '</option>';
	echo '</select>';
	echo '</div>';
	echo '<script type="text/javascript">
			// current location
			var currentlocation = $("#"+"' . $_POST ['widget'] . '").parent().attr("id");

			// load the widgets for the given location
			load_widgets_in_location_for_widgetsettings(currentlocation,"widget_setting_display_order");

			function load_widgets_in_location_for_widgetsettings(location, formelement){
				//alert("*"+location);
				if (location==""){
					location = $("#widget_setting_location").val()
				}

    			// remove all the ones that are currently in the display order dropdown
    			$("#widget_setting_display_order").children().remove();
    			// add the first location option
				$("#widget_setting_display_order").append("<option value=\"FIRST\">' . get_lang ( 'FirstPosition' ) . '</option>");

				// get all the widgets in the current location
				var arrayWidgetsInLocations = $("#"+location+" .portlet");

				// populate the #widget_setting_display_order dropdown list with all the widgets of the current location
				$.each(arrayWidgetsInLocations, function() {
					$("#widget_setting_display_order").append($(\'<option class="selectdisplayorder"></option>\').val($(this).attr("id")).html("' . get_lang ( 'Before' ) . ' "+$(".widgettitle",this).html()));
				});

				// Add a LAST position
				$("#widget_setting_display_order").append($(\'<option class="selectdisplayorder"></option>\').val("LAST").html("' . get_lang ( 'LastPosition' ) . '"));

				// we want the dropdown to display the correct display order of the current widget
				var nextwidget_id = $("#"+"' . $_POST ['widget'] . '").next().attr("id");
				if (typeof(nextwidget_id) == "undefined") {
					$("#widget_setting_display_order").val("LAST");
				} else {
					$("#widget_setting_display_order").val(nextwidget_id);
				}
			}
		  </script>';

	// check if it is a RSS feed
	if (strstr(trim($_POST ['widget']),'widget_rss')){
		$rss = $_SESSION['cached_rss'][str_replace('widget_rss','',$_POST ['widget'])];
		$widget = 'rss';
	} else {
		$widget = str_replace ( 'widget_', '', $_POST ['widget'] );
	}

	// include the widgetfunctions of the particular widget
	include_once api_get_path ( SYS_PATH ) . 'main/widgets/' . $widget . '/widgetfunctions.php';

	// the widget specific form elements
	echo call_user_func ( $widget . '_settings_form', $realwidgetname);

	// the submit button
	//echo '<input type="submit" name="SaveWidgetSettings" value="'.get_lang('SaveSettings').'" id="SaveWidgetSettings" />';


	// closing the form
	echo '</form>';

	// making it an ajax form
	echo '<script type="text/javascript">';
	// we already add the save button and a feedback message to the button pane (but do not display it yet)
	echo '$(".ui-dialog-buttonpane").prepend("<button class=\"ui-state-default ui-corner-all\" type=\"button\" name=\"SaveWidgetSettings\" id=\"SaveWidgetSettings\" style=\"display:none;\">' . get_lang ( 'SaveSettings' ) . '</button>");';
	echo '$(".ui-dialog-buttonpane").append("<div class=\"ui-widget\" style=\"width: 75%\">', '<div class=\"ui-corner-all dialogfeedback ui-state-highlight\" name=\"dialogfeedback\" id=\"dialogfeedback\" style=\"display:none; line-height:1.4em; font-size: 100%; margin:5px 5px 3px 0px; padding:0.2em 0.6em 0.3em;\">', get_lang ( 'ABC' ), '</div>', '</div>");';

	// displaying the save button when something in the form is changed and hiding the OK button
	echo '$("#widget_settings").live("click", function() {
		// hiding the OK button
		$(".ui-dialog-buttonpane button").hide();
		// showing the SaveSettings button
		$("#SaveWidgetSettings").show(); // attr("style","display:block;");
    	});';
	// saving the widget settings
	echo '$("#SaveWidgetSettings").live("click", function() {
    		// changing the button to indicate that we are saving it
			$("#SaveWidgetSettings").html("' . get_lang ( 'SavingDotted' ) . '");
			// the actual saving
			var options = {
		    	success:    function() {
		    		// display a feedback message in the dialog for 5 seconds, then remove it
        			$(".dialogfeedback").html("' . get_lang ( 'WidgetSettingsAreSaved' ) . '").show();
        			// hide it again
					$(".dialogfeedback").animate({
						opacity: 1
				  	}, 5000).animate({
						opacity: 0
				  	}, 1500);
				  	// we set the text of the button again to SaveSettings
				  	$("#SaveWidgetSettings").html("' . get_lang ( 'SaveSettings' ) . '");
					// we show all the buttons again (the OK button was hidden)
					$(".ui-dialog-buttonpane button").show();
					// but we hide the save button again after successfully saving the widget settings
				  	$("#SaveWidgetSettings").hide();

    			}
			};
			$("#widget_settings").ajaxSubmit(options);

    	});';
	echo '';
	echo '</script>';
}

function savewidgetsettings() {
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// before continuing we cache all the rss feeds (if any)
	cache_rss();

	$widget = $_POST ['widget'];

	// check if it is a RSS feed
	if (strstr(trim($widget),'widget_rss')){
		$widget = $_SESSION['cached_rss'][str_replace('widget_rss','',$widget)];
	}

	// show title or not: first we delete the setting and then we save the new setting
	$sql = "DELETE FROM $table_setting
				WHERE variable='" . Database::escape_string ( $widget ) . "'
				AND subkey='hide_title'
				AND subcategory = '".Database::escape_string($script)."'";
	//echo $sql;
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	$sql = "INSERT INTO $table_setting (variable, subkey, category,subcategory,$value_field) VALUES(
					'" . Database::escape_string ( $widget ) . "',
					'hide_title',
					'widget',
					'".Database::escape_string($script)."',
					'" . Database::escape_string ( $_POST ['widget_setting_hide_title'] ) . "'
					)";
	//echo $sql;
	$res = Database::query($sql, __FILE__, __LINE__);


	// changing the location and display order
	// 1. remove from the old location
	$sql = "DELETE FROM $table_setting WHERE variable LIKE 'location_' AND subkey = '" . Database::escape_string ( str_replace ( 'widget_', '', $widget ) ) . "' AND subcategory = '".Database::escape_string($script)."'";
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	//r($sql);
	// 2. add to the new location. To do so we loop trough the new location and if the $_POST['widget_setting_display_order'] is the one we found in the database then we
	// give the widget we are currently setting the sorting order of the matched one and increase the position of all the items that follow
	$sql = "SELECT * FROM $table_setting WHERE variable =  '" . Database::escape_string ( $_POST ['widget_setting_location'] ) . "' AND subcategory = '".Database::escape_string($script)."' ORDER BY $value_field ASC";
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $res ) ) {
		// if it has to be in the first position
		if ($_POST ['widget_setting_display_order'] == 'FIRST' and ! $found) {
			$sql_insert = "INSERT INTO $table_setting (variable, subkey, category, subcategory, $value_field) VALUES (
					'" . Database::escape_string ( $_POST ['widget_setting_location'] ) . "',
					'" . Database::escape_string ( str_replace ( 'widget_', '', $widget ) ) . "',
					'widget',
					'".Database::escape_string($script)."',
					'1'
					)";
			$res_insert = Database::query ( $sql_insert, __FILE__, __LINE__ );
			$found = true;
		}

		// we have found the widget that should be placed after the widget we are configuring
		if ($row ['subkey'] == str_replace ( 'widget_', '', $_POST ['widget_setting_display_order'] )) {
			$found = true;
			$sql_insert = "INSERT INTO $table_setting (variable, subkey, category, subcategory, $value_field) VALUES (
					'" . Database::escape_string ( $_POST ['widget_setting_location'] ) . "',
					'" . Database::escape_string ( str_replace ( 'widget_', '', $widget ) ) . "',
					'widget',
					'".Database::escape_string($script)."',
					'" . Database::escape_string ( $row [$value_field] ) . "'
					)";
			$res_insert = Database::query ( $sql_insert, __FILE__, __LINE__ );
			$found = true;
		}

		// the new widget has been inserted which means that we have to move all the next widgets one place up
		if ($found) {
			$sql_update = "UPDATE $table_setting SET $value_field='" . Database::escape_string ( $row [$value_field] + 1 ) . "' WHERE id = '" . $row ['id'] . "'";
			$res_update = Database::query ( $sql_update, __FILE__, __LINE__ );
			//r($sql_update);
		}

		$tmp_last = $row [$value_field];
	}

	// if the widget has to be in the last position
	if (($_POST ['widget_setting_display_order'] == 'LAST' and ! $found) OR !$found) {
		$sql_insert = "INSERT INTO $table_setting (variable, subkey, category, subcategory, $value_field) VALUES (
				'" . Database::escape_string ( $_POST ['widget_setting_location'] ) . "',
				'" . Database::escape_string ( str_replace ( 'widget_', '', $widget ) ) . "',
				'widget',
				'".Database::escape_string($script)."',
				'" . Database::escape_string ( $tmp_last + 1 ) . "'
				)";
		$res_insert = Database::query ( $sql_insert, __FILE__, __LINE__ );
	}

	// first we delete all the widget specific settings
	$sql_delete = "DELETE FROM $table_setting WHERE variable = '" . Database::escape_string ( str_replace ( 'widget_', '', $widget ) ) . "' AND category='widget' AND subcategory = '".Database::escape_string($script)."'";
	$res_delete = Database::query ( $sql_delete, __FILE__, __LINE__ );
	echo $sql_delete;

	// saving all the specific widget settings (name starts with widget_setting_) but we have to filter out the widget_setting_display_order and the widget_setting_location
	// (because these widget settings are treated differently in the code above)
	foreach ( $_POST as $key => $value ) {
		// see examples on http://www.php.net/manual/en/function.strpos.php
		$iswidgetsetting = strpos ( $key, 'widget_setting_' );
		if ($iswidgetsetting !== false and ! in_array ( $key, array ('widget_setting_display_order', 'widget_setting_location' ) )) {
			$sql_insert = "INSERT INTO $table_setting (variable, subkey, category, subcategory, $value_field) VALUES (
					'" . Database::escape_string ( str_replace ( 'widget_', '', $widget ) ) . "',
					'" . Database::escape_string ( str_replace ( 'widget_setting_', '', $key ) ) . "',
					'widget',
					'".Database::escape_string($script)."',
					'" . Database::escape_string ( $value ) . "'
					)";
			$res_insert = Database::query ( $sql_insert, __FILE__, __LINE__ );
			echo $sql_insert;
			//r($sql_insert);
		}
	}

	// include the widgetfunctions of the particular widget
	include_once api_get_path ( SYS_PATH ) . 'main/widgets/' . str_replace ( 'widget_', '', $_POST ['widget'] ) . '/widgetfunctions.php';
	echo str_replace ( 'widget_', '', $widget ) . '_settings_save';

	// if the widget is a RSS we have to call widget_rss_settings_save
	if (strstr(trim($_POST['widget']),'widget_rss')){
		$function = 'rss';
	} else {
		$function = str_replace ( 'widget_', '', $widget);
	}
	echo call_user_func ( $function . '_settings_save', $_POST );
}


function addrsswidget(){
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	echo '<form id="widget_settings" method="post" action="' . api_get_path ( WEB_PATH ) . 'main/inc/lib/widgets.lib.php?action=saverss">';

	// The address of the RSS feed
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">' . get_lang ( 'RSSFeed' ) . '</div>';
	echo '<input name="RSSFeed" id="RSSFeed" />';
	echo '</div>';

	// The number of items to display
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">' . get_lang ( 'NumberOfItemsToDisplay' ) . '</div>';
	echo '<input name="RSSFeeditems" id="RSSFeeditems" />';
	echo '</div>';

	// the location
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">' . get_lang ( 'Location' ) . '</div>';
	echo '<select id="widget_setting_location" name="widget_setting_location" onchange="load_widgets_in_location_for_widgetsettings(\'\',\'widget_setting_display_order\');">
			<option value="disable">' . get_lang ( 'Disabled' ) . '</option>';
	echo '</select>';
	echo '</div>';
	echo '<script type="text/javascript">
			// get all the locations of the current layout
			var arrayLocations = $(".location");

			// populate the #widget_setting_location dropdown list with all the locations
			$.each(arrayLocations, function() {
				$("#widget_setting_location").append($(\'<option class="selectlocation"></option>\').val($(this).attr("id")).html($(this).attr("title")));
			});

			// if the widget is already activated then we have to make sure that the dropdown displays the correct location for that widget
			//$.get("' . api_get_path ( WEB_PATH ) . 'main/inc/lib/widgets.lib.php", {action:"get_widget_location",widget:"' . str_replace ( 'widget_', '', $_POST ['widget'] ) . '"},
			//	function(activeinlocation) {
			//		$("#widget_setting_location").val(activeinlocation);
			//	});
		  </script>';


	// making it an ajax form
	echo '<script type="text/javascript">';
	// we already add the save button and a feedback message to the button pane (but do not display it yet)
	echo '$(".ui-dialog-buttonpane").prepend("<button class=\"ui-state-default ui-corner-all\" type=\"button\" name=\"SaveWidgetSettings\" id=\"SaveWidgetSettings\" style=\"display:none;\">' . get_lang ( 'SaveRSSAsWidget' ) . '</button>");';
	echo '$(".ui-dialog-buttonpane").append("<div class=\"ui-widget\" style=\"width: 75%\">', '<div class=\"ui-corner-all dialogfeedback ui-state-highlight\" name=\"dialogfeedback\" id=\"dialogfeedback\" style=\"display:none; line-height:1.4em; font-size: 100%; margin:5px 5px 3px 0px; padding:0.2em 0.6em 0.3em;\">', get_lang ( 'ABC' ), '</div>', '</div>");';

	// displaying the save button when something in the form is changed and hiding the OK button
	echo '$("#widget_settings").live("click", function() {
		// hiding the OK button
		$(".ui-dialog-buttonpane button").hide();
		// showing the SaveSettings button
		$("#SaveWidgetSettings").show(); // attr("style","display:block;");
    	});';
	// saving the widget settings
	echo '$("#SaveWidgetSettings").live("click", function() {
    		// changing the button to indicate that we are saving it
			$("#SaveWidgetSettings").html("' . get_lang ( 'SavingDotted' ) . '");
			// the actual saving
			var options = {
		    	success:    function() {
		    		// display a feedback message in the dialog for 5 seconds, then remove it
        			$(".dialogfeedback").html("' . get_lang ( 'WidgetSettingsAreSaved' ) . '").show();
        			// hide it again
					$(".dialogfeedback").animate({
						opacity: 1
				  	}, 5000).animate({
						opacity: 0
				  	}, 1500);
				  	// we set the text of the button again to SaveSettings
				  	$("#SaveWidgetSettings").html("' . get_lang ( 'SaveSettings' ) . '");
					// we show all the buttons again (the OK button was hidden)
					$(".ui-dialog-buttonpane button").show();
					// but we hide the save button again after successfully saving the widget settings
				  	$("#SaveWidgetSettings").hide();

    			}
			};
			$("#widget_settings").ajaxSubmit(options);

    	});';
	echo '';
	echo '</script>';
}

function saverss(){
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			echo '1';
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			echo '2';
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// first we get the maximum widget position in the target location (because newly added widgets will be added at the end)
	$sql_max_position = "SELECT MAX(value) AS max FROM $table_setting WHERE variable = '" . Database::escape_string ( $_POST['widget_setting_location'] ) . "' AND subcategory = '".Database::escape_string($script)."'";
	$result_max_position = Database::query ( $sql_max_position, __FILE__, __LINE__ );
	$max_position = Database::fetch_array ( $result_max_position, 'ASSOC' );
	$max_position = $max_position ['max'];

	// now we save the RSS feed as a widget
	$sql = "INSERT INTO $table_setting (variable, subkey, category, subcategory, $value_field) VALUES ('".Database::escape_string($_POST['widget_setting_location'])."','".Database::escape_string($_POST['RSSFeed'])."','widget', '".Database::escape_string($script)."', '".Database::escape_string($max_position + 1)."')";
	$result = Database::query ( $sql, __FILE__, __LINE__ );
	$row = Database::fetch_array ( $result, 'ASSOC' );

	// we now save the RSS settings (number of items to display)
	if (!is_numeric($_POST['RSSFeeditems'])){
		$number_items = '10';
	} else {
		$number_items = (int)$_POST['RSSFeeditems'];
	}
	$sql = "INSERT INTO $table_setting (variable, subkey, category, subcategory, $value_field) VALUES ('".Database::escape_string($_POST['RSSFeed'])."','number_of_items','widget', '".Database::escape_string($script)."',  '".Database::escape_string($number_items)."')";
	$result = Database::query ( $sql, __FILE__, __LINE__ );
	$row = Database::fetch_array ( $result, 'ASSOC' );
}

/**
 * This function displays the widget list that is displayed when you click "add widget" in the configuration widget
 *
 */
function addwidgets() {
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// getting all the active widgets. We cannot use api_get_course_setting because this function only gets one (the first) entry with variable = the parameter that you pass to the api_get_course_setting function
	// variable is 'widgetlocation' the subkey is the actial location and the value is the widget that is activated in the location (subkey)
	$sql = "SELECT subkey FROM $table_setting WHERE variable LIKE 'location_' AND subcategory = '".Database::escape_string($script)."'";
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $res ) ) {
		$active_widgets [] = $row ['subkey'];
	}

	// getting all the possible widgets and displaying them.
	// we also include their widgetfunctions.php file that contains all the necessary functions for this widget.
	// if the widget is active we add a class "active" to the div
	echo '<div id="widgetlist" style="height:380px; overflow: auto;">';
	$widget_dir = api_get_path ( SYS_PATH ) . 'main/widgets/';
	$handle = opendir ( $widget_dir );
	while ( false !== ($file = readdir ( $handle )) ) {
		if ($file != "." && $file != ".." && $file != "rss" && file_exists ( $widget_dir . $file . '/widgetfunctions.php' )) {

			// include the file with the widget specific functions
			// if this would seem not performant enough we could remove this include
			// and replace call_user_func($file.'_get_title') with $file instead so that the foldername is used instead of the widget title
			include_once ($widget_dir . $file . '/widgetfunctions.php');

			if (in_array ( $file, $active_widgets )) {
				$additionalclass = ' active ';
			} else {
				$additionalclass = '';
			}
			// we only display the widgets that can be used in the course (if we are inside a course)
			// or on the platform (if we are outside a course)
			$scope = call_user_func ( $file . '_get_scope' );
			if ((!empty($_course) AND is_array($_course) AND  in_array('course',$scope)) OR (empty($_course) OR !is_array($_course) AND in_array('platform',$scope))){
				echo '<div class="widgetconfigurationitem ' . $additionalclass . '" id="' . $file . '">
            				'.Display::return_icon('widget-info.gif','',array('class'=>'widgetconfigurationiteminfo')).'
            				<a href="#" class="widgetconfigurationitembutton">
							<span class="widgettitle">' . call_user_func ( $file . '_get_title', $row ['subkey'],true) . '</span>
						</a>

					</div>' . "\n";
			}
		}
	}

	echo '</div>';

	echo '<div id="widgetconfigurationinfo">' . get_lang ( 'WidgetInformation' ) . '</div>';

}

function displayactivationform() {
	echo 'hier moet je ee locatie kunnen kiezen waarna je de widget toevoegt';
	echo '<select id="widgetactivateinlocation2" name="widgetactivateinlocation2"><option value="disable">' . get_lang ( 'Disabled' ) . '</option></select>';
	echo '<div id="test">*</div>';
}

function get_widget_location() {
	global $_course;

	$widget = $_GET ['widget'];

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// cache rss
	cache_rss();

	if (strstr(trim($widget),'rss') AND $widget <> 'tabbedrss'){
		$widget = $_SESSION['cached_rss'][str_replace('rss','',$_GET ['widget'])];
	}

	// getting the loction of this active widget. variable is 'widgetlocation' the subkey is the actial location and the value is the widget that is activated in the location (subkey)
	// so in this case we want to know where a certain widget is activated so we look for the subkey
	$sql = "SELECT * FROM $table_setting WHERE subkey='" . Database::escape_string ( $widget ) . "' AND subcategory = '".Database::escape_string($script)."'";
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	$row = Database::fetch_array ( $res, 'ASSOC' );
	// we return 'disabled' when the widget is not active
	if (empty ( $row ['variable'] )) {
		echo 'disable';
	}
	echo $row ['variable'];
}

/**
 * This functions saves a widget in a certain location.
 * This function is called after the "add widgets" link is clicked
 *
 */
function save_widget_location() {
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// the widget dir
	$widget_dir = api_get_path ( SYS_PATH ) . 'main/widgets/';

	// first we need to check if the widget is already saved in a certain location.
	$sql = "SELECT * FROM $table_setting WHERE variable LIKE 'location_' AND subkey='" . Database::escape_string ( $_GET ['widget'] ) . "' AND subcategory = '".Database::escape_string($script)."'";
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	$old_widget_info = Database::fetch_array ( $res, 'ASSOC' );

	// secondly we get the maximum widget position in the target location (because newly added widgets will be added at the end)
	$sql_max_position = "SELECT MAX(value) AS max FROM $table_setting WHERE variable = '" . Database::escape_string ( $_GET ['location'] ) . "' AND subcategory = '".Database::escape_string($script)."'";
	$result_max_position = Database::query ( $sql_max_position, __FILE__, __LINE__ );
	$max_position = Database::fetch_array ( $result_max_position, 'ASSOC' );
	$max_position = $max_position ['max'];

	// if it is already saved in a location we update the location, else we add a new course setting
	// we also change the widget position for the old and the new location
	if (Database::num_rows ( $res ) > 0) {
		// updating the location by moving the activated widget from location X to location Y
		$sql = "UPDATE $table_setting
					SET variable = '" . Database::escape_string ( $_GET ['location'] ) . "',
					value = '" . Database::escape_string ( ($max_position + 1) ) . "'
					WHERE variable = '" . Database::escape_string ( $old_widget_info ['variable'] ) . "' AND subkey='" . Database::escape_string ( $_GET ['widget'] ) . "' AND subcategory = '".Database::escape_string($script)."'";
	} else {
		// adding the widget to the location
		$sql = "INSERT INTO $table_setting (variable, subkey, category, subcategory, $value_field) VALUES
				('" . Database::escape_string ( $_GET ['location'] ) . "','" . Database::escape_string ( $_GET ['widget'] ) . "','widget', '".Database::escape_string($script)."', '" . Database::escape_string ( ($max_position + 1) ) . "')";
	}
	echo $sql;


	// execute or remove the setting
	if ($_GET ['location'] == 'disable') {
		// delete the widget as active in the location
		$sql = "DELETE FROM $table_setting
							WHERE variable = '" . Database::escape_string ( $old_widget_info ['variable'] ) . "'
							AND subkey = '" . Database::escape_string ( $_GET ['widget'] ) . "'
							AND subcategory = '".Database::escape_string($script)."'";
	}

	$res = Database::query ( $sql, __FILE__, __LINE__ );

	// call the installation function of the widget (if any)
	// include the widget function
	include_once api_get_path ( SYS_PATH ) . 'main/widgets/' . $_GET ['widget'] . '/widgetfunctions.php';
	call_user_func ( $_GET ['widget'] . '_install' );
}

function r($var) {
	echo '<pre>';
	print_r ( $var );
	echo '</pre>';
}

function load_widgets($location) {
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table ( TABLE_COURSE_SETTING );
		$value_field = 'value';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$value_field = 'selected_value';
		$script = $_SESSION['widget_script'];
	}

	// the widget dir
	$widget_dir = api_get_path ( SYS_PATH ) . 'main/widgets/';

	if (!empty($_course) AND is_array($_course)){
		if (api_is_allowed_to_edit() or api_is_platform_admin()){
			$sql = "SELECT * FROM $table_setting WHERE variable='" . Database::escape_string ( $location ) . "' ORDER BY $value_field ASC";
		} else {
			$sql = "SELECT * FROM $table_setting WHERE variable='" . Database::escape_string ( $location ) . "' AND subkey <> 'configuration' ORDER BY $value_field ASC";
		}
	} else {
		if (api_is_platform_admin()){
			$sql = "SELECT * FROM $table_setting WHERE variable='" . Database::escape_string ( $location ) . "' AND subcategory = '".Database::escape_string($script)."' ORDER BY $value_field ASC";
		} else {
			$sql = "SELECT * FROM $table_setting WHERE variable='" . Database::escape_string ( $location ) . "' AND subkey <> 'configuration' AND subcategory = '".Database::escape_string($script)."' ORDER BY $value_field ASC";
		}
	}
	$res = Database::query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
		$id = $row['subkey'];
		// check if it is a RSS feed
		if (strstr(trim($row ['subkey']),'http://')){
			$row ['content'] = trim($row ['subkey']);
			$id = 'rss'.md5(trim($row['subkey']));
			$row ['subkey'] = 'rss';
		}


		// include the file with the widget specific functions
		// if this would seem not performant enough we could remove this include
		// and replace call_user_func($file.'_get_title') with $file instead so that the foldername is used instead of the widget title
		include_once ($widget_dir . $row ['subkey'] . '/widgetfunctions.php');

		// display the widget
		echo '	<div id="widget_' . $id . '" class="portlet">
					<div class="portlet-header"><span class="widgettitle">' . call_user_func ( $row ['subkey'] . '_get_title', $row['content'] ) . '</span></div>
					<div class="portlet-content">' . Display::return_icon ( 'ajax-loader.gif', '', array ('style' => 'text-align: center;' ) ) . '</div>';

		// load the widget content (note: it is important to have this <script type="text/javascript"> tags inside the div.portlet otherwise the up/down icons won't work anymore)
		if (api_get_setting ( $row ['subkey'], 'hide_title' ) == '1' AND api_get_setting ('widget_hidden_title_behaviour')=='showtoggle') {
			$extra_style = "";
		} else {
			$extra_style = "display:none;";
		}
		?>
<script type="text/javascript">
		$("#widget_<?php
		echo $id;
		?> .portlet-content").load('<?php
		echo api_get_path ( WEB_PATH );
		?>main/widgets/<?php
		echo $row ['subkey'];
		?>/widgetfunctions.php',{'action':'get_widget_content', 'content':'<?php echo $row ['content']; ?>'}, function(){
			$("#widget_<?php
		echo $row ['subkey'];
		?> .portlet-content").prepend('<span style="float:right; <?php
		echo $extra_style;
		?>" class="toggleheader ui-icon ui-icon-triangle-2-n-s"></span>');
		});
		</script>

<?php
		// closing the portlet div
		echo '	</div>';

		// include the widget function
		include_once api_get_path ( SYS_PATH ) . 'main/widgets/' . $row ['subkey'] . '/widgetfunctions.php';
	}
}

/**
 * This function loads the configuration widget in the place that is defined in the layout/customhomepageX.php file
 * before it loads the widget in that given location it checks the database to see if the widget has been moved to a different place or not.
 * If it has been moved to a different place then it won't be loaded in the place that is defined in the layout/customhomepageX.php file
 * but it will be loaded in the place that has been defined by the course admin in the database.
 */
function load_configuration_widget() {
	global $_course;

	// acces control
	if (!empty($_course) AND is_array($_course)){
		if(!api_is_allowed_to_edit())
		{
			return false;
		}
	} else {
		if (!api_is_platform_admin()){
			return false;
		}
	}

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table(TABLE_COURSE_SETTING);
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$script = $_SESSION['widget_script'];
	}

	$sql = "SELECT * FROM $table_setting WHERE variable LIKE 'location%' AND subkey='configuration' AND subcategory = '".Database::escape_string($script)."'";

	$result = Database::query ( $sql, __FILE__, __LINE__ );
	if (Database::num_rows ( $result ) > 0) {
		return true;
	} else {

		if (!empty($_course) AND is_array($_course)){

			echo '
			<div  id="widget_configuration" class="portlet">
				<div class="portlet-header"><span class="widgettitle">configuration</span></div>
				<div class="portlet-content">
					<ul id="configuration" style="list-style: none;margin: 0;padding: 0;">
						<li><a href="'.api_get_path(WEB_PATH).'main/inc/lib/widgets.lib.php?action=addwidgets" title="'.get_lang('ManageWidgets').'" class="dialoglink">'.Display::return_icon('settings.gif','',array('align'=>'middle')).'  '.get_lang('ManageWidgets').'</a></li>
						<li><a href="'.api_get_path(WEB_PATH).'main/inc/lib/widgets.lib.php?action=addrsswidget" title="'.get_lang('AddRSSAsWidget').'" class="dialoglink">'.Display::return_icon('links.gif','',array('align'=>'middle')).'  '.get_lang('AddRSSAsWidget').'</a></li>
						<li><a href="'.api_get_path(WEB_CODE_PATH).'blog/blog_admin.php">'.Display::return_icon('blog_admin.gif','',array('align'=>'middle')).' '.get_lang('Blog_management').'</a></li>
						<li><a href="'.api_get_path(WEB_CODE_PATH).'tracking/courseLog.php">'.Display::return_icon('statistics.png','',array('align'=>'middle')).' '.get_lang('Tracking').'</a></li>
						<li><a href="'.api_get_path(WEB_CODE_PATH).'course_info/infocours.php">'.Display::return_icon('reference.gif','',array('align'=>'middle')).' '.get_lang('Course_setting').'</a></li>
						<li><a href="'.api_get_path(WEB_CODE_PATH).'course_info/maintenance.php">'.Display::return_icon('backup.gif','',array('align'=>'middle')).' '.get_lang('Course_maintenance').'</a></li>
					</ul>
				</div>
			</div>';
		} else {
			echo '
			<div  id="widget_configuration" class="portlet">
				<div class="portlet-header"><span class="widgettitle">configuration</span></div>
				<div class="portlet-content">
					<ul id="configuration" style="list-style: none;margin: 0;padding: 0;">
						<li><a href="'.api_get_path(WEB_PATH).'main/inc/lib/widgets.lib.php?action=addwidgets" title="'.get_lang('ManageWidgets').'" class="dialoglink">'.Display::return_icon('settings.gif','',array('align'=>'middle')).'  '.get_lang('ManageWidgets').'</a></li>
						<li><a href="'.api_get_path(WEB_PATH).'main/inc/lib/widgets.lib.php?action=addrsswidget" title="'.get_lang('AddRSSAsWidget').'" class="dialoglink">'.Display::return_icon('links.gif','',array('align'=>'middle')).'  '.get_lang('AddRSSAsWidget').'</a></li>
					</ul>
				</div>
			</div>';
		}
	}
}

function load_user_widget(){
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table(TABLE_COURSE_SETTING);
		$script='';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$script = $_SESSION['widget_script'];
	}

	// check if the user widget has been enabled elsewhere
	$sql = "SELECT * FROM $table_setting WHERE variable LIKE 'location%' AND subkey='user' AND subcategory = '".Database::escape_string($script)."'";
	$result = Database::query ( $sql, __FILE__, __LINE__ );

	// if the user widget has been enabled elsewhere and the user is logged in then we will load it in the location it has been enabled by the platform admin
	// (it will be the user picture in this case)
	if (Database::num_rows ( $result ) > 0) {
		return true;
	} else {
	// if the user is not logged in we display the user widget in the default location (it will be the login screen in this case)
		echo '
			<div  id="widget_user" class="portlet">
				<div class="portlet-header"><span class="widgettitle">'.get_lang('UserWidgetTitle').'</span></div>
				<div class="portlet-content">' . Display::return_icon ( 'ajax-loader.gif', '', array ('style' => 'text-align: center;' ) ) . '</div>
			</div>';
	?>
		<script type="text/javascript">
		$("#widget_user .portlet-content").load('<?php echo api_get_path ( WEB_PATH );?>main/widgets/user/widgetfunctions.php',{'action':'get_widget_content', 'content':''}, function(){
		});
		</script>
	<?php
	}
}

function cache_rss(){
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_setting = Database::get_course_table(TABLE_COURSE_SETTING);
		$script='';
	} else {
		$table_setting = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
		$script = $_SESSION['widget_script'];
	}

	// before continuing we cache all the rss feeds (if any)
	$sql = "SELECT * FROM $table_setting WHERE variable LIKE 'location%'";
	$result = api_sql_query ( $sql );
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		if (strstr(trim($row ['subkey']),'http://')){
			if (!in_array(md5(trim($row['subkey'])),$_SESSION['cached_rss'])){
				$_SESSION['cached_rss'][md5(trim($row['subkey']))] = $row['subkey'];
			}
		}
	}
}
