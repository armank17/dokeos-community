<?php
// name of the language file that needs to be included
$language_file='admin';

// resetting the course id
$cidReset=true;

// including some necessary dokeos files
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'import.lib.php';
require_once api_get_path(LIBRARY_PATH).'urlmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

if ($_POST['formSent'] AND $_FILES['import_file']['size'] !== 0) {
    $file_type = $_POST['file_type'];
    $access_url_id = $_POST['access_url_id'];
    Security::clear_token();
    $tok = Security::get_token();
    $allowed_file_mimetype = array('csv','xml');	
    $error_kind_file = false;
   
    $ext_import_file = substr($_FILES['import_file']['name'],(strrpos($_FILES['import_file']['name'],'.')+1));
    if (in_array($ext_import_file,$allowed_file_mimetype)) {
        if (strcmp($file_type, 'csv') === 0 && $ext_import_file ==  $allowed_file_mimetype[0]) { 
            $users	= parse_csv_data($_FILES['import_file']['tmp_name']);		            
            $errors = validate_data($users);		
            $error_kind_file = false;
        } elseif (strcmp($file_type, 'xml') === 0 && $ext_import_file == $allowed_file_mimetype[1]) { 
            $users = parse_xml_data($_FILES['import_file']['tmp_name']);
            $errors = validate_data($users);
            $error_kind_file = false;
        } else {
            $error_kind_file = true;
            $error_message = get_lang('YouMustImportAFileAccordingToSelectedOption');
            header('Location: '.api_get_self().'?warn='.urlencode($error_message).'&amp;file_type='.$file_type.'&amp;sec_token='.$tok);
            exit ();
        }
    } 
    else {
        $error_kind_file = true;
    }

    // List user id whith error.
    $user_id_error = array();
    if (is_array($errors)) {
        foreach ($errors as $my_errors) {
            $user_id_error[] = $my_errors['UserName'];
        }
    }
    
    if (is_array($users)) {
        foreach ($users as $my_user) {
            if (!in_array($my_user['UserName'], $user_id_error)) {
                    $users_to_insert[] = $my_user;
            }
        }
    }

    if (strcmp($file_type, 'csv') === 0) {
        $result = save_data($users_to_insert, array($access_url_id));		
    } elseif (strcmp($file_type, 'xml') === 0) {		
        $result = save_data($users_to_insert, array($access_url_id));		
    } else {		
        $error_messag = get_lang('YouMustImportAFileAccordingToSelectedOption');
    }	

    if (count($errors) > 0) {
        //$see_message_import = get_lang('FileImportedJustUsersThatAreNotRegistered');
    } else {
        //$see_message_import = get_lang('FileImported');
    }

    if (count($errors) != 0) {
        $warning_message = '<ul>';
        foreach ($errors as $index => $error_user) {
            $warning_message .= '<li><b>'.$error_user['error'].'</b>: '.$error_user['UserName'].'</li>';
        }
        $warning_message .= '</ul>';
    } 

    if (!empty($result)) {
        $confirmation_message = '<ul>';
        foreach ($result as $index => $res) {
            $confirmation_message .= '<li><b>'.$res['message'].'</b>: '.$res['UserName'].'</li>';
        }
        $confirmation_message .= '</ul>';
    }
    
    // if the warning message is too long then we display the warning message trough a session
    if (api_strlen($warning_message) > 150) {
        $_SESSION['session_message_import_users'] = $warning_message;
        $warning_message = 'session_message';
    }

    if ($error_kind_file) {
        $error_message = get_lang('YouMustImportAFileAccordingToSelectedOption');
    } else {
        header('Location: '.api_get_path(WEB_CODE_PATH).'admin/access_url_add_users_in_batch_to_sites.php?action=show_message&warn='.urlencode($warning_message).'&message='.urlencode($see_message_import).'&conf='.urlencode($confirmation_message).'&sec_token='.$tok);
        exit;	
    }
}

$url_list = UrlManager::get_url_data();
$urls = array();
foreach ($url_list as $url_obj) {
    if ($url_obj['active']==1) {
        $urls[$url_obj[0]] = $url_obj[1];
    }
}

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

$tool_name = get_lang('ImportUsersToURL');

