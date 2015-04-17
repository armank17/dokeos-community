<?php
class application_dms_models_modeldms extends Database
{
    
    private $link;
    
    public function __construct() {
        
        $this->link = $this->openConnection();
    }    
    
    public function getSettings($key, $access_url='')
    {
        $sql= "SELECT * FROM ".$this->getConfiguration('main_database').".settings_current WHERE variable like '".$key."'";
        if(strlen(trim($access_url))>0)
            $sql.=" AND access_url =".$access_url;
        $result = $this->query($sql);
        while ($objectStyle = $this->fetch_object($result))
        {
            $objStyle= $objectStyle;
        }
        return $objStyle->selected_value;         
    }
    
    public function getPreviewTemplate($template ='')
    {
        if(strlen($template) == 0)
        $html ='<div>template</div>';
        return $html;
    }
    
    public function getHtmlContent()
    {
        $html='<div id="wrapper">
<div id="header">
	<div id="header1">		
		<div class="headerinner">
                    <div id="top_corner"><a style="display:block;height:50px;width:200px;" href="<?php echo $this->getConfiguration(\'root_web\'); ?>"></a></div>
                        <div id="institution">
                        <a href="<?php echo $this->getConfiguration(\'root_web\'); ?>" target="_top">Academy</a>
                        -&nbsp;<a href="http://www.dokeos.com" target="_top">Organisation</a></div>
                </div>

	</div>
	<div id="header2">
		<div class="headerinner1">
                    <div id="dokeostabs">                                                                                      				                               
                    <a href="#prev" id="prev"></a>                 
                    <div id="tabs_container">              
                    <ul id="tabs"><li class="tab_mycampus"><a class="active" href="<?php echo $this->getConfiguration(\'root_web\'); ?>" target="_top">Home</a></li><li class="tab_mycourses"><a href="<?php echo $this->getConfiguration(\'root_web\'); ?>user_portal.php?nosession=true" target="_top">Courses</a></li><li class="tab_session_my_space"><a href="<?php echo $this->getConfiguration(\'root_web\'); ?>main/mySpace/" target="_top">Reporting</a></li><li class="tab_social"><a href="<?php echo $this->getConfiguration(\'root_web\'); ?>main/social/home.php" target="_top">Social</a></li><li class="tab_myagenda"><a href="<?php echo $this->getConfiguration(\'root_web\'); ?>main/calendar/myagenda.php" target="_top">Calendar</a></li><li id="current" class="tab_platform_admin_current"><a href="<?php echo $this->getConfiguration(\'root_web\'); ?>main/admin/" target="_top">Administration</a></li>	<li class="logout"><a class="logoutClick" href="#" onclick="xajax_logout()">Logout (admin)</a></li>
                    </ul>
                    </div> <!-- /#tabs_container -->
                    <a href="#next" id="next"></a>                                                                                             
                    </div> <!-- /#dokeostabs -->
                    <div style="clear: both;" class="clear"> </div>                
                </div>
                <div id="clear_div"> </div>
	</div>
</div>    
</div>';
        $html.='<div id="main">
    <!--<div class="actions">
        <a href="#" onClick="xajax_generateProgramme()">
            <img class="toolactionsKey" title="Add key" alt="Add key" src="img/pixel.gif">
            Add key</a>    
    </div>    -->
    <div id="content">
   
</div>';
        $html.='<div id="footer" style="left: 0pt; bottom: 0pt;"> <!-- start of #footer section -->
	<div id="chat_container">&nbsp;<div onclick="nextChat()" style="display: none; right: 0px; bottom: 0px;" id="chat_next" class="chatbox_control"><div><span>&gt;</span></div></div><div onclick="backChat()" style="display: none; right: 940px; bottom: 0px;" id="chat_prev" class="chatbox_control"><div><span>&lt;</span></div></div></div><div id="footerinner">
		<div id="bottom_corner"></div>
		<div class="copyright">
		Plataforma <a target="_blank" href="http://www.dokeos.com">Dokeos 2.2</a>		</div>

<span id="platformmanager">Responsable : <a name="clickable_email_link" href="mailto:newportal@dokeos.com">John Doe</a></span>&nbsp;<span class="usersonline"><span class="usersonlinetitle">Usuarios en línea : </span><span class="usersonlinecontent">1</span><span class="usersonlineicon" style="cursor: pointer;"></span></span><script language="javascript" type="text/javascript" src="http://dokeos.pe/main/inc/lib/javascript/jquery_chat/chat.js"></script><link media="projection, screen" type="text/css" href="http://dokeos.pe/main/inc/lib/javascript/jquery_chat/screen.css" rel="stylesheet"><!--[if lte IE 7]><link rel="stylesheet" href="http://dokeos.pe/main/inc/lib/javascript/jquery_chat/screen_ie.css" type="text/css" media="projection, screen"><![endif]-->

<input type="hidden" value="http://dokeos.pe/main/social/chat.php?" id="pathChat">&nbsp;
</div>        
</div>  ';
        return $html;
    }
    
    public function saveSetting($arraySettings, $access_url)
    {
        foreach($arraySettings as $index=>$value)
        {
            $sql ="UPDATE ".$this->getConfiguration('main_database').".settings_current SET selected_value = '".$value."' WHERE variable like '".$index."' AND access_url = '".$access_url."'";        
            $this->query($sql);
        }
    }
    
    public function getIdUrlByIdUser($id_user)
    {
        $sql= "SELECT * FROM ".$this->getConfiguration('main_database').".access_url_rel_user WHERE user_id IN (".$id_user.")";
        $result = $this->query($sql);
        $array  = array();
        while ($objectStyle = $this->fetch_object($result))
        {
            array_push($array, $objectStyle->access_url_id);
        }
        return $array;
    }
    
    public function getObjUrl($array_id_url)
    {
        $id_url = implode(",", $array_id_url);
        $sql= "SELECT * FROM ".$this->getConfiguration('main_database').".access_url WHERE id IN (".$id_url.")";
        $result = $this->query($sql);
        $array = array();
        while ($objectStyle = $this->fetch_object($result))
        {
            array_push($array, $objectStyle);
        }
        return $array;         
    }
    
}