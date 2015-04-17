<?php
/**
 * Learnpath Item model
 * @package application.author.models.ModelLearnpath
 */
class application_author_models_ModelLearnpathItem
{
    private $_ado;
    
    private $_sessionId;
    private $_userId;
    private $_courseCode;
    private $_lpItemId;
    private $_lpId;
    private $_itemPosition;
    
    private $_tblSystemTemplates;
    private $_tblQuiz;
    private $_tblQuizQuestion;
    private $_tblQuizRelQuestion;
    private $_tblItemProperty;
    private $_tblDocument;
    private $_tblLpItem;
    private $_tblLpItemView;
    private $_tblLpView;
    private $_tblTrackExercise;
    private $_tblWebtvChannel;
    private $_tblWebtvVideo;
    
    public $courseInfo;
    
    public  $fieldValues = array();
    
    
    public function __construct($courseCode = null, $sessionId = null, $userId = null) {
        
        if (!isset($courseCode)) {
            $courseCode = api_get_course_id();
        }
        if (!isset($sessionId)) {
            $sessionId = api_get_session_id();
        }
        if (!isset($userId)) {
            $userId = api_get_user_id();
        }
        
        $this->_courseCode = $courseCode;
        $this->_sessionId = $sessionId;
        $this->_userId = $userId;     
        $this->courseInfo = api_get_course_info($courseCode);
        $this->_ado = appcore_db_DB::conn();

        // Definition of tables
        $this->_tblSystemTemplates = Database::get_main_table(TABLE_MAIN_SYSTEM_TEMPLATE);
        $this->_tblQuiz = Database::get_course_table(TABLE_QUIZ_TEST, $this->courseInfo['dbName']);            
        $this->_tblQuizQuestion = Database::get_course_table(TABLE_QUIZ_QUESTION, $this->courseInfo['dbName']);        
        $this->_tblQuizRelQuestion = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $this->courseInfo['dbName']);        
        $this->_tblDocument = Database::get_course_table(TABLE_DOCUMENT, $this->courseInfo['dbName']);   
        $this->_tblItemProperty = Database::get_course_table(TABLE_ITEM_PROPERTY, $this->courseInfo['dbName']);  
        $this->_tblLpItem = Database::get_course_table(TABLE_LP_ITEM, $this->courseInfo['dbName']);
        $this->_tblLpItemView = Database::get_course_table(TABLE_LP_ITEM_VIEW, $this->courseInfo['dbName']);
        $this->_tblLpView = Database::get_course_table(TABLE_LP_VIEW, $this->courseInfo['dbName']);
        $this->_tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
        
