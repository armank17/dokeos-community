<?php
/**
 * controller used for authoring page
 * @package Author 
 */

class application_author_controllers_Player extends appcore_command_Command 
{
    
    const LP_DOKEOS_TYPE = 1;
    const LP_SCORM_TYPE = 2;
    const LP_AICC_TYPE = 3;
    
    protected $_lpModel;
    protected $_lpItemModel;
    protected $_lpItemViewModel;
    public $courseCode;
    public $courseInfo;
    public $lpId;
    public $lpInfo;
    public $lpItems;
    public $lpView;
    public $lpItemViews;
    public $itemId;
    public $itemViewId;
    public $currentItem;
    public $currentItemView;
    public $prereqsCompleted;
    public $possibleStatus = array('not attempted', 'incomplete', 'completed', 'passed', 'failed', 'browsed');
    public $allowNewAttempt = false;
    public $nextItemId;
    public $prevItemId;
    public $documentSysPath;
    public $documentWebPath;
    public $content;
    public $contentLink;
    public $from;
    public $stepId;
    public $tool;
    public $activityId;
    
    public function __construct() {
        parent::__construct();
        $this->validateSession();
        $this->setTheme('tools');
        $this->toolName = TOOL_AUTHOR;
        $GLOBALS['toolName'] = $this->toolName;
        $this->stylesheet = api_get_setting('stylesheets');
        $this->courseCode = api_get_course_id();
        $this->courseInfo = api_get_course_info();
        $this->documentSysPath = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document';
        $this->documentWebPath = api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/document';
        
        $this->_lpModel = new application_author_models_ModelLearnpath();
        $this->_lpItemModel = new application_author_models_ModelLearnpathItem();
        $this->_lpItemViewModel = new application_author_models_ModelLearnpathItemView();
        
        $this->lpId = $this->getRequest()->getProperty('lpId', '');
        $this->itemId = $this->getRequest()->getProperty('lpItemId', '');        
        $this->lpItemId = $this->getRequest()->getProperty('lpItemId', '');        
        $this->from = $this->getRequest()->getProperty('from', '');
		$this->stepId = $this->getRequest()->getProperty('step_id', '');
		$this->tool = $this->getRequest()->getProperty('tool', '');

		$this->activityId = $this->getRequest()->getProperty('activity_id', '');  

		$this->currentItem=array();

		if(isset($this->tool) && $this->tool == 'scenario'){
			$_SESSION['tool'] = 'scenario';
			$_SESSION['stepId'] = $this->stepId;
			$_SESSION['activityId'] = intval($this->activityId);
		}
        
        if (!empty($this->lpId)) {
            $this->_lpItemModel->setLpId($this->lpId);
            $this->_lpItemViewModel->setLpId($this->lpId);            
            $this->lpInfo = $this->_lpModel->getModuleInfo($this->lpId);   
                                                                            
            
            

			if($this->lpInfo["behavior"]=="2") {
				$this->currentItem = $this->_lpModel->getFirstItemByStatus($this->lpId,api_get_user_id(),'completed');
			}
			
			if(!count($this->currentItem))
				$this->currentItem = $this->_lpModel->getFirstItem($this->lpId);
			
			$this->itemId = $this->currentItem['id'];
            
            
            $this->lpView = $this->_lpItemViewModel->getLpView();             
            if (!empty($this->lpView)) {
                $this->loadItemsview();
            }
            $this->getItems();            
             
            if(empty($this->lpItemId)) {
				$this->loadAndSaveContent($this->itemId, $this->lpId );                
			}
            $this->nextItemId = $this->getItemIdNavigation($this->itemId, 'next');
            $this->prevItemId = $this->getItemIdNavigation($this->itemId, 'prev');                         
        }                     
        if (!empty($this->lpInfo['theme'])) {
            $this->setStyleSheet($this->lpInfo['theme']);
            $this->stylesheet = $this->lpInfo['theme'];
        }
    }
    
    public function loadItemsview() {
        if ($this->lpView) {
            $items = $this->_lpModel->getLpItems($this->lpId);
            if (!empty($items)) {
                foreach ($items as $item) {
                    $itemView = $this->_lpItemViewModel->getLpItemView($item['id']);
                    if (empty($itemView)) {
                        $this->_lpItemViewModel->saveLpItemView($item['id']);
                    }
                }
            }
        }
    }
    
