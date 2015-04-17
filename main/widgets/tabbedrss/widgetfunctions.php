<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');

// load the specific widget settings
api_load_widget_settings();

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		tabbedrss_get_information();
		break;
	case 'get_widget_content':
		tabbedrss_get_content();
		break;
	case 'tabbedrss_add':
		tabbedrss_add($_POST['tabbedrss_id']);
		break;
	case 'install':
		tabbedrss_install();
		break;	
	case 'tabbedrss_form':
		tabbedrss_form($_POST['id']);
		break;
	case 'tabbedrss_store':
		tabbedrss_store($_POST);
		break;
	case 'tabbedrss_delete':
		tabbedrss_delete($_POST['id']);
		break;
	case 'get_rss_content':
		get_rss_content($_POST['url'], $_POST['number_of_items']);
		break;
}
switch ($_GET['action']) {
	case 'get_widget_information':
		tabbedrss_get_information();
		break;
	case 'get_widget_content':
		tabbedrss_get_content();
		break;
	case 'tabbedrss_add':
		tabbedrss_add($_GET['tabbedrss_id']);
		break;	
	case 'install':
		tabbedrss_install();
		break;	
	case 'tabbedrss_form':
		tabbedrss_form($_GET['id']);
		break;
	case 'tabbedrss_store':
		tabbedrss_store($_GET);
		break;
	case 'tabbedrss_delete':
		tabbedrss_delete($_GET['id']);
		break;
	case 'get_rss_content':
		get_rss_content($_GET['url'], $_GET['number_of_items']);
		break;		
}

/**
 * This function determines if the widget can be used inside a course, outside a course or both
 * 
 * @return array 
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_get_scope(){
	return array('course', 'platform');
}


/**
 * Enter description here...
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium 
 */
