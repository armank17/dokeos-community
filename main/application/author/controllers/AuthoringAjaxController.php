<?php
class application_author_controllers_AuthoringAjax  extends application_author_controllers_Authoring 
{            
    
    public $messages;
    public $certificate;
    public function __construct() {
        parent::__construct();
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
    }
    
    public function displayCertPicture() {        
        $certifId = $this->getRequest()->getProperty('certifId', '');
        $this->certificate = CertificateManager::create()->returnCertificateThumbnailImg($certifId);
    }
    
    public function updateLp() {
        extract($_POST);
        // update module
        if ($lpId) {
            $this->_lpModel->setLpId($lpId);
        }
        else {
            $this->_lpModel->fieldValues['description'] = '';
            $this->_lpModel->fieldValues['path'] = '';
            $this->_lpModel->fieldValues['content_license'] = '';
            $this->_lpModel->fieldValues['origin_tool'] = 'author';
        }
        $this->_lpModel->fieldValues['name'] = (empty($lp_name) ? '' : $lp_name);;        
        $this->_lpModel->fieldValues['default_encoding'] = (empty($lp_encoding) ? 'UTF-8' : $lp_encoding);        
        $this->_lpModel->fieldValues['content_maker'] = (empty($lp_maker) ? '' : $lp_maker);
        $this->_lpModel->fieldValues['content_local'] = (empty($lp_proximity) ? '' : $lp_proximity);         
        $this->_lpModel->fieldValues['theme'] = (empty($lp_theme) ? '' : $lp_theme);
        $this->_lpModel->fieldValues['author'] = (empty($lp_author) ? 'Dokeos' : $lp_author);
        $this->_lpModel->fieldValues['lp_interface'] = intval($lp_interface);        
        $this->_lpModel->fieldValues['certif_template'] = intval($certificate_template);
        $this->_lpModel->fieldValues['session_id'] = api_get_session_id();
        if ($certif_evaluation_type == 1) {
            $this->_lpModel->fieldValues['certif_min_progress'] = floatval($certificate_min_score);
            $this->_lpModel->fieldValues['certif_min_score'] = 0.00;
        }
        else {
            $this->_lpModel->fieldValues['certif_min_progress'] = 0.00;
            $this->_lpModel->fieldValues['certif_min_score'] = floatval($certificate_min_score);
        }
        $this->_lpModel->fieldValues['debug'] = intval($enable_debug);
        $this->_lpModel->fieldValues['behavior'] = intval($enable_behavior);
        $updateLp = $this->_lpModel->save();
        if ($updateLp) {
            //echo $this->get_lang('LpUpdated');
            echo $updateLp;
        }
        exit;
    }
    
