<?php

/**
 * controller for create new createAnnouncement
 * @author Johnny, <johnny1402@gmail.com>
 * @package announcement 
 */
class application_announcement_controllers_ShowAnnouncement extends appcore_command_Command {
    
    private $theme ='tools';
    public $css;
    public $announcementList;
    private $objCollection;
    private $announcement_id;
    private $userId;
    private $courseCode;
    private $session_id;
    private $objPaginator;
    
    public function __construct() {
        $this->setTheme($this->theme);
        $this->setLanguageFile(array('announcements', 'group', 'survey'));
        $this->css = 'application/announcement/assets/css/style.css';
        $this->objCollection = new application_announcement_models_collection();
        $this->objPaginator = new appcore_library_pagination_Paginator();
        $this->announcement_id = $this->objCollection->getLastAnnouncement();
        $this->userId = api_get_user_id();
        $this->courseCode = api_get_course_id();
        $this->session_id = api_get_session_id();
    }
    
    public function getAnnouncement()
    {
        return $this->objCollection->getAnnouncement();
    }
    
    public function getAction() {
        $html = '<div class="actions">';
        if (api_is_allowed_to_edit()) {
            $html.='<a href="index.php?module=announcement&' . api_get_cidreq() . '&cmd=CreateAnnouncement">' . Display::return_icon('pixel.gif', $this->get_lang('AddAnnouncement'), array('class' => 'toolactionplaceholdericon toolactionannoucement')) . $this->get_lang('AddAnnouncement') . '</a>';
        }
        $html.='</div>';
        return $html;
    }    
    
    public function getAnnouncementList() {
        $result = $this->objCollection->getAnnouncementList();
        $this->generatePaginator($result);
        $this->pagerLinks = $this->objPaginator->links();         
        return $result;
    }
    
    public function generatePaginator($Collecttion)
    {
        if($Collecttion->count() > 0)
        {
            $array_announcement = array();
            foreach($Collecttion->getIterator() as $objAnnouncement)
            {
                array_push($array_announcement, $objAnnouncement);
            }
            
            $resultCollection = $this->objPaginator->generate($array_announcement);
            $resultCollection = $this->objCollection->exchangeArray($resultCollection);
            return $resultCollection;
        }
    }    
    
    public function getAnnouncementInfo($announcementId) {
        $result = $this->objCollection->getAnnouncementInfo($announcementId);
        return $result;
    }
    
    public function getAnnouncementById()
    {
        $id_announcement = $this->getRequest()->getProperty('id');
        if(!$id_announcement)
            $id_announcement = $this->announcement_id;
        $objCollectionAnnouncement = $this->getAnnouncementInfo($id_announcement);
        if($objCollectionAnnouncement->count()>0)
        {
            foreach ($objCollectionAnnouncement->getIterator() as $oAnnouncement)
            {
                $objAnnouncement = $oAnnouncement;
            }
        }
        return $objAnnouncement;
    }
    
    public function getUserById()
    {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        
            $user_id = intval($this->getRequest()->getProperty('user_id'));
            $user_info = array();
            $user_info = UserManager::get_all_user_info($user_id);
            $image_path = UserManager::get_user_picture_path_by_id($user_id, 'web', false, true);
            $image_dir = $image_path['dir'];
            $image = $image_path['file'];
            $image_file = $image_dir.$image;
            $img_attributes = 'src="'.$image_file.'?rand='.time().'" '
	  .'alt="'.api_get_person_name($user_data['firstname'], $user_data['lastname']).'" ';

            $get_user_info = $user_info[0];
            $user_table = '
            <table class="data_table" width="645" height="271" border="0">
            <tbody>
                <tr class="row_odd" >
                <td style="width: 250px;">'.$this->get_lang('Photo').'<br />
                </td>
                <td align="center">:<br />
                </td>
                <td><center><img '.$img_attributes.'/></center></td>
                </tr>
                <tr class="row_even">
                <td>'.$this->get_lang('FirstName').'<br />
                </td>
                <td align="center">:</td>
                <td>'.$get_user_info['firstname'].'</td>
                </tr>
                <tr class="row_odd">
                <td>'.$this->get_lang('LastName').'<br />
                </td>
                <td align="center">:</td>
                <td>'.$get_user_info['lastname'].'</td>
                </tr>
                <tr class="row_even">
                <td>'.$this->get_lang('MyCompetences').'</td>
                <td align="center">:</td>
                <td>'.$get_user_info['competences'].'</td>
                </tr>
                <tr class="row_odd">
                <td style="width: 250px;"> '.$this->get_lang('MyDiplomas').' </td>
                <td align="center">:</td>
                <td>'.$get_user_info['diplomas'].'</td>
                </tr>
                <tr class="row_even">
                <td style="width: 250px;"> '.$this->get_lang('MyTeach').' </td>
                <td align="center" style="margin-left: -88px;">:</td>
                <td>'.$get_user_info['teach'].'</td>
                </tr>
                <tr class="row_odd">
                <td>'.$this->get_lang('MyPersonalOpenArea').' </td>
                <td align="center">:</td>
                <td>'.$get_user_info['openarea'].'</td>
                </tr>
            </tbody>
            </table>
            ';
        echo ($user_table);
    }
    
    public function getIdUSer()
    {
        return $this->objCollection->getIdUser();
    }
  
    
    
}
