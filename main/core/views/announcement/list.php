<div id="announcement-content">
    <div id="announcement-content-left">
		<div id="announcement-content-leftinner">
			<table width="100%" class="data_table">
				<?php
					if (!empty($announcementList)) {
						$i = 0;
                                                $addPageToUrl = "";
                                               if (isset($_GET['page'])) {
                                                   $addPageToUrl = '&page='.Security::remove_XSS($_GET['page']);
                                               }
						foreach ($announcementList as $announcementId => $announcement) {
							echo '<script type="text/javascript">
							$(document).ready(function(){
								$("#announcement'.$announcement->id.'").click(function(){
									window.location=$(this).find("a").attr("href");
									return false;
								});
							});
							</script>';
							echo '<tr class="'.($i%2==0?'row_odd':'row_even').'"><td>';                                                      
							echo '<div id="announcement'.$announcement->id.'" class="announcement_list_item">';                                                       
							echo '<a href="index.php?'.api_get_cidreq().'&amp;action=view&amp;id='.$announcement->id.$addPageToUrl.'" title="'.$announcement->title.'">';
							echo '<span class="announcements_list_date">'.format_locale_date('%b %e',strtotime(TimeZone::ConvertTimeFromServerToUser(api_get_user_id(),strtotime($announcement->announcementdate),'d-m-Y'))).'</span>';
							echo shorten($announcement->title,25);
							echo '</a>';
							echo '</div>';
							echo '</td></tr>';
						   $i++;
						}
					}
					else {
						echo '<tr><td><div>'.get_lang('YouHaveNoAnnouncement').'</div></td></tr>';
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
		if(api_is_allowed_to_edit()){

			echo $announcementForm->display();
                        $defaults = $announcementForm->_defaultValues;
                        if ($defaults ['send_to'] ['receivers'] == 0 OR $defaults ['send_to'] ['receivers'] == '-1') {
				$js = "<script type=\"text/javascript\">/* <![CDATA[ */ receivers_hide('receivers_to');receivers_hide('receivers_scenario'); if (document.announcement_form.title) document.announcement_form.title.focus();  /* ]]> */ </script>\n";
			} else if($defaults ['send_to'] ['receivers'] == '2') {
				$js = "<script type=\"text/javascript\">/* <![CDATA[ */ receivers_hide('receivers_to');receivers_show('receivers_scenario'); if (document.announcement_form.title) document.announcement_form.title.focus();  /* ]]> */ </script>\n";
			} else {
				$js = "<script type=\"text/javascript\">/* <![CDATA[ */ receivers_show('receivers_to');receivers_hide('receivers_scenario'); if (document.announcement_form.title) document.announcement_form.title.focus();  /* ]]> */ </script>\n";
			}
			echo $js;
		}
		?>
    </div>

</div>