function tabbedrss_get_content(){
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_tabbedrss = Database :: get_course_table('tabbedrss');
	} else {
		$table_tabbedrss = Database :: get_main_table('tabbedrss');
	}
	?>
	<style type="text/css">
		.tabbedrss_item{ width: 100%; height: 25px;padding: 2px; padding-left: 5px; border: 1px solid transparent;}
		.tabbedrss_item:hover{ border: 1px solid #CCCCCC; }
		.tabbedrss_item_actions{ float:right;}
		.tabbedrss_source, .tabbedrss_title, .tabbedrss_number_of_items{display:none;}
	</style>
	<script type="text/javascript">
	$(function() {
		$( "#tabbedrss" ).tabs(); 
		});
	</script>
	
	<?php if (api_is_allowed_to_edit()){ ?>
	<script type="text/javascript">
	$(function() {		
		// showing the form to add
		$("#addtabbedrss").live('click',function(){
			$.ajax({
				url: '<?php echo api_get_path(WEB_PATH);?>main/widgets/tabbedrss/widgetfunctions.php',
				data: {action: 'tabbedrss_form'},
				success: function(content){
					$("#tabbedrss_form").html(content);
					$("#tabbedrss_actions").hide();
					$("#tabbedrss_content").hide();

					// change the form if the size is not wide enough
					var locationwidth = $("#addtabbedrss").parents(".location").width();
					if (locationwidth < 650){
						$("div.formw").css("float","none");
						$("div.formw").css("width","200px");
						$("#tabbedrss_form input").attr("size","30");

					}
				}
			});
			return false;
		});
		
		// saving the form
		$("#tabbedrss_store").live('click',function(){
			var varid 	= $("#tabbedrssid").val();
			var vartitle = $("#title").val();
			var varurl = $("#url").val();
			var varnumber_items = $("#number_items").val();

			if (vartitle.length == 0 || varurl.length == 0 || varnumber_items.length == 0){
				alert('<?php echo get_lang('SomeFieldsAreEmpty');?>');
				return false;	
			}			
			
			$.ajax({
				url: '<?php echo api_get_path(WEB_PATH);?>main/widgets/tabbedrss/widgetfunctions.php',
				data: {action: 'tabbedrss_store', title: vartitle, url: varurl, number_items: varnumber_items, id: varid},
				success: function(content){
					$("#tabbedrss_form").remove();
					$("#tabbedrss_actions").remove();
					$("#tabs-mngnt").html(content);
					
					if (varid == ''){
						// if we add a tabbed rss feed we add a tab
						$("#tabbedrss").tabs("add", '' , vartitle);
					} else {
						// if we edit a tab we empty the .tabbedrss_content and other div of that tabbed rss so that when reloading it gets filled with the new info
						$("#tabbedrss_"+varid+" a").html(vartitle);
						$("#tabs-"+varid+" div.tabbedrss_content").html('');
						$("#tabs-"+varid+" div.tabbedrss_title").html(vartitle);
						$("#tabs-"+varid+" div.tabbedrss_number_of_items").html(varnumber_items);
						$("#tabs-"+varid+" div.tabbedrss_source").html(varurl);
					}
					
				}
			});
			return false;
		});	

		// editing an item
		$(".tabbedrss_edit").live('click',function(){
			var tabbedrss_id = $(this).attr("id").replace('edit_','');
			$.ajax({
				url: '<?php echo api_get_path(WEB_PATH);?>main/widgets/tabbedrss/widgetfunctions.php',
				data: {action: 'tabbedrss_form', id: tabbedrss_id},
				success: function(content){
					$("#tabbedrss_form").html(content);
					$("#tabbedrss_actions").hide();
					$("#tabbedrss_content").hide();
				}
			});
			return false;
		});
		
		// deleting an item
		$(".tabbedrss_delete").live('click',function(){
			// get the id of the item we are about to remove
			var tabbedrss_id = $(this).attr("id").replace('delete_','');
			// count position of this item in the list
			var listitem =$(this).parent().parent(); 
			console.log(listitem);
			var position = $("div.tabbedrss_item").index(listitem);
			// execute 
			$.ajax({
				url: '<?php echo api_get_path(WEB_PATH);?>main/widgets/tabbedrss/widgetfunctions.php',
				data: {action: 'tabbedrss_delete', id: tabbedrss_id},
				success: function(content){
					$("#tabs-mngnt").html(content);
					$("#tabbedrss").tabs("remove", position+1);
				}
			});
		});
		
		// load the RSS feed if not loaded yet
		$("#tabbedrss").tabs({
				show: function(event, ui){
					var varurl = $(this).attr('href');
					var tabindex = $("#tabbedrss").tabs('option','selected');
					var temp_id = $("li.tab").eq(tabindex).attr('id');
					if (temp_id !== 'tab-mngnt'){
							// id of the item in the database
							var tabbedrss_id = temp_id.replace('tabbedrss_','');
							// url of the feed
							var tabbedrss_source = $("#tabs-"+tabbedrss_id+" div.tabbedrss_source").html();
							// url of the feed
							var tabbedrss_number_of_items = $("#tabs-"+tabbedrss_id+" div.tabbedrss_number_of_items").html();							

							if ($("#tabs-"+tabbedrss_id+" div.tabbedrss_content").html().length == 0){
								$("#tabs-"+tabbedrss_id+" div.tabbedrss_content").html('<img src="<?php echo api_get_path(WEB_PATH);?>main/img/ajax-loader.gif" alt="<?php echo get_lang("Loading"); ?>"/>');
								
								$.ajax({
									url: '<?php echo api_get_path(WEB_PATH);?>main/widgets/tabbedrss/widgetfunctions.php',
									data: {action: 'get_rss_content', url: tabbedrss_source, number_of_items: tabbedrss_number_of_items},
									success: function(content){
										$("#tabs-"+tabbedrss_id+" div.tabbedrss_content").html(content);
									}	
								});
							}
						}
					}	
		});
	});
	</script>
	<?php } 
	$tabbed_rss = get_tabbedrss();
	?>
	<div id="tabbedrss">
	<ul>
		<?php if (api_is_allowed_to_edit()){ ?>
		<li class="tab" id="tab-mngnt"><a href="#tabs-mngnt"><span class="ui-icon ui-icon-gear"></span></a></li>
		<?php } 
		foreach ($tabbed_rss as $key=>$item){
			echo '<li class="tab" id="tabbedrss_'.$item['id'].'"><a href="#tabs-'.$item['id'].'">'.$item['title'].'</a></li>';	
		}
		?>
	</ul>
	<?php if (api_is_allowed_to_edit()){ ?>
	<div id="tabs-mngnt">
		<?php echo tabbedrss_management_interface();?>
	</div>
	<?php } 
	foreach ($tabbed_rss as $key=>$item){
		echo '<div id="tabs-'.$item['id'].'">
					<div class="tabbedrss_source">'.$item['url'].'</div>
					<div class="tabbedrss_number_of_items">'.$item['number_items'].'</div>					
					<div class="tabbedrss_title">'.$item['title'].'</div>
					<div class="tabbedrss_content"></div>
				</div>';	
	}	
	?>
	</div>
	<?php	
	

}

