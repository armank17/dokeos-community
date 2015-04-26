<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* With this tool you can easily adjust non critical configuration settings.
* Non critical means that changing them will not result in a broken campus.
* @author Patrick Cool
* @author Julio Montoya - Multiple URL site
* @since Dokeos 1.6
* @package dokeos.admin
*/


// Language files that should be included
$language_file = array ('document','admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsettings';
switch ($_GET['category']) {
	case 'Security':
		$help_content = 'platformadministrationsettingssecurity';
		break;
}

// including the global Dokeos file
require_once '../inc/global.inc.php';


// acccess url id
$access_url_id = api_get_current_access_url_id();
if ($access_url_id< 0) {
    $access_url_id = 1;
}


$pro_category_settings = array('Templates', 'CAS', 'Ecommerce');
$pro_variable_settings = array('show_quizcategory', 'allow_terms_conditions', 'mindmap_converter_activated', 'calendar_export_all', 'show_catalogue');
if ((api_get_setting('enable_document_templates') !== 'true' && (isset($_GET['category']) && $_GET['category'] == 'Templates')) || 
        (api_get_setting('enable_pro_settings') !== 'true' && (isset($_GET['category']) && in_array($_GET['category'], $pro_category_settings))) ) {
    header('Location: '.api_get_path(WEB_CODE_PATH).'admin/index.php');
    exit;
}


// Including additional libraries.
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
require_once (api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
require_once (api_get_path(LIBRARY_PATH).'urlmanager.lib.php');
// additional javascript, css, ...
$htmlHeadXtra [] = '<script src="' . api_get_path ( WEB_LIBRARY_PATH ) . 'javascript/iphone-style-checkboxes.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra [] = '<script src="' . api_get_path ( WEB_LIBRARY_PATH ) . 'javascript/jquery.tools.min.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra [] = '<link rel="stylesheet" href="' . api_get_path ( WEB_LIBRARY_PATH ) . 'javascript/iphone-style-checkboxes.css" type="text/css" media="screen"  />';

// Add style in the source page
$fck_attribute['Config']['FullPage'] = true;

if (isset($_GET['category']) && $_GET['category'] == 'Templates' && isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add')) {
$htmlHeadXtra[] = '<script type="text/javascript">
  $(document).ready(function (){
    $("div.label").attr("style","width: 6%; text-align:left");
    $("div.row div.formw").attr("style","width: auto");
    $(".pull-bottom div.row div.formw").attr("style","width: 92%");
    $(".ck-loading").attr("style","margin-top: -55px;");
//    $("div.row").attr("style","width: 100%;");
//    $("div.formw").attr("style","width: 100%;");
    // Set focus
    //$("#idTitle").focus();
  });
</script>';
}


// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;
$_SESSION['this_section'] = $this_section;

// Access restrictions
api_protect_admin_script();

// Submit Stylesheets
if (isset($_POST['submit_stylesheets']))
{
	$message = store_stylesheets();
	header("Location: ".api_get_self()."?category=stylesheets");
	exit;
}
// Database Table Definitions
$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

// setting breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

// setting the name of the tool
$tool_name = get_lang('DokeosConfigSettings');

// Build the form
if (!empty($_GET['category']) && !in_array($_GET['category'], array('Plugins', 'stylesheets', 'Search')))
{
	$form = new FormValidator('settings', 'post', 'settings.php?category='.$_GET['category']);
	$renderer = & $form->defaultRenderer();
	$renderer->setHeaderTemplate('<div class="section"><div class="sectiontitle">{header}</div>'."\n");
	$renderer->setElementTemplate('<div class="sectioncomment">{label}</div>'."\n".'<div class="sectionvalue">{element}</div></div>'."\n");
	$my_category = Database::escape_string($_GET['category']);
	$sqlcountsettings = "SELECT COUNT(*) FROM $table_settings_current WHERE category='".$my_category."' AND type<>'checkbox' AND access_url = $access_url_id";
	$resultcountsettings = Database::query($sqlcountsettings, __FILE__, __LINE__);
	$countsetting = Database::fetch_array($resultcountsettings);

	if ($_configuration['access_url']==1){
		$settings = api_get_settings($my_category,'group',$_configuration['access_url']);
	} else {
		$url_info = api_get_access_url($_configuration['access_url']);
		if ($url_info['active']==1)	{
			//the default settings of Dokeos
			$settings = api_get_settings($my_category,'group',1,0);
			//the settings that are changeable from a particular site
			$settings_by_access = api_get_settings($my_category,'group',$_configuration['access_url'],1);
			//echo '<pre>';
			//print_r($settings_by_access);
			$settings_by_access_list=array();
			foreach($settings_by_access as $row) {
                                if ($row['variable'] == 'installation_date' ) { continue; }

				if (empty($row['variable']))
					$row['variable']=0;
				if (empty($row['subkey']))
					$row['subkey']=0;
				if (empty($row['category']))
					$row['category']=0;
				// one more validation if is changeable
				if ($row['access_url_changeable']==1)
					$settings_by_access_list[ $row['variable'] ] [ $row['subkey'] ]	[ $row['category'] ]  = $row;
				else
					$settings_by_access_list[ $row['variable'] ] [ $row['subkey'] ]	[ $row['category'] ]  = array();
			}
		}
	}

	$default_values = array();
        $url_object = new UrlManager();
        $user_id = $_user['user_id'];
        $is_superadmin = $url_object->is_superadmin($user_id);
        $allow_superadmin = $url_object->allow_superadmin();
	$settings_to_ignore = array('import_calendar');
        $hidden_variables = array(
            'installation_date', 
            'display_mini_month_calendar', 
            'display_upcoming_events', 
            'number_of_upcoming_events', 
            'students_download_folders', 
            'allow_user_edit_agenda', 
            'number_of_announcements', 
            'calendar_export_all', 
            'display_context_help',
            'allow_reservation',            
            'groupscenariofield',
            'show_navigation_menu',
            'allow_terms_conditions',
            'show_toolshortcuts'
        );
	foreach($settings as $row) {
                // Set enable pro settings by default, so you could see all tools in course home
                api_set_setting('enable_pro_settings', 'true');
		if (
                    in_array($row['variable'], $hidden_variables) || 
                    ($row['variable'] =='search_enabled' && $_GET['category'] <> 'PRO')  || 
                    ($rowkeys['variable'] == 'course_create_active_tools' && $rowkeys['subkey'] == 'enable_search') || 
                    ($rowkeys['variable'] == 'show_tabs' && $rowkeys['subkey'] == 'search') ||
                    ($row['variable'] == 'enable_pro_settings')
                    || ($row['variable'] =='show_tabs')
                    || ($row['variable'] =='show_quizcategory')
                ) {
			continue;
		}
               
                if (api_get_setting('enable_pro_settings') !== 'true' && in_array($row['variable'], $pro_variable_settings)) {
                    continue;
                }
                
//                if($row['variable']=="allow_terms_conditions")
//					continue;
                
		$anchor_name = $row['variable'].(!empty($row['subkey']) ? '_'.$row['subkey'] : '');
		$form->addElement('html',"\n<a name=\"$anchor_name\"></a>\n");

		// adding intermediate save settings buttons
		($countsetting['0']%10) < 5 ?$b=$countsetting['0']-10:$b=$countsetting['0'];
		if ($i % 11 == 0 and $i<$b){
			if ($_GET['category'] <> "Languages"){
				$form->addElement('html','<div>'); // only opening div because the closing </div> is added to the template and the opening <div> is not displayed
				$form->addElement('style_submit_button', ' ',get_lang('SaveSettings'), 'class="save" style="margin-bottom:10px;"');
			}
		}
		$i++;

		// the setting delegation (= a platform setting that can be overridden inside a course). Only possible when scope is not -1
		if ($row['scope'] != '-1' AND !is_null($row['scope'])) {
			if ($row['scope'] == '1') {
				$checked = 'checked="checked"';
			}else{
				$checked = '';
			}
			$form->addElement ( 'html', '<div style=" float: right; margin-bottom: 5px;   margin-right: 7px;   margin-top: 5px;" id="coursesettingdelegationdiv_' . $row ['variable'] . '" class="coursesettingdelegationdiv" title="<sub>'.get_lang('ClickToEnableOrDisable').'</sub>">
                                                                                        <span class="feedback" style="float:left;"></span>
											<label for="coursesettingdelegation_' . $row ['variable'] . '" style="float:left;" class="coursesettingdelegationlabel">&nbsp;&nbsp;</label>
											<input type="checkbox" id="coursesettingdelegation_' . $row ['variable'] . '" class="coursesettingdelegationcheckbox" style="float:left;display:none;" '.$checked.' />
										</div>' );
		}

		$form->addElement('header', null, get_lang($row['title']));

		// icon that indicates that it is a shared setting
		if ($row['access_url_changeable']=='1' && $_configuration['multiple_access_urls']==true) {
			$form->addElement('html', '<div style="float:right;">'.Display::return_icon('shared_setting.png',get_lang('SharedSettingIconComment')).'</div>');
		}

		$hideme = array();
		$hide_element = false;
		if ($_configuration['access_url'] != 1){

			if ($row['access_url_changeable'] == 0 && $is_superadmin == false)
			{
				//we hide the element in other cases (checkbox, radiobutton) we 'freeze' the element
				if($allow_superadmin) {
                                    $hide_element=true;

                                }
                                $hideme=array('disabled');
			}
			elseif($url_info['active']==1)
			{
				// we show the elements
				if (empty($row['variable']))
					$row['variable']=0;
				if (empty($row['subkey']))
					$row['subkey']=0;
				if (empty($row['category']))
					$row['category']=0;

				if (is_array ($settings_by_access_list[ $row['variable'] ] [ $row['subkey'] ]	[ $row['category'] ]))
				{
					// we are sure that the other site have a selected value
					if ($settings_by_access_list[ $row['variable'] ] [ $row['subkey'] ]	[ $row['category'] ]['selected_value']!='')
						$row['selected_value']	=$settings_by_access_list[$row['variable']] [$row['subkey']]	[ $row['category'] ]['selected_value'];
				}
				// there is no else because we load the default $row['selected_value'] of the main Dokeos site
			}

		}

		switch ($row['type']) {
			case 'textfield' :
				if ($row['variable']=='account_valid_duration') {
					$form->addElement('text', $row['variable'], get_lang($row['comment']),array('maxlength'=>'5'));
					$form->applyFilter($row['variable'],'html_filter');
					$default_values[$row['variable']] = $row['selected_value'];

				// For platform character set selection: Conversion of the textfield to a select box with valid values.
				} elseif ($row['variable'] == 'platform_charset') {
					$current_system_encoding = api_refine_encoding_id(trim($row['selected_value']));
					$valid_encodings = array($current_system_encoding => $current_system_encoding);
					if (!isset($valid_encodings[$current_system_encoding])) {
						$is_alias_encoding = false;
						foreach ($valid_encodings as $encoding) {
							if (api_equal_encodings($encoding, $current_system_encoding)) {
								$is_alias_encoding = true;
								$current_system_encoding = $encoding;
								break;
							}
						}
						if (!$is_alias_encoding) {
							$valid_encodings[$current_system_encoding] = $current_system_encoding;
						}
					}
					foreach ($valid_encodings as $key => &$encoding) {
						$encoding = api_is_encoding_supported($key) ? $key : $key.' (n.a.)';
					}
					$form->addElement('select', $row['variable'], get_lang($row['comment']), $valid_encodings);
					$default_values[$row['variable']] = $current_system_encoding;
				//

				} else {
					$form->addElement('text', $row['variable'], get_lang($row['comment']),$hideme);
					$form->applyFilter($row['variable'],'html_filter');
					$default_values[$row['variable']] = $row['selected_value'];
				}

				break;
			case 'textarea' :
				$form->addElement('textarea', $row['variable'], get_lang($row['comment']),$hideme);
				$default_values[$row['variable']] = $row['selected_value'];
				break;
			case 'radio' :
				$values = get_settings_options($row['variable']);
				$group = array ();
				if (is_array($values )) {
					foreach ($values as $key => $value) {
                                                if (
                                                    ($row['variable'] == 'search_enabled' && $_GET['category'] <> 'PRO') || 
                                                    ($row['variable'] == 'show_glossary_in_documents' && $value['value'] == 'ismanual')
                                                ) {
                                                    continue;
                                                }
                                                $radio_value = count(explode(' ', $value['display_text'])) == 1 ? get_lang($value['display_text']) : $value['display_text'];
						$element = & $form->createElement('radio', $row['variable'], '', $radio_value, $value['value']);
						if ($hide_element) {
							$element->freeze();
						}
						$group[] = $element;
					}
				}
				$form->addGroup($group, $row['variable'], get_lang($row['comment']), '<br />', false);
				$default_values[$row['variable']] = $row['selected_value'];
				break;
			case 'checkbox';
                                //checked if the access_url have active tools
                                $url_obj = new UrlManager();
                                $url_id = $_configuration['access_url'];
                                $rs_active_url = $url_obj->url_has_active_tools($url_id);
				//1. we collect all the options of this variable
				$sql = "SELECT * FROM settings_current WHERE variable='".$row['variable']."' AND access_url =  1";

				$result = Database::query($sql, __FILE__, __LINE__);
				$group = array ();
				while ($rowkeys = Database::fetch_array($result)) {
 					if (($rowkeys['variable'] == 'course_create_active_tools' && $rowkeys['subkey'] == 'enable_search') || ($rowkeys['variable'] == 'show_tabs' && $rowkeys['subkey'] == 'search') || ($rowkeys['variable'] == 'course_create_active_tools' && $rowkeys['subkey'] == 'Advanced')) {
                                                 continue;
                                        }
					$element = & $form->createElement('checkbox', $rowkeys['subkey'], '', get_lang($rowkeys['subkeytext']));
					if ($row['access_url_changeable']==1 || ($row['variable'] == 'course_create_active_tools' && $is_superadmin == true) || $rs_active_url == true) {
						//2. we look into the DB if there is a setting for a specific access_url
						$access_url = $_configuration['access_url'];
						if(empty($access_url )) $access_url =1;
                                                $t_cs = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
						$sql = "SELECT selected_value FROM $t_cs WHERE variable='".$rowkeys['variable']."' AND subkey='".$rowkeys['subkey']."'  AND  subkeytext='".$rowkeys['subkeytext']."' AND access_url =  $access_url";
						$result_access = Database::query($sql, __FILE__, __LINE__);
						$row_access = Database::fetch_array($result_access);
                                                if (Database::num_rows($result_access) > 0) {
                                                    if ($row_access['selected_value'] == 'true' && ! $form->isSubmitted()) {
                                                            $element->setChecked(true);
                                                    }
                                                } else if ($rowkeys['selected_value'] == 'true' && ! $form->isSubmitted()) {
							$element->setChecked(true);
						}
					} else {
                                                if ($rowkeys['selected_value'] == 'true' && ! $form->isSubmitted()) {
							$element->setChecked(true);
						}
					}
					if ($hide_element) {
						$element->freeze();
					}
					$group[] = $element;
				}
				$form->addGroup($group, $row['variable'], get_lang($row['comment']), '<br />'."\n");
				break;
			case "link" :
				$form->addElement('static', null, get_lang($row['comment']), get_lang('CurrentValue').' : '.$row['selected_value'],$hideme);
                break;
            case "file" :
				$form->addElement('file', $row['variable'], get_lang($row['comment']), '',$hideme);
		}
	}

         // installation date insife category "Advanced"
         if ($_GET['category'] == 'Advanced') {
             
            $rs_install_date = Database::query("SELECT title, comment, variable, selected_value FROM $table_settings_current WHERE category='".Database::escape_string($_GET['category'])."' AND variable='installation_date'");
            if (Database::num_rows($rs_install_date) > 0){
                $row_install_date = Database::fetch_array($rs_install_date, 'ASSOC');
                
                $form->addElement('header', null, get_lang($row_install_date['title']));
                $form->addElement('static', null, get_lang($row_install_date['comment']), get_lang('CurrentValue').' : '.$row_install_date['selected_value']);
            }
         }

	if ($_GET['category'] <> "Languages"){
		$form->addElement('html','<div align="right">');
		$form->addElement('style_submit_button', ' ',get_lang('SaveSettings'), 'class="save"');
		$form->addElement('html','</div>');
	}
	$form->setDefaults($default_values);
        
	if ($form->validate()) {           
                $saved = '&saved=true';
                $values = $form->exportValues();
                // Create active tools list
                $url_obj = new UrlManager();
                $is_main_url = $url_obj->is_main_url($url_obj->get_main_url_id());
                $allow_superadmin = $url_obj->allow_superadmin();
                $is_superadmin_in_url = $url_obj->is_superadmin_in_url($_configuration['access_url'],  api_get_user_id());
                //if ($is_main_url && $is_superadmin_in_url>0) {
                    $url_obj->add_course_create_active_tools_to_urls();
                //}
                // the first step is to set all the variables that have type=checkbox of the category
                // to false as the checkbox that is unchecked is not in the $_POST data and can
                // therefore not be set to false.
                // This, however, also means that if the process breaks on the third of five checkboxes, the others
                // will be set to false.

                if($is_superadmin == true) {
                    //get the list of url
                    /*$url_list = $url_obj->get_url_data();
                    foreach ($url_list as $url_objs) {
                        if ($url_objs['active']==1) {
                            $r = api_set_settings_category_no_changable($my_category,'false',$url_objs['id'],$is_superadmin);
                        }
                    }*/
                    $r = api_set_settings_category($my_category,'false',$_configuration['access_url']);
                } else {
                    $r = api_set_settings_category($my_category,'false',$_configuration['access_url']);
                }
                //$sql = "UPDATE $table_settings_current SET selected_value='false' WHERE category='$my_category' AND type='checkbox'";
                //$result = Database::query($sql, __FILE__, __LINE__);
                // Save the settings
                $keys = array();
                foreach ($values as $key => $value) {
                        if (!is_array($value)) {
                                //$sql = "UPDATE $table_settings_current SET selected_value='".Database::escape_string($value)."' WHERE variable='$key'";
                                //$result = Database::query($sql, __FILE__, __LINE__);

                                if(api_get_setting($key) != $value) $keys[] = $key;
                                if(!api_check_if_setting_exist($key, $value, null, null, $_configuration['access_url'])) {
                                    api_create_setting($key, $value, null, null, $_configuration['access_url']);
                                }

                                if(api_get_check_if_setting_is_changable($key, $value, null, null, $_configuration['access_url'])) {
                                    if ($is_superadmin == true) {
                                        //get the list of url
                                        /*$url_list = $url_obj->get_url_data();

                                        foreach ($url_list as $url_objs) {
                                            if ($url_objs['active'] == 1) {
                                                $result = api_set_setting($key, $value, null, null, $url_objs['id']);
                                            }
                                        }*/
                                        $result = api_set_setting($key, $value, null, null, $_configuration['access_url']);
                                    } else {
                                        $result = api_set_setting($key, $value, null, null, $_configuration['access_url']);
                                    }
                                } else {
                                    $result = api_set_setting($key, $value, null, null, $_configuration['access_url']);
                                }

                        } else {

                                $sql = "SELECT subkey FROM $table_settings_current WHERE variable = '$key' AND access_url = $access_url_id";
                                $res = Database::query($sql,__FILE__,__LINE__);
                                $subkeys = array();
                                while ($row_subkeys = Database::fetch_array($res)) {
                                        // if subkey is changed
                                        if ( (isset($value[$row_subkeys['subkey']]) && api_get_setting($key,$row_subkeys['subkey']) == 'false') ||
                                             (!isset($value[$row_subkeys['subkey']]) && api_get_setting($key,$row_subkeys['subkey']) == 'true')) {
                                                $keys[] = $key;
                                                break;
                                        }
                                }

                                foreach ($value as $subkey => $subvalue) {
                                        //$sql = "UPDATE $table_settings_current SET selected_value='true' WHERE variable='$key' AND subkey = '$subkey'";
                                        //$result = Database::query($sql, __FILE__, __LINE__);

                                        if(!api_check_if_setting_exist($key, 'true', $subkey, null, $_configuration['access_url'])) {
                                            api_create_setting($key, 'true', $subkey, null, $_configuration['access_url']);
                                        }

                                        if(api_get_check_if_setting_is_changable($key, 'true', $subkey, null, $_configuration['access_url'])) {
                                            if ($is_superadmin == true) {
                                                //get the list of url
                                                $url_list = $url_obj->get_url_data();
                                                foreach ($url_list as $url_objs) {
                                                    if ($url_objs['active'] == 1) {
                                                        $result = api_set_setting($key, 'true', $subkey, null, $url_objs['id']);
                                                    }
                                                }
                                            } else {
                                                $result = api_set_setting($key, 'true', $subkey, null, $_configuration['access_url']);
                                            }
                                        } else {
                                            $result = api_set_setting($key, 'true', $subkey, null, $_configuration['access_url']);
                                            if($allow_superadmin == false) {
                                                $result = api_set_setting($key, 'true', $subkey, null, 1);
                                            }

                                        }

                                }
                        }
                }

                // add event configuration settings category to system log
                $time = time();
                $user_id = api_get_user_id();
                $category = $_GET['category'];
                event_system(LOG_CONFIGURATION_SETTINGS_CHANGE, LOG_CONFIGURATION_SETTINGS_CATEGORY, $category, $time, $user_id);


                // add event configuration settings variable to system log
                if (is_array($keys) && count($keys) > 0) {
                        foreach($keys as $variable) {
                            event_system(LOG_CONFIGURATION_SETTINGS_CHANGE, LOG_CONFIGURATION_SETTINGS_VARIABLE, $variable, $time, $user_id);
                        }
                }
                
                header('Location: settings.php?action=stored&category='.Security::remove_XSS($_GET['category']));
                exit;
	}
}

// Displaying the header
Display :: display_header($tool_name);
// Displayting the tool title
//api_display_tool_title($tool_name);

// tooltip
echo '<div id="tooltip" class="tooltip">&nbsp;</div>';

// displaying the message that the settings have been stored
if (!empty($_GET['action']) && $_GET['action'] == "stored")
{    
	Display :: display_confirmation_message(get_lang('SettingsStored'));
}

// the action images
$action_images['platform'] 		= array('class' => 'actionplaceholdericon actioncoursehome');
$action_images['course'] 		= array('class' => 'actionplaceholdericon actionscourse');
$action_images['tools'] 		= array('class' => 'actionplaceholdericon actionseditsettings');
$action_images['user'] 			= array('class' => 'actionplaceholdericon actionsmembers');
$action_images['ldap'] 			= array('class' => 'actionplaceholdericon actionsloginmanager');
$action_images['languages']		= 'languages.png';
$action_images['tuning'] 		= array('class' => 'actionplaceholdericon actionstuning');
$action_images['security'] 		= array('class' => 'actionplaceholdericon actionslook');
$action_images['plugins'] 		= 'plugin.png';
$action_images['stylesheets']           = 'theme.png';
$action_images['templates']             = 'tools_wizard_22.png';
$action_images['search']                = 'find_22.png';
$action_images['editor']		= array('class' => 'actionplaceholdericon actionsedithtml');
$action_images['system']		= array('class' => 'actionplaceholdericon actionsedithtml');
$action_images['wysiwyg']		= 'toolbar.png';
$action_images['cas']			= array('class' => 'actionplaceholdericon actionsloginmanager');
$action_images['pro']			= array('class' => 'actionplaceholdericon actionadminpro');
$action_images['ecommerce']		= array('class' => 'actionplaceholdericon actionecommercesetting');


// grabbing the categories
//$selectcategories = "SELECT DISTINCT category FROM ".$table_settings_current." WHERE category NOT IN ('stylesheets','Plugins')";
//$resultcategories = Database::query($selectcategories, __FILE__, __LINE__);
$resultcategories = api_get_settings_categories(array('stylesheets','Plugins', 'Templates', 'Search','widget','Languages', 'Advanced'));
echo "\n<div class=\"actions\">";
//while ($row = Database::fetch_array($resultcategories))
foreach($resultcategories as $row)
{
	if ($row['category'] == 'Gradebook' || $row['category'] == 'Ecommerce' || $row['category'] == 'PRO' || (api_get_setting('enable_pro_settings') !== 'true' && in_array($row['category'], $pro_category_settings))) {
		continue;
	}    
   //echo "\n\t<a href=\"".api_get_self()."?category=".$row['category']."\">".Display::return_icon($action_images[strtolower($row['category'])], api_ucfirst(get_lang($row['category']))).api_ucfirst(get_lang($row['category']))."</a>";
   echo "\n\t<a href=\"".api_get_self()."?category=".$row['category']."\">".Display::return_icon('pixel.gif',api_ucfirst(get_lang($row['category'])),$action_images[strtolower($row['category'])]).api_ucfirst(get_lang($row['category']))."</a>";
}
//echo "\n\t<a href=\"".api_get_self()."?category=Plugins\">".Display::return_icon($action_images['plugins'], api_ucfirst(get_lang('Plugins'))).api_ucfirst(get_lang('Plugins'))."</a>";
echo "\n\t<a href=\"".api_get_self()."?category=stylesheets\">".Display::return_icon('pixel.gif',get_lang('Stylesheets'), array('class' => 'actionplaceholdericon actionstheme')).api_ucfirst(get_lang('Stylesheets'))."</a>";

if (api_get_setting('enable_document_templates') === 'true') {
    echo "\n\t<a href=\"".api_get_self()."?category=Templates\">".Display::return_icon('pixel.gif',get_lang('Templates'), array('class' => 'actionplaceholdericon actionstoolswizard')).api_ucfirst(get_lang('Templates'))."</a>";
}
// advanced parameters
echo "\n\t<a href=\"".api_get_self()."?category=Advanced\">".Display::return_icon('pixel.gif',get_lang('AdvancedParameters'), array('class' => 'actionplaceholdericon actionadvanced')).api_ucfirst(get_lang('AdvancedParameters'))."</a>";
echo "\n</div>";

// start the content div
echo '<div id="content">';

if(isset($_GET['saved']))
    echo '<div class="section"><p>'.get_lang('EcommerceSettingsSaved').'</p></div>';
if (!empty($_GET['category']))
{
	switch ($_GET['category'])
	{
		// displaying the extensions: plugins
		// this will be available to all the sites (access_urls)
		case 'Plugins' :
			include_once('settings_plugins.inc.php');
			exit;
			break;
			// displaying the extensions: Stylesheets
		case 'stylesheets' :
			handle_stylesheets();
			break;
                case 'Search' :
                        handle_search();
                        break;
		case 'Templates' :                    
			handle_templates();
			break;
		//case 'System' :
			//include ('settings_system.inc.php');
			//exit;
		case 'Wysiwyg' :
			include ('settings_wysiwyg.inc.php');
			exit;
		default :
			$form->display();
	}
}

// close the content div
echo '<div class="clear"> </div>';
echo '</div>';

// display the footer
if(isset($_SESSION["display_confirmation_message"])){       
    Display :: display_confirmation_message2($_SESSION["display_confirmation_message"],false,true);
    unset($_SESSION["display_confirmation_message"]);
}
Display :: display_footer();



/*
==============================================================================
		FUNCTIONS
==============================================================================
*/


    /**
     * The function that retrieves all the possible settings for a certain config setting
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     */
    function get_settings_options($var)
    {
        $table_settings_options = Database :: get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);
        $sql = "SELECT * FROM $table_settings_options WHERE variable='$var'";
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result))
        {
            $temp_array = array ('value' => $row['value'], 'display_text' => $row['display_text']);
            $settings_options_array[] = $temp_array;
        }
        return $settings_options_array;
    }





