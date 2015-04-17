<?php

function update_order()
{
    $type = $_REQUEST['type'];
    // Database table product
    $product_table = Database::get_main_table(TABLE_MAIN_PRODUCT);
    if ($type=="tool")
        $product_table = Database::get_main_table(TABLE_MAIN_PRODUCT_TOOL);
    
    $cont = 1;
    foreach ($_REQUEST[$type] as $item) {
        $item = intval($item);
        $sql = "UPDATE {$product_table} SET position = {$cont} WHERE id = {$item}";
        if ($type=="tool")
        $sql = "UPDATE {$product_table} SET position = {$cont}, product_id = {$_REQUEST['key']} WHERE id = {$item}";
        api_sql_query($sql, __FILE__, __LINE__);
        $cont++;
    }
}

function update_product_status()
{
    $id = intval($_REQUEST['id']);
    $status = intval($_REQUEST['status']);
    $product_table = Database::get_main_table(TABLE_MAIN_PRODUCT);
    $sql = "UPDATE {$product_table} SET status = {$status} WHERE id = {$id}";
    api_sql_query($sql, __FILE__, __LINE__);
}

function display_item_form() {
    echo '<div id="dialog-item-form" style="display:none;">';
    show_item_form(array());
    echo '</div>';
}

function display_tool_form() {
    echo '<div id="dialog-tool-form" style="display:none;">';
    show_tool_form(array());
    echo '</div>';
}

