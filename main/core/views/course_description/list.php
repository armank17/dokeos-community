<?php
	// the actual content
	if (isset($descriptions) && count($descriptions) > 0) {
			foreach ($descriptions as $id => $description) {
                            
                                        if (strpos($description->content, '../../courses/') !== FALSE) {
                                            $description->content = str_replace('../../courses/', '/courses/', $description->content);
                                        }
                            
					echo '<div class="section_white_list">';
					echo '	<div class="sectiontitle">'.$description->title.'</div>';	
					echo '	<div class="sectioncontent">';
					echo 	text_filter($description->content);
					echo '	</div>';
					echo '</div>';
					echo '<div class="float_r">';
					if (api_is_allowed_to_edit()) {
							//delete
							//echo '<a class="" href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=delete&amp;description_id='.$description->id.'&amp;showlist=1" onclick="javascript:if(!confirm(\''.addslashes(api_htmlentities(get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset)).'\')) return false;">';
                                                        $link  = api_get_self().'?'.api_get_cidreq().'&amp;action=delete&amp;description_id='.$description->id.'&amp;showlist=1';
                                                        $title = get_lang("ConfirmationDialog");
                                                        $text = get_lang("ConfirmYourChoice");
                                                        echo '<a class="" href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');">';
							echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'));
							echo '</a> ';
							//edit
							echo '<a class="" href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=edit&amp;description_id='.$description->id.'&amp;description_type='.$description->description_type.'">';
							echo Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit'));
							echo '</a> ';
					}
					echo '</div>';
					echo '<div>&nbsp;</div>';
					echo '<div>&nbsp;</div>';
			}
	} else {
			echo Display::display_normal_message('<em>'.get_lang('ThisCourseDescriptionIsEmpty').'</em>', false,true);
	}
?>