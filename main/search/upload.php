<?php
/* For licensing terms, see /dokeos_license.txt */


/*****************
TODO: This is a draft for a demo. This code has to be improved before the release
******************/

$language_file = array('document');
require_once ('../inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';

$this_section =  SECTION_COURSES;

// Access restrictions
if (api_is_anonymous()) {
	api_not_allowed(true);
}

// Form init
$form = new FormValidator('upload_search', 'POST', api_get_self().'?'.api_get_cidreq());
$form->addElement('file', 'search_uploaded_file', get_lang('UploadFile'));
$form -> add_real_progress_bar(uniqid(), 'search_uploaded_file');
$form -> addElement ('textarea', 'search_terms', get_lang('Tags'), array('cols'=>100));
$form -> addElement ('style_submit_button', 'upload_button', get_lang('Validate'), array('class'=>"save"));

$renderer = & $form->defaultRenderer();

$div_upload_limit = get_lang('UploadMaxSize').' : '.ini_get('post_max_size');
// set template for user_file element
$user_file_template =
<<<EOT
<div class="row">
		<!-- BEGIN required --><!-- END required -->{label}<br />{element}&nbsp;$div_upload_limit
		<!-- BEGIN error --><br />{error}<!-- END error -->
</div>
EOT;
$renderer->setElementTemplate($user_file_template,'search_uploaded_file');

// set template for other elements
$search_terms_template =
<<<EOT
<div class="row">
		<!-- BEGIN required --><!-- END required -->{label}<br />{element}
		<!-- BEGIN error --><br />{error}<!-- END error -->
</div>
EOT;
$renderer->setElementTemplate($search_terms_template, 'search_terms');

if($form->validate())
{
		
	$courseDir   = $_course['path']."/document";
	$sys_course_path = api_get_path(SYS_COURSE_PATH);
	$base_work_dir = $sys_course_path.$courseDir;
	$temp_path = $sys_course_path.$_course['path'].'/temp/';
	
	$file_uploaded = $form->getSubmitValue('search_uploaded_file');
	$upload_ok = process_uploaded_file($file_uploaded);
		
	if($upload_ok)
	{
		
		$extension = pathinfo($file_uploaded['name'], PATHINFO_EXTENSION);
		if(in_array($extension, array('wmv','mpg','mpeg','mov','avi')))
		{
			// if video, we convert it
		
			$src = $temp_path.$file_uploaded['name'];
			$dest = $base_work_dir.'/'.str_replace($extension, 'flv', $file_uploaded['name']);
			
			if(move_uploaded_file($file_uploaded['tmp_name'], $temp_path.$file_uploaded['name']))
			{
				$ffmpeg = new ffmpeg_movie($src);
				$width = $ffmpeg->getFrameWidth();
				$height = $ffmpeg->getFrameHeight();
				$ab = intval($ffmpeg->getAudioBitRate());
				$ar = intval($ffmpeg->getAudioSampleRate());
				shell_exec('ffmpeg -i '.$src.' -f flv '.$dest.' -s '.$width.'x'.$height.' -ab '.$ab.' -ar '.$ar);
				unlink($src);
				
				$doc_path = '/'.pathinfo($dest, PATHINFO_BASENAME);
				if(add_document($_course, $doc_path, 'file', filesize($dest), pathinfo($dest, PATHINFO_FILENAME), '', 0))
					$new_path = $doc_path;
			}
			
		}
		else {
		
			// register document
			ob_start();
			$new_path = handle_uploaded_document($_course, $file_uploaded, $base_work_dir,'/',$_user['user_id']);
			ob_end_clean();
		}
		
		if(!empty($new_path))
		{
		
			//indexation process
			$docid = DocumentManager::get_document_id($_course, $new_path);
			$table_document = Database::get_course_table(TABLE_DOCUMENT);
	        $result = Database::query("SELECT * FROM $table_document WHERE id = '$docid' LIMIT 1", __FILE__, __LINE__);
	        if (Database::num_rows($result) == 1) {
	        	$row = Database::fetch_array($result);
		    	$doc_path = api_get_path(SYS_COURSE_PATH) . $courseDir. $row['path'];
	        
				$file_title = $row['title'];
	            $courseid = api_get_course_id();
	            $lang = 'english';
	
	            require_once api_get_path(LIBRARY_PATH).'search/DokeosIndexer.class.php';
	            require_once api_get_path(LIBRARY_PATH).'search/IndexableChunk.class.php';
	
	            $ic_slide = new IndexableChunk();
	            $ic_slide->addValue("title", $file_title);
	            $ic_slide->addCourseId($courseid);
	            $ic_slide->addToolId(TOOL_DOCUMENT);
	            $xapian_data = array(
	              SE_COURSE_ID => $courseid,
	              SE_TOOL_ID => TOOL_DOCUMENT,
	              SE_DATA => array('doc_id' => (int)$docid),
	              SE_USER => (int)api_get_user_id(),
	            );
	            $ic_slide->xapian_data = serialize($xapian_data);
	            $di = new DokeosIndexer();
	            $di->connectDb(NULL, NULL, $lang);
	
	            
				$all_specific_terms = $form->getSubmitValue('search_terms');
				// add terms also to content to make terms findable by probabilistic search
				$file_content = $all_specific_terms;
				$ic_slide->addValue("content", $file_content);
				$di->addChunk($ic_slide);
				//index and return search engine document id
				$did = $di->index();
				if ($did) {
					// save it to db
					$tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
					$sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
						VALUES (NULL , \'%s\', \'%s\', %s, %s)';
					$sql = sprintf($sql, $tbl_se_ref, $courseid, TOOL_DOCUMENT, $docid, $did);
					Database::query($sql,__FILE__,__LINE__);
				}
		
	          
	
	        }
	        header('location:index.php?message=success&'.api_get_cidreq());
	        exit;
		}
		else
		{
			$errorMessage = get_lang('UplUnableToSaveFile');
		}
        
	}
	else 
	{
		$errorMessage = get_lang('UplUnableToSaveFile');
	}
		
	
}


Display::display_tool_header(get_lang('CourseTool'));

	
?>



<style type="text/css">
h3.orange {
	margin-left:0px;
}
.form_orange					{ width:400px;  }
.form_orange input				{ width:100%;}
.form_orange h3					{
	color:#F09A43;
	text-transform:uppercase;
	margin-left:0px;
}
.form_orange #fileQueue 		{ margin-right:130px; }
.form_orange #fakeButton		{ margin:10px; }
.form_orange #uploadifyUploader	{ position:absolute; }
.form_orange a.submit			{
	background:url('../img/navigation/bg_orange_form_submit.gif') no-repeat 0 0 #F09A43;
	color:#fff;
	cursor:pointer;
	display:inline-block;
	float:right;
	font-size:90%;
	font-weight:bold;
	line-height:26px;
	height:26px;
	margin-top:10px;
	padding:0 10px 0 40px;
	text-transform:uppercase;
}
.form_orange .fileQSubmitContainer	{ margin-top:20px; }

.loader {
	background:url('../img/navigation/ajax-loader.gif') no-repeat center 0;
	color:#F09A43;
	margin:0 auto;
	padding:20px 0;
	text-align:center;
	width:150px;
}

.button_text	{
	height:15px;
	padding:70px 10px 30px;
	width:180px;
}
.button_notext	{
	height:115px !important;
	padding:0 !important;

}

.uploadbtn {

	margin-left:400px;

}

.megane			{ background:url('../img/navigation/renault/megane.png') no-repeat center center; }
.sandero		{ background:url('../img/navigation/renault/sandero.png') no-repeat center center; }
.kangoo			{ background:url('../img/navigation/renault/kangoo.png') no-repeat center center; }
.zero_emmission	{ background:url('../img/navigation/renault/zero_emmission.png') no-repeat center center #000; }
.alpine_renault	{ background:url('../img/navigation/renault/alpine.png') no-repeat center center #000; }
.carlos			{ background:url('../img/navigation/renault/carlos.png') no-repeat center center #000; }

.ardoise		{ background:url('../img/navigation/renault/ardoise.png') no-repeat center 20px; }
.pdf			{ background:url('../img/navigation/renault/pdf.png') no-repeat center 20px; }
.ipod			{ background:url('../img/navigation/renault/ipod.png') no-repeat center 20px; }
.xls			{ background:url('../img/navigation/renault/xls.png') no-repeat center 20px; }
.ppt			{ background:url('../img/navigation/renault/ppt.png') no-repeat center 20px; }
.doc 			{ background:url('../img/navigation/renault/doc.png') no-repeat center 20px; }

.abs	{ position:absolute; zoom:1; }
.rel	{ position:relative; }

.bg_black:hover	{ background-color:#000 !important; }

#result { padding: 20px 0; }
</style>
<?php
		
	
//--- actions ---
echo '<div class="actions">';
		echo '<a class="" href="index.php?'.api_get_cidreq().'">'.Display::return_icon('navigation/renault/loupe.png').get_lang('NewSearch').'</a>';
		/*echo '<a class="" href="#">'.Display::return_icon('navigation/renault/films.png').'VIDEO ONLY</a>';
		echo '<a class="" href="#">'.Display::return_icon('navigation/renault/plus.png'). 'MORE CRITERIA</a>';*/
		//echo '<a class="uploadbtn" href="upload.php?'.api_get_cidreq().'">'.Display::return_icon('navigation/renault/upload.png'). 'UPLOAD</a>';
echo '</div>';
	
	
	
// --- div#content ---
echo '<div id="content_with_secondary_actions" class="rel">';


echo '<h3 class="orange">'.get_lang('FillTheLibrary').'</h3>';


if(!empty($successMessage))
{
	Display::display_confirmation_message($successMessage);
}
if(!empty($errorMessage))
{
	Display::display_error_message($errorMessage);
}
	
$form -> display();



echo '</div>';	//end div#content

//echo '<div class="actions">';
//	echo '&nbsp;';
//echo '</div>';
	
//	Display::display_error_message(get_lang('SearchXapianModuleNotInstaled'));
Display::display_footer();
exit;

?>