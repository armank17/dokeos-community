<?php
class application_author_controllers_HomeAjax  extends application_author_controllers_Home 
{            
    
    public $messages;
    
    public function __construct() {
        parent::__construct();
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
    }
    
    public function changeLpPosition() {        
        $lpId = $this->getRequest()->getProperty('lp_id', '');
        $newPosition = $this->getRequest()->getProperty('new_order', '');
        $updated = $this->_lpModel->updateModulePosition($lpId, $newPosition); 
        exit;
    }
    
    public function uploadOogie() {    
        if (isset($_FILES['user_file'])) {
            $extensions = array('odp', 'sxi', 'ppt', 'pps', 'sxd', 'pptx');
            if (in_array(strtolower(pathinfo($_FILES['user_file']['name'], PATHINFO_EXTENSION)), $extensions)) {
                $o_ppt = new OpenofficePresentation(true);
                $firstItem = $o_ppt->convert_document($_FILES['user_file']);
                if (isset($o_ppt) && !empty($firstItem)) {
                    $this->messages['error'] = FALSE;
                    $this->messages['msn'] = $this->get_lang('PptAdded');
                }
                else {
                    $this->messages['error'] = TRUE;                    
                    if (!empty($o_ppt->error)) {
                        $this->messages['msn'] = $o_ppt->error;
                    }
                    else {
                        $this->messages['msn'] = $this->get_lang('OogieUnknownError');
                    }
                }
            }
            else {
                $this->messages['error'] = TRUE;
                $this->messages['msn'] = $this->get_lang('OogieBadExtension');
            }
        }
        else {
            $this->messages['error'] = TRUE;
            $this->messages['msn'] = $this->get_lang('ThisFieldIsRequired');
        }         
    }
    
    public function uploadScorm() {
         if (isset($_FILES['user_file'])) {
            $zipFile = new pclZip($_FILES['user_file']['tmp_name']);
            // Check the zip content (real size and file extension)
            $zipContentArray = $zipFile->listContent();
            $package_type = '';
            $manifest = '';
            //the following loop should be stopped as soon as we found the right imsmanifest.xml (how to recognize it?)
            if (is_array($zipContentArray) && count($zipContentArray) > 0) {
                foreach ($zipContentArray as $thisContent) {
                    if (preg_match('~.(php.*|phtml)$~i', $thisContent['filename'])) {
                        //New behaviour: Don't do anything. These files will be removed in scorm::import_package
                    } elseif (stristr($thisContent['filename'], 'imsmanifest.xml') !== FALSE) {
                        $manifest = $thisContent['filename']; //just the relative directory inside scorm/
                        $package_type = 'scorm';
                        break; //exit the foreach loop
                    } elseif (preg_match('/aicc\//i', $thisContent['filename']) != false || strtolower(pathinfo($thisContent['filename'], PATHINFO_EXTENSION)) == 'crs') {
                        //if found an aicc directory... (!= false means it cannot be false (error) or 0 (no match))
                        //or if a file has the .crs extension
                        $package_type = 'aicc';
                    }
                }
            }
            if (!empty($package_type)) {
                switch ($package_type) {
                    case 'scorm':
                        $scormDir = api_get_path(SYS_COURSE_PATH).api_get_course_path().'/scorm';
			$oScorm = new scorm();
			$manifest = $oScorm->import_package($_FILES['user_file'], $scormDir);
			if(!empty($manifest)){
				$oScorm->parse_manifest($manifest);
				$oScorm->import_manifest(api_get_course_id());
			}
                        $proximity = $this->getRequest()->getProperty('content_proximity', '');
                        $maker = $this->getRequest()->getProperty('content_maker', '');
			$oScorm->set_proximity($proximity);
			$oScorm->set_maker($maker);
			$oScorm->set_jslib('scorm_api.php');                  
                        $this->messages['error'] = FALSE;
                        $this->messages['msn'] = $this->get_lang('ScormAdded');
                        break;
                    case 'aicc':
			$oAICC = new aicc();
			$config_dir = $oAICC->import_package($_FILES['user_file']);
			if (!empty($config_dir)){
                            $oAICC->parse_config_files($config_dir);
                            $oAICC->import_aicc(api_get_course_id());
			}
			$proximity = $this->getRequest()->getProperty('content_proximity', '');
                        $maker = $this->getRequest()->getProperty('content_maker', '');
			$oAICC->set_proximity($proximity);
			$oAICC->set_maker($maker);
			$oAICC->set_jslib('aicc_api.php');                        
                        $this->messages['error'] = FALSE;
                        $this->messages['msn'] = $this->get_lang('AiccAdded');
			break;
                }                 
            }
            else {
               $this->messages['error'] = TRUE;
               $this->messages['msn'] = $this->get_lang('OogieUnknownError');
            }
        }
        else {
            $this->messages['error'] = TRUE;
            $this->messages['msn'] = $this->get_lang('ThisFieldIsRequired');
        }
    }
}