// item form
function show_item_form($input_values, $options=array())
{
    // initiate the object
    $form = new FormValidator('item_form', 'post', $_SERVER['REQUEST_URI']);
    
    // the header for the form
//    if (!$dialog) {
//        if ($_GET['action'] == 'myadd')
//            $form->addElement('header', '', get_lang ( 'AgendaAdd' ) );
//        if ($_GET['action'] == 'myedit')
//            $form->addElement('header', '', get_lang ( 'AgendaEdit' ) );
//    }
    
    $item_id = $input_values && is_numeric($input_values['item_id'])?intval($input_values['item_id']):'';
    $parent_id = $input_values && is_numeric($input_values['parent_id'])?intval($input_values['parent_id']):0;
    // hidden inputs
    $form->addElement('hidden', 'item_id', $item_id, array('id'=>'item_id'));
    $form->addElement('hidden', 'parent_id', $parent_id, array('id'=>'parent_id'));
    
    // The title
	$form->addElement('text', 'name', get_lang ( 'Name' ), array ('maxlength'=>'250', 'size'=>'50', 'class'=>'focus'));
//	$form->addElement('text', 'url', get_lang ( 'URL' ), array ('maxlength'=>'250', 'size'=>'50', 'id'=>'url'));
//    $form->addElement('html', '<div id="action-bottom">');
    $form->addElement('style_submit_button', 'submit_item_product', get_lang ( 'SaveEvent' ) ,'class="save"');                
//    $form->addElement('html', '</div>' );

	// when we are editing we have to overwrite all this with the information form the event we are editing
	if (!empty($input_values))
	{
		$defaults = $input_values;
	}

	// The rules (required fields)
	$form->addRule ('name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

	// The validation or display
	if ($form->validate ()) {
		$post = $form->exportValues ();
//        $check = Security::check_token('post');
        if (isset($_GET['action'])) { unset($_GET['action']); }
        //if($check) {
        if (isset ( $_POST ['submit_item_product'] )) {
            $values = array();
            if ($post['item_id'])
                $values['id'] = intval($post['item_id']);
//            if ($parent_id)
            $values['name'] = Database::escape_string($post['name']);
            $values['key'] = Database::escape_string(api_friendly_url($post['name']));
            $values['parent'] = intval($post['parent_id']);
            
            save_product($values);
        }
        Security::clear_token();
        header('Location: '.api_get_path(WEB_VIEW_PATH).'main/admin/product_list.php');
        exit;
	} else {
        if (isset($_POST['submit'])) {
                Security::clear_token();
        }
        $token = Security::get_token();
        $form->addElement('hidden', 'sec_token');
        $defaults['sec_token'] = $token;

		$form->setDefaults ($defaults);
		$form->display ();  
	}
}

// tool form
function show_tool_form($input_values)
{
    $form = new FormValidator('tool_form', 'post', $_SERVER['REQUEST_URI']);
    
    $tool_id = $input_values && is_numeric($input_values['tool_id'])?intval($input_values['tool_id']):'';
    $category_id = $input_values && is_numeric($input_values['category_id'])?intval($input_values['category_id']):0;
    
    $form->addElement('hidden', 'tool_id', $tool_id, array('id'=>'item_id'));
    $form->addElement('hidden', 'category_id', $category_id, array('id'=>'category_id'));
    
	$form->addElement('text','tool_name',get_lang('Name'), array('id'=>'tool_name','maxlength'=>'250','size'=>'30','class'=>'focus'));
	$form->addElement('text','tool_url',get_lang('URL'), array('id'=>'tool_url','maxlength'=>'250','size'=>'50'));
    $type = array('_self'=>get_lang('_Self'), '_blank'=>get_lang('_Blank'));
    $form->addElement('select', 'tool_type', get_lang('Type'), $type, array('id' => 'tool_type'));
    $form->addElement('file', 'tool_icon', get_lang('AddIcon'));
    $allowed_icon_types = array('jpg', 'jpeg', 'png', 'gif');

    $form->addRule('tool_icon', get_lang('OnlyImagesAllowed').' ('.implode(',', $allowed_icon_types).')', 'filetype', $allowed_icon_types);
    
    $form->addElement('style_submit_button', 'submit_tool_product', get_lang('SaveTool'), 'class="save"');

	// when we are editing we have to overwrite all this with the information form the event we are editing
	if (!empty($input_values))
	{
		$defaults = $input_values;
	}

	// The rules (required fields)
	$form->addRule ('tool_name', get_lang('ThisFieldIsRequired'), 'required' );

	// The validation or display
	if ($form->validate()) 
    {
		$post = $form->exportValues();
        
		$picture_element =& $form->getElement('tool_icon');
		$picture = $picture_element->getValue();
        $image = 'default_tool_icon.png';

        if (isset($_POST['submit_tool_product']))
        {
            $values = array();
            if ($post['tool_id'])
                $values['id'] = intval($post['tool_id']);
            
            if(!empty($picture))
            {
                $sys_path = api_get_path(WEB_CODE_PATH);
                $temp_path = $sys_path.'upload/tool/icon/';
                if(move_uploaded_file($_FILES['tool_icon']['tmp_name'], $temp_path.$_FILES['tool_icon']['name']))
                    $image = $_FILES['tool_icon']['name'];
            }
            
            $values['name'] = Database::escape_string($post['tool_name']);
            $values['link'] = Database::escape_string($post['tool_url']);
            $values['image'] = 'default_tool_icon.png';
            $values['target'] = Database::escape_string($post['tool_type']);
            $values['key'] = Database::escape_string(api_friendly_url($post['tool_name']));

            $tool_id = save_tool($values);
            if($tool_id)
                save_product_tool($tool_id, $post['category_id']);
//            echo '<script type="text/javascript">location.href="'.api_get_path(WEB_CODE_PATH).'admin/product_list.php";</script>';
        }
	} else {
        if (isset($_POST['submit'])) {
                Security::clear_token();
        }
        $token = Security::get_token();
        $form->addElement('hidden', 'sec_token');
        $defaults['sec_token'] = $token;

		$form->setDefaults ($defaults);
		$form->display ();  
	}
}

function save_product($values)
{
    $product_table = Database::get_main_table(TABLE_MAIN_PRODUCT);
    
    $string_values = "";
    if (isset($values['id']))
    {
        $id = $values['id'];
        unset($values['id']);
    }  else {
        $sql = "SELECT position FROM {$product_table} WHERE parent = {$values['parent']} ORDER BY position DESC";
        $res = api_sql_query($sql, __FILE__, __LINE__);
        
        if (Database::num_rows($res) > 0) {
            $position = intval(Database::num_rows($res))+1;
            $values['position'] = $position;
        }
    }

    foreach ($values as $ind=>$val)
    {
        $string_values .= is_string($val)?"`{$ind}` = '{$val}', ":"`{$ind}` = {$val}, ";
    }
    $string_values = substr($string_values, 0, -2);
    
    $sql = "INSERT INTO {$product_table} SET ";
    $sql .= $string_values;
    
    if ($id)
    {
        $sql = "UPDATE {$product_table}";
        $sql .= $string_values;
        $sql .= "WHERE id = {$id}";
    }
    api_sql_query($sql, __FILE__, __LINE__);
}

function save_tool($values)
{
    $tool_table = Database::get_main_table(TABLE_MAIN_TOOL);
    
    if(isset($values['id']) && !empty($values['id']))
    {
        $id = $values['id'];
        unset($values['id']);
    }

    $string_values = "";
    foreach ($values as $ind=>$val)
        $string_values .= is_string($val)?"`{$ind}` = '{$val}', ":"`{$ind}` = {$val}, ";
    $string_values = substr($string_values, 0, -2);
    
    $sql = (isset($id))?"UPDATE {$tool_table} ".$string_values." WHERE id = {$id}":"INSERT INTO {$tool_table} SET ".$string_values;
    Database::query($sql, __FILE__, __LINE__);
    
    echo '<hr><pre>';
    print_r($sql);
    echo '</pre><hr>';
    $insert_id = FALSE;
    if(!$id)
        $insert_id = Database::insert_id();
    return $insert_id;
}

function save_product_tool($tool, $category)
{
    $product_tool_table = Database::get_main_table(TABLE_MAIN_PRODUCT_TOOL);
    $sql = "SELECT * FROM {$product_tool_table} AS pt WHERE pt.product_id = {$category}";
    $res = Database::query($sql);
    $tools = intval(Database::num_rows($res))+1;
    $sql = "INSERT INTO {$product_tool_table} SET `product_id` = {$category}, `tool_id` = {$tool}, `position` = {$tools}";
    
    echo '<hr><pre>';
    print_r($sql);
    echo '</pre><hr>';
    return Database::query($sql);
}

function delete_item_product($item_id)
{
    $product_table = Database::get_main_table(TABLE_MAIN_PRODUCT);
    
    $sql = "DELETE FROM {$product_table} WHERE id = {$item_id}";
    api_sql_query($sql, __FILE__, __LINE__);
}

function save_item_tool($values)
{
    $product_table = Database::get_main_table(TABLE_MAIN_TOOL);
    
    $string_values = "";
    if (isset($values['id']))
    {
        $id = $values['id'];
        unset($values['id']);
    }
    $string_values = "";
    foreach ($values as $ind=>$val)
    {
        $string_values .= "{$ind} = {$val} ";
    }
    
    $sql = "INSER INTO {$product_table} SET ";
    $sql .= $string_values;
    
    if ($id)
    {
        $sql = "UPDATE {$product_table}";
        $sql .= $string_values;
        $sql .= "WHERE id = {$id}";
    }
    api_sql_query($sql, __FILE__, __LINE__);
}

function delete_item_tool($item_id)
{
    $product_table = Database::get_main_table(TABLE_MAIN_TOOL);
    
    $sql = "DELETE FROM {$product_table} WHERE id = {$item_id}";
    api_sql_query($sql, __FILE__, __LINE__);
}

?>
