<?php // $Id: edit_document.php 22259 2009-07-20 18:56:45Z ivantcholakov $

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
* This file allows editing documents.
*
* Based on create_document, this file allows
* - edit name
* - edit comments
* - edit metadata (requires a document table entry)
* - edit html content (only for htm/html files)
*
* For all files
* - show editable name field
* - show editable comments field
* Additionally, for html and text files
* - show RTE
*
* Remember, all files and folders must always have an entry in the
* database, regardless of wether they are visible/invisible, have
* comments or not.
*
* @package dokeos.document
* @todo improve script structure (FormValidator is used to display form, but
* not for validation at the moment)
==============================================================================
*/

// name of the language file that needs to be included
$language_file = array('document','gradebook');

// include the global Dokeos file
require_once '../inc/global.inc.php';

// include additional libraries
require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

// section (for the tabs)
$this_section = SECTION_COURSES;

define('DOKEOS_DOCUMENT', true);

$_SESSION['whereami'] = 'document/create';


if (api_is_in_group()) {
	$group_properties = GroupManager::get_group_properties($_SESSION['_gid']);
}

$file = $_GET['file'];
$doc=basename($file);
$dir=Security::remove_XSS($_GET['curdirpath']);
if (empty($dir)) {
  $dir='/'; 
}
$currentEditor = strtolower(api_get_setting('use_default_editor'));
   if ($currentEditor <> 'fckeditor') {
        $htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery_modal/jquery.simplemodal-1.4.2.js" language="javascript"></script>';
   }
//I'm in the certification module?
$is_certificate_mode = DocumentManager::is_certificate_mode($dir);
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css">
        input.error { border: 1px solid red; }
    </style>

    <script type="text/javascript">

    $(document).ready(function(){
        if ($("#formEdit").length > 0) {
            $("#formEdit").validate({
                rules: {
                    newTitle: {
                      required: true
                    }
                },
                messages: {
                    newTitle: {
                        required: "<img src=\"'.  api_get_path(WEB_IMG_PATH).'exclamation.png\" title=\''.get_lang('Required').'\' />"
                    }
                }
            });
        }
   });



</script>';
$file_name = $doc;

$baseServDir = api_get_path(SYS_COURSE_PATH);
$baseServUrl = $_configuration['url_append']."/";
$courseDir   = $_course['path']."/document";
$baseWorkDir = $baseServDir.$courseDir;
$group_document = false;

$current_session_id = api_get_session_id();
$doc_tree= explode('/', $file);
$count_dir = count($doc_tree) -2; // "2" because at the begin and end there are 2 "/"
// Level correction for group documents.
if (!empty($group_properties['directory']))
{
	$count_dir = $count_dir > 0 ? $count_dir - 1 : 0;
}
$relative_url='';
for($i=0;$i<($count_dir);$i++)
{
	$relative_url.='../';
}

$is_allowed_to_edit = api_is_allowed_to_edit(null,true);

$html_editor_config = array(
	'ToolbarSet' => ($is_allowed_to_edit ? 'Documents' :'DocumentsStudent'),
	'Width' => '100%',
	'Height' => '650',
	'FullPage' => true,
	'InDocument' => true,
	'CreateDocumentDir' => $relative_url,
	'CreateDocumentWebDir' => (empty($group_properties['directory']))
		? api_get_path('WEB_COURSE_PATH').$_course['path'].'/document/'
		: api_get_path('WEB_COURSE_PATH').api_get_course_path().'/document'.$group_properties['directory'].'/',
	'BaseHref' =>  api_get_path('WEB_COURSE_PATH').$_course['path'].'/document'.$dir
);

$use_document_title = (api_get_setting('use_document_title')=='true') ? true : false;
$noPHP_SELF=true;

/* please do not modify this dirname formatting */

if(strstr($dir,'..'))
{
	$dir='/';
}

if($dir[0] == '.')
{
	$dir=substr($dir,1);
}

if($dir[0] != '/')
{
	$dir='/'.$dir;
}

if($dir[strlen($dir)-1] != '/')
{
	$dir.='/';
}

$filepath=api_get_path('SYS_COURSE_PATH').$_course['path'].'/document'.$dir;

if(!is_dir($filepath))
{
	$filepath=api_get_path('SYS_COURSE_PATH').$_course['path'].'/document/';
	$dir='/';
}

// Database table definition
$dbTable = Database::get_course_table(TABLE_DOCUMENT);

if(!empty($_SESSION['_gid'])) {
	$req_gid = '&gidReq='.$_SESSION['_gid'];
	$interbreadcrumb[]= array ("url"=>"../group/group_space.php?gidReq=".$_SESSION['_gid'], "name"=> get_lang('GroupSpace'));
	$group_document = true;
	$noPHP_SELF=true;
}
$my_cur_dir_path=Security::remove_XSS($_GET['curdirpath']);

