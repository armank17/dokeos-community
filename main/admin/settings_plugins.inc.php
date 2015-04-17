<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Patrick Cool
* @since Dokeos 2.0
* @package dokeos.admin
*/
?>
<style type="text/css">
	.sorthelper { border: 1px dashed #CCC; background-color: #EFEFEF; height: 80px;}
	ul {list-style-type: none; margin-left: 0px; padding-left 0px;}
	li {list-style-type: none; margin-left: 0px; padding-left 0px;}
	#data_table_header{background-color:#4171B5;}
	#data_table_header div{
		border: 1px solid #BDB9B8;
		font-weight:bold;
		background: #F5F9FC;
		/* Mozilla: */
		background: -moz-linear-gradient(top, #F5F9FC, #BDB9B8);
		/* Chrome, Safari:*/
		background: -webkit-gradient(linear,left top, left bottom, from(#F5F9FC), to(#BDB9B8));
		/* MSIE */
		filter: progid:DXImageTransform.Microsoft.Gradient(StartColorStr="#F5F9FC", EndColorStr="#BDB9B8", GradientType=0);
		float: left;
	}
	.plugin {border-bottom: 1px solid #CCC;padding-bottom: 10px; padding-top: 10px;};
</style>
<script type="text/javascript">
$(function() {
	// make the plugins sortable
	$("#pluginlist").sortable({
		placeholder: 'sorthelper',
		forcePlaceholderSize: 'true',
		cursor: 'move',
	        update: function() {
			var order = $('#pluginlist').sortable('serialize') + '&amp;action=savepluginorder';
			$.post('<?php echo api_get_path(WEB_CODE_PATH);?>admin/ajax.php', order, function(theResponse){
				$(theResponse).insertBefore(".normal-message");
			});
	        }
	});

});
</script>

<?php

// All the valid locations. If somewhere in the code you want to add a new plugin location then
// you have to add this location here and call api_plugin('name_of_new_location') in the code
$valid_locations=array('loginpage_main', 'loginpage_menu', 'campushomepage_main', 'campushomepage_menu', 'mycourses_main', 'mycourses_menu','header', 'footer');

// display a message to the users informing them where they can find more plugins
Display::display_normal_message(get_lang('AvailablePlugins'), false);




handle_plugins();

// display the footer
Display::display_footer();

/**
 * This function allows easy activating and inactivating of plugins
 * @todo: a similar function needs to be written to activate or inactivate additional tools.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*/
function handle_plugins()
{
	// action handling
	switch ($_GET['action']){
		case 'edit':
			display_plugin_settings_form();
	}

	// get all the plugins that are activated
	$usedplugins = get_active_plugins();

	// variable initialisation
	$userplugins = array();

	// Database table definition
	$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

	// which are the plugins that are found on the filesystem?
	$filesystem_plugins = scan_plugin_dir();

	// which are the plugins that are saved in the setting "pluginorder"
	$setting_plugins = explode(',',api_get_setting('pluginorder'));

	// we now create a list with all the plugins that are OK and display them like they are currently saved
	foreach ($setting_plugins as $key=>$setting_plugin){
		if (in_array($setting_plugin,$filesystem_plugins)){
			$possible_plugins[] = $setting_plugin;
		}
	}

	// we also have to append the plugins that are on the filesystem but are not saved in the pluginorder setting yet (maybe because they are newly added)
	foreach ($filesystem_plugins as $key=>$filesystem_plugin){
		if (!in_array($filesystem_plugin, $possible_plugins)){
			$possible_plugins[] = $filesystem_plugin;
		}
	}

	/* 	for each of the possible plugin dirs we check if a file plugin.php (that contains all the needed information about this plugin)
	 	can be found in the dir.
		this plugin.php file looks like
		$plugin_info['title']='The title of the plugin'; //
		$plugin_info['comment']="Some comment about the plugin";
		$plugin_info['location']=array("loginpage_menu", "campushomepage_menu","banner"); // the possible locations where the plugins can be used
		$plugin_info['version']='0.1 alpha'; // The version number of the plugin
		$plugin_info['author']='Patrick Cool'; // The author of the plugin
	*/

	echo '<ul><li>';
	echo "\t<div id=\"data_table_header\">\n";
		echo "\t\t<div style=\"width: 10%;\">\n";
		echo get_lang('DisplayOrder');
		echo "\t\t</div>\n";

		echo "\t\t<div style=\"width: 50%;\">\n";
		echo get_lang('Plugin');
		echo "\t\t</div>\n";

		echo "\t\t<div style=\"width: 30%;\">\n";
		echo get_lang('EnabledLocations');
		echo "\t\t</div>\n";

		echo "\t\t<div style=\"width: 10%;\">\n";
		echo get_lang('Configure');
		echo "\t\t</div>\n";
	echo "\t</div>\n";
	echo '<div style="clear: both;"></div>';
	echo '</li></ul>';


	echo '<ul id="pluginlist">';
	/* display every plugin as a row in the table */
	foreach ($possible_plugins as $testplugin)
	{
		if ($testplugin == "search") { continue; }
		// the plugin information file. This file stores all the information of the plugin
		// and a possible plugin is only a real plugin if this file exists
		$plugin_info_file = api_get_path(SYS_PLUGIN_PATH).$testplugin.'/plugin.php';

		if (file_exists($plugin_info_file))
		{
			// variable initialisation
			$plugin_info = array();

			// include the plugin information file
			include ($plugin_info_file);

			// starting the row of the plugin
			echo "\t<li id=\"plugin_$testplugin\" class=\"plugin\"><div>\n";

			// First column: drag handles
			echo "\t\t<div style=\"float:left; width: 10%;\">\n";
			Display::display_icon('draggable.png','',array('class'=>'sortable_handle'));
			echo "\t\t</div>\n";

			// Second column: the plugin information
			echo "\t\t<div style=\"float:left; width: 50%;\">\n";
			foreach ($plugin_info as $key => $value)
			{
				if ($key <> 'location')
				{
					if ($key == 'title')
					{
						$value = '<strong>'.$value.'</strong>';
					}
					echo $value.'<br />';
				}
			}
			// if there is a readme.txt file then we provide a link to it
			if (file_exists(api_get_path(SYS_PLUGIN_PATH).$testplugin.'/readme.txt'))
			{
				echo "<a href='".api_get_path(WEB_PLUGIN_PATH).$testplugin."/readme.txt'>readme.txt</a>";
			}
			echo "\t\t</div>\n";

			// Third column: display where the plugin is enabled
			// we loop through every location to see if the plugin is activated in that location
			echo "\t\t<div style=\"float:left; width: 20%;\">&nbsp;\n";
			foreach ($usedplugins as $location=>$plugins_in_location){
				if (in_array($testplugin, $plugins_in_location)){
					echo $location.'<br />&nbsp;';
				}
			}
			echo "\t\t</div>\n";

			// Fourth column: a link to edit the plugin
			echo "\t\t<div style=\"float:left; width: 10%;\">\n";
			echo "\t\t\t<a href=\"settings.php?category=Plugins&amp;action=edit&amp;plugin=".$testplugin."\">".Display::return_icon('pixel.gif','',array('class'=>'actionplaceholdericon actionedit'))."</a>";
			echo "\t\t</div>\n";

			if(empty($usedplugins))
			{
				$usedplugins = array();
			}
			echo "\t</div>\n";
			echo '<div style="clear: both;"></div>';
			echo '</li>';
		}
	}

	//echo '<button class="save" type="submit" name="submit_plugins">'.get_lang('EnablePlugins').'</button></form>';
}



/**
 * This function allows easy activating and inactivating of plugins
 * @todo: a similar function needs to be written to activate or inactivate additional tools.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*/
function store_plugin_settings($values)
{
	global $_configuration;

	// Database table deinition
	$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

	// Step 1 : we remove all the locations for this plugin that are already in the database
	$sql = "DELETE FROM $table_settings_current WHERE category='Plugins' AND subkey = '".$values['plugin']."'";
	api_sql_query($sql, __LINE__, __FILE__);

	// step 2: looping through all the post values we only store these which are really a valid plugin location.
	foreach ($values as $form_name => $formvalue)
	{
		$form_name_elements = explode("location_", $form_name);
		if (is_valid_plugin_location($form_name_elements[1]))
		{
			api_add_setting($values['plugin'], $form_name_elements['1'],$values['plugin'],null,'Plugins',$form_name_elements['0'],null,null,null,$_configuration['access_url'],1);
		}
	}
	//event_system(LOG_CONFIGURATION_SETTINGS_CHANGE, LOG_CONFIGURATION_SETTINGS_CATEGORY, $category, $time, $user_id);
}

/**
 * Check if the post information is really a valid plugin location.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*/
function is_valid_plugin_location($location)
{
	global $valid_locations;

	if (in_array($location, $valid_locations))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function scan_plugin_dir(){
	/* We scan the plugin directory. Each folder is a potential plugin. */
	$pluginpath = api_get_path(SYS_PLUGIN_PATH);

	$handle = @opendir($pluginpath);
	while (false !== ($file = readdir($handle)))
	{
		if ($file <> '.' AND $file <> '..' AND is_dir(api_get_path(SYS_PLUGIN_PATH).$file))
		{
			$possibleplugins[] = $file;
		}
	}
	@closedir($handle);

	return $possibleplugins;
}

function is_valid_plugin($plugin_to_test){
	// these are the valid plugins
	$validplugins = scan_plugin_dir();

	if (in_array($plugin_to_test,$validplugins)){
		return true;
	} else{
		return false;
	}
}

function display_plugin_settings_form(){
	global $valid_locations;

	// get all the plugins that are activated
	$usedplugins = get_active_plugins();

	// include the formvalidator library
	require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

	// is it a valid plugin
	if (is_valid_plugin($_GET['plugin'])){
		$testplugin = $_GET['plugin'];
	} else {
		Display::display_error_message(get_lang('ThisIsNotAValidPlugin').' '.Security::Remove_XSS($_GET['plugin']).' '.get_lang('DoesNotExistInPluginFolder'));
		return false;
	}

	// include the plugin info file
	$plugin_info_file = api_get_path(SYS_PLUGIN_PATH).$testplugin.'/plugin.php';
	if (file_exists($plugin_info_file)){
		include_once ($plugin_info_file);
	} else{
		Display::display_error_message(get_lang('ThisIsNotAValidPlugin').' '.Security::Remove_XSS($_GET['plugin']).' '.get_lang('DoesNotHaveAPluginPHPFile'));
	}

	// include the plugin function file
	$plugin_functions_file = api_get_path(SYS_PLUGIN_PATH).$testplugin.'/functions.php';
	if (file_exists($plugin_functions_file)){
		include_once ($plugin_functions_file);
	}

	// create the form
	$form = new FormValidator('configureplugin','post','settings.php?category=Plugins&amp;action=edit&plugin='.Security::Remove_XSS($_GET['plugin']));
	$form->addElement('header', '', get_lang('ConfigurePlugin').': '.$plugin_info['title']);

	// hidden element to store the plugin
	$form->addElement('hidden','plugin');
	$defaults['plugin'] = $testplugin;

	// all the locations where the plugin can be activated
	foreach ($plugin_info['location'] as $key=>$location){
		$form->addElement('checkbox', 'location_'.$location, null, get_lang($location));
	}

	// set the additional plugin settings
	if (function_exists($testplugin.'_additional_plugin_settings')){
		$form = call_user_func($testplugin.'_additional_plugin_settings',$form);
	}

	// submit button
	$form->addElement('style_submit_button', null,get_lang('SavePluginSettings'), 'class="save"');


	// set the default values
	foreach ($usedplugins as $location=>$activepluginsinlocation){
		if (in_array($testplugin,$activepluginsinlocation)){
			$defaults['location_'.$location]='checked';
		}
	}
	$form->setDefaults($defaults);

	if( $form->validate())
	{
		$values = $form->exportValues();
		store_plugin_settings($values);
		Display::display_confirmation_message(get_lang('PluginSettingsSaved'));
	} else {
		$form->display();
	}
}

/**
 * This function gets all the locations and the plugins that are active in that location
 *
 * @return array where the key is the name of the plugin and the value is an array with all the locations where the plugin is active
 * @version Dokeos 2.0
 * @since July 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function get_active_plugins(){
	// We retrieve all the active plugins.
	$result = api_get_settings('Plugins');
	foreach($result as $row)
	{
		$usedplugins[$row['variable']][] = $row['selected_value'];
	}
	return $usedplugins;
}
?>