    public function view() {                
        if ($this->lpInfo['lp_type'] != self::LP_DOKEOS_TYPE) {
            $this->redirect(api_get_path(WEB_CODE_PATH).'newscorm/lp_controller.php?'.api_get_cidreq().'&action=view&step_id='.$this->stepId.'&lp_id='.$this->lpInfo['id']);
        }        
        $this->loadIframeHtmlXtra();
        $this->setReducedHeader();        
        $this->getItems();          
        $this->updateLpProgress();               
    }

    public function updateLpProgress() {
        if (!empty($this->lpView)) {          
            $completedItems = $this->getCompletedItems();           
            $progress = 0;
            if (count($this->lpItems) > 0) {
                $progress = ceil((count($completedItems) * 100)/count($this->lpItems));               
            }  
            $this->_lpItemViewModel->fieldValues['progress'] = $progress;
            $viewId = $this->_lpItemViewModel->saveLpView();
        }
    }
    
    public function updateLpItemTotalTime($itemId) {
        if (!empty($this->lpView)) {
            $stopTime = time();
            $this->_lpItemModel->setId($itemId);
            $view = $this->_lpItemModel->getItemView();            
            if (!empty($view)) {
                $startTime = $view['start_time'];
                if ($startTime == 0) {
                    $startTime = time();
                }                               
                $totalTime = $stopTime - $startTime;                
                $newTotalTime = intval($view['total_time']) + $totalTime;
                $this->_lpItemViewModel->updateItemViewTotalTime($newTotalTime, $itemId, $view['lp_view_id']);
            }            
        }
    }
    
    public function loadAndSaveContent($itemId, $lpId) {       
        $itemInfo = $this->_lpItemModel->getLpItemInfo($itemId);
        $lastItem =  $this->_lpModel->getLastItem($lpId);
        switch ($itemInfo['item_type']) {
            case 'document':
                $this->prereqsCompleted = true;                
                if (empty($itemInfo['content'])) {
                    $this->_lpItemModel->setId($itemId);
                    $this->_lpItemModel->fieldValues['content'] = mysql_real_escape_string($this->_lpItemModel->getDocumentContent($itemInfo['path']));
                    $lpItemId = $this->_lpItemModel->save();
                    $itemInfo = $this->_lpItemModel->getLpItemInfo($lpItemId);
                }                 
                $this->content = stripslashes($itemInfo['content']);    
                $this->content.='<script>$("#continue").hide();</script>';                
                 
                if($lastItem['display_order']==$itemInfo['display_order'] && isset($_SESSION['stepId']) && $_SESSION['stepId'] > 0 )	{
					$this->content.= "<script> function goto (href) { window.parent.location.href = href }</script>";
					$this->content.= '
					<script>
						function positioning_btnContinue()	{
							
							$("#continue").hide();
							
							width = $(document).width();
							height = $(document).height();
						
							$("#continue").css("width","140px");
							$("#continue").css("left",(0 + width)-160);
							$("#continue").css("top",(0 + height)-74);
							
							$("#continue").show();
						}
					
						$( window ).resize(function() {
							positioning_btnContinue();
						});
						
						
						setTimeout(function(){
                                                                           
									positioning_btnContinue();
								
						},400);
		
					</script>';
				}
                
                $this->saveItemView();
                break;
            case 'quiz':                
                if(isset($_SESSION['stepId']) && $_SESSION['stepId'] > 0){
					$this->contentLink = api_get_path(WEB_CODE_PATH).'exercice/exercice_submit.php?lp_init=1&origin=author&learnpath_id='.$this->lpId.'&learnpath_item_id='.$itemId.'&exerciseId='.$itemInfo['path'].'&'.api_get_cidreq().'&tool=scenario&step='.$this->stepId;
				}
				else {
					$this->contentLink = api_get_path(WEB_CODE_PATH).'exercice/exercice_submit.php?lp_init=1&origin=author&learnpath_id='.$this->lpId.'&learnpath_item_id='.$itemId.'&exerciseId='.$itemInfo['path'].'&'.api_get_cidreq();
				}
				      
                $this->content = $this->getTemplate('quiz_content');
                $this->content .= '<script>                        
               
                    var objContinue = document.getElementById("continue");
                    objContinue.style.display="none";
               
                </script>';
                
                if($lastItem['display_order']==$itemInfo['display_order'])	{
                
                	$this->content .= '<script>                            
							
                                        var objSlide = document.getElementById("isLastSlide");
							objSlide.setAttribute("value","1");
					
                        </script>';
                }
                break;                
        }
    }
    
