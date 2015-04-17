<?php
/**
 * controller used for authoring page
 * @package Author 
 */

class application_author_controllers_Authoring extends appcore_command_Command 
{
    
    protected $_lpModel;
    protected $_lpItemModel;
    
    public $courseCode;
    public $courseInfo;
    public $userId;
    public $lpId;
    public $tplId;  
    public $templates;
    public $images;
    public $videos;
    public $quizzes;
    public $toolName;
        
    public $editorValue = '';
    public $editorConfig;
    
    public $itemTitle = '';
    public $itemType = 'document';
    public $itemRef = 1;
    public $itemPath = 0;
    public $itemAudio = '';
    public $itemId;
    public $lpItems;
    public $displayGallery = false;
    public $displayUploadForm = false;
    public $breadcrumb = '';
    public $documentSysPath;
    public $documentWebPath;
    
    public $extraParams;
    public $stylesheet;
    public $showIframe = false;
    public $itemUrl;
    public $encodings;
    public $makers;
    public $proximities;
    public $themes;
    public $interfaces;
    public $certificates;
    public $certificateThumb = '';
    public $certifScoreType;
    public $certifScoreValue;
    public $charset;
    public $exportMode;
    public $exportNav;
    public $streamServer;
    public $streamClient;
    public $imageCategory;
    public $lblImageUpload;
    public $isScorm;
   
    public function __construct() {
		
		if(!isset($_SESSION['tool']) && $_SESSION['tool'] != 'scenario'){
			$this->validateSession();
		}

        $this->courseCode = api_get_course_id();
        $this->courseInfo = api_get_course_info();
        $this->userId = api_get_user_id();
        
        $this->_lpModel = new application_author_models_ModelLearnpath();
        $this->_lpItemModel = new application_author_models_ModelLearnpathItem();
        
        $this->lpId = $this->getRequest()->getProperty('lpId', '');
        $this->tplId = $this->getRequest()->getProperty('tplId', '');        
        $this->itemType = $this->getRequest()->getProperty('itemType', 'document');
        $this->itemId = $this->getRequest()->getProperty('lpItemId', '');                       
        $ressource = $this->getRequest()->getProperty('ressource', '');
            
        if (!empty($this->lpId)) {
            $this->lpInfo = $this->_lpModel->getModuleInfo($this->lpId);
            $this->lpItems = $this->_lpModel->getLpItems($this->lpId);
            $this->_lpItemModel->setLpId($this->lpId);
        }
        
        if (!empty($this->itemId)) {
            $itemInfo = $this->_lpItemModel->getLpItemInfo($this->itemId);
            $this->itemTitle = $itemInfo['title'];
            $this->itemPath = $itemInfo['path'];
            $this->_lpItemModel->setId($this->itemId);
            $this->_lpItemModel->setPosition($itemInfo['display_order']);
            $this->itemType = $itemInfo['item_type'];
            $this->itemAudio = $itemInfo['audio'];
        }
       
        if (!empty($ressource)) {
            switch ($ressource) {
                case 'document':                    
                    if (is_numeric($this->tplId)) {
                        $template = $this->_lpItemModel->getTemplateInfo($this->tplId);
                        $this->itemTitle = $template['title'];
                        $this->itemPath = '';
                        $this->itemType = $ressource;
                    }                    
                    break;
                case 'quiz':
                    $path = $this->getRequest()->getProperty('itemPath', '');
                    if (is_numeric($path)) {
                        $quiz = $this->_lpItemModel->getQuizInfo($path);
                        $this->itemTitle = $quiz['title'];
                        $this->itemPath = $path;
                        $this->itemType = $ressource;
                    }
                    break;
            }
        }
        $this->itemTitle = $this->encodingCharset($this->itemTitle);
        $this->initSettings();
    }
    
