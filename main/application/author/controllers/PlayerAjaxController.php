<?php
class application_author_controllers_PlayerAjax  extends application_author_controllers_Player 
{            
        
    public function __construct() {
        parent::__construct();
        $this->loadIframeHtmlXtra();
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
    }
    
    public function switchItem() {
        $this->lpId = $this->getRequest()->getProperty('lpId', '');
        $this->itemId = $this->getRequest()->getProperty('lpItemId', '');
        $this->currentItem = $this->lpItems[$this->itemId];
        $this->loadAndSaveContent($this->itemId, $this->lpId);
        $this->getItems();
        $this->updateLpProgress();
        $currentItemId = $this->getRequest()->getProperty('currentItemId', '');
        $this->updateLpItemTotalTime($currentItemId);        
        $this->nextItemId = $this->getItemIdNavigation($this->itemId, 'next');
        $this->prevItemId = $this->getItemIdNavigation($this->itemId, 'prev');
        $templates['view_content'] = $this->getTemplate('view_content');
        $templates['view_left'] = $this->getTemplate('view_left', 'Player');
        $templates['view_top'] = $this->getTemplate('view_top', 'Player');
        echo $this->getXmlHtmlCdata($templates);
        exit;
    }
    
    public function saveLpItemTime() {            
        $this->lpId = $this->getRequest()->getProperty('lpId', '');
        $currentItemId = $this->getRequest()->getProperty('lpItemId', '');
        $this->updateLpItemTotalTime($currentItemId);
        exit;
    }
    
    public function saveLpQuiz() {
        $this->lpId = $this->getRequest()->getProperty('lpId', '');
        $this->itemId = $this->getRequest()->getProperty('lpItemId', '');
        $this->currentItem = $this->lpItems[$this->itemId];
        $this->saveQuizItem();
        $this->getItems();
        $this->updateLpProgress();
        $this->nextItemId = $this->getItemIdNavigation($this->itemId, 'next');
        $this->prevItemId = $this->getItemIdNavigation($this->itemId, 'prev');
        $templates['view_left'] = $this->getTemplate('view_left', 'Player');
        $templates['view_top'] = $this->getTemplate('view_top', 'Player');
        echo $this->getXmlHtmlCdata($templates);
        exit;
    }            
}
?>