    public function getItemIdNavigation($currItemId, $action) {    
        $itemId = 0;
        if ($this->lpItems) {
            $keys = array_keys($this->lpItems);
            switch($action) {
                case 'next':
                    $next = (array_search($currItemId, $keys) + 1);
                    if ($next < count($keys)) {
                        $itemId = $keys[$next];
                    }
                    break;
                case 'prev':
                    $prev = (array_search($currItemId, $keys) - 1);
                    if ($prev >= 0) {
                        $itemId = $keys[$prev];
                    }
                    break;
            }
        }
        return $itemId;
    }
    
    public function getItems() {
        $items = $this->_lpModel->getLpItems($this->lpId);      
        if (!empty($items)) {
            foreach ($items as $item) {
                $this->lpItems[$item['id']] = $item;
                $this->_lpItemModel->setId($item['id']);
                $this->lpItems[$item['id']]['view'] = $this->_lpItemModel->getItemView();
            }
        }
    }
    
    public function getCompletedItems() {
        $items = $this->lpItems;
        $completed = array();
        if (!empty($items)) {
            foreach ($items as $item) {
                if (isset($item['view']) && $item['view']['status'] == 'completed') {
                    $completed[$item['id']] = $item;
                }                
            }
        } 
        return $completed;
    }
    
    public function getItemViewTotalTime() {
    	/*if ($this->currentItemView['start_time'] == 0) { //shouldn't be necessary thanks to the open() method
            $startTime = time();
    	}
        else {
            $startTime = $this->currentItemView['start_time'];
        }*/
        $this->currentItemView['start_time'] = time();
        $startTime = $this->currentItemView['start_time'];
    	if (time() < ($this->currentItemView['start_time'] + $this->currentItemView['total_time'])) {
            $stopTime = time() + 2;
    	}
        else {
                $stopTime = time() + 2;
        }
    	$time = $stopTime - $startTime;
        $this->currentItemView['total_time'] += $time;
        $totalTime = $this->currentItemView['total_time'];
    	if ($totalTime < 0){
            return 0;
    	} else {    	
            return $totalTime;
    	}
    }
    
    public function getItemViewAttemptId() {
        $attemptId = 0;
        if ($this->currentItemView) {            
            if ($this->allowNewAttempt && isset($this->currentItemView['status']) && ($this->currentItemView['status'] != $this->possibleStatus[0])) {
                $attemptId = $this->currentItemView['view_count'] + 1; //open a new attempt
            }
            else {
                $attemptId = $this->currentItemView['view_count'];
            }
        }
        return $attemptId;        
    }
    
    public function getCurrentStartTime() {    	
    	if (empty($this->currentItemView['start_time'])) {
            return time();
    	} else {
            return $this->currentItemView['start_time'];
    	}
    }
    
    public function getScore() {
    	$score = 0;
    	if (!empty($this->currentItemView['score'])) {
            $score = $this->currentItemView['score'];
    	}    	
    	return $score;
    }
    
    public function getSuspendData() {
    	if (!empty($this->currentItemView['suspend_data'])) {
            return str_replace(array("\r","\n"), array('\r','\n'), $this->currentItemView['suspend_data']);
        }
        else {
            return '';                
        }
    }
    
    public function getMaxScore(){
    	if ($this->currentItem['item_type'] == 'sco')
    	{
            if (is_numeric($this->currentItemView['max_score']) && $this->currentItemView['max_score'] > 0) {
                return $this->currentItemView['max_score'];
            }
            else {
                if (!empty($this->currentItem['max_score'])) {
                    return $this->currentItem['max_score'];                           
                }
                else {
                    return 100;                            
                }
            }
    	}
    	else
    	{
            if (!empty($this->currentItem['max_score'])) {
                return $this->currentItem['max_score'];                    
            }
            else {
                return 100;
            }
    	}
    }
    
    public function getLessonLocation() {
        if (!empty($this->currentItemView['lesson_location'])) {
            return $this->currentItemView['lesson_location'];            
        }
        else {
            return '';                
        }
    }
    
