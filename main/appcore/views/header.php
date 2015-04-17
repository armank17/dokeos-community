<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php 
    echo $this->headDokeos();
?>
<title>DMS - Dokeos Managment Style</title>
</head>
<body>
<div id="wrapper">
<div id="header">
	<div id="header1">		
		<div class="headerinner">
                    <div id="top_corner"><a style="display:block;height:50px;width:200px;" href="<?php echo $this->getConfiguration('root_web'); ?>"></a></div>
                    <div id="institution">
                        <a target="_top" href="<?php echo $this->getConfiguration('root_web'); ?>"><?php echo $this->getSettings('Institution', $this->access_url); ?></a>
                        - 
                        <a target="_top" href="http://www.dokeos.com"><?php echo $this->getSettings('Sitename', $this->access_url); ?></a>                        
                    </div>                    
                </div>
	</div>
	<div id="header2">
		<div class="headerinner1">
                    <div id="dokeostabs">                                                                                      				                               
                    <a href="#prev" id="prev"></a>                 
                    <div id="tabs_container">              
                    <ul id="tabs">
                        <?php
                            if(property_exists($this, 'menu_disabled'))
                            {
                              if($this->menu_disabled)
                                  echo '<li class="tab_mycampus"><a class="active" href="#">Synchronization</a></li>';
                            }
                            else
                            {
                        ?>
                                <li class="tab_mycampus"><a class="active" href="<?php echo $this->path_web; ?>" target="_top"><?php echo $this->get_lang('langCourseHomepage', $this->language) ?></a></li>
                                <li class="tab_mycourses"><a href="<?php echo $this->path_web; ?>user_portal.php?nosession=true" target="_top"><?php echo $this->get_lang('langMyCourses', $this->language) ?></a></li>
                                <li class="tab_session_my_space"><a href="<?php echo $this->path_web; ?>main/mySpace/" target="_top"><?php echo $this->get_lang('Tracking', $this->language) ?></a></li>
                                <li class="tab_social"><a href="<?php echo $this->path_web; ?>main/social/home.php" target="_top"><?php echo $this->get_lang('SocialNetwork', $this->language) ?></a></li>
                                <li class="tab_myagenda"><a href="<?php echo $this->path_web; ?>main/calendar/myagenda.php" target="_top"><?php echo $this->get_lang('langMyAgenda', $this->language) ?></a></li>
                                <li id="current" class="tab_platform_admin_current"><a href="<?php echo $this->path_web; ?>main/admin/" target="_top"><?php echo $this->get_lang('langPlatformAdmin', $this->language) ?></a></li>	
                                <li class="logout"><a class="logoutClick" href="#" onclick="xajax_logout()"><?php echo $this->get_lang('langLogout', $this->getSettings('platformLanguage')) ?> (admin)</a></li>
                        <?php
                            }
                        ?>
                    </ul>
                    </div> <!-- /#tabs_container -->
                    <a href="#next" id="next"></a>                                                                                             
                    </div> <!-- /#dokeostabs -->
                    <div style="clear: both;" class="clear"> </div>                
                </div>
                <div id="clear_div"> </div>
	</div>
</div>    
</div>