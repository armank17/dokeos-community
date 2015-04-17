<script>
    $(document).ready(function(){
        //close_message_box
        $(".close_message_box").click(function() {
            $("#divMessage").html('');
        });
    });
</script>
<?php
if(!$this->is_payment){
    echo Display::display_normal_message('<em>'.get_lang('NotActivatedCourseCatalog').'</em>', false,true);
}
?>
<form name="formEditCourse" id="formEditCourse" action="index.php?module=ecommerce&cmd=Shop&func=updatePayment&<?php echo api_get_cidreq() ?>" method="POST" >
    <div style="width: 100%;">
        <button class="cancel" type="submit" style=" margin-top: 20px;" name="btnActive" id="btnActive"><?php echo $this->get_lang('ActivePayment') ?></button>
    </div>
</form>