    public function getStatus() {
    	if ($this->currentItemView['status'] == $this->possibleStatus[2]) {
            return $this->currentItemView['status'];
    	}                
        if (!empty($this->currentItem['item_type'])) {
            switch($this->currentItem['item_type']) {
                case 'asset':
                    if ($this->prereqsCompleted) {
                        return $this->possibleStatus[2];
                    }
                    break;
                case TOOL_QUIZ: 
                    if ($this->prereqsCompleted) {
                        return $this->possibleStatus[2];
                    }
                    break;
                default:
                    //for now, everything that is not sco and not asset is set to
                    //completed when saved
                    if ($this->prereqsCompleted) {
                        return $this->possibleStatus[2];
                    }
                break;
            }    
        }
    	return $this->possibleStatus[0];
    }
    
    public function saveQuizItem() {
        $this->itemId = $this->getRequest()->getProperty('lpItemId', '');
        $this->currentItem = $this->lpItems[$this->itemId];
        $this->lpInfo = $this->_lpModel->getModuleInfo($this->lpId);
        $this->currentItemView = $this->currentItem['view'];   
        $exeId = $this->getRequest()->getProperty('exeId', '');
        $caseCompleted = array('completed','passed','browsed');
        if (!empty($exeId)) {       
            if (!in_array($this->currentItem['view']['status'], $caseCompleted)) {
                $this->prereqsCompleted = true;
                $quizTrack  = $this->_lpItemModel->getQuizTracking($exeId);   
                $startDate  = convert_mysql_date($quizTrack['start_date']);
                $endDate    = convert_mysql_date($quizTrack['exe_date']);
                $mytime     = ((int)$endDate - (int)$startDate);               
                $this->_lpItemViewModel->fieldValues['total_time'] = $mytime;
                $this->_lpItemViewModel->fieldValues['start_time'] = $startDate;
                $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();                         
                $this->_lpItemViewModel->fieldValues['score'] = $quizTrack['exe_result'];                        
                $this->_lpItemViewModel->fieldValues['max_score'] = $quizTrack['exe_weighting'];
                $this->_lpItemViewModel->fieldValues['lp_item_id'] = $this->itemId;
                $this->_lpItemViewModel->fieldValues['lp_view_id'] = $this->lpView['id'];
                $this->_lpItemViewModel->fieldValues['view_count'] = $this->getItemViewAttemptId();
                $this->_lpItemViewModel->fieldValues['suspend_data'] = $this->getSuspendData();
                $this->_lpItemViewModel->fieldValues['lesson_location'] = $this->getLessonLocation();
                $where =  "lp_item_id = {$this->itemId} AND lp_view_id = {$this->lpView['id']}";
                $this->itemViewId = $this->_lpItemViewModel->save($where);
                if(isset($_SESSION['tool']) && $_SESSION['tool'] == 'scenario'){
                        $this->_lpItemViewModel->updateScenarioActView();
                }
            }                                      
        }
    }
    