/**
 * This function allows the platform admin to choose the default stylesheet
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*/
function handle_stylesheets()
{
	global $_configuration;
	// Current style
	$currentstyle = api_get_setting('stylesheets');
        if ($currentstyle != "dokeos2_blue_tablet" && $currentstyle != "dokeos2_orange_tablet") {
            $currentstyle = str_replace(array("dokeos2_blue","dokeos2_orange"),array("dokeos2_blue_tablet","dokeos2_orange_tablet"),$currentstyle);
        }
	$is_style_changeable=false;


	if ($_configuration['access_url']!=1)
	{
		$style_info = api_get_settings('stylesheets','',1,0);
		$url_info = api_get_access_url($_configuration['access_url']);
		if ($style_info[0]['access_url_changeable']==1 && $url_info['active']==1)
		{
			$is_style_changeable=true;
			echo '<div class="" id="stylesheetuploadlink">';
			//Display::display_icon('theme_add.gif');
                        echo Display::return_icon('pixel.gif',get_lang(''), array('class' => 'actionplaceholdericon actionstheme_add'));
			echo '<a href="" onclick="document.getElementById(\'newstylesheetform\').style.display = \'block\'; document.getElementById(\'stylesheetuploadlink\').style.display = \'none\';return false; ">'.get_lang('UploadNewStylesheet').'</a>';
			echo '</div>';
		}
	}
	else
	{
		$is_style_changeable=true;
		echo '<div class="" id="stylesheetuploadlink">';
		//Display::display_icon('theme_add.gif');
                echo Display::return_icon('pixel.gif',get_lang(''), array('class' => 'actionplaceholdericon actionstheme_add'));
		echo '<a href="" onclick="document.getElementById(\'newstylesheetform\').style.display = \'block\'; document.getElementById(\'stylesheetuploadlink\').style.display = \'none\';return false; ">'.get_lang('UploadNewStylesheet').'</a>';
		echo '</div>';
	}

	$form = new FormValidator('stylesheet_upload','post','settings.php?category=stylesheets&amp;showuploadform=true',null, "class='outer_form'");
	$form->addElement('text','name_stylesheet',get_lang('NameStylesheet'),array('size' => '40', 'maxlength' => '40'));
	$form->addRule('name_stylesheet', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('file', 'new_stylesheet', get_lang('UploadNewStylesheet'));
	$allowed_file_types = array ('zip');
	$form->addRule('new_stylesheet', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
	$form->addRule('new_stylesheet', get_lang('ThisFieldIsRequired'), 'required');
	$form->addElement('style_submit_button', 'stylesheet_upload', get_lang('Ok'), array('class'=>'save'));
	if( $form->validate() AND is_writable(api_get_path(SYS_CODE_PATH).'css/'))
	{
		$values = $form->exportValues();
		$picture_element = & $form->getElement('new_stylesheet');
		$picture = $picture_element->getValue();
		$get_status=upload_stylesheet($values, $picture);
		$stylesheet_error=false;
                if ($get_status===false) {
                    $stylesheet_error=true;
                    Display::display_confirmation_message(get_lang('StylesheetNotHasBeenAdded'), null, true);
                } else {
                            // add event to system log
                            $time = time();
                            $user_id = api_get_user_id();
                            $category = Security::remove_XSS($_GET['category']);
                            event_system(LOG_CONFIGURATION_SETTINGS_CHANGE, LOG_CONFIGURATION_SETTINGS_CATEGORY, $category, $time, $user_id);
                            Display::display_confirmation_message(get_lang('StylesheetAdded'), null, true);
                }
	}
	else
	{
		if (!is_writable(api_get_path(SYS_CODE_PATH).'css/'))
		{
			Display::display_warning_message(api_get_path(SYS_CODE_PATH).'css/'.get_lang('IsNotWritable'), false, true);
		}
		else
		{
			if (isset($_GET['showuploadform']) && $_GET['showuploadform'] == 'true')
			{
				echo '<div id="newstylesheetform">';
			}
			else
			{
				echo '<div id="newstylesheetform" style="display: none;">';
			}
				// uploading a new stylesheet
			if ($_configuration['access_url']==1)
			{
				$form->display();
			}
			else
			{
				if ($is_style_changeable)
				{
					$form->display();
				}
			}
			echo '</div>';
		}
	}

	// Preview of the stylesheet
	echo '<div style="padding:0px;border:0px;"><iframe style="border-width:2px; border-style:solid; border-color:#A4A4A4;"  frameborder="1" src="style_preview.php" width="100%" height="500"  name="preview"></iframe></div>';

	echo '<form style="padding:5px" name="stylesheets" method="post" action="'.api_get_self().'?category='.Security::remove_XSS($_GET['category']).'">';
	if ($handle = @opendir(api_get_path(SYS_PATH).'main/css/'))
	{
		$counter=1;
		while (false !== ($style_dir = readdir($handle)))
		{
			if(substr($style_dir,0,1)=='.') //skip dirs starting with a '.'
			{
				continue;
			}
			$dirpath = api_get_path(SYS_PATH).'main/css/'.$style_dir;
			if (is_dir($dirpath))
			{
				if ($style_dir != '.' && $style_dir != '..')
				{
					if ($currentstyle == $style_dir OR ($style_dir == 'dokeos2_orange' AND !$currentstyle))
					{
						$selected = 'checked="checked"';
					}
					else
					{
						$selected = '';
					}

					if ($is_style_changeable)
					{
						echo "<input style=\"margin:5px;\" type=\"radio\" name=\"style\" value=\"".$style_dir."\" ".$selected." onClick=\"parent.preview.location='style_preview.php?style=".$style_dir."';\"/>";
						echo '<a href="style_preview.php?style='.$style_dir.'" target="preview">'.get_lang($style_dir).'</a>';
					}
					else
						echo '<a href="style_preview.php?style='.$style_dir.'" target="preview">'.get_lang($style_dir).'</a>';
					echo "<br />\n";
					$counter++;
				}
			}
		}
		@closedir($handle);
	}
	if ($is_style_changeable)
	{
		echo '<div class="pull-bottom"><button class="save" type="submit" name="submit_stylesheets"> '.get_lang('SaveSettings').' </button></div></form>';
	}
}

/**
 * creates the folder (if needed) and uploads the stylesheet in it
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @author Isaac flores
 * @param array $values the values of the form
 * @param array $picture the values of the uploaded file
 * @return mixed
 * @version May 2008
 * @since Dokeos 1.8.5
 */
function upload_stylesheet($values,$picture)
{
  // valid name for the stylesheet folder
  $style_name = api_ereg_replace("[^A-Za-z0-9_]", "", $values['name_stylesheet'] );

  // move the file in the folder
  if($picture['type'] == 'application/zip' ) {
    require_once api_get_path(LIBRARY_PATH).'pclzip/pclzip.lib.php';
    $base_work_dir = api_get_path(SYS_CODE_PATH).'css';
    $upload_path = $style_name;
    $zip_file = new pclZip($picture['tmp_name']);
    // it happens on Linux that $uploadPath sometimes doesn't start with '/'
    if($upload_path[0] != '/') {
      $upload_path='/'.$upload_path;
    }
    $zip_content = $zip_file->listContent();
    $allow_extension_files=array('png', 'jpg', 'gif', 'css', 'html', 'htc', 'info');
    foreach ($zip_content as $key=>$zip_content_value) {
       $value=$zip_content_value['stored_filename'];

            if (is_string($value)) {
                $file_info=explode('/',$value);
                $file_extension=$file_info[count($file_info)-1];

                if ($file_extension<>'') {
                  $file_extension_info=explode('.',$file_extension);
                  $file_extension_data=$file_extension_info[count($file_extension_info)-1];
                    if (count($file_info)>1 && in_array($file_extension_data,$allow_extension_files)) {
                      //
                    } else {
                      return false;
                    }
                }
            }

    	}

  // create the folder if needed
  if(!is_dir(api_get_path(SYS_CODE_PATH).'css/'.$style_name.'/')) {
    if(mkdir(api_get_path(SYS_CODE_PATH).'css/'.$style_name.'/')) {
      $perm = api_get_setting('permissions_for_new_directories');
      $perm = octdec(!empty($perm)?$perm:'0770');
      chmod(api_get_path(SYS_CODE_PATH).'css/'.$style_name.'/', $perm);
    }
  }

    $name_dir = '';
    $list_dir =array();
    foreach($zip_content as $content){
      list($name_dir) = @explode('/', $content['filename']);
      if(!in_array($name_dir, $list_dir)){ $list_dir[] = $name_dir; }
    }
    //get into the right directory
    $save_dir = getcwd();

    chdir($base_work_dir.$upload_path);

    if(count($list_dir) === 1) {
      $unzipping_state = $zip_file->extract(PCLZIP_OPT_PATH, '',
                                    PCLZIP_OPT_REMOVE_PATH, $list_dir[0].'/',
                        PCLZIP_CB_PRE_EXTRACT, 'clean_up_files_zip');
    } else {
      $unzipping_state = $zip_file->extract(PCLZIP_CB_PRE_EXTRACT, 'clean_up_files_zip ');
    }

  }
}

 /**
 * this function is a callback function that is used while extracting a zipfile
 * http://www.phpconcept.net/pclzip/man/en/index.php?options-pclzip_cb_pre_extract
 *
 * @param $p_event
 * @param $p_header
 * @return 1 (If the function returns 1, then the extraction is resumed)
 */
function clean_up_files_zip($p_event, &$p_header) {
  require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';

  $res = clean_up_path($p_header['filename']);
  return $res;
}

/**
 * This function allows the platform admin to choose which should be the default stylesheet
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*/
function store_stylesheets()
{
	global $_configuration;
	// Database Table Definitions
	$table_settings_current = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

	// Insert the stylesheet
	$style = Database::escape_string($_POST['style']);
	if (is_style($style))
	{
		api_set_setting('stylesheets',$style,null,'stylesheets',$_configuration['access_url']);
	}

	return true;
}

/**
 * This function checks if the given style is a recognize style that exists in the css directory as
 * a standalone directory.
 * @param	string	Style
 * @return	bool	True if this style is recognized, false otherwise
 */
function is_style($style)
{
	$dir = api_get_path(SYS_PATH).'main/css/';
	$dirs = scandir($dir);
	$style = str_replace(array('/','\\'),array('',''),$style); //avoid slashes or backslashes
	if (in_array($style,$dirs) && is_dir($dir.$style))
	{
		return true;
	}
	return false;
}

/**
 * Search options
 * TODO: support for multiple site. aka $_configuration['access_url'] == 1
 * @author Marco Villegas <marvil07@gmail.com>
 */
function handle_search() {
// including additional libraries

    global $SettingsStored, $_configuration;
    $search_enabled = api_get_setting('search_enabled');
    $settings = api_get_settings('Search');

    if ($search_enabled !== 'true' || count($settings) < 1) {

        //Display::display_error_message(get_lang('SearchFeatureNotEnabledComment'));
        echo get_lang('SearchFeatureNotEnabledComment');
        return;
    }

    require_once api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php';

    $form = new FormValidator('search-options', 'post', api_get_self().'?category=Search');
    $renderer = & $form->defaultRenderer();
    $renderer->setHeaderTemplate('<div class="section"><div class="sectiontitle">{header}</div>'."\n");
    $renderer->setElementTemplate('<div class="sectioncomment">{label}</div>'."\n".'<div class="sectionvalue">{element}</div></div>'."\n");

    //search_show_unlinked_results
    $form->addElement('header', null, get_lang('SearchShowUnlinkedResultsTitle'));
    $form->addElement('label', null, get_lang('SearchShowUnlinkedResultsComment'));
    $values = get_settings_options('search_show_unlinked_results');
    $group = array ();
    foreach ($values as $key => $value) {
        $element = & $form->createElement('radio', 'search_show_unlinked_results', '', get_lang($value['display_text']), $value['value']);
        $group[] = $element;
    }
    $form->addGroup($group, 'search_show_unlinked_results', get_lang('SearchShowUnlinkedResultsComment'), '<br />', false);
    $default_values['search_show_unlinked_results'] = api_get_setting('search_show_unlinked_results');

    //search_prefilter_prefix
    $form->addElement('header', null, get_lang('SearchPrefilterPrefix'));
    $form->addElement('label', null, get_lang('SearchPrefilterPrefixComment'));
    $specific_fields = get_specific_field_list();
    $sf_values = array();
    foreach ($specific_fields as $sf) {
       $sf_values[$sf['code']] = $sf['name'];
    }
    $group = array ();
    $form->addElement('select', 'search_prefilter_prefix', get_lang('SearchPrefilterPrefix'), $sf_values, '');
    $default_values['search_prefilter_prefix'] = api_get_setting('search_prefilter_prefix');

    //$form->addRule('search_show_unlinked_results', get_lang('ThisFieldIsRequired'), 'required');
    $form->addElement('style_submit_button', 'search-options-save', get_lang('Ok'));
    $form->setDefaults($default_values);

    if( $form->validate()) {
        $formvalues = $form->exportValues();
        $r = api_set_settings_category('Search','false',$_configuration['access_url']);
        // Save the settings
        foreach ($formvalues as $key => $value)
        {
                $result = api_set_setting($key,$value,null,null);
        }

        Display :: display_confirmation_message($SettingsStored);
    } else {
        echo '<div id="search-options-form">';
        $form->display();

        echo '</div>';
    }
}

/**
 * wrapper for the templates
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
function handle_templates()
{
//	if ($_GET['action'] != 'add') {
		echo "\n<div class=\"actions\" style=\"margin-left:1px\" >";
		echo '<a href="settings.php?category=Templates&amp;action=add">'.Display::return_icon('pixel.gif', get_lang('AddTemplate'),array('class'=>'actionplaceholdericon actionaddtemplate')).get_lang('AddTemplate').'</a>';
		echo "\n</div>";
//	}

	if ($_GET['action'] == 'add' OR ( $_GET['action'] == 'edit' AND is_numeric($_GET['id']))) {
		add_edit_template();
                
		// add event to system log
		$time = time();
		$user_id = api_get_user_id();
		$category = $_GET['category'];
		event_system(LOG_CONFIGURATION_SETTINGS_CHANGE, LOG_CONFIGURATION_SETTINGS_CATEGORY, $category, $time, $user_id);
               
	} else {
		if ($_GET['action'] == 'delete' and is_numeric($_GET['id'])) {
			delete_template($_GET['id']);

			// add event to system log
			$time = time();
			$user_id = api_get_user_id();
			$category = $_GET['category'];
			event_system(LOG_CONFIGURATION_SETTINGS_CHANGE, LOG_CONFIGURATION_SETTINGS_CATEGORY, $category, $time, $user_id);
		}
		display_templates();
	}
}

/**
 * Display a sortable table with all the templates that the platform administrator has defined.
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
function display_templates()
{       
	$table = new SortableTable('templates', 'get_number_of_templates', 'get_template_data',1);
	$table->set_additional_parameters(array('category'=>Security::remove_XSS($_GET['category'])));
	$table->set_header(0, get_lang('Image'), true, array ('style' => 'width:101px;'));
	$table->set_header(1, get_lang('Title'));
	$table->set_header(2, get_lang('Actions'), false, array ('style' => 'width:50px;'));
	$table->set_column_filter(2,'actions_filter');
	$table->set_column_filter(0,'image_filter');
	$table->display();
}

/**
 * Get the number of templates that are defined by the platform admin.
 *
 * @return integer
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
function get_number_of_templates()
{
	// Database table definition
	$table_system_template = Database :: get_main_table('system_template');

	// The sql statement
	$sql = "SELECT COUNT(id) AS total FROM $table_system_template";
	$result = Database::query($sql, __FILE__, __LINE__);
	$row = Database::fetch_array($result);

	// returning the number of templates
	return $row['total'];
}

/**
 * Get all the template data for the sortable table
 *
 * @param integer $from the start of the limit statement
 * @param integer $number_of_items the number of elements that have to be retrieved from the database
 * @param integer $column the column that is
 * @param string $direction the sorting direction (ASC or DESC�
 * @return array
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
function get_template_data($from, $number_of_items, $column, $direction)
{
	// Database table definition
	$table_system_template = Database :: get_main_table('system_template');

	// the sql statement
	$sql = "SELECT image as col0, title as col1, id as col2 FROM $table_system_template";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$result = Database::query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_array($result)) {
		$row['1'] = get_lang($row['1']);
		$return[]=$row;
	}
	// returning all the information for the sortable table
	return $return;
}

/**
 * display the edit and delete icons in the sortable table
 *
 * @param integer $id the id of the template
 * @return html code for the link to edit and delete the template
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
function actions_filter($id)
{
    /*
     	$return .= '<a href="settings.php?category=Templates&amp;action=edit&amp;id='.Security::remove_XSS($id).'">'.Display::return_icon('pixel.gif', get_lang('Edit'),array('class'=>'actionplaceholdericon actionedit')).'</a>';
	$return .= '<a href="settings.php?category=Templates&amp;action=delete&amp;id='.Security::remove_XSS($id).'" onclick="javascript:if(!confirm('."'".get_lang("ConfirmYourChoice")."'".')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'),array('class'=>'actionplaceholdericon actiondelete')).'</a>';
     */
	$return .= '<a href="settings.php?category=Templates&amp;action=edit&amp;id='.Security::remove_XSS($id).'">'.Display::return_icon('pixel.gif', get_lang('Edit'),array('class'=>'actionplaceholdericon actionedit')).'</a>';
	$return .= '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\'settings.php?category=Templates&amp;action=delete&amp;id='.Security::remove_XSS($id) . '\',\'' . get_lang("ConfirmationDialog") . '\',\'' . get_lang("ConfirmYourChoice") . '\');">'.Display::return_icon('pixel.gif', get_lang('Delete'),array('class'=>'actionplaceholdericon actiondelete')).'</a>';
	return $return;
}

/**
 * Display the image of the template in the sortable table
 *
 * @param string $image the image
 * @return html code for the image
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
function image_filter($image)
{
	if (!empty($image))
	{
		return '<img src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$image.'" alt="'.get_lang('TemplatePreview').'"/>';
	}
	else
	{
		return '<img src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/noimage.gif" alt="'.get_lang('NoTemplatePreview').'"/>';
	}
}

/**
 * Add (or edit) a template. This function displays the form and also takes care of uploading the image and storing the information in the database
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
 function add_edit_template()
{       $get_template_id = "";
        if (isset($_GET['id']) && $_GET['id'] > 0) {
            $get_template_id = '&id='.Security::remove_XSS($_GET['id']);
        }
	// initiate the object
	$form = new FormValidator('template', 'post', 'settings.php?category=Templates&action='.Security::remove_XSS($_GET['action']).$get_template_id);

	// settting the form elements: the header
	if ($_GET['action'] == 'add') {
		$title = get_lang('AddTemplate');
	} else {
                $title = get_lang('EditTemplate');
	}
	$form->addElement('header', '', $title);

	// settting the form elements: the title of the template
   if ($_GET['action'] == 'add') {
        $form->add_textfield('title', get_lang('Title'), false, array('id' => 'idTitle','size' => '50'));
    } else {
     //   $form->addElement('static','title', '', get_lang('Title'));
		  $form->add_textfield('title', get_lang('Title'), false, array('id' => 'idTitle','size' => '50'));
    }

	// settting the form elements: the form to upload an image to be used with the template
	$form->addElement('file','template_image',get_lang('Image'),'');

	// settting the form elements: a little bit information about the template image
	$form->addElement('static', 'file_comment', '', get_lang('TemplateImageComment100x70')."<br/><br/><br/>");

        // getting all the information of the template when editing a template
	if ($_GET['action'] == 'edit') {
		// Database table definition
		$table_system_template = Database :: get_main_table('system_template');
		$sql = "SELECT * FROM $table_system_template WHERE id = '".Database::escape_string($_GET['id'])."'";
		$result = Database::query($sql, __FILE__, __LINE__);
		$row = Database::fetch_array($result,'ASSOC');

		$defaults['template_id'] 	= intval($_GET['id']);

		$title = get_lang($row['title']);
		$position = strpos($title, '[=');
		if ($position === false){
			$title = get_lang($row['title']);
		}
		else {
			$title = $row['title'];
		}
		$defaults['title'] 			= $title;

                // setting some paths
                $img_dir = api_get_path(REL_CODE_PATH).'img/';
                $default_course_dir = api_get_path(REL_CODE_PATH).'default_course_document/';

		$valcontent =  $row['content'];
		$css_name = api_get_setting('stylesheets');
		$defaults['template_text'] 	= $valcontent;

        // adding an extra field: a hidden field with the id of the template we are editing
		$form->addElement('hidden','template_id');

		// adding an extra field: a preview of the image that is currently used
		if (!empty($row['image'])) {
			$form->addElement('static','template_image_preview', '', '<div class="CusImageTemplate"><img src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$row['image'].'" alt="'.get_lang('TemplatePreview').'"/></div>');
                } else {
			$form->addElement('static','template_image_preview', '', '<div class="CusImageTemplate"><img src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/noimage.gif" alt="'.get_lang('NoTemplatePreview').'"/></div>');
                }

		// setting the information of the template that we are editing
		$form->setDefaults($defaults);
	} else {
                $default_content = '';
                $defaults['template_text'] = $default_content;
		// setting the information of the template that we are adding
		$form->setDefaults($defaults);
    }
        
        
	// settting the form elements: the content of the template (wysiwyg editor)
	//$form->addElement('html_editor', 'template_text', get_lang('Text'), null, array('ToolbarSet' => 'AdminTemplates', 'Width' => '100%', 'Height' => '730'));
	$form->addElement('html_editor', 'template_text', '', null, array('ToolbarSet' => 'AdminTemplates', 'Width' => '100%', 'Height' => '730'));
	
        $form->addElement('html','<div class="pull-bottom">');
        // settting the form elements: the submit button
	$form->addElement('style_submit_button' , 'submit', get_lang('Ok') ,'class="save" style="float:right !important;"');
        $form->addElement('html','</div>');
	// setting the rules: the required fields
    if ($_GET['action'] == 'add') {
        $form->addRule('title', get_lang('ThisFieldIsRequired'), 'required');
    }
	$form->addRule('template_text', get_lang('ThisFieldIsRequired'), 'required');


	

	// if the form validates (complies to all rules) we save the information, else we display the form again (with error message if needed)
	if( $form->validate() ) {

		$check = Security::check_token('post');
		if ($check) {
			// exporting the values
			$values = $form->exportValues();

			// upload the file
			if (!empty($_FILES['template_image']['name']))
			{
				include_once (api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
				$upload_ok = process_uploaded_file($_FILES['template_image']);

				if ($upload_ok)
				{
					// Try to add an extension to the file if it hasn't one
					$new_file_name = add_ext_on_mime(stripslashes($_FILES['template_image']['name']), $_FILES['template_image']['type']);

					// upload dir
					$upload_dir = api_get_path(SYS_PATH).'home/default_platform_document/template_thumb/';

					// create dir if not exists
	                if (!is_dir($upload_dir)) {
	                    $perm = api_get_setting('permissions_for_new_directories');
	                    $perm = octdec(!empty($perm)?$perm:'0770');
	                	$res = @mkdir($upload_dir,$perm);
	                }

					// resize image to max default and upload
					require_once (api_get_path(LIBRARY_PATH).'image.lib.php');
					$temp = new image($_FILES['template_image']['tmp_name']);
					$picture_infos=@getimagesize($_FILES['template_image']['tmp_name']);
					$max_width_for_picture = 200;
                                        $max_heigt_for_picture = 130;
					if ($picture_infos[0]>$max_width_for_picture)
					{
						$thumbwidth = $max_width_for_picture;
						if (empty($thumbwidth) or $thumbwidth==0) {
						  $thumbwidth=$max_width_for_picture;
						}
                                                if($picture_infos[1]>$max_heigt_for_picture){
                                                    $new_height = $max_heigt_for_picture;
                                                }else{
                                                    $new_height = round(($thumbwidth/$picture_infos[0])*$picture_infos[1]);
                                                }
						$temp->resize($thumbwidth,$new_height,1);
                                        }else{
						$thumbwidth = $max_width_for_picture;
						if (empty($thumbwidth) or $thumbwidth==0) {
						  $thumbwidth=$max_width_for_picture;
						}
                                                if($picture_infos[1]>$max_heigt_for_picture){
                                                    $new_height = $max_heigt_for_picture;
                                                }else{
                                                    $new_height = round(($thumbwidth/$picture_infos[0])*$picture_infos[1]);
                                                }

						$temp->resize($thumbwidth,$new_height,1);
                                        }
					$type=$picture_infos[2];

					switch (!empty($type))
					{
						case 2 : $temp->send_image('JPG',$upload_dir.$new_file_name);
								 break;
						case 3 : $temp->send_image('PNG',$upload_dir.$new_file_name);
								 break;
						case 1 : $temp->send_image('GIF',$upload_dir.$new_file_name);
								 break;
					}
				}
		   }

		   // store the information in the database (as insert or as update)
		   $table_system_template = Database :: get_main_table('system_template');
		   if ($_GET['action'] == 'add') {
                        $real_content_template = $values['template_text'];
                        $content_template = Database::escape_string($real_content_template);

		   	$sql = "INSERT INTO $table_system_template (title, content, image) VALUES ('".Database::escape_string($values['title'])."','".$content_template."','".Database::escape_string($new_file_name)."')";
                        $result = Database::query($sql, __FILE__, __LINE__);

			// display a feedback message
                         $_SESSION["display_confirmation_message"]   =  get_lang('TemplateAdded', '');
                         
                        //echo get_lang('TemplateAdded');
	//		echo '<a href="settings.php?category=Templates&amp;action=add">'.Display::return_icon('template_add.gif', get_lang('AddTemplate')).get_lang('AddTemplate').'</a>';
		   } else {
                        // Split content templates
                        $real_content_template = $values['template_text'];
                        $content_template = Database::escape_string($real_content_template);
                        $sql = "UPDATE $table_system_template set title = '".Database::escape_string($values['title'])."', content = '".$content_template."'";

                        if (!empty($new_file_name)) {
                                $sql .= ", image = '".Database::escape_string($new_file_name)."'";
                        }
                        $sql .= " WHERE id='".Database::escape_string($_GET['id'])."'";
                        $result = Database::query($sql, __FILE__, __LINE__);

			// display a feedback message
                        $_SESSION["display_confirmation_message"]   =  get_lang('TemplateEdited', '');                        
                        //echo get_lang('TemplateEdited');
		   }
                   
		}
	   Security::clear_token();
	   display_templates();
	}
	else
	{
		$token = Security::get_token();
		$form->addElement('hidden','sec_token');
		$form->setConstants(array('sec_token' => $token));
		// display the form
		$form->display();
	}
}
//commented old code
/*function add_edit_template()
{
	// initiate the object
	$form = new FormValidator('template', 'post', 'settings.php?category=Templates&amp;action='.Security::remove_XSS($_GET['action']).'&amp;id='.Security::remove_XSS($_GET['id']));

	// settting the form elements: the header
	if ($_GET['action'] == 'add') {
		$title = get_lang('AddTemplate');
	} else {
		$title = get_lang('EditTemplate');
	}
	$form->addElement('header', '', $title);

	// settting the form elements: the title of the template
    if ($_GET['action'] == 'add') {
        $form->add_textfield('title', get_lang('Title'), false, array('id' => 'idTitle'));
    } else {
        $form->addElement('static','title', '', get_lang('Title'));
    }

	// settting the form elements: the content of the template (wysiwyg editor)
	//$form->addElement('html_editor', 'template_text', get_lang('Text'), null, array('ToolbarSet' => 'AdminTemplates', 'Width' => '100%', 'Height' => '730'));
	$form->addElement('html_editor', 'template_text', '', null, array('ToolbarSet' => 'AdminTemplates', 'Width' => '100%', 'Height' => '730'));
		// settting the form elements: the submit button
	$form->addElement('style_submit_button' , 'submit', get_lang('Ok') ,'class="save"');

	// settting the form elements: the form to upload an image to be used with the template
	$form->addElement('file','template_image',get_lang('Image'),'');

	// settting the form elements: a little bit information about the template image
	$form->addElement('static', 'file_comment', '', get_lang('TemplateImageComment100x70'));


	// setting the rules: the required fields
    if ($_GET['action'] == 'add') {
        $form->addRule('title', '<div class="required">'.get_lang('ThisFieldIsRequired'), 'required');
    }
	$form->addRule('template_text', '<div class="required">'.get_lang('ThisFieldIsRequired'), 'required');


	// getting all the information of the template when editing a template
	if ($_GET['action'] == 'edit') {
		// Database table definition
		$table_system_template = Database :: get_main_table('system_template');
		$sql = "SELECT * FROM $table_system_template WHERE id = '".Database::escape_string($_GET['id'])."'";
		$result = Database::query($sql, __FILE__, __LINE__);
		$row = Database::fetch_array($result,'ASSOC');

		$defaults['template_id'] 	= intval($_GET['id']);
		//$defaults['template_text'] 	= $row['content'];
		//forcing a get_lang
		$defaults['title'] 			= get_lang($row['title']);

        // setting some paths
        $img_dir = api_get_path(REL_CODE_PATH).'img/';
        $default_course_dir = api_get_path(REL_CODE_PATH).'default_course_document/';

        $css_name = api_get_setting('stylesheets');
        $template_css = ' <style type="text/css">'.str_replace('../../img/',api_get_path(REL_CODE_PATH).'img/',file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/default.css')).'</style>';
        if(file_exists(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')) {
            $template_css .= ' <style type="text/css">'.str_replace('../../img/',api_get_path(REL_CODE_PATH).'img/',file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')).'</style>';
        }
        $template_css = str_replace('images/',api_get_path(REL_CODE_PATH).'css/'.$css_name.'/images/',$template_css);
        $valcontent =  $row['content'];
        $valcontent =  str_replace('{CSS}',$template_css, $valcontent);
        $valcontent =  str_replace('{IMG_DIR}',$img_dir, $valcontent);
        $valcontent =  str_replace('{REL_PATH}', api_get_path(REL_PATH), $valcontent);
        $valcontent =  str_replace('{COURSE_DIR}',$default_course_dir, $valcontent);
		$defaults['template_text'] 	= $valcontent;

        // adding an extra field: a hidden field with the id of the template we are editing
		$form->addElement('hidden','template_id');

		// adding an extra field: a preview of the image that is currently used
		if (!empty($row['image'])) {
			$form->addElement('static','template_image_preview', '', '<img src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$row['image'].'" alt="'.get_lang('TemplatePreview').'"/>');
		} else {
			$form->addElement('static','template_image_preview', '', '<img src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/noimage.gif" alt="'.get_lang('NoTemplatePreview').'"/>');
		}

		// setting the information of the template that we are editing
		$form->setDefaults($defaults);
	} else {
        $default_content = '<!-- white table for the course --> <!-- Your template should be inside of the table with class=white --> <table class="white" style="text-align: left; width: 100%; height: 600px;" border="0" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td style="vertical-align: top;"><br>
            </td>
          </tr>
        </tbody>
        </table> <!-- end white table for the course -->';
        $defaults['template_text'] = $default_content;
		// setting the information of the template that we are adding
		$form->setDefaults($defaults);
    }

	// if the form validates (complies to all rules) we save the information, else we display the form again (with error message if needed)
	if( $form->validate() ) {

		$check = Security::check_token('post');
		if ($check) {
			// exporting the values
			$values = $form->exportValues();

			// upload the file
			if (!empty($_FILES['template_image']['name']))
			{
				include_once (api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
				$upload_ok = process_uploaded_file($_FILES['template_image']);

				if ($upload_ok)
				{
					// Try to add an extension to the file if it hasn't one
					$new_file_name = add_ext_on_mime(stripslashes($_FILES['template_image']['name']), $_FILES['template_image']['type']);

					// upload dir
					$upload_dir = api_get_path(SYS_PATH).'home/default_platform_document/template_thumb/';

					// create dir if not exists
	                if (!is_dir($upload_dir)) {
	                    $perm = api_get_setting('permissions_for_new_directories');
	                    $perm = octdec(!empty($perm)?$perm:'0770');
	                	$res = @mkdir($upload_dir,$perm);
	                }

					// resize image to max default and upload
					require_once (api_get_path(LIBRARY_PATH).'image.lib.php');
					$temp = new image($_FILES['template_image']['tmp_name']);
					$picture_infos=@getimagesize($_FILES['template_image']['tmp_name']);

					$max_width_for_picture = 100;

					if ($picture_infos[0]>$max_width_for_picture)
					{
						$thumbwidth = $max_width_for_picture;
						if (empty($thumbwidth) or $thumbwidth==0) {
						  $thumbwidth=$max_width_for_picture;
						}
						$new_height = round(($thumbwidth/$picture_infos[0])*$picture_infos[1]);

						$temp->resize($thumbwidth,$new_height,0);
					}

					$type=$picture_infos[2];

					switch (!empty($type))
					{
						case 2 : $temp->send_image('JPG',$upload_dir.$new_file_name);
								 break;
						case 3 : $temp->send_image('PNG',$upload_dir.$new_file_name);
								 break;
						case 1 : $temp->send_image('GIF',$upload_dir.$new_file_name);
								 break;
					}
				}
		   }

		   // store the information in the database (as insert or as update)
		   $table_system_template = Database :: get_main_table('system_template');
		   if ($_GET['action'] == 'add') {
	   		$content_template = '<head>{CSS}<style type="text/css">.text{font-weight: normal;}</style></head><body>'.Database::escape_string($values['template_text']).'</body>';
		   	$sql = "INSERT INTO $table_system_template (title, content, image) VALUES ('".Database::escape_string($values['title'])."','".$content_template."','".Database::escape_string($new_file_name)."')";
            $result = Database::query($sql, __FILE__, __LINE__);

			// display a feedback message
		   	//Display::display_confirmation_message(get_lang('TemplateAdded'));
            echo get_lang('TemplateAdded');
			echo '<a href="settings.php?category=Templates&amp;action=add">'.Display::return_icon('template_add.gif', get_lang('AddTemplate')).get_lang('AddTemplate').'</a>';
		   } else {
                // Split content templates
                $content_template = explode('<!-- white table for the course -->',$values['template_text']);
                $content_template = explode('<!-- end white table for the course -->',$content_template[1]);
                $real_content_template = $content_template[0];
                // Save the updated template
                $content_template = '<head>{CSS}<style type="text/css">.text{font-weight: normal;}</style></head><body><!-- white table for the course -->'.Database::escape_string($real_content_template).'<!-- end white table for the course --></body>';
			   	/*$sql = "UPDATE $table_system_template set title = '".Database::escape_string($values['title'])."',
											   		  content = '".$content_template."'";*/

		/*	   	$sql = "UPDATE $table_system_template set content = '".$content_template."'";

			   	if (!empty($new_file_name)) {
			   		$sql .= ", image = '".Database::escape_string($new_file_name)."'";
			   	}
			   	$sql .= " WHERE id='".Database::escape_string($_GET['id'])."'";
			   	$result = Database::query($sql, __FILE__, __LINE__);

			   	// display a feedback message
		   	//Display::display_confirmation_message(get_lang('TemplateEdited'));
            echo get_lang('TemplateEdited');
		   }

		}
	   Security::clear_token();
	   display_templates();
	}
	else
	{
		$token = Security::get_token();
		$form->addElement('hidden','sec_token');
		$form->setConstants(array('sec_token' => $token));
		// display the form
		$form->display();
	}
}*/


/**
 * Delete a template
 *
 * @param integer $id the id of the template that has to be deleted
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version August 2008
 * @since Dokeos 1.8.6
 */
function delete_template($id)
{
	// first we remove the image
	$table_system_template = Database :: get_main_table('system_template');
	$sql = "SELECT * FROM $table_system_template WHERE id = '".Database::escape_string($id)."'";
	$result = Database::query($sql, __FILE__, __LINE__);
	$row = Database::fetch_array($result);
	if (!empty($row['image']))
	{
		unlink(api_get_path(SYS_PATH).'home/default_platform_document/template_thumb/'.$row['image']);
	}

	// now we remove it from the database
	$sql = "DELETE FROM $table_system_template WHERE id = '".Database::escape_string($id)."'";
	$result = Database::query($sql, __FILE__, __LINE__);

	// display a feedback message
	Display::display_confirmation_message(get_lang('TemplateDeleted'));
}
?>