    public function saveItem() {
        extract($_POST);         
        $json = array();
        
        if (trim($item_title) == '') {
            $json = array('itemId'=>0, 'message'=>$this->get_lang('TitleRequiredField'));    
        }
        else {            
            if (empty($item_lp_id)) {                
                // Create module
                $this->_lpModel->fieldValues['lp_type'] = 1;
                $this->_lpModel->fieldValues['name'] = api_ucfirst(format_locale_date(DATE_TIME_FORMAT_LONG));
                $this->_lpModel->fieldValues['description'] = '';
                $this->_lpModel->fieldValues['path'] = '';
                $this->_lpModel->fieldValues['content_license'] = '';
                $this->_lpModel->fieldValues['origin_tool'] = 'author';
                $item_lp_id = $this->_lpModel->save();
                api_item_property_update($this->courseInfo, 'learnpath', $item_lp_id, 'LearnpathAdded', $this->userId);
                
                $ecommerceCatalog['title'] = $this->_lpModel->fieldValues['name'];
                $ecommerceCatalog['description'] = $this->_lpModel->fieldValues['description'];
                $ecommerceCatalog['lp_id'] = $item_lp_id;
                $ecommerceCatalog['course_code'] = api_get_course_id();
                CatalogueModuleModel::create()->saveEcommerceLpModule($ecommerceCatalog);                
            }     
            
            
            //api_convert_encoding($item_title,"ISO-8859-15","UTF-8");
            
            switch($item_type) {
                case 'document':
                    if (!empty($item_id)) {
                        $this->_lpItemModel->setId($item_id);
                    }
                    else {
                        $lastItem =  $this->_lpModel->getLastItem($item_lp_id);                                                
                        $this->_lpItemModel->fieldValues['display_order'] = intval($lastItem['display_order']) + 1;
                    }
                    
                    //$this->_lpItemModel->fieldValues['content'] = addslashes($authoring_editor);
                    $this->_lpItemModel->fieldValues['content'] = $authoring_editor;
                    $this->_lpItemModel->fieldValues['item_type'] = $item_type;
                    $this->_lpItemModel->fieldValues['lp_id'] = $item_lp_id;
                    $this->_lpItemModel->fieldValues['title'] = $item_title;
                    $this->_lpItemModel->fieldValues['path'] = '';
                    $this->_lpItemModel->fieldValues['max_score'] = 100;
                    $lpItemId = $this->_lpItemModel->save();
                    
                    if (empty($item_path)) {
                        //$documentId = $this->_lpItemModel->createDocument($item_title, $authoring_editor);                        
                        $documentId = $this->_lpItemModel->createDocument($item_title, $authoring_editor,$item_lp_id,$lpItemId);
                    }
                    else {
                        $documentId = $this->_lpItemModel->editDocument($item_path, $authoring_editor,$item_lp_id,$lpItemId); 
                    }                    
                    $this->_lpItemModel->fieldValues['path'] = $documentId;
                    $this->_lpItemModel->setId($lpItemId);
                    $this->_lpItemModel->save();
                       
                    break;
                case 'quiz':
                    if (!empty($item_id)) {
                        $this->_lpItemModel->setId($item_id);
                    }
                    else {
                        $lastItem =  $this->_lpModel->getLastItem($item_lp_id);
                        $this->_lpItemModel->fieldValues['display_order'] = intval($lastItem['display_order']) + 1;
                    }
                    $this->_lpItemModel->fieldValues['item_type'] = $item_type;
                    $this->_lpItemModel->fieldValues['lp_id'] = $item_lp_id;
                    $this->_lpItemModel->fieldValues['title'] = $item_title;
                    $this->_lpItemModel->fieldValues['path'] = $item_path;            
                    $this->_lpItemModel->fieldValues['max_score'] = $this->_lpItemModel->getQuizMaxScore($item_path); 
                    $getQuizInfo = $this->_lpItemModel->getQuizInfo($item_path);                                        
                    //$this->_lpItemModel->fieldValues['audio'] = str_replace(api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/document', '', $item_audio);
                    $lpItemId = $this->_lpItemModel->save();                      
                    $this->_lpItemModel->_createThumbnail($getQuizInfo['id'],$item_lp_id,$lpItemId, 'quiz');
                    break;                                                       
            }
            if (!empty($lpItemId)) {
                if ($btn_action == 'next') {
                    $this->_lpItemModel->setLpId($item_lp_id);
                    $lpItemInfo = $this->_lpItemModel->getLpItemInfo($lpItemId);
                    $this->_lpItemModel->setPosition($lpItemInfo['display_order']);
                    $next = $this->_lpItemModel->nextItem();
                    $json['itemId'] = $lpItemId;
                    $json['message'] = '';
                    $json['url'] = urlencode(api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&lpId='.$item_lp_id.'&action='.$btn_action);
                }
                else {
                    $json['itemId'] = $lpItemId;
                    if ($item_func != 'sorter') {
                        $json['url'] = urlencode(api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType='.$nextInfo['item_type'].'&lpId='.$item_lp_id.'&lpItemId='.$lpItemId);
                    }
                }
                
                // update item audio
                if (!empty($item_audio)) {
                    $item_audio =  str_replace(api_get_path(WEB_COURSE_PATH), api_get_path(SYS_COURSE_PATH), $item_audio);
                    if (strpos($item_audio, '/temp/audionano-') !== FALSE && file_exists($item_audio)) {
                        $audiorecorderDir = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document/audio/audiorecorder/';
                        if (!is_dir($audiorecorderDir)) {
                            $perm = api_get_setting('permissions_for_new_directories');
                            $perm = octdec(!empty($perm) ? $perm : '0770');
                            mkdir($audiorecorderDir, $perm, true);
                            $docId = add_document($this->courseInfo, '/audio/audiorecorder/', 'folder', 0, 'audiorecorder');
                            if ($docId) {
                                api_item_property_update($this->courseInfo, 'document', $docId, 'FolderCreated', $this->userId, null, null, null, null, api_get_session_id());
                                api_item_property_update($this->courseInfo, 'document', $docId, 'invisible', $this->userId);
                            }
                        }
                        $newAudioSysPath = $audiorecorderDir.'audiorecorder-'.$lpItemId.'.mp3';
                        if (copy($item_audio, $newAudioSysPath)) {
                            @unlink($item_audio);
                            $item_audio = $newAudioSysPath;
                            $fileSize = filesize($item_audio);
                            $tmpTitle = basename($item_audio);
                            $saveFilePath = str_replace(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document', '', $item_audio);
                            $documentId = add_document($this->courseInfo, $saveFilePath, 'file', $fileSize, $tmpTitle);
                            if ($documentId) {
                                api_item_property_update($this->courseInfo, 'document', $documentId, 'DocumentAddedFromLearnpath', $this->userId, null, null, null, null, api_get_session_id());
                            }
                        }
                    }
                    if (file_exists($item_audio)) {
                        $this->_lpItemModel->setId($lpItemId);
                        $this->_lpItemModel->fieldValues['audio'] = str_replace(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document', '', $item_audio);
                        $lpItemId = $this->_lpItemModel->save();   
                    }                    
                }
                                                
            }              
        }
        echo json_encode($json);
        exit;
    }
    
    public function uploadVideo() {
        $extension = pathinfo($_FILES['myfile']['name'], PATHINFO_EXTENSION);
        if (in_array($extension, array('mp4', 'flv', 'mov', 'mpg', 'wmv', 'wma', 'avi'))) {
            $videoPath = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document/video/'.$_FILES['myfile']['name'];
            if (move_uploaded_file($_FILES['myfile']['tmp_name'], $videoPath)) {
                $docPath = '/'.pathinfo($videoPath, PATHINFO_BASENAME);
                $docId = add_document($this->courseInfo, $docPath, 'file', filesize($videoPath), pathinfo($videoPath, PATHINFO_FILENAME), '', 0);
                if ($docId) {
                    $json['success'] = true;  
                    $json['message'] = get_lang('InvalidExtension');
                    $json['href'] = '/courses/'.$this->courseInfo['path'].'/document/video/'.$_FILES['myfile']['name'];
                }
            }
            else {
                $json['success'] = false;  
                $json['message'] = get_lang('ErrorUploadingFile'); 
            }
        }
        else {
          $json['success'] = false;  
          $json['message'] = get_lang('InvalidExtension');  
        }                        
        echo json_encode($json);
        exit;
    }
    
    public function deleteItem() {
        $itemId = $this->getRequest()->getProperty('itemId', '');
        $this->_lpItemModel->setId($itemId);
        $this->_lpItemModel->delete();
        exit;
    }
    
    public function deleteAudioItem() {
        $itemId =  $this->getRequest()->getProperty('itemId', '');
        $this->_lpItemModel->setId($itemId);
        $this->_lpItemModel->fieldValues['audio'] = '';
        $this->_lpItemModel->save();
        exit;
    }
    
    public function updateItems() {
        extract($_POST);

        if (!empty($itemIds)) {
            foreach ($itemIds as $ind => $itemId) {
               $pos = $ind + 1;
               $this->_lpItemModel->fieldValues['display_order'] = $pos;
               $this->_lpItemModel->setId($itemId);
               $this->_lpItemModel->save();
            }
        }
    }
    
    public function updateTplEditor() {
        $tplId = $this->getRequest()->getProperty('tplId', '');
        $authoring_editor = $this->getRequest()->getProperty('authoring_editor', '');
        $tplInfo = $this->_lpItemModel->getTemplateInfo($tplId);
        $tplTitle = $tplInfo['title'];
        $tplContent = $this->_lpItemModel->getTemplateForEditor($tplId);       
        if (!empty($authoring_editor)) {
            $content = str_replace(array('\n', '\r', '\r\n'), '', $authoring_editor);
            $blocks = $this->getHtmlEditorBlocksContent($content);
        }         
        if (!empty($blocks)) {
            $newdoc = new DOMDocument();
            $newdoc->loadHTML($tplContent);             
            $divs = $newdoc->getElementsByTagName('div');
            if (!empty($divs)) {
              foreach ($divs as $div) {                                                
                  $div_id = $div->getAttribute('id');
                  if (preg_match("/col[0-9]/", $div_id)) {                    
                      $newdoc->getElementById($div_id)->nodeValue = $blocks[$div_id];                       
                      if ($div_id == 'col1') {
                          $tplTitle = cut(strip_tags($newdoc->getElementById($div_id)->textContent), 80);
                      }
                  }                   
              }
            }             
            $tplContent = $newdoc->saveHTML();
        }       
        echo json_encode(array('success'=>true, 'title'=>$tplTitle, 'item_type'=>'document', 'content'=>$tplContent));
        exit;
    }
    
    public function updateTopNavigation() {
        echo $this->getTopNavigation($this->itemTitle);
        exit;
    }
    
    public function uploadAudio() {        
        $categoryDir = 'audio';
        $options['script_url'] = api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/server/php/';
        $options['upload_dir'] = $this->documentSysPath.'/'.$categoryDir.'/';
        $options['upload_url'] = $this->documentWebPath.'/'.$categoryDir.'/';
        $options['accept_file_types'] = '/\.(mp3)$/i';
        $options['course_code'] = $this->courseCode;        
        $uploadHandler = new UploadAjaxHandler($options);        
        exit;
    }
    
    public function loadAudios() {        
        $this->audios = $this->_lpItemModel->getDocumentFiles('audio');      
        echo $this->setTemplate('load_audios', 'Authoring');
        exit;
    }
    
    public function uploadImage() {        
        $category = $this->getRequest()->getProperty('category', '');       
        $categoryDir = $category;
        if ($category == 'avatars') {
            $categoryDir = 'mascot';
        } 
        else if ($category == 'diagrams') {
            $categoryDir = 'images/diagrams';
        }
        $options['script_url'] = api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/server/php/';
        $options['upload_dir'] = $this->documentSysPath.'/'.$categoryDir.'/';
        $options['upload_url'] = $this->documentWebPath.'/'.$categoryDir.'/';
        $options['accept_file_types'] = '/\.(gif|jpe?g|png)$/i';
        $options['course_code'] = $this->courseCode;
        $options['thumbnail']['max_width'] = 150;
        $options['thumbnail']['max_height'] = 125;
        
        
        
        $uploadHandler = new UploadAjaxHandler($options);        
        exit;
    }
    
    public function uploadImage2() {
		
		$ci = new application_courseInfo_controllers_Info();
		
        $category = $this->getRequest()->getProperty('category', '');       
        $categoryDir = $category;
        if ($category == 'avatars') {
            $categoryDir = 'mascot';
        } 
        else if ($category == 'diagrams') {
            $categoryDir = 'images/diagrams';
        }
        $upload_dir = $this->documentSysPath.'/'.$categoryDir.'/';
        $upload_url = $this->documentWebPath.'/'.$categoryDir.'/';
        $course_code = $this->courseCode;
        
        $thumbnail_max_width = 150;
        $thumbnail_max_height = 125;
        
        $tk = api_get_crop_token();
        
        $imageFileName =  $this->getRequest()->getProperty('imageFileName', '');
        
        $image_sys_path_temp =$ci->imageParam['PICTURE_AUTHOR']['PATH']['SYS'];
		$image_sys_path = $ci->imageParam['PICTURE_AUTHOR']['PATH']['SYS'] ;
		$name_image = $imageFileName;
		$name_image_temp = 'temp_'.$name_image;

		$mode = api_get_setting('permissions_for_new_directories');
		$mode = octdec(!empty($mode)?$mode:'0770');
		mkdir($image_sys_path,$mode,true);
		
		$filename_temp = $image_sys_path_temp . $name_image_temp ;
		$filename = $image_sys_path . $name_image ;
		
		rename($filename_temp,$filename);
        
        $saveFilePath = '/'.$categoryDir.'/'.$name_image;
        $fileSize= filesize($filename);
        $origin_name = $name_image_temp;
        $courseInfo = api_get_course_info($this->course_code);
        $documentId = add_document($courseInfo, $saveFilePath, 'file', $fileSize, $origin_name);
        if ($documentId) {
             api_item_property_update($courseInfo, 'document', $documentId, 'DocumentAddedFromLearnpath', api_get_user_id(), null, null, null, null, api_get_session_id());
        }
        
        exit;
    }
    
    
    public function loadImages() {        
        $category = $this->getRequest()->getProperty('category', '');  
        $this->imageCategory = $category;
        switch ($category) {
            case 'avatars':
                $this->images = $this->_lpItemModel->getDocumentFiles('mascot');                                       
                break;
            case 'mindmaps': 
                $this->images = $this->_lpItemModel->getDocumentFiles('mindmaps');                              
                break;
            case 'diagrams':
                $this->images = $this->_lpItemModel->getDocumentFiles('images', 'diagrams');                    
                break;
            case 'images':
                $this->images = $this->_lpItemModel->getDocumentFiles('images', '', 'images/diagrams');
                break;
        }        
        echo $this->setTemplate('load_images', 'Authoring');
        exit;
    }
    
    public function deleteImage() {        
        $docId = $this->getRequest()->getProperty('docId', '');        
        $category = $this->getRequest()->getProperty('category', ''); 
        $categoryDir = $category;
        if ($category == 'avatars') {
            $categoryDir = 'mascot';
        } 
        else if ($category == 'diagrams') {
            $categoryDir = 'images/diagrams';
        }
        if (!empty($docId)) {
            $docInfo = $this->_lpItemModel->getDocumentInfo($docId);
            $fullpath = $this->documentSysPath.$docInfo['path'];
            $filename = basename($fullpath);
            $thumbpath = $this->documentSysPath.'/'.$categoryDir.'/thumbnail/'.$filename;
            $deleted = DocumentManager::delete_document($this->courseInfo, $docInfo['path'], $this->documentSysPath);
            if ($deleted && file_exists($thumbpath)) {
                @unlink($thumbpath);
            }
        }        
        $this->loadImages();
    }
    
    public function deleteAudio() {
        $docId = $this->getRequest()->getProperty('docId', '');        
        if (!empty($docId)) {
            $docInfo = $this->_lpItemModel->getDocumentInfo($docId);            
            $deleted = DocumentManager::delete_document($this->courseInfo, $docInfo['path'], $this->documentSysPath);            
        }        
        $this->loadAudios();
    }
    
}
