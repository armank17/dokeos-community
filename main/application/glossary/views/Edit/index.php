<!--View Add-->
<script type="text/javascript" src="<?php echo api_get_path(WEB_PATH);?>main/appcore/library/jquery/jquery.validate.js"></script>
<link rel="stylesheet" href="/main/application/glossary/assets/css/styles.css" />
<script src="/main/application/glossary/assets/scripts/script.js"></script>

<div id="glossary-add">
     <div id="divMessage"></div>
     <div class="fila">
        <?php echo $this->form->display();?>
     </div>
<!--     <div class="fila">
             <?php echo Display::return_icon('pixel.gif', $this->get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'))?>
             <?php echo $this->get_lang('Delete')?>
     </div>-->
         
</div>
<script type="text/javascript">
$(document).ready(function(){
    $("#divMessage").hide();
    $("#glossary-form").validate({
        debug: false,
        rules: {
            name: {
                required: true
                } 
        },
        messages: {
            name: {
                required: "<img src=\"<?php echo api_get_path(WEB_IMG_PATH)?>exclamation.png\" title=\'<?php echo $this->get_lang('Required')?>\' />"
            } 
        },
        submitHandler: function(form) {
            //alert($("#description").val());
            if(!$("#description").val()){
                $("#divMessage").empty();
                $("#divMessage").addClass("ui-state-error ui-corner-all");
                $("#divMessage").append("<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><strong>Alert:</strong> The content field is required</p>");
                $("#divMessage").show(1000);
                $("#divMessage").delay(3000).hide(2000);
                //alert('the content field is required'); 
                return false;
            }
            else{
//                $.post('?module=glossary&cmd=Add&func=createnew', $("#glossary-form").serialize(), function(data) {
//
//                });
                datos = $('#glossary-form').serialize();
                $.ajax({
                    type: "POST",
                    url: "index.php?module=glossary&cmd=Edit&func=edititem&id=<?php echo $this->_course;?>",
                    data: datos,
                    dataType: "json",
                    success: function(data){
                        //alert(data.action);
//                        switch(data.action){
//                            case 1:
                                $("#divMessage").empty();
                                $("#divMessage").addClass("ui-state-highlight ui-corner-all");
                                $("#divMessage").append("<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span><strong>Alert:</strong>"+data.message+"</p>");
                                $("#divMessage").show(1000);
                                $("#divMessage").delay(3000).hide(2000);
//                                break;
//                            case 2:
//                                
//                                break;
                           
//                        }
                    },
                    timeout:80000
                });
            }

        }
    });
});
</script>