    public function initSettings() {
        $this->validateSession();
        $this->setTheme('tools');  
        
        if (!empty($this->lpInfo['theme'])) {
            $this->setStyleSheet($this->lpInfo['theme']);
        }
        
        $this->toolName = TOOL_AUTHOR;
        $this->setLanguageFile(array('learnpath', 'course_home', 'scormdocument', 'scorm', 'document', 'resourcelinker', 'registration'));        
        $this->documentSysPath = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document';
        $this->documentWebPath = api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/document';        
        $this->editorConfig = array(
                                'ToolbarSet' => 'Authoring', 'Width' => '100%', 'Height' => '600', 'FullPage' => true,
                                'CreateDocumentDir' => '',
                                'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
                                'bodyId'=>'author',
                                'Placeholder' => get_lang('TypeHereAuthor')
                            );        
        $this->encodings = array('UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'cp1251', 'cp1252', 'KOI8-R', 'BIG5', 'GB2312', 'Shift_JIS', 'EUC-JP');        
        $this->stylesheet = api_get_setting('stylesheets');
        $this->makers = array(
                "--".get_lang('GenericScorm')."--", "--".get_lang('Other')."--", 'Accent', 'Accenture', 'ADLNet', 'Articulate', 'ATutor', 'Blackboard', 'Calfat',
                'Captivate', 'Claroline', 'Commest', 'Coursebuilder', 'Docent', 'Dokeos', 'Dreamweaver', 'Easyquiz', 'e-doceo', 'ENI Editions', 'Explio', 'Flash',
                'HTML', 'HotPotatoes', 'Hyperoffice', 'Ingenatic', 'Instruxion', 'iProgress', 'Lectora', 'Microsoft', 'Onlineformapro', 'Opikanoba', 'Plantyn',
                'Saba', 'Skillsoft', 'Speechi', 'Thomson-NETg', 'U&I Learning', 'Udutu', 'WebCT'
          );

        $this->proximities = array('local'=>$this->get_lang('Local'), 'remote'=>$this->get_lang('Remote'));
        $this->interfaces = array(0 => 'Dokeos navigation', 1 => 'With a table of contents', 2 => 'Full screen (Flash modules)', 3 => 'Open new window (Flash modules)', 4 => 'Dokeos navigation (Fixed height)');        
        $this->themes = api_get_themes();        
        
        $language = !empty($this->courseInfo['language'])?$this->courseInfo['language']:api_get_interface_language();                
        if (api_get_setting('enable_certificate') === 'true') {
            $this->certificates = CertificateManager::create()->getCertificatesList($language);   
            $this->certificateThumb = CertificateManager::create()->returnCertificateThumbnailImg($this->lpInfo['certif_template']);            
            if (!empty($this->lpInfo['certif_min_score']) && $this->lpInfo['certif_min_score'] != '0.00') {
                $this->certifScoreType = 'score';
                $this->certifScoreValue = $this->lpInfo['certif_min_score'];
            } 
            else if (!empty($this->lpInfo['certif_min_progress']) && $this->lpInfo['certif_min_progress'] != '0.00') {
                $this->certifScoreType = 'progress';
                $this->certifScoreValue = $this->lpInfo['certif_min_progress'];
            }
        }
        $this->charset = $GLOBALS['charset'];
        
        $splitWebpath = explode("/", api_get_path(WEB_PATH));                
        $this->streamServer = 'dokeos.net';
        $this->streamClient = $splitWebpath[count($splitWebpath) - 2];
        
    }
    
    public function index() {
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'css/'.$this->stylesheet.'/course_navigation.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/css/styles.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/css/author.css', 'css');
        //$this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/css/iframe_styles.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.alerts/jquery.alerts.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/jquery.iframe-auto-height.js'); 
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'ckeditor/plugins/streaming/jquery.base64.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/functions.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/authorModel.js');         
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/scriptController.js');  
        //$this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/fix.js');  
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js');  
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.alerts/jquery.alerts.js');
        // load current Items
        if (!empty($this->itemType)) {
            switch ($this->itemType) {                
                case 'document':
                    if (is_numeric($this->tplId)) {
                        // set selected template to the editor 
                        $this->editorValue = $this->_lpItemModel->getTemplateForEditor($this->tplId);
                    }
                    else if (is_numeric($this->itemId)) {
                        // set selected template to the editor 
                        $itemInfo = $this->_lpItemModel->getLpItemInfo($this->itemId);                            
                        if (empty($itemInfo['content'])) {
                            $this->_lpItemModel->setId($this->itemId);
                            $this->_lpItemModel->fieldValues['content'] = $this->_lpItemModel->getDocumentContent($itemInfo['path']);
                            $lpItemId = $this->_lpItemModel->save();
                            $itemInfo = $this->_lpItemModel->getLpItemInfo($lpItemId); 
                        }                            
                        $this->editorValue = stripslashes($itemInfo['content']);
                    }
                    break;
                case 'quiz':
                    if (is_numeric($this->itemPath)) {                        
                        $this->showIframe = true;                        
                        $this->itemUrl = api_get_path(WEB_CODE_PATH).'exercice/exercice_submit.php?'.api_get_cidreq().'&origin=learnpath&learnpath_id='.$this->lpId.'&exerciseId='.$this->itemPath.'&notime=notime';
                    }
                    break;                
            }
        }        
        
