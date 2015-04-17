<script type="text/javascript" src="appcore/library/jquery/jquery.alerts-1.1/jquery.alerts.js"></script>
<link rel="StyleSheet" href="appcore/library/jquery/jquery.alerts-1.1/jquery.alerts.css" />
<script type="text/javascript">

    function deleteDescription(id)
    {
        $(document).ready(function(){

            jConfirm('Are you sure?', 'Delete description', function(r) {
                            
                if(r)//true
                {
                    //datos = $('#course_description').serialize();
                    $.ajax({
                        type: "POST",
                        url: "index.php?module=courseDescription&cmd=Index&func=deleteDescription&id_description="+id,
                        //data: datos,
                        dataType: "json",
                        success: function(data){
                            //alert(data.action);
                            switch(data.action){
                                case 1:
                                    $("#divMessage").empty();
                                    $("#description_"+id).hide(2000);
                                    $("#description_"+id).empty();                                    
                                    break;
                                default:
                                    $("#divMessage").empty();
                                    $("#divMessage").addClass("ui-state-error ui-corner-all");
                                    $("#divMessage").append("<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><strong>Alert:</strong>"+data.message+"</p>");
                                    $("#divMessage").show(1000);
                                    $("#divMessage").delay(3000).hide(2000);                                
                                    break;
                                }
                            }
                            //timeout:80000
                        }); 
                        /***********************************/
                    }
                });

        });
        //return false;
    }
 
</script>
<div id="divMessage"></div>
<br />
<?php
// the actual content
$objDescription = $this->getCourseDescriptionList();
if (isset($objDescription) && $objDescription->count() > 0) {
    foreach ($objDescription->getIterator() as $id => $description) {
        echo '<div id="description_'.$description->id.'">';
        echo '<div class="section_white_list">';
        $user = api_get_user_info();
        if($this->belongsSession($description->id))
            echo '	<div class="sectiontitle">' . $description->title . ' '.api_get_session_image(api_get_session_id(), $user['status']).'</div>';
        else
            echo '	<div class="sectiontitle">' . $description->title . '</div>';
        echo '	<div class="sectioncontent">';
        echo text_filter($description->content);
        echo '	</div>';
        echo '</div>';
        echo '<div class="float_r">';
        if (api_is_allowed_to_edit()) {
            //delete index.php?'.api_get_cidreq().'&module=courseDescription&func=deleteDescription&description_id='.$description->id.' onclick="javascript:if(!confirm(\''.addslashes(api_htmlentities($this->get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset)).'\')) return false;"
            echo '<a class="" href="#" id="link_delete_description" onclick="deleteDescription(' . $description->id . ')"  >';
            echo Display::return_icon('pixel.gif', $this->get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'));
            echo '</a> ';
            //edit
            echo '<a class="" href="index.php?' . api_get_cidreq() . '&module=courseDescription&func=showDescription&description_id=' . $description->id . '&description_type=' . $description->description_type . '">';
            echo Display::return_icon('pixel.gif', $this->get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit'));
            echo '</a> ';
        }
        echo '</div>';
        echo '<div>&nbsp;</div>';
        echo '<div>&nbsp;</div>';
        echo '</div>';
    }
} else {
    echo '<em>' . $this->get_lang('langThisCourseDescriptionIsEmpty') . '</em>';
}
?>