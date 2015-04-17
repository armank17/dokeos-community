<?php

/**
 * controller for delete createAnnouncement
 * @author Johnny, <johnny1402@gmail.com>
 * @package announcement 
 */
class application_announcement_controllers_DeleteAnnouncement extends appcore_command_Command {

    private $id_announcement;
    
    private $objCollection;
    
    public function __construct() {
        $this->objCollection = new application_announcement_models_collection();
        $this->id_announcement = $this->getRequest()->getProperty('id');
        $this->deleteAnnouncement($this->id_announcement);
    }
    
    public function deleteAnnouncement($id_announcement)
    {
        $objTemp = new stdClass();
        $objTemp->user_id = api_get_user_id();
        $objTemp->announcement_id = $id_announcement;
        $result = $this->objCollection->deleteAnnouncement($objTemp);
        header("Location: ?module=announcement&cmd=CreateAnnouncement");
    }
}
  