        $this->_tblWebtvChannel = Database::get_course_table(TABLE_WEBTV_CHANNEL, $this->courseInfo['dbName']);        
        $this->_tblWebtvVideo = Database::get_course_table(TABLE_WEBTV_VIDEO, $this->courseInfo['dbName']);
        
    }
   
	public function getThumbnails() {
	
		
	
	}
   
    public function getTemplates() {                
        //return $this->_ado->GetAll("SELECT id, title, image, comment, content FROM {$this->_tblSystemTemplates} WHERE comment LIKE 'tpl_ppt%' ORDER BY id DESC");
        return $this->_ado->GetAll("SELECT id, title, image, comment, content FROM {$this->_tblSystemTemplates} WHERE template_type = 'platform' ORDER BY id DESC");
    }
    
    public function getTemplateInfo($tplId) {
        $tplId = intval($tplId);
        return $this->_ado->GetRow("SELECT id, title, image, comment, content FROM {$this->_tblSystemTemplates} WHERE id = $tplId"); 
    }
    
    public function getTemplateForEditor($tplId) {        
        // create css folder
        $stylesheet = api_get_setting('stylesheets');
        $perm = api_get_setting('permissions_for_new_directories');
        $perm = octdec(!empty($perm)?$perm:'0770');
        $cssFolder = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document/css';
        if (!is_dir($cssFolder)) {
                mkdir($cssFolder);
                chmod($cssFolder, $perm);
                $docId = add_document($this->courseInfo, '/css', 'folder', 0, 'css');
                api_item_property_update($this->courseInfo, TOOL_DOCUMENT, $docId, 'FolderCreated', $this->_userId);
                api_item_property_update($this->courseInfo, TOOL_DOCUMENT, $docId, 'invisible', $this->_userId);
        }

        if (!file_exists($cssFolder.'/templates.css')) {
            if(file_exists(api_get_path(SYS_PATH).'main/css/'.$stylesheet.'/templates.css')) {
                $templateContent = str_replace('../../img/', api_get_path(REL_CODE_PATH).'img/', file_get_contents(api_get_path(SYS_PATH).'main/css/'.$stylesheet.'/templates.css'));
                $templateContent = str_replace('images/', api_get_path(REL_CODE_PATH).'css/'.$stylesheet.'/images/', $templateContent);
                file_put_contents($cssFolder.'/templates.css', $templateContent);
            }
        }
        
        $template = $this->getTemplateInfo($tplId);
        $valcontent = $template['content'];
        if ($tplId == 0) {
            $valcontent = '<head>{CSS}<style type="text/css">.text{font-weight: normal;}</style></head><body></body>';
        }

        $templateCss = '';
        if (strpos($valcontent, '/css/templates.css') === false) {
            $templateCss = '<link rel="stylesheet" href="'.api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/document/css/templates.css" type="text/css" />';
        }

        $templateJs = '';
        if (strpos($valcontent, 'javascript/jquery.highlight.js') === false) {
            $templateJs .= '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
            $templateJs .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js" language="javascript"></script>'.PHP_EOL;
            if (api_get_setting('show_glossary_in_documents') != 'none') {
                $templateJs .='<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" language="javascript"></script>';
                $templateJs .='<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />'; 
                $templateJs .= '<script language="javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.highlight.js"></script>';
                if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
                    $templateJs .= '<script language="javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                } else {
                    $templateJs .= '<script language="javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                }
            }
        }
        $valcontent = str_replace('{CSS}', $templateCss.$templateJs, $valcontent);
        if (strpos($valcontent, '/css/templates.css') === false) {
            $valcontent = str_replace('</head>', $templateCss.'</head>', $valcontent);
        }
        if (strpos($valcontent, 'javascript/jquery.highlight.js') === false) {
            $valcontent = str_replace('</head>', $templateJs.'</head>', $valcontent);
        }
        $valcontent = str_replace('{IMG_DIR}', api_get_path(REL_CODE_PATH) . 'img/', $valcontent);
        $valcontent = str_replace('{REL_PATH}', api_get_path(REL_PATH), $valcontent);
        $valcontent = str_replace('{COURSE_DIR}', api_get_path(REL_CODE_PATH) . 'default_course_document/', $valcontent);        
        return $valcontent;
    }
    
    public function getQuizzes() {
        return $this->_ado->GetAll("SELECT id, title FROM {$this->_tblQuiz} WHERE active <> -1 AND quiz_type <> 2");                   
    }
    
    public function getQuizInfo($quizId) {
        $quizId = intval($quizId);
        return $this->_ado->GetRow("SELECT id, title FROM {$this->_tblQuiz} WHERE id = $quizId"); 
    }
    
    public function getQuizMaxScore($quizId) {
        $quizId = intval($quizId);
        $max_score = $this->_ado->GetOne("SELECT SUM(ponderation) as max_score FROM {$this->_tblQuizQuestion} as qq INNER JOIN  {$this->_tblQuizRelQuestion} as qrq ON qq.id = qrq.question_id AND qrq.exercice_id = $quizId");
        return round($max_score);
    }
                    
    public function getDocumentInfo($docId) {
        $docId = intval($docId);
        return $this->_ado->GetRow("SELECT id, path, title FROM {$this->_tblDocument} WHERE id = $docId"); 
    }
    
    public function getDocumentContent($docId) {
        $docId = intval($docId);
        $docPath = $this->_ado->GetRow("SELECT path FROM {$this->_tblDocument} WHERE id = $docId"); 
        
        //echo "SELECT path FROM {$this->_tblDocument} WHERE id = $docId";
        
        $content = '';
        if (!empty($docPath['path'])) {
            $fullpath = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document'.$docPath['path'];            
            if (file_exists($fullpath)) {
                $content = file_get_contents($fullpath);
            }
        }
        return $content;        
    }
    
    public function createDocument($title, $content,$lpId=null,$lpItemId=null) {
        $dir = '/';
        $filepath = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document' . $dir;
        //stripslashes before calling replace_dangerous_char() because $_POST['title']
        //is already escaped twice when it gets here
        $tmpTitle = stripslashes($title);
        $title = api_clean_pathfile($title);
        $filename = $title;
        $tmpFilename = $filename;

        $i = 0;        
        while (file_exists($filepath.$tmpFilename.'.html')) {
            $tmpFilename = $filename.'_'.++$i;
        }        
        $filename = $tmpFilename . '.html';
        $content = stripslashes(text_filter($content));
        $content = str_replace(api_get_path(WEB_COURSE_PATH), api_get_path(REL_PATH).'courses/', $content);
        // change the path of mp3 to absolute
        // first regexp deals with ../../../ urls
        $content = preg_replace("|(flashvars=\"file=)(\.+/)+|", "$1".api_get_path(REL_COURSE_PATH).$this->courseInfo['path'].'/document/', $content);
        //second regexp deals with audio/ urls
        $content = preg_replace("|(flashvars=\"file=)([^/]+)/|", "$1".api_get_path(REL_COURSE_PATH).$this->courseInfo['path'].'/document/$2/', $content);
        // for flv player : to prevent edition problem with firefox, we have to use a strange tip (don't blame me please)
        //$content = str_replace('</body>', '<style type="text/css">body{}</style></body>', $content);
        if (!file_exists($filepath.$filename)) {
            if ($fp = @fopen($filepath.$filename, 'w')) {
                fputs($fp, $content);
                fclose($fp);
                $fileSize = filesize($filepath . $filename);
                $saveFilePath = $dir.$filename;
                $documentId = add_document($this->courseInfo, $saveFilePath, 'file', $fileSize, $tmpTitle);
                if ($documentId) {
                    api_item_property_update($this->courseInfo, TOOL_DOCUMENT, $documentId, 'DocumentAddedFromLearnpath', api_get_user_id(), null, null, null, null, api_get_session_id());
                }

				if (!is_dir(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path']."/upload/learning_path/slides")) {
					mkdir(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path']."/upload/learning_path/slides");
					$perm = api_get_setting('permissions_for_new_directories');
					$perm = octdec(!empty($perm) ? $perm : '0770');
					chmod(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path']."/document/images/author", $perm);
				}
                
                $this->_saveDocument(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path']."/upload/learning_path/slides/".$lpItemId.".html",$content);                
            }
        }
        
        $this->_createThumbnail($lpItemId.".html",$lpId,$lpItemId);
                
        return $documentId;
    }
  
    function _saveDocument($file,$content)
    {
		if ($fp = @fopen($file, 'w')) {
		
			fputs($fp, $content);
            fclose($fp);
			
		}
	}
    
    function _createThumbnail($filename, $lpId, $lpItemId, $type = 'document', $nweb = true){
        $syscoursepath = api_get_path(SYS_COURSE_PATH);
        $coursedir = $this->courseInfo['path'];		
        $notfinal_file = $syscoursepath.$coursedir."/upload/learning_path/thumbnails/".$lpId."/".$lpItemId.".jpg";
        $final_file = $syscoursepath.$coursedir."/upload/learning_path/thumbnails/".$lpId."/".$lpItemId.".png";

        if (file_exists($notfinal_file)) {
            @unlink($notfinal_file);
        }

        if (file_exists($final_file)) {
            @unlink($final_file);
        }

		if (!is_dir(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path']."/upload/learning_path/thumbnails")) {
			$perm = api_get_setting('permissions_for_new_directories');
			$perm = octdec(!empty($perm) ? $perm : '0770');
			mkdir(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path']."/upload/learning_path/thumbnails", $perm);
		}

        if (!is_dir($syscoursepath.$coursedir."/upload/learning_path/thumbnails/".$lpId)) {
            mkdir($syscoursepath.$coursedir."/upload/learning_path/thumbnails/".$lpId);
        }
        
        switch($type){
            case 'quiz':
                $url = api_get_path(WEB_PATH).'main/exercice/exercice_submit.php?'.  api_get_cidreq().'&origin=learnpath&learnpath_id='.$lpId.'&exerciseId='.$filename.'&screenshot=true';
                $quiz_conten = file_get_contents($url);
                file_put_contents(api_get_path(SYS_COURSE_PATH)."/".$this->courseInfo['path']."/upload/learning_path/slides/".$lpItemId.".html", $quiz_conten);
                $url_t = api_get_path(WEB_COURSE_PATH).$coursedir.'/upload/learning_path/slides/'.$lpItemId.".html";                
                if ($nweb) {
                    $cmd = "http://127.0.0.1:8181/para=-t&2000&-W&800&-H&600&".$url_t."&".$notfinal_file;                                        
                    file($cmd);
                }
                else {
                    $html2imagePath = api_get_path(LIBRARY_PATH).'html2image/';
                    if (!is_dir($html2imagePath)) { return false; }                                                                                 
                    $cmd = 'cd '.$html2imagePath.' && export LD_LIBRARY_PATH='.$html2imagePath.' && '.$html2imagePath.'html2image "'.($url).'" "'.$notfinal_file.'"';	                    
                    exec($cmd);
                }
                unlink(api_get_path(SYS_COURSE_PATH)."/".$this->courseInfo['path']."/upload/learning_path/slides/".$lpItemId.".html");
            break;
            default:                
                if ($nweb) {
                    $cmd = "http://127.0.0.1:8181/para=-t&2000&-W&800&-H&600&".api_get_path(WEB_COURSE_PATH).$coursedir."/upload/learning_path/slides/".$filename."&".$notfinal_file;
                    
                    file($cmd);
                }
                else {
                    $html2imagePath = api_get_path(LIBRARY_PATH).'html2image/';
                    if (!is_dir($html2imagePath)) { return false; }
                    $url = api_get_path(WEB_COURSE_PATH).$coursedir.'/upload/learning_path/slides/'.$filename;
                    $cmd = 'cd '.$html2imagePath.' && export LD_LIBRARY_PATH='.$html2imagePath.' && '.$html2imagePath.'html2image "'.escapeshellcmd($url).'" "'.$notfinal_file.'"';                         
                    exec($cmd);
                }
                
            break;
            
            if (file_exists($notfinal_file)) {
                    // convert to png and resized to 144x144 using imagemagick
                    $cmd = "cp ".$notfinal_file." ".$notfinal_file.".1.jpg";
                    exec($cmd);
                    $cmd = "chmod 777 ".$notfinal_file.".1.jpg";
                    exec($cmd);
                    $cmd ="convert ".$notfinal_file.".1.jpg  -resize 144x144 ".$final_file. "; ";//rm ".$notfinal_file;
                    exec($cmd);
                    if (filesize($final_file)<1024) {
                        $cmd = "rm ".$final_file." ; convert -size 144x144 xc:white " .$final_file;
                        exec($cmd);	
                    }
                }
        }
        
    }
    
    function editDocument($docId, $content,$lpId=null,$lpItemId=null) {
        $filepath = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document';
        $docInfo = $this->getDocumentInfo($docId);
        $content = stripslashes($content);
        $file = $filepath.$docInfo['path'];
        if ($fp = @fopen($file, 'w')) {
            $content = text_filter($content);
            $content = str_replace(api_get_path(WEB_COURSE_PATH), api_get_path(REL_PATH).'courses/', $content);
            // change the path of mp3 to absolute
            // first regexp deals with ../../../ urls
            $content = preg_replace("|(flashvars=\"file=)(\.+/)+|", "$1" . api_get_path(REL_COURSE_PATH).$this->courseInfo['path'].'/document/', $content);
            //second regexp deals with audio/ urls
            $content = preg_replace("|(flashvars=\"file=)([^/]+)/|", "$1" . api_get_path(REL_COURSE_PATH).$this->courseInfo['path'].'/document/$2/', $content);
            fputs($fp, $content);
            fclose($fp);
            
            $this->_saveDocument(api_get_path(SYS_COURSE_PATH)."/".$this->courseInfo['path']."/upload/learning_path/slides/".$lpItemId.".html",$content);                
        }
        
        $this->_createThumbnail($lpItemId.".html",$lpId,$lpItemId);
        
        return $docId;
    }
    
    public function getDocumentFiles($ressource, $category = '', $notIncluded = '', $lasteditType = 'DocumentAddedFromLearnpath') {
        if (!empty($category)) {
            $ressource = $ressource.'/'.$category;
        }
        $extrafilter = '';
        if (!empty($notIncluded)) {
            $extrafilter .= " AND doc.path NOT LIKE '/$notIncluded/%'";
        }
        return $this->_ado->GetAll("SELECT * FROM {$this->_tblDocument} doc, {$this->_tblItemProperty} prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND prop.lastedit_type = '$lasteditType' AND doc.filetype = 'file' AND doc.path LIKE '/$ressource/%' $extrafilter ORDER BY doc.title ASC");
    }
    
    
    
    public function getLpItemInfo($itemId) {
        $itemId = intval($itemId);
        return $this->_ado->GetRow("SELECT * FROM {$this->_tblLpItem} WHERE id = $itemId"); 
    }
    
    public function getItemView() {                
        $viewId = $this->_ado->GetOne("SELECT id FROM {$this->_tblLpView} WHERE user_id = ? AND lp_id = ? AND session_id = ?", array($this->_userId, $this->_lpId, $this->_sessionId));
        $data = array();
        if ($viewId) {
            $data = $this->_ado->GetRow("SELECT lp_item_id, id, lp_view_id, view_count, start_time, total_time, status  FROM {$this->_tblLpItemView} WHERE lp_item_id = ? AND lp_view_id = ? ORDER BY id DESC LIMIT 1", array($this->_lpItemId, $viewId));
        }     
        return $data;
    }
    
    public function getQuizTracking($exeId) {
        return $this->_ado->GetRow("SELECT start_date, exe_date, exe_result, exe_weighting FROM {$this->_tblTrackExercise} WHERE exe_id = ?", array($exeId));  
    }
    
    public function getWebtvChannels() {
        return $this->_ado->GetAll("SELECT id, name, description, image_src, image_canonical, status FROM {$this->_tblWebtvChannel}");
    }
    
    public function getVideosByChannel($channelId) {
        return $this->_ado->GetAll("SELECT id, title, video_src, video_canonical, duration, status, shared_status FROM {$this->_tblWebtvVideo} WHERE channel_id = ?", array($channelId));
    }
    
    public function getWebtvVideos() {
        return $this->_ado->GetAll("SELECT id, title, video_src, video_canonical, duration, status, shared_status FROM {$this->_tblWebtvVideo}");
    }
    
    public function save() {
        if (!empty($this->_lpItemId)) {
            // update the item
            $where = 'id='.intval($this->_lpItemId);
            $this->fieldValues['content'] = str_replace("'","&#39;",$this->fieldValues['content']);
            $updateSQL = $this->_ado->AutoExecute($this->_tblLpItem, $this->fieldValues, 'UPDATE', $where);
            $lastId = $this->_lpItemId;
        }
        else {
            // create the item'
            $insertSQL = $this->_ado->AutoExecute($this->_tblLpItem, $this->fieldValues, 'INSERT');
            $lastId = $this->_ado->Insert_ID();
        }
        return $lastId;
    }
    
    public function delete() {
        $this->_ado->Execute("DELETE FROM {$this->_tblLpItem} WHERE id = ?", array($this->_lpItemId));
    }
    
    public function nextItem() {
        $itemId = 0;
        if (!empty($this->_itemPosition)) {
            $itemId = $this->_ado->GetOne("SELECT id FROM {$this->_tblLpItem} WHERE display_order > {$this->_itemPosition} AND lp_id = {$this->_lpId} ORDER BY display_order LIMIT 1");
        }
        return intval($itemId);
    }
    
    public function previousItem() {
        $itemId = 0;
        if (!empty($this->_itemPosition)) {
            $itemId = $this->_ado->GetOne("SELECT id FROM {$this->_tblLpItem} WHERE display_order < {$this->_itemPosition} AND lp_id = {$this->_lpId} ORDER BY display_order DESC LIMIT 1");
        }
        return intval($itemId);
    }        
    
    public function setId($lpItemId) {
        $this->_lpItemId = $lpItemId;
    }
    
    public function setLpId($lpId) {
        $this->_lpId = $lpId;
    }
    
    public function setPosition($position) {
        $this->_itemPosition = $position;
    }
    
}

