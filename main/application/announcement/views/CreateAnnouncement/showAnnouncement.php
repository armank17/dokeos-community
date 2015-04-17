<link rel="stylesheets" href="<?php echo $this->css; ?>" />
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
							echo '<a href="index.php?'.api_get_cidreq().'&action=view&amp;id='.$announcement->id.'" title="'.$announcement->title.'">';
							echo '<span class="announcements_list_date">'.$announcement->announcementdate.'</span>';
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
        <div class="pager" align="right"><?php //echo $pagerLinks; ?></div>    
    </div>
    
    <div id="announcement-content-right">
        <?php  
		if(api_is_allowed_to_edit()){
                        $announcementForm = $this->getForm();
			echo $announcementForm->display(); 
                        $defaults = $announcementForm->_defaultValues;
                        if ($defaults ['send_to'] ['receivers'] == 0 OR $defaults ['send_to'] ['receivers'] == '-1') {
				$js = "<script type=\"text/javascript\">/* <![CDATA[ */ receivers_hide('receivers_to'); if (document.announcement_form.title) document.announcement_form.title.focus();  /* ]]> */ </script>\n";
			} else {
				$js = "<script type=\"text/javascript\">/* <![CDATA[ */ receivers_show('receivers_to'); if (document.announcement_form.title) document.announcement_form.title.focus();  /* ]]> */ </script>\n";
			}
			echo $js;
		}
		?>
    </div>
    
</div>