if (!$is_certificate_mode)
	$interbreadcrumb[]=array("url"=>"./document.php?curdirpath=".urlencode($my_cur_dir_path).$req_gid, "name"=> get_lang('Documents'));
else
	$interbreadcrumb[]= array (	'url' => '../gradebook/'.$_SESSION['gradebook_dest'], 'name' => get_lang('Gradebook'));


$is_allowedToEdit = is_allowed_to_edit() || $_SESSION['group_member_with_upload_rights'];

if(!$is_allowedToEdit)
{
	api_not_allowed(true);
}


$user_id = api_get_user_id();
event_access_tool(TOOL_DOCUMENT);

if (!is_allowed_to_edit())
{
	if(DocumentManager::check_readonly($_course,$user_id,$file))
	{
		api_not_allowed();
	}

}




/*
==============================================================================
	   MAIN TOOL CODE
==============================================================================
*/

/*
------------------------------------------------------------------------------
	General functions
------------------------------------------------------------------------------
*/



/*
------------------------------------------------------------------------------
	Workhorse functions

	These do the actual work that is expected from of this tool, other functions
	are only there to support these ones.
------------------------------------------------------------------------------
*/

/**
	This function changes the name of a certain file.
	It needs no global variables, it takes all info from parameters.
	It returns nothing.
*/
function change_name($baseWorkDir, $sourceFile, $renameTo, $dir, $doc)
{
	$file_name_for_change = $baseWorkDir.$dir.$sourceFile;
	//api_display_debug_info("call my_rename: params $file_name_for_change, $renameTo");
    	$renameTo = disable_dangerous_file($renameTo); //avoid renaming to .htaccess file
	$renameTo = my_rename($file_name_for_change, stripslashes($renameTo)); //fileManage API

	if ($renameTo)
	{
		if (isset($dir) && $dir != "")
		{
			$sourceFile = $dir.$sourceFile;
			$new_full_file_name = dirname($sourceFile)."/".$renameTo;
		}
		else
		{
			$sourceFile = "/".$sourceFile;
			$new_full_file_name = "/".$renameTo;
		}

		update_db_info("update", $sourceFile, $new_full_file_name); //fileManage API
		$name_changed = get_lang("ElRen");
		$info_message = get_lang('fileModified');

		$GLOBALS['file_name'] = $renameTo;
		$GLOBALS['doc'] = $renameTo;

		return $info_message;
	}
	else
	{
		$dialogBox = get_lang('FileExists');

		/* return to step 1 */
		$rename = $sourceFile;
		unset($sourceFile);
	}
}

/*
------------------------------------------------------------------------------
	Code to change the comment
------------------------------------------------------------------------------
	Step 2. React on POST data
	(Step 1 see below)
*/
if (isset($_POST['newComment']))
{
	//to try to fix the path if it is wrong
	$commentPath = str_replace("//", "/", Database::escape_string(Security::remove_XSS($_POST['commentPath'])));
        $newComment = str_replace('?', '', Database::escape_string(Security::remove_XSS($_POST['newComment']))); // remove spaces
	$newTitle = str_replace('?', '', Database::escape_string(Security::remove_XSS($_POST['newTitle']))); // remove spaces
	// Check if there is already a record for this file in the DB
	$result = Database::query ("SELECT * FROM $dbTable WHERE path LIKE BINARY '".$commentPath."'",__FILE__,__LINE__);
	while($row = Database::fetch_array($result, 'ASSOC'))
	{
		$attribute['path'      ] = $row['path' ];
		$attribute['comment'   ] = $row['title'];
        $real_document_id  = $row['id'];
	}
	//Determine the correct query to the DB
	//new code always keeps document in database
	$query = "UPDATE $dbTable
		SET comment='".$newComment."', title='".$newTitle."'
		WHERE path
		LIKE BINARY '".$commentPath."'";
	Database::query($query,__FILE__,__LINE__);
    //$document_path
	$oldComment = $newComment;
	$oldTitle = $newTitle;
	$comments_updated = get_lang('ComMod');
	$info_message = get_lang('fileModified').'';
        $_SESSION["show_message"] = $info_message;
        
    $old_document_path = substr($attribute['path'], 1);
    $file_path_info = explode("/",$old_document_path);
    $count_file_info = count($file_path_info);
    $my_file_name = $file_path_info[$count_file_info - 1];
    unset($file_path_info[$count_file_info - 1]);
    $base_file_path = implode("/",$file_path_info);
    if (!empty($base_file_path)) {
        $real_base_file_path = '/'.$base_file_path.'/';
    } else {
         $real_base_file_path = '/';
    }

    // File info for rename it
    $old_real_path = $real_base_file_path.$my_file_name;
    $new_real_path = $real_base_file_path.disable_dangerous_file(replace_dangerous_char($my_file_name, 'strict'));

    // Path files for create a copy
    $normal_path_old_encoding = $filepath.$my_file_name;
    $my_new_file_name = disable_dangerous_file(replace_dangerous_char($my_file_name, 'strict'));
    $new_path_without_weird_characters = $filepath.$my_new_file_name;

    if (file_exists($normal_path_old_encoding) && !is_dir($normal_path_old_encoding)) {
        if(copy($normal_path_old_encoding, $new_path_without_weird_characters)) {
            $query = "UPDATE $dbTable
                SET path=REPLACE(path,'".Database::escape_string($old_real_path)."','".Database::escape_string($new_real_path)."')
                WHERE id = '".$real_document_id."'";
            Database::query($query,__FILE__,__LINE__);
        }
    }
    $curdirpath = (isset($_GET['curdirpath'])) ? '&curdirpath='.$_GET['curdirpath'] : '';
    $url = api_get_path(WEB_CODE_PATH).'document/document.php?'.api_get_cidreq().$curdirpath;
    header("Location: " . $url );
}

