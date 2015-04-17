<script type="text/javascript" src="appcore/library/jquery/jquery.validate.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    $.fn.stripTags = function() { return this.replaceWith( this.html().replace(/<\/?[^>]+>/gi, '') ); };
    $("#divMessage").hide();
    $("#note").validate({
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
            //$("#btnDescription").attr("disabled", "disabled");
            //if(!$("#contentDescription").val()){
            var datoLimpio = $("#id_description").val().replace(/<\/?[^>]+>/gi, '');
            var s = datoLimpio.replace(/\s+/gi, ' ');
            s = s.replace(/^\s+|\s+$/gi, '');
            //alert(s+'---'+s.length);
            if(s.length > 0)
            {
                datos = $('#note').serialize();
                $.ajax({
                    type: "POST",
                    url: "index.php?module=notebook&cmd=Index&func=addNotebook&id=<?php echo $this->notebook_id;?>",
                    data: datos,
                    dataType: "json",
                    success: function(data){
                        switch(data.action){
                            case 1:
                                    $("#description_id").val(data.id);
                                    $("#divMessage").empty();
                                    $("#divMessage").addClass("ui-state-highlight ui-corner-all");
                                    $("#divMessage").append("<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span><strong>Alert:</strong>"+data.message+"</p>");
                                    $("#divMessage").show(1000);
                                    $("#divMessage").delay(3000).hide(2000);
                                break;
                            default:
                                    $("#divMessage").empty();
                                    $("#divMessage").addClass("ui-state-error ui-corner-all");
                                    $("#divMessage").append("<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><strong>Alert:</strong>"+data.message+"</p>");
                                    $("#divMessage").show(1000);
                                    $("#divMessage").delay(3000).hide(2000);                                
                                break;
                        }
                    },
                    timeout:80000
                });                
                /*****************************/

            }
            else{
                $("#divMessage").empty();
                $("#divMessage").addClass("ui-state-error ui-corner-all");
                $("#divMessage").append("<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><strong>Alert:</strong> The content field is required</p>");
                $("#divMessage").show(1000);
                $("#divMessage").delay(3000).hide(2000);
                //$("#btnDescription").removeAttr("disabled");
                return false;
            }

        }
    });
});
</script>
<link rel="StyleSheet" href="<?php echo $this->css; ?>" />
<div id="divMessage"></div>
<div id="notebook-content">
    <div id="notebook-content-left">
		<div id="notebook-content-leftinner">	
			<table width="100%" class="data_table">
        <?php         
         $noteList = $this->getNotesList();
            if (!empty($noteList)) {
                $i = 0;
                foreach ($noteList->getIterator() as $note) { 
                    //echo date("F j, Y, g:i a", strtotime(date('Y-m-d H:i:s')) );
                   echo '<tr class="'.($i%2==0?'row_odd':'row_even').'">
                            <td width="75%">
                                <a href="'.api_get_path(WEB_PATH).'main/index.php?module=notebook&'.api_get_cidreq().'&func=EditNotebook&id='.$note->notebook_id.'">'.$note->title.'</a>
                            </td>
                            <td valign="top" align="right" style="color:#999999;">
                                '.date("F j, Y", strtotime($note->creation_date)).'
                            </td>
                         </tr>';
                   $i++;
                }                
            } 
            else {
                echo '<tr><td>'.$this->get_lang('YouHaveNotPersonalNotesHere').'</td></tr>';
            }
        ?>
        </table>
		</div>
        <div class="pager" align="right"><?php //echo $pagerLinks; ?></div>    
    </div>
    
    <div id="notebook-content-right" class="notebook_form">
        <?php  
            $objForm = $this->getForm();
            echo $objForm->display(); ?>
    </div>
    
</div>