    public function saveItemView() {
        $this->currentItem = $this->lpItems[$this->itemId];
        $this->lpInfo = $this->_lpModel->getModuleInfo($this->lpId);
        $this->currentItemView = $this->currentItem['view'];
        //depending on what we want (really), we'll update or insert a new row
        //now save into DB
        if (empty($this->currentItemView)) {
            $this->_lpItemViewModel->fieldValues['total_time'] = $this->getItemViewTotalTime();
            $this->_lpItemViewModel->fieldValues['start_time'] = $this->getCurrentStartTime();
            $this->_lpItemViewModel->fieldValues['score'] = $this->getScore();
            $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();                    
            $this->_lpItemViewModel->fieldValues['max_score'] = $this->getMaxScore();
            $this->_lpItemViewModel->fieldValues['lp_item_id'] = $this->itemId;
            $this->_lpItemViewModel->fieldValues['lp_view_id'] = $this->lpView['id'];
            $this->_lpItemViewModel->fieldValues['view_count'] = $this->getItemViewAttemptId();
            $this->_lpItemViewModel->fieldValues['suspend_data'] = $this->getSuspendData();
            $this->_lpItemViewModel->fieldValues['lesson_location'] = $this->getLessonLocation();
            $this->itemViewId = $this->_lpItemViewModel->save();
			if(isset($_SESSION['tool']) && $_SESSION['tool'] == 'scenario'){
				$this->_lpItemViewModel->updateScenarioActView();
			}
        } 
        else {
            // this is a array containing values finished
            $caseCompleted = array('completed','passed','browsed');
            //is not multiple attempts
            if ($this->lpInfo['prevent_reinit'] == 1) {
                // process of status verified into data base
                // get type lp: 1=lp dokeos and  2=scorm
                // if not is completed or passed or browsed and learning path is scorm
                if (!in_array($this->getStatus(), $caseCompleted) && $this->lpInfo['lp_type'] == 2) {
                    $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                    $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                } 
                else {
                    //verified into data base
                    if (!in_array($this->currentItem['status'], $caseCompleted) && $this->lpInfo['lp_type'] == 2) {
                        $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                        $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                    } 
                    elseif (in_array($this->currentItem['status'], $caseCompleted) && $this->lpInfo['lp_type'] == 2 && $this->currentItem['item_type'] != 'sco' ) {
                        $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                        $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                    }
                    else {
                        //is lp dokeos or aicc
                        if ($this->lpInfo['lp_type'] == 1 && $this->currentItem['item_type'] != 'chapter') {
                            $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                            $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                        }
                        else if ($this->lpInfo['lp_type'] == 3) {
                            $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                            $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                        }
                    }
                }
            } 
            else {
                // is multiple attempts
                if (in_array($this->getStatus(), $caseCompleted) && $this->lpInfo['lp_type'] == 2) {
                    //reset zero new attempt ?
                    $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                }  
                elseif (!in_array($this->getStatus(), $caseCompleted) &&  $this->lpInfo['lp_type'] == 2){
                    $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                    $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                } 
                else {                                                            
                    //is dokeos LP
                    $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                    $this->_lpItemViewModel->fieldValues['status'] = $this->getStatus();
                }
                if ($this->lpInfo['lp_type'] == 2) {
                    //verify current status in multiples attempts
                    if (!in_array($this->currentItemView['status'], $caseCompleted)) {
                        $this->_lpItemViewModel->fieldValues['total_time'] = $this->currentItem['total_time'] + $this->getItemViewTotalTime();
                    } 
                }
            }
            $this->_lpItemViewModel->fieldValues['start_time'] = $this->getCurrentStartTime();
            $this->_lpItemViewModel->fieldValues['score'] = $this->getScore();
            $this->_lpItemViewModel->fieldValues['max_score'] = $this->getMaxScore();
            $this->_lpItemViewModel->fieldValues['suspend_data'] = $this->getSuspendData();
            $this->_lpItemViewModel->fieldValues['lesson_location'] = $this->getLessonLocation();
            $where =  "lp_item_id = {$this->itemId} AND lp_view_id = {$this->lpView['id']}";
            $this->_lpItemViewModel->save($where);
			if(isset($_SESSION['tool']) && $_SESSION['tool'] == 'scenario'){
				$this->_lpItemViewModel->updateScenarioActView();
			}
        }
        if (!empty($this->lpView)) {
            $completedItems = $this->getCompletedItems();
            $progress = 0;
            if (count($this->lpItems) > 0) {
                
                $progress = ceil(((count($completedItems)) * 100)/count($this->lpItems));
            }            
            $this->_lpItemViewModel->fieldValues['last_item'] = $this->itemId;            
            $this->_lpItemViewModel->fieldValues['progress'] = $progress;
            
            $viewId = $this->_lpItemViewModel->saveLpView();
        }
    }
    
    public function itemViewRestart() {
        $allowed = $this->isRestartAllowed();
        if ($allowed === -1) {
            //nothing allowed, do nothing
        } elseif ($allowed === 1) {
                //restart as new attempt is allowed, record a new attempt
                $this->_lpItemViewModel->fieldValues['status'] = $this->possibleStatus[0];
                $this->_lpItemViewModel->fieldValues['score'] = 0;
                $this->_lpItemViewModel->fieldValues['start_time'] = 0;
                $this->_lpItemViewModel->fieldValues['suspend_data'] = '';
                $this->_lpItemViewModel->fieldValues['lesson_location'] = '';
                $this->_lpItemViewModel->fieldValues['view_count'] = 1;

                if ($this->currentItem['item_type'] != TOOL_QUIZ) {
                    //$this->write_to_db();
                }
        } else {
                //restart current element is allowed (because it's not finished yet),
                // reinit current
                $this->_lpItemViewModel->fieldValues['score'] = 0;
                $this->_lpItemViewModel->fieldValues['start_time'] = 0;
                $this->_lpItemViewModel->fieldValues['suspend_data'] = '';
                $this->_lpItemViewModel->fieldValues['status'] = $this->possibleStatus[0];                
        }
    	return true;
    }
   