/*
------------------------------------------------------------------------------
	Code to change the name
------------------------------------------------------------------------------
	Step 2. react on POST data - change the name
	(Step 1 see below)
*/

if (isset($_POST['renameTo'])) {
    //$info_message = change_name($baseWorkDir, $_GET['sourceFile'], $_POST['renameTo'], $dir, $doc);
    $_SESSION["show_message"] = change_name($baseWorkDir, $_GET['sourceFile'], $_POST['renameTo'], $dir, $doc);
    $curdirpath = (isset($_GET['curdirpath'])) ? '&curdirpath='.$_GET['curdirpath'] : '';
    $url = api_get_path(WEB_CODE_PATH).'document/document.php?'.api_get_cidreq().$curdirpath;
    header("Location: " . $url );
    //assume name change was successful
}

/*
------------------------------------------------------------------------------
	Code to change the comment
------------------------------------------------------------------------------
	Step 1. Create dialog box.
*/

/** TODO check if this code is still used **/
/* Search the old comment */  // RH: metadata: added 'id,'
$result = Database::query("SELECT id,comment,title FROM $dbTable WHERE path LIKE BINARY '$dir$doc'",__FILE__,__LINE__);

$message = "<i>Debug info</i><br>directory = $dir<br>";
$message .= "document = $file_name<br>";
$message .= "comments file = " . $file . "<br>";

while ($row = Database::fetch_array($result, 'ASSOC')) {
    $oldComment = $row['comment'];
    $oldTitle = Security :: remove_XSS($row['title']);
    $docId = $row['id'];  // RH: metadata
}

/*
------------------------------------------------------------------------------
	WYSIWYG HTML EDITOR - Program Logic
------------------------------------------------------------------------------
*/

