<?php
class application_thumbnail_controller_Generate //extends appcore_command_Command
{

    public function __construct() {
       //$this->setTheme('tools');
    }
    
    public static function load($url, $width= '130px', $height= '160px')
    {
        header("Content-type: image/jpg");
        $mythumb = new appcore_library_thumbnail_Thumbnail();
        //$mythumb->loadImage('http://img43.imageshack.us/img43/3022/finalfantasyn.jpg');
        $mythumb->loadImage('application/thumbnail/assets/images/thumb_dokeos.jpg');
        $mythumb->resize(100, 'width');
        $mythumb->show(); 
        //return '<img src="application/thumbnail/assets/images/thumb_dokeos.jpg" />';
    }
    
    public function thumbnail()
    {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
    }
}