    public function isRestartAllowed() {		
        $restart = 1;
        $mystatus = $this->currentItem['views'][$this->itemId]['status'];
        if ($this->lpInfo['prevent_reinit'] > 0) { //if prevent_reinit == 1 (or more)
            //if status is not attempted or incomplete, authorize retaking (of the same) anyway. Otherwise:
            if ($mystatus != $this->possibleStatus[0] && $mystatus != $this->possibleStatus[1]) {
                    $restart = -1;
            } else {
                    $restart = 0;
            }
        } else {
            if ($mystatus == $this->possibleStatus[0] || $mystatus == $this->possibleStatus[1]) {
                    $restart = -1;
            }
        }
        return $restart;
    }
    
    public function getIconStatusUrl($status) {
        $icons = array(
            'not attempted' => api_get_path(WEB_IMG_PATH).'notattempted.gif',
            'incomplete' => api_get_path(WEB_IMG_PATH).'incomplete.gif',
            'failed' => api_get_path(WEB_IMG_PATH).'failed.gif',
            'completed' => api_get_path(WEB_IMG_PATH).'validate.png',
            'passed' => api_get_path(WEB_IMG_PATH).'passed.gif',
            'succeeded' => api_get_path(WEB_IMG_PATH).'succeeded.gif',
            'browsed' => api_get_path(WEB_IMG_PATH).'completed.gif'
        );
        return $icons[$status];
    }
    
    public function getAudio($fullpath, $id, $w = 400, $h = 300, $autoplay = false, $hide = false) {
            $ext = substr(strrchr($fullpath, '.'), 1);
            $extrascript = $previewImage = '';            
            if ($autoplay) { $extrascript = "jwplayer('mediaplayer$id').play();"; }                      
            if ($ext == 'mp3'|| $ext == 'ogg'|| $ext == 'ogv') {
                $return .= "<div class='thePlayer ".($hide?'invisible':'visible')."' id='$id' >";
                $return .= "<div id='mediaplayer".$id."'></div>";
                $return .= "<script type=\"text/javascript\">
                                try {
                                jwplayer(\"mediaplayer$id\").setup({                                    
                                    file: '$fullpath',
                                    width: '".$w."',
                                    height:'".$h."',
                                    controlbar: 'bottom',
                                    primary: 'flash'
                                });
                                $extrascript
                                    } catch(e) {}
                                </script>";
                $return .= "</div>";
            } 
            return $return;
    }
    
    public function getProgressBar() {        
        $completedItems = $this->getCompletedItems();
        $w = 0;
        if (count($this->lpItems) > 0) {
            $w = ceil((count($completedItems) * 100)/count($this->lpItems));
        }        
        $html .= '<div id="progress-bar" class="cols"><div style="width:'.$w.'%;float:left;" id="percent"></div></div>';
        return $html;
    }
    
    public function validateSession() {  
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();        
        $objSecurity->verifyAccessToCourse();
        if (api_get_setting('enable_author_tool') === 'false') {
            api_not_allowed();
        } 
    }
    
     public function loadIframeHtmlXtra() {
        $navigator = api_get_navigator();
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'css/'.$this->stylesheet.'/course_navigation.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css', 'css');
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.alerts/jquery.alerts.css', 'css');
        if ($navigator['shortname'] == 'msie') {
            $version = intval($navigator['version']);
            if ($version == 7) {
                $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'css/'.$this->stylesheet.'/ie7-cleanup.css', 'css'); 
            }
            if ($version == 8) {
                $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'css/'.$this->stylesheet.'/ie8-cleanup.css', 'css');
            }
        }      
        $this->setHtmlHeadXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/css/player.css', 'css');        
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js');
        $this->setHtmlHeadXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js'); 
        $this->setHtmlFootXtra(api_get_path(WEB_LIBRARY_PATH).'javascript/responsive/js/modernizr.js'); 
        
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js');
        
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.alerts/jquery.alerts.js');                 
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/functions.js');
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/playerModel.js');         
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'application/author/assets/js/playerController.js');
        
        $this->setHtmlFootXtra(api_get_path(WEB_CODE_PATH).'newscorm/js/jquery.iframe-auto-height.js');
        
    }
    public function audioActions() {        
        $audioActions = api_is_allowed_to_edit() ? 'audio-actions' : '';
        return $audioActions;        
    }

}