if($is_allowedToEdit)
{
	if($_POST['formSent']==1)
	{
		if(isset($_POST['renameTo']))
		{
			$_POST['filename']=disable_dangerous_file($_POST['renameTo']);

			$extension=explode('.',$_POST['filename']);
			$extension=$extension[sizeof($extension)-1];

			$_POST['filename']=str_replace('.'.$extension,'',$_POST['filename']);
		}

		$filename=stripslashes($_POST['filename']);

		$texte=trim(str_replace(array("\r","\n"),"",stripslashes($_POST['texte'])));
		$texte=Security::remove_XSS($texte,COURSEMANAGERLOWSECURITY);

                // add template css path if it doesn't exist
                if (strpos($texte, '/css/templates.css') === false) {
                    //$texte=str_replace('</head>','<link rel="stylesheet" href="'.api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/css/templates.css" type="text/css" /></head>',$texte);
		}

                // add js path if it doesn't exist
                $js = '';
                if (strpos($texte, '/javascript/jquery.highlight.js') === false) { 
                    $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
                    $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js" language="javascript"></script>'.PHP_EOL;
                    if (api_get_setting('show_glossary_in_documents') != 'none') {   
						
						// If is allowed the use of themes inside of this course
                            if(api_get_setting('allow_course_theme')=='true' && api_get_course_setting('course_theme', null, true)!=''){  
                                $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_course_setting('course_theme', null, true).'/default.css" rel="stylesheet" />'; 
                            }else{
                                // Else the stylesheet used will be the platform stylesheet   
                                $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_setting('stylesheets').'/default.css" rel="stylesheet" />'; 
                            }
                            
                            $js .='<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" language="javascript"></script>';
                            $js .='<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />'; 

                        if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                        } else {
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                        }
                    }
				}
                $texte = str_replace('</head>', $js.'</head>', $texte);

		// RH commented: $filename=replace_dangerous_char($filename,'strict');
		// What??
		//if($_POST['extension'] != 'htm' && $_POST['extension'] != 'html')
		//{
			//$extension='html';
		//}
		//else
		//{
			$extension = $_POST['extension'];
		//}

		$file=$dir.$filename.'.'.$extension;
		$read_only_flag=$_POST['readonly'];
		if (!empty($read_only_flag))
		{
			$read_only_flag=1;
		}
		else
		{
			$read_only_flag=0;
		}

		$show_edit=$_SESSION['showedit'];
		//unset($_SESSION['showedit']);
		api_session_unregister('showedit');


		if(empty($filename))
		{
			$msgError=get_lang('NoFileName');
		}
		else
		{
			if ($read_only_flag==0)
			{
				if (!empty($texte))
				{
					if($fp=@fopen($filepath.$filename.'.'.$extension,'w'))
					{
						$texte = text_filter($texte);
						//if flv player, change absolute paht temporarely to prevent from erasing it in the following lines
						$texte = str_replace('flv=h','flv=h|',$texte);
						$texte = str_replace('flv=/','flv=/|',$texte);

						// change the path of mp3 to absolute
						// first regexp deals with ../../../ urls
						// Disabled by Ivan Tcholakov.
						//$texte = preg_replace("|(flashvars=\"file=)(\.+/)+|","$1".api_get_path(REL_COURSE_PATH).$_course['path'].'/document/',$texte);
						//second regexp deals with audio/ urls
						// Disabled by Ivan Tcholakov.
						//$texte = preg_replace("|(flashvars=\"file=)([^/]+)/|","$1".api_get_path(REL_COURSE_PATH).$_course['path'].'/document/$2/',$texte);


 						fputs($fp,$texte);
						fclose($fp);
						$perm = api_get_setting('permissions_for_new_directories');
						$perm = octdec(!empty($perm)?$perm:'0770');
						if(!is_dir($filepath.'css'))
						{
							mkdir($filepath.'css',$perm);
							$doc_id=add_document($_course,$dir.'css','folder',0,'css');
							api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'FolderCreated', $_user['user_id']);
							api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'invisible', $_user['user_id']);
						}

                                                if (!is_file($filepath.'css/templates.css')) {
                                                        //make a copy of the current css for the new document
                                                        copy(api_get_path(SYS_CODE_PATH).'css/'.api_get_setting('stylesheets').'/templates.css', $filepath.'css/templates.css');
                                                        $doc_id = add_document($_course, $dir.'css/templates.css', 'file', filesize($filepath.'css/templates.css'), 'templates.css');
                                                        api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'DocumentAdded', $_user['user_id']);
                                                        api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'invisible', $_user['user_id']);
                                                }

						// "WHAT'S NEW" notification: update table item_property (previously last_tooledit)
						$document_id = DocumentManager::get_document_id($_course,$file);
						if($document_id)
						{
							$file_size = filesize($filepath.$filename.'.'.$extension);
							update_existing_document($_course, $document_id,$file_size,$read_only_flag, $_POST['newTitle']);
							api_item_property_update($_course, TOOL_DOCUMENT, $document_id, 'DocumentUpdated', $_user['user_id']);
							//update parent folders
							item_property_update_on_folder($_course,$dir,$_user['user_id']);
							$dir= substr($dir,0,-1);
						//	header('Location: document.php?curdirpath='.urlencode($dir));
							if($extension == 'htm' || $extension == 'html' || $extension == 'gif' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'png')
							{
							echo '<script type="text/javascript">window.location.href="showinframes.php?'.api_get_cidReq().'&curdirpath='.urlencode($dir).'&file='.$dir.'/'.$filename.'.'.$extension.'";</script>';
							}
							else
							{
							echo '<script type="text/javascript">window.location.href="document.php?curdirpath='.urlencode($dir).'";</script>';
							}
							exit ();
						}
						else
						{
						//$msgError=get_lang('Impossible');
						}
					}
					else
					{
						$msgError=get_lang('Impossible');
					}
				}
				else
				{
					if (is_file($filepath.$filename.'.'.$extension)) {
						$file_size = filesize($filepath.$filename.'.'.$extension);
						$document_id = DocumentManager::get_document_id($_course,$file);
						if ($document_id) {
							$updatestatus = update_existing_document($_course, $document_id,$file_size,$read_only_flag, $_POST['newTitle']);
							if($updatestatus)
							{
							echo '<script type="text/javascript">window.location.href="document.php?curdirpath='.urlencode($dir).'";</script>';
							}
						}
					}
				}
			}
			else
			{

				if (is_file($filepath.$filename.'.'.$extension)) {
					$file_size = filesize($filepath.$filename.'.'.$extension);
					$document_id = DocumentManager::get_document_id($_course,$file);

					if ($document_id) {
						$updatestatus = update_existing_document($_course, $document_id,$file_size,$read_only_flag, $_POST['newTitle']);
						if($updatestatus)
						{
						echo '<script type="text/javascript">window.location.href="document.php?curdirpath='.urlencode($dir).'";</script>';
						}
					}
				}

				if (empty($document_id)) //or if is folder
				{
					$folder=$_POST['file_path'];
					$document_id = DocumentManager::get_document_id($_course,$folder);

					if (DocumentManager::is_folder($_course, $document_id))
					{
						if($document_id)
						{
							$updatestatus = update_existing_document($_course, $document_id,$file_size,$read_only_flag, $_POST['newTitle']);
							if($updatestatus)
							{
							echo '<script type="text/javascript">window.location.href="document.php?curdirpath='.urlencode($dir).'";</script>';
							}
						}
					}
				}


			}
		}
	}
}


