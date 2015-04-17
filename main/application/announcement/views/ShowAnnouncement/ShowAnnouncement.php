<script type="text/javascript">
$(document).ready( function() {
      $(".user_info").click(function () {
        var myuser_id = $(this).attr("id");
        var user_info_id = myuser_id.split("user_id_");
        my_user_id = user_info_id[1];
        $.ajax({
            url: "<?php echo api_get_path(WEB_PATH)?>main/index.php?module=announcement&cmd=ShowAnnouncement&func=getUserById&user_id="+my_user_id,
            success: function(data){
               var dialog_div = $("<div id=\'html_user_info\'></div>");
                dialog_div.html(data);
                dialog_div.dialog({
                modal: true,
                title: "<?php echo $this->get_lang('UserInfo')?>",
                width: 450,
                height : 352,
                resizable:false
                }); 
            }   
        });
    });
});
</script>
<div id="announcement-content">
    <div id="announcement-content-left">   
		<div id="announcement-content-leftinner">
			<table width="100%" class="data_table">
				<?php  
                                        $announcementList = $this->getAnnouncementList();
					if (!empty($announcementList)) {
						$i = 0;

						foreach ($announcementList as $announcementId => $announcement) {                   
							echo '<script>
							$(document).ready(function(){
								$("#announcement'.$announcement->id.'").click(function(){
									window.location=$(this).find("a").attr("href");
									return false;
								});
							});
							</script>';

							echo '<tr class="'.($i%2==0?'row_odd':'row_even').'"><td>';
							echo '<div id="announcement'.$announcement->id.'" class="announcement_list_item">';
							echo '<a href="index.php?module=announcement&cmd=ShowAnnouncement&'.api_get_cidreq().'&id='.$announcement->id.'" title="'.$announcement->title.'">';
							echo '<span class="announcements_list_date">'.$announcement->announcementdate.'</span>';
							echo shorten($announcement->title,25);
							echo '</a>';
							echo '</div>';
							echo '</td></tr>';
						   $i++;
						}                
					} 
					else {
						echo '<tr><td><div>'.$this->get_lang('YouHaveNoAnnouncement').'</div></td></tr>';
					}
				?>  
			</table>
			</div>
        <div class="pager" align="right"><?php echo $this->pagerLinks; ?></div>    
    </div>
    
    <div id="announcement-content-right">
        <?php
                    $objAnnouncement = $this->getAnnouncementById();
                    
			echo '<div style="padding:10px;">';
			echo '<div class="announcement_title"><span style="font-size:16px;font-weight:bold;">'.$objAnnouncement->announcement_title.'</span></div>';
			echo  '	<br/><div class="announcement_metadata">';
			echo  '		<div class="announcement_date">'.$objAnnouncement->announcement_date.'</div>';
			echo  '		<div class="announcement_sender"><a href="javascript:void(0);" class="user_info" id="user_id_'.$objAnnouncement->insert_user_id.'">'.$objAnnouncement->firstname.' '.$objAnnouncement->lastname.'</a></div>';	
			echo  '	</div>';
			if (ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) {
				echo  '	<br/><div class="announcement_content rounded" style="height: 455px;overflow: auto;">'.$objAnnouncement->announcement_content.'</div>';
			} else {
				echo  '	<br/><div class="announcement_content rounded" style="height: 450px;overflow: auto;">'.$objAnnouncement->announcement_content.'</div>';	
			}	
			echo  '</div>';
			if(api_is_allowed_to_edit() || (api_get_course_setting('allow_user_edit_announcement') && api_get_user_id() == $objAnnouncement->insert_user_id)){
			echo  '<div class="announcements_actions" style="padding-top:0px">';
			echo  '<a href="index.php?module=announcement&'.api_get_cidreq().'&cmd=EditAnnouncement&id='.$objAnnouncement->announcement_id.'">'.Display::return_icon('pixel.gif',$this->get_lang('EditAnnouncement'),array('align' => 'absmiddle','class'=>'actionplaceholdericon actionedit')).' '.$this->get_lang('EditAnnouncement').'</a>';
			echo  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';		
			echo  '<a href="'.api_get_self().'?module=announcement&'.api_get_cidreq().'&cmd=DeleteAnnouncement&id='.$objAnnouncement->announcement_id.'" onclick="javascript:if(!confirm(\''.$this->get_lang("ConfirmYourChoice").'\')) return false;">'.Display::return_icon('pixel.gif',$this->get_lang('DeleteAnnouncement'),array('class'=>'actionplaceholdericon actiondelete','align' => 'absmiddle')).' '.$this->get_lang('DeleteAnnouncement').'</a>';
			echo  '</div>';
		}
		?>
    </div>
</div>