/**
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('tabbedrss', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('TabbedRss');
	}
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_get_information(){
	echo '<span style="float:right;">';
	tabbedrss_get_screenshot();
	echo '</span>';
	echo get_lang('TabbedRSSExplanation');
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/widgets/tabbedrss/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_settings_form(){

}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_install(){
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_tabbedrss = Database :: get_course_table('tabbedrss');
	} else {
		$table_tabbedrss = Database :: get_main_table('tabbedrss');
	}
	
	$sql = "CREATE TABLE  $table_tabbedrss (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`title` VARCHAR( 250 ) NOT NULL ,
					`url` VARCHAR( 250 ) NOT NULL ,
					`number_items` INT( 11 ) NOT NULL
				) ";
	$result = Database::query($sql, __FILE__, __LINE__);	
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_management_interface(){
	if (api_is_allowed_to_edit()){	
		$return = '<div id="tabbedrss_actions"><a href="" id="addtabbedrss">'.get_lang('AddTabbedRss').'</a></div>';
		$return .= '<div id="tabbedrss_form"></div>';
		$return .= '<div id="tabbedrss_content">';
		$tabbedrss = array();
	
		$tabbedrss = get_tabbedrss();
		foreach ($tabbedrss as $key=>$feed){
			$return .= '<div id="tabbedrss_'.$feed['id'].'" class="tabbedrss_item rounded">'.$feed['title'].'<span class="tabbedrss_item_actions">';
			$return .= Display::return_icon('pixel.gif',	get_lang('EditTabbedRss'),	array('class'=>'tabbedrss_edit', 'id'=>'edit_'.$feed['id'],'class'=>'actionplaceholdericon actionedit'));
			$return .= Display::return_icon('delete.gif',	get_lang('DeleteTabbedRss'),	array('class'=>'tabbedrss_delete', 'id'=>'delete_'.$feed['id']));
			$return .= '</span></div>';	
		}
		$return .= '</div>';	
		return $return;
	}
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_form($id=''){
	if (api_is_allowed_to_edit()){
		if ($id <> ''){
			$tabbedrss_info = get_tabbedrss($id);
			$forminfo = $tabbedrss_info[$id];			
		}
	?>
	<form>
	<input name="id" type="hidden" id="tabbedrssid" value="<?php echo $id; ?>" />
	<div class="row">
		<div class="form_header"><?php echo get_lang('AddTabbedRSS');?></div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span><?php echo get_lang('TabbedRssTitle');?>
		</div>
		<div class="formw"><input size="50" name="title" id="title" type="text" value="<?php echo $forminfo['title']; ?>">
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span><?php echo get_lang('TabbedRssUrl');?>
		</div>
		<div class="formw"><input size="50" name="url" id="url" type="text" value="<?php echo $forminfo['url']; ?>">
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span><?php echo get_lang('TabbedRssNumberItems');?>
		</div>
		<div class="formw"><input size="50" name="number_items" id="number_items" type="text" value="<?php echo $forminfo['number_items']; ?>">
		</div>
	</div>	
	<div class="row">
		<div class="label">
		</div>
		<div class="formw">	<button class="add" id="tabbedrss_store" type="submit"><?php echo get_lang('TabbedRssStore');?></button>
		</div>
	</div>	
	<div>&nbsp;</div>		
	<?php
	}
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function get_tabbedrss($id){
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_tabbedrss = Database :: get_course_table('tabbedrss');
	} else {
		$table_tabbedrss = Database :: get_main_table('tabbedrss');
	}

	$sql = "SELECT * FROM $table_tabbedrss";
	if ($id <> ''){
		$sql .= "WHERE id='".Database::escape_string($id)."'";	
	}
	$sql .= ' ORDER BY id ASC';
	$result = Database::query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_array($result,'ASSOC')){
		$return[$row['id']] = $row;	
	}
	return $return;
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_store($values){
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_tabbedrss = Database :: get_course_table('tabbedrss');
	} else {
		$table_tabbedrss = Database :: get_main_table('tabbedrss');
	}
	if (api_is_allowed_to_edit()){
		if (empty($values['id'])){
			$sql = "INSERT INTO $table_tabbedrss (title, url, number_items) VALUES('".Database::escape_string($values['title'])."', '".Database::escape_string($values['url'])."', '".Database::escape_string($values['number_items'])."')";
			$result = Database::query($sql, __FILE__, __LINE__);
		} else {
			$sql = "UPDATE $table_tabbedrss SET title = '".Database::escape_string($values['title'])."', url = '".Database::escape_string($values['url'])."', number_items = '".Database::escape_string($values['number_items'])."' WHERE id = '".Database::escape_string($values['id'])."'";
			$result = Database::query($sql, __FILE__, __LINE__);			
		}	

		echo tabbedrss_management_interface();
	}
}

/**
 *
 * @version Dokeos 2.0
 * @since Februari 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function tabbedrss_delete($tabbedrss_id){
	global $_course;

	// Database table definition
	if (!empty($_course) AND is_array($_course)){
		$table_tabbedrss = Database :: get_course_table('tabbedrss');
	} else {
		$table_tabbedrss = Database :: get_main_table('tabbedrss');
	}
	
	if (api_is_allowed_to_edit()){
		$sql = "DELETE FROM $table_tabbedrss WHERE id = '".Database::escape_string($tabbedrss_id)."'";
		$result = Database::query($sql, __FILE__, __LINE__);	

		echo tabbedrss_management_interface();
	}
}


function get_rss_content($url, $number_of_items){
	// include the magpie RSS reader
	require_once '../rss/includes/rss_fetch.inc';
	
	// fetching the RSS feed
	$rss = '';
	$rss = fetch_rss(trim($url));

	// initialisation of the counter
	$counter = 1;
	
	// displaying the RSS feed elements
	echo '<ul>';
	foreach ($rss->items as $item ) 
	{
		if($counter <= $number_of_items OR $number_of_items <= 0) {	
			echo "<li><a href=".$item['link'].">".$item['title']."</a></li>\n";
		}
		$counter++;
	}
	echo "</ul>";		
}
?>