//replace relative paths by absolute web paths  (e.g. "./" => "http://www.dokeos.com/courses/ABC/document/")
if(file_exists($filepath.$doc))
{

	$extension=explode('.',$doc);
	$extension=$extension[sizeof($extension)-1];
	$filename=str_replace('.'.$extension,'',$doc);
	$extension=strtolower($extension);

	if(in_array($extension,array('html','htm'))) {
	//	$texte=file($filepath.$doc);
	//	$texte=implode('',$texte);

		$texte = file_get_contents($filepath.$doc);
		$css_name = api_get_setting('stylesheets');
		$position = strpos($texte, 'table.result');

		if ($position !== false){

			//$template_css = ' <style type="text/css">'.str_replace('../../img/',api_get_path(REL_CODE_PATH).'img/',file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/default.css')).'</style>';
			if(file_exists(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')) {
                            $template_content = str_replace('../../img/', api_get_path(REL_CODE_PATH).'img/', file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css'));
                            $template_content = str_replace('images/', api_get_path(REL_CODE_PATH).'css/'.$css_name.'/images/', $template_content);
                            file_put_contents($filepath.'css/templates.css', $template_content);
			}
		}

                if(file_exists(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')) {

                    $template_content = str_replace('../../img/', api_get_path(REL_CODE_PATH).'img/', file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css'));
                    $template_content = str_replace('images/', api_get_path(REL_CODE_PATH).'css/'.$css_name.'/images/', $template_content);
                    file_put_contents($filepath.'css/templates.css', $template_content);
                }

                $texte =  str_replace('{CSS}','<link rel="stylesheet" href="'.api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/css/templates.css" type="text/css" />', $texte);
                // add template css path if it doesn't exist
                $template_css = '';

                if (strpos($texte, '/css/templates.css') === false) {

                    //$template_css = '<link rel="stylesheet" href="'.api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/css/templates.css" type="text/css" />';
                }
                // add js path if it doesn't exist
                $js = '';
                if (strpos($texte, 'javascript/jquery.highlight.js') === false) {
                    
                    $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
                    $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js" language="javascript"></script>'.PHP_EOL;   
                    if (api_get_setting('show_glossary_in_documents') != 'none' && isset($_POST['is_template']) && $_POST['is_template'] == 0) { 
						
						// If is allowed the use of themes inside of this course
                            if(api_get_setting('allow_course_theme')=='true' && api_get_course_setting('course_theme', null, true)!=''){ 
                                $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_course_setting('course_theme', null, true).'/default.css" rel="stylesheet" />'; 
                            }else{
                                // Else the stylesheet used will be the platform stylesheet   
                                $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_setting('stylesheets').'/default.css" rel="stylesheet" />'; 
                            }
                            
                            $js .='<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" language="javascript"></script>';
                            $js .='<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';  

                        if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                        } else {
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                            $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                        }
                    }
                }
		$default_sys_dir = api_get_path(REL_CODE_PATH).'default_course_document/';

		$req_dir = $_REQUEST['curdirpath'];
		$count = substr_count($req_dir,"/");
		$relpath = '';
		if($req_dir != '/') {
		for($i=0;$i<$count;$i++) {
			$relpath .= '../';
		}
		}

		$texte = str_replace('</head>', $template_css.$js.'</head>', $texte);
		if(!empty($relpath)){
		$texte =  str_replace($relpath.'images/templates/',$default_sys_dir.'images/templates/',$texte);
		}
		//This code has been added to remove the style added by Roullier client double the times in old document
		$style_start = strpos($texte,'<style type="text/css">body{');
		if($style_start !== false){
			$style_end = strpos($texte,'</body>');
			$sub = substr($texte,$style_start,$style_end);
			if($style_end > $style_start){
			$texte = str_replace($sub, '', $texte);
			}
		}
                //error in replace//
		//$texte =  str_replace($relpath.'mascot/',$default_sys_dir.'mascot/',$texte);
		$path_to_append=api_get_path(WEB_COURSE_PATH).$_course['path'].'/document'.$dir;
		$texte=str_replace('="./','="'.$path_to_append,$texte);
		$texte=str_replace('mp3player.swf?son=.%2F','mp3player.swf?son='.urlencode($path_to_append),$texte);
	}
}

/*
==============================================================================
		- display user interface
==============================================================================
*/
// display the header
$nameTools = get_lang("EditDocument") . ': '.$file_name;
Display::display_tool_header($nameTools,"Doc");
Display::display_introduction_section(TOOL_DOCUMENT);
// display the tool title
//api_display_tool_title($nameTools);

if(isset($msgError))
{
	Display::display_error_message($msgError); //main API
}

if( isset($info_message))
{
	Display::display_confirmation_message($info_message); //main API
	if (isset($_POST['origin']))
	{
		$slide_id=$_POST['origin_opt'];
		//nav_to_slideshow($slide_id);
	}
}



// readonly
$sql = 'SELECT id, readonly FROM '.$dbTable.' WHERE path LIKE BINARY "'.$dir.$doc.'"';
$rs = Database::query($sql, __FILE__, __LINE__);
$readonly = Database::result($rs,0,'readonly');
$doc_id = Database::result($rs,0,'id');

// owner
$sql = 'SELECT insert_user_id FROM '.Database::get_course_table(TABLE_ITEM_PROPERTY).'
		WHERE tool LIKE "document"
		AND ref='.intval($doc_id);
$rs = Database::query($sql, __FILE__, __LINE__);
$owner_id = Database::result($rs,0,'insert_user_id');


if ($owner_id == $_user['user_id'] || api_is_platform_admin() || $is_allowed_to_edit || GroupManager :: is_user_in_group($_user['user_id'],$_SESSION['_gid'] ))
{
	$get_cur_path=Security::remove_XSS($_GET['curdirpath']);
	$get_file=Security::remove_XSS($_GET['file']);
	$action =  api_get_self().'?'.api_get_cidreq().'&sourceFile='.urlencode($file_name).'&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'&doc='.urlencode($doc);
	$form = new FormValidator('formEdit','post',$action);

	// form title
	//$form->addElement('header', '', $nameTools);
	$renderer = $form->defaultRenderer();

	$form->addElement('hidden','filename');
	$form->addElement('hidden','extension');
	$form->addElement('hidden','file_path');
	$form->addElement('hidden','commentPath');
	$form->addElement('hidden','showedit');
	$form->addElement('hidden','origin');
	$form->addElement('hidden','origin_opt');

	if($use_document_title)
	{
	//	$form->add_textfield('newTitle',get_lang('Title'));
		$form->addElement('text','newTitle',get_lang('Title'),'class="focus" id="newTitle" style="width:300px;"');
		$defaults['newTitle'] = $oldTitle;
	}
	else
	{
		$form->addElement('hidden','renameTo');
	}

	$form->addElement('hidden','formSent');
	$defaults['formSent'] = 1;


	$read_only_flag=$_POST['readonly'];

	$defaults['texte'] = $texte;

	//if($extension == 'htm' || $extension == 'html')
	// HotPotatoes tests are html files, but they should not be edited in order their functionality to be preserved.
	if(($extension == 'htm' || $extension == 'html') && stripos($dir, '/HotPotatoes_files') === false)
	{
		if (empty($readonly) && $readonly==0)
		{
			$_SESSION['showedit']=1;
		//	$renderer->setElementTemplate('<div class="row"><div class="label" id="frmModel" style="overflow: visible;"></div><div class="formw">{element}</div></div>', 'texte');
		//	$form->addElement('html','<div style="display:block; height:525px; width:240px; position:absolute; top:50px; left:50px;"><table width="100%" cellpadding="3" cellspacing="3"><tr><td><a href="document.php?curdirpath='.Security::remove_XSS($_GET['dir']).'">'.Display::return_icon('go_previous_32.png',get_lang('Back'),array('style'=>'vertical-align:middle;')).'&nbsp;&nbsp;'.get_lang('Back').'</a></td></tr><tr><td align="center"><a href="template_gallery.php?doc=E&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'"><div class="actions" ><img src="'.api_get_path(WEB_IMG_PATH).'tools_wizard.png"></div></a></td></tr><tr><td align="center"><h4>Templates Gallery</h4></td></tr></table></div>');
	//	$renderer->setElementTemplate('<div class="row"><div class="label" style="overflow: visible;"><table width="100%" cellpadding="3" cellspacing="3"><tr><td align="center"><a href="document.php?curdirpath='.Security::remove_XSS($_GET['dir']).'">'.Display::return_icon('go_previous_32.png',get_lang('Back'),array('style'=>'vertical-align:middle;')).'&nbsp;&nbsp;'.get_lang('Back').'</a></td></tr><tr><td align="center"><a href="template_gallery.php?doc=E&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'"><div class="actions" ><img src="'.api_get_path(WEB_IMG_PATH).'tools_wizard.png"></div></a></td></tr><tr><td align="center"><h4>Templates Gallery</h4></td></tr></table></div><div class="formw">{element}</div></div>', 'texte');
		$renderer->setElementTemplate('<div class="row"><div style="width:100%;float:right;">{element}</div></div>', 'texte');
		$form->add_html_editor('texte', '', false, true, $html_editor_config);
		}
	}

	$form->addElement('textarea','newComment',get_lang('Comment'),'rows="3" style="width:300px;"');
	/*
	$renderer = $form->defaultRenderer();
	*/
	if ($owner_id == $_user['user_id'] || api_is_platform_admin())
	{
		$renderer->setElementTemplate('<div class="row"><div class="label"></div><div class="formw">{element}{label}</div></div>', 'readonly');
		$checked =&$form->addElement('checkbox','readonly',get_lang('ReadOnly'));
		if ($readonly==1)
		{
			$checked->setChecked(true);
		}
	}

	if ($is_certificate_mode) {
		$form->addElement('style_submit_button', 'submit', get_lang('SaveCertificate'), 'class="save"');
        }
	else {
		$form->addElement('style_submit_button','submit',get_lang('SaveDocument'), 'class="save"');
        }

	if (isset($_REQUEST['tplid'])) {
            $table_sys_template = Database::get_main_table('system_template');
            $table_template = Database::get_main_table(TABLE_MAIN_TEMPLATES);
            $table_document = Database::get_course_table(TABLE_DOCUMENT, $_course['dbName']);
            $user_id = api_get_user_id();

            // setting some paths
            $img_dir = api_get_path(REL_CODE_PATH).'img/';
            $default_course_dir = api_get_path(REL_CODE_PATH).'default_course_document/';
            if(file_exists(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')) {
                $template_content = str_replace('../../img/', api_get_path(REL_CODE_PATH).'img/', file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css'));
                $template_content = str_replace('images/', api_get_path(REL_CODE_PATH).'css/'.$css_name.'/images/', $template_content);
                file_put_contents($filepath.'css/templates.css', $template_content);
            }

	if(!isset($_REQUEST['tmpltype']))
	{
		if($_REQUEST['tplid'] <> 0)
		{
                    $query = 'SELECT content FROM '.$table_sys_template.' WHERE id='.$_REQUEST['tplid'];
                    $result = api_sql_query($query,__FILE__,__LINE__);
                    while($obj = Database::fetch_object($result)) {
                        $valcontent = $obj->content;
                    }

                    // add js path if it doesn't exist
                    $js = '';
                    if (strpos($valcontent, 'javascript/jquery.highlight.js') === false) {
                        if (api_get_setting('show_glossary_in_documents') != 'none') {
                            $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>'.PHP_EOL;
                            $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js" language="javascript"></script>'.PHP_EOL;
                            if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"/>';
                                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"/>'.PHP_EOL;
                                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"/>';
                            } else {
                                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"/>'.PHP_EOL;
                                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"/>';
                            }
                        }
                    }

                    // add template css path if it doesn't exist
                    $template_css = '';
                    if (strpos($valcontent, '/css/templates.css') === false) {
                        $template_css = '<link rel="stylesheet" href="'.api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/css/templates.css" type="text/css" />';
                    }
                    $valcontent =  str_replace('{CSS}',$template_css.$js, $valcontent);
                    $valcontent =  str_replace('{IMG_DIR}',$img_dir, $valcontent);
                    $valcontent =  str_replace('{REL_PATH}', api_get_path(REL_PATH), $valcontent);
                    $valcontent =  str_replace('{COURSE_DIR}',$default_course_dir, $valcontent);
                    $defaults['texte'] = $valcontent;
		}
	}
	else
	{
			$sql = "SELECT template.id, template.title, template.description, template.image, template.ref_doc, document.path
			FROM ".$table_template." template, ".$table_document." document
			WHERE user_id='".Database::escape_string($user_id)."'
			AND course_code='".Database::escape_string(api_get_course_id())."'
			AND document.id = template.ref_doc";
			$result_template = api_sql_query($sql,__FILE__,__LINE__);
			while ($row = Database::fetch_array($result_template))
			{
				$valcontent = file_get_contents(api_get_path('SYS_COURSE_PATH').$_course['path'].'/document'.$row['path']);
			}
			$defaults['texte'] = $valcontent;
	}
}
else
{
	$defaults['texte'] = $texte;
}

	$defaults['filename'] = $filename;
	$defaults['extension'] = $extension;
	$defaults['file_path'] = Security::remove_XSS($_GET['file']);
	$defaults['commentPath'] = $file;
	$defaults['renameTo'] = $file_name;
	$defaults['newComment'] = $oldComment;
	$defaults['origin'] = Security::remove_XSS($_GET['origin']);
	$defaults['origin_opt'] = Security::remove_XSS($_GET['origin_opt']);
	$form->setDefaults($defaults);
	// show templates
	/*
	$form->addElement('html','<div id="frmModel" style="display:block; height:525px; width:240px; position:absolute; top:115px; left:1px;"></div>');
	*/
$origin=Security::remove_XSS($_GET['origin']);
	if ($origin=='slideshow') {
		$slide_id=$_GET['origin_opt'];
		//nav_to_slideshow($slide_id);
	}

	if (isset($_REQUEST['curdirpath']) && $_GET['curdirpath']=='/certificates') {
		$all_information_by_create_certificate=DocumentManager::get_all_info_to_certificate();
		$str_info='';
		foreach ($all_information_by_create_certificate[0] as $info_value) {
			$str_info.=$info_value.'<br/>';
		}
		$create_certificate=get_lang('CreateCertificateWithTags');
		Display::display_normal_message($create_certificate.': <br /><br />'.$str_info,false);
	}

//	echo '<div class="actions">';

	// link back to the documents overview
/*	if ($is_certificate_mode)
		echo '<a href="document.php?curdirpath='.Security::remove_XSS($_GET['curdirpath']).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'">'.Display::return_icon('back.png',get_lang('Back').' '.get_lang('To').' '.get_lang('CertificateOverview')).get_lang('Back').' '.get_lang('To').' '.get_lang('CertificateOverview').'</a>';
	else
		echo '<a href="document.php?curdirpath='.Security::remove_XSS($_GET['curdirpath']).'">'.Display::return_icon('back.png',get_lang('Back').' '.get_lang('To').' '.get_lang('DocumentsOverview')).get_lang('Back').' '.get_lang('To').' '.get_lang('DocumentsOverview').'</a>';
	echo '</div>';*/

	echo '<div class="actions" style="min-height: 40px;">';
          DocumentManager::show_li_eeight($_GET['document'],$_GET['gidReq'],$_GET['curdirpath'],$_GET['curdirpath'],$group_properties['directory'],$image_present,'edit_document',$file);
//        echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.Security::remove_XSS($_GET['dir']).'">'.Display::return_icon('pixel.gif',get_lang('Documents'),array('style'=>'vertical-align:middle;','class'=>'toolactionplaceholdericon toolactiondocument')).'&nbsp;&nbsp;'.get_lang('Documents').'</a>
//             <a href="template_gallery.php?'.api_get_cidreq().'&doc=N&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'">'.Display::return_icon('pixel.gif', get_lang('TemplatesGallery'), array('class' => 'toolactionplaceholdericon toolactiontemplates')).get_lang('TemplatesGallery').'</a>';
        echo '</div>';
	echo '<div id="content">';
         DocumentManager::show_back_directory($curdirpath, $group_properties['directory'],TRUE,$_GET['curdirpath']);
    if(isset($msgError))
{
	Display::display_error_message($msgError); //main API
}

	$form->display();
	echo '<div>';
	//Display::display_error_message(get_lang('ReadOnlyFile')); //main API

}

//for better navigation when a slide is been commented
function nav_to_slideshow($slide_id)
{
		echo '<div class="actions">';
		echo '<a href="'.api_get_path(WEB_PATH).'main/document/slideshow.php?slide_id='.$slide_id.'&curdirpath='.Security::remove_XSS(urlencode($_GET['curdirpath'])).'">'.Display::return_icon('back.png', get_lang('BackTo').' '.get_lang('ViewSlideshow')).get_lang('BackTo').' '.get_lang('ViewSlideshow').'</a>';
		echo '</div>';
}
/*
==============================================================================
	   DOKEOS FOOTER
==============================================================================
*/
echo '</div>';
echo '</div>';

 // bottom actions bar
//echo '<div class="actions">';
//echo '</div>';

Display::display_footer();
?>
