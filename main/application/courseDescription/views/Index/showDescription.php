<script type="text/javascript" src="appcore/library/jquery/jquery.validate.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    $.fn.stripTags = function() { return this.replaceWith( this.html().replace(/<\/?[^>]+>/gi, '') ); };
    $("#divMessage").hide();
    $("#course_description").validate({
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
            //$("#btnDescription").attr("disabled", "disabled");
            //if(!$("#contentDescription").val()){
            var datoLimpio = $("#contentDescription").val().replace(/<\/?[^>]+>/gi, '');
            var s = datoLimpio.replace(/\s+/gi, ' ');
            s = s.replace(/^\s+|\s+$/gi, '');
            //alert(s+'---'+s.length);
            if(s.length > 0)
            {
                datos = $('#course_description').serialize();
                $.ajax({
                    type: "POST",
                    url: "index.php?module=courseDescription&cmd=Index&func=addDescription&id_description=<?php echo $this->id_description;?>",
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
<?php
echo $this->form_html->display();