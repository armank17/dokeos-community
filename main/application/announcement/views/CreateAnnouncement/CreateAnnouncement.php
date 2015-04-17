<link rel="StyleSheet" href="<?php echo $this->css; ?>" />
<script type="text/javascript" src="application/announcement/assets/js/jquery.validate.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    $("#divMessage").hide();
    $("#announcement_form").validate({
        debug: false,
        rules: {
            title: {
                required: true
                } 
        },
        messages: {
            title: {
                required: "<img src=\"<?php echo api_get_path(WEB_IMG_PATH)?>exclamation.png\" title=\'<?php echo $this->get_lang('Required')?>\' />"
            } 
        },
        submitHandler: function(form) {
        if(!$("#id_description").val())
            {
                $("#divMessage").empty();
                $("#divMessage").addClass("ui-state-error ui-corner-all");
                $("#divMessage").append("<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><strong>Alert:</strong> The content field is required</p>");
                $("#divMessage").show(1000);
                $("#divMessage").delay(3000).hide(2000);
                //alert('the content field is required'); 
                return false;
            }
            else
                {
                    $.post('?module=announcement&cmd=CreateAnnouncement&func=addAnnouncement', $("#announcement_form").serialize(), function(data) {
                        location.href='index.php?module=announcement&cmd=CreateAnnouncement';
                    });                    
                }

        }
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
        <div id="divMessage"></div>
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