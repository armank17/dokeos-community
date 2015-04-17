<!--View Add-->
<script type="text/javascript" src="<?php echo api_get_path(WEB_PATH);?>main/appcore/library/jquery/jquery.validate.js"></script>
<link rel="stylesheet" href="/main/application/glossary/assets/css/styles.css" />

<!--Validator Form-->
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
                    url: "index.php?module=glossary&cmd=Add&func=createnew&id=<?php echo $this->_course;?>",
                    data: datos,
                    dataType: "json",
                    success: function(data){
                        //alert(data.action);
                        switch(data.action){
                            case 1:
                                location.href='<?php echo api_get_path(WEB_PATH);?>main/index.php?module=glossary&cidReq='+data.course;
                                break;
                            case 2:
                                alert(data.message);
                                location.href = '<?php echo api_get_path(WEB_PATH);?>main/index.php?module=glossary&cidReq='+data.course;
                                break;
                            default:
                                alert('<?php echo GLOSSARY_MESSSAGE_NOT_POST;?>');
                                location.href = document.URL;
                                break;
                        }
                    },
                    timeout:80000
                });
                return false;
            }

        }
    });
});
</script>
<div id="glossary-add">
     <div id="divMessage"></div>
     <div><?php echo $this->form->display();?></div>
</div>