// Display header
Display::display_header($tool_name);
?>
<div class="actions">
<?php   
echo '<a href="'.api_get_path(WEB_CODE_PATH).'admin/access_urls.php">'.Display::return_icon('pixel.gif',get_lang('Back'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Back').'</a>';
?>
</div>

<?php 
    // start the content div
echo '<div id="content" class="maxcontent">';

if($_FILES['import_file']['size'] == 0 AND $_POST) {
    Display::display_error_message(get_lang('ThisFieldIsRequired'),false,true);
}
else if (!empty($error_message)) {
    Display::display_error_message($error_message, false, true);
} 
else if (!empty($_GET['warn'])) {
    $error_message = urldecode($_GET['warn']);
    Display :: display_error_message($error_message,false, true);
}

if (!empty($_GET['conf'])) {
    $message = urldecode($_GET['conf']);
    Display :: display_confirmation_message($message, false, true);
}

$form = new FormValidator('user_import', 'POST');
$form->addElement('header', '', $tool_name);
$form->addElement('hidden', 'formSent');

$form->addElement('select', 'access_url_id', get_lang('SelectUrl'), $urls);
$form->addRule('access_url_id', get_lang('ThisFieldIsRequired'), 'required');

$form->addElement('file', 'import_file', get_lang('ImportFileLocation'));
$form->addRule('import_file', get_lang('ThisFieldIsRequired'), 'required');
$allowed_file_types = array ('csv');

$form->addRule('import_file', get_lang('InvalidExtension').' ('.implode(',', $allowed_file_types).')', 'filetype', $allowed_file_types);
//$form->addElement('radio', 'file_type', get_lang('FileType'), 'XML', 'xml');
$form->addElement('radio', 'file_type', get_lang('FileType'), 'CSV', 'csv');
$form->addElement('style_submit_button', 'submit', get_lang('Import'), 'class="save"');

// default values
$defaults['formSent'] = 1;
$defaults['file_type'] = 'csv';
$form->setDefaults($defaults);

// display the form
$form->display();

?>
<p><?php echo get_lang('CSVMustLookLike').' ('.get_lang('MandatoryFields').')'; ?> :</p>
    <blockquote>
    <pre>
        <b>UserName</b>;
        user01;
        user02;
        ...
    </pre>
    </blockquote>
<!--p><php echo get_lang('XMLMustLookLike').' ('.get_lang('MandatoryFields').')'; ?> :</p>
    <blockquote>
    <pre>
            &lt;?xml version=&quot;1.0&quot; encoding=&quot;<?php echo api_refine_encoding_id(api_get_system_encoding()); ?>&quot;?&gt;
            &lt;Contacts&gt;
            &lt;Contact&gt;
            <b>&lt;UserName&gt;user01&lt;/UserName&gt;</b>
            &lt;/Contact&gt;
            &lt;Contact&gt;
            <b>&lt;UserName&gt;user02&lt;/UserName&gt;</b>
            &lt;/Contact&gt;			
            &lt;/Contacts&gt;
    </pre>
    </blockquote-->
<?php
// close the content div
echo '</div>';

// display the footer
Display :: display_footer();

function validate_data($users) {
    $errors = array();        
    // 1. Check if mandatory fields are set.
    $mandatory_fields = array('UserName');	
    foreach ($users as $index => $user) {
        foreach ($mandatory_fields as $field) {
            if (empty($user[$field])) {
                $user['error'] = get_lang($field.'Mandatory');
                $errors[] = $user;
            }
        }
        // 2. Check username exists.
        if (UserManager::is_username_available($user['UserName'])) {
            $user['error'] = get_lang('UserDoesntExist');
            $errors[] = $user;                    
        }
    }
    return $errors;
}

/**
 * Read the CSV-file
 * @param string $file Path to the CSV-file
 * @return array All userinformation read from the file
 */
function parse_csv_data($file) {
    $users = Import :: csv_to_array($file);
    foreach ($users as $index => $user) {
            $users[$index] = $user;
    }
    return $users;
}
/**
 * XML-parser: handle start of element
 */
function element_start($parser, $data) {
    global $current_tag;
    if ($data) {
        $current_tag = api_utf8_decode($data);
    }    
}

/**
 * XML-parser: handle end of element
 */
function element_end($parser, $data) {
    $data = api_utf8_decode($data);
    global $current_value;    
    if ($data) {
        $user[$data] = $current_value;
    }
}

/**
 * XML-parser: handle character data
 */
function character_data($parser, $data) {
    $data = trim(api_utf8_decode($data));
    global $current_value;
    $current_value = $data;
}

/**
 * Read the XML-file
 * @param string $file Path to the XML-file
 * @return array All userinformation read from the file
 */
function parse_xml_data($file) {
    global $current_tag;
    global $current_value;
    global $user;
    global $users;
    $users = array();
    $parser = xml_parser_create('UTF-8');
    xml_set_element_handler($parser, 'element_start', 'element_end');
    xml_set_character_data_handler($parser, 'character_data');
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
    xml_parse($parser, api_utf8_encode_xml(file_get_contents($file)));
    xml_parser_free($parser);
    return $users;
}

function save_data($users, $url_list) {
    if (is_array($users)) {        
        $user_list = array();
        foreach ($users as $user) {
            $user_list[] = UserManager::get_user_id_from_username($user['UserName']);
            $message[] = array('message' => get_lang('AddedUsernameToUrl'), 'UserName' => $user['UserName']); 
        }
        UrlManager::add_users_to_urls($user_list, $url_list);
        return $message;
    }
}