<div id="announcement-content">
    <div id="announcement-content-left">
        <div id="announcement-content-leftinner">
            <table width="100%" class="data_table">
                <?php
                if (!empty($announcementList)) {
                    $i = 0;

                    foreach ($announcementList as $announcementId => $announcement) {
                        echo '<script type="text/javascript">
							$(document).ready(function(){
								$("#announcement' . $announcement->id . '").click(function(){
									window.location=$(this).find("a").attr("href");
									return false;
								});
							});
							</script>';

                        echo '<tr class="' . ($i % 2 == 0 ? 'row_odd' : 'row_even') . '"><td>';
                        echo '<div id="announcement' . $announcement->id . '" class="announcement_list_item">';
                        echo '<a href="index.php?' . api_get_cidreq() . '&amp;action=view&amp;id=' . $announcement->id . '" title="' . $announcement->title . '">';
                        
                       
                        echo '<span class="announcements_list_date">'.format_locale_date('%b %e',strtotime(TimeZone::ConvertTimeFromServerToUser(api_get_user_id(),strtotime($announcement->announcementdate),'d-m-Y'))).'</span>';
                        echo shorten($announcement->title, 25);
                        echo '</a>';
                        echo '</div>';
                        echo '</td></tr>';
                        $i++;
                    }
                } else {
                    echo '<tr><td><div>' . get_lang('YouHaveNoAnnouncement') . '</div></td></tr>';
                }
                ?>
            </table>
        </div>
        <div id="pager" class="pager" style="margin-left:0px;position:absolute;top:440px;left:10px;"></div>
        <input type="hidden" id="varLangStart" value="<?php echo get_lang('FirstPage');?>">
        <input type="hidden" id="varLangEnd" value="<?php echo get_lang('LastPage');?>">
        <input type="hidden" id="pages" value="<?php echo $pages;?>">
    </div>

    <div id="announcement-content-right">
        <?php
		foreach ($allAnnouncement as $announcementId => $announcement) {

			echo '<div class="custom-annoucement">';
			echo '<div class="announcement_title "><span style="font-size:14px;font-weight:bold; padding-left: 5px;    padding-right: 5px;">' . $ann_title[$announcementId] . '</span></div> ';
		   
			if (ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) {
				echo '	<br/><div class="custom-" style="min-height: 30px;    overflow: auto;     padding: 5px; ">' . $ann_content[$announcementId] . '</div>';
			} else {
				echo '	<br/><div class="custom-" style="min-height: 30px;    overflow: auto;    padding: 5px;">' . $ann_content[$announcementId] . '</div>';
			}


	 echo '	<div class="announcement_metadata">';
			echo '		<div class="announcement_date"style="padding-right:7px; padding-top:5px;" >' . TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), strtotime($ann_date[$announcementId]), 'd-m-Y') . '</div>';
			echo '		<div class="announcement_sender" style="padding:5px;"><a href="javascript:void(0);" class="user_info" id="user_id_' . $insert_user_id[$announcementId] . '">' . $firstname[$announcementId] . ' ' . $lastname[$announcementId] . '</a></div>';
			echo '	</div>';



			echo '</div>';
			echo '<div">';
			if (api_is_allowed_to_edit() || (api_get_course_setting('allow_user_edit_announcement') && api_get_user_id() == $insert_user_id[$announcementId])) {
				echo '<div class="announcements_actions" style="padding-top:0px; padding-right:10px;">';
				echo '<a href="index.php?' . api_get_cidreq() . '&amp;action=edit&amp;id=' . $announcementId . '">' . Display::return_icon('pixel.gif', get_lang('EditAnnouncement'), array('align' => 'middle', 'class' => 'actionplaceholdericon actionedit')) . ' ' . get_lang('EditAnnouncement') . '</a>';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$link  = api_get_self() . '?' . api_get_cidreq() . '&amp;action=delete&amp;id=' . $announcementId;
				$title = get_lang("ConfirmationDialog");
				$text = get_lang("ConfirmYourChoice");
				echo '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');">' . Display::return_icon('pixel.gif', get_lang('DeleteAnnouncement'), array('class' => 'actionplaceholdericon actiondelete', 'align' => 'middle')) . ' ' . get_lang('DeleteAnnouncement') . '</a>';
				echo '</div>';
			}
			echo '</br>';
		}	
        echo '</div>';	
	
        ?>
    </div>

</div>