        $deviceInfo = api_get_navigator();
		$this->deviceType = ($deviceInfo["device"]["devicetype"] == "mobile" ? "1" : "0");

    
        $this->templates = $this->_lpItemModel->getTemplates();
        $this->thumbnails = $this->_lpItemModel->getThumbnails();                         
    }
    
    public function exportScorm() {
        $objLp = new learnpath($this->courseCode, $this->lpId, $this->userId);
        $objLp->scorm_export();
        exit;
    }
    
    public function layout() {        
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();            
        if (!empty($this->itemId)) {
            $this->extraParams .= '&lpItemId='.intval($this->itemId);
        }        
        $this->templates = $this->_lpItemModel->getTemplates();                      
    }
    
    public function quiz() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();        
        if (!empty($this->itemId)) {
            $this->extraParams .= '&lpItemId='.intval($this->itemId);
        }
        if(isset($_GET['action']) AND $_GET['action'] == 'next'){
           $this->extraParams .= '&action=next';
        }        
        $this->quizzes = $this->_lpItemModel->getQuizzes();        
    }
    
    public function image() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();    

        $category = $this->getRequest()->getProperty('category', '');   
        $this->imageCategory = $category;
        $links = array();
        $links[] = '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=image&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=550&height=360&refresh=false" class="reopen-dialog" title="'.$this->get_lang("Image").'" class="reopen-dialog">'.$this->get_lang("Images").'</a>';
        if (!empty($category)) {
            switch ($category) {
                case 'avatars':
                    $this->lblImageUpload = $this->get_lang('AddAvatars');
                    $this->images = $this->_lpItemModel->getDocumentFiles('mascot');                    
                    $links[] = '<strong>'.$this->get_lang("Avatars").'</strong>';                    
                    break;
                case 'mindmaps': 
                    $this->lblImageUpload = $this->get_lang('AddMindmaps');
                    $this->images = $this->_lpItemModel->getDocumentFiles('mindmaps');                    
                    $links[] = '<strong>'.$this->get_lang("Mindmaps").'</strong>';           
                    break;
                case 'icons': 
                    $this->lblImageUpload = $this->get_lang('AddIcons');
                    $this->images = $this->_lpItemModel->getDocumentFiles('images', 'icons');                    
                    $links[] = '<strong>'.$this->get_lang("Icons").'</strong>';           
                    break;
                case 'diagrams':
                    $this->lblImageUpload = $this->get_lang('AddDiagrams');
                    $this->images = $this->_lpItemModel->getDocumentFiles('images', 'diagrams');                    
                    $links[] = '<strong>'.$this->get_lang("Diagrams").'</strong>';
                    break;
                case 'images':
                    $this->lblImageUpload = $this->get_lang('AddImages');
                    $this->images = $this->_lpItemModel->getDocumentFiles('images', '', 'images/diagrams');
                    $links[] = '<strong>'.$this->get_lang("Gallery").'</strong>';
                    break;
            }
            $this->displayGallery = true;
            $this->breadcrumb = implode(' / ',$links);
        }
        
    }
    
    public function audio() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();         
        $category = $this->getRequest()->getProperty('category', '');   
        $links = array();
        $links[] = '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=audio&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=555&height=220&refresh=false" class="reopen-dialog" title="'.$this->get_lang("Audio").'" class="reopen-dialog">'.$this->get_lang("Audio").'</a>';
        if (!empty($category)) {
            switch ($category) {
                case 'audio':
                    $this->audios = $this->_lpItemModel->getDocumentFiles('audio');                    
                    $links[] = '<strong>'.$this->get_lang("Gallery").'</strong>';  
                    $this->displayGallery = true;
                    break;
                case 'recording':
                    $provider = 1;
                    $uniqid = uniqid();
                    $objAudiorecorder = AudiorecorderFactory::getAudiorecorderObject($provider, 'module', $uniqid);
                    $this->setHtmlHeadXtra($objAudiorecorder->returnCss(), 'inline');
                    $this->setHtmlHeadXtra($objAudiorecorder->returnJs('author'), 'inline');
                    $this->displayUploadForm = true;
                    $links[] = '<strong>'.$this->get_lang("Recording").'</strong>';                                              
                    break;               
            }            
            $this->breadcrumb = implode(' / ',$links);
        }
        
    }
    
    public function video() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();         
        $category = $this->getRequest()->getProperty('category', '');   
        $links = array();
        $links[] = '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=video&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=550&height=200&refresh=false" class="reopen-dialog" title="'.$this->get_lang("Videos").'" class="reopen-dialog">'.$this->get_lang("Videos").'</a>';
        if (!empty($category)) {
            switch ($category) {
                case 'video':
                    $this->videos = $this->_lpItemModel->getDocumentFiles('video');                    
                    $links[] = '<strong>'.$this->get_lang("Gallery").'</strong>';  
                    $this->displayGallery = true;
                    break;
                case 'streaming':
                    $this->videos = $this->getAllWebTvVideos();
                    $links[] = '<strong>'.$this->get_lang("Gallery").'</strong>';  
                    $this->displayGallery = true;
                    break;
                case 'social':
                    $this->displayUploadForm = true;
                    $links[] = '<strong>'.$this->get_lang("YoutubeAndVimeo").'</strong>';
                    break;
                case 'upload':                     
                    $this->displayUploadForm = true;
                    $links[] = '<strong>'.$this->get_lang("Upload").'</strong>';           
                    break;               
            }            
            $this->breadcrumb = implode(' / ',$links);
        }        
    }
    
    public function getVideoThumbnail($fullsyspath) {
        $thumb_sys_path = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/temp/thumb_'.pathinfo($fullsyspath, PATHINFO_FILENAME).'.png';
        $thumb_web_path = api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/temp/thumb_'.pathinfo($fullsyspath, PATHINFO_FILENAME).'.png';
        $thumb_width = 200;
        $thumb_height = 115;
        $extension = strtolower(pathinfo($fullsyspath, PATHINFO_EXTENSION));
        if(in_array($extension, array('wmv','mpg','mpeg','mov','avi','flv','mp4'))) {
            // check extension ffmpeg            
            $ffmpeg = trim(shell_exec('type -P ffmpeg'));
            if (empty($ffmpeg)) {
                return false;
            }
            if(!file_exists($thumb_sys_path)) {     
                $cmd = "ffmpeg -i $fullsyspath -ss 00:00:14.435 -f image2 -vframes 1 $thumb_sys_path";
                $result = shell_exec($cmd);
            }
        }
        return file_exists($thumb_sys_path)?$thumb_web_path:'';
    }
    
    public function sorter() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();   
    }
    
    public function settings() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
        $this->isScorm =  in_array($this->lpInfo['lp_type'], array(2, 3));        
    }
    
    public function initialSettings() {
		
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore();
        //$this->isScorm =  in_array($this->lpInfo['lp_type'], array(2, 3));        
    }
    
    public function preview() {
        $this->loadIframeHtmlHeadXtra();
        $this->setReducedHeader();
        $this->disabledFooterCore(); 
    }
            
    public function getAction() {
        $html = '';
        if (api_is_allowed_to_edit()) {
            if(isset($_GET['action']) AND $_GET['action'] == 'next'){
                $action_next .= '&action=next';
            }
            $html .= '<span id="hd-top-navigation">';
            $html .= $this->getTopNavigation($this->itemTitle);
            $html .= '</span>';            
            $extraParams = '';
            if (!empty($this->itemId)) {
                $extraParams .= '&lpItemId='.intval($this->itemId);
            }
            if (!empty($this->itemPath)) {
                $extraParams .= '&itemPath='.intval($this->itemPath);
            }            
            $html .= '<div id="header_actions" class="actions">';
            $html .=  '<a href="#" title="'.$this->get_lang("Save").'" id="author-save">' . Display::return_icon('pixel.gif', $this->get_lang('Save'), array('class' => 'toolactionplaceholdericon toolactionsave')) . $this->get_lang("Save") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=image&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=550&height=360&refresh=false" class="action-dialog" title="'.$this->get_lang("Image").'">' . Display::return_icon('pixel.gif', $this->get_lang('Image'), array('class' => 'toolactionplaceholdericon toolactiongallery_all')) . $this->get_lang("Image") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=audio&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=555&height=220&refresh=false" class="action-dialog" title="'.$this->get_lang("Audio").'">' . Display::return_icon('pixel.gif', $this->get_lang('Audio'), array('class' => 'toolactionplaceholdericon toolactionaudio')) . $this->get_lang("Audio") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=video&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=550&height=200&refresh=false" class="action-dialog" title="'.$this->get_lang("Video").'">' . Display::return_icon('pixel.gif', $this->get_lang('Video'), array('class' => 'toolactionplaceholdericon toolactionauthorscenario')) . $this->get_lang("Video") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=settings&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=650&height=490&refresh=false" class="action-dialog" title="'.$this->get_lang("Publication").'">' . Display::return_icon('pixel.gif', $this->get_lang('Publication'), array('class' => 'toolactionplaceholdericon toolsettings')) . $this->get_lang("Publication") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=quiz&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=400&height=500&refresh=false'.$extraParams.$action_next.'" class="action-dialog" title="'.$this->get_lang("Quiz").'"">' . Display::return_icon('pixel.gif', $this->get_lang('Quiz'), array('class' => 'toolactionplaceholdericon toolactionnewquiz')) . $this->get_lang("Quiz") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=sorter&'.api_get_cidreq().'&lpId='.$this->lpId.'&width=750&height=600&refresh=false" class="action-dialog" title="'.$this->get_lang("Sorter").'">' . Display::return_icon('pixel.gif', $this->get_lang('Sorter'), array('class' => 'toolactionplaceholdericon toolactiondraganddrop')) . $this->get_lang("Sorter") . '</a>';
            $html .=  '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Player&func=view&'.api_get_cidreq().'&lpId='.$this->lpId.'">' . Display::return_icon('pixel.gif', $this->get_lang('Preview'), array('class' => 'toolactionplaceholdericon toolactionauthorpreview')) . $this->get_lang("Preview") . '</a>';
            $html .= '</div>';            
        }        
        return $html;
    }
    
    public function getTopNavigation($itemTitle) {
		$itemTitle = ($itemTitle ? $itemTitle : "slide" . str_pad("".(count($this->lpItems)+1)."", 3, '0', STR_PAD_LEFT));       
        $this->itemTitle = $itemTitle;
        $html  = '<div id="lp-top-navigation">';
        $html .= ' <div class="cols" style="padding-top:7px;padding-right:5px;">'.get_lang('SlideName').'</div>';
        $html .= '  <div id="item-title" class="cols"><input type="text" id="txt-item-title" value="'.$itemTitle.'" /></div>';
        $html .= '  <a href="#" id="unmute" class="audio-actions" style="'.(!empty($this->itemAudio)?'display:block;':'display:none;').'float:left;"><img id="speaker_on" src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" /></a>';        
        $html .= '  <div id="arrows" class="cols">';         
        $html .= $this->getMenuItems();
//      $html .= '<button id="author-next">'.$this->get_lang('CreateNewSlide').'</button>';     
        if (!empty($this->itemAudio) && file_exists($this->documentSysPath.$this->itemAudio)) {
            $autoplay = true;
            $audiopath = $this->documentWebPath.$this->itemAudio;
        }
        else {
            $autoplay = false;
            $audiopath = 'audio.mp3';
        }       
        $html .= $this->getAudioVideo($audiopath, 'item-record', 0, 0, $autoplay, true);
        
        $html .= '  </div>';
        $html .= '</div>';
        return $html;
    }
    
    public function getAllWebTvVideos() {        
        $webtvVideos = array();
        $videos = $this->_lpItemModel->getWebtvVideos();
        if (!empty($videos)) {
            foreach ($videos as $video) {
                $webtvVideos[$video['id']] = $video;
                $webtvVideos[$video['id']]['type'] = 'mp4';
                $webtvVideos[$video['id']]['file'] = $video['video_src'];
                $webtvVideos[$video['id']]['url'] = $this->streamClient.'/'.$video['video_src'];
            }
        }
        return $webtvVideos;
    }
    
    public function getAudioVideo($fullpath, $id, $w = 400, $h = 300, $autoplay = false, $hide = false, $thumbnail = '', $streaming = false) {
            $ext = substr(strrchr($fullpath, '.'), 1);
            $extrascript = $previewImage = '';            
            if ($autoplay) { $extrascript = "jwplayer('mediaplayer$id').play();"; }
            if (!empty($thumbnail)) {$previewImage = ",image:'$thumbnail'"; }                        
            if ($ext == 'flv' || $ext == 'mp4' || $ext == 'mov') {
                $return .= "<div class='thePlayer ".($hide?'invisible':'visible')."' id='$id'>";
                $return .= "<div id='mediaplayer".$id."'>Playing educational video on the Dokeos platform</div>";
                $return .= "<script type='text/javascript'>
                                jwplayer('mediaplayer$id').setup({                                    
                                    file: '$fullpath',
                                    width: '$w',
                                    height:'$h',
                                    primary: 'flash'
                                    $previewImage
                                });
                                $extrascript
                                </script>";
                $return .= "</div>";
            } elseif ($ext == 'mp3'|| $ext == 'ogg'|| $ext == 'ogv') {
                $return .= "<div class='thePlayer ".($hide?'invisible':'visible')."' id='$id' >";
                $return .= "<div id='mediaplayer".$id."'>Playing educational video on the Dokeos platform</div>";
                $return .= "<script type=\"text/javascript\">
                                jwplayer(\"mediaplayer$id\").setup({                                    
                                    file: '$fullpath',
                                    width: '".$w."',
                                    height:'".$h."',
                                    controlbar: 'bottom',
                                    primary: 'flash'
                                });
                                $extrascript
                                </script>";
                $return .= "</div>";
            } elseif ($ext == 'mpg' || $ext == 'wmv' || $ext == 'wma' || $ext == 'avi') {
                $return .= '<OBJECT ID="MediaPlayer" WIDTH="'.$w.'" HEIGHT="'.$h.'" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
                                <PARAM NAME="FileName" VALUE="'.$fullpath.'">
                                <PARAM name="autostart" VALUE="false">
                                <PARAM name="ShowControls" VALUE="true">
                                <param name="ShowStatusBar" value="true">
                                <PARAM name="ShowDisplay" VALUE="true">
                                <EMBED type="application/x-mplayer2" src="'.$fullpath.'" name="MediaPlayer" width="'.$w.'" height="'.$h.'" ShowControls="1" ShowStatusBar="1" ShowDisplay="1" autostart="0"></EMBED>
                            </OBJECT>';
            }
            return $return;
    }
    
    public function getMenuItems($edition = false) {
        $html .= '  <a href="#" class="course_menu_button"><img src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" id="courseMenuButton"></a>';        
        if (!empty($this->lpItems)) {                                              
            if ($edition) {
                $li = $li_dsp = '';
                foreach ($this->lpItems as $item) {
                    $itemInfo = $this->_lpItemModel->getLpItemInfo($item['id']);
                    $li .= '<li id="toggle_menu_item_'.$item['id'].'" class="items-menu-view '.($this->itemId == $item['id']?'current':'').'">
                               <a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType='.$item['item_type'].'&lpId='.$this->lpId.'&lpItemId='.$item['id'].'">
                                    '.cut($item['title'], 25).'
                                </a>                  
                            </li>';
                    
                    $laudio = '';
                    if (!empty($itemInfo['audio'])) {
                        $laudio = '<img src="'.api_get_path(WEB_IMG_PATH).'sound_mp3.png" class="item-action-delaudio item-actions" id="delaudio-'.$item['id'].'" />';
                    }                    
                    $li_dsp .= '<li id="toggle_menu_item_'.$item['id'].'" class="ui-state-default '.($this->itemId == $item['id']?'current':'').'">
                                <table width="100%">
                                <tr>
                                    <td width="30px"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></td>
                                    <td>
                                        <a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType='.$item['item_type'].'&lpId='.$this->lpId.'&lpItemId='.$item['id'].'">
                                            '.cut($this->encodingCharset($item['title']), 25).'
                                        </a>
                                    </td>
                                    <td width="80px">
                                        <img src="'.api_get_path(WEB_IMG_PATH).'delete.png" class="item-action-delete item-actions" id="delitem-'.$item['id'].'" />&nbsp;
                                        '.$laudio.'                                    
                                    </td>
                                </tr>
                                </table>
                                <input type="hidden" name="itemIds[]" value="'.$item['id'].'" />                            
                            </li>';
                    
                }
                $html .= '<div class="scroll-pane menu-item-view" id="courseToggleMenu">
                            <div class="jspContainer">
                                <div class="jspPane">  
                                        <div class="icon-notify"><a href="#" id="show-items-form"><img src="'.api_get_path(WEB_IMG_PATH).'edit.png">&nbsp;'.get_lang('EditItems').'</a></div>
                                        <ul>
                                            '.$li.'
                                        </ul>
                                 </div>
                             </div>
                          </div>';
                $html .= '<div class="scroll-pane menu-item-edit" id="courseToggleMenu">
                                <div class="jspContainer">
                                    <div class="jspPane">
                                        <p class="item-notify">'.get_lang('YouHaveToDoSubmitForUpdatingTheChanges').'</p>
                                        <form method="POST" action="'.api_get_path(WEB_CODE_PATH).'application/author/assets/ajax/learnpathItem.ajax.php?action=updateItems&'.api_get_cidreq().'" id="form-items">
                                            <ul class="item-sort">
                                                '.$li_dsp.'
                                            </ul>
                                            <p class="align-right">
                                                <input type="button" name="items_cancel" id="bt-items-cancel" value="'.get_lang('Cancel').'" />&nbsp;
                                                <input type="submit" name="items_submit" value="'.get_lang('Save').'" /></p>
                                            <input type="hidden" name="lpId" value="'.$this->lpId.'" />
                                         </form>                                         
                                     </div>
                                 </div>
                              </div>';
            }
            else {
                $li = '';
                foreach ($this->lpItems as $item) {
                    $itemInfo = $this->_lpItemModel->getLpItemInfo($item['id']);                                                       
                    $li .= '<li id="toggle_menu_item_'.$item['id'].'" class="items-menu-view '.($this->itemId == $item['id']?'current':'').'">
                               <a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType='.$item['item_type'].'&lpId='.$this->lpId.'&lpItemId='.$item['id'].'">
                                    '.cut($this->encodingCharset($item['title']), 80).'
                                </a>                  
                            </li>';
                }
                $html .= '<div class="scroll-pane menu-item-view" id="courseToggleMenu">
                            <div class="jspContainer">
                                <div class="jspPane">  
                                    <ul>
                                        '.$li.'
                                    </ul>
                                 </div>
                             </div>
                          </div>';
            }
            
            
        }
        return $html;
    }
    
    public function getHtmlEditorBlocksContent($html) {    
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $blocks = array();
        $divs = $doc->getElementsByTagName('div');
        if (!empty($divs)) {
           foreach ($divs as $div) {                                                
               $div_id = $div->getAttribute('id');
               if (preg_match("/col[0-9]/", $div_id)) {                    
                   $block = $doc->getElementById($div_id);
                   $blocks[$div_id] = $block->textContent;
               }
           }
        }
        return $blocks;
    }
    
    
    
    public function loadIframeHtmlHeadXtra() {
        $navigator = api_get_navigator();
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.alerts/jquery.alerts.css', 'css');               
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/css/iframe_styles.css', 'css');   
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/css/bootstrap.min.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/css/bootstrap-responsive.min.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/css/jquery.fileupload-ui.css', 'css');                        
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js');                  
         
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js');  
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.alerts/jquery.alerts.js'); 
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'ckeditor/plugins/streaming/jquery.base64.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/responsive/js/modernizr.js');
        
        
        
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js');
        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/dokeos.js.php');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/functions.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/authorModel.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/iframeScriptController.js');
        //$this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/fix.js');
    }
    
    public function validateSession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
        $objSecurity->verifyAccessToCourse(true);
        if (api_get_setting('enable_author_tool') === 'false') {
            api_not_allowed();
        } 
    }
    